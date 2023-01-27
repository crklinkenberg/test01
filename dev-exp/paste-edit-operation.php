<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';
	$checkInitialIfExistConnect = 0;
	$checkInitialIfExistPaste = 0;
	try {
		$fv_symptom_de = (isset($_POST['edited_comparative_symptom_de']) AND $_POST['edited_comparative_symptom_de'] != "") ? $_POST['edited_comparative_symptom_de'] : "";
		$fv_symptom_en = (isset($_POST['edited_comparative_symptom_en']) AND $_POST['edited_comparative_symptom_en'] != "") ? $_POST['edited_comparative_symptom_en'] : "";
		$comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$initial_symptom_id = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : "";
		$disconnectedPreviousInitialIdToSend = (isset($_POST['disconnectedPreviousInitialIdToSend']) AND $_POST['disconnectedPreviousInitialIdToSend'] != "") ? $_POST['disconnectedPreviousInitialIdToSend'] : "";
		$comparative_symptom_id = (isset($_POST['comparative_symptom_id']) AND $_POST['comparative_symptom_id'] != "") ? $_POST['comparative_symptom_id'] : "";
		$comparison_table_name = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? $_POST['comparison_table_name'] : "";
		$fv_comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : "";
		$cutoff_percentage = (isset($_POST['cutoff_percentage']) AND $_POST['cutoff_percentage'] != "") ? $_POST['cutoff_percentage'] : "";
		$operation_type = (isset($_POST['operation_type']) AND $_POST['operation_type'] != "") ? $_POST['operation_type'] : "";
		$operation = (isset($_POST['operation']) AND $_POST['operation'] != "") ? $_POST['operation'] : "";
		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
		$fv_connection_or_paste_type = (isset($_POST['fv_connection_or_paste_type']) AND $_POST['fv_connection_or_paste_type'] != "") ? $_POST['fv_connection_or_paste_type'] : 0;
		// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
		$is_final_version_available = 0;
		if($fv_connection_or_paste_type == 3)
			$is_final_version_available = 1;
		else if($fv_connection_or_paste_type == 4)
			$is_final_version_available = 2;

		$earlierConnectionPe = (isset($_POST['earlierConnectionPe']) AND $_POST['earlierConnectionPe'] != "") ? $_POST['earlierConnectionPe'] : 0;
		$arrayForEarlierConnection = (isset($_POST['arrayForEarlierConnection']) AND $_POST['arrayForEarlierConnection'] != "") ? $_POST['arrayForEarlierConnection'] : "";
		if($arrayForEarlierConnection != ""){
			$arrayForEarlierConnection = json_decode($_POST['arrayForEarlierConnection'],true);
		}

		$is_final_version_available = 1;
		$connection_type = "PE";
		$sendingArray = array();
		$updateHighestMatchSymptomIdArray = array();
		if($comparison_table_name != "" AND $operation_type != ""){
			if($is_final_version_available != 0){
				if($operation_type == "pastePE"){
		            //non secure paste checking
					$checkInitialIfExistPaste = checkInitialInConnectionForPaste($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExistPaste == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialPaste="UPDATE $comparison_table_name SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialPaste);
		            }
		            //non secure connect checking
					$checkInitialIfExistConnect = checkInitialInConnectionForConnect($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExistConnect == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialConnect="UPDATE $comparison_table_name SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialConnect);
		            }
					$fv_symptom_de = prepareFinalVersionSymptom($fv_symptom_de);
					$fv_symptom_en = prepareFinalVersionSymptom($fv_symptom_en);
					$fv_symptom_de_insert = (isset($fv_symptom_de) AND $fv_symptom_de != "") ? mysqli_real_escape_string($db, $fv_symptom_de) : "";
					$fv_symptom_en_insert = (isset($fv_symptom_en) AND $fv_symptom_en != "") ? mysqli_real_escape_string($db, $fv_symptom_en) : "";
					$updateSymptom = "UPDATE $comparison_table_name SET final_version_de = NULLIF('".$fv_symptom_de_insert."', ''), final_version_en = NULLIF('".$fv_symptom_en_insert."', ''), is_final_version_available = '".$is_final_version_available."' WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
					$updateRes = $db->query($updateSymptom);
				}else{
					$symptomUpdateQueryInitialPaste = "UPDATE $comparison_table_name SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0 WHERE symptom_id = '".$initial_symptom_id."'  AND `is_initial_symptom`= '1'";
					$symptomUpdateQueryInitialPasteRes = $db->query($symptomUpdateQueryInitialPaste);

					if($operation == "PE_previous"){
						$updateSymptom = "UPDATE $comparison_table_name SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0 WHERE symptom_id = '".$comparative_symptom_id."'";

					}else{
						$updateSymptom = "UPDATE $comparison_table_name SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";

					}
					$updateRes = $db->query($updateSymptom);
				}
					
				if ($updateRes == true) {
					$initial_symptom_id_for_delete = $initial_symptom_id;
					if($operation == "PE_previous"){
						$initial_symptom_id = $disconnectedPreviousInitialIdToSend;
					}
					// Data for connection table insertion start 
					$connectionDataArray = array();
					$connectionDataArray['initial_symptom_id'] = $initial_symptom_id;
					$connectionDataArray['comparing_symptom_id'] = $comparative_symptom_id;
					$connectionDataArray['connection_type'] = $connection_type;
					$connectionDataArray['matched_percentage'] = 0;
					$connectionDataArray['ns_connect'] = 0; 
					$connectionDataArray['ns_paste'] = 0;
					$connectionDataArray['ns_connect_comment'] = "";
					$connectionDataArray['ns_paste_comment'] = "";
					$connectionDataArray['initial_quelle_id'] = "";
					$connectionDataArray['comparing_quelle_id'] = "";
					$connectionDataArray['initial_quelle_code'] = "";
					$connectionDataArray['comparing_quelle_code'] = "";
					$connectionDataArray['initial_quelle_original_language'] = "";
					$connectionDataArray['comparing_quelle_original_language'] = "";
					$connectionDataArray['highlighted_initial_symptom_de'] = "";
					$connectionDataArray['highlighted_initial_symptom_en'] = "";
					$connectionDataArray['highlighted_comparing_symptom_de'] = "";
					$connectionDataArray['highlighted_comparing_symptom_en'] = "";
					$connectionDataArray['initial_symptom_de'] = "";
					$connectionDataArray['initial_symptom_en'] = "";
					$connectionDataArray['comparing_symptom_de'] = "";
					$connectionDataArray['comparing_symptom_en'] = "";
					$connectionDataArray['comparison_language'] = $comparison_language;
					$connectionDataArray['initial_year'] = "";
					$connectionDataArray['comparing_year'] = "";
					$connectionDataArray['is_earlier_connection'] = 0;
					// Data for connection table insertion end

					$runningInitialSymptomId = "";

					
					//Initial Symptom Data Fetching
					$InitialQuelleResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'");
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
							if($iniSymRow['swap'] != 0){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
							}else if($iniSymRow['swap_ce'] != 0){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
							}else if($iniSymRow['is_final_version_available'] != 0){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['final_version_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['final_version_en']);
							}else{
								if($fv_comparison_option == 1){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
								}else{
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
								}
							}

							// if($iniSymRow['is_final_version_available'] != 0){
							// 	$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['final_version_de']);
							// 	$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['final_version_en']);
							// } else {
							// 	if($fv_comparison_option == 1){
							// 		$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
							// 		$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
							// 	}else{
							// 		$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
							// 		$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
							// 	}
							// }
							// initial source symptom string Before convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymptomString_de != ""){
								$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
								// $iniSymptomString_de = base64_encode($iniSymptomString_de);
							}
							if($iniSymptomString_en != ""){
								$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
								// $iniSymptomString_en = base64_encode($iniSymptomString_en);
							}
							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;

							// For connection table start
							$connectionDataArray['initial_quelle_id'] = $iniSymRow['quelle_id'];
							$connectionDataArray['initial_quelle_code'] = $iniSymRow['quelle_code'];
							$connectionDataArray['initial_quelle_original_language'] = $iniSymRow['initial_source_original_language'];
							$connectionDataArray['highlighted_initial_symptom_de'] = $iniSymptomString_de;
							$connectionDataArray['highlighted_initial_symptom_en'] = $iniSymptomString_en;
							$connectionDataArray['initial_symptom_de'] = $iniSymptomString_de;
							$connectionDataArray['initial_symptom_en'] = $iniSymptomString_en;
							$connectionDataArray['initial_year'] = $iniSymRow['quelle_jahr'];
							// For connection table end
						}
					}
					
					// Comparative Symptom Data Fetching
					$queryCom = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
					$quelleComparingSymptomResult = mysqli_query($db,$queryCom);
					
					if(mysqli_num_rows($quelleComparingSymptomResult) > 0){
						while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
							
							// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
							if($quelleComparingSymptomRow['swap'] != 0){
								$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
								$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
							}else if($quelleComparingSymptomRow['swap_ce'] != 0){
								$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
								$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
							}else if($quelleComparingSymptomRow['is_final_version_available'] != 0){
								$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_de']);
								$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_en']);
							}else {
								if($fv_comparison_option == 1){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);
								}else{
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
								}
							}
							// comparing source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
							$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

							// Apply dynamic conversion
							if($quelleComparingSymptomRow['swap'] != 0){
								if($compSymptomString_de != ""){
									$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_de = base64_encode($compSymptomString_de);
								}
								if($compSymptomString_en != ""){
									$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_en = base64_encode($compSymptomString_en);
								}
							}else if($quelleComparingSymptomRow['swap_ce'] != 0){
								if($compSymptomString_de != ""){
									$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_de = base64_encode($compSymptomString_de);
								}
								if($compSymptomString_en != ""){
									$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_en = base64_encode($compSymptomString_en);
								}
							}else{
								if($compSymptomString_de != ""){
									$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_de = base64_encode($compSymptomString_de);
								}
								if($compSymptomString_en != ""){
									$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_en = base64_encode($compSymptomString_en);
								}
							}

							if($comparison_language == "en")
								$com = $compSymptomString_en;
							else
								$com = $compSymptomString_de;
							
							//Comparison and percentage calculations
							$resultArray = newComareSymptom($ini, $com);
							$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
							$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
							
							// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
							$highlightedComparativeSymptomString_en = $compSymptomString_en;
							$highlightedComparativeSymptomString_de = $compSymptomString_de;
							if($comparison_language == "en")
								$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
							else
								$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;
							if($comparison_language == "de")
							{
								$highlightedString = $highlightedComparativeSymptomString_de;
							}
							else
							{
								$highlightedString = $highlightedComparativeSymptomString_en;
							}
							// For connection table start
							if($comparative_symptom_id == $quelleComparingSymptomRow['symptom_id']){
								$connectionDataArray['matched_percentage'] = $percentage;
								$connectionDataArray['comparing_quelle_id'] = $quelleComparingSymptomRow['quelle_id'];
								$connectionDataArray['comparing_quelle_code'] = $quelleComparingSymptomRow['quelle_code'];
								$connectionDataArray['comparing_quelle_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$connectionDataArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de;
								$connectionDataArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en;
								$connectionDataArray['comparing_symptom_de'] = $compSymptomString_de;
								$connectionDataArray['comparing_symptom_en'] = $compSymptomString_en;
								$connectionDataArray['comparing_year'] = $quelleComparingSymptomRow['quelle_jahr'];
							}
							// For connection table end
							
							// For before CE edit done
							if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage){
								if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
									array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
							}
							// For after CE edit done
							if($percentage >= $cutoff_percentage){
								if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
									array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
							}
							
							
							if($earlierConnectionPe != 0){
								foreach ($arrayForEarlierConnection as $key) {
									$earlier_comparative_symptom = $key['comparativeIdToSend'];
									$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
									$quelleComparingSymptomResultEarlier = mysqli_query($db,$queryComEarlier);
									
									if(mysqli_num_rows($quelleComparingSymptomResultEarlier) > 0){
										while($quelleComparingSymptomRowEarlier = mysqli_fetch_array($quelleComparingSymptomResultEarlier)){
											// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)

											if($quelleComparingSymptomRowEarlier['swap'] != 0){
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_en']);
											}else if($quelleComparingSymptomRowEarlier['swap_ce'] != 0){
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_ce_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_ce_en']);
											}else if($quelleComparingSymptomRowEarlier['is_final_version_available'] != 0){
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_en']);
											}else {
												if($fv_comparison_option == 1){
													$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
													$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);
												}else{
													$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
													$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
												}
											}

											// Apply dynamic conversion
											if($quelleComparingSymptomRowEarlier['swap'] != 0){
												if($compSymptomString_de_ealier != ""){
													$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_de = base64_encode($compSymptomString_de);
												}
												if($compSymptomString_en_ealier != ""){
													$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_en = base64_encode($compSymptomString_en);
												}
											}else if($quelleComparingSymptomRowEarlier['swap_ce'] != 0){
												if($compSymptomString_de_ealier != ""){
													$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap_ce'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_de = base64_encode($compSymptomString_de);
												}
												if($compSymptomString_en_ealier != ""){
													$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap_ce'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_en = base64_encode($compSymptomString_en);
												}
											}else{
												if($compSymptomString_de_ealier != ""){
													$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_de = base64_encode($compSymptomString_de);
												}
												if($compSymptomString_en_ealier != ""){
													$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
													// $compSymptomString_en = base64_encode($compSymptomString_en);
												}
											}

											if($comparison_language == "en")
												$com_earlier = $compSymptomString_en_ealier;
											else
												$com_earlier = $compSymptomString_de_ealier;
											
											//Comparison and percentage calculations
											$resultArrayEarlier = newComareSymptom($ini, $com_earlier);
											$percentageEarlier = (isset($resultArrayEarlier['percentage'])) ? $resultArrayEarlier['percentage'] : 0;
											$highlightedComparingSymptomEarlier = (isset($resultArrayEarlier['comparing_source_symptom_highlighted']) AND $resultArrayEarlier['comparing_source_symptom_highlighted'] != "") ? $resultArrayEarlier['comparing_source_symptom_highlighted'] : "";

											// For before CE edit done
											if($quelleComparingSymptomRowEarlier['matched_percentage'] >= $cutoff_percentage){
												if(!in_array($quelleComparingSymptomRowEarlier['symptom_id'], $updateHighestMatchSymptomIdArray))
													array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRowEarlier['symptom_id']); 
											}
											// For after CE edit done
											if($percentageEarlier >= $cutoff_percentage){
												if(!in_array($quelleComparingSymptomRowEarlier['symptom_id'], $updateHighestMatchSymptomIdArray))
													array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRowEarlier['symptom_id']); 
											}
											
											// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
											$highlightedComparativeSymptomString_en_earlier = $compSymptomString_en_ealier;
											$highlightedComparativeSymptomString_de_earlier = $compSymptomString_de_ealier;
											if($comparison_language == "en")
												$highlightedComparativeSymptomString_en_earlier = ($highlightedComparingSymptomEarlier != "") ? $highlightedComparingSymptomEarlier : $compSymptomString_en_ealier;
											else
												$highlightedComparativeSymptomString_de_earlier = ($highlightedComparingSymptomEarlier != "") ? $highlightedComparingSymptomEarlier : $compSymptomString_de_ealier;

											$earlierConnectionSaveArray = array();
											$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRowEarlier['symptom_id'];
											$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparativeIdToSend'];
											$earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
											$earlierConnectionSaveArray['matched_percentage'] = $percentageEarlier;
											$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de_earlier;
											$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en_earlier;
											//inserting into array 
											$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
										}
									}
								}
							}
						}
					}
					// if($operation_type == "disconnectPE"){
					// 	//updatng value if non secure exist start
			 
					//Delete connection if already exist.
					
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id_for_delete."' AND comparing_symptom_id = '".$connectionDataArray['comparing_symptom_id']."'";

										
				
	            	$db->query($deleteExistingQuery);
					if($operation_type == "pastePE")
					{
						// Inserting in connection in connections table
		            	$insertConnection = "INSERT INTO ".$comparison_table_name."_connections (initial_symptom_id, comparing_symptom_id, connection_type, matched_percentage, ns_connect, ns_paste, ns_connect_comment, ns_paste_comment, initial_quelle_id, comparing_quelle_id, initial_quelle_code, comparing_quelle_code, initial_quelle_original_language, comparing_quelle_original_language, highlighted_initial_symptom_de, highlighted_initial_symptom_en, highlighted_comparing_symptom_de, highlighted_comparing_symptom_en, initial_symptom_de,  initial_symptom_en, comparing_symptom_de, comparing_symptom_en, comparison_language, initial_year, comparing_year, is_earlier_connection) VALUES (NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_id']."', ''), NULLIF('".$connectionDataArray['connection_type']."', ''), '".$connectionDataArray['matched_percentage']."', '".$connectionDataArray['ns_connect']."', '".$connectionDataArray['ns_paste']."', NULLIF('".$connectionDataArray['ns_connect_comment']."', ''), NULLIF('".$connectionDataArray['ns_paste_comment']."', ''), NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparison_language']."', ''), NULLIF('".$connectionDataArray['initial_year']."', ''), NULLIF('".$connectionDataArray['comparing_year']."', ''), NULLIF('".$connectionDataArray['is_earlier_connection']."', ''))";
						$db->query($insertConnection);
						//updating marking in the comparison table
						markingUpdation($db,$comparison_table_name,"1",$initial_symptom_id);
					}
					//updating marking in the comparison table
					markingUpdation($db,$comparison_table_name,"0",$initial_symptom_id);

					if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							if($earlierConnectionPe == 1){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), is_earlier_connection='0', initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', '') WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";
								
							}else{
								//for paste and paste edit previous connection
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), is_earlier_connection='0', initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='1',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

							}
							$db->query($updateSymptomEarlier);

						}
					}

					// Updation in highest match table
					if(!empty($updateHighestMatchSymptomIdArray)){
						foreach ($updateHighestMatchSymptomIdArray as $symId) {
							// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
							$fetchHighestMatchQuery = "SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'";
							$fetchHighestMatchResult = mysqli_query($db,$fetchHighestMatchQuery);
							if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

								$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
								$updateRes = $db->query($updateHighestMatchDetails);
							}
						}
					} 

				
					//Final update of percentage in comparison table
					$updatePercent = "UPDATE $comparison_table_name SET matched_percentage = $percentage WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";

					$updateRes = $db->query($updatePercent);

				}
				$status = "success";
			} else {
				$status = 'error';
	    		$message = 'Could not find final vesrion type';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}
	//Creating array to send
	$com = base64_encode($com);
	$arrayToSend = array(
		'percentage'=> $percentage,
		'symptomText' => $highlightedString,
		'comparingString' => $com
	); 
	echo json_encode( array( 'status' => $status, 'resultArray'=>$arrayToSend, 'compareArray'=>$resultArray)); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>