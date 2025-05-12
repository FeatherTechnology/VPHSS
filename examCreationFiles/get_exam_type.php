<?php
include '../ajaxconfig.php';
@session_start();
if(isset($_SESSION["academic_year"])){
    $academic_year = $_SESSION["academic_year"];
} 
$exam_arr = array();
$qry = $connect->query("SELECT id,exam_type FROM exam_type WHERE academic_year = '$academic_year'");
if ($qry->rowCount() > 0) {
    while ($exam_info = $qry->fetch(PDO::FETCH_ASSOC)) {
        $exam_info['action'] = "<span class='icon-border_color examActionBtn' value='" . $exam_info['id'] . "'></span>  <span class='icon-trash-2 examDeleteBtn' value='" . $exam_info['id'] . "'></span>";
        $exam_arr[] = $exam_info;
    }
}

$connect = null; //Connection Close.

echo json_encode($exam_arr);
