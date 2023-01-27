<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Generating the comparison result bacthes (This is used in backup section) 
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();

	$is_invalid_quelle = 0;

	$compareResultArray = array();
	$formData = array();
	$step = 'done';
	$processStage = "";
	$progress_percentage = 0;
	$comparisonName = "";
	$totalBatchesInPart1 = 1;
	$totalBatchesInPart2 = 1;
	$matchedSymptomIds = array();
	$comparingSourceIds = "";
	$isStage2Checked = 0;
	$un_matched_symptoms_set_number = 0;

	try {

		if(isset($_POST['form']) AND !empty($_POST['form'])){
			
			parse_str( $_POST['form'], $formData );
			$arzneiId = (isset($formData['arznei_id']) AND $formData['arznei_id'] != "") ? $formData['arznei_id'] : null;
			$initialSource = (isset($formData['initial_source']) AND $formData['initial_source'] != "") ? $formData['initial_source'] : null;
			$comparingSources = (isset($formData['comparing_sources']) AND !empty($formData['comparing_sources'])) ? $formData['comparing_sources'] : array();
			$similarityRate = (isset($formData['similarity_rate']) AND $formData['similarity_rate'] != "") ? $formData['similarity_rate'] : null;
			$comparisonOption = (isset($formData['comparison_option']) AND $formData['comparison_option'] != "") ? $formData['comparison_option'] : null;
			$comparisonLanguage = (isset($formData['comparison_language']) AND $formData['comparison_language'] != "") ? $formData['comparison_language'] : null;
			$step = (isset($_POST['step']) AND $_POST['step'] != "") ? $_POST['step'] : 1;
			$totalBatchesInPart1 = (isset($_POST['total_batches_in_part1']) AND $_POST['total_batches_in_part1'] != "") ? (int)$_POST['total_batches_in_part1'] : 1;
			$totalBatchesInPart2 = (isset($_POST['total_batches_in_part2']) AND $_POST['total_batches_in_part2'] != "") ? (int)$_POST['total_batches_in_part2'] : 1;
			$isStage2Checked = (isset($_POST['is_stage2_checked']) AND $_POST['is_stage2_checked'] != "") ? (int)$_POST['is_stage2_checked'] : 1;
			$un_matched_symptoms_set_number = (isset($_POST['un_matched_symptoms_set_number']) AND $_POST['un_matched_symptoms_set_number'] != "") ? (int)$_POST['un_matched_symptoms_set_number'] : 0;
			$matchedSymptomIds = (isset($_POST['matched_symptom_ids']) AND !empty($_POST['matched_symptom_ids'])) ? $_POST['matched_symptom_ids'] : array();
			$initialSourceArray = array();
			$comparingSourcesArray = array();
			$saved_comparisons_backup_id = (isset($_POST['saved_comparisons_backup_id']) AND !empty($_POST['saved_comparisons_backup_id'])) ? $_POST['saved_comparisons_backup_id'] : null;
			
			if($initialSource != "" AND !empty($comparingSources) AND $arzneiId != ""){

				// For returning the comparison comparing source ids back
				$comparingSourceIds = implode(',', $comparingSources);

				$InitialQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$initialSource."'");
				if(mysqli_num_rows($InitialQuelleResult) > 0){
					$iniRow = mysqli_fetch_assoc($InitialQuelleResult);

					if($iniRow['quelle_type_id'] == 3)
						$preparedQuelleCode = $iniRow['code'];
					else{
						if($iniRow['jahr'] != "" AND $iniRow['code'] != "")
							$rowQuelleCode = trim(str_replace(trim($iniRow['jahr']), '', $iniRow['code']));
						else
							$rowQuelleCode = trim($iniRow['code']);
						$preparedQuelleCode = trim($rowQuelleCode." ".$iniRow['jahr']);
					}

					$comparisonName .= $preparedQuelleCode;
					$initialSourceArray[] = array(
						'quelle_id' => $iniRow['quelle_id'],
						'quelle' => $preparedQuelleCode
					);
				}

				foreach ($comparingSources as $getKey => $getVal) {
					$comparingQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$getVal."'");
					if(mysqli_num_rows($comparingQuelleResult) > 0){
						while($comparingRow = mysqli_fetch_array($comparingQuelleResult)){
							if($comparingRow['quelle_type_id'] == 3)
								$preparedQuelleCodeForCom = $comparingRow['code'];
							else{
								if($comparingRow['jahr'] != "" AND $comparingRow['code'] != "")
									$rowQuelleCodeForCom = trim(str_replace(trim($comparingRow['jahr']), '', $comparingRow['code']));
								else
									$rowQuelleCodeForCom = trim($comparingRow['code']);
								$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingRow['jahr']);
							}

							$comparisonName .= "_".$preparedQuelleCodeForCom;
							if($comparingRow['quelle_id'] != $initialSource){
								$comparingSourcesArray[] = array(
									'quelle_id' => $comparingRow['quelle_id'],
									'quelle' => $preparedQuelleCodeForCom,
									'year' => trim($comparingRow['jahr'])
								);
							}
						}
					}
				}
				// Short Sources ASC
				$comparingSourceWhereIn = "";
				if(!empty($comparingSourcesArray)){
					$quelleYear = array();
					$comparingSourceWhereIn = "(";
					foreach ($comparingSourcesArray as $key => $row)
					{
					    $quelleYear[$key] = $row['year'];
					    $comparingSourceWhereIn .= $row['quelle_id'].',';
					}
					array_multisort($quelleYear, SORT_ASC, $comparingSourcesArray);
					$comparingSourceWhereIn = rtrim($comparingSourceWhereIn, ',');
					$comparingSourceWhereIn .= ")";
				}
				$comparisonName = ltrim($comparisonName,"_");
				$initialQuelleId = (isset($initialSourceArray[0]['quelle_id']) AND $initialSourceArray[0]['quelle_id'] != "") ? $initialSourceArray[0]['quelle_id'] : null;
				$initialQuelle = (isset($initialSourceArray[0]['quelle']) AND $initialSourceArray[0]['quelle'] != "") ? $initialSourceArray[0]['quelle'] : null;


				$allComparedSourcers = array();
				$comparedSourcersOfInitialSource = array();
				$queryConditionForComparative = '';
				$queryCondition = '';
				$workingSourceIdsArr = array();
				$workingSourceIdsArr = $comparingSources;
				if($initialQuelleId != ""){
					array_push($workingSourceIdsArr, $initialQuelleId);
				}

				if(!empty($workingSourceIdsArr)){
					$returnedIds = getAllComparedSourceIds($workingSourceIdsArr);
					if(!empty($returnedIds)){
						foreach ($returnedIds as $IdVal) {
							if(!in_array($IdVal, $workingSourceIdsArr))
								array_push($workingSourceIdsArr, $IdVal);
						}
					}	
				}

				// Collecting initial source's already compared sources ids of initial source
				$initialQuelleIdInArr = explode(',', $initialQuelleId);
				if(!empty($initialQuelleIdInArr)){
					$returnedIds = getAllComparedSourceIds($initialQuelleIdInArr);
					if(!empty($returnedIds)){
						foreach ($returnedIds as $IdVal) {
							if(!in_array($IdVal, $comparedSourcersOfInitialSource))
								array_push($comparedSourcersOfInitialSource, $IdVal);
						}
					}	
				}

				$newComparedSourcersOfInitialSource = array();
				foreach ($workingSourceIdsArr as $wKey => $wVal) {
					if(!in_array($wVal, $comparedSourcersOfInitialSource))
						array_push($newComparedSourcersOfInitialSource, $wVal);
				}

				$conditionIds = (!empty($workingSourceIdsArr)) ? rtrim(implode(',', $workingSourceIdsArr), ',') : null;
				$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;
				if($conditionIds != "")
					$queryCondition .= " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
				if($conditionIdsForComparative != "")
					$queryConditionForComparative .= " AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative."))";

				if($arzneiId != ""){
					$queryCondition .= " AND source_arznei_id = '".$arzneiId."'"; 
					$queryConditionForComparative .= " AND source_arznei_id = '".$arzneiId."'"; 
				}


				// Collecting appended symptoms of this saved comparison
				$approvedAppendedSymptoms = array();
				$appendedSymptomBackupQuery = mysqli_query($db,"SELECT symptom_id FROM appended_symptoms_backup WHERE saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
				if(mysqli_num_rows($appendedSymptomBackupQuery) > 0){
					while($appendedSymRow = mysqli_fetch_array($appendedSymptomBackupQuery)){
						array_push($approvedAppendedSymptoms, $appendedSymRow['symptom_id']);
					}
				}

				if($step <= $totalBatchesInPart1){

					// Process Stage
					$processStage = 1;

					$totalIniSymptomQuery = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE quelle_id = '".$initialSource."' AND arznei_id = '".$arzneiId."'");
					$totalInitialSymptom = mysqli_num_rows($totalIniSymptomQuery);
					if($totalInitialSymptom > 0){
						$limit = 20;
						$totalBatchesInPart1 = ceil($totalInitialSymptom / $limit);

						$pageno = (int)$step;
						if ($pageno > $totalBatchesInPart1) {
						   $pageno = $totalBatchesInPart1;
						} 
						if ($pageno < 1) {
						   $pageno = 1;
						} 
						$offset = ($pageno - 1)  * $limit;

						// Progress bar percentage
						$progress_percentage = round(($step / $totalBatchesInPart1) * 100);

						$InitialQuelleResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.Kommentar, quelle_import_test.Fussnote, quelle_import_test.is_symptom_appended FROM quelle_import_test WHERE quelle_import_test.quelle_id = '".$initialQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."' LIMIT ".$offset.", ".$limit);
						if(mysqli_num_rows($InitialQuelleResult) > 0){
							while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){

								if($iniSymRow['is_symptom_appended'] == 1 AND !in_array($iniSymRow['id'], $approvedAppendedSymptoms))
									continue;

								$iniHasConnections = 0;
								$isFurtherConnectionsAreSaved = 1;
								$is_paste_disabled = 0;
								$is_ns_paste_disabled = 1;
								$is_connect_disabled = 0;
								$is_ns_connect_disabled = 1;
								$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$iniSymRow['id']."' OR comparing_source_symptom_id = '".$iniSymRow['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryCondition);
								if(mysqli_num_rows($ceheckConnectionResult) > 0){
									$iniHasConnections = 1;
									while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
										if($checkConRow['is_saved'] == 0){
											$isFurtherConnectionsAreSaved = 0;
											break;
										}
									}
								}

								// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
								if($iniHasConnections == 0)    
									$iniSymRow['is_final_version_available'] = 0;

								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								if($iniSymRow['is_final_version_available'] != 0){
									$iniSymptomString_de =  $iniSymRow['final_version_de'];
									$iniSymptomString_en =  $iniSymRow['final_version_en'];
								} else {
									if($comparisonOption == 1){
										$iniSymptomString_de =  $iniSymRow['searchable_text_de'];
										$iniSymptomString_en =  $iniSymRow['searchable_text_en'];
									}
									else{
										$iniSymptomString_de =  $iniSymRow['BeschreibungFull_de'];
										$iniSymptomString_en =  $iniSymRow['BeschreibungFull_en'];
									}
								}

								// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
								$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
								$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
								
								// Apply dynamic conversion (this string is used in displying the symptom)
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
									$iniSymptomString_de = base64_encode($iniSymptomString_de);	
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
									$iniSymptomString_en = base64_encode($iniSymptomString_en);	
								}

								// get Origin Jahr/Year
								$originInitialSourceYear = "";
								$originInitialSourceLanguage = "";
								$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$iniSymRow['original_quelle_id']."'");
								if(mysqli_num_rows($originInitialQuelleResult) > 0){
									$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
									$originInitialSourceYear = $originIniQuelleRow['jahr'];
									if($originIniQuelleRow['sprache'] == "deutsch")
										$originInitialSourceLanguage = "de";
									else if($originIniQuelleRow['sprache'] == "englisch") 
										$originInitialSourceLanguage = "en";
								}

								$setArzneiId = $arzneiId;
								$setInitialQuelleId= $initialQuelleId;
								$setInitialOriginalSourceId= $iniSymRow['original_quelle_id'];
								$setInitialQuelleCode = $iniSymRow['quelle_code'];
								$setInitialSavedVersionSourceCode = $initialQuelle;
								$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
								$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
								$setInitialSymptomString_de = $iniSymptomString_de;
								$setInitialSymptomString_en = $iniSymptomString_en;
								$setInitialSymptomId = $iniSymRow['id'];
								$setInitialKommentar = $iniSymRow['Kommentar'];
								$setInitialFussnote = $iniSymRow['Fussnote'];
								$setIsFinalVersionAvailable = $iniSymRow['is_final_version_available'];
								$setInitialSourceYear = $originInitialSourceYear;
								$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;


								// 
								// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there. 
								$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND original_symptom_id = '".$iniSymRow['id']."'");
				            	if($backupConnectedSymptomQuery->num_rows > 0){
				            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

				            		// $setInitialQuelleId = $rowData['original_quelle_id'];
									$setInitialQuelleCode = $rowData['quelle_code'];

									// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
									if($iniHasConnections == 0)    
										$rowData['is_final_version_available'] = 0;

									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($rowData['is_final_version_available'] != 0){
										$iniSymptomString_de =  $rowData['final_version_de'];
										$iniSymptomString_en =  $rowData['final_version_en'];
									} else {
										if($comparisonOption == 1){
											$iniSymptomString_de =  $rowData['searchable_text_de'];
											$iniSymptomString_en =  $rowData['searchable_text_en'];
										}
										else{
											$iniSymptomString_de =  $rowData['BeschreibungFull_de'];
											$iniSymptomString_en =  $rowData['BeschreibungFull_en'];
										}
									}

									// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
									$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
									$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
									
									// Apply dynamic conversion (this string is used in displying the symptom)
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
										$iniSymptomString_de = base64_encode($iniSymptomString_de);	
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
										$iniSymptomString_en = base64_encode($iniSymptomString_en);	
									}

									// get Origin Jahr/Year
									$originInitialSourceYear = "";
									$originInitialSourceLanguage = "";
									$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$rowData['original_quelle_id']."'");
									if(mysqli_num_rows($originInitialQuelleResult) > 0){
										$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
										$originInitialSourceYear = $originIniQuelleRow['jahr'];
										if($originIniQuelleRow['sprache'] == "deutsch")
											$originInitialSourceLanguage = "de";
										else if($originIniQuelleRow['sprache'] == "englisch") 
											$originInitialSourceLanguage = "en";
									}
									

									$setInitialOriginalSourceId= $rowData['original_quelle_id'];
									$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
									$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
									$setInitialSymptomString_de = $iniSymptomString_de;
									$setInitialSymptomString_en = $iniSymptomString_en;
									$setInitialSymptomId = $rowData['original_symptom_id'];
									$setInitialKommentar = $rowData['Kommentar'];
									$setInitialFussnote = $rowData['Fussnote'];
									$setIsFinalVersionAvailable = $rowData['is_final_version_available'];
									$setInitialSourceYear = $originInitialSourceYear;
									$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;
				            	}
				            	else
				            	{
				            		// 
				            		// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            		$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$iniSymRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
									if(mysqli_num_rows($swappedSymptomResult) > 0){
										$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$iniSymRow['id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												// $setInitialQuelleId = $backupSetSymptomRow['original_quelle_id'];
												$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];

												// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
												if($iniHasConnections == 0)    
													$backupSetSymptomRow['is_final_version_available'] = 0;
									
												// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
												if($backupSetSymptomRow['is_final_version_available'] != 0){
													$iniSymptomString_de =  $backupSetSymptomRow['final_version_de'];
													$iniSymptomString_en =  $backupSetSymptomRow['final_version_en'];
												} else {
													if($comparisonOption == 1){
														$iniSymptomString_de =  $backupSetSymptomRow['searchable_text_de'];
														$iniSymptomString_en =  $backupSetSymptomRow['searchable_text_en'];
													}
													else{
														$iniSymptomString_de =  $backupSetSymptomRow['BeschreibungFull_de'];
														$iniSymptomString_en =  $backupSetSymptomRow['BeschreibungFull_en'];
													}
												}

												// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
												$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
												$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
												
												// Apply dynamic conversion (this string is used in displying the symptom)
												if($iniSymptomString_de != ""){
													$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
													$iniSymptomString_de = base64_encode($iniSymptomString_de);
												}
												if($iniSymptomString_en != ""){
													$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
													$iniSymptomString_en = base64_encode($iniSymptomString_en);
												}

												// get Origin Jahr/Year
												$originInitialSourceYear = "";
												$originInitialSourceLanguage = "";
												$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$backupSetSymptomRow['original_quelle_id']."'");
												if(mysqli_num_rows($originInitialQuelleResult) > 0){
													$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
													$originInitialSourceYear = $originIniQuelleRow['jahr'];
													if($originIniQuelleRow['sprache'] == "deutsch")
														$originInitialSourceLanguage = "de";
													else if($originIniQuelleRow['sprache'] == "englisch") 
														$originInitialSourceLanguage = "en";
												}

												$setInitialOriginalSourceId = $backupSetSymptomRow['original_quelle_id'];
												$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
												$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
												$setInitialSymptomString_de = $iniSymptomString_de;
												$setInitialSymptomString_en = $iniSymptomString_en;
												$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
												$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
												$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
												$setIsFinalVersionAvailable = $backupSetSymptomRow['is_final_version_available'];
												$setInitialSourceYear = $originInitialSourceYear;
												$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;
											}
										}
									}
				            	}


								// Checking for swapped data
								/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$iniSymRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
								if(mysqli_num_rows($swappedSymptomResult) > 0){
									$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
									$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
									if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0){
										$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);

										// $setInitialQuelleId = $backupSetSymptomRow['original_quelle_id'];
										$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];
										if($comparisonOption == 1)
											$iniSymptomString =  $backupSetSymptomRow['searchable_text'];
										else
											$iniSymptomString =  $backupSetSymptomRow['BeschreibungOriginal'];

										// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
										$iniSymptomStringBeforeConversion = base64_encode($iniSymptomString);
										
										// Apply dynamic conversion (this string is used in displying the symptom)
										$iniSymptomString = convertTheSymptom($iniSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
										$iniSymptomString = base64_encode($iniSymptomString);

										$setInitialOriginalSourceId= $backupSetSymptomRow['original_quelle_id'];
										$setInitialSymptomStringBeforeConversion = $iniSymptomStringBeforeConversion;
										$setInitialSymptomString = $iniSymptomString;
										$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
										$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
										$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
									} else {
										// Get the first symptom set from the backups of this comparison
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$iniSymRow['id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												// if(isset($backupSetSymptomRow['original_symptom_id']) AND $backupSetSymptomRow['original_symptom_id'] != ""){
												// 	$originalSymptomInfoResult = mysqli_query($db,"SELECT original_quelle_id, arznei_id FROM quelle_import_test WHERE id = '".$backupSetSymptomRow['original_symptom_id']."'");
												// 	if(mysqli_num_rows($originalSymptomInfoResult) > 0)
												// 		$originalSymptomInfoData = mysqli_fetch_assoc($originalSymptomInfoResult);
												// }
												// $initialOrzId = (isset($originalSymptomInfoData['original_quelle_id']) AND $originalSymptomInfoData['original_quelle_id'] != "") ? $originalSymptomInfoData['original_quelle_id'] : "";
												// $initialArzId = (isset($originalSymptomInfoData['arznei_id']) AND $originalSymptomInfoData['arznei_id'] != "") ? $originalSymptomInfoData['arznei_id'] : "";

												// $setInitialQuelleId = $backupSetSymptomRow['original_quelle_id'];
												$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];
												if($comparisonOption == 1)
													$iniSymptomString =  $backupSetSymptomRow['searchable_text'];
												else
													$iniSymptomString =  $backupSetSymptomRow['BeschreibungOriginal'];

												// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
												$iniSymptomStringBeforeConversion = base64_encode($iniSymptomString);
												
												// Apply dynamic conversion (this string is used in displying the symptom)
												$iniSymptomString = convertTheSymptom($iniSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
												$iniSymptomString = base64_encode($iniSymptomString);

												$setInitialOriginalSourceId = $backupSetSymptomRow['original_quelle_id'];
												$setInitialSymptomStringBeforeConversion = $iniSymptomStringBeforeConversion;
												$setInitialSymptomString = $iniSymptomString;
												$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
												$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
												$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
											}
										}
									}
								}*/


								$compareResultArray[] = array(
									"no_of_match" => 0,
									"percentage" => 0,
									"comparison_initial_source_id" => $setInitialQuelleId,
									"source_arznei_id" => $setArzneiId,
									"initial_source_id" => $setInitialQuelleId,
									"initial_original_source_id" => $setInitialOriginalSourceId,
									"initial_source_code" => $setInitialQuelleCode,
									"initial_source_year" => $setInitialSourceYear,
									"initial_source_original_language" => $setInitialSourceOriginalLanguage,
									"initial_saved_version_source_code" => $setInitialSavedVersionSourceCode,
									"initial_source_symptom_highlighted_de" => $setInitialSymptomString_de,
									"initial_source_symptom_highlighted_en" => $setInitialSymptomString_en,
									"initial_source_symptom_de" => $setInitialSymptomString_de,
									"initial_source_symptom_en" => $setInitialSymptomString_en,
									"initial_source_symptom_before_conversion_highlighted_de" => $setInitialSymptomStringBeforeConversion_de,
									"initial_source_symptom_before_conversion_highlighted_en" => $setInitialSymptomStringBeforeConversion_en,
									"initial_source_symptom_before_conversion_de" => $setInitialSymptomStringBeforeConversion_de,
									"initial_source_symptom_before_conversion_en" => $setInitialSymptomStringBeforeConversion_en,
									"initial_source_symptom_id" => $setInitialSymptomId,
									"main_parent_initial_symptom_id" => $setInitialSymptomId,
									"connections_main_parent_symptom_id" => $setInitialSymptomId,
									"initial_source_symptom_comment" => $setInitialKommentar,
									"initial_source_symptom_footnote" => $setInitialFussnote,
									"comparing_source_id" => "",
									"comparing_original_source_id" => "",
									"comparing_source_code" => "",
									"comparing_source_year" => "",
									"comparing_source_original_language" => "",
									"comparing_saved_version_source_code" => "",
									"comparing_source_symptom_highlighted_de" => "",
									"comparing_source_symptom_highlighted_en" => "",
									"comparing_source_symptom_de" => "",
									"comparing_source_symptom_en" => "",
									"comparing_source_symptom_before_conversion_highlighted_de" => "",
									"comparing_source_symptom_before_conversion_highlighted_en" => "",
									"comparing_source_symptom_before_conversion_de" => "",
									"comparing_source_symptom_before_conversion_en" => "",
									"comparing_source_symptom_id" => "",
									"comparing_source_symptom_comment" => "",
									"comparing_source_symptom_footnote" => "",
									"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
									// "main_initial_symptom_id" => $iniSymRow['id'],
									"has_connections" => $iniHasConnections,
									"is_final_version_available" => $setIsFinalVersionAvailable,
									"is_further_connections_are_saved" => $isFurtherConnectionsAreSaved,
									"is_pasted" => 0,
									"is_ns_paste" => 0,
									"ns_paste_note" => "",
									"is_initial_source" => 1,
									"active_symptom_type" => "initial",
									"similarity_rate" => $similarityRate,
									"comparison_option" => $comparisonOption,
									"is_unmatched_symptom" => 0,
									"is_paste_disabled" => $is_paste_disabled,
									"is_ns_paste_disabled" => $is_ns_paste_disabled,
									"is_connect_disabled" => $is_connect_disabled,
									"is_ns_connect_disabled" => $is_ns_connect_disabled
								);

								if(!empty($comparingSourcesArray)){
									$matchedSymptomArray = array();

									// Comparing with the main initial source symptoms START
									foreach($comparingSourcesArray as $comSrcKey => $comSrcVal){
										$comparingQuelleId = $comparingSourcesArray[$comSrcKey]['quelle_id'];
										$comparingQuelle = $comparingSourcesArray[$comSrcKey]['quelle'];
										$quelleComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote, quelle_import_test.is_symptom_appended FROM quelle_import_test WHERE quelle_import_test.quelle_id = '".$comparingQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."'");
										while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){

											if($quelleComparingSymptomRow['is_symptom_appended'] == 1 AND !in_array($quelleComparingSymptomRow['id'], $approvedAppendedSymptoms))
												continue;

											$comHasConnections = 0;
											$isFurtherConnectionsAreSaved = 1;
											$is_paste_disabled = 0;
											$is_ns_paste_disabled = 1;
											$is_connect_disabled = 0;
											$is_ns_connect_disabled = 1;
											$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND (initial_source_symptom_id != '".$iniSymRow['id']."' AND comparing_source_symptom_id != '".$iniSymRow['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative);
											if(mysqli_num_rows($ceheckConnectionResult) > 0){
												$comHasConnections = 1;
												while($conRow = mysqli_fetch_array($ceheckConnectionResult)){
													if($conRow['is_saved'] == 0)
														$isFurtherConnectionsAreSaved = 0;

													if($conRow['initial_source_id'] == $initialQuelleId OR $conRow['comparing_source_id'] == $initialQuelleId){
														if($conRow['is_connected'] == 1)
														{
															$is_paste_disabled = 1;
														}
														else if($conRow['is_pasted'] == 1) 
														{
															$is_connect_disabled = 1;
															$is_paste_disabled = 1;
														}
													}
												}
											}
											// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
											if($comHasConnections == 0)    
												$quelleComparingSymptomRow['is_final_version_available'] = 0;

											// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
											if($quelleComparingSymptomRow['is_final_version_available'] != 0){
												$compSymptomString_de =  $quelleComparingSymptomRow['final_version_de'];
												$compSymptomString_en =  $quelleComparingSymptomRow['final_version_en'];
											} else {
												if($comparisonOption == 1){
													$compSymptomString_de =  $quelleComparingSymptomRow['searchable_text_de'];
													$compSymptomString_en =  $quelleComparingSymptomRow['searchable_text_en'];
												}
												else{
													$compSymptomString_de =  $quelleComparingSymptomRow['BeschreibungFull_de'];
													$compSymptomString_en =  $quelleComparingSymptomRow['BeschreibungFull_en'];
												}
											}

											// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
											$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
											$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

											// Apply dynamic conversion
											if($compSymptomString_de != "") {
												$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
												$compSymptomString_de = base64_encode($compSymptomString_de);
											}
											if($compSymptomString_en != "") {
												$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
												$compSymptomString_en = base64_encode($compSymptomString_en);
											}

											// get Origin Jahr/Year
											$originComparingSourceYear = "";
											$originComparingSourceLanguage = "";
											$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$quelleComparingSymptomRow['original_quelle_id']."'");
											if(mysqli_num_rows($originComparingQuelleResult) > 0){
												$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
												$originComparingSourceYear = $originComQuelleRow['jahr'];
												if($originComQuelleRow['sprache'] == "deutsch")
													$originComparingSourceLanguage = "de";
												else if($originComQuelleRow['sprache'] == "englisch") 
													$originComparingSourceLanguage = "en";
											}

											$setComparingOriginalSourceId = $quelleComparingSymptomRow['original_quelle_id'];
											$setComparingQuelleCode = $quelleComparingSymptomRow['quelle_code'];
											$setComparingSavedVersionSourceCode = $comparingQuelle;
											$setComparingSymptomStringBeforeConversion_de = $compSymptomStringBeforeConversion_de;
											$setComparingSymptomStringBeforeConversion_en = $compSymptomStringBeforeConversion_en;
											$setComparingSymptomString_de = $compSymptomString_de;
											$setComparingSymptomString_en = $compSymptomString_en;
											$setComparingSymptomId = $quelleComparingSymptomRow['id'];
											$setComparingKommentar = $quelleComparingSymptomRow['Kommentar'];
											$setComparingFussnote = $quelleComparingSymptomRow['Fussnote'];
											$setComparingIsFinalVersionAvailable = $quelleComparingSymptomRow['is_final_version_available'];
											$setComparingSourceYear = $originComparingSourceYear;
											$setComparingSourceOriginalLanguage = $originComparingSourceLanguage;

											// 
											// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there. 
											$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND original_symptom_id = '".$quelleComparingSymptomRow['id']."'");
							            	if($backupConnectedSymptomQuery->num_rows > 0){
							            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

							            		$setComparingQuelleCode = $rowData['quelle_code'];

							            		// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
												if($comHasConnections == 0)    
													$rowData['is_final_version_available'] = 0;

							            		// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
												if($rowData['is_final_version_available'] != 0){
													$compSymptomString_de =  $rowData['final_version_de'];
													$compSymptomString_en =  $rowData['final_version_en'];
												} else {
													if($comparisonOption == 1){
														$compSymptomString_de =  $rowData['searchable_text_de'];
														$compSymptomString_en =  $rowData['searchable_text_en'];
													}
													else{
														$compSymptomString_de =  $rowData['BeschreibungFull_de'];
														$compSymptomString_en =  $rowData['BeschreibungFull_en'];
													}
												}

												// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
												$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
												$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

												// Apply dynamic conversion
												if($compSymptomString_de != "") {
													$compSymptomString_de = convertTheSymptom($compSymptomString_de, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
													$compSymptomString_de = base64_encode($compSymptomString_de);
												}
												if($compSymptomString_en != "") {
													$compSymptomString_en = convertTheSymptom($compSymptomString_en, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
													$compSymptomString_en = base64_encode($compSymptomString_en);
												}
												
												// get Origin Jahr/Year
												$originComparingSourceYear = "";
												$originComparingSourceLanguage = "";
												$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$rowData['original_quelle_id']."'");
												if(mysqli_num_rows($originComparingQuelleResult) > 0){
													$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
													$originComparingSourceYear = $originComQuelleRow['jahr'];
													if($originComQuelleRow['sprache'] == "deutsch")
														$originComparingSourceLanguage = "de";
													else if($originComQuelleRow['sprache'] == "englisch") 
														$originComparingSourceLanguage = "en";
												}

												$setComparingOriginalSourceId = $rowData['original_quelle_id'];
												$setComparingSymptomStringBeforeConversion_de = $compSymptomStringBeforeConversion_de;
												$setComparingSymptomStringBeforeConversion_en = $compSymptomStringBeforeConversion_en;
												$setComparingSymptomString_de = $compSymptomString_de;
												$setComparingSymptomString_en = $compSymptomString_en;
												$setComparingSymptomId = $rowData['original_symptom_id'];
												$setComparingKommentar = $rowData['Kommentar'];
												$setComparingFussnote = $rowData['Fussnote'];
												$setComparingIsFinalVersionAvailable = $rowData['is_final_version_available'];
												$setComparingSourceYear = $originComparingSourceYear;
												$setComparingSourceOriginalLanguage = $originComparingSourceLanguage;

							            	}
							            	else
							            	{
							            		// 
				            					// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            					$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$quelleComparingSymptomRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
												if(mysqli_num_rows($swappedSymptomResult) > 0){
													$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$quelleComparingSymptomRow['id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															$setComparingQuelleCode = $backupSetSymptomRow['quelle_code'];

															// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
															if($comHasConnections == 0)    
																$backupSetSymptomRow['is_final_version_available'] = 0;

															// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
															if($backupSetSymptomRow['is_final_version_available'] != 0){
																$compSymptomString_de =  $backupSetSymptomRow['final_version_de'];
																$compSymptomString_en =  $backupSetSymptomRow['final_version_en'];
															} else {
																if($comparisonOption == 1){
																	$compSymptomString_de =  $backupSetSymptomRow['searchable_text_de'];
																	$compSymptomString_en =  $backupSetSymptomRow['searchable_text_en'];
																}
																else{
																	$compSymptomString_de =  $backupSetSymptomRow['BeschreibungFull_de'];
																	$compSymptomString_en =  $backupSetSymptomRow['BeschreibungFull_en'];
																}
															}
															
															// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
															$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
															$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

															// Apply dynamic conversion
															if($compSymptomString_de != "") {
																$compSymptomString_de = convertTheSymptom($compSymptomString_de, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
																$compSymptomString_de = base64_encode($compSymptomString_de);	
															}
															if($compSymptomString_en != "") {
																$compSymptomString_en = convertTheSymptom($compSymptomString_en, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
																$compSymptomString_en = base64_encode($compSymptomString_en);	
															}
															
															// get Origin Jahr/Year
															$originComparingSourceYear = "";
															$originComparingSourceLanguage = "";
															$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$backupSetSymptomRow['original_quelle_id']."'");
															if(mysqli_num_rows($originComparingQuelleResult) > 0){
																$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
																$originComparingSourceYear = $originComQuelleRow['jahr'];
																if($originComQuelleRow['sprache'] == "deutsch")
																	$originComparingSourceLanguage = "de";
																else if($originComQuelleRow['sprache'] == "englisch") 
																	$originComparingSourceLanguage = "en";
															}

															$setComparingOriginalSourceId = $backupSetSymptomRow['original_quelle_id'];
															$setComparingSymptomStringBeforeConversion_de = $compSymptomStringBeforeConversion_de;
															$setComparingSymptomStringBeforeConversion_en = $compSymptomStringBeforeConversion_en;
															$setComparingSymptomString_de = $compSymptomString_de;
															$setComparingSymptomString_en = $compSymptomString_en;
															$setComparingSymptomId = $backupSetSymptomRow['original_symptom_id'];
															$setComparingKommentar = $backupSetSymptomRow['Kommentar'];
															$setComparingFussnote = $backupSetSymptomRow['Fussnote'];
															$setComparingIsFinalVersionAvailable = $backupSetSymptomRow['is_final_version_available'];
															$setComparingSourceYear = $originComparingSourceYear;
															$setComparingSourceOriginalLanguage = $originComparingSourceLanguage;
														}
													}
												}
							            	}



											/*// Checking for swapped data
											$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$quelleComparingSymptomRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
											if(mysqli_num_rows($swappedSymptomResult) > 0){
												$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
												$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
												if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0){
													$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);

													$setComparingQuelleCode = $backupSetSymptomRow['quelle_code'];
													if($comparisonOption == 1)
														$compSymptomString =  $backupSetSymptomRow['searchable_text'];
													else
														$compSymptomString =  $backupSetSymptomRow['BeschreibungOriginal'];

													// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
													$compSymptomStringBeforeConversion = base64_encode($compSymptomString);

													// Apply dynamic conversion
													$compSymptomString = convertTheSymptom($compSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
													$compSymptomString = base64_encode($compSymptomString);

													$setComparingOriginalSourceId = $backupSetSymptomRow['original_quelle_id'];
													$setComparingSymptomStringBeforeConversion = $compSymptomStringBeforeConversion;
													$setComparingSymptomString = $compSymptomString;
													$setComparingSymptomId = $backupSetSymptomRow['original_symptom_id'];
													$setComparingKommentar = $backupSetSymptomRow['Kommentar'];
													$setComparingFussnote = $backupSetSymptomRow['Fussnote'];
												} else {
													// Get the first symptom set from the backups of this comparison
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$quelleComparingSymptomRow['id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															// if(isset($backupSetSymptomRow['original_symptom_id']) AND $backupSetSymptomRow['original_symptom_id'] != ""){
															// 	$originalSymptomInfoResult = mysqli_query($db,"SELECT original_quelle_id, arznei_id FROM quelle_import_test WHERE id = '".$backupSetSymptomRow['original_symptom_id']."'");
															// 	if(mysqli_num_rows($originalSymptomInfoResult) > 0)
															// 		$originalSymptomInfoData = mysqli_fetch_assoc($originalSymptomInfoResult);
															// }
															// $comOrzId = (isset($originalSymptomInfoData['original_quelle_id']) AND $originalSymptomInfoData['original_quelle_id'] != "") ? $originalSymptomInfoData['original_quelle_id'] : "";
															// $comArzId = (isset($originalSymptomInfoData['arznei_id']) AND $originalSymptomInfoData['arznei_id'] != "") ? $originalSymptomInfoData['arznei_id'] : "";

															$setComparingQuelleCode = $backupSetSymptomRow['quelle_code'];
															if($comparisonOption == 1)
																$compSymptomString =  $backupSetSymptomRow['searchable_text'];
															else
																$compSymptomString =  $backupSetSymptomRow['BeschreibungPlain'];
															
															// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
															$compSymptomStringBeforeConversion = base64_encode($compSymptomString);

															// Apply dynamic conversion
															$compSymptomString = convertTheSymptom($compSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
															$compSymptomString = base64_encode($compSymptomString);

															$setComparingOriginalSourceId = $backupSetSymptomRow['original_quelle_id'];
															$setComparingSymptomStringBeforeConversion = $compSymptomStringBeforeConversion;
															$setComparingSymptomString = $compSymptomString;
															$setComparingSymptomId = $backupSetSymptomRow['original_symptom_id'];
															$setComparingKommentar = $backupSetSymptomRow['Kommentar'];
															$setComparingFussnote = $backupSetSymptomRow['Fussnote'];
														}
														else
														{
															// When the symptom is not there in quelle_import_backup table check backup connection table may be this symptom was a connected symptom in this backup set.
															$connectedSymptomInfo = mysqli_query($db, "SELECT id, initial_source_type, comparing_source_type, source_arznei_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative." LIMIT 0, 1");
															if(mysqli_num_rows($connectedSymptomInfo) > 0){
																$connectedSymptomRow = mysqli_fetch_assoc($connectedSymptomInfo);
																if($connectedSymptomRow['initial_source_symptom_id'] == $quelleComparingSymptomRow['id']) {
																	// Apply dynamic conversion
																	$compSymptomString = convertTheSymptom($connectedSymptomRow['initial_source_symptom'], $connectedSymptomRow['conversion_initial_source_id'], $connectedSymptomRow['source_arznei_id']);
																	$compSymptomString = base64_encode($compSymptomString);

																	$setComparingQuelleCode = $connectedSymptomRow['initial_source_code'];
																	$setComparingOriginalSourceId = $connectedSymptomRow['conversion_initial_source_id'];
																	$setComparingSymptomStringBeforeConversion = base64_encode($connectedSymptomRow['initial_source_symptom']);
																	$setComparingSymptomString = $compSymptomString;
																	$setComparingSymptomId = $connectedSymptomRow['initial_source_symptom_id'];
																} else {
																	// Apply dynamic conversion
																	$compSymptomString = convertTheSymptom($connectedSymptomRow['comparing_source_symptom'], $connectedSymptomRow['conversion_comparing_source_id'], $connectedSymptomRow['source_arznei_id']);
																	$compSymptomString = base64_encode($compSymptomString);

																	$setComparingQuelleCode = $connectedSymptomRow['comparing_source_code'];
																	$setComparingOriginalSourceId = $connectedSymptomRow['conversion_comparing_source_id'];
																	$setComparingSymptomStringBeforeConversion = base64_encode($connectedSymptomRow['comparing_source_symptom']);
																	$setComparingSymptomString = $compSymptomString;
																	$setComparingSymptomId = $connectedSymptomRow['comparing_source_symptom_id'];
																}
															}
														}
													}
												}
											}*/

											$connectedSymptomResult = mysqli_query($db, "SELECT id FROM symptom_connections_backup WHERE ((initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND (initial_source_symptom_id = '".$iniSymRow['id']."' OR comparing_source_symptom_id = '".$iniSymRow['id']."')) AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative);
											if(mysqli_num_rows($connectedSymptomResult) > 0){
												// if(!in_array($quelleComparingSymptomRow['id'], $matchedSymptomIds))
												array_push($matchedSymptomIds, $quelleComparingSymptomRow['id']);
											}
											else
											{
												if($comparisonLanguage == "en"){
													// English
													$resultArray = comareSymptom2($setInitialSymptomString_en, $setComparingSymptomString_en, $setInitialSymptomStringBeforeConversion_en, $setComparingSymptomStringBeforeConversion_en);
													$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
													$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
													$initial_source_symptom_highlighted_en = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
													// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
													$comparing_source_symptom_highlighted_en = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
													// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
													$initial_source_symptom_before_conversion_highlighted_en = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : null;
													$comparing_source_symptom_before_conversion_highlighted_en = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : null;

													// German
													$initial_source_symptom_highlighted_de = (isset($setInitialSymptomString_de)) ? $setInitialSymptomString_de : null;
													$comparing_source_symptom_highlighted_de = (isset($setComparingSymptomString_de)) ? $setComparingSymptomString_de : null;
													$initial_source_symptom_before_conversion_highlighted_de = (isset($setInitialSymptomString_de)) ? $setInitialSymptomString_de : null;
													$comparing_source_symptom_before_conversion_highlighted_de = (isset($setComparingSymptomString_de)) ? $setComparingSymptomString_de : null;
												} else {
													// German
													$resultArray = comareSymptom2($setInitialSymptomString_de, $setComparingSymptomString_de, $setInitialSymptomStringBeforeConversion_de, $setComparingSymptomStringBeforeConversion_de);
													$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
													$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
													$initial_source_symptom_highlighted_de = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
													// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
													$comparing_source_symptom_highlighted_de = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
													// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
													$initial_source_symptom_before_conversion_highlighted_de = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : null;
													$comparing_source_symptom_before_conversion_highlighted_de = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : null;

													// English
													$initial_source_symptom_highlighted_en = (isset($setInitialSymptomString_en)) ? $setInitialSymptomString_en : null;
													$comparing_source_symptom_highlighted_en = (isset($setComparingSymptomString_en)) ? $setComparingSymptomString_en : null;
													$initial_source_symptom_before_conversion_highlighted_en = (isset($setInitialSymptomString_en)) ? $setInitialSymptomString_en : null;
													$comparing_source_symptom_before_conversion_highlighted_en = (isset($setComparingSymptomString_en)) ? $setComparingSymptomString_en : null;
												}

												if($percentage >= $similarityRate){
													// if(!in_array($quelleComparingSymptomRow['id'], $matchedSymptomIds))
													array_push($matchedSymptomIds, $quelleComparingSymptomRow['id']);

													/* $comHasConnections = 0;
													$isFurtherConnectionsAreSaved = 1;
													$is_paste_disabled = 0;
													$is_ns_paste_disabled = 1;
													$is_connect_disabled = 0;
													$is_ns_connect_disabled = 1;
													$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND (initial_source_symptom_id != '".$iniSymRow['id']."' AND comparing_source_symptom_id != '".$iniSymRow['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative);
													if(mysqli_num_rows($ceheckConnectionResult) > 0){
														$comHasConnections = 1;
														while($conRow = mysqli_fetch_array($ceheckConnectionResult)){
															if($conRow['is_saved'] == 0)
																$isFurtherConnectionsAreSaved = 0;

															if($conRow['initial_source_id'] == $initialQuelleId OR $conRow['comparing_source_id'] == $initialQuelleId){
																if($conRow['is_connected'] == 1)
																{
																	$is_paste_disabled = 1;
																}
																else if($conRow['is_pasted'] == 1) 
																{
																	$is_connect_disabled = 1;
																	$is_paste_disabled = 1;
																}
															}
														}
													} */

													$matchedSymptomArray[] = array(
														"no_of_match" => $no_of_match,
														"percentage" => $percentage,
														"comparison_initial_source_id" => $setInitialQuelleId,
														"source_arznei_id" => $setArzneiId,
														"initial_source_id" => $setInitialQuelleId,
														"initial_original_source_id" => $setInitialOriginalSourceId,
														"initial_source_code" => $setInitialQuelleCode,
														"initial_source_year" => $setInitialSourceYear,
														"initial_source_original_language" => $setInitialSourceOriginalLanguage,
														"initial_saved_version_source_code" => $setInitialSavedVersionSourceCode,
														"initial_source_symptom_highlighted_de" => $initial_source_symptom_highlighted_de,
														"initial_source_symptom_highlighted_en" => $initial_source_symptom_highlighted_en,
														"initial_source_symptom_de" => $setInitialSymptomString_de,
														"initial_source_symptom_en" => $setInitialSymptomString_en,
														"initial_source_symptom_before_conversion_highlighted_de" => $initial_source_symptom_before_conversion_highlighted_de,
														"initial_source_symptom_before_conversion_highlighted_en" => $initial_source_symptom_before_conversion_highlighted_en,
														"initial_source_symptom_before_conversion_de" => $setInitialSymptomStringBeforeConversion_de,
														"initial_source_symptom_before_conversion_en" => $setInitialSymptomStringBeforeConversion_en,
														"initial_source_symptom_id" => $setInitialSymptomId,
														"main_parent_initial_symptom_id" => $setInitialSymptomId,
														"connections_main_parent_symptom_id" => $setComparingSymptomId,
														"initial_source_symptom_comment" => $setInitialKommentar,
														"initial_source_symptom_footnote" => $setInitialFussnote,
														"comparing_source_id" => $comparingQuelleId,
														"comparing_original_source_id" => $setComparingOriginalSourceId,
														"comparing_source_code" => $setComparingQuelleCode,
														"comparing_source_year" => $setComparingSourceYear,
														"comparing_source_original_language" => $setComparingSourceOriginalLanguage,
														"comparing_saved_version_source_code" => $setComparingSavedVersionSourceCode,
														"comparing_source_symptom_highlighted_de" => $comparing_source_symptom_highlighted_de,
														"comparing_source_symptom_highlighted_en" => $comparing_source_symptom_highlighted_en,
														"comparing_source_symptom_de" => $setComparingSymptomString_de,
														"comparing_source_symptom_en" => $setComparingSymptomString_en,
														"comparing_source_symptom_before_conversion_highlighted_de" => $comparing_source_symptom_before_conversion_highlighted_de,
														"comparing_source_symptom_before_conversion_highlighted_en" => $comparing_source_symptom_before_conversion_highlighted_en,
														"comparing_source_symptom_before_conversion_de" => $setComparingSymptomStringBeforeConversion_de,
														"comparing_source_symptom_before_conversion_en" => $setComparingSymptomStringBeforeConversion_en,
														"comparing_source_symptom_id" => $setComparingSymptomId,
														"comparing_source_symptom_comment" => $setComparingKommentar,
														"comparing_source_symptom_footnote" => $setComparingFussnote,
														"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
														// "main_initial_symptom_id" => $iniSymRow['id'],
														"has_connections" => $comHasConnections,
														"is_final_version_available" => $setComparingIsFinalVersionAvailable,
														"is_further_connections_are_saved" => $isFurtherConnectionsAreSaved,
														"is_pasted" => 0,
														"is_ns_paste" => 0,
														"ns_paste_note" => "",
														"is_initial_source" => 0,
														"active_symptom_type" => "comparing",
														"similarity_rate" => $similarityRate,
														"comparison_option" => $comparisonOption,
														"is_unmatched_symptom" => 0,
														"is_paste_disabled" => $is_paste_disabled,
														"is_ns_paste_disabled" => $is_ns_paste_disabled,
														"is_connect_disabled" => $is_connect_disabled,
														"is_ns_connect_disabled" => $is_ns_connect_disabled
													);
												}
											}
										}
									}
									// Comparing with the main initial source symptoms END

									if(!empty($matchedSymptomArray)){
										// Short the matched symptoms Percentage DESC
										$per = array();
										foreach ($matchedSymptomArray as $key => $row)
										{
										    $per[$key] = $row['percentage'];
										}
										array_multisort($per, SORT_DESC, $matchedSymptomArray);
										foreach ($matchedSymptomArray as $key => $value) {
											$compareResultArray[] = $value;
										}
										// array_push($compareResultArray, $matchedSymptomArray);
									}
								}
								// Comparing source check end
							}
						}
					}
					else{
						$totalBatchesInPart1 = 0;
						$progress_percentage = 100;
					}
					$step = $step + 1;
				}
				else if($isStage2Checked == 0 OR $step <= $totalBatchesInPart2+$totalBatchesInPart1)
				{
					$isStage2Checked = 1;
					// Rest of the unmatch symptoms 
					$pageno = (int)$step - (int)$totalBatchesInPart1;
					// Process Stage
					$processStage = 2;

					$escapeSymptomCondition = "";
					if(!empty($matchedSymptomIds)){
						$uniqueMatchedSymptomIds = array_unique($matchedSymptomIds);
						$matchedSymptomIdsString = implode(',', $uniqueMatchedSymptomIds);
						$escapeSymptomCondition = "AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")";
					}

					if($comparingSourceWhereIn != ""){
						$restOfComparingSymptomResultCount = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition);
						$totalRestOfSymptoms = mysqli_num_rows($restOfComparingSymptomResultCount);
						if($totalRestOfSymptoms > 0){
							$un_matched_symptoms_set_number++;
							$limit = 20;
							$totalBatchesInPart2 = ceil($totalRestOfSymptoms / $limit);

							// $pageno = (int)$totalBatchesInPart1 - (int)$step;
							if ($pageno > $totalBatchesInPart2) {
							   $pageno = $totalBatchesInPart2;
							} 
							if ($pageno < 1) {
							   $pageno = 1;
							} 
							$offset = ($pageno - 1)  * $limit;

							// Progress bar percentage
							$progress_percentage = round(($pageno / $totalBatchesInPart2) * 100);

							$restOfComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote, quelle_import_test.is_symptom_appended, quelle_import_test.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.quelle_id = quelle.quelle_id WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition." ORDER BY quelle.jahr ASC LIMIT ".$offset.", ".$limit);
							if(mysqli_num_rows($restOfComparingSymptomResult))
							{
								while($restOfComparingSymptomRow = mysqli_fetch_array($restOfComparingSymptomResult)){

									if($restOfComparingSymptomRow['is_symptom_appended'] == 1 AND !in_array($restOfComparingSymptomRow['id'], $approvedAppendedSymptoms))
										continue;

									$comHasConnections = 0;
									$isFurtherConnectionsAreSaved = 1;
									$is_paste_disabled = 0;
									$is_ns_paste_disabled = 1;
									$is_connect_disabled = 0;
									$is_ns_connect_disabled = 1;
									$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$restOfComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$restOfComparingSymptomRow['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1)".$queryCondition);
									if(mysqli_num_rows($ceheckConnectionResult) > 0){
										$comHasConnections = 1;
										while($conRow = mysqli_fetch_array($ceheckConnectionResult)){
											if($conRow['is_saved'] == 0)
												$isFurtherConnectionsAreSaved = 0;

											if($conRow['initial_source_id'] == $initialQuelleId OR $conRow['comparing_source_id'] == $initialQuelleId){
												if($conRow['is_connected'] == 1)
												{
													$is_paste_disabled = 1;
												}
												else if($conRow['is_pasted'] == 1) 
												{
													$is_connect_disabled = 1;
													$is_paste_disabled = 1;
												}
											}
										}
									}

									// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
									if($comHasConnections == 0)    
										$restOfComparingSymptomRow['is_final_version_available'] = 0;

									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($restOfComparingSymptomRow['is_final_version_available'] != 0){
										$iniSymptomString_de =  $restOfComparingSymptomRow['final_version_de'];
										$iniSymptomString_en =  $restOfComparingSymptomRow['final_version_en'];
									} else {
										if($comparisonOption == 1){
											$iniSymptomString_de =  $restOfComparingSymptomRow['searchable_text_de'];
											$iniSymptomString_en =  $restOfComparingSymptomRow['searchable_text_en'];
										}
										else{
											$iniSymptomString_de =  $restOfComparingSymptomRow['BeschreibungFull_de'];
											$iniSymptomString_en =  $restOfComparingSymptomRow['BeschreibungFull_en'];
										}
									}

									// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
									$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
									$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

									// Apply dynamic conversion
									if($iniSymptomString_de != "") {
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $restOfComparingSymptomRow['original_quelle_id'], $restOfComparingSymptomRow['arznei_id'], $restOfComparingSymptomRow['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
										$iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != "") {
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $restOfComparingSymptomRow['original_quelle_id'], $restOfComparingSymptomRow['arznei_id'], $restOfComparingSymptomRow['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
										$iniSymptomString_en = base64_encode($iniSymptomString_en);
									}

									if($restOfComparingSymptomRow['quelle_type_id'] == 3)
										$preparedQuelleCode = $restOfComparingSymptomRow['code'];
									else{
										if($restOfComparingSymptomRow['jahr'] != "" AND $restOfComparingSymptomRow['code'] != "")
											$rowQuelleCode = trim(str_replace(trim($restOfComparingSymptomRow['jahr']), '', $restOfComparingSymptomRow['code']));
										else
											$rowQuelleCode = trim($restOfComparingSymptomRow['code']);
										$preparedQuelleCode = trim($rowQuelleCode." ".$restOfComparingSymptomRow['jahr']);
									}

									// get Origin Jahr/Year
									$originInitialSourceYear = "";
									$originInitialSourceLanguage = "";
									$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$restOfComparingSymptomRow['original_quelle_id']."'");
									if(mysqli_num_rows($originInitialQuelleResult) > 0){
										$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
										$originInitialSourceYear = $originIniQuelleRow['jahr'];
										if($originIniQuelleRow['sprache'] == "deutsch")
											$originInitialSourceLanguage = "de";
										else if($originIniQuelleRow['sprache'] == "englisch") 
											$originInitialSourceLanguage = "en";
									}

									$setInitialQuelleId= $restOfComparingSymptomRow['quelle_id'];
									$setInitialOriginalSourceId= $restOfComparingSymptomRow['original_quelle_id'];
									$setInitialQuelleCode = $restOfComparingSymptomRow['quelle_code'];
									$setInitialSavedVersionSourceCode = $preparedQuelleCode;
									$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
									$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
									$setInitialSymptomString_de = $iniSymptomString_de;
									$setInitialSymptomString_en = $iniSymptomString_en;
									$setInitialSymptomId = $restOfComparingSymptomRow['id'];
									$setInitialKommentar = $restOfComparingSymptomRow['Kommentar'];
									$setInitialFussnote = $restOfComparingSymptomRow['Fussnote']; 
									$setInitialIsFinalVersionAvailable = $restOfComparingSymptomRow['is_final_version_available']; 
									$setInitialSourceYear = $originInitialSourceYear;
									$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;


									// 
									// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
									$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND original_symptom_id = '".$restOfComparingSymptomRow['id']."'");
					            	if($backupConnectedSymptomQuery->num_rows > 0){
					            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

					            		$setInitialQuelleId = $rowData['quelle_id'];
										$setInitialQuelleCode = $rowData['quelle_code'];

										// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
										if($comHasConnections == 0)    
											$rowData['is_final_version_available'] = 0;

										// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										if($rowData['is_final_version_available'] != 0){
											$iniSymptomString_de =  $rowData['final_version_de'];
											$iniSymptomString_en =  $rowData['final_version_en'];
										} else {
											if($comparisonOption == 1){
												$iniSymptomString_de =  $rowData['searchable_text_de'];
												$iniSymptomString_en =  $rowData['searchable_text_en'];
											}
											else{
												$iniSymptomString_de =  $rowData['BeschreibungFull_de'];
												$iniSymptomString_en =  $rowData['BeschreibungFull_en'];
											}
										}

										// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
										$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
										$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

										// Apply dynamic conversion
										if($iniSymptomString_de != "") {
											$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
											$iniSymptomString_de = base64_encode($iniSymptomString_de);	
										}
										if($iniSymptomString_en != "") {
											$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
											$iniSymptomString_en = base64_encode($iniSymptomString_en);	
										}

										// get Origin Jahr/Year
										$originInitialSourceYear = "";
										$originInitialSourceLanguage = "";
										$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$rowData['original_quelle_id']."'");
										if(mysqli_num_rows($originInitialQuelleResult) > 0){
											$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
											$originInitialSourceYear = $originIniQuelleRow['jahr'];
											if($originIniQuelleRow['sprache'] == "deutsch")
												$originInitialSourceLanguage = "de";
											else if($originIniQuelleRow['sprache'] == "englisch") 
												$originInitialSourceLanguage = "en";
										}

										$setInitialOriginalSourceId = $rowData['original_quelle_id'];
										$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
										$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
										$setInitialSymptomString_de = $iniSymptomString_de;
										$setInitialSymptomString_en = $iniSymptomString_en;
										$setInitialSymptomId = $rowData['original_symptom_id'];
										$setInitialKommentar = $rowData['Kommentar'];
										$setInitialFussnote = $rowData['Fussnote'];
										$setInitialIsFinalVersionAvailable = $rowData['is_final_version_available']; 
										$setInitialSourceYear = $originInitialSourceYear;
										$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;
					            	}
					            	else
					            	{
					            		// 
				            			// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            			$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$restOfComparingSymptomRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
										if(mysqli_num_rows($swappedSymptomResult) > 0){
											$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
											// Here joining is made on backup table's quelle_id not with the original_quelle_id
											$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
											if(mysqli_num_rows($importMasterBackupResult) > 0){
												$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
												$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$restOfComparingSymptomRow['id']."'");
												if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
													$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

													$setInitialQuelleId = $backupSetSymptomRow['original_quelle_id'];
													$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];

													// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
													if($comHasConnections == 0)    
														$backupSetSymptomRow['is_final_version_available'] = 0;

													// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
													if($backupSetSymptomRow['is_final_version_available'] != 0){
														$iniSymptomString_de =  $backupSetSymptomRow['final_version_de'];
														$iniSymptomString_en =  $backupSetSymptomRow['final_version_en'];
													} else {
														if($comparisonOption == 1){
															$iniSymptomString_de =  $backupSetSymptomRow['searchable_text_de'];
															$iniSymptomString_en =  $backupSetSymptomRow['searchable_text_en'];
														}
														else{
															$iniSymptomString_de =  $backupSetSymptomRow['BeschreibungFull_de'];
															$iniSymptomString_en =  $backupSetSymptomRow['BeschreibungFull_en'];
														}
													}

													// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
													$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
													$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

													// Apply dynamic conversion
													if($iniSymptomString_de != "") {
														$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
														$iniSymptomString_de = base64_encode($iniSymptomString_de);
													}
													if($iniSymptomString_en != "") {
														$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']);
														$iniSymptomString_en = base64_encode($iniSymptomString_en);
													}

													// get Origin Jahr/Year
													$originInitialSourceYear = "";
													$originInitialSourceLanguage = "";
													$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$backupSetSymptomRow['original_quelle_id']."'");
													if(mysqli_num_rows($originInitialQuelleResult) > 0){
														$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
														$originInitialSourceYear = $originIniQuelleRow['jahr'];
														if($originIniQuelleRow['sprache'] == "deutsch")
															$originInitialSourceLanguage = "de";
														else if($originIniQuelleRow['sprache'] == "englisch") 
															$originInitialSourceLanguage = "en";
													}

													$setInitialOriginalSourceId= $backupSetSymptomRow['original_quelle_id'];
													$setInitialSymptomStringBeforeConversion_de = $iniSymptomStringBeforeConversion_de;
													$setInitialSymptomStringBeforeConversion_en = $iniSymptomStringBeforeConversion_en;
													$setInitialSymptomString_de = $iniSymptomString_de;
													$setInitialSymptomString_en = $iniSymptomString_en;
													$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
													$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
													$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
													$setInitialIsFinalVersionAvailable = $backupSetSymptomRow['is_final_version_available'];
													$setInitialSourceYear = $originInitialSourceYear;
													$setInitialSourceOriginalLanguage = $originInitialSourceLanguage;
												}
											}
										}
					            	}



									/*// Checking for swapped data
									$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$restOfComparingSymptomRow['id']."' AND comparison_initial_source_id = '".$initialSource."' AND comparison_comparing_source_ids = '".$comparingSourceIds."' AND arznei_id = '".$arzneiId."'");
									if(mysqli_num_rows($swappedSymptomResult) > 0){
										$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
										$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
										if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0){
											$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);

											$setInitialQuelleId = $backupSetSymptomRow['quelle_id'];
											$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];
											if($comparisonOption == 1)
												$iniSymptomString =  $backupSetSymptomRow['searchable_text'];
											else
												$iniSymptomString =  $backupSetSymptomRow['BeschreibungOriginal'];

											// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
											$iniSymptomStringBeforeConversion = base64_encode($iniSymptomString);

											// Apply dynamic conversion
											$iniSymptomString = convertTheSymptom($iniSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
											$iniSymptomString = base64_encode($iniSymptomString);

											$setInitialOriginalSourceId= $backupSetSymptomRow['original_quelle_id'];
											$setInitialSymptomStringBeforeConversion = $iniSymptomStringBeforeConversion;
											$setInitialSymptomString = $iniSymptomString;
											$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
											$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
											$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
										} else {
											// Get the first symptom set from the backups of this comparison
											// Here joining is made on backup table's quelle_id not with the original_quelle_id
											$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
											if(mysqli_num_rows($importMasterBackupResult) > 0){
												$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
												$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$restOfComparingSymptomRow['id']."'");
												if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
													$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

													// if(isset($backupSetSymptomRow['original_symptom_id']) AND $backupSetSymptomRow['original_symptom_id'] != ""){
													// 	$originalSymptomInfoResult = mysqli_query($db,"SELECT original_quelle_id, arznei_id FROM quelle_import_test WHERE id = '".$backupSetSymptomRow['original_symptom_id']."'");
													// 	if(mysqli_num_rows($originalSymptomInfoResult) > 0)
													// 		$originalSymptomInfoData = mysqli_fetch_assoc($originalSymptomInfoResult);
													// }
													// $initialOrzId = (isset($originalSymptomInfoData['original_quelle_id']) AND $originalSymptomInfoData['original_quelle_id'] != "") ? $originalSymptomInfoData['original_quelle_id'] : "";
													// $initialArzId = (isset($originalSymptomInfoData['arznei_id']) AND $originalSymptomInfoData['arznei_id'] != "") ? $originalSymptomInfoData['arznei_id'] : "";

													$setInitialQuelleId = $backupSetSymptomRow['original_quelle_id'];
													$setInitialQuelleCode = $backupSetSymptomRow['quelle_code'];
													if($comparisonOption == 1)
														$iniSymptomString =  $backupSetSymptomRow['searchable_text'];
													else
														$iniSymptomString =  $backupSetSymptomRow['BeschreibungOriginal'];

													// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
													$iniSymptomStringBeforeConversion = base64_encode($iniSymptomString);

													// Apply dynamic conversion
													$iniSymptomString = convertTheSymptom($iniSymptomString, $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
													$iniSymptomString = base64_encode($iniSymptomString);

													$setInitialOriginalSourceId= $backupSetSymptomRow['original_quelle_id'];
													$setInitialSymptomStringBeforeConversion = $iniSymptomStringBeforeConversion;
													$setInitialSymptomString = $iniSymptomString;
													$setInitialSymptomId = $backupSetSymptomRow['original_symptom_id'];
													$setInitialKommentar = $backupSetSymptomRow['Kommentar'];
													$setInitialFussnote = $backupSetSymptomRow['Fussnote'];
												}
											}
										}
									}*/

									$compareResultArray[] = array(
										"no_of_match" => 0,
										"percentage" => 0,
										"comparison_initial_source_id" => $initialQuelleId,
										"source_arznei_id" => $arzneiId,
										"initial_source_id" => $setInitialQuelleId,
										"initial_original_source_id" => $setInitialOriginalSourceId,
										"initial_source_code" => $setInitialQuelleCode,
										"initial_source_year" => $setInitialSourceYear,
										"initial_source_original_language" => $setInitialSourceOriginalLanguage,
										"initial_saved_version_source_code" => $setInitialSavedVersionSourceCode,
										"initial_source_symptom_highlighted_de" => $setInitialSymptomString_de,
										"initial_source_symptom_highlighted_en" => $setInitialSymptomString_en,
										"initial_source_symptom_de" => $setInitialSymptomString_de,
										"initial_source_symptom_en" => $setInitialSymptomString_en,
										"initial_source_symptom_before_conversion_highlighted_de" => $setInitialSymptomStringBeforeConversion_de,
										"initial_source_symptom_before_conversion_highlighted_en" => $setInitialSymptomStringBeforeConversion_en,
										"initial_source_symptom_before_conversion_de" => $setInitialSymptomStringBeforeConversion_de,
										"initial_source_symptom_before_conversion_en" => $setInitialSymptomStringBeforeConversion_en,
										"initial_source_symptom_id" => $setInitialSymptomId,
										"main_parent_initial_symptom_id" => $setInitialSymptomId,
										"connections_main_parent_symptom_id" => $setInitialSymptomId,
										"initial_source_symptom_comment" => $setInitialKommentar,
										"initial_source_symptom_footnote" => $setInitialFussnote,
										"comparing_source_id" => "",
										"comparing_original_source_id" => "",
										"comparing_source_code" => "",
										"comparing_source_year" => "",
										"comparing_source_original_language" => "",
										"comparing_saved_version_source_code" => "",
										"comparing_source_symptom_highlighted_de" => "",
										"comparing_source_symptom_highlighted_en" => "",
										"comparing_source_symptom_de" => "",
										"comparing_source_symptom_en" => "",
										"comparing_source_symptom_before_conversion_highlighted_de" => "",
										"comparing_source_symptom_before_conversion_highlighted_en" => "",
										"comparing_source_symptom_before_conversion_de" => "",
										"comparing_source_symptom_before_conversion_en" => "",
										"comparing_source_symptom_id" => "",
										"comparing_source_symptom_comment" => "",
										"comparing_source_symptom_footnote" => "",
										"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
										// "main_initial_symptom_id" => $restOfComparingSymptomRow['id'],
										"has_connections" => $comHasConnections,
										"is_final_version_available" => $setInitialIsFinalVersionAvailable,
										"is_further_connections_are_saved" => $isFurtherConnectionsAreSaved,
										"is_pasted" => 0,
										"is_ns_paste" => 0,
										"ns_paste_note" => "",
										"is_initial_source" => 1,
										"active_symptom_type" => "initial",
										"similarity_rate" => $similarityRate,
										"comparison_option" => $comparisonOption,
										"is_unmatched_symptom" => 1,
										"is_paste_disabled" => $is_paste_disabled,
										"is_ns_paste_disabled" => $is_ns_paste_disabled,
										"is_connect_disabled" => $is_connect_disabled,
										"is_ns_connect_disabled" => $is_ns_connect_disabled
									);
								}
							}
						}
						else
						{
							$pageno = 0;
							$totalBatchesInPart2 = 0;
						}
					}
					else
					{
						$pageno = 0;
						$totalBatchesInPart2 = 0;
					}

					if($pageno == $totalBatchesInPart2){
						$progress_percentage = 100;
					}
					$step = $step + 1;
					
				}
				else
				{
					// For display lable
					$processStage = 2;
					$progress_percentage = 100;
					$step = 'done';
				}
			}
			// if($step < $totalBatchesInPart1)
			// 	$step = $step + 1;
			// else
			// 	$step = 'done';
		}
	} catch (Exception $e) {
	    $step = 'error';
	}


	echo json_encode( array( 'process_stage' => $processStage, 'step' => $step, 'total_batches_in_part1' => $totalBatchesInPart1, 'total_batches_in_part2' => $totalBatchesInPart2, 'data' => $formData, 'result_data' => $compareResultArray, 'progress_percentage' => $progress_percentage, 'system_generated_comparison_name' => $comparisonName, 'matched_symptom_ids' => $matchedSymptomIds, 'comparing_source_ids' => $comparingSourceIds, 'saved_comparison_comparing_source_ids' => "", 'is_stage2_checked' => $isStage2Checked, 'un_matched_symptoms_set_number' => $un_matched_symptoms_set_number, 'is_invalid_quelle' => $is_invalid_quelle) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>