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

				$allComparedSourcers = array();
				$comparedSourcersOfInitialSource = array();
				$queryConditionForComparative = '';
				$queryCondition = '';
				$workingSourceIdsArr = array();
				$workingSourceIdsArr = $comparingSources;
				if($initialSource != ""){
					array_push($workingSourceIdsArr, $initialSource);
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
				$initialQuelleIdInArr = explode(',', $initialSource);
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

					$totalBatchesInPart1 = 1;
					// Process Stage
					$processStage = 1;

					// Progress bar percentage
					$progress_percentage = round(($step / $totalBatchesInPart1) * 100);

					$tempComparisonSourceArr = (!empty($comparingSources)) ? $comparingSources : array();
					sort($tempComparisonSourceArr);
					$comparingSourecsCombined = implode('_', $tempComparisonSourceArr); 
					$tableName = $arzneiId."_".$initialSource."_".$comparingSourecsCombined."_".$comparisonLanguage;
					$preComparedData = mysqli_query($db,"SELECT * FROM ".$tableName);
					if(mysqli_num_rows($preComparedData) > 0){
						while($row = mysqli_fetch_array($preComparedData))
						{
							if($row['active_symptom_type'] == "initial"){
								$compareResultArray[] = array(
									"no_of_match" => ($row['no_of_match'] != "") ? $row['no_of_match'] : 0,
									"percentage" => ($row['percentage'] != "") ? $row['percentage'] : 0,
									"comparison_initial_source_id" => ($row['comparison_initial_source_id'] != "") ? $row['comparison_initial_source_id'] : "",
									"source_arznei_id" => ($row['source_arznei_id'] != "") ? $row['source_arznei_id'] : "",
									"initial_source_id" => ($row['initial_source_id'] != "") ? $row['initial_source_id'] : "",
									"initial_original_source_id" => ($row['initial_original_source_id'] != "") ? $row['initial_original_source_id'] : "",
									"initial_source_code" => ($row['initial_source_code'] != "") ? $row['initial_source_code'] : "",
									"initial_source_year" => ($row['initial_source_year'] != "") ? $row['initial_source_year'] : "",
									"initial_source_original_language" => ($row['initial_source_original_language'] != "") ? $row['initial_source_original_language'] : "",
									"initial_saved_version_source_code" => ($row['initial_saved_version_source_code'] != "") ? $row['initial_saved_version_source_code'] : "",
									"initial_source_symptom_highlighted_de" => ($row['initial_source_symptom_highlighted_de'] != "") ? $row['initial_source_symptom_highlighted_de'] : "",
									"initial_source_symptom_de" => ($row['initial_source_symptom_de'] != "") ? $row['initial_source_symptom_de'] : "",
									"initial_source_symptom_before_conversion_highlighted_de" => ($row['initial_source_symptom_before_conversion_highlighted_de'] != "") ? $row['initial_source_symptom_before_conversion_highlighted_de'] : "",
									"initial_source_symptom_before_conversion_de" => ($row['initial_source_symptom_before_conversion_de'] != "") ? $row['initial_source_symptom_before_conversion_de'] : "",
									"initial_source_symptom_highlighted_en" => ($row['initial_source_symptom_highlighted_en'] != "") ? $row['initial_source_symptom_highlighted_en'] : "",
									"initial_source_symptom_en" => ($row['initial_source_symptom_en'] != "") ? $row['initial_source_symptom_en'] : "",
									"initial_source_symptom_before_conversion_highlighted_en" => ($row['initial_source_symptom_before_conversion_highlighted_en'] != "") ? $row['initial_source_symptom_before_conversion_highlighted_en'] : "",
									"initial_source_symptom_before_conversion_en" => ($row['initial_source_symptom_before_conversion_en'] != "") ? $row['initial_source_symptom_before_conversion_en'] : "",
									"initial_source_symptom_id" => ($row['initial_source_symptom_id'] != "") ? $row['initial_source_symptom_id'] : "",
									"main_parent_initial_symptom_id" => ($row['main_parent_initial_symptom_id'] != "") ? $row['main_parent_initial_symptom_id'] : "",
									"connections_main_parent_symptom_id" => ($row['connections_main_parent_symptom_id'] != "") ? $row['connections_main_parent_symptom_id'] : "",
									"initial_source_symptom_comment" => ($row['initial_source_symptom_comment'] != "") ? $row['initial_source_symptom_comment'] : "",
									"initial_source_symptom_footnote" => ($row['initial_source_symptom_footnote'] != "") ? $row['initial_source_symptom_footnote'] : "",
									"comparing_source_id" => ($row['comparing_source_id'] != "") ? $row['comparing_source_id'] : "",
									"comparing_original_source_id" => ($row['comparing_original_source_id'] != "") ? $row['comparing_original_source_id'] : "",
									"comparing_source_code" => ($row['comparing_source_code'] != "") ? $row['comparing_source_code'] : "",
									"comparing_source_year" => ($row['comparing_source_year'] != "") ? $row['comparing_source_year'] : "",
									"comparing_source_original_language" => ($row['comparing_source_original_language'] != "") ? $row['comparing_source_original_language'] : "",
									"comparing_saved_version_source_code" => ($row['comparing_saved_version_source_code'] != "") ? $row['comparing_saved_version_source_code'] : "",
									"comparing_source_symptom_highlighted_de" => ($row['comparing_source_symptom_highlighted_de'] != "") ? $row['comparing_source_symptom_highlighted_de'] : "",
									"comparing_source_symptom_de" => ($row['comparing_source_symptom_de'] != "") ? $row['comparing_source_symptom_de'] : "",
									"comparing_source_symptom_before_conversion_highlighted_de" => ($row['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? $row['comparing_source_symptom_before_conversion_highlighted_de'] : "",
									"comparing_source_symptom_before_conversion_de" => ($row['comparing_source_symptom_before_conversion_de'] != "") ? $row['comparing_source_symptom_before_conversion_de'] : "",
									"comparing_source_symptom_highlighted_en" => ($row['comparing_source_symptom_highlighted_en'] != "") ? $row['comparing_source_symptom_highlighted_en'] : "",
									"comparing_source_symptom_en" => ($row['comparing_source_symptom_en'] != "") ? $row['comparing_source_symptom_en'] : "",
									"comparing_source_symptom_before_conversion_highlighted_en" => ($row['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? $row['comparing_source_symptom_before_conversion_highlighted_en'] : "",
									"comparing_source_symptom_before_conversion_en" => ($row['comparing_source_symptom_before_conversion_en'] != "") ? $row['comparing_source_symptom_before_conversion_en'] : "",
									"comparing_source_symptom_id" => ($row['comparing_source_symptom_id'] != "") ? $row['comparing_source_symptom_id'] : "",
									"comparing_source_symptom_comment" => ($row['comparing_source_symptom_comment'] != "") ? $row['comparing_source_symptom_comment'] : "",
									"comparing_source_symptom_footnote" => ($row['comparing_source_symptom_footnote'] != "") ? $row['comparing_source_symptom_footnote'] : "",
									"comparison_language" => ($row['comparison_language'] != "") ? $row['comparison_language'] : "",
									// "main_initial_symptom_id" => $iniSymRow['id'],
									"has_connections" => ($row['has_connections'] != "") ? $row['has_connections'] : "",
									"is_final_version_available" => ($row['is_final_version_available'] != "") ? $row['is_final_version_available'] : "",
									"is_further_connections_are_saved" => ($row['is_further_connections_are_saved'] != "") ? $row['is_further_connections_are_saved'] : "",
									"should_swap_connect_be_active" => ($row['should_swap_connect_be_active'] != "") ? $row['should_swap_connect_be_active'] : 1,
									"is_pasted" => ($row['is_pasted'] != "") ? $row['is_pasted'] : 0,
									"is_ns_paste" => ($row['is_ns_paste'] != "") ? $row['is_ns_paste'] : 0,
									"ns_paste_note" => ($row['ns_paste_note'] != "") ? $row['ns_paste_note'] : "",
									"is_initial_source" => ($row['is_initial_source'] != "") ? $row['is_initial_source'] : 1,
									"active_symptom_type" => ($row['active_symptom_type'] != "") ? $row['active_symptom_type'] : "",
									"similarity_rate" => ($row['similarity_rate'] != "") ? $row['similarity_rate'] : "",
									"comparison_option" => ($row['comparison_option'] != "") ? $row['comparison_option'] : "",
									"is_unmatched_symptom" => ($row['is_unmatched_symptom'] != "") ? $row['is_unmatched_symptom'] : 0,
									"is_paste_disabled" => ($row['is_paste_disabled'] != "") ? $row['is_paste_disabled'] : "",
									"is_ns_paste_disabled" => ($row['is_ns_paste_disabled'] != "") ? $row['is_ns_paste_disabled'] : "",
									"is_connect_disabled" => ($row['is_connect_disabled'] != "") ? $row['is_connect_disabled'] : "",
									"is_ns_connect_disabled" => ($row['is_ns_connect_disabled'] != "") ? $row['is_ns_connect_disabled'] : ""
								);
							}else if($row['active_symptom_type'] == "comparing" AND $row['percentage'] >= $similarityRate){
								array_push($matchedSymptomIds, $row['comparing_source_symptom_id']);

								$compareResultArray[] = array(
									"no_of_match" => ($row['no_of_match'] != "") ? $row['no_of_match'] : 0,
									"percentage" => ($row['percentage'] != "") ? $row['percentage'] : 0,
									"comparison_initial_source_id" => ($row['comparison_initial_source_id'] != "") ? $row['comparison_initial_source_id'] : "",
									"source_arznei_id" => ($row['source_arznei_id'] != "") ? $row['source_arznei_id'] : "",
									"initial_source_id" => ($row['initial_source_id'] != "") ? $row['initial_source_id'] : "",
									"initial_original_source_id" => ($row['initial_original_source_id'] != "") ? $row['initial_original_source_id'] : "",
									"initial_source_code" => ($row['initial_source_code'] != "") ? $row['initial_source_code'] : "",
									"initial_source_year" => ($row['initial_source_year'] != "") ? $row['initial_source_year'] : "",
									"initial_source_original_language" => ($row['initial_source_original_language'] != "") ? $row['initial_source_original_language'] : "",
									"initial_saved_version_source_code" => ($row['initial_saved_version_source_code'] != "") ? $row['initial_saved_version_source_code'] : "",
									"initial_source_symptom_highlighted_de" => ($row['initial_source_symptom_highlighted_de'] != "") ? $row['initial_source_symptom_highlighted_de'] : "",
									"initial_source_symptom_de" => ($row['initial_source_symptom_de'] != "") ? $row['initial_source_symptom_de'] : "",
									"initial_source_symptom_before_conversion_highlighted_de" => ($row['initial_source_symptom_before_conversion_highlighted_de'] != "") ? $row['initial_source_symptom_before_conversion_highlighted_de'] : "",
									"initial_source_symptom_before_conversion_de" => ($row['initial_source_symptom_before_conversion_de'] != "") ? $row['initial_source_symptom_before_conversion_de'] : "",
									"initial_source_symptom_highlighted_en" => ($row['initial_source_symptom_highlighted_en'] != "") ? $row['initial_source_symptom_highlighted_en'] : "",
									"initial_source_symptom_en" => ($row['initial_source_symptom_en'] != "") ? $row['initial_source_symptom_en'] : "",
									"initial_source_symptom_before_conversion_highlighted_en" => ($row['initial_source_symptom_before_conversion_highlighted_en'] != "") ? $row['initial_source_symptom_before_conversion_highlighted_en'] : "",
									"initial_source_symptom_before_conversion_en" => ($row['initial_source_symptom_before_conversion_en'] != "") ? $row['initial_source_symptom_before_conversion_en'] : "",
									"initial_source_symptom_id" => ($row['initial_source_symptom_id'] != "") ? $row['initial_source_symptom_id'] : "",
									"main_parent_initial_symptom_id" => ($row['main_parent_initial_symptom_id'] != "") ? $row['main_parent_initial_symptom_id'] : "",
									"connections_main_parent_symptom_id" => ($row['connections_main_parent_symptom_id'] != "") ? $row['connections_main_parent_symptom_id'] : "",
									"initial_source_symptom_comment" => ($row['initial_source_symptom_comment'] != "") ? $row['initial_source_symptom_comment'] : "",
									"initial_source_symptom_footnote" => ($row['initial_source_symptom_footnote'] != "") ? $row['initial_source_symptom_footnote'] : "",
									"comparing_source_id" => ($row['comparing_source_id'] != "") ? $row['comparing_source_id'] : "",
									"comparing_original_source_id" => ($row['comparing_original_source_id'] != "") ? $row['comparing_original_source_id'] : "",
									"comparing_source_code" => ($row['comparing_source_code'] != "") ? $row['comparing_source_code'] : "",
									"comparing_source_year" => ($row['comparing_source_year'] != "") ? $row['comparing_source_year'] : "",
									"comparing_source_original_language" => ($row['comparing_source_original_language'] != "") ? $row['comparing_source_original_language'] : "",
									"comparing_saved_version_source_code" => ($row['comparing_saved_version_source_code'] != "") ? $row['comparing_saved_version_source_code'] : "",
									"comparing_source_symptom_highlighted_de" => ($row['comparing_source_symptom_highlighted_de'] != "") ? $row['comparing_source_symptom_highlighted_de'] : "",
									"comparing_source_symptom_de" => ($row['comparing_source_symptom_de'] != "") ? $row['comparing_source_symptom_de'] : "",
									"comparing_source_symptom_before_conversion_highlighted_de" => ($row['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? $row['comparing_source_symptom_before_conversion_highlighted_de'] : "",
									"comparing_source_symptom_before_conversion_de" => ($row['comparing_source_symptom_before_conversion_de'] != "") ? $row['comparing_source_symptom_before_conversion_de'] : "",
									"comparing_source_symptom_highlighted_en" => ($row['comparing_source_symptom_highlighted_en'] != "") ? $row['comparing_source_symptom_highlighted_en'] : "",
									"comparing_source_symptom_en" => ($row['comparing_source_symptom_en'] != "") ? $row['comparing_source_symptom_en'] : "",
									"comparing_source_symptom_before_conversion_highlighted_en" => ($row['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? $row['comparing_source_symptom_before_conversion_highlighted_en'] : "",
									"comparing_source_symptom_before_conversion_en" => ($row['comparing_source_symptom_before_conversion_en'] != "") ? $row['comparing_source_symptom_before_conversion_en'] : "",
									"comparing_source_symptom_id" => ($row['comparing_source_symptom_id'] != "") ? $row['comparing_source_symptom_id'] : "",
									"comparing_source_symptom_comment" => ($row['comparing_source_symptom_comment'] != "") ? $row['comparing_source_symptom_comment'] : "",
									"comparing_source_symptom_footnote" => ($row['comparing_source_symptom_footnote'] != "") ? $row['comparing_source_symptom_footnote'] : "",
									"comparison_language" => ($row['comparison_language'] != "") ? $row['comparison_language'] : "",
									// "main_initial_symptom_id" => $iniSymRow['id'],
									"has_connections" => ($row['has_connections'] != "") ? $row['has_connections'] : "",
									"is_final_version_available" => ($row['is_final_version_available'] != "") ? $row['is_final_version_available'] : "",
									"is_further_connections_are_saved" => ($row['is_further_connections_are_saved'] != "") ? $row['is_further_connections_are_saved'] : "",
									"should_swap_connect_be_active" => ($row['should_swap_connect_be_active'] != "") ? $row['should_swap_connect_be_active'] : 1,
									"is_pasted" => ($row['is_pasted'] != "") ? $row['is_pasted'] : 0,
									"is_ns_paste" => ($row['is_ns_paste'] != "") ? $row['is_ns_paste'] : 0,
									"ns_paste_note" => ($row['ns_paste_note'] != "") ? $row['ns_paste_note'] : "",
									"is_initial_source" => ($row['is_initial_source'] != "") ? $row['is_initial_source'] : 1,
									"active_symptom_type" => ($row['active_symptom_type'] != "") ? $row['active_symptom_type'] : "",
									"similarity_rate" => ($row['similarity_rate'] != "") ? $row['similarity_rate'] : "",
									"comparison_option" => ($row['comparison_option'] != "") ? $row['comparison_option'] : "",
									"is_unmatched_symptom" => ($row['is_unmatched_symptom'] != "") ? $row['is_unmatched_symptom'] : 0,
									"is_paste_disabled" => ($row['is_paste_disabled'] != "") ? $row['is_paste_disabled'] : "",
									"is_ns_paste_disabled" => ($row['is_ns_paste_disabled'] != "") ? $row['is_ns_paste_disabled'] : "",
									"is_connect_disabled" => ($row['is_connect_disabled'] != "") ? $row['is_connect_disabled'] : "",
									"is_ns_connect_disabled" => ($row['is_ns_connect_disabled'] != "") ? $row['is_ns_connect_disabled'] : ""
								);
							}
						}
					}
					
					$step = $step + 1;
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

											if($conRow['initial_source_id'] == $initialSource)
												$should_swap_connect_be_active = 0;
											
											if($conRow['initial_source_id'] == $initialSource OR $conRow['comparing_source_id'] == $initialSource){
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
										"comparison_initial_source_id" => ($initialSource != "") ? $initialSource : "",
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