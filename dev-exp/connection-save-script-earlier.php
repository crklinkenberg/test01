<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$resultData = array();
	$test= array();
	$status = 'error';
	$message = 'Could not perform the action.';
	try {
		$comparing_symptom_id = (isset($_POST['comparing_symptom_id']) AND $_POST['comparing_symptom_id'] != "") ? $_POST['comparing_symptom_id'] : "";
		$earlier_symptom_id = (isset($_POST['earlier_symptom_id']) AND $_POST['earlier_symptom_id'] != "") ? $_POST['earlier_symptom_id'] : "";
		$initial_symptom_id = (isset($_POST['initial_symptom_id']) AND $_POST['initial_symptom_id'] != "") ? $_POST['initial_symptom_id'] : "";
		$initial_quelle_id = (isset($_POST['initial_quelle_id']) AND $_POST['initial_quelle_id'] != "") ? $_POST['initial_quelle_id'] : "";
		$initial_symptom_text = (isset($_POST['initial_symptom_text']) AND $_POST['initial_symptom_text'] != "") ? $_POST['initial_symptom_text'] : "";
		$initial_quelle_code = (isset($_POST['initial_quelle_code']) AND $_POST['initial_quelle_code'] != "") ? $_POST['initial_quelle_code'] : "";
		$initial_quelle_original_language = (isset($_POST['initial_quelle_original_language']) AND $_POST['initial_quelle_original_language'] != "") ? $_POST['initial_quelle_original_language'] : "";
		$initial_year = (isset($_POST['initial_year']) AND $_POST['initial_year'] != "") ? $_POST['initial_year'] : "";
		$initial_symptom_de = (isset($_POST['initial_symptom_de']) AND $_POST['initial_symptom_de'] != "") ? $_POST['initial_symptom_de'] : "";
		$initial_symptom_en = (isset($_POST['initial_symptom_en']) AND $_POST['initial_symptom_en'] != "") ? $_POST['initial_symptom_en'] : "";
		$initial_quelle_id = (isset($_POST['initial_quelle_id']) AND $_POST['initial_quelle_id'] != "") ? $_POST['initial_quelle_id'] : "";
		$comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? $_POST['comparison_language'] : "";
		$type = (isset($_POST['type']) AND $_POST['type'] != "") ? $_POST['type'] : "";
		$free_flag = (isset($_POST['free_flag']) AND $_POST['free_flag'] != "") ? $_POST['free_flag'] : "";
		$cutoff_percentage = (isset($_POST['sub_connected_percentage']) AND $_POST['sub_connected_percentage'] != "") ? $_POST['sub_connected_percentage'] : "";
		$fv_comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : "";

		
		// Getting main comparison data array from session
		$comparisonTableDataArr = (isset($_SESSION['comparison_table_data']) AND !empty($_SESSION['comparison_table_data'])) ? $_SESSION['comparison_table_data'] : array(); 
		// Comparison table don't exist in DB then the session data and other required data empty. 
		$comparison_table_name = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
		
		$comparisonSavedDataTable = $comparison_table_name."_connections";

		//Symptom text according to language	
		if($comparison_language == "de"){
			$connectedSymptomTextDe = $initial_symptom_text;
			$connectedSymptomTextEn = "";
		}
		else{
			$connectedSymptomTextDe = "";
			$connectedSymptomTextEn = $initial_symptom_text;
		}
		
		//Decoding strings before inserting to the table
		$initial_symptom_de = base64_decode($initial_symptom_de);
		$initial_symptom_en = base64_decode($initial_symptom_en);

		//array declaration
		$updateHighestMatchSymptomIdArray = array();

		//Array for veryfing post values
		$data= array(
			'comparing_symptom_id'=>$comparing_symptom_id,
			'earlier_symptom_id'=>$earlier_symptom_id,
			'initial_symptom_id'=> $initial_symptom_id,
			'initial_quelle_id'=>$initial_quelle_id,
			'initial_symptom_text'=>$initial_symptom_text,
			'initial_quelle_code'=>$initial_quelle_code,
			'initial_quelle_original_language'=>$initial_quelle_original_language,
			'initial_year'=>$initial_year,
			'initial_symptom_de'=>$initial_symptom_de,
			'initial_symptom_en'=>$initial_symptom_en,
			'initial_quelle_id'=>$initial_quelle_id,
			'saved_table_name'=>$comparisonSavedDataTable,
			'comparison_language'=>$comparison_language,
			'free_flag'=>$free_flag,
			'type'=>$type,
			'cutoff_percentage'=>$cutoff_percentage,
			'comparisonTable'=>$comparison_table_name
		);

		// echo json_encode( array( 'status' => $status, 'result_data' => $data) ); 
		// exit;

		// Data for connection table insertion start 
		$connectionDataArray = array();
		$testArray = array();
		$connectionDataArray['initial_symptom_id'] = $initial_symptom_id;
		$connectionDataArray['comparing_symptom_id'] = $comparing_symptom_id;
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

		//selecting data of the initial 
		$InitialQuelleResultQuery = "SELECT * FROM $comparison_table_name WHERE symptom_id = '".$initial_symptom_id."' AND is_initial_symptom = '1'";
		$InitialQuelleResult = mysqli_query($db,$InitialQuelleResultQuery);

		if(mysqli_num_rows($InitialQuelleResult) > 0){
			while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
				$runningInitialSymptomId = $iniSymRow['symptom_id'];
				// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
				if($iniSymRow['is_final_version_available'] != 0){
					$iniSymptomString_de =  $iniSymRow['final_version_de'];
					$iniSymptomString_en =  $iniSymRow['final_version_en'];
				} else {
					if($fv_comparison_option == 1){
						$iniSymptomString_de =  $iniSymRow['searchable_text_de'];
						$iniSymptomString_en =  $iniSymRow['searchable_text_en'];
					}else{
						$iniSymptomString_de =  $iniSymRow['BeschreibungFull_de'];
						$iniSymptomString_en =  $iniSymRow['BeschreibungFull_en'];
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

				// Comparing symptoms
				$quelleComparingSymptomResultQuery = "SELECT * FROM $comparison_table_name WHERE initial_symptom_id = '".$initial_symptom_id."' AND symptom_id = '".$comparing_symptom_id."'";
				$quelleComparingSymptomResult = mysqli_query($db,$quelleComparingSymptomResultQuery);
				while($quelleComparingSymptomRow = mysqli_fetch_array($quelleComparingSymptomResult)){
					// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
					if($quelleComparingSymptomRow['is_final_version_available'] != 0){
						$compSymptomString_de =  $quelleComparingSymptomRow['final_version_de'];
						$compSymptomString_en =  $quelleComparingSymptomRow['final_version_en'];
					}else{
						if($fv_comparison_option == 1){
							$compSymptomString_de =  $quelleComparingSymptomRow['searchable_text_de'];
							$compSymptomString_en =  $quelleComparingSymptomRow['searchable_text_en'];
						}else{
							$compSymptomString_de =  $quelleComparingSymptomRow['BeschreibungFull_de'];
							$compSymptomString_en =  $quelleComparingSymptomRow['BeschreibungFull_en'];
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
					$resultArray = newComareSymptom($ini, $com);
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
					if($comparing_symptom_id == $quelleComparingSymptomRow['symptom_id']){
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
					// Id collect for highest match table update end

				}
			}
		}
		
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
		// echo json_encode( array( 'status' => $status, 'result_data' => $data) ); 
		// exit;
		switch($type){
			case 'connect':{
				$updateSymptom = "UPDATE $comparisonSavedDataTable SET initial_symptom_id = NULLIF('".$initial_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = '".$connectionDataArray['highlighted_initial_symptom_de']."', initial_quelle_code = '".$connectionDataArray['initial_quelle_code']."', initial_quelle_original_language = '".$connectionDataArray['initial_quelle_original_language']."', initial_symptom_de = '".$connectionDataArray['initial_symptom_de']."', initial_symptom_en = '".$connectionDataArray['initial_symptom_en']."', initial_quelle_id = '".$connectionDataArray['initial_quelle_id']."', matched_percentage = '".$percentage."', highlighted_comparing_symptom_de = '".$connectionDataArray['highlighted_comparing_symptom_de']."', highlighted_comparing_symptom_en = '".$connectionDataArray['highlighted_comparing_symptom_en']."',is_earlier_connection='0' WHERE comparing_symptom_id = '".$comparing_symptom_id."' AND initial_symptom_id = '".$earlier_symptom_id."'";

				break;
			}
			case 'paste':{
				$selectQuery = mysqli_query($db, "SELECT * FROM $comparisonSavedDataTable WHERE  `initial_symptom_id`=$comparing_symptom_id AND `comparing_symptom_id` = $earlier_symptom_id ");
				if(mysqli_num_rows($selectQuery) > 0){
					while($row = mysqli_fetch_array($selectQuery)){
						
						$initial_quelle_id_fetched = $row['initial_quelle_id'];
						$highlighted_initial_symptom_en_fetched = $row['highlighted_initial_symptom_en'];
						$highlighted_initial_symptom_de_fetched = $row['highlighted_initial_symptom_de'];
						$initial_quelle_code_fetched = $row['initial_quelle_code'];
						$initial_quelle_original_language_fetched = $row['initial_quelle_original_language'];
						$initial_year_fetched = $row['initial_year'];
						$comparing_year_fetched = $row['comparing_year'];
						$initial_symptom_de_fetched = $row['initial_symptom_de'];
						$initial_symptom_en_fetched = $row['initial_symptom_en'];

						//encoding to preserve integrity
						$initial_symptom_de_fetched = $initial_symptom_de;
						$initial_symptom_en_fetched = $initial_symptom_en;

						$connection_type = $row['connection_type'];
					}
				}
				
				//$updateSymptom = "UPDATE $comparisonSavedDataTable SET initial_symptom_id = NULLIF('".$initial_symptom_id."', ''),comparing_symptom_id = NULLIF('".$comparing_symptom_id."', ''), highlighted_initial_symptom_en = NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), highlighted_initial_symptom_de = '".$connectionDataArray['highlighted_initial_symptom_de']."', initial_quelle_code = '".$connectionDataArray['initial_quelle_code']."', initial_quelle_original_language = '".$connectionDataArray['initial_quelle_original_language']."', initial_symptom_de = '".$connectionDataArray['initial_symptom_de']."', initial_symptom_en = '".$connectionDataArray['initial_symptom_en']."', initial_quelle_id = '".$connectionDataArray['initial_quelle_id']."', initial_year = '".$connectionDataArray['initial_year']."', is_earlier_connection='0', highlighted_comparing_symptom_en = NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), highlighted_comparing_symptom_de = '".$connectionDataArray['highlighted_comparing_symptom_de']."', comparing_quelle_code = '".$connectionDataArray['comparing_quelle_code']."', comparing_quelle_original_language = '".$connectionDataArray['comparing_quelle_original_language']."', comparing_symptom_de = '".$connectionDataArray['comparing_symptom_de']."', comparing_symptom_en = '".$connectionDataArray['comparing_symptom_en']."', comparing_quelle_id = '".$connectionDataArray['comparing_quelle_id']."', comparing_year = '".$connectionDataArray['comparing_year']."', free_flag = '".$free_flag."', matched_percentage = '".$percentage."' WHERE initial_symptom_id = '".$comparing_symptom_id."' AND comparing_symptom_id = '".$earlier_symptom_id."'";
				
					$deleteExistingQuery="DELETE FROM $comparisonSavedDataTable WHERE comparing_symptom_id = '".$earlier_symptom_id."' AND initial_symptom_id = '".$comparing_symptom_id."'";
		            $db->query($deleteExistingQuery);
		  			$updateSymptom="INSERT INTO $comparisonSavedDataTable (comparing_symptom_id, initial_symptom_id, connection_type, matched_percentage, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, initial_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id) VALUES ($comparing_symptom_id, $initial_symptom_id, NULLIF('".$connection_type."', ''), $percentage, NULLIF('".$connectionDataArray['highlighted_comparing_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_comparing_symptom_de']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_en']."', ''), NULLIF('".$connectionDataArray['highlighted_initial_symptom_de']."', ''), '".$comparison_language."', '".$connectionDataArray['comparing_quelle_code']."', '".$connectionDataArray['initial_quelle_code']."',NULLIF('".$connectionDataArray['comparing_year']."', ''),NULLIF('".$connectionDataArray['comparing_symptom_de']."', ''),NULLIF('".$connectionDataArray['comparing_symptom_en']."', ''),NULLIF('".$connectionDataArray['comparing_quelle_original_language']."', ''),NULLIF('".$connectionDataArray['initial_quelle_original_language']."', ''),NULLIF('".$connectionDataArray['comparing_quelle_id']."', ''),NULLIF('".$connectionDataArray['initial_year']."', ''),NULLIF('".$connectionDataArray['initial_symptom_de']."', ''),NULLIF('".$connectionDataArray['initial_symptom_en']."', ''),NULLIF('".$connectionDataArray['initial_quelle_id']."', ''))";
				break;
			}
			default:
				break;
		}
		$db->query($updateSymptom);
		$status = 'success';
		$message = 'Success';
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Something went wrong';
	}
	echo json_encode( array( 'status' => $status, 'result_data' => $data, 'message' => $updateSymptom) ); 
	exit;
?>