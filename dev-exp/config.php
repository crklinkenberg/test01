<?php
//DB details
$dbHost = 'localhost';
$dbUsername = 'symrepe';
$dbPassword = 'HOax&1990';
// $dbName = 'alegra_new_repertory_temp';
$dbName = 'alegra_new_repertory_development';

//Create connection and select DB
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($db->connect_error) {
    die("Unable to connect database: " . $db->connect_error);
}

// Change character set to utf8
mysqli_set_charset($db,"utf8");
mb_internal_encoding("UTF-8");

$baseUrl = 'http://www.newrepertory.com/dev-exp/';