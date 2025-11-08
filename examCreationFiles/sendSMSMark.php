<?php
include '../ajaxconfig.php';
@session_start();
$user_id = $_SESSION["userid"];

$response = ['status' => 'error', 'message' => 'Failed to send messages'];

if (isset($_SESSION["academic_year"])) {
    $academic_year = $_SESSION["academic_year"];
}

$standard = $_POST['standard'];
$exam = $_POST['exam'];
$section = $_POST['section'];
$selectedStudents = $_POST['selectedStudents'];

$exam_name = '';
$examQry = $connect->query("
    SELECT exam_type
    FROM exam_type 
    WHERE id = '$exam' AND academic_year = '$academic_year'
");
if ($row1 = $examQry->fetch()) {
    $exm_name = $row1['exam_type'];
}
$apiKey = 'ffacb4a6f0caed0ddfc9bdb371ce4d09';
$sender = 'VPHSSS';
$allSuccess = true;
$exam_name = preg_replace('/\s*-\s*/', ' ', $exm_name);
foreach ($selectedStudents as $students) {
    $student_id = $students['student_id'];
    $student_name = $students['student_name'];
    $smsNo = $students['smsNo'];
    $marks = $students['marks'];
    $total = $students['total'];
    $sms = '';
    $templateid = '';

    if ($standard >= 9 && $standard <= 13) { //from  std VI to X
        // Dear Parents, {#var#}{#var#} Marks is Tamil {#var#} English {#var#} Maths {#var#} Science {#var#} Social {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tamil {$marks['Tamil']} English {$marks['English']} Maths {$marks['Maths']} Science {$marks['Science']} Social {$marks['Social Science']} Total {$total} VPHSSS";
        $templateid = '1707163237858078334';
    } elseif (in_array($standard, [14, 19])) { // Maths Biology (XI, XII)
        // Dear Parents, {#var#}{#var#} Marks is Tam {#var#} Eng {#var#} Maths {#var#} Phy {#var#} Chem {#var#} Bio {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tam {$marks['Tamil']} Eng {$marks['English']} Maths {$marks['Maths']} Phy {$marks['Physics']} Chem {$marks['Chemistry']} Bio {$marks['Biology']} Total {$total} VPHSSS";
        $templateid = '1707163220456858756';
    } elseif (in_array($standard, [15, 20])) { // Maths Computer Science  (XI, XII)
        // Dear Parents, {#var#}{#var#} Marks is Tam {#var#} Eng {#var#} Maths {#var#} Phy {#var#} Chem {#var#} Com.Sci {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tam {$marks['Tamil']} Eng {$marks['English']} Maths {$marks['Maths']} Phy {$marks['Physics']} Chem {$marks['Chemistry']} Com.Sci {$marks['Computer Science']} Total {$total} VPHSSS";
        $templateid = '1707163220452115038';
    } elseif (in_array($standard, [16, 21])) { // Biology Computer Science  (XI, XII)
        // Dear Parents, {#var#}{#var#} Marks is Tam {#var#} Eng {#var#} Phy {#var#} Chem {#var#} Bio {#var#} Com.Sci {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tam {$marks['Tamil']} Eng {$marks['English']} Phy {$marks['Physics']} Chem {$marks['Chemistry']} Bio {$marks['Biology']} Com.Sci {$marks['Computer Science']} Total {$total} VPHSSS";
        $templateid = '1707163220444609639';
    } elseif (in_array($standard, [17, 22])) { // Commerce Computer Science  (XI, XII)
        // Dear Parents, {#var#}{#var#} Marks is Tam {#var#} Eng {#var#} Eco {#var#} Acc {#var#} Comm {#var#} Com.App. {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tam {$marks['Tamil']} Eng {$marks['English']} Eco {$marks['Economics']} Acc {$marks['Accotancy']} Comm {$marks['Commerce']} Com.App. {$marks['Computer Application']} Total {$total} VPHSSS";
        $templateid = '1707163220438035490';
    } elseif (in_array($standard, [24, 25])) { // Commerce Business Mathematics  (XI, XII)
        // Dear Parents, {#var#}{#var#} Marks is Tam {#var#} Eng {#var#} Eco {#var#} Acc {#var#} Comm {#var#} Buss.Mat {#var#} Total {#var#} VPHSSS
        $sms = "Dear Parents, {$student_name} {$exam_name} Marks is Tam {$marks['Tamil']} Eng {$marks['English']} Eco {$marks['Economics']} Acc {$marks['Accotancy']} Comm {$marks['Commerce']} Buss.Mat {$marks['Business Maths']} Total {$total} VPHSSS";
        $templateid = '1707163220433675167';
    }

    if ($sms != '') {
        $recipients = urlencode($smsNo);
        $sms_encoded = urlencode($sms);
        $url = "http://smartconnect.co3.live/api/smsapi?key={$apiKey}&route=2&sender={$sender}&number={$recipients}&templateid={$templateid}&sms={$sms_encoded}";
        $smsResponse = file_get_contents($url);
        $lines = explode("\n", trim($smsResponse));
        $messageID = isset($lines[0]) ? explode(':', $lines[0])[0] : '';
        $delivery_status = (!empty($messageID) && is_numeric($messageID)) ? 'Success' : 'Undelivered';
        if ($delivery_status != 'Success') {
            $allSuccess = false;
        }

        // Insert SMS report
        $sms_date = date("Y-m-d");
        $message_type = "ExamSMS";
        $sms_escaped = $sms;

        $insertQry = "
            INSERT INTO sms_delivery 
            (student_id, student_name, standard, section, exam_name, message_type, sms_date, status, sms_message, message_id, academic_year,insert_login_id,created_on)
            VALUES 
            ('$student_id', '$student_name', '$standard', '$section', '$exam', '$message_type', '$sms_date', '$delivery_status', '$sms_escaped', '$messageID', '$academic_year','$user_id',now())
        ";
        $connect->query($insertQry);
    }
}

if ($allSuccess) {
    $response = ['status' => 'success', 'message' => 'Exam marks sent successfully'];
}

echo json_encode($response);
