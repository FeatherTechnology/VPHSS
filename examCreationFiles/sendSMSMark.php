<?php
include '../ajaxconfig.php';
@session_start();
$user_id = $_SESSION["userid"];
if(isset($_SESSION["academic_year"])){
    $academic_year = $_SESSION["academic_year"];
} 
$standard = $_POST['standard'];
$exam = $_POST['exam'];
$section = $_POST['section'];
$selectedStudents = $_POST['selectedStudents'];

foreach ($selectedStudents as $students) {
    $student_id = $students['student_id'];
    $admission_no = $students['admission_no'];
    $student_name = $students['student_name'];
    $smsNo = $students['smsNo'];
    $marks = $students['marks'];
    $total = $students['total'];

}
foreach ($selectedStudents as $students){
    $stuent_id = $students['student_id'];
}

echo json_encode([
    "status" => "success",
    "message" => "Exam marks send successfully"
]);
