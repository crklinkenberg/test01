<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Here we are re-activating a comparison from the comparison's backup sets. 
	*/
	$stopWords = array();
	$stopWords = getStopWords();
?>
<?php  
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the operation, please try again!';
	try 
	{
		$original_quelle_id = (isset($_POST['original_quelle_id']) AND $_POST['original_quelle_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['original_quelle_id'])) : null;
		$quelle_id = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['quelle_id'])) : null;
		$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['arznei_id'])) : null;
		if($original_quelle_id != "" AND $quelle_id != "" AND $arznei_id != "")
		{
			$db->begin_transaction();
			$original_quelle_ids = explode(',', $original_quelle_id);
			$deletingQuelleIds = getAllRelatedQuelle($original_quelle_ids, $original_quelle_ids);

			if(!empty($deletingQuelleIds)){

				// Delete existing source data
				foreach ($deletingQuelleIds as $key => $quelle) {

					// Resetting the source to it's default state when that particual single source has unsaved swapped connection with this source id START
		            $conQuelleResult = $db->query("SELECT initial_source_id, comparing_source_id FROM symptom_connections WHERE (initial_source_id = '".$quelle."' OR comparing_source_id = '".$quelle."') AND (connection_or_paste_type = 2 AND is_saved = 0)");
					if($conQuelleResult->num_rows > 0){
						while($conQuelleData = mysqli_fetch_array($conQuelleResult)){
							if($conQuelleData['initial_source_id'] == $quelle){
								$chkSavedComQuery = $db->query("SELECT id FROM saved_comparisons WHERE initial_source_id = '".$conQuelleData['comparing_source_id']."' OR FIND_IN_SET('".$conQuelleData['comparing_source_id']."', comparing_source_ids)");
								if($chkSavedComQuery->num_rows == 0){

									$fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$conQuelleData['comparing_source_id']);
						            if($fetchSavedComInfoQuery->num_rows > 0){
						            	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
						            	if($fetchSavedComInfoData['quelle_type_id'] != 3){
						            		// deleteing current symptoms and adding the default imported symptom of the source
						            		restoreTheDefautImportedSymptoms($conQuelleData['comparing_source_id'], $arznei_id); 
						            	} //else {
						            		// If it is a saved comparison than re-saveing the save comparison 
						            		//resaveTheSavedComparison($conQuelleData['comparing_source_id'], $arznei_id);
						            	//}
						            }
								}
							} else if($conQuelleData['comparing_source_id'] == $quelle){
								$chkSavedComQuery = $db->query("SELECT id FROM saved_comparisons WHERE initial_source_id = '".$conQuelleData['initial_source_id']."' OR FIND_IN_SET('".$conQuelleData['initial_source_id']."', comparing_source_ids)");
								if($chkSavedComQuery->num_rows == 0){

									$fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$conQuelleData['initial_source_id']);
						            if($fetchSavedComInfoQuery->num_rows > 0){
						            	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
						            	if($fetchSavedComInfoData['quelle_type_id'] != 3){
						            		// deleteing current symptoms and adding the default imported symptom of the source
						            		restoreTheDefautImportedSymptoms($conQuelleData['initial_source_id'], $arznei_id); 
						            	} //else {
						            		// If it is a saved comparison than re-saveing the save comparison 
						            		//resaveTheSavedComparison($conQuelleData['initial_source_id'], $arznei_id);
						            	//}
						            }
								}
							}
						}
					}
					// Resetting the source to it's default state when that particual single source has unsaved swapped connection with this source id END

					$masterResult = $db->query("SELECT id, quelle_id FROM quelle_import_master WHERE quelle_id = '".$quelle."' AND arznei_id = '".$arznei_id."'");
					if($masterResult->num_rows > 0){
						while($masterData = mysqli_fetch_array($masterResult)){
							$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE master_id = ".$masterData['id']);
							if($symptomResult->num_rows > 0){
								while($symptomData = mysqli_fetch_array($symptomResult)){
									$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($symPrueferDeleteQuery);

			            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($symRefDeleteQuery);

			            			// Deleteing backup sets swapped symptoms data
			            			$swappedSymDeleteQuery="DELETE FROM swapped_symptoms WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($swappedSymDeleteQuery);

			            			$backupSetSwapSymptomResult = $db->query("SELECT id FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$symptomData['id']);
									if($backupSetSwapSymptomResult->num_rows > 0){
										while($backupSetSwapSymptomData = mysqli_fetch_array($backupSetSwapSymptomResult)){
											$backupSetsSwapSymPrueferDeleteQuery="DELETE FROM backup_sets_swapped_symptom_pruefer WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
					            			$db->query($backupSetsSwapSymPrueferDeleteQuery);

					            			$backupSetsSwapSymRefDeleteQuery = "DELETE FROM backup_sets_swapped_symptom_reference WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
					            			$db->query($backupSetsSwapSymRefDeleteQuery);
										}
									}
			            			$backupSetsSwappedSymDeleteQuery="DELETE FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$symptomData['id'];
			            			$db->query($backupSetsSwappedSymDeleteQuery);

			            			// Delete if it is there in appended_symptoms
			            			$appendedSymptomDeleteQuery="DELETE FROM appended_symptoms WHERE symptom_id = ".$symptomData['id'];
				            		$db->query($appendedSymptomDeleteQuery); 
			            		}
							}
							$symDeleteQuery="DELETE FROM quelle_import_test WHERE master_id = ".$masterData['id'];
			            	$db->query($symDeleteQuery);

			            	$workingQuelleId = $masterData['quelle_id'];

			            	$arzneiQuelleDeleteQuery="DELETE FROM arznei_quelle WHERE quelle_id = '".$workingQuelleId."' AND arznei_id = '".$arznei_id."'";
				            $db->query($arzneiQuelleDeleteQuery);

				            $quelleConnDeleteQuery="DELETE FROM symptom_connections WHERE (initial_source_id = '".$workingQuelleId."' OR comparing_source_id = '".$workingQuelleId."') AND source_arznei_id = '".$arznei_id."'";
				            $db->query($quelleConnDeleteQuery);

				            $quelleDeleteQuery="DELETE FROM quelle WHERE quelle_id = '".$workingQuelleId."' AND quelle_type_id = 3";
				            $db->query($quelleDeleteQuery);

				            $fetchSavedComQuery=$db->query("SELECT initial_source_id, comparing_source_ids, comparison_option FROM saved_comparisons WHERE quelle_id = ".$workingQuelleId);
				            if($fetchSavedComQuery->num_rows > 0){
				            	$fetchSavedComData = mysqli_fetch_assoc($fetchSavedComQuery);
								$savedComparisonInitialSourceId = (isset($fetchSavedComData['initial_source_id']) AND $fetchSavedComData['initial_source_id'] != "") ? $fetchSavedComData['initial_source_id'] : null;
								$savedComparisonComparingSourceIds = (isset($fetchSavedComData['comparing_source_ids']) AND $fetchSavedComData['comparing_source_ids'] != "") ? explode(',', $fetchSavedComData['comparing_source_ids']) : null;
								$savedComparisonComparingOption = (isset($fetchSavedComData['comparison_option']) AND $fetchSavedComData['comparison_option'] != "") ? $fetchSavedComData['comparison_option'] : null;

								if($savedComparisonInitialSourceId != "") {
									$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$savedComparisonInitialSourceId;
			            			$db->query($updQuelleQuery);

			            			
			            			if($workingQuelleId != $original_quelle_id) {
			            				// Deleteing backup sets swapped symptoms data
				            			$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE quelle_id = ".$savedComparisonInitialSourceId." AND arznei_id = '".$arznei_id."'");
										if($symptomResult->num_rows > 0){
											while($iniSymptomData = mysqli_fetch_array($symptomResult)){
												$swappedSymDeleteQuery="DELETE FROM swapped_symptoms WHERE symptom_id = ".$iniSymptomData['id'];
						            			$db->query($swappedSymDeleteQuery);

						            			$backupSetSwapSymptomResult = $db->query("SELECT id FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$iniSymptomData['id']);
												if($backupSetSwapSymptomResult->num_rows > 0){
													while($backupSetSwapSymptomData = mysqli_fetch_array($backupSetSwapSymptomResult)){
														$backupSetsSwapSymPrueferDeleteQuery="DELETE FROM backup_sets_swapped_symptom_pruefer WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
								            			$db->query($backupSetsSwapSymPrueferDeleteQuery);

								            			$backupSetsSwapSymRefDeleteQuery = "DELETE FROM backup_sets_swapped_symptom_reference WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
								            			$db->query($backupSetsSwapSymRefDeleteQuery);
													}
												}
						            			$backupSetsSwappedSymDeleteQuery="DELETE FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$iniSymptomData['id'];
						            			$db->query($backupSetsSwappedSymDeleteQuery);
											}
										}

			            				// If the source is a imported source and not a saved comparison source than re add the default symptoms which are there in the backup table to avoid swaped symptoms 
				            			$fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$savedComparisonInitialSourceId);
							            if($fetchSavedComInfoQuery->num_rows > 0){
							            	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
							            	if($fetchSavedComInfoData['quelle_type_id'] != 3){
								            	// deleteing current symptoms and adding the default imported symptom of the source
								            	restoreTheDefautImportedSymptoms($savedComparisonInitialSourceId, $arznei_id); 
							            	} else {
							            		// If it is a saved comparison than re-saveing the save comparison 
							            		resaveTheSavedComparison($savedComparisonInitialSourceId, $arznei_id);
							            	}
							            }
			            			}
								}

								if(!empty($savedComparisonComparingSourceIds)){
									foreach ($savedComparisonComparingSourceIds as $allSourcesKey => $allSourcesVal) {
										$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$allSourcesVal;
			        					$db->query($updQuelleQuery);

			        					if($workingQuelleId != $original_quelle_id) {
			        						// Deleteing backup sets swapped symptoms data
				        					$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE quelle_id = ".$allSourcesVal." AND arznei_id = '".$arznei_id."'");
											if($symptomResult->num_rows > 0){
												while($comSymptomData = mysqli_fetch_array($symptomResult)){
													$swappedSymDeleteQuery="DELETE FROM swapped_symptoms WHERE symptom_id = ".$comSymptomData['id'];
							            			$db->query($swappedSymDeleteQuery);

							            			$backupSetSwapSymptomResult = $db->query("SELECT id FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$comSymptomData['id']);
													if($backupSetSwapSymptomResult->num_rows > 0){
														while($backupSetSwapSymptomData = mysqli_fetch_array($backupSetSwapSymptomResult)){
															$backupSetsSwapSymPrueferDeleteQuery="DELETE FROM backup_sets_swapped_symptom_pruefer WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
									            			$db->query($backupSetsSwapSymPrueferDeleteQuery);

									            			$backupSetsSwapSymRefDeleteQuery = "DELETE FROM backup_sets_swapped_symptom_reference WHERE symptom_id = ".$backupSetSwapSymptomData['id'];
									            			$db->query($backupSetsSwapSymRefDeleteQuery);
														}
													}
							            			$backupSetsSwappedSymDeleteQuery="DELETE FROM backup_sets_swapped_symptoms WHERE original_symptom_id = ".$comSymptomData['id'];
							            			$db->query($backupSetsSwappedSymDeleteQuery);
												}
											}

			        						// If the source is a imported source and not a saved comparison source than re add the default symptoms which are there in the backup table to avoid swaped symptoms 
				        					$fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$allSourcesVal);
								            if($fetchSavedComInfoQuery->num_rows > 0){
								            	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
								            	if($fetchSavedComInfoData['quelle_type_id'] != 3){
								            		// deleteing current symptoms and adding the default imported symptom of the source
								            		restoreTheDefautImportedSymptoms($allSourcesVal, $arznei_id); 
								            	} else {
								            		// If it is a saved comparison than re-saveing the save comparison 
								            		resaveTheSavedComparison($allSourcesVal, $arznei_id);
								            	}
								            }
			        					}
									}
								}

								// Delete parents/preset connections
								// Collecting initial source's compared sources chain 
								$initialSourceComparedChainIds = array();
								if($savedComparisonInitialSourceId != "") {
									$initialSourceIdInArr = explode(',', $savedComparisonInitialSourceId);
									if(!empty($initialSourceIdInArr)){
										if(!in_array($savedComparisonInitialSourceId, $initialSourceComparedChainIds))
											array_push($initialSourceComparedChainIds, $savedComparisonInitialSourceId);
										$returnedIds = getAllComparedSourceIds($initialSourceIdInArr);
										if(!empty($returnedIds)){
											foreach ($returnedIds as $IdVal) {
												if(!in_array($IdVal, $initialSourceComparedChainIds))
													array_push($initialSourceComparedChainIds, $IdVal);
											}
										}	
									}
								}

								// Collecting comparing sources compared sources chain
								$comparingSourceComparedChainIds = array();
								if(!empty($savedComparisonComparingSourceIds)){
									foreach ($savedComparisonComparingSourceIds as $allSourcesKey => $allSourcesVal) {
										$comparingSourceIdInArr = explode(',', $allSourcesVal);
										if(!empty($comparingSourceIdInArr)){
											if(!in_array($allSourcesVal, $comparingSourceComparedChainIds))
												array_push($comparingSourceComparedChainIds, $allSourcesVal);
											$returnedIds = getAllComparedSourceIds($comparingSourceIdInArr);
											if(!empty($returnedIds)){
												foreach ($returnedIds as $IdVal) {
													if(!in_array($IdVal, $comparingSourceComparedChainIds))
														array_push($comparingSourceComparedChainIds, $IdVal);
												}
											}	
										}
									}
								}

								$allInvolvedSourcesOfComparingSources = (!empty($comparingSourceComparedChainIds)) ? implode(',', $comparingSourceComparedChainIds) : "";
								
								$fetchAllConnResult = mysqli_query($db,"SELECT initial_source_symptom_id, comparing_source_symptom_id, connection_or_paste_type FROM symptom_connections WHERE source_arznei_id = '".$arznei_id."' AND ((initial_source_id = '".$savedComparisonInitialSourceId."' AND FIND_IN_SET(comparing_source_id, '".$allInvolvedSourcesOfComparingSources."')) OR (FIND_IN_SET(initial_source_id, '".$allInvolvedSourcesOfComparingSources."') AND comparing_source_id = '".$savedComparisonInitialSourceId."'))");
								if(mysqli_num_rows($fetchAllConnResult) > 0){
									while($symptomConnData = mysqli_fetch_array($fetchAllConnResult)){
										// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
										if($symptomConnData['connection_or_paste_type'] == 2){
											revertBackSwappedConnectedSymptoms($symptomConnData['initial_source_symptom_id'], $symptomConnData['comparing_source_symptom_id'], $arznei_id, $savedComparisonComparingOption, $savedComparisonInitialSourceId, $fetchSavedComData['comparing_source_ids']);
										}
									}
								}

								$quelleParentsConnDeleteQuery="DELETE FROM symptom_connections WHERE source_arznei_id = '".$arznei_id."' AND ((initial_source_id = '".$savedComparisonInitialSourceId."' AND FIND_IN_SET(comparing_source_id, '".$allInvolvedSourcesOfComparingSources."')) OR (FIND_IN_SET(initial_source_id, '".$allInvolvedSourcesOfComparingSources."') AND comparing_source_id = '".$savedComparisonInitialSourceId."'))";
				           		$db->query($quelleParentsConnDeleteQuery);

				           		if(!empty($initialSourceComparedChainIds)){
									$allInvolvedSourcesOfInitialSources = (!empty($initialSourceComparedChainIds)) ? implode(',', $initialSourceComparedChainIds) : "";
									$deleteInitialSourceInternalConnQuery="DELETE FROM symptom_connections WHERE source_arznei_id = '".$arznei_id."' AND ((initial_source_id = '".$savedComparisonInitialSourceId."' AND FIND_IN_SET(comparing_source_id, '".$allInvolvedSourcesOfInitialSources."')) OR (FIND_IN_SET(initial_source_id, '".$allInvolvedSourcesOfInitialSources."') AND comparing_source_id = '".$savedComparisonInitialSourceId."'))";
				           			$db->query($deleteInitialSourceInternalConnQuery);
								}
								foreach ($savedComparisonComparingSourceIds as $allSourcesKey => $allSourcesVal) {
									if($allInvolvedSourcesOfComparingSources != ""){
										$deleteInitialSourceInternalConnQuery="DELETE FROM symptom_connections WHERE source_arznei_id = '".$arznei_id."' AND ((initial_source_id = '".$allSourcesVal."' AND FIND_IN_SET(comparing_source_id, '".$allInvolvedSourcesOfComparingSources."')) OR (FIND_IN_SET(initial_source_id, '".$allInvolvedSourcesOfComparingSources."') AND comparing_source_id = '".$allSourcesVal."'))";
					           			$db->query($deleteInitialSourceInternalConnQuery);
									}
								}
				            }
				            
				            $savedComDeleteQuery="DELETE FROM saved_comparisons WHERE quelle_id = ".$workingQuelleId;
				            $db->query($savedComDeleteQuery);	
		            	}
					}
					$masterDeleteQuery="DELETE FROM quelle_import_master WHERE quelle_id = '".$quelle."' AND arznei_id = '".$arznei_id."'";
		            $db->query($masterDeleteQuery);

		            // DELETEING THE RELATED DATA IN THE BACKUP TABLES EXCEPT CURRENTLY WORKING SOURCE(MEANS THE REACTIVATING SOURCE) START
		            if($original_quelle_id != $quelle){
		            	$fetchBackupQuelleMasterQuery=$db->query("SELECT * FROM quelle_import_master_backup WHERE original_quelle_id = ".$quelle." AND arznei_id = ".$arznei_id);
			            if($fetchBackupQuelleMasterQuery->num_rows > 0){
			            	while($backupQuelleMasterData = mysqli_fetch_array($fetchBackupQuelleMasterQuery)){
			            		$symptomResult = mysqli_query($db, "SELECT * FROM quelle_import_backup WHERE master_id = ".$backupQuelleMasterData['id']."");
								if(mysqli_num_rows($symptomResult) > 0){
									while($symptomData = mysqli_fetch_array($symptomResult)){
										$backupSymPrueferDeleteQuery="DELETE FROM symptom_pruefer_backup WHERE symptom_id = ".$symptomData['id'];
				            			$db->query($backupSymPrueferDeleteQuery);

				            			$backupSymRefDeleteQuery="DELETE FROM symptom_reference_backup WHERE symptom_id = ".$symptomData['id'];
				            			$db->query($backupSymRefDeleteQuery);
									}
								}

								$backupSymDeleteQuery="DELETE FROM quelle_import_backup WHERE master_id = ".$backupQuelleMasterData['id'];
				            	$db->query($backupSymDeleteQuery);

				            	$backupQuelleDeleteQuery="DELETE FROM quelle_backup WHERE quelle_id = ".$backupQuelleMasterData['quelle_id'];
				            	$db->query($backupQuelleDeleteQuery);
			            	}
			            	$backupQuelleMasterDeleteQuery="DELETE FROM quelle_import_master_backup WHERE original_quelle_id = ".$quelle." AND arznei_id = ".$arznei_id;
				            $db->query($backupQuelleMasterDeleteQuery);
			            }

			            $fetchBackupSavedComparisonQuery=$db->query("SELECT * FROM saved_comparisons_backup WHERE original_quelle_id = ".$quelle." AND arznei_id = ".$arznei_id);
			            if($fetchBackupSavedComparisonQuery->num_rows > 0){
			            	while($backupSavedComparisonData = mysqli_fetch_array($fetchBackupSavedComparisonQuery)){
			            		$backupSymptomConnectionDeleteQuery="DELETE FROM symptom_connections_backup WHERE saved_comparisons_backup_id = ".$backupSavedComparisonData['id'];
				            	$db->query($backupSymptomConnectionDeleteQuery);

				            	$backupAppendedSymptomDeleteQuery="DELETE FROM appended_symptoms_backup WHERE saved_comparisons_backup_id = ".$backupSavedComparisonData['id'];
				            	$db->query($backupAppendedSymptomDeleteQuery);

				            	$FVSymptomBackupsDeleteQuery="DELETE FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = ".$backupSavedComparisonData['id'];
								$db->query($FVSymptomBackupsDeleteQuery);

				            	// Deleting The Backup connected symptoms detailed states 
				            	$symptomResult = mysqli_query($db, "SELECT id FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = ".$backupSavedComparisonData['id']);
								if(mysqli_num_rows($symptomResult) > 0){
									while($symptomData = mysqli_fetch_array($symptomResult)){
										$backupSymPrueferDeleteQuery="DELETE FROM backup_connected_symptoms_details_pruefer WHERE symptom_id = ".$symptomData['id'];
				            			$db->query($backupSymPrueferDeleteQuery);

				            			$backupSymRefDeleteQuery="DELETE FROM backup_connected_symptoms_details_reference WHERE symptom_id = ".$symptomData['id'];
				            			$db->query($backupSymRefDeleteQuery);
									}
								}
				            	$deleteConnectedSymptomDetailsQuery="DELETE FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = ".$backupSavedComparisonData['id'];
				            	$db->query($deleteConnectedSymptomDetailsQuery);
			            	}
			            	$backupSavedComparisonDeleteQuery="DELETE FROM saved_comparisons_backup WHERE original_quelle_id = ".$quelle." AND arznei_id = ".$arznei_id;
				            $db->query($backupSavedComparisonDeleteQuery);
			            }
		            }
		            /*else
		            {
		            	$fetchSavedComQuery=$db->query("SELECT id FROM saved_comparisons_backup WHERE quelle_id = ".$quelle_id);
			            if($fetchSavedComQuery->num_rows > 0){
			            	$fetchSavedComData = mysqli_fetch_assoc($fetchSavedComQuery);

			            	// Deleting The Backup connected symptoms detailed states 
			            	$symptomResult = mysqli_query($db, "SELECT id FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = ".$fetchSavedComData['id']);
							if(mysqli_num_rows($symptomResult) > 0){
								while($symptomData = mysqli_fetch_array($symptomResult)){
									$backupSymPrueferDeleteQuery="DELETE FROM backup_connected_symptoms_details_pruefer WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($backupSymPrueferDeleteQuery);

			            			$backupSymRefDeleteQuery="DELETE FROM backup_connected_symptoms_details_reference WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($backupSymRefDeleteQuery);
								}
							}
			            	$deleteConnectedSymptomDetailsQuery="DELETE FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = ".$fetchSavedComData['id'];
			            	$db->query($deleteConnectedSymptomDetailsQuery);
			            }
		            }*/
		            // DELETEING THE RELATED DATA IN THE BACKUP TABLES EXCEPT CURRENTLY WORKING SOURCE(MEANS THE REACTIVATING SOURCE) END
				}

				// Adding reactivable backup source data in the main table
				$fetchBackupQuelleQuery=$db->query("SELECT * FROM quelle_backup WHERE quelle_id = ".$quelle_id);
	            if($fetchBackupQuelleQuery->num_rows > 0){
	            	$backupQuelleData = mysqli_fetch_assoc($fetchBackupQuelleQuery);
					$backupQuelleData['quelle_type_id'] = ($backupQuelleData['quelle_type_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['quelle_type_id']) : null;
					$backupQuelleData['quelle_schema_id'] = ($backupQuelleData['quelle_schema_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['quelle_schema_id']) : null;
					$backupQuelleData['herkunft_id'] = ($backupQuelleData['herkunft_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['herkunft_id']) : null;
					$backupQuelleData['code'] = ($backupQuelleData['code'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['code']) : null;
					$backupQuelleData['titel'] = ($backupQuelleData['titel'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['titel']) : null;
					$backupQuelleData['jahr'] = ($backupQuelleData['jahr'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['jahr']) : null;
					$backupQuelleData['band'] = ($backupQuelleData['band'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['band']) : null;
					$backupQuelleData['jahrgang'] = ($backupQuelleData['jahrgang'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['jahrgang']) : null;
					$backupQuelleData['nummer'] = ($backupQuelleData['nummer'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['nummer']) : null;
					$backupQuelleData['supplementheft'] = ($backupQuelleData['supplementheft'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['supplementheft']) : null;
					$backupQuelleData['auflage'] = ($backupQuelleData['auflage'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['auflage']) : null;
					$backupQuelleData['file_url'] = ($backupQuelleData['file_url'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['file_url']) : null;
					$backupQuelleData['verlag_id'] = ($backupQuelleData['verlag_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['verlag_id']) : null;
					$backupQuelleData['sprache'] = ($backupQuelleData['sprache'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['sprache']) : null;
					$backupQuelleData['source_type'] = ($backupQuelleData['source_type'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['source_type']) : null;
					$backupQuelleData['autor_or_herausgeber'] = ($backupQuelleData['autor_or_herausgeber'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['autor_or_herausgeber']) : null;
					$backupQuelleData['is_coding_with_symptom_number'] = ($backupQuelleData['is_coding_with_symptom_number'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['is_coding_with_symptom_number']) : null;
					$backupQuelleData['is_materia_medica'] = ($backupQuelleData['is_materia_medica'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['is_materia_medica']) : null;
					$backupQuelleData['active'] = ($backupQuelleData['active'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['active']) : null;
					$backupQuelleData['ip_address'] = ($backupQuelleData['ip_address'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['ip_address']) : null;
					$backupQuelleData['stand'] = ($backupQuelleData['stand'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['stand']) : null;
					$backupQuelleData['bearbeiter_id'] = ($backupQuelleData['bearbeiter_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['bearbeiter_id']) : null;
					$backupQuelleData['ersteller_datum'] = ($backupQuelleData['ersteller_datum'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['ersteller_datum']) : null;
					$backupQuelleData['ersteller_id'] = ($backupQuelleData['ersteller_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleData['ersteller_id']) : null;

					$newQuelleInsertQuery="INSERT INTO quelle (quelle_type_id, quelle_schema_id, herkunft_id, code, titel, jahr, band, jahrgang, nummer, supplementheft, auflage, file_url, verlag_id, sprache, source_type, autor_or_herausgeber, is_coding_with_symptom_number, is_materia_medica, active, ip_address, stand, bearbeiter_id, ersteller_datum, ersteller_id) VALUES (NULLIF('".$backupQuelleData['quelle_type_id']."', ''), NULLIF('".$backupQuelleData['quelle_schema_id']."', ''), NULLIF('".$backupQuelleData['herkunft_id']."', ''), NULLIF('".$backupQuelleData['code']."', ''), NULLIF('".$backupQuelleData['titel']."', ''), NULLIF('".$backupQuelleData['jahr']."', ''), NULLIF('".$backupQuelleData['band']."', ''), NULLIF('".$backupQuelleData['jahrgang']."', ''), NULLIF('".$backupQuelleData['nummer']."', ''), NULLIF('".$backupQuelleData['supplementheft']."', ''), NULLIF('".$backupQuelleData['auflage']."', ''), NULLIF('".$backupQuelleData['file_url']."', ''), NULLIF('".$backupQuelleData['verlag_id']."', ''), NULLIF('".$backupQuelleData['sprache']."', ''), NULLIF('".$backupQuelleData['source_type']."', ''), NULLIF('".$backupQuelleData['autor_or_herausgeber']."', ''), NULLIF('".$backupQuelleData['is_coding_with_symptom_number']."', ''), NULLIF('".$backupQuelleData['is_materia_medica']."', ''), NULLIF('".$backupQuelleData['active']."', ''), NULLIF('".$backupQuelleData['ip_address']."', ''), NULLIF('".$backupQuelleData['stand']."', ''), NULLIF('".$backupQuelleData['bearbeiter_id']."', ''), NULLIF('".$backupQuelleData['ersteller_datum']."', ''), NULLIF('".$backupQuelleData['ersteller_id']."', ''))";
		            $db->query($newQuelleInsertQuery);
		            $newQuelleId = mysqli_insert_id($db);

		            $backupQuelleMasterData['id'] = "";
		            $fetchBackupQuelleMasterQuery=$db->query("SELECT * FROM quelle_import_master_backup WHERE quelle_id = ".$quelle_id." AND arznei_id = ".$arznei_id);
		            if($fetchBackupQuelleMasterQuery->num_rows > 0){
		            	$backupQuelleMasterData = mysqli_fetch_assoc($fetchBackupQuelleMasterQuery);
		            	$backupQuelleMasterData['id'] = $backupQuelleMasterData['id'];
		            	$backupQuelleMasterData['import_rule'] = ($backupQuelleMasterData['import_rule'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['import_rule']) : null;
		            	$backupQuelleMasterData['importing_language'] = ($backupQuelleMasterData['importing_language'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['importing_language']) : null;
		            	$backupQuelleMasterData['is_symptoms_available_in_de'] = ($backupQuelleMasterData['is_symptoms_available_in_de'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['is_symptoms_available_in_de']) : null;
		            	$backupQuelleMasterData['is_symptoms_available_in_en'] = ($backupQuelleMasterData['is_symptoms_available_in_en'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['is_symptoms_available_in_en']) : null;
		            	$backupQuelleMasterData['translation_method_of_de'] = ($backupQuelleMasterData['translation_method_of_de'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['translation_method_of_de']) : null;
		            	$backupQuelleMasterData['translation_method_of_en'] = ($backupQuelleMasterData['translation_method_of_en'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['translation_method_of_en']) : null;
		            	$backupQuelleMasterData['arznei_id'] = ($backupQuelleMasterData['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['arznei_id']) : null;
		            	$backupQuelleMasterData['quelle_id'] = ($newQuelleId != "") ? mysqli_real_escape_string($db, $newQuelleId) : null;
		            	$backupQuelleMasterData['pruefer_ids'] = ($backupQuelleMasterData['pruefer_ids'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['pruefer_ids']) : null;
		            	$backupQuelleMasterData['active'] = ($backupQuelleMasterData['active'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['active']) : null;
						$backupQuelleMasterData['ip_address'] = ($backupQuelleMasterData['ip_address'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['ip_address']) : null;
						$backupQuelleMasterData['stand'] = ($backupQuelleMasterData['stand'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['stand']) : null;
						$backupQuelleMasterData['bearbeiter_id'] = ($backupQuelleMasterData['bearbeiter_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['bearbeiter_id']) : null;
						$backupQuelleMasterData['ersteller_datum'] = ($backupQuelleMasterData['ersteller_datum'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['ersteller_datum']) : null;
						$backupQuelleMasterData['ersteller_id'] = ($backupQuelleMasterData['ersteller_id'] != "") ? mysqli_real_escape_string($db, $backupQuelleMasterData['ersteller_id']) : null;

						$newQuelleMasterInsertQuery="INSERT INTO quelle_import_master (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, pruefer_ids, active, ip_address, stand, bearbeiter_id, ersteller_datum, ersteller_id) VALUES (NULLIF('".$backupQuelleMasterData['import_rule']."', ''), NULLIF('".$backupQuelleMasterData['importing_language']."', ''), NULLIF('".$backupQuelleMasterData['is_symptoms_available_in_de']."', ''), NULLIF('".$backupQuelleMasterData['is_symptoms_available_in_en']."', ''), NULLIF('".$backupQuelleMasterData['translation_method_of_de']."', ''), NULLIF('".$backupQuelleMasterData['translation_method_of_en']."', ''), NULLIF('".$backupQuelleMasterData['arznei_id']."', ''), NULLIF('".$backupQuelleMasterData['quelle_id']."', ''), NULLIF('".$backupQuelleMasterData['pruefer_ids']."', ''), NULLIF('".$backupQuelleMasterData['active']."', ''), NULLIF('".$backupQuelleMasterData['ip_address']."', ''), NULLIF('".$backupQuelleMasterData['stand']."', ''), NULLIF('".$backupQuelleMasterData['bearbeiter_id']."', ''), NULLIF('".$backupQuelleMasterData['ersteller_datum']."', ''), NULLIF('".$backupQuelleMasterData['ersteller_id']."', ''))";
			            $db->query($newQuelleMasterInsertQuery);
			            $newQuelleMasterId = mysqli_insert_id($db);
		            }

		            $backupSCData['sc_id'] = "";
		            $backupSCData['initial_source_id'] = "";
		            $backupSCDataComparingSourcesIds = "";
		            $allEntirelyInvolvedSoures = array();
		            $fetchBackupSCQuery=$db->query("SELECT * FROM saved_comparisons_backup WHERE quelle_id = ".$quelle_id." AND arznei_id = ".$arznei_id);
		            if($fetchBackupSCQuery->num_rows > 0){
		            	$backupSCData = mysqli_fetch_assoc($fetchBackupSCQuery);
		            	$comparingSourcesArr = explode(',', $backupSCData['comparing_source_ids']);

		            	$backupSCData['sc_id'] = $backupSCData['id'];
		            	$backupSCData['arznei_id'] = ($backupSCData['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSCData['arznei_id']) : null;
		            	$backupSCData['initial_source_id'] = ($backupSCData['initial_source_id'] != "") ? mysqli_real_escape_string($db, $backupSCData['initial_source_id']) : null;
		            	$backupSCData['comparing_source_ids'] = ($backupSCData['comparing_source_ids'] != "") ? mysqli_real_escape_string($db, $backupSCData['comparing_source_ids']) : null;
		            	$backupSCData['comparison_name'] = ($backupSCData['comparison_name'] != "") ? mysqli_real_escape_string($db, $backupSCData['comparison_name']) : null;
		            	$backupSCData['similarity_rate'] = ($backupSCData['similarity_rate'] != "") ? mysqli_real_escape_string($db, $backupSCData['similarity_rate']) : null;
		            	$backupSCData['comparison_option'] = ($backupSCData['comparison_option'] != "") ? mysqli_real_escape_string($db, $backupSCData['comparison_option']) : null;
		            	$backupSCData['comparison_language'] = ($backupSCData['comparison_language'] != "") ? mysqli_real_escape_string($db, $backupSCData['comparison_language']) : null;
		            	$backupSCData['quelle_id'] = ($newQuelleId != "") ? mysqli_real_escape_string($db, $newQuelleId) : null;

		            	$newSCInsertQuery="INSERT INTO saved_comparisons (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language, quelle_id) VALUES (NULLIF('".$backupSCData['arznei_id']."', ''), NULLIF('".$backupSCData['initial_source_id']."', ''), NULLIF('".$backupSCData['comparing_source_ids']."', ''), NULLIF('".$backupSCData['comparison_name']."', ''), NULLIF('".$backupSCData['similarity_rate']."', ''), NULLIF('".$backupSCData['comparison_option']."', ''), NULLIF('".$backupSCData['comparison_language']."', ''), NULLIF('".$backupSCData['quelle_id']."', ''))";
			            $db->query($newSCInsertQuery);

			            if($backupSCData['initial_source_id'] != "") {
							$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$backupSCData['initial_source_id'];
	            			$db->query($updQuelleQuery);
						}


						if(!empty($comparingSourcesArr)){
							foreach ($comparingSourcesArr as $allSourcesKey => $allSourcesVal) {
								$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$allSourcesVal;
	        					$db->query($updQuelleQuery);
							}
						}
						$backupSCDataComparingSourcesIds = (!empty($comparingSourcesArr)) ? rtrim(implode(',', $comparingSourcesArr), ',') : null;

						
						$allEntirelyInvolvedSoures = ($backupSCData['comparing_source_ids'] != "") ? explode(',', $backupSCData['comparing_source_ids']) : array();
						if($backupSCData['initial_source_id'] != "")
							array_push($allEntirelyInvolvedSoures, $backupSCData['initial_source_id']);
						if(!empty($allEntirelyInvolvedSoures)){
							$returnedIds = getAllComparedSourceIds($allEntirelyInvolvedSoures);
							if(!empty($returnedIds)){
								foreach ($returnedIds as $IdVal) {
									if(!in_array($IdVal, $allEntirelyInvolvedSoures))
										array_push($allEntirelyInvolvedSoures, $IdVal);
								}
							}	
						}


						// Manging the appended symptoms
						// First deleting existing appended symptoms information
						$allDirectlyInvolvedSoures = ($backupSCData['comparing_source_ids'] != "") ? explode(',', $backupSCData['comparing_source_ids']) : array();
							if($backupSCData['initial_source_id'] != "")
								array_push($allDirectlyInvolvedSoures, $backupSCData['initial_source_id']);
						foreach ($allDirectlyInvolvedSoures as $sourceKey => $sourceVal) {
							$fetchSymptomsQuery=$db->query("SELECT id, is_symptom_appended FROM quelle_import_test WHERE quelle_id = '".$sourceVal."' AND arznei_id = '".$backupSCData['arznei_id']."'");
			            	if($fetchSymptomsQuery->num_rows > 0){
			            		while($symptomData = mysqli_fetch_array($fetchSymptomsQuery)){
			            			if($symptomData['is_symptom_appended'] == 1){
			            				$updAppendedSymQuery = "UPDATE quelle_import_test SET is_appended_symptom_active = 0 WHERE id = ".$symptomData['id'];
	        							$db->query($updAppendedSymQuery);
			            			}

			            			$appendedSymDeleteQuery = "DELETE FROM appended_symptoms WHERE symptom_id = ".$symptomData['id'];
        							$db->query($appendedSymDeleteQuery);
			            		}
			            	}
						}
						// Now adding appended symptoms information of this saved compariosn  
						$fetchAppendedSymptomsBackupQuery=$db->query("SELECT symptom_id FROM appended_symptoms_backup WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
		            	if($fetchAppendedSymptomsBackupQuery->num_rows > 0){
		            		while($appendedSymptomsBackupData = mysqli_fetch_array($fetchAppendedSymptomsBackupQuery)){
		            			$appendedSymptomsQuery=$db->query("SELECT id FROM appended_symptoms WHERE symptom_id = '".$appendedSymptomsBackupData['symptom_id']."'");
				            	if($appendedSymptomsQuery->num_rows == 0){
				            		$updAppendedSymQuery = "UPDATE quelle_import_test SET is_appended_symptom_active = 1 WHERE id = ".$appendedSymptomsBackupData['symptom_id'];
	        						$db->query($updAppendedSymQuery);

				            		$appendedSymptomInsertQuery = "INSERT INTO appended_symptoms (symptom_id) VALUES (NULLIF('".$appendedSymptomsBackupData['symptom_id']."', ''))";
		            				$db->query($appendedSymptomInsertQuery);
				            	}
		            		}
		            	}
		            }

		            if($backupSCData['sc_id'] != "" AND $backupSCData['initial_source_id'] != "" AND $backupSCDataComparingSourcesIds != "" AND !empty($allEntirelyInvolvedSoures)){

		            	$allInvolvedSourceIds = (!empty($allEntirelyInvolvedSoures)) ? rtrim(implode(',', $allEntirelyInvolvedSoures), ',') : null;
		            	$allInvolvedSourceIdsQueryCondition = "";
		            	if($allInvolvedSourceIds != "")
							$allInvolvedSourceIdsQueryCondition = " AND (initial_source_id IN (".$allInvolvedSourceIds.") AND comparing_source_id IN (".$allInvolvedSourceIds."))";

						// Fisrt we are Removing the PE(Paste edit) and CE(Connect edit) from the symptoms of the initila and comparative source symptoms.
						// The required PE and CE will get added aging in the lower section just below 
						$initialAndComparingSourceIds = (!empty($comparingSourcesArr)) ? $comparingSourcesArr : array();
						if($backupSCData['initial_source_id'] != "")
							array_push($initialAndComparingSourceIds, $backupSCData['initial_source_id']);
						foreach ($initialAndComparingSourceIds as $sourceKey => $sourceVal) {
							$getSymInfoQuery=$db->query("SELECT id FROM quelle_import_test WHERE quelle_id = '".$sourceVal."' AND arznei_id = '".$arznei_id."' AND is_final_version_available != 0");
		            		if($getSymInfoQuery->num_rows > 0){
		            			while($sympData = mysqli_fetch_array($getSymInfoQuery)){
		            				$updSympQuery="UPDATE quelle_import_test SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0 WHERE id = ".$sympData['id'];
	            					$db->query($updSympQuery);
		            			}
		            		}
						}


		            	// $fetchBackupOriginalConnsQuery=$db->query("SELECT * FROM symptom_connections_backup WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND initial_source_id = '".$backupSCData['initial_source_id']."' AND FIND_IN_SET(comparing_source_id, '".$backupSCDataComparingSourcesIds."') AND source_arznei_id = '".$arznei_id."' AND initial_source_type = 'original' AND comparing_source_type = 'original'");
		            	$fetchBackupOriginalConnsQuery=$db->query("SELECT * FROM symptom_connections_backup WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."'".$allInvolvedSourceIdsQueryCondition." AND source_arznei_id = '".$arznei_id."' AND initial_source_type = 'original' AND comparing_source_type = 'original'");
		            	if($fetchBackupOriginalConnsQuery->num_rows > 0){
		            		while($originalConnData = mysqli_fetch_array($fetchBackupOriginalConnsQuery)){

		            			// Checking if this connection is already exist in the active section connection table. 
		            			$checkingExistingConnsQuery=$db->query("SELECT id FROM symptom_connections WHERE initial_source_id = '".$originalConnData['initial_source_id']."' AND comparing_source_id = '".$originalConnData['comparing_source_id']."' AND initial_source_symptom_id = '".$originalConnData['initial_source_symptom_id']."' AND comparing_source_symptom_id = '".$originalConnData['comparing_source_symptom_id']."' AND source_arznei_id = '".$originalConnData['source_arznei_id']."'");
		            			if($checkingExistingConnsQuery->num_rows > 0)
		            				continue;


		            			$originalConnData['is_initial_source'] = ($originalConnData['is_initial_source'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_initial_source']) : null;
		            			$originalConnData['source_arznei_id'] = ($originalConnData['source_arznei_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['source_arznei_id']) : null;
		            			$originalConnData['initial_source_id'] = ($originalConnData['initial_source_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_id']) : null;
		            			$originalConnData['conversion_initial_source_id'] = ($originalConnData['conversion_initial_source_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['conversion_initial_source_id']) : null;
		            			$originalConnData['comparing_source_id'] = ($originalConnData['comparing_source_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_id']) : null;
		            			$originalConnData['conversion_comparing_source_id'] = ($originalConnData['conversion_comparing_source_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['conversion_comparing_source_id']) : null;
		            			$originalConnData['initial_source_code'] = ($originalConnData['initial_source_code'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_code']) : null;
		            			$originalConnData['comparing_source_code'] = ($originalConnData['comparing_source_code'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_code']) : null;
		            			$originalConnData['initial_source_symptom_id'] = ($originalConnData['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_symptom_id']) : null;
		            			$originalConnData['comparing_source_symptom_id'] = ($originalConnData['comparing_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_symptom_id']) : null;
		            			// Initial
		            			$originalConnData['initial_source_symptom_highlighted_de'] = ($originalConnData['initial_source_symptom_highlighted_de'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_symptom_highlighted_de']) : null;
		            			$originalConnData['initial_source_symptom_highlighted_en'] = ($originalConnData['initial_source_symptom_highlighted_en'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_symptom_highlighted_en']) : null;
		            			$originalConnData['initial_source_symptom_de'] = ($originalConnData['initial_source_symptom_de'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_symptom_de']) : null;
		            			$originalConnData['initial_source_symptom_en'] = ($originalConnData['initial_source_symptom_en'] != "") ? mysqli_real_escape_string($db, $originalConnData['initial_source_symptom_en']) : null;
		            			// comparing
		            			$originalConnData['comparing_source_symptom_highlighted_de'] = ($originalConnData['comparing_source_symptom_highlighted_de'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_symptom_highlighted_de']) : null;
		            			$originalConnData['comparing_source_symptom_highlighted_en'] = ($originalConnData['comparing_source_symptom_highlighted_en'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_symptom_highlighted_en']) : null;
		            			$originalConnData['comparing_source_symptom_de'] = ($originalConnData['comparing_source_symptom_de'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_symptom_de']) : null;
		            			$originalConnData['comparing_source_symptom_en'] = ($originalConnData['comparing_source_symptom_en'] != "") ? mysqli_real_escape_string($db, $originalConnData['comparing_source_symptom_en']) : null;
		            			
		            			$originalConnData['connection_language'] = ($originalConnData['connection_language'] != "") ? mysqli_real_escape_string($db, $originalConnData['connection_language']) : null;
		            			
		            			$originalConnData['matching_percentage'] = ($originalConnData['matching_percentage'] != "") ? mysqli_real_escape_string($db, $originalConnData['matching_percentage']) : 0;
		            			$originalConnData['is_connected'] = ($originalConnData['is_connected'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_connected']) : null;
		            			$originalConnData['is_ns_connect'] = ($originalConnData['is_ns_connect'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_ns_connect']) : null;
		            			$originalConnData['ns_connect_note'] = ($originalConnData['ns_connect_note'] != "") ? mysqli_real_escape_string($db, $originalConnData['ns_connect_note']) : null;
		            			$originalConnData['is_pasted'] = ($originalConnData['is_pasted'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_pasted']) : null;
		            			$originalConnData['is_ns_paste'] = ($originalConnData['is_ns_paste'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_ns_paste']) : null;
		            			$originalConnData['ns_paste_note'] = ($originalConnData['ns_paste_note'] != "") ? mysqli_real_escape_string($db, $originalConnData['ns_paste_note']) : null;
		            			$originalConnData['is_saved'] = ($originalConnData['is_saved'] != "") ? mysqli_real_escape_string($db, $originalConnData['is_saved']) : null;
		            			$originalConnData['connection_or_paste_type'] = ($originalConnData['connection_or_paste_type'] != "") ? mysqli_real_escape_string($db, $originalConnData['connection_or_paste_type']) : null;

		            			$newOriginalSymConnInsertQuery="INSERT INTO symptom_connections (is_initial_source, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$originalConnData['is_initial_source']."', ''), NULLIF('".$originalConnData['source_arznei_id']."', ''), NULLIF('".$originalConnData['initial_source_id']."', ''), NULLIF('".$originalConnData['comparing_source_id']."', ''), NULLIF('".$originalConnData['conversion_initial_source_id']."', ''), NULLIF('".$originalConnData['conversion_comparing_source_id']."', ''), NULLIF('".$originalConnData['initial_source_code']."', ''), NULLIF('".$originalConnData['comparing_source_code']."', ''), NULLIF('".$originalConnData['initial_source_symptom_id']."', ''), NULLIF('".$originalConnData['comparing_source_symptom_id']."', ''), NULLIF('".$originalConnData['initial_source_symptom_highlighted_de']."', ''), NULLIF('".$originalConnData['initial_source_symptom_highlighted_en']."', ''), NULLIF('".$originalConnData['comparing_source_symptom_highlighted_de']."', ''), NULLIF('".$originalConnData['comparing_source_symptom_highlighted_en']."', ''), NULLIF('".$originalConnData['initial_source_symptom_de']."', ''), NULLIF('".$originalConnData['initial_source_symptom_en']."', ''), NULLIF('".$originalConnData['comparing_source_symptom_de']."', ''), NULLIF('".$originalConnData['comparing_source_symptom_en']."', ''), NULLIF('".$originalConnData['connection_language']."', ''), '".$originalConnData['matching_percentage']."', NULLIF('".$originalConnData['is_connected']."', ''), NULLIF('".$originalConnData['is_ns_connect']."', ''), NULLIF('".$originalConnData['ns_connect_note']."', ''), NULLIF('".$originalConnData['is_pasted']."', ''), NULLIF('".$originalConnData['is_ns_paste']."', ''), NULLIF('".$originalConnData['ns_paste_note']."', ''), NULLIF('".$originalConnData['is_saved']."', ''), NULLIF('".$originalConnData['connection_or_paste_type']."', ''))";
			            		$db->query($newOriginalSymConnInsertQuery);


			            		// Checking Initial symptom
			            		// Checking for swapped data and making updates in the main symptom START
								$updateData = array();
								/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$originalConnData['initial_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
								if(mysqli_num_rows($swappedSymptomResult) > 0){
									$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
									$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
									if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0) {
										$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);


										$updateData['id'] = $backupSetSymptomRow['id'];
										$updateData['symptom_pruefer_table'] = 'backup_sets_swapped_symptom_pruefer';
										$updateData['symptom_reference_table'] = 'backup_sets_swapped_symptom_reference';

										$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
										$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
										$updateData['quelle_id'] = ($backupSetSymptomRow['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_id']) : null;
										$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
										$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
										$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
										$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
										$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
										$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
										$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
										$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
										$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
										$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
										$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
										$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
										$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
										$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
										$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
										$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
										$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
										$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
										$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
										$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
										$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
										$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
										$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
										$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
									} else {
										// Get the first symptom set from the backups of this comparison
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$originalConnData['initial_source_symptom_id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												$updateData['id'] = $backupSetSymptomRow['id'];
												$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
												$updateData['symptom_reference_table'] = 'symptom_reference_backup';

												$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
												$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
												$updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
												$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
												$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
												$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
												$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
												$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
												$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
											}
										}
									}
								}*/

								// 
								// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
								$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND original_symptom_id = '".$originalConnData['initial_source_symptom_id']."'");
				            	if($backupConnectedSymptomQuery->num_rows > 0){
				            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

				            		$updateData['symptom_pruefer_table'] = "backup_connected_symptoms_details_pruefer";
									$updateData['symptom_reference_table'] = "backup_connected_symptoms_details_reference";

									$updateData['id'] = ($rowData['id'] != "") ? $rowData['id'] : "";

				            		$updateData['master_id'] = ($rowData['master_id'] != "") ? mysqli_real_escape_string($db, $rowData['master_id']) : null;
				            		$updateData['quelle_id'] = ($rowData['quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_id']) : null;
				            		$updateData['arznei_id'] = ($rowData['arznei_id'] != "") ? mysqli_real_escape_string($db, $rowData['arznei_id']) : null;
									$updateData['original_quelle_id'] = ($rowData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['original_quelle_id']) : null;
									$updateData['quelle_code'] = ($rowData['quelle_code'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_code']) : null;
									$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $rowData['Symptomnummer']);
									$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalVon']);
									$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalBis']);
									$updateData['final_version_de'] = mysqli_real_escape_string($db, $rowData['final_version_de']);
									$updateData['final_version_en'] = mysqli_real_escape_string($db, $rowData['final_version_en']);
									$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $rowData['Beschreibung_de']);
									$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $rowData['Beschreibung_en']);
									$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_de']);
									$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_en']);
									$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_de']);
									$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_en']);
									$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_de']);
									$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_en']);
									$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $rowData['searchable_text_de']);
									$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $rowData['searchable_text_en']);
									$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $rowData['bracketedString_de']);
									$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $rowData['bracketedString_en']);
									$updateData['timeString_de'] = mysqli_real_escape_string($db, $rowData['timeString_de']);
									$updateData['timeString_en'] = mysqli_real_escape_string($db, $rowData['timeString_en']);
									$updateData['Fussnote'] = mysqli_real_escape_string($db, $rowData['Fussnote']);
									$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $rowData['EntnommenAus']);
									$updateData['Verweiss'] = mysqli_real_escape_string($db, $rowData['Verweiss']);
									$updateData['Graduierung'] = mysqli_real_escape_string($db, $rowData['Graduierung']);
									$updateData['BereichID'] = mysqli_real_escape_string($db, $rowData['BereichID']);
									$updateData['Kommentar'] = mysqli_real_escape_string($db, $rowData['Kommentar']);
									$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $rowData['Unklarheiten']);
									$updateData['Remedy'] = mysqli_real_escape_string($db, $rowData['Remedy']);
									$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $rowData['symptom_of_different_remedy']);
									$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $rowData['is_symptom_number_mismatch']);
									$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $rowData['is_symptom_appended']);
									$updateData['subChapter'] = mysqli_real_escape_string($db, $rowData['subChapter']);
									$updateData['subSubChapter'] = mysqli_real_escape_string($db, $rowData['subSubChapter']);
									$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $rowData['symptom_edit_comment']);
									$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $rowData['is_final_version_available']);
				            	} else {
				            		// 
				            		// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            		$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$originalConnData['initial_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
									if(mysqli_num_rows($swappedSymptomResult) > 0){
										$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$originalConnData['initial_source_symptom_id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												$updateData['id'] = $backupSetSymptomRow['id'];
												$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
												$updateData['symptom_reference_table'] = 'symptom_reference_backup';

												// quelle_import_backup stores the "master_id" of backup section
												//$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);

												$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
												
												// $updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												
												$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
												$updateData['final_version_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_de']);
												$updateData['final_version_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_en']);
												$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_de']);
												$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_en']);
												$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_de']);
												$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_en']);
												$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_de']);
												$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_en']);
												$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_de']);
												$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_en']);
												$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_de']);
												$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_en']);
												$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_de']);
												$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_en']);
												$updateData['timeString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_de']);
												$updateData['timeString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_en']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
												$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_appended']);
												$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_final_version_available']);
											}
										}
									}
				            	}
		          

								if(!empty($updateData)){
									// If these updating data gets collected from "quelle_import_backup" than master_id and quelle_id information are not there AND they don't need to be updated (So far!)
									$conditionalUpdFields = "";
									if(isset($updateData['master_id']) AND $updateData['master_id'] != "")
										$conditionalUpdFields .= ", master_id = NULLIF('".$updateData['master_id']."', '')";
									if(isset($updateData['quelle_id']) AND $updateData['quelle_id'] != "")
										$conditionalUpdFields .= ", quelle_id = NULLIF('".$updateData['quelle_id']."', '')";

									$updateSwappedSymptomsInMainQuery = "UPDATE quelle_import_test SET arznei_id = NULLIF('".$updateData['arznei_id']."', ''), original_quelle_id = NULLIF('".$updateData['original_quelle_id']."', ''), quelle_code = NULLIF('".$updateData['quelle_code']."', ''), Symptomnummer = NULLIF('".$updateData['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateData['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateData['SeiteOriginalBis']."', ''), final_version_de = NULLIF('".$updateData['final_version_de']."', ''), final_version_en = NULLIF('".$updateData['final_version_en']."', ''), Beschreibung_de = NULLIF('".$updateData['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateData['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateData['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateData['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateData['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateData['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateData['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateData['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateData['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateData['searchable_text_en']."', ''), bracketedString_de = NULLIF('".$updateData['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$updateData['bracketedString_en']."', ''), timeString_de = NULLIF('".$updateData['timeString_de']."', ''), timeString_en = NULLIF('".$updateData['timeString_en']."', ''), Fussnote = NULLIF('".$updateData['Fussnote']."', ''), EntnommenAus = NULLIF('".$updateData['EntnommenAus']."', ''), Verweiss = NULLIF('".$updateData['Verweiss']."', ''), Graduierung = NULLIF('".$updateData['Graduierung']."', ''), BereichID = NULLIF('".$updateData['BereichID']."', ''), Kommentar = NULLIF('".$updateData['Kommentar']."', ''), Unklarheiten = NULLIF('".$updateData['Unklarheiten']."', ''), Remedy = NULLIF('".$updateData['Remedy']."', ''), symptom_of_different_remedy = NULLIF('".$updateData['symptom_of_different_remedy']."', ''), subChapter = NULLIF('".$updateData['subChapter']."', ''), subSubChapter = NULLIF('".$updateData['subSubChapter']."', ''), symptom_edit_comment = NULLIF('".$updateData['symptom_edit_comment']."', ''), is_final_version_available = NULLIF('".$updateData['is_final_version_available']."', ''), is_symptom_number_mismatch = NULLIF('".$updateData['is_symptom_number_mismatch']."', ''), is_symptom_appended = NULLIF('".$updateData['is_symptom_appended']."', '')".$conditionalUpdFields." WHERE id = ".$originalConnData['initial_source_symptom_id'];
		            				$db->query($updateSwappedSymptomsInMainQuery);

		            				$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$originalConnData['initial_source_symptom_id'];
			            			$db->query($symPrueferDeleteQuery);

			            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$originalConnData['initial_source_symptom_id'];
			            			$db->query($symRefDeleteQuery);

			            			/* Insert Symptom_pruefer relation START */
						            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM ".$updateData['symptom_pruefer_table']." where symptom_id = '".$updateData['id']."'");
									if($symptomPrueferResult->num_rows > 0){
										while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
											$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$originalConnData['initial_source_symptom_id']."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
							            	$db->query($mainSymptomPrueferInsertQuery);
										}
									}
									/* Insert Symptom_pruefer relation END */

									/* Insert symptom_reference relation START */
						            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM ".$updateData['symptom_reference_table']." where symptom_id = '".$updateData['id']."'");
									if($symptomReferenceResult->num_rows > 0){
										while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
											$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$originalConnData['initial_source_symptom_id']."', '".$symptomReferenceData['reference_id']."', '".$date."')";
							            	$db->query($mainSymptomReferenceInsertQuery);
										}
									}
									/* Insert symptom_reference relation END */
								}
								// Checking for swapped data and making updates in the main symptom END

								// Checking Comparing symptom
								// Checking for swapped data and making updates in the main symptom START
								$updateData = array();
								/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$originalConnData['comparing_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
								if(mysqli_num_rows($swappedSymptomResult) > 0){
									$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
									$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
									if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0) {
										$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);


										$updateData['id'] = $backupSetSymptomRow['id'];
										$updateData['symptom_pruefer_table'] = 'backup_sets_swapped_symptom_pruefer';
										$updateData['symptom_reference_table'] = 'backup_sets_swapped_symptom_reference';

										$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
										$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
										$updateData['quelle_id'] = ($backupSetSymptomRow['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_id']) : null;
										$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
										$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
										$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
										$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
										$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
										$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
										$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
										$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
										$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
										$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
										$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
										$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
										$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
										$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
										$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
										$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
										$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
										$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
										$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
										$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
										$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
										$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
										$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
										$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
									} else {
										// Get the first symptom set from the backups of this comparison
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$originalConnData['comparing_source_symptom_id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												$updateData['id'] = $backupSetSymptomRow['id'];
												$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
												$updateData['symptom_reference_table'] = 'symptom_reference_backup';

												$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
												$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
												$updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
												$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
												$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
												$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
												$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
												$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
												$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
											}
										}
									}
								}*/

								// 
								// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
								$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND original_symptom_id = '".$originalConnData['comparing_source_symptom_id']."'");
				            	if($backupConnectedSymptomQuery->num_rows > 0){
				            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

				            		$updateData['symptom_pruefer_table'] = "backup_connected_symptoms_details_pruefer";
									$updateData['symptom_reference_table'] = "backup_connected_symptoms_details_reference";

									$updateData['id'] = ($rowData['id'] != "") ? $rowData['id'] : "";

				            		$updateData['master_id'] = ($rowData['master_id'] != "") ? mysqli_real_escape_string($db, $rowData['master_id']) : null;
				            		$updateData['quelle_id'] = ($rowData['quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_id']) : null;
				            		$updateData['arznei_id'] = ($rowData['arznei_id'] != "") ? mysqli_real_escape_string($db, $rowData['arznei_id']) : null;
									$updateData['original_quelle_id'] = ($rowData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['original_quelle_id']) : null;
									$updateData['quelle_code'] = ($rowData['quelle_code'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_code']) : null;
									$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $rowData['Symptomnummer']);
									$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalVon']);
									$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalBis']);
									$updateData['final_version_de'] = mysqli_real_escape_string($db, $rowData['final_version_de']);
									$updateData['final_version_en'] = mysqli_real_escape_string($db, $rowData['final_version_en']);
									$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $rowData['Beschreibung_de']);
									$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $rowData['Beschreibung_en']);
									$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_de']);
									$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_en']);
									$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_de']);
									$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_en']);
									$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_de']);
									$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_en']);
									$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $rowData['searchable_text_de']);
									$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $rowData['searchable_text_en']);
									$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $rowData['bracketedString_de']);
									$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $rowData['bracketedString_en']);
									$updateData['timeString_de'] = mysqli_real_escape_string($db, $rowData['timeString_de']);
									$updateData['timeString_en'] = mysqli_real_escape_string($db, $rowData['timeString_en']);
									$updateData['Fussnote'] = mysqli_real_escape_string($db, $rowData['Fussnote']);
									$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $rowData['EntnommenAus']);
									$updateData['Verweiss'] = mysqli_real_escape_string($db, $rowData['Verweiss']);
									$updateData['Graduierung'] = mysqli_real_escape_string($db, $rowData['Graduierung']);
									$updateData['BereichID'] = mysqli_real_escape_string($db, $rowData['BereichID']);
									$updateData['Kommentar'] = mysqli_real_escape_string($db, $rowData['Kommentar']);
									$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $rowData['Unklarheiten']);
									$updateData['Remedy'] = mysqli_real_escape_string($db, $rowData['Remedy']);
									$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $rowData['symptom_of_different_remedy']);
									$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $rowData['is_symptom_number_mismatch']);
									$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $rowData['is_symptom_appended']);
									$updateData['subChapter'] = mysqli_real_escape_string($db, $rowData['subChapter']);
									$updateData['subSubChapter'] = mysqli_real_escape_string($db, $rowData['subSubChapter']);
									$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $rowData['symptom_edit_comment']);
									$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $rowData['is_final_version_available']);
				            	}
				            	else
				            	{
				            		// 
				            		// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            		$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$originalConnData['comparing_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
									if(mysqli_num_rows($swappedSymptomResult) > 0){
										$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
										// Here joining is made on backup table's quelle_id not with the original_quelle_id
										$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
										if(mysqli_num_rows($importMasterBackupResult) > 0){
											$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
											$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$originalConnData['comparing_source_symptom_id']."'");
											if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
												$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

												$updateData['id'] = $backupSetSymptomRow['id'];
												$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
												$updateData['symptom_reference_table'] = 'symptom_reference_backup';

												// quelle_import_backup stores the "master_id" of backup section
												// $updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
												
												$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
												
												// $updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												
												$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
												$updateData['final_version_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_de']);
												$updateData['final_version_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_en']);
												$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_de']);
												$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_en']);
												$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_de']);
												$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_en']);
												$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_de']);
												$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_en']);
												$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_de']);
												$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_en']);
												$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_de']);
												$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_en']);
												$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_de']);
												$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_en']);
												$updateData['timeString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_de']);
												$updateData['timeString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_en']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
												$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_appended']);
												$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_final_version_available']);
											}
										}
									}
				            	}

								if(!empty($updateData)){
									// If these updating data gets collected from "quelle_import_backup" than master_id and quelle_id information are not there AND they don't need to be updated (So far!)
									$conditionalUpdFields = "";
									if(isset($updateData['master_id']) AND $updateData['master_id'] != "")
										$conditionalUpdFields .= ", master_id = NULLIF('".$updateData['master_id']."', '')";
									if(isset($updateData['quelle_id']) AND $updateData['quelle_id'] != "")
										$conditionalUpdFields .= ", quelle_id = NULLIF('".$updateData['quelle_id']."', '')";

									$updateSwappedSymptomsInMainQuery = "UPDATE quelle_import_test SET arznei_id = NULLIF('".$updateData['arznei_id']."', ''), original_quelle_id = NULLIF('".$updateData['original_quelle_id']."', ''), quelle_code = NULLIF('".$updateData['quelle_code']."', ''), Symptomnummer = NULLIF('".$updateData['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateData['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateData['SeiteOriginalBis']."', ''), final_version_de = NULLIF('".$updateData['final_version_de']."', ''), final_version_en = NULLIF('".$updateData['final_version_en']."', ''), Beschreibung_de = NULLIF('".$updateData['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateData['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateData['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateData['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateData['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateData['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateData['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateData['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateData['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateData['searchable_text_en']."', ''), bracketedString_de = NULLIF('".$updateData['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$updateData['bracketedString_en']."', ''), timeString_de = NULLIF('".$updateData['timeString_de']."', ''), timeString_en = NULLIF('".$updateData['timeString_en']."', ''), Fussnote = NULLIF('".$updateData['Fussnote']."', ''), EntnommenAus = NULLIF('".$updateData['EntnommenAus']."', ''), Verweiss = NULLIF('".$updateData['Verweiss']."', ''), Graduierung = NULLIF('".$updateData['Graduierung']."', ''), BereichID = NULLIF('".$updateData['BereichID']."', ''), Kommentar = NULLIF('".$updateData['Kommentar']."', ''), Unklarheiten = NULLIF('".$updateData['Unklarheiten']."', ''), Remedy = NULLIF('".$updateData['Remedy']."', ''), symptom_of_different_remedy = NULLIF('".$updateData['symptom_of_different_remedy']."', ''), subChapter = NULLIF('".$updateData['subChapter']."', ''), subSubChapter = NULLIF('".$updateData['subSubChapter']."', ''), symptom_edit_comment = NULLIF('".$updateData['symptom_edit_comment']."', ''), is_final_version_available = NULLIF('".$updateData['is_final_version_available']."', ''), is_symptom_number_mismatch = NULLIF('".$updateData['is_symptom_number_mismatch']."', ''), is_symptom_appended = NULLIF('".$updateData['is_symptom_appended']."', '')".$conditionalUpdFields." WHERE id = ".$originalConnData['comparing_source_symptom_id'];
		            				$db->query($updateSwappedSymptomsInMainQuery);

		            				$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$originalConnData['comparing_source_symptom_id'];
			            			$db->query($symPrueferDeleteQuery);

			            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$originalConnData['comparing_source_symptom_id'];
			            			$db->query($symRefDeleteQuery);

			            			/* Insert Symptom_pruefer relation START */
						            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM ".$updateData['symptom_pruefer_table']." where symptom_id = '".$updateData['id']."'");
									if($symptomPrueferResult->num_rows > 0){
										while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
											$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$originalConnData['comparing_source_symptom_id']."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
							            	$db->query($mainSymptomPrueferInsertQuery);
										}
									}
									/* Insert Symptom_pruefer relation END */

									/* Insert symptom_reference relation START */
						            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM ".$updateData['symptom_reference_table']." where symptom_id = '".$updateData['id']."'");
									if($symptomReferenceResult->num_rows > 0){
										while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
											$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$originalConnData['comparing_source_symptom_id']."', '".$symptomReferenceData['reference_id']."', '".$date."')";
							            	$db->query($mainSymptomReferenceInsertQuery);
										}
									}
									/* Insert symptom_reference relation END */
								}
								// Checking for swapped data and making updates in the main symptom END

		            		}
		            	}
		            }

		        	$arzneiQuelleResult = $db->query("SELECT arznei_id FROM arznei_quelle WHERE arznei_id = '".$arznei_id."' AND quelle_id = '".$newQuelleId."'");
					if($arzneiQuelleResult->num_rows == 0){
						$arzneiQuelleInsertQuery="INSERT INTO arznei_quelle (arznei_id, quelle_id, ersteller_datum) VALUES ('".$arznei_id."', '".$newQuelleId."', '".$date."')";
		        		$db->query($arzneiQuelleInsertQuery);  
					}

					$symptomResult = mysqli_query($db, "SELECT * FROM quelle_import_backup WHERE quelle_id = ".$quelle_id." AND arznei_id = ".$arznei_id);
					if(mysqli_num_rows($symptomResult) > 0){
						while($symptomData = mysqli_fetch_array($symptomResult)){
							$symptomData['original_symptom_id'] = ($symptomData['original_symptom_id'] != "") ? mysqli_real_escape_string($db, $symptomData['original_symptom_id']) : null;
							$symptomData['master_id'] = ($newQuelleMasterId != "") ? $newQuelleMasterId : null;
							$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
							$symptomData['quelle_id'] = ($newQuelleId != "") ? $newQuelleId : null;
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
							$symptomData['ip_address'] = mysqli_real_escape_string($db, $symptomData['ip_address']);
							$symptomData['stand'] = mysqli_real_escape_string($db, $symptomData['stand']);
							$symptomData['bearbeiter_id'] = mysqli_real_escape_string($db, $symptomData['bearbeiter_id']);
							$symptomData['ersteller_datum'] = mysqli_real_escape_string($db, $symptomData['ersteller_datum']);
							$symptomData['ersteller_id'] = mysqli_real_escape_string($db, $symptomData['ersteller_id']);

							// Checking for swapped data and making updates in the main symptom START
							$updateData = array();
							/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$symptomData['original_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
							if(mysqli_num_rows($swappedSymptomResult) > 0){
								$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
								$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
								if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0) {
									$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);


									$updateData['id'] = $backupSetSymptomRow['id'];
									$updateData['symptom_pruefer_table'] = 'backup_sets_swapped_symptom_pruefer';
									$updateData['symptom_reference_table'] = 'backup_sets_swapped_symptom_reference';

									$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
									$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
									$updateData['quelle_id'] = ($backupSetSymptomRow['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_id']) : null;
									$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
									$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
									$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
									$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
									$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
									$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
									$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
									$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
									$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
									$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
									$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
									$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
									$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
									$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
									$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
									$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
									$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
									$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
									$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
									$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
									$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
									$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
									$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
									$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
								} else {
									// Get the first symptom set from the backups of this comparison
									// Here joining is made on backup table's quelle_id not with the original_quelle_id
									$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
									if(mysqli_num_rows($importMasterBackupResult) > 0){
										$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
										$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$symptomData['original_symptom_id']."'");
										if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
											$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

											$updateData['id'] = $backupSetSymptomRow['id'];
											$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
											$updateData['symptom_reference_table'] = 'symptom_reference_backup';

											$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
											$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
											$updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
											$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
											$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
											$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
											$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
											$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
											$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
											$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
											$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
											$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
											$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
											$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
											$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
											$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
											$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
											$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
											$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
											$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
											$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
											$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
											$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
											$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
											$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
											$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
											$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
										}
									}
								}
							}*/

							// 
							// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
							$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND original_symptom_id = '".$symptomData['original_symptom_id']."'");
			            	if($backupConnectedSymptomQuery->num_rows > 0){
			            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

			            		$updateData['symptom_pruefer_table'] = "backup_connected_symptoms_details_pruefer";
								$updateData['symptom_reference_table'] = "backup_connected_symptoms_details_reference";

								$updateData['id'] = ($rowData['id'] != "") ? $rowData['id'] : "";

			            		$updateData['master_id'] = ($rowData['master_id'] != "") ? mysqli_real_escape_string($db, $rowData['master_id']) : null;
			            		$updateData['quelle_id'] = ($rowData['quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_id']) : null;
			            		$updateData['arznei_id'] = ($rowData['arznei_id'] != "") ? mysqli_real_escape_string($db, $rowData['arznei_id']) : null;
								$updateData['original_quelle_id'] = ($rowData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['original_quelle_id']) : null;
								$updateData['quelle_code'] = ($rowData['quelle_code'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_code']) : null;
								$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $rowData['Symptomnummer']);
								$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalVon']);
								$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalBis']);
								$updateData['final_version_de'] = mysqli_real_escape_string($db, $rowData['final_version_de']);
								$updateData['final_version_en'] = mysqli_real_escape_string($db, $rowData['final_version_en']);
								$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $rowData['Beschreibung_de']);
								$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $rowData['Beschreibung_en']);
								$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_de']);
								$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_en']);
								$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_de']);
								$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_en']);
								$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_de']);
								$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_en']);
								$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $rowData['searchable_text_de']);
								$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $rowData['searchable_text_en']);
								$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $rowData['bracketedString_de']);
								$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $rowData['bracketedString_en']);
								$updateData['timeString_de'] = mysqli_real_escape_string($db, $rowData['timeString_de']);
								$updateData['timeString_en'] = mysqli_real_escape_string($db, $rowData['timeString_en']);
								$updateData['Fussnote'] = mysqli_real_escape_string($db, $rowData['Fussnote']);
								$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $rowData['EntnommenAus']);
								$updateData['Verweiss'] = mysqli_real_escape_string($db, $rowData['Verweiss']);
								$updateData['Graduierung'] = mysqli_real_escape_string($db, $rowData['Graduierung']);
								$updateData['BereichID'] = mysqli_real_escape_string($db, $rowData['BereichID']);
								$updateData['Kommentar'] = mysqli_real_escape_string($db, $rowData['Kommentar']);
								$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $rowData['Unklarheiten']);
								$updateData['Remedy'] = mysqli_real_escape_string($db, $rowData['Remedy']);
								$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $rowData['symptom_of_different_remedy']);
								$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $rowData['is_symptom_number_mismatch']);
								$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $rowData['is_symptom_appended']);
								$updateData['subChapter'] = mysqli_real_escape_string($db, $rowData['subChapter']);
								$updateData['subSubChapter'] = mysqli_real_escape_string($db, $rowData['subSubChapter']);
								$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $rowData['symptom_edit_comment']);
								$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $rowData['is_final_version_available']);
			            	}
			            	else
			            	{
			            		// 
				            	// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            	$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$symptomData['original_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
								if(mysqli_num_rows($swappedSymptomResult) > 0){
									$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
									// Here joining is made on backup table's quelle_id not with the original_quelle_id
									$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
									if(mysqli_num_rows($importMasterBackupResult) > 0){
										$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
										$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$symptomData['original_symptom_id']."'");
										if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
											$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

											$updateData['id'] = $backupSetSymptomRow['id'];
											$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
											$updateData['symptom_reference_table'] = 'symptom_reference_backup';

											// quelle_import_backup stores the "master_id" of backup section
											// $updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
											
											$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
											
											// $updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
											
											$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
											$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
											$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
											$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
											$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
											$updateData['final_version_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_de']);
											$updateData['final_version_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_en']);
											$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_de']);
											$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_en']);
											$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_de']);
											$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_en']);
											$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_de']);
											$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_en']);
											$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_de']);
											$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_en']);
											$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_de']);
											$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_en']);
											$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_de']);
											$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_en']);
											$updateData['timeString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_de']);
											$updateData['timeString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_en']);
											$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
											$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
											$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
											$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
											$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
											$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
											$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
											$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
											$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
											$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
											$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
											$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
											$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
											$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_appended']);
											$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_final_version_available']);
										}
									}
								}
			            	}

							if(!empty($updateData)){
								// If these updating data gets collected from "quelle_import_backup" than master_id and quelle_id information are not there AND they don't need to be updated (So far!)
								$conditionalUpdFields = "";
								if(isset($updateData['master_id']) AND $updateData['master_id'] != "")
									$conditionalUpdFields .= ", master_id = NULLIF('".$updateData['master_id']."', '')";
								if(isset($updateData['quelle_id']) AND $updateData['quelle_id'] != "")
									$conditionalUpdFields .= ", quelle_id = NULLIF('".$updateData['quelle_id']."', '')";

								$updateSwappedSymptomsInMainQuery = "UPDATE quelle_import_test SET arznei_id = NULLIF('".$updateData['arznei_id']."', ''), original_quelle_id = NULLIF('".$updateData['original_quelle_id']."', ''), quelle_code = NULLIF('".$updateData['quelle_code']."', ''), Symptomnummer = NULLIF('".$updateData['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateData['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateData['SeiteOriginalBis']."', ''), final_version_de = NULLIF('".$updateData['final_version_de']."', ''), final_version_en = NULLIF('".$updateData['final_version_en']."', ''), Beschreibung_de = NULLIF('".$updateData['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateData['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateData['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateData['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateData['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateData['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateData['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateData['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateData['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateData['searchable_text_en']."', ''), bracketedString_de = NULLIF('".$updateData['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$updateData['bracketedString_en']."', ''), timeString_de = NULLIF('".$updateData['timeString_de']."', ''), timeString_en = NULLIF('".$updateData['timeString_en']."', ''), Fussnote = NULLIF('".$updateData['Fussnote']."', ''), EntnommenAus = NULLIF('".$updateData['EntnommenAus']."', ''), Verweiss = NULLIF('".$updateData['Verweiss']."', ''), Graduierung = NULLIF('".$updateData['Graduierung']."', ''), BereichID = NULLIF('".$updateData['BereichID']."', ''), Kommentar = NULLIF('".$updateData['Kommentar']."', ''), Unklarheiten = NULLIF('".$updateData['Unklarheiten']."', ''), Remedy = NULLIF('".$updateData['Remedy']."', ''), symptom_of_different_remedy = NULLIF('".$updateData['symptom_of_different_remedy']."', ''), subChapter = NULLIF('".$updateData['subChapter']."', ''), subSubChapter = NULLIF('".$updateData['subSubChapter']."', ''), symptom_edit_comment = NULLIF('".$updateData['symptom_edit_comment']."', ''), is_final_version_available = NULLIF('".$updateData['is_final_version_available']."', ''), is_symptom_number_mismatch = NULLIF('".$updateData['is_symptom_number_mismatch']."', ''), is_symptom_appended = NULLIF('".$updateData['is_symptom_appended']."', '')".$conditionalUpdFields." WHERE id = ".$symptomData['original_symptom_id'];
	            				$db->query($updateSwappedSymptomsInMainQuery);

	            				$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$symptomData['original_symptom_id'];
		            			$db->query($symPrueferDeleteQuery);

		            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$symptomData['original_symptom_id'];
		            			$db->query($symRefDeleteQuery);

		            			/* Insert Symptom_pruefer relation START */
					            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM ".$updateData['symptom_pruefer_table']." where symptom_id = '".$updateData['id']."'");
								if($symptomPrueferResult->num_rows > 0){
									while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
										$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$symptomData['original_symptom_id']."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
						            	$db->query($mainSymptomPrueferInsertQuery);
									}
								}
								/* Insert Symptom_pruefer relation END */

								/* Insert symptom_reference relation START */
					            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM ".$updateData['symptom_reference_table']." where symptom_id = '".$updateData['id']."'");
								if($symptomReferenceResult->num_rows > 0){
									while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
										$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$symptomData['original_symptom_id']."', '".$symptomReferenceData['reference_id']."', '".$date."')";
						            	$db->query($mainSymptomReferenceInsertQuery);
									}
								}
								/* Insert symptom_reference relation END */
							}
							// Checking for swapped data and making updates in the main symptom END
							// 
							
							// Checking do we need to add original symptom id
							$sourceResult = mysqli_query($db,"SELECT Q.quelle_type_id FROM quelle AS Q WHERE Q.quelle_id = '".$newQuelleId."'");
							if(mysqli_num_rows($sourceResult) > 0){
								$sourceRow = mysqli_fetch_assoc($sourceResult);
								$sourceType = $sourceRow['quelle_type_id'];
							}
							$symptomData['original_symptom_id'] = (isset($sourceType) AND $sourceType == 3) ? $symptomData['original_symptom_id'] : null;


							$newSymptomInsertQuery="INSERT INTO quelle_import_test (master_id, original_symptom_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended, ip_address, stand, bearbeiter_id, ersteller_datum, ersteller_id) VALUES (NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''), NULLIF('".$symptomData['ip_address']."', ''), NULLIF('".$symptomData['stand']."', ''), NULLIF('".$symptomData['bearbeiter_id']."', ''), NULLIF('".$symptomData['ersteller_datum']."', ''), NULLIF('".$symptomData['ersteller_id']."', ''))";
					
				            $db->query($newSymptomInsertQuery);
				            $newSymtomId = mysqli_insert_id($db);


				            if($newSymtomId != ""){

				            	$backupSymptomPrueferQuery = $db->query("SELECT * FROM symptom_pruefer_backup WHERE symptom_id = '".$symptomData['id']."'");
								if($backupSymptomPrueferQuery->num_rows > 0){
									while($backupSymPrueferData = mysqli_fetch_array($backupSymptomPrueferQuery)){
										$backupSymPrueferData['symptom_id'] = $newSymtomId;
										$backupSymPrueferData['pruefer_id'] = mysqli_real_escape_string($db, $backupSymPrueferData['pruefer_id']);
										$backupSymPrueferData['stand'] = mysqli_real_escape_string($db, $backupSymPrueferData['stand']);
										$backupSymPrueferData['bearbeiter_id'] = mysqli_real_escape_string($db, $backupSymPrueferData['bearbeiter_id']);
										$backupSymPrueferData['ersteller_datum'] = mysqli_real_escape_string($db, $backupSymPrueferData['ersteller_datum']);
										$backupSymPrueferData['ersteller_id'] = mysqli_real_escape_string($db, $backupSymPrueferData['ersteller_id']);

										$newSymptomPrueferInsertQuery="INSERT INTO symptom_pruefer (symptom_id, pruefer_id, stand, bearbeiter_id, ersteller_datum, ersteller_id) VALUES (NULLIF('".$backupSymPrueferData['symptom_id']."', ''), NULLIF('".$backupSymPrueferData['pruefer_id']."', ''), NULLIF('".$backupSymPrueferData['stand']."', ''), NULLIF('".$backupSymPrueferData['bearbeiter_id']."', ''), NULLIF('".$backupSymPrueferData['ersteller_datum']."', ''), NULLIF('".$backupSymPrueferData['ersteller_id']."', ''))";
				            			$db->query($newSymptomPrueferInsertQuery);
									}
								}

								$backupSymptomReferenceQuery = $db->query("SELECT * FROM symptom_reference_backup WHERE symptom_id = '".$symptomData['id']."'");
								if($backupSymptomReferenceQuery->num_rows > 0){
									while($backupSymReferenceData = mysqli_fetch_array($backupSymptomReferenceQuery)){
										$backupSymReferenceData['symptom_id'] = $newSymtomId;
										$backupSymReferenceData['reference_id'] = mysqli_real_escape_string($db, $backupSymReferenceData['reference_id']);
										$backupSymReferenceData['stand'] = mysqli_real_escape_string($db, $backupSymReferenceData['stand']);
										$backupSymReferenceData['bearbeiter_id'] = mysqli_real_escape_string($db, $backupSymReferenceData['bearbeiter_id']);
										$backupSymReferenceData['ersteller_datum'] = mysqli_real_escape_string($db, $backupSymReferenceData['ersteller_datum']);
										$backupSymReferenceData['ersteller_id'] = mysqli_real_escape_string($db, $backupSymReferenceData['ersteller_id']);

										$newSymptomReferenceInsertQuery="INSERT INTO symptom_reference (symptom_id, reference_id, stand, bearbeiter_id, ersteller_datum, ersteller_id) VALUES (NULLIF('".$backupSymReferenceData['symptom_id']."', ''), NULLIF('".$backupSymReferenceData['reference_id']."', ''), NULLIF('".$backupSymReferenceData['stand']."', ''), NULLIF('".$backupSymReferenceData['bearbeiter_id']."', ''), NULLIF('".$backupSymReferenceData['ersteller_datum']."', ''), NULLIF('".$backupSymReferenceData['ersteller_id']."', ''))";
				            			$db->query($newSymptomReferenceInsertQuery);
									}
								}

				            	$backupConnQuery = $db->query("SELECT * FROM symptom_connections_backup WHERE ((initial_source_symptom_id = '".$symptomData['id']."' AND initial_source_type = 'backup') OR (comparing_source_symptom_id = '".$symptomData['id']."' AND comparing_source_type = 'backup')) AND (is_connected = 1 OR is_pasted = 1) AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'AND source_arznei_id = '".$arznei_id."'");
								if($backupConnQuery->num_rows > 0){
									while($backupConnData = mysqli_fetch_array($backupConnQuery)){

										$backupConnData['conversion_initial_source_id'] = ($backupConnData['conversion_initial_source_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['conversion_initial_source_id']) : null;
										$backupConnData['conversion_comparing_source_id'] = ($backupConnData['conversion_comparing_source_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['conversion_comparing_source_id']) : null;

										if($backupConnData['initial_source_symptom_id'] == $symptomData['id']){
											$backupConnData['initial_source_id'] = ($newQuelleId != "") ? $newQuelleId : null;
				            				$backupConnData['comparing_source_id'] = ($backupConnData['comparing_source_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_id']) : null;
				            				$backupConnData['initial_source_symptom_id'] = ($newSymtomId != "") ? $newSymtomId : null;
				            				$backupConnData['comparing_source_symptom_id'] = ($backupConnData['comparing_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_symptom_id']) : null;
										} else {
											$backupConnData['initial_source_id'] = ($backupConnData['initial_source_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_id']) : null;
				            				$backupConnData['comparing_source_id'] = ($newQuelleId != "") ? $newQuelleId : null;
				            				$backupConnData['initial_source_symptom_id'] = ($backupConnData['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_symptom_id']) : null;
				            				$backupConnData['comparing_source_symptom_id'] = ($newSymtomId != "") ? $newSymtomId : null;
										}
										$backupConnData['is_initial_source'] = ($backupConnData['is_initial_source'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_initial_source']) : null;
				            			$backupConnData['source_arznei_id'] = ($backupConnData['source_arznei_id'] != "") ? mysqli_real_escape_string($db, $backupConnData['source_arznei_id']) : null;
				            			
				            			$backupConnData['initial_source_code'] = ($backupConnData['initial_source_code'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_code']) : null;
				            			$backupConnData['comparing_source_code'] = ($backupConnData['comparing_source_code'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_code']) : null;
				            			
				            			$backupConnData['initial_source_symptom_highlighted_de'] = ($backupConnData['initial_source_symptom_highlighted_de'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_symptom_highlighted_de']) : null;
				            			$backupConnData['initial_source_symptom_highlighted_en'] = ($backupConnData['initial_source_symptom_highlighted_en'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_symptom_highlighted_en']) : null;
				            			$backupConnData['comparing_source_symptom_highlighted_de'] = ($backupConnData['comparing_source_symptom_highlighted_de'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_symptom_highlighted_de']) : null;
				            			$backupConnData['comparing_source_symptom_highlighted_en'] = ($backupConnData['comparing_source_symptom_highlighted_en'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_symptom_highlighted_en']) : null;
				            			$backupConnData['initial_source_symptom_de'] = ($backupConnData['initial_source_symptom_de'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_symptom_de']) : null;
				            			$backupConnData['initial_source_symptom_en'] = ($backupConnData['initial_source_symptom_en'] != "") ? mysqli_real_escape_string($db, $backupConnData['initial_source_symptom_en']) : null;
				            			$backupConnData['comparing_source_symptom_de'] = ($backupConnData['comparing_source_symptom_de'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_symptom_de']) : null;
				            			$backupConnData['comparing_source_symptom_en'] = ($backupConnData['comparing_source_symptom_en'] != "") ? mysqli_real_escape_string($db, $backupConnData['comparing_source_symptom_en']) : null;
				            			
				            			$backupConnData['connection_language'] = ($backupConnData['connection_language'] != "") ? mysqli_real_escape_string($db, $backupConnData['connection_language']) : null;

				            			$backupConnData['matching_percentage'] = ($backupConnData['matching_percentage'] != "") ? mysqli_real_escape_string($db, $backupConnData['matching_percentage']) : 0;
				            			$backupConnData['is_connected'] = ($backupConnData['is_connected'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_connected']) : null;
				            			$backupConnData['is_ns_connect'] = ($backupConnData['is_ns_connect'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_ns_connect']) : null;
				            			$backupConnData['ns_connect_note'] = ($backupConnData['ns_connect_note'] != "") ? mysqli_real_escape_string($db, $backupConnData['ns_connect_note']) : null;
				            			$backupConnData['is_pasted'] = ($backupConnData['is_pasted'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_pasted']) : null;
				            			$backupConnData['is_ns_paste'] = ($backupConnData['is_ns_paste'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_ns_paste']) : null;
				            			$backupConnData['ns_paste_note'] = ($backupConnData['ns_paste_note'] != "") ? mysqli_real_escape_string($db, $backupConnData['ns_paste_note']) : null;
				            			$backupConnData['is_saved'] = ($backupConnData['is_saved'] != "") ? mysqli_real_escape_string($db, $backupConnData['is_saved']) : null;
				            			$backupConnData['connection_or_paste_type'] = ($backupConnData['connection_or_paste_type'] != "") ? mysqli_real_escape_string($db, $backupConnData['connection_or_paste_type']) : null;

										$newBackupSymConnInsertQuery="INSERT INTO symptom_connections (is_initial_source, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, is_saved, connection_or_paste_type) VALUES (NULLIF('".$backupConnData['is_initial_source']."', ''), NULLIF('".$backupConnData['source_arznei_id']."', ''), NULLIF('".$backupConnData['initial_source_id']."', ''), NULLIF('".$backupConnData['comparing_source_id']."', ''), NULLIF('".$backupConnData['conversion_initial_source_id']."', ''), NULLIF('".$backupConnData['conversion_comparing_source_id']."', ''), NULLIF('".$backupConnData['initial_source_code']."', ''), NULLIF('".$backupConnData['comparing_source_code']."', ''), NULLIF('".$backupConnData['initial_source_symptom_id']."', ''), NULLIF('".$backupConnData['comparing_source_symptom_id']."', ''), NULLIF('".$backupConnData['initial_source_symptom_highlighted_de']."', ''), NULLIF('".$backupConnData['initial_source_symptom_highlighted_en']."', ''), NULLIF('".$backupConnData['comparing_source_symptom_highlighted_de']."', ''), NULLIF('".$backupConnData['comparing_source_symptom_highlighted_en']."', ''), NULLIF('".$backupConnData['initial_source_symptom_de']."', ''), NULLIF('".$backupConnData['initial_source_symptom_en']."', ''), NULLIF('".$backupConnData['comparing_source_symptom_de']."', ''), NULLIF('".$backupConnData['comparing_source_symptom_en']."', ''), NULLIF('".$backupConnData['connection_language']."', ''), '".$backupConnData['matching_percentage']."', NULLIF('".$backupConnData['is_connected']."', ''), NULLIF('".$backupConnData['is_ns_connect']."', ''), NULLIF('".$backupConnData['ns_connect_note']."', ''), NULLIF('".$backupConnData['is_pasted']."', ''), NULLIF('".$backupConnData['is_ns_paste']."', ''), NULLIF('".$backupConnData['ns_paste_note']."', ''), NULLIF('".$backupConnData['is_saved']."', ''), NULLIF('".$backupConnData['connection_or_paste_type']."', ''))";
			            				$db->query($newBackupSymConnInsertQuery);

			            				// // Delete this symptom's previous version in the backup_connected_symptoms_details table and add the current one
			            				// deletePreviousAddNewBackupConnectedSymptom($backupConnData['initial_source_symptom_id'], $backupSCData['sc_id']);
			            				// deletePreviousAddNewBackupConnectedSymptom($backupConnData['comparing_source_symptom_id'], $backupSCData['sc_id']);
										

										if($backupConnData['initial_source_type'] == "original") {
											// Checking Initial symptom
						            		// Checking for swapped data and making updates in the main symptom START
											$updateData = array();
											/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$backupConnData['initial_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
											if(mysqli_num_rows($swappedSymptomResult) > 0){
												$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
												$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
												if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0) {
													$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);


													$updateData['id'] = $backupSetSymptomRow['id'];
													$updateData['symptom_pruefer_table'] = 'backup_sets_swapped_symptom_pruefer';
													$updateData['symptom_reference_table'] = 'backup_sets_swapped_symptom_reference';

													$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
													$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
													$updateData['quelle_id'] = ($backupSetSymptomRow['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_id']) : null;
													$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
													$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
													$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
													$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
													$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
													$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
													$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
													$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
													$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
													$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
													$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
													$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
													$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
													$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
													$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
													$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
													$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
													$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
													$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
													$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
													$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
													$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
													$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
													$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
												} else {
													// Get the first symptom set from the backups of this comparison
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$backupConnData['initial_source_symptom_id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															$updateData['id'] = $backupSetSymptomRow['id'];
															$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
															$updateData['symptom_reference_table'] = 'symptom_reference_backup';

															$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
															$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
															$updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
															$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
															$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
															$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
															$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
															$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
															$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
															$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
															$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
															$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
															$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
															$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
															$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
															$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
															$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
															$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
															$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
															$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
															$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
															$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
															$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
															$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
															$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
														}
													}
												}
											}*/

											// 
											// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
											$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND original_symptom_id = '".$backupConnData['initial_source_symptom_id']."'");
							            	if($backupConnectedSymptomQuery->num_rows > 0){
							            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

							            		$updateData['symptom_pruefer_table'] = "backup_connected_symptoms_details_pruefer";
												$updateData['symptom_reference_table'] = "backup_connected_symptoms_details_reference";

												$updateData['id'] = ($rowData['id'] != "") ? $rowData['id'] : "";

							            		$updateData['master_id'] = ($rowData['master_id'] != "") ? mysqli_real_escape_string($db, $rowData['master_id']) : null;
							            		$updateData['quelle_id'] = ($rowData['quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_id']) : null;
							            		$updateData['arznei_id'] = ($rowData['arznei_id'] != "") ? mysqli_real_escape_string($db, $rowData['arznei_id']) : null;
												$updateData['original_quelle_id'] = ($rowData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($rowData['quelle_code'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $rowData['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalBis']);
												$updateData['final_version_de'] = mysqli_real_escape_string($db, $rowData['final_version_de']);
												$updateData['final_version_en'] = mysqli_real_escape_string($db, $rowData['final_version_en']);
												$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $rowData['Beschreibung_de']);
												$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $rowData['Beschreibung_en']);
												$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_de']);
												$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_en']);
												$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_de']);
												$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_en']);
												$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_de']);
												$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_en']);
												$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $rowData['searchable_text_de']);
												$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $rowData['searchable_text_en']);
												$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $rowData['bracketedString_de']);
												$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $rowData['bracketedString_en']);
												$updateData['timeString_de'] = mysqli_real_escape_string($db, $rowData['timeString_de']);
												$updateData['timeString_en'] = mysqli_real_escape_string($db, $rowData['timeString_en']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $rowData['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $rowData['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $rowData['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $rowData['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $rowData['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $rowData['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $rowData['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $rowData['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $rowData['symptom_of_different_remedy']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $rowData['is_symptom_number_mismatch']);
												$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $rowData['is_symptom_appended']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $rowData['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $rowData['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $rowData['symptom_edit_comment']);
												$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $rowData['is_final_version_available']);
							            	}
							            	else
							            	{
							            		// 
				            					// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            					$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$backupConnData['initial_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
												if(mysqli_num_rows($swappedSymptomResult) > 0){
													$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$backupConnData['initial_source_symptom_id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															$updateData['id'] = $backupSetSymptomRow['id'];
															$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
															$updateData['symptom_reference_table'] = 'symptom_reference_backup';

															// quelle_import_backup stores the "master_id" of backup section
															// $updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
															
															$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
															
															// $updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															
															$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
															$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
															$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
															$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
															$updateData['final_version_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_de']);
															$updateData['final_version_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_en']);
															$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_de']);
															$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_en']);
															$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_de']);
															$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_en']);
															$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_de']);
															$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_en']);
															$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_de']);
															$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_en']);
															$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_de']);
															$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_en']);
															$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_de']);
															$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_en']);
															$updateData['timeString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_de']);
															$updateData['timeString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_en']);
															$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
															$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
															$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
															$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
															$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
															$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
															$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
															$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
															$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
															$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
															$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
															$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
															$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
															$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_appended']);
															$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_final_version_available']);
														}
													}
												}
							            	}

											if(!empty($updateData)){
												// If these updating data gets collected from "quelle_import_backup" than master_id and quelle_id information are not there AND they don't need to be updated (So far!)
												$conditionalUpdFields = "";
												if(isset($updateData['master_id']) AND $updateData['master_id'] != "")
													$conditionalUpdFields .= ", master_id = NULLIF('".$updateData['master_id']."', '')";
												if(isset($updateData['quelle_id']) AND $updateData['quelle_id'] != "")
													$conditionalUpdFields .= ", quelle_id = NULLIF('".$updateData['quelle_id']."', '')";

												$updateSwappedSymptomsInMainQuery = "UPDATE quelle_import_test SET arznei_id = NULLIF('".$updateData['arznei_id']."', ''), original_quelle_id = NULLIF('".$updateData['original_quelle_id']."', ''), quelle_code = NULLIF('".$updateData['quelle_code']."', ''), Symptomnummer = NULLIF('".$updateData['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateData['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateData['SeiteOriginalBis']."', ''), final_version_de = NULLIF('".$updateData['final_version_de']."', ''), final_version_en = NULLIF('".$updateData['final_version_en']."', ''), Beschreibung_de = NULLIF('".$updateData['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateData['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateData['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateData['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateData['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateData['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateData['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateData['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateData['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateData['searchable_text_en']."', ''), bracketedString_de = NULLIF('".$updateData['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$updateData['bracketedString_en']."', ''), timeString_de = NULLIF('".$updateData['timeString_de']."', ''), timeString_en = NULLIF('".$updateData['timeString_en']."', ''), Fussnote = NULLIF('".$updateData['Fussnote']."', ''), EntnommenAus = NULLIF('".$updateData['EntnommenAus']."', ''), Verweiss = NULLIF('".$updateData['Verweiss']."', ''), Graduierung = NULLIF('".$updateData['Graduierung']."', ''), BereichID = NULLIF('".$updateData['BereichID']."', ''), Kommentar = NULLIF('".$updateData['Kommentar']."', ''), Unklarheiten = NULLIF('".$updateData['Unklarheiten']."', ''), Remedy = NULLIF('".$updateData['Remedy']."', ''), symptom_of_different_remedy = NULLIF('".$updateData['symptom_of_different_remedy']."', ''), subChapter = NULLIF('".$updateData['subChapter']."', ''), subSubChapter = NULLIF('".$updateData['subSubChapter']."', ''), symptom_edit_comment = NULLIF('".$updateData['symptom_edit_comment']."', ''), is_final_version_available = NULLIF('".$updateData['is_final_version_available']."', ''), is_symptom_number_mismatch = NULLIF('".$updateData['is_symptom_number_mismatch']."', ''), is_symptom_appended = NULLIF('".$updateData['is_symptom_appended']."', '')".$conditionalUpdFields." WHERE id = ".$backupConnData['initial_source_symptom_id'];
					            				$db->query($updateSwappedSymptomsInMainQuery);

					            				$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$backupConnData['initial_source_symptom_id'];
						            			$db->query($symPrueferDeleteQuery);

						            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$backupConnData['initial_source_symptom_id'];
						            			$db->query($symRefDeleteQuery);

						            			/* Insert Symptom_pruefer relation START */
									            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM ".$updateData['symptom_pruefer_table']." where symptom_id = '".$updateData['id']."'");
												if($symptomPrueferResult->num_rows > 0){
													while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
														$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$backupConnData['initial_source_symptom_id']."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
										            	$db->query($mainSymptomPrueferInsertQuery);
													}
												}
												/* Insert Symptom_pruefer relation END */

												/* Insert symptom_reference relation START */
									            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM ".$updateData['symptom_reference_table']." where symptom_id = '".$updateData['id']."'");
												if($symptomReferenceResult->num_rows > 0){
													while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
														$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$backupConnData['initial_source_symptom_id']."', '".$symptomReferenceData['reference_id']."', '".$date."')";
										            	$db->query($mainSymptomReferenceInsertQuery);
													}
												}
												/* Insert symptom_reference relation END */
											}
											// Checking for swapped data and making updates in the main symptom END
										}
										if($backupConnData['comparing_source_type'] == "original") {
											// Checking Comparing symptom
											// Checking for swapped data and making updates in the main symptom START
											$updateData = array();
											/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$backupConnData['comparing_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
											if(mysqli_num_rows($swappedSymptomResult) > 0){
												$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
												$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT * FROM backup_sets_swapped_symptoms WHERE original_symptom_id = '".$symptomRow['symptom_id']."' AND saved_comparisons_backup_id = '".$backupSCData['sc_id']."'");
												if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0) {
													$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);


													$updateData['id'] = $backupSetSymptomRow['id'];
													$updateData['symptom_pruefer_table'] = 'backup_sets_swapped_symptom_pruefer';
													$updateData['symptom_reference_table'] = 'backup_sets_swapped_symptom_reference';

													$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
													$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
													$updateData['quelle_id'] = ($backupSetSymptomRow['quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_id']) : null;
													$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
													$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
													$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
													$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
													$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
													$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
													$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
													$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
													$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
													$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
													$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
													$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
													$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
													$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
													$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
													$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
													$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
													$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
													$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
													$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
													$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
													$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
													$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
													$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
												} else {
													// Get the first symptom set from the backups of this comparison
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$backupConnData['comparing_source_symptom_id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															$updateData['id'] = $backupSetSymptomRow['id'];
															$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
															$updateData['symptom_reference_table'] = 'symptom_reference_backup';

															$updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
															$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
															$updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
															$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
															$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
															$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
															$updateData['Beschreibung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung']);
															$updateData['BeschreibungOriginal'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal']);
															$updateData['BeschreibungPlain'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain']);
															$updateData['searchable_text'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text']);
															$updateData['bracketedString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString']);
															$updateData['timeString'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString']);
															$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
															$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
															$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
															$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
															$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
															$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
															$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
															$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
															$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
															$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
															$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
															$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
															$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
														}
													}
												}
											}*/


											// 
											// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
											$backupConnectedSymptomQuery = $db->query("SELECT * FROM backup_connected_symptoms_details WHERE saved_comparisons_backup_id = '".$backupSCData['sc_id']."' AND original_symptom_id = '".$backupConnData['comparing_source_symptom_id']."'");
							            	if($backupConnectedSymptomQuery->num_rows > 0){
							            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

							            		$updateData['symptom_pruefer_table'] = "backup_connected_symptoms_details_pruefer";
												$updateData['symptom_reference_table'] = "backup_connected_symptoms_details_reference";

												$updateData['id'] = ($rowData['id'] != "") ? $rowData['id'] : "";

							            		$updateData['master_id'] = ($rowData['master_id'] != "") ? mysqli_real_escape_string($db, $rowData['master_id']) : null;
							            		$updateData['quelle_id'] = ($rowData['quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_id']) : null;
							            		$updateData['arznei_id'] = ($rowData['arznei_id'] != "") ? mysqli_real_escape_string($db, $rowData['arznei_id']) : null;
												$updateData['original_quelle_id'] = ($rowData['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $rowData['original_quelle_id']) : null;
												$updateData['quelle_code'] = ($rowData['quelle_code'] != "") ? mysqli_real_escape_string($db, $rowData['quelle_code']) : null;
												$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $rowData['Symptomnummer']);
												$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalVon']);
												$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $rowData['SeiteOriginalBis']);
												$updateData['final_version_de'] = mysqli_real_escape_string($db, $rowData['final_version_de']);
												$updateData['final_version_en'] = mysqli_real_escape_string($db, $rowData['final_version_en']);
												$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $rowData['Beschreibung_de']);
												$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $rowData['Beschreibung_en']);
												$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_de']);
												$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungOriginal_en']);
												$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_de']);
												$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungFull_en']);
												$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_de']);
												$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $rowData['BeschreibungPlain_en']);
												$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $rowData['searchable_text_de']);
												$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $rowData['searchable_text_en']);
												$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $rowData['bracketedString_de']);
												$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $rowData['bracketedString_en']);
												$updateData['timeString_de'] = mysqli_real_escape_string($db, $rowData['timeString_de']);
												$updateData['timeString_en'] = mysqli_real_escape_string($db, $rowData['timeString_en']);
												$updateData['Fussnote'] = mysqli_real_escape_string($db, $rowData['Fussnote']);
												$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $rowData['EntnommenAus']);
												$updateData['Verweiss'] = mysqli_real_escape_string($db, $rowData['Verweiss']);
												$updateData['Graduierung'] = mysqli_real_escape_string($db, $rowData['Graduierung']);
												$updateData['BereichID'] = mysqli_real_escape_string($db, $rowData['BereichID']);
												$updateData['Kommentar'] = mysqli_real_escape_string($db, $rowData['Kommentar']);
												$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $rowData['Unklarheiten']);
												$updateData['Remedy'] = mysqli_real_escape_string($db, $rowData['Remedy']);
												$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $rowData['symptom_of_different_remedy']);
												$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $rowData['is_symptom_number_mismatch']);
												$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $rowData['is_symptom_appended']);
												$updateData['subChapter'] = mysqli_real_escape_string($db, $rowData['subChapter']);
												$updateData['subSubChapter'] = mysqli_real_escape_string($db, $rowData['subSubChapter']);
												$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $rowData['symptom_edit_comment']);
												$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $rowData['is_final_version_available']);
							            	}
							            	else
							            	{
							            		// 
				            					// If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				            					$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$backupConnData['comparing_source_symptom_id']."' AND comparison_initial_source_id = '".$backupSCData['initial_source_id']."' AND comparison_comparing_source_ids = '".$backupSCDataComparingSourcesIds."' AND arznei_id = '".$arznei_id."'");
												if(mysqli_num_rows($swappedSymptomResult) > 0){
													$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
													// Here joining is made on backup table's quelle_id not with the original_quelle_id
													$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$backupSCData['sc_id']."'");
													if(mysqli_num_rows($importMasterBackupResult) > 0){
														$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
														$masterBackupSetSymptomResult = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND original_symptom_id = '".$backupConnData['comparing_source_symptom_id']."'");
														if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
															$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

															$updateData['id'] = $backupSetSymptomRow['id'];
															$updateData['symptom_pruefer_table'] = 'symptom_pruefer_backup';
															$updateData['symptom_reference_table'] = 'symptom_reference_backup';

															// quelle_import_backup stores the "master_id" of backup section
															// $updateData['master_id'] = mysqli_real_escape_string($db, $backupSetSymptomRow['master_id']);
															
															$updateData['arznei_id'] = ($backupSetSymptomRow['arznei_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['arznei_id']) : null;
															
															// $updateData['quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															
															$updateData['original_quelle_id'] = ($backupSetSymptomRow['original_quelle_id'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['original_quelle_id']) : null;
															$updateData['quelle_code'] = ($backupSetSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, $backupSetSymptomRow['quelle_code']) : null;
															$updateData['Symptomnummer'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Symptomnummer']);
															$updateData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalVon']);
															$updateData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $backupSetSymptomRow['SeiteOriginalBis']);
															$updateData['final_version_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_de']);
															$updateData['final_version_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['final_version_en']);
															$updateData['Beschreibung_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_de']);
															$updateData['Beschreibung_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Beschreibung_en']);
															$updateData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_de']);
															$updateData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungOriginal_en']);
															$updateData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_de']);
															$updateData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungFull_en']);
															$updateData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_de']);
															$updateData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BeschreibungPlain_en']);
															$updateData['searchable_text_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_de']);
															$updateData['searchable_text_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['searchable_text_en']);
															$updateData['bracketedString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_de']);
															$updateData['bracketedString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['bracketedString_en']);
															$updateData['timeString_de'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_de']);
															$updateData['timeString_en'] = mysqli_real_escape_string($db, $backupSetSymptomRow['timeString_en']);
															$updateData['Fussnote'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Fussnote']);
															$updateData['EntnommenAus'] = mysqli_real_escape_string($db, $backupSetSymptomRow['EntnommenAus']);
															$updateData['Verweiss'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Verweiss']);
															$updateData['Graduierung'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Graduierung']);
															$updateData['BereichID'] = mysqli_real_escape_string($db, $backupSetSymptomRow['BereichID']);
															$updateData['Kommentar'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Kommentar']);
															$updateData['Unklarheiten'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Unklarheiten']);
															$updateData['Remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['Remedy']);
															$updateData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_of_different_remedy']);
															$updateData['subChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subChapter']);
															$updateData['subSubChapter'] = mysqli_real_escape_string($db, $backupSetSymptomRow['subSubChapter']);
															$updateData['symptom_edit_comment'] = mysqli_real_escape_string($db, $backupSetSymptomRow['symptom_edit_comment']);
															$updateData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_number_mismatch']);
															$updateData['is_symptom_appended'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_symptom_appended']);
															$updateData['is_final_version_available'] = mysqli_real_escape_string($db, $backupSetSymptomRow['is_final_version_available']);
														}
													}
												}
							            	}

											if(!empty($updateData)){
												// If these updating data gets collected from "quelle_import_backup" than master_id and quelle_id information are not there AND they don't need to be updated (So far!)
												$conditionalUpdFields = "";
												if(isset($updateData['master_id']) AND $updateData['master_id'] != "")
													$conditionalUpdFields .= ", master_id = NULLIF('".$updateData['master_id']."', '')";
												if(isset($updateData['quelle_id']) AND $updateData['quelle_id'] != "")
													$conditionalUpdFields .= ", quelle_id = NULLIF('".$updateData['quelle_id']."', '')";

												$updateSwappedSymptomsInMainQuery = "UPDATE quelle_import_test SET arznei_id = NULLIF('".$updateData['arznei_id']."', ''), original_quelle_id = NULLIF('".$updateData['original_quelle_id']."', ''), quelle_code = NULLIF('".$updateData['quelle_code']."', ''), Symptomnummer = NULLIF('".$updateData['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateData['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateData['SeiteOriginalBis']."', ''), final_version_de = NULLIF('".$updateData['final_version_de']."', ''), final_version_en = NULLIF('".$updateData['final_version_en']."', ''), Beschreibung_de = NULLIF('".$updateData['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateData['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateData['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateData['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateData['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateData['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateData['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateData['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateData['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateData['searchable_text_en']."', ''), bracketedString_de = NULLIF('".$updateData['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$updateData['bracketedString_en']."', ''), timeString_de = NULLIF('".$updateData['timeString_de']."', ''), timeString_en = NULLIF('".$updateData['timeString_en']."', ''), Fussnote = NULLIF('".$updateData['Fussnote']."', ''), EntnommenAus = NULLIF('".$updateData['EntnommenAus']."', ''), Verweiss = NULLIF('".$updateData['Verweiss']."', ''), Graduierung = NULLIF('".$updateData['Graduierung']."', ''), BereichID = NULLIF('".$updateData['BereichID']."', ''), Kommentar = NULLIF('".$updateData['Kommentar']."', ''), Unklarheiten = NULLIF('".$updateData['Unklarheiten']."', ''), Remedy = NULLIF('".$updateData['Remedy']."', ''), symptom_of_different_remedy = NULLIF('".$updateData['symptom_of_different_remedy']."', ''), subChapter = NULLIF('".$updateData['subChapter']."', ''), subSubChapter = NULLIF('".$updateData['subSubChapter']."', ''), symptom_edit_comment = NULLIF('".$updateData['symptom_edit_comment']."', ''), is_final_version_available = NULLIF('".$updateData['is_final_version_available']."', ''), is_symptom_number_mismatch = NULLIF('".$updateData['is_symptom_number_mismatch']."', ''), is_symptom_appended = NULLIF('".$updateData['is_symptom_appended']."', '')".$conditionalUpdFields." WHERE id = ".$backupConnData['comparing_source_symptom_id'];
					            				$db->query($updateSwappedSymptomsInMainQuery);

					            				$symPrueferDeleteQuery="DELETE FROM symptom_pruefer WHERE symptom_id = ".$backupConnData['comparing_source_symptom_id'];
						            			$db->query($symPrueferDeleteQuery);

						            			$symRefDeleteQuery="DELETE FROM symptom_reference WHERE symptom_id = ".$backupConnData['comparing_source_symptom_id'];
						            			$db->query($symRefDeleteQuery);

						            			/* Insert Symptom_pruefer relation START */
									            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM ".$updateData['symptom_pruefer_table']." where symptom_id = '".$updateData['id']."'");
												if($symptomPrueferResult->num_rows > 0){
													while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
														$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$backupConnData['comparing_source_symptom_id']."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
										            	$db->query($mainSymptomPrueferInsertQuery);
													}
												}
												/* Insert Symptom_pruefer relation END */

												/* Insert symptom_reference relation START */
									            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM ".$updateData['symptom_reference_table']." where symptom_id = '".$updateData['id']."'");
												if($symptomReferenceResult->num_rows > 0){
													while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
														$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$backupConnData['comparing_source_symptom_id']."', '".$symptomReferenceData['reference_id']."', '".$date."')";
										            	$db->query($mainSymptomReferenceInsertQuery);
													}
												}
												/* Insert symptom_reference relation END */
											}
											// Checking for swapped data and making updates in the main symptom END
										}
									}
								}
				            }
				        }
				    }

				    $backupSavedComUpdateQuelleQuery="UPDATE saved_comparisons_backup SET original_quelle_id = '".$newQuelleId."' WHERE original_quelle_id = ".$original_quelle_id;
	            	$db->query($backupSavedComUpdateQuelleQuery);

	            	$backupUpdateQuelleMasterQuery="UPDATE quelle_import_master_backup SET original_quelle_id = '".$newQuelleId."' WHERE original_quelle_id = ".$original_quelle_id;
	            	$db->query($backupUpdateQuelleMasterQuery);
	            	if($backupQuelleMasterData['id'] != ""){
	            		$backupUpdateQuelleMasterQuery="UPDATE quelle_import_master_backup SET ersteller_datum = '".$date."' WHERE id = ".$backupQuelleMasterData['id'];
	            		$db->query($backupUpdateQuelleMasterQuery);
	            	}
	            	
	            	$resultData['original_quelle_id'] = $newQuelleId;
				}

				$db->commit();
				$status = 'success';
		    	$message = 'Reactivated successfully';
			} else {
				$status = 'error';
		    	$message = 'Could not perform the operation, data not found';
			}
		}
		else
		{
			$status = 'error';
	    	$message = 'Operation failed. Required data not found please try again!';
		}

	} catch (Exception $e) {
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