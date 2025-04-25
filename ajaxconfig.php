<?php
$timeZoneQry = "SET time_zone = '+5:30' ";

$mysqli =mysqli_connect("mysql5050.site4now.net", "9fc19e_hsslive", "School@123", "db_9fc19e_hsslive") or die("Error in database connection".mysqli_error($mysqli));
mysqli_set_charset($mysqli, "utf8");
$mysqli->query($timeZoneQry);

$host = "mysql5050.site4now.net";  
$db_user = "9fc19e_hsslive";  
$db_pass = "School@123";  
$dbname = "db_9fc19e_hsslive";  

$connect = new PDO("mysql:host=$host; dbname=$dbname", $db_user, $db_pass); 
$connect->exec($timeZoneQry);
?>