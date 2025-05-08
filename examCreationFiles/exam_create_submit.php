
<?php
include '../ajaxconfig.php';
@session_start();
$user_id =  $_SESSION["userid"];
$standard = $_POST['standard'];
$exam = $_POST['exam'];
$subjectData = ($_POST['subjectData']);

foreach ($subjectData as $subject) {
    $paper = $subject['paper_name'];
    $marks = $subject['out_of_marks'];
    $pass = $subject['pass_percent'];

    // Check if record exists
    $checkQuery = "SELECT id FROM exam_creation WHERE standard = '$standard' AND exam_type = '$exam' AND paper_name = '$paper'";
    $checkResult = $connect->query($checkQuery);

    if ($checkResult->rowCount() > 0) {
        // Update existing
        $updateQuery = "UPDATE exam_creation SET 
            out_of_marks = '$marks', 
            pass = '$pass', 
            update_login_id = '$user_id', 
            updated_on = NOW() 
            WHERE standard = '$standard' AND exam_type = '$exam' AND paper_name = '$paper'";
        $connect->query($updateQuery);
    } else {
        // Insert new
        $insertQuery = "INSERT INTO exam_creation 
            (standard, exam_type, paper_name, out_of_marks, pass, insert_login_id, created_on) 
            VALUES 
            ('$standard', '$exam', '$paper', '$marks', '$pass', '$user_id', NOW())";
        $connect->query($insertQuery);
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Exam subjects saved successfully"
]);

?>
