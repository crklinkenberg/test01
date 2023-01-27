<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* On each connect or disconnect operatins here we are generating the updated data(for the applicable symptoms) which will appear on the comparison page in replacement of it's old data.
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';
	$matchedSymptomIdsToSent = "";

	try {
		if(isset($_POST['action']) AND $_POST['action'] != ""){
			switch ($_POST['action']) {
				case 'matched_section':
					{
						$arzneiId = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : null;
						$mainInitialSymptomId = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : null;
						$mainInitialSourceId = (isset($_POST['initial_source_id']) AND !empty($_POST['initial_source_id'])) ? $_POST['initial_source_id'] : array();
						$comparingSources = (isset($_POST['comparing_source_ids']) AND !empty($_POST['comparing_source_ids'])) ? $_POST['comparing_source_ids'] : array();
						$similarityRate = (isset($_POST['similarity_rate']) AND $_POST['similarity_rate'] != "") ? $_POST['similarity_rate'] : null;
						$comparisonOption = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : null;
						$matchedSymptomIds = (isset($_POST['matched_symptom_ids']) AND !empty($_POST['matched_symptom_ids'])) ? $_POST['matched_symptom_ids'] : array();
						$individual_comparison_language = (isset($_POST['individual_comparison_language']) AND $_POST['individual_comparison_language'] != "") ? $_POST['individual_comparison_language'] : null;
						$matchedSymptomIdsArray = explode(',', $matchedSymptomIds);
						$initialSourceArray = array();
						$comparingSourcesArray = array();
						if($mainInitialSymptomId != "" AND $comparingSources != "" AND $arzneiId != ""){

							$InitialQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$mainInitialSourceId."'");
							if(mysqli_num_rows($InitialQuelleResult) > 0){
								$iniRow = mysqli_fetch_assoc($InitialQuelleResult);
								if($iniRow['quelle_type_id'] == 3)
									$preparedQuelleCode = $iniRow['code'];
								else{
									if($iniRow['jahr'] != "" AND $iniRow['code'] != "")
										$rowQuelleCode = trim(str_replace(trim($iniRow['jahr']), '', $iniRow['code']));
									else
										$rowQuelleCode = trim($iniRow['code']);
									$preparedQuelleCode = trim($rowQuelleCode." ".$iniRow['jahr']);
								}

								$initialSourceArray[] = array(
									'quelle_id' => $iniRow['quelle_id'],
									'quelle' => $preparedQuelleCode
								);
							}

							$comparingSourceIdsArr = explode(',', $comparingSources);
							foreach ($comparingSourceIdsArr as $getKey => $getVal) {
								$comparingQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$getVal."'");
								if(mysqli_num_rows($comparingQuelleResult) > 0){
									while($comparingRow = mysqli_fetch_array($comparingQuelleResult)){
										if($comparingRow['quelle_id'] != $mainInitialSourceId){
											if($comparingRow['quelle_type_id'] == 3)
												$preparedQuelleCodeForCom = $comparingRow['code'];
											else{
												if($comparingRow['jahr'] != "" AND $comparingRow['code'] != "")
													$rowQuelleCodeForCom = trim(str_replace(trim($comparingRow['jahr']), '', $comparingRow['code']));
												else
													$rowQuelleCodeForCom = trim($comparingRow['code']);
												$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingRow['jahr']);
											}

											$comparingSourcesArray[] = array(
												'quelle_id' => $comparingRow['quelle_id'],
												'quelle' => $preparedQuelleCodeForCom,
												'year' => trim($comparingRow['jahr'])
											);
										}
									}
								}
							}

							if(!empty($comparingSourcesArray)){
								$quelleYear = array();
								foreach ($comparingSourcesArray as $key => $row)
								{
								    $quelleYear[$key] = $row['year'];
								}
								array_multisort($quelleYear, SORT_ASC, $comparingSourcesArray);
							}

							$initialQuelleId = (isset($initialSourceArray[0]['quelle_id']) AND $initialSourceArray[0]['quelle_id'] != "") ? $initialSourceArray[0]['quelle_id'] : null;
							$initialQuelle = (isset($initialSourceArray[0]['quelle']) AND $initialSourceArray[0]['quelle'] != "") ? $initialSourceArray[0]['quelle'] : null;

							$allComparedSourcers = array();
							$comparedSourcersOfInitialSource = array();
							$queryConditionForComparative = '';
							$queryCondition = '';
							$workingSourceIdsArr = array();
							$workingSourceIdsArr = $comparingSourceIdsArr;
							if($initialQuelleId != ""){
								array_push($workingSourceIdsArr, $initialQuelleId);
							}

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
							$initialQuelleIdInArr = explode(',', $initialQuelleId);
							if(!empty($initialQuelleIdInArr)){
								$returnedIds = getAllComparedSourceIds($initialQuelleIdInArr);
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

							$conditionIds = (!empty($workingSourceIdsArr)) ? rtrim(implode(',', $workingSourceIdsArr), ',') : null;
							$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;
							if($conditionIds != "")
								$queryCondition .= " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
							if($conditionIdsForComparative != "")
								$queryConditionForComparative .= " AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative."))";

							if($arzneiId != ""){
								$queryCondition .= " AND source_arznei_id = '".$arzneiId."'"; 
								$queryConditionForComparative .= " AND source_arznei_id = '".$arzneiId."'"; 
							}

							$compareResultArray = array();
							$InitialQuelleResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test WHERE quelle_import_test.id = '".$mainInitialSymptomId."' AND quelle_import_test.quelle_id = '".$initialQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1");
							if(mysqli_num_rows($InitialQuelleResult) > 0){
								while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($iniSymRow['is_final_version_available'] != 0){
										$iniSymptomString_de =  ($iniSymRow['final_version_de'] !="") ? $iniSymRow['final_version_de'] : "";
										$iniSymptomString_en = ($iniSymRow['final_version_en'] !="") ? $iniSymRow['final_version_en'] : "";
									}else{
										if($comparisonOption == 1){
											$iniSymptomString_de =  ($iniSymRow['searchable_text_de'] !="") ? $iniSymRow['searchable_text_de'] : "";
											$iniSymptomString_en =  ($iniSymRow['searchable_text_en'] !="") ? $iniSymRow['searchable_text_en'] : "";
										}else{
											$iniSymptomString_de =  ($iniSymRow['BeschreibungFull_de'] !="") ? $iniSymRow['BeschreibungFull_de'] : "";
											$iniSymptomString_en =  ($iniSymRow['BeschreibungFull_en'] !="") ? $iniSymRow['BeschreibungFull_en'] : "";
										}
									}

									// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
									$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
									$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

									// Apply dynamic conversion
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
									$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved FROM symptom_connections WHERE (initial_source_symptom_id = '".$iniSymRow['id']."' OR comparing_source_symptom_id = '".$iniSymRow['id']."') AND (is_connected = 1 OR is_pasted = 1)".$queryCondition);
									if(mysqli_num_rows($ceheckConnectionResult) > 0){
										$iniHasConnections = 1;
										while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
											if($checkConRow['is_saved'] == 0){
												$isFurtherConnectionsAreSaved = 0;
												break;
											}
										}
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

									$compareResultArray[] = array(
										"no_of_match" => 0,
										"percentage" => 0,
										"comparison_initial_source_id" => ($initialQuelleId != "") ? $initialQuelleId : "",
										"source_arznei_id" => ($arzneiId != "") ? $arzneiId : "",
										"initial_source_id" => ($initialQuelleId != "") ? $initialQuelleId : "",
										"initial_original_source_id" => ($iniSymRow['original_quelle_id'] != "") ? $iniSymRow['original_quelle_id'] : "",
										"initial_source_code" => ($iniSymRow['quelle_code'] != "") ? $iniSymRow['quelle_code'] : "",
										"initial_source_year" => ($originInitialSourceYear != "") ? $originInitialSourceYear : "",
										"initial_source_original_language" => ($originInitialSourceLanguage != "") ? $originInitialSourceLanguage : "",
										"initial_saved_version_source_code" => ($initialQuelle != "") ? $initialQuelle : "",
										"initial_source_symptom_highlighted_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
										"initial_source_symptom_highlighted_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
										"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
										"initial_source_symptom_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
										"initial_source_symptom_before_conversion_highlighted_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
										"initial_source_symptom_before_conversion_highlighted_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
										"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
										"initial_source_symptom_before_conversion_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
										"initial_source_symptom_id" => ($iniSymRow['id'] != "") ? $iniSymRow['id'] : "",
										"main_parent_initial_symptom_id" => ($iniSymRow['id'] != "") ? $iniSymRow['id'] : "",
										"connections_main_parent_symptom_id" => ($iniSymRow['id'] != "") ? $iniSymRow['id'] : "",
										"initial_source_symptom_comment" => ($iniSymRow['Kommentar'] != "") ? $iniSymRow['Kommentar'] : "",
										"initial_source_symptom_footnote" => ($iniSymRow['Fussnote'] != "") ? $iniSymRow['Fussnote'] : "",
										"comparing_source_id" => "",
										"comparing_original_source_id" => "",
										"comparing_source_code" => "",
										"comparing_source_year" => "",
										"comparing_source_original_language" => "",
										"comparing_saved_version_source_code" => "",
										"comparing_source_symptom_highlighted_de" => "",
										"comparing_source_symptom_highlighted_en" => "",
										"comparing_source_symptom_de" => "",
										"comparing_source_symptom_en" => "",
										"comparing_source_symptom_before_conversion_highlighted_de" => "",
										"comparing_source_symptom_before_conversion_highlighted_en" => "",
										"comparing_source_symptom_before_conversion_de" => "",
										"comparing_source_symptom_before_conversion_en" => "",
										"comparing_source_symptom_id" => "",
										"comparing_source_symptom_comment" => "",
										"comparing_source_symptom_footnote" => "",
										"comparison_language" => ($individual_comparison_language != "") ? $individual_comparison_language : "",
										// "main_initial_symptom_id" => $iniSymRow['id'],
										"has_connections" => ($iniHasConnections !="") ? $iniHasConnections : "",
										"is_final_version_available" => ($iniSymRow['is_final_version_available'] !="") ? $iniSymRow['is_final_version_available'] : "",
										"is_further_connections_are_saved" => ($isFurtherConnectionsAreSaved !="") ? $isFurtherConnectionsAreSaved : "",
										"should_swap_connect_be_active" => 1,
										"is_pasted" => 0,
										"is_ns_paste" => 0,
										"ns_paste_note" => "",
										"is_initial_source" => 1,
										"active_symptom_type" => "initial",
										"similarity_rate" => ($similarityRate !="") ? $similarityRate : "",
										"comparison_option" => ($comparisonOption !="") ? $comparisonOption : "",
										"is_unmatched_symptom" => 0,
										"is_paste_disabled" => ($is_paste_disabled !="") ? $is_paste_disabled : "",
										"is_ns_paste_disabled" => ($is_ns_paste_disabled !="") ? $is_ns_paste_disabled : "",
										"is_connect_disabled" => ($is_connect_disabled !="") ? $is_connect_disabled : "",
										"is_ns_connect_disabled" => ($is_ns_connect_disabled !="") ? $is_ns_connect_disabled : ""
									);

									if(!empty($comparingSourcesArray)){
										$matchedSymptomArray = array();
										foreach($comparingSourcesArray as $comSrcKey => $comSrcVal){
											$comparingQuelleId = $comparingSourcesArray[$comSrcKey]['quelle_id'];
											$comparingQuelle = $comparingSourcesArray[$comSrcKey]['quelle'];
											$quelleComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test WHERE quelle_import_test.quelle_id = '".$comparingQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1");
											while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
												// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
												if($quelleComparingSymptomRow['is_final_version_available'] != 0){
													$compSymptomString_de =  ($quelleComparingSymptomRow['final_version_de'] !="") ? $quelleComparingSymptomRow['final_version_de'] : "";
													$compSymptomString_en =  ($quelleComparingSymptomRow['final_version_en'] !="") ? $quelleComparingSymptomRow['final_version_en'] : "";
												}else{
													if($comparisonOption == 1){
														$compSymptomString_de =  ($quelleComparingSymptomRow['searchable_text_de'] !="") ? $quelleComparingSymptomRow['searchable_text_de'] : "";
														$compSymptomString_en =  ($quelleComparingSymptomRow['searchable_text_en'] !="") ? $quelleComparingSymptomRow['searchable_text_en'] : "";
													}else{
														$compSymptomString_de =  ($quelleComparingSymptomRow['BeschreibungFull_de'] !="") ? $quelleComparingSymptomRow['BeschreibungFull_de'] : "";
														$compSymptomString_en =  ($quelleComparingSymptomRow['BeschreibungFull_en'] !="") ? $quelleComparingSymptomRow['BeschreibungFull_en'] : "";
													}
												}

												// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
												$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
												$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

												// Apply dynamic conversion
												if($compSymptomString_de != ""){
													$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
													$compSymptomString_de = base64_encode($compSymptomString_de);	
												}
												if($compSymptomString_en != ""){
													$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
													$compSymptomString_en = base64_encode($compSymptomString_en);	
												}

												$connectedSymptomResult = mysqli_query($db, "SELECT id FROM symptom_connections WHERE ((initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND (initial_source_symptom_id = '".$iniSymRow['id']."' OR comparing_source_symptom_id = '".$iniSymRow['id']."')) AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative);
												if(mysqli_num_rows($connectedSymptomResult) == 0){

													if($individual_comparison_language == "en"){
														// English
														$resultArray = comareSymptom2($iniSymptomString_en, $compSymptomString_en, $iniSymptomStringBeforeConversion_en, $compSymptomStringBeforeConversion_en);
														$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
														$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
														$initial_source_symptom_highlighted_en = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : "";
														// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
														$comparing_source_symptom_highlighted_en = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : "";
														// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
														$initial_source_symptom_before_conversion_highlighted_en = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : "";
														$comparing_source_symptom_before_conversion_highlighted_en = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : "";	

														// German
														$initial_source_symptom_highlighted_de = (isset($iniSymptomString_de)) ? $iniSymptomString_de : "";
														$comparing_source_symptom_highlighted_de = (isset($compSymptomString_de)) ? $compSymptomString_de : "";
														$initial_source_symptom_before_conversion_highlighted_de = (isset($iniSymptomString_de)) ? $iniSymptomString_de : "";
														$comparing_source_symptom_before_conversion_highlighted_de = (isset($compSymptomString_de)) ? $compSymptomString_de : "";
													} else {
														// German
														$resultArray = comareSymptom2($iniSymptomString_de, $compSymptomString_de, $iniSymptomStringBeforeConversion_de, $compSymptomStringBeforeConversion_de);
														$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
														$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
														$initial_source_symptom_highlighted_de = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : "";
														// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
														$comparing_source_symptom_highlighted_de = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : "";
														// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
														$initial_source_symptom_before_conversion_highlighted_de = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : "";
														$comparing_source_symptom_before_conversion_highlighted_de = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : "";

														// English
														$initial_source_symptom_highlighted_en = (isset($iniSymptomString_en)) ? $iniSymptomString_en : "";
														$comparing_source_symptom_highlighted_en = (isset($compSymptomString_en)) ? $compSymptomString_en : "";
														$initial_source_symptom_before_conversion_highlighted_en = (isset($iniSymptomString_en)) ? $iniSymptomString_en : "";
														$comparing_source_symptom_before_conversion_highlighted_en = (isset($compSymptomString_en)) ? $compSymptomString_en : "";
													}
													
													if($percentage >= $similarityRate){

														if(!in_array($quelleComparingSymptomRow['id'], $matchedSymptomIdsArray))
															array_push($matchedSymptomIdsArray, $quelleComparingSymptomRow['id']);

														$comHasConnections = 0;
														$isFurtherConnectionsAreSaved = 1;
														$is_paste_disabled = 0;
														$is_ns_paste_disabled = 1;
														$is_connect_disabled = 0;
														$is_ns_connect_disabled = 1;
														$should_swap_connect_be_active = 1;
														$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections WHERE (initial_source_symptom_id = '".$quelleComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$quelleComparingSymptomRow['id']."') AND (initial_source_symptom_id != '".$iniSymRow['id']."' AND comparing_source_symptom_id != '".$iniSymRow['id']."') AND (is_connected = 1 OR is_pasted = 1)".$queryConditionForComparative);
														if(mysqli_num_rows($ceheckConnectionResult) > 0){
															$comHasConnections = 1;
															while($conRow = mysqli_fetch_array($ceheckConnectionResult)){
																if($conRow['is_saved'] == 0)
																	$isFurtherConnectionsAreSaved = 0;

																if($conRow['initial_source_id'] == $initialQuelleId)
																	$should_swap_connect_be_active = 0;

																if($conRow['initial_source_id'] == $initialQuelleId OR $conRow['comparing_source_id'] == $initialQuelleId){
																	if($conRow['is_connected'] == 1)
																	{
																		$is_paste_disabled = 1;
																	}
																	else if($conRow['is_pasted'] == 1) 
																	{
																		$is_connect_disabled = 1;
																		$is_paste_disabled = 1;
																	}
																}
															}
														}

														// get Origin Jahr/Year
														$originComparingSourceYear = "";
														$originComparingSourceLanguage = "";
														$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$quelleComparingSymptomRow['original_quelle_id']."'");
														if(mysqli_num_rows($originComparingQuelleResult) > 0){
															$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
															$originComparingSourceYear = $originComQuelleRow['jahr'];
															if($originComQuelleRow['sprache'] == "deutsch")
																$originComparingSourceLanguage = "de";
															else if($originComQuelleRow['sprache'] == "englisch") 
																$originComparingSourceLanguage = "en";
														}

														$matchedSymptomArray[] = array(
															"no_of_match" => ($no_of_match != "") ? $no_of_match : "",
															"percentage" => ($percentage != "") ? $percentage : "",
															"comparison_initial_source_id" => ($initialQuelleId != "") ? $initialQuelleId : "",
															"source_arznei_id" => ($arzneiId != "") ? $arzneiId : "",
															"initial_source_id" => ($initialQuelleId != "") ? $initialQuelleId : "",
															"initial_original_source_id" => ($iniSymRow['original_quelle_id'] != "") ? $iniSymRow['original_quelle_id'] : "",
															"initial_source_code" => ($iniSymRow['quelle_code'] != "") ? $iniSymRow['quelle_code'] : "",
															"initial_source_year" => ($originInitialSourceYear != "") ? $originInitialSourceYear : "",
															"initial_source_original_language" => ($originInitialSourceLanguage != "") ? $originInitialSourceLanguage : "",
															"initial_saved_version_source_code" => ($initialQuelle != "") ? $initialQuelle : "",
															"initial_source_symptom_highlighted_de" => ($initial_source_symptom_highlighted_de != "") ? $initial_source_symptom_highlighted_de : "",
															"initial_source_symptom_highlighted_en" => ($initial_source_symptom_highlighted_en != "") ? $initial_source_symptom_highlighted_en : "",
															"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
															"initial_source_symptom_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
															"initial_source_symptom_before_conversion_highlighted_de" => ($initial_source_symptom_before_conversion_highlighted_de != "") ? $initial_source_symptom_before_conversion_highlighted_de : "",
															"initial_source_symptom_before_conversion_highlighted_en" => ($initial_source_symptom_before_conversion_highlighted_en != "") ? $initial_source_symptom_before_conversion_highlighted_en : "",
															"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
															"initial_source_symptom_before_conversion_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
															"initial_source_symptom_id" => ($iniSymRow['id'] != "") ? $iniSymRow['id'] : "",
															"main_parent_initial_symptom_id" => ($iniSymRow['id'] != "") ? $iniSymRow['id'] : "",
															"connections_main_parent_symptom_id" => ($quelleComparingSymptomRow['id'] != "") ? $quelleComparingSymptomRow['id'] : "",
															"initial_source_symptom_comment" => ($iniSymRow['Kommentar'] != "") ? $iniSymRow['Kommentar'] : "",
															"initial_source_symptom_footnote" => ($iniSymRow['Fussnote'] != "") ? $iniSymRow['Fussnote'] : "",
															"comparing_source_id" => ($comparingQuelleId != "") ? $comparingQuelleId : "",
															"comparing_original_source_id" => ($quelleComparingSymptomRow['original_quelle_id'] != "") ? $quelleComparingSymptomRow['original_quelle_id'] : "",
															"comparing_source_code" => ($quelleComparingSymptomRow['quelle_code'] != "") ? $quelleComparingSymptomRow['quelle_code'] : "",
															"comparing_source_year" => ($originComparingSourceYear != "") ? $originComparingSourceYear : "",
															"comparing_source_original_language" => ($originComparingSourceLanguage != "") ? $originComparingSourceLanguage : "",
															"comparing_saved_version_source_code" => ($comparingQuelle != "") ? $comparingQuelle : "",
															"comparing_source_symptom_highlighted_de" => ($comparing_source_symptom_highlighted_de !="") ? $comparing_source_symptom_highlighted_de : "",
															"comparing_source_symptom_highlighted_en" => ($comparing_source_symptom_highlighted_en !="") ? $comparing_source_symptom_highlighted_en : "",
															"comparing_source_symptom_de" => ($compSymptomString_de !="") ? $compSymptomString_de : "",
															"comparing_source_symptom_en" => ($compSymptomString_en !="") ? $compSymptomString_en : "",
															"comparing_source_symptom_before_conversion_highlighted_de" => ($comparing_source_symptom_before_conversion_highlighted_de != "") ? $comparing_source_symptom_before_conversion_highlighted_de : "",
															"comparing_source_symptom_before_conversion_highlighted_en" => ($comparing_source_symptom_before_conversion_highlighted_en != "") ? $comparing_source_symptom_before_conversion_highlighted_en : "",
															"comparing_source_symptom_before_conversion_de" => ($compSymptomStringBeforeConversion_de != "") ? $compSymptomStringBeforeConversion_de : "",
															"comparing_source_symptom_before_conversion_en" => ($compSymptomStringBeforeConversion_en != "") ? $compSymptomStringBeforeConversion_en : "",
															"comparing_source_symptom_id" => ($quelleComparingSymptomRow['id'] != "") ? $quelleComparingSymptomRow['id'] : "",
															"comparing_source_symptom_comment" => ($quelleComparingSymptomRow['Kommentar'] != "") ? $quelleComparingSymptomRow['Kommentar'] : "",
															"comparing_source_symptom_footnote" => ($quelleComparingSymptomRow['Fussnote'] != "") ? $quelleComparingSymptomRow['Fussnote'] : "",
															"comparison_language" => ($individual_comparison_language != "") ? $individual_comparison_language : "",
															// "main_initial_symptom_id" => $iniSymRow['id'],
															"has_connections" => ($comHasConnections != "") ? $comHasConnections : "",
															"is_final_version_available" => ($quelleComparingSymptomRow['is_final_version_available'] != "") ? $quelleComparingSymptomRow['is_final_version_available'] : "",
															"is_further_connections_are_saved" => ($isFurtherConnectionsAreSaved != "") ? $isFurtherConnectionsAreSaved : "",
															"should_swap_connect_be_active" => ($should_swap_connect_be_active != "") ? $should_swap_connect_be_active : "",
															"is_pasted" => 0,
															"is_ns_paste" => 0,
															"ns_paste_note" => "",
															"is_initial_source" => 0,
															"active_symptom_type" => "comparing",
															"similarity_rate" => ($similarityRate != "") ? $similarityRate : "",
															"comparison_option" => ($comparisonOption != "") ? $comparisonOption : "",
															"is_unmatched_symptom" => 0,
															"is_paste_disabled" => ($is_paste_disabled != "") ? $is_paste_disabled : "",
															"is_ns_paste_disabled" => ($is_ns_paste_disabled != "") ? $is_ns_paste_disabled : "",
															"is_connect_disabled" => ($is_connect_disabled != "") ? $is_connect_disabled : "",
															"is_ns_connect_disabled" => ($is_ns_connect_disabled != "") ? $is_ns_connect_disabled : ""
														);
													}
												}
											}
										}

										if(!empty($matchedSymptomIdsArray))
											$matchedSymptomIdsToSent = implode(',', $matchedSymptomIdsArray);

										// Short the matched symptoms DESC
										if(!empty($matchedSymptomArray)){
											$per = array();
											foreach ($matchedSymptomArray as $key => $row)
											{
											    $per[$key] = $row['percentage'];
											}
											array_multisort($per, SORT_DESC, $matchedSymptomArray);
											foreach ($matchedSymptomArray as $key => $value) {
												$compareResultArray[] = $value;
											}
										}
									}
								}
							}

							$resultData = $compareResultArray;
							$status = 'success';
	    					$message = "Success";
						}else{
							$status = 'error';
							$message = 'Could not find the required data';
						}
						break;	
					}
				case 'unmatched_section':
					{
						$arzneiId = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : null;
						$removable_sets = (isset($_POST['removable_sets']) AND $_POST['removable_sets'] != "") ? $_POST['removable_sets'] : array();
						// $removableSymptomId = (isset($_POST['removable_symptom_id']) AND $_POST['removable_symptom_id'] != "") ? $_POST['removable_symptom_id'] : null;
						// $connectedWithSymptomId = (isset($_POST['connected_with_symptom_id']) AND $_POST['connected_with_symptom_id'] != "") ? $_POST['connected_with_symptom_id'] : null;
						$mainInitialSourceId = (isset($_POST['initial_source_id']) AND !empty($_POST['initial_source_id'])) ? $_POST['initial_source_id'] : array();
						$comparingSources = (isset($_POST['comparing_source_ids']) AND !empty($_POST['comparing_source_ids'])) ? $_POST['comparing_source_ids'] : array();
						$activeMatchingPercentage = (isset($_POST['active_matching_percentage']) AND $_POST['active_matching_percentage'] != "") ? $_POST['active_matching_percentage'] : null;
						$similarityRate = (isset($_POST['similarity_rate']) AND $_POST['similarity_rate'] != "") ? $_POST['similarity_rate'] : null;
						$comparisonOption = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : null;
						$matchedSymptomIds = (isset($_POST['matched_symptom_ids']) AND !empty($_POST['matched_symptom_ids'])) ? $_POST['matched_symptom_ids'] : null;
						$individual_comparison_language = (isset($_POST['individual_comparison_language']) AND $_POST['individual_comparison_language'] != "") ? $_POST['individual_comparison_language'] : null;
						$matchedSymptomIdsToSent = $matchedSymptomIds;
						$initialSourceArray = array();
						$comparingSourcesArray = array();
						if($mainInitialSourceId != "" AND $comparingSources != "" AND $arzneiId != ""){
							$InitialQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$mainInitialSourceId."'");
							if(mysqli_num_rows($InitialQuelleResult) > 0){
								$iniRow = mysqli_fetch_assoc($InitialQuelleResult);
								if($iniRow['quelle_type_id'] == 3)
									$preparedQuelleCode = $iniRow['code'];
								else{
									if($iniRow['jahr'] != "" AND $iniRow['code'] != "")
										$rowQuelleCode = trim(str_replace(trim($iniRow['jahr']), '', $iniRow['code']));
									else
										$rowQuelleCode = trim($iniRow['code']);
									$preparedQuelleCode = trim($rowQuelleCode." ".$iniRow['jahr']);
								}
								$initialSourceArray[] = array(
									'quelle_id' => $iniRow['quelle_id'],
									'quelle' => $preparedQuelleCode
								);
							}

							$comparingSourceIdsArr = explode(',', $comparingSources);
							foreach ($comparingSourceIdsArr as $getKey => $getVal) {
								$comparingQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$getVal."'");
								if(mysqli_num_rows($comparingQuelleResult) > 0){
									while($comparingRow = mysqli_fetch_array($comparingQuelleResult)){
										if($comparingRow['quelle_id'] != $mainInitialSourceId){
											if($comparingRow['quelle_type_id'] == 3)
												$preparedQuelleCodeForCom = $comparingRow['code'];
											else{
												if($comparingRow['jahr'] != "" AND $comparingRow['code'] != "")
													$rowQuelleCodeForCom = trim(str_replace(trim($comparingRow['jahr']), '', $comparingRow['code']));
												else
													$rowQuelleCodeForCom = trim($comparingRow['code']);
												$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingRow['jahr']);
											}

											$comparingSourcesArray[] = array(
												'quelle_id' => $comparingRow['quelle_id'],
												'quelle' => $preparedQuelleCodeForCom,
												'year' => trim($comparingRow['jahr'])
											);
										}
									}
								}
							}
							$comparingSourceWhereIn = "";
							if(!empty($comparingSourcesArray)){
								$quelleYear = array();
								$comparingSourceWhereIn = "(";
								foreach ($comparingSourcesArray as $key => $row)
								{
								    $quelleYear[$key] = $row['year'];
								    $comparingSourceWhereIn .= $row['quelle_id'].',';
								}
								array_multisort($quelleYear, SORT_ASC, $comparingSourcesArray);
								$comparingSourceWhereIn = rtrim($comparingSourceWhereIn, ',');
								$comparingSourceWhereIn .= ")";
							}
							$initialQuelleId = (isset($initialSourceArray[0]['quelle_id']) AND $initialSourceArray[0]['quelle_id'] != "") ? $initialSourceArray[0]['quelle_id'] : null;
							$initialQuelle = (isset($initialSourceArray[0]['quelle']) AND $initialSourceArray[0]['quelle'] != "") ? $initialSourceArray[0]['quelle'] : null;

							$allComparedSourcers = array();
							$comparedSourcersOfInitialSource = array();
							$queryConditionForComparative = '';
							$queryCondition = '';
							$workingSourceIdsArr = array();
							$workingSourceIdsArr = $comparingSourceIdsArr;
							if($initialQuelleId != ""){
								array_push($workingSourceIdsArr, $initialQuelleId);
							}

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
							$initialQuelleIdInArr = explode(',', $initialQuelleId);
							if(!empty($initialQuelleIdInArr)){
								$returnedIds = getAllComparedSourceIds($initialQuelleIdInArr);
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

							$conditionIds = (!empty($workingSourceIdsArr)) ? rtrim(implode(',', $workingSourceIdsArr), ',') : null;
							$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;
							if($conditionIds != "")
								$queryCondition .= " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
							if($conditionIdsForComparative != "")
								$queryConditionForComparative .= " AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative."))";

							if($arzneiId != ""){
								$queryCondition .= " AND source_arznei_id = '".$arzneiId."'"; 
								$queryConditionForComparative .= " AND source_arznei_id = '".$arzneiId."'"; 
							}

							$compareResultArray = array();
							$matchedSymptomIdsArray = explode(',', $matchedSymptomIds);
							if(!empty($removable_sets))
							{
								foreach ($removable_sets as $remKey => $remVal) {
									$removableSymptomId = $removable_sets[$remKey]['active_symptom_id'];
									$connectedWithSymptomId = $removable_sets[$remKey]['opposite_symptom_id'];
									$matching_percentage = $removable_sets[$remKey]['matching_percentage'];
									
									if (($key = array_search($removableSymptomId, $matchedSymptomIdsArray)) !== false) {
										// $cnt = count(array_filter($matchedSymptomIdsArray,function($a) {return $a==$removableSymptomId;}));
										$tmp = array_count_values($matchedSymptomIdsArray);
										$cnt = $tmp[$removableSymptomId];
										if($cnt < 2 AND $matching_percentage < $similarityRate)
										{
											unset($matchedSymptomIdsArray[$key]);
										}
									}
									
								}
							}

							$escapeSymptomCondition = "";
							if(!empty($matchedSymptomIdsArray)){
								$matchedSymptomIdsToSent = implode(',', $matchedSymptomIdsArray);
								$uniqueMatchedSymptomIds = array_unique($matchedSymptomIdsArray);
								$matchedSymptomIdsString = implode(',', $uniqueMatchedSymptomIds);
								
								$escapeSymptomCondition = "AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")";

								$restOfComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote, quelle_import_test.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.quelle_id = quelle.quelle_id WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1 AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition." ORDER BY quelle.jahr ASC");
								if(mysqli_num_rows($restOfComparingSymptomResult) > 0)
								{
									while($restOfComparingSymptomRow = mysqli_fetch_array($restOfComparingSymptomResult)){
										// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										if($restOfComparingSymptomRow['is_final_version_available'] != 0){
											$iniSymptomString_de =  ($restOfComparingSymptomRow['final_version_de'] != "") ? $restOfComparingSymptomRow['final_version_de'] : "";
											$iniSymptomString_en =  ($restOfComparingSymptomRow['final_version_en'] != "") ? $restOfComparingSymptomRow['final_version_en'] : "";
										}else{
											if($comparisonOption == 1){
												$iniSymptomString_de =  ($restOfComparingSymptomRow['searchable_text_de'] != "") ? $restOfComparingSymptomRow['searchable_text_de'] : "";
												$iniSymptomString_en =  ($restOfComparingSymptomRow['searchable_text_en'] != "") ? $restOfComparingSymptomRow['searchable_text_en'] : "";
											}else{
												$iniSymptomString_de =  ($restOfComparingSymptomRow['BeschreibungFull_de'] != "") ? $restOfComparingSymptomRow['BeschreibungFull_de'] : "";
												$iniSymptomString_en =  ($restOfComparingSymptomRow['BeschreibungFull_en'] != "") ? $restOfComparingSymptomRow['BeschreibungFull_en'] : "";
											}
										}

										// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
										$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
										$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

										// Apply dynamic conversion
										if($iniSymptomString_de != ""){
											$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $restOfComparingSymptomRow['original_quelle_id'], $restOfComparingSymptomRow['arznei_id'], $restOfComparingSymptomRow['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
											$iniSymptomString_de = base64_encode($iniSymptomString_de);
										}
										if($iniSymptomString_en != ""){
											$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $restOfComparingSymptomRow['original_quelle_id'], $restOfComparingSymptomRow['arznei_id'], $restOfComparingSymptomRow['is_final_version_available'], 0, $restOfComparingSymptomRow['id'], $restOfComparingSymptomRow['original_symptom_id']);
											$iniSymptomString_en = base64_encode($iniSymptomString_en);
										}

										$comHasConnections = 0;
										$isFurtherConnectionsAreSaved = 1;
										$is_paste_disabled = 0;
										$is_ns_paste_disabled = 1;
										$is_connect_disabled = 0;
										$is_ns_connect_disabled = 1;
										$should_swap_connect_be_active = 1;
										$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections WHERE (initial_source_symptom_id = '".$restOfComparingSymptomRow['id']."' OR comparing_source_symptom_id = '".$restOfComparingSymptomRow['id']."') AND (is_connected = 1 OR is_pasted = 1)".$queryCondition);
										if(mysqli_num_rows($ceheckConnectionResult) > 0){
											$comHasConnections = 1;
											while($conRow = mysqli_fetch_array($ceheckConnectionResult)){
												if($conRow['is_saved'] == 0)
													$isFurtherConnectionsAreSaved = 0;

												if($conRow['initial_source_id'] == $initialQuelleId)
													$should_swap_connect_be_active = 0;
												
												if($conRow['initial_source_id'] == $initialQuelleId OR $conRow['comparing_source_id'] == $initialQuelleId){
													if($conRow['is_connected'] == 1)
													{
														$is_paste_disabled = 1;
													}
													else if($conRow['is_pasted'] == 1) 
													{
														$is_connect_disabled = 1;
														$is_paste_disabled = 1;
													}
												}
											}
										}

										if($restOfComparingSymptomRow['quelle_type_id'] == 3)
											$preparedQuelleCode = $restOfComparingSymptomRow['code'];
										else{
											if($restOfComparingSymptomRow['jahr'] != "" AND $restOfComparingSymptomRow['code'] != "")
												$rowQuelleCode = trim(str_replace(trim($restOfComparingSymptomRow['jahr']), '', $restOfComparingSymptomRow['code']));
											else
												$rowQuelleCode = trim($restOfComparingSymptomRow['code']);
											$preparedQuelleCode = trim($rowQuelleCode." ".$restOfComparingSymptomRow['jahr']);
										}

										// get Origin Jahr/Year
										$originInitialSourceYear = "";
										$originInitialSourceLanguage = "";
										$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$restOfComparingSymptomRow['original_quelle_id']."'");
										if(mysqli_num_rows($originInitialQuelleResult) > 0){
											$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
											$originInitialSourceYear = $originIniQuelleRow['jahr'];
											if($originIniQuelleRow['sprache'] == "deutsch")
												$originInitialSourceLanguage = "de";
											else if($originIniQuelleRow['sprache'] == "englisch") 
												$originInitialSourceLanguage = "en";
										}

										$compareResultArray[] = array(
											"no_of_match" => 0,
											"percentage" => 0,
											"comparison_initial_source_id" => ($initialQuelleId != "") ? $initialQuelleId : "",
											"source_arznei_id" => ($arzneiId != "") ? $arzneiId : "",
											"initial_source_id" => ($restOfComparingSymptomRow['quelle_id'] != "") ? $restOfComparingSymptomRow['quelle_id'] : "",
											"initial_original_source_id" => ($restOfComparingSymptomRow['original_quelle_id'] != "") ? $restOfComparingSymptomRow['original_quelle_id'] : "",
											"initial_source_code" => ($restOfComparingSymptomRow['quelle_code'] != "") ? $restOfComparingSymptomRow['quelle_code'] : "",
											"initial_source_year" => ($originInitialSourceYear != "") ? $originInitialSourceYear : "",
											"initial_source_original_language" => ($originInitialSourceLanguage != "") ? $originInitialSourceLanguage : "",
											"initial_saved_version_source_code" => ($preparedQuelleCode != "") ? $preparedQuelleCode : "",
											"initial_source_symptom_highlighted_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
											"initial_source_symptom_highlighted_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
											"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
											"initial_source_symptom_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
											"initial_source_symptom_before_conversion_highlighted_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
											"initial_source_symptom_before_conversion_highlighted_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
											"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
											"initial_source_symptom_before_conversion_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
											"initial_source_symptom_id" => ($restOfComparingSymptomRow['id'] != "") ? $restOfComparingSymptomRow['id'] : "",
											"main_parent_initial_symptom_id" => ($restOfComparingSymptomRow['id'] != "") ? $restOfComparingSymptomRow['id'] : "",
											"connections_main_parent_symptom_id" => ($restOfComparingSymptomRow['id'] != "") ? $restOfComparingSymptomRow['id'] : "",
											"initial_source_symptom_comment" => ($restOfComparingSymptomRow['Kommentar'] != "") ? $restOfComparingSymptomRow['Kommentar'] : "",
											"initial_source_symptom_footnote" => ($restOfComparingSymptomRow['Fussnote'] != "") ? $restOfComparingSymptomRow['Fussnote'] : "",
											"comparing_source_id" => "",
											"comparing_original_source_id" => "",
											"comparing_source_code" => "",
											"comparing_source_year" => "",
											"comparing_source_original_language" => "",
											"comparing_saved_version_source_code" => "",
											"comparing_source_symptom_highlighted_de" => "",
											"comparing_source_symptom_highlighted_en" => "",
											"comparing_source_symptom_de" => "",
											"comparing_source_symptom_en" => "",
											"comparing_source_symptom_before_conversion_highlighted_de" => "",
											"comparing_source_symptom_before_conversion_highlighted_en" => "",
											"comparing_source_symptom_before_conversion_de" => "",
											"comparing_source_symptom_before_conversion_en" => "",
											"comparing_source_symptom_id" => "",
											"comparing_source_symptom_comment" => "",
											"comparing_source_symptom_footnote" => "",
											"comparison_language" => ($individual_comparison_language != "") ? $individual_comparison_language : "",
											// "main_initial_symptom_id" => $restOfComparingSymptomRow['id'],
											"has_connections" => ($comHasConnections != "") ? $comHasConnections : "",
											"is_final_version_available" => ($restOfComparingSymptomRow['is_final_version_available'] != "") ? $restOfComparingSymptomRow['is_final_version_available'] : "",
											"is_further_connections_are_saved" => ($isFurtherConnectionsAreSaved != "") ? $isFurtherConnectionsAreSaved : "",
											"should_swap_connect_be_active" => ($should_swap_connect_be_active != "") ? $should_swap_connect_be_active : "",
											"is_pasted" => 0,
											"is_ns_paste" => 0,
											"ns_paste_note" => "",
											"is_initial_source" => 1,
											"active_symptom_type" => "initial",
											"similarity_rate" => ($similarityRate != "") ? $similarityRate : "",
											"comparison_option" => ($comparisonOption != "") ? $comparisonOption : "",
											"is_unmatched_symptom" => 1,
											"is_paste_disabled" => ($is_paste_disabled != "") ? $is_paste_disabled : "",
											"is_ns_paste_disabled" => ($is_ns_paste_disabled != "") ? $is_ns_paste_disabled : "",
											"is_connect_disabled" => ($is_connect_disabled != "") ? $is_connect_disabled : "",
											"is_ns_connect_disabled" => ($is_ns_connect_disabled != "") ? $is_ns_connect_disabled : ""
										);
									}
								}
							}

							$resultData = $compareResultArray;
							$status = 'success';
							$message = 'Success';
						}else{
							$status = 'success';
							$message = 'Update not needed';
						}
					}
				
				default:
					break;
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message, 'matched_symptom_ids' => $matchedSymptomIdsToSent) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>