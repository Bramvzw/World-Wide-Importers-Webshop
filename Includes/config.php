<?php
session_start();
// set registratie aan of uit
DEFINE('REGISTER_ALLOWED', true);

define('ROOT_DIR', realpath(__DIR__.'/..'));
DEFINE ('DB_HOST', '127.0.0.1');
DEFINE ('DB_DATABASE', 'wideworldimporters');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function newDBConn($user) {
    $DBUsers = [
        "admin" => "wwiadmin",
        "productview" => "wwiproductview",
        "login" => "wwilogin",
        "klantedit" => "wwiklantedit",
        "orderview" => "wwiorderview",
        "orderedit" => "wwiorderedit",
        "stockedit" => "wwistockedit"
    ];
    $DBConnection = mysqli_connect(DB_HOST, $user, $DBUsers[$user], DB_DATABASE);
    if(!$DBConnection) die("Unable to connect to MySQL: " . mysqli_error($DBConnection));
    return $DBConnection;
}

// als de sessie voor winkelwagen niet bestaat, maak een lege winkelwagen aan. Dit is om errors te voorkomen.
if(!isset($_SESSION["Winkelwagen"])) {
    $_SESSION["Winkelwagen"] = array();
}

// oproepen  PHPMAiler dingen
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/phpmailer/Exception.php';
require 'includes/phpmailer/PHPMailer.php';
require 'includes/phpmailer/SMTP.php';

// sendmail functie.
define('GUSER', "ictm1e52019@gmail.com");
define('GPWD', "!g5X8P^odMsH");

function smtpmailer($to, $subject, $body) {
    global $error;
    $mail = new PHPMailer();  // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true;  // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 465;
    $mail->Username = GUSER;
    $mail->Password = GPWD;
    $mail->SetFrom(GUSER, "WideWorldImporters Webshop");
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $body;
    $mail->AddAddress($to);
    if(!$mail->Send()) {
        $error = 'Mail error: '.$mail->ErrorInfo;
        return false;
    } else {
        $error = 'Message sent!';
        return true;
    }
}