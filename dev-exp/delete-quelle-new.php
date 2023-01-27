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
			$deletingQuelleIds = getAllRelatedQuelleNew($quelle_ids, $quelle_ids);
			$reSaveableQuelleIds = array();
			$resultData['deletingQuelleIds'] = $deletingQuelleIds;

			if(!empty($deletingQuelleIds)){
				foreach ($deletingQuelleIds as $key => $quelle) {
					$totalSymptomQuery = mysqli_query($db,"SELECT id, quelle_id, comparison_name, table_name, arznei_id, comparing_sources, initial_source FROM pre_comparison_master_data WHERE quelle_id = '".$quelle."'");
					if(mysqli_num_rows($totalSymptomQuery) > 0){
						$dataRow = mysqli_fetch_assoc($totalSymptomQuery);

						$sourceIdsArray = (isset($dataRow['comparing_sources']) AND $dataRow['comparing_sources'] != "") ? explode(",", $dataRow['comparing_sources']) : array();
						if($dataRow['initial_source'] != "")
							array_push($sourceIdsArray, $dataRow['initial_source']);

						if(!empty($sourceIdsArray)){
							$sourceIds = implode(",", $sourceIdsArray); 
							$updQuelleQuery = "UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id IN (".$sourceIds.")";
					    	$db->query($updQuelleQuery);
						}

		            	// Deleting history sets SATRT
		            	$historyResult = $db->query("SELECT table_name FROM pre_comparison_master_data_for_history WHERE quelle_id = '".$dataRow['quelle_id']."'");
						if($historyResult->num_rows > 0){
							while($historyData = mysqli_fetch_array($historyResult)){
								$historyTable = $historyData['table_name'];
								$historyTableHighestMatch = $historyData['table_name']."_highest_matches";
								$historyTableConnections = $historyData['table_name']."_connections";
								$historyTableCompleted = $historyData['table_name']."_completed";

								$checkHistoryTable = mysqli_query($db, "SHOW TABLES LIKE '".$historyTable."'");
								if(mysqli_num_rows($checkHistoryTable) != 0){
									$dropHistoryTable = "DROP TABLE ".$historyTable;
		    						$db->query($dropHistoryTable);
								}

								$checkHistoryTableHighestMatch = mysqli_query($db, "SHOW TABLES LIKE '".$historyTableHighestMatch."'");
								if(mysqli_num_rows($checkHistoryTableHighestMatch) != 0){
									$dropHistoryTableHighestMatch = "DROP TABLE ".$historyTableHighestMatch;
		    						$db->query($dropHistoryTableHighestMatch);
								}

								$checkHistoryTableConnections = mysqli_query($db, "SHOW TABLES LIKE '".$historyTableConnections."'");
								if(mysqli_num_rows($checkHistoryTableConnections) != 0){
									$dropHistoryTableConnections = "DROP TABLE ".$historyTableConnections;
		    						$db->query($dropHistoryTableConnections);
								}

								$checkHistoryTableCompleted = mysqli_query($db, "SHOW TABLES LIKE '".$historyTableCompleted."'");
								if(mysqli_num_rows($checkHistoryTableCompleted) != 0){
									$dropHistoryTableCompleted = "DROP TABLE ".$historyTableCompleted;
		    						$db->query($dropHistoryTableCompleted);
								}
							}
							$deleteHistoryRecord = "DELETE FROM pre_comparison_master_data_for_history WHERE quelle_id = '".$dataRow['quelle_id']."'";
		            		$db->query($deleteHistoryRecord);
						}
		            	// Deleting history sets END

		            	$checkIfDynamicComparisonTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."'");
						if(mysqli_num_rows($checkIfDynamicComparisonTableExist) != 0){
							$dropComparisonTable = "DROP TABLE ".$dataRow['table_name'];
		            		$db->query($dropComparisonTable);
						}
						$checkIfInitialTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."_initials'");
						if(mysqli_num_rows($checkIfInitialTableExist) != 0){
							$dropComparisonTableInitials = "DROP TABLE ".$dataRow['table_name']."_initials";
		            		$db->query($dropComparisonTableInitials);
						}
						$checkIfSavedDataTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."_saved_data'");
						if(mysqli_num_rows($checkIfSavedDataTableExist) != 0){
							$dropComparisonTableSavedData = "DROP TABLE ".$dataRow['table_name']."_saved_data";
		            		$db->query($dropComparisonTableSavedData);
						}

		            	$checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."_completed'");
						if(mysqli_num_rows($checkIfComparisonCompleteTableExist) != 0){
							$dropComparisonTableComplete = "DROP TABLE ".$dataRow['table_name']."_completed";
		            		$db->query($dropComparisonTableComplete);
						}
						$checkIfHighestMatchTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."_highest_matches'");
						if(mysqli_num_rows($checkIfHighestMatchTableExist) != 0){
							$drophighestMatchTable = "DROP TABLE ".$dataRow['table_name']."_highest_matches";
		            		$db->query($drophighestMatchTable);
						}

						$checkIfConnectionsTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dataRow['table_name']."_connections'");
						if(mysqli_num_rows($checkIfConnectionsTableExist) != 0){
							$dropComparisonTableConnections = "DROP TABLE ".$dataRow['table_name']."_connections";
		            		$db->query($dropComparisonTableConnections);
						}
						

						if(isset($_SESSION['comparison_table_data']['comparison_table']) AND $_SESSION['comparison_table_data']['comparison_table'] == $dataRow['table_name']){
							$_SESSION['comparison_table_data'] = array();
						}

		            	$deleteMasterData = "DELETE FROM pre_comparison_master_data WHERE id = '".$dataRow['id']."'";
		            	$db->query($deleteMasterData);
					}

					$masterResult = $db->query("SELECT id, quelle_id FROM quelle_import_master WHERE quelle_id = '".$quelle."' AND arznei_id = '".$arznei_id."'");
					if($masterResult->num_rows > 0){
						while($masterData = mysqli_fetch_array($masterResult)){
							$symptomResult = $db->query("SELECT id FROM quelle_import_test WHERE master_id = ".$masterData['id']);
							if($symptomResult->num_rows > 0){
								while($symptomData = mysqli_fetch_array($symptomResult)){
									$symRemedyDeleteQuery="DELETE FROM symptom_remedy WHERE symptom_id = ".$symptomData['id'];
			            			$db->query($symRemedyDeleteQuery);

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
		            	}
					}
					$masterDeleteQuery="DELETE FROM quelle_import_master WHERE quelle_id = '".$quelle."' AND arznei_id = '".$arznei_id."'";
		            $db->query($masterDeleteQuery);
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