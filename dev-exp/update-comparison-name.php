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
	$message = 'Could not perform the action, please try again!';
	try {
		$comparison_name = (isset($_POST['comparison_name']) AND $_POST['comparison_name'] != "") ? trim($_POST['comparison_name']) : null;
		$quelle_id = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? $_POST['quelle_id'] : null;
		$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : null;
		$existing_comparison_name = (isset($_POST['existing_comparison_name']) AND $_POST['existing_comparison_name'] != "") ? trim($_POST['existing_comparison_name']) : null;
		if($quelle_id != "" AND $comparison_name != "")
		{
			$totalSymptomQuery = mysqli_query($db,"SELECT quelle_id FROM quelle WHERE (code = '".$comparison_name."' OR titel = '".$comparison_name."') AND quelle_id != '".$quelle_id."'");
			if(mysqli_num_rows($totalSymptomQuery) > 0){
				$status = 'error';
				$message = 'This name is already used.';
			}
			else
			{
				$db->begin_transaction();
				$comparison_name = mysqli_real_escape_string($db, $comparison_name);
				
				$updSavedComparisonQuery = "UPDATE saved_comparisons SET comparison_name = '".$comparison_name."' WHERE quelle_id = ".$quelle_id;
            	$db->query($updSavedComparisonQuery);

            	$updConnectionISCQuery = "UPDATE symptom_connections SET initial_source_code = '".$comparison_name."' WHERE initial_source_code = '".$existing_comparison_name."'";
            	$db->query($updConnectionISCQuery);

            	$updConnectionCSCQuery = "UPDATE symptom_connections SET comparing_source_code = '".$comparison_name."' WHERE comparing_source_code = '".$existing_comparison_name."'";
            	$db->query($updConnectionCSCQuery);

            	$updQuelleQuery = "UPDATE quelle SET code = '".$comparison_name."', titel = '".$comparison_name."' WHERE quelle_id = ".$quelle_id;
            	$db->query($updQuelleQuery);

            	// This current_saved_comparisons_backup_id is used in the last section of this page for backup_connected_symptoms_details
            	$current_saved_comparisons_backup_id = "";
				$currentSavedComBackupResult = mysqli_query($db, "SELECT SC.id as scid, SC.comparison_name FROM quelle_import_master_backup AS QIM JOIN saved_comparisons_backup AS SC ON QIM.quelle_id = SC.quelle_id WHERE QIM.original_quelle_id = '".$quelle_id."' ORDER BY QIM.ersteller_datum DESC LIMIT 0, 1");
				if(mysqli_num_rows($currentSavedComBackupResult) > 0){
					$currentSavedComBackupData = mysqli_fetch_assoc($currentSavedComBackupResult);
					$current_saved_comparisons_backup_id = $currentSavedComBackupData['scid'];
				}

            	// ADDING IT IN THE BACKUP TABLE
            	$fetchQuelleQuery=$db->query("SELECT * FROM quelle WHERE quelle_id = ".$quelle_id);
	            if($fetchQuelleQuery->num_rows > 0){
	            	$quelleData = mysqli_fetch_assoc($fetchQuelleQuery);
	            	$backupQuelleInsertQuery="INSERT INTO quelle_backup (quelle_type_id, code, titel, jahr, ersteller_datum) VALUES (NULLIF('".$quelleData['quelle_type_id']."', ''), NULLIF('".$quelleData['code']."', ''), NULLIF('".$quelleData['titel']."', ''), NULLIF('".$quelleData['jahr']."', ''), NULLIF('".$quelleData['ersteller_datum']."', ''))";
					$db->query($backupQuelleInsertQuery);
					$newQuelleId = $db->insert_id;
					$originalQuelleIdOfNewQuelleId = $quelleData['quelle_id'];
					
					$fetchSavedComparisonQuery=$db->query("SELECT * FROM saved_comparisons WHERE quelle_id = ".$quelle_id." AND arznei_id = ".$arznei_id);
		            if($fetchSavedComparisonQuery->num_rows > 0){
		            	$savedComparisonData = mysqli_fetch_assoc($fetchSavedComparisonQuery);

		            	$savedComparisonQuery="INSERT INTO saved_comparisons_backup (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language, quelle_id, original_quelle_id, include_appended_symptom) VALUES (NULLIF('".$savedComparisonData['arznei_id']."', ''), '".$savedComparisonData['initial_source_id']."', NULLIF('".$savedComparisonData['comparing_source_ids']."', ''), NULLIF('".$savedComparisonData['comparison_name']."', ''), NULLIF('".$savedComparisonData['similarity_rate']."', ''), NULLIF('".$savedComparisonData['comparison_option']."', ''), NULLIF('".$savedComparisonData['comparison_language']."', ''), '".$newQuelleId."', '".$quelle_id."', NULLIF('".$savedComparisonData['include_appended_symptom']."', ''))";
						$db->query($savedComparisonQuery);
						$saved_comparison_master_id = mysqli_insert_id($db);
		            }
		            $bckup_saved_comparison_master_id = ($saved_comparison_master_id != "") ? $saved_comparison_master_id : "";

		            $fetchQuelleImportMasterQuery=$db->query("SELECT * FROM quelle_import_master WHERE quelle_id = ".$quelle_id." AND arznei_id = ".$arznei_id);
		            if($fetchQuelleImportMasterQuery->num_rows > 0){
		            	$quelleImportMasterData = mysqli_fetch_assoc($fetchQuelleImportMasterQuery);

		            	$quelleSymptomsMasterBackupInsertQuery="INSERT INTO quelle_import_master_backup (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, original_quelle_id, ersteller_datum) VALUES (NULLIF('".$quelleImportMasterData['import_rule']."', ''), NULLIF('".$quelleImportMasterData['importing_language']."', ''), NULLIF('".$quelleImportMasterData['is_symptoms_available_in_de']."', ''), NULLIF('".$quelleImportMasterData['is_symptoms_available_in_en']."', ''), NULLIF('".$quelleImportMasterData['translation_method_of_de']."', ''), NULLIF('".$quelleImportMasterData['translation_method_of_en']."', ''), NULLIF('".$quelleImportMasterData['arznei_id']."', ''), NULLIF('".$newQuelleId."', ''), NULLIF('".$quelle_id."', ''), '".$date."')";
			            $db->query($quelleSymptomsMasterBackupInsertQuery);
			            $quelleSymptomsMasterBackupId = $db->insert_id;
		            }

		            // Adding below part from save-comparison page to get the connects in a way that i can add in the backup connects table 
		            $arzneiId = (isset($savedComparisonData['arznei_id']) AND $savedComparisonData['arznei_id'] != "") ? trim($savedComparisonData['arznei_id']) : null;
					$initialSource = (isset($savedComparisonData['initial_source_id']) AND $savedComparisonData['initial_source_id'] != "") ? trim($savedComparisonData['initial_source_id']) : null;
					$comparingSources = (isset($savedComparisonData['comparing_source_ids']) AND $savedComparisonData['comparing_source_ids'] != "") ? explode(',', $savedComparisonData['comparing_source_ids']) : array();
					$similarityRate = (isset($savedComparisonData['similarity_rate']) AND $savedComparisonData['similarity_rate'] != "") ? trim($savedComparisonData['similarity_rate']) : null;
					$comparisonOption = (isset($savedComparisonData['comparison_option']) AND $savedComparisonData['comparison_option'] != "") ? trim($savedComparisonData['comparison_option']) : null;
					$comparisonLanguage = (isset($savedComparisonData['comparison_language']) AND $savedComparisonData['comparison_language'] != "") ? trim($savedComparisonData['comparison_language']) : null;
					$comparisonName = (isset($savedComparisonData['comparison_name']) AND $savedComparisonData['comparison_name'] != "") ? trim($savedComparisonData['comparison_name']) : null;
					$include_appended_symptom = (isset($savedComparisonData['include_appended_symptom']) AND $savedComparisonData['include_appended_symptom'] != "") ? trim($savedComparisonData['include_appended_symptom']) : null;
					$allSourcers = array();
					$escapeSymptomIds = array();

					$comparedSourcersOfInitialSource = array();
					$symptomConQueryCondition = '';
					$comparison_initial_source_id = $initialSource;
					if($comparison_initial_source_id != "")
						array_push($allSourcers, $comparison_initial_source_id);
					foreach ($comparingSources as $cSourceKey => $cSourceVal) {
						array_push($allSourcers, $cSourceVal);
					}

					if(!empty($allSourcers)){
						$returnedIds = getAllComparedSourceIds($allSourcers);
						if(!empty($returnedIds)){
							foreach ($returnedIds as $IdVal) {
								if(!in_array($IdVal, $allSourcers))
									array_push($allSourcers, $IdVal);
							}
						}	
					}

					// Collecting initial source's already compared sources ids of initial source
					$initialQuelleIdInArr = explode(',', $comparison_initial_source_id);
					if(!empty($initialQuelleIdInArr)){
						$returnedIds = getAllComparedSourceIds($initialQuelleIdInArr);
						if(!empty($returnedIds)){
							foreach ($returnedIds as $IdVal) {
								if(!in_array($IdVal, $comparedSourcersOfInitialSource))
									array_push($comparedSourcersOfInitialSource, $IdVal);
							}
						}	
					}

					$conditionIds = (!empty($allSourcers)) ? rtrim(implode(',', $allSourcers), ',') : null;
					if($conditionIds != "")
						$symptomConQueryCondition = " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
					if($arzneiId != "")
						$symptomConQueryCondition .= " AND source_arznei_id = '".$arzneiId."'";
					$comparison_name = mysqli_real_escape_string($db, $comparisonName);


					$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE quelle_id = '".$comparison_initial_source_id."' AND arznei_id = '".$arznei_id."'");
					if($symptomResult->num_rows > 0){
						while($symptomData = mysqli_fetch_array($symptomResult)){
							$addResult = addTheSymptomInOnlyBackTable($symptomData['id'], $quelleSymptomsMasterBackupId, $newQuelleId);
							if($addResult['status'] === true)
							{
								$backupSymtomId = $addResult['return_data']['backup_symptom_id'];

								if($backupSymtomId != "")
								{
									$connectedSymptomResult = $db->query("SELECT id, source_arznei_id, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$symptomData['id']."' OR comparing_source_symptom_id = '".$symptomData['id']."') AND (is_connected = 1 OR is_pasted = 1)".$symptomConQueryCondition);
									if($connectedSymptomResult->num_rows > 0){
										while($conRow = mysqli_fetch_array($connectedSymptomResult)){

											$source_arznei_id = (isset($conRow['source_arznei_id']) AND $conRow['source_arznei_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['source_arznei_id']) : null;
											$connection_language = (isset($conRow['connection_language']) AND $conRow['connection_language'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_language']) : null;
											if($conRow['initial_source_symptom_id'] == $symptomData['id']){
												$oppositeSymptomId = $conRow['comparing_source_symptom_id'];

												$is_initial_source = 1;
												$initial_source_type = 'backup';
												$comparing_source_type = 'original';
												$backup_initial_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
												$backup_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
												$backup_conversion_initial_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
												$backup_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
												$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
												$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
												$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
												$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
												$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP

												$initial_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
												$comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
												$backup_initial_source_symptom_id = (isset($backupSymtomId) AND $backupSymtomId !="" ) ? mysqli_real_escape_string($db, $backupSymtomId) : null;
												$backup_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
												$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$initial_source_symptom_highlighted_de = (isset($conRow['initial_source_symptom_highlighted_de']) AND $conRow['initial_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_de']) : null;
												$initial_source_symptom_highlighted_en = (isset($conRow['initial_source_symptom_highlighted_en']) AND $conRow['initial_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_en']) : null;
												$comparing_source_symptom_highlighted_de = (isset($conRow['comparing_source_symptom_highlighted_de']) AND $conRow['comparing_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_de']) : null;
												$comparing_source_symptom_highlighted_en = (isset($conRow['comparing_source_symptom_highlighted_en']) AND $conRow['comparing_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_en']) : null;
												$initial_source_symptom_de = (isset($conRow['initial_source_symptom_de']) AND $conRow['initial_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_de']) : null;
												$initial_source_symptom_en = (isset($conRow['initial_source_symptom_en']) AND $conRow['initial_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_en']) : null;
												$comparing_source_symptom_de = (isset($conRow['comparing_source_symptom_de']) AND $conRow['comparing_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_de']) : null;
												$comparing_source_symptom_en = (isset($conRow['comparing_source_symptom_en']) AND $conRow['comparing_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_en']) : null;
												$matching_percentage = (isset($conRow['matching_percentage']) AND $conRow['matching_percentage'] !="" ) ? mysqli_real_escape_string($db, $conRow['matching_percentage']) : 0;
												$is_connected = $conRow['is_connected'];
												$is_ns_connect = $conRow['is_ns_connect'];
												$ns_connect_note = (isset($conRow['ns_connect_note']) AND $conRow['ns_connect_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_connect_note']) : null;
												$is_pasted = $conRow['is_pasted'];
												$is_ns_paste = $conRow['is_ns_paste'];
												$ns_paste_note = (isset($conRow['ns_paste_note']) AND $conRow['ns_paste_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_paste_note']) : null;
												$connection_or_paste_type = (isset($conRow['connection_or_paste_type']) AND $conRow['connection_or_paste_type'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_or_paste_type']) : null;
											}
											else
											{
												$oppositeSymptomId = $conRow['initial_source_symptom_id'];

												$is_initial_source = 0;
												$comparing_source_type = "backup";
												$initial_source_type = "original";
												$backup_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
												$backup_comparing_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
												$backup_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
												$backup_conversion_comparing_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
												$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
												$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
												$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
												$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
												$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP

												$initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
												$comparing_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
												
												// FOR BACKUP TABLE QUERY
												$backup_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
												$backup_comparing_source_symptom_id = (isset($backupSymtomId) AND $backupSymtomId !="" ) ? mysqli_real_escape_string($db, $backupSymtomId) : null;
												// FOR BACKUP TABLE QUERY
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
												$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$initial_source_symptom_highlighted_de = (isset($conRow['initial_source_symptom_highlighted_de']) AND $conRow['initial_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_de']) : null;
												$initial_source_symptom_highlighted_en = (isset($conRow['initial_source_symptom_highlighted_en']) AND $conRow['initial_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_en']) : null;
												$comparing_source_symptom_highlighted_de = (isset($conRow['comparing_source_symptom_highlighted_de']) AND $conRow['comparing_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_de']) : null;
												$comparing_source_symptom_highlighted_en = (isset($conRow['comparing_source_symptom_highlighted_en']) AND $conRow['comparing_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_en']) : null;
												$initial_source_symptom_de = (isset($conRow['initial_source_symptom_de']) AND $conRow['initial_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_de']) : null;
												$initial_source_symptom_en = (isset($conRow['initial_source_symptom_en']) AND $conRow['initial_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_en']) : null;
												$comparing_source_symptom_de = (isset($conRow['comparing_source_symptom_de']) AND $conRow['comparing_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_de']) : null;
												$comparing_source_symptom_en = (isset($conRow['comparing_source_symptom_en']) AND $conRow['comparing_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_en']) : null;
												$matching_percentage = (isset($conRow['matching_percentage']) AND $conRow['matching_percentage'] !="" ) ? mysqli_real_escape_string($db, $conRow['matching_percentage']) : 0;
												$is_connected = $conRow['is_connected'];
												$is_ns_connect = $conRow['is_ns_connect'];
												$ns_connect_note = (isset($conRow['ns_connect_note']) AND $conRow['ns_connect_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_connect_note']) : null;
												$is_pasted = $conRow['is_pasted'];
												$is_ns_paste = $conRow['is_ns_paste'];
												$ns_paste_note = (isset($conRow['ns_paste_note']) AND $conRow['ns_paste_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_paste_note']) : null;
												$connection_or_paste_type = (isset($conRow['connection_or_paste_type']) AND $conRow['connection_or_paste_type'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_or_paste_type']) : null;
											}

											if(!in_array($oppositeSymptomId, $escapeSymptomIds))
												array_push($escapeSymptomIds, $oppositeSymptomId);

											if($conRow['is_pasted'] == 1) 
											{

												if($conRow['initial_source_symptom_id'] == $symptomData['id'])
													$examining_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
												else
													$examining_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;

												// Add pasted symptom separately as initial symptom only when it is not from previously saved comparison sources of the initial source 
												if(!in_array($examining_source_id, $comparedSourcersOfInitialSource)){
													// For pasted symptoms
													$addedSymtomId = "";
													$addResult2 = addTheSymptomInOnlyBackTable($oppositeSymptomId, $quelleSymptomsMasterBackupId, $newQuelleId);
													if($addResult2['status'] === true){
														$backupAddedSymtomId = $addResult2['return_data']['backup_symptom_id'];

														if($backupAddedSymtomId != ""){
															if($conRow['initial_source_symptom_id'] == $symptomData['id']){
																$initial_source_type = 'original';
																$comparing_source_type = 'backup';
																// FOR BACKUP TABLE QUERY
																$backup_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
																$backup_comparing_source_symptom_id = (isset($backupAddedSymtomId) AND $backupAddedSymtomId !="" ) ? mysqli_real_escape_string($db, $backupAddedSymtomId) : null;
																// FOR BACKUP TABLE QUERY
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
																$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
																$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
																$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$comparing_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
																$initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
																// FOR BACKUP TABLE QUERY
																$backup_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
																$backup_comparing_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
																$backup_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
																$backup_conversion_comparing_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
																// FOR BACKUP TABLE QUERY
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
																$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
																$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
																$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
																// RAW COMPARISON CONNECTIONS FOR BACKUP

															}
															else{
																$initial_source_type = 'backup';
																$comparing_source_type = 'original';
																// FOR BACKUP TABLE QUERY
																$backup_initial_source_symptom_id = (isset($backupAddedSymtomId) AND $backupAddedSymtomId !="" ) ? mysqli_real_escape_string($db, $backupAddedSymtomId) : null;
																$backup_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
																// FOR BACKUP TABLE QUERY
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
																$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
																$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
																$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$initial_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
																$comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
																// FOR BACKUP TABLE QUERY
																$backup_initial_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
																$backup_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
																$backup_conversion_initial_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
																$backup_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
																// FOR BACKUP TABLE QUERY
																// RAW COMPARISON CONNECTIONS FOR BACKUP
																$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
																$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
																$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
																$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
																// RAW COMPARISON CONNECTIONS FOR BACKUP
															}
														}
													}
												}
											}
											
											// FOR BACKUP TABLE QUERY
											$backupQuery = "INSERT INTO symptom_connections_backup (saved_comparisons_backup_id, initial_source_type, comparing_source_type, source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), '".$initial_source_type."', '".$comparing_source_type."', NULLIF('".$source_arznei_id."', ''), '".$is_initial_source."', NULLIF('".$backup_initial_source_id."', ''), NULLIF('".$backup_comparing_source_id."', ''), NULLIF('".$backup_conversion_initial_source_id."', ''), NULLIF('".$backup_conversion_comparing_source_id."', ''), NULLIF('".$initial_source_code."', ''), NULLIF('".$comparing_source_code."', ''), NULLIF('".$backup_initial_source_symptom_id."', ''), NULLIF('".$backup_comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted_de."', ''), NULLIF('".$initial_source_symptom_highlighted_en."', ''), NULLIF('".$comparing_source_symptom_highlighted_de."', ''), NULLIF('".$comparing_source_symptom_highlighted_en."', ''), NULLIF('".$initial_source_symptom_de."', ''), NULLIF('".$initial_source_symptom_en."', ''), NULLIF('".$comparing_source_symptom_de."', ''), NULLIF('".$comparing_source_symptom_en."', ''), NULLIF('".$connection_language."', ''), '".$matching_percentage."', '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''), 1, NULLIF('".$connection_or_paste_type."', ''))";
											$db->query($backupQuery);
											
											// RAW COMPARISON CONNECTIONS FOR BACKUP
											$backupRawConnQuery = "INSERT INTO symptom_connections_backup (saved_comparisons_backup_id, source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$source_arznei_id."', ''), '".$is_initial_source."', NULLIF('".$raw_initial_source_id."', ''), NULLIF('".$raw_comparing_source_id."', ''), NULLIF('".$raw_conversion_initial_source_id."', ''), NULLIF('".$raw_conversion_comparing_source_id."', ''), NULLIF('".$raw_initial_source_code."', ''), NULLIF('".$raw_comparing_source_code."', ''), NULLIF('".$raw_initial_source_symptom_id."', ''), NULLIF('".$raw_comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted_de."', ''), NULLIF('".$initial_source_symptom_highlighted_en."', ''), NULLIF('".$comparing_source_symptom_highlighted_de."', ''), NULLIF('".$comparing_source_symptom_highlighted_en."', ''), NULLIF('".$initial_source_symptom_de."', ''), NULLIF('".$initial_source_symptom_en."', ''), NULLIF('".$comparing_source_symptom_de."', ''), NULLIF('".$comparing_source_symptom_en."', ''), NULLIF('".$connection_language."', ''), '".$matching_percentage."', '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''), 1, NULLIF('".$connection_or_paste_type."', ''))";
											$db->query($backupRawConnQuery);
										}
									}
								}
							}
						}
					}

					// Add remaining un-matched comparative symptoms
					$escapeSymptomCondition = "";
					if(!empty($escapeSymptomIds)){
						$uniqueMatchedSymptomIds = array_unique($escapeSymptomIds);
						$matchedSymptomIdsString = implode(',', $uniqueMatchedSymptomIds);
						$escapeSymptomCondition = "AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")";
					}
					$comparingSourceIds = (!empty($comparingSources)) ? rtrim(implode(',', $comparingSources), ',') : null;
					if($comparingSourceIds != "")
					{
						$restOfComparingSymptomResultCount = $db->query("SELECT quelle_import_test.id FROM quelle_import_test WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.quelle_id IN (".$comparingSourceIds.") ".$escapeSymptomCondition);
						if($restOfComparingSymptomResultCount->num_rows > 0){
							while($restOfComparingSymptomRow = mysqli_fetch_array($restOfComparingSymptomResultCount)){
								$addResult3 = addTheSymptomInOnlyBackTable($restOfComparingSymptomRow['id'], $quelleSymptomsMasterBackupId, $newQuelleId);
								if($addResult3['status'] === true)
								{
									$backupSymtomId = $addResult3['return_data']['backup_symptom_id'];

									if($backupSymtomId != "")
									{
										$connectedSymptomResult = $db->query("SELECT id, source_arznei_id, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$restOfComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$restOfComparingSymptomRow['id']."') AND (is_connected = 1 OR is_pasted = 1)".$symptomConQueryCondition);
										if($connectedSymptomResult->num_rows > 0){
											while($conRow = mysqli_fetch_array($connectedSymptomResult)){
												$source_arznei_id = (isset($conRow['source_arznei_id']) AND $conRow['source_arznei_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['source_arznei_id']) : null;
												$connection_language = (isset($conRow['connection_language']) AND $conRow['connection_language'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_language']) : null;
												if($conRow['initial_source_symptom_id'] == $restOfComparingSymptomRow['id']){
													$oppositeSymptomId = $conRow['comparing_source_symptom_id'];

													$is_initial_source = 1;
													$initial_source_type = 'backup';
													$comparing_source_type = 'original';
													$backup_initial_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
													$backup_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
													$backup_conversion_initial_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
													$backup_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
													$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
													$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
													$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
													$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
													$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP

													$initial_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
													$comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
													$backup_initial_source_symptom_id = (isset($backupSymtomId) AND $backupSymtomId !="" ) ? mysqli_real_escape_string($db, $backupSymtomId) : null;
													$backup_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
													$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$initial_source_symptom_highlighted_de = (isset($conRow['initial_source_symptom_highlighted_de']) AND $conRow['initial_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_de']) : null;
													$initial_source_symptom_highlighted_en = (isset($conRow['initial_source_symptom_highlighted_en']) AND $conRow['initial_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_en']) : null;
													$comparing_source_symptom_highlighted_de = (isset($conRow['comparing_source_symptom_highlighted_de']) AND $conRow['comparing_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_de']) : null;
													$comparing_source_symptom_highlighted_en = (isset($conRow['comparing_source_symptom_highlighted_en']) AND $conRow['comparing_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_en']) : null;
													$initial_source_symptom_de = (isset($conRow['initial_source_symptom_de']) AND $conRow['initial_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_de']) : null;
													$initial_source_symptom_en = (isset($conRow['initial_source_symptom_en']) AND $conRow['initial_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_en']) : null;
													$comparing_source_symptom_de = (isset($conRow['comparing_source_symptom_de']) AND $conRow['comparing_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_de']) : null;
													$comparing_source_symptom_en = (isset($conRow['comparing_source_symptom_en']) AND $conRow['comparing_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_en']) : null;
													$matching_percentage = (isset($conRow['matching_percentage']) AND $conRow['matching_percentage'] !="" ) ? mysqli_real_escape_string($db, $conRow['matching_percentage']) : 0;
													$is_connected = $conRow['is_connected'];
													$is_ns_connect = $conRow['is_ns_connect'];
													$ns_connect_note = (isset($conRow['ns_connect_note']) AND $conRow['ns_connect_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_connect_note']) : null;
													$is_pasted = $conRow['is_pasted'];
													$is_ns_paste = $conRow['is_ns_paste'];
													$ns_paste_note = (isset($conRow['ns_paste_note']) AND $conRow['ns_paste_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_paste_note']) : null;
													$connection_or_paste_type = (isset($conRow['connection_or_paste_type']) AND $conRow['connection_or_paste_type'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_or_paste_type']) : null;
												}
												else
												{
													$oppositeSymptomId = $conRow['initial_source_symptom_id'];

													$is_initial_source = 0;
													$comparing_source_type = "backup";
													$initial_source_type = "original";
													$backup_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
													$backup_comparing_source_id = (isset($newQuelleId) AND $newQuelleId !="" ) ? mysqli_real_escape_string($db, $newQuelleId) : null;
													$backup_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
													$backup_conversion_comparing_source_id = (isset($originalQuelleIdOfNewQuelleId) AND $originalQuelleIdOfNewQuelleId !="" ) ? mysqli_real_escape_string($db, $originalQuelleIdOfNewQuelleId) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$raw_initial_source_id = (isset($conRow['initial_source_id']) AND $conRow['initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_id']) : null;
													$raw_comparing_source_id = (isset($conRow['comparing_source_id']) AND $conRow['comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_id']) : null;
													$raw_initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
													$raw_comparing_source_code = (isset($conRow['comparing_source_code']) AND $conRow['comparing_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_code']) : null;
													$raw_conversion_initial_source_id = (isset($conRow['conversion_initial_source_id']) AND $conRow['conversion_initial_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_initial_source_id']) : null;
													$raw_conversion_comparing_source_id = (isset($conRow['conversion_comparing_source_id']) AND $conRow['conversion_comparing_source_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['conversion_comparing_source_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP

													$initial_source_code = (isset($conRow['initial_source_code']) AND $conRow['initial_source_code'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_code']) : null;
													$comparing_source_code = (isset($comparison_name) AND $comparison_name !="" ) ? mysqli_real_escape_string($db, $comparison_name) : null;
													
													// FOR BACKUP TABLE QUERY
													$backup_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
													$backup_comparing_source_symptom_id = (isset($backupSymtomId) AND $backupSymtomId !="" ) ? mysqli_real_escape_string($db, $backupSymtomId) : null;
													// FOR BACKUP TABLE QUERY
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$raw_initial_source_symptom_id = (isset($conRow['initial_source_symptom_id']) AND $conRow['initial_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_id']) : null;
													$raw_comparing_source_symptom_id = (isset($conRow['comparing_source_symptom_id']) AND $conRow['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_id']) : null;
													// RAW COMPARISON CONNECTIONS FOR BACKUP
													$initial_source_symptom_highlighted_de = (isset($conRow['initial_source_symptom_highlighted_de']) AND $conRow['initial_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_de']) : null;
													$initial_source_symptom_highlighted_en = (isset($conRow['initial_source_symptom_highlighted_en']) AND $conRow['initial_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_highlighted_en']) : null;
													$comparing_source_symptom_highlighted_de = (isset($conRow['comparing_source_symptom_highlighted_de']) AND $conRow['comparing_source_symptom_highlighted_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_de']) : null;
													$comparing_source_symptom_highlighted_en = (isset($conRow['comparing_source_symptom_highlighted_en']) AND $conRow['comparing_source_symptom_highlighted_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_highlighted_en']) : null;
													$initial_source_symptom_de = (isset($conRow['initial_source_symptom_de']) AND $conRow['initial_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_de']) : null;
													$initial_source_symptom_en = (isset($conRow['initial_source_symptom_en']) AND $conRow['initial_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['initial_source_symptom_en']) : null;
													$comparing_source_symptom_de = (isset($conRow['comparing_source_symptom_de']) AND $conRow['comparing_source_symptom_de'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_de']) : null;
													$comparing_source_symptom_en = (isset($conRow['comparing_source_symptom_en']) AND $conRow['comparing_source_symptom_en'] !="" ) ? mysqli_real_escape_string($db, $conRow['comparing_source_symptom_en']) : null;
													$matching_percentage = (isset($conRow['matching_percentage']) AND $conRow['matching_percentage'] !="" ) ? mysqli_real_escape_string($db, $conRow['matching_percentage']) : 0;
													$is_connected = $conRow['is_connected'];
													$is_ns_connect = $conRow['is_ns_connect'];
													$ns_connect_note = (isset($conRow['ns_connect_note']) AND $conRow['ns_connect_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_connect_note']) : null;
													$is_pasted = $conRow['is_pasted'];
													$is_ns_paste = $conRow['is_ns_paste'];
													$ns_paste_note = (isset($conRow['ns_paste_note']) AND $conRow['ns_paste_note'] !="" ) ? mysqli_real_escape_string($db, $conRow['ns_paste_note']) : null;
													$connection_or_paste_type = (isset($conRow['connection_or_paste_type']) AND $conRow['connection_or_paste_type'] !="" ) ? mysqli_real_escape_string($db, $conRow['connection_or_paste_type']) : null;
												}

												// FOR BACKUP TABLE QUERY
												$backupQuery = "INSERT INTO symptom_connections_backup (saved_comparisons_backup_id, initial_source_type, comparing_source_type, source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), '".$initial_source_type."', '".$comparing_source_type."', NULLIF('".$source_arznei_id."', ''), '".$is_initial_source."', NULLIF('".$backup_initial_source_id."', ''), NULLIF('".$backup_comparing_source_id."', ''), NULLIF('".$backup_conversion_initial_source_id."', ''), NULLIF('".$backup_conversion_comparing_source_id."', ''), NULLIF('".$initial_source_code."', ''), NULLIF('".$comparing_source_code."', ''), NULLIF('".$backup_initial_source_symptom_id."', ''), NULLIF('".$backup_comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted_de."', ''), NULLIF('".$initial_source_symptom_highlighted_en."', ''), NULLIF('".$comparing_source_symptom_highlighted_de."', ''), NULLIF('".$comparing_source_symptom_highlighted_en."', ''), NULLIF('".$initial_source_symptom_de."', ''), NULLIF('".$initial_source_symptom_en."', ''), NULLIF('".$comparing_source_symptom_de."', ''), NULLIF('".$comparing_source_symptom_en."', ''), NULLIF('".$connection_language."', ''), '".$matching_percentage."', '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''), 1, NULLIF('".$connection_or_paste_type."', ''))";
												$db->query($backupQuery);
												
												// RAW COMPARISON CONNECTIONS FOR BACKUP
												$backupRawConnQuery = "INSERT INTO symptom_connections_backup (saved_comparisons_backup_id, source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$source_arznei_id."', ''), '".$is_initial_source."', NULLIF('".$raw_initial_source_id."', ''), NULLIF('".$raw_comparing_source_id."', ''), NULLIF('".$raw_conversion_initial_source_id."', ''), NULLIF('".$raw_conversion_comparing_source_id."', ''), NULLIF('".$raw_initial_source_code."', ''), NULLIF('".$raw_comparing_source_code."', ''), NULLIF('".$raw_initial_source_symptom_id."', ''), NULLIF('".$raw_comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted_de."', ''), NULLIF('".$initial_source_symptom_highlighted_en."', ''), NULLIF('".$comparing_source_symptom_highlighted_de."', ''), NULLIF('".$comparing_source_symptom_highlighted_en."', ''), NULLIF('".$initial_source_symptom_de."', ''), NULLIF('".$initial_source_symptom_en."', ''), NULLIF('".$comparing_source_symptom_de."', ''), NULLIF('".$comparing_source_symptom_en."', ''), NULLIF('".$connection_language."', ''), '".$matching_percentage."', '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''), 1, NULLIF('".$connection_or_paste_type."', ''))";
												$db->query($backupRawConnQuery);
											}
										}
									}
								}
							}
						} 
					}



					// Adding swapped symptoms in their current status separately for this backup set
					$comparing_source_ids = "";
					if(!empty($comparingSources))
						$comparing_source_ids = implode(",", $comparingSources);
					$swappedSymptomsQuery = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE comparison_initial_source_id = '".$comparison_initial_source_id."' AND comparison_comparing_source_ids = '".$comparing_source_ids."' AND arznei_id = '".$arzneiId."'");
					if(mysqli_num_rows($swappedSymptomsQuery) > 0){
						while($swappedSymptomData = mysqli_fetch_array($swappedSymptomsQuery)){
							$symptomResult = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE id = '".$swappedSymptomData['symptom_id']."'");
							if(mysqli_num_rows($symptomResult) > 0){
								$symptomData = mysqli_fetch_assoc($symptomResult);

								$symptomData['original_symptom_id'] = ($symptomData['id'] != "") ? mysqli_real_escape_string($db, $symptomData['id']) : null;
								$symptomData['master_id'] = ($symptomData['master_id'] != "") ? mysqli_real_escape_string($db, $symptomData['master_id']) : null;
								$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
								$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
								$symptomData['original_quelle_id'] = ($symptomData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['original_quelle_id']) : null;
								$symptomData['quelle_code'] = ($symptomData['quelle_code'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_code']) : null;
								$symptomData['Symptomnummer'] = mysqli_real_escape_string($db, $symptomData['Symptomnummer']);
								$symptomData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalVon']);
								$symptomData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalBis']);
								$symptomData['final_version_de'] = mysqli_real_escape_string($db, $symptomData['final_version_de']);
								$symptomData['final_version_en'] = mysqli_real_escape_string($db, $symptomData['final_version_en']);
								$symptomData['Beschreibung_de'] = mysqli_real_escape_string($db, $symptomData['Beschreibung_de']);
								$symptomData['Beschreibung_en'] = mysqli_real_escape_string($db, $symptomData['Beschreibung_en']);
								$symptomData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal_de']);
								$symptomData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal_en']);
								$symptomData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $symptomData['BeschreibungFull_de']);
								$symptomData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $symptomData['BeschreibungFull_en']);
								$symptomData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $symptomData['BeschreibungPlain_de']);
								$symptomData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $symptomData['BeschreibungPlain_en']);
								$symptomData['searchable_text_de'] = mysqli_real_escape_string($db, $symptomData['searchable_text_de']);
								$symptomData['searchable_text_en'] = mysqli_real_escape_string($db, $symptomData['searchable_text_en']);
								$symptomData['bracketedString_de'] = mysqli_real_escape_string($db, $symptomData['bracketedString_de']);
								$symptomData['bracketedString_en'] = mysqli_real_escape_string($db, $symptomData['bracketedString_en']);
								$symptomData['timeString_de'] = mysqli_real_escape_string($db, $symptomData['timeString_de']);
								$symptomData['timeString_en'] = mysqli_real_escape_string($db, $symptomData['timeString_en']);
								$symptomData['Fussnote'] = mysqli_real_escape_string($db, $symptomData['Fussnote']);
								$symptomData['EntnommenAus'] = mysqli_real_escape_string($db, $symptomData['EntnommenAus']);
								$symptomData['Verweiss'] = mysqli_real_escape_string($db, $symptomData['Verweiss']);
								$symptomData['Graduierung'] = mysqli_real_escape_string($db, $symptomData['Graduierung']);
								$symptomData['BereichID'] = mysqli_real_escape_string($db, $symptomData['BereichID']);
								$symptomData['Kommentar'] = mysqli_real_escape_string($db, $symptomData['Kommentar']);
								$symptomData['Unklarheiten'] = mysqli_real_escape_string($db, $symptomData['Unklarheiten']);
								$symptomData['Remedy'] = mysqli_real_escape_string($db, $symptomData['Remedy']);
								$symptomData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $symptomData['symptom_of_different_remedy']);
								$symptomData['subChapter'] = mysqli_real_escape_string($db, $symptomData['subChapter']);
								$symptomData['subSubChapter'] = mysqli_real_escape_string($db, $symptomData['subSubChapter']);
								$symptomData['symptom_edit_comment'] = mysqli_real_escape_string($db, $symptomData['symptom_edit_comment']);
								$symptomData['is_final_version_available'] = mysqli_real_escape_string($db, $symptomData['is_final_version_available']);
								$symptomData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $symptomData['is_symptom_number_mismatch']);
								$symptomData['is_symptom_appended'] = mysqli_real_escape_string($db, $symptomData['is_symptom_appended']);

								$mainSymptomInsertQuery="INSERT INTO backup_sets_swapped_symptoms (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
						
					            $db->query($mainSymptomInsertQuery);
					            $mainSymtomId = $db->insert_id;

					            /* Insert Symptom_pruefer relation START */
					            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM symptom_pruefer where symptom_id = '".$symptomData['id']."'");
								if($symptomPrueferResult->num_rows > 0){
									while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
										$mainSymptomPrueferInsertQuery = "INSERT INTO backup_sets_swapped_symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
						            	$db->query($mainSymptomPrueferInsertQuery);
									}
								}
								/* Insert Symptom_pruefer relation END */

								/* Insert symptom_reference relation START */
					            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM symptom_reference where symptom_id = '".$symptomData['id']."'");
								if($symptomReferenceResult->num_rows > 0){
									while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
										$mainSymptomReferenceInsertQuery = "INSERT INTO backup_sets_swapped_symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
						            	$db->query($mainSymptomReferenceInsertQuery);
									}
								}
								/* Insert symptom_reference relation END */
							}
						}
					}


					// Adding current backup_connected_symptoms_details, appended_symptoms_backup AND final_version_symptoms_info_for_backups Tables data for this newly creating backup set START
					if($current_saved_comparisons_backup_id != ""){
						$backupConnectedSymptomResult = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$current_saved_comparisons_backup_id."'");
						if($backupConnectedSymptomResult->num_rows > 0){
							while($backupConnectedSymptomData = mysqli_fetch_array($backupConnectedSymptomResult)){

								$symptomData['original_symptom_id'] = ($backupConnectedSymptomData['original_symptom_id'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['original_symptom_id']) : null;
								$symptomData['master_id'] = ($backupConnectedSymptomData['master_id'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['master_id']) : null;
								$symptomData['arznei_id'] = ($backupConnectedSymptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['arznei_id']) : null;
								$symptomData['quelle_id'] = ($backupConnectedSymptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['quelle_id']) : null;
								$symptomData['original_quelle_id'] = ($backupConnectedSymptomData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['original_quelle_id']) : null;
								$symptomData['quelle_code'] = ($backupConnectedSymptomData['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupConnectedSymptomData['quelle_code']) : null;
								$symptomData['Symptomnummer'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Symptomnummer']);
								$symptomData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['SeiteOriginalVon']);
								$symptomData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['SeiteOriginalBis']);
								$symptomData['final_version_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['final_version_de']);
								$symptomData['final_version_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['final_version_en']);
								$symptomData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Beschreibung_de']);
								$symptomData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Beschreibung_en']);
								$symptomData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungOriginal_de']);
								$symptomData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungOriginal_en']);
								$symptomData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungFull_de']);
								$symptomData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungFull_en']);
								$symptomData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungPlain_de']);
								$symptomData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BeschreibungPlain_en']);
								$symptomData['searchable_text_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['searchable_text_de']);
								$symptomData['searchable_text_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['searchable_text_en']);
								$symptomData['bracketedString_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['bracketedString_de']);
								$symptomData['bracketedString_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['bracketedString_en']);
								$symptomData['timeString_de'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['timeString_de']);
								$symptomData['timeString_en'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['timeString_en']);
								$symptomData['Fussnote'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Fussnote']);
								$symptomData['EntnommenAus'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['EntnommenAus']);
								$symptomData['Verweiss'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Verweiss']);
								$symptomData['Graduierung'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Graduierung']);
								$symptomData['BereichID'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['BereichID']);
								$symptomData['Kommentar'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Kommentar']);
								$symptomData['Unklarheiten'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Unklarheiten']);
								$symptomData['Remedy'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['Remedy']);
								$symptomData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['symptom_of_different_remedy']);
								$symptomData['subChapter'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['subChapter']);
								$symptomData['subSubChapter'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['subSubChapter']);
								$symptomData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['symptom_edit_comment']);
								$symptomData['is_final_version_available'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['is_final_version_available']);
								$symptomData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['is_symptom_number_mismatch']);
								$symptomData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupConnectedSymptomData['is_symptom_appended']);

								$mainSymptomInsertQuery="INSERT INTO backup_connected_symptoms_details (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
		            			$db->query($mainSymptomInsertQuery);
		            			$mainSymtomId = $db->insert_id;

		            			/* Insert Symptom_pruefer relation START */
					            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM backup_connected_symptoms_details_pruefer where symptom_id = '".$backupConnectedSymptomData['id']."'");
								if($symptomPrueferResult->num_rows > 0){
									while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
										$mainSymptomPrueferInsertQuery = "INSERT INTO backup_connected_symptoms_details_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
						            	$db->query($mainSymptomPrueferInsertQuery);
									}
								}
								/* Insert Symptom_pruefer relation END */

								/* Insert symptom_reference relation START */
					            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM backup_connected_symptoms_details_reference where symptom_id = '".$backupConnectedSymptomData['id']."'");
								if($symptomReferenceResult->num_rows > 0){
									while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
										$mainSymptomReferenceInsertQuery = "INSERT INTO backup_connected_symptoms_details_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
						            	$db->query($mainSymptomReferenceInsertQuery);
									}
								}
								/* Insert symptom_reference relation END */
							}
						}

						// copying existing backs appended symptoms information for this new backup
						$fetchAppendedSymptomsBackupQuery=$db->query("SELECT * FROM appended_symptoms_backup WHERE saved_comparisons_backup_id = '".$current_saved_comparisons_backup_id."'");
		            	if($fetchAppendedSymptomsBackupQuery->num_rows > 0){
		            		while($appendedSymptomsBackupData = mysqli_fetch_array($fetchAppendedSymptomsBackupQuery)){
		            			$appendedSymptomsQuery=$db->query("SELECT id FROM appended_symptoms_backup WHERE symptom_id = '".$appendedSymptomsBackupData['symptom_id']."' AND saved_comparisons_backup_id = '".$bckup_saved_comparison_master_id."'");
				            	if($appendedSymptomsQuery->num_rows == 0){
				            		$appendedSymptomInsertQuery = "INSERT INTO appended_symptoms_backup (saved_comparisons_backup_id, symptom_id) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$appendedSymptomsBackupData['symptom_id']."', ''))";
		            				$db->query($appendedSymptomInsertQuery);
				            	}
		            		}
		            	}

		            	// copying existing backs final_version_symptoms_info_for_backups information for this new backup
		            	$fetchExistingFVQuery = $db->query("SELECT * FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = '".$current_saved_comparisons_backup_id."'");
		            	if($fetchExistingFVQuery->num_rows > 0){
		            		while($exinsingFVData = mysqli_fetch_array($fetchExistingFVQuery)){
		            			$FVinfoInsertQuery="INSERT INTO final_version_symptoms_info_for_backups (saved_comparisons_backup_id, symptom_id, final_version_de, final_version_en, is_final_version_available) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$exinsingFVData['symptom_id']."', ''), NULLIF('".$exinsingFVData['final_version_de']."', ''), NULLIF('".$exinsingFVData['final_version_en']."', ''), '".$exinsingFVData['is_final_version_available']."')";
					            $db->query($FVinfoInsertQuery);
		            		}
		            	}
					}
		           	// Adding current backup_connected_symptoms_details, appended_symptoms_backup AND final_version_symptoms_info_for_backups Tables data for this newly creating backup set END
	            }

            	$db->commit();
				$status = 'success';
		    	$message = 'Updated successfully';
			}
		}
	} catch (Exception $e) {
	    // echo '<p>', $e->getMessage(), '</p>';
	    $db->rollback();
	    $status = 'error';
	    $message = 'Something went wrong, please try again!';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>