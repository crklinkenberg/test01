<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
$get_data = '';
$response = [];
$herkunft = [];

// get a single herkunft

if(isset($_GET['herkunft_id'])) {
	$herkunft_id = $_GET['herkunft_id'];
	$url = $baseApiURL.'herkunft/view?herkunft_id='.$herkunft_id;
	$get_data = callAPI('GET',$url , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$herkunft = $response['content']['data'];
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
}