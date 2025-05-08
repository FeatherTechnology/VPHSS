<?php
include '../ajaxconfig.php';

$standard = $_POST['standard'];

$qry = $connect->query("SELECT et.exam_type, ec.paper_name, ec.out_of_marks, ec.pass 
                        FROM exam_creation ec 
                        JOIN exam_type et ON ec.exam_type = et.id 
                        WHERE ec.standard = '$standard' 
                        ORDER BY et.exam_type, ec.paper_name");

$data = [];
$subjects = [];
$exam_types = [];

// Collect data grouped by exam_type and paper_name
while ($row = $qry->fetch()) {
    $exam_type = $row['exam_type']; // Use exam_type as exam row
    $subject = $row['paper_name'];
    $out_of = $row['out_of_marks'];
    $pass = $row['pass'];

    $subjects[$subject] = true;
    $data[$exam_type][$subject] = ['out_of' => $out_of, 'pass' => $pass];
    $exam_types[$exam_type] = true;
}

// Begin building HTML table
$html = '<table class="table table-bordered" id="report_info_table">
            <thead>
                <tr>
                    <th>Exam/Paper</th>';

// Table headers for subjects (2 columns per subject)
foreach (array_keys($subjects) as $subject) {
    $html .= '<th colspan="2">' . htmlspecialchars($subject) . '</th>';
}

$html .= '</tr><tr><th></th>';

// Add sub-headers: Out of Marks and Pass %
foreach (array_keys($subjects) as $subject) {
    $html .= '<th>Out of Marks</th><th>Pass %</th>';
}

$html .= '</tr></thead><tbody>';

// Rows for each exam type (e.g., Mid Term, Final Exam)
foreach (array_keys($exam_types) as $exam_type) {
    $html .= '<tr><td>' . htmlspecialchars($exam_type) . '</td>';
    foreach (array_keys($subjects) as $subject) {
        if (isset($data[$exam_type][$subject])) {
            $out = $data[$exam_type][$subject]['out_of'];
            $pass = $data[$exam_type][$subject]['pass'];
        } else {
            $out = $pass = '-';
        }
        $html .= "<td>$out</td><td>$pass</td>";
    }
    $html .= '</tr>';
}

// Grand Total row
$html .= '<tr><td><strong>Total</strong></td>';
foreach (array_keys($subjects) as $subject) {
    $total_out = $total_pass = 0;
    foreach (array_keys($exam_types) as $exam_type) {
        if (isset($data[$exam_type][$subject])) {
            $total_out += (int)$data[$exam_type][$subject]['out_of'];
            $total_pass += (int)$data[$exam_type][$subject]['pass'];
        }
    }
    $html .= "<td><strong>$total_out</strong></td><td><strong>$total_pass</strong></td>";
}
$html .= '</tr>';

$html .= '</tbody></table>';

echo $html;
?>
