<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* When a symptom gets disconnected and that symptom appears no where in the comparative parts and it needs to get added in any of the comparing sources
	* Fetching that newly added symptom's information to append on the bottom of the comparison as a particular comparing source's symptom  
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';

	try {
		if(isset($_POST['appendable_materia_medica_symptom_id']) AND $_POST['appendable_materia_medica_symptom_id'] != ""){
			$appendable_materia_medica_symptom_id = (isset($_POST['appendable_materia_medica_symptom_id']) AND $_POST['appendable_materia_medica_symptom_id'] != "") ? $_POST['appendable_materia_medica_symptom_id'] : null;
			$InitialQuelleResult = mysqli_query($db,"SELECT QI.id, QI.original_symptom_id, QI.quelle_code, QI.quelle_id, QI.original_quelle_id, QI.final_version_de, QI.final_version_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Kommentar, QI.Fussnote, QIM.arznei_id, SC.comparison_option FROM quelle_import_test AS QI LEFT JOIN saved_comparisons as SC ON QI.quelle_id = SC.quelle_id LEFT JOIN quelle_import_master AS QIM ON QI.quelle_id = QIM.quelle_id WHERE QI.id = '".$appendable_materia_medica_symptom_id."'");
			if(mysqli_num_rows($InitialQuelleResult) > 0){
				while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
					// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
					if($iniSymRow['is_final_version_available'] != 0){
						$iniSymptomString_de =  $iniSymRow['final_version_de'];
						$iniSymptomString_en =  $iniSymRow['final_version_en'];
					} else {
						if(isset($iniSymRow['comparison_option']) AND $iniSymRow['comparison_option'] == 1)
							$iniSymptomString_de =  $iniSymRow['searchable_text_de'];
							$iniSymptomString_en =  $iniSymRow['searchable_text_en'];
						}else{
							$iniSymptomString_de =  $iniSymRow['BeschreibungOriginal_de'];
							$iniSymptomString_en =  $iniSymRow['BeschreibungOriginal_en'];
						}
					}

					// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
					$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
					$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
					
					// Apply dynamic conversion (this string is used in displying the symptom)
					if($iniSymptomString_de != ""){
						$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
						$iniSymptomString_de = base64_encode($iniSymptomString_de);
					}
					if($iniSymptomString_en != ""){
						$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
						$iniSymptomString_en = base64_encode($iniSymptomString_en);
					}

					$iniHasConnections = 0;
					$isFurtherConnectionsAreSaved = 1;
					$is_paste_disabled = 0;
					$is_ns_paste_disabled = 1;
					$is_connect_disabled = 0;
					$is_ns_connect_disabled = 1;
					$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved FROM symptom_connections WHERE (initial_source_symptom_id = '".$iniSymRow['id']."' OR comparing_source_symptom_id = '".$iniSymRow['id']."') AND (is_connected = 1 OR is_pasted = 1)");
					if(mysqli_num_rows($ceheckConnectionResult) > 0){
						$iniHasConnections = 1;
						while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
							if($checkConRow['is_saved'] == 0){
								$isFurtherConnectionsAreSaved = 0;
								break;
							}
						}
					}

					$initial_saved_version_source_code = "";
					$getQuelleResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle WHERE quelle_id = '".$iniSymRow['quelle_id']."'");
					if(mysqli_num_rows($getQuelleResult) > 0){
						$quelleRow = mysqli_fetch_assoc($getQuelleResult);
						if($quelleRow['quelle_type_id'] == 3)
							$preparedQuelleCode = $quelleRow['code'];
						else{
							if($quelleRow['jahr'] != "" AND $quelleRow['code'] != "")
								$rowQuelleCode = trim(str_replace(trim($quelleRow['jahr']), '', $quelleRow['code']));
							else
								$rowQuelleCode = trim($quelleRow['code']);
							$preparedQuelleCode = trim($rowQuelleCode." ".$quelleRow['jahr']);
						}

						$initial_saved_version_source_code = ($preparedQuelleCode != "") ? $preparedQuelleCode : "";
					}

					// get Origin Jahr/Year
					$originInitialSourceYear = "";
					$originInitialSourceLanguage = "";
					$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$iniSymRow['original_quelle_id']."'");
					if(mysqli_num_rows($originInitialQuelleResult) > 0){
						$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
						$originInitialSourceYear = $originIniQuelleRow['jahr'];
						if($originIniQuelleRow['sprache'] == "deutsch")
							$originInitialSourceLanguage = "de";
						else if($originIniQuelleRow['sprache'] == "englisch") 
							$originInitialSourceLanguage = "en";
					}

					$resultData[] = array(
						// "no_of_match" => 0,
						// "percentage" => 0,
						// "comparison_initial_source_id" => $initialQuelleId,
						"source_arznei_id" => $iniSymRow['arznei_id'],
						"initial_source_id" => $iniSymRow['quelle_id'],
						"initial_original_source_id" => $iniSymRow['original_quelle_id'],
						"initial_source_code" => $iniSymRow['quelle_code'],
						"initial_source_year" => $originInitialSourceYear,
						"initial_source_original_language" => $originInitialSourceLanguage,
						"initial_saved_version_source_code" => $initial_saved_version_source_code,
						"initial_source_symptom_highlighted_de" => $iniSymptomString_de,
						"initial_source_symptom_de" => $iniSymptomString_de,
						"initial_source_symptom_before_conversion_highlighted_de" => $iniSymptomStringBeforeConversion_de,
						"initial_source_symptom_before_conversion_de" => $iniSymptomStringBeforeConversion_de,
						"initial_source_symptom_highlighted_en" => $iniSymptomString_en,
						"initial_source_symptom_en" => $iniSymptomString_en,
						"initial_source_symptom_before_conversion_highlighted_en" => $iniSymptomStringBeforeConversion_en,
						"initial_source_symptom_before_conversion_en" => $iniSymptomStringBeforeConversion_en,
						"initial_source_symptom_id" => $iniSymRow['id'],
						"main_parent_initial_symptom_id" => $iniSymRow['id'],
						"connections_main_parent_symptom_id" => $iniSymRow['id'],
						"initial_source_symptom_comment" => $iniSymRow['Kommentar'],
						"initial_source_symptom_footnote" => $iniSymRow['Fussnote'],
						"comparing_source_id" => "",
						"comparing_source_code" => "",
						"comparing_source_year" => "",
						"comparing_source_original_language" => "",
						"comparing_saved_version_source_code" => "",
						"comparing_source_symptom_highlighted_de" => "",
						"comparing_source_symptom_de" => "",
						"comparing_source_symptom_before_conversion_highlighted_de" => "",
						"comparing_source_symptom_before_conversion_de" => "",
						"comparing_source_symptom_highlighted_en" => "",
						"comparing_source_symptom_en" => "",
						"comparing_source_symptom_before_conversion_highlighted_en" => "",
						"comparing_source_symptom_before_conversion_en" => "",
						"comparing_source_symptom_id" => "",
						"comparing_source_symptom_comment" => "",
						"comparing_source_symptom_footnote" => "",
						// "comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
						// "main_initial_symptom_id" => $iniSymRow['id'],
						"has_connections" => $iniHasConnections,
						"is_final_version_available" => $iniSymRow['is_final_version_available'],
						"is_further_connections_are_saved" => $isFurtherConnectionsAreSaved,
						"should_swap_connect_be_active" => 1,
						"is_pasted" => 0,
						"is_ns_paste" => 0,
						"ns_paste_note" => "",
						"is_initial_source" => 1,
						"active_symptom_type" => "initial",
						// "similarity_rate" => $similarityRate,
						// "comparison_option" => $comparisonOption,
						// "is_unmatched_symptom" => 0,
						"is_paste_disabled" => $is_paste_disabled,
						"is_ns_paste_disabled" => $is_ns_paste_disabled,
						"is_connect_disabled" => $is_connect_disabled,
						"is_ns_connect_disabled" => $is_ns_connect_disabled
					);

					$status = "success";
					$message = "Success";
				}
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