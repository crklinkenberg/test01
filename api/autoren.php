<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
$autoren = [];
$response = [];
$autorTitels = [];

// get titels

$autorTitelsUrl = $baseApiURL.'autor/titels';
$get_data = callAPI('GET',$autorTitelsUrl , false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$autorTitels = $response['content']['data'];
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

if(isset($_GET['autor_id'])) {
	$autor_id = $_GET['autor_id'];
	$autorEditUrl = $baseApiURL.'autor/view?autor_id='.$autor_id;
	$get_data = callAPI('GET',$autorEditUrl , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$autoren = $response['content']['data'];
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

