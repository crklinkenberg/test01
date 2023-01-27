<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
$get_data = '';
$response = [];
$zeitschriften = [];

// herkunft selectbox options
$herkunfte= [];
$get_data = callAPI('GET', $baseApiURL.'herkunft/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$herkunfte = $response['content']['data'];
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

// Autor selectbox 
$autorenSelectBox = [];

$get_data = callAPI('GET', $baseApiURL.'autor/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$autorenSelectBox = $response['content']['data'];
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


// get a single quelle

if(isset($_GET['zeitschrift_id'])) {
	$zeitschrift_id = $_GET['zeitschrift_id'];
	$url = $baseApiURL.'zeitschrift/view?quelle_id='.$zeitschrift_id;
	$get_data = callAPI('GET',$url , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$zeitschriften = $response['content']['data'];
			$autoren_selected = $zeitschriften['autoren'];
			$autor_id_selected_values = [];
			foreach ($autoren_selected as $key => $autor_selected) {
				$autor_id_selected_values[] = $autor_selected['autor_id'];
			}
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