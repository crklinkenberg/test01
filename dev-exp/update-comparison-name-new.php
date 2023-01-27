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
			$totalSymptomQuery = mysqli_query($db,"SELECT Q.quelle_id FROM quelle AS Q LEFT JOIN pre_comparison_master_data_for_history AS PCMH ON Q.quelle_id = PCMH.quelle_id WHERE (Q.code = '".$comparison_name."' OR Q.titel = '".$comparison_name."' OR PCMH.comparison_name = '".$comparison_name."') AND Q.quelle_id != '".$quelle_id."'");
			if(mysqli_num_rows($totalSymptomQuery) > 0){
				$status = 'error';
				$message = 'This name is already used.';
			}
			else
			{
				$db->begin_transaction();
				$comparison_name = mysqli_real_escape_string($db, $comparison_name);

				$dynamicTableName = "";
				$historyMasterId = "";
				$getComMasterTableInfo = mysqli_query($db, "SELECT * FROM pre_comparison_master_data WHERE quelle_id = '".$quelle_id."'");
				if(mysqli_num_rows($getComMasterTableInfo) > 0){
					$comMasterTableData = mysqli_fetch_assoc($getComMasterTableInfo);
					$dynamicTableName = (isset($comMasterTableData['table_name']) AND $comMasterTableData['table_name'] != "") ? $comMasterTableData['table_name'] : "";

					$insertComparisonHistoryQuery="INSERT INTO pre_comparison_master_data_for_history (pre_comparison_master_id, quelle_id, table_name, comparison_name, similarity_rate, comparison_language, arznei_id, comparison_option, initial_source, comparing_sources, status, comparison_save_status, ersteller_datum) VALUES (NULLIF('".$comMasterTableData['id']."', ''), NULLIF('".$comMasterTableData['quelle_id']."', ''), NULLIF('".$comMasterTableData['table_name']."', ''), NULLIF('".$comMasterTableData['comparison_name']."', ''), NULLIF('".$comMasterTableData['similarity_rate']."', ''), NULLIF('".$comMasterTableData['comparison_language']."', ''), NULLIF('".$comMasterTableData['arznei_id']."', ''), NULLIF('".$comMasterTableData['comparison_option']."', ''), NULLIF('".$comMasterTableData['initial_source']."', ''), NULLIF('".$comMasterTableData['comparing_sources']."', ''), NULLIF('".$comMasterTableData['status']."', ''), NULLIF('".$comMasterTableData['comparison_save_status']."', ''), '".$date."')";
					$db->query($insertComparisonHistoryQuery);
					$historyMasterId = $db->insert_id;
				}

				if($dynamicTableName != "" AND $historyMasterId != ""){
					$preFixForHistoryTable = "history_".$historyMasterId."_";
					$newHistoryDynamicComparisonBaseTableName = $preFixForHistoryTable.$dynamicTableName;

					$updComHistoryQuery = "UPDATE pre_comparison_master_data_for_history SET table_name = '".$newHistoryDynamicComparisonBaseTableName."' WHERE id = ".$historyMasterId;
            		$db->query($updComHistoryQuery);

					$checkIfDynamicComparisonBaseTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dynamicTableName."'");
					if(mysqli_num_rows($checkIfDynamicComparisonBaseTableExist) != 0){
						//Copy the table structure
  						$db->query("CREATE TABLE $newHistoryDynamicComparisonBaseTableName LIKE $dynamicTableName");
  						//Copy the data to the new table
  						$db->query("INSERT $newHistoryDynamicComparisonBaseTableName SELECT * FROM $dynamicTableName");
					}

					$newHistoryDynamicComparisonHighestMatchTableName = $preFixForHistoryTable.$dynamicTableName."_highest_matches";
					$checkIfDynamicComparisonHighestMatchTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$dynamicTableName."_highest_matches'");
					if(mysqli_num_rows($checkIfDynamicComparisonHighestMatchTableExist) != 0){
						//Copy the table structure
  						$db->query("CREATE TABLE $newHistoryDynamicComparisonHighestMatchTableName LIKE ".$dynamicTableName."_highest_matches");
  						//Copy the data to the new table
  						$db->query("INSERT $newHistoryDynamicComparisonHighestMatchTableName SELECT * FROM ".$dynamicTableName."_highest_matches");
					}

					$newHistoryDynamicComparisonConnectionsTableName = $preFixForHistoryTable.$dynamicTableName."_connections";
					$checkIfDynamicComparisonConnectionsExist = mysqli_query($db, "SHOW TABLES LIKE '".$dynamicTableName."_connections'");
					if(mysqli_num_rows($checkIfDynamicComparisonConnectionsExist) != 0){
						//Copy the table structure
  						$db->query("CREATE TABLE $newHistoryDynamicComparisonConnectionsTableName LIKE ".$dynamicTableName."_connections");
  						//Copy the data to the new table
  						$db->query("INSERT $newHistoryDynamicComparisonConnectionsTableName SELECT * FROM ".$dynamicTableName."_connections");
					}

					$newHistoryDynamicComparisonCompletedTableName = $preFixForHistoryTable.$dynamicTableName."_completed";
					$checkIfDynamicComparisonCompletedExist = mysqli_query($db, "SHOW TABLES LIKE '".$dynamicTableName."_completed'");
					if(mysqli_num_rows($checkIfDynamicComparisonCompletedExist) != 0){
						//Copy the table structure
  						$db->query("CREATE TABLE $newHistoryDynamicComparisonCompletedTableName LIKE ".$dynamicTableName."_completed");
  						//Copy the data to the new table
  						$db->query("INSERT $newHistoryDynamicComparisonCompletedTableName SELECT * FROM ".$dynamicTableName."_completed");
					}

					$updPreComMasterQuery = "UPDATE pre_comparison_master_data SET comparison_name = '".$comparison_name."', is_comparison_renamed = 1 WHERE quelle_id = ".$quelle_id;
            		$db->query($updPreComMasterQuery);

            		$updQuelleQuery = "UPDATE quelle SET code = '".$comparison_name."', titel = '".$comparison_name."' WHERE quelle_id = ".$quelle_id;
            		$db->query($updQuelleQuery);
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