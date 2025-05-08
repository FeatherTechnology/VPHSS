<?php
include '../ajaxconfig.php';

$section = $_POST['section'];
$standard = $_POST['standard'];

$response = [];
$staffList = [];

// Fetch all staff
$staffQry = $connect->query("SELECT id, CONCAT(first_name, ' ', last_name) AS staff_name FROM staff_creation WHERE designation ='Teaching' AND status = 0 ");
if ($staffQry->rowCount() > 0) {
    while ($row = $staffQry->fetch()) {
        $staffList[] = [
            'id' => $row['id'],
            'staff_name' => $row['staff_name']
        ];
    }
}

// Fetch all subjects
$subjectQry = $connect->query("SELECT paper_name FROM subject_details WHERE class_id = '$standard'");
if ($subjectQry->rowCount() > 0) {
    while ($row = $subjectQry->fetch()) {
        $response[$row['paper_name']] = [
            'paper_name' => $row['paper_name'],
            'staff_name' => '',
            'staff_id' => '',
            'is_allocated' => false
        ];
    }
}

// Fetch already allocated staff
$examQry = $connect->query("
    SELECT sta.paper_name, sta.staff, CONCAT(sc.first_name, ' ', sc.last_name) AS staff_name 
    FROM staff_subject_allocation sta  
    JOIN staff_creation sc ON sta.staff = sc.id 
    WHERE sta.standard = '$standard' AND sta.section = '$section'
");

if ($examQry->rowCount() > 0) {
    while ($row = $examQry->fetch()) {
        $paper = $row['paper_name'];
        if (isset($response[$paper])) {
            $response[$paper]['staff_name'] = $row['staff_name'];
            $response[$paper]['staff_id'] = $row['staff'];
            $response[$paper]['is_allocated'] = true;
        }
    }
}

echo json_encode([
    'subjects' => array_values($response),
    'staff_list' => $staffList
]);

?>
