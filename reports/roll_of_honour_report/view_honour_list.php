<?php
include '../../ajaxconfig.php';
@session_start();

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$academic_year = $_SESSION['academic_year'];

$school_id = isset($_SESSION['school_id']) ? $_SESSION['school_id'] : '';
$response = ['html' => ''];

// Get School Name
$school_name = '';
$getbrc = $mysqli->query("SELECT school_name ,district,address2 FROM school_creation WHERE status = 0 AND school_id = '$school_id'");
if ($getbrc && $row = $getbrc->fetch_assoc()) {
    $school_name = $row["school_name"];
    $district = $row["district"];
    $address2 = $row["address2"];
}

// Get Exam Name
$exam_name = '';
$examQry = $connect->query("SELECT exam_type FROM exam_type WHERE id = '$exam' AND academic_year = '$academic_year'");
if ($examQry && $row = $examQry->fetch()) {
    $exam_name = $row['exam_type'];
}

// Get Standard Name
$standard_name = '';
$stdQry = $connect->query("SELECT standard FROM standard_creation WHERE standard_id = '$standard'");
if ($stdQry && $row = $stdQry->fetch()) {
    $standard_name = $row['standard'];
}

// Step 1: Get out_of_marks for each subject
$outOfMarksList = [];
$outQry = $connect->query("SELECT paper_name, out_of_marks FROM exam_creation 
                           WHERE standard = '$standard' AND exam_type = '$exam' AND academic_year = '$academic_year'");
while ($row = $outQry->fetch()) {
    $outOfMarksList[$row['paper_name']] = $row['out_of_marks'];
}

// Step 2: Get student marks
$studentData = [];
$studentQry = $connect->query("SELECT sc.student_id, sc.student_name, sc.section, sm.paper_name, sm.mark
                               FROM student_mark_entry sm
                               JOIN student_creation sc ON sc.student_id = sm.student_id
                               WHERE sm.standard = '$standard' AND sm.exam = '$exam' AND sm.academic_year = '$academic_year'
                               ORDER BY sc.student_name ASC");

while ($row = $studentQry->fetch()) {
    $sid = $row['student_id'];
    if (!isset($studentData[$sid])) {
        $studentData[$sid] = [
            'student_id' => $sid,
            'student_name' => $row['student_name'],
            'section' => $row['section'],
            'marks' => [],
            'converted_total' => 0,
        ];
    }

    $mark = $row['mark'];
    $paper = $row['paper_name'];

    if (is_numeric($mark) && isset($outOfMarksList[$paper]) && is_numeric($outOfMarksList[$paper]) && $outOfMarksList[$paper] > 0) {
        $converted = round(($mark / $outOfMarksList[$paper]) * 100);
        $studentData[$sid]['marks'][$paper] = $converted;
        $studentData[$sid]['converted_total'] += $converted;
    }
}

// Step 3: Filter students with converted_total > 400
$filteredStudents = array_filter($studentData, function ($stu) {
    return $stu['converted_total'] > 400;
});

// Step 4: Sort by converted_total descending
usort($filteredStudents, function ($a, $b) {
    return $b['converted_total'] <=> $a['converted_total'];
});

// Step 5: Assign ranks
$rank = 0;
$actualRank = 0;
$prevTotal = null;
foreach ($filteredStudents as &$stu) {
    $actualRank++;
    if ($stu['converted_total'] === $prevTotal) {
        $stu['rank'] = $rank;
    } else {
        $rank = $actualRank;
        $stu['rank'] = $rank;
    }
    $prevTotal = $stu['converted_total'];
}
unset($stu);
$response['html'] .= "
<div id='honour_report'>
    <h4 style='text-align:center; font-weight:bold;'>$school_name</h4>
    <p style='text-align:center;'>$district, $address2</p>
    <h5 style='text-align:center; font-weight:bold;'>ROLL OF HONOURS - ($academic_year)</h5>
    <h5 style='text-align:center;'>$exam_name</h5>
    <h5 style='text-align:center;'>$standard_name</h5>
    <br>
    <table class='table table-bordered' id='student_total_list'>
        <thead style='background-color:#aad4f5;'>
            <tr>
                <th>S.No</th>
                <th>Name</th>
                <th>Standard & Sec</th>
                <th>Marks</th>
                <th>Rank</th>
            </tr>
        </thead>
        <tbody>
";

$sno = 1;
foreach ($filteredStudents as $stu) {
    $response['html'] .= "
            <tr>
                <td>$sno</td>
                <td>{$stu['student_name']}</td>
                <td>$standard_name - {$stu['section']}</td>
                <td>{$stu['converted_total']}</td>
                <td>{$stu['rank']}</td>
            </tr>
    ";
    $sno++;
}

$response['html'] .= "
        </tbody>
    </table>
</div>
";

echo json_encode($response);
