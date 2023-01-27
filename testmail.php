<?php
/*
 * Enable error reporting
 */
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
// if ( function_exists( 'mail' ) )
// {
//     echo 'mail() is available';
// }
// else
// {
//     echo 'mail() has been disabled';
// }

$to = "hemantapro@gmail.com";
$subject = "AWS Newrepertory Test mail";
$message = "Hello! This is a simple email message.";
// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <jobsdone32@gmail.com>' . "\r\n";
$headers .= 'Cc: jay3000bc@gmail.com' . "\r\n";
if(mail($to,$subject,$message,$headers))
	echo "Mail Sent.";
else
	echo "Mail not sent";
?>