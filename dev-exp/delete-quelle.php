<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Deleting a particular source and it's all related data
	*/
?>
<?php  
	$resultData = array();
	$status = 'error';
	$message = 'Could not perform the action, please try again!';
	try 
	{
		$quelle_id = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['quelle_id'])) : null;
		$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['arznei_id'])) : null;
		if($quelle_id != "" AND $arznei_id != "")
		{
			$db->begin_transaction();
			$quelle_ids = explode(',', $quelle_id);
			$deletingQuelleIds = getAllRelatedQuelle($quelle_ids, $quelle_ids);
			$reSaveableQuelleIds = array();

			if(!empty($deletingQuelleIds)){
				foreach ($deletingQuelleIds as $key => $quelle) {
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

			            			// Deleteing symptom specific settings
			            			$symTypeSettingDeleteQuery="DELETE FROM symptom_type_setting WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($symTypeSettingDeleteQuery);
			            			$symGradingSettingDeleteQuery="DELETE FROM symptom_grading_settings WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($symGradingSettingDeleteQuery);

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

				            $fetchConnectedSymptomsQuery=$db->query("SELECT initial_source_symptom_id, comparing_source_symptom_id FROM symptom_connections WHERE (initial_source_id = '".$workingQuelleId."' OR comparing_source_id = '".$workingQuelleId."') AND source_arznei_id = '".$arznei_id."'");
				            if($fetchConnectedSymptomsQuery->num_rows > 0){
				            	while($connectedSymptomsData = mysqli_fetch_array($fetchConnectedSymptomsQuery)){
				            		$sympUpdQuery1="UPDATE quelle_import_test SET is_final_version_available = 0 WHERE id = '".$connectedSymptomsData['initial_source_symptom_id']."'";
									$db->query($sympUpdQuery1);
									$sympUpdQuery2="UPDATE quelle_import_test SET is_final_version_available = 0 WHERE id = '".$connectedSymptomsData['comparing_source_symptom_id']."'";
									$db->query($sympUpdQuery2);
				            	}
				            }

				            $quelleConnDeleteQuery="DELETE FROM symptom_connections WHERE (initial_source_id = '".$workingQuelleId."' OR comparing_source_id = '".$workingQuelleId."') AND source_arznei_id = '".$arznei_id."'";
				            $db->query($quelleConnDeleteQuery);

				            $quelleDeleteQuery="DELETE FROM quelle WHERE quelle_id = '".$workingQuelleId."' AND quelle_type_id = 3";
				            $db->query($quelleDeleteQuery);

				            $fetchSavedComQuery=$db->query("SELECT initial_source_id, comparing_source_ids FROM saved_comparisons WHERE quelle_id = ".$workingQuelleId);
				            if($fetchSavedComQuery->num_rows > 0){
				            	$fetchSavedComData = mysqli_fetch_assoc($fetchSavedComQuery);
								$savedComparisonInitialSourceId = (isset($fetchSavedComData['initial_source_id']) AND $fetchSavedComData['initial_source_id'] != "") ? $fetchSavedComData['initial_source_id'] : null;
								$savedComparisonComparingSourceIds = (isset($fetchSavedComData['comparing_source_ids']) AND $fetchSavedComData['comparing_source_ids'] != "") ? explode(',', $fetchSavedComData['comparing_source_ids']) : null;
								if($savedComparisonInitialSourceId != "") {
									$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$savedComparisonInitialSourceId;
			            			$db->query($updQuelleQuery);

			            			// Deleteing backup sets swapped symptoms data
			            			$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE quelle_id = ".$savedComparisonInitialSourceId." AND arznei_id = '".$arznei_id."'");
									if($symptomResult->num_rows > 0){
										while($iniSymptomData = mysqli_fetch_array($symptomResult)){
											// Delete if it is there in appended_symptoms
					            			$appendedSymptomDeleteQuery="DELETE FROM appended_symptoms WHERE symptom_id = ".$symptomData['id'];
						            		$db->query($appendedSymptomDeleteQuery); 

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

									if($savedComparisonInitialSourceId != $quelle_id) {
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
							            		// $reSaveableQuelleIds[] = $savedComparisonInitialSourceId;
							            	}
							            }
									}
										

			            			// Collect freshly re-saveable quelle id (saved comparisons) 
			            			// $fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$savedComparisonInitialSourceId);
						            // if($fetchSavedComInfoQuery->num_rows > 0){
						            // 	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
						            // 	if($fetchSavedComInfoData['quelle_type_id'] == 3)
						            // 		$reSaveableQuelleIds[] = $savedComparisonInitialSourceId;
						            // }
								}

								// It Will always connect to direct initial source so there is no need to fetch the compared sources chain of initial source Here i am collecting comparing source's compared source chain
								$comparingSourceComparedChainIds = array();

								if(!empty($savedComparisonComparingSourceIds)){
									foreach ($savedComparisonComparingSourceIds as $key => $val) {
										$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE quelle_id = ".$val." AND arznei_id = '".$arznei_id."'");
										if($symptomResult->num_rows > 0){
											while($comSymptomData = mysqli_fetch_array($symptomResult)){
												// Delete if it is there in appended_symptoms
						            			$appendedSymptomDeleteQuery="DELETE FROM appended_symptoms WHERE symptom_id = ".$comSymptomData['id'];
							            		$db->query($appendedSymptomDeleteQuery); 

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

										if($val != $quelle_id) {
											// If the source is a imported source and not a saved comparison source than re add the default symptoms which are there in the backup table to avoid swaped symptoms 
				        					$fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$val);
								            if($fetchSavedComInfoQuery->num_rows > 0){
								            	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
								            	if($fetchSavedComInfoData['quelle_type_id'] != 3){
								            		// deleteing current symptoms and adding the default imported symptom of the source
								            		restoreTheDefautImportedSymptoms($val, $arznei_id); 
								            	} else {
								            		// If it is a saved comparison than re-saveing the save comparison 
								            		resaveTheSavedComparison($val, $arznei_id);
								            	}
								            }
										}
									}


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

										$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$allSourcesVal;
			        					$db->query($updQuelleQuery);

			        					// Collect freshly re-saveable quelle id (saved comparisons)
			        					// $fetchSavedComInfoQuery = $db->query("SELECT quelle_type_id FROM quelle WHERE quelle_id = ".$allSourcesVal);
							         //    if($fetchSavedComInfoQuery->num_rows > 0){
							         //    	$fetchSavedComInfoData = mysqli_fetch_assoc($fetchSavedComInfoQuery);
							         //    	if($fetchSavedComInfoData['quelle_type_id'] == 3)
							         //    		$reSaveableQuelleIds[] = $allSourcesVal;
							         //    }
									}
								}
								$comparedSourceChainOfComparingSource = (!empty($comparingSourceComparedChainIds)) ? implode(',', $comparingSourceComparedChainIds) : "";

								// Delete parents connections
								$fetchConnectedSymptomsQuery2=$db->query("SELECT initial_source_symptom_id, comparing_source_symptom_id FROM symptom_connections WHERE ((initial_source_id = '".$savedComparisonInitialSourceId."' AND FIND_IN_SET(comparing_source_id, '".$comparedSourceChainOfComparingSource."')) OR (FIND_IN_SET(initial_source_id, '".$comparedSourceChainOfComparingSource."') AND comparing_source_id = '".$savedComparisonInitialSourceId."')) AND source_arznei_id = '".$arznei_id."'");
					            if($fetchConnectedSymptomsQuery2->num_rows > 0){
					            	while($connectedSymptomsData2 = mysqli_fetch_array($fetchConnectedSymptomsQuery2)){
					            		$sympUpdQuery1="UPDATE quelle_import_test SET is_final_version_available = 0 WHERE id = '".$connectedSymptomsData2['initial_source_symptom_id']."'";
										$db->query($sympUpdQuery1);
										$sympUpdQuery2="UPDATE quelle_import_test SET is_final_version_available = 0 WHERE id = '".$connectedSymptomsData2['comparing_source_symptom_id']."'";
										$db->query($sympUpdQuery2);
					            	}
					            }

								$quelleParentsConnDeleteQuery="DELETE FROM symptom_connections WHERE ((initial_source_id = '".$savedComparisonInitialSourceId."' AND FIND_IN_SET(comparing_source_id, '".$comparedSourceChainOfComparingSource."')) OR (FIND_IN_SET(initial_source_id, '".$comparedSourceChainOfComparingSource."') AND comparing_source_id = '".$savedComparisonInitialSourceId."')) AND source_arznei_id = '".$arznei_id."'";
				           		$db->query($quelleParentsConnDeleteQuery);
				            }

				            $savedComDeleteQuery="DELETE FROM saved_comparisons WHERE quelle_id = ".$workingQuelleId;
				            $db->query($savedComDeleteQuery);	
		            	}
					}
					$masterDeleteQuery="DELETE FROM quelle_import_master WHERE quelle_id = '".$quelle."' AND arznei_id = '".$arznei_id."'";
		            $db->query($masterDeleteQuery);


		            // DELETEING THE RELATED DATA IN THE BACKUP TABLES START
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
		            // DELETEING THE RELATED DATA IN THE BACKUP TABLES END
				}

				
			}

			$db->commit();
			$status = 'success';
	    	$message = 'Deleted successfully';
		}
		else
		{
			$status = 'error';
	    	$message = 'Operation failed. Required data not found please try again!';
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