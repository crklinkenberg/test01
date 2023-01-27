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
		if(isset($_POST['form']) AND !empty($_POST['form'])) {	
			parse_str( $_POST['form'], $formData );
			
			$symptom_version = (isset($formData['symptom_version']) AND $formData['symptom_version'] != "") ? trim($formData['symptom_version']) : "";
			$symptom_edit_de = (isset($formData['symptom_edit_de']) AND $formData['symptom_edit_de'] != "") ? $formData['symptom_edit_de'] : "";
			$symptom_edit_en = (isset($formData['symptom_edit_en']) AND $formData['symptom_edit_en'] != "") ? $formData['symptom_edit_en'] : "";
			$symptom_id = (isset($formData['symptom_edit_modal_symptom_id']) AND $formData['symptom_edit_modal_symptom_id'] != "") ? $formData['symptom_edit_modal_symptom_id'] : "";
			$original_source_id = (isset($formData['symptom_edit_modal_original_source_id']) AND $formData['symptom_edit_modal_original_source_id'] != "") ? $formData['symptom_edit_modal_original_source_id'] : "";
			$connection_id = (isset($formData['symptom_edit_modal_connection_id']) AND $formData['symptom_edit_modal_connection_id'] != "") ? $formData['symptom_edit_modal_connection_id'] : "";
			$comparison_table = (isset($formData['symptom_edit_modal_comparison_table']) AND $formData['symptom_edit_modal_comparison_table'] != "") ? $formData['symptom_edit_modal_comparison_table'] : "";
			//$comparison_table = "quelle_import_test";
			
			$symptom_type = (isset($formData['symptom_type']) AND $formData['symptom_type'] != "") ? $formData['symptom_type'] : "";

			$gradingData = array();
			$gradingData['normal']= (isset($formData['normal']) and $formData['normal'] != "")? $formData['normal'] : NULL;
		    $gradingData['normal_within_parentheses']= (isset($formData['normal_within_parentheses']) and $formData['normal_within_parentheses'] != "") ? $formData['normal_within_parentheses'] : NULL;
		    $gradingData['normal_end_with_t']= (isset($formData['normal_end_with_t']) and $formData['normal_end_with_t'] != "") ? $formData['normal_end_with_t'] : NULL;
		    $gradingData['normal_end_with_tt']= (isset($formData['normal_end_with_tt']) and $formData['normal_end_with_tt'] != "")? $formData['normal_end_with_tt'] : NULL;
		    $gradingData['normal_begin_with_degree']= (isset($formData['normal_begin_with_degree']) and $formData['normal_begin_with_degree'] != "")? $formData['normal_begin_with_degree'] : NULL;
		    $gradingData['normal_end_with_degree']= (isset($formData['normal_end_with_degree']) and $formData['normal_end_with_degree'] != "")? $formData['normal_end_with_degree'] : NULL;
		    $gradingData['normal_begin_with_asterisk']= (isset($formData['normal_begin_with_asterisk']) and $formData['normal_begin_with_asterisk'] != "")? $formData['normal_begin_with_asterisk'] : NULL;
		    $gradingData['normal_begin_with_asterisk_end_with_t']= (isset($formData['normal_begin_with_asterisk_end_with_t']) and $formData['normal_begin_with_asterisk_end_with_t'] != "") ? $formData['normal_begin_with_asterisk_end_with_t'] : NULL; 
		    $gradingData['normal_begin_with_asterisk_end_with_tt']= (isset($formData['normal_begin_with_asterisk_end_with_tt']) and $formData['normal_begin_with_asterisk_end_with_tt'] !="" ) ? $formData['normal_begin_with_asterisk_end_with_tt'] : NULL;
		    $gradingData['normal_begin_with_asterisk_end_with_degree']= (isset($formData['normal_begin_with_asterisk_end_with_degree']) and $formData['normal_begin_with_asterisk_end_with_degree'] !="" ) ? $formData['normal_begin_with_asterisk_end_with_degree'] : NULL;
		    $gradingData['sperrschrift']= (isset($formData['sperrschrift']) and $formData['sperrschrift'] !="" ) ? $formData['sperrschrift'] : NULL;
		    $gradingData['sperrschrift_begin_with_degree']= (isset($formData['sperrschrift_begin_with_degree']) and $formData['sperrschrift_begin_with_degree'] !="" ) ? $formData['sperrschrift_begin_with_degree'] : NULL;
		    $gradingData['sperrschrift_begin_with_asterisk']= (isset($formData['sperrschrift_begin_with_asterisk']) and $formData['sperrschrift_begin_with_asterisk'] !="" ) ? $formData['sperrschrift_begin_with_asterisk'] : NULL;
		    $gradingData['sperrschrift_bold']= (isset($formData['sperrschrift_bold']) and $formData['sperrschrift_bold'] !="" ) ? $formData['sperrschrift_bold'] : NULL;
		    $gradingData['sperrschrift_bold_begin_with_degree']= (isset($formData['sperrschrift_bold_begin_with_degree']) and $formData['sperrschrift_bold_begin_with_degree'] !="" ) ? $formData['sperrschrift_bold_begin_with_degree'] : NULL;
		    $gradingData['sperrschrift_bold_begin_with_asterisk']= (isset($formData['sperrschrift_bold_begin_with_asterisk']) and $formData['sperrschrift_bold_begin_with_asterisk'] !="" ) ? $formData['sperrschrift_bold_begin_with_asterisk'] : NULL;
		    $gradingData['kursiv']= (isset($formData['kursiv']) and $formData['kursiv'] !="" ) ? $formData['kursiv'] : NULL;
		    $gradingData['kursiv_end_with_t']= (isset($formData['kursiv_end_with_t']) and $formData['kursiv_end_with_t'] !="" ) ? $formData['kursiv_end_with_t'] : NULL;
		    $gradingData['kursiv_end_with_tt']= (isset($formData['kursiv_end_with_tt']) and $formData['kursiv_end_with_tt'] !="" ) ? $formData['kursiv_end_with_tt'] : NULL;
		    $gradingData['kursiv_begin_with_degree']= (isset($formData['kursiv_begin_with_degree']) and $formData['kursiv_begin_with_degree'] !="" ) ? $formData['kursiv_begin_with_degree'] : NULL;
		    $gradingData['kursiv_end_with_degree']= (isset($formData['kursiv_end_with_degree']) and $formData['kursiv_end_with_degree'] !="" ) ? $formData['kursiv_end_with_degree'] : NULL;
		    $gradingData['kursiv_begin_with_asterisk']= (isset($formData['kursiv_begin_with_asterisk']) and $formData['kursiv_begin_with_asterisk'] !="" ) ? $formData['kursiv_begin_with_asterisk'] : NULL;
		    $gradingData['kursiv_begin_with_asterisk_end_with_t']= (isset($formData['kursiv_begin_with_asterisk_end_with_t']) and $formData['kursiv_begin_with_asterisk_end_with_t'] !="" ) ? $formData['kursiv_begin_with_asterisk_end_with_t'] : NULL;
		    $gradingData['kursiv_begin_with_asterisk_end_with_tt']= (isset($formData['kursiv_begin_with_asterisk_end_with_tt']) and $formData['kursiv_begin_with_asterisk_end_with_tt'] !="" ) ? $formData['kursiv_begin_with_asterisk_end_with_tt'] : NULL;
		    $gradingData['kursiv_begin_with_asterisk_end_with_degree']= (isset($formData['kursiv_begin_with_asterisk_end_with_degree']) and $formData['kursiv_begin_with_asterisk_end_with_degree'] !="" ) ? $formData['kursiv_begin_with_asterisk_end_with_degree'] : NULL;
		    $gradingData['kursiv_bold']= (isset($formData['kursiv_bold']) and $formData['kursiv_bold'] !="" ) ? $formData['kursiv_bold'] : NULL;
		    $gradingData['kursiv_bold_begin_with_asterisk_end_with_t']= (isset($formData['kursiv_bold_begin_with_asterisk_end_with_t']) and $formData['kursiv_bold_begin_with_asterisk_end_with_t'] !="" ) ? $formData['kursiv_bold_begin_with_asterisk_end_with_t'] : NULL;
		    $gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']= (isset($formData['kursiv_bold_begin_with_asterisk_end_with_tt']) and $formData['kursiv_bold_begin_with_asterisk_end_with_tt'] !="" ) ? $formData['kursiv_bold_begin_with_asterisk_end_with_tt'] : NULL;
		    $gradingData['kursiv_bold_begin_with_degree']= (isset($formData['kursiv_bold_begin_with_degree']) and $formData['kursiv_bold_begin_with_degree'] !="" ) ? $formData['kursiv_bold_begin_with_degree'] : NULL;
		    $gradingData['kursiv_bold_begin_with_asterisk']= (isset($formData['kursiv_bold_begin_with_asterisk']) and $formData['kursiv_bold_begin_with_asterisk'] !="" ) ? $formData['kursiv_bold_begin_with_asterisk'] : NULL;
		    $gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']= (isset($formData['kursiv_bold_begin_with_asterisk_end_with_degree']) and $formData['kursiv_bold_begin_with_asterisk_end_with_degree'] !="" ) ? $formData['kursiv_bold_begin_with_asterisk_end_with_degree'] : NULL;
		    $gradingData['fett']= (isset($formData['fett']) and $formData['fett'] !="" ) ? $formData['fett'] : NULL;
		    $gradingData['fett_end_with_t']= (isset($formData['fett_end_with_t']) and $formData['fett_end_with_t'] !="" ) ? $formData['fett_end_with_t'] : NULL;
		    $gradingData['fett_end_with_tt']= (isset($formData['fett_end_with_tt']) and $formData['fett_end_with_tt'] !="" ) ? $formData['fett_end_with_tt'] : NULL;
		    $gradingData['fett_begin_with_degree']= (isset($formData['fett_begin_with_degree']) and $formData['fett_begin_with_degree'] !="" ) ? $formData['fett_begin_with_degree'] : NULL;
		    $gradingData['fett_end_with_degree']= (isset($formData['fett_end_with_degree']) and $formData['fett_end_with_degree'] !="" ) ? $formData['fett_end_with_degree'] : NULL;
		    $gradingData['fett_begin_with_asterisk']= (isset($formData['fett_begin_with_asterisk']) and $formData['fett_begin_with_asterisk'] !="" ) ? $formData['fett_begin_with_asterisk'] : NULL;
		    $gradingData['fett_begin_with_asterisk_end_with_t']= (isset($formData['fett_begin_with_asterisk_end_with_t']) and $formData['fett_begin_with_asterisk_end_with_t'] !="" ) ? $formData['fett_begin_with_asterisk_end_with_t'] : NULL;
		    $gradingData['fett_begin_with_asterisk_end_with_tt']= (isset($formData['fett_begin_with_asterisk_end_with_tt']) and $formData['fett_begin_with_asterisk_end_with_tt'] !="" ) ? $formData['fett_begin_with_asterisk_end_with_tt'] : NULL;
		    $gradingData['fett_begin_with_asterisk_end_with_degree']= (isset($formData['fett_begin_with_asterisk_end_with_degree']) and $formData['fett_begin_with_asterisk_end_with_degree'] !="" ) ? $formData['fett_begin_with_asterisk_end_with_degree'] : NULL;
		    $gradingData['gross']= (isset($formData['gross']) and $formData['gross'] !="" ) ? $formData['gross'] : NULL;
		    $gradingData['gross_begin_with_degree']= (isset($formData['gross_begin_with_degree']) and $formData['gross_begin_with_degree'] !="" ) ? $formData['gross_begin_with_degree'] : NULL;
		    $gradingData['gross_begin_with_asterisk']= (isset($formData['gross_begin_with_asterisk']) and $formData['gross_begin_with_asterisk'] !="" ) ? $formData['gross_begin_with_asterisk'] : NULL;
		    $gradingData['gross_bold']= (isset($formData['gross_bold']) and $formData['gross_bold'] !="" ) ? $formData['gross_bold'] : NULL;
		    $gradingData['gross_bold_begin_with_degree']= (isset($formData['gross_bold_begin_with_degree']) and $formData['gross_bold_begin_with_degree'] !="" ) ? $formData['gross_bold_begin_with_degree'] : NULL;
		    $gradingData['gross_bold_begin_with_asterisk']= (isset($formData['gross_bold_begin_with_asterisk']) and $formData['gross_bold_begin_with_asterisk'] !="" ) ? $formData['gross_bold_begin_with_asterisk'] : NULL;
		    $gradingData['pi_sign']= (isset($formData['pi_sign']) and $formData['pi_sign'] !="" ) ? $formData['pi_sign'] : NULL;
		    $gradingData['one_bar']= (isset($formData['one_bar']) and $formData['one_bar'] !="" ) ? $formData['one_bar'] : NULL;
		    $gradingData['two_bar']= (isset($formData['two_bar']) and $formData['two_bar'] !="" ) ? $formData['two_bar'] : NULL;
		    $gradingData['three_bar']= (isset($formData['three_bar']) and $formData['three_bar'] !="" ) ? $formData['three_bar'] : NULL;
		    $gradingData['three_and_half_bar']= (isset($formData['three_and_half_bar']) and $formData['three_and_half_bar'] !="" ) ? $formData['three_and_half_bar'] : NULL;
		    $gradingData['four_bar']= (isset($formData['four_bar']) and $formData['four_bar'] !="" ) ? $formData['four_bar'] : NULL;
		    $gradingData['four_and_half_bar']= (isset($formData['four_and_half_bar']) and $formData['four_and_half_bar'] !="" ) ? $formData['four_and_half_bar'] : NULL;
		    $gradingData['five_bar']= (isset($formData['five_bar']) and $formData['five_bar'] !="" ) ? $formData['five_bar'] : NULL;
			
			if($symptom_edit_de != "" OR $symptom_edit_en != "") {
				$data = array();

				// Updating symptom
				if($connection_id != ""){
					if($symptom_edit_de != "")
						$result = editConnectedSymptom($symptom_id, $symptom_edit_de, 'de', $symptom_version, $connection_id, $comparison_table);
					else
						$result = editConnectedSymptomAsBlnak($symptom_id, 'de', $symptom_version, $connection_id, $comparison_table);
					if($symptom_edit_en != "")
						$result = editConnectedSymptom($symptom_id, $symptom_edit_en, 'en', $symptom_version, $connection_id, $comparison_table);
					else
						$result = editConnectedSymptomAsBlnak($symptom_id, 'en', $symptom_version, $connection_id, $comparison_table);
				} else {
					if($symptom_edit_de != "")
						$result = editSymptomOriginalOrConverted($symptom_id, $symptom_edit_de, 'de', $symptom_version, $comparison_table);
					else
						$result = editSymptomAsBlankOriginalOrConverted($symptom_id, 'de', $symptom_version, $comparison_table);
						// editSymptomComparisonTable($symptom_id, $symptom_edit_de, 'de', $comparison_table);
					if($symptom_edit_en != "")
						$result = editSymptomOriginalOrConverted($symptom_id, $symptom_edit_en, 'en', $symptom_version, $comparison_table);
					else
						$result = editSymptomAsBlankOriginalOrConverted($symptom_id, 'en', $symptom_version, $comparison_table);
						// editSymptomComparisonTable($symptom_id, $symptom_edit_en, 'en', $comparison_table);
				}

				$symptomTypeResult = mysqli_query($db, "SELECT symptom_type_setting_id FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomTypeResult) > 0){
					$deleteSymptomType ="DELETE FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'";
					$db->query($deleteSymptomType);
				}

				$symptomGradingResult = mysqli_query($db, "SELECT symptom_grading_settings_id FROM symptom_grading_settings WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomGradingResult) > 0){
					$deleteSymptomGrading ="DELETE FROM symptom_grading_settings WHERE symptom_id = '".$symptom_id."'";
					$db->query($deleteSymptomGrading);
				}
				
				if($symptom_type != ""){
					$symptomTypeInsertQuery="INSERT INTO symptom_type_setting (symptom_id, symptom_type, ersteller_datum) VALUES (NULLIF('".$symptom_id."', ''), NULLIF('".$symptom_type."', ''), NULLIF('".$date."', ''))";
					$db->query($symptomTypeInsertQuery);
				}
				
				$symptomGradingInsertQuery="INSERT INTO symptom_grading_settings (symptom_id, normal, normal_within_parentheses, normal_end_with_t, normal_end_with_tt, normal_begin_with_degree, normal_end_with_degree, normal_begin_with_asterisk, normal_begin_with_asterisk_end_with_t, normal_begin_with_asterisk_end_with_tt, normal_begin_with_asterisk_end_with_degree, sperrschrift, sperrschrift_begin_with_degree, sperrschrift_begin_with_asterisk, sperrschrift_bold, sperrschrift_bold_begin_with_degree, sperrschrift_bold_begin_with_asterisk, kursiv, kursiv_end_with_t, kursiv_end_with_tt, kursiv_begin_with_degree, kursiv_end_with_degree, kursiv_begin_with_asterisk, kursiv_begin_with_asterisk_end_with_t, kursiv_begin_with_asterisk_end_with_tt, kursiv_begin_with_asterisk_end_with_degree, kursiv_bold, kursiv_bold_begin_with_asterisk_end_with_t, kursiv_bold_begin_with_asterisk_end_with_tt, kursiv_bold_begin_with_degree, kursiv_bold_begin_with_asterisk, kursiv_bold_begin_with_asterisk_end_with_degree, fett, fett_end_with_t, fett_end_with_tt, fett_begin_with_degree, fett_end_with_degree, fett_begin_with_asterisk, fett_begin_with_asterisk_end_with_t, fett_begin_with_asterisk_end_with_tt, fett_begin_with_asterisk_end_with_degree, gross, gross_begin_with_degree, gross_begin_with_asterisk, gross_bold, gross_bold_begin_with_degree, gross_bold_begin_with_asterisk, pi_sign, one_bar, two_bar, three_bar, three_and_half_bar, four_bar, four_and_half_bar, five_bar, ersteller_datum) VALUES (NULLIF('".$symptom_id."', ''), NULLIF('".$gradingData['normal']."', ''), NULLIF('".$gradingData['normal_within_parentheses']."', ''), NULLIF('".$gradingData['normal_end_with_t']."', ''), NULLIF('".$gradingData['normal_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_degree']."', ''), NULLIF('".$gradingData['normal_end_with_degree']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_asterisk']."', ''), NULLIF('".$gradingData['sperrschrift_bold']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv']."', ''), NULLIF('".$gradingData['kursiv_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['fett']."', ''), NULLIF('".$gradingData['fett_end_with_t']."', ''), NULLIF('".$gradingData['fett_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_degree']."', ''), NULLIF('".$gradingData['fett_end_with_degree']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['gross']."', ''), NULLIF('".$gradingData['gross_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_begin_with_asterisk']."', ''), NULLIF('".$gradingData['gross_bold']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['pi_sign']."', ''), NULLIF('".$gradingData['one_bar']."', ''), NULLIF('".$gradingData['two_bar']."', ''), NULLIF('".$gradingData['three_bar']."', ''), NULLIF('".$gradingData['three_and_half_bar']."', ''), NULLIF('".$gradingData['four_bar']."', ''), NULLIF('".$gradingData['four_and_half_bar']."', ''), NULLIF('".$gradingData['five_bar']."', ''), NULLIF('".$date."', ''))";
				$db->query($symptomGradingInsertQuery);
				
				
				$resultData = $data;
				$status = "success";
				$message = "success";
			} else {
				$status = 'error';
	    		$message = 'Operation failed, required data not found.';
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