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
	$message = 'Could not perform the action.';
	try {
		$id = (isset($_POST['id']) AND $_POST['id'] != "") ? $_POST['id'] : null;
		if($id != "")
		{
			$totalSymptomQuery = mysqli_query($db,"SELECT id, quelle_id, comparison_name, table_name, arznei_id, comparing_sources, initial_source FROM pre_comparison_master_data WHERE id = '".$id."'");
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

				$deleteQuelle = "DELETE FROM quelle WHERE quelle_id = '".$dataRow['quelle_id']."' AND quelle_type_id = 3";
            	$db->query($deleteQuelle);

            	$deleteQuelleIM = "DELETE FROM quelle_import_master WHERE quelle_id = '".$dataRow['quelle_id']."' AND arznei_id = '".$dataRow['arznei_id']."'";
            	$db->query($deleteQuelleIM);

            	$deleteArzneiQuelle = "DELETE FROM arznei_quelle WHERE quelle_id = '".$dataRow['quelle_id']."' AND arznei_id = '".$dataRow['arznei_id']."'";
            	$db->query($deleteArzneiQuelle);

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

            	$deleteMasterData = "DELETE FROM pre_comparison_master_data WHERE id = '".$id."'";
            	$db->query($deleteMasterData);

				//Removing json file
				$tablenName = $dataRow['table_name'];
				$filename= $tablenName."_chapter_data.json";
				$checkIfJsonExist = file_exists( "chapter-data/".$filename);
				if($checkIfJsonExist == 1){
					unlink( "chapter-data/".$filename);
				}

            	$status = 'success';
		    	$message = 'Deleted successfully';
			}
			else
			{
				$status = 'error';
				$message = 'Record does not exist.';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong, please try again!';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>