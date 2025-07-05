<?php
include '../ajaxconfig.php';
@session_start();

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];

if (isset($_SESSION["academic_year"])) {
    $academic_year = $_SESSION["academic_year"];
}

$response = ['html' => ''];

// Step 1: Get distinct paper names (subjects)
$paperNames = [];
$paperQry = $connect->query("
    SELECT DISTINCT paper_name 
    FROM student_mark_entry 
    WHERE standard = '$standard' AND section = '$section' AND exam = '$exam' AND academic_year = '$academic_year'
");

while ($row = $paperQry->fetch()) {
    $paperNames[] = $row['paper_name'];
}

// Step 1.5: Get out_of_marks for each paper
$outOfMarksList = [];
$outQry = $connect->query("
    SELECT paper_name, out_of_marks 
    FROM exam_creation 
    WHERE standard = '$standard' AND exam_type = '$exam' AND academic_year = '$academic_year'
");
while ($row = $outQry->fetch()) {
    $outOfMarksList[$row['paper_name']] = $row['out_of_marks'];
}

// Step 2: Get student data with all subjects and marks
$studentData = [];
$studentQry = $connect->query("
    SELECT sc.student_id, sc.admission_number, sc.student_name, sm.paper_name, sm.mark, sc.sms_sent_no
    FROM student_mark_entry sm
    JOIN student_creation sc ON sc.student_id = sm.student_id
    WHERE sm.standard = '$standard' AND sm.section = '$section' AND sm.exam = '$exam' AND sm.academic_year = '$academic_year'
    ORDER BY sc.student_name ASC
");

while ($row = $studentQry->fetch()) {
    $sid = $row['student_id'];
    if (!isset($studentData[$sid])) {
        $studentData[$sid] = [
            'admission_number' => $row['admission_number'],
            'student_name' => $row['student_name'],
            'sms_sent_no' => $row['sms_sent_no'],
            'marks' => [],
            'converted_total' => 0
        ];
    }

    $paper = $row['paper_name'];
    $mark = $row['mark'];

    $convertedMark = '-';

    if (is_numeric($mark) && isset($outOfMarksList[$paper]) && $outOfMarksList[$paper] > 0) {
        $convertedMark = round(($mark / $outOfMarksList[$paper]) * 100);
        $studentData[$sid]['converted_total'] += $convertedMark;
    }

    $studentData[$sid]['marks'][$paper] = [
        'original' => $mark,
        'converted' => $convertedMark
    ];
}

// Step 3: Build HTML table
$response['html'] .= "<table class='table table-bordered'>
    <thead>
        <tr>
            <th><input type='checkbox' id='selectAll'></th>
            <th>Admission No</th>
            <th>Student Name</th>";

foreach ($paperNames as $paper) {
    $outOf = isset($outOfMarksList[$paper]) ? $outOfMarksList[$paper] : '';
    $response['html'] .= "<th>$paper</th>";
}

$response['html'] .= "<th>Total</th></tr></thead><tbody>";

foreach ($studentData as $sid => $stu) {
    $response['html'] .= "<tr data-sms='{$stu['sms_sent_no']}'>
        <td><input type='checkbox' class='student-check' data-student-id='$sid'></td>
        <td>{$stu['admission_number']}</td>
        <td>{$stu['student_name']}</td>";

    $total = 0;

    foreach ($paperNames as $paper) {
        $markData = $stu['marks'][$paper] ?? ['original' => '-', 'converted' => '-'];
        $converted = $markData['converted'];
        $original = $markData['original'];

        if (is_numeric($converted)) {
            $display = $converted; // removed bracketed original marks
            $total += $converted;
        } else {
            $display = strtoupper($original) == 'AB' ? 'AB' : '-';
        }

        $response['html'] .= "<td>$display</td>";
    }

    $response['html'] .= "<td><b>$total</b></td></tr>";
}


$response['html'] .= "</tbody></table>";

// Add single button at the end of the table
if (!empty($studentData)) {
    $response['html'] .= "<div class='text-center mt-2'>
        <button id='sendSelectedSMS' class='btn btn-success'>Send SMS</button>
    </div>";
} else {
    $response['html'] = "<p>No student records found.</p>";
}

echo json_encode($response);
?>
