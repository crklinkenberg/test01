<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching sources/quelle of a particular arznei 
	*/
?>
<?php
	$finalReturnData = '<select class="form-control save-data" name="initial_source" id="initial_source"><option value="">Select</option></select><span class="error-text"></span>(#$$#)<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)"><option value="">Select</option></select><span class="error-text"></span>';
	if(isset($_POST['arznei_id']) AND $_POST['arznei_id'] != ""){

		$savedInitialSourceId = (isset($_POST['saved_initial_source_id']) AND $_POST['saved_initial_source_id'] != "") ? $_POST['saved_initial_source_id'] : "";
		$savedComparingSourceIdsArray = (isset($_POST['saved_comparing_source_ids']) AND $_POST['saved_comparing_source_ids'] != "") ? explode(",", $_POST['saved_comparing_source_ids']) : array();
		$initialSourceSelectBox = '<select class="form-control save-data" name="initial_source" id="initial_source">';
		$initialSourceSelectBox .= '<option value="">Select</option>';
		$initialSelectBoxComparisonSourcesOptionGroup = '<optgroup label="Comparisons">';
		$initialSelectBoxSingleSourcesOptionGroup = '<optgroup label="Single sources">';
		$initialSelectBoxComparisonSourcesOptions = '';
		$initialSelectBoxSingleSourcesOptions = '';

		$comparingSourceSelectBox = '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
		$comparingSourceSelectBox .= '<option value="">Select</option>';
		$comparingSelectBoxComparisonSourcesOptionGroup = '<optgroup label="Comparisons">';
		$comparingSelectBoxSingleSourcesOptionGroup = '<optgroup label="Single sources">';
		$comparingSelectBoxComparisonSourcesOptions = '';
		$comparingSelectBoxSingleSourcesOptions = '';

		$quelleArzneiResult = mysqli_query($db,"SELECT AQ.quelle_id, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, QIM.is_symptoms_available_in_de, QIM.is_symptoms_available_in_en FROM arznei_quelle as AQ LEFT JOIN quelle as Q ON AQ.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id LEFT JOIN quelle_import_master as QIM ON Q.quelle_id = QIM.quelle_id WHERE Q.is_materia_medica = 1 AND AQ.arznei_id = '".$_POST['arznei_id']."' AND QIM.arznei_id = '".$_POST['arznei_id']."' GROUP BY AQ.quelle_id ORDER BY Q.jahr ASC");
		while($quelleArzneiRow = mysqli_fetch_array($quelleArzneiResult)){

			$quellen_value = $quelleArzneiRow['code'];
			if(!empty($quelleArzneiRow['jahr'])) $quellen_value .= ', '.$quelleArzneiRow['jahr'];
			if($quelleArzneiRow['code'] != $quelleArzneiRow['titel'])
				if(!empty($quelleArzneiRow['titel'])) $quellen_value .= ', '.$quelleArzneiRow['titel'];
			if($quelleArzneiRow['quelle_type_id'] == 1){
				if(!empty($quelleArzneiRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleArzneiRow['bucher_autor_or_herausgeber'];
			}else if($quelleArzneiRow['quelle_type_id'] == 2){
				if(!empty($quelleArzneiRow['zeitschriften_autor_suchname']) ) 
					$zeitschriften_autor = $quelleArzneiRow['zeitschriften_autor_suchname']; 
				else 
					$zeitschriften_autor = $quelleArzneiRow['zeitschriften_autor_vorname'].' '.$quelleArzneiRow['zeitschriften_autor_nachname'];
				if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
			}

			if($quelleArzneiRow['quelle_type_id'] == 3)
				$preparedQuelleCode = $quelleArzneiRow['code'];
			else{
				if($quelleArzneiRow['jahr'] != "" AND $quelleArzneiRow['code'] != "")
					$rowQuelleCode = trim(str_replace(trim($quelleArzneiRow['jahr']), '', $quelleArzneiRow['code']));
				else
					$rowQuelleCode = trim($quelleArzneiRow['code']);
				$preparedQuelleCode = trim($rowQuelleCode." ".$quelleArzneiRow['jahr']);
			}
			

			$initialOptionSelected = ($savedInitialSourceId == $quelleArzneiRow['quelle_id']) ? 'selected' : '';
			$comparingOptionSelected = (in_array($quelleArzneiRow['quelle_id'], $savedComparingSourceIdsArray)) ? 'selected' : '';

			if($quelleArzneiRow['quelle_type_id'] == 3){
				$initialSelectBoxComparisonSourcesOptions .= '<option data-is-symptoms-available-in-de="'.$quelleArzneiRow['is_symptoms_available_in_de'].'" data-is-symptoms-available-in-en="'.$quelleArzneiRow['is_symptoms_available_in_en'].'" data-quelle-code="'.$preparedQuelleCode.'" data-year="'.$quelleArzneiRow['jahr'].'" '.$initialOptionSelected.' value="'.$quelleArzneiRow['quelle_id'].'">'.$quellen_value.'</option>';
				$comparingSelectBoxComparisonSourcesOptions .= '<option data-is-symptoms-available-in-de="'.$quelleArzneiRow['is_symptoms_available_in_de'].'" data-is-symptoms-available-in-en="'.$quelleArzneiRow['is_symptoms_available_in_en'].'" data-quelle-code="'.$preparedQuelleCode.'" data-year="'.$quelleArzneiRow['jahr'].'" '.$comparingOptionSelected.' value="'.$quelleArzneiRow['quelle_id'].'">'.$quellen_value.'</option>';
			}else{
				$initialSelectBoxSingleSourcesOptions .= '<option data-is-symptoms-available-in-de="'.$quelleArzneiRow['is_symptoms_available_in_de'].'" data-is-symptoms-available-in-en="'.$quelleArzneiRow['is_symptoms_available_in_en'].'" data-quelle-code="'.$preparedQuelleCode.'" data-year="'.$quelleArzneiRow['jahr'].'" '.$initialOptionSelected.' value="'.$quelleArzneiRow['quelle_id'].'">'.$quellen_value.'</option>';
				$comparingSelectBoxSingleSourcesOptions .= '<option data-is-symptoms-available-in-de="'.$quelleArzneiRow['is_symptoms_available_in_de'].'" data-is-symptoms-available-in-en="'.$quelleArzneiRow['is_symptoms_available_in_en'].'" data-quelle-code="'.$preparedQuelleCode.'" data-year="'.$quelleArzneiRow['jahr'].'" '.$comparingOptionSelected.' value="'.$quelleArzneiRow['quelle_id'].'">'.$quellen_value.'</option>';
			}
			
		}

		if($initialSelectBoxComparisonSourcesOptions == '')
			$initialSelectBoxComparisonSourcesOptionGroup .= '<option value="" disabled="disabled">None</option>';
		else
			$initialSelectBoxComparisonSourcesOptionGroup .= $initialSelectBoxComparisonSourcesOptions;
		if($initialSelectBoxSingleSourcesOptions == '')
			$initialSelectBoxSingleSourcesOptionGroup .= '<option value="" disabled="disabled">None</option>';
		else
			$initialSelectBoxSingleSourcesOptionGroup .= $initialSelectBoxSingleSourcesOptions;

		if($comparingSelectBoxComparisonSourcesOptions == '')
			$comparingSelectBoxComparisonSourcesOptionGroup .= '<option value="" disabled="disabled">None</option>';
		else
			$comparingSelectBoxComparisonSourcesOptionGroup .= $comparingSelectBoxComparisonSourcesOptions;
		if($comparingSelectBoxSingleSourcesOptions == '')
			$comparingSelectBoxSingleSourcesOptionGroup .= '<option value="" disabled="disabled">None</option>';
		else
			$comparingSelectBoxSingleSourcesOptionGroup .= $comparingSelectBoxSingleSourcesOptions;

		$initialSelectBoxComparisonSourcesOptionGroup .= '</optgroup>';
		$initialSelectBoxSingleSourcesOptionGroup .= '</optgroup>';
		$initialSourceSelectBox .= $initialSelectBoxComparisonSourcesOptionGroup.$initialSelectBoxSingleSourcesOptionGroup;
		$initialSourceSelectBox .= '</select>';
		$initialSourceSelectBox .= '<span class="error-text"></span>';


		$comparingSelectBoxComparisonSourcesOptionGroup .= '</optgroup>';
		$comparingSelectBoxSingleSourcesOptionGroup .= '</optgroup>';
		$comparingSourceSelectBox .= $comparingSelectBoxComparisonSourcesOptionGroup.$comparingSelectBoxSingleSourcesOptionGroup;
		$comparingSourceSelectBox .= '</select>';
		$comparingSourceSelectBox .= '<span class="error-text"></span>';

		$finalReturnData = $initialSourceSelectBox."(#$$#)".$comparingSourceSelectBox;
	}

	echo $finalReturnData; 
	exit;
?>