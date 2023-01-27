<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
// echo "Hemmo"; exit;
include 'mainCall.php';
$get_data = '';
$response = [];
// herkunft selectbox options
$herkunfte = [];
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

// Schema selectbox options
$schemas = [];
$get_data = callAPI('GET', $baseApiURL.'quelle/quelle-schemas', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$schemas = $response['content']['data'];
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

// verlage selectbox
$verlage = [];
$get_data = callAPI('GET', $baseApiURL.'verlage/all?is_paginate=0', false);
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

// Pruefer selectbox 
$prueferSelectBox = [];
$get_data = callAPI('GET', $baseApiURL.'pruefer/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
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

// Geting all Quelle And Zeitschriftselectbox 
$allQuelleZeitschriftSelectBox = [];
$get_data = callAPI('GET', $baseApiURL.'quelle/all-quelle-zeitschrift?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$allQuelleZeitschriftSelectBox = $response['content']['data'];
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
$quellen = [];
if(isset($_GET['quelle_id'])) {
	$quelle_id = $_GET['quelle_id'];
	$quelleEditUrl = $baseApiURL.'quelle/view?quelle_id='.$quelle_id;
	$get_data = callAPI('GET',$quelleEditUrl , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$quellen = $response['content']['data'];
			$autor_id_selected_values = [];
			$pruefer_id_selected_values = [];
			$autoren_selected = $quellen['autoren'];
			foreach ($autoren_selected as $key => $autor_selected) {
				$autor_id_selected_values[] = $autor_selected['autor_id'];
			}
			foreach ($quellen['pruefer'] as $prufKey => $prufVal) {
				$pruefer_id_selected_values[] = $prufVal['pruefer_id'];
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
