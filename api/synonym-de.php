<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
$get_data = '';
$response = [];
$synonymDe = [];

// get a single synonymDe

if(isset($_GET['synonym_id'])) {
	$synonym_id = $_GET['synonym_id'];
	$url = $baseApiURL.'synonym-de/view?synonym_id='.$synonym_id;
	$get_data = callAPI('GET',$url , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$synonymDe = $response['content']['data'];
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