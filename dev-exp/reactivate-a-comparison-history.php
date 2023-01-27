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
		$comparison_history_id = (isset($_POST['comparison_history_id']) AND $_POST['comparison_history_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['comparison_history_id'])) : null;
		$quelle_id = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['quelle_id'])) : null;
		$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? mysqli_real_escape_string($db, trim($_POST['arznei_id'])) : null;
		if($comparison_history_id != "" AND $quelle_id != "" AND $arznei_id != "")
		{
			$db->begin_transaction();
			$quelle_id_in_array = explode(',', $quelle_id);
			$deletingQuelleIds = getAllRelatedQuelleNew($quelle_id_in_array, $quelle_id_in_array);

			if(!empty($deletingQuelleIds)){
				foreach ($deletingQuelleIds as $key => $quelle) {
					if($quelle != $quelle_id){
						$historyResult = $db->query("SELECT table_name FROM pre_comparison_master_data_for_history WHERE quelle_id = '".$quelle."'");
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
							$deleteHistoryRecord = "DELETE FROM pre_comparison_master_data_for_history WHERE quelle_id = '".$quelle."'";
            				$db->query($deleteHistoryRecord);	
						}

						$deleteQuelleRecord = "DELETE FROM quelle WHERE quelle_id = '".$quelle."' AND quelle_type_id = 3";
            			$db->query($deleteQuelleRecord);
					}

					$comparisonTableResult = $db->query("SELECT quelle_id, table_name, initial_source, comparing_sources FROM pre_comparison_master_data WHERE quelle_id = '".$quelle."'");
					if($comparisonTableResult->num_rows > 0){
						while($comparisonTableData = mysqli_fetch_array($comparisonTableResult)){
							$comparisonTable = $comparisonTableData['table_name'];
							$comparisonTableHighestMatch = $comparisonTableData['table_name']."_highest_matches";
							$comparisonTableConnections = $comparisonTableData['table_name']."_connections";
							$comparisonTableCompleted = $comparisonTableData['table_name']."_completed";

							$checkComparisonTable = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTable."'");
							if(mysqli_num_rows($checkComparisonTable) != 0){
								$dropComparisonTable = "DROP TABLE ".$comparisonTable;
        						$db->query($dropComparisonTable);
							}

							$checkComparisonTableHighestMatch = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTableHighestMatch."'");
							if(mysqli_num_rows($checkComparisonTableHighestMatch) != 0){
								$dropComparisonTableHighestMatch = "DROP TABLE ".$comparisonTableHighestMatch;
        						$db->query($dropComparisonTableHighestMatch);
							}

							$checkComparisonTableConnections = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTableConnections."'");
							if(mysqli_num_rows($checkComparisonTableConnections) != 0){
								$dropComparisonTableConnections = "DROP TABLE ".$comparisonTableConnections;
        						$db->query($dropComparisonTableConnections);
							}

							$checkComparisonTableCompleted = mysqli_query($db, "SHOW TABLES LIKE '".$comparisonTableCompleted."'");
							if(mysqli_num_rows($checkComparisonTableCompleted) != 0){
								$dropComparisonTableCompleted = "DROP TABLE ".$comparisonTableCompleted;
        						$db->query($dropComparisonTableCompleted);
							}

							// Making the free source available in materia medica again
							if($comparisonTableData['quelle_id'] != $quelle_id){
								$savedComparisonInitialSourceId = (isset($comparisonTableData['initial_source']) AND $comparisonTableData['initial_source'] != "") ? $comparisonTableData['initial_source'] : null;
								$savedComparisonComparingSourceIds = (isset($comparisonTableData['comparing_sources']) AND $comparisonTableData['comparing_sources'] != "") ? explode(',', $comparisonTableData['comparing_sources']) : array();
								if($savedComparisonInitialSourceId != "") {
									$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$savedComparisonInitialSourceId;
			            			$db->query($updQuelleQuery);
				            	}
				            	if(!empty($savedComparisonComparingSourceIds)){
									foreach ($savedComparisonComparingSourceIds as $key => $val) {
										$updQuelleQuery="UPDATE quelle SET is_materia_medica = 1 WHERE quelle_id = ".$val;
				        				$db->query($updQuelleQuery);
									}
								}
							}
						}
					}
					$deleteComparisonRecord = "DELETE FROM pre_comparison_master_data WHERE quelle_id = '".$quelle."'";
            		$db->query($deleteComparisonRecord);
				}

				$historySetResult = $db->query("SELECT * FROM pre_comparison_master_data_for_history WHERE id = '".$comparison_history_id."'");
				if($historySetResult->num_rows > 0){
					$historySetData = mysqli_fetch_assoc($historySetResult);
					$repaceblePart = "history_".$historySetData['id']."_";
					$comparisonTableName = str_replace($repaceblePart, "", $historySetData['table_name']);
					$insertComparisonQuery="INSERT INTO pre_comparison_master_data (quelle_id, table_name, comparison_name, similarity_rate, comparison_language, arznei_id, comparison_option, initial_source, comparing_sources, status, comparison_save_status, is_comparison_renamed, ersteller_datum) VALUES (NULLIF('".$historySetData['quelle_id']."', ''), NULLIF('".$comparisonTableName."', ''), NULLIF('".$historySetData['comparison_name']."', ''), NULLIF('".$historySetData['similarity_rate']."', ''), NULLIF('".$historySetData['comparison_language']."', ''), NULLIF('".$historySetData['arznei_id']."', ''), NULLIF('".$historySetData['comparison_option']."', ''), NULLIF('".$historySetData['initial_source']."', ''), NULLIF('".$historySetData['comparing_sources']."', ''), NULLIF('".$historySetData['status']."', ''), NULLIF('".$historySetData['comparison_save_status']."', ''), 1, '".$date."')";
					$db->query($insertComparisonQuery);
					$comparisonInsertedId = $db->insert_id;
					if($comparisonInsertedId != ""){
						$historySetComparisonTableName = $historySetData['table_name'];
						$historySetComparisonTableNameHighestMatch = $historySetData['table_name']."_highest_matches";
						$historySetComparisonTableNameConnections = $historySetData['table_name']."_connections";
						$historySetComparisonTableNameCompleted = $historySetData['table_name']."_completed";

						$newComparisonTableName = $comparisonTableName;
						$newComparisonTableNameHighestMatch = $comparisonTableName."_highest_matches";
						$newComparisonTableNameConnections = $comparisonTableName."_connections";
						$newComparisonTableNameCompleted = $comparisonTableName."_completed";

						$checkIfDynamicComparisonBaseTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$historySetComparisonTableName."'");
						if(mysqli_num_rows($checkIfDynamicComparisonBaseTableExist) != 0){
							//Copy the table structure
	  						$db->query("CREATE TABLE $newComparisonTableName LIKE $historySetComparisonTableName");
	  						//Copy the data to the new table
	  						$db->query("INSERT $newComparisonTableName SELECT * FROM $historySetComparisonTableName");
						}

						$checkIfDynamicComparisonHighestMatchTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$historySetComparisonTableNameHighestMatch."'");
						if(mysqli_num_rows($checkIfDynamicComparisonHighestMatchTableExist) != 0){
							//Copy the table structure
	  						$db->query("CREATE TABLE $newComparisonTableNameHighestMatch LIKE $historySetComparisonTableNameHighestMatch");
	  						//Copy the data to the new table
	  						$db->query("INSERT $newComparisonTableNameHighestMatch SELECT * FROM $historySetComparisonTableNameHighestMatch");
						}

						$checkIfDynamicComparisonConnectionsTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$historySetComparisonTableNameConnections."'");
						if(mysqli_num_rows($checkIfDynamicComparisonConnectionsTableExist) != 0){
							//Copy the table structure
	  						$db->query("CREATE TABLE $newComparisonTableNameConnections LIKE $historySetComparisonTableNameConnections");
	  						//Copy the data to the new table
	  						$db->query("INSERT $newComparisonTableNameConnections SELECT * FROM $historySetComparisonTableNameConnections");
						}

						$checkIfDynamicComparisonCompletedTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$historySetComparisonTableNameCompleted."'");
						if(mysqli_num_rows($checkIfDynamicComparisonCompletedTableExist) != 0){
							//Copy the table structure
	  						$db->query("CREATE TABLE $newComparisonTableNameCompleted LIKE $historySetComparisonTableNameCompleted");
	  						//Copy the data to the new table
	  						$db->query("INSERT $newComparisonTableNameCompleted SELECT * FROM $historySetComparisonTableNameCompleted");
						}
					}

					$updQuelleQuery = "UPDATE quelle SET code = NULLIF('".$historySetData['comparison_name']."', ''), titel = NULLIF('".$historySetData['comparison_name']."', ''), is_materia_medica = 1 WHERE quelle_id = '".$quelle_id."'";
			    	$db->query($updQuelleQuery);

					$updPreComMasterIdQuery = "UPDATE pre_comparison_master_data_for_history SET pre_comparison_master_id = '".$comparisonInsertedId."' WHERE quelle_id = '".$quelle_id."'";
			    	$db->query($updPreComMasterIdQuery);
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