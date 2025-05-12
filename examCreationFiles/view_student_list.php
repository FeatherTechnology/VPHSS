<?php
include '../ajaxconfig.php';
@session_start();
if (isset($_SESSION["academic_year"])) {
    $academic_year = $_SESSION["academic_year"];
}

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];

// Step 1: Check if subjects exist
$subjectQry = $connect->query("SELECT * FROM `subject_details` WHERE class_id = '$standard'AND academic_year = '$academic_year'");
if ($subjectQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_subject']);
    exit;
}

// Step 2: Check if exam is created
$examQry = $connect->query("SELECT * FROM `exam_creation` WHERE standard = '$standard' AND exam_type = '$exam' AND academic_year = '$academic_year'");
if ($examQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_exam']);
    exit;
}

// Step 3: Check if staff allocation exists
$staffQry = $connect->query("SELECT * FROM `staff_subject_allocation` WHERE standard = '$standard' AND section = '$section' AND academic_year = '$academic_year'");
if ($staffQry->rowCount() == 0) {
    echo json_encode(['status' => 'no_staff']);
    exit;
}

// Fetch students
$studentQry = $connect->query("SELECT student_id, student_name FROM student_creation WHERE standard = '$standard' AND section = '$section' AND status =0 AND year_id = '$academic_year' ORDER BY student_name ASC");
$students = $studentQry->fetchAll();

// Fetch only subjects with staff allocated
$subjectQry = $connect->query("
    SELECT DISTINCT sd.paper_name, CONCAT(sc.first_name, ' ', sc.last_name) AS staff_name,ec.out_of_marks,ec.pass,sc.id as staff_id
    FROM subject_details sd
    JOIN staff_subject_allocation ssa ON sd.paper_name = ssa.paper_name AND sd.class_id = ssa.standard AND ssa.academic_year = '$academic_year'
    JOIN staff_creation sc ON sc.id = ssa.staff
    JOIN exam_creation ec ON ec.paper_name = ssa.paper_name AND ec.standard = ssa.standard
    WHERE ssa.standard = '$standard' AND ssa.section = '$section'  AND ec.exam_type = '$exam' AND sd.academic_year = '$academic_year'
");
$subjects = $subjectQry->fetchAll();

// Start building HTML table
$tableHtml = '<table class="table table-bordered"><thead>';

// First header row: Subject names
$tableHtml .= '<tr><th rowspan="2">Student Name</th>';
foreach ($subjects as $subject) {
    $tableHtml .= '<th>' . htmlspecialchars($subject['paper_name']) . '<br>Scored Mark</th>';
}
$tableHtml .= '</tr>';

// Second header row: Staff names
$tableHtml .= '<tr>';
$tableHtml .= '<tr><td rowspan="1" style="font-weight: bold;">Staff Name</td>';
foreach ($subjects as $subject) {
    $tableHtml .= '<td style="text-align: left;font-weight: bold;">' . htmlspecialchars($subject['staff_name']) . '</td>';
}
$tableHtml .= '</tr></thead><tbody>';

// Student rows
foreach ($students as $student) {
    $tableHtml .= '<tr><td>' . htmlspecialchars($student['student_name']) . '</td>';
    foreach ($subjects as $subject) {
        $markQry = $connect->query("SELECT mark 
            FROM student_mark_entry 
            WHERE standard = '$standard' AND section = '$section' 
            AND paper_name = '" . $subject['paper_name'] . "' 
            AND student_id = '" . $student['student_id'] . "' AND exam = '$exam' AND academic_year = '$academic_year'");

        $mark = $markQry->fetchColumn();
        $markValue = is_numeric($mark) ? floatval($mark) : '';
        $passMark = floatval($subject['pass']);
        
        $class = ($markValue < $passMark) ? 'text-danger' : '';
        
        $tableHtml .= '<td><input type="text" value="' . $markValue . '" 
            class="form-control mark-input ' . $class . '" 
            style="width: 75px;" 
            data-student="' . $student['student_id'] . '" 
            data-staff="' . $subject['staff_id'] . '" 
            data-subject="' . htmlspecialchars($subject['paper_name']) . '" 
            data-outof="' . $subject['out_of_marks'] . '" 
            data-pass="' . $passMark . '" /></td>';
        
    }
    $tableHtml .= '</tr>';
}


$tableHtml .= '</tbody></table>';

// Add Save button
$tableHtml .= '<div class="text-right mt-3">
    <button type="button" id="saveMark" class="btn btn-primary">Save</button>
</div>';

// Return the final HTML
echo json_encode(['status' => 'success', 'html' => $tableHtml]);