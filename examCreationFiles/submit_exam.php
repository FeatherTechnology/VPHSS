<?php
include '../ajaxconfig.php';
@session_start();
$user_id =  $_SESSION["userid"];
$exam_type = $_POST['exam_type'];
$id = $_POST['id'];
if(isset($_SESSION["academic_year"])){
    $academic_year = $_SESSION["academic_year"];
} 

$qry = $connect->query("SELECT * FROM `exam_type` WHERE REPLACE(TRIM(exam_type), ' ', '') = REPLACE(TRIM('$exam_type'), ' ', '') AND academic_year = '$academic_year'");
if ($qry->rowCount() > 0) {
    $result = 0; //already Exists.

} else {
    if ($id != '0' && $id != '') {
        $connect->query("UPDATE `exam_type` SET `exam_type`='$exam_type',academic_year = '$academic_year',`update_login_id`='$user_id',`updated_on`=now() WHERE `id`='$id' ");
        $result = 1; //update

    } else {
        $connect->query("INSERT INTO `exam_type`(`exam_type`,academic_year, `insert_login_id`, `created_on`) VALUES ('$exam_type','$academic_year','$user_id', now())");
        $result = 2; //Insert
    }
}

echo json_encode($result);
