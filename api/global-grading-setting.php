<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
$get_data = '';
$response = [];

// Geting all Quelle And Zeitschriftselectbox 
$allGlobalGradingSets = [];
$get_data = callAPI('GET', $baseApiURL.'global-grading-set/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$allGlobalGradingSets = $response['content']['data'];
		break;
	case 3:
		$error = $response['message'];
		break;
	case 4:
		$error = $response['message'];
		break;
	case 5:
		$error = $response['message'];
		break;
	case 6:
		$error = $response['message'];
		break;
	default:
		break;
}
