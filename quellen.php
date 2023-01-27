<?php
if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3)) {
	header('Location: '.$absoluteUrl);
}
include 'mainCall.php';
// herkunft selectbox options
$get_data = callAPI('GET', $baseApiURL.'herkunft/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		echo $response['message'];
		die();
		break;
	case 2:
		$herkunfte = $response['content']['data'];
		break;
	default:
		break;
}

// Schema selectbox options

$get_data = callAPI('GET', $baseApiURL.'quelle/quelle-schemas', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		echo $response['message'];
		die();
		break;
	case 2:
		$schemas = $response['content']['data'];
		break;
	default:
		break;
}

// verlage selectbox
$get_data = callAPI('GET', $baseApiURL.'verlage/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		echo $response['message'];
		die();
		break;
	case 2:
		$verlage = $response['content']['data'];
		break;
	default:
		break;
}
// Autor selectbox 
$get_data = callAPI('GET', $baseApiURL.'autor/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		echo $response['message'];
		die();
		break;
	case 2:
		$autorenSelectBox = $response['content']['data'];
		break;
	default:
		break;
}


// get a single quelle

if(isset($_GET['quelle_id'])) {
	$quelle_id = $_GET['quelle_id'];
	$quelleEditUrl = $baseApiURL.'quelle/view?quelle_id='.$quelle_id;
	$get_data = callAPI('GET',$quelleEditUrl , false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			echo $response['message'];
			die();
			break;
		case 2:
			$quellen = $response['content']['data'];
			$code = $quellen['code'];
			$titel = $quellen['titel'];
			$sprache = $quellen['sprache'];
			$herkunft_id = $quellen['herkunft_id'];
			$quelle_schema_id = $quellen['quelle_schema_id'];
			$jahr = $quellen['jahr'];
			$band = $quellen['band'];
			$nummer = $quellen['nummer'];
			$auflage = $quellen['auflage'];
			$verlag_id = $quellen['verlag_id'];
			$autor_id_selected_values = [];
			$autoren_selected = $quellen['autoren'];
			foreach ($autoren_selected as $key => $autor_selected) {
				$autor_id_selected_values[] = $autor_selected['autor_id'];
			}
			$file_url = $quellen['file_url'];
			print_r($autor_id_selected_values);
			die();
			break;
		default:
			break;
	}																
}

// add and edit a quelle

if(isset($_POST['Speichern']) || isset($_POST['ÄnderungenSpeichern'])) {
	$code = isset($_POST['code']) ? $_POST['code'] : '';
	$titel = isset($_POST['titel']) ? $_POST['titel'] : '';
	$sprache = isset($_POST['sprache']) ? $_POST['sprache'] : '';
	$herkunft_id = isset($_POST['herkunft_id']) ? $_POST['herkunft_id'] : '';
	$quelle_schema_id = isset($_POST['quelle_schema_id']) ? $_POST['quelle_schema_id'] : '';
	$jahr = isset($_POST['jahr']) ? $_POST['jahr'] : '';
	$band = isset($_POST['band']) ? $_POST['band'] : '';
	$nummer = isset($_POST['nummer']) ? $_POST['nummer'] : '';
	$auflage = isset($_POST['auflage']) ? $_POST['auflage'] : '';
	$verlag_id = isset($_POST['verlag_id']) ? $_POST['verlag_id'] : '';
	$autor_id = isset($_POST['autor_id']) ? $_POST['autor_id'] : '';

	//$tmpfile = $_FILES['file_url']['tmp_name'];
	//$filename = basename($_FILES['file_url']['name']);
	//$handle    = fopen($tmpfile, "r");
 	//$data      = fread($handle, filesize($tmpfile));

	//die();
	//$filename = $_FILES['file_url']['name'];
	//print_r($_FILES['file_url']);
	//$file_url = curl_file_create( $tmpfile, mime_content_type($tmpfile), $filename);
	$data_array =  array(
		"code"      		=> $code,
		"titel"     		=> $titel,
		"sprache"  			=> $sprache,
		"herkunft_id"   	=> $herkunft_id,
		"quelle_schema_id"  => $quelle_schema_id,
		"jahr"				=> $jahr,
		"band" 				=> $band,
		"nummer" 			=> $nummer,
		"auflage" 			=> $auflage,
		"verlag_id" 		=> $verlag_id,
		"autor_id" 			=> $autor_id,
		"file_url" 			=> ''
	);


	// add quelle

	if(isset($_POST['Speichern'])) {
		//print_r(json_encode($data_array));
		$get_data = callAPI('POST', $baseApiURL.'quelle/add', json_encode($data_array));
		//print_r($get_data);
		//die();
	}
	// edit quelle

	if(isset($_POST['ÄnderungenSpeichern'])) {
		$quelle_id = $_POST['quelle_id'];
		$get_data = callAPI('POST',  $baseApiURL.'quelle/update?quelle_id='.$quelle_id, json_encode($data_array));
	}
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			echo $response['message'];
			die();
			break;
		case 2:
			$code = '';
			$titel = '';
			$sprache = '';
			$herkunft_id = '';
			$quelle_schema_id = '';
			$jahr = '';
			$band = '';
			$nummer = '';
			$auflage = '';
			$verlag_id = '';
			$autor_id = '';
			$file_url = '';
			$_SESSION['success'] = $response['message'];
			$quellen = $response['content']['data'];
			$autoren = $quellen['autoren'];
			foreach ($autoren as $key => $autor) {
				$autor_id_selected_values[] = $autor['autor_id'];
			}
			//header('Location: '.$absoluteUrl. 'stammdaten/quellen/index.php');
			break;	
		case 3:
			$_SESSION['validationError'] = $response['message'];
			break;
		default:
			//header('Location: '.$absoluteUrl. 'stammdaten/quellen/index.php');
			break;
	}
}
