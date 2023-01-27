<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	
	$testingArray = array();
	$resultData = array();
	$status = '';
	$message = '';
	try {
		$longUpdate = 0;
		$checkInitialIfExistConnect = 0;
		$checkInitialIfExistPaste = 0;
		$fv_symptom_de = (isset($_POST['edited_initial_symptom_de']) AND $_POST['edited_initial_symptom_de'] != "") ? $_POST['edited_initial_symptom_de'] : "";
		$fv_symptom_en = (isset($_POST['edited_initial_symptom_en']) AND $_POST['edited_initial_symptom_en'] != "") ? $_POST['edited_initial_symptom_en'] : "";
		$comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$initial_symptom_id = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : "";
		$comparative_symptom_id = (isset($_POST['comparative_symptom_id']) AND $_POST['comparative_symptom_id'] != "") ? $_POST['comparative_symptom_id'] : "";
		$comparison_table_name = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? $_POST['comparison_table_name'] : "";
		$fv_comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : "";
		$cutoff_percentage = (isset($_POST['cutoff_percentage']) AND $_POST['cutoff_percentage'] != "") ? $_POST['cutoff_percentage'] : "";

		$edited_comparing_symptom_de = (isset($_POST['edited_comparing_symptom_de']) AND $_POST['edited_comparing_symptom_de'] != "") ? $_POST['edited_comparing_symptom_de'] : "";
		$edited_comparing_symptom_en = (isset($_POST['edited_comparing_symptom_en']) AND $_POST['edited_comparing_symptom_en'] != "") ? $_POST['edited_comparing_symptom_en'] : "";
		
		//earlier connection ce value is used for previous connections. 
		//for connect and ce value is 1
		//for paste and pe value is 2
		$earlierConnectionCe = (isset($_POST['earlierConnectionCe']) AND $_POST['earlierConnectionCe'] != "") ? $_POST['earlierConnectionCe'] : "";
		$arrayForEarlierConnection = (isset($_POST['arrayForEarlierConnection']) AND $_POST['arrayForEarlierConnection'] != "") ? $_POST['arrayForEarlierConnection'] : "";
		if($arrayForEarlierConnection != ""){
			$arrayForEarlierConnection = json_decode($_POST['arrayForEarlierConnection'],true);
		}
		
		//print_r($arrayForEarlierConnection);
		$operation_type = (isset($_POST['operation_type']) AND $_POST['operation_type'] != "") ? $_POST['operation_type'] : "";
		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
		$fv_connection_or_paste_type = (isset($_POST['fv_connection_or_paste_type']) AND $_POST['fv_connection_or_paste_type'] != "") ? $_POST['fv_connection_or_paste_type'] : 0;
		// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
		$is_final_version_available = 0;
		if($fv_connection_or_paste_type == 3)
			$is_final_version_available = 1;
		else if($fv_connection_or_paste_type == 4)
			$is_final_version_available = 2;

		$is_final_version_available = 1;
		$connection_type = "CE";
		$sendingArray = array();
		$earlierConnectionSaveArrayFinal = array();
		$updateHighestMatchSymptomIdArray = array();
		$testArray = array(
			'initial_symptom_id'=>$initial_symptom_id,
			'comparative_symptom_id'=>$comparative_symptom_id,
			'comparison_language'=>$comparison_language,
			'cutoff_percentage'=>$cutoff_percentage,
			'comparison_table_name'=>$comparison_table_name,
			'comparison_option'=>$fv_comparison_option,
			'earlierConnectionCe'=>$earlierConnectionCe,
			'arrayForEarlierConnection'=>$arrayForEarlierConnection,
			'operation_type'=>$operation_type,
			'fv_symptom_de'=>$fv_symptom_de,
			'fv_symptom_en'=>$fv_symptom_en
		);
		// echo json_encode( array( 'testArray' => $testArray)); 
		// exit;
		/* Collecting Stored available synonyms START */
		$availableSynonyms = array();
		$globalStopWords = getStopWords();
		if($comparison_language == "de" OR $comparison_language == "en"){
			$synonymResult = mysqli_query($db, "SELECT synonym_id, word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn FROM synonym_".$comparison_language);
			if(mysqli_num_rows($synonymResult) > 0){
				while($synonymRow = mysqli_fetch_array($synonymResult)){
					$synonymData = array();
					$synonymData['synonym_id'] = $synonymRow['synonym_id'];
					$synonymData['word'] = mb_strtolower($synonymRow['word']);
					$synonymData['strict_synonym'] = mb_strtolower($synonymRow['strict_synonym']);
					$synonymData['synonym_partial_1'] = mb_strtolower($synonymRow['synonym_partial_1']);
					$synonymData['synonym_partial_2'] = mb_strtolower($synonymRow['synonym_partial_2']);
					$synonymData['synonym_general'] = mb_strtolower($synonymRow['synonym_general']);
					$synonymData['synonym_minor'] = mb_strtolower($synonymRow['synonym_minor']);
					$synonymData['synonym_nn'] = mb_strtolower($synonymRow['synonym_nn']);
					$availableSynonyms[] = $synonymData;
				}
			}
		}
		/* Collecting Stored available synonyms END */
		if($comparison_table_name != "" AND $operation_type != ""){
			if($is_final_version_available != 0){
				if($operation_type == "connectCE"){
					$fv_symptom_de = prepareFinalVersionSymptom($fv_symptom_de);
					$fv_symptom_en = prepareFinalVersionSymptom($fv_symptom_en);
					
					$fv_symptom_de_insert = (isset($fv_symptom_de) AND $fv_symptom_de != "") ? mysqli_real_escape_string($db, $fv_symptom_de) : "";
					$fv_symptom_en_insert = (isset($fv_symptom_en) AND $fv_symptom_en != "") ? mysqli_real_escape_string($db, $fv_symptom_en) : "";
					if($comparison_language == 'de'){
						$swap_text = $fv_symptom_de_insert;
						$searchableText = $fv_symptom_de_insert;
					}else{
						$swap_text = $fv_symptom_en_insert;
						$searchableText = $fv_symptom_en_insert;
					}

					//string stripping
					$stripped_de = strip_tags($fv_symptom_de_insert);
					$stripped_en = strip_tags($fv_symptom_en_insert);

					$stripped_de_length = strlen($fv_symptom_de_insert);
					$stripped_en_length = strlen($fv_symptom_en_insert);

					// Finding match synonyms START
					$arrangedSynonymData = array();
					$matchedSynonyms = findMatchedSynonyms($searchableText, $globalStopWords, $availableSynonyms);
					if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
						$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
					}

					$dataSynonym['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
					$dataSynonym['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
					$dataSynonym['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
					$dataSynonym['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
					$dataSynonym['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
					$dataSynonym['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
					$dataSynonym['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
					// Finding match synonyms END

					//checking if the symptom has been swapped
					$checkingIfSwapedQuery = "SELECT swap_ce, swap_value_ce_en, swap_value_ce_de, swap, swap_value_en,swap_value_de, is_final_version_available, final_version_de,final_version_en FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom='1'";
					$checkingIfSwaped = mysqli_query($db,$checkingIfSwapedQuery);
					
					if(mysqli_num_rows($checkingIfSwaped) > 0){
						$swappedResult = mysqli_fetch_assoc($checkingIfSwaped);
						if($swappedResult['swap_ce'] != ""){
							$swap_value = 1;
							$updateSymptom = "UPDATE $comparison_table_name SET swap_value_ce_de = NULLIF('".$fv_symptom_de_insert."', ''),swap_value_ce_en = NULLIF('".$fv_symptom_en_insert."', ''), swap_ce = '".$swap_value."', synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
						}else if($swappedResult['swap'] != ""){
							$swap_value = 1;
							$updateSymptom = "UPDATE $comparison_table_name SET swap_value_de = NULLIF('".$fv_symptom_de_insert."', ''),swap_value_en = NULLIF('".$fv_symptom_en_insert."', ''), swap = '".$swap_value."', synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
						}else{
							$updateSymptom = "UPDATE $comparison_table_name SET final_version_de = NULLIF('".$fv_symptom_de_insert."', ''), final_version_en = NULLIF('".$fv_symptom_en_insert."', ''), is_final_version_available = '".$is_final_version_available."' , synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
						}		
					}
					$updateRes = $db->query($updateSymptom);
		  			//non secure connect checking
					$checkInitialIfExistConnect = checkInitialInConnectionForConnect($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExistConnect == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialConnect="UPDATE $comparison_table_name SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialConnect);
		            }
		            //non secure paste checking
			    	$checkInitialIfExistPaste = checkInitialInConnectionForPaste($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExistPaste == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitialPaste="UPDATE $comparison_table_name SET non_secure_paste = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitialPaste);
		            }
				}else{
					//checking if the symptom has been swapped
					$checkingIfSwapedQuery = "SELECT swap_ce FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom='1'";
					// echo json_encode( array( 'status' => $checkingIfSwapedQuery)); 
					// exit;
					$checkingIfSwaped = mysqli_query($db,$checkingIfSwapedQuery);
					
					if(mysqli_num_rows($checkingIfSwaped) > 0){
						$swappedResult = mysqli_fetch_assoc($checkingIfSwaped);
						if($swappedResult['swap_ce'] != ""){
							$updateSymptom = "UPDATE $comparison_table_name SET swap_value_ce_en = NULL,swap_value_ce_de = NULL, swap_ce = 0 WHERE symptom_id = '".$initial_symptom_id."'";
						}else{
							$updateSymptom = "UPDATE $comparison_table_name SET final_version_de = NULL, final_version_en = NULL, is_final_version_available = 0 WHERE symptom_id = '".$initial_symptom_id."'";

						}
					}
					$updateRes = $db->query($updateSymptom);
				}
					
				if ($updateRes == true) {
					// Data for connection table insertion start 
					$connectionDataArray = array();
					$testArray = array();
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

					$completeSendingArray = array();
					$comparingSymptomsArray = array();
					$runningInitialSymptomId = "";
					$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."'");
					if(mysqli_num_rows($fetchIdResult) > 0){
						$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
					}
					$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";

					$InitialQuelleResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'");
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
							if($iniSymRow['swap_ce'] != 0){
								if($comparison_language=='de'){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
									$iniSymptomString_en =  "";
								}else{
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
									$iniSymptomString_de =  "";
								}
							}else{
								if($iniSymRow['swap'] != 0){
									if($comparison_language=='de'){
										$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
										$iniSymptomString_en =  "";
									}else{
										$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
										$iniSymptomString_de =  "";
									}
								}else{
									if($iniSymRow['is_final_version_available'] != 0){
										$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['final_version_de']);
										$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['final_version_en']);
									} else {
										if($fv_comparison_option == 1){
											$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
											$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
										}else{
											$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
											$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
										}
									}
								}
									
							}
								
							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
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
							$ini_earlier = $ini;
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

							$initialSymptomData = array();
							$initialSymptomData['symptom_id'] = $iniSymRow['symptom_id'];
							$initialSymptomData['initial_symptom_id'] = $iniSymRow['initial_symptom_id'];
							$initialSymptomData['is_initial_symptom'] = $iniSymRow['is_initial_symptom'];
							$initialSymptomData['quelle_code'] = $iniSymRow['quelle_code'];
							$initialSymptomData['quelle_titel'] = $iniSymRow['quelle_titel'];
							$initialSymptomData['quelle_type_id'] = $iniSymRow['quelle_type_id'];
							$initialSymptomData['quelle_jahr'] = $iniSymRow['quelle_jahr'];
							$initialSymptomData['quelle_band'] = $iniSymRow['quelle_band'];
							$initialSymptomData['quelle_auflage'] = $iniSymRow['quelle_auflage'];
							$initialSymptomData['quelle_autor_or_herausgeber'] = $iniSymRow['quelle_autor_or_herausgeber'];
							$initialSymptomData['arznei_id'] = $iniSymRow['arznei_id'];
							$initialSymptomData['quelle_id'] = $iniSymRow['quelle_id'];
							$initialSymptomData['Symptomnummer'] = $iniSymRow['Symptomnummer'];
							$initialSymptomData['SeiteOriginalVon'] = $iniSymRow['SeiteOriginalVon'];
							$initialSymptomData['SeiteOriginalBis'] = $iniSymRow['SeiteOriginalBis'];
							// $initialSymptomData['final_version_de'] = $iniSymRow['final_version_de'];
							// $initialSymptomData['final_version_en'] = $iniSymRow['final_version_en'];
							// $initialSymptomData['Beschreibung_de'] = $iniSymRow['Beschreibung_de'];
							// $initialSymptomData['Beschreibung_en'] = $iniSymRow['Beschreibung_en'];
							// $initialSymptomData['BeschreibungOriginal_de'] = $iniSymRow['BeschreibungOriginal_de'];
							// $initialSymptomData['BeschreibungOriginal_en'] = $iniSymRow['BeschreibungOriginal_en'];
							// $initialSymptomData['BeschreibungFull_de'] = $iniSymRow['BeschreibungFull_de'];
							// $initialSymptomData['BeschreibungFull_en'] = $iniSymRow['BeschreibungFull_en'];
							// $initialSymptomData['BeschreibungPlain_de'] = $iniSymRow['BeschreibungPlain_de'];
							// $initialSymptomData['BeschreibungPlain_en'] = $iniSymRow['BeschreibungPlain_en'];
							// $initialSymptomData['searchable_text_de'] = $iniSymRow['searchable_text_de'];
							// $initialSymptomData['searchable_text_en'] = $iniSymRow['searchable_text_en'];
							$initialSymptomData['initial_symptom_en'] = $iniSymptomString_en;
							$initialSymptomData['initial_symptom_de'] = $iniSymptomString_de;
							$initialSymptomData['initial_symptom_base64encoded_en'] = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							$initialSymptomData['initial_symptom_base64encoded_de'] = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$initialSymptomData['bracketedString_de'] = $iniSymRow['bracketedString_de'];
							$initialSymptomData['bracketedString_en'] = $iniSymRow['bracketedString_en'];
							$initialSymptomData['timeString_de'] = $iniSymRow['timeString_de'];
							$initialSymptomData['timeString_en'] = $iniSymRow['timeString_en'];
							$initialSymptomData['initial_source_original_language'] = $iniSymRow['initial_source_original_language'];
							$initialSymptomData['comparing_source_original_language'] = $iniSymRow['comparing_source_original_language'];
							$initialSymptomData['Fussnote'] = mysqli_real_escape_string($db, $iniSymRow['Fussnote']);
							$initialSymptomData['EntnommenAus'] = mysqli_real_escape_string($db, $iniSymRow['EntnommenAus']);
							$initialSymptomData['Verweiss'] = mysqli_real_escape_string($db, $iniSymRow['Verweiss']);
							$initialSymptomData['BereichID'] = mysqli_real_escape_string($db, $iniSymRow['BereichID']);
							$initialSymptomData['Kommentar'] = mysqli_real_escape_string($db, $iniSymRow['Kommentar']);
							$initialSymptomData['Unklarheiten'] = mysqli_real_escape_string($db, $iniSymRow['Unklarheiten']);
							$initialSymptomData['Remedy'] = mysqli_real_escape_string($db, $iniSymRow['Remedy']);
							$initialSymptomData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $iniSymRow['symptom_of_different_remedy']);
							$initialSymptomData['subChapter'] = mysqli_real_escape_string($db, $iniSymRow['subChapter']);
							$initialSymptomData['subSubChapter'] = mysqli_real_escape_string($db, $iniSymRow['subSubChapter']);
							$initialSymptomData['synonym_word'] = mysqli_real_escape_string($db, $iniSymRow['synonym_word']);
							$initialSymptomData['strict_synonym'] = mysqli_real_escape_string($db, $iniSymRow['strict_synonym']);
							$initialSymptomData['synonym_partial_1'] = mysqli_real_escape_string($db, $iniSymRow['synonym_partial_1']);
							$initialSymptomData['synonym_partial_2'] = mysqli_real_escape_string($db, $iniSymRow['synonym_partial_2']);
							$initialSymptomData['synonym_general'] = mysqli_real_escape_string($db, $iniSymRow['synonym_general']);
							$initialSymptomData['synonym_minor'] = mysqli_real_escape_string($db, $iniSymRow['synonym_minor']);
							$initialSymptomData['synonym_nn'] = mysqli_real_escape_string($db, $iniSymRow['synonym_nn']);
							$initialSymptomData['symptom_edit_comment'] = mysqli_real_escape_string($db, $iniSymRow['symptom_edit_comment']);
							$initialSymptomData['is_final_version_available'] = $iniSymRow['is_final_version_available'];
							$initialSymptomData['matched_percentage'] = 0;
							$initialSymptomData['ersteller_datum'] = mysqli_real_escape_string($db, $iniSymRow['ersteller_datum']);

							// Collecting Synonyms of this Symptom START
							$initialSymptomsAllSynonyms = array();
							$wordSynonyms = array();
							$strictSynonyms = array();
							$partial1Synonyms = array();
							$partial2Synonyms = array();
							$generalSynonyms = array();
							$minorSynonyms = array();
							$nnSynonyms = array();
							if(!empty($iniSymRow['synonym_word'])){
								$wordSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_word']);
								$wordSynonyms = (!empty($wordSynonyms)) ? $wordSynonyms : array(); 
							}
							if(!empty($iniSymRow['strict_synonym'])){
								$strictSynonyms = getAllOrganizeSynonyms($iniSymRow['strict_synonym']);
								$strictSynonyms = (!empty($strictSynonyms)) ? $strictSynonyms : array(); 
							}
							if(!empty($iniSymRow['synonym_partial_1'])){
								$partial1Synonyms = getAllOrganizeSynonyms($iniSymRow['synonym_partial_1']);
								$partial1Synonyms = (!empty($partial1Synonyms)) ? $partial1Synonyms : array(); 
							}
							if(!empty($iniSymRow['synonym_partial_2'])){
								$partial2Synonyms = getAllOrganizeSynonyms($iniSymRow['synonym_partial_2']);
								$partial2Synonyms = (!empty($partial2Synonyms)) ? $partial2Synonyms : array(); 
							}
							if(!empty($iniSymRow['synonym_general'])){
								$generalSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_general']);
								$generalSynonyms = (!empty($generalSynonyms)) ? $generalSynonyms : array(); 
							}
							if(!empty($iniSymRow['synonym_minor'])){
								$minorSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_minor']);
								$minorSynonyms = (!empty($minorSynonyms)) ? $minorSynonyms : array(); 
							}
							if(!empty($iniSymRow['synonym_nn'])){
								$nnSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_nn']);
								$nnSynonyms = (!empty($nnSynonyms)) ? $nnSynonyms : array(); 
							}
							$initialSymptomsAllSynonyms = array_merge($wordSynonyms, $strictSynonyms, $partial1Synonyms, $partial2Synonyms, $generalSynonyms, $minorSynonyms, $nnSynonyms);
							// Collecting Synonyms of this Symptom END

							// Comparing symptoms
							$quelleComparingSymptomQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."' AND symptom_id = '".$connectionDataArray['comparing_symptom_id']."'";
							$quelleComparingSymptomResult = mysqli_query($db,$quelleComparingSymptomQuery);
							while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								if($quelleComparingSymptomRow['is_final_version_available'] != 0){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_en']);
								}else{
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
								if($compSymptomString_de != ""){
									$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_de = base64_encode($compSymptomString_de);
								}
								if($compSymptomString_en != ""){
									$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['symptom_id']);
									// $compSymptomString_en = base64_encode($compSymptomString_en);
								}

								if($comparison_language == "en")
									$com = $compSymptomString_en;
								else
									$com = $compSymptomString_de;
								
								//$resultArray = newComareSymptom($ini, $com);
								$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
								$testArray[] = $resultArray;
								
								$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
								$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
								// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
								$highlightedComparativeSymptomString_en = $compSymptomString_en;
								$highlightedComparativeSymptomString_de = $compSymptomString_de;
								if($comparison_language == "en")
									$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
								else
									$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;

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

								// Collecting comparative symptom ids match grater than 0 for updating highest match table
								// $highestMatchData = array();
								// if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage || $percentage >= $cutoff_percentage){
								// 	$fetchHighestMatchResult = mysqli_query($db,"SELECT comparison_table_id FROM ".$comparison_table_name."_highest_matches WHERE symptom_id = '".$quelleComparingSymptomRow['symptom_id']."'");
								// 	if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								// 		$highestMatchData = mysqli_fetch_assoc($fetchHighestMatchResult);
								// 	}
								// }
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
								// Id collect for highest match table update end

								$data = array();
								$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
								$data['initial_symptom_id'] = $runningInitialSymptomId;
								$data['is_initial_symptom'] = '0';
								$data['quelle_code'] = $quelleComparingSymptomRow['quelle_code'];
								$data['quelle_titel'] = $quelleComparingSymptomRow['quelle_titel'];
								$data['quelle_type_id'] = $quelleComparingSymptomRow['quelle_type_id'];
								$data['quelle_jahr'] = $quelleComparingSymptomRow['quelle_jahr'];
								$data['quelle_band'] = $quelleComparingSymptomRow['quelle_band'];
								$data['quelle_auflage'] = $quelleComparingSymptomRow['quelle_auflage'];
								$data['quelle_autor_or_herausgeber'] = $quelleComparingSymptomRow['quelle_autor_or_herausgeber'];
								$data['arznei_id'] = $quelleComparingSymptomRow['arznei_id'];
								$data['quelle_id'] = $quelleComparingSymptomRow['quelle_id'];
								$data['Symptomnummer'] = $quelleComparingSymptomRow['Symptomnummer'];
								$data['SeiteOriginalVon'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalVon']);
								$data['SeiteOriginalBis'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalBis']);
								$data['final_version_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_de']);
								$data['final_version_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_en']);
								$data['Beschreibung_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_de']);
								$data['Beschreibung_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_en']);
								$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_de']);
								$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_en']);
								$data['BeschreibungFull_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
								$data['BeschreibungFull_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
								$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_de']);
								$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_en']);
								$data['searchable_text_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
								$data['searchable_text_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);
								$data['initial_symptom_en'] = $iniSymptomString_en;
								$data['initial_symptom_de'] = $iniSymptomString_de;
								$data['comparing_symptom_en'] = $compSymptomString_en;
								$data['comparing_symptom_de'] = $compSymptomString_de;
								$data['initial_symptom_base64encoded_en'] = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
								$data['initial_symptom_base64encoded_de'] = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
								$data['comparing_symptom_base64encoded_en'] = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";
								$data['comparing_symptom_base64encoded_de'] = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
								$data['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en;
								$data['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de;
								$data['bracketedString_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['bracketedString_de']);
								$data['bracketedString_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['bracketedString_en']);
								$data['timeString_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['timeString_de']);
								$data['timeString_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['timeString_en']);
								$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
								$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
								$data['EntnommenAus'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['EntnommenAus']);
								$data['Verweiss'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Verweiss']);
								$data['BereichID'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BereichID']);
								$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
								$data['Unklarheiten'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Unklarheiten']);
								$data['Remedy'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Remedy']);
								$data['symptom_of_different_remedy'] = $quelleComparingSymptomRow['symptom_of_different_remedy'];
								$data['subChapter'] = $quelleComparingSymptomRow['subChapter'];
								$data['subSubChapter'] = $quelleComparingSymptomRow['subSubChapter'];
								$data['synonym_word'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_word']);
								$data['strict_synonym'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['strict_synonym']);
								$data['synonym_partial_1'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_partial_1']);
								$data['synonym_partial_2'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_partial_2']);
								$data['synonym_general'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_general']);
								$data['synonym_minor'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_minor']);
								$data['synonym_nn'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['synonym_nn']);
								$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
								$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
								$data['matched_percentage'] = $percentage;
								$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
								$data['connection'] = $quelleComparingSymptomRow['connection'];
								$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
								
								
								if(!empty($data)){
									$comparingSymptomsArray[] = $data;
									if($percentage >= $cutoff_percentage){
										unset($data['final_version_de']);
										unset($data['final_version_en']);
										unset($data['Beschreibung_de']);
										unset($data['Beschreibung_en']);
										unset($data['BeschreibungOriginal_de']);
										unset($data['BeschreibungOriginal_en']);
										unset($data['BeschreibungFull_de']);
										unset($data['BeschreibungFull_en']);
										unset($data['BeschreibungPlain_de']);
										unset($data['BeschreibungPlain_en']);
										unset($data['searchable_text_de']);
										unset($data['searchable_text_en']);
										$completeSendingArray[] = $data;
									}

								}

							}

							if(!empty($completeSendingArray)){
								usort($completeSendingArray, 'sortByOrder');
							}
							if(!empty($comparingSymptomsArray)){
								usort($comparingSymptomsArray, 'sortByOrder');
							}
							array_unshift($completeSendingArray, $initialSymptomData);
						}
					}

					if($earlierConnectionCe != 0){
						foreach ($arrayForEarlierConnection as $key) {
							$earlier_comparative_symptom = $key['comparativeIdToSend'];

							$queryComEarlierOne = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
				
							$queryComEarlierTwo = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
							// echo json_encode( array( 'status' => $queryComEarlier) ); 
							// exit;
							$proceed = 0;
							$quelleComparingSymptomResultEarlierOne = mysqli_query($db,$queryComEarlierOne);
							$quelleComparingSymptomResultEarlierTwo = mysqli_query($db,$queryComEarlierTwo);
							if(mysqli_num_rows($quelleComparingSymptomResultEarlierOne) > 0){
								$proceed = 1;
								$mysqliOperation = $quelleComparingSymptomResultEarlierOne;
							}

							if(mysqli_num_rows($quelleComparingSymptomResultEarlierTwo) > 0){
								$proceed = 1;
								$mysqliOperation = $quelleComparingSymptomResultEarlierTwo;
							}
							if($proceed > 0){
								while($mysqliOperationRow = mysqli_fetch_array($mysqliOperation)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($mysqliOperationRow['is_final_version_available'] != 0){
										$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['final_version_de']);
										$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['final_version_en']);
									}else{
										if($fv_comparison_option == 1){
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['searchable_text_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['searchable_text_en']);
										}else{
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['BeschreibungFull_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['BeschreibungFull_en']);
										}
									}
									
									// Apply dynamic conversion
									if($compSymptomString_de_ealier != ""){
										$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['is_final_version_available'], 0, $mysqliOperationRow['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en_ealier != ""){
										$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['is_final_version_available'], 0, $mysqliOperationRow['symptom_id']);
										// $compSymptomString_en = base64_encode($compSymptomString_en);
									}

									if($comparison_language == "en")
										$com_earlier = $compSymptomString_en_ealier;
									else
										$com_earlier = $compSymptomString_de_ealier;
									//Comparison and percentage calculations
									//$resultArrayEarlier = newComareSymptom($ini_earlier, $com_earlier);
									$resultArrayEarlier = compareSymptomWithSynonyms($ini_earlier, $com_earlier, $globalStopWords, $initialSymptomsAllSynonyms);
									$percentageEarlier = (isset($resultArrayEarlier['percentage'])) ? $resultArrayEarlier['percentage'] : 0;
									$highlightedComparingSymptomEarlier = (isset($resultArrayEarlier['comparing_source_symptom_highlighted']) AND $resultArrayEarlier['comparing_source_symptom_highlighted'] != "") ? $resultArrayEarlier['comparing_source_symptom_highlighted'] : "";
									
									// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
									$highlightedComparativeSymptomString_en_earlier = $compSymptomString_en_ealier;
									$highlightedComparativeSymptomString_de_earlier = $compSymptomString_de_ealier;
									if($comparison_language == "en")
										$highlightedComparativeSymptomString_en_earlier = ($highlightedComparingSymptomEarlier != "") ? $highlightedComparingSymptomEarlier : $compSymptomString_en_ealier;
									else
										$highlightedComparativeSymptomString_de_earlier = ($highlightedComparingSymptomEarlier != "") ? $highlightedComparingSymptomEarlier : $compSymptomString_de_ealier;
									
									$earlierConnectionSaveArray = array();
									$earlierConnectionSaveArray['symptom_id'] = $mysqliOperationRow['symptom_id'];
									$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparativeIdToSend'];
									$earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
									$earlierConnectionSaveArray['initialId'] = $key['initialId'];
									$earlierConnectionSaveArray['operationFlag'] = $key['operationFlag'];
									$earlierConnectionSaveArray['matched_percentage'] = $percentageEarlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de_earlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en_earlier;

									//inserting into array 
									$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
								}
							}
							// if($key['comparativeIdToSend'] == $quelleComparingSymptomRow['symptom_id']){
							// 	$earlierConnectionSaveArray = array();
							// 	$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
							// 	$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparativeIdToSend'];
							// 	$earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
							// 	$earlierConnectionSaveArray['matched_percentage'] = $percentage;
							// 	$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de;
							// 	$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en;
							// 	//inserting into array 
							// 	$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
							// }
						}
					}
					

					// if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){
					// 	$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
					// 	$deleteRes = $db->query($deleteExistingComparingSymptoms);
					// 	if($deleteRes == true){
					// 		$count = $rowIdToInsertFrom;
					// 		foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
					// 			$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, symptom_edit_comment, is_final_version_available, matched_percentage, ersteller_datum,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$comparingRow['initial_symptom_id']."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''), NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['connection']."', ''))";
					// 			$db->query($insertComparative);

					// 			$count++;
					// 		}
					// 	}
					// }

					// Inserting in connection in connections table
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$connectionDataArray['initial_symptom_id']."' AND comparing_symptom_id = '".$connectionDataArray['comparing_symptom_id']."'";
	            	$db->query($deleteExistingQuery);
	            	if($operation_type == "connectCE"){
	            		$insertConnection = "INSERT INTO ".$comparison_table_name."_connections (initial_symptom_id, comparing_symptom_id, connection_type, matched_percentage, ns_connect, ns_paste, ns_connect_comment, ns_paste_comment, initial_quelle_id, comparing_quelle_id, initial_quelle_code, comparing_quelle_code, initial_quelle_original_language, comparing_quelle_original_language, highlighted_initial_symptom_de, highlighted_initial_symptom_en, highlighted_comparing_symptom_de, highlighted_comparing_symptom_en, initial_symptom_de,  initial_symptom_en, comparing_symptom_de, comparing_symptom_en, comparison_language, initial_year, comparing_year, is_earlier_connection) VALUES (NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_id']."', ''), NULLIF('".$connectionDataArray['connection_type']."', ''), '".$connectionDataArray['matched_percentage']."', '".$connectionDataArray['ns_connect']."', '".$connectionDataArray['ns_paste']."', NULLIF('".$connectionDataArray['ns_connect_comment']."', ''), NULLIF('".$connectionDataArray['ns_paste_comment']."', ''), NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparison_language']."', ''), NULLIF('".$connectionDataArray['initial_year']."', ''), NULLIF('".$connectionDataArray['comparing_year']."', ''), NULLIF('".$connectionDataArray['is_earlier_connection']."', ''))";
						$db->query($insertConnection);
						//updating marking in the comparison table
						markingUpdation($db,$comparison_table_name,"1",$initial_symptom_id);
	            	}
	   				//updating marking in the comparison table
					markingUpdation($db,$comparison_table_name,"0",$initial_symptom_id);
					if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							if($earlierConnectionFinalRow['operationFlag'] == 3 || $earlierConnectionFinalRow['operationFlag'] == 1){
								$updateSymptom = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', '') WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
								
							}else if($earlierConnectionFinalRow['operationFlag'] == 2){
								$updateSymptom = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection='0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";
							}else{
								//for paste and paste edit previous connection
								$updateSymptom="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
            					// $db->query($deleteExistingQuery);
            					
								// $updateSymptom = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), is_earlier_connection='0', highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='1',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";
							}
							$db->query($updateSymptom);

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
				}

				
				$resultData = $completeSendingArray;
				$status = "success";
				$message = $updateHighestMatchSymptomIdArray;
			} else {
				$status = 'error';
	    		$message = 'Could not find final vesrion type';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	
	//echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' =>'ok') ); 
	echo json_encode( array( 'status' => $status, 'earlierConnectionSaveArrayFinal' => $earlierConnectionSaveArrayFinal,'arrayForEarlierConnection' => $arrayForEarlierConnection, 'message' =>'ok') ); 
	exit;

	function sortByOrder($a, $b) {
	   return  $b['matched_percentage'] - $a['matched_percentage'];
	}
?>
<?php
	include 'includes/php-foot-includes.php';
?>