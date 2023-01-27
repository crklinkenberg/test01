<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Performing applicable connections related operations (This is used in backup section) 
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
						$source_arznei_id = (isset($_POST['source_arznei_id']) AND $_POST['source_arznei_id'] !="") ? mysqli_real_escape_string($db, $_POST['source_arznei_id']) : null;
						$initial_source_id = (isset($_POST['initial_source_id']) AND $_POST['initial_source_id'] !="") ? mysqli_real_escape_string($db, $_POST['initial_source_id']) : null;
						$comparing_source_id = (isset($_POST['comparing_source_id']) AND $_POST['comparing_source_id'] != "") ? mysqli_real_escape_string($db, $_POST['comparing_source_id']) : null;
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : null;
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : null;
						$is_connected = (isset($_POST['is_connected']) AND $_POST['is_connected'] != "") ? $_POST['is_connected'] : null;
						$active_symptom_type = (isset($_POST['active_symptom_type']) AND $_POST['active_symptom_type'] != "") ? $_POST['active_symptom_type'] : null;
						$main_parent_initial_symptom_id = (isset($_POST['main_parent_initial_symptom_id']) AND $_POST['main_parent_initial_symptom_id'] != "") ? $_POST['main_parent_initial_symptom_id'] : null;

						if($active_symptom_type == "initial")
							$checkingSymptomId = $comparing_source_symptom_id;
						else
							$checkingSymptomId = $initial_source_symptom_id;

						$appendableMateriaMedicaSymptomId = "";

						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != "" AND $is_connected == 1 AND $source_arznei_id != ""){

							$mainInitialSourceId = "";
							$mainParentInitialSymptomResult = mysqli_query($db, "SELECT quelle_id FROM quelle_import_test WHERE id = '".$main_parent_initial_symptom_id."'");
							if(mysqli_num_rows($mainParentInitialSymptomResult) > 0){
								$mainIniRow = mysqli_fetch_assoc($mainParentInitialSymptomResult);
								$mainInitialSourceId = $mainIniRow['quelle_id'];
							}
							// If connection is made with saved comparison/Materia medica adding the disconnected symptom at the bottom as a initial symptom in that Materia medica.
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
								if(mysqli_num_rows($checkConnections) == 0){
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
								if(mysqli_num_rows($checkConnections) == 0){
									$addResult = addTheSymptomInConnectionOperation($initial_source_symptom_id, $comparingSourceMasterId, $comparing_source_id);
									if($addResult['status'] === true)
									{
										$matericaMedicaSymptomId = $addResult['return_data']['symptom_id'];
										if($comparing_source_id == $mainInitialSourceId)
											$appendableMateriaMedicaSymptomId = $matericaMedicaSymptomId;
									}
								}
							}

							$data = array();
							$connectedSymptomResult = mysqli_query($db,"SELECT id, is_saved, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note FROM symptom_connections WHERE (initial_source_symptom_id = '".$checkingSymptomId."' OR comparing_source_symptom_id = '".$checkingSymptomId."') AND is_connected = 1 ORDER BY matching_percentage DESC");
							$data['remaining_number_of_connections'] = mysqli_num_rows($connectedSymptomResult);

							// If connection is made with saved comparison/Materia medica than disconnected symptom is apprearing at the bottom as a initial symptom of that Materia medica.
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
						$source_arznei_id = (isset($_POST['source_arznei_id']) AND $_POST['source_arznei_id'] !="") ? mysqli_real_escape_string($db, $_POST['source_arznei_id']) : null;
						$initial_source_symptom_id = (isset($_POST['initial_source_symptom_id']) AND $_POST['initial_source_symptom_id'] != "") ? mysqli_real_escape_string($db, $_POST['initial_source_symptom_id']) : null;
						$comparing_source_symptom_id = (isset($_POST['comparing_source_symptom_id']) AND $_POST['comparing_source_symptom_id'] !="" ) ? mysqli_real_escape_string($db, $_POST['comparing_source_symptom_id']) : null;
						$is_pasted = (isset($_POST['is_pasted']) AND $_POST['is_pasted'] != "") ? $_POST['is_pasted'] : null;
						$active_symptom_type = (isset($_POST['active_symptom_type']) AND $_POST['active_symptom_type'] != "") ? $_POST['active_symptom_type'] : null;

						if($active_symptom_type == "initial")
							$checkingSymptomId = $comparing_source_symptom_id;
						else
							$checkingSymptomId = $initial_source_symptom_id;
						
						if($initial_source_symptom_id != "" AND $comparing_source_symptom_id != "" AND $is_pasted == 1 AND $source_arznei_id != ""){

							// Remove the current connection
							$deleteQuery="DELETE FROM symptom_connections WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND source_arznei_id = '".$source_arznei_id."'";
							$db->query($deleteQuery);

							$data = array();
							$connectedSymptomResult = mysqli_query($db,"SELECT id, is_saved, initial_source_id, comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note FROM symptom_connections WHERE (initial_source_symptom_id = '".$checkingSymptomId."' OR comparing_source_symptom_id = '".$checkingSymptomId."') AND is_connected = 1 ORDER BY matching_percentage DESC");
							$data['remaining_number_of_connections'] = mysqli_num_rows($connectedSymptomResult);

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
							$connectedSymptomResult = mysqli_query($db, "SELECT id, is_pasted, is_ns_paste, ns_paste_note FROM symptom_connections_backup WHERE ((initial_source_symptom_id = '".$initial_source_symptom_id."' OR comparing_source_symptom_id = '".$initial_source_symptom_id."') AND (initial_source_symptom_id = '".$comparing_source_symptom_id."' OR comparing_source_symptom_id = '".$comparing_source_symptom_id."')) AND '".$saved_comparisons_backup_id."'");
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
	    // echo '<p>', $e->getMessage(), '</p>';
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>