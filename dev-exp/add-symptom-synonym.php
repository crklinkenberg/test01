<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the operation.';
	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])) {	
			parse_str( $_POST['form'], $formData );
			
			$synonym_language = (isset($formData['synonym_language']) AND $formData['synonym_language'] != "") ? trim($formData['synonym_language']) : "";
			$word = (isset($formData['word']) AND $formData['word'] != "") ? mysqli_real_escape_string($db, trim($formData['word'])) : "";
			$strict_synonym = (isset($formData['strict_synonym']) AND $formData['strict_synonym'] != "") ? mysqli_real_escape_string($db, trim($formData['strict_synonym'])) : "";
			$synonym_partial_1 = (isset($formData['synonym_partial_1']) AND $formData['synonym_partial_1'] != "") ? mysqli_real_escape_string($db, trim($formData['synonym_partial_1'])) : "";
			$synonym_partial_2 = (isset($formData['synonym_partial_2']) AND $formData['synonym_partial_2'] != "") ? mysqli_real_escape_string($db, trim($formData['synonym_partial_2'])) : "";
			$synonym_general = (isset($formData['synonym_general']) AND $formData['synonym_general'] != "") ? mysqli_real_escape_string($db, trim($formData['synonym_general'])) : "";
			$synonym_minor = (isset($formData['synonym_minor']) AND $formData['synonym_minor'] != "") ? mysqli_real_escape_string($db, trim($formData['synonym_minor'])) : "";
			$synonym_nn = (isset($formData['synonym_nn']) AND $formData['synonym_nn'] != "") ? mysqli_real_escape_string($db, trim($formData['synonym_nn'])) : "";
			$modal_quelle_id = (isset($formData['modal_quelle_id']) AND $formData['modal_quelle_id'] != "") ? $formData['modal_quelle_id'] : "";
			$modal_arznei_id = (isset($formData['modal_arznei_id']) AND $formData['modal_arznei_id'] != "") ? $formData['modal_arznei_id'] : "";
			$modal_quelle_import_master_id = (isset($formData['modal_quelle_import_master_id']) AND $formData['modal_quelle_import_master_id'] != "") ? $formData['modal_quelle_import_master_id'] : "";

			if($synonym_language != "" AND $word != "" AND $strict_synonym != "" AND $modal_quelle_id != "" AND $modal_arznei_id != "" AND $modal_quelle_import_master_id != ""){
				if($synonym_language == "de" OR $synonym_language == "en"){
					$isAlreadyExist = 0;
					$checkSynonym = mysqli_query($db, "SELECT synonym_id FROM synonym_".$synonym_language." WHERE LOWER(word) = '".strtolower($word)."'");
					if(mysqli_num_rows($checkSynonym) > 0){
						$isAlreadyExist = 1;
					}
					if($isAlreadyExist == 1){
						$status = 'error';
		    			$message = 'Synonym already exist.';
					}else{
						$synonymInsertQuery="INSERT INTO synonym_".$synonym_language." (word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, ersteller_datum) VALUES (NULLIF('".$word."', ''), NULLIF('".$strict_synonym."', ''), NULLIF('".$synonym_partial_1."', ''), NULLIF('".$synonym_partial_2."', ''), NULLIF('".$synonym_general."', ''), NULLIF('".$synonym_minor."', ''), NULLIF('".$synonym_nn."', ''), NULLIF('".$date."', ''))";
						$db->query($synonymInsertQuery);
						$newSynonymId = mysqli_insert_id($db);
						if($newSynonymId != ""){
							$result = upToDateSourceSymptomSynonyms($modal_quelle_id, $modal_arznei_id, $modal_quelle_import_master_id);
							if(isset($result['status']) AND $result['status'] == true){
								$status = "success";
								$message = "Added successfully.";
							} else {
								$status = 'error';
	    						$message = 'The process of making source symptoms up to date with the latest synonym is not complete.';
							}
						} else {
							$status = 'error';
	    					$message = 'Operation failed.';
						}
					}
				}else{
					$status = 'error';
	    			$message = 'Operation failed, Invalid language data.';
				}
			}else{
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