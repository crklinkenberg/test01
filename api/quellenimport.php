<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
// selectbox
$quellenSelectBox = [];
$get_data = callAPI('GET', $baseApiURL.'quelle/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$quellenSelectBox = $response['content']['data'];
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

// arznei selectbox 
$arzneiSelectBox = [];
$get_data = callAPI('GET', $baseApiURL.'arznei/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');;
		break;
	case 2:
		$arzneiSelectBox = $response['content']['data'];
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

// arznei selectbox 
$prueferSelectBox = [];
$get_data = callAPI('GET', $baseApiURL.'pruefer/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');;
		break;
	case 2:
		$prueferSelectBox = $response['content']['data'];
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

if(isset($_GET['arznei_id'])) {
	$arznei_id = $_GET['arznei_id'];
	$url = $baseApiURL.'arznei/view?arznei_id='.$arznei_id;
	$get_data = callAPI('GET',$url , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$arzneien = $response['content']['data'];
			$autoren_selected = $arzneien['autoren'];
			$autor_id_selected_values = [];
			foreach ($autoren_selected as $key => $autor_selected) {
				$autor_id_selected_values[] = $autor_selected['autor_id'];
			}
			$quelle_id_selected_values = [];
			$quellen_selected = $arzneien['quelle'];
			foreach ($quellen_selected as $key => $quelle_selected) {
				$quelle_id_selected_values[] = $quelle_selected['quelle_id'];
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