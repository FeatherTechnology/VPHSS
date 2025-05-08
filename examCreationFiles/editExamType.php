<?php
include '../ajaxconfig.php';

$id = $_POST['id'];

$qry = $connect->query("SELECT * FROM `exam_type` WHERE id='$id'");
if ($qry->rowCount() > 0) {
    $result = $qry->fetchAll(PDO::FETCH_ASSOC);
}
$connect = null; //Close connection.

echo json_encode($result);
