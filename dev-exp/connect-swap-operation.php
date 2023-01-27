<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$stopWords = array();
	$stopWords = getStopWords();
	$resultData = array();
	$testArray = array();	
	$allQueryArray = array();	
	$status = '';
	$message = '';
	try {
		$checkInitialIfExist = 0;
		//For connect edit connections
		$fv_symptom_de = (isset($_POST['edited_comparing_symptom_de']) AND $_POST['edited_comparing_symptom_de'] != "") ? $_POST['edited_comparing_symptom_de'] : "";
		$fv_symptom_en = (isset($_POST['edited_comparing_symptom_en']) AND $_POST['edited_comparing_symptom_en'] != "") ? $_POST['edited_comparing_symptom_en'] : "";

		$fv_symptom_initial_de = (isset($_POST['edited_initial_symptom_de']) AND $_POST['edited_initial_symptom_de'] != "") ? $_POST['edited_initial_symptom_de'] : "";
		$fv_symptom_initial_en = (isset($_POST['edited_initial_symptom_en']) AND $_POST['edited_initial_symptom_en'] != "") ? $_POST['edited_initial_symptom_en'] : "";
		
		//For connect connections
		$comparative_symptom_text = (isset($_POST['comparative_symptom_text']) AND $_POST['comparative_symptom_text'] != "") ? $_POST['comparative_symptom_text'] : "";
		$initial_symptom_text = (isset($_POST['initial_symptom_text']) AND $_POST['initial_symptom_text'] != "") ? $_POST['initial_symptom_text'] : "";
		$initial_symptom_text_lang = (isset($_POST['initial_symptom_text_lang']) AND $_POST['initial_symptom_text_lang'] != "") ? $_POST['initial_symptom_text_lang'] : "";
		$comparing_symptom_text_lang = (isset($_POST['comparing_symptom_text_lang']) AND $_POST['comparing_symptom_text_lang'] != "") ? $_POST['comparing_symptom_text_lang'] : "";

		//Other values
		$comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$initial_symptom_id = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : "";
		$comparative_symptom_id = (isset($_POST['comparative_symptom_id']) AND $_POST['comparative_symptom_id'] != "") ? $_POST['comparative_symptom_id'] : "";
		$comparison_table_name = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? $_POST['comparison_table_name'] : "";
		$fv_comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : 0;
		$cutoff_percentage = (isset($_POST['cutoff_percentage']) AND $_POST['cutoff_percentage'] != "") ? $_POST['cutoff_percentage'] : "";
		$operation_type = (isset($_POST['operation_type']) AND $_POST['operation_type'] != "") ? $_POST['operation_type'] : "";
		$previousSwapConnecectEdit = (isset($_POST['previousSwapConnecectEdit']) AND $_POST['previousSwapConnecectEdit'] != "") ? $_POST['previousSwapConnecectEdit'] : "";

		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
		$fv_connection_or_paste_type = (isset($_POST['fv_connection_or_paste_type']) AND $_POST['fv_connection_or_paste_type'] != "") ? $_POST['fv_connection_or_paste_type'] : 0;
		$operationFlag = (isset($_POST['operationFlag']) AND $_POST['operationFlag'] != "") ? $_POST['operationFlag'] : 0;
		$arrayForEarlierConnection = (isset($_POST['arrayForEarlierConnection']) AND $_POST['arrayForEarlierConnection'] != "") ? $_POST['arrayForEarlierConnection'] : array();
		if(!empty($arrayForEarlierConnection)){
			$arrayForEarlierConnection = json_decode($arrayForEarlierConnection,true);
		}
		// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
		$is_final_version_available = 0;
		if($fv_connection_or_paste_type == 3)
			$is_final_version_available = 1;
		else if($fv_connection_or_paste_type == 4)
			$is_final_version_available = 2;

		$is_final_version_available = 1;
		$connection_type = "swap";
		$quelleQuery="";
		$ini_earlier = "";
		$searchableText = "";
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
				$fv_symptom_de = prepareFinalVersionSymptom($fv_symptom_de);
				$fv_symptom_en = prepareFinalVersionSymptom($fv_symptom_en);

				$fv_symptom_initial_de = prepareFinalVersionSymptom($fv_symptom_initial_de);
				$fv_symptom_initial_en = prepareFinalVersionSymptom($fv_symptom_initial_en);

				$comparative_symptom_text = prepareFinalVersionSymptom($comparative_symptom_text);
				$initial_symptom_text = prepareFinalVersionSymptom($initial_symptom_text);

				$comparing_symptom_text_lang = prepareFinalVersionSymptom($comparing_symptom_text_lang);
				$initial_symptom_text_lang = prepareFinalVersionSymptom($initial_symptom_text_lang);
				

				$fv_symptom_de_insert = (isset($fv_symptom_de) AND $fv_symptom_de != "") ? mysqli_real_escape_string($db, $fv_symptom_de) : "";
				$fv_symptom_en_insert = (isset($fv_symptom_en) AND $fv_symptom_en != "") ? mysqli_real_escape_string($db, $fv_symptom_en) : "";

				$fv_symptom_initial_de_insert = (isset($fv_symptom_initial_de) AND $fv_symptom_initial_de != "") ? mysqli_real_escape_string($db, $fv_symptom_initial_de) : "";
				$fv_symptom_initial_en_insert = (isset($fv_symptom_initial_en) AND $fv_symptom_initial_en != "") ? mysqli_real_escape_string($db, $fv_symptom_initial_en) : "";
				
				$comparative_symptom_text_insert = (isset($comparative_symptom_text) AND $comparative_symptom_text != "") ? mysqli_real_escape_string($db, $comparative_symptom_text) : "";
				$initial_symptom_text_insert = (isset($initial_symptom_text) AND $initial_symptom_text != "") ? mysqli_real_escape_string($db, $initial_symptom_text) : "";

				$comparing_symptom_text_lang_insert = (isset($comparing_symptom_text_lang) AND $comparing_symptom_text_lang != "") ? mysqli_real_escape_string($db, $comparing_symptom_text_lang) : "";
				$initial_symptom_text_lang_insert = (isset($initial_symptom_text_lang) AND $initial_symptom_text_lang != "") ? mysqli_real_escape_string($db, $initial_symptom_text_lang) : "";

			
				
				//Operation type connectSWAP for connect and swap.
				if($comparison_language == "de"){
					$swapTextDe = $comparative_symptom_text_insert;
					$swapTextEn = $comparing_symptom_text_lang_insert;
				}else{
					$swapTextEn = $comparative_symptom_text_insert;
					$swapTextDe = $comparing_symptom_text_lang_insert;
				}
				//variables for matching synonymc
				$searchableText = $comparative_symptom_text_insert;
				$searchableTextForComparing = $initial_symptom_text_insert;

				$fv_symptom_initial_de_insert_connect="";
				$fv_symptom_initial_en_insert_connect="";
				$fv_symptom_de_insert_connect="";
				$fv_symptom_en_insert_connect="";
				if($comparison_language == "de")
				{
					$fv_symptom_initial_de_insert_connect = $comparative_symptom_text_insert;
					$fv_symptom_de_insert_connect = $initial_symptom_text_insert;
					$fv_symptom_en_insert_connect = $initial_symptom_text_lang_insert;
				}
				else
				{
					$fv_symptom_initial_en_insert_connect = $comparative_symptom_text_insert;
					$fv_symptom_en_insert_connect = $initial_symptom_text_insert;
					$fv_symptom_de_insert_connect = $initial_symptom_text_lang_insert;

				}

				$testArray = array(
					'initial_symptom_id'=>$initial_symptom_id,
					'comparative_symptom_id'=>$comparative_symptom_id,
					'comparison_language'=>$comparison_language,
					'cutoff_percentage'=>$cutoff_percentage,
					'comparison_table_name'=>$comparison_table_name,
					'comparison_option'=>$fv_comparison_option,
					'operationFlag'=>$operationFlag,
					'arrayForEarlierConnection'=>$arrayForEarlierConnection,
					'operation_type'=>$operation_type,
					'fv_symptom_de'=>$fv_symptom_de,
					'fv_symptom_en'=>$fv_symptom_en,
					'fv_symptom_initial_de'=>$fv_symptom_initial_de,
					'fv_symptom_initial_en'=>$fv_symptom_initial_en,
					'comparative_symptom_text'=>$comparative_symptom_text_insert,
					'initial_symptom_text'=>$initial_symptom_text_insert,
					'fv_symptom_en_insert_connect'=>$fv_symptom_en_insert_connect,
					'fv_symptom_de_insert_connect'=>$fv_symptom_de_insert_connect,
					'fv_symptom_initial_de_insert_connect'=>$fv_symptom_initial_de_insert_connect,
					'fv_symptom_initial_en_insert_connect'=>$fv_symptom_initial_en_insert_connect
				);
				if($operation_type == "connectCESwap"){
					if($comparison_language == "de"){
						$searchableText = $fv_symptom_de_insert;
						$searchableTextForComparing = $fv_symptom_initial_de_insert;
					}else{
						$searchableText = $fv_symptom_en_insert;
						$searchableTextForComparing = $fv_symptom_initial_en_insert;
					}
				}

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

				if($operation_type == "connectSWAP"){
					$checkInitialIfExist = checkInitialInConnectionForConnect($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExist == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitial="UPDATE $comparison_table_name SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitial);
		            }
					//#1 Updating the symptom text of the initial symptom

					if($previousSwapConnecectEdit == 1){
						$condition = "SET swap_value_de = NULLIF('".$swapTextDe."', ''),swap_value_en = NULLIF('".$swapTextEn."', ''),swap = 1, swap_value_ce_en = NULL,swap_value_ce_de = NULL, swap_ce = 0, synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
					}else{
						$condition = "SET swap_value_de = NULLIF('".$swapTextDe."', ''),swap_value_en = NULLIF('".$swapTextEn."', ''),swap = 1, synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
					}
					$updateSymptom = "UPDATE $comparison_table_name $condition";
					$updateRes = $db->query($updateSymptom);
					//Flag for connect swap
					$updateResult = 1;	
				}elseif($operation_type == "disconnectSWAP"){
					//Disconnect swap update
					//#1 Updating the symptom text of the initial symptom
					$condition = "SET swap_value_de = NULLIF('".$swapTextDe."', ''),swap_value_en = NULLIF('".$swapTextEn."', ''),swap = 1, synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
					$updateSymptom = "UPDATE $comparison_table_name $condition";
					$updateRes = $db->query($updateSymptom);
					//Flag for disconnect swap
					$updateResult = 0;	
				}elseif($operation_type == "disconnectSWAPConnected"){
					//Disconnected swap for connected symptoms
					//Flag for disconnect swap
					$updateResult = 2;	
				}elseif($operation_type == "connectCESwap"){
					$checkInitialIfExist = checkInitialInConnectionForConnect($db, $comparison_table_name, $initial_symptom_id);
		            if($checkInitialIfExist == 0){
		            	$ns_value = '0';
						$symptomUpdateQueryInitial="UPDATE $comparison_table_name SET non_secure_connect = NULLIF('".$ns_value."', '') WHERE symptom_id = '".$initial_symptom_id."' AND `is_initial_symptom`= '1'";
						$db->query($symptomUpdateQueryInitial);
		            }
					//#1 Updating the symptom text of the initial symptom
					$condition = "SET swap_value_ce_de = NULLIF('".$fv_symptom_de_insert."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_en_insert."', ''),swap_ce = 1, synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
					$updateSymptom = "UPDATE $comparison_table_name $condition";
					$updateRes = $db->query($updateSymptom);
					//Flag for connect edit swap
					//Swap connection CE
					$updateResult = 3;
				}elseif($operation_type == "disconnectSWAPCE"){
					//Disconnect swap CE
					//Swap disconnection CE
					if($comparison_language == "de")
					{
						$fv_symptom_initial_de_insert = $comparative_symptom_text_insert;
						$fv_symptom_de_insert = $initial_symptom_text_insert;
                        $searchableText = $fv_symptom_initial_de_insert;
					}
					else
					{
						$fv_symptom_initial_en_insert = $comparative_symptom_text_insert;
						$fv_symptom_en_insert = $initial_symptom_text_insert;
                        $searchableText = $fv_symptom_initial_en_insert;
					}
					//#1 Updating the symptom text of the initial symptom
					$condition = "SET swap_value_ce_de = NULLIF('".$fv_symptom_initial_de_insert."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_initial_en_insert."', ''),swap_ce = 1, synonym_word = '".$dataSynonym['synonym_word']."', strict_synonym = '".$dataSynonym['strict_synonym']."', synonym_partial_1 = '".$dataSynonym['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonym['synonym_partial_2']."', synonym_general = '".$dataSynonym['synonym_general']."', synonym_minor = '".$dataSynonym['synonym_minor']."', synonym_nn = '".$dataSynonym['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
					$updateSymptom = "UPDATE $comparison_table_name $condition";
					$updateRes = $db->query($updateSymptom);
					$updateResult = 4;
				}else{
					//Disconnect swap earlier connected CE
					$updateResult = 5;
				}
				
				//Array declaration
				$connectionDataArray = array();
				$completeSendingArray = array();
				$comparingSymptomsArray = array();
				$data = array();
				$sendingArray = array();
				$updateHighestMatchSymptomIdArray = array();
				$testArray = array();	
				$earlierConnectionSaveArrayFinal = array();	
				$earlierConnectionSaveArrayFinalConnection = array();	
				$allQueryArrayFinal = array();

				if ($updateResult == 1) {

					// Data for connection table insertion start 
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

					//#2 Fetching the id of the initial symptom
					$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."'");
					if(mysqli_num_rows($fetchIdResult) > 0){
						$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
					}
					$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";

					//#3 Initial symptom information
					$InitialQuelleResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'");
					// echo json_encode( array( 'status' => 'ok') ); 
					// exit;
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							//Now informations are taken for comparison with comparative symptom
							$iniSymptomString_de="";
							$iniSymptomString_en="";
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

							if($fv_comparison_option == 1){
								$iniSymptomStringConnection_de =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
								$iniSymptomStringConnection_en =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
							}else{
								$iniSymptomStringConnection_de =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
								$iniSymptomStringConnection_en =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
							}
							// For connection table start
							$connectionDataArray['initial_quelle_id'] = $iniSymRow['quelle_id'];
							$connectionDataArray['initial_quelle_code'] = $iniSymRow['quelle_code'];
							$connectionDataArray['initial_quelle_original_language'] = $iniSymRow['initial_source_original_language'];
							$connectionDataArray['highlighted_initial_symptom_de'] = $iniSymptomString_de;
							$connectionDataArray['highlighted_initial_symptom_en'] = $iniSymptomString_en;
							// $connectionDataArray['initial_symptom_de'] = $iniSymptomString_de;
							// $connectionDataArray['initial_symptom_en'] = $iniSymptomString_en;
							$connectionDataArray['initial_year'] = $iniSymRow['quelle_jahr'];
							// For connection table end

							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymptomString_de != ""){
								$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
								// $iniSymptomString_de = base64_encode($iniSymptomString_de);
							}
							if($iniSymptomString_en != ""){
								$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
							}

							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;

							//variable taken to use in the modification of the connected earlier symptoms
							$ini_earlier = $ini;

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

							//#4 Updating symptom text of the comparative symptom
							$updateSymptom2 = "UPDATE $comparison_table_name SET swap_value_en = NULLIF('".$fv_symptom_en_insert_connect."', ''),swap_value_de = NULLIF('".$fv_symptom_de_insert_connect."', ''), swap = 1 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes2 = $db->query($updateSymptom2);

							//#5 Updating the symptom id of the conparative symptom with the initial symptom
							$updateSymptom3 = "UPDATE $comparison_table_name SET symptom_id = $initial_symptom_id WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes3 = $db->query($updateSymptom3);

							// Comparing symptoms
							//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
							$quelleComparingSymptomResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'");
							while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
								$compSymptomString_de="";
								$compSymptomString_en="";
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
								if($initial_symptom_id == $quelleComparingSymptomRow['symptom_id']){
									//For connection array ***start
									// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
									$highlightedComparingSymptom = "";
									$highlightedComparativeSymptomString_en = $compSymptomString_en;
									$highlightedComparativeSymptomString_de = $compSymptomString_de;
									if($comparison_language == "en"){
										$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
									}
									else{
										$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;
									}

									//Storing in connection array
									$connectionDataArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de;
									$connectionDataArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en;
									$connectionDataArray['comparing_symptom_de'] = strip_tags($compSymptomString_de);
									$connectionDataArray['comparing_symptom_en'] = strip_tags($compSymptomString_en);
									//For connection array ***end
								}	

								if($comparison_language == "en")
									$com = $compSymptomString_en;
								else
									$com = $compSymptomString_de;
								//$resultArray = newComareSymptom($ini, $com);
								$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
								$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
								$testArray[] = $resultArray;

								//#7 comparing the symptom texts with the initials for percentage
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
								if($initial_symptom_id == $quelleComparingSymptomRow['symptom_id']){
									//$resultArrayOnlyInitial = newComareSymptom($com, $ini);
									$resultArrayOnlyInitial = compareSymptomWithSynonyms($com, $ini, $globalStopWords, $initialSymptomsAllSynonyms);
									$highlightedInitialSymptom = (isset($resultArrayOnlyInitial['initial_source_symptom_highlighted']) AND $resultArrayOnlyInitial['initial_source_symptom_highlighted'] != "") ? $resultArrayOnlyInitial['initial_source_symptom_highlighted'] : "";
									$highlightedInitialSymptomString_en = $compSymptomString_en;
									$highlightedInitialSymptomString_de = $compSymptomString_de;

									if($comparison_language == "en")
										$highlightedInitialSymptomString_en = ($highlightedInitialSymptom != "") ? $highlightedInitialSymptom : $compSymptomString_en;
									else
										$highlightedInitialSymptomString_de = ($highlightedInitialSymptom != "") ? $highlightedInitialSymptom : $compSymptomString_de;
									
									$connectionDataArray['highlighted_initial_symptom_de'] = $highlightedInitialSymptomString_de;
									$connectionDataArray['highlighted_initial_symptom_en'] = $highlightedInitialSymptomString_en;
									
									$connectionDataArray['matched_percentage'] = $percentage;
									$connectionDataArray['comparing_quelle_id'] = $quelleComparingSymptomRow['quelle_id'];
									$connectionDataArray['comparing_quelle_code'] = $quelleComparingSymptomRow['quelle_code'];
									$connectionDataArray['comparing_quelle_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
									$connectionDataArray['comparing_year'] = $quelleComparingSymptomRow['quelle_jahr'];
								}
								// For connection table end

	
								// Id collect for highest match table update start
								// For before operation done percentage check
								if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage){
									if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
										array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
								}
								// For after operation done percentage check
								if($percentage >= $cutoff_percentage){
									if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
										array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
								}
								// Id collect for highest match table update end

								//#8 Storing all the comparing info in the data array
								$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
								$data['initial_symptom_id'] = $comparative_symptom_id;
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
								$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
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
								$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
								$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
								$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
								$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
								$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
								$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
								$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
								$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
								$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
								$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
								$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
								$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
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
								$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
								$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
								$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
								$data['matched_percentage'] = $percentage;
								$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
								$data['swap'] = $quelleComparingSymptomRow['swap'];
								$data['swap_value_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
								$data['swap_value_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
								$data['swap_ce'] = $quelleComparingSymptomRow['swap_ce'];
								$data['swap_value_ce_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
								$data['swap_value_ce_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
								$data['connection'] = $quelleComparingSymptomRow['connection'];
								

								//#9 Updating the quelle id, code, year, titel of the initial symptom
								if($quelleComparingSymptomRow['symptom_id']==$initial_symptom_id && $quelleComparingSymptomRow['initial_symptom_id']==$initial_symptom_id)
								{
									//edited here for previous swap connection table translation
									$connectionDataArray['comparing_symptom_de'] = strip_tags($compSymptomString_de);
									$connectionDataArray['comparing_symptom_en'] = strip_tags($compSymptomString_en);

									$updateInitialArr['SeiteOriginalVon'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalVon']);
									$updateInitialArr['SeiteOriginalBis'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalBis']);
									$updateInitialArr['Beschreibung_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_de']);
									$updateInitialArr['Beschreibung_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_en']);
									$updateInitialArr['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_de']);
									$updateInitialArr['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_en']);
									$updateInitialArr['BeschreibungFull_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
									$updateInitialArr['BeschreibungFull_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
									$updateInitialArr['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_de']);
									$updateInitialArr['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_en']);
									$updateInitialArr['searchable_text_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
									$updateInitialArr['searchable_text_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);
									

									//Updating swapped initial
									$updateSymptom4 = "UPDATE $comparison_table_name SET quelle_code = '".$quelleComparingSymptomRow['quelle_code']."',quelle_titel= '".$quelleComparingSymptomRow['quelle_titel']."',quelle_jahr='".$quelleComparingSymptomRow['quelle_jahr']."', quelle_id='".$quelleComparingSymptomRow['quelle_id']."', original_quelle_id='".$quelleComparingSymptomRow['original_quelle_id']."', Beschreibung_de = NULLIF('".$updateInitialArr['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateInitialArr['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateInitialArr['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateInitialArr['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateInitialArr['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateInitialArr['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateInitialArr['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateInitialArr['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateInitialArr['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateInitialArr['searchable_text_en']."', ''), quelle_type_id = NULLIF('".$quelleComparingSymptomRow['quelle_type_id']."', ''), quelle_band = NULLIF('".$quelleComparingSymptomRow['quelle_band']."', ''), quelle_auflage = NULLIF('".$quelleComparingSymptomRow['quelle_auflage']."', ''), quelle_autor_or_herausgeber = NULLIF('".$quelleComparingSymptomRow['quelle_autor_or_herausgeber']."', ''), arznei_id = NULLIF('".$quelleComparingSymptomRow['arznei_id']."', ''), Symptomnummer = NULLIF('".$quelleComparingSymptomRow['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateInitialArr['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateInitialArr['SeiteOriginalBis']."', ''), bracketedString_de = NULLIF('".$quelleComparingSymptomRow['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$quelleComparingSymptomRow['bracketedString_en']."', ''), timeString_de = NULLIF('".$quelleComparingSymptomRow['timeString_de']."', ''), timeString_en = NULLIF('".$quelleComparingSymptomRow['timeString_en']."', ''), initial_source_original_language = NULLIF('".$quelleComparingSymptomRow['initial_source_original_language']."', ''), comparing_source_original_language = NULLIF('".$quelleComparingSymptomRow['comparing_source_original_language']."', ''), ip_address = NULLIF('".$quelleComparingSymptomRow['ip_address']."', ''), stand = NULLIF('".$quelleComparingSymptomRow['stand']."', ''), bearbeiter_id = NULLIF('".$quelleComparingSymptomRow['bearbeiter_id']."', ''), ersteller_datum = NULLIF('".$quelleComparingSymptomRow['ersteller_datum']."', ''), ersteller_id = NULLIF('".$quelleComparingSymptomRow['ersteller_id']."', '') WHERE symptom_id = '".$initial_symptom_id."'";
									$updateRes4 = $db->query($updateSymptom4);
									
									// Finding match synonyms of swapped comparing START
									$arrangedSynonymDataComparing = array();
									$matchedSynonymsComparing = findMatchedSynonyms($searchableTextForComparing, $globalStopWords, $availableSynonyms);
									if((isset($matchedSynonymsComparing['status']) AND $matchedSynonymsComparing['status'] == true) AND (isset($matchedSynonymsComparing['return_data']) AND !empty($matchedSynonymsComparing['return_data']))){
										$arrangedSynonymDataComparing = arrangeSynonymDataToStore($matchedSynonymsComparing['return_data']);
									}

									$dataSynonymComparing['synonym_word'] = (isset($arrangedSynonymDataComparing['synonym_word']) AND !empty($arrangedSynonymDataComparing['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_word'])) : "";
									$dataSynonymComparing['strict_synonym'] = (isset($arrangedSynonymDataComparing['strict_synonym']) AND !empty($arrangedSynonymDataComparing['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['strict_synonym'])) : "";
									$dataSynonymComparing['synonym_partial_1'] = (isset($arrangedSynonymDataComparing['synonym_partial_1']) AND !empty($arrangedSynonymDataComparing['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_1'])) : "";
									$dataSynonymComparing['synonym_partial_2'] = (isset($arrangedSynonymDataComparing['synonym_partial_2']) AND !empty($arrangedSynonymDataComparing['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_2'])) : "";
									$dataSynonymComparing['synonym_general'] = (isset($arrangedSynonymDataComparing['synonym_general']) AND !empty($arrangedSynonymDataComparing['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_general'])) : "";
									$dataSynonymComparing['synonym_minor'] = (isset($arrangedSynonymDataComparing['synonym_minor']) AND !empty($arrangedSynonymDataComparing['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_minor'])) : "";
									$dataSynonymComparing['synonym_nn'] = (isset($arrangedSynonymDataComparing['synonym_nn']) AND !empty($arrangedSynonymDataComparing['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_nn'])) : "";
									// Finding match synonyms END

									//Updating swapped comparative
									$data['quelle_code'] = $iniSymRow['quelle_code'];
									$data['quelle_titel'] = $iniSymRow['quelle_titel'];
									$data['quelle_type_id'] = $iniSymRow['quelle_type_id'];
									$data['quelle_jahr'] = $iniSymRow['quelle_jahr'];
									$data['quelle_band'] = $iniSymRow['quelle_band'];
									$data['quelle_auflage'] = $iniSymRow['quelle_auflage'];
									$data['quelle_autor_or_herausgeber'] = $iniSymRow['quelle_autor_or_herausgeber'];
									$data['arznei_id'] = $iniSymRow['arznei_id'];
									$data['quelle_id'] = $iniSymRow['quelle_id'];
									$data['original_quelle_id'] = $iniSymRow['original_quelle_id'];
									$data['Symptomnummer'] = $iniSymRow['Symptomnummer'];
									$data['SeiteOriginalVon'] = mysqli_real_escape_string($db,$iniSymRow['SeiteOriginalVon']);
									$data['SeiteOriginalBis'] = mysqli_real_escape_string($db,$iniSymRow['SeiteOriginalBis']);
									$data['Beschreibung_de'] = mysqli_real_escape_string($db,$iniSymRow['Beschreibung_de']);
									$data['Beschreibung_en'] = mysqli_real_escape_string($db,$iniSymRow['Beschreibung_en']);
									$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungOriginal_de']);
									$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungOriginal_en']);
									$data['BeschreibungFull_de'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungFull_de']);
									$data['BeschreibungFull_en'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungFull_en']);
									$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungPlain_de']);
									$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db,$iniSymRow['BeschreibungPlain_en']);
									$data['searchable_text_de'] = mysqli_real_escape_string($db,$iniSymRow['searchable_text_de']);
									$data['searchable_text_en'] = mysqli_real_escape_string($db,$iniSymRow['searchable_text_en']);
									$data['bracketedString_de'] = mysqli_real_escape_string($db,$iniSymRow['bracketedString_de']);
									$data['bracketedString_en'] = mysqli_real_escape_string($db,$iniSymRow['bracketedString_en']);
									$data['timeString_de'] = $iniSymRow['timeString_de'];
									$data['timeString_en'] = $iniSymRow['timeString_en'];
									$data['initial_source_original_language'] = $iniSymRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $iniSymRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db,$iniSymRow['Fussnote']);
									$data['EntnommenAus'] = mysqli_real_escape_string($db,$iniSymRow['EntnommenAus']);
									$data['Verweiss'] = mysqli_real_escape_string($db,$iniSymRow['Verweiss']);
									$data['BereichID'] = mysqli_real_escape_string($db,$iniSymRow['BereichID']);
									$data['Kommentar'] = mysqli_real_escape_string($db,$iniSymRow['Kommentar']);
									$data['Unklarheiten'] = $iniSymRow['Unklarheiten'];
									$data['Remedy'] = $iniSymRow['Remedy'];
									$data['symptom_of_different_remedy'] = $iniSymRow['symptom_of_different_remedy'];
									$data['subChapter'] = $iniSymRow['subChapter'];
									$data['subSubChapter'] = $iniSymRow['subSubChapter'];
									$data['synonym_word'] = $dataSynonymComparing['synonym_word'];
									$data['strict_synonym'] = $dataSynonymComparing['strict_synonym'];
									$data['synonym_partial_1'] = $dataSynonymComparing['synonym_partial_1'];
									$data['synonym_partial_2'] = $dataSynonymComparing['synonym_partial_2'];
									$data['synonym_general'] = $dataSynonymComparing['synonym_general'];
									$data['synonym_minor'] = $dataSynonymComparing['synonym_minor'];
									$data['synonym_nn'] = $dataSynonymComparing['synonym_nn'];
									$data['symptom_edit_comment'] = $iniSymRow['symptom_edit_comment'];
									$data['is_final_version_available'] = 0;
									$data['ersteller_datum'] = $iniSymRow['ersteller_datum'];
									$data['swap'] = "1";
									$data['swap_value_en'] = $fv_symptom_en_insert_connect;
									$data['swap_value_de'] = $fv_symptom_de_insert_connect;

									$connectionDataArray['initial_symptom_de'] = strip_tags($iniSymptomString_de);
									$connectionDataArray['initial_symptom_en'] =strip_tags($iniSymptomString_en);	
								
								}
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
						}
					}

					
					if($operationFlag != 0){
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
						}
					}

					if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

						//#10 Deleting all the comparing symptoms under that initial
						$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$deleteRes = $db->query($deleteExistingComparingSymptoms);
						if($deleteRes == true){

							//#11 Updating the symptom id of the initial symptom with the comparative and also resetting it to earlier state
							$count = $rowIdToInsertFrom;
							$idModify = $count -1;
							$updateSymptom5 = "UPDATE $comparison_table_name SET symptom_id = $comparative_symptom_id WHERE id = $idModify";
							$updateRes5 = $db->query($updateSymptom5);

							//#12 Inserting comparatives below the initial from the data array 
							foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
								$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_final_version_available,matched_percentage, ersteller_datum, swap,swap_value_en,swap_value_de,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$comparative_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''),  NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['swap']."', ''), NULLIF('".$comparingRow['swap_value_en']."', ''),NULLIF('".$comparingRow['swap_value_de']."', ''), NULLIF('".$comparingRow['connection']."', ''))";
								$db->query($insertComparative);

								$count++;
							}
						}
					}
					
					
					
					
					// Inserting in connection in connections table
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$connectionDataArray['initial_symptom_id']."' AND comparing_symptom_id = '".$connectionDataArray['comparing_symptom_id']."'";
	            	$db->query($deleteExistingQuery);
	            	if($operation_type == "connectSWAP"){
	            		$insertConnection = "INSERT INTO ".$comparison_table_name."_connections (initial_symptom_id, comparing_symptom_id, connection_type, matched_percentage, ns_connect, ns_paste, ns_connect_comment, ns_paste_comment, initial_quelle_id, comparing_quelle_id, initial_quelle_code, comparing_quelle_code, initial_quelle_original_language, comparing_quelle_original_language, highlighted_initial_symptom_de, highlighted_initial_symptom_en, highlighted_comparing_symptom_de, highlighted_comparing_symptom_en, initial_symptom_de,  initial_symptom_en, comparing_symptom_de, comparing_symptom_en, comparison_language, initial_year, comparing_year, is_earlier_connection) VALUES (NULLIF('".$connectionDataArray['comparing_symptom_id']."', ''), NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), NULLIF('".$connectionDataArray['connection_type']."', ''), '".$connectionDataArray['matched_percentage']."', '".$connectionDataArray['ns_connect']."', '".$connectionDataArray['ns_paste']."', NULLIF('".$connectionDataArray['ns_connect_comment']."', ''), NULLIF('".$connectionDataArray['ns_paste_comment']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparison_language']."', ''), NULLIF('".$connectionDataArray['comparing_year']."', ''), NULLIF('".$connectionDataArray['initial_year']."', ''), NULLIF('".$connectionDataArray['is_earlier_connection']."', ''))";
						$db->query($insertConnection);
						//updating marking in the comparison table
						markingUpdation($db,$comparison_table_name,"1",$comparative_symptom_id);
	            	}
	            	
	            	if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							if($earlierConnectionFinalRow['operationFlag'] == 3 || $earlierConnectionFinalRow['operationFlag'] == 1){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
								
							}else if($earlierConnectionFinalRow['operationFlag'] == 2){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0'  WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

							}else if($earlierConnectionFinalRow['operationFlag'] == 4){

								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''),comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='0',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
							}else{
								//for paste and paste edit previous connection
								$updateSymptomEarlier = "DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

							}
							$allQueryArray = array();

							$allQueryArray['query'] = $updateSymptomEarlier;
							$allQueryArray['comparing_symptom_id'] = $earlierConnectionFinalRow['comparativeIdToSend'];
							$allQueryArray['initial_symptom_id'] = $earlierConnectionFinalRow['initialId'];
							$allQueryArrayFinal[] = $allQueryArray;

							$db->query($updateSymptomEarlier);

						}
					}

					// Updation in highest match table
					if(!empty($updateHighestMatchSymptomIdArray)){
						foreach ($updateHighestMatchSymptomIdArray as $symId) {
							// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
							$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
							if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

								$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
								$updateRes = $db->query($updateHighestMatchDetails);
							}
						}
					} 
				}
				elseif ($updateResult == 0){
					//Disconnect swap operation

					$runningInitialSymptomId = "";

					//#2 Fetching the ids below that initial symptom
					$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND initial_symptom_id IS NULL");
					if(mysqli_num_rows($fetchIdResult) > 0){
						$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
					}
					$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";
					
					
					//#3 Initial symptom information
					$initialQuery = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
					$InitialQuelleResult = mysqli_query($db,$initialQuery);
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){	
							
							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)

							//Informations are taken for comparison with comparative symptom
							if($iniSymRow['swap'] != 0){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
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

							
							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymRow['swap'] != 0){
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
								}
							}else{
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
								}
							}
								

							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;

							$ini_earlier = $ini;
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
							//#4 Updating symptom text of the comparative symptom
							$updateSymptom2 = "UPDATE $comparison_table_name SET swap_value_en = NULLIF('".$fv_symptom_en_insert_connect."', ''),swap_value_de = NULLIF('".$fv_symptom_de_insert_connect."', ''),swap = 1 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes2 = $db->query($updateSymptom2);

							//#5 Updating the symptom id of the conparative symptom with the initial symptom
							$updateSymptom3 = "UPDATE $comparison_table_name SET symptom_id = $initial_symptom_id WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes3 = $db->query($updateSymptom3);

							// Comparing symptoms
							//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
							$quelleQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
							$quelleComparingSymptomResult = mysqli_query($db,$quelleQuery);
							while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									
								if($quelleComparingSymptomRow['swap'] != 0){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
								} else{
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
								//$resultArray = newComareSymptom($ini, $com);
								$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
								$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
								$testArray[] = $resultArray;

								//#7 comparing the symptom texts with the initials for percentage
								$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
								$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
								// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
								$highlightedComparativeSymptomString_en = $compSymptomString_en;
								$highlightedComparativeSymptomString_de = $compSymptomString_de;
								if($comparison_language == "en")
									$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
								else
									$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;

								// For before operation done percentage check
								if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage){
									if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
										array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
								}
								// For after operation done percentage check
								if($percentage >= $cutoff_percentage){
									if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
										array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
								}
								// Id collect for highest match table update end

								//#8 Storing all the comparing info in the data array
								$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
								$data['initial_symptom_id'] = $comparative_symptom_id;
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
								$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
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
								$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
								$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
								$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
								$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
								$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
								$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
								$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
								$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
								$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
								$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
								$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
								$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
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
								$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
								$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
								$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
								$data['matched_percentage'] = $percentage;
								$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
								$data['connection'] = $quelleComparingSymptomRow['connection'];
								
								//#9 Updating the quelle id, code, year, titel of the initial symptom
								if($quelleComparingSymptomRow['symptom_id']==$initial_symptom_id && $quelleComparingSymptomRow['initial_symptom_id']==$initial_symptom_id)
								{
									
									$updateInitialArr['SeiteOriginalVon'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalVon']);
									$updateInitialArr['SeiteOriginalBis'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalBis']);
									$updateInitialArr['final_version_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_de']);
									$updateInitialArr['final_version_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_en']);
									$updateInitialArr['Beschreibung_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_de']);
									$updateInitialArr['Beschreibung_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_en']);
									$updateInitialArr['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_de']);
									$updateInitialArr['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_en']);
									$updateInitialArr['BeschreibungFull_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
									$updateInitialArr['BeschreibungFull_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
									$updateInitialArr['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_de']);
									$updateInitialArr['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_en']);
									$updateInitialArr['searchable_text_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
									$updateInitialArr['searchable_text_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);

									//Updating swapped initial
									$updateSymptom4 = "UPDATE $comparison_table_name SET quelle_code = '".$quelleComparingSymptomRow['quelle_code']."',quelle_titel= '".$quelleComparingSymptomRow['quelle_titel']."',quelle_jahr='".$quelleComparingSymptomRow['quelle_jahr']."', quelle_id='".$quelleComparingSymptomRow['quelle_id']."',original_quelle_id='".$quelleComparingSymptomRow['original_quelle_id']."', Beschreibung_de = NULLIF('".$updateInitialArr['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateInitialArr['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateInitialArr['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateInitialArr['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateInitialArr['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateInitialArr['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateInitialArr['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateInitialArr['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateInitialArr['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateInitialArr['searchable_text_en']."', ''), quelle_type_id = NULLIF('".$quelleComparingSymptomRow['quelle_type_id']."', ''), quelle_band = NULLIF('".$quelleComparingSymptomRow['quelle_band']."', ''), quelle_auflage = NULLIF('".$quelleComparingSymptomRow['quelle_auflage']."', ''), quelle_autor_or_herausgeber = NULLIF('".$quelleComparingSymptomRow['quelle_autor_or_herausgeber']."', ''), arznei_id = NULLIF('".$quelleComparingSymptomRow['arznei_id']."', ''), Symptomnummer = NULLIF('".$quelleComparingSymptomRow['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$quelleComparingSymptomRow['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$quelleComparingSymptomRow['SeiteOriginalBis']."', ''), bracketedString_de = NULLIF('".$quelleComparingSymptomRow['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$quelleComparingSymptomRow['bracketedString_en']."', ''), timeString_de = NULLIF('".$quelleComparingSymptomRow['timeString_de']."', ''), timeString_en = NULLIF('".$quelleComparingSymptomRow['timeString_en']."', ''), initial_source_original_language = NULLIF('".$quelleComparingSymptomRow['initial_source_original_language']."', ''), comparing_source_original_language = NULLIF('".$quelleComparingSymptomRow['comparing_source_original_language']."', ''), ip_address = NULLIF('".$quelleComparingSymptomRow['ip_address']."', ''), stand = NULLIF('".$quelleComparingSymptomRow['stand']."', ''), bearbeiter_id = NULLIF('".$quelleComparingSymptomRow['bearbeiter_id']."', ''), ersteller_datum = NULLIF('".$quelleComparingSymptomRow['ersteller_datum']."', ''), ersteller_id = NULLIF('".$quelleComparingSymptomRow['ersteller_id']."', '') WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
									$updateRes4 = $db->query($updateSymptom4);

									// Finding match synonyms of swapped comparing START
									$arrangedSynonymDataComparing = array();
									$matchedSynonymsComparing = findMatchedSynonyms($searchableTextForComparing, $globalStopWords, $availableSynonyms);
									if((isset($matchedSynonymsComparing['status']) AND $matchedSynonymsComparing['status'] == true) AND (isset($matchedSynonymsComparing['return_data']) AND !empty($matchedSynonymsComparing['return_data']))){
										$arrangedSynonymDataComparing = arrangeSynonymDataToStore($matchedSynonymsComparing['return_data']);
									}

									$dataSynonymComparing['synonym_word'] = (isset($arrangedSynonymDataComparing['synonym_word']) AND !empty($arrangedSynonymDataComparing['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_word'])) : "";
									$dataSynonymComparing['strict_synonym'] = (isset($arrangedSynonymDataComparing['strict_synonym']) AND !empty($arrangedSynonymDataComparing['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['strict_synonym'])) : "";
									$dataSynonymComparing['synonym_partial_1'] = (isset($arrangedSynonymDataComparing['synonym_partial_1']) AND !empty($arrangedSynonymDataComparing['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_1'])) : "";
									$dataSynonymComparing['synonym_partial_2'] = (isset($arrangedSynonymDataComparing['synonym_partial_2']) AND !empty($arrangedSynonymDataComparing['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_2'])) : "";
									$dataSynonymComparing['synonym_general'] = (isset($arrangedSynonymDataComparing['synonym_general']) AND !empty($arrangedSynonymDataComparing['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_general'])) : "";
									$dataSynonymComparing['synonym_minor'] = (isset($arrangedSynonymDataComparing['synonym_minor']) AND !empty($arrangedSynonymDataComparing['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_minor'])) : "";
									$dataSynonymComparing['synonym_nn'] = (isset($arrangedSynonymDataComparing['synonym_nn']) AND !empty($arrangedSynonymDataComparing['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_nn'])) : "";
									// Finding match synonyms END

									//Updating swapped comparative
									$data['quelle_code'] = $iniSymRow['quelle_code'];
									$data['quelle_titel'] = $iniSymRow['quelle_titel'];
									$data['quelle_type_id'] = $iniSymRow['quelle_type_id'];
									$data['quelle_jahr'] = $iniSymRow['quelle_jahr'];
									$data['quelle_band'] = $iniSymRow['quelle_band'];
									$data['quelle_auflage'] = $iniSymRow['quelle_auflage'];
									$data['quelle_autor_or_herausgeber'] = $iniSymRow['quelle_autor_or_herausgeber'];
									$data['arznei_id'] = $iniSymRow['arznei_id'];
									$data['quelle_id'] = $iniSymRow['quelle_id'];
									$data['original_quelle_id'] = $iniSymRow['original_quelle_id'];
									$data['Symptomnummer'] = $iniSymRow['Symptomnummer'];
									$data['SeiteOriginalVon'] = $iniSymRow['SeiteOriginalVon'];
									$data['SeiteOriginalBis'] = $iniSymRow['SeiteOriginalBis'];
									$data['swap_value_en'] = "";
									$data['swap_value_de'] = "";
									$data['swap'] = "";
									$data['Beschreibung_de'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_de']);
									$data['Beschreibung_en'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_en']);
									$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_de']);
									$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_en']);
									$data['BeschreibungFull_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
									$data['BeschreibungFull_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
									$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_de']);
									$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_en']);
									$data['searchable_text_de'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
									$data['searchable_text_en'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
									$data['bracketedString_de'] = mysqli_real_escape_string($db, $iniSymRow['bracketedString_de']);
									$data['bracketedString_en'] = mysqli_real_escape_string($db, $iniSymRow['bracketedString_en']);
									$data['timeString_de'] = $iniSymRow['timeString_de'];
									$data['timeString_en'] = $iniSymRow['timeString_en'];
									$data['initial_source_original_language'] = $iniSymRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $iniSymRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db, $iniSymRow['Fussnote']);
									$data['EntnommenAus'] = $iniSymRow['EntnommenAus'];
									$data['Verweiss'] = $iniSymRow['Verweiss'];
									$data['BereichID'] = $iniSymRow['BereichID'];
									$data['Kommentar'] = mysqli_real_escape_string($db, $iniSymRow['Kommentar']);
									$data['Unklarheiten'] = $iniSymRow['Unklarheiten'];
									$data['Remedy'] = $iniSymRow['Remedy'];
									$data['symptom_of_different_remedy'] = $iniSymRow['symptom_of_different_remedy'];
									$data['subChapter'] = $iniSymRow['subChapter'];
									$data['subSubChapter'] = $iniSymRow['subSubChapter'];
									$data['synonym_word'] = $dataSynonymComparing['synonym_word'];
									$data['strict_synonym'] = $dataSynonymComparing['strict_synonym'];
									$data['synonym_partial_1'] = $dataSynonymComparing['synonym_partial_1'];
									$data['synonym_partial_2'] = $dataSynonymComparing['synonym_partial_2'];
									$data['synonym_general'] = $dataSynonymComparing['synonym_general'];
									$data['synonym_minor'] = $dataSynonymComparing['synonym_minor'];
									$data['synonym_nn'] = $dataSynonymComparing['synonym_nn'];
									$data['symptom_edit_comment'] = $iniSymRow['symptom_edit_comment'];
									$data['is_final_version_available'] = 0;
									$data['ersteller_datum'] = $iniSymRow['ersteller_datum'];
								}

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
						}
					}

					if($operationFlag != 0){
						$queryComEarlierConnection = "SELECT comparing_symptom_id,connection_type,is_earlier_connection FROM $comparison_table_name".'_connections'." WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$quelleComparingSymptomResultEarlierConnection = mysqli_query($db,$queryComEarlierConnection);
						//since the swap connection itself will be selected so min num rows > 1 chosen
						if(mysqli_num_rows($quelleComparingSymptomResultEarlierConnection) > 1){
							$arrayForEarlierConnectionData=array();
								$earlyPe = 0;
								while($quelleComparingSymptomRowEarlierConnection = mysqli_fetch_array($quelleComparingSymptomResultEarlierConnection)){
									if($quelleComparingSymptomRowEarlierConnection['connection_type'] == 'PE' || $quelleComparingSymptomRowEarlierConnection['connection_type'] == 'paste'){
										if($quelleComparingSymptomRowEarlierConnection['is_earlier_connection'] == '1'){
											//not taking erlier pe and p connection
											$earlyPe = 1;
										}
									}
									if($earlyPe == 0){
										$arrayForEarlierConnectionData['comparing_symptom_id'] = $quelleComparingSymptomRowEarlierConnection['comparing_symptom_id'];
										$arrayForEarlierConnectionData['connection_type'] = $quelleComparingSymptomRowEarlierConnection['connection_type'];
										
										$earlierConnectionSaveArrayFinalConnection[] = $arrayForEarlierConnectionData;
									}	
								}	
						}

							
					}
					if(!empty($earlierConnectionSaveArrayFinalConnection)){
						foreach ($earlierConnectionSaveArrayFinalConnection as $key) {
							$earlier_comparative_symptom = $key['comparing_symptom_id'];
							
							$earlierCheckInitial = "SELECT is_initial_symptom FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' LIMIT 1";
							$resultEarlierCheckInitial = mysqli_query($db,$earlierCheckInitial);
							if(mysqli_num_rows($resultEarlierCheckInitial) > 0){
								while($rowEarlierCheckInitial = mysqli_fetch_array($resultEarlierCheckInitial)){
									if($rowEarlierCheckInitial['is_initial_symptom']==1){
										$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
									}
									else{
										$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
									}

								}
							}

							$allQueryArray = array();

							$allQueryArray['query'] = $queryComEarlier;
							$allQueryArray['queryUpper'] = $earlierCheckInitial;
							$allQueryArrayFinal[] = $allQueryArray;
							
							$quelleComparingSymptomResultEarlier = mysqli_query($db,$queryComEarlier);
							if(mysqli_num_rows($quelleComparingSymptomResultEarlier) > 0){
								while($quelleComparingSymptomRowEarlier = mysqli_fetch_array($quelleComparingSymptomResultEarlier)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($quelleComparingSymptomRowEarlier['is_final_version_available'] != 0){
										$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_de']);
										$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_en']);
									}else{
										if($fv_comparison_option == 1){
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_en']);
										}else{
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_en']);
										}
									}
									
									// Apply dynamic conversion
									if($compSymptomString_de_ealier != ""){
										$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en_ealier != ""){
										$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
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
									$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRowEarlier['symptom_id'];
									$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparing_symptom_id'];
									// $earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
									// $earlierConnectionSaveArray['initialId'] = $key['initialId'];
									$earlierConnectionSaveArray['connection_type'] = $key['connection_type'];
									$earlierConnectionSaveArray['matched_percentage'] = $percentageEarlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de_earlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en_earlier;

									//inserting into array 
									$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
								}
							}
						}
					}

					if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

						//#10 Deleting all the comparing symptoms under that initial
						$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$deleteRes = $db->query($deleteExistingComparingSymptoms);
						if($deleteRes == true){

							//#11 Updating the symptom id of the initial symptom with the comparative
							$count = $rowIdToInsertFrom;
							$idModify = $count -1;
							$updateSymptom5 = "UPDATE $comparison_table_name SET symptom_id = $comparative_symptom_id,swap_value_en = NULL,swap_value_de = NULL, swap = NULL WHERE id = $idModify";
							$updateRes5 = $db->query($updateSymptom5);

							//#12 Inserting comparatives below the initial from the data array 
							foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
								$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_final_version_available, matched_percentage, ersteller_datum,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$comparative_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''), NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['connection']."', ''))";
								$db->query($insertComparative);

								$count++;
							}
						}
					}

					// Update in connection in connections table
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
	            	$db->query($deleteExistingQuery);
	            	//updating marking in the comparison table
					markingUpdation($db,$comparison_table_name,"0",$comparative_symptom_id);

	            	if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							// if($earlierConnectionFinalRow['connection_type']!= 'swap'){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$initial_symptom_id."'";
								$db->query($updateSymptomEarlier);
							// }
						}
								
					}
            		
					// Updation in highest match table
					if(!empty($updateHighestMatchSymptomIdArray)){
						foreach ($updateHighestMatchSymptomIdArray as $symId) {
							// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
							$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
							if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

								$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
								$updateRes = $db->query($updateHighestMatchDetails);
							}
						}
					} 
				}
				elseif ($updateResult == 2){
					if($operationFlag == 10){
						//connection field made 0 
						$updateSymptom = "UPDATE $comparison_table_name SET connection = '0',swap=NULL,swap_value_en=NULL,swap_value_de=NULL WHERE symptom_id = '".$comparative_symptom_id."'";
						$updateRes = $db->query($updateSymptom);

						$updateSymptom2 = "UPDATE $comparison_table_name SET connection = '0',swap=NULL,swap_value_en=NULL,swap_value_de=NULL WHERE symptom_id = '".$initial_symptom_id."'";
						$updateRes2 = $db->query($updateSymptom2);

						//Deleting from connections table
						// Update in connection in connections table
						$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
		            	$db->query($deleteExistingQuery);
					}else{
						//connection field made 0 
						$updateSymptom = "UPDATE $comparison_table_name SET connection = '0',swap=NULL,swap_value_en=NULL,swap_value_de=NULL WHERE symptom_id = '".$comparative_symptom_id."'";
						$updateRes = $db->query($updateSymptom);

						$updateSymptom2 = "UPDATE $comparison_table_name SET connection = '0',swap=NULL,swap_value_en=NULL,swap_value_de=NULL WHERE symptom_id = '".$initial_symptom_id."'";
						$updateRes2 = $db->query($updateSymptom2);

		            	/////////////////////////////////////////////
		            	//Disconnect swap operation

						$runningInitialSymptomId = "";

						//#2 Fetching the ids below that initial symptom
						$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND initial_symptom_id IS NULL");
						if(mysqli_num_rows($fetchIdResult) > 0){
							$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
						}
						$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";
						
						
						//#3 Initial symptom information
						$initialQuery = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
						$InitialQuelleResult = mysqli_query($db,$initialQuery);
						if(mysqli_num_rows($InitialQuelleResult) > 0){
							while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){	
								
								$runningInitialSymptomId = $iniSymRow['symptom_id'];
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)

								//Informations are taken for comparison with comparative symptom
								if($iniSymRow['swap'] != 0){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
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

								
								// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
								$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
								$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
								
								// Apply dynamic conversion (this string is used in displying the symptom)
								if($iniSymRow['swap'] != 0){
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
										// $iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
									}
								}else{
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
										// $iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
									}
								}
									

								if($comparison_language == "en")
									$ini = $iniSymptomString_en;
								else
									$ini = $iniSymptomString_de;

								$ini_earlier = $ini;
								$searchableText = $ini;
								// Finding match synonyms START
								$arrangedSynonymInitialData = array();
								$matchedSynonymsInitial = findMatchedSynonyms($searchableText, $globalStopWords, $availableSynonyms);
								if((isset($matchedSynonymsInitial['status']) AND $matchedSynonymsInitial['status'] == true) AND (isset($matchedSynonymsInitial['return_data']) AND !empty($matchedSynonymsInitial['return_data']))){
									$arrangedSynonymInitialData = arrangeSynonymDataToStore($matchedSynonymsInitial['return_data']);
								}

								$dataSynonymInitial['synonym_word'] = (isset($arrangedSynonymInitialData['synonym_word']) AND !empty($arrangedSynonymInitialData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_word'])) : "";
								$dataSynonymInitial['strict_synonym'] = (isset($arrangedSynonymInitialData['strict_synonym']) AND !empty($arrangedSynonymInitialData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['strict_synonym'])) : "";
								$dataSynonymInitial['synonym_partial_1'] = (isset($arrangedSynonymInitialData['synonym_partial_1']) AND !empty($arrangedSynonymInitialData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_partial_1'])) : "";
								$dataSynonymInitial['synonym_partial_2'] = (isset($arrangedSynonymInitialData['synonym_partial_2']) AND !empty($arrangedSynonymInitialData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_partial_2'])) : "";
								$dataSynonymInitial['synonym_general'] = (isset($arrangedSynonymInitialData['synonym_general']) AND !empty($arrangedSynonymInitialData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_general'])) : "";
								$dataSynonymInitial['synonym_minor'] = (isset($arrangedSynonymInitialData['synonym_minor']) AND !empty($arrangedSynonymInitialData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_minor'])) : "";
								$dataSynonymInitial['synonym_nn'] = (isset($arrangedSynonymInitialData['synonym_nn']) AND !empty($arrangedSynonymInitialData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_nn'])) : "";
								// Finding match synonyms END

								//Taking unescape datas
								$matchInitialArry['synonym_word'] = serialize($arrangedSynonymInitialData['synonym_word']); 
								$matchInitialArry['strict_synonym'] = serialize($arrangedSynonymInitialData['strict_synonym']); 
								$matchInitialArry['synonym_partial_1'] = serialize($arrangedSynonymInitialData['synonym_partial_1']); 
								$matchInitialArry['synonym_partial_2'] = serialize($arrangedSynonymInitialData['synonym_partial_2']); 
								$matchInitialArry['synonym_general'] = serialize($arrangedSynonymInitialData['synonym_general']); 
								$matchInitialArry['synonym_minor'] = serialize($arrangedSynonymInitialData['synonym_minor']); 
								$matchInitialArry['synonym_nn'] = serialize($arrangedSynonymInitialData['synonym_nn']); 

								// Collecting Synonyms of this Symptom START
								$initialSymptomsAllSynonyms = array();
								$wordSynonyms = array();
								$strictSynonyms = array();
								$partial1Synonyms = array();
								$partial2Synonyms = array();
								$generalSynonyms = array();
								$minorSynonyms = array();
								$nnSynonyms = array();
								if(!empty($matchInitialArry['synonym_word'])){
									$wordSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_word']);
									$wordSynonyms = (!empty($wordSynonyms)) ? $wordSynonyms : array(); 
								}
								if(!empty($matchInitialArry['strict_synonym'])){
									$strictSynonyms = getAllOrganizeSynonyms($matchInitialArry['strict_synonym']);
									$strictSynonyms = (!empty($strictSynonyms)) ? $strictSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_partial_1'])){
									$partial1Synonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_partial_1']);
									$partial1Synonyms = (!empty($partial1Synonyms)) ? $partial1Synonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_partial_2'])){
									$partial2Synonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_partial_2']);
									$partial2Synonyms = (!empty($partial2Synonyms)) ? $partial2Synonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_general'])){
									$generalSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_general']);
									$generalSynonyms = (!empty($generalSynonyms)) ? $generalSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_minor'])){
									$minorSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_minor']);
									$minorSynonyms = (!empty($minorSynonyms)) ? $minorSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_nn'])){
									$nnSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_nn']);
									$nnSynonyms = (!empty($nnSynonyms)) ? $nnSynonyms : array(); 
								}
								$initialSymptomsAllSynonyms = array_merge($wordSynonyms, $strictSynonyms, $partial1Synonyms, $partial2Synonyms, $generalSynonyms, $minorSynonyms, $nnSynonyms);
								// Collecting Synonyms of this Symptom END

								// Comparing symptoms
								//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
								$quelleQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
								$quelleComparingSymptomResult = mysqli_query($db,$quelleQuery);
								while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										
									if($quelleComparingSymptomRow['swap'] != 0){
										$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
										$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
									} else{
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
									//$resultArray = newComareSymptom($ini, $com);
									$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
									$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
									$testArray[] = $resultArray;

									//#7 comparing the symptom texts with the initials for percentage
									$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
									$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
									// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
									$highlightedComparativeSymptomString_en = $compSymptomString_en;
									$highlightedComparativeSymptomString_de = $compSymptomString_de;
									if($comparison_language == "en")
										$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
									else
										$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;

									// For before operation done percentage check
									if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage){
										if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
											array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
									}
									// For after operation done percentage check
									if($percentage >= $cutoff_percentage){
										if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
											array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
									}
									// Id collect for highest match table update end

									//#8 Storing all the comparing info in the data array
									$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
									$data['initial_symptom_id'] = $initial_symptom_id;
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
									$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
									$data['Symptomnummer'] = $quelleComparingSymptomRow['Symptomnummer'];
									$data['SeiteOriginalVon'] = $quelleComparingSymptomRow['SeiteOriginalVon'];
									$data['SeiteOriginalBis'] = $quelleComparingSymptomRow['SeiteOriginalBis'];
									$data['final_version_de'] = $quelleComparingSymptomRow['final_version_de'];
									$data['final_version_en'] = $quelleComparingSymptomRow['final_version_en'];
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
									$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
									$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
									$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
									$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
									$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
									$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
									$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
									$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
									$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
									$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
									$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
									$data['symptom_of_different_remedy'] = $quelleComparingSymptomRow['symptom_of_different_remedy'];
									$data['subChapter'] = $quelleComparingSymptomRow['subChapter'];
									$data['subSubChapter'] = $quelleComparingSymptomRow['subSubChapter'];
									$data['synonym_word'] = $quelleComparingSymptomRow['synonym_word'];
									$data['strict_synonym'] = $quelleComparingSymptomRow['strict_synonym'];
									$data['synonym_partial_1'] = $quelleComparingSymptomRow['synonym_partial_1'];
									$data['synonym_partial_2'] = $quelleComparingSymptomRow['synonym_partial_2'];
									$data['synonym_general'] = $quelleComparingSymptomRow['synonym_general'];
									$data['synonym_minor'] = $quelleComparingSymptomRow['synonym_minor'];
									$data['synonym_nn'] = $quelleComparingSymptomRow['synonym_nn'];
									$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
									$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
									$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
									$data['matched_percentage'] = $percentage;
									$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
									$data['connection'] = $quelleComparingSymptomRow['connection'];

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
							}
						}

						if($operationFlag != 0){
							$queryComEarlierConnection = "SELECT comparing_symptom_id,connection_type FROM $comparison_table_name".'_connections'." WHERE initial_symptom_id = '".$initial_symptom_id."'";
							$quelleComparingSymptomResultEarlierConnection = mysqli_query($db,$queryComEarlierConnection);
							//since the swap connection itself will be selected so min num rows > 1 chosen
							if(mysqli_num_rows($quelleComparingSymptomResultEarlierConnection) > 1){
								$arrayForEarlierConnectionData=array();
									while($quelleComparingSymptomRowEarlierConnection = mysqli_fetch_array($quelleComparingSymptomResultEarlierConnection)){
										$arrayForEarlierConnectionData['comparing_symptom_id'] = $quelleComparingSymptomRowEarlierConnection['comparing_symptom_id'];
										$arrayForEarlierConnectionData['connection_type'] = $quelleComparingSymptomRowEarlierConnection['connection_type'];
										
										$earlierConnectionSaveArrayFinalConnection[] = $arrayForEarlierConnectionData;
									}	
							}		
						}

						if(!empty($earlierConnectionSaveArrayFinalConnection)){
							foreach ($earlierConnectionSaveArrayFinalConnection as $key) {
								$earlier_comparative_symptom = $key['comparing_symptom_id'];
								
								$earlierCheckInitial = "SELECT is_initial_symptom FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' LIMIT 1";
								$resultEarlierCheckInitial = mysqli_query($db,$earlierCheckInitial);
								if(mysqli_num_rows($resultEarlierCheckInitial) > 0){
									while($rowEarlierCheckInitial = mysqli_fetch_array($resultEarlierCheckInitial)){
										if($rowEarlierCheckInitial['is_initial_symptom']==1){
											$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
										}
										else{
											$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
										}

									}
								}

								$allQueryArray = array();

								$allQueryArray['query'] = $queryComEarlier;
								$allQueryArray['queryUpper'] = $earlierCheckInitial;
								$allQueryArrayFinal[] = $allQueryArray;
								
								$quelleComparingSymptomResultEarlier = mysqli_query($db,$queryComEarlier);
								if(mysqli_num_rows($quelleComparingSymptomResultEarlier) > 0){
									while($quelleComparingSymptomRowEarlier = mysqli_fetch_array($quelleComparingSymptomResultEarlier)){
										// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										if($quelleComparingSymptomRowEarlier['is_final_version_available'] != 0){
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_en']);
										}else{
											if($fv_comparison_option == 1){
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_en']);
											}else{
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_en']);
											}
										}
										
										// Apply dynamic conversion
										if($compSymptomString_de_ealier != ""){
											$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en_ealier != ""){
											$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
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
										$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRowEarlier['symptom_id'];
										$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparing_symptom_id'];
										// $earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
										// $earlierConnectionSaveArray['initialId'] = $key['initialId'];
										$earlierConnectionSaveArray['connection_type'] = $key['connection_type'];
										$earlierConnectionSaveArray['matched_percentage'] = $percentageEarlier;
										$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de_earlier;
										$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en_earlier;

										//inserting into array 
										$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
									}
								}
							}
						}

						if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

							//#10 Deleting all the comparing symptoms under that initial
							$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
							$deleteRes = $db->query($deleteExistingComparingSymptoms);
							if($deleteRes == true){
								$count = $rowIdToInsertFrom;
								$idModify = $count -1;

								//#12 Inserting comparatives below the initial from the data array 
								foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
									$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_final_version_available, matched_percentage, ersteller_datum,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$initial_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''),NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['connection']."', ''))";
									$db->query($insertComparative);

									$count++;
								}

								//Updting the synonyms in the initial symptom
								$condition = "SET synonym_word = '".$dataSynonymInitial['synonym_word']."', strict_synonym = '".$dataSynonymInitial['strict_synonym']."', synonym_partial_1 = '".$dataSynonymInitial['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonymInitial['synonym_partial_2']."', synonym_general = '".$dataSynonymInitial['synonym_general']."', synonym_minor = '".$dataSynonymInitial['synonym_minor']."', synonym_nn = '".$dataSynonymInitial['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
								$updateSymptomInitial = "UPDATE $comparison_table_name $condition";
								$updateRes = $db->query($updateSymptomInitial);
							}
						}
						
						// Update in connection in connections table
						$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
		            	$db->query($deleteExistingQuery);


		            	if(!empty($earlierConnectionSaveArrayFinal)){
							foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
								if($earlierConnectionFinalRow['connection_type']!= 'swap'){
									$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$initial_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', '') WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$initial_symptom_id."'";
									$db->query($updateSymptomEarlier);
								}
							}
									
						}
	            		
						// Updation in highest match table
						if(!empty($updateHighestMatchSymptomIdArray)){
							foreach ($updateHighestMatchSymptomIdArray as $symId) {
								// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
								$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
								if(mysqli_num_rows($fetchHighestMatchResult) > 0){
									$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

									$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
									$updateRes = $db->query($updateHighestMatchDetails);
								}
							}
						} 
					}		
				}
				elseif ($updateResult == 3){
					//Connect edit swap
					// Data for connection table insertion start 
					$connection_type = "swapCE";
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

					//#2 Fetching the ids below that initial symptom
					$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."'");
					if(mysqli_num_rows($fetchIdResult) > 0){
						$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
					}
					$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";
					
					//#3 Initial symptom information
					$InitialQuelleResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'");
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							//First informations are taken for the connection array ***start
							if($fv_comparison_option == 1){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
							}else{
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
							}

							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymptomString_de != ""){
								$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
								// $iniSymptomString_de = base64_encode($iniSymptomString_de);
							}
							if($iniSymptomString_en != ""){
								$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
							}

							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;

							//Informations are taken for the connection array ***end
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

							//Now informations are taken for comparison with comparative symptom
							$iniSymptomString_de="";
							$iniSymptomString_en="";
							if($iniSymRow['swap_ce'] != 0){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
							}else if($iniSymRow['swap'] != 0){
								$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
								$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
							}else if($iniSymRow['is_final_version_available'] != 0){
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

							// For connection table start
							$connectionDataArray['initial_quelle_id'] = $iniSymRow['quelle_id'];
							$connectionDataArray['initial_quelle_code'] = $iniSymRow['quelle_code'];
							$connectionDataArray['initial_quelle_original_language'] = $iniSymRow['initial_source_original_language'];
							$connectionDataArray['initial_symptom_de'] = strip_tags($iniSymptomString_de);
							$connectionDataArray['initial_symptom_en'] = strip_tags($iniSymptomString_en);
							$connectionDataArray['initial_year'] = $iniSymRow['quelle_jahr'];
							// For connection table end

							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymRow['swap_ce'] != 0){
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
								}
							}else if($iniSymRow['swap'] != 0){
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
								}
							}else{
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
								}
							}

							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;
							$ini_earlier = $ini;

							//#4 Updating symptom text of the comparative symptom
							$updateSymptom2 = "UPDATE $comparison_table_name SET swap_value_ce_de = NULLIF('".$fv_symptom_initial_de."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_initial_en."', ''), swap_ce = 1 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes2 = $db->query($updateSymptom2);

							//#5 Updating the symptom id of the comparative symptom with the initial symptom
							$updateSymptom3 = "UPDATE $comparison_table_name SET symptom_id = $initial_symptom_id WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes3 = $db->query($updateSymptom3);
							
							// Comparing symptoms
							//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
							$quelleComparingSymptomResult = mysqli_query($db,"SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'");
							while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
								if($initial_symptom_id == $quelleComparingSymptomRow['symptom_id']){
									//For connection array ***start
									$compSymptomString_de="";
									$compSymptomString_en="";
									if($quelleComparingSymptomRow['swap_ce'] != 0){
										$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
										$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
									}else if($quelleComparingSymptomRow['swap'] != 0){
										$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
										$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
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

									// comparing source symptom string Before convertion(this string is used to store in the connecteion table)  
									$compSymptomStringBeforeConversion_de = ($compSymptomString_de != "") ? base64_encode($compSymptomString_de) : "";
									$compSymptomStringBeforeConversion_en = ($compSymptomString_en != "") ? base64_encode($compSymptomString_en) : "";

									// Apply dynamic conversion
									if($quelleComparingSymptomRow['swap_ce'] != 0){
										if($compSymptomString_de != ""){
											$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en != ""){
											$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_en = base64_encode($compSymptomString_en);
										}
									}else if($quelleComparingSymptomRow['swap'] != 0){
										if($compSymptomString_de != ""){
											$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en != ""){
											$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
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
										

									// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
									$highlightedComparingSymptom = "";
									$highlightedComparativeSymptomString_en = $compSymptomString_en;
									$highlightedComparativeSymptomString_de = $compSymptomString_de;
									if($comparison_language == "en"){
										$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
									}
									else{
										$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;
									}

									
								}
								$compSymptomString_de="";
								$compSymptomString_en="";
								if($quelleComparingSymptomRow['swap_ce'] != 0){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
								}else if($quelleComparingSymptomRow['swap'] != 0){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
								}else if($quelleComparingSymptomRow['is_final_version_available'] != 0){
									$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_de']);
									$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['final_version_en']);
								} else {
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
								if($quelleComparingSymptomRow['swap_ce'] != 0){
									if($compSymptomString_de != ""){
										$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en != ""){
										$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_en = base64_encode($compSymptomString_en);
									}
								}else if($quelleComparingSymptomRow['swap'] != 0){
									if($compSymptomString_de != ""){
										$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en != ""){
										$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
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
								//$resultArray = newComareSymptom($ini, $com);
								$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
								$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
								$testArray[] = $resultArray;

								//#7 comparing the symptom texts with the initials for percentage
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
								if($initial_symptom_id == $quelleComparingSymptomRow['symptom_id']){
									//$resultArrayOnlyInitial = newComareSymptom($com, $ini);
									$resultArrayOnlyInitial = compareSymptomWithSynonyms($com, $ini, $globalStopWords, $initialSymptomsAllSynonyms);
									$highlightedInitialSymptomForConnectionTable = (isset($resultArrayOnlyInitial['initial_source_symptom_highlighted']) AND $resultArrayOnlyInitial['initial_source_symptom_highlighted'] != "") ? $resultArrayOnlyInitial['initial_source_symptom_highlighted'] : "";
									$highlightedComparingSymptomForConnectionTable = (isset($resultArrayOnlyInitial['comparing_source_symptom_highlighted']) AND $resultArrayOnlyInitial['comparing_source_symptom_highlighted'] != "") ? $resultArrayOnlyInitial['comparing_source_symptom_highlighted'] : "";
									$highlightedInitialSymptomString_en = $iniSymptomString_en;
									$highlightedInitialSymptomString_de = $iniSymptomString_de;

									$highlightedComparativeSymptomString_en_connect = $compSymptomString_en;
									$highlightedComparativeSymptomString_de_connect = $compSymptomString_de;

									if($comparison_language == "en"){
										$highlightedInitialSymptomString_en = ($highlightedInitialSymptomForConnectionTable != "") ? $highlightedInitialSymptomForConnectionTable : $iniSymptomString_en;
										$highlightedComparativeSymptomString_en_connect = ($highlightedComparingSymptomForConnectionTable != "") ? $highlightedComparingSymptomForConnectionTable : $compSymptomString_en;
									}
									else{
										$highlightedInitialSymptomString_de = ($highlightedInitialSymptomForConnectionTable != "") ? $highlightedInitialSymptomForConnectionTable : $iniSymptomString_de;
										$highlightedComparativeSymptomString_de_connect = ($highlightedComparingSymptomForConnectionTable != "") ? $highlightedComparingSymptomForConnectionTable : $compSymptomString_de;
									}
									
									
									//Storing in connection array
									if($comparison_language == "de"){
										$connectionDataArray['highlighted_initial_symptom_de'] = $highlightedInitialSymptomForConnectionTable;
										$connectionDataArray['highlighted_initial_symptom_en'] = strip_tags($fv_symptom_initial_en_insert);
									}else{
										$connectionDataArray['highlighted_initial_symptom_en'] = $highlightedInitialSymptomForConnectionTable;
										$connectionDataArray['highlighted_initial_symptom_de'] = strip_tags($fv_symptom_initial_de_insert);
									}
										
									$connectionDataArray['comparing_symptom_de'] = strip_tags($fv_symptom_initial_de_insert);
									$connectionDataArray['comparing_symptom_en'] = strip_tags($fv_symptom_initial_en_insert);
									//For connection array ***end

									$connectionDataArray['matched_percentage'] = $percentage;
									$connectionDataArray['comparing_quelle_id'] = $quelleComparingSymptomRow['quelle_id'];
									$connectionDataArray['comparing_quelle_code'] = $quelleComparingSymptomRow['quelle_code'];
									$connectionDataArray['comparing_quelle_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
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
								// Id collect for highest match table update end

								//#8 Storing all the comparing info in the data array
								$data = array();
								$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
								$data['initial_symptom_id'] = $comparative_symptom_id;
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
								$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
								$data['Symptomnummer'] = $quelleComparingSymptomRow['Symptomnummer'];
								$data['SeiteOriginalVon'] = $quelleComparingSymptomRow['SeiteOriginalVon'];
								$data['SeiteOriginalBis'] = $quelleComparingSymptomRow['SeiteOriginalBis'];
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
								$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
								$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
								$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
								$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
								$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
								$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
								$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
								$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
								$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
								$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
								$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
								$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
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
								$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
								$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
								$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
								$data['matched_percentage'] = $percentage;
								$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
								$data['connection'] = $quelleComparingSymptomRow['connection'];
								$data['swap_ce'] = $quelleComparingSymptomRow['swap_ce'];
								$data['swap_value_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
								$data['swap_value_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
								$data['swap'] = $quelleComparingSymptomRow['swap'];
								$data['swap_value_ce_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
								$data['swap_value_ce_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);

								
								//#9 Updating the quelle id, code, year, titel of the initial symptom
								if($quelleComparingSymptomRow['symptom_id']==$initial_symptom_id && $quelleComparingSymptomRow['initial_symptom_id']==$initial_symptom_id)
								{
									$updateInitialArr['SeiteOriginalVon'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalVon']);
									$updateInitialArr['SeiteOriginalBis'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalBis']);
									$updateInitialArr['Beschreibung_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_de']);
									$updateInitialArr['Beschreibung_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_en']);
									$updateInitialArr['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_de']);
									$updateInitialArr['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_en']);
									$updateInitialArr['BeschreibungFull_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
									$updateInitialArr['BeschreibungFull_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
									$updateInitialArr['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_de']);
									$updateInitialArr['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_en']);
									$updateInitialArr['searchable_text_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
									$updateInitialArr['searchable_text_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);

									//Updating swapped initial
									$updateSymptom4 = "UPDATE $comparison_table_name SET quelle_code = '".$quelleComparingSymptomRow['quelle_code']."',quelle_titel= '".$quelleComparingSymptomRow['quelle_titel']."',quelle_jahr='".$quelleComparingSymptomRow['quelle_jahr']."', quelle_id='".$quelleComparingSymptomRow['quelle_id']."',original_quelle_id='".$quelleComparingSymptomRow['original_quelle_id']."', Beschreibung_de = NULLIF('".$updateInitialArr['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateInitialArr['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateInitialArr['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateInitialArr['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateInitialArr['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateInitialArr['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateInitialArr['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateInitialArr['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateInitialArr['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateInitialArr['searchable_text_en']."', ''), quelle_type_id = NULLIF('".$quelleComparingSymptomRow['quelle_type_id']."', ''), quelle_band = NULLIF('".$quelleComparingSymptomRow['quelle_band']."', ''), quelle_auflage = NULLIF('".$quelleComparingSymptomRow['quelle_auflage']."', ''), quelle_autor_or_herausgeber = NULLIF('".$quelleComparingSymptomRow['quelle_autor_or_herausgeber']."', ''), arznei_id = NULLIF('".$quelleComparingSymptomRow['arznei_id']."', ''), Symptomnummer = NULLIF('".$quelleComparingSymptomRow['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$updateInitialArr['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$updateInitialArr['SeiteOriginalBis']."', ''), bracketedString_de = NULLIF('".$quelleComparingSymptomRow['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$quelleComparingSymptomRow['bracketedString_en']."', ''), timeString_de = NULLIF('".$quelleComparingSymptomRow['timeString_de']."', ''), timeString_en = NULLIF('".$quelleComparingSymptomRow['timeString_en']."', ''), initial_source_original_language = NULLIF('".$quelleComparingSymptomRow['initial_source_original_language']."', ''), comparing_source_original_language = NULLIF('".$quelleComparingSymptomRow['comparing_source_original_language']."', ''), ip_address = NULLIF('".$quelleComparingSymptomRow['ip_address']."', ''), stand = NULLIF('".$quelleComparingSymptomRow['stand']."', ''), bearbeiter_id = NULLIF('".$quelleComparingSymptomRow['bearbeiter_id']."', ''), ersteller_datum = NULLIF('".$quelleComparingSymptomRow['ersteller_datum']."', ''), ersteller_id = NULLIF('".$quelleComparingSymptomRow['ersteller_id']."', '') WHERE symptom_id = '".$initial_symptom_id."'";
									$updateRes4 = $db->query($updateSymptom4);

									// Finding match synonyms of swapped comparing START
									$arrangedSynonymDataComparing = array();
									$matchedSynonymsComparing = findMatchedSynonyms($searchableTextForComparing, $globalStopWords, $availableSynonyms);
									if((isset($matchedSynonymsComparing['status']) AND $matchedSynonymsComparing['status'] == true) AND (isset($matchedSynonymsComparing['return_data']) AND !empty($matchedSynonymsComparing['return_data']))){
										$arrangedSynonymDataComparing = arrangeSynonymDataToStore($matchedSynonymsComparing['return_data']);
									}

									$dataSynonymComparing['synonym_word'] = (isset($arrangedSynonymDataComparing['synonym_word']) AND !empty($arrangedSynonymDataComparing['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_word'])) : "";
									$dataSynonymComparing['strict_synonym'] = (isset($arrangedSynonymDataComparing['strict_synonym']) AND !empty($arrangedSynonymDataComparing['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['strict_synonym'])) : "";
									$dataSynonymComparing['synonym_partial_1'] = (isset($arrangedSynonymDataComparing['synonym_partial_1']) AND !empty($arrangedSynonymDataComparing['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_1'])) : "";
									$dataSynonymComparing['synonym_partial_2'] = (isset($arrangedSynonymDataComparing['synonym_partial_2']) AND !empty($arrangedSynonymDataComparing['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_2'])) : "";
									$dataSynonymComparing['synonym_general'] = (isset($arrangedSynonymDataComparing['synonym_general']) AND !empty($arrangedSynonymDataComparing['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_general'])) : "";
									$dataSynonymComparing['synonym_minor'] = (isset($arrangedSynonymDataComparing['synonym_minor']) AND !empty($arrangedSynonymDataComparing['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_minor'])) : "";
									$dataSynonymComparing['synonym_nn'] = (isset($arrangedSynonymDataComparing['synonym_nn']) AND !empty($arrangedSynonymDataComparing['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_nn'])) : "";
									// Finding match synonyms END

									//Updating swapped comparative
									$data['quelle_code'] = $iniSymRow['quelle_code'];
									$data['quelle_titel'] = $iniSymRow['quelle_titel'];
									$data['quelle_type_id'] = $iniSymRow['quelle_type_id'];
									$data['quelle_jahr'] = $iniSymRow['quelle_jahr'];
									$data['quelle_band'] = $iniSymRow['quelle_band'];
									$data['quelle_auflage'] = $iniSymRow['quelle_auflage'];
									$data['quelle_autor_or_herausgeber'] = $iniSymRow['quelle_autor_or_herausgeber'];
									$data['arznei_id'] = $iniSymRow['arznei_id'];
									$data['quelle_id'] = $iniSymRow['quelle_id'];
									$data['original_quelle_id'] = $iniSymRow['original_quelle_id'];
									$data['Symptomnummer'] = $iniSymRow['Symptomnummer'];
									$data['SeiteOriginalVon'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalVon']);
									$data['SeiteOriginalBis'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalBis']);
									$data['Beschreibung_de'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_de']);
									$data['Beschreibung_en'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_en']);
									$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_de']);
									$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_en']);
									$data['BeschreibungFull_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
									$data['BeschreibungFull_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
									$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_de']);
									$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_en']);
									$data['searchable_text_de'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
									$data['searchable_text_en'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
									$data['bracketedString_de'] = $iniSymRow['bracketedString_de'];
									$data['bracketedString_en'] = $iniSymRow['bracketedString_en'];
									$data['timeString_de'] = $iniSymRow['timeString_de'];
									$data['timeString_en'] = $iniSymRow['timeString_en'];
									$data['initial_source_original_language'] = $iniSymRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $iniSymRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db, $iniSymRow['Fussnote']);
									$data['EntnommenAus'] = $iniSymRow['EntnommenAus'];
									$data['Verweiss'] = $iniSymRow['Verweiss'];
									$data['BereichID'] = $iniSymRow['BereichID'];
									$data['Kommentar'] = mysqli_real_escape_string($db, $iniSymRow['Kommentar']);
									$data['Unklarheiten'] = $iniSymRow['Unklarheiten'];
									$data['Remedy'] = $iniSymRow['Remedy'];
									$data['symptom_of_different_remedy'] = $iniSymRow['symptom_of_different_remedy'];
									$data['subChapter'] = $iniSymRow['subChapter'];
									$data['subSubChapter'] = $iniSymRow['subSubChapter'];
									$data['synonym_word'] = $dataSynonymComparing['synonym_word'];
									$data['strict_synonym'] = $dataSynonymComparing['strict_synonym'];
									$data['synonym_partial_1'] = $dataSynonymComparing['synonym_partial_1'];
									$data['synonym_partial_2'] = $dataSynonymComparing['synonym_partial_2'];
									$data['synonym_general'] = $dataSynonymComparing['synonym_general'];
									$data['synonym_minor'] = $dataSynonymComparing['synonym_minor'];
									$data['synonym_nn'] = $dataSynonymComparing['synonym_nn'];
									$data['symptom_edit_comment'] = $iniSymRow['symptom_edit_comment'];
									$data['is_final_version_available'] = 0;
									$data['ersteller_datum'] = $iniSymRow['ersteller_datum'];
									$data['swap_ce'] = "1";
									$data['swap_value_de'] = "";
									$data['swap_value_en'] = "";
									$data['swap'] = "";
									$data['swap_value_ce_de'] = $fv_symptom_initial_de_insert;
									$data['swap_value_ce_en'] = $fv_symptom_initial_en_insert;

									$connectionDataArray['highlighted_comparing_symptom_de'] = $fv_symptom_de_insert;
									$connectionDataArray['highlighted_comparing_symptom_en'] = $fv_symptom_en_insert;

								}
								

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
						}
					}


					if($operationFlag != 0){
						foreach ($arrayForEarlierConnection as $key) {
							$earlier_comparative_symptom = $key['comparativeIdToSend'];

							// if($key['operationFlag']==3 || $key['operationFlag']==4){
							// 	$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
							// }else if($key['operationFlag']==2){
							// 	$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
							// }else{
							// 	$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
							// }

							//new edits
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
							//at present restricted, some problem with "$progress > 0 part"
							if($proceed > 0){
								$compSymptomString_de_ealier = "";
								$compSymptomString_en_ealier = "";
								while($mysqliOperationRow = mysqli_fetch_array($mysqliOperation)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($mysqliOperationRow['swap'] != 0){
										if($comparison_language == "de")
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['swap_value_de']);
										else
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
									}else if($mysqliOperationRow['swap_ce'] != 0){
										if($comparison_language == "de")
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['swap_value_ce_de']);
										else
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $mysqliOperationRow['swap_value_ce_en']);
									}else if($mysqliOperationRow['is_final_version_available'] != 0){
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

									
									// // Apply dynamic conversion
									if($mysqliOperationRow['swap'] != 0){
										if($compSymptomString_de_ealier != ""){
											$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['swap'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en_ealier != ""){
											$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['swap'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_en = base64_encode($compSymptomString_en);
										}
									}else if($mysqliOperationRow['swap_ce'] != 0){
										if($compSymptomString_de_ealier != ""){
											$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['swap_ce'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en_ealier != ""){
											$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['swap_ce'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_en = base64_encode($compSymptomString_en);
										}
									}else {
										if($compSymptomString_de_ealier != ""){
											$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['is_final_version_available'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en_ealier != ""){
											$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $mysqliOperationRow['quelle_id'], $mysqliOperationRow['arznei_id'], $mysqliOperationRow['is_final_version_available'], 0, $mysqliOperationRow['symptom_id']);
											// $compSymptomString_en = base64_encode($compSymptomString_en);
										}
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
						}
					}
					// echo json_encode( array( 'status' => 'ok','result_data'=>$comparingSymptomsArray) ); 
					// exit;

					if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

						//#10 Deleting all the comparing symptoms under that initial
						$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$deleteRes = $db->query($deleteExistingComparingSymptoms);
						if($deleteRes == true){

							//#11 Updating the symptom id of the initial symptom with the comparative and also resetting it to earlier state
							$count = $rowIdToInsertFrom;
							$idModify = $count -1;
							$updateSymptom5 = "UPDATE $comparison_table_name SET symptom_id = $comparative_symptom_id WHERE id = $idModify";
							$updateRes5 = $db->query($updateSymptom5);

							//#12 Inserting comparatives below the initial from the data array 
							foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
								$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id,original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_final_version_available,matched_percentage, ersteller_datum, swap,swap_value_en,swap_value_de, swap_ce, swap_value_ce_en, swap_value_ce_de, connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$comparative_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''), NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['swap']."', ''), NULLIF('".$comparingRow['swap_value_en']."', ''),NULLIF('".$comparingRow['swap_value_de']."', ''), NULLIF('".$comparingRow['swap_ce']."', ''),NULLIF('".$comparingRow['swap_value_ce_en']."', ''),NULLIF('".$comparingRow['swap_value_ce_de']."', ''), NULLIF('".$comparingRow['connection']."', ''))";
								$db->query($insertComparative);

								$count++;
							}
						}
					}
					
					// Inserting in connection in connections table
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$connectionDataArray['initial_symptom_id']."' AND comparing_symptom_id = '".$connectionDataArray['comparing_symptom_id']."'";
	            	$db->query($deleteExistingQuery);

	            	if($operation_type == "connectCESwap"){
	            		$insertConnection = "INSERT INTO ".$comparison_table_name."_connections (initial_symptom_id, comparing_symptom_id, connection_type, matched_percentage, ns_connect, ns_paste, ns_connect_comment, ns_paste_comment, initial_quelle_id, comparing_quelle_id, initial_quelle_code, comparing_quelle_code, initial_quelle_original_language, comparing_quelle_original_language, highlighted_initial_symptom_de, highlighted_initial_symptom_en, highlighted_comparing_symptom_de, highlighted_comparing_symptom_en, initial_symptom_de,  initial_symptom_en, comparing_symptom_de, comparing_symptom_en, comparison_language, initial_year, comparing_year, is_earlier_connection) VALUES (NULLIF('".$connectionDataArray['comparing_symptom_id']."', ''), NULLIF('".$connectionDataArray['initial_symptom_id']."', ''), NULLIF('".$connectionDataArray['connection_type']."', ''), '".$connectionDataArray['matched_percentage']."', '".$connectionDataArray['ns_connect']."', '".$connectionDataArray['ns_paste']."', NULLIF('".$connectionDataArray['ns_connect_comment']."', ''), NULLIF('".$connectionDataArray['ns_paste_comment']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['comparison_language']."', ''), NULLIF('".$connectionDataArray['comparing_year']."', ''), NULLIF('".$connectionDataArray['initial_year']."', ''), NULLIF('".$connectionDataArray['is_earlier_connection']."', ''))";
						$db->query($insertConnection);
						//updating marking in the comparison table
						markingUpdation($db,$comparison_table_name,"1",$comparative_symptom_id);
	            	}

	            	if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							if($earlierConnectionFinalRow['operationFlag'] == 3 || $earlierConnectionFinalRow['operationFlag'] == 1){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
								
							}else if($earlierConnectionFinalRow['operationFlag'] == 2){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0'  WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

							}else if($earlierConnectionFinalRow['operationFlag'] == 4){

								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''),comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='0',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
							}else{
								//for paste and paste edit previous connection
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='1',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

							}
							$allQueryArray = array();

							$allQueryArray['query'] = $updateSymptomEarlier;
							$allQueryArray['comparing_symptom_id'] = $earlierConnectionFinalRow['comparativeIdToSend'];
							$allQueryArray['initial_symptom_id'] = $earlierConnectionFinalRow['initialId'];
							$allQueryArrayFinal[] = $allQueryArray;

							$db->query($updateSymptomEarlier);

						}
					}

					

					// Updation in highest match table
					if(!empty($updateHighestMatchSymptomIdArray)){
						foreach ($updateHighestMatchSymptomIdArray as $symId) {
							// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
							$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
							if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

								$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
								$updateRes = $db->query($updateHighestMatchDetails);
							}
						}
					} 
				}
				elseif ($updateResult == 4){
					//Disconnect swap CE
					//Disconnect swap operation

					$runningInitialSymptomId = "";

					//#2 Fetching the ids below that initial symptom
                    $testQuery = "SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND initial_symptom_id IS NULL";
                    // echo $testQuery ;   
                    // exit();
					$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND initial_symptom_id IS NULL");
					if(mysqli_num_rows($fetchIdResult) > 0){
						$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
					}
					$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";
					//#3 Initial symptom information
					$initialQuery = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
					$InitialQuelleResult = mysqli_query($db,$initialQuery);
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
							

							$runningInitialSymptomId = $iniSymRow['symptom_id'];
							// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)

							//Informations are taken for comparison with comparative symptom
							if($iniSymRow['swap_ce'] != 0){
								if($comparison_language == "de"){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
									$iniSymptomString_en =  "";
								}
								else{
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
									$iniSymptomString_de =  "";
								}
							}else if($iniSymRow['swap'] != 0){
								if($comparison_language == "de"){
									$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
									$iniSymptomString_en =  "";
								}
								else{
									$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
									$iniSymptomString_de =  "";
								}
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
					
							// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
							$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
							$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
							
							// Apply dynamic conversion (this string is used in displying the symptom)
							if($iniSymRow['swap_ce'] != 0){
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
								}
							}else if($iniSymRow['swap'] != 0){
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
								}
							}else{
								if($iniSymptomString_de != ""){
									$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
									// $iniSymptomString_de = base64_encode($iniSymptomString_de);
								}
								if($iniSymptomString_en != ""){
									$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
								}
							}
								
							
							if($comparison_language == "en")
								$ini = $iniSymptomString_en;
							else
								$ini = $iniSymptomString_de;
							$ini_earlier = $ini;

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

							//#4 Updating symptom text of the comparative symptom
							if($fv_comparison_option == 1){
								$sendStringDe['searchable_text_de'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
								$sendStringEn['searchable_text_en'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
								$updateSymptom2 = "UPDATE $comparison_table_name SET swap_value_ce_de = NULLIF('".$sendStringDe['searchable_text_de']."', ''), swap_value_ce_en = NULLIF('".$sendStringEn['searchable_text_en']."', ''), swap_ce = 1 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							} else {
								$sendStringDe['BeschreibungFull_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
								$sendStringEn['BeschreibungFull_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
								$updateSymptom2 = "UPDATE $comparison_table_name SET swap_value_ce_de = NULLIF('".$sendStringDe['BeschreibungFull_de']."', ''), swap_value_ce_en = NULLIF('".$sendStringEn['BeschreibungFull_en']."', ''), swap_ce = 1 WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							}
							
							$updateRes2 = $db->query($updateSymptom2);

							//#5 Updating the symptom id of the conparative symptom with the initial symptom
							$updateSymptom3 = "UPDATE $comparison_table_name SET symptom_id = $initial_symptom_id WHERE symptom_id = '".$comparative_symptom_id."' AND initial_symptom_id = '".$initial_symptom_id."'";
							$updateRes3 = $db->query($updateSymptom3);
					
							// Comparing symptoms
							//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
							$quelleQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
							$quelleComparingSymptomResult = mysqli_query($db,$quelleQuery);
							while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								$compSymptomString_de="";
								$compSymptomString_en="";
								if($quelleComparingSymptomRow['swap_ce'] != 0){
									if($comparison_language == "de")
										$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
									else
										$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
								}else if($quelleComparingSymptomRow['swap'] != 0){
									if($comparison_language == "de")
										$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
									else
										$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
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
								if($quelleComparingSymptomRow['swap_ce'] != 0){
									if($compSymptomString_de != ""){
										$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en != ""){
										$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_en = base64_encode($compSymptomString_en);
									}
								}else if($quelleComparingSymptomRow['swap'] != 0){
									if($compSymptomString_de != ""){
										$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en != ""){
										$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
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
                                
                               $searchableTextForComparing = $com; 
								
								//$resultArray = newComareSymptom($ini, $com);
								$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
								$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
								$testArray[] = $resultArray;

								//#7 comparing the symptom texts with the initials for percentage
								$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
								$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
								// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
								$highlightedComparativeSymptomString_en = $compSymptomString_en;
								$highlightedComparativeSymptomString_de = $compSymptomString_de;
								if($comparison_language == "en")
									$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
								else
									$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;

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

								//#8 Storing all the comparing info in the data array
								$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
								$data['initial_symptom_id'] = $comparative_symptom_id;
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
								$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
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
								$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
								$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
								$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
								$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
								$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
								$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
								$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
								$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
								$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
								$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
								$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
								$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
								$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
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
								$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
								$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
								$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
								$data['matched_percentage'] = $percentage;
								$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
								$data['connection'] = $quelleComparingSymptomRow['connection'];
								
								
								//#9 Updating the quelle id, code, year, titel of the initial symptom
								if($quelleComparingSymptomRow['symptom_id']==$initial_symptom_id && $quelleComparingSymptomRow['initial_symptom_id']==$initial_symptom_id)
								{
									$updateInitialArr['SeiteOriginalVon'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalVon']);
									$updateInitialArr['SeiteOriginalBis'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['SeiteOriginalBis']);
									$updateInitialArr['Beschreibung_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_de']);
									$updateInitialArr['Beschreibung_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Beschreibung_en']);
									$updateInitialArr['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_de']);
									$updateInitialArr['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungOriginal_en']);
									$updateInitialArr['BeschreibungFull_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_de']);
									$updateInitialArr['BeschreibungFull_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungFull_en']);
									$updateInitialArr['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_de']);
									$updateInitialArr['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['BeschreibungPlain_en']);
									$updateInitialArr['searchable_text_de'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_de']);
									$updateInitialArr['searchable_text_en'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['searchable_text_en']);

									//Updating swapped initial
									$updateSymptom4 = "UPDATE $comparison_table_name SET quelle_code = '".$quelleComparingSymptomRow['quelle_code']."',quelle_titel= '".$quelleComparingSymptomRow['quelle_titel']."',quelle_jahr='".$quelleComparingSymptomRow['quelle_jahr']."', quelle_id='".$quelleComparingSymptomRow['quelle_id']."', original_quelle_id='".$quelleComparingSymptomRow['original_quelle_id']."', Beschreibung_de = NULLIF('".$updateInitialArr['Beschreibung_de']."', ''), Beschreibung_en = NULLIF('".$updateInitialArr['Beschreibung_en']."', ''), BeschreibungOriginal_de = NULLIF('".$updateInitialArr['BeschreibungOriginal_de']."', ''), BeschreibungOriginal_en = NULLIF('".$updateInitialArr['BeschreibungOriginal_en']."', ''), BeschreibungFull_de = NULLIF('".$updateInitialArr['BeschreibungFull_de']."', ''), BeschreibungFull_en = NULLIF('".$updateInitialArr['BeschreibungFull_en']."', ''), BeschreibungPlain_de = NULLIF('".$updateInitialArr['BeschreibungPlain_de']."', ''), BeschreibungPlain_en = NULLIF('".$updateInitialArr['BeschreibungPlain_en']."', ''), searchable_text_de = NULLIF('".$updateInitialArr['searchable_text_de']."', ''), searchable_text_en = NULLIF('".$updateInitialArr['searchable_text_en']."', ''), quelle_type_id = NULLIF('".$quelleComparingSymptomRow['quelle_type_id']."', ''), quelle_band = NULLIF('".$quelleComparingSymptomRow['quelle_band']."', ''), quelle_auflage = NULLIF('".$quelleComparingSymptomRow['quelle_auflage']."', ''), quelle_autor_or_herausgeber = NULLIF('".$quelleComparingSymptomRow['quelle_autor_or_herausgeber']."', ''), arznei_id = NULLIF('".$quelleComparingSymptomRow['arznei_id']."', ''), Symptomnummer = NULLIF('".$quelleComparingSymptomRow['Symptomnummer']."', ''), SeiteOriginalVon = NULLIF('".$quelleComparingSymptomRow['SeiteOriginalVon']."', ''), SeiteOriginalBis = NULLIF('".$quelleComparingSymptomRow['SeiteOriginalBis']."', ''), bracketedString_de = NULLIF('".$quelleComparingSymptomRow['bracketedString_de']."', ''), bracketedString_en = NULLIF('".$quelleComparingSymptomRow['bracketedString_en']."', ''), timeString_de = NULLIF('".$quelleComparingSymptomRow['timeString_de']."', ''), timeString_en = NULLIF('".$quelleComparingSymptomRow['timeString_en']."', ''), initial_source_original_language = NULLIF('".$quelleComparingSymptomRow['initial_source_original_language']."', ''), comparing_source_original_language = NULLIF('".$quelleComparingSymptomRow['comparing_source_original_language']."', ''), ip_address = NULLIF('".$quelleComparingSymptomRow['ip_address']."', ''), stand = NULLIF('".$quelleComparingSymptomRow['stand']."', ''), bearbeiter_id = NULLIF('".$quelleComparingSymptomRow['bearbeiter_id']."', ''), ersteller_datum = NULLIF('".$quelleComparingSymptomRow['ersteller_datum']."', ''), ersteller_id = NULLIF('".$quelleComparingSymptomRow['ersteller_id']."', '') WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
									$updateRes4 = $db->query($updateSymptom4);

									// Finding match synonyms of swapped comparing START
									$arrangedSynonymDataComparing = array();
									$matchedSynonymsComparing = findMatchedSynonyms($searchableTextForComparing, $globalStopWords, $availableSynonyms);
									if((isset($matchedSynonymsComparing['status']) AND $matchedSynonymsComparing['status'] == true) AND (isset($matchedSynonymsComparing['return_data']) AND !empty($matchedSynonymsComparing['return_data']))){
										$arrangedSynonymDataComparing = arrangeSynonymDataToStore($matchedSynonymsComparing['return_data']);
									}

									$dataSynonymComparing['synonym_word'] = (isset($arrangedSynonymDataComparing['synonym_word']) AND !empty($arrangedSynonymDataComparing['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_word'])) : "";
									$dataSynonymComparing['strict_synonym'] = (isset($arrangedSynonymDataComparing['strict_synonym']) AND !empty($arrangedSynonymDataComparing['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['strict_synonym'])) : "";
									$dataSynonymComparing['synonym_partial_1'] = (isset($arrangedSynonymDataComparing['synonym_partial_1']) AND !empty($arrangedSynonymDataComparing['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_1'])) : "";
									$dataSynonymComparing['synonym_partial_2'] = (isset($arrangedSynonymDataComparing['synonym_partial_2']) AND !empty($arrangedSynonymDataComparing['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_partial_2'])) : "";
									$dataSynonymComparing['synonym_general'] = (isset($arrangedSynonymDataComparing['synonym_general']) AND !empty($arrangedSynonymDataComparing['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_general'])) : "";
									$dataSynonymComparing['synonym_minor'] = (isset($arrangedSynonymDataComparing['synonym_minor']) AND !empty($arrangedSynonymDataComparing['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_minor'])) : "";
									$dataSynonymComparing['synonym_nn'] = (isset($arrangedSynonymDataComparing['synonym_nn']) AND !empty($arrangedSynonymDataComparing['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymDataComparing['synonym_nn'])) : "";
									// Finding match synonyms END


									//Updating swapped comparative
									$data['quelle_code'] = $iniSymRow['quelle_code'];
									$data['quelle_titel'] = $iniSymRow['quelle_titel'];
									$data['quelle_type_id'] = $iniSymRow['quelle_type_id'];
									$data['quelle_jahr'] = $iniSymRow['quelle_jahr'];
									$data['quelle_band'] = $iniSymRow['quelle_band'];
									$data['quelle_auflage'] = $iniSymRow['quelle_auflage'];
									$data['quelle_autor_or_herausgeber'] = $iniSymRow['quelle_autor_or_herausgeber'];
									$data['arznei_id'] = $iniSymRow['arznei_id'];
									$data['quelle_id'] = $iniSymRow['quelle_id'];
									$data['original_quelle_id'] = $iniSymRow['original_quelle_id'];
									$data['Symptomnummer'] = $iniSymRow['Symptomnummer'];
									$data['SeiteOriginalVon'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalVon']);
									$data['SeiteOriginalBis'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalBis']);
									$data['swap_value_ce_de'] = "";
									$data['swap_value_ce_en'] = "";
									$data['swap_ce'] = "";
									$data['Beschreibung_de'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_de']);
									$data['Beschreibung_en'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_en']);
									$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_de']);
									$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_en']);
									$data['BeschreibungFull_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
									$data['BeschreibungFull_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
									$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_de']);
									$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_en']);
									$data['searchable_text_de'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
									$data['searchable_text_en'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
									$data['bracketedString_de'] = $iniSymRow['bracketedString_de'];
									$data['bracketedString_en'] = $iniSymRow['bracketedString_en'];
									$data['timeString_de'] = $iniSymRow['timeString_de'];
									$data['timeString_en'] = $iniSymRow['timeString_en'];
									$data['initial_source_original_language'] = $iniSymRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $iniSymRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db, $iniSymRow['Fussnote']);
									$data['EntnommenAus'] = $iniSymRow['EntnommenAus'];
									$data['Verweiss'] = $iniSymRow['Verweiss'];
									$data['BereichID'] = $iniSymRow['BereichID'];
									$data['Kommentar'] = mysqli_real_escape_string($db, $iniSymRow['Kommentar']);
									$data['Unklarheiten'] = $iniSymRow['Unklarheiten'];
									$data['Remedy'] = $iniSymRow['Remedy'];
									$data['symptom_of_different_remedy'] = $iniSymRow['symptom_of_different_remedy'];
									$data['subChapter'] = $iniSymRow['subChapter'];
									$data['subSubChapter'] = $iniSymRow['subSubChapter'];
									$data['synonym_word'] = $dataSynonymComparing['synonym_word'];
									$data['strict_synonym'] = $dataSynonymComparing['strict_synonym'];
									$data['synonym_partial_1'] = $dataSynonymComparing['synonym_partial_1'];
									$data['synonym_partial_2'] = $dataSynonymComparing['synonym_partial_2'];
									$data['synonym_general'] = $dataSynonymComparing['synonym_general'];
									$data['synonym_minor'] = $dataSynonymComparing['synonym_minor'];
									$data['synonym_nn'] = $dataSynonymComparing['synonym_nn'];
									$data['symptom_edit_comment'] = $iniSymRow['symptom_edit_comment'];
									$data['ersteller_datum'] = $iniSymRow['ersteller_datum'];
								}

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

						}
					}
					if($operationFlag != 0){
						$queryComEarlierConnection = "SELECT comparing_symptom_id,connection_type FROM $comparison_table_name".'_connections'." WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$quelleComparingSymptomResultEarlierConnection = mysqli_query($db,$queryComEarlierConnection);
						if(mysqli_num_rows($quelleComparingSymptomResultEarlierConnection) > 1){
							$arrayForEarlierConnectionData = array();
							while($quelleComparingSymptomRowEarlierConnection = mysqli_fetch_array($quelleComparingSymptomResultEarlierConnection)){
								$arrayForEarlierConnectionData['comparing_symptom_id'] = $quelleComparingSymptomRowEarlierConnection['comparing_symptom_id'];
								$arrayForEarlierConnectionData['connection_type'] = $quelleComparingSymptomRowEarlierConnection['connection_type'];
								
								$earlierConnectionSaveArrayFinalConnection[] = $arrayForEarlierConnectionData;
							}
						}

						foreach ($earlierConnectionSaveArrayFinalConnection as $key) {
							$earlier_comparative_symptom = $key['comparing_symptom_id'];
							
							$earlierCheckInitial = "SELECT is_initial_symptom FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' LIMIT 1";
							$resultEarlierCheckInitial = mysqli_query($db,$earlierCheckInitial);
							if(mysqli_num_rows($resultEarlierCheckInitial) > 0){
								while($rowEarlierCheckInitial = mysqli_fetch_array($resultEarlierCheckInitial)){
									if($rowEarlierCheckInitial['is_initial_symptom']==1){
										$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
									}
									else{
										$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
									}

								}
							}

							$allQueryArray = array();

							$allQueryArray['query'] = $queryComEarlier;
							$allQueryArray['queryUpper'] = $earlierCheckInitial;
							$allQueryArrayFinal[] = $allQueryArray;
							
							$quelleComparingSymptomResultEarlier = mysqli_query($db,$queryComEarlier);
							if(mysqli_num_rows($quelleComparingSymptomResultEarlier) > 0){
								while($quelleComparingSymptomRowEarlier = mysqli_fetch_array($quelleComparingSymptomResultEarlier)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									if($quelleComparingSymptomRowEarlier['is_final_version_available'] != 0){
										$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_de']);
										$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_en']);
									}else{
										if($fv_comparison_option == 1){
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_en']);
										}else{
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_en']);
										}
									}
									
									// Apply dynamic conversion
									if($compSymptomString_de_ealier != ""){
										$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
										// $compSymptomString_de = base64_encode($compSymptomString_de);
									}
									if($compSymptomString_en_ealier != ""){
										$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['is_final_version_available'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
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
									$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRowEarlier['symptom_id'];
									$earlierConnectionSaveArray['comparativeIdToSend'] = $key['comparing_symptom_id'];
									// $earlierConnectionSaveArray['earlierConnectedId'] = $key['earlierConnectedId'];
									// $earlierConnectionSaveArray['initialId'] = $key['initialId'];
									$earlierConnectionSaveArray['connection_type'] = $key['connection_type'];
									$earlierConnectionSaveArray['matched_percentage'] = $percentageEarlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_de'] = $highlightedComparativeSymptomString_de_earlier;
									$earlierConnectionSaveArray['highlighted_comparing_symptom_en'] = $highlightedComparativeSymptomString_en_earlier;

									//inserting into array 
									$earlierConnectionSaveArrayFinal[] = $earlierConnectionSaveArray;
								}
							}
						}
					}
					
					if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

						//#10 Deleting all the comparing symptoms under that initial
						$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
						$deleteRes = $db->query($deleteExistingComparingSymptoms);
						if($deleteRes == true){

							//#11 Updating the symptom id of the initial symptom with the comparative
							$count = $rowIdToInsertFrom;
							$idModify = $count -1;
							$updateSymptom5 = "UPDATE $comparison_table_name SET symptom_id = $comparative_symptom_id,swap_value_ce_de = NULL,swap_value_ce_en = NULL, swap_ce = NULL WHERE id = $idModify";
							$updateRes5 = $db->query($updateSymptom5);

							//#12 Inserting comparatives below the initial from the data array 
							foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
								$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id,  original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms,symptom_edit_comment, is_final_version_available, matched_percentage, ersteller_datum,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$comparative_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''),NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''), NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''),NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['connection']."', ''))";
								$db->query($insertComparative);

								$count++;
							}
						}
					}
					
					// Update in connection in connections table
					$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
	            	$db->query($deleteExistingQuery);
	            	//updating marking in the comparison table
					markingUpdation($db,$comparison_table_name,"0",$comparative_symptom_id);
	            	if(!empty($earlierConnectionSaveArrayFinal)){
						foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
							// if($earlierConnectionFinalRow['connection_type']!= 'swapCE'){
								$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['initial_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['initial_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['initial_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['initial_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$initial_symptom_id."'";
								$db->query($updateSymptomEarlier);
							// }
						}
								
					}
	            	
	   
					// Updation in highest match table
					if(!empty($updateHighestMatchSymptomIdArray)){
						foreach ($updateHighestMatchSymptomIdArray as $symId) {
							// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
							$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
							if(mysqli_num_rows($fetchHighestMatchResult) > 0){
								$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

								$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
								$updateRes = $db->query($updateHighestMatchDetails);
							}
						}
					}
				}
				else{
					if($operationFlag == 10){
						//connection field made 0 
						$updateSymptom = "UPDATE $comparison_table_name SET connection = '0',swap_ce=NULL,swap_value_ce_en=NULL,swap_value_ce_de = NULL WHERE symptom_id = '".$comparative_symptom_id."'";
						$updateRes = $db->query($updateSymptom);

						$updateSymptom2 = "UPDATE $comparison_table_name SET connection = '0',swap_ce=NULL,swap_value_ce_en=NULL,swap_value_ce_de = NULL WHERE symptom_id = '".$initial_symptom_id."'";
						$updateRes2 = $db->query($updateSymptom2);

						//Deleting from connections table
						// Update in connection in connections table
						$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
		            	$db->query($deleteExistingQuery);
					}else{
						//Disconnected swap for connected symptoms
						//#1 Updating the symptom text of the initial symptom
						if($comparison_language == "de")
						{
							$fv_symptom_initial_de_insert = $comparative_symptom_text_insert;
							$fv_symptom_de_insert = $initial_symptom_text_insert;
						}
						else
						{
							$fv_symptom_initial_en_insert = $comparative_symptom_text_insert;
							$fv_symptom_en_insert = $initial_symptom_text_insert;

						}
						//#1 Updating the symptom text of the initial symptom
						// $condition = "SET swap_value_ce_de = NULLIF('".$fv_symptom_initial_de_insert."', ''), swap_value_ce_en = NULLIF('".$fv_symptom_initial_en_insert."', ''),swap_ce = 1 WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
						// $updateSymptom = "UPDATE $comparison_table_name $condition";
						// $updateRes = $db->query($updateSymptom);

						$updateSymptom = "UPDATE $comparison_table_name SET connection = '0',swap_ce=NULL,swap_value_ce_en=NULL,swap_value_ce_de = NULL WHERE symptom_id = '".$comparative_symptom_id."'";
						$updateRes = $db->query($updateSymptom);

						$updateSymptom2 = "UPDATE $comparison_table_name SET connection = '0',swap_ce=NULL,swap_value_ce_en=NULL,swap_value_ce_de = NULL WHERE symptom_id = '".$initial_symptom_id."'";
						$updateRes2 = $db->query($updateSymptom2);

		            	/////////////////////////////////////////////
		            	//Disconnect swap operation
					
						$runningInitialSymptomId = "";

						//#2 Fetching the ids below that initial symptom
						$fetchIdResult = mysqli_query($db,"SELECT id FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND initial_symptom_id IS NULL");
						if(mysqli_num_rows($fetchIdResult) > 0){
							$fetchedRow = mysqli_fetch_assoc($fetchIdResult);
						}
						$rowIdToInsertFrom = (isset($fetchedRow['id']) AND $fetchedRow['id'] != "") ? $fetchedRow['id']+1 : "";
						
						
						//#3 Initial symptom information
						$initialQuery = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
						$InitialQuelleResult = mysqli_query($db,$initialQuery);

						if(mysqli_num_rows($InitialQuelleResult) > 0){
							while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){	
								
								$runningInitialSymptomId = $iniSymRow['symptom_id'];
								// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								$iniSymptomString_de="";
								$iniSymptomString_en="";
								//Informations are taken for comparison with comparative symptom
								if($iniSymRow['swap_ce'] != 0){
									if($comparison_language == "de")
										$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
									else
										$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
								}else if($iniSymRow['swap'] != 0){
									if($comparison_language == "de")
										$iniSymptomString_de =  mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
									else
										$iniSymptomString_en =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
								}else if($iniSymRow['is_final_version_available'] != 0){
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

								
								// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
								$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
								$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";
								
								// Apply dynamic conversion (this string is used in displying the symptom)
								if($iniSymRow['swap_ce'] != 0){
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
										// $iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap_ce'], 0, $iniSymRow['symptom_id']);
									}
								}else if($iniSymRow['swap'] != 0){
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
										// $iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['swap'], 0, $iniSymRow['symptom_id']);
									}
								}else{
									if($iniSymptomString_de != ""){
										$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
										// $iniSymptomString_de = base64_encode($iniSymptomString_de);
									}
									if($iniSymptomString_en != ""){
										$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['symptom_id']);
									}
								}
									

								if($comparison_language == "en")
									$ini = $iniSymptomString_en;
								else
									$ini = $iniSymptomString_de;

								$ini_earlier = $ini;
								$searchableText = $ini;
								// Finding match synonyms START
								$arrangedSynonymInitialData = array();
								$matchedSynonymsInitial = findMatchedSynonyms($searchableText, $globalStopWords, $availableSynonyms);
								if((isset($matchedSynonymsInitial['status']) AND $matchedSynonymsInitial['status'] == true) AND (isset($matchedSynonymsInitial['return_data']) AND !empty($matchedSynonymsInitial['return_data']))){
									$arrangedSynonymInitialData = arrangeSynonymDataToStore($matchedSynonymsInitial['return_data']);
								}

								$dataSynonymInitial['synonym_word'] = (isset($arrangedSynonymInitialData['synonym_word']) AND !empty($arrangedSynonymInitialData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_word'])) : "";
								$dataSynonymInitial['strict_synonym'] = (isset($arrangedSynonymInitialData['strict_synonym']) AND !empty($arrangedSynonymInitialData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['strict_synonym'])) : "";
								$dataSynonymInitial['synonym_partial_1'] = (isset($arrangedSynonymInitialData['synonym_partial_1']) AND !empty($arrangedSynonymInitialData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_partial_1'])) : "";
								$dataSynonymInitial['synonym_partial_2'] = (isset($arrangedSynonymInitialData['synonym_partial_2']) AND !empty($arrangedSynonymInitialData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_partial_2'])) : "";
								$dataSynonymInitial['synonym_general'] = (isset($arrangedSynonymInitialData['synonym_general']) AND !empty($arrangedSynonymInitialData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_general'])) : "";
								$dataSynonymInitial['synonym_minor'] = (isset($arrangedSynonymInitialData['synonym_minor']) AND !empty($arrangedSynonymInitialData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_minor'])) : "";
								$dataSynonymInitial['synonym_nn'] = (isset($arrangedSynonymInitialData['synonym_nn']) AND !empty($arrangedSynonymInitialData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymInitialData['synonym_nn'])) : "";
								// Finding match synonyms END

								//Taking unescape datas
								$matchInitialArry['synonym_word'] = serialize($arrangedSynonymInitialData['synonym_word']); 
								$matchInitialArry['strict_synonym'] = serialize($arrangedSynonymInitialData['strict_synonym']); 
								$matchInitialArry['synonym_partial_1'] = serialize($arrangedSynonymInitialData['synonym_partial_1']); 
								$matchInitialArry['synonym_partial_2'] = serialize($arrangedSynonymInitialData['synonym_partial_2']); 
								$matchInitialArry['synonym_general'] = serialize($arrangedSynonymInitialData['synonym_general']); 
								$matchInitialArry['synonym_minor'] = serialize($arrangedSynonymInitialData['synonym_minor']); 
								$matchInitialArry['synonym_nn'] = serialize($arrangedSynonymInitialData['synonym_nn']); 

								// Collecting Synonyms of this Symptom START
								$initialSymptomsAllSynonyms = array();
								$wordSynonyms = array();
								$strictSynonyms = array();
								$partial1Synonyms = array();
								$partial2Synonyms = array();
								$generalSynonyms = array();
								$minorSynonyms = array();
								$nnSynonyms = array();
								if(!empty($matchInitialArry['synonym_word'])){
									$wordSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_word']);
									$wordSynonyms = (!empty($wordSynonyms)) ? $wordSynonyms : array(); 
								}
								if(!empty($matchInitialArry['strict_synonym'])){
									$strictSynonyms = getAllOrganizeSynonyms($matchInitialArry['strict_synonym']);
									$strictSynonyms = (!empty($strictSynonyms)) ? $strictSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_partial_1'])){
									$partial1Synonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_partial_1']);
									$partial1Synonyms = (!empty($partial1Synonyms)) ? $partial1Synonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_partial_2'])){
									$partial2Synonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_partial_2']);
									$partial2Synonyms = (!empty($partial2Synonyms)) ? $partial2Synonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_general'])){
									$generalSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_general']);
									$generalSynonyms = (!empty($generalSynonyms)) ? $generalSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_minor'])){
									$minorSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_minor']);
									$minorSynonyms = (!empty($minorSynonyms)) ? $minorSynonyms : array(); 
								}
								if(!empty($matchInitialArry['synonym_nn'])){
									$nnSynonyms = getAllOrganizeSynonyms($matchInitialArry['synonym_nn']);
									$nnSynonyms = (!empty($nnSynonyms)) ? $nnSynonyms : array(); 
								}
								$initialSymptomsAllSynonyms = array_merge($wordSynonyms, $strictSynonyms, $partial1Synonyms, $partial2Synonyms, $generalSynonyms, $minorSynonyms, $nnSynonyms);
								// Collecting Synonyms of this Symptom END

								// Comparing symptoms
								//#6 Selecting the comparatives under that initial for comparison with the new edited initial symptom text
								$quelleQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
								$quelleComparingSymptomResult = mysqli_query($db,$quelleQuery);
								while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
									// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
									$compSymptomString_de="";
									$compSymptomString_en="";
									if($quelleComparingSymptomRow['swap_ce'] != 0){
										if($comparison_language == "de")
											$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_de']);
										else
											$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_ce_en']);
									}else if($quelleComparingSymptomRow['swap'] != 0){
										if($comparison_language == "de")
											$compSymptomString_de =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_de']);
										else
											$compSymptomString_en =  mysqli_real_escape_string($db, $quelleComparingSymptomRow['swap_value_en']);
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
									if($quelleComparingSymptomRow['swap_ce'] != 0){
										if($compSymptomString_de != ""){
											$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en != ""){
											$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap_ce'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_en = base64_encode($compSymptomString_en);
										}
									}else if($quelleComparingSymptomRow['swap'] != 0){
										if($compSymptomString_de != ""){
											$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
											// $compSymptomString_de = base64_encode($compSymptomString_de);
										}
										if($compSymptomString_en != ""){
											$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['swap'], 0, $quelleComparingSymptomRow['symptom_id']);
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

									//$resultArray = newComareSymptom($ini, $com);
									$resultArray = compareSymptomWithSynonyms($ini, $com, $globalStopWords, $initialSymptomsAllSynonyms);
									$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
									$testArray[] = $resultArray;

									//#7 comparing the symptom texts with the initials for percentage
									$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
									$highlightedComparingSymptom = (isset($resultArray['comparing_source_symptom_highlighted']) AND $resultArray['comparing_source_symptom_highlighted'] != "") ? $resultArray['comparing_source_symptom_highlighted'] : "";
									// updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
									$highlightedComparativeSymptomString_en = $compSymptomString_en;
									$highlightedComparativeSymptomString_de = $compSymptomString_de;
									if($comparison_language == "en")
										$highlightedComparativeSymptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_en;
									else
										$highlightedComparativeSymptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $compSymptomString_de;

									// For before operation done percentage check
									if($quelleComparingSymptomRow['matched_percentage'] >= $cutoff_percentage){
										if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
											array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
									}
									// For after operation done percentage check
									if($percentage >= $cutoff_percentage){
										if(!in_array($quelleComparingSymptomRow['symptom_id'], $updateHighestMatchSymptomIdArray))
											array_push($updateHighestMatchSymptomIdArray, $quelleComparingSymptomRow['symptom_id']); 
									}
									// Id collect for highest match table update end
									
									//#8 Storing all the comparing info in the data array
									$data['symptom_id'] = $quelleComparingSymptomRow['symptom_id'];
									$data['initial_symptom_id'] = $initial_symptom_id;
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
									$data['original_quelle_id'] = $quelleComparingSymptomRow['original_quelle_id'];
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
									$data['bracketedString_de'] = $quelleComparingSymptomRow['bracketedString_de'];
									$data['bracketedString_en'] = $quelleComparingSymptomRow['bracketedString_en'];
									$data['timeString_de'] = $quelleComparingSymptomRow['timeString_de'];
									$data['timeString_en'] = $quelleComparingSymptomRow['timeString_en'];
									$data['initial_source_original_language'] = $quelleComparingSymptomRow['initial_source_original_language'];
									$data['comparing_source_original_language'] = $quelleComparingSymptomRow['comparing_source_original_language'];
									$data['Fussnote'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Fussnote']);
									$data['EntnommenAus'] = $quelleComparingSymptomRow['EntnommenAus'];
									$data['Verweiss'] = $quelleComparingSymptomRow['Verweiss'];
									$data['BereichID'] = $quelleComparingSymptomRow['BereichID'];
									$data['Kommentar'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['Kommentar']);
									$data['Unklarheiten'] = $quelleComparingSymptomRow['Unklarheiten'];
									$data['Remedy'] = $quelleComparingSymptomRow['Remedy'];
									$data['symptom_of_different_remedy'] = $quelleComparingSymptomRow['symptom_of_different_remedy'];
									$data['subChapter'] = $quelleComparingSymptomRow['subChapter'];
									$data['subSubChapter'] = $quelleComparingSymptomRow['subSubChapter'];
									$data['synonym_word'] = $quelleComparingSymptomRow['synonym_word'];
									$data['strict_synonym'] = $quelleComparingSymptomRow['strict_synonym'];
									$data['synonym_partial_1'] = $quelleComparingSymptomRow['synonym_partial_1'];
									$data['synonym_partial_2'] = $quelleComparingSymptomRow['synonym_partial_2'];
									$data['synonym_general'] = $quelleComparingSymptomRow['synonym_general'];
									$data['synonym_minor'] = $quelleComparingSymptomRow['synonym_minor'];
									$data['synonym_nn'] = $quelleComparingSymptomRow['synonym_nn'];
									$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
									$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $quelleComparingSymptomRow['symptom_edit_comment']);
									$data['is_final_version_available'] = $quelleComparingSymptomRow['is_final_version_available'];
									$data['matched_percentage'] = $percentage;
									$data['ersteller_datum'] = $quelleComparingSymptomRow['ersteller_datum'];
									$data['connection'] = $quelleComparingSymptomRow['connection'];

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
							}
						}


						if($operationFlag != 0 && !empty($arrayForEarlierConnection)){
							foreach ($arrayForEarlierConnection as $key) {
								$earlier_comparative_symptom = $key['comparativeIdToSend'];
								if($key['operationFlag']==3 || $key['operationFlag']==4){
									$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND is_initial_symptom = '1'";
								}else if($key['operationFlag']==2){
									$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
								}else{
									$queryComEarlier = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$earlier_comparative_symptom."' AND initial_symptom_id = '".$initial_symptom_id."'";
								}
								// echo json_encode( array( 'status' => $queryComEarlier) ); 
								// exit;
								$quelleComparingSymptomResultEarlier = mysqli_query($db,$queryComEarlier);
								
								$compSymptomString_de_ealier = "";
								$compSymptomString_en_ealier = "";
								if(mysqli_num_rows($quelleComparingSymptomResultEarlier) > 0){
									while($quelleComparingSymptomRowEarlier = mysqli_fetch_array($quelleComparingSymptomResultEarlier)){
										// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
										if($quelleComparingSymptomRowEarlier['swap_ce'] != 0){
											if($comparison_language == "de")
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_ce_de']);
											else
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_ce_en']);
										}else if($quelleComparingSymptomRowEarlier['swap'] != 0){
											if($comparison_language == "de")
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['swap_value_de']);
											else
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
										}else if($quelleComparingSymptomRowEarlier['is_final_version_available'] != 0){
											$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_de']);
											$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['final_version_en']);
										}else{
											if($fv_comparison_option == 1){
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['searchable_text_en']);
											}else{
												$compSymptomString_de_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_de']);
												$compSymptomString_en_ealier =  mysqli_real_escape_string($db, $quelleComparingSymptomRowEarlier['BeschreibungFull_en']);
											}
										}

										
										// Apply dynamic conversion
										if($quelleComparingSymptomRowEarlier['swap_ce'] != 0){
											if($compSymptomString_de_ealier != ""){
												$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap_ce'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
												// $compSymptomString_de = base64_encode($compSymptomString_de);
											}
											if($compSymptomString_en_ealier != ""){
												$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap_ce'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
												// $compSymptomString_en = base64_encode($compSymptomString_en);
											}
										}else if($quelleComparingSymptomRowEarlier['swap'] != 0){
											if($compSymptomString_de_ealier != ""){
												$compSymptomString_de_ealier = convertTheSymptom($compSymptomString_de_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
												// $compSymptomString_de = base64_encode($compSymptomString_de);
											}
											if($compSymptomString_en_ealier != ""){
												$compSymptomString_en_ealier = convertTheSymptom($compSymptomString_en_ealier, $quelleComparingSymptomRowEarlier['quelle_id'], $quelleComparingSymptomRowEarlier['arznei_id'], $quelleComparingSymptomRowEarlier['swap'], 0, $quelleComparingSymptomRowEarlier['symptom_id']);
												// $compSymptomString_en = base64_encode($compSymptomString_en);
											}
										}else {
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
										$earlierConnectionSaveArray['symptom_id'] = $quelleComparingSymptomRowEarlier['symptom_id'];
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
							}
						}

						if($rowIdToInsertFrom != "" AND !empty($comparingSymptomsArray)){

							//#10 Deleting all the comparing symptoms under that initial
							$deleteExistingComparingSymptoms = "DELETE FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."'";
							$deleteRes = $db->query($deleteExistingComparingSymptoms);
							if($deleteRes == true){
								$count = $rowIdToInsertFrom;
								$idModify = $count -1;

								//#12 Inserting comparatives below the initial from the data array 
								foreach ($comparingSymptomsArray as $comparingRowKey => $comparingRow) {
									$insertComparative="INSERT INTO $comparison_table_name (id, symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_final_version_available, matched_percentage, ersteller_datum,connection) VALUES ($count, NULLIF('".$comparingRow['symptom_id']."', ''), NULLIF('".$initial_symptom_id."', ''), NULLIF('".$comparingRow['is_initial_symptom']."', ''), NULLIF('".$comparingRow['quelle_code']."', ''), NULLIF('".$comparingRow['quelle_titel']."', ''), NULLIF('".$comparingRow['quelle_type_id']."', ''), NULLIF('".$comparingRow['quelle_jahr']."', ''), NULLIF('".$comparingRow['quelle_band']."', ''), NULLIF('".$comparingRow['quelle_auflage']."', ''), NULLIF('".$comparingRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comparingRow['arznei_id']."', ''), NULLIF('".$comparingRow['quelle_id']."', ''),NULLIF('".$comparingRow['original_quelle_id']."', ''), NULLIF('".$comparingRow['Symptomnummer']."', ''), NULLIF('".$comparingRow['SeiteOriginalVon']."', ''), NULLIF('".$comparingRow['SeiteOriginalBis']."', ''), NULLIF('".$comparingRow['final_version_de']."', ''), NULLIF('".$comparingRow['final_version_en']."', ''), NULLIF('".$comparingRow['Beschreibung_de']."', ''), NULLIF('".$comparingRow['Beschreibung_en']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comparingRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comparingRow['BeschreibungFull_de']."', ''), NULLIF('".$comparingRow['BeschreibungFull_en']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_de']."', ''), NULLIF('".$comparingRow['BeschreibungPlain_en']."', ''), NULLIF('".$comparingRow['searchable_text_de']."', ''), NULLIF('".$comparingRow['searchable_text_en']."', ''), NULLIF('".$comparingRow['bracketedString_de']."', ''), NULLIF('".$comparingRow['bracketedString_en']."', ''), NULLIF('".$comparingRow['timeString_de']."', ''), NULLIF('".$comparingRow['timeString_en']."', ''), NULLIF('".$comparingRow['comparing_source_original_language']."', ''), NULLIF('".$comparingRow['Fussnote']."', ''), NULLIF('".$comparingRow['EntnommenAus']."', ''), NULLIF('".$comparingRow['Verweiss']."', ''), NULLIF('".$comparingRow['BereichID']."', ''), NULLIF('".$comparingRow['Kommentar']."', ''), NULLIF('".$comparingRow['Unklarheiten']."', ''), NULLIF('".$comparingRow['Remedy']."', ''), NULLIF('".$comparingRow['symptom_of_different_remedy']."', ''), NULLIF('".$comparingRow['subChapter']."', ''), NULLIF('".$comparingRow['subSubChapter']."', ''),NULLIF('".$comparingRow['synonym_word']."', ''),NULLIF('".$comparingRow['strict_synonym']."', ''),NULLIF('".$comparingRow['synonym_partial_1']."', ''),NULLIF('".$comparingRow['synonym_partial_2']."', ''),NULLIF('".$comparingRow['synonym_general']."', ''),NULLIF('".$comparingRow['synonym_minor']."', ''),NULLIF('".$comparingRow['synonym_nn']."', ''),NULLIF('".$comparingRow['comparison_matched_synonyms']."', ''), NULLIF('".$comparingRow['symptom_edit_comment']."', ''), NULLIF('".$comparingRow['is_final_version_available']."', ''), NULLIF('".$comparingRow['matched_percentage']."', ''), NULLIF('".$date."', ''), NULLIF('".$comparingRow['connection']."', ''))";
									$db->query($insertComparative);

									$count++;
								}

								//Updting the synonyms in the initial symptom
								$condition = "SET synonym_word = '".$dataSynonymInitial['synonym_word']."', strict_synonym = '".$dataSynonymInitial['strict_synonym']."', synonym_partial_1 = '".$dataSynonymInitial['synonym_partial_1']."', synonym_partial_2 = '".$dataSynonymInitial['synonym_partial_2']."', synonym_general = '".$dataSynonymInitial['synonym_general']."', synonym_minor = '".$dataSynonymInitial['synonym_minor']."', synonym_nn = '".$dataSynonymInitial['synonym_nn']."' WHERE symptom_id = '".$initial_symptom_id."'";
								$updateSymptomInitial = "UPDATE $comparison_table_name $condition";
								$updateRes = $db->query($updateSymptomInitial);
							}
						}
						
						// Update in connection in connections table
						$deleteExistingQuery="DELETE FROM ".$comparison_table_name."_connections WHERE initial_symptom_id = '".$initial_symptom_id."' AND comparing_symptom_id = '".$comparative_symptom_id."'";
		            	$db->query($deleteExistingQuery);

		            	//updating marking in the comparison table
						markingUpdation($db,$comparison_table_name,"0",$comparative_symptom_id);
		            	
		            	if(!empty($earlierConnectionSaveArrayFinal)){
							foreach ($earlierConnectionSaveArrayFinal as $earlierConnectionFinalRow) {
								if($earlierConnectionFinalRow['operationFlag'] == 3 || $earlierConnectionFinalRow['operationFlag'] == 1){
									$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0' WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
									
								}else if($earlierConnectionFinalRow['operationFlag'] == 2){
									$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),is_earlier_connection = '0'  WHERE comparing_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND initial_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";

								}else if($earlierConnectionFinalRow['operationFlag'] == 4){

									$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''),comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='0',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['initialId']."'";
								}else{
									//for paste and paste edit previous connection
									$updateSymptomEarlier = "UPDATE ".$comparison_table_name."_connections SET initial_symptom_id = NULLIF('".$comparative_symptom_id."', ''), comparing_symptom_id = NULLIF('".$earlierConnectionFinalRow['comparativeIdToSend']."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_initial_symptom_de = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), initial_quelle_code = NULLIF('".$connectionDataArray['comparing_quelle_code']."', ''), initial_quelle_original_language = NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),  initial_symptom_de = NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''), initial_symptom_en = NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''), initial_quelle_id = NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''), highlighted_comparing_symptom_de = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_de']."', ''), highlighted_comparing_symptom_en = NULLIF('".$earlierConnectionFinalRow['highlighted_comparing_symptom_en']."', ''), matched_percentage = NULLIF('".$earlierConnectionFinalRow['matched_percentage']."', ''),free_flag='1',is_earlier_connection = '0' WHERE initial_symptom_id = '".$earlierConnectionFinalRow['comparativeIdToSend']."' AND comparing_symptom_id = '".$earlierConnectionFinalRow['earlierConnectedId']."'";
								}
								$allQueryArray = array();

								$allQueryArray['query'] = $updateSymptomEarlier;
								$allQueryArray['comparing_symptom_id'] = $earlierConnectionFinalRow['comparativeIdToSend'];
								$allQueryArray['initial_symptom_id'] = $earlierConnectionFinalRow['initialId'];
								$allQueryArrayFinal[] = $allQueryArray;

								$db->query($updateSymptomEarlier);
							}
						}
	            		
						// Updation in highest match table
						if(!empty($updateHighestMatchSymptomIdArray)){
							foreach ($updateHighestMatchSymptomIdArray as $symId) {
								// $fetchHighestMatchResult = mysqli_query($db,"SELECT id, matched_percentage FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."' ORDER BY matched_percentage DESC LIMIT 1");
								$fetchHighestMatchResult = mysqli_query($db,"SELECT max(matched_percentage) AS highest_match_percentage, id FROM ".$comparison_table_name." WHERE symptom_id = '".$symId."'");
								if(mysqli_num_rows($fetchHighestMatchResult) > 0){
									$fetchedHighestMatchedRow = mysqli_fetch_assoc($fetchHighestMatchResult);

									$updateHighestMatchDetails = "UPDATE ".$comparison_table_name."_highest_matches SET comparison_table_id = NULLIF('".$fetchedHighestMatchedRow['id']."', ''), matched_percentage = NULLIF('".$fetchedHighestMatchedRow['highest_match_percentage']."', '') WHERE symptom_id = '".$symId."'";
									$updateRes = $db->query($updateHighestMatchDetails);
								}
							}
						} 
					}
				}

				
				$resultData = $completeSendingArray;
				$status = "success";
				$message = 'ok';
			} else {
				$status = 'error';
	    		$message = 'Could not find final vesrion type';
			}
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}

	echo json_encode( array( 'status' => 'ok','result_data'=>$comparingSymptomsArray, 'connectionDataArray'=>$connectionDataArray) ); 

	function sortByOrder($a, $b) {
	   return  $b['matched_percentage'] - $a['matched_percentage'];
	}
?>
<?php
	include 'includes/php-foot-includes.php';
?>