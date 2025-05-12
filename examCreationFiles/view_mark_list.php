<?php
include '../ajaxconfig.php';
@session_start();
$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];
if(isset($_SESSION["academic_year"])){
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

// Step 2: Get student data with all subjects and marks
$studentData = [];
$studentQry = $connect->query("
    SELECT sc.student_id, sc.admission_number, sc.student_name, sm.paper_name, sm.mark,sc.sms_sent_no
    FROM student_mark_entry sm
    JOIN student_creation sc ON sc.student_id = sm.student_id
    WHERE sm.standard = '$standard' AND sm.section = '$section' AND sm.exam = '$exam'AND sm.academic_year = '$academic_year'
    ORDER BY sc.student_name ASC 
");

while ($row = $studentQry->fetch()) {
    $sid = $row['student_id'];
    if (!isset($studentData[$sid])) {
        $studentData[$sid] = [
            'admission_number' => $row['admission_number'],
            'student_name' => $row['student_name'],
            'sms_sent_no' => $row['sms_sent_no'],
            'marks' => []
        ];
    }
    $studentData[$sid]['marks'][$row['paper_name']] = $row['mark'];
}

// Step 3: Build HTML table
$response['html'] .= "<table class='table table-bordered'>
    <thead>
        <tr>
            <th><input type='checkbox' id='selectAll'></th>
            <th>Admission No</th>
            <th>Student Name</th>";

foreach ($paperNames as $paper) {
    $response['html'] .= "<th>$paper</th>";
}

$response['html'] .= "<th>Total</th></tr></thead><tbody>";

foreach ($studentData as $sid => $stu) {
$response['html'] .= "<tr data-sms='{$stu['sms_sent_no']}'>
    <td><input type='checkbox' class='student-check' data-student-id='$sid'></td>
    <td>{$stu['admission_number']}</td>
    <td>{$stu['student_name']}</td>";


    $total = 0;
    $markData = [];
   foreach ($paperNames as $paper) {
    $mark = isset($stu['marks'][$paper]) ? $stu['marks'][$paper] : '-';
    $markDisplay = is_numeric($mark) ? $mark : '-';
    if (is_numeric($mark)) {
        $total += $mark;
    }
    $response['html'] .= "<td data-paper='$paper' data-mark='$markDisplay'>$markDisplay</td>";
}


$response['html'] .= "<td data-total='$total'>$total</td></tr>";

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
