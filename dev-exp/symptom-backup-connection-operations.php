<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Performing all connections related operations (This is used in backup section) 
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';

	try {
		if(isset($_POST['action']) AND $_POST['action'] != ""){
			
			switch ($_POST['action']) {
				case 'connect':
					{
						$unique_id = (isset($_POST['unique_id']) AND $_POST['unique_id'] !="") ? $_POST['unique_id'] : null;
						$source_arznei_id = (isset($_POST['source_arznei_id']) AND $_POST['source_arznei_id'] !="") ? mysqli_real_escape_string($db, $_POST['source_arznei_id']) : null;
						$initial_source_id = (isset($_POST['initial_source_id']) AND $_POST['initial_source_id'] !="") ? mysqli_real_escape_string($db, $_POST['initial_source_id']) : null;
						// $initial_source_code = (isset($_POST['initial_source_code']) AND $_POST['initial_source_code'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_code']) : null;
						$comparing_source_id = (isset($_POST['comparing_source_id']) AND $_POST['comparing_source_id'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_id']) : null;
						// $comparing_source_code = (isset($_POST['comparing_source_code']) AND $_POST['comparing_source_code'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_code']) : null;
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : null;
						$initial_source_symptom = (isset($_POST['initial_source_symptom']) AND $_POST['initial_source_symptom'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom']) : null;
						$comparing_source_symptom = (isset($_POST['comparing_source_symptom']) AND $_POST['comparing_source_symptom'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom']) : null;
						$initial_source_symptom_highlighted = (isset($_POST['initial_source_symptom_highlighted']) AND $_POST['initial_source_symptom_highlighted'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_highlighted']) : null;
						$comparing_source_symptom_highlighted = (isset($_POST['comparing_source_symptom_highlighted']) AND $_POST['comparing_source_symptom_highlighted'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_highlighted']) : null;
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : null;
						$matching_percentage = (isset($_POST['matching_percentage']) AND $_POST['matching_percentage'] != "") ? mysqli_real_escape_string($db, $_POST['matching_percentage']) : 0;
						$is_initial_source = (isset($_POST['is_initial_source']) AND $_POST['is_initial_source'] != "") ? $_POST['is_initial_source'] : 1;
						$is_connected = (isset($_POST['is_connected']) AND $_POST['is_connected'] != "") ? $_POST['is_connected'] : null;
						$comparing_source_ids = (isset($_POST['comparing_source_ids']) AND $_POST['comparing_source_ids'] != "") ? $_POST['comparing_source_ids'] : null;
						$active_symptom_type = (isset($_POST['active_symptom_type']) AND $_POST['active_symptom_type'] != "") ? $_POST['active_symptom_type'] : null;
						$main_parent_initial_symptom_id = (isset($_POST['main_parent_initial_symptom_id']) AND $_POST['main_parent_initial_symptom_id'] != "") ? $_POST['main_parent_initial_symptom_id'] : null;
						$comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : null;
						// $is_ns_connect = (isset($_POST['is_ns_connect']) AND $_POST['is_ns_connect'] != "") ? $_POST['is_ns_connect'] : 0;
						$is_ns_connect = 0;
						// $ns_connect_note = (isset($_POST['ns_connect_note']) AND $_POST['ns_connect_note'] != "") ? mysqli_real_escape_string($db, $_POST['ns_connect_note']) : null;
						$ns_connect_note = null;
						// $is_pasted = (isset($_POST['is_pasted']) AND $_POST['is_pasted'] != "") ? $_POST['is_pasted'] : 0;
						$is_pasted = 0;
						// $is_ns_paste = (isset($_POST['is_ns_paste']) AND $_POST['is_ns_paste'] != "") ? $_POST['is_ns_paste'] : 0;
						$is_ns_paste = 0;
						// $ns_paste_note = (isset($_POST['ns_paste_note']) AND $_POST['ns_paste_note'] != "") ? mysqli_real_escape_string($db, $_POST['ns_paste_note']) : null;
						$ns_paste_note = null;
						$subConnetionsArray = (isset($_POST['sub_connetions_array']) AND !empty($_POST['sub_connetions_array'])) ? $_POST['sub_connetions_array'] : array();
						$updateableSymptomIds = (isset($_POST['updateable_symptom_ids']) AND !empty($_POST['updateable_symptom_ids'])) ? $_POST['updateable_symptom_ids'] : array();
						$removable_sets = (isset($_POST['removable_sets']) AND !empty($_POST['removable_sets'])) ? $_POST['removable_sets'] : array();
						$appendableMateriaMedicaSymptomId = "";

						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != "" AND $is_connected != "" AND $source_arznei_id != "" AND $comparing_source_ids != "" AND $active_symptom_type != "" AND $main_parent_initial_symptom_id != ""){
							if($is_connected == 1)
								$is_connected = 0;
							else
								$is_connected = 1;

							$comparingSourceIdsArr = explode(',', $comparing_source_ids);

							// While it is connect making all sub level connections directly connect to the initial symptom START
							$mainInitialSourceId = "";
							$mainParentInitialSymptomResult = mysqli_query($db, "SELECT quelle_id FROM quelle_import_test WHERE id = '".$main_parent_initial_symptom_id."'");
							if(mysqli_num_rows($mainParentInitialSymptomResult) > 0){
								$mainIniRow = mysqli_fetch_assoc($mainParentInitialSymptomResult);
								$mainInitialSourceId = $mainIniRow['quelle_id'];
							}

							$workingSymptomId = "";
							$againstSymptomId = "";
							if($active_symptom_type == "comparing"){
								$workingSymptomId = $comparing_source_symptom_id;
								$againstSymptomId = $initial_source_symptom_id;
								$workingSourceId = $comparing_source_id;
								$againstSourceId = $initial_source_id;
							}
							else if($active_symptom_type == "initial"){
								$workingSymptomId = $initial_source_symptom_id;
								$againstSymptomId = $comparing_source_symptom_id;
								$workingSourceId = $initial_source_id;
								$againstSourceId = $comparing_source_id;
							}
							
							if($mainInitialSourceId != "" AND $is_connected == 1)
							{
								if(empty($subConnetionsArray))
								{
									$comparedSourcersOfInitialSource = array();
									$workingSourceIdsArr = explode(',', $comparing_source_ids);
									if(!in_array($mainInitialSourceId, $workingSourceIdsArr))
										array_push($workingSourceIdsArr, $mainInitialSourceId);

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
									$mainInitialSourceIdInArr = explode(',', $mainInitialSourceId);
									if(!empty($mainInitialSourceIdInArr)){
										$returnedIds = getAllComparedSourceIds($mainInitialSourceIdInArr);
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
									$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;

									if($workingSymptomId != "" AND $againstSymptomId != ""){
										if(!in_array($workingSymptomId, $updateableSymptomIds))
											array_push($updateableSymptomIds, $workingSymptomId);
										if(!in_array($againstSymptomId, $updateableSymptomIds))
											array_push($updateableSymptomIds, $againstSymptomId);


										$escapeSymptomIdsArr = (!empty($updateableSymptomIds)) ? $updateableSymptomIds : array();
										if (($key = array_search($workingSymptomId, $escapeSymptomIdsArr)) !== false) {
										    unset($escapeSymptomIdsArr[$key]);
										}
										$escapeSymptomIds = implode(',', $escapeSymptomIdsArr);

										$connectedSymptomResult = mysqli_query($db, "SELECT id, initial_source_symptom_id, comparing_source_symptom_id, initial_source_id, comparing_source_id FROM symptom_connections WHERE ((initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative.")) AND (initial_source_symptom_id NOT IN (".$escapeSymptomIds.") AND comparing_source_symptom_id NOT IN (".$escapeSymptomIds."))) AND source_arznei_id = '".$source_arznei_id."'");
										if(mysqli_num_rows($connectedSymptomResult) > 0){
											while($connectedRow = mysqli_fetch_array($connectedSymptomResult)){
												$dataArray = array(); 
												if($workingSymptomId == $connectedRow['initial_source_symptom_id']){
													$activeSymptomId = $connectedRow['comparing_source_symptom_id'];
													$activeSymptomSourceId = $connectedRow['comparing_source_id'];
													$oppositeSymptomId = $connectedRow['initial_source_symptom_id'];
													$oppositeSymptomSourceId = $connectedRow['initial_source_id'];
												}
												else{
													$activeSymptomId = $connectedRow['initial_source_symptom_id'];
													$activeSymptomSourceId = $connectedRow['initial_source_id'];
													$oppositeSymptomId = $connectedRow['comparing_source_symptom_id'];
													$oppositeSymptomSourceId = $connectedRow['comparing_source_id'];
												}
												$dataArray['active_symptom_id'] = $activeSymptomId;
												$dataArray['active_symptom_source_id'] = $activeSymptomSourceId;
												$dataArray['opposite_symptom_id'] = $oppositeSymptomId;
												$dataArray['opposite_symptom_source_id'] = $oppositeSymptomSourceId;

												$subConnetionsArray[] = $dataArray;
											}	
										}
									}
								}
								else
								{
									$returnedData = sublevelConnectOperations($subConnetionsArray, $updateableSymptomIds, $removable_sets, $main_parent_initial_symptom_id, $mainInitialSourceId, $comparison_option, $comparing_source_ids, $initial_source_symptom_id, $comparing_source_symptom_id, $source_arznei_id);

									$subConnetionsArray = (isset($returnedData['sub_connetions_array']) AND !empty($returnedData['sub_connetions_array'])) ? $returnedData['sub_connetions_array'] : array();
									if(isset($returnedData['updateable_symptom_ids']) AND !empty($returnedData['updateable_symptom_ids']))
										$updateableSymptomIds = $returnedData['updateable_symptom_ids'];
									// $removable_sets = (isset($returnedData['removable_sets']) AND !empty($returnedData['removable_sets'])) ? $returnedData['removable_sets'] : array();
								}
							}
							else
							{
								$dataArray = array();
								$dataArray['active_symptom_id'] = $workingSymptomId;
								$dataArray['opposite_symptom_id'] = $againstSymptomId;
								$dataArray['matching_percentage'] = $matching_percentage;
								$removable_sets[] = $dataArray;

								if(!in_array($initial_source_symptom_id, $updateableSymptomIds))
									array_push($updateableSymptomIds, $initial_source_symptom_id);
								if(!in_array($comparing_source_symptom_id, $updateableSymptomIds))
									array_push($updateableSymptomIds, $comparing_source_symptom_id);
							}

							if(empty($subConnetionsArray)){

								if($is_connected == 1){
									$initial_source_code = null;
									$comparing_source_code = null;
									$InitialSymptomResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code FROM quelle_import_test WHERE quelle_import_test.id = '".$initial_source_symptom_id."'");
									if(mysqli_num_rows($InitialSymptomResult) > 0){
										$iniSymRow = mysqli_fetch_assoc($InitialSymptomResult);
										$initial_source_code = (isset($iniSymRow['quelle_code']) AND $iniSymRow['quelle_code'] != "") ? mysqli_real_escape_string($db, trim($iniSymRow['quelle_code'])) : null;
									}

									$comparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code FROM quelle_import_test WHERE quelle_import_test.id = '".$comparing_source_symptom_id."'");
									if(mysqli_num_rows($comparingSymptomResult) > 0){
										$comparingSymptomRow = mysqli_fetch_assoc($comparingSymptomResult);
										$comparing_source_code = (isset($comparingSymptomRow['quelle_code']) AND $comparingSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, trim($comparingSymptomRow['quelle_code'])) : null;
									}

									$connectedSymptomResult = mysqli_query($db, "SELECT id FROM symptom_connections WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND source_arznei_id = '".$source_arznei_id."'");
									if(mysqli_num_rows($connectedSymptomResult) == 0){
										$query="INSERT INTO symptom_connections (source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note) VALUES (NULLIF('".$source_arznei_id."', ''), '".$is_initial_source."', NULLIF('".$initial_source_id."', ''), NULLIF('".$comparing_source_id."', ''), NULLIF('".$initial_source_code."', ''), NULLIF('".$comparing_source_code."', ''), NULLIF('".$initial_source_symptom_id."', ''), NULLIF('".$comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted."', ''), NULLIF('".$comparing_source_symptom_highlighted."', ''), NULLIF('".$initial_source_symptom."', ''), NULLIF('".$comparing_source_symptom."', ''), NULLIF('".$matching_percentage."', ''), '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''))";
										$db->query($query);
										$rowId = mysqli_insert_id($db);
									}
								}
								else
								{
									// If connection is made with saved comparison/Materia medica than disconnected symptom is apprearing at the bottom as a initial symptom of that Materia medica.
									$initialSourceType = "";
									$comparingSourceType = "";
									$initialSourceMasterId = "";
									$comparingSourceMasterId = "";
									$initialSourceResult = mysqli_query($db,"SELECT Q.quelle_type_id, QIM.id AS master_id FROM quelle AS Q LEFT JOIN quelle_import_master AS QIM ON Q.quelle_id = QIM.quelle_id WHERE Q.quelle_id = '".$initial_source_id."'");
									if(mysqli_num_rows($initialSourceResult) > 0){
										$iniSourceRow = mysqli_fetch_assoc($initialSourceResult);
										$initialSourceType = $iniSourceRow['quelle_type_id'];
										$initialSourceMasterId = $iniSourceRow['master_id'];
									}
									$comparingSourceResult = mysqli_query($db,"SELECT Q.quelle_type_id, QIM.id AS master_id FROM quelle AS Q LEFT JOIN quelle_import_master AS QIM ON Q.quelle_id = QIM.quelle_id WHERE Q.quelle_id = '".$comparing_source_id."'");
									if(mysqli_num_rows($comparingSourceResult) > 0){
										$comSourceRow = mysqli_fetch_assoc($comparingSourceResult);
										$comparingSourceType = $comSourceRow['quelle_type_id'];
										$comparingSourceMasterId = $comSourceRow['master_id'];
									}

									// Remove the current connection
									$deleteQuery="DELETE FROM symptom_connections WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND source_arznei_id = '".$source_arznei_id."'";
									$db->query($deleteQuery);

									// 3 = Materia medica 
									if($initialSourceType == 3){
										$checkConnections = mysqli_query($db,"SELECT id FROM symptom_connections WHERE (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."') AND (initial_source_id = '".$initial_source_id."' OR comparing_source_id = '".$initial_source_id."')");
										if(mysqli_num_rows($checkConnections) == 0 AND !in_array($comparing_source_id, $comparingSourceIdsArr)){
											$addResult = addTheSymptomInConnectionOperation($comparing_source_symptom_id, $initialSourceMasterId, $initial_source_id);
											if($addResult['status'] === true)
											{
												$matericaMedicaSymptomId = $addResult['return_data']['symptom_id'];
												if($initial_source_id == $mainInitialSourceId)
													$appendableMateriaMedicaSymptomId = $matericaMedicaSymptomId;
											}
										}
									}
									if($comparingSourceType == 3){
										$checkConnections = mysqli_query($db,"SELECT id FROM symptom_connections WHERE (initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_id = '".$comparing_source_id."' OR comparing_source_id = '".$comparing_source_id."')");
										if(mysqli_num_rows($checkConnections) == 0 AND !in_array($initial_source_id, $comparingSourceIdsArr)){
											$addResult = addTheSymptomInConnectionOperation($initial_source_symptom_id, $comparingSourceMasterId, $comparing_source_id);
											if($addResult['status'] === true)
											{
												$matericaMedicaSymptomId = $addResult['return_data']['symptom_id'];
												if($comparing_source_id == $mainInitialSourceId)
													$appendableMateriaMedicaSymptomId = $matericaMedicaSymptomId;
											}
										}
									}

									
								}
							}

							$data = array();
							$data['sub_connetions_array'] = $subConnetionsArray;
							$data['updateable_symptom_ids'] = $updateableSymptomIds;
							$data['removable_sets'] = $removable_sets;
							$data['appendable_materia_medica_symptom_id'] = $appendableMateriaMedicaSymptomId;
							$resultData = $data;
							$status = 'success';
							$message = "Success";
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}			
						break;	
					}
				case 'get_nsc_note':
					{
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : "";
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : "";
						$saved_comparisons_backup_id = (isset($_POST['saved_comparisons_backup_id']) AND $_POST['saved_comparisons_backup_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['saved_comparisons_backup_id']) : "";
						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != ""){
							$connectedSymptomResult = mysqli_query($db, "SELECT id, is_connected, is_ns_connect, ns_connect_note FROM symptom_connections_backup WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
							if(mysqli_num_rows($connectedSymptomResult) > 0){
								$conRow = mysqli_fetch_assoc($connectedSymptomResult);
								$rowId = $conRow['id'];
								$data = array();
								$data['id'] = $conRow['id'];
								$data['ns_connect_note'] = $conRow['ns_connect_note'];
								$resultData = $data;

								$status = 'success';
								$message = "Success";
							}
							else
							{
								$status = 'error';
								$message = 'Data not found';
							}
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}
						break;	
					}
				case 'save_nsc_note':
					{
						$row_id = (isset($_POST['row_id']) AND $_POST['row_id'] != "") ? mysqli_real_escape_string($db, $_POST['row_id']) : null;
						$ns_connect_note = (isset($_POST['nsc_note']) AND $_POST['nsc_note'] !="" ) ? mysqli_real_escape_string($db, $_POST['nsc_note']) : null;
						if($row_id != ""){
							if($ns_connect_note != "")
								$connectionUpdateQuery="UPDATE symptom_connections SET is_ns_connect = 1, ns_connect_note = NULLIF('".$ns_connect_note."', '') WHERE id = '".$row_id."'";
							else
								$connectionUpdateQuery="UPDATE symptom_connections SET is_ns_connect = 0, ns_connect_note = NULLIF('".$ns_connect_note."', '') WHERE id = '".$row_id."'";
							$db->query($connectionUpdateQuery);

							$connectedSymptomResult = mysqli_query($db, "SELECT id, is_connected, is_ns_connect, ns_connect_note FROM symptom_connections WHERE id = '".$row_id."'");
							if(mysqli_num_rows($connectedSymptomResult) > 0){
								$conRow = mysqli_fetch_assoc($connectedSymptomResult);
								$rowId = $conRow['id'];
								$data = array();
								$data['id'] = $conRow['id'];
								$data['is_connected'] = $conRow['is_connected'];
								$data['is_ns_connect'] = $conRow['is_ns_connect'];
								$data['ns_connect_note'] = $conRow['ns_connect_note'];
								$resultData = $data;

								$status = 'success';
								$message = "Success";
							}
							else
							{
								$status = 'error';
								$message = 'Data not found';
							}
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}
						break;	
					}
				case 'paste':
					{
						$unique_id = (isset($_POST['unique_id']) AND $_POST['unique_id'] !="") ? $_POST['unique_id'] : null;
						$source_arznei_id = (isset($_POST['source_arznei_id']) AND $_POST['source_arznei_id'] !="") ? mysqli_real_escape_string($db, $_POST['source_arznei_id']) : null;
						$initial_source_id = (isset($_POST['initial_source_id']) AND $_POST['initial_source_id'] !="") ? mysqli_real_escape_string($db, $_POST['initial_source_id']) : null;
						// $initial_source_code = (isset($_POST['initial_source_code']) AND $_POST['initial_source_code'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_code']) : null;
						$comparing_source_id = (isset($_POST['comparing_source_id']) AND $_POST['comparing_source_id'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_id']) : null;
						// $comparing_source_code = (isset($_POST['comparing_source_code']) AND $_POST['comparing_source_code'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_code']) : null;
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : null;
						$initial_source_symptom = (isset($_POST['initial_source_symptom']) AND $_POST['initial_source_symptom'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom']) : null;
						$comparing_source_symptom = (isset($_POST['comparing_source_symptom']) AND $_POST['comparing_source_symptom'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom']) : null;
						$initial_source_symptom_highlighted = (isset($_POST['initial_source_symptom_highlighted']) AND $_POST['initial_source_symptom_highlighted'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_highlighted']) : null;
						$comparing_source_symptom_highlighted = (isset($_POST['comparing_source_symptom_highlighted']) AND $_POST['comparing_source_symptom_highlighted'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_highlighted']) : null;
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : null;
						$matching_percentage = (isset($_POST['matching_percentage']) AND $_POST['matching_percentage'] != "") ? mysqli_real_escape_string($db, $_POST['matching_percentage']) : 0;
						$is_initial_source = (isset($_POST['is_initial_source']) AND $_POST['is_initial_source'] != "") ? $_POST['is_initial_source'] : 1;
						$is_connected = (isset($_POST['is_connected']) AND $_POST['is_connected'] != "") ? $_POST['is_connected'] : null;
						$comparing_source_ids = (isset($_POST['comparing_source_ids']) AND $_POST['comparing_source_ids'] != "") ? $_POST['comparing_source_ids'] : null;
						$active_symptom_type = (isset($_POST['active_symptom_type']) AND $_POST['active_symptom_type'] != "") ? $_POST['active_symptom_type'] : null;
						$main_parent_initial_symptom_id = (isset($_POST['main_parent_initial_symptom_id']) AND $_POST['main_parent_initial_symptom_id'] != "") ? $_POST['main_parent_initial_symptom_id'] : null;
						$comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : null;
						// $is_ns_connect = (isset($_POST['is_ns_connect']) AND $_POST['is_ns_connect'] != "") ? $_POST['is_ns_connect'] : 0;
						$is_ns_connect = 0;
						// $ns_connect_note = (isset($_POST['ns_connect_note']) AND $_POST['ns_connect_note'] != "") ? mysqli_real_escape_string($db, $_POST['ns_connect_note']) : null;
						$ns_connect_note = null;
						// $is_pasted = (isset($_POST['is_pasted']) AND $_POST['is_pasted'] != "") ? $_POST['is_pasted'] : 0;
						$is_pasted = (isset($_POST['is_pasted']) AND $_POST['is_pasted'] != "") ? $_POST['is_pasted'] : null;
						// $is_ns_paste = (isset($_POST['is_ns_paste']) AND $_POST['is_ns_paste'] != "") ? $_POST['is_ns_paste'] : 0;
						$is_ns_paste = 0;
						// $ns_paste_note = (isset($_POST['ns_paste_note']) AND $_POST['ns_paste_note'] != "") ? mysqli_real_escape_string($db, $_POST['ns_paste_note']) : null;
						$ns_paste_note = null;
						$subConnetionsArray = (isset($_POST['sub_connetions_array']) AND !empty($_POST['sub_connetions_array'])) ? $_POST['sub_connetions_array'] : array();
						$updateableSymptomIds = (isset($_POST['updateable_symptom_ids']) AND !empty($_POST['updateable_symptom_ids'])) ? $_POST['updateable_symptom_ids'] : array();
						$removable_sets = (isset($_POST['removable_sets']) AND !empty($_POST['removable_sets'])) ? $_POST['removable_sets'] : array();

						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != "" AND $is_pasted != "" AND $source_arznei_id != "" AND $comparing_source_ids != "" AND $active_symptom_type != "" AND $main_parent_initial_symptom_id != ""){
							if($is_pasted == 1)
								$is_pasted = 0;
							else
								$is_pasted = 1;

							$mainInitialSourceId = "";
							$mainParentInitialSymptomResult = mysqli_query($db, "SELECT quelle_id FROM quelle_import_test WHERE id = '".$main_parent_initial_symptom_id."'");
							if(mysqli_num_rows($mainParentInitialSymptomResult) > 0){
								$mainIniRow = mysqli_fetch_assoc($mainParentInitialSymptomResult);
								$mainInitialSourceId = $mainIniRow['quelle_id'];
							}

							$workingSymptomId = "";
							$againstSymptomId = "";
							if($active_symptom_type == "comparing"){
								$workingSymptomId = $comparing_source_symptom_id;
								$againstSymptomId = $initial_source_symptom_id;
								$workingSourceId = $comparing_source_id;
								$againstSourceId = $initial_source_id;
							}
							else if($active_symptom_type == "initial"){
								$workingSymptomId = $initial_source_symptom_id;
								$againstSymptomId = $comparing_source_symptom_id;
								$workingSourceId = $initial_source_id;
								$againstSourceId = $comparing_source_id;
							}

							if($mainInitialSourceId != "" AND $is_pasted == 1)
							{
								if(empty($subConnetionsArray))
								{
									$comparedSourcersOfInitialSource = array();
									$workingSourceIdsArr = explode(',', $comparing_source_ids);
									if(!in_array($mainInitialSourceId, $workingSourceIdsArr))
										array_push($workingSourceIdsArr, $mainInitialSourceId);

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
									$mainInitialSourceIdInArr = explode(',', $mainInitialSourceId);
									if(!empty($mainInitialSourceIdInArr)){
										$returnedIds = getAllComparedSourceIds($mainInitialSourceIdInArr);
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
									$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;

									if($workingSymptomId != "" AND $againstSymptomId != ""){
										if(!in_array($workingSymptomId, $updateableSymptomIds))
											array_push($updateableSymptomIds, $workingSymptomId);
										if(!in_array($againstSymptomId, $updateableSymptomIds))
											array_push($updateableSymptomIds, $againstSymptomId);

										$escapeSymptomIdsArr = (!empty($updateableSymptomIds)) ? $updateableSymptomIds : array();
										if (($key = array_search($workingSymptomId, $escapeSymptomIdsArr)) !== false) {
										    unset($escapeSymptomIdsArr[$key]);
										}
										$escapeSymptomIds = implode(',', $escapeSymptomIdsArr);

										$connectedSymptomResult = mysqli_query($db, "SELECT id, initial_source_symptom_id, comparing_source_symptom_id, initial_source_id, comparing_source_id FROM symptom_connections WHERE ((initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative.")) AND (initial_source_symptom_id NOT IN (".$escapeSymptomIds.") AND comparing_source_symptom_id NOT IN (".$escapeSymptomIds."))) AND source_arznei_id = '".$source_arznei_id."'");
										if(mysqli_num_rows($connectedSymptomResult) > 0){
											while($connectedRow = mysqli_fetch_array($connectedSymptomResult)){
												$dataArray = array(); 
												if($workingSymptomId == $connectedRow['initial_source_symptom_id']){
													$activeSymptomId = $connectedRow['comparing_source_symptom_id'];
													$activeSymptomSourceId = $connectedRow['comparing_source_id'];
													$oppositeSymptomId = $connectedRow['initial_source_symptom_id'];
													$oppositeSymptomSourceId = $connectedRow['initial_source_id'];
												}
												else{
													$activeSymptomId = $connectedRow['initial_source_symptom_id'];
													$activeSymptomSourceId = $connectedRow['initial_source_id'];
													$oppositeSymptomId = $connectedRow['comparing_source_symptom_id'];
													$oppositeSymptomSourceId = $connectedRow['comparing_source_id'];
												}
												$dataArray['active_symptom_id'] = $activeSymptomId;
												$dataArray['active_symptom_source_id'] = $activeSymptomSourceId;
												$dataArray['opposite_symptom_id'] = $oppositeSymptomId;
												$dataArray['opposite_symptom_source_id'] = $oppositeSymptomSourceId;

												$subConnetionsArray[] = $dataArray;
											}	
										}
									}
								}
								else
								{
									$returnedData = sublevelConnectOperations($subConnetionsArray, $updateableSymptomIds, $removable_sets, $main_parent_initial_symptom_id, $mainInitialSourceId, $comparison_option, $comparing_source_ids, $initial_source_symptom_id, $comparing_source_symptom_id, $source_arznei_id);

									$subConnetionsArray = (isset($returnedData['sub_connetions_array']) AND !empty($returnedData['sub_connetions_array'])) ? $returnedData['sub_connetions_array'] : array();
									if(isset($returnedData['updateable_symptom_ids']) AND !empty($returnedData['updateable_symptom_ids']))
										$updateableSymptomIds = $returnedData['updateable_symptom_ids'];
									// $removable_sets = (isset($returnedData['removable_sets']) AND !empty($returnedData['removable_sets'])) ? $returnedData['removable_sets'] : array();
								}
							}
							else
							{
								$dataArray = array();
								$dataArray['active_symptom_id'] = $workingSymptomId;
								$dataArray['opposite_symptom_id'] = $againstSymptomId;
								$dataArray['matching_percentage'] = $matching_percentage;
								$removable_sets[] = $dataArray;

								if(!in_array($initial_source_symptom_id, $updateableSymptomIds))
									array_push($updateableSymptomIds, $initial_source_symptom_id);
								if(!in_array($comparing_source_symptom_id, $updateableSymptomIds))
									array_push($updateableSymptomIds, $comparing_source_symptom_id);
							}

							if(empty($subConnetionsArray)){
								if($is_pasted == 1)
								{
									$initial_source_code = null;
									$comparing_source_code = null;
									$InitialSymptomResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code FROM quelle_import_test WHERE quelle_import_test.id = '".$initial_source_symptom_id."'");
									if(mysqli_num_rows($InitialSymptomResult) > 0){
										$iniSymRow = mysqli_fetch_assoc($InitialSymptomResult);
										$initial_source_code = (isset($iniSymRow['quelle_code']) AND $iniSymRow['quelle_code'] != "") ? mysqli_real_escape_string($db, trim($iniSymRow['quelle_code'])) : null;
									}

									$comparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code FROM quelle_import_test WHERE quelle_import_test.id = '".$comparing_source_symptom_id."'");
									if(mysqli_num_rows($comparingSymptomResult) > 0){
										$comparingSymptomRow = mysqli_fetch_assoc($comparingSymptomResult);
										$comparing_source_code = (isset($comparingSymptomRow['quelle_code']) AND $comparingSymptomRow['quelle_code'] != "") ? mysqli_real_escape_string($db, trim($comparingSymptomRow['quelle_code'])) : null;
									}

									$query="INSERT INTO symptom_connections (source_arznei_id, is_initial_source, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note) VALUES (NULLIF('".$source_arznei_id."', ''),'".$is_initial_source."', NULLIF('".$initial_source_id."', ''), NULLIF('".$comparing_source_id."', ''), NULLIF('".$initial_source_code."', ''), NULLIF('".$comparing_source_code."', ''), NULLIF('".$initial_source_symptom_id."', ''), NULLIF('".$comparing_source_symptom_id."', ''), NULLIF('".$initial_source_symptom_highlighted."', ''), NULLIF('".$comparing_source_symptom_highlighted."', ''), NULLIF('".$initial_source_symptom."', ''), NULLIF('".$comparing_source_symptom."', ''), NULLIF('".$matching_percentage."', ''), '".$is_connected."', '".$is_ns_connect."', NULLIF('".$ns_connect_note."', ''), '".$is_pasted."', '".$is_ns_paste."', NULLIF('".$ns_paste_note."', ''))";
									$db->query($query);
									$rowId = mysqli_insert_id($db);
								}
								else
								{
									// Remove the current connection
									$deleteQuery="DELETE FROM symptom_connections WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND source_arznei_id  = '".$source_arznei_id."'";
									$db->query($deleteQuery);
								}
							}

							$data = array();
							$data['sub_connetions_array'] = $subConnetionsArray;
							$data['updateable_symptom_ids'] = $updateableSymptomIds;
							$data['removable_sets'] = $removable_sets;
							$resultData = $data;
							$status = 'success';
							$message = "Success";
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}			
						break;	
					}
				case 'get_nsp_note':
					{
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : "";
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : "";
						$saved_comparisons_backup_id = (isset($_POST['saved_comparisons_backup_id']) AND $_POST['saved_comparisons_backup_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['saved_comparisons_backup_id']) : "";
						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != ""){
							$connectedSymptomResult = mysqli_query($db, "SELECT id, is_pasted, is_ns_paste, ns_paste_note FROM symptom_connections WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND '".$saved_comparisons_backup_id."'");
							if(mysqli_num_rows($connectedSymptomResult) > 0){
								$conRow = mysqli_fetch_assoc($connectedSymptomResult);
								$rowId = $conRow['id'];
								$data = array();
								$data['id'] = $conRow['id'];
								$data['ns_paste_note'] = $conRow['ns_paste_note'];
								$resultData = $data;

								$status = 'success';
								$message = "Success";
							}
							else
							{
								$status = 'error';
								$message = 'Data not found';
							}
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}
						break;	
					}
				case 'save_nsp_note':
					{
						$row_id = (isset($_POST['row_id']) AND $_POST['row_id'] != "") ? mysqli_real_escape_string($db, $_POST['row_id']) : null;
						$ns_paste_note = (isset($_POST['nsp_note']) AND $_POST['nsp_note'] !="" ) ? mysqli_real_escape_string($db, $_POST['nsp_note']) : null;
						if($row_id != ""){
							if($ns_paste_note != "")
								$connectionUpdateQuery="UPDATE symptom_connections SET is_ns_paste = 1, ns_paste_note = NULLIF('".$ns_paste_note."', '') WHERE id = '".$row_id."'";
							else
								$connectionUpdateQuery="UPDATE symptom_connections SET is_ns_paste = 0, ns_paste_note = NULLIF('".$ns_paste_note."', '') WHERE id = '".$row_id."'";
							$db->query($connectionUpdateQuery);

							$connectedSymptomResult = mysqli_query($db, "SELECT id, is_pasted, is_ns_paste, ns_paste_note FROM symptom_connections WHERE id = '".$row_id."'");
							if(mysqli_num_rows($connectedSymptomResult) > 0){
								$conRow = mysqli_fetch_assoc($connectedSymptomResult);
								$rowId = $conRow['id'];
								$data = array();
								$data['id'] = $conRow['id'];
								$data['is_pasted'] = $conRow['is_pasted'];
								$data['is_ns_paste'] = $conRow['is_ns_paste'];
								$data['ns_paste_note'] = $conRow['ns_paste_note'];
								$resultData = $data;

								$status = 'success';
								$message = "Success";
							}
							else
							{
								$status = 'error';
								$message = 'Data not found';
							}
						}
						else
						{
							$status = 'error';
							$message = 'Could not find the required data';
						}
						break;	
					}
				
				default:
					break;
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>