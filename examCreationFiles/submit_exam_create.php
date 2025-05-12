<?php
include '../ajaxconfig.php';
@session_start();
if(isset($_SESSION["academic_year"])){
    $academic_year = $_SESSION["academic_year"];
} 
$exam = $_POST['exam'];
$standard = $_POST['standard'];

// Step 1: Fetch all subject names for this standard
$subjectQry = $connect->query("SELECT * FROM `subject_details` WHERE class_id = '$standard' AND academic_year = '$academic_year'");
$subjects = [];

if ($subjectQry->rowCount() > 0) {
    while ($row = $subjectQry->fetch()) {
        $subjects[$row['paper_name']] = [
            'paper_name' => $row['paper_name'],
            'mrk' => $row['max_mark'],
            'max_mark' => '',
            'pass_mark' => '',
            'already_created' => false
        ];
    }
}

// Step 2: Fetch existing exam papers
$examQry = $connect->query("SELECT * FROM `exam_creation` WHERE standard = '$standard' AND exam_type = '$exam' AND academic_year = '$academic_year'");
if ($examQry->rowCount() > 0) {
    while ($row = $examQry->fetch()) {
        $subjects[$row['paper_name']] = [
            'paper_name' => $row['paper_name'],
            'max_mark' => $row['out_of_marks'],
            'pass_mark' => $row['pass'],
            'already_created' => true
        ];
    }
}

// Step 3: Return the merged subject list
echo json_encode(array_values($subjects));
?>
