<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Fetching basic informations of a particular symptom
	*/
?>
<?php  
	$resultData = array();
	$status = '';
	$message = '';
	try {
		if(isset($_POST['symptom_id']) AND !empty($_POST['symptom_id'])){	
			$symptomId = trim($_POST['symptom_id']);
			$individual_comparison_language = (isset($_POST['individual_comparison_language']) AND $_POST['individual_comparison_language'] != "") ? trim($_POST['individual_comparison_language']) : "";
			$queryRes = mysqli_query($db,"SELECT QI.id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.original_symptom_id, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber FROM quelle_import_test as QI LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.id = '".$symptomId."'");
			if(mysqli_num_rows($queryRes) > 0){
				$row = mysqli_fetch_assoc($queryRes);

				$pruStr = "";
				$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer JOIN pruefer ON symptom_pruefer.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer.symptom_id = '".$row['id']."'");
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
				// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
				$searchableText_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";		
				// $searchableText = base64_encode($searchableText);
				$BeschreibungOriginal_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : "";
				// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
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
					$connectedSymptomResult = mysqli_query($db,"SELECT initial_source_symptom_id, comparing_source_symptom_id, initial_source_symptom_highlighted_de, initial_source_symptom_highlighted_en, comparing_source_symptom_highlighted_de, comparing_source_symptom_highlighted_en, initial_source_symptom_de, initial_source_symptom_en, comparing_source_symptom_de, comparing_source_symptom_en, connection_language, matching_percentage, is_connected, is_ns_connect, ns_connect_note, is_pasted, is_ns_paste, ns_paste_note, connection_or_paste_type FROM symptom_connections WHERE (initial_source_symptom_id = '".$symptomId."' OR comparing_source_symptom_id = '".$symptomId."') AND (connection_or_paste_type = 3 OR connection_or_paste_type = 4)");
					if(mysqli_num_rows($connectedSymptomResult) > 0){
						$connInfoRow = mysqli_fetch_assoc($connectedSymptomResult);
						if($symptomId == $connInfoRow['initial_source_symptom_id']){
							$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available FROM quelle_import_test as QI WHERE QI.id = '".$connInfoRow['comparing_source_symptom_id']."'");
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
							$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available FROM quelle_import_test as QI WHERE QI.id = '".$connInfoRow['initial_source_symptom_id']."'");
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
					}
				} 

				
				
				// Converted symptom with grading number
				// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
				// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
				// convertTheSymptom()
				$BeschreibungOriginal_with_grading_de = ($row['BeschreibungOriginal_de'] != "") ? convertTheSymptom($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungOriginal_with_grading_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				// Converted symptom with grading number
				$searchable_text_with_grading_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$searchable_text_with_grading_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				// Converting the symptom to the original format(means the book format as it was found in the book)
				$BeschreibungOriginal_book_format_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : "";
				$BeschreibungOriginal_book_format_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : "";

				$BeschreibungFull_with_grading_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";
				$BeschreibungFull_with_grading_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

				$resultData['id'] = $row['id'];
				$resultData['quelle_id'] = $row['quelle_id'];
				$resultData['quelle_code'] = $row['quelle_code'];

				$resultData['symptom_number'] = $symptomNumber;
				$resultData['symptom_page'] = $symptomPageData;
				
				$resultData['Beschreibung_de'] = $row['Beschreibung_de'];
				$resultData['Beschreibung_en'] = $row['Beschreibung_en'];
				$resultData['BeschreibungOriginal_book_format_de'] = $BeschreibungOriginal_book_format_de;
				$resultData['BeschreibungOriginal_book_format_en'] = $BeschreibungOriginal_book_format_en;
				$resultData['BeschreibungPlain_de'] = $row['BeschreibungPlain_de'];
				$resultData['BeschreibungPlain_en'] = $row['BeschreibungPlain_en'];
				$resultData['BeschreibungOriginal_de'] = $BeschreibungOriginal_de;
				$resultData['BeschreibungOriginal_en'] = $BeschreibungOriginal_en;
				$resultData['BeschreibungOriginal_with_grading_de'] = $BeschreibungOriginal_with_grading_de;
				$resultData['BeschreibungOriginal_with_grading_en'] = $BeschreibungOriginal_with_grading_en;
				$resultData['BeschreibungFull_de'] = $BeschreibungFull_de;
				$resultData['BeschreibungFull_en'] = $BeschreibungFull_en;
				$resultData['BeschreibungFull_with_grading_de'] = $BeschreibungFull_with_grading_de;
				$resultData['BeschreibungFull_with_grading_en'] = $BeschreibungFull_with_grading_en;
				$resultData['searchable_text_with_grading_de'] = $searchable_text_with_grading_de;
				$resultData['searchable_text_with_grading_en'] = $searchable_text_with_grading_en;
				$resultData['searchable_text_de'] = $searchableText_de;
				$resultData['searchable_text_en'] = $searchableText_en;
				$resultData['Fussnote'] = $row['Fussnote'];
				$resultData['EntnommenAus'] = $row['EntnommenAus'];
				$resultData['Pruefer'] = $pruStr;
				$resultData['Verweiss'] = $row['Verweiss'];
				$resultData['Kommentar'] = $row['Kommentar'];
				$resultData['Remedy'] = $row['Remedy'];
				$resultData['symptom_of_different_remedy'] = $row['symptom_of_different_remedy'];
				$resultData['BereichID'] = $row['BereichID'];
				$resultData['Unklarheiten'] = $row['Unklarheiten'];
				$resultData['comparison_language'] = $individual_comparison_language;

				$resultData['is_final_version_available'] = $row['is_final_version_available'];
				$resultData['fv_con_initial_symptom_de'] = $fv_con_initial_symptom_de;
				$resultData['fv_con_initial_symptom_en'] = $fv_con_initial_symptom_en;
				$resultData['fv_con_comparative_symptom_de'] = $fv_con_comparative_symptom_de;
				$resultData['fv_con_comparative_symptom_en'] = $fv_con_comparative_symptom_en;
				$resultData['fv_symptom_de'] = $fv_symptom_de;
				$resultData['fv_symptom_en'] = $fv_symptom_en;
				$resultData['fv_con_initial_source_code'] = $fv_con_initial_source_code;
				$resultData['fv_con_comparative_source_code'] = $fv_con_comparative_source_code;

				// Source Data
				$resultData['titel'] = $row['titel'];
				$resultData['code'] = $row['code'];
				$resultData['jahr'] = $row['jahr'];
				$resultData['band'] = $row['band'];
				$resultData['auflage'] = $row['auflage'];
				$resultData['autor_or_herausgeber'] = $row['autor_or_herausgeber'];
				
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