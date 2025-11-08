<?php
include '../ajaxconfig.php';
@session_start();

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];

$academic_year = $_SESSION["academic_year"] ?? '';
$school_id = $_SESSION['school_id'] ?? '';

$response = ['html' => ''];

// Get school name
$getbrc = $mysqli->query("SELECT sc.school_name FROM school_creation sc WHERE sc.status = 0 AND school_id = '$school_id'");
$school_name = $getbrc->fetch_assoc()['school_name'] ?? '';

// Get exam name
$examQry = $connect->query("SELECT exam_type FROM exam_type WHERE id = '$exam' AND academic_year = '$academic_year'");
$exam_name = $examQry->fetch()['exam_type'] ?? '';

// Get standard name
$stdQry = $connect->query("SELECT standard FROM standard_creation WHERE standard_id = '$standard'");
$standard_name = $stdQry->fetch()['standard'] ?? '';

// Get student data
$studentData = [];
$studentQry = $connect->query("
    SELECT student_id, student_name, message_type, sms_date, status, message_id,sms_message
    FROM sms_delivery
    WHERE standard = '$standard' AND section = '$section' AND exam_name = '$exam' AND academic_year = '$academic_year'
    ORDER BY student_name ASC
");

while ($row = $studentQry->fetch()) {
    $sid = $row['student_id'];
    $studentData[] = [
        'student_id' => $sid,
        'student_name' => $row['student_name'],
        'message_type' => $row['message_type'],
        'sms_date' => $row['sms_date'],
        'status' => $row['status'],
        'message_id' => $row['message_id'],
        'sms_message' => $row['sms_message']
    ];
}



// Build HTML
$response['html'] .= "<h4 style='text-align:center; font-weight:bold; text-transform:uppercase;'>$school_name</h4>";
$response['html'] .= "<h5 style='text-align:center; font-weight:bold;'>Exam: $exam_name | Standard: $standard_name - $section</h5><br>";
$response['html'] .= "<table class='table table-bordered'><thead><tr>
    <th width='20'>S.NO</th>
    <th>Student Name</th>
    <th>Message Type</th>
    <th>SMS Date</th>
    <th>Status</th>
    <th>Comments</th>
    <th>MessageID</th>";



// Table rows
if (!empty($studentData)) {
    $i = 1;
    foreach ($studentData as $data) {
        $response['html'] .= "<tr>
        <td>$i</td>
        <td>{$data['student_name']}</td>
        <td>{$data['message_type']}</td>
        <td>" . date('d-m-Y', strtotime($data['sms_date'])) . "</td>
        <td>{$data['status']}</td>
        <td>{$data['sms_message']}</td>
        <td>{$data['message_id']}</td>
    </tr>";
        $i++; // increment here
    }

    $response['html'] .= "</tbody></table>";
} else {
    $response['html'] = "<p>No records found.</p>";
}

echo json_encode($response);
