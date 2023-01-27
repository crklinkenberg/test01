<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Performing all connections related operations (This is used in backup section) 
	*/
?>
<?php
	$stopWords = array();
	$stopWords = getStopWords();
	
	$resultData = array();
	$status = '';
	$message = '';

	try {
		$symptom_id = (isset($_POST['symptom_id']) AND $_POST['symptom_id'] != "") ? $_POST['symptom_id'] : "";
		$original_source_id = (isset($_POST['original_source_id']) AND $_POST['original_source_id'] != "") ? $_POST['original_source_id'] : "";
		if($original_source_id != "" AND $symptom_id != ""){
			$symptomResult = mysqli_query($db, "SELECT * FROM quelle_import_test WHERE id = '".$symptom_id."'");
			if(mysqli_num_rows($symptomResult) > 0){
				$row = mysqli_fetch_assoc($symptomResult);
				$data = array();
				$data['id'] = $row['id'];
				$data['Beschreibung_de'] = $row['Beschreibung_de'];
				$data['Beschreibung_en'] = $row['Beschreibung_en'];

				$symptomTypeResult = mysqli_query($db, "SELECT * FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomTypeResult) > 0){
					$symptomTypeRow = mysqli_fetch_assoc($symptomTypeResult);
				}
				$data['symptom_type']= (isset($symptomTypeRow['symptom_type']) and $symptomTypeRow['symptom_type'] != "")? $symptomTypeRow['symptom_type'] : "";	

				$symptomGradingResult = mysqli_query($db, "SELECT * FROM quelle_grading_settings WHERE quelle_id = '".$original_source_id."'");
				if(mysqli_num_rows($symptomGradingResult) > 0){
					$gradingRow = mysqli_fetch_assoc($symptomGradingResult);
				}
				// If this symptom has it own specific garading settings then that will be applied
				// Fetching original_symptom_id 
				$originalSymptomIdResult = mysqli_query($db, "SELECT original_symptom_id FROM quelle_import_test WHERE id = '".$symptom_id."'");
				if(mysqli_num_rows($originalSymptomIdResult) > 0){
					$originalSymptomIdData = mysqli_fetch_assoc($originalSymptomIdResult);
				}
				$originalSymptomId = (isset($originalSymptomIdData['original_symptom_id']) AND $originalSymptomIdData['original_symptom_id'] != "") ? $originalSymptomIdData['original_symptom_id'] : "";
				if(isset($originalSymptomId) AND $originalSymptomId != ""){
					$originalSymptomSettingResult = mysqli_query($db, "SELECT * FROM symptom_grading_settings WHERE symptom_id = '".$originalSymptomId."'");
					if(mysqli_num_rows($originalSymptomSettingResult) > 0){
						$gradingRow = mysqli_fetch_assoc($originalSymptomSettingResult);
					}
				}
				$symptomSettingResult = mysqli_query($db, "SELECT * FROM symptom_grading_settings WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomSettingResult) > 0){
					$gradingRow = mysqli_fetch_assoc($symptomSettingResult);
				}

				$data['normal']= (isset($gradingRow['normal']) and $gradingRow['normal'] != "")? $gradingRow['normal'] : "";
			    $data['normal_within_parentheses']= (isset($gradingRow['normal_within_parentheses']) and $gradingRow['normal_within_parentheses'] != "") ? $gradingRow['normal_within_parentheses'] : "";
			    $data['normal_end_with_t']= (isset($gradingRow['normal_end_with_t']) and $gradingRow['normal_end_with_t'] != "") ? $gradingRow['normal_end_with_t'] : "";
			    $data['normal_end_with_tt']= (isset($gradingRow['normal_end_with_tt']) and $gradingRow['normal_end_with_tt'] != "")? $gradingRow['normal_end_with_tt'] : "";
			    $data['normal_begin_with_degree']= (isset($gradingRow['normal_begin_with_degree']) and $gradingRow['normal_begin_with_degree'] != "")? $gradingRow['normal_begin_with_degree'] : "";
			    $data['normal_end_with_degree']= (isset($gradingRow['normal_end_with_degree']) and $gradingRow['normal_end_with_degree'] != "")? $gradingRow['normal_end_with_degree'] : "";
			    $data['normal_begin_with_asterisk']= (isset($gradingRow['normal_begin_with_asterisk']) and $gradingRow['normal_begin_with_asterisk'] != "")? $gradingRow['normal_begin_with_asterisk'] : "";
			    $data['normal_begin_with_asterisk_end_with_t']= (isset($gradingRow['normal_begin_with_asterisk_end_with_t']) and $gradingRow['normal_begin_with_asterisk_end_with_t'] != "") ? $gradingRow['normal_begin_with_asterisk_end_with_t'] : ""; 
			    $data['normal_begin_with_asterisk_end_with_tt']= (isset($gradingRow['normal_begin_with_asterisk_end_with_tt']) and $gradingRow['normal_begin_with_asterisk_end_with_tt'] !="" ) ? $gradingRow['normal_begin_with_asterisk_end_with_tt'] : "";
			    $data['normal_begin_with_asterisk_end_with_degree']= (isset($gradingRow['normal_begin_with_asterisk_end_with_degree']) and $gradingRow['normal_begin_with_asterisk_end_with_degree'] !="" ) ? $gradingRow['normal_begin_with_asterisk_end_with_degree'] : "";
			    $data['sperrschrift']= (isset($gradingRow['sperrschrift']) and $gradingRow['sperrschrift'] !="" ) ? $gradingRow['sperrschrift'] : "";
			    $data['sperrschrift_begin_with_degree']= (isset($gradingRow['sperrschrift_begin_with_degree']) and $gradingRow['sperrschrift_begin_with_degree'] !="" ) ? $gradingRow['sperrschrift_begin_with_degree'] : "";
			    $data['sperrschrift_begin_with_asterisk']= (isset($gradingRow['sperrschrift_begin_with_asterisk']) and $gradingRow['sperrschrift_begin_with_asterisk'] !="" ) ? $gradingRow['sperrschrift_begin_with_asterisk'] : "";
			    $data['sperrschrift_bold']= (isset($gradingRow['sperrschrift_bold']) and $gradingRow['sperrschrift_bold'] !="" ) ? $gradingRow['sperrschrift_bold'] : "";
			    $data['sperrschrift_bold_begin_with_degree']= (isset($gradingRow['sperrschrift_bold_begin_with_degree']) and $gradingRow['sperrschrift_bold_begin_with_degree'] !="" ) ? $gradingRow['sperrschrift_bold_begin_with_degree'] : "";
			    $data['sperrschrift_bold_begin_with_asterisk']= (isset($gradingRow['sperrschrift_bold_begin_with_asterisk']) and $gradingRow['sperrschrift_bold_begin_with_asterisk'] !="" ) ? $gradingRow['sperrschrift_bold_begin_with_asterisk'] : "";
			    $data['kursiv']= (isset($gradingRow['kursiv']) and $gradingRow['kursiv'] !="" ) ? $gradingRow['kursiv'] : "";
			    $data['kursiv_end_with_t']= (isset($gradingRow['kursiv_end_with_t']) and $gradingRow['kursiv_end_with_t'] !="" ) ? $gradingRow['kursiv_end_with_t'] : "";
			    $data['kursiv_end_with_tt']= (isset($gradingRow['kursiv_end_with_tt']) and $gradingRow['kursiv_end_with_tt'] !="" ) ? $gradingRow['kursiv_end_with_tt'] : "";
			    $data['kursiv_begin_with_degree']= (isset($gradingRow['kursiv_begin_with_degree']) and $gradingRow['kursiv_begin_with_degree'] !="" ) ? $gradingRow['kursiv_begin_with_degree'] : "";
			    $data['kursiv_end_with_degree']= (isset($gradingRow['kursiv_end_with_degree']) and $gradingRow['kursiv_end_with_degree'] !="" ) ? $gradingRow['kursiv_end_with_degree'] : "";
			    $data['kursiv_begin_with_asterisk']= (isset($gradingRow['kursiv_begin_with_asterisk']) and $gradingRow['kursiv_begin_with_asterisk'] !="" ) ? $gradingRow['kursiv_begin_with_asterisk'] : "";
			    $data['kursiv_begin_with_asterisk_end_with_t']= (isset($gradingRow['kursiv_begin_with_asterisk_end_with_t']) and $gradingRow['kursiv_begin_with_asterisk_end_with_t'] !="" ) ? $gradingRow['kursiv_begin_with_asterisk_end_with_t'] : "";
			    $data['kursiv_begin_with_asterisk_end_with_tt']= (isset($gradingRow['kursiv_begin_with_asterisk_end_with_tt']) and $gradingRow['kursiv_begin_with_asterisk_end_with_tt'] !="" ) ? $gradingRow['kursiv_begin_with_asterisk_end_with_tt'] : "";
			    $data['kursiv_begin_with_asterisk_end_with_degree']= (isset($gradingRow['kursiv_begin_with_asterisk_end_with_degree']) and $gradingRow['kursiv_begin_with_asterisk_end_with_degree'] !="" ) ? $gradingRow['kursiv_begin_with_asterisk_end_with_degree'] : "";
			    $data['kursiv_bold']= (isset($gradingRow['kursiv_bold']) and $gradingRow['kursiv_bold'] !="" ) ? $gradingRow['kursiv_bold'] : "";
			    $data['kursiv_bold_begin_with_asterisk_end_with_t']= (isset($gradingRow['kursiv_bold_begin_with_asterisk_end_with_t']) and $gradingRow['kursiv_bold_begin_with_asterisk_end_with_t'] !="" ) ? $gradingRow['kursiv_bold_begin_with_asterisk_end_with_t'] : "";
			    $data['kursiv_bold_begin_with_asterisk_end_with_tt']= (isset($gradingRow['kursiv_bold_begin_with_asterisk_end_with_tt']) and $gradingRow['kursiv_bold_begin_with_asterisk_end_with_tt'] !="" ) ? $gradingRow['kursiv_bold_begin_with_asterisk_end_with_tt'] : "";
			    $data['kursiv_bold_begin_with_degree']= (isset($gradingRow['kursiv_bold_begin_with_degree']) and $gradingRow['kursiv_bold_begin_with_degree'] !="" ) ? $gradingRow['kursiv_bold_begin_with_degree'] : "";
			    $data['kursiv_bold_begin_with_asterisk']= (isset($gradingRow['kursiv_bold_begin_with_asterisk']) and $gradingRow['kursiv_bold_begin_with_asterisk'] !="" ) ? $gradingRow['kursiv_bold_begin_with_asterisk'] : "";
			    $data['kursiv_bold_begin_with_asterisk_end_with_degree']= (isset($gradingRow['kursiv_bold_begin_with_asterisk_end_with_degree']) and $gradingRow['kursiv_bold_begin_with_asterisk_end_with_degree'] !="" ) ? $gradingRow['kursiv_bold_begin_with_asterisk_end_with_degree'] : "";
			    $data['fett']= (isset($gradingRow['fett']) and $gradingRow['fett'] !="" ) ? $gradingRow['fett'] : "";
			    $data['fett_end_with_t']= (isset($gradingRow['fett_end_with_t']) and $gradingRow['fett_end_with_t'] !="" ) ? $gradingRow['fett_end_with_t'] : "";
			    $data['fett_end_with_tt']= (isset($gradingRow['fett_end_with_tt']) and $gradingRow['fett_end_with_tt'] !="" ) ? $gradingRow['fett_end_with_tt'] : "";
			    $data['fett_begin_with_degree']= (isset($gradingRow['fett_begin_with_degree']) and $gradingRow['fett_begin_with_degree'] !="" ) ? $gradingRow['fett_begin_with_degree'] : "";
			    $data['fett_end_with_degree']= (isset($gradingRow['fett_end_with_degree']) and $gradingRow['fett_end_with_degree'] !="" ) ? $gradingRow['fett_end_with_degree'] : "";
			    $data['fett_begin_with_asterisk']= (isset($gradingRow['fett_begin_with_asterisk']) and $gradingRow['fett_begin_with_asterisk'] !="" ) ? $gradingRow['fett_begin_with_asterisk'] : "";
			    $data['fett_begin_with_asterisk_end_with_t']= (isset($gradingRow['fett_begin_with_asterisk_end_with_t']) and $gradingRow['fett_begin_with_asterisk_end_with_t'] !="" ) ? $gradingRow['fett_begin_with_asterisk_end_with_t'] : "";
			    $data['fett_begin_with_asterisk_end_with_tt']= (isset($gradingRow['fett_begin_with_asterisk_end_with_tt']) and $gradingRow['fett_begin_with_asterisk_end_with_tt'] !="" ) ? $gradingRow['fett_begin_with_asterisk_end_with_tt'] : "";
			    $data['fett_begin_with_asterisk_end_with_degree']= (isset($gradingRow['fett_begin_with_asterisk_end_with_degree']) and $gradingRow['fett_begin_with_asterisk_end_with_degree'] !="" ) ? $gradingRow['fett_begin_with_asterisk_end_with_degree'] : "";
			    $data['gross']= (isset($gradingRow['gross']) and $gradingRow['gross'] !="" ) ? $gradingRow['gross'] : "";
			    $data['gross_begin_with_degree']= (isset($gradingRow['gross_begin_with_degree']) and $gradingRow['gross_begin_with_degree'] !="" ) ? $gradingRow['gross_begin_with_degree'] : "";
			    $data['gross_begin_with_asterisk']= (isset($gradingRow['gross_begin_with_asterisk']) and $gradingRow['gross_begin_with_asterisk'] !="" ) ? $gradingRow['gross_begin_with_asterisk'] : "";
			    $data['gross_bold']= (isset($gradingRow['gross_bold']) and $gradingRow['gross_bold'] !="" ) ? $gradingRow['gross_bold'] : "";
			    $data['gross_bold_begin_with_degree']= (isset($gradingRow['gross_bold_begin_with_degree']) and $gradingRow['gross_bold_begin_with_degree'] !="" ) ? $gradingRow['gross_bold_begin_with_degree'] : "";
			    $data['gross_bold_begin_with_asterisk']= (isset($gradingRow['gross_bold_begin_with_asterisk']) and $gradingRow['gross_bold_begin_with_asterisk'] !="" ) ? $gradingRow['gross_bold_begin_with_asterisk'] : "";


			    $resultData = $data;
				$status = 'success';
				$message = "Success";
			}
			else
			{
				$status = 'error';
				$message = 'Data not found';
			}
		} else {
			$status = 'error';
			$message = 'Data not found';
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