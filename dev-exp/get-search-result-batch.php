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
	$step = 'done';
	$progress_percentage = 0;
	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])){
			
			parse_str( $_POST['form'], $formData );
			$searchKeyword = (isset($formData['search_keyword']) AND $formData['search_keyword'] != "") ? trim($formData['search_keyword']) : null;
			$searchSources = (isset($formData['search_sources']) AND !empty($formData['search_sources'])) ? $formData['search_sources'] : array();
			$step = (isset($_POST['step']) AND $_POST['step'] != "") ? $_POST['step'] : 1;
			if($searchKeyword != "" AND !empty($searchSources)){
				$sourceIds = implode(',', $searchSources);
				$totalSymptomQuery = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE quelle_id IN (".$sourceIds.")");
				$totalInitialSymptom = mysqli_num_rows($totalSymptomQuery);
				$limit = $totalInitialSymptom;
				// $limit = 2;
				$batches = ceil($totalInitialSymptom / $limit);

				$pageno = (int)$step;
				if ($pageno > $batches) {
				   $pageno = $batches;
				} 
				if ($pageno < 1) {
				   $pageno = 1;
				} 
				// $offset = ($pageno - 1)  * $limit;
				$offset = 0;

				// Progress bar percentage
				$progress_percentage = round(($step / $batches) * 100);

				$cleanKeywordString = str_replace(array(".", ",",":",";","!","(",")","[","]","{","}","|", "\\", "/", "?", "<", ">", "*", "°"),'',$searchKeyword);
    			$searchKeywordArray = explode(" ", $cleanKeywordString);
    			$searchKeywordArray = array_filter($searchKeywordArray,'longenough');
    			$searchKeywordLowerArray = array_map('strtolower', $searchKeywordArray);
    			$totalKeyWord = count($searchKeywordArray);
    			$matchedSymptomArray = array();
    			// Encoding for Comparison
				$searchKeyword = base64_encode($searchKeyword);
				$quelleSymptomResult = mysqli_query($db,"SELECT quelle_import_test.original_symptom_id, quelle_import_test.quelle_code, quelle_import_test.original_quelle_id, quelle_import_test.arznei_id, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.id FROM quelle_import_test WHERE quelle_import_test.quelle_id IN (".$sourceIds.") LIMIT ".$offset.", ".$limit);
    			if(mysqli_num_rows($quelleSymptomResult) > 0){
					while($quelleSymptomRow = mysqli_fetch_array($quelleSymptomResult)){
						$compSymptomString_de =  trim($quelleSymptomRow['BeschreibungOriginal_de']);
						$compSymptomString_en =  trim($quelleSymptomRow['BeschreibungOriginal_en']);

						// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
						$compSymptomStringBeforeConversion_de = base64_encode($compSymptomString_de);
						$compSymptomStringBeforeConversion_en = base64_encode($compSymptomString_en);

						// Apply dynamic conversion
						// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
						// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
						// convertTheSymptom()
						$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleSymptomRow['original_quelle_id'], $quelleSymptomRow['arznei_id'], 0, 0, $quelleSymptomRow['id'], $quelleSymptomRow['original_symptom_id']);
						$compSymptomString_de = base64_encode($compSymptomString_de);

						// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
						// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
						// convertTheSymptom()
						$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleSymptomRow['original_quelle_id'], $quelleSymptomRow['arznei_id'], 0, 0, $quelleSymptomRow['id'], $quelleSymptomRow['original_symptom_id']);
						$compSymptomString_en = base64_encode($compSymptomString_en);
						
						
						// German
						$resultArray = comareSymptom2($searchKeyword, $compSymptomString_de, null, $compSymptomStringBeforeConversion_de);
						$no_of_match_de = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
						$percentage_de = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
						$initial_source_symptom_highlighted_de = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
						// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
						$comparing_source_symptom_highlighted_de = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
						// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
						
						// English
						$resultArray = comareSymptom2($searchKeyword, $compSymptomString_en, null, $compSymptomStringBeforeConversion_en);
						$no_of_match_en = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
						$percentage_en = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
						$initial_source_symptom_highlighted_en = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
						// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
						$comparing_source_symptom_highlighted_en = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
						// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
						$percentage = 0;
						$no_of_match = 0;
						if($percentage_de > $percentage_en){
							$percentage = $percentage_de;
							$no_of_match = $no_of_match_de;
						}
						else{
							$percentage = $percentage_en;
							$no_of_match = $no_of_match_en;
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
								"symptom_highlighted_de" => $comparing_source_symptom_highlighted_de,
								"symptom_de" => $compSymptomString_de,
								"symptom_highlighted_en" => $comparing_source_symptom_highlighted_en,
								"symptom_en" => $compSymptomString_en
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
			
			if($step < $batches)
				$step = $step + 1;
			else
				$step = 'done';

		}
	} catch (Exception $e) {
	    $step = 'error';
	}


	echo json_encode( array( 'step' => $step, 'data' => $formData, 'result_data' => $matchedSymptomArray, 'progress_percentage' => $progress_percentage) ); 
	exit;
?>
<?php
	include 'includes/php-foot-includes.php';
?>