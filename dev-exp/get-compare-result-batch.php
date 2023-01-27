<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Generating the comparison result bacthes
	*/
?>
<?php  
	$stopWords = array();
	$stopWords = getStopWords();

	$is_invalid_quelle = 0;

	$compareResultArray = array();
	$formData = array();
	$step = 'done';
	$processStage = "";
	$progress_percentage = 0;
	$comparisonName = "";
	$totalBatchesInPart1 = 1;
	$totalBatchesInPart2 = 1;
	$matchedSymptomIds = array();
	$comparingSourceIds = "";
	$isStage2Checked = 0;
	$un_matched_symptoms_set_number = 0;

	try {

		$saved_comparison_quelle_id = (isset($_POST['saved_comparison_quelle_id']) AND $_POST['saved_comparison_quelle_id'] != "") ? trim($_POST['saved_comparison_quelle_id']) : "";
		if($saved_comparison_quelle_id != "")
		{
			$isQuelleAllowedToPerform = isQuelleAllowedToPerformThisAction($saved_comparison_quelle_id);
			if($isQuelleAllowedToPerform === false)
			{
				echo json_encode( array( 'process_stage' => $processStage, 'step' => $step, 'total_batches_in_part1' => $totalBatchesInPart1, 'total_batches_in_part2' => $totalBatchesInPart2, 'data' => $formData, 'result_data' => $compareResultArray, 'progress_percentage' => $progress_percentage, 'system_generated_comparison_name' => $comparisonName, 'matched_symptom_ids' => $matchedSymptomIds, 'comparing_source_ids' => $comparingSourceIds, 'saved_comparison_comparing_source_ids' => "", 'is_stage2_checked' => $isStage2Checked, 'un_matched_symptoms_set_number' => $un_matched_symptoms_set_number, 'is_invalid_quelle' => 1) ); 
				exit;
			}
		}

		if(isset($_POST['form']) AND !empty($_POST['form'])){
			
			parse_str( $_POST['form'], $formData );
			$arzneiId = (isset($formData['arznei_id']) AND $formData['arznei_id'] != "") ? $formData['arznei_id'] : "";
			$initialSource = (isset($formData['initial_source']) AND $formData['initial_source'] != "") ? $formData['initial_source'] : "";
			$comparingSources = (isset($formData['comparing_sources']) AND !empty($formData['comparing_sources'])) ? $formData['comparing_sources'] : array();
			if(!empty($comparingSources) AND !is_array($comparingSources))
				$comparingSources = explode(',', $comparingSources);
			$similarityRate = (isset($formData['similarity_rate']) AND $formData['similarity_rate'] != "") ? $formData['similarity_rate'] : "";
			$comparisonOption = (isset($formData['comparison_option']) AND $formData['comparison_option'] != "") ? $formData['comparison_option'] : "";
			$comparisonLanguage = (isset($formData['comparison_language']) AND $formData['comparison_language'] != "") ? $formData['comparison_language'] : "";
			$step = (isset($_POST['step']) AND $_POST['step'] != "") ? $_POST['step'] : 1;
			$totalBatchesInPart1 = (isset($_POST['total_batches_in_part1']) AND $_POST['total_batches_in_part1'] != "") ? (int)$_POST['total_batches_in_part1'] : 1;
			$totalBatchesInPart2 = (isset($_POST['total_batches_in_part2']) AND $_POST['total_batches_in_part2'] != "") ? (int)$_POST['total_batches_in_part2'] : 1;
			$isStage2Checked = (isset($_POST['is_stage2_checked']) AND $_POST['is_stage2_checked'] != "") ? (int)$_POST['is_stage2_checked'] : 1;
			$un_matched_symptoms_set_number = (isset($_POST['un_matched_symptoms_set_number']) AND $_POST['un_matched_symptoms_set_number'] != "") ? (int)$_POST['un_matched_symptoms_set_number'] : 0;
			$matchedSymptomIds = (isset($_POST['matched_symptom_ids']) AND !empty($_POST['matched_symptom_ids'])) ? $_POST['matched_symptom_ids'] : array();
			$initialSourceArray = array();
			$comparingSourcesArray = array();
			
			if($initialSource != "" AND !empty($comparingSources) AND $arzneiId != ""){
				// For returning the comparison comparing source ids back
				$comparingSourceIds = implode(',', $comparingSources);

				$InitialQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$initialSource."'");
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

					$comparisonName .= $preparedQuelleCode;
					$initialSourceArray[] = array(
						'quelle_id' => $iniRow['quelle_id'],
						'quelle' => $preparedQuelleCode
					);
				}

				foreach ($comparingSources as $getKey => $getVal){
					$comparingQuelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle WHERE quelle.quelle_id = '".$getVal."'");
					if(mysqli_num_rows($comparingQuelleResult) > 0){
						while($comparingRow = mysqli_fetch_array($comparingQuelleResult)){
							if($comparingRow['quelle_type_id'] == 3)
								$preparedQuelleCodeForCom = $comparingRow['code'];
							else{
								if($comparingRow['jahr'] != "" AND $comparingRow['code'] != "")
									$rowQuelleCodeForCom = trim(str_replace(trim($comparingRow['jahr']), '', $comparingRow['code']));
								else
									$rowQuelleCodeForCom = trim($comparingRow['code']);
								$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingRow['jahr']);
							}

							$comparisonName .= "_".$preparedQuelleCodeForCom;
							if($comparingRow['quelle_id'] != $initialSource){
								$comparingSourcesArray[] = array(
									'quelle_id' => $comparingRow['quelle_id'],
									'quelle' => $preparedQuelleCodeForCom,
									'year' => trim($comparingRow['jahr'])
								);
							}
						}
					}
				}
				// Short Sources ASC
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
				$comparisonName = ltrim($comparisonName,"_");
				$initialQuelleId = (isset($initialSourceArray[0]['quelle_id']) AND $initialSourceArray[0]['quelle_id'] != "") ? $initialSourceArray[0]['quelle_id'] : "";
				$initialQuelle = (isset($initialSourceArray[0]['quelle']) AND $initialSourceArray[0]['quelle'] != "") ? $initialSourceArray[0]['quelle'] : "";


				$allComparedSourcers = array();
				$comparedSourcersOfInitialSource = array();
				$queryConditionForComparative = '';
				$queryCondition = '';
				$workingSourceIdsArr = array();
				$workingSourceIdsArr = $comparingSources;
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

				$conditionIds = (!empty($workingSourceIdsArr)) ? rtrim(implode(',', $workingSourceIdsArr), ',') : "";
				$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : "";
				if($conditionIds != "")
					$queryCondition .= " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
				if($conditionIdsForComparative != "")
					$queryConditionForComparative .= " AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative."))";

				if($arzneiId != ""){
					$queryCondition .= " AND source_arznei_id = '".$arzneiId."'"; 
					$queryConditionForComparative .= " AND source_arznei_id = '".$arzneiId."'"; 
				}

				if($step <= $totalBatchesInPart1){
					// Generating Comparing symptoms array start
					$comparingSymptomsArr = array();
					if(!empty($comparingSourcesArray)){
						foreach($comparingSourcesArray as $comSrcKey => $comSrcVal){
							$comparingQuelleId = $comparingSourcesArray[$comSrcKey]['quelle_id'];
							$comparingQuelle = $comparingSourcesArray[$comSrcKey]['quelle'];

							$quelleComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.arznei_id, quelle_import_test.original_quelle_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.quelle_id = '".$comparingQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1");
							while($quelleComparingSymptomRowPre = mysqli_fetch_array($quelleComparingSymptomResult))
							{
								$dataArray = array();
								$dataArray['original_symptom_id'] = $quelleComparingSymptomRowPre['original_symptom_id'];
								$dataArray['quelle_code'] = $quelleComparingSymptomRowPre['quelle_code'];
								$dataArray['arznei_id'] = $quelleComparingSymptomRowPre['arznei_id'];
								$dataArray['original_quelle_id'] = $quelleComparingSymptomRowPre['original_quelle_id'];
								$dataArray['final_version_de'] = $quelleComparingSymptomRowPre['final_version_de'];
								$dataArray['final_version_en'] = $quelleComparingSymptomRowPre['final_version_en'];
								$dataArray['BeschreibungPlain_de'] = $quelleComparingSymptomRowPre['BeschreibungPlain_de'];
								$dataArray['BeschreibungPlain_en'] = $quelleComparingSymptomRowPre['BeschreibungPlain_en'];
								$dataArray['BeschreibungOriginal_de'] = $quelleComparingSymptomRowPre['BeschreibungOriginal_de'];
								$dataArray['BeschreibungOriginal_en'] = $quelleComparingSymptomRowPre['BeschreibungOriginal_en'];
								$dataArray['BeschreibungFull_de'] = $quelleComparingSymptomRowPre['BeschreibungFull_de'];
								$dataArray['BeschreibungFull_en'] = $quelleComparingSymptomRowPre['BeschreibungFull_en'];
								$dataArray['searchable_text_de'] = $quelleComparingSymptomRowPre['searchable_text_de'];
								$dataArray['searchable_text_en'] = $quelleComparingSymptomRowPre['searchable_text_en'];
								$dataArray['is_final_version_available'] = $quelleComparingSymptomRowPre['is_final_version_available'];
								$dataArray['id'] = $quelleComparingSymptomRowPre['id'];
								$dataArray['Kommentar'] = $quelleComparingSymptomRowPre['Kommentar'];
								$dataArray['Fussnote'] = $quelleComparingSymptomRowPre['Fussnote'];
								$dataArray['comparingQuelleId'] = $comparingQuelleId;
								$dataArray['comparingQuelle'] = $comparingQuelle;
								$comparingSymptomsArr[] = $dataArray;
							}
						}
					}


					// Process Stage
					$processStage = 1;

					$totalIniSymptomQuery = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE quelle_id = '".$initialSource."' AND arznei_id = '".$arzneiId."' AND is_appended_symptom_active = 1");
					$totalInitialSymptom = mysqli_num_rows($totalIniSymptomQuery);

					if($totalInitialSymptom > 0){
						$limit = 40;
						$totalBatchesInPart1 = ceil($totalInitialSymptom / $limit);

						$pageno = (int)$step;
						if ($pageno > $totalBatchesInPart1) {
						   $pageno = $totalBatchesInPart1;
						} 
						if ($pageno < 1) {
						   $pageno = 1;
						} 
						$offset = ($pageno - 1)  * $limit;

						// Progress bar percentage
						$progress_percentage = round(($step / $totalBatchesInPart1) * 100);

						$InitialQuelleResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.arznei_id, quelle_import_test.original_quelle_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.quelle_id = '".$initialQuelleId."' AND quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1 LIMIT ".$offset.", ".$limit);
						if(mysqli_num_rows($InitialQuelleResult) > 0){
							while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								if($iniSymRow['is_final_version_available'] != 0){
									$iniSymptomString_de =  ($iniSymRow['final_version_de'] != "") ? $iniSymRow['final_version_de'] : "";
									$iniSymptomString_en =  ($iniSymRow['final_version_en'] != "") ? $iniSymRow['final_version_en'] : "";
								} else {
									if($comparisonOption == 1){
										$iniSymptomString_de =  ($iniSymRow['searchable_text_de'] != "") ? $iniSymRow['searchable_text_de'] : "";
										$iniSymptomString_en =  ($iniSymRow['searchable_text_en'] != "") ? $iniSymRow['searchable_text_en'] : "";
									}else{
										$iniSymptomString_de =  ($iniSymRow['BeschreibungFull_de'] != "") ? $iniSymRow['BeschreibungFull_de'] : "";
										$iniSymptomString_en =  ($iniSymRow['BeschreibungFull_en'] != "") ? $iniSymRow['BeschreibungFull_en'] : "";
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
									"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
									"initial_source_symptom_before_conversion_highlighted_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
									"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
									"initial_source_symptom_highlighted_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
									"initial_source_symptom_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
									"initial_source_symptom_before_conversion_highlighted_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
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
									"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
									// "main_initial_symptom_id" => $iniSymRow['id'],
									"has_connections" => ($iniHasConnections != "") ? $iniHasConnections : "",
									"is_final_version_available" => ($iniSymRow['is_final_version_available'] != "") ? $iniSymRow['is_final_version_available'] : "",
									"is_further_connections_are_saved" => ($isFurtherConnectionsAreSaved != "") ? $isFurtherConnectionsAreSaved : "",
									"should_swap_connect_be_active" => 1,
									"is_pasted" => 0,
									"is_ns_paste" => 0,
									"ns_paste_note" => "",
									"is_initial_source" => 1,
									"active_symptom_type" => "initial",
									"similarity_rate" => ($similarityRate != "") ? $similarityRate : "",
									"comparison_option" => ($comparisonOption != "") ? $comparisonOption : "",
									"is_unmatched_symptom" => 0,
									"is_paste_disabled" => ($is_paste_disabled != "") ? $is_paste_disabled : "",
									"is_ns_paste_disabled" => ($is_ns_paste_disabled != "") ? $is_ns_paste_disabled : "",
									"is_connect_disabled" => ($is_connect_disabled != "") ? $is_connect_disabled : "",
									"is_ns_connect_disabled" => ($is_ns_connect_disabled != "") ? $is_ns_connect_disabled : ""
								);


								if(!empty($comparingSymptomsArr)){
									$matchedSymptomArray = array();
									foreach ($comparingSymptomsArr as $key => $quelleComparingSymptomRow) {
										// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										if($quelleComparingSymptomRow['is_final_version_available'] != 0){
											$compSymptomString_de =  ($quelleComparingSymptomRow['final_version_de'] != "") ? $quelleComparingSymptomRow['final_version_de'] : "";
											$compSymptomString_en =  ($quelleComparingSymptomRow['final_version_en'] != "") ? $quelleComparingSymptomRow['final_version_en'] : "";
										}else{
											if($comparisonOption == 1){
												$compSymptomString_de =  ($quelleComparingSymptomRow['searchable_text_de'] != "") ? $quelleComparingSymptomRow['searchable_text_de'] : "";
												$compSymptomString_en =  ($quelleComparingSymptomRow['searchable_text_en'] != "") ? $quelleComparingSymptomRow['searchable_text_en'] : "";
											}else{
												$compSymptomString_de =  ($quelleComparingSymptomRow['BeschreibungFull_de'] != "") ? $quelleComparingSymptomRow['BeschreibungFull_de'] : "";
												$compSymptomString_en =  ($quelleComparingSymptomRow['BeschreibungFull_en'] != "") ? $quelleComparingSymptomRow['BeschreibungFull_en'] : "";
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
										if(mysqli_num_rows($connectedSymptomResult) > 0){
											// if(!in_array($quelleComparingSymptomRow['id'], $matchedSymptomIds))
											array_push($matchedSymptomIds, $quelleComparingSymptomRow['id']);
										}
										else
										{
											if($comparisonLanguage == "en"){
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
												// if(!in_array($quelleComparingSymptomRow['id'], $matchedSymptomIds))
												array_push($matchedSymptomIds, $quelleComparingSymptomRow['id']);

												$comHasConnections = 0;
												$isFurtherConnectionsAreSaved = 1;
												$is_paste_disabled = 0;
												$is_ns_paste_disabled = 1;
												$is_connect_disabled = 0;
												$is_ns_connect_disabled = 1;
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
													"no_of_match" => ($no_of_match !="") ? $no_of_match : "",
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
													"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
													"initial_source_symptom_before_conversion_highlighted_de" => ($initial_source_symptom_before_conversion_highlighted_de != "") ? $initial_source_symptom_before_conversion_highlighted_de : "",
													"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de !="") ? $iniSymptomStringBeforeConversion_de : "",
													"initial_source_symptom_highlighted_en" => ($initial_source_symptom_highlighted_en !="") ? $initial_source_symptom_highlighted_en : "",
													"initial_source_symptom_en" => ($iniSymptomString_en !="") ? $iniSymptomString_en : "",
													"initial_source_symptom_before_conversion_highlighted_en" => ($initial_source_symptom_before_conversion_highlighted_en !="") ? $initial_source_symptom_before_conversion_highlighted_en : "",
													"initial_source_symptom_before_conversion_en" => ($iniSymptomStringBeforeConversion_en !="") ? $iniSymptomStringBeforeConversion_en : "",
													"initial_source_symptom_id" => ($iniSymRow['id'] !="") ? $iniSymRow['id'] : "",
													"main_parent_initial_symptom_id" => ($iniSymRow['id'] !="") ? $iniSymRow['id'] : "",
													"connections_main_parent_symptom_id" => ($quelleComparingSymptomRow['id'] !="") ? $quelleComparingSymptomRow['id'] : "",
													"initial_source_symptom_comment" => ($iniSymRow['Kommentar'] !="") ? $iniSymRow['Kommentar'] : "",
													"initial_source_symptom_footnote" => ($iniSymRow['Fussnote'] !="") ? $iniSymRow['Fussnote'] : "",
													"comparing_source_id" => ($quelleComparingSymptomRow['comparingQuelleId'] !="") ? $quelleComparingSymptomRow['comparingQuelleId'] : "",
													"comparing_original_source_id" => ($quelleComparingSymptomRow['original_quelle_id'] !="") ? $quelleComparingSymptomRow['original_quelle_id'] : "",
													"comparing_source_code" => ($quelleComparingSymptomRow['quelle_code'] !="") ? $quelleComparingSymptomRow['quelle_code'] : "",
													"comparing_source_year" => ($originComparingSourceYear !="") ? $originComparingSourceYear : "",
													"comparing_source_original_language" => ($originComparingSourceLanguage !="") ? $originComparingSourceLanguage : "",
													"comparing_saved_version_source_code" => ($quelleComparingSymptomRow['comparingQuelle'] !="") ? $quelleComparingSymptomRow['comparingQuelle'] : "",
													"comparing_source_symptom_highlighted_de" => ($comparing_source_symptom_highlighted_de !="") ? $comparing_source_symptom_highlighted_de : "",
													"comparing_source_symptom_de" => ($compSymptomString_de !="") ? $compSymptomString_de : "",
													"comparing_source_symptom_before_conversion_highlighted_de" => ($comparing_source_symptom_before_conversion_highlighted_de != "") ? $comparing_source_symptom_before_conversion_highlighted_de : "",
													"comparing_source_symptom_before_conversion_de" => ($compSymptomStringBeforeConversion_de != "") ? $compSymptomStringBeforeConversion_de : "",
													"comparing_source_symptom_highlighted_en" => ($comparing_source_symptom_highlighted_en != "") ? $comparing_source_symptom_highlighted_en : "",
													"comparing_source_symptom_en" => ($compSymptomString_en != "") ? $compSymptomString_en : "",
													"comparing_source_symptom_before_conversion_highlighted_en" => ($comparing_source_symptom_before_conversion_highlighted_en != "") ? $comparing_source_symptom_before_conversion_highlighted_en : "",
													"comparing_source_symptom_before_conversion_en" => ($compSymptomStringBeforeConversion_en != "") ? $compSymptomStringBeforeConversion_en : "",
													"comparing_source_symptom_id" => ($quelleComparingSymptomRow['id'] != "") ? $quelleComparingSymptomRow['id'] : "",
													"comparing_source_symptom_comment" => ($quelleComparingSymptomRow['Kommentar'] != "") ? $quelleComparingSymptomRow['Kommentar'] : "",
													"comparing_source_symptom_footnote" => ($quelleComparingSymptomRow['Fussnote'] != "") ? $quelleComparingSymptomRow['Fussnote'] : "",
													"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
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

									// Comparing with the main initial source symptoms START
									
									// Comparing with the main initial source symptoms END

									if(!empty($matchedSymptomArray)){
										// Short the matched symptoms Percentage DESC
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
								// Comparing source check end
							}
						}
					}
					else{
						$totalBatchesInPart1 = 0;
						$progress_percentage = 100;
					}
					
					if($step == $totalBatchesInPart1){
						$totalNumber = "";
						$escapeSymptomCondition = "";
						if(!empty($matchedSymptomIds)){
							$uniqueMatchedSymptomIds = array_unique($matchedSymptomIds);
							$matchedSymptomIdsString = implode(',', $uniqueMatchedSymptomIds);
							$escapeSymptomCondition = "AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")";
						}
						if($comparingSourceWhereIn != ""){
							$restOfComparingSymptomResultCount = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1 AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition);
							$totalRestOfSymptoms = mysqli_num_rows($restOfComparingSymptomResultCount);
							if($totalRestOfSymptoms == 0){
								$totalNumber = 0;
							}
						}
						if($totalNumber === 0){
							$isStage2Checked = 1;
							$totalBatchesInPart2 = 0;
							// For display lable
							$processStage = 2;
							$progress_percentage = 100;
							$step = 'done';
						}else{
							$step = $step + 1;
						}
					}
					else{
						$step = $step + 1;
					}
				}
				else if($isStage2Checked == 0 OR $step <= $totalBatchesInPart2+$totalBatchesInPart1)
				{
					$isStage2Checked = 1;
					// Rest of the unmatch symptoms 
					$pageno = (int)$step - (int)$totalBatchesInPart1;
					// Process Stage
					$processStage = 2;

					$escapeSymptomCondition = "";
					if(!empty($matchedSymptomIds)){
						$uniqueMatchedSymptomIds = array_unique($matchedSymptomIds);
						$matchedSymptomIdsString = implode(',', $uniqueMatchedSymptomIds);
						$escapeSymptomCondition = "AND quelle_import_test.id NOT IN (".$matchedSymptomIdsString.")";
					}

					if($comparingSourceWhereIn != ""){
						$restOfComparingSymptomResultCount = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1 AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition);
						$totalRestOfSymptoms = mysqli_num_rows($restOfComparingSymptomResultCount);
						if($totalRestOfSymptoms > 0){
							$un_matched_symptoms_set_number++;
							$limit = 40;
							$totalBatchesInPart2 = ceil($totalRestOfSymptoms / $limit);

							// $pageno = (int)$totalBatchesInPart1 - (int)$step;
							if ($pageno > $totalBatchesInPart2) {
							   $pageno = $totalBatchesInPart2;
							} 
							if ($pageno < 1) {
							   $pageno = 1;
							} 
							$offset = ($pageno - 1)  * $limit;

							// Progress bar percentage
							$progress_percentage = round(($pageno / $totalBatchesInPart2) * 100);

							$restOfComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.arznei_id, quelle_import_test.original_quelle_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.Kommentar, quelle_import_test.Fussnote, quelle_import_test.quelle_id, quelle.code, quelle.jahr, quelle.quelle_type_id FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id LEFT JOIN quelle ON quelle_import_test.quelle_id = quelle.quelle_id WHERE quelle_import_test.arznei_id = '".$arzneiId."' AND quelle_import_test.is_appended_symptom_active = 1 AND quelle_import_test.quelle_id IN ".$comparingSourceWhereIn." ".$escapeSymptomCondition." ORDER BY quelle.jahr ASC LIMIT ".$offset.", ".$limit);
							if(mysqli_num_rows($restOfComparingSymptomResult))
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
										"initial_source_symptom_de" => ($iniSymptomString_de != "") ? $iniSymptomString_de : "",
										"initial_source_symptom_before_conversion_highlighted_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
										"initial_source_symptom_before_conversion_de" => ($iniSymptomStringBeforeConversion_de != "") ? $iniSymptomStringBeforeConversion_de : "",
										"initial_source_symptom_highlighted_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
										"initial_source_symptom_en" => ($iniSymptomString_en != "") ? $iniSymptomString_en : "",
										"initial_source_symptom_before_conversion_highlighted_en" => ($iniSymptomStringBeforeConversion_en != "") ? $iniSymptomStringBeforeConversion_en : "",
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
										"comparison_language" => ($comparisonLanguage != "") ? $comparisonLanguage : "",
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
						else
						{
							$pageno = 0;
							$totalBatchesInPart2 = 0;
						}
					}
					else
					{
						$pageno = 0;
						$totalBatchesInPart2 = 0;
					}

					if($pageno == $totalBatchesInPart2){
						$progress_percentage = 100;
					}

					if($step == $totalBatchesInPart2+$totalBatchesInPart1)
						$step = 'done';
					else
						$step = $step + 1;
				}
				else
				{
					// For display lable
					$processStage = 2;
					$progress_percentage = 100;
					$step = 'done';
				}
			}
		}
	} catch (Exception $e) {
	    $step = 'error';
	}


	echo json_encode( array( 'process_stage' => $processStage, 'step' => $step, 'total_batches_in_part1' => $totalBatchesInPart1, 'total_batches_in_part2' => $totalBatchesInPart2, 'data' => $formData, 'result_data' => $compareResultArray, 'progress_percentage' => $progress_percentage, 'system_generated_comparison_name' => $comparisonName, 'matched_symptom_ids' => $matchedSymptomIds, 'comparing_source_ids' => $comparingSourceIds, 'saved_comparison_comparing_source_ids' => "", 'is_stage2_checked' => $isStage2Checked, 'un_matched_symptoms_set_number' => $un_matched_symptoms_set_number, 'is_invalid_quelle' => $is_invalid_quelle) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>