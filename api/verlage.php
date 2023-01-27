<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';

$get_data = '';
$response = [];
// land select list
$lands  = [];
$get_data = callAPI('GET', $baseApiURL.'verlage/land' , false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$lands = $response['content']['data'];
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

// get a single verlage
$verlage = [];

if(isset($_GET['verlag_id'])) {
	$verlag_id = $_GET['verlag_id'];
	$url = $baseApiURL.'verlage/view?verlag_id='.$verlag_id;
	$get_data = callAPI('GET',$url , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$verlage = $response['content']['data'];
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