<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Saving a particular comparison
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {

		echo json_encode( array( 'result_data' => $_POST) ); 
		exit;


		$saved_comparison_quelle_id = (isset($_POST['saved_comparison_quelle_id']) AND $_POST['saved_comparison_quelle_id'] != "") ? trim($_POST['saved_comparison_quelle_id']) : null;
		if($saved_comparison_quelle_id != "")
		{
			$isQuelleAllowedToPerform = isQuelleAllowedToPerformThisAction($saved_comparison_quelle_id);
			if($isQuelleAllowedToPerform === false)
			{
				echo json_encode( array( 'status' => 'invalid', 'result_data' => $resultData, 'message' => $message) ); 
				exit;
			}
		}
		
		if(isset($_POST['form']) AND !empty($_POST['form'])){
			parse_str( $_POST['form'], $formData );

			$arzneiId = (isset($formData['arznei_id']) AND $formData['arznei_id'] != "") ? trim($formData['arznei_id']) : null;
			$initialSource = (isset($formData['initial_source']) AND $formData['initial_source'] != "") ? trim($formData['initial_source']) : null;
			$comparingSources = array();
			$comparingSourcesPre = (isset($formData['comparing_sources']) AND $formData['comparing_sources'] != "") ? $formData['comparing_sources'] : array();
			if(!empty($comparingSourcesPre)){
				foreach ($comparingSourcesPre as $key => $val) {
					$sourcesInArr = explode(',', $val);
					foreach ($sourcesInArr as $srcKey => $srcVal) {
						array_push($comparingSources, $srcVal);
					}
				}
			}

			// Soruce existance checking START
			$isSymptomsAvailable = 1;
			if($initialSource != ""){
				$checkQuelleQuery1 = mysqli_query($db, "SELECT quelle_id FROM quelle_import_master WHERE quelle_id = '".$initialSource."'");
				if(mysqli_num_rows($checkQuelleQuery1) == 0){
					$isSymptomsAvailable = 0;
				}
			}
			if(!empty($comparingSources) AND $isSymptomsAvailable != 0){
				foreach ($comparingSources as $srcExiKey => $srcExiVal) {
					$checkQuelleQuery2 = mysqli_query($db, "SELECT quelle_id FROM quelle_import_master WHERE quelle_id = '".$srcExiVal."'");
					if(mysqli_num_rows($checkQuelleQuery2) == 0){
						$isSymptomsAvailable = 0;
						break;
					}
				}
			}
			if($isSymptomsAvailable == 0){
				echo json_encode( array( 'status' => 'invalid', 'result_data' => $resultData, 'message' => $message) );
				exit;
			}
			// Soruce existance checking END
			
			$similarityRate = (isset($formData['similarity_rate']) AND $formData['similarity_rate'] != "") ? trim($formData['similarity_rate']) : null;
			$comparisonOption = (isset($formData['comparison_option']) AND $formData['comparison_option'] != "") ? trim($formData['comparison_option']) : null;
			$comparisonName = (isset($formData['comparison_name']) AND $formData['comparison_name'] != "") ? trim($formData['comparison_name']) : null;
			// $is_save_as_new = (isset($_POST['is_save_as_new']) AND $_POST['is_save_as_new'] != "") ? trim($_POST['is_save_as_new']) : null;
			$is_save_on_existing = (isset($_POST['is_save_on_existing']) AND $_POST['is_save_on_existing'] != "") ? trim($_POST['is_save_on_existing']) : null;
			$comparisonLanguage = (isset($formData['comparison_language']) AND $formData['comparison_language'] != "") ? trim($formData['comparison_language']) : null;

			$initial_source_id = $initialSource;
			$comparing_source_ids = "";
			if(!empty($comparingSources))
				$comparing_source_ids = implode(",", $comparingSources);
			$comparison_name = mysqli_real_escape_string($db, $comparisonName);
			$similarity_rate = mysqli_real_escape_string($db, $similarityRate);
			$comparison_option = mysqli_real_escape_string($db, $comparisonOption);	
			$comparison_language = mysqli_real_escape_string($db, $comparisonLanguage);	

			$comparedSourcesIdsArr = explode(',', $initial_source_id);
			if(!empty($comparingSources)){
				foreach ($comparingSources as $comKey => $comVal) {
					array_push($comparedSourcesIdsArr, $comVal);
				}
			}


			$totalSymptomQuery = mysqli_query($db,"SELECT SC.id, SC.quelle_id, SC.comparison_name, SC.initial_source_id, SC.comparing_source_ids, QIM.id AS master_id FROM saved_comparisons AS SC LEFT JOIN quelle_import_master AS QIM ON SC.quelle_id = QIM.quelle_id WHERE SC.initial_source_id = '".$initial_source_id."' OR SC.comparing_source_ids = '".$initial_source_id."'");
			if(mysqli_num_rows($totalSymptomQuery) > 0){
				$isExist = 0; 
				$savedComparisonName = ""; 
				$savedComparisonId = ""; 
				$savedDataArray = array();
				while($row = mysqli_fetch_array($totalSymptomQuery)){
					if($row['comparing_source_ids'] == $initial_source_id)
					{
						if($row['initial_source_id'] == $comparing_source_ids){
							$isExist = 1;
							$data = array();
							$data['saved_comparison_name'] = $row['comparison_name'];
							$data['saved_comparison_id'] = $row['id'];
							$data['saved_comparison_quelle_id'] = $row['quelle_id'];
							$data['saved_comparison_initial_source_id'] = $row['initial_source_id'];
							$data['saved_comparison_comparing_source_ids'] = $row['comparing_source_ids'];
							$data['saved_comparison_master_id'] = $row['master_id'];
							$savedDataArray[] = $data;
						}
					}
					else
					{
						$comparingSourcesIdArr = ($row['comparing_source_ids'] != null) ? explode(',', $row['comparing_source_ids']) : null;
						$form_comparing_source_ids = ($comparing_source_ids != null) ? explode(',', $comparing_source_ids) : null;
						if(!empty($comparingSourcesIdArr) AND !empty($form_comparing_source_ids)){
							if(count($form_comparing_source_ids) == count($comparingSourcesIdArr))
							{
								$difference = array_diff($form_comparing_source_ids, $comparingSourcesIdArr);
								if(empty($difference)){
									$isExist = 1;
									$data = array();
									$data['saved_comparison_name'] = $row['comparison_name'];
									$data['saved_comparison_id'] = $row['id'];
									$data['saved_comparison_quelle_id'] = $row['quelle_id'];
									$data['saved_comparison_initial_source_id'] = $row['initial_source_id'];
									$data['saved_comparison_comparing_source_ids'] = $row['comparing_source_ids'];
									$data['saved_comparison_master_id'] = $row['master_id'];
									$savedDataArray[] = $data;
								}
							}
						}
					}
				}
				if($isExist == 1)
				{
					$alreadyComparedSourcesArr = array();
					$sourceIdsToSend = array();
					$savedComparisonComparingSourceIds = (isset($savedDataArray[0]['saved_comparison_comparing_source_ids']) AND $savedDataArray[0]['saved_comparison_comparing_source_ids'] != "") ? $savedDataArray[0]['saved_comparison_comparing_source_ids'] : null;
					$savedComparisonInitialSourceId = (isset($savedDataArray[0]['saved_comparison_initial_source_id']) AND $savedDataArray[0]['saved_comparison_initial_source_id'] != "") ? $savedDataArray[0]['saved_comparison_initial_source_id'] : null;
					$savedComparisonQuelleId = (isset($savedDataArray[0]['saved_comparison_quelle_id']) AND $savedDataArray[0]['saved_comparison_quelle_id'] != "") ? $savedDataArray[0]['saved_comparison_quelle_id'] : null;
					$savedComparisonId = (isset($savedDataArray[0]['saved_comparison_id']) AND $savedDataArray[0]['saved_comparison_id'] != "") ? $savedDataArray[0]['saved_comparison_id'] : null;
					$savedComparisonName = (isset($savedDataArray[0]['saved_comparison_name']) AND $savedDataArray[0]['saved_comparison_name'] != "") ? $savedDataArray[0]['saved_comparison_name'] : null;

					// here we are resaving a comparison so we are using the exising comparison name for this quelle
					$formData['comparison_name'] = $savedComparisonName;

					$comparingSourceIdsArr = explode(',', $savedComparisonComparingSourceIds);
					foreach ($comparingSourceIdsArr as $cSourceKey => $cSourceVal) {
						array_push($alreadyComparedSourcesArr, $cSourceVal);
						array_push($sourceIdsToSend, $cSourceVal);
					}
					if($savedComparisonInitialSourceId != "" AND  !in_array($savedComparisonInitialSourceId, $alreadyComparedSourcesArr)){
						array_push($alreadyComparedSourcesArr, $savedComparisonInitialSourceId);
						array_push($sourceIdsToSend, $savedComparisonInitialSourceId);
					}

					if(!empty($sourceIdsToSend)){
						$returnedIds = getAllComparedSourceIds($sourceIdsToSend);
						if(!empty($returnedIds)){
							foreach ($returnedIds as $IdVal) {
								if(!in_array($IdVal, $alreadyComparedSourcesArr))
									array_push($alreadyComparedSourcesArr, $IdVal);
							}
						}	
					}

					$alreadyComparedSourcesIds = (!empty($alreadyComparedSourcesArr)) ? rtrim(implode(',', $alreadyComparedSourcesArr), ',') : null;

					if($is_save_on_existing == 1)
					{
						$savedComparisonQuelleIdArr = explode(',', $savedComparisonQuelleId);
						$removeResult = removeExistingQuelleData($savedComparisonQuelleIdArr, $arzneiId);
						if($removeResult['status'] === true)
						{
							$masterQuery="INSERT INTO saved_comparisons (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$savedComparisonName."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
							$db->query($masterQuery);
							$saved_comparison_master_id = mysqli_insert_id($db);
							// ADD IN THE BACKUP
							$backupMasterQuery="INSERT INTO saved_comparisons_backup (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$savedComparisonName."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
							$db->query($backupMasterQuery);
							$bckup_saved_comparison_master_id = mysqli_insert_id($db);

							if($saved_comparison_master_id != "" AND $bckup_saved_comparison_master_id != "")
							{
								$result = addQuelleDetailsInSaveOperation($saved_comparison_master_id, $bckup_saved_comparison_master_id, $formData, $savedComparisonQuelleId);
								if($result['status'] === true)
								{
									if(!empty($comparedSourcesIdsArr)){
										foreach ($comparedSourcesIdsArr as $allSourcesKey => $allSourcesVal) {
											$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$allSourcesVal;
			            					$db->query($updQuelleQuery);
										}
									}

									// Adding swapped symptoms in their current status separately for this backup set
									$swappedSymptomsQuery = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE comparison_initial_source_id = '".$initial_source_id."' AND comparison_comparing_source_ids = '".$comparing_source_ids."' AND arznei_id = '".$arzneiId."'");
									if(mysqli_num_rows($swappedSymptomsQuery) > 0){
										while($swappedSymptomData = mysqli_fetch_array($swappedSymptomsQuery)){
											$symptomResult = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE id = '".$swappedSymptomData['symptom_id']."'");
											if(mysqli_num_rows($symptomResult) > 0){
												$symptomData = mysqli_fetch_assoc($symptomResult);

												$symptomData['original_symptom_id'] = ($symptomData['id'] != "") ? mysqli_real_escape_string($db, $symptomData['id']) : null;
												$symptomData['master_id'] = ($symptomData['master_id'] != "") ? mysqli_real_escape_string($db, $symptomData['master_id']) : null;
												$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
												$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
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
												$mainSymptomInsertQuery="INSERT INTO backup_sets_swapped_symptoms (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
										
									            $db->query($mainSymptomInsertQuery);
									            $mainSymtomId = $db->insert_id;

									            /* Insert Symptom_pruefer relation START */
									            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM symptom_pruefer where symptom_id = '".$symptomData['id']."'");
												if($symptomPrueferResult->num_rows > 0){
													while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
														$mainSymptomPrueferInsertQuery = "INSERT INTO backup_sets_swapped_symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
										            	$db->query($mainSymptomPrueferInsertQuery);
													}
												}
												/* Insert Symptom_pruefer relation END */

												/* Insert symptom_reference relation START */
									            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM symptom_reference where symptom_id = '".$symptomData['id']."'");
												if($symptomReferenceResult->num_rows > 0){
													while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
														$mainSymptomReferenceInsertQuery = "INSERT INTO backup_sets_swapped_symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
										            	$db->query($mainSymptomReferenceInsertQuery);
													}
												}
												/* Insert symptom_reference relation END */
											}
										}
									}
									//


									$status = 'success';
									$resultData['saved_comparison_id'] = $saved_comparison_master_id;
									$message = "Saved successfully.";
								}
								else
								{
									$savedComDeleteQuery="DELETE FROM saved_comparisons WHERE id = ".$saved_comparison_master_id;
			            			$db->query($savedComDeleteQuery);

			            			$backupSavedComDeleteQuery="DELETE FROM saved_comparisons_backup WHERE id = ".$bckup_saved_comparison_master_id;
			            			$db->query($backupSavedComDeleteQuery);

			            			$FVSymptomBackupsDeleteQuery="DELETE FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = ".$bckup_saved_comparison_master_id;
									$db->query($FVSymptomBackupsDeleteQuery);

									$status = 'error';
									$message = $result['message'];
								}
							}
							else
							{
								$status = 'error';
								$message = "Operation failed.";
							}
						}
						else
						{
							$status = 'error';
							$message = $removeResult['message'];
						}
					}
					else
					{
						$checkFartherConnectionsQuery = mysqli_query($db,"SELECT id FROM symptom_connections WHERE (initial_source_id = '".$savedComparisonQuelleId."' OR comparing_source_id = '".$savedComparisonQuelleId."') AND (initial_source_id NOT IN (".$alreadyComparedSourcesIds.") AND comparing_source_id NOT IN (".$alreadyComparedSourcesIds."))");

						$isUsedInFartherComparisonQuery = mysqli_query($db,"SELECT SC.id, SC.quelle_id, SC.comparison_name, SC.initial_source_id, SC.comparing_source_ids, QIM.id AS master_id FROM saved_comparisons AS SC LEFT JOIN quelle_import_master AS QIM ON SC.quelle_id = QIM.quelle_id WHERE SC.initial_source_id = '".$savedComparisonQuelleId."' OR FIND_IN_SET('".$savedComparisonQuelleId."', SC.comparing_source_ids)");
						if(mysqli_num_rows($checkFartherConnectionsQuery) > 0 OR mysqli_num_rows($isUsedInFartherComparisonQuery) > 0){
							if(mysqli_num_rows($isUsedInFartherComparisonQuery) > 0){
								while($row = mysqli_fetch_array($isUsedInFartherComparisonQuery)){
									$data = array();
									$data['saved_comparison_name'] = $row['comparison_name'];
									$data['saved_comparison_id'] = $row['id'];
									$data['saved_comparison_quelle_id'] = $row['quelle_id'];
									$data['saved_comparison_initial_source_id'] = $row['initial_source_id'];
									$data['saved_comparison_comparing_source_ids'] = $row['comparing_source_ids'];
									$data['saved_comparison_master_id'] = $row['master_id'];
									$savedDataArray[] = $data;
								}
							}
							
							$status = 'used_further';
							$resultData = $savedDataArray;
			    			$message = "Need confirmation";
							// $message = "This comparison is already saved, your current changes will automatically reflect on already saved comparison <a title='' href='".$baseUrl."comparison.php?scid=".$savedComparisonId."&scto=v'>".$savedComparisonName."</a>.";
						}
						else
						{
							$savedComparisonQuelleIdArr = explode(',', $savedComparisonQuelleId);
							$removeResult = removeExistingQuelleData($savedComparisonQuelleIdArr, $arzneiId);
							if($removeResult['status'] === true)
							{
								$masterQuery="INSERT INTO saved_comparisons (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$savedComparisonName."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
								$db->query($masterQuery);
								$saved_comparison_master_id = mysqli_insert_id($db);
								// ADD IN THE BACKUP
								$backupMasterQuery="INSERT INTO saved_comparisons_backup (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$savedComparisonName."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
								$db->query($backupMasterQuery);
								$bckup_saved_comparison_master_id = mysqli_insert_id($db);

								if($saved_comparison_master_id != "" AND $bckup_saved_comparison_master_id != "")
								{
									$result = addQuelleDetailsInSaveOperation($saved_comparison_master_id, $bckup_saved_comparison_master_id, $formData, $savedComparisonQuelleId);
									if($result['status'] === true)
									{
										if(!empty($comparedSourcesIdsArr)){
											foreach ($comparedSourcesIdsArr as $allSourcesKey => $allSourcesVal) {
												$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$allSourcesVal;
				            					$db->query($updQuelleQuery);
											}
										}

										// Adding swapped symptoms in their current status separately for this backup set
										$swappedSymptomsQuery = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE comparison_initial_source_id = '".$initial_source_id."' AND comparison_comparing_source_ids = '".$comparing_source_ids."' AND arznei_id = '".$arzneiId."'");
										if(mysqli_num_rows($swappedSymptomsQuery) > 0){
											while($swappedSymptomData = mysqli_fetch_array($swappedSymptomsQuery)){
												$symptomResult = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE id = '".$swappedSymptomData['symptom_id']."'");
												if(mysqli_num_rows($symptomResult) > 0){
													$symptomData = mysqli_fetch_assoc($symptomResult);

													$symptomData['original_symptom_id'] = ($symptomData['id'] != "") ? mysqli_real_escape_string($db, $symptomData['id']) : null;
													$symptomData['master_id'] = ($symptomData['master_id'] != "") ? mysqli_real_escape_string($db, $symptomData['master_id']) : null;
													$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
													$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
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
													$mainSymptomInsertQuery="INSERT INTO backup_sets_swapped_symptoms (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
											
										            $db->query($mainSymptomInsertQuery);
										            $mainSymtomId = $db->insert_id;

										            /* Insert Symptom_pruefer relation START */
										            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM symptom_pruefer where symptom_id = '".$symptomData['id']."'");
													if($symptomPrueferResult->num_rows > 0){
														while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
															$mainSymptomPrueferInsertQuery = "INSERT INTO backup_sets_swapped_symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
											            	$db->query($mainSymptomPrueferInsertQuery);
														}
													}
													/* Insert Symptom_pruefer relation END */

													/* Insert symptom_reference relation START */
										            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM symptom_reference where symptom_id = '".$symptomData['id']."'");
													if($symptomReferenceResult->num_rows > 0){
														while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
															$mainSymptomReferenceInsertQuery = "INSERT INTO backup_sets_swapped_symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
											            	$db->query($mainSymptomReferenceInsertQuery);
														}
													}
													/* Insert symptom_reference relation END */
												}
											}
										}
										// 
										

										$status = 'success';
										$resultData['saved_comparison_id'] = $saved_comparison_master_id;
										$message = "Saved successfully.";
									}
									else
									{
										$savedComDeleteQuery="DELETE FROM saved_comparisons WHERE id = ".$saved_comparison_master_id;
				            			$db->query($savedComDeleteQuery);

				            			$backupSavedComDeleteQuery="DELETE FROM saved_comparisons_backup WHERE id = ".$bckup_saved_comparison_master_id;
			            				$db->query($backupSavedComDeleteQuery);

			            				$FVSymptomBackupsDeleteQuery="DELETE FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = ".$bckup_saved_comparison_master_id;
										$db->query($FVSymptomBackupsDeleteQuery);

										$status = 'error';
										$message = $result['message'];
									}
								}
								else
								{
									$status = 'error';
									$message = "Operation failed.";
								}
							}
							else
							{
								$status = 'error';
								$message = $removeResult['message'];
							}
						}
					}
					
				}
				else
				{
					$masterQuery="INSERT INTO saved_comparisons (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$comparison_name."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
					$db->query($masterQuery);
					$saved_comparison_master_id = mysqli_insert_id($db);
					// ADD IN THE BACKUP
					$backupMasterQuery="INSERT INTO saved_comparisons_backup (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$comparison_name."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
					$db->query($backupMasterQuery);
					$bckup_saved_comparison_master_id = mysqli_insert_id($db);

					if($saved_comparison_master_id != "" AND $bckup_saved_comparison_master_id != "")
					{
						$result = addQuelleDetailsInSaveOperation($saved_comparison_master_id, $bckup_saved_comparison_master_id, $formData);
						if($result['status'] === true)
						{
							if(!empty($comparedSourcesIdsArr)){
								foreach ($comparedSourcesIdsArr as $allSourcesKey => $allSourcesVal) {
									$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$allSourcesVal;
	            					$db->query($updQuelleQuery);
								}
							}

							// Adding swapped symptoms in their current status separately for this backup set
							$swappedSymptomsQuery = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE comparison_initial_source_id = '".$initial_source_id."' AND comparison_comparing_source_ids = '".$comparing_source_ids."' AND arznei_id = '".$arzneiId."'");
							if(mysqli_num_rows($swappedSymptomsQuery) > 0){
								while($swappedSymptomData = mysqli_fetch_array($swappedSymptomsQuery)){
									$symptomResult = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE id = '".$swappedSymptomData['symptom_id']."'");
									if(mysqli_num_rows($symptomResult) > 0){
										$symptomData = mysqli_fetch_assoc($symptomResult);

										$symptomData['original_symptom_id'] = ($symptomData['id'] != "") ? mysqli_real_escape_string($db, $symptomData['id']) : null;
										$symptomData['master_id'] = ($symptomData['master_id'] != "") ? mysqli_real_escape_string($db, $symptomData['master_id']) : null;
										$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
										$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
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
										$mainSymptomInsertQuery="INSERT INTO backup_sets_swapped_symptoms (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
								
							            $db->query($mainSymptomInsertQuery);
							            $mainSymtomId = $db->insert_id;

							            /* Insert Symptom_pruefer relation START */
							            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM symptom_pruefer where symptom_id = '".$symptomData['id']."'");
										if($symptomPrueferResult->num_rows > 0){
											while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
												$mainSymptomPrueferInsertQuery = "INSERT INTO backup_sets_swapped_symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
								            	$db->query($mainSymptomPrueferInsertQuery);
											}
										}
										/* Insert Symptom_pruefer relation END */

										/* Insert symptom_reference relation START */
							            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM symptom_reference where symptom_id = '".$symptomData['id']."'");
										if($symptomReferenceResult->num_rows > 0){
											while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
												$mainSymptomReferenceInsertQuery = "INSERT INTO backup_sets_swapped_symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
								            	$db->query($mainSymptomReferenceInsertQuery);
											}
										}
										/* Insert symptom_reference relation END */
									}
								}
							}
							// 
							

							$status = 'success';
							$resultData['saved_comparison_id'] = $saved_comparison_master_id;
							$message = "Saved successfully.";
						}
						else
						{
							$savedComDeleteQuery="DELETE FROM saved_comparisons WHERE id = ".$saved_comparison_master_id;
	            			$db->query($savedComDeleteQuery);
	            			$backupSavedComDeleteQuery="DELETE FROM saved_comparisons_backup WHERE id = ".$bckup_saved_comparison_master_id;
			            	$db->query($backupSavedComDeleteQuery);

			            	$FVSymptomBackupsDeleteQuery="DELETE FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = ".$bckup_saved_comparison_master_id;
							$db->query($FVSymptomBackupsDeleteQuery);

							$status = 'error';
							$message = $result['message'];
						}
					}
					else
					{
						$status = 'error';
						$message = "Operation failed.";
					}
				}
			}
			else
			{
				$masterQuery="INSERT INTO saved_comparisons (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$comparison_name."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
					$db->query($masterQuery);
				$saved_comparison_master_id = mysqli_insert_id($db);
				// ADD IN THE BACKUP
				$backupMasterQuery="INSERT INTO saved_comparisons_backup (arznei_id, initial_source_id, comparing_source_ids, comparison_name, similarity_rate, comparison_option, comparison_language) VALUES (NULLIF('".$arzneiId."', ''), '".$initial_source_id."', NULLIF('".$comparing_source_ids."', ''), NULLIF('".$comparison_name."', ''), NULLIF('".$similarity_rate."', ''), NULLIF('".$comparison_option."', ''), NULLIF('".$comparison_language."', ''))";
				$db->query($backupMasterQuery);
				$bckup_saved_comparison_master_id = mysqli_insert_id($db);

				if($saved_comparison_master_id != "" AND $bckup_saved_comparison_master_id != "")
				{
					$result = addQuelleDetailsInSaveOperation($saved_comparison_master_id, $bckup_saved_comparison_master_id, $formData);
					if($result['status'] === true)
					{
						if(!empty($comparedSourcesIdsArr)){
							foreach ($comparedSourcesIdsArr as $allSourcesKey => $allSourcesVal) {
								$updQuelleQuery="UPDATE quelle SET is_materia_medica = 0 WHERE quelle_id = ".$allSourcesVal;
            					$db->query($updQuelleQuery);
							}
						}

						// Adding swapped symptoms in their current status separately for this backup set
						$swappedSymptomsQuery = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE comparison_initial_source_id = '".$initial_source_id."' AND comparison_comparing_source_ids = '".$comparing_source_ids."' AND arznei_id = '".$arzneiId."'");
						if(mysqli_num_rows($swappedSymptomsQuery) > 0){
							while($swappedSymptomData = mysqli_fetch_array($swappedSymptomsQuery)){
								$symptomResult = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE id = '".$swappedSymptomData['symptom_id']."'");
								if(mysqli_num_rows($symptomResult) > 0){
									$symptomData = mysqli_fetch_assoc($symptomResult);

									$symptomData['original_symptom_id'] = ($symptomData['id'] != "") ? mysqli_real_escape_string($db, $symptomData['id']) : null;
									$symptomData['master_id'] = ($symptomData['master_id'] != "") ? mysqli_real_escape_string($db, $symptomData['master_id']) : null;
									$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
									$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
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
									$mainSymptomInsertQuery="INSERT INTO backup_sets_swapped_symptoms (saved_comparisons_backup_id, original_symptom_id, master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, symptom_edit_comment, is_final_version_available, is_symptom_number_mismatch, is_symptom_appended) VALUES (NULLIF('".$bckup_saved_comparison_master_id."', ''), NULLIF('".$symptomData['original_symptom_id']."', ''), NULLIF('".$symptomData['master_id']."', ''), NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['final_version_de']."', ''), NULLIF('".$symptomData['final_version_en']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['subChapter']."', ''), NULLIF('".$symptomData['subSubChapter']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), NULLIF('".$symptomData['is_final_version_available']."', ''), '".$symptomData['is_symptom_number_mismatch']."', NULLIF('".$symptomData['is_symptom_appended']."', ''))";
							
						            $db->query($mainSymptomInsertQuery);
						            $mainSymtomId = $db->insert_id;

						            /* Insert Symptom_pruefer relation START */
						            $symptomPrueferResult = $db->query("SELECT symptom_id, pruefer_id FROM symptom_pruefer where symptom_id = '".$symptomData['id']."'");
									if($symptomPrueferResult->num_rows > 0){
										while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
											$mainSymptomPrueferInsertQuery = "INSERT INTO backup_sets_swapped_symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
							            	$db->query($mainSymptomPrueferInsertQuery);
										}
									}
									/* Insert Symptom_pruefer relation END */

									/* Insert symptom_reference relation START */
						            $symptomReferenceResult = $db->query("SELECT symptom_id, reference_id FROM symptom_reference where symptom_id = '".$symptomData['id']."'");
									if($symptomReferenceResult->num_rows > 0){
										while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
											$mainSymptomReferenceInsertQuery = "INSERT INTO backup_sets_swapped_symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
							            	$db->query($mainSymptomReferenceInsertQuery);
										}
									}
									/* Insert symptom_reference relation END */
								}
							}
						}
						// 
						

						$status = 'success';
						$resultData['saved_comparison_id'] = $saved_comparison_master_id;
						$message = "Saved successfully.";
					}
					else
					{
						$savedComDeleteQuery="DELETE FROM saved_comparisons WHERE id = ".$saved_comparison_master_id;
            			$db->query($savedComDeleteQuery);
            			$backupSavedComDeleteQuery="DELETE FROM saved_comparisons_backup WHERE id = ".$bckup_saved_comparison_master_id;
			            $db->query($backupSavedComDeleteQuery);

			            $FVSymptomBackupsDeleteQuery="DELETE FROM final_version_symptoms_info_for_backups WHERE saved_comparisons_backup_id = ".$bckup_saved_comparison_master_id;
						$db->query($FVSymptomBackupsDeleteQuery);

						$status = 'error';
						$message = $result['message'];
					}
				}
				else
				{
					$status = 'error';
					$message = "Operation failed.";
				}
			}
			// DELETEING PREVIOUS COMPARISON CONNECTIONS TO AVOIDE HAVING DUPLICATE CONNECTIONS.
			// if($status == "success"){
			// 	$deleteBackConnectionsQuery="DELETE FROM symptom_connections WHERE initial_source_id = ".$initial_source_id;
			// 	$db->query($deleteBackConnectionsQuery);
			// }
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