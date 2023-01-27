<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php
	// $stopWords = array();
	// $stopWords = getStopWords();
	
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the operation.';

	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])) {	
			parse_str( $_POST['form'], $formData );
			
			$symptom_version = (isset($formData['symptom_version']) AND $formData['symptom_version'] != "") ? trim($formData['symptom_version']) : "";
			$symptom_edit_de = (isset($formData['symptom_edit_de']) AND $formData['symptom_edit_de'] != "") ? trim($formData['symptom_edit_de']) : "";
			$symptom_edit_en = (isset($formData['symptom_edit_en']) AND $formData['symptom_edit_en'] != "") ? trim($formData['symptom_edit_en']) : "";
			$symptom_id = (isset($formData['modal_symptom_edit_symptom_id']) AND $formData['modal_symptom_edit_symptom_id'] != "") ? mysqli_real_escape_string($db, trim($formData['modal_symptom_edit_symptom_id'])) : "";
			$quelle_id = (isset($formData['modal_symptom_edit_quelle_id']) AND $formData['modal_symptom_edit_quelle_id'] != "") ? mysqli_real_escape_string($db, trim($formData['modal_symptom_edit_quelle_id'])) : "";
			$arznei_id = (isset($formData['modal_symptom_edit_arznei_id']) AND $formData['modal_symptom_edit_arznei_id'] != "") ? mysqli_real_escape_string($db, trim($formData['modal_symptom_edit_arznei_id'])) : "";
			$quelle_import_master_id = (isset($formData['modal_symptom_edit_quelle_import_master_id']) AND $formData['modal_symptom_edit_quelle_import_master_id'] != "") ? mysqli_real_escape_string($db, trim($formData['modal_symptom_edit_quelle_import_master_id'])) : "";
			$symptom_type = (isset($formData['symptom_type']) AND $formData['symptom_type'] != "") ? mysqli_real_escape_string($db, $formData['symptom_type']) : "";
			

			if(($symptom_version != "" AND $symptom_id != "") AND ($symptom_edit_de != "" OR $symptom_edit_en != "")){
				$sts = 1;
				$symptomTypeResult = mysqli_query($db, "SELECT symptom_type_setting_id FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomTypeResult) > 0){
					$deleteSymptomType ="DELETE FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'";
					$db->query($deleteSymptomType);
				}

				if($symptom_type != ""){
					$symptomTypeInsertQuery="INSERT INTO symptom_type_setting (symptom_id, symptom_type, ersteller_datum) VALUES (NULLIF('".$symptom_id."', ''), NULLIF('".$symptom_type."', ''), NULLIF('".$date."', ''))";
					$db->query($symptomTypeInsertQuery);
				}

				if($symptom_edit_de != ""){
					$result = editSymptomOriginalOrConverted($symptom_id, $symptom_edit_de, 'de', $symptom_version);
					// echo json_encode( array( 'status' => $status, 'result_data' => $result, 'message' => $message) ); 
					// exit;
					if(isset($result['status']) AND $result['status'] == false)
						$sts = 0;
				}
				if($symptom_edit_en != ""){
					$result = editSymptomOriginalOrConverted($symptom_id, $symptom_edit_en, 'en', $symptom_version);
					if(isset($result['status']) AND $result['status'] == false)
						$sts = 0;
				}

				if($sts == 1) {
					$status = "success";
					$message = "Symptom edited successfully.";
				} else {
					$status = 'error';
					$message = 'Could not complete the symptom edit process.';
				}
			} else{
				$status = 'error';
	    		$message = 'Operation failed, required data not found.';
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