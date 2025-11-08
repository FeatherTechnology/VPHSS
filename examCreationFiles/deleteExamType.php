<?php
include '../ajaxconfig.php';

$id = $_POST['id'];

try {
    $qry = $connect->prepare("DELETE FROM `exam_type` WHERE id = :id");
    $qry->bindParam(':id', $id, PDO::PARAM_INT);
    $qry->execute();
    $result = 1; // Deleted.
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        // Integrity constraint violation
        $result = 0; // Already used in Loan Category Creation Table.
    } else {
        // Some other error occurred
        $result = -1; // Indicate a general error.
    }
}

echo json_encode($result);
