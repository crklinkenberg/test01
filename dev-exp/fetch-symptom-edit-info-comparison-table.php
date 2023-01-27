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
		$initial_id = (isset($_POST['initial_id']) AND $_POST['initial_id'] != "") ? $_POST['initial_id'] : "";
		$source_id = (isset($_POST['source_id']) AND $_POST['source_id'] != "") ? $_POST['source_id'] : "";
		$comparison_table = (isset($_POST['comparison_table']) AND $_POST['comparison_table'] != "") ? $_POST['comparison_table'] : "";
		$is_connected_symptom = (isset($_POST['is_connected_symptom']) AND $_POST['is_connected_symptom'] != "") ? $_POST['is_connected_symptom'] : "";
		$connection_id = (isset($_POST['connection_id']) AND $_POST['connection_id'] != "") ? $_POST['connection_id'] : "";
		// $comparison_table="quelle_import_test";
		if($source_id != "" AND $symptom_id != "" AND $comparison_table != "" AND $initial_id != ""){
			if($symptom_id != $initial_id)
				$symptomResult = mysqli_query($db, "SELECT * FROM $comparison_table WHERE symptom_id = '".$symptom_id."' AND initial_symptom_id = '".$initial_id."' LIMIT 1");
			else
				$symptomResult = mysqli_query($db, "SELECT * FROM $comparison_table WHERE symptom_id = '".$symptom_id."' LIMIT 1");
			if(mysqli_num_rows($symptomResult) > 0){
				$row = mysqli_fetch_assoc($symptomResult);
				$data = array();
				$data['id'] = $row['id'];
				$data['Beschreibung_de'] = $row['Beschreibung_de'];
				$data['Beschreibung_en'] = $row['Beschreibung_en'];
				// $data['BeschreibungOriginal_de'] = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
				// $data['BeschreibungOriginal_en'] = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : "";
				if($row['swap_ce'] !=0){
					$data['BeschreibungOriginal_de'] =  ($row['swap_value_ce_de'] != "") ? convertSymptomToOriginal($row['swap_value_ce_de'], $row['original_quelle_id'], $row['arznei_id']) : "";
					$data['BeschreibungOriginal_en'] =  ($row['swap_value_ce_en'] != "") ? convertSymptomToOriginal($row['swap_value_ce_en'], $row['original_quelle_id'], $row['arznei_id']) : "";
				}else{
					if($row['swap'] != 0){
						$data['BeschreibungOriginal_de'] =  ($row['swap_value_de'] != "") ? convertSymptomToOriginal($row['swap_value_de'], $row['original_quelle_id'], $row['arznei_id']) : "";
						$data['BeschreibungOriginal_en'] =  ($row['swap_value_en'] != "") ? convertSymptomToOriginal($row['swap_value_en'], $row['original_quelle_id'], $row['arznei_id']) : "";
					}else{
						if($row['is_final_version_available'] != 0){
							$data['BeschreibungOriginal_de'] =  ($row['final_version_de'] != "") ? convertSymptomToOriginal($row['final_version_de'], $row['original_quelle_id'], $row['arznei_id']) : "";
							$data['BeschreibungOriginal_en'] =  ($row['final_version_en'] != "") ? convertSymptomToOriginal($row['final_version_en'], $row['original_quelle_id'], $row['arznei_id']) : "";
						}else{
							$data['BeschreibungOriginal_de'] = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
							$data['BeschreibungOriginal_en'] = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : "";
						}
					}
				}
				// If it is a connected symptom
				if($is_connected_symptom == 1 AND $connection_id != ""){
					$connectedSymptomResult = mysqli_query($db, "SELECT * FROM ".$comparison_table."_connections WHERE id = '".$connection_id."' LIMIT 1");
					if(mysqli_num_rows($connectedSymptomResult) > 0){
						$conRow = mysqli_fetch_assoc($connectedSymptomResult);
						if($conRow['initial_symptom_id'] == $symptom_id) {
							$data['Beschreibung_de'] = $conRow['initial_symptom_de'];
							$data['Beschreibung_en'] = $conRow['initial_symptom_en'];
							$data['BeschreibungOriginal_de'] = str_replace ( array (
									'<cite style="background: #f7c77b;">',
									'</cite>' 
								), array (
									"",
									"" 
								), $conRow['highlighted_initial_symptom_de'] );
							$data['BeschreibungOriginal_en'] = str_replace ( array (
									'<cite style="background: #f7c77b;">',
									'</cite>' 
								), array (
									"",
									"" 
								), $conRow['highlighted_initial_symptom_en'] );
						} else if($conRow['comparing_symptom_id'] == $symptom_id) {
							$data['Beschreibung_de'] = $conRow['comparing_symptom_de'];
							$data['Beschreibung_en'] = $conRow['comparing_symptom_en'];
							$data['BeschreibungOriginal_de'] = str_replace ( array (
									'<cite style="background: #f7c77b;">',
									'</cite>' 
								), array (
									"",
									"" 
								), $conRow['highlighted_comparing_symptom_de'] );
							$data['BeschreibungOriginal_en'] = str_replace ( array (
									'<cite style="background: #f7c77b;">',
									'</cite>' 
								), array (
									"",
									"" 
								), $conRow['highlighted_comparing_symptom_en'] );
						}
					}
				}

				// collecting symptom type info
				$symptomType = "";
				$querySympTypeInfo = mysqli_query($db,"SELECT symptom_type_for_whole FROM quelle_symptom_settings WHERE quelle_id =".$row['original_quelle_id']);
				if(mysqli_num_rows($querySympTypeInfo) > 0){
					$rowSympTypeInfo = mysqli_fetch_assoc($querySympTypeInfo);
					$symptomType = (isset($rowSympTypeInfo['symptom_type_for_whole']) AND $rowSympTypeInfo['symptom_type_for_whole'] != "") ? $rowSympTypeInfo['symptom_type_for_whole'] : "";
				}

				$symptomTypeResult = mysqli_query($db, "SELECT * FROM symptom_type_setting WHERE symptom_id = '".$symptom_id."'");
				if(mysqli_num_rows($symptomTypeResult) > 0){
					$symptomTypeRow = mysqli_fetch_assoc($symptomTypeResult);
				}
				$data['symptom_type']= (isset($symptomTypeRow['symptom_type']) and $symptomTypeRow['symptom_type'] != "")? $symptomTypeRow['symptom_type'] : $symptomType;	

				$symptomGradingResult = mysqli_query($db, "SELECT * FROM quelle_grading_settings WHERE quelle_id = '".$source_id."'");
				if(mysqli_num_rows($symptomGradingResult) > 0){
					$gradingRow = mysqli_fetch_assoc($symptomGradingResult);
				}
				// If this symptom has it own specific garading settings then that will be applied
				// Fetching original_symptom_id 
				
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
			    $data['pi_sign']= (isset($gradingRow['pi_sign']) and $gradingRow['pi_sign'] !="" ) ? $gradingRow['pi_sign'] : "";
			    $data['one_bar']= (isset($gradingRow['one_bar']) and $gradingRow['one_bar'] !="" ) ? $gradingRow['one_bar'] : "";
			    $data['two_bar']= (isset($gradingRow['two_bar']) and $gradingRow['two_bar'] !="" ) ? $gradingRow['two_bar'] : "";
			    $data['three_bar']= (isset($gradingRow['three_bar']) and $gradingRow['three_bar'] !="" ) ? $gradingRow['three_bar'] : "";
			    $data['three_and_half_bar']= (isset($gradingRow['three_and_half_bar']) and $gradingRow['three_and_half_bar'] !="" ) ? $gradingRow['three_and_half_bar'] : "";
			    $data['four_bar']= (isset($gradingRow['four_bar']) and $gradingRow['four_bar'] !="" ) ? $gradingRow['four_bar'] : "";
			    $data['four_and_half_bar']= (isset($gradingRow['four_and_half_bar']) and $gradingRow['four_and_half_bar'] !="" ) ? $gradingRow['four_and_half_bar'] : "";
			    $data['five_bar']= (isset($gradingRow['five_bar']) and $gradingRow['five_bar'] !="" ) ? $gradingRow['five_bar'] : "";


			    $resultData = $data;
				$status = 'success';
				$message = "SELECT symptom_type_for_whole FROM quelle_symptom_settings WHERE quelle_id =".$row['original_quelle_id'];
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