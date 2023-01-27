<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		$add_translation_master_id = (isset($_POST['add_translation_master_id']) AND $_POST['add_translation_master_id'] != "") ? $_POST['add_translation_master_id'] : "";
		$add_translation_arznei_id = (isset($_POST['add_translation_arznei_id']) AND $_POST['add_translation_arznei_id'] != "") ? $_POST['add_translation_arznei_id'] : "";
		$add_translation_quelle_id = (isset($_POST['add_translation_quelle_id']) AND $_POST['add_translation_quelle_id'] != "") ? $_POST['add_translation_quelle_id'] : "";
		$add_translation_language = (isset($_POST['add_translation_language']) AND $_POST['add_translation_language'] != "") ? $_POST['add_translation_language'] : "";
		$temp_symptom_id = (isset($_POST['temp_symptom_id']) AND $_POST['temp_symptom_id'] != "") ? $_POST['temp_symptom_id'] : "";
		$action = (isset($_POST['action']) AND $_POST['action'] != "") ? $_POST['action'] : "";

		if($add_translation_master_id == "" OR $add_translation_arznei_id == "" OR $add_translation_quelle_id == "" OR $action == ""){
			$status = 'error';
    		$message = 'Some required data not found. Please reload the page and try again!';
			echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
			exit;
		} else {
			try {
				switch ($action) {
					case 'continue':
						{
							if($temp_symptom_id == ""){
								$status = 'error';
					    		$message = 'Some required data not found. Please reload the page and try again!';
								echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
								exit;
							} 
							// UPDATING TEMP SYMPTOM MAKING IT APPROVED
							$updateTempSymptomQuery = "UPDATE temp_translation_symptoms SET need_approval = 0 WHERE id = '".$temp_symptom_id."'";
							$db->query($updateTempSymptomQuery);

							// Checking is this translation already available
							$importingLanguageSuffix = "";
							$oppositeLanguageSuffix = "";
							$is_translation_added = 0;
							if($add_translation_language == "de"){
								$importingLanguageSuffix = "de";
								$oppositeLanguageSuffix = "en";
							}
							if($add_translation_language == "en"){
								$importingLanguageSuffix = "en";
								$oppositeLanguageSuffix = "de";
							}


							if($importingLanguageSuffix != ""){
								$queryRes = mysqli_query($db,"SELECT QIM.id FROM quelle_import_master AS QIM JOIN arznei_quelle AS AQ ON QIM.quelle_id = AQ.quelle_id WHERE QIM.is_symptoms_available_in_".$importingLanguageSuffix." = 1 AND QIM.id = '".$add_translation_master_id."' AND AQ.quelle_id = '".$add_translation_quelle_id."' AND AQ.arznei_id = '".$add_translation_arznei_id."'");
								if(mysqli_num_rows($queryRes) > 0)
									$is_translation_added = 1;
							}

							if($is_translation_added == 0){
								$tempTransQuelleQuery = mysqli_query($db,"SELECT translation_language, translation_method FROM temp_translation_quelle WHERE master_id = '".$add_translation_master_id."' AND quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."'");
								if(mysqli_num_rows($tempTransQuelleQuery) > 0)
									$tempTransQuelleData = mysqli_fetch_assoc($tempTransQuelleQuery);
								$translation_language = (isset($tempTransQuelleData['translation_language']) AND $tempTransQuelleData['translation_language'] != "") ? $tempTransQuelleData['translation_language'] : "";
								$translation_method = (isset($tempTransQuelleData['translation_method']) AND $tempTransQuelleData['translation_method'] != "") ? $tempTransQuelleData['translation_method'] : "";

								if($translation_language != "" AND $translation_method != ""){
									if($translation_language == "de")
										$oposite_translation_language = "en";
									else
										$oposite_translation_language = "de";

									// CHECKING IF ANY MORE TEMP SYMPTOM NEED USER APPROVAL
									$tempTransSymptomNeedApprovalQuery = mysqli_query($db,"SELECT id FROM temp_translation_symptoms WHERE master_id = '".$add_translation_master_id."' AND quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."' AND need_approval = 1");
									if(mysqli_num_rows($tempTransSymptomNeedApprovalQuery) > 0){
										$data = array();
										$data['need_approval'] = 1;
										$resultData = $data;

										$status = "success";
										$message = "success";
									} else {
										// ADDING THE TRANSLATION IN THE MAIN AND IT'S BACKUP TABLES
										$tempTransSymptomQuery = mysqli_query($db,"SELECT id, symptom_id, Beschreibung_".$translation_language.", BeschreibungOriginal_".$translation_language.", BeschreibungFull_".$translation_language.", BeschreibungPlain_".$translation_language.", searchable_text_".$translation_language.", bracketedString_".$translation_language.", timeString_".$translation_language." FROM temp_translation_symptoms WHERE master_id = '".$add_translation_master_id."' AND quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."' ORDER BY id ASC");
										$totalTempTransSymptom = mysqli_num_rows($tempTransSymptomQuery);
										if(mysqli_num_rows($tempTransSymptomQuery) > 0){
											$tempTransSymptomMasterArray = array();
											while($tempTransSymptomRow = mysqli_fetch_array($tempTransSymptomQuery)){
												$updData = array();
												$updData['Beschreibung_'.$translation_language] = ($tempTransSymptomRow['Beschreibung_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['Beschreibung_'.$translation_language]) : null;
												$updData['BeschreibungOriginal_'.$translation_language] = ($tempTransSymptomRow['BeschreibungOriginal_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['BeschreibungOriginal_'.$translation_language]) : null;
												$updData['BeschreibungFull_'.$translation_language] = ($tempTransSymptomRow['BeschreibungFull_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['BeschreibungFull_'.$translation_language]) : null;
												$updData['BeschreibungPlain_'.$translation_language] = ($tempTransSymptomRow['BeschreibungPlain_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['BeschreibungPlain_'.$translation_language]) : null;
												$updData['searchable_text_'.$translation_language] = ($tempTransSymptomRow['searchable_text_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['searchable_text_'.$translation_language]) : null;
												$updData['bracketedString_'.$translation_language] = ($tempTransSymptomRow['bracketedString_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['bracketedString_'.$translation_language]) : null;
												$updData['timeString_'.$translation_language] = ($tempTransSymptomRow['timeString_'.$translation_language] != "") ? mysqli_real_escape_string($db, $tempTransSymptomRow['timeString_'.$translation_language]) : null;
												$updData['symptom_id'] = $tempTransSymptomRow['symptom_id'];


												// For combined sources dynamic "_completed" table updates SATRT
												$isCombinedSource = 0;
												$combinedSourceCompletedTable = "";
												$isCombinedSourceSaved = "";
												$checkQuelle = mysqli_query($db,"SELECT Q.quelle_id FROM quelle AS Q WHERE Q.quelle_type_id = 3 AND Q.quelle_id = '".$add_translation_quelle_id."'");
												if(mysqli_num_rows($checkQuelle) > 0){
													$isCombinedSource = 1;
													$preComparisonMasterDataQuery = $db->query("SELECT table_name, comparison_save_status FROM pre_comparison_master_data WHERE quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."'");
													if($preComparisonMasterDataQuery->num_rows > 0)
														$preComparisonMasterData = mysqli_fetch_assoc($preComparisonMasterDataQuery);
													$combinedSourceCompletedTable = (isset($preComparisonMasterData['table_name']) AND $preComparisonMasterData['table_name'] != "") ? $preComparisonMasterData['table_name'] : "";
													// 0 = Initial stage when compared(Blue), 1 = State when user saved comparison(Yellow), 2 = State when admin approved the saved comparison(Green)
													$isCombinedSourceSaved = (isset($preComparisonMasterData['comparison_save_status']) AND $preComparisonMasterData['comparison_save_status'] != 2) ? 0 : 1;
												}

												if($isCombinedSource == 1 AND $isCombinedSourceSaved == 1 AND $combinedSourceCompletedTable != ""){
													$updData['original_symptom_id'] = $tempTransSymptomRow['symptom_id'];
												}
												else
												{
													$getOrgSymptomIdQuery = mysqli_query($db,"SELECT original_symptom_id FROM quelle_import_test WHERE id = '".$updData['symptom_id']."'");
													if(mysqli_num_rows($getOrgSymptomIdQuery) > 0)
														$OrgSymptomIdRow = mysqli_fetch_assoc($getOrgSymptomIdQuery);
													$updData['original_symptom_id'] = (isset($OrgSymptomIdRow['original_symptom_id']) AND $OrgSymptomIdRow['original_symptom_id'] != "") ? $OrgSymptomIdRow['original_symptom_id'] : "";
												}
												// For combined sources dynamic "_completed" table updates END

												$tempTransSymptomMasterArray[] = $updData;

												// For combined sources dynamic "_completed" table updates SATRT
												if($isCombinedSource == 1 AND $isCombinedSourceSaved == 1 AND $combinedSourceCompletedTable != ""){
													$checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$combinedSourceCompletedTable."'");
													if(mysqli_num_rows($checkIfComparisonCompleteTableExist) == 0)
													{
														$updMainSymptomsQuery = "UPDATE ".$combinedSourceCompletedTable." SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$updData['symptom_id']."'";
														$db->query($updMainSymptomsQuery);
													}
												}
												// For combined sources dynamic "_completed" table updates END
												$updMainSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$updData['symptom_id']."'";
												$db->query($updMainSymptomsQuery);

												if ($translation_method == "Professional Translation") {
													$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE original_symptom_id = '".$updData['symptom_id']."'";
													$db->query($updSavedComparisonSymptomsQuery);

													if($updData['original_symptom_id'] != ""){
														$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE original_symptom_id = '".$updData['original_symptom_id']."'";
														$db->query($updSavedComparisonSymptomsQuery);
													}

													// For symptom backup table
													$updSavedComparisonBackupSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE original_symptom_id = '".$updData['symptom_id']."'";
													$db->query($updSavedComparisonBackupSymptomsQuery);

													if($updData['original_symptom_id'] != ""){
														$updSavedComparisonBackupSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE original_symptom_id = '".$updData['original_symptom_id']."'";
														$db->query($updSavedComparisonBackupSymptomsQuery);
													}
												} else {
													$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE original_symptom_id = '".$updData['symptom_id']."' AND (Beschreibung_".$translation_language." IS NULL OR Beschreibung_".$translation_language." = '')");
													if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
														while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
															$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
															$db->query($updSavedComparisonSymptomsQuery);
														}
													}

													if($updData['original_symptom_id'] != ""){
														$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE original_symptom_id = '".$updData['original_symptom_id']."' AND (Beschreibung_".$translation_language." IS NULL OR Beschreibung_".$translation_language." = '')");
														if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
															while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
																$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
																$db->query($updSavedComparisonSymptomsQuery);
															}
														}
													}

													// For symptom backup table
													$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_backup WHERE original_symptom_id = '".$updData['symptom_id']."' AND (Beschreibung_".$translation_language." IS NULL OR Beschreibung_".$translation_language." = '')");
													if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
														while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
															$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
															$db->query($updSavedComparisonSymptomsQuery);
														}
													}

													if($updData['original_symptom_id'] != ""){
														$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_backup WHERE original_symptom_id = '".$updData['original_symptom_id']."' AND (Beschreibung_".$translation_language." IS NULL OR Beschreibung_".$translation_language." = '')");
														if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
															while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
																$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
																$db->query($updSavedComparisonSymptomsQuery);
															}
														}
													}
												}
											}

											// Collecting all backup sets and updating them accordingly.
											$getQuelleMasterBackupQuery = mysqli_query($db,"SELECT id FROM quelle_import_master_backup WHERE arznei_id = '".$add_translation_arznei_id."' AND original_quelle_id = '".$add_translation_quelle_id."'");
											if(mysqli_num_rows($getQuelleMasterBackupQuery) > 0){
												while($quelleMasterBackupRow = mysqli_fetch_array($getQuelleMasterBackupQuery)){
													$backupSouceSymptomInfo = mysqli_query($db,"SELECT id, Beschreibung_".$translation_language." FROM quelle_import_backup WHERE master_id = '".$quelleMasterBackupRow['id']."'");
													$totalBackupSouceSymptoms = mysqli_num_rows($backupSouceSymptomInfo);

													if($totalTempTransSymptom == $totalBackupSouceSymptoms){
														$arrayKey = 0;
														while($symptomRow = mysqli_fetch_array($backupSouceSymptomInfo)){
															$updData = array();
															$updData['Beschreibung_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['Beschreibung_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['Beschreibung_'.$translation_language] : null;
															$updData['BeschreibungOriginal_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['BeschreibungOriginal_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['BeschreibungOriginal_'.$translation_language] : null;
															$updData['BeschreibungFull_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['BeschreibungFull_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['BeschreibungFull_'.$translation_language] : null;
															$updData['BeschreibungPlain_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['BeschreibungPlain_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['BeschreibungPlain_'.$translation_language] : null;
															$updData['searchable_text_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['searchable_text_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['searchable_text_'.$translation_language] : null;
															$updData['bracketedString_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['bracketedString_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['bracketedString_'.$translation_language] : null;
															$updData['timeString_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['timeString_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['timeString_'.$translation_language] : null;

															if ($translation_method == "Professional Translation") {
																$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
																$db->query($updSavedComparisonSymptomsQuery);
															} else {
																if($symptomRow['Beschreibung_'.$translation_language] == ""){
																	$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungFull_".$translation_language." = NULLIF('".$updData['BeschreibungFull_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
																	$db->query($updSavedComparisonSymptomsQuery);
																}
															}

															$arrayKey++;
														}

														// For quelle master backup table
														$updateQuelleMasterBackupWithLanguageQuery = "UPDATE quelle_import_master_backup SET is_symptoms_available_in_".$translation_language." = 1, translation_method_of_".$translation_language." = NULLIF('".$translation_method."', '') WHERE id = '".$quelleMasterBackupRow['id']."'";
														$db->query($updateQuelleMasterBackupWithLanguageQuery);
													}
												}
											}


											/*// For Single source symptoms in Backup (Saved comparison sources symptom of backup are managed just above)
											$backupSingleSouceSymptomInfo = mysqli_query($db,"SELECT id, Beschreibung_".$translation_language." FROM quelle_import_backup WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND original_quelle_id = '".$add_translation_quelle_id."' AND (original_symptom_id IS NULL OR original_symptom_id = '')");
											$totalBackupSingleSouceSymptoms = mysqli_num_rows($backupSingleSouceSymptomInfo);

											if($totalTempTransSymptom == $totalBackupSingleSouceSymptoms){
												// For quelle master backup table
												$updateQuelleMasterBackupWithLanguageQuery = "UPDATE quelle_import_master_backup SET is_symptoms_available_in_".$translation_language." = 1, translation_method_of_".$translation_language." = NULLIF('".$translation_method."', '') WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND original_quelle_id = '".$add_translation_quelle_id."'";
												$db->query($updateQuelleMasterBackupWithLanguageQuery);

												$arrayKey = 0;
												while($symptomRow = mysqli_fetch_array($backupSingleSouceSymptomInfo)){
													$updData = array();
													$updData['Beschreibung_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['Beschreibung_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['Beschreibung_'.$translation_language] : null;
													$updData['BeschreibungOriginal_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['BeschreibungOriginal_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['BeschreibungOriginal_'.$translation_language] : null;
													$updData['BeschreibungPlain_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['BeschreibungPlain_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['BeschreibungPlain_'.$translation_language] : null;
													$updData['searchable_text_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['searchable_text_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['searchable_text_'.$translation_language] : null;
													$updData['bracketedString_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['bracketedString_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['bracketedString_'.$translation_language] : null;
													$updData['timeString_'.$translation_language] = ($tempTransSymptomMasterArray[$arrayKey]['timeString_'.$translation_language] != "") ? $tempTransSymptomMasterArray[$arrayKey]['timeString_'.$translation_language] : null;

													if ($translation_method == "Professional Translation") {
														$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
														$db->query($updSavedComparisonSymptomsQuery);
													} else {
														if($symptomRow['Beschreibung_'.$translation_language] == ""){
															$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$translation_language." = NULLIF('".$updData['Beschreibung_'.$translation_language]."', ''), BeschreibungOriginal_".$translation_language." = NULLIF('".$updData['BeschreibungOriginal_'.$translation_language]."', ''), BeschreibungPlain_".$translation_language." = NULLIF('".$updData['BeschreibungPlain_'.$translation_language]."', ''), searchable_text_".$translation_language." = NULLIF('".$updData['searchable_text_'.$translation_language]."', ''), bracketedString_".$translation_language." = NULLIF('".$updData['bracketedString_'.$translation_language]."', ''), timeString_".$translation_language." = NULLIF('".$updData['timeString_'.$translation_language]."', '') WHERE id = '".$symptomRow['id']."'";
															$db->query($updSavedComparisonSymptomsQuery);
														}
													}

													$arrayKey++;
												}
											} */

											// UPDATE QUELLE MASTER TABLE
											$updateQuelleMasterWithLanguageQuery = "UPDATE quelle_import_master SET is_symptoms_available_in_".$translation_language." = 1, translation_method_of_".$translation_language." = NULLIF('".$translation_method."', '') WHERE id = '".$add_translation_master_id."'";
											$db->query($updateQuelleMasterWithLanguageQuery);

											// DELETING THE TEMP TRANSLATION APPROVAL DATA OF TEMP TABLE
											$deleteTempTransQuelleQuery="DELETE FROM temp_translation_quelle WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
											$db->query($deleteTempTransQuelleQuery);

											$deleteTempTransSymptomQuery="DELETE FROM temp_translation_symptoms WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
											$db->query($deleteTempTransSymptomQuery);
											
											$status = "success";
											$message = "success";
										} else {
											$status = 'error';
							    			$message = 'No data found.';
										}
									}
								} else {
									$status = 'error';
						    		$message = 'Required data not found. Please reload the page and try again!';
								}

							} else {
								$status = 'error';
			    				$message = 'Translation already added. Please reload the page and check!';
							}
							break;
						}
					case 'delete':
						{
							// DELETING THE TEMP TRANSLATION APPROVAL DATA OF TEMP TABLE
							$deleteTempTransQuelleQuery="DELETE FROM temp_translation_quelle WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
							$db->query($deleteTempTransQuelleQuery);

							$deleteTempTransSymptomQuery="DELETE FROM temp_translation_symptoms WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
							$db->query($deleteTempTransSymptomQuery);

							$status = "success";
							$message = "success";
							break;
						}

					default:
						break;
				}
			} catch (Exception $e) {
			    // echo '<p>', $e->getMessage(), '</p>';
			    $status = 'error';
			    $message = 'Exception error';
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