<?php
include '../ajaxconfig.php';
@session_start();

$admission_id = $_POST['student_id'];
$medium = $_POST['medium'];
$standard = $_POST['standard'];
$confirm = isset($_POST['confirm']) ? true : false;

if (isset($_SESSION["userid"])) {
    $userid = $_SESSION["userid"];
    $school_id = $_SESSION["school_id"];
    $academic_year = $_SESSION["academic_year"];
}

// Step 1: Get applicable fees_master_id
$getFeesMasterQry = $connect->query("
    SELECT DISTINCT gcf.fee_master_id 
    FROM group_course_fee gcf
    JOIN fees_master fm ON fm.fees_id = gcf.fee_master_id
    WHERE fm.standard = '$standard' 
      AND fm.medium = '$medium' 
      AND fm.academic_year = '$academic_year'
      AND gcf.status = 1
");

$feesMasterData = $getFeesMasterQry->fetchAll(PDO::FETCH_ASSOC);
if (empty($feesMasterData)) {
    echo json_encode([
        "status" => "error",
        "message" => "No fee structure found for the selected standard, medium, and academic year."
    ]);
    exit;
}

$new_fees_master_id = $feesMasterData[0]['fee_master_id'];

// Step 2: Fetch new group course + amenity fees total
$groupFees = $connect->query("
    SELECT grp_amount FROM group_course_fee 
    WHERE fee_master_id = '$new_fees_master_id' AND status = 1
")->fetchAll(PDO::FETCH_ASSOC);

$amenityFees = $connect->query("
    SELECT amenity_amount FROM amenity_fee 
    WHERE fee_master_id = '$new_fees_master_id' AND status = 1
")->fetchAll(PDO::FETCH_ASSOC);

$new_total_fees = array_sum(array_column($groupFees, 'grp_amount')) + array_sum(array_column($amenityFees, 'amenity_amount'));

// Step 3: Fetch old admission_fees
$oldFeesEntries = $connect->query("
    SELECT * FROM admission_fees 
    WHERE admission_id = '$admission_id' AND academic_year = '$academic_year'
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($oldFeesEntries)) {
    echo json_encode([
        "status" => "error",
        "message" => "No previous admission fee entries found."
    ]);
    exit;
}

$totalPaid = array_sum(array_column($oldFeesEntries, 'fees_collected'));

// Step 4: If paid > new fee, warn first
if ($totalPaid > $new_total_fees && !$confirm) {
    echo json_encode([
        "status" => "warning",
        "message" => "The amount already paid (₹$totalPaid) is greater than the new fee structure total (₹$new_total_fees). Do you still want to continue?"
    ]);
    exit;
}
if ($totalPaid > $new_total_fees && $confirm) {
    $refundAmount = $totalPaid - $new_total_fees;

    // Update student_creation table with refund amount
    $updateQry = $connect->query("UPDATE student_creation SET refund = '$refundAmount' WHERE student_id = '$admission_id'");
}

// Step 5: Collect old data before deletion
$oldReceipts = [];
foreach ($oldFeesEntries as $entry) {
    $ref_id = $entry['id'];
    $details = $connect->query("
        SELECT * FROM admission_fees_details 
        WHERE admission_fees_ref_id = '$ref_id'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $oldReceipts[] = [
        "receipt_no" => $entry['receipt_no'],
        "id" => $entry['id'],
        "receipt_date" => $entry['receipt_date'],
        "fees_collected" => $entry['fees_collected'],
        "scholarship" => $entry['scholarship'] ?? 0,
        "details" => $details
    ];
}

// Step 6: Delete old records
$oldRefIds = array_column($oldReceipts, 'id');
$oldRefIdsList = implode(',', array_map('intval', $oldRefIds));
if (!empty($oldRefIdsList)) {
    $connect->query("DELETE FROM admission_fees_details WHERE admission_fees_ref_id IN ($oldRefIdsList)");
}
$connect->query("DELETE FROM admission_fees WHERE admission_id = '$admission_id' AND academic_year = '$academic_year'");

// Step 7: Re-insert based on new structure
$groupFees = $connect->query("
    SELECT * FROM group_course_fee 
    WHERE fee_master_id = '$new_fees_master_id' AND status = 1
")->fetchAll(PDO::FETCH_ASSOC);

$amenityFees = $connect->query("
    SELECT * FROM amenity_fee 
    WHERE fee_master_id = '$new_fees_master_id' AND status = 1
")->fetchAll(PDO::FETCH_ASSOC);
$cumulativeCollected = 0;
$new_total_fees = array_sum(array_column($groupFees, 'grp_amount')) + array_sum(array_column($amenityFees, 'amenity_amount'));

$allFees = [];
foreach ($groupFees as $group) {
    $allFees[] = [
        "table" => "grptable",
        "id" => $group['grp_course_id'],
        "amount" => $group['grp_amount']
    ];
}
foreach ($amenityFees as $amenity) {
    $allFees[] = [
        "table" => "amenitytable",
        "id" => $amenity['amenity_fee_id'],
        "amount" => $amenity['amenity_amount']
    ];
}

$paidAmounts = [];
foreach ($allFees as $fee) {
    $feeKey = "{$fee['table']}-{$fee['id']}";
    $paidAmounts[$feeKey] = 0;
}

foreach ($oldReceipts as $receipt) {
    $receiptCollected = $receipt['fees_collected'];
    $collected = min($receiptCollected, $new_total_fees - $cumulativeCollected); // Only allocate remaining part of total fees
    $scholarship = $receipt['scholarship'];
    $cumulativeCollected += $collected;

    $balance = max($new_total_fees - $cumulativeCollected - $scholarship, 0);
    $total_fee_to_collect = max($new_total_fees - ($cumulativeCollected - $collected), 0);
    $final_amount_tobe_collect = max($new_total_fees - ($cumulativeCollected - $collected) - $scholarship, 0);
    $connect->query("
        INSERT INTO admission_fees 
        (admission_id, receipt_no, receipt_date, academic_year, school_id, fees_collected, balance_tobe_paid, scholarship, total_fees_tobe_collected, final_amount_tobe_collect)
        VALUES 
        ('$admission_id', '{$receipt['receipt_no']}', '{$receipt['receipt_date']}', '$academic_year', '$school_id', '$collected', '$balance', '$scholarship', '$total_fee_to_collect', '$final_amount_tobe_collect')
    ");
    $newRefId = $connect->lastInsertId();
    $alreadyInsertedFees = [];
    $remaining = $collected;
  $amenityPaidInOldFees = false;
foreach ($receipt['details'] as $detail) {
    if ($detail['fees_table_name'] == 'amenitytable') {
        $amenityPaidInOldFees = true;
        break;
    }
}

if ($amenityPaidInOldFees) {
    $amenityFeesTotal = 0;
    foreach ($allFees as $fee) {
        if ($fee['table'] == 'amenitytable') {
            $amenityFeesTotal += $fee['amount'];
        }
    }

    $amountToAssignToAmenityFees = min($remaining, $amenityFeesTotal);
    foreach ($allFees as $fee) {
        if ($fee['table'] == 'amenitytable') {
            $feeKey = "{$fee['table']}-{$fee['id']}";
            $paidAmount = $paidAmounts[$feeKey];
            $amountToAssign = min($amountToAssignToAmenityFees, $fee['amount'] - $paidAmount);
            $fee_received = $amountToAssign;
            $balance_tobe_paid = $fee['amount'] - ($paidAmount + $amountToAssign);
            $paidAmounts[$feeKey] += $amountToAssign;
            $amountToAssignToAmenityFees -= $amountToAssign;
            $remaining -= $amountToAssign;
            $connect->query("
                INSERT INTO admission_fees_details (admission_fees_ref_id, fees_master_id, fees_table_name, fees_id, fee_received, balance_tobe_paid, scholarship)
                VALUES ('$newRefId', '$new_fees_master_id', '{$fee['table']}', '{$fee['id']}', '$fee_received', '$balance_tobe_paid', '$scholarship')
            ");
        }
    }
}

foreach ($allFees as $fee) {
    if ($fee['table'] == 'grptable') {
        $feeKey = "{$fee['table']}-{$fee['id']}";
        $paidAmount = $paidAmounts[$feeKey];
        $amountToAssign = min($remaining, $fee['amount'] - $paidAmount);
        $fee_received = $amountToAssign;
        $balance_tobe_paid = $fee['amount'] - ($paidAmount + $amountToAssign);
        $paidAmounts[$feeKey] += $amountToAssign;
        $remaining -= $amountToAssign;
        $connect->query("
            INSERT INTO admission_fees_details (admission_fees_ref_id, fees_master_id, fees_table_name, fees_id, fee_received, balance_tobe_paid, scholarship)
            VALUES ('$newRefId', '$new_fees_master_id', '{$fee['table']}', '{$fee['id']}', '$fee_received', '$balance_tobe_paid', '$scholarship')
        ");
    }
}
}

echo json_encode([
    "status" => "success",
    "message" => "Previous fees deleted and restructured successfully with updated fee structure."
]);
