<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if(isset($_POST['form']) AND !empty($_POST['form'])){	
			parse_str( $_POST['form'], $formData );
			
			$fv_symptom_de = (isset($formData['fv_symptom_de']) AND $formData['fv_symptom_de'] != "") ? $formData['fv_symptom_de'] : "";
			$fv_symptom_en = (isset($formData['fv_symptom_en']) AND $formData['fv_symptom_en'] != "") ? $formData['fv_symptom_en'] : "";
			$fv_comparison_language = (isset($formData['fv_comparison_language']) AND $formData['fv_comparison_language'] != "") ? $formData['fv_comparison_language'] : "";
			$fv_symptom_id = (isset($formData['fv_symptom_id']) AND $formData['fv_symptom_id'] != "") ? $formData['fv_symptom_id'] : "";
			$fv_initial_source_symptom_id = (isset($formData['fv_initial_source_symptom_id']) AND $formData['fv_initial_source_symptom_id'] != "") ? $formData['fv_initial_source_symptom_id'] : "";
			$fv_comparing_source_symptom_id = (isset($formData['fv_comparing_source_symptom_id']) AND $formData['fv_comparing_source_symptom_id'] != "") ? $formData['fv_comparing_source_symptom_id'] : "";
			$fv_comparison_option = (isset($formData['fv_comparison_option']) AND $formData['fv_comparison_option'] != "") ? $formData['fv_comparison_option'] : "";
			// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
			$fv_connection_or_paste_type = (isset($formData['fv_connection_or_paste_type']) AND $formData['fv_connection_or_paste_type'] != "") ? $formData['fv_connection_or_paste_type'] : 0;
			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
 			$is_final_version_available = 0;
			if($fv_connection_or_paste_type == 3)
				$is_final_version_available = 1;
			else if($fv_connection_or_paste_type == 4)
				$is_final_version_available = 2;


			// Soruce symptom existance checking START
			$isSymptomsAvailable = 1;
			if($fv_initial_source_symptom_id != "" AND $fv_comparing_source_symptom_id != ""){
				$checkIniSymp = mysqli_query($db, "SELECT id FROM quelle_import_test WHERE id = '".$fv_initial_source_symptom_id."'");
				if(mysqli_num_rows($checkIniSymp) == 0){
					$isSymptomsAvailable = 0;
				}

				$checkComSymp = mysqli_query($db, "SELECT id FROM quelle_import_test WHERE id = '".$fv_comparing_source_symptom_id."'");
				if(mysqli_num_rows($checkComSymp) == 0){
					$isSymptomsAvailable = 0;
				}
			}else{
				$isSymptomsAvailable = 0;
			}

			if($isSymptomsAvailable == 0){
				echo json_encode( array( 'status' => 'invalid', 'result_data' => $resultData, 'message' => $message) );
				exit;
			}
			// Soruce symptom existance checking END
			
			if($is_final_version_available != 0){
				$fv_symptom_de = prepareFinalVersionSymptom($fv_symptom_de);
				$fv_symptom_en = prepareFinalVersionSymptom($fv_symptom_en);

				$fv_symptom_de_insert = (isset($fv_symptom_de) AND $fv_symptom_de != "") ? mysqli_real_escape_string($db, $fv_symptom_de) : "";
				$fv_symptom_en_insert = (isset($fv_symptom_en) AND $fv_symptom_en != "") ? mysqli_real_escape_string($db, $fv_symptom_en) : "";
				$updateSymptom = "UPDATE quelle_import_test SET final_version_de = NULLIF('".$fv_symptom_de_insert."', ''), final_version_en = NULLIF('".$fv_symptom_en_insert."', ''), is_final_version_available = '".$is_final_version_available."' WHERE id = '".$fv_symptom_id."'";
				$db->query($updateSymptom);

				$data = array();
				$InitialQuelleResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code, quelle_import_test.arznei_id, quelle_import_test.original_quelle_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.id = '".$fv_initial_source_symptom_id."'");
				if(mysqli_num_rows($InitialQuelleResult) > 0){
					while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){
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
							$iniSymptomString_de = convertTheSymptom($iniSymptomString_de, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
							$iniSymptomString_de = base64_encode($iniSymptomString_de);
						}
						if($iniSymptomString_en != ""){
							$iniSymptomString_en = convertTheSymptom($iniSymptomString_en, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], $iniSymRow['is_final_version_available'], 0, $iniSymRow['id'], $iniSymRow['original_symptom_id']);
							$iniSymptomString_en = base64_encode($iniSymptomString_en);
						}


						$quelleComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.quelle_code, quelle_import_test.arznei_id, quelle_import_test.original_quelle_id, quelle_import_test.final_version_de, quelle_import_test.final_version_en, quelle_import_test.BeschreibungPlain_de, quelle_import_test.BeschreibungPlain_en, quelle_import_test.BeschreibungOriginal_de, quelle_import_test.BeschreibungOriginal_en, quelle_import_test.BeschreibungFull_de, quelle_import_test.BeschreibungFull_en, quelle_import_test.searchable_text_de, quelle_import_test.searchable_text_en, quelle_import_test.is_final_version_available, quelle_import_test.id, quelle_import_test.original_symptom_id, quelle_import_test.Kommentar, quelle_import_test.Fussnote FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.id = '".$fv_comparing_source_symptom_id."'");
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
								$compSymptomString_de = convertTheSymptom($compSymptomString_de, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
								$compSymptomString_de = base64_encode($compSymptomString_de);
							}
							if($compSymptomString_en != ""){
								$compSymptomString_en = convertTheSymptom($compSymptomString_en, $quelleComparingSymptomRow['original_quelle_id'], $quelleComparingSymptomRow['arznei_id'], $quelleComparingSymptomRow['is_final_version_available'], 0, $quelleComparingSymptomRow['id'], $quelleComparingSymptomRow['original_symptom_id']);
								$compSymptomString_en = base64_encode($compSymptomString_en);
							}

							if($fv_comparison_language == "en"){
								// English
								$resultArray = comareSymptom2($iniSymptomString_en, $compSymptomString_en, $iniSymptomStringBeforeConversion_en, $compSymptomStringBeforeConversion_en);
								$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
								$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
								$initial_source_symptom_highlighted_en = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
								// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
								$comparing_source_symptom_highlighted_en = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
								// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
								$initial_source_symptom_before_conversion_highlighted_en = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : null;
								$comparing_source_symptom_before_conversion_highlighted_en = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : null;

								// German
								$initial_source_symptom_highlighted_de = (isset($iniSymptomString_de)) ? $iniSymptomString_de : null;
								$comparing_source_symptom_highlighted_de = (isset($compSymptomString_de)) ? $compSymptomString_de : null;
								$initial_source_symptom_before_conversion_highlighted_de = (isset($iniSymptomString_de)) ? $iniSymptomString_de : null;
								$comparing_source_symptom_before_conversion_highlighted_de = (isset($compSymptomString_de)) ? $compSymptomString_de : null;

							} else {
								// German
								$resultArray = comareSymptom2($iniSymptomString_de, $compSymptomString_de, $iniSymptomStringBeforeConversion_de, $compSymptomStringBeforeConversion_de);
								$no_of_match = (isset($resultArray['no_of_match'])) ? $resultArray['no_of_match'] : 0;
								$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
								$initial_source_symptom_highlighted_de = (isset($resultArray['initial_source_symptom_highlighted'])) ? $resultArray['initial_source_symptom_highlighted'] : null;
								// $initial_source_symptom_highlighted = htmlentities($initial_source_symptom_highlighted);
								$comparing_source_symptom_highlighted_de = (isset($resultArray['comparing_source_symptom_highlighted'])) ? $resultArray['comparing_source_symptom_highlighted'] : null;
								// $comparing_source_symptom_highlighted = htmlentities($comparing_source_symptom_highlighted);
								$initial_source_symptom_before_conversion_highlighted_de = (isset($resultArray['initial_source_symptom_before_conversion_highlighted'])) ? $resultArray['initial_source_symptom_before_conversion_highlighted'] : null;
								$comparing_source_symptom_before_conversion_highlighted_de = (isset($resultArray['comparing_source_symptom_before_conversion_highlighted'])) ? $resultArray['comparing_source_symptom_before_conversion_highlighted'] : null;

								// English
								$initial_source_symptom_highlighted_en = (isset($iniSymptomString_en)) ? $iniSymptomString_en : null;
								$comparing_source_symptom_highlighted_en = (isset($compSymptomString_en)) ? $compSymptomString_en : null;
								$initial_source_symptom_before_conversion_highlighted_en = (isset($iniSymptomString_en)) ? $iniSymptomString_en : null;
								$comparing_source_symptom_before_conversion_highlighted_en = (isset($compSymptomString_en)) ? $compSymptomString_en : null;
							}

							$data = array(
								"percentage" => $percentage,
								"initial_source_symptom_highlighted_de" => $initial_source_symptom_highlighted_de,
								"initial_source_symptom_de" => $iniSymptomString_de,
								"initial_source_symptom_before_conversion_highlighted_de" => $initial_source_symptom_before_conversion_highlighted_de,
								"initial_source_symptom_before_conversion_de" => $iniSymptomStringBeforeConversion_de,
								"initial_source_symptom_highlighted_en" => $initial_source_symptom_highlighted_en,
								"initial_source_symptom_en" => $iniSymptomString_en,
								"initial_source_symptom_before_conversion_highlighted_en" => $initial_source_symptom_before_conversion_highlighted_en,
								"initial_source_symptom_before_conversion_en" => $iniSymptomStringBeforeConversion_en,
								"comparing_source_symptom_highlighted_de" => $comparing_source_symptom_highlighted_de,
								"comparing_source_symptom_de" => $compSymptomString_de,
								"comparing_source_symptom_before_conversion_highlighted_de" => $comparing_source_symptom_before_conversion_highlighted_de,
								"comparing_source_symptom_before_conversion_de" => $compSymptomStringBeforeConversion_de,
								"comparing_source_symptom_highlighted_en" => $comparing_source_symptom_highlighted_en,
								"comparing_source_symptom_en" => $compSymptomString_en,
								"comparing_source_symptom_before_conversion_highlighted_en" => $comparing_source_symptom_before_conversion_highlighted_en,
								"comparing_source_symptom_before_conversion_en" => $compSymptomStringBeforeConversion_en,
							);
						}
					}
				}
				
				$resultData = $data;
				$status = "success";
				$message = "success";
			} else {
				$status = 'error';
	    		$message = 'Could not find final vesrion type';
			}
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