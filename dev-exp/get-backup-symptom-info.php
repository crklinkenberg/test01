<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom (This is used in backup section) 
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if((isset($_POST['symptom_id']) AND !empty($_POST['symptom_id'])) AND (isset($_POST['source_type']) AND !empty($_POST['source_type']))){

			$comparison_initial_source_id = (isset($_POST['comparison_initial_source_id']) AND $_POST['comparison_initial_source_id'] != "") ? $_POST['comparison_initial_source_id'] : null;
			$comparison_comparing_source_ids = (isset($_POST['comparison_comparing_source_ids']) AND $_POST['comparison_comparing_source_ids'] != "") ? $_POST['comparison_comparing_source_ids'] : null;
			$arznei_id = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? $_POST['arznei_id'] : null;
			$comparison_option = (isset($_POST['comparison_option']) AND $_POST['comparison_option'] != "") ? $_POST['comparison_option'] : null;
			$saved_comparisons_backup_id = (isset($_POST['saved_comparisons_backup_id']) AND $_POST['saved_comparisons_backup_id'] != "") ? $_POST['saved_comparisons_backup_id'] : null;
			$individual_comparison_language = (isset($_POST['individual_comparison_language']) AND $_POST['individual_comparison_language'] != "") ? trim($_POST['individual_comparison_language']) : "";

			$symptomId = trim($_POST['symptom_id']);
			$source_type = trim($_POST['source_type']);
			if($source_type == "backup"){
				$quelle_table = "quelle_backup";
				$symptom_pruefer_table = "symptom_pruefer_backup";
				$quelle_import_table = "quelle_import_backup";
			} else {
				$quelle_table = "quelle";
				$symptom_pruefer_table = "symptom_pruefer";
				$quelle_import_table = "quelle_import_test";
			}

			$queryRes = mysqli_query($db,"SELECT QI.id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.original_symptom_id, QI.quelle_id, QI.quelle_code, QI.original_quelle_id, QI.arznei_id, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM ".$quelle_import_table." as QI LEFT JOIN ".$quelle_table." as Q ON QI.quelle_id = Q.quelle_id WHERE QI.id = '".$symptomId."'");
			if(mysqli_num_rows($queryRes) > 0){
				$row = mysqli_fetch_assoc($queryRes);

				if($quelle_import_table == "quelle_import_test"){
					$FVInfoQuery = mysqli_query($db,"SELECT final_version_de, final_version_en, is_final_version_available FROM final_version_symptoms_info_for_backups WHERE symptom_id = '".$row['id']."' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
					if(mysqli_num_rows($FVInfoQuery) > 0){
						$FVInfoRow = mysqli_fetch_assoc($FVInfoQuery);
						$row['is_final_version_available'] = $FVInfoRow['is_final_version_available'];
						$row['final_version_de'] = $FVInfoRow['final_version_de'];
						$row['final_version_en'] = $FVInfoRow['final_version_en'];
					} else {
						$row['is_final_version_available'] = 0;
						$row['final_version_de'] = "";
						$row['final_version_en'] = "";
					}
				}

				$pruStr = "";
				$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM ".$symptom_pruefer_table." JOIN pruefer ON ".$symptom_pruefer_table.".pruefer_id	= pruefer.pruefer_id WHERE ".$symptom_pruefer_table.".symptom_id = '".$row['id']."'");
				while($prueferRow = mysqli_fetch_array($prueferResult)){
					if($prueferRow['suchname'] != "")
						$pruStr .= $prueferRow['suchname'].", ";
					else
						$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
				}
				$pruStr =rtrim($pruStr, ", ");

				$symptomNumber = $row['Symptomnummer'];
				$symptomPageData = ($row['SeiteOriginalVon'] == $row['SeiteOriginalBis']) ? $row['SeiteOriginalVon'] : $row['SeiteOriginalVon']."-".$row['SeiteOriginalBis'];


				// Apply dynamic conversion
				// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
				// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
				// convertTheSymptom()
				$BeschreibungOriginal_de = ($row['BeschreibungOriginal_de'] != "") ? convertTheSymptom($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungOriginal_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";
				// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
				$searchableText_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";		
				$searchableText_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";		
				// $searchableText = base64_encode($searchableText);

				$BeschreibungFull_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungFull_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";


				// Getting connection informations if it is a Final version symptom
				$fv_con_initial_symptom_de = "";
				$fv_con_initial_symptom_en = "";
				$fv_con_initial_source_code = "";
				$fv_con_comparative_symptom_de = "";
				$fv_con_comparative_symptom_en = "";
				$fv_con_comparative_source_code = "";
				$fv_symptom_de = "";
				$fv_symptom_en = "";

				if($row['is_final_version_available'] != 0){
					$fv_symptom_de = ($row['final_version_de'] != "") ? convertTheSymptom($row['final_version_de'], $row['original_quelle_id'], $row['arznei_id'], $row['is_final_version_available'], 0, $row['id'], $row['original_symptom_id']) : "";
					$fv_symptom_en = ($row['final_version_en'] != "") ? convertTheSymptom($row['final_version_en'], $row['original_quelle_id'], $row['arznei_id'], $row['is_final_version_available'], 0, $row['id'], $row['original_symptom_id']) : "";
					// connection_or_paste_type [1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit]
					$connectedSymptomResult = mysqli_query($db,"SELECT initial_source_symptom_id, comparing_source_symptom_id, initial_source_type, comparing_source_type, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$symptomId."' OR comparing_source_symptom_id = '".$symptomId."') AND (connection_or_paste_type = 3 OR connection_or_paste_type = 4) LIMIT 1");
					if(mysqli_num_rows($connectedSymptomResult) > 0){
						$connInfoRow = mysqli_fetch_assoc($connectedSymptomResult);
						if($symptomId == $connInfoRow['initial_source_symptom_id']){
							if($connInfoRow['comparing_source_type'] == "backup")
								$symptom_tbl = "quelle_import_backup";
							else
								$symptom_tbl = "quelle_import_test";
							$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available FROM ".$symptom_tbl." as QI WHERE QI.id = '".$connInfoRow['comparing_source_symptom_id']."'");
							if(mysqli_num_rows($querySymptomRes) > 0)
								$fvCheckRow = mysqli_fetch_assoc($querySymptomRes);
							$symIsFinalVersionAvailable = (isset($fvCheckRow['is_final_version_available']) AND $fvCheckRow['is_final_version_available'] != "") ? $fvCheckRow['is_final_version_available'] : 0; 
							$symOriginalQuelleId = (isset($fvCheckRow['original_quelle_id']) AND $fvCheckRow['original_quelle_id'] != "") ? $fvCheckRow['original_quelle_id'] : ""; 
							$symArzneiId = (isset($fvCheckRow['arznei_id']) AND $fvCheckRow['arznei_id'] != "") ? $fvCheckRow['arznei_id'] : ""; 
							$symId = (isset($fvCheckRow['id']) AND $fvCheckRow['id'] != "") ? $fvCheckRow['id'] : ""; 
							$originalSymId = (isset($fvCheckRow['original_symptom_id']) AND $fvCheckRow['original_symptom_id'] != "") ? $fvCheckRow['original_symptom_id'] : ""; 

							$fv_con_initial_symptom_de = $searchableText_de;	
							$fv_con_initial_symptom_en = $searchableText_en;
							$fv_con_comparative_symptom_de = ($connInfoRow['comparing_source_symptom_de'] != "" AND $symOriginalQuelleId != "" AND $symArzneiId != "") ? convertTheSymptom($connInfoRow['comparing_source_symptom_de'], $symOriginalQuelleId, $symArzneiId, $symIsFinalVersionAvailable, 0, $symId, $originalSymId) : "";	
							$fv_con_comparative_symptom_en = ($connInfoRow['comparing_source_symptom_en'] != "" AND $symOriginalQuelleId != "" AND $symArzneiId != "") ? convertTheSymptom($connInfoRow['comparing_source_symptom_en'], $symOriginalQuelleId, $symArzneiId, $symIsFinalVersionAvailable, 0, $symId, $originalSymId) : "";

							$fv_con_initial_source_code = $row['quelle_code'];
							$fv_con_comparative_source_code = (isset($fvCheckRow['quelle_code']) AND $fvCheckRow['quelle_code'] != "") ? $fvCheckRow['quelle_code'] : "";
						} else {
							if($connInfoRow['initial_source_type'] == "backup")
								$symptom_tbl = "quelle_import_backup";
							else
								$symptom_tbl = "quelle_import_test";
							$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available FROM ".$symptom_tbl." as QI WHERE QI.id = '".$connInfoRow['initial_source_symptom_id']."'");
							if(mysqli_num_rows($querySymptomRes) > 0)
								$fvCheckRow = mysqli_fetch_assoc($querySymptomRes);
							$symIsFinalVersionAvailable = (isset($fvCheckRow['is_final_version_available']) AND $fvCheckRow['is_final_version_available'] != "") ? $fvCheckRow['is_final_version_available'] : 0; 
							$symOriginalQuelleId = (isset($fvCheckRow['original_quelle_id']) AND $fvCheckRow['original_quelle_id'] != "") ? $fvCheckRow['original_quelle_id'] : ""; 
							$symArzneiId = (isset($fvCheckRow['arznei_id']) AND $fvCheckRow['arznei_id'] != "") ? $fvCheckRow['arznei_id'] : "";
							$symId = (isset($fvCheckRow['id']) AND $fvCheckRow['id'] != "") ? $fvCheckRow['id'] : ""; 
							$originalSymId = (isset($fvCheckRow['original_symptom_id']) AND $fvCheckRow['original_symptom_id'] != "") ? $fvCheckRow['original_symptom_id'] : "";

							$fv_con_initial_symptom_de = ($connInfoRow['initial_source_symptom_de'] != "" AND $symOriginalQuelleId != "" AND $symArzneiId != "") ? convertTheSymptom($connInfoRow['initial_source_symptom_de'], $symOriginalQuelleId, $symArzneiId, $symIsFinalVersionAvailable, 0, $symId, $originalSymId) : "";	
							$fv_con_initial_symptom_en = ($connInfoRow['initial_source_symptom_en'] != "" AND $symOriginalQuelleId != "" AND $symArzneiId != "") ? convertTheSymptom($connInfoRow['initial_source_symptom_en'], $symOriginalQuelleId, $symArzneiId, $symIsFinalVersionAvailable, 0, $symId, $originalSymId) : "";
							$fv_con_comparative_symptom_de = $searchableText_de;	
							$fv_con_comparative_symptom_en = $searchableText_en;

							$fv_con_initial_source_code = (isset($fvCheckRow['quelle_code']) AND $fvCheckRow['quelle_code'] != "") ? $fvCheckRow['quelle_code'] : "";
							$fv_con_comparative_source_code = $row['quelle_code'];
						}
					} else {
						// If this final version is not for or not applicable for this backup than making "is_final_version_available" value "0"
						$row['is_final_version_available'] = 0;
					}

				}

				// Converted symptom with grading number
				// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
				// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
				// convertTheSymptom()
				$BeschreibungOriginal_with_grading_de = ($row['BeschreibungOriginal_de'] != "") ? convertTheSymptom($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungOriginal_with_grading_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

				$searchable_text_with_grading_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$searchable_text_with_grading_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

				// Converting the symptom to the original format(means the book format as it was found in the book)
				$BeschreibungOriginal_book_format_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : "";
				$BeschreibungOriginal_book_format_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : "";

				$BeschreibungFull_with_grading_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungFull_with_grading_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

				$setQuelleId = $row['quelle_id'];
				$setQuelleCode = $row['quelle_code'];

				$setSymptomNumber = $symptomNumber;
				$setSymptomPageData = $symptomPageData;

				$setBeschreibung_de = $row['Beschreibung_de'];
				$setBeschreibung_en = $row['Beschreibung_en'];
				$setBeschreibungOriginal_book_format_de = $BeschreibungOriginal_book_format_de;
				$setBeschreibungOriginal_book_format_en = $BeschreibungOriginal_book_format_en;
				$setBeschreibungOriginal_de = $BeschreibungOriginal_de;
				$setBeschreibungOriginal_en = $BeschreibungOriginal_en;
				$setBeschreibungOriginal_with_grading_de = $BeschreibungOriginal_with_grading_de;
				$setBeschreibungOriginal_with_grading_en = $BeschreibungOriginal_with_grading_en;
				$setSearchable_text_with_grading_de = $searchable_text_with_grading_de;
				$setSearchable_text_with_grading_en = $searchable_text_with_grading_en;
				$setSearchableText_de = $searchableText_de;
				$setSearchableText_en = $searchableText_en;
				$setBeschreibungFull_de = $BeschreibungFull_de;
				$setBeschreibungFull_en = $BeschreibungFull_en;
				$setBeschreibungFull_with_grading_de = $BeschreibungFull_with_grading_de;
				$setBeschreibungFull_with_grading_en = $BeschreibungFull_with_grading_en;
				$setFussnote = $row['Fussnote'];
				$setEntnommenAus = $row['EntnommenAus'];
				$setVerweiss = $row['Verweiss'];
				$setKommentar = $row['Kommentar'];
				$setRemedy = $row['Remedy'];
				$setsymptomOfDifferentRemedy = $row['symptom_of_different_remedy'];
				$setBereichID = $row['BereichID'];
				$setUnklarheiten = $row['Unklarheiten'];

				$set_is_final_version_available = $row['is_final_version_available'];
				$set_fv_con_initial_symptom_de = $fv_con_initial_symptom_de;
				$set_fv_con_initial_symptom_en = $fv_con_initial_symptom_en;
				$set_fv_con_comparative_symptom_de = $fv_con_comparative_symptom_de;
				$set_fv_con_comparative_symptom_en = $fv_con_comparative_symptom_en;
				$set_fv_symptom_de = $fv_symptom_de;
				$set_fv_symptom_en = $fv_symptom_en;
				$set_fv_con_initial_source_code = $fv_con_initial_source_code;
				$set_fv_con_comparative_source_code = $fv_con_comparative_source_code;

				$setTitel = $row['titel'];
				$setCode = $row['code'];
				$setJahr = $row['jahr'];
				$setBand = $row['band'];
				$setAuflage = $row['auflage'];
				$setAutorOrHerausgeber = $row['autor_or_herausgeber'];
				$setComparisonLanguage = $individual_comparison_language;
				
				// Checking for swapped data
				if($source_type != "backup"){

					// 
					// If this symptom has it's details in backup_connected_symptoms_details table associated with it's saved_comparisons_backup_id than picking it's information from there.
					$backupConnectedSymptomQuery = $db->query("SELECT B.*, B.id, B.original_symptom_id, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM backup_connected_symptoms_details AS B LEFT JOIN quelle as Q ON B.quelle_id = Q.quelle_id WHERE B.saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND B.original_symptom_id = '".$row['id']."'");
	            	if($backupConnectedSymptomQuery->num_rows > 0){
	            		$rowData = mysqli_fetch_assoc($backupConnectedSymptomQuery);

	            		if($rowData['is_final_version_available'] != 0){
							$fv_symptom_de = ($rowData['final_version_de'] != "") ? convertTheSymptom($rowData['final_version_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $rowData['id'], $rowData['original_symptom_id']) : "";
							$fv_symptom_en = ($rowData['final_version_en'] != "") ? convertTheSymptom($rowData['final_version_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], $rowData['is_final_version_available'], 0, $rowData['id'], $rowData['original_symptom_id']) : "";
						}

						$symptomNumber = $rowData['Symptomnummer'];
						$symptomPageData = ($rowData['SeiteOriginalVon'] == $rowData['SeiteOriginalBis']) ? $rowData['SeiteOriginalVon'] : $rowData['SeiteOriginalVon']."-".$rowData['SeiteOriginalBis'];

	            		// Apply dynamic conversion
	            		// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
						// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
						// convertTheSymptom()
						$BeschreibungOriginal_de = ($rowData['BeschreibungOriginal_de'] != "") ? convertTheSymptom($rowData['BeschreibungOriginal_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";
						$BeschreibungOriginal_en = ($rowData['BeschreibungOriginal_en'] != "") ? convertTheSymptom($rowData['BeschreibungOriginal_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";
						// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
						$searchableText_de = ($rowData['searchable_text_de'] != "") ? convertTheSymptom($rowData['searchable_text_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";		
						$searchableText_en = ($rowData['searchable_text_en'] != "") ? convertTheSymptom($rowData['searchable_text_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";		
						// $searchableText = base64_encode($searchableText);

						$BeschreibungFull_de = ($rowData['BeschreibungFull_de'] != "") ? convertTheSymptom($rowData['BeschreibungFull_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";
						$BeschreibungFull_en = ($rowData['BeschreibungFull_en'] != "") ? convertTheSymptom($rowData['BeschreibungFull_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 0, $rowData['id'], $rowData['original_symptom_id']) : "";

						// Converted symptom with grading number
						// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
						// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
						// convertTheSymptom()
						$BeschreibungOriginal_with_grading_de = ($rowData['BeschreibungOriginal_de'] != "") ? convertTheSymptom($rowData['BeschreibungOriginal_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";
						$BeschreibungOriginal_with_grading_en = ($rowData['BeschreibungOriginal_en'] != "") ? convertTheSymptom($rowData['BeschreibungOriginal_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";

						$searchable_text_with_grading_de = ($rowData['searchable_text_de'] != "") ? convertTheSymptom($rowData['searchable_text_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";
						$searchable_text_with_grading_en = ($rowData['searchable_text_en'] != "") ? convertTheSymptom($rowData['searchable_text_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";

						// Converting the symptom to the original format(means the book format as it was found in the book)
						$BeschreibungOriginal_book_format_de = ($rowData['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($rowData['BeschreibungOriginal_de'], $rowData['original_quelle_id'], $rowData['arznei_id']) : "";
						$BeschreibungOriginal_book_format_en = ($rowData['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($rowData['BeschreibungOriginal_en'], $rowData['original_quelle_id'], $rowData['arznei_id']) : "";

						$BeschreibungFull_with_grading_de = ($rowData['BeschreibungFull_de'] != "") ? convertTheSymptom($rowData['BeschreibungFull_de'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";
						$BeschreibungFull_with_grading_en = ($rowData['BeschreibungFull_en'] != "") ? convertTheSymptom($rowData['BeschreibungFull_en'], $rowData['original_quelle_id'], $rowData['arznei_id'], 0, 1, $rowData['id'], $rowData['original_symptom_id']) : "";

	            		$setQuelleId = $rowData['quelle_id'];
						$setQuelleCode = $rowData['quelle_code'];

						$setSymptomNumber = $symptomNumber;
						$setSymptomPageData = $symptomPageData;

						$setBeschreibung_de = $rowData['Beschreibung_de'];
						$setBeschreibung_en = $rowData['Beschreibung_en'];
						$setBeschreibungOriginal_book_format_de = $BeschreibungOriginal_book_format_de;
						$setBeschreibungOriginal_book_format_en = $BeschreibungOriginal_book_format_en;
						$setBeschreibungOriginal_de = $BeschreibungOriginal_de;
						$setBeschreibungOriginal_en = $BeschreibungOriginal_en;
						$setBeschreibungOriginal_with_grading_de = $BeschreibungOriginal_with_grading_de;
						$setBeschreibungOriginal_with_grading_en = $BeschreibungOriginal_with_grading_en;
						$setSearchable_text_with_grading_de= $searchable_text_with_grading_de;
						$setSearchable_text_with_grading_en = $searchable_text_with_grading_en;
						$setSearchableText_de = $searchableText_de;
						$setSearchableText_en = $searchableText_en;
						$setBeschreibungFull_de = $BeschreibungFull_de;
						$setBeschreibungFull_en = $BeschreibungFull_en;
						$setBeschreibungFull_with_grading_de = $BeschreibungFull_with_grading_de;
						$setBeschreibungFull_with_grading_en = $BeschreibungFull_with_grading_en;
						$setFussnote = $rowData['Fussnote'];
						$setEntnommenAus = $rowData['EntnommenAus'];
						$setVerweiss = $rowData['Verweiss'];
						$setKommentar = $rowData['Kommentar'];
						$setRemedy = $rowData['Remedy'];
						$setsymptomOfDifferentRemedy = $rowData['symptom_of_different_remedy'];
						$setBereichID = $rowData['BereichID'];
						$setUnklarheiten = $rowData['Unklarheiten'];

						$set_fv_symptom_de = $fv_symptom_de;
						$set_fv_symptom_en = $fv_symptom_en;

						$setTitel = $rowData['titel'];
						$setCode = $rowData['code'];
						$setJahr = $rowData['jahr'];
						$setBand = $rowData['band'];
						$setAuflage = $rowData['auflage'];
						$setAutorOrHerausgeber = $rowData['autor_or_herausgeber'];

						$pruStr = "";
						$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM backup_connected_symptoms_details_pruefer JOIN pruefer ON backup_connected_symptoms_details_pruefer.pruefer_id	= pruefer.pruefer_id WHERE backup_connected_symptoms_details_pruefer.symptom_id = '".$rowData['id']."'");
						while($prueferRow = mysqli_fetch_array($prueferResult)){
							if($prueferRow['suchname'] != "")
								$pruStr .= $prueferRow['suchname'].", ";
							else
								$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
						}
						$pruStr =rtrim($pruStr, ", ");
	            	}
	            	else
	            	{
	            		// 
				        // If this symptom is been swapped and it's infromation is not found in the above if section, then we have to pick this symptom's information from the quelle_import_backup table, as it was stored at the time of creation of this backup set
				        $swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$row['id']."' AND comparison_initial_source_id = '".$comparison_initial_source_id."' AND comparison_comparing_source_ids = '".$comparison_comparing_source_ids."' AND arznei_id = '".$arznei_id."'");
						if(mysqli_num_rows($swappedSymptomResult) > 0){
							$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
							// Here joining is made on backup table's quelle_id not with the original_quelle_id
							$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
							if(mysqli_num_rows($importMasterBackupResult) > 0){
								$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
								$masterBackupSetSymptomResult = mysqli_query($db,"SELECT QI.id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.original_symptom_id, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.BereichID, QI.Unklarheiten FROM quelle_import_backup as QI WHERE QI.master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND QI.original_symptom_id = '".$row['id']."'");
								if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
									$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

									$masterBackupSetQuelleInfoResult = mysqli_query($db,"SELECT Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM quelle as Q WHERE Q.quelle_id = '".$backupSetSymptomRow['original_quelle_id']."'");
									if(mysqli_num_rows($masterBackupSetQuelleInfoResult) > 0){
										$backupSetQuelleInfoRow = mysqli_fetch_assoc($masterBackupSetQuelleInfoResult);
									}

									if($backupSetSymptomRow['is_final_version_available'] != 0){
										$fv_symptom_de = ($backupSetSymptomRow['final_version_de'] != "") ? convertTheSymptom($backupSetSymptomRow['final_version_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
										$fv_symptom_en = ($backupSetSymptomRow['final_version_en'] != "") ? convertTheSymptom($backupSetSymptomRow['final_version_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], $backupSetSymptomRow['is_final_version_available'], 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									}

									$symptomNumber = $backupSetSymptomRow['Symptomnummer'];
									$symptomPageData = ($backupSetSymptomRow['SeiteOriginalVon'] == $backupSetSymptomRow['SeiteOriginalBis']) ? $backupSetSymptomRow['SeiteOriginalVon'] : $backupSetSymptomRow['SeiteOriginalVon']."-".$backupSetSymptomRow['SeiteOriginalBis'];

									// Apply dynamic conversion
									// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
									// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
									// convertTheSymptom()
									$BeschreibungOriginal_de = ($backupSetSymptomRow['BeschreibungOriginal_de'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									$BeschreibungOriginal_en = ($backupSetSymptomRow['BeschreibungOriginal_en'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
									$searchableText_de = ($backupSetSymptomRow['searchable_text_de'] != "") ? convertTheSymptom($backupSetSymptomRow['searchable_text_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";	
									$searchableText_en = ($backupSetSymptomRow['searchable_text_en'] != "") ? convertTheSymptom($backupSetSymptomRow['searchable_text_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";	
									// $searchableText = base64_encode($searchableText);

									$BeschreibungFull_de = ($backupSetSymptomRow['BeschreibungFull_de'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungFull_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									$BeschreibungFull_en = ($backupSetSymptomRow['BeschreibungFull_en'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungFull_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 0, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";

									// Converted symptom with grading number
									// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
									// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
									// convertTheSymptom()
									$BeschreibungOriginal_with_grading_de = ($backupSetSymptomRow['BeschreibungOriginal_de'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									$BeschreibungOriginal_with_grading_en = ($backupSetSymptomRow['BeschreibungOriginal_en'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";

									$searchable_text_with_grading_de = ($backupSetSymptomRow['searchable_text_de'] != "") ? convertTheSymptom($backupSetSymptomRow['searchable_text_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									$searchable_text_with_grading_en = ($backupSetSymptomRow['searchable_text_en'] != "") ? convertTheSymptom($backupSetSymptomRow['searchable_text_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									
									// Converting the symptom to the original format(means the book format as it was found in the book)
									$BeschreibungOriginal_book_format_de = ($backupSetSymptomRow['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($backupSetSymptomRow['BeschreibungOriginal_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']) : "";
									$BeschreibungOriginal_book_format_en = ($backupSetSymptomRow['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($backupSetSymptomRow['BeschreibungOriginal_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']) : "";

									$BeschreibungFull_with_grading_de = ($backupSetSymptomRow['BeschreibungFull_de'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungFull_de'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";
									$BeschreibungFull_with_grading_en = ($backupSetSymptomRow['BeschreibungFull_en'] != "") ? convertTheSymptom($backupSetSymptomRow['BeschreibungFull_en'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id'], 0, 1, $backupSetSymptomRow['id'], $backupSetSymptomRow['original_symptom_id']) : "";

									$setQuelleId = $backupSetSymptomRow['original_quelle_id'];
									$setQuelleCode = $backupSetSymptomRow['quelle_code'];

									$setSymptomNumber = $symptomNumber;
									$setSymptomPageData = $symptomPageData;

									$setBeschreibung_de = $backupSetSymptomRow['Beschreibung_de'];
									$setBeschreibung_en = $backupSetSymptomRow['Beschreibung_en'];
									$setBeschreibungOriginal_book_format_de = $BeschreibungOriginal_book_format_de;
									$setBeschreibungOriginal_book_format_en = $BeschreibungOriginal_book_format_en;
									$setBeschreibungOriginal_de = $BeschreibungOriginal_de;
									$setBeschreibungOriginal_en = $BeschreibungOriginal_en;
									$setBeschreibungOriginal_with_grading_de = $BeschreibungOriginal_with_grading_de;
									$setBeschreibungOriginal_with_grading_en = $BeschreibungOriginal_with_grading_en;
									$setSearchable_text_with_grading_de = $searchable_text_with_grading_de;
									$setSearchable_text_with_grading_en = $searchable_text_with_grading_en;
									$setSearchableText_de = $searchableText_de;
									$setSearchableText_en = $searchableText_en;
									$setBeschreibungFull_de = $BeschreibungFull_de;
									$setBeschreibungFull_en = $BeschreibungFull_en;
									$setBeschreibungFull_with_grading_de = $BeschreibungFull_with_grading_de;
									$setBeschreibungFull_with_grading_en = $BeschreibungFull_with_grading_en;
									$setEntnommenAus = $backupSetSymptomRow['EntnommenAus'];
									$setVerweiss = $backupSetSymptomRow['Verweiss'];
									$setRemedy = $backupSetSymptomRow['Remedy'];
									$setsymptomOfDifferentRemedy = $backupSetSymptomRow['symptom_of_different_remedy'];
									$setBereichID = $backupSetSymptomRow['BereichID'];
									$setUnklarheiten = $backupSetSymptomRow['Unklarheiten'];
									$setKommentar = $backupSetSymptomRow['Kommentar'];
									$setFussnote = $backupSetSymptomRow['Fussnote'];

									$set_fv_symptom_de = $fv_symptom_de;
									$set_fv_symptom_en = $fv_symptom_en;

									$setTitel = (isset($backupSetQuelleInfoRow['titel']) AND $backupSetQuelleInfoRow['titel'] != "") ? $backupSetQuelleInfoRow['titel'] : "";
									$setCode = (isset($backupSetQuelleInfoRow['code']) AND $backupSetQuelleInfoRow['code'] != "") ? $backupSetQuelleInfoRow['code'] : "";
									$setJahr = (isset($backupSetQuelleInfoRow['jahr']) AND $backupSetQuelleInfoRow['jahr'] != "") ? $backupSetQuelleInfoRow['jahr'] : "";
									$setBand = (isset($backupSetQuelleInfoRow['band']) AND $backupSetQuelleInfoRow['band'] != "") ? $backupSetQuelleInfoRow['band'] : "";
									$setAuflage = (isset($backupSetQuelleInfoRow['auflage']) AND $backupSetQuelleInfoRow['auflage'] != "") ? $backupSetQuelleInfoRow['auflage'] : "";
									$setAutorOrHerausgeber = (isset($backupSetQuelleInfoRow['autor_or_herausgeber']) AND $backupSetQuelleInfoRow['autor_or_herausgeber'] != "") ? $backupSetQuelleInfoRow['autor_or_herausgeber'] : "";

									$pruStr = "";
									$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer_backup JOIN pruefer ON symptom_pruefer_backup.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer_backup.symptom_id = '".$backupSetSymptomRow['id']."'");
									while($prueferRow = mysqli_fetch_array($prueferResult)){
										if($prueferRow['suchname'] != "")
											$pruStr .= $prueferRow['suchname'].", ";
										else
											$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
									}
									$pruStr =rtrim($pruStr, ", ");
								}
							}
						}
	            	}


					/*$swappedSymptomResult = mysqli_query($db,"SELECT * FROM swapped_symptoms WHERE symptom_id = '".$row['id']."' AND comparison_initial_source_id = '".$comparison_initial_source_id."' AND comparison_comparing_source_ids = '".$comparison_comparing_source_ids."' AND arznei_id = '".$arznei_id."'");
					if(mysqli_num_rows($swappedSymptomResult) > 0){
						$symptomRow = mysqli_fetch_assoc($swappedSymptomResult);
						$backupSetSwappedSymptomResult = mysqli_query($db,"SELECT QI.id, QI.quelle_id, QI.quelle_code, QI.original_quelle_id, QI.arznei_id, QI.Beschreibung, QI.BeschreibungOriginal, QI.searchable_text, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM backup_sets_swapped_symptoms as QI LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.original_symptom_id = '".$symptomRow['symptom_id']."' AND QI.saved_comparisons_backup_id = '".$saved_comparisons_backup_id."'");
						if(mysqli_num_rows($backupSetSwappedSymptomResult) > 0){
							$backupSetSymptomRow = mysqli_fetch_assoc($backupSetSwappedSymptomResult);

							// Apply dynamic conversion
							$BeschreibungOriginal = convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
							// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
							$searchableText = convertTheSymptom($backupSetSymptomRow['searchable_text'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);		
							// $searchableText = base64_encode($searchableText);

							$setQuelleId = $backupSetSymptomRow['quelle_id'];
							$setQuelleCode = $backupSetSymptomRow['quelle_code'];
							$setBeschreibung = $backupSetSymptomRow['Beschreibung'];
							$setBeschreibungOriginal = $BeschreibungOriginal;
							$setSearchableText = $searchableText;
							$setEntnommenAus = $backupSetSymptomRow['EntnommenAus'];
							$setVerweiss = $backupSetSymptomRow['Verweiss'];
							$setRemedy = $backupSetSymptomRow['Remedy'];
							$setsymptomOfDifferentRemedy = $backupSetSymptomRow['symptom_of_different_remedy'];
							$setBereichID = $backupSetSymptomRow['BereichID'];
							$setUnklarheiten = $backupSetSymptomRow['Unklarheiten'];
							$setKommentar = $backupSetSymptomRow['Kommentar'];
							$setFussnote = $backupSetSymptomRow['Fussnote'];
							$setTitel = $backupSetSymptomRow['titel'];
							$setCode = $backupSetSymptomRow['code'];
							$setJahr = $backupSetSymptomRow['jahr'];
							$setBand = $backupSetSymptomRow['band'];
							$setAuflage = $backupSetSymptomRow['auflage'];
							$setAutorOrHerausgeber = $backupSetSymptomRow['autor_or_herausgeber'];

							$pruStr = "";
							$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM backup_sets_swapped_symptom_pruefer JOIN pruefer ON backup_sets_swapped_symptom_pruefer.pruefer_id	= pruefer.pruefer_id WHERE backup_sets_swapped_symptom_pruefer.symptom_id = '".$backupSetSymptomRow['id']."'");
							while($prueferRow = mysqli_fetch_array($prueferResult)){
								if($prueferRow['suchname'] != "")
									$pruStr .= $prueferRow['suchname'].", ";
								else
									$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
							}
							$pruStr =rtrim($pruStr, ", ");

						} else {
							// Get the first symptom set from the backups of this comparison
							// Here joining is made on backup table's quelle_id not with the original_quelle_id
							$importMasterBackupResult = mysqli_query($db,"SELECT quelle_import_master_backup.id AS quelle_import_master_backup_id FROM saved_comparisons_backup JOIN quelle_import_master_backup ON saved_comparisons_backup.quelle_id = quelle_import_master_backup.quelle_id WHERE saved_comparisons_backup.id = '".$saved_comparisons_backup_id."'");
							if(mysqli_num_rows($importMasterBackupResult) > 0){
								$masterSetRow = mysqli_fetch_assoc($importMasterBackupResult);
								$masterBackupSetSymptomResult = mysqli_query($db,"SELECT QI.id, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.Beschreibung, QI.BeschreibungOriginal, QI.searchable_text, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.BereichID, QI.Unklarheiten FROM quelle_import_backup as QI WHERE QI.master_id = '".$masterSetRow['quelle_import_master_backup_id']."' AND QI.original_symptom_id = '".$row['id']."'");
								if(mysqli_num_rows($masterBackupSetSymptomResult) > 0){
									$backupSetSymptomRow = mysqli_fetch_assoc($masterBackupSetSymptomResult);

									$masterBackupSetQuelleInfoResult = mysqli_query($db,"SELECT Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM quelle as Q WHERE Q.quelle_id = '".$backupSetSymptomRow['original_quelle_id']."'");
									if(mysqli_num_rows($masterBackupSetQuelleInfoResult) > 0){
										$backupSetQuelleInfoRow = mysqli_fetch_assoc($masterBackupSetQuelleInfoResult);
									}

									// Apply dynamic conversion
									$BeschreibungOriginal = convertTheSymptom($backupSetSymptomRow['BeschreibungOriginal'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);
									// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
									$searchableText = convertTheSymptom($backupSetSymptomRow['searchable_text'], $backupSetSymptomRow['original_quelle_id'], $backupSetSymptomRow['arznei_id']);		
									// $searchableText = base64_encode($searchableText);

									$setQuelleId = $backupSetSymptomRow['original_quelle_id'];
									$setQuelleCode = $backupSetSymptomRow['quelle_code'];
									$setBeschreibung = $backupSetSymptomRow['Beschreibung'];
									$setBeschreibungOriginal = $BeschreibungOriginal;
									$setSearchableText = $searchableText;
									$setEntnommenAus = $backupSetSymptomRow['EntnommenAus'];
									$setVerweiss = $backupSetSymptomRow['Verweiss'];
									$setRemedy = $backupSetSymptomRow['Remedy'];
									$setsymptomOfDifferentRemedy = $backupSetSymptomRow['symptom_of_different_remedy'];
									$setBereichID = $backupSetSymptomRow['BereichID'];
									$setUnklarheiten = $backupSetSymptomRow['Unklarheiten'];
									$setKommentar = $backupSetSymptomRow['Kommentar'];
									$setFussnote = $backupSetSymptomRow['Fussnote'];
									$setTitel = (isset($backupSetQuelleInfoRow['titel']) AND $backupSetQuelleInfoRow['titel'] != "") ? $backupSetQuelleInfoRow['titel'] : "";
									$setCode = (isset($backupSetQuelleInfoRow['code']) AND $backupSetQuelleInfoRow['code'] != "") ? $backupSetQuelleInfoRow['code'] : "";
									$setJahr = (isset($backupSetQuelleInfoRow['jahr']) AND $backupSetQuelleInfoRow['jahr'] != "") ? $backupSetQuelleInfoRow['jahr'] : "";
									$setBand = (isset($backupSetQuelleInfoRow['band']) AND $backupSetQuelleInfoRow['band'] != "") ? $backupSetQuelleInfoRow['band'] : "";
									$setAuflage = (isset($backupSetQuelleInfoRow['auflage']) AND $backupSetQuelleInfoRow['auflage'] != "") ? $backupSetQuelleInfoRow['auflage'] : "";
									$setAutorOrHerausgeber = (isset($backupSetQuelleInfoRow['autor_or_herausgeber']) AND $backupSetQuelleInfoRow['autor_or_herausgeber'] != "") ? $backupSetQuelleInfoRow['autor_or_herausgeber'] : "";

									$pruStr = "";
									$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer_backup JOIN pruefer ON symptom_pruefer_backup.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer_backup.symptom_id = '".$backupSetSymptomRow['id']."'");
									while($prueferRow = mysqli_fetch_array($prueferResult)){
										if($prueferRow['suchname'] != "")
											$pruStr .= $prueferRow['suchname'].", ";
										else
											$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
									}
									$pruStr =rtrim($pruStr, ", ");
								}
								else
								{
									// When the symptom is not there in quelle_import_backup table check backup connection table may be this symptom was a connected symptom in this backup set.
									$connectedSymptomInfo = mysqli_query($db, "SELECT id, initial_source_type, comparing_source_type, source_arznei_id, initial_source_id, comparing_source_id, conversion_initial_source_id, conversion_comparing_source_id, initial_source_code, comparing_source_code, initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted, comparing_source_symptom_highlighted, initial_source_symptom, comparing_source_symptom FROM symptom_connections_backup WHERE (initial_source_symptom_id = '".$row['id']."' OR comparing_source_symptom_id = '".$row['id']."') AND initial_source_type = 'original' AND comparing_source_type = 'original' AND saved_comparisons_backup_id = '".$saved_comparisons_backup_id."' AND (is_connected = 1 OR is_pasted = 1) LIMIT 0, 1");
									if(mysqli_num_rows($connectedSymptomInfo) > 0){
										$connectedSymptomRow = mysqli_fetch_assoc($connectedSymptomInfo);
										if($connectedSymptomRow['initial_source_symptom_id'] == $row['id']) {

											// Apply dynamic conversion
											$BeschreibungOriginal = convertTheSymptom($connectedSymptomRow['initial_source_symptom'], $connectedSymptomRow['conversion_initial_source_id'], $connectedSymptomRow['source_arznei_id']);
											// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
											$searchableText = convertTheSymptom($connectedSymptomRow['initial_source_symptom'], $connectedSymptomRow['conversion_initial_source_id'], $connectedSymptomRow['source_arznei_id']);		
											// $searchableText = base64_encode($searchableText);

											$setQuelleId = $connectedSymptomRow['initial_source_id'];
											$setQuelleCode = $connectedSymptomRow['initial_source_code'];
											// $setBeschreibung = $backupSetSymptomRow['Beschreibung'];
											$setBeschreibung = $connectedSymptomRow['initial_source_symptom'];
											$setBeschreibungOriginal = $BeschreibungOriginal;
											$setSearchableText = $searchableText;
											// $setEntnommenAus = $backupSetSymptomRow['EntnommenAus'];
											// $setVerweiss = $backupSetSymptomRow['Verweiss'];
											// $setRemedy = $backupSetSymptomRow['Remedy'];
											// $setsymptomOfDifferentRemedy = $backupSetSymptomRow['symptom_of_different_remedy'];
											// $setBereichID = $backupSetSymptomRow['BereichID'];
											// $setUnklarheiten = $backupSetSymptomRow['Unklarheiten'];
											// $setKommentar = $backupSetSymptomRow['Kommentar'];
											// $setFussnote = $backupSetSymptomRow['Fussnote'];
											// $setTitel = (isset($backupSetQuelleInfoRow['titel']) AND $backupSetQuelleInfoRow['titel'] != "") ? $backupSetQuelleInfoRow['titel'] : "";
											// $setCode = (isset($backupSetQuelleInfoRow['code']) AND $backupSetQuelleInfoRow['code'] != "") ? $backupSetQuelleInfoRow['code'] : "";
											// $setJahr = (isset($backupSetQuelleInfoRow['jahr']) AND $backupSetQuelleInfoRow['jahr'] != "") ? $backupSetQuelleInfoRow['jahr'] : "";
											// $setBand = (isset($backupSetQuelleInfoRow['band']) AND $backupSetQuelleInfoRow['band'] != "") ? $backupSetQuelleInfoRow['band'] : "";
											// $setAuflage = (isset($backupSetQuelleInfoRow['auflage']) AND $backupSetQuelleInfoRow['auflage'] != "") ? $backupSetQuelleInfoRow['auflage'] : "";
											// $setAutorOrHerausgeber = (isset($backupSetQuelleInfoRow['autor_or_herausgeber']) AND $backupSetQuelleInfoRow['autor_or_herausgeber'] != "") ? $backupSetQuelleInfoRow['autor_or_herausgeber'] : "";

											// $pruStr = "";
											// $prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer_backup JOIN pruefer ON symptom_pruefer_backup.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer_backup.symptom_id = '".$backupSetSymptomRow['id']."'");
											// while($prueferRow = mysqli_fetch_array($prueferResult)){
											// 	if($prueferRow['suchname'] != "")
											// 		$pruStr .= $prueferRow['suchname'].", ";
											// 	else
											// 		$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
											// }
											// $pruStr =rtrim($pruStr, ", ");
										} else {

											// Apply dynamic conversion
											$BeschreibungOriginal = convertTheSymptom($connectedSymptomRow['comparing_source_symptom'], $connectedSymptomRow['conversion_comparing_source_id'], $connectedSymptomRow['source_arznei_id']);
											// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
											$searchableText = convertTheSymptom($connectedSymptomRow['comparing_source_symptom'], $connectedSymptomRow['conversion_comparing_source_id'], $connectedSymptomRow['source_arznei_id']);		
											// $searchableText = base64_encode($searchableText);
											
											$setQuelleId = $connectedSymptomRow['comparing_source_id'];
											$setQuelleCode = $connectedSymptomRow['comparing_source_code'];
											// $setBeschreibung = $backupSetSymptomRow['Beschreibung'];
											$setBeschreibung = $connectedSymptomRow['comparing_source_symptom'];
											$setBeschreibungOriginal = $BeschreibungOriginal;
											$setSearchableText = $searchableText;
											// $setEntnommenAus = $backupSetSymptomRow['EntnommenAus'];
											// $setVerweiss = $backupSetSymptomRow['Verweiss'];
											// $setRemedy = $backupSetSymptomRow['Remedy'];
											// $setsymptomOfDifferentRemedy = $backupSetSymptomRow['symptom_of_different_remedy'];
											// $setBereichID = $backupSetSymptomRow['BereichID'];
											// $setUnklarheiten = $backupSetSymptomRow['Unklarheiten'];
											// $setKommentar = $backupSetSymptomRow['Kommentar'];
											// $setFussnote = $backupSetSymptomRow['Fussnote'];
											// $setTitel = (isset($backupSetQuelleInfoRow['titel']) AND $backupSetQuelleInfoRow['titel'] != "") ? $backupSetQuelleInfoRow['titel'] : "";
											// $setCode = (isset($backupSetQuelleInfoRow['code']) AND $backupSetQuelleInfoRow['code'] != "") ? $backupSetQuelleInfoRow['code'] : "";
											// $setJahr = (isset($backupSetQuelleInfoRow['jahr']) AND $backupSetQuelleInfoRow['jahr'] != "") ? $backupSetQuelleInfoRow['jahr'] : "";
											// $setBand = (isset($backupSetQuelleInfoRow['band']) AND $backupSetQuelleInfoRow['band'] != "") ? $backupSetQuelleInfoRow['band'] : "";
											// $setAuflage = (isset($backupSetQuelleInfoRow['auflage']) AND $backupSetQuelleInfoRow['auflage'] != "") ? $backupSetQuelleInfoRow['auflage'] : "";
											// $setAutorOrHerausgeber = (isset($backupSetQuelleInfoRow['autor_or_herausgeber']) AND $backupSetQuelleInfoRow['autor_or_herausgeber'] != "") ? $backupSetQuelleInfoRow['autor_or_herausgeber'] : "";

											// $pruStr = "";
											// $prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer_backup JOIN pruefer ON symptom_pruefer_backup.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer_backup.symptom_id = '".$backupSetSymptomRow['id']."'");
											// while($prueferRow = mysqli_fetch_array($prueferResult)){
											// 	if($prueferRow['suchname'] != "")
											// 		$pruStr .= $prueferRow['suchname'].", ";
											// 	else
											// 		$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
											// }
											// $pruStr =rtrim($pruStr, ", ");
										}
									}
								}
							}
						}
					}*/
				} 

				$resultData['id'] = $row['id'];
				$resultData['quelle_id'] = $setQuelleId;
				$resultData['quelle_code'] = $setQuelleCode;

				$resultData['symptom_number'] = $setSymptomNumber;
				$resultData['symptom_page'] = $setSymptomPageData;

				$resultData['Beschreibung_de'] = $setBeschreibung_de;
				$resultData['Beschreibung_en'] = $setBeschreibung_en;
				$resultData['BeschreibungOriginal_book_format_de'] = $setBeschreibungOriginal_book_format_de;
				$resultData['BeschreibungOriginal_book_format_en'] = $setBeschreibungOriginal_book_format_en;
				$resultData['BeschreibungOriginal_de'] = $setBeschreibungOriginal_de;
				$resultData['BeschreibungOriginal_en'] = $setBeschreibungOriginal_en;
				$resultData['BeschreibungOriginal_with_grading_de'] = $setBeschreibungOriginal_with_grading_de;
				$resultData['BeschreibungOriginal_with_grading_en'] = $setBeschreibungOriginal_with_grading_en;
				$resultData['searchable_text_with_grading_de'] = $setSearchable_text_with_grading_de;
				$resultData['searchable_text_with_grading_en'] = $setSearchable_text_with_grading_en;
				$resultData['searchable_text_de'] = $setSearchableText_de;
				$resultData['searchable_text_en'] = $setSearchableText_en;
				$resultData['BeschreibungFull_de'] = $setBeschreibungFull_de;
				$resultData['BeschreibungFull_en'] = $setBeschreibungFull_en;
				$resultData['BeschreibungFull_with_grading_de'] = $setBeschreibungFull_with_grading_de;
				$resultData['BeschreibungFull_with_grading_en'] = $setBeschreibungFull_with_grading_en;
				$resultData['Fussnote'] = $setFussnote;
				$resultData['EntnommenAus'] = $setEntnommenAus;
				$resultData['Pruefer'] = $pruStr;
				$resultData['Verweiss'] = $setVerweiss;
				$resultData['Kommentar'] = $setKommentar;
				$resultData['Remedy'] = $setRemedy;
				$resultData['symptom_of_different_remedy'] = $setsymptomOfDifferentRemedy;
				$resultData['BereichID'] = $setBereichID;
				$resultData['Unklarheiten'] = $setUnklarheiten;
				$resultData['comparison_language'] = $setComparisonLanguage;

				$resultData['is_final_version_available'] = $set_is_final_version_available;
				$resultData['fv_con_initial_symptom_de'] = $set_fv_con_initial_symptom_de;
				$resultData['fv_con_initial_symptom_en'] = $set_fv_con_initial_symptom_en;
				$resultData['fv_con_comparative_symptom_de'] = $set_fv_con_comparative_symptom_de;
				$resultData['fv_con_comparative_symptom_en'] = $set_fv_con_comparative_symptom_en;
				$resultData['fv_con_initial_source_code'] = $set_fv_con_initial_source_code;
				$resultData['fv_con_comparative_source_code'] = $set_fv_con_comparative_source_code;
				$resultData['fv_symptom_de'] = $set_fv_symptom_de;
				$resultData['fv_symptom_en'] = $set_fv_symptom_en;

				// Source Data
				$resultData['titel'] = $setTitel;
				$resultData['code'] = $setCode;
				$resultData['jahr'] = $setJahr;
				$resultData['band'] = $setBand;
				$resultData['auflage'] = $setAuflage;
				$resultData['autor_or_herausgeber'] = $setAutorOrHerausgeber;
				
				$status = "success";
				$message = "success";
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