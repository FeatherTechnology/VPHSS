<?php
include "../../ajaxconfig.php";
@session_start();
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];
}

if (isset($_POST['feeType'])) {
    $feeType = $_POST['feeType'];
}
if (isset($_POST['dateSelect'])) {
    $dateSelect = $_POST['dateSelect'];
}
if (isset($_POST['singleDate'])) {
    $singleDate = $_POST['singleDate'];
}
if (isset($_POST['feesFromDate'])) {
    $feesFromDate = new DateTime($_POST['feesFromDate']);
    $startdate = clone $feesFromDate;
}
if (isset($_POST['feesToDate'])) {
    $feesToDate = new DateTime($_POST['feesToDate']);
    $to_date = $feesToDate->format('Y-m-d');
}
$getbrc = $mysqli->query("SELECT sc.school_name, sc.district, sc.pincode FROM school_creation sc WHERE sc.status = 0 AND school_id = '$school_id'");
while ($schoolInfo = $getbrc->fetch_assoc()) {
    $school_name     = $schoolInfo["school_name"];
    $district  = $schoolInfo["district"];
    $pincode  = $schoolInfo["pincode"];
}
if ($dateSelect == 'singledate') {

    if ($feeType == 'grptable' || $feeType == 'extratable' || $feeType == 'amenitytable') { //school
        $Qry = "SELECT af.receipt_no, sc.admission_number, sc.student_name, std.standard, sh.section, 
        SUM(CASE WHEN afd.fees_table_name = 'grptable' THEN afd.fee_received ELSE 0 END) AS grp_fee,
        SUM(CASE WHEN afd.fees_table_name = 'extratable' THEN afd.fee_received ELSE 0 END) AS extra_fee,
        SUM(CASE WHEN afd.fees_table_name = 'amenitytable' THEN afd.fee_received ELSE 0 END) AS amenity_fee
        FROM `admission_fees` af 
        JOIN admission_fees_details afd ON af.id = afd.admission_fees_ref_id 
        JOIN student_creation sc ON af.admission_id = sc.student_id 
          JOIN student_history sh ON sh.student_id = sc.student_id AND af.academic_year = sh.academic_year
        JOIN standard_creation std ON sh.standard = std.standard_id 
         JOIN group_course_fee gcf 
            ON afd.fees_id = gcf.grp_course_id
        WHERE af.receipt_date ='$singleDate' AND afd.fee_received > 0 AND afd.fees_table_name = '$feeType' AND sc.school_id = '$school_id' AND sc.status = 0
        GROUP BY 
             afd.id,
            af.receipt_no, 
            sc.admission_number, 
            sc.student_name, 
            std.standard, 
            sh.section  ORDER BY CAST(SUBSTRING(receipt_no, LOCATE('-', receipt_no) + 1) AS UNSIGNED)";
    } else if ($feeType == 'lastyear') { //Last Year
        $Qry = "SELECT 
    lyf.receipt_no, 
    sc.admission_number, 
    sc.student_name, 
    std.standard, 
    sh.section, 
    lyf.receipt_date,
    (CASE WHEN lyfd.fees_table_name = 'grptable' THEN lyfd.fee_received ELSE 0 END) AS group_fees,
    (CASE WHEN lyfd.fees_table_name = 'transport' THEN lyfd.fee_received ELSE 0 END) AS transport_fees,
    (CASE WHEN lyfd.fees_table_name = 'amenitytable' THEN lyfd.fee_received ELSE 0 END) AS amenity_fees
FROM last_year_fees lyf 
JOIN last_year_fees_details lyfd ON lyf.id = lyfd.admission_fees_ref_id 
JOIN student_creation sc ON lyf.admission_id = sc.student_id
JOIN student_history sh ON sh.student_id = sc.student_id AND lyf.academic_year = sh.academic_year 
JOIN standard_creation std ON sh.standard = std.standard_id 

WHERE 
    lyf.receipt_date = '$singleDate' 
    AND lyfd.fee_received > 0 
    AND sc.school_id = '$school_id' 
    AND sc.status = 0 
ORDER BY 
    CAST(SUBSTRING(lyf.receipt_no, LOCATE('-', lyf.receipt_no) + 1) AS UNSIGNED)";
    } else if ($feeType == 'transport') { //Transport
        $Qry = "SELECT taf.receipt_no, sc.admission_number, sc.student_name, std.standard, sh.section, 0 AS grp_fee, 0 AS extra_fee, tafd.fee_received AS transportFees 
        FROM `transport_admission_fees` taf 
        JOIN transport_admission_fees_details tafd ON taf.id = tafd.admission_fees_ref_id 
        JOIN student_creation sc ON taf.admission_id = sc.student_id 
              JOIN student_history sh ON sh.student_id = sc.student_id AND taf.academic_year = sh.academic_year 
        JOIN standard_creation std ON sh.standard = std.standard_id 
        WHERE taf.receipt_date ='$singleDate' AND tafd.fee_received > 0 AND sc.school_id = '$school_id' AND sc.status = 0 GROUP BY 
        tafd.id,
             taf.receipt_no,
            sc.admission_number, 
            sc.student_name, 
            std.standard ORDER BY CAST(SUBSTRING(receipt_no, LOCATE('-', receipt_no) + 1) AS UNSIGNED)";
    }
?>

    <table class="table table-bordered" id="show_dayend_report_list">
        <thead>
            <?php if ($feeType == 'lastyear') { ?>
                <tr>
                    <th colspan='9'>Day End Report At <?php echo date('d-m-Y', strtotime($singleDate)); ?> </th>
                </tr>
            <?php } else { ?>
                <tr>
                    <th colspan='7'>Day End Report At <?php echo date('d-m-Y', strtotime($singleDate)); ?> </th>
                </tr>
            <?php } ?>
            <tr>
                <th>S.No</th>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Standard & Section</th>
                <?php if ($feeType == 'lastyear') { ?>
                    <th>Group Fee</th>
                    <th>Amenity Fee</th>
                    <th>Transport Fee</th>
                <?php } else { ?>
                    <th>Collected Fee</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $single_total = 0;
            $single_total2 = 0;
            $single_total3 = 0;
            $a = 1;
            $getFeeCollectionQry = $connect->query("$Qry");
            while ($feeCollection = $getFeeCollectionQry->fetchObject()) {

                if ($feeType == 'grptable') {
                    $schoolfee_total = $feeCollection->grp_fee;
                } else if ($feeType == 'extratable') {
                    $schoolfee_total = $feeCollection->extra_fee;
                } else if ($feeType == 'amenitytable') {
                    $schoolfee_total = $feeCollection->amenity_fee;
                } else if ($feeType == 'lastyear') {
                    $schoolfee_total = $feeCollection->group_fees;
                    $schoolfee_total2 = $feeCollection->amenity_fees;
                    $schoolfee_total3 = $feeCollection->transport_fees;
                } else if ($feeType == 'transport') {
                    $schoolfee_total = $feeCollection->transportFees;
                } else {
                    $schoolfee_total = '0';
                }
            ?>

                <tr>
                    <td><?php echo $a++; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($singleDate)); ?></td>
                    <td><?php echo $feeCollection->receipt_no; ?></td>
                    <td><?php echo $feeCollection->admission_number; ?></td>
                    <td><?php echo $feeCollection->student_name; ?></td>
                    <td><?php echo $feeCollection->standard . '-' . $feeCollection->section; ?></td>
                    <?php if ($feeType == 'lastyear') { ?>
                        <td><?php echo $schoolfee_total; ?></td>
                        <td><?php echo $schoolfee_total2; ?></td>
                        <td><?php echo $schoolfee_total3; ?></td>
                    <?php } else { ?>
                        <td><?php echo $schoolfee_total; ?></td>
                    <?php } ?>
                </tr>

            <?php
                if ($feeType == 'lastyear') {
                    $single_total += $schoolfee_total;
                    $single_total2 += $schoolfee_total2;
                    $single_total3 += $schoolfee_total3;
                } else {
                    $single_total += $schoolfee_total;
                }
            } ?>
            <tr style="font-weight: bold;">
                <td><?php echo $a; ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Grand Total</td>
                <?php if ($feeType == 'lastyear') { ?>
                    <td><?php echo $single_total; ?></td>
                    <td><?php echo $single_total2; ?></td>
                    <td><?php echo $single_total3; ?></td>
                <?php } else { ?>
                    <td><?php echo $single_total; ?></td>
                <?php } ?>
            </tr>
        </tbody>
    </table>

<?php } else if ($dateSelect == 'multipledate') { ?>

    <table class="table table-bordered" id="show_dayend_report_list">
        <thead>
            <?php if ($feeType == 'lastyear') { ?>
                <tr>
                    <th colspan='9'>Day End Report From <?php echo $feesFromDate->format('d-m-Y'); ?> To <?php echo $feesToDate->format('d-m-Y'); ?></th>
                </tr>
            <?php } else { ?>
                <tr>
                    <th colspan='7'>Day End Report From <?php echo $feesFromDate->format('d-m-Y'); ?> To <?php echo $feesToDate->format('d-m-Y'); ?></th>
                </tr>
            <?php } ?>

            <tr>
                <th>S.No</th>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Standard & Section</th>

                <?php if ($feeType == 'lastyear') { ?>
                    <th>Group Fee</th>
                    <th>Amenity Fee</th>
                    <th>Transport Fee</th>
                <?php } else { ?>
                    <th>Collected Fee</th>
                <?php } ?>
            </tr>

        </thead>
        <tbody>
            <?php
            $multiple_total = 0;
            $multiple_total2 = 0;
            $multiple_total3 = 0;
            $a = 1;
            while ($startdate <= $feesToDate) {
                $from_date = $startdate->format('Y-m-d');

                if ($feeType == 'grptable' || $feeType == 'extratable' || $feeType == 'amenitytable') { //school
                    $Qry = "SELECT af.receipt_no, sc.admission_number, sc.student_name, std.standard, sh.section, af.receipt_date,
        SUM(CASE WHEN afd.fees_table_name = 'grptable' THEN afd.fee_received ELSE 0 END) AS grp_fee,
        SUM(CASE WHEN afd.fees_table_name = 'extratable' THEN afd.fee_received ELSE 0 END) AS extra_fee,
        SUM(CASE WHEN afd.fees_table_name = 'amenitytable' THEN afd.fee_received ELSE 0 END) AS amenity_fee
        FROM `admission_fees` af 
        JOIN admission_fees_details afd ON af.id = afd.admission_fees_ref_id 
        JOIN student_creation sc ON af.admission_id = sc.student_id 
        JOIN student_history sh ON sh.student_id = sc.student_id AND af.academic_year = sh.academic_year
        JOIN standard_creation std ON sh.standard = std.standard_id 
        WHERE af.receipt_date ='$from_date' AND afd.fee_received > 0 AND afd.fees_table_name = '$feeType' AND sc.school_id = '$school_id' AND sc.status = 0
        GROUP BY 
            afd.id,
            af.receipt_no, 
            sc.admission_number, 
            sc.student_name, 
            std.standard, 
            sh.section ORDER BY CAST(SUBSTRING(receipt_no, LOCATE('-', receipt_no) + 1) AS UNSIGNED)";
                } else if ($feeType == 'lastyear') { //Last Year
                    $Qry = "SELECT 
    lyf.receipt_no, 
    sc.admission_number, 
    sc.student_name, 
    std.standard, 
    sh.section, 
    lyf.receipt_date,
    (CASE WHEN lyfd.fees_table_name = 'grptable' THEN lyfd.fee_received ELSE 0 END) AS group_fees,
    (CASE WHEN lyfd.fees_table_name = 'transport' THEN lyfd.fee_received ELSE 0 END) AS transport_fees,
    (CASE WHEN lyfd.fees_table_name = 'amenitytable' THEN lyfd.fee_received ELSE 0 END) AS amenity_fees
FROM last_year_fees lyf 
JOIN last_year_fees_details lyfd ON lyf.id = lyfd.admission_fees_ref_id 
JOIN student_creation sc ON lyf.admission_id = sc.student_id
JOIN student_history sh ON sh.student_id = sc.student_id AND lyf.academic_year = sh.academic_year 
JOIN standard_creation std ON sh.standard = std.standard_id 

WHERE 
    lyf.receipt_date = '$from_date' 
    AND lyfd.fee_received > 0 
    AND sc.school_id = '$school_id' 
    AND sc.status = 0 
ORDER BY 
    CAST(SUBSTRING(lyf.receipt_no, LOCATE('-', lyf.receipt_no) + 1) AS UNSIGNED)";
                } else if ($feeType == 'transport') { //Transport
                    $Qry = "SELECT taf.receipt_no, sc.admission_number, sc.student_name, std.standard, sh.section, 0 AS grp_fee, 0 AS extra_fee, tafd.fee_received AS transportFees, taf.receipt_date 
        FROM `transport_admission_fees` taf 
        JOIN transport_admission_fees_details tafd ON taf.id = tafd.admission_fees_ref_id 
        JOIN student_creation sc ON taf.admission_id = sc.student_id 
        JOIN student_history sh ON sh.student_id = sc.student_id AND taf.academic_year = sh.academic_year 
        JOIN standard_creation std ON sh.standard = std.standard_id 
        WHERE taf.receipt_date ='$from_date' AND tafd.fee_received > 0 AND sc.school_id = '$school_id' AND sc.status = 0 GROUP BY 
        tafd.id,
             taf.receipt_no,
            sc.admission_number, 
            sc.student_name, 
            std.standard ORDER BY CAST(SUBSTRING(receipt_no, LOCATE('-', receipt_no) + 1) AS UNSIGNED)";
                }

                $getFeeCollectionQry = $connect->query("$Qry");
                while ($feeCollection = $getFeeCollectionQry->fetchObject()) {

                    if ($feeType == 'grptable') {
                        $grand_fee_total = $feeCollection->grp_fee;
                    } else if ($feeType == 'extratable') {
                        $grand_fee_total = $feeCollection->extra_fee;
                    } else if ($feeType == 'amenitytable') {
                        $grand_fee_total = $feeCollection->amenity_fee;
                    } else if ($feeType == 'lastyear') {
                        $grand_fee_total1 = $feeCollection->group_fees;
                        $grand_fee_total2 = $feeCollection->amenity_fees;
                        $grand_fee_total3 = $feeCollection->transport_fees;
                    } else if ($feeType == 'transport') {
                        $grand_fee_total = $feeCollection->transportFees;
                    } else {
                        $grand_fee_total = '0';
                    }
            ?>
                    <tr>
                        <td><?php echo $a++; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($feeCollection->receipt_date)); ?></td>
                        <td><?php echo $feeCollection->receipt_no; ?></td>
                        <td><?php echo $feeCollection->admission_number; ?></td>
                        <td><?php echo $feeCollection->student_name; ?></td>
                        <td><?php echo $feeCollection->standard . '-' . $feeCollection->section; ?></td>

                        <?php if ($feeType == 'lastyear') { ?>
                            <td><?php echo $grand_fee_total1; ?></td>
                            <td><?php echo $grand_fee_total2; ?></td>
                            <td><?php echo $grand_fee_total3; ?></td>
                        <?php } else { ?>
                            <td><?php echo $grand_fee_total; ?></td>
                        <?php } ?>
                    </tr>

            <?php
                    if ($feeType == 'lastyear') {
                        $multiple_total += $grand_fee_total1;
                        $multiple_total2 += $grand_fee_total2;
                        $multiple_total3 += $grand_fee_total3;
                    } else {
                        $multiple_total += $grand_fee_total;
                    }
                }

                $startdate->modify('+1 day');
            } //End of While loop for getting dates from start to end date. 
            ?>
            <tr style="font-weight: bold;">
                <td><?php echo $a; ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>Grand Total</td>
                <?php if ($feeType == 'lastyear') { ?>
                    <td><?php echo $multiple_total; ?></td>
                    <td><?php echo $multiple_total2; ?></td>
                    <td><?php echo $multiple_total3; ?></td>
                <?php } else { ?>
                    <td><?php echo $multiple_total; ?></td>
                <?php } ?>
            </tr>
        </tbody>
    </table>

<?php
} ?>

<script>
    $(document).ready(function() {
        var schoolName = "<?php echo $school_name . ' - ' . $district . ' - ' . $pincode; ?>";

        var feeHeading = "<?php
            if ($feeType == 'grptable') {
                echo 'Group Fees';
            } elseif ($feeType == 'amenitytable') {
                echo 'Amenity Fees';
            } elseif ($feeType == 'lastyear') {
                echo 'Last Year Fees';
            } elseif ($feeType == 'transport') {
                echo 'Transport Fees';
            } else {
                echo 'Day End Report';
            }
        ?>";

        var table = $('#show_dayend_report_list').DataTable({
            order: [[0, "asc"]],
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf',
                {
                    extend: 'print',
                    text: 'Print',
                    title: '',
                    customize: function(win) {
                        $(win.document.body)
                            .prepend(
                                '<h2 style="text-align:center;">' + schoolName + '</h2>' +
                                '<h4 style="text-align:center;">' + feeHeading + '</h4><br>'
                            );

                        var originalThead = $('#show_dayend_report_list thead').clone();
                        $(win.document.body).find('table thead').replaceWith(originalThead);

                        // Only style table headers (th), not table data (td)
                        $(win.document.head).append(
                            '<style>' +
                            'table th { color: black !important; font-weight: bold !important; }' +
                            'table { border-collapse: collapse !important; width: 100%; }' +
                            '</style>'
                        );
                    }
                }
            ],
            paging: false
        });
    });
</script>

