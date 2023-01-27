<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Updating the saved comparison name
	*/
?>
<?php  
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the action!';
	try {
		$quelle_id = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? $_POST['quelle_id'] : null;
		$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : null;
		$quelle_import_master_id = (isset($_POST['quelle_import_master_id']) AND $_POST['quelle_import_master_id'] != "") ? trim($_POST['quelle_import_master_id']) : null;
		if($quelle_id != "" AND $arznei_id != "" AND $quelle_import_master_id != "")
		{
			$result = upToDateSourceSymptomSynonyms($quelle_id, $arznei_id, $quelle_import_master_id);
			if(isset($result['status']) AND $result['status'] == true){
				$status = "success";
				$message = "Updated successfully.";
			} else {
				$status = 'error';
				$message = 'The process of making source symptoms up to date with the latest synonym is not complete.';
			}

			/*$db->begin_transaction();
			$getMasterTableInfo = mysqli_query($db, "SELECT is_symptoms_available_in_de, is_symptoms_available_in_en FROM quelle_import_master WHERE id = '".$quelle_import_master_id."'");
			if(mysqli_num_rows($getMasterTableInfo) > 0){
				$masterTableData = mysqli_fetch_assoc($getMasterTableInfo);
				$availableEnSynonyms = array();
				$availableDeSynonyms = array();
				$globalStopWords = getStopWords();
				$synonymEnResult = mysqli_query($db, "SELECT synonym_id, word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn FROM synonym_en");
				if(mysqli_num_rows($synonymEnResult) > 0){
					while($synonymEnRow = mysqli_fetch_array($synonymEnResult)){
						$synonymData = array();
						$synonymData['synonym_id'] = $synonymEnRow['synonym_id'];
						$synonymData['word'] = mb_strtolower($synonymEnRow['word']);
						$synonymData['strict_synonym'] = mb_strtolower($synonymEnRow['strict_synonym']);
						$synonymData['synonym_partial_1'] = mb_strtolower($synonymEnRow['synonym_partial_1']);
						$synonymData['synonym_partial_2'] = mb_strtolower($synonymEnRow['synonym_partial_2']);
						$synonymData['synonym_general'] = mb_strtolower($synonymEnRow['synonym_general']);
						$synonymData['synonym_minor'] = mb_strtolower($synonymEnRow['synonym_minor']);
						$synonymData['synonym_nn'] = mb_strtolower($synonymEnRow['synonym_nn']);
						$availableEnSynonyms[] = $synonymData;
					}
				}
				$synonymDeResult = mysqli_query($db, "SELECT synonym_id, word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn FROM synonym_de");
				if(mysqli_num_rows($synonymDeResult) > 0){
					while($synonymDeRow = mysqli_fetch_array($synonymDeResult)){
						$synonymData = array();
						$synonymData['synonym_id'] = $synonymDeRow['synonym_id'];
						$synonymData['word'] = mb_strtolower($synonymDeRow['word']);
						$synonymData['strict_synonym'] = mb_strtolower($synonymDeRow['strict_synonym']);
						$synonymData['synonym_partial_1'] = mb_strtolower($synonymDeRow['synonym_partial_1']);
						$synonymData['synonym_partial_2'] = mb_strtolower($synonymDeRow['synonym_partial_2']);
						$synonymData['synonym_general'] = mb_strtolower($synonymDeRow['synonym_general']);
						$synonymData['synonym_minor'] = mb_strtolower($synonymDeRow['synonym_minor']);
						$synonymData['synonym_nn'] = mb_strtolower($synonymDeRow['synonym_nn']);
						$availableDeSynonyms[] = $synonymData;
					}
				}

				$getComMasterTableInfo = mysqli_query($db, "SELECT table_name FROM pre_comparison_master_data WHERE quelle_id = '".$quelle_id."'");
				if(mysqli_num_rows($getComMasterTableInfo) > 0){
					// Combined sources
					$comMasterTableData = mysqli_fetch_assoc($getComMasterTableInfo);
					$comparisonTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comMasterTableData['table_name']."'");
					if(mysqli_num_rows($comparisonTableCheck) > 0){
						$symptomResult = mysqli_query($db,"SELECT id, symptom_id, searchable_text_de, searchable_text_en FROM ".$comMasterTableData['table_name']." GROUP BY symptom_id");
						if(mysqli_num_rows($symptomResult) > 0){
							while($symRow = mysqli_fetch_array($symptomResult)){
								if($masterTableData['is_symptoms_available_in_de'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_de'], $globalStopWords, $availableDeSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']." SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								} else if($masterTableData['is_symptoms_available_in_en'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_en'], $globalStopWords, $availableEnSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']." SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								}
							}
						}
					}
					$comparisonCompletedTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comMasterTableData['table_name']."_completed'");
					if(mysqli_num_rows($comparisonCompletedTableCheck) > 0){
						$symptomResult = mysqli_query($db,"SELECT id, symptom_id, searchable_text_de, searchable_text_en FROM ".$comMasterTableData['table_name']."_completed GROUP BY symptom_id");
						if(mysqli_num_rows($symptomResult) > 0){
							while($symRow = mysqli_fetch_array($symptomResult)){
								if($masterTableData['is_symptoms_available_in_de'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_de'], $globalStopWords, $availableDeSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']."_completed SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								} else if($masterTableData['is_symptoms_available_in_en'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_en'], $globalStopWords, $availableEnSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']."_completed SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								}
							}
						}
					}
					$comparisonHighestMatchTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comMasterTableData['table_name']."_highest_matches'");
					if(mysqli_num_rows($comparisonHighestMatchTableCheck) > 0){
						$symptomResult = mysqli_query($db,"SELECT id, symptom_id, searchable_text_de, searchable_text_en FROM ".$comMasterTableData['table_name']."_highest_matches GROUP BY symptom_id");
						if(mysqli_num_rows($symptomResult) > 0){
							while($symRow = mysqli_fetch_array($symptomResult)){
								if($masterTableData['is_symptoms_available_in_de'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_de'], $globalStopWords, $availableDeSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']."_highest_matches SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								} else if($masterTableData['is_symptoms_available_in_en'] == 1) {
									// Finding match synonyms START
									$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_en'], $globalStopWords, $availableEnSynonyms);
									if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
										$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
									}
									$data = array();
									$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
									$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
									$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
									$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
									$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
									$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
									$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
									// Finding match synonyms END

									$updSymptom = "UPDATE ".$comMasterTableData['table_name']."_highest_matches SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE symptom_id = ".$symRow['symptom_id'];
	        						$db->query($updSymptom);
								}
							}
						}
					}
				} else {
					// Single sources
					$symptomResult = mysqli_query($db,"SELECT id, searchable_text_de, searchable_text_en FROM quelle_import_test WHERE master_id = '".$quelle_import_master_id."'");
					if(mysqli_num_rows($symptomResult) > 0){
						while($symRow = mysqli_fetch_array($symptomResult)){
							if($masterTableData['is_symptoms_available_in_de'] == 1) {
								// Finding match synonyms START
								$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_de'], $globalStopWords, $availableDeSynonyms);
								if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
									$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
								}
								$data = array();
								$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
								$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
								$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
								$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
								$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
								$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
								$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
								// Finding match synonyms END

								$updSymptom = "UPDATE quelle_import_test SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE id = ".$symRow['id'];
        						$db->query($updSymptom);
							} else if($masterTableData['is_symptoms_available_in_en'] == 1) {
								// Finding match synonyms START
								$matchedSynonyms = findMatchedSynonyms($symRow['searchable_text_en'], $globalStopWords, $availableEnSynonyms);
								if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
									$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
								}
								$data = array();
								$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
								$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
								$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
								$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
								$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
								$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
								$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
								// Finding match synonyms END

								$updSymptom = "UPDATE quelle_import_test SET synonym_word = '".$data['synonym_word']."', strict_synonym = '".$data['strict_synonym']."', synonym_partial_1 = '".$data['synonym_partial_1']."', synonym_partial_2 = '".$data['synonym_partial_2']."', synonym_general = '".$data['synonym_general']."', synonym_minor = '".$data['synonym_minor']."', synonym_nn = '".$data['synonym_nn']."' WHERE id = ".$symRow['id'];
        						$db->query($updSymptom);
							}
						}
					}
				}
	        	$successUpdQuelleImportmasterQuery = "UPDATE quelle_import_master SET is_synonyms_up_to_date = 1 WHERE id = ".$quelle_import_master_id;
	        	$db->query($successUpdQuelleImportmasterQuery);
			}
        	$db->commit();
			$status = 'success';
	    	$message = 'Updated successfully';*/
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong.';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>