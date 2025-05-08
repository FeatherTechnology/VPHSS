
<?php
include '../ajaxconfig.php';
@session_start();
$user_id =  $_SESSION["userid"];
$standard = $_POST['standard'];
$section = $_POST['section'];
$subjectData = ($_POST['subjectData']);

foreach ($subjectData as $subject) {
    $paper = $subject['paper_name'];
    $staff_id = $subject['staff_id'];

    // Check if record exists
    $checkQuery = "SELECT id FROM staff_subject_allocation WHERE standard = '$standard' AND section = '$section' AND paper_name = '$paper'";
    $checkResult = $connect->query($checkQuery);

    if ($checkResult->rowCount() > 0) {
        // Update existing
        $updateQuery = "UPDATE staff_subject_allocation SET 
            staff = '$staff_id', 
            update_login_id = '$user_id', 
            updated_on = NOW() 
            WHERE standard = '$standard' AND section = '$section' AND paper_name = '$paper'";
        $connect->query($updateQuery);
    } else {
        // Insert new
        $insertQuery = "INSERT INTO staff_subject_allocation 
            (standard, section, paper_name, staff, insert_login_id, created_on) 
            VALUES 
            ('$standard', '$section', '$paper', '$staff_id', '$user_id', NOW())";
        $connect->query($insertQuery);
    }
}

echo json_encode([
    "status" => "success",
    "message" => "Staff Alloacted Successfully"
]);

?>
