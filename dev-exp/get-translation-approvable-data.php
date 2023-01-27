<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		$add_translation_master_id = (isset($_POST['add_translation_master_id']) AND $_POST['add_translation_master_id'] != "") ? $_POST['add_translation_master_id'] : "";
		$add_translation_arznei_id = (isset($_POST['add_translation_arznei_id']) AND $_POST['add_translation_arznei_id'] != "") ? $_POST['add_translation_arznei_id'] : "";
		$add_translation_quelle_id = (isset($_POST['add_translation_quelle_id']) AND $_POST['add_translation_quelle_id'] != "") ? $_POST['add_translation_quelle_id'] : "";
		$add_translation_language = (isset($_POST['add_translation_language']) AND $_POST['add_translation_language'] != "") ? $_POST['add_translation_language'] : "";

		if($add_translation_master_id == "" OR $add_translation_arznei_id == "" OR $add_translation_quelle_id == "" OR $add_translation_language == ""){
			$status = 'error';
    		$message = 'Some required data not found. Please reload the page and try again!';
			echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
			exit;
		} else {
			$tempTransQuelleQuery = mysqli_query($db,"SELECT translation_language, translation_method FROM temp_translation_quelle WHERE master_id = '".$add_translation_master_id."' AND quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."'");
			if(mysqli_num_rows($tempTransQuelleQuery) > 0)
				$tempTransQuelleData = mysqli_fetch_assoc($tempTransQuelleQuery);
			$translation_language = (isset($tempTransQuelleData['translation_language']) AND $tempTransQuelleData['translation_language'] != "") ? $tempTransQuelleData['translation_language'] : "";
			$translation_method = (isset($tempTransQuelleData['translation_method']) AND $tempTransQuelleData['translation_method'] != "") ? $tempTransQuelleData['translation_method'] : "";

			if($translation_language == $add_translation_language AND $translation_method != ""){
				if($translation_language == "de")
					$oposite_translation_language = "en";
				else
					$oposite_translation_language = "de";
				$tempTransSymptomQuery = mysqli_query($db,"SELECT id, symptom_id, Beschreibung_".$translation_language." FROM temp_translation_symptoms WHERE master_id = '".$add_translation_master_id."' AND quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."' AND need_approval = 1 ORDER BY id ASC LIMIT 1");
				if(mysqli_num_rows($tempTransSymptomQuery) > 0){
					$tempTransSymptomData = mysqli_fetch_assoc($tempTransSymptomQuery);
					$existingSymptomQuery = mysqli_query($db,"SELECT Beschreibung_".$oposite_translation_language." FROM quelle_import_test WHERE id = '".$tempTransSymptomData['symptom_id']."'");
					if(mysqli_num_rows($existingSymptomQuery) > 0)
						$existingSymptomData = mysqli_fetch_assoc($existingSymptomQuery);

					$data = array();
					$data['Beschreibung_'.$translation_language] = base64_encode($tempTransSymptomData['Beschreibung_'.$translation_language]);
					$data['Beschreibung_'.$oposite_translation_language] = (isset($existingSymptomData['Beschreibung_'.$oposite_translation_language]) AND $existingSymptomData['Beschreibung_'.$oposite_translation_language] != "") ? base64_encode($existingSymptomData['Beschreibung_'.$oposite_translation_language]) : "";
					// $data['existing_symptom_id'] = $tempTransSymptomData['symptom_id'];
					$data['temp_symptom_id'] = $tempTransSymptomData['id'];

					$resultData = $data;
					$status = "success";
					$message = "success";
				}else{
					$resultData = array();
					$status = "success";
					$message = "Required data not found. Please reload the page and try again!";
				}
			} else {
				$resultData = array();
				$status = 'success';
	    		$message = 'Required data not found. Please reload the page and try again!';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>