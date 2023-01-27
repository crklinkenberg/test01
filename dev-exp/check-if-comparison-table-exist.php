<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	$resultData = array();
	$status = '';
	$message = '';

	try {
		$arzneiId = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : "";
		$initialSourceId = (isset($_POST['initial_source']) AND $_POST['initial_source'] != "") ? $_POST['initial_source'] : "";
		$comparingSourceIds = (isset($_POST['comparing_sources']) AND !empty($_POST['comparing_sources'])) ? $_POST['comparing_sources'] : array();
		if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
			$comparingSourceIds = explode(",", $comparingSourceIds);
		$comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";
		$similarityRate = (isset($_POST['similarity_rate']) AND $_POST['similarity_rate'] != "") ? $_POST['similarity_rate'] : 20;
		$comparisonOption = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : 1;
		$comparisonLanguage = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$per_page_initial_symptom_number = (isset($_POST['per_page_initial_symptom_number']) AND $_POST['per_page_initial_symptom_number'] != "") ? $_POST['per_page_initial_symptom_number'] : "";
		$is_opened_a_saved_comparison = (isset($_POST['is_opened_a_saved_comparison']) AND $_POST['is_opened_a_saved_comparison'] != "") ? $_POST['is_opened_a_saved_comparison'] : "";

		// Precompared compared comparison table name 
		$precomparedComparisonTable = "comparison_table_".$arzneiId."_".$initialSourceId."_".implode('_', $comparingSourceIds)."_".$comparisonLanguage;

		// Checking if the dynamic table exist
		$dynamicTableResult = mysqli_query($db,"SHOW TABLES LIKE '".$precomparedComparisonTable."'");

		$errorCount = 0;
		if($arzneiId == ""){
			$errorCount = 1;
		}
		if($initialSourceId == ""){
			$errorCount = 1;
		}
		if($comparingSourcesInsertString == ""){
			$errorCount = 1;
		}
		if($comparisonLanguage == ""){
			$errorCount = 1;
		}
		
		if($errorCount == 0){
			// Comparison table name
			$comparingSourcesString = implode("_", $comparingSourceIds);
			$comparisonTable = "comparison_table_".$arzneiId."_".$initialSourceId."_".$comparingSourcesString."_".$comparisonLanguage;

			// Comparison only initials table name
			$comparisonOnlyInitialTable = $comparisonTable."_initials";
			
			// Storing comparison table data in sesssion Start
			$_SESSION['comparison_table_data'] = array();
			$tempData = array();
			$tempData['arznei_id'] = $arzneiId;
			$tempData['initial_source'] = $initialSourceId;
			$tempData['comparing_sources'] = $comparingSourceIds;
			$tempData['similarity_rate'] = $similarityRate;
			$tempData['comparison_option'] = $comparisonOption;
			$tempData['comparison_language'] = $comparisonLanguage;
			$tempData['comparison_table'] = $comparisonTable;
			$tempData['comparison_only_initial_table'] = $comparisonOnlyInitialTable;
			$tempData['per_page_initial_symptom_number'] = $per_page_initial_symptom_number;
			$tempData['is_opened_a_saved_comparison'] = $is_opened_a_saved_comparison;

			$_SESSION['comparison_table_data'] = $tempData;
			// Storing comparison table data in sesssion End

			$data = array();
			$data['dynamic_table_name'] = $precomparedComparisonTable;
			$checkIfExist = mysqli_query($db,"SELECT id, table_name, status FROM pre_comparison_master_data  WHERE table_name = '".$comparisonTable."'");
			if(mysqli_num_rows($checkIfExist) == 0){
				$data['is_table_exist'] = 0;

				// Prepare comparison name
				$initialQuelleCode = getQuelleCode($initialSourceId);
				$comparingQuelleCodes = array();
				foreach ($comparingSourceIds as $val) {
					$comparingQuelleCode = getQuelleCode($val);
					$comparingQuelleCodes[] = $comparingQuelleCode;
				}
				
				$comparingQuelleCodeCombined = (!empty($comparingQuelleCodes)) ? implode("_", $comparingQuelleCodes) : "";
				$comparisonName = $initialQuelleCode;
				if($comparingQuelleCodeCombined != "")
					$comparisonName .= "_".$comparingQuelleCodeCombined;

				$checkIfExist = mysqli_query($db,"SELECT id, table_name, status FROM pre_comparison_master_data  WHERE table_name = '".$comparisonTable."'");
				if(mysqli_num_rows($checkIfExist) == 0){
					$quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$initialSourceId."'");
					if(mysqli_num_rows($quelleResult) > 0)
						$quelleRow = mysqli_fetch_assoc($quelleResult);
					$combinedSourceJahr = ($quelleRow['jahr'] != "") ? $quelleRow['jahr'] : "";

					$newQuelleId = "";
					$checkingQuery = $db->query("SELECT quelle_id FROM quelle where titel = '".$comparisonName."'");
					if($checkingQuery->num_rows == 0){
						$quelleInsertQuery="INSERT INTO quelle (quelle_type_id, titel, jahr, comparison_save_status, ersteller_datum) VALUES (3, NULLIF('".$comparisonName."', ''), NULLIF('".$combinedSourceJahr."', ''), 0, NULLIF('".$date."', ''))";
			            $db->query($quelleInsertQuery);
			            $newQuelleId = $db->insert_id;

			            $is_symptoms_available_in_en = 0;
			            $is_symptoms_available_in_de = 0;
			            $allComparingSourceIds = (!empty($comparingSourceIds)) ? implode(',', $comparingSourceIds) : "";
						$checkLanguageAvailability = $db->query("SELECT id FROM quelle_import_master WHERE (quelle_id = '".$initialSourceId."' OR quelle_id IN (".$allComparingSourceIds.")) AND arznei_id = '".$arzneiId."' AND (is_symptoms_available_in_de = 0 OR is_symptoms_available_in_en = 0)");
			   			if($checkLanguageAvailability->num_rows > 0){
			   				if($comparisonLanguage == "en")
				            	$is_symptoms_available_in_en = 1;
				            if($comparisonLanguage == "de")
				            	$is_symptoms_available_in_de = 1;
			   			}else{
			   				$is_symptoms_available_in_de = 1;
			   				$is_symptoms_available_in_en = 1;
			   			}

			            $quelleSymptomsMasterInsertQuery="INSERT INTO quelle_import_master (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, arznei_id, quelle_id, ersteller_datum) VALUES ('default_setting', NULLIF('".$comparisonLanguage."', ''), NULLIF('".$is_symptoms_available_in_de."', ''), NULLIF('".$is_symptoms_available_in_en."', ''), NULLIF('".$arzneiId."', ''), NULLIF('".$newQuelleId."', ''), '".$date."')";
			            $db->query($quelleSymptomsMasterInsertQuery);
			            $quelleSymptomsMasterId = $db->insert_id;

			            // Making arznei quelle relationship
			        	$arzneiQuelleResult = $db->query("SELECT arznei_id FROM arznei_quelle where arznei_id = '".$arzneiId."' AND quelle_id = '".$newQuelleId."'");
						if($arzneiQuelleResult->num_rows == 0){
							$arzneiQuelleInsertQuery="INSERT INTO arznei_quelle (arznei_id, quelle_id, ersteller_datum) VALUES ('".$arzneiId."', '".$newQuelleId."', '".$date."')";
			        		$db->query($arzneiQuelleInsertQuery);  
						}
					}

					$updateQuelleData = "UPDATE quelle SET is_materia_medica = 0, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$initialSourceId;
					$db->query($updateQuelleData);
					$searchMultipleQuelleInitial = $db->query("SELECT id, quelle_id, arznei_id FROM quelle_import_master where arznei_id != '".$arzneiId."' AND quelle_id = '".$initialSourceId."'");
					if($searchMultipleQuelleInitial->num_rows > 0){
						$isMateriaMedicaCheckingCntr = 0;
						while($mutiQuelleRow = mysqli_fetch_array($searchMultipleQuelleInitial)){
							$preComMaster = $db->query("SELECT id FROM pre_comparison_master_data where arznei_id = '".$mutiQuelleRow['arznei_id']."' AND (initial_source = '".$mutiQuelleRow['quelle_id']."' OR FIND_IN_SET('".$mutiQuelleRow['quelle_id']."', comparing_sources) > 0)");
							if($preComMaster->num_rows > 0){
								$isMateriaMedicaCheckingCntr++;
							}
						}
						if($searchMultipleQuelleInitial->num_rows != $isMateriaMedicaCheckingCntr){
							$updateQuelleData = "UPDATE quelle SET is_materia_medica = 1, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$initialSourceId;
							$db->query($updateQuelleData);
						}
					}
					foreach ($comparingSourceIds as $key => $comparingSourceValue) {
						$updateQuelleData = "UPDATE quelle SET is_materia_medica = 0, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$comparingSourceValue;
						$db->query($updateQuelleData);
						$searchMultipleQuelleComparing = $db->query("SELECT id, quelle_id, arznei_id FROM quelle_import_master where arznei_id != '".$arzneiId."' AND quelle_id = '".$comparingSourceValue."'");
						if($searchMultipleQuelleComparing->num_rows > 0){
							$isMateriaMedicaCheckingComCntr = 0;
							while($mutiQuelleRow = mysqli_fetch_array($searchMultipleQuelleComparing)){
								$preComMaster = $db->query("SELECT id FROM pre_comparison_master_data where arznei_id = '".$mutiQuelleRow['arznei_id']."' AND (initial_source = '".$mutiQuelleRow['quelle_id']."' OR FIND_IN_SET('".$mutiQuelleRow['quelle_id']."', comparing_sources) > 0)");
								if($preComMaster->num_rows > 0){
									$isMateriaMedicaCheckingComCntr++;
								}
							}
							if($searchMultipleQuelleComparing->num_rows != $isMateriaMedicaCheckingComCntr){
								$updateQuelleData = "UPDATE quelle SET is_materia_medica = 1, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$comparingSourceValue;
								$db->query($updateQuelleData);
							}
						}
					}

					

		            $masterDataInsertQuery="INSERT INTO pre_comparison_master_data (quelle_id, table_name, comparison_name, similarity_rate, comparison_language, arznei_id, comparison_option, initial_source, comparing_sources, status, ersteller_datum) VALUES (NULLIF('".$newQuelleId."', ''), NULLIF('".$comparisonTable."', ''), NULLIF('".$comparisonName."', ''), NULLIF('".$similarityRate."', ''), NULLIF('".$comparisonLanguage."', ''), NULLIF('".$arzneiId."', ''), NULLIF('".$comparisonOption."', ''), NULLIF('".$initialSourceId."', ''), NULLIF('".$comparingSourcesInsertString."', ''), 'processing', NULLIF('".$date."', ''))";
		            $db->query($masterDataInsertQuery);
		            $preComparisonMasterDatainsertedId = $db->insert_id;
		            if($preComparisonMasterDatainsertedId != ""){
		            	$comparisonTable=escapeshellarg($comparisonTable);
		            	/* FOR LIVE SERVER ENVIRONMENT */
						$cmd = '/usr/bin/php create-dynamic-comparison-table.php '.$comparisonTable.' > /dev/null 2>/dev/null &';
						/* FOR LOCAL ENVIRONMENT UNCOMMENT BELOW LINE AND COMMENT ABOVE LINE */
						// $cmd = 'php create-dynamic-comparison-table.php "'.$comparisonTable.'" 2>&1 &';

						$result = shell_exec($cmd);
						if($result != ""){
					   		$message = 'Dynamic table creation script got executed in the background.';
						}else{
					   		$message = 'Dynamic table creation script got executed in the background.';
						}
		            }else{
		            	$status = 'error';
	   					$message = 'Data did not get inserted in pre_comparison_master_data table';
		            }
		        }
			}else{
				$data['is_table_exist'] = 1;
			}
			$resultData = $data;
			$status = 'success';
		}else{
			$status = 'error';
	   		$message = 'Required data not found';
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>