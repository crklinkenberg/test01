<?php
// server should keep session data for AT LEAST 2 hour
ini_set('session.gc_maxlifetime', 14400);
// each client should remember their session id for EXACTLY 2 hour
session_set_cookie_params(14400);
session_start();
// $now = time();
// if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
//     // this session has worn out its welcome; kill it and start a brand new one
//     session_unset();
//     session_destroy();
//     session_start();
// }

// // either new or old, it should live at most for another hour
// $_SESSION['discard_after'] = $now + 3600;
// $maxlifetime = ini_get("session.gc_maxlifetime");
// echo $maxlifetime." --- HR --- "; exit;
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
ini_set('max_input_vars', 10000);
// Central European timezone
date_default_timezone_set('Europe/Vienna');
// $maxlifetime = ini_get("session.gc_maxlifetime");
// echo $maxlifetime." --- HR --- "; exit;
// Site domain
$absoluteUrl = 'http://com.newrepertory.com/';
//$absoluteUrl = 'http://repertorium.loc/';

// full url of a page

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// api base url
$baseApiURL = 'http://com.newrepertory.com/symcom/api/public/v1/';

// DB Config details
$dbHost = 'localhost';
$dbUsername = 'symrepe';
$dbPassword = 'HOax&1990';
$dbName = 'development_repertory';

//Create connection and select DB
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($db->connect_error) {
    die("Unable to connect database: " . $db->connect_error);
}

// Change character set to utf8
mysqli_set_charset($db,"utf8");
mb_internal_encoding("UTF-8");


$date = date("Y-m-d H:i:s"); 
?>