<?php
include '../../ajaxconfig.php';
@session_start();

$exam = $_POST['exam'];
$standard = $_POST['standard'];
$section = $_POST['section'];
$academic_year = $_SESSION['academic_year'];
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];
}
$getbrc = $mysqli->query("SELECT sc.school_name FROM school_creation sc WHERE sc.status = 0 AND school_id = '$school_id'");
while ($schoolInfo = $getbrc->fetch_assoc()) {
    $school_name     = $schoolInfo["school_name"];
}
$response = ['html' => ''];

// Step 1: Get distinct paper names
$paperNames = [];
$paperQry = $connect->query("
    SELECT DISTINCT paper_name 
    FROM student_mark_entry 
    WHERE standard = '$standard' AND section = '$section' AND exam = '$exam' AND academic_year = '$academic_year'
");
while ($row = $paperQry->fetch()) {
    $paperNames[] = $row['paper_name'];
}

// Step 2: Get out_of_marks for each subject
$outOfMarksList = [];
$outQry = $connect->query("
    SELECT paper_name, out_of_marks 
    FROM exam_creation 
    WHERE standard = '$standard' AND exam_type = '$exam' AND academic_year = '$academic_year'
");
while ($row = $outQry->fetch()) {
    $outOfMarksList[$row['paper_name']] = $row['out_of_marks'];
}
$examQry = $connect->query("
    SELECT exam_type
    FROM exam_type 
    WHERE id = '$exam' AND academic_year = '$academic_year'
");
while ($row1 = $examQry->fetch()) {
    $exam_name = $row1['exam_type'];
}
$stdQry = $connect->query("
    SELECT standard
    FROM standard_creation 
    WHERE standard_id = '$standard' 
");
while ($row2 = $stdQry->fetch()) {
    $standard_name = $row2['standard'];
}
// Step 3: Get student marks
$studentData = [];
$studentQry = $connect->query("
    SELECT sc.student_id, sc.admission_number, sc.student_name, sm.paper_name, sm.mark
    FROM student_mark_entry sm
    JOIN student_creation sc ON sc.student_id = sm.student_id
    WHERE sm.standard = '$standard' AND sm.section = '$section' AND sm.exam = '$exam' AND sm.academic_year = '$academic_year'
    ORDER BY sc.student_name ASC
");

while ($row = $studentQry->fetch()) {
    $sid = $row['student_id'];
    if (!isset($studentData[$sid])) {
        $studentData[$sid] = [
            'admission_number' => $row['admission_number'],
            'student_name' => $row['student_name'],
            'marks' => [],
            'total' => 0,
            'converted_total' => 0,
            'fail_count' => 0,
            'absent_count' => 0
        ];
    }

    $mark = $row['mark'];
    $paper = $row['paper_name'];

    if (is_numeric($mark) && isset($outOfMarksList[$paper]) && is_numeric($outOfMarksList[$paper]) && $outOfMarksList[$paper] > 0) {
        $converted = round(($mark / $outOfMarksList[$paper]) * 100);
        $studentData[$sid]['marks'][$paper] = $converted;
        $studentData[$sid]['total'] += $mark;
        $studentData[$sid]['converted_total'] += $converted;
        if ($converted < 35) {
            $studentData[$sid]['fail_count']++;
        }
    } else {
        if (strtoupper($mark) === 'A') {
            $studentData[$sid]['marks'][$paper] = 'A';
            $studentData[$sid]['absent_count']++;
        } else {
            $studentData[$sid]['marks'][$paper] = '-';
        }
    }
}

// Step 4: Sort by converted total marks in descending order
usort($studentData, function ($a, $b) {
    return $b['converted_total'] <=> $a['converted_total'];
});

$rank = 1;
$prevTotal = null;
$actualRank = 0;

foreach ($studentData as $index => &$stu) {
    if ($stu['fail_count'] == 0 && $stu['absent_count'] == 0) {
        $actualRank++;
        if ($stu['converted_total'] === $prevTotal) {
            $stu['rank'] = $rank;
        } else {
            $rank = $actualRank;
            $stu['rank'] = $rank;
        }
        $prevTotal = $stu['converted_total'];
    } else {
        $stu['rank'] = '';
    }
}
unset($stu);
// Final display: sort alphabetically by student name
usort($studentData, function ($a, $b) {
    return strcmp($a['student_name'], $b['student_name']);
});

// Step 5: Build HTML Table
$response['html'] .= "<div id='student_mark_export'>";
$response['html'] .= "<h4 style='text-align:center; font-weight:bold; text-transform:uppercase;'>$school_name</h4>";
$response['html'] .= "<h5 style='text-align:center; font-weight:bold;'>Exam: $exam_name | Standard: $standard_name - $section</h5><br>";
$response['html'] .= "<table class='table table-bordered' id ='student_mark_list'>
<thead style='background-color:#aad4f5;'>
<tr>
    <th>SNo</th>
    <th>Student Name</th>
    <th>Admission No</th>";

foreach ($paperNames as $paper) {
    $response['html'] .= "<th>{$paper}-(100)</th>";
}

$response['html'] .= "
    <th>Total Mark - (" . (count($paperNames) * 100) . ")</th>
    <th>Total - (100%)</th>
    <th>Rank</th>
    <th>No of sub failed</th>
    <th>Abs Sub</th>
</tr>
</thead><tbody>";

$i = 1;
foreach ($studentData as $stu) {
    $percent = count($paperNames) > 0 ? round($stu['converted_total'] / count($paperNames), 2) : 0;

    $response['html'] .= "<tr>
        <td>$i</td>
        <td>{$stu['student_name']}</td>
        <td>{$stu['admission_number']}</td>";

    foreach ($paperNames as $paper) {
        $mark = isset($stu['marks'][$paper]) ? $stu['marks'][$paper] : '-';
        if (is_numeric($mark) && $mark < 35) {
            $response['html'] .= "<td style='color:red;'><b>$mark</b></td>";
        } else {
            $response['html'] .= "<td>$mark</td>";
        }
    }

    $response['html'] .= "
        <td>{$stu['converted_total']}</td>
        <td>{$percent}%</td>
        <td>{$stu['rank']}</td>
        <td>{$stu['fail_count']}</td>
        <td>{$stu['absent_count']}</td>
    </tr>";

    $i++;
}
// Calculate average total and average percent BEFORE outputting the row
$totalSum = 0;
$studentCount = count($studentData);

foreach ($studentData as $stu) {
    $totalSum += $stu['converted_total']; // Treats absent as 0
}

$avgTotalMark = $studentCount > 0 ? round($totalSum / $studentCount) : '-';
$avgPercent = count($paperNames) > 0 && $avgTotalMark !== '-' ? round($avgTotalMark / count($paperNames), 2) . '%' : '-';

// Now print the row
$response['html'] .= "<tr>";
$response['html'] .= "<td></td><td></td><td><b>Average</b></td>"; // 3 columns

foreach ($paperNames as $paper) {
    $sum = 0;
    $count = 0;

    foreach ($studentData as $stu) {
        $mark = $stu['marks'][$paper] ?? null;
        $sum += is_numeric($mark) ? $mark : 0;
        $count++;
    }

    $avg = $count > 0 ? round($sum / $count) : '-';
    $response['html'] .= "<td><b>$avg</b></td>";
}

$response['html'] .= "<td><b>$avgTotalMark</b></td>"; // 1 column
$response['html'] .= "<td><b>$avgPercent</b></td>";   // 1 column
$response['html'] .= "<td></td><td></td><td></td>";   // Rank, Fail, Abs Sub
$response['html'] .= "</tr>";


// Subject Summary
$subjectSummary = [];
foreach ($paperNames as $paper) {
    $subjectSummary[$paper] = [
        'total' => 0, 'fail' => 0, 'pass' => 0, 'absent' => 0,
        'above80' => 0, 'above60' => 0, 'above40' => 0, 'faculty' => ''
    ];

    $getStaffQry = $connect->query("
        SELECT s.first_name, s.last_name 
        FROM staff_subject_allocation sa 
        JOIN staff_creation s ON sa.staff = s.id 
        WHERE sa.standard = '$standard' 
          AND sa.section = '$section' 
          AND sa.paper_name = '$paper' 
          AND sa.academic_year = '$academic_year'
        LIMIT 1
    ");
    if ($getStaffQry->rowCount() > 0) {
        $staffRow = $getStaffQry->fetch();
        $subjectSummary[$paper]['faculty'] = $staffRow['first_name'] . ' ' . $staffRow['last_name'];
    } else {
        $subjectSummary[$paper]['faculty'] = 'N/A';
    }
}

// Step 6: Subject Summary Initialization
$subjectSummary = [];
foreach ($paperNames as $paper) {
    $subjectSummary[$paper] = [
        'total' => 0, 'fail' => 0, 'pass' => 0, 'absent' => 0,
        'above80' => 0, 'above60' => 0, 'above40' => 0, 'faculty' => ''
    ];

    $getStaffQry = $connect->query("
        SELECT s.first_name, s.last_name 
        FROM staff_subject_allocation sa 
        JOIN staff_creation s ON sa.staff = s.id 
        WHERE sa.standard = '$standard' 
          AND sa.section = '$section' 
          AND sa.paper_name = '$paper' 
          AND sa.academic_year = '$academic_year'
        LIMIT 1
    ");
    if ($getStaffQry->rowCount() > 0) {
        $staffRow = $getStaffQry->fetch();
        $subjectSummary[$paper]['faculty'] = $staffRow['first_name'] . ' ' . $staffRow['last_name'];
    } else {
        $subjectSummary[$paper]['faculty'] = 'N/A';
    }
}

// Step 7: Define custom thresholds from total mark to 100, in steps of 50
$thresholdStep = 50;
$minThreshold = 100;
$maxTotal = count($paperNames) * 100;

$thresholdValues = [];
for ($i = $maxTotal - $thresholdStep; $i >= $minThreshold; $i -= $thresholdStep) {
    $thresholdValues[] = $i;
}

// Create threshold buckets
$thresholdCounts = [];
foreach ($thresholdValues as $value) {
    $thresholdCounts[$value] = 0; // keys: 550, 500, 450, ...
}

$allPassCount = 0;
// Loop students
foreach ($studentData as $stu) {
    $isAllPass = true;
    foreach ($paperNames as $paper) {
        $mark = $stu['marks'][$paper] ?? '-';
        $subjectSummary[$paper]['total']++;

        if ($mark === 'A') {
            $subjectSummary[$paper]['absent']++;
            $isAllPass = false;
        } elseif (is_numeric($mark)) {
            if ($mark < 35) {
                $subjectSummary[$paper]['fail']++;
                $isAllPass = false;
            } else {
                $subjectSummary[$paper]['pass']++;
            }
            if ($mark >= 80) $subjectSummary[$paper]['above80']++;
            if ($mark >= 60) $subjectSummary[$paper]['above60']++;
            if ($mark >= 40) $subjectSummary[$paper]['above40']++;
        } else {
            $isAllPass = false;
        }
    }

    if ($isAllPass) $allPassCount++;

    // Bucket count logic
    foreach ($thresholdValues as $val) {
        if ($stu['converted_total'] >= $val) {
            $thresholdCounts[$val]++;
            break;
        }
    }
}

// Subject Summary Table
$response['html'] .= "</tbody></table><br>";
$response['html'] .= "<table class='table table-bordered subject-summary'>
<thead style='background-color:#f2f2f2'>
<tr>
<th>Paper Name</th><th>Total Student</th><th>Total Student Fail</th><th>Total Student Pass</th><th>Total Student Absent</th>
<th>Total Student Pass %</th><th>Above 80 +</th><th>Above 60 +</th><th>Above 40 +</th><th>Name of the Faculty</th><th>Sign</th>
</tr></thead><tbody>";

foreach ($subjectSummary as $paper => $data) {
    $passPercent = $data['total'] > 0 ? round(($data['pass'] / $data['total']) * 100) : 0;
    $response['html'] .= "<tr>
        <td>$paper</td><td>{$data['total']}</td><td>{$data['fail']}</td><td>{$data['pass']}</td>
        <td>{$data['absent']}</td><td>{$passPercent}%</td><td>{$data['above80']}</td>
        <td>{$data['above60']}</td><td>{$data['above40']}</td><td>{$data['faculty']}</td><td></td>
    </tr>";
}

// Final Summary Table
$totalStudents = count($studentData);
$passPercent = $totalStudents > 0 ? round(($allPassCount / $totalStudents) * 100, 2) . '%' : '0%';

$response['html'] .= "</tbody></table><br>";
$response['html'] .= "<table class='table table-bordered final-summary'><tbody>";
$response['html'] .= "<tr><td><b>All Pass Student</b></td><td>$allPassCount</td>";

$counter = 0;
foreach ($thresholdCounts as $value => $count) {
    if ($counter % 2 == 0 && $counter != 0) {
        $response['html'] .= "</tr><tr><td></td><td></td>";
    }
    $label = "Above $value";
    $response['html'] .= "<td><b>$label</b></td><td>$count</td>";
    $counter++;
}
$response['html'] .= "</tr>";

$response['html'] .= "<tr><td><b>All Pass Student %</b></td><td>$passPercent</td><td colspan='6'></td></tr>
<tr><td><b>Class Incharge</b></td><td colspan='7'></td></tr>
<tr><td><b>Administrator</b></td><td colspan='7'></td></tr>
<tr><td><b>Correspondent</b></td><td colspan='7'></td></tr>
</tbody></table>";

$response['html'] .= "</div>";
echo json_encode($response);