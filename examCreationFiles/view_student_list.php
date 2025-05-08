<?php
include '../ajaxconfig.php';

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];

// Step 1: Check if subjects exist
$subjectQry = $connect->query("SELECT * FROM `subject_details` WHERE class_id = '$standard'");
if ($subjectQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_subject']);
    exit;
}

// Step 2: Check if exam is created
$examQry = $connect->query("SELECT * FROM `exam_creation` WHERE standard = '$standard' AND exam_type = '$exam'");
if ($examQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_exam']);
    exit;
}

// Step 3: Check if staff allocation exists
$staffQry = $connect->query("SELECT * FROM `staff_subject_allocation` WHERE standard = '$standard' AND section = '$section'");
if ($staffQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_staff']);
    exit;
}

// Fetch students and subjects
$studentQry = $connect->query("SELECT student_id, student_name FROM student_creation WHERE standard = '$standard' AND section = '$section' ORDER BY student_name ASC");
$students = $studentQry->fetchAll();

$subjectQry = $connect->query("SELECT paper_name FROM subject_details WHERE class_id = '$standard'");
$subjects = $subjectQry->fetchAll();

// Start building HTML table
$tableHtml = '<table class="table table-bordered"><thead>';

// First header row: Subject names with mark
$tableHtml .= '<tr><th rowspan="2">Student Name</th>';
foreach ($subjects as $subject) {
    $tableHtml .= '<th>' . $subject['paper_name'] . '<br>Scored Mark</th>';
}
$tableHtml .= '</tr>';

// Second header row: Staff names
$tableHtml .= '<tr>';
foreach ($subjects as $subject) {
    $staffQry = $connect->query("SELECT CONCAT(first_name, ' ', last_name) AS staff_name
        FROM staff_creation s 
        JOIN staff_subject_allocation a ON s.id = a.staff
        WHERE a.standard = '$standard' AND a.section = '$section' AND a.paper_name = '".$subject['paper_name']."'");
    $staff = $staffQry->fetchColumn();
    $tableHtml .= '<td>' . ($staff ?? '-') . '</td>';
}
$tableHtml .= '</tr></thead><tbody>';

// Student rows
foreach ($students as $student) {
    $tableHtml .= '<tr><td>' . $student['student_name'] . '</td>';
    foreach ($subjects as $subject) {
        $markQry = $connect->query("SELECT mark 
            FROM student_mark_entry 
            WHERE standard = '$standard' AND section = '$section' 
            AND paper_name = '".$subject['paper_name']."' 
            AND student_id = '".$student['student_id']."'");
        $mark = $markQry->fetchColumn();
        $markValue = $mark ?? '0';

        $tableHtml .= '<td><input type="number" value="' . $markValue . '" class="form-control mark-input" data-student="' . $student['student_id'] . '" data-subject="' . $subject['paper_name'] . '" /></td>';
    }
    $tableHtml .= '</tr>';
}

$tableHtml .= '</tbody></table>';

// Return the final HTML table
echo json_encode(['status' => 'success', 'html' => $tableHtml]);
?>
