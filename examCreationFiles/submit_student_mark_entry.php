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
$marksData = $_POST['marks'];

foreach ($marksData as $mrks) {
    $student_id = $mrks['student_id'];
    $staff_id = $mrks['staff_id'];
    $paper = $mrks['subject'];
    $mark = $mrks['mark'];
    // Skip if mark is not a valid number
    // Allow both numeric and "A"
if (!is_numeric($mark) && strtoupper($mark) !== 'A') {
    continue;
}

    // Check if record exists for this student and subject
    $checkQuery = "SELECT id FROM student_mark_entry 
        WHERE student_id = '$student_id' 
        AND standard = '$standard' 
        AND section = '$section' 
        AND exam = '$exam' 
        AND paper_name = '$paper' AND  academic_year = '$academic_year'";

    $checkResult = $connect->query($checkQuery);

    if ($checkResult->rowCount() > 0) {
        // Update existing
        $updateQuery = "UPDATE student_mark_entry SET 
            mark = '$mark', 
            update_login_id = '$user_id', 
            updated_on = NOW() 
            WHERE student_id = '$student_id' 
            AND standard = '$standard' 
            AND section = '$section' 
            AND exam = '$exam' 
            AND paper_name = '$paper' AND academic_year = '$academic_year'";
        $connect->query($updateQuery);
    } else {
        // Insert new
        $insertQuery = "INSERT INTO student_mark_entry 
            (student_id, standard, section, exam, paper_name, staff_id, mark,academic_year ,insert_login_id, created_on) 
            VALUES 
            ('$student_id', '$standard', '$section', '$exam', '$paper', '$staff_id', '$mark','$academic_year', '$user_id', NOW())";
        $connect->query($insertQuery);
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Exam marks saved successfully"
]);
