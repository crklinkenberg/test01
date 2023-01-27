<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching the connections of a particular symptom which are related to the current comparison 
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if((isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") AND (isset($_POST['active_symptom_type']) AND $_POST['active_symptom_type'] != "")){	
			$comparisonInitialSourceId = (isset($_POST['comparison_initial_source_id']) AND $_POST['comparison_initial_source_id'] != "") ? trim($_POST['comparison_initial_source_id']) : null;
			$sourceArzneiId = (isset($_POST['source_arznei_id']) AND $_POST['source_arznei_id'] != "") ? trim($_POST['source_arznei_id']) : null;
			$comparingSymptomId = (isset($_POST['comparing_symptom_id']) AND $_POST['comparing_symptom_id'] != "") ? trim($_POST['comparing_symptom_id']) : null;
			$comparingSourceIds = (isset($_POST['comparing_source_ids']) AND $_POST['comparing_source_ids'] != "") ? trim($_POST['comparing_source_ids']) : null;
			$initialSourceId = (isset($_POST['initial_source_id']) AND $_POST['initial_source_id'] != "") ? trim($_POST['initial_source_id']) : null;
			$comparingSourceId = (isset($_POST['comparing_source_id']) AND $_POST['comparing_source_id'] != "") ? trim($_POST['comparing_source_id']) : null;
			$isRecompare = (isset($_POST['is_recompare']) AND $_POST['is_recompare'] != "") ? trim($_POST['is_recompare']) : null;
			$savedComparisonComparingSourceIds = (isset($_POST['saved_comparison_comparing_source_ids']) AND $_POST['saved_comparison_comparing_source_ids'] != "") ? trim($_POST['saved_comparison_comparing_source_ids']) : null;
			$mainParentInitialSymptomId = (isset($_POST['main_parent_initial_symptom_id']) AND $_POST['main_parent_initial_symptom_id'] != "") ? trim($_POST['main_parent_initial_symptom_id']) : null;
			$connectionsMainParentSymptomId = (isset($_POST['connections_main_parent_symptom_id']) AND $_POST['connections_main_parent_symptom_id'] != "") ? trim($_POST['connections_main_parent_symptom_id']) : null;
			
			$saved_comparison_quelle_id = (isset($_POST['saved_comparison_quelle_id']) AND $_POST['saved_comparison_quelle_id'] != "") ? trim($_POST['saved_comparison_quelle_id']) : null;
			$individual_comparison_language = (isset($_POST['individual_comparison_language']) AND $_POST['individual_comparison_language'] != "") ? trim($_POST['individual_comparison_language']) : "";
			if($saved_comparison_quelle_id != "")
			{
				$isQuelleAllowedToPerform = isQuelleAllowedToPerformThisAction($saved_comparison_quelle_id);
				if($isQuelleAllowedToPerform === false)
				{
					echo json_encode( array( 'status' => 'invalid', 'result_data' => $resultData, 'message' => $message) ); 
					exit;
				}
			}

			$activeSymptomType = trim($_POST['active_symptom_type']);
			if($activeSymptomType == "comparing")
			{
				$oppositeSymptomId = trim($_POST['initial_symptom_id']);
				$workingSymptomId = $comparingSymptomId;
				$workingSourceId = $comparingSourceId;
				$oppositeSourceId = $initialSourceId;
			}
			else
			{
				$oppositeSymptomId = $comparingSymptomId;
				$workingSymptomId = trim($_POST['initial_symptom_id']);
				$workingSourceId = $initialSourceId;
				$oppositeSourceId = $comparingSourceId;
			}

			$allComparedSourcers = array();
			$comparedSourcersOfInitialSource = array();
			$queryCondition = '';
			$queryConditionForComparative = '';
			$queryForCheckingFurtherConditions = '';
			$queryConditionForComparativeSymptoms = '';
			if($comparingSourceIds != ""){
				$workingSourceIdsArr = array();
				$workingSourceIdsArr = explode(',', $comparingSourceIds);
				if($comparisonInitialSourceId != "")
					array_push($workingSourceIdsArr, $comparisonInitialSourceId);

				if(!empty($workingSourceIdsArr)){
					$returnedIds = getAllComparedSourceIds($workingSourceIdsArr);
					if(!empty($returnedIds)){
						foreach ($returnedIds as $IdVal) {
							if(!in_array($IdVal, $workingSourceIdsArr))
								array_push($workingSourceIdsArr, $IdVal);
						}
					}	
				}

				// Collecting sinitial source's already compared sources ids of initial source
				$initialQuelleIdInArr = explode(',', $comparisonInitialSourceId);
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

				$moreEscapeableSourceIds = array();
				$workingSourceIdInArr = ($workingSourceId != "") ? explode(',', $workingSourceId) : array();
				if(!empty($workingSourceIdInArr)){
					$returnedIds = getAllComparedSourceIds($workingSourceIdInArr);
					if(!empty($returnedIds)){
						foreach ($returnedIds as $IdVal) {
							if(!in_array($IdVal, $moreEscapeableSourceIds))
								array_push($moreEscapeableSourceIds, $IdVal);
						}
					}	
				}
				
				$finalEscapeableSourceIds = array();
				foreach ($newComparedSourcersOfInitialSource as $newComparedSourceVal) {
					if(!in_array($newComparedSourceVal, $moreEscapeableSourceIds))
						array_push($finalEscapeableSourceIds, $newComparedSourceVal);	
				}

				$conditionIds = (!empty($workingSourceIdsArr)) ? rtrim(implode(',', $workingSourceIdsArr), ',') : null;
				$conditionIdsForComparative = (!empty($newComparedSourcersOfInitialSource)) ? rtrim(implode(',', $newComparedSourcersOfInitialSource), ',') : null;
				$sourceIdsForFurtherConnectionCheck = (!empty($finalEscapeableSourceIds)) ? rtrim(implode(',', $finalEscapeableSourceIds), ',') : null;
				if($conditionIds != "")
					$queryCondition .= " AND (initial_source_id IN (".$conditionIds.") AND comparing_source_id IN (".$conditionIds."))";
				if($conditionIdsForComparative != "")
					$queryConditionForComparative .= " AND (initial_source_id IN (".$conditionIdsForComparative.") AND comparing_source_id IN (".$conditionIdsForComparative."))";
				if($sourceIdsForFurtherConnectionCheck != "")
					$queryForCheckingFurtherConditions .= " AND (initial_source_id IN (".$sourceIdsForFurtherConnectionCheck.") AND comparing_source_id IN (".$sourceIdsForFurtherConnectionCheck."))";
			}
			if($sourceArzneiId != ""){
				$queryCondition .= " AND source_arznei_id = '".$sourceArzneiId."'"; 
				$queryConditionForComparative .= " AND source_arznei_id = '".$sourceArzneiId."'"; 
				$queryForCheckingFurtherConditions .= " AND source_arznei_id = '".$sourceArzneiId."'"; 
			}

			if($mainParentInitialSymptomId != ""){
				$queryConditionForComparativeSymptoms .= $queryConditionForComparative." AND (initial_source_symptom_id != '".$mainParentInitialSymptomId."' AND comparing_source_symptom_id != '".$mainParentInitialSymptomId."')";
				$queryForCheckingFurtherConditions .= " AND (initial_source_symptom_id != '".$mainParentInitialSymptomId."' AND comparing_source_symptom_id != '".$mainParentInitialSymptomId."')";
			}

			if($connectionsMainParentSymptomId != "" AND $workingSymptomId != $connectionsMainParentSymptomId){
				$queryConditionForComparativeSymptoms .= " AND (initial_source_symptom_id != '".$connectionsMainParentSymptomId."' AND comparing_source_symptom_id != '".$connectionsMainParentSymptomId."')";
				$queryForCheckingFurtherConditions .= " AND (initial_source_symptom_id != '".$connectionsMainParentSymptomId."' AND comparing_source_symptom_id != '".$connectionsMainParentSymptomId."')";
			}
			

			/*
			* Connect section
			*/
			if($oppositeSymptomId != "")
				$connectedSymptomResult = mysqli_query($db,"SELECT id, is_saved, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND (initial_source_symptom_id != '".$oppositeSymptomId."' AND comparing_source_symptom_id != '".$oppositeSymptomId."') AND is_connected = 1".$queryConditionForComparativeSymptoms." ORDER BY matching_percentage DESC");
			else
				$connectedSymptomResult = mysqli_query($db,"SELECT id, is_saved, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND is_connected = 1".$queryCondition." ORDER BY matching_percentage DESC");
			if(mysqli_num_rows($connectedSymptomResult) > 0){
				while($connectedRow = mysqli_fetch_array($connectedSymptomResult)) {
					if($connectedRow['is_saved'] == 1)
						$isSavedConnections = 1;
					else
						$isSavedConnections = 0;

					$comment = "";
					$footnote = "";
					if($workingSymptomId == $connectedRow['initial_source_symptom_id']){
						$inner_is_initial_source = 0;
						$innerInitialSymptomId = $connectedRow['initial_source_symptom_id'];
						$innerWorkingSymptomId = $connectedRow['comparing_source_symptom_id'];
					}
					else{
						$inner_is_initial_source = 1;
						$innerInitialSymptomId = $connectedRow['comparing_source_symptom_id'];
						$innerWorkingSymptomId = $connectedRow['initial_source_symptom_id'];
					}

					// Gtting the information of "is final vesrion available" of the active symptom 
					// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
					$is_final_version_available = 0;

					$symptomInfoResult = mysqli_query($db, "SELECT Kommentar, Fussnote, is_final_version_available FROM quelle_import_test WHERE id = '".$innerWorkingSymptomId."'");
					if(mysqli_num_rows($symptomInfoResult) > 0){
						$infoRow = mysqli_fetch_assoc($symptomInfoResult);
						$comment = (isset($infoRow['Kommentar']) AND $infoRow['Kommentar'] != "") ? $infoRow['Kommentar'] : "";
						$footnote = (isset($infoRow['Fussnote']) AND $infoRow['Fussnote'] != "") ? $infoRow['Fussnote'] : "";
						$is_final_version_available = (isset($infoRow['is_final_version_available']) AND $infoRow['is_final_version_available'] != "") ? $infoRow['is_final_version_available'] : 0;
					}

					$comHasConnections = 0;
					$isFurtherConnectionsAreSaved = 1;
					$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections WHERE (initial_source_symptom_id = '".$innerWorkingSymptomId."' OR comparing_source_symptom_id = '".$innerWorkingSymptomId."') AND (initial_source_symptom_id != '".$innerInitialSymptomId."' AND comparing_source_symptom_id != '".$innerInitialSymptomId."') AND id != '".$connectedRow['id']."' AND (is_connected = 1 OR is_pasted = 1)".$queryForCheckingFurtherConditions);
					if(mysqli_num_rows($ceheckConnectionResult) > 0){
						$comHasConnections = 1;
						while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
							if($checkConRow['is_saved'] == 0){
								$isFurtherConnectionsAreSaved = 0;
								break;
							}
						}
					}

					// Fetching saved version source codes
					$initial_source_code = "";
					$comparing_source_code = "";
					// $initial_source_jahr = "";
					// $comparing_source_jahr = "";
					if($connectedRow['initial_source_id'] != ""){
						$InitialSymptomResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle WHERE quelle_id = '".$connectedRow['initial_source_id']."'");
						if(mysqli_num_rows($InitialSymptomResult) > 0){
							$iniSymRow = mysqli_fetch_assoc($InitialSymptomResult);
							if($iniSymRow['quelle_type_id'] == 3)
								$preparedQuelleCode = $iniSymRow['code'];
							else{
								if($iniSymRow['jahr'] != "" AND $iniSymRow['code'] != "")
									$rowQuelleCode = trim(str_replace(trim($iniSymRow['jahr']), '', $iniSymRow['code']));
								else
									$rowQuelleCode = trim($iniSymRow['code']);
								$preparedQuelleCode = trim($rowQuelleCode." ".$iniSymRow['jahr']);
							}

							$initial_source_code = ($preparedQuelleCode != "") ? $preparedQuelleCode : "";
							// $initial_source_jahr = $iniSymRow['jahr'];
						}
					}

					if($connectedRow['comparing_source_id'] != ""){
						$comparingSymptomResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle WHERE quelle_id = '".$connectedRow['comparing_source_id']."'");
						if(mysqli_num_rows($comparingSymptomResult) > 0){
							$comparingSymptomRow = mysqli_fetch_assoc($comparingSymptomResult);
							if($comparingSymptomRow['quelle_type_id'] == 3)
								$preparedQuelleCodeForCom = $comparingSymptomRow['code'];
							else{
								if($comparingSymptomRow['jahr'] != "" AND $comparingSymptomRow['code'] != "")
									$rowQuelleCodeForCom = trim(str_replace(trim($comparingSymptomRow['jahr']), '', $comparingSymptomRow['code']));
								else
									$rowQuelleCodeForCom = trim($comparingSymptomRow['code']);
								$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingSymptomRow['jahr']);
							}

							$comparing_source_code = ($preparedQuelleCodeForCom != "") ? $preparedQuelleCodeForCom : "";
							// $comparing_source_jahr = $comparingSymptomRow['jahr'];
						}
					}

					// get Origin Jahr/Year
					$originInitialSourceYear = "";
					$iniIsFinalVersionAvailable = 0;
					$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.is_final_version_available FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.original_quelle_id = quelle.quelle_id WHERE quelle_import_test.id = '".$connectedRow['initial_source_symptom_id']."'");
					if(mysqli_num_rows($originInitialQuelleResult) > 0){
						$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
						$originInitialSourceYear = $originIniQuelleRow['jahr'];
						$iniIsFinalVersionAvailable = $originIniQuelleRow['is_final_version_available'];
						// $initialOrzId = $originIniQuelleRow['original_quelle_id'];
						// $initialArzId = $originIniQuelleRow['arznei_id'];
					}
					$iniSymId = (isset($originIniQuelleRow['id']) AND $originIniQuelleRow['id'] != "") ? $originIniQuelleRow['id'] : "";
					$iniOriginalSymId = (isset($originIniQuelleRow['original_symptom_id']) AND $originIniQuelleRow['original_symptom_id'] != "") ? $originIniQuelleRow['original_symptom_id'] : "";

					$originComparingSourceYear = "";
					$comIsFinalVersionAvailable = 0;
					$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.is_final_version_available FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.original_quelle_id = quelle.quelle_id WHERE quelle_import_test.id = '".$connectedRow['comparing_source_symptom_id']."'");
					if(mysqli_num_rows($originComparingQuelleResult) > 0){
						$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
						$originComparingSourceYear = $originComQuelleRow['jahr'];
						$comIsFinalVersionAvailable = $originComQuelleRow['is_final_version_available'];
						// $comparingOrzId = $originComQuelleRow['original_quelle_id'];
						// $comparingArzId = $originComQuelleRow['arznei_id'];
					}
					$comSymId = (isset($originComQuelleRow['id']) AND $originComQuelleRow['id'] != "") ? $originComQuelleRow['id'] : "";
					$comOriginalSymId = (isset($originComQuelleRow['original_symptom_id']) AND $originComQuelleRow['original_symptom_id'] != "") ? $originComQuelleRow['original_symptom_id'] : "";

					$initialOrigianlSourceOriginalLanguage = "";
					$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$connectedRow['conversion_initial_source_id']."'");
					if(mysqli_num_rows($originInitialQuelleResult) > 0){
						$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
						if($originIniQuelleRow['sprache'] == "deutsch")
							$initialOrigianlSourceOriginalLanguage = "de";
						else if($originIniQuelleRow['sprache'] == "englisch") 
							$initialOrigianlSourceOriginalLanguage = "en";
					}

					$comparingOrigianlSourceOriginalLanguage = "";
					$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$connectedRow['conversion_comparing_source_id']."'");
					if(mysqli_num_rows($originComparingQuelleResult) > 0){
						$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
						if($originComQuelleRow['sprache'] == "deutsch")
							$comparingOrigianlSourceOriginalLanguage = "de";
						else if($originComQuelleRow['sprache'] == "englisch") 
							$comparingOrigianlSourceOriginalLanguage = "en";
					}

					$initialOrzId = $connectedRow['conversion_initial_source_id']; // Original source id
					$initialArzId = $connectedRow['source_arznei_id'];
					$comparingOrzId = $connectedRow['conversion_comparing_source_id']; // Original source id
					$comparingArzId = $connectedRow['source_arznei_id'];

					// initial source symptom string
					$initial_source_symptom_de = (isset($connectedRow['initial_source_symptom_de'])) ? $connectedRow['initial_source_symptom_de'] : "";
					$initial_source_symptom_en = (isset($connectedRow['initial_source_symptom_en'])) ? $connectedRow['initial_source_symptom_en'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringBeforeConversion_de = ($initial_source_symptom_de != "") ? base64_encode($initial_source_symptom_de) : "";
					$iniSymptomStringBeforeConversion_en = ($initial_source_symptom_en != "") ? base64_encode($initial_source_symptom_en) : "";
					// Apply dynamic conversion
					$iniSymptomString_de = ($initial_source_symptom_de != "") ? convertTheSymptom($initial_source_symptom_de, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomString_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
					$iniSymptomString_en = ($initial_source_symptom_en != "") ? convertTheSymptom($initial_source_symptom_en, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomString_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

					// initial source symptom string highlighted
					$initial_source_symptom_highlighted_de = (isset($connectedRow['initial_source_symptom_highlighted_de']) AND $connectedRow['initial_source_symptom_highlighted_de'] != "") ? $connectedRow['initial_source_symptom_highlighted_de'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringHighlightedBeforeConversion_de = ($initial_source_symptom_highlighted_de != "") ? base64_encode($initial_source_symptom_highlighted_de) : "";
					// initial source symptom string highlighted
					$initial_source_symptom_highlighted_en = (isset($connectedRow['initial_source_symptom_highlighted_en']) AND $connectedRow['initial_source_symptom_highlighted_en'] != "") ? $connectedRow['initial_source_symptom_highlighted_en'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringHighlightedBeforeConversion_en = ($initial_source_symptom_highlighted_en != "") ? base64_encode($initial_source_symptom_highlighted_en) : "";

					// Apply dynamic conversion
					$iniSymptomStringHighlighted_de = ($initial_source_symptom_highlighted_de != "") ? convertTheSymptom($initial_source_symptom_highlighted_de, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomStringHighlighted_de = ($iniSymptomStringHighlighted_de != "") ? base64_encode($iniSymptomStringHighlighted_de) : "";
					$iniSymptomStringHighlighted_en = ($initial_source_symptom_highlighted_en != "") ? convertTheSymptom($initial_source_symptom_highlighted_en, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomStringHighlighted_en = ($iniSymptomStringHighlighted_en != "") ? base64_encode($iniSymptomStringHighlighted_en) : "";

					// comparing source symptom string
					$comparing_source_symptom_de = (isset($connectedRow['comparing_source_symptom_de']) AND $connectedRow['comparing_source_symptom_de'] != "") ? $connectedRow['comparing_source_symptom_de'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringBeforeConversion_de = ($comparing_source_symptom_de != "") ? base64_encode($comparing_source_symptom_de) : "";
					// comparing source symptom string
					$comparing_source_symptom_en = (isset($connectedRow['comparing_source_symptom_en']) AND $connectedRow['comparing_source_symptom_en'] != "") ? $connectedRow['comparing_source_symptom_en'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringBeforeConversion_en = ($comparing_source_symptom_en != "") ? base64_encode($comparing_source_symptom_en) : "";
					
					// Apply dynamic conversion
					$comSymptomString_de = ($comparing_source_symptom_de != "") ? convertTheSymptom($comparing_source_symptom_de, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomString_de = ($comSymptomString_de != "") ? base64_encode($comSymptomString_de) : "";
					$comSymptomString_en = ($comparing_source_symptom_en != "") ? convertTheSymptom($comparing_source_symptom_en, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomString_en = ($comSymptomString_en != "") ? base64_encode($comSymptomString_en) : "";

					// comparing source symptom string highlighted
					$comparing_source_symptom_highlighted_de = (isset($connectedRow['comparing_source_symptom_highlighted_de']) AND $connectedRow['comparing_source_symptom_highlighted_de'] != "") ? $connectedRow['comparing_source_symptom_highlighted_de'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringHighlightedBeforeConversion_de = ($comparing_source_symptom_highlighted_de != "") ? base64_encode($comparing_source_symptom_highlighted_de) : "";
					$comparing_source_symptom_highlighted_en = (isset($connectedRow['comparing_source_symptom_highlighted_en']) AND $connectedRow['comparing_source_symptom_highlighted_en'] != "") ? $connectedRow['comparing_source_symptom_highlighted_en'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringHighlightedBeforeConversion_en = ($comparing_source_symptom_highlighted_en != "") ? base64_encode($comparing_source_symptom_highlighted_en) : "";

					// Apply dynamic conversion
					$comSymptomStringHighlighted_de = ($comparing_source_symptom_highlighted_de != "") ? convertTheSymptom($comparing_source_symptom_highlighted_de, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomStringHighlighted_de = ($comSymptomStringHighlighted_de != "") ? base64_encode($comSymptomStringHighlighted_de) : "";
					$comSymptomStringHighlighted_en = ($comparing_source_symptom_highlighted_en != "") ? convertTheSymptom($comparing_source_symptom_highlighted_en, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomStringHighlighted_en = ($comSymptomStringHighlighted_en != "") ? base64_encode($comSymptomStringHighlighted_en) : "";

					$active_source_jahr = ($inner_is_initial_source == 1) ? $originInitialSourceYear : $originComparingSourceYear;

					$data = array();
					$data['id'] = $connectedRow['id'];
					$data['initial_source_id'] = $connectedRow['initial_source_id'];
					$data['initial_original_source_id'] = $connectedRow['conversion_initial_source_id'];
					$data['initial_source_original_language'] = $initialOrigianlSourceOriginalLanguage; // initial origial sources original language
					$data['comparing_source_id'] = $connectedRow['comparing_source_id'];
					$data['comparing_original_source_id'] = $connectedRow['conversion_comparing_source_id'];
					$data['comparing_source_original_language'] = $comparingOrigianlSourceOriginalLanguage; // Comparing original source original language
					$data['initial_source_code'] = $connectedRow['initial_source_code'];
					$data['initial_source_year'] = $originInitialSourceYear;
					$data['initial_saved_version_source_code'] = $initial_source_code;
					$data['comparing_source_code'] = $connectedRow['comparing_source_code'];
					$data['comparing_source_year'] = $originComparingSourceYear;
					$data['comparing_saved_version_source_code'] = $comparing_source_code;
					$data['initial_source_symptom_id'] = $connectedRow['initial_source_symptom_id'];
					$data['comparing_source_symptom_id'] = $connectedRow['comparing_source_symptom_id'];

					$data['initial_source_symptom_before_conversion_highlighted_de'] = $iniSymptomStringHighlightedBeforeConversion_de;
					$data['initial_source_symptom_before_conversion_highlighted_en'] = $iniSymptomStringHighlightedBeforeConversion_en;
					$data['initial_source_symptom_before_conversion_de'] = $iniSymptomStringBeforeConversion_de;
					$data['initial_source_symptom_before_conversion_en'] = $iniSymptomStringBeforeConversion_en;
					$data['comparing_source_symptom_before_conversion_highlighted_de'] = $comSymptomStringHighlightedBeforeConversion_de;
					$data['comparing_source_symptom_before_conversion_highlighted_en'] = $comSymptomStringHighlightedBeforeConversion_en;
					$data['comparing_source_symptom_before_conversion_de'] = $comSymptomStringBeforeConversion_de;
					$data['comparing_source_symptom_before_conversion_en'] = $comSymptomStringBeforeConversion_en;

					$data['initial_source_symptom_highlighted_de'] = $iniSymptomStringHighlighted_de;
					$data['initial_source_symptom_highlighted_en'] = $iniSymptomStringHighlighted_en;
					$data['comparing_source_symptom_highlighted_de'] = $comSymptomStringHighlighted_de;
					$data['comparing_source_symptom_highlighted_en'] = $comSymptomStringHighlighted_en;
					$data['initial_source_symptom_de'] = $iniSymptomString_de;
					$data['initial_source_symptom_en'] = $iniSymptomString_en;
					$data['comparing_source_symptom_de'] = $comSymptomString_de;
					$data['comparing_source_symptom_en'] = $comSymptomString_en;
					
					$data['comparison_language'] = $individual_comparison_language;
					$data['connection_language'] = $connectedRow['connection_language'];
					
					$data['matching_percentage'] = $connectedRow['matching_percentage'];
					$data['is_connected'] = $connectedRow['is_connected'];
					$data['is_ns_connect'] = $connectedRow['is_ns_connect'];
					$data['ns_connect_note'] = $connectedRow['ns_connect_note'];
					$data['is_pasted'] = $connectedRow['is_pasted'];
					$data['is_ns_paste'] = $connectedRow['is_ns_paste'];
					$data['ns_paste_note'] = $connectedRow['ns_paste_note'];
					$data['initial_source_symptom_comment'] = ($inner_is_initial_source == 1) ? $comment : "";
					$data['initial_source_symptom_footnote'] = ($inner_is_initial_source == 1) ? $footnote : "";
					$data['comparing_source_symptom_comment'] = ($inner_is_initial_source == 0) ? $comment : "";
					$data['comparing_source_symptom_footnote'] = ($inner_is_initial_source == 0) ? $footnote : "";
					$data['has_connections'] = $comHasConnections;
					$data['is_final_version_available'] = $is_final_version_available;
					$data['is_further_connections_are_saved'] = $isFurtherConnectionsAreSaved;
					$data['connection_or_paste_type'] = $connectedRow['connection_or_paste_type'];
					$data['is_saved_connections'] = $isSavedConnections;
					$data['is_initial_source'] = $inner_is_initial_source;
					$data['active_source_jahr'] = $active_source_jahr;
					$data['source_test'] = $finalEscapeableSourceIds;
					$data['workingSourceId'] = $workingSourceId;
					$resultData [] = $data;
				}

				// Short the matched symptoms chronological jahr/year ASC
				$order_by_jahr = array();
				foreach ($resultData as $key => $row)
				{
				    $order_by_jahr[$key] = $row['active_source_jahr'];
				}
				array_multisort($order_by_jahr, SORT_ASC, $resultData);
			}

			/*
			* Paste section
			*/
			$pasteResultData = array();
			$escapeQueryCondition = "";
			$savedComparisonComparingSourceIdArr = ($savedComparisonComparingSourceIds != "") ? explode(',', $savedComparisonComparingSourceIds) : array();
			if(!empty($savedComparisonComparingSourceIdArr)){
				$commaSeparatedSavedComparingSources = implode(',', $savedComparisonComparingSourceIdArr);
				$escapeQueryCondition = ' AND comparing_source_id NOT IN ('.$commaSeparatedSavedComparingSources.')'; 
			}
			if($oppositeSymptomId != "")
				$pastedSymptomResult = mysqli_query($db,"SELECT id, is_saved, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND (initial_source_symptom_id != '".$oppositeSymptomId."' AND comparing_source_symptom_id != '".$oppositeSymptomId."') AND is_pasted = 1".$queryConditionForComparativeSymptoms." ORDER BY matching_percentage DESC");
			else
				$pastedSymptomResult = mysqli_query($db,"SELECT id, is_saved, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$workingSymptomId."' OR comparing_source_symptom_id = '".$workingSymptomId."') AND is_pasted = 1".$escapeQueryCondition.$queryCondition." ORDER BY matching_percentage DESC");
			if(mysqli_num_rows($pastedSymptomResult) > 0){
				while($pastedRow = mysqli_fetch_array($pastedSymptomResult)) {
					if($pastedRow['is_saved'] == 1)
						$isSavedConnections = 1;
					else
						$isSavedConnections = 0;

					$comment = "";
					$footnote = "";
					if($workingSymptomId == $pastedRow['initial_source_symptom_id']) {
						$inner_is_initial_source = 0;
						$innerInitialSymptomId = $pastedRow['initial_source_symptom_id'];
						$innerWorkingSymptomId = $pastedRow['comparing_source_symptom_id'];
					} else {
						$inner_is_initial_source = 1;
						$innerInitialSymptomId = $pastedRow['comparing_source_symptom_id'];
						$innerWorkingSymptomId = $pastedRow['initial_source_symptom_id'];
					}
					// Gtting the information of "is final vesrion available" of the active symptom 
					// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
					$is_final_version_available = 0;

					$symptomInfoResult = mysqli_query($db, "SELECT Kommentar, Fussnote, is_final_version_available FROM quelle_import_test WHERE id = '".$innerWorkingSymptomId."'");
					if(mysqli_num_rows($symptomInfoResult) > 0){
						$infoRow = mysqli_fetch_assoc($symptomInfoResult);
						$comment = (isset($infoRow['Kommentar']) AND $infoRow['Kommentar'] != "") ? $infoRow['Kommentar'] : "";
						$footnote = (isset($infoRow['Fussnote']) AND $infoRow['Fussnote'] != "") ? $infoRow['Fussnote'] : "";
						$is_final_version_available = (isset($infoRow['is_final_version_available']) AND $infoRow['is_final_version_available'] != "") ? $infoRow['is_final_version_available'] : 0;
					}

					$comHasConnections = 0;
					$isFurtherConnectionsAreSaved = 1;
					$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved, is_connected, is_pasted, initial_source_id, comparing_source_id FROM symptom_connections WHERE (initial_source_symptom_id = '".$innerWorkingSymptomId."' OR comparing_source_symptom_id = '".$innerWorkingSymptomId."') AND (initial_source_symptom_id != '".$innerInitialSymptomId."' AND comparing_source_symptom_id != '".$innerInitialSymptomId."') AND id != '".$pastedRow['id']."' AND (is_connected = 1 OR is_pasted = 1)".$queryForCheckingFurtherConditions);
					if(mysqli_num_rows($ceheckConnectionResult) > 0){
						$comHasConnections = 1;
						while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
							if($checkConRow['is_saved'] == 0){
								$isFurtherConnectionsAreSaved = 0;
								break;
							}
						}
					}

					/*$initial_source_symptom_highlighted = (isset($pastedRow['initial_source_symptom_highlighted'])) ? base64_encode($pastedRow['initial_source_symptom_highlighted']) : "";
					// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
					$comparing_source_symptom_highlighted = (isset($pastedRow['comparing_source_symptom_highlighted'])) ? base64_encode($pastedRow['comparing_source_symptom_highlighted']) : "";
					// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);*/

					// Fetching saved version source codes
					$initial_source_code = "";
					$comparing_source_code = "";
					// $initial_source_jahr = "";
					// $comparing_source_jahr = "";
					if($pastedRow['initial_source_id'] != ""){
						$InitialSymptomResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle WHERE quelle_id = '".$pastedRow['initial_source_id']."'");
						if(mysqli_num_rows($InitialSymptomResult) > 0){
							$iniSymRow = mysqli_fetch_assoc($InitialSymptomResult);
							if($iniSymRow['quelle_type_id'] == 3)
								$preparedQuelleCode = $iniSymRow['code'];
							else{
								if($iniSymRow['jahr'] != "" AND $iniSymRow['code'] != "")
									$rowQuelleCode = trim(str_replace(trim($iniSymRow['jahr']), '', $iniSymRow['code']));
								else
									$rowQuelleCode = trim($iniSymRow['code']);
								$preparedQuelleCode = trim($rowQuelleCode." ".$iniSymRow['jahr']);
							}

							$initial_source_code = ($preparedQuelleCode != "") ? $preparedQuelleCode : "";
							// $initial_source_jahr = $iniSymRow['jahr'];
						}
					}
					if($pastedRow['comparing_source_id'] != ""){
						$comparingSymptomResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle WHERE quelle_id = '".$pastedRow['comparing_source_id']."'");
						if(mysqli_num_rows($comparingSymptomResult) > 0){
							$comparingSymptomRow = mysqli_fetch_assoc($comparingSymptomResult);
							if($comparingSymptomRow['quelle_type_id'] == 3)
								$preparedQuelleCodeForCom = $comparingSymptomRow['code'];
							else{
								if($comparingSymptomRow['jahr'] != "" AND $comparingSymptomRow['code'] != "")
									$rowQuelleCodeForCom = trim(str_replace(trim($comparingSymptomRow['jahr']), '', $comparingSymptomRow['code']));
								else
									$rowQuelleCodeForCom = trim($comparingSymptomRow['code']);
								$preparedQuelleCodeForCom = trim($rowQuelleCodeForCom." ".$comparingSymptomRow['jahr']);
							}

							$comparing_source_code = ($preparedQuelleCodeForCom != "") ? $preparedQuelleCodeForCom : "";
							// $comparing_source_jahr = $comparingSymptomRow['jahr'];
						}
					}

					// get Origin Jahr/Year
					$originInitialSourceYear = "";
					$iniIsFinalVersionAvailable = 0;
					$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.is_final_version_available FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.original_quelle_id = quelle.quelle_id WHERE quelle_import_test.id = '".$pastedRow['initial_source_symptom_id']."'");
					if(mysqli_num_rows($originInitialQuelleResult) > 0){
						$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
						$originInitialSourceYear = $originIniQuelleRow['jahr'];
						$iniIsFinalVersionAvailable = $originIniQuelleRow['is_final_version_available'];
						// $initialOrzId = $originIniQuelleRow['original_quelle_id'];
						// $initialArzId = $originIniQuelleRow['arznei_id'];
					}
					$iniSymId = (isset($originIniQuelleRow['id']) AND $originIniQuelleRow['id'] != "") ? $originIniQuelleRow['id'] : "";
					$iniOriginalSymId = (isset($originIniQuelleRow['original_symptom_id']) AND $originIniQuelleRow['original_symptom_id'] != "") ? $originIniQuelleRow['original_symptom_id'] : "";

					$originComparingSourceYear = "";
					$comIsFinalVersionAvailable = 0;
					$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.is_final_version_available FROM quelle_import_test LEFT JOIN quelle ON quelle_import_test.original_quelle_id = quelle.quelle_id WHERE quelle_import_test.id = '".$pastedRow['comparing_source_symptom_id']."'");
					if(mysqli_num_rows($originComparingQuelleResult) > 0){
						$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
						$originComparingSourceYear = $originComQuelleRow['jahr'];
						$comIsFinalVersionAvailable = $originComQuelleRow['is_final_version_available'];
						// $comparingOrzId = $originComQuelleRow['original_quelle_id'];
						// $comparingArzId = $originComQuelleRow['arznei_id'];
					}
					$comSymId = (isset($originComQuelleRow['id']) AND $originComQuelleRow['id'] != "") ? $originComQuelleRow['id'] : "";
					$comOriginalSymId = (isset($originComQuelleRow['original_symptom_id']) AND $originComQuelleRow['original_symptom_id'] != "") ? $originComQuelleRow['original_symptom_id'] : "";

					$initialOrigianlSourceOriginalLanguage = "";
					$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$pastedRow['conversion_initial_source_id']."'");
					if(mysqli_num_rows($originInitialQuelleResult) > 0){
						$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
						if($originIniQuelleRow['sprache'] == "deutsch")
							$initialOrigianlSourceOriginalLanguage = "de";
						else if($originIniQuelleRow['sprache'] == "englisch") 
							$initialOrigianlSourceOriginalLanguage = "en";
					}

					$comparingOrigianlSourceOriginalLanguage = "";
					$originComparingQuelleResult = mysqli_query($db,"SELECT quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$pastedRow['conversion_comparing_source_id']."'");
					if(mysqli_num_rows($originComparingQuelleResult) > 0){
						$originComQuelleRow = mysqli_fetch_assoc($originComparingQuelleResult);
						if($originComQuelleRow['sprache'] == "deutsch")
							$comparingOrigianlSourceOriginalLanguage = "de";
						else if($originComQuelleRow['sprache'] == "englisch") 
							$comparingOrigianlSourceOriginalLanguage = "en";
					}

					$initialOrzId = $pastedRow['conversion_initial_source_id'];
					$initialArzId = $pastedRow['source_arznei_id'];
					$comparingOrzId = $pastedRow['conversion_comparing_source_id'];
					$comparingArzId = $pastedRow['source_arznei_id'];

					// initial source symptom string
					$initial_source_symptom_de = (isset($pastedRow['initial_source_symptom_de']) AND $pastedRow['initial_source_symptom_de'] != "") ? $pastedRow['initial_source_symptom_de'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringBeforeConversion_de = ($initial_source_symptom_de != "") ? base64_encode($initial_source_symptom_de) : "";
					$initial_source_symptom_en = (isset($pastedRow['initial_source_symptom_en']) AND $pastedRow['initial_source_symptom_en'] != "") ? $pastedRow['initial_source_symptom_en'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringBeforeConversion_en = ($initial_source_symptom_en != "") ? base64_encode($initial_source_symptom_en) : "";
					// Apply dynamic conversion
					$iniSymptomString_de = ($initial_source_symptom_de != "") ? convertTheSymptom($initial_source_symptom_de, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomString_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
					$iniSymptomString_en = ($initial_source_symptom_en != "") ? convertTheSymptom($initial_source_symptom_en, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomString_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

					// initial source symptom string highlighted
					$initial_source_symptom_highlighted_de = (isset($pastedRow['initial_source_symptom_highlighted_de']) AND $pastedRow['initial_source_symptom_highlighted_de'] != "") ? $pastedRow['initial_source_symptom_highlighted_de'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringHighlightedBeforeConversion_de = ($initial_source_symptom_highlighted_de != "") ? base64_encode($initial_source_symptom_highlighted_de) : "";
					$initial_source_symptom_highlighted_en = (isset($pastedRow['initial_source_symptom_highlighted_en']) AND $pastedRow['initial_source_symptom_highlighted_en'] != "") ? $pastedRow['initial_source_symptom_highlighted_en'] : "";
					// initial source symptom string Bfore convertion
					$iniSymptomStringHighlightedBeforeConversion_en = ($initial_source_symptom_highlighted_en != "") ? base64_encode($initial_source_symptom_highlighted_en) : "";
					
					// Apply dynamic conversion
					$iniSymptomStringHighlighted_de = ($initial_source_symptom_highlighted_de != "") ? convertTheSymptom($initial_source_symptom_highlighted_de, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomStringHighlighted_de = ($iniSymptomStringHighlighted_de != "") ? base64_encode($iniSymptomStringHighlighted_de) : "";
					$iniSymptomStringHighlighted_en = ($initial_source_symptom_highlighted_en != "") ? convertTheSymptom($initial_source_symptom_highlighted_en, $initialOrzId, $initialArzId, $iniIsFinalVersionAvailable, 0, $iniSymId, $iniOriginalSymId) : "";
					$iniSymptomStringHighlighted_en = ($iniSymptomStringHighlighted_en != "") ? base64_encode($iniSymptomStringHighlighted_en) : "";

					// comparing source symptom string
					$comparing_source_symptom_de = (isset($pastedRow['comparing_source_symptom_de']) AND $pastedRow['comparing_source_symptom_de'] != "") ? $pastedRow['comparing_source_symptom_de'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringBeforeConversion_de = ($comparing_source_symptom_de != "") ? base64_encode($comparing_source_symptom_de) : "";
					$comparing_source_symptom_en = (isset($pastedRow['comparing_source_symptom_en']) AND $pastedRow['comparing_source_symptom_en'] != "") ? $pastedRow['comparing_source_symptom_en'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringBeforeConversion_en = ($comparing_source_symptom_en != "") ? base64_encode($comparing_source_symptom_en) : "";

					// Apply dynamic conversion
					$comSymptomString_de = ($comparing_source_symptom_de != "") ? convertTheSymptom($comparing_source_symptom_de, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomString_de = ($comSymptomString_de != "") ? base64_encode($comSymptomString_de) : "";
					$comSymptomString_en = ($comparing_source_symptom_en != "") ? convertTheSymptom($comparing_source_symptom_en, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomString_en = ($comSymptomString_en != "") ? base64_encode($comSymptomString_en) : "";

					// comparing source symptom string highlighted
					$comparing_source_symptom_highlighted_de = (isset($pastedRow['comparing_source_symptom_highlighted_de']) AND $pastedRow['comparing_source_symptom_highlighted_de'] != "") ? $pastedRow['comparing_source_symptom_highlighted_de'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringHighlightedBeforeConversion_de = ($comparing_source_symptom_highlighted_de != "") ? base64_encode($comparing_source_symptom_highlighted_de) : "";
					$comparing_source_symptom_highlighted_en = (isset($pastedRow['comparing_source_symptom_highlighted_en']) AND $pastedRow['comparing_source_symptom_highlighted_en'] != "") ? $pastedRow['comparing_source_symptom_highlighted_en'] : "";
					// initial source symptom string Bfore convertion
					$comSymptomStringHighlightedBeforeConversion_en = ($comparing_source_symptom_highlighted_en != "") ? base64_encode($comparing_source_symptom_highlighted_en) : "";
					
					// Apply dynamic conversion
					$comSymptomStringHighlighted_de = ($comparing_source_symptom_highlighted_de != "") ? convertTheSymptom($comparing_source_symptom_highlighted_de, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomStringHighlighted_de = ($comSymptomStringHighlighted_de != "") ? base64_encode($comSymptomStringHighlighted_de) : "";
					$comSymptomStringHighlighted_en = ($comparing_source_symptom_highlighted_en != "") ? convertTheSymptom($comparing_source_symptom_highlighted_en, $comparingOrzId, $comparingArzId, $comIsFinalVersionAvailable, 0, $comSymId, $comOriginalSymId) : "";
					$comSymptomStringHighlighted_en = ($comSymptomStringHighlighted_en != "") ? base64_encode($comSymptomStringHighlighted_en) : "";

					$active_source_jahr = ($inner_is_initial_source == 1) ? $originInitialSourceYear : $originComparingSourceYear;

					$data = array();
					$data['id'] = $pastedRow['id'];
					$data['initial_source_id'] = $pastedRow['initial_source_id'];
					$data['initial_original_source_id'] = $pastedRow['conversion_initial_source_id'];
					$data['initial_source_original_language'] = $initialOrigianlSourceOriginalLanguage; // initial origial sources original language
					$data['comparing_source_id'] = $pastedRow['comparing_source_id'];
					$data['comparing_original_source_id'] = $pastedRow['conversion_comparing_source_id'];
					$data['comparing_source_original_language'] = $comparingOrigianlSourceOriginalLanguage; // Comparing original source original language
					$data['initial_source_code'] = $pastedRow['initial_source_code'];
					$data['initial_source_year'] = $originInitialSourceYear;
					$data['initial_saved_version_source_code'] = $initial_source_code;
					$data['comparing_source_code'] = $pastedRow['comparing_source_code'];
					$data['comparing_source_year'] = $originComparingSourceYear;
					$data['comparing_saved_version_source_code'] = $comparing_source_code;
					$data['initial_source_symptom_id'] = $pastedRow['initial_source_symptom_id'];
					$data['comparing_source_symptom_id'] = $pastedRow['comparing_source_symptom_id'];

					$data['initial_source_symptom_before_conversion_highlighted_de'] = $iniSymptomStringHighlightedBeforeConversion_de;
					$data['initial_source_symptom_before_conversion_highlighted_en'] = $iniSymptomStringHighlightedBeforeConversion_en;
					$data['initial_source_symptom_before_conversion_de'] = $iniSymptomStringBeforeConversion_de;
					$data['initial_source_symptom_before_conversion_en'] = $iniSymptomStringBeforeConversion_en;
					$data['comparing_source_symptom_before_conversion_highlighted_de'] = $comSymptomStringHighlightedBeforeConversion_de;
					$data['comparing_source_symptom_before_conversion_highlighted_en'] = $comSymptomStringHighlightedBeforeConversion_en;
					$data['comparing_source_symptom_before_conversion_de'] = $comSymptomStringBeforeConversion_de;
					$data['comparing_source_symptom_before_conversion_en'] = $comSymptomStringBeforeConversion_en;
					
					$data['initial_source_symptom_highlighted_de'] = $iniSymptomStringHighlighted_de;
					$data['initial_source_symptom_highlighted_en'] = $iniSymptomStringHighlighted_en;
					$data['comparing_source_symptom_highlighted_de'] = $comSymptomStringHighlighted_de;
					$data['comparing_source_symptom_highlighted_en'] = $comSymptomStringHighlighted_en;
					$data['initial_source_symptom_de'] = $iniSymptomString_de;
					$data['initial_source_symptom_en'] = $iniSymptomString_en;
					$data['comparing_source_symptom_de'] = $comSymptomString_de;
					$data['comparing_source_symptom_en'] = $comSymptomString_en;

					$data['comparison_language'] = $individual_comparison_language;
					$data['connection_language'] = $pastedRow['connection_language'];
					
					$data['matching_percentage'] = $pastedRow['matching_percentage'];
					$data['is_connected'] = $pastedRow['is_connected'];
					$data['is_ns_connect'] = $pastedRow['is_ns_connect'];
					$data['ns_connect_note'] = $pastedRow['ns_connect_note'];
					$data['is_pasted'] = $pastedRow['is_pasted'];
					$data['is_ns_paste'] = $pastedRow['is_ns_paste'];
					$data['ns_paste_note'] = $pastedRow['ns_paste_note'];
					$data['initial_source_symptom_comment'] = ($inner_is_initial_source == 1) ? $comment : "";
					$data['initial_source_symptom_footnote'] = ($inner_is_initial_source == 1) ? $footnote : "";
					$data['comparing_source_symptom_comment'] = ($inner_is_initial_source == 0) ? $comment : "";
					$data['comparing_source_symptom_footnote'] = ($inner_is_initial_source == 0) ? $footnote : "";
					$data['has_connections'] = $comHasConnections;
					$data['is_final_version_available'] = $is_final_version_available;
					$data['is_further_connections_are_saved'] = $isFurtherConnectionsAreSaved;
					$data['connection_or_paste_type'] = $pastedRow['connection_or_paste_type'];
					$data['is_saved_connections'] = $isSavedConnections;
					$data['is_initial_source'] = $inner_is_initial_source;
					$data['active_source_jahr'] = $active_source_jahr;
					$data['source_test'] = $finalEscapeableSourceIds;
					$data['workingSourceId'] = $workingSourceId;
					$pasteResultData [] = $data;
				}

				// Short the matched symptoms chronological jahr/year ASC
				$order_by_jahr = array();
				foreach ($pasteResultData as $key => $row)
				{
				    $order_by_jahr[$key] = $row['active_source_jahr'];
				}
				array_multisort($order_by_jahr, SORT_ASC, $pasteResultData);

				foreach ($pasteResultData as $key => $value) {
					$resultData[] = $value;
				}
			}

			$status = "success";
			$message = "success";
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