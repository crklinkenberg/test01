<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the comment or Foot note of a particular symptom 
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		$symptomId = (isset($_POST['symptom_id']) AND $_POST['symptom_id'] != "") ? $_POST['symptom_id'] : "";
		$connectedSymptomId = (isset($_POST['connected_symptom_id']) AND $_POST['connected_symptom_id'] != "") ? $_POST['connected_symptom_id'] : "";
		$comparisonLanguage = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$matchedPercentage = (isset($_POST['matched_percentage']) AND $_POST['matched_percentage'] != "") ? $_POST['matched_percentage'] : "";
		$quelleId = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? $_POST['quelle_id'] : "";
		$quelleIdConnected = (isset($_POST['quelle_id_connected']) AND $_POST['quelle_id_connected'] != "") ? $_POST['quelle_id_connected'] : "";
		$symptomText = (isset($_POST['symptom_text']) AND $_POST['symptom_text'] != "") ? $_POST['symptom_text'] : "";
		$connectedSymptomText = (isset($_POST['connected_symptom_text']) AND $_POST['connected_symptom_text'] != "") ? $_POST['connected_symptom_text'] : "";
		$quelleCode = (isset($_POST['quelle_code']) AND $_POST['quelle_code'] != "") ? $_POST['quelle_code'] : "";
		$quelleCodeConnected = (isset($_POST['quelle_code_connected']) AND $_POST['quelle_code_connected'] != "") ? $_POST['quelle_code_connected'] : "";
		$connectionsTableName ="symptom_connections_new";
		$connectionType = "connect";
		if($comparisonLanguage == "de"){
			$symptomTextDe = $symptomText;
			$connectedSymptomTextDe = $connectedSymptomText;
			$symptomTextEn = "";
			$connectedSymptomTextEn = "";
		}
		else{
			$symptomTextDe = "";
			$connectedSymptomTextDe = "";
			$symptomTextEn = $symptomText;
			$connectedSymptomTextEn = $connectedSymptomText;
		}

		if($symptomId != ""){
			$symptomConnectionInsertQuery="INSERT INTO $connectionsTableName (symptom_id, connected_symptom_id, connection_type, matched_percentage, quelle_id, quelle_id_connected, symptom_text_en, symptom_text_de, connected_symptom_text_en, connected_symptom_text_de, comparison_language, quelle_code, quelle_code_connected) VALUES ($symptomId, $connectedSymptomId, NULLIF('".$connectionType."', ''), $matchedPercentage, $quelleId, $quelleIdConnected, NULLIF('".$symptomTextEn."', ''), NULLIF('".$symptomTextDe."', ''), NULLIF('".$connectedSymptomTextEn."', ''), NULLIF('".$connectedSymptomTextDe."', ''), '".$comparisonLanguage."', '".$quelleCode."', '".$quelleCodeConnected."')";
			$db->query($symptomConnectionInsertQuery);
			$data= array(
				'symptom ID'=>$symptomId,
				'connected_symptom_id'=> $connectedSymptomId,
				'comparison_language'=> $comparisonLanguage,
				'matched_percentage' =>$matchedPercentage,
				'quelle_id'=>$quelleId,
				'quelle_id_connected'=>$quelleIdConnected,
				'symptom_text'=>$symptomText,
				'connected_symptom_text'=>$connectedSymptomText,
				'quelle_code'=>$quelleCode,
				'quelle_code_connected'=>$quelleCodeConnected
			);
			$resultData = $data;
			$status = "success";
			$message = "success";
		}	

	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message,'debug'=>$db,'symptom'=>$symptomId) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>