<?php
include '../ajaxconfig.php';

$exam = $_POST['exam'];
$standard = $_POST['standard'];

// Step 1: Fetch all subject names for this standard
$subjectQry = $connect->query("SELECT * FROM `subject_details` WHERE class_id = '$standard'");
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
$examQry = $connect->query("SELECT * FROM `exam_creation` WHERE standard = '$standard' AND exam_type = '$exam'");
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
