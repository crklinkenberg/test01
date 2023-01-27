<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Generating the searched text's result among the user's selected sources 
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();

	$compareResultArray = array();
	$matchedSymptomArray = array();
	$formData = array();
	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])){
			
			parse_str( $_POST['form'], $formData );
			$searchKeyword = (isset($formData['searching_symptom']) AND $formData['searching_symptom'] != "") ? trim($formData['searching_symptom']) : null;
			$sourceIds = (isset($formData['comparing_source_ids_for_search']) AND $formData['comparing_source_ids_for_search'] != "") ? $formData['comparing_source_ids_for_search'] : "";
			$comparison_language_for_search = (isset($formData['comparison_language_for_search']) AND $formData['comparison_language_for_search'] != "") ? $formData['comparison_language_for_search'] : "";
			$comparison_option_for_search = (isset($formData['comparison_option_for_search']) AND $formData['comparison_option_for_search'] != "") ? $formData['comparison_option_for_search'] : 1;
			if($searchKeyword != "" AND $sourceIds != ""){
				$quelleSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.id, quelle_import_test.quelle_id FROM quelle_import_test WHERE quelle_import_test.quelle_id IN (".$sourceIds.")");
    			if(mysqli_num_rows($quelleSymptomResult) > 0){
					while($quelleSymptomRow = mysqli_fetch_array($quelleSymptomResult)){
						$symptomString_de = "";
						$symptomString_en = "";
				    	if($comparison_option_for_search == 1){
							$symptomString_de =  ($quelleSymptomRow['searchable_text_de'] != "") ? $quelleSymptomRow['searchable_text_de'] : "";
							$symptomString_en =  ($quelleSymptomRow['searchable_text_en'] != "") ? $quelleSymptomRow['searchable_text_en'] : "";
						}else{
							$symptomString_de =  ($quelleSymptomRow['BeschreibungFull_de'] != "") ? $quelleSymptomRow['BeschreibungFull_de'] : "";
							$symptomString_en =  ($quelleSymptomRow['BeschreibungFull_en'] != "") ? $quelleSymptomRow['BeschreibungFull_en'] : "";
						}

						if($symptomString_de != ""){
							// Converting the symptoms to it's applicable format according to the settings to present it in front of the user
							// [1st parameter] $symptom symptom string
							// [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
							// [3rd parameter] $arzneiId arzneiId 
							// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
							// [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
							// [6th parameter] $symptomId the symptom_id of the symptombelong
							// [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
							$symptomString_de = convertTheSymptom($symptomString_de, $quelleSymptomRow['quelle_id'], $quelleSymptomRow['arznei_id'], 0, 0, $quelleSymptomRow['id']);
						}
						if($symptomString_en != ""){
							// Converting the symptoms to it's applicable format according to the settings to present it in front of the user
							// [1st parameter] $symptom symptom string
							// [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
							// [3rd parameter] $arzneiId arzneiId 
							// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
							// [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
							// [6th parameter] $symptomId the symptom_id of the symptombelong
							// [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
							$symptomString_en = convertTheSymptom($symptomString_en, $quelleSymptomRow['quelle_id'], $quelleSymptomRow['arznei_id'], 0, 0, $quelleSymptomRow['id']);
						}

						// Displayable symptom string without highlighting
						$symptomString_without_highlight_en = $symptomString_en;
						$symptomString_without_highlight_de = $symptomString_de;

				    	
				    	$displayingSymptomString = "";
						if($comparison_language_for_search == "en"){
							$displayingSymptomString = $symptomString_en;
							$resultArray = newComareSymptom($searchKeyword, $symptomString_en);
							$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
							$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
							$initial_source_symptom_highlighted = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
							// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
							$comparing_source_symptom_highlighted = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
							// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
						}
						else
						{
							$displayingSymptomString = $symptomString_de;
							$resultArray = newComareSymptom($searchKeyword, $symptomString_de);
							$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
							$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
							$initial_source_symptom_highlighted = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
							// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
							$comparing_source_symptom_highlighted = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
							// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
						}
						

						// get Origin Jahr/Year
						$originInitialSourceYear = "";
						$originInitialSourceLanguage = "";
						$originInitialQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$quelleSymptomRow['original_quelle_id']."'");
						if(mysqli_num_rows($originInitialQuelleResult) > 0){
							$originIniQuelleRow = mysqli_fetch_assoc($originInitialQuelleResult);
							$originInitialSourceYear = $originIniQuelleRow['jahr'];
							if($originIniQuelleRow['sprache'] == "deutsch")
								$originInitialSourceLanguage = "de";
							else if($originIniQuelleRow['sprache'] == "englisch") 
								$originInitialSourceLanguage = "en";
						}

						if($percentage > 0){
							$matchedSymptomArray[] = array(
								"no_of_match" => $no_of_match,
								"percentage" => $percentage,
								"source_code" => $quelleSymptomRow['quelle_code'],
								"source_original_language" => $originInitialSourceLanguage,
								"symptom_highlighted" => $comparing_source_symptom_highlighted,
								"symptom" => $displayingSymptomString
							);
						}
					}
				}
    			if(!empty($matchedSymptomArray)){
    				$prce = array();
					foreach ($matchedSymptomArray as $key => $row)
					{
					    $prce[$key] = $row['percentage'];
					}
					array_multisort($prce, SORT_DESC, $matchedSymptomArray);
    			}
			}

		}
	} catch (Exception $e) {
	    // $step = 'error';
	}


	echo json_encode( array( 'data' => $formData, 'result_data' => $matchedSymptomArray) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>