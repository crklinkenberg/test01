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
		if(isset($_POST['initial_symptom_id']) AND !empty($_POST['initial_symptom_id']) AND isset($_POST['comparison_table_name']) AND !empty($_POST['comparison_table_name'])){	
			$iniId = trim($_POST['initial_symptom_id']);
			$comSymptomId = (isset($_POST['comparing_symptom_id']) AND $_POST['comparing_symptom_id'] != "") ? trim($_POST['comparing_symptom_id']) : "";
			if($comSymptomId != ""){
				$symptomId = $comSymptomId;
			}else{
				$symptomId = $iniId;
			}
			$comparisonTableName = (isset($_POST['comparison_table_name']) AND $_POST['comparison_table_name'] != "") ? trim($_POST['comparison_table_name']) : "";
			$individual_comparison_language = (isset($_POST['comparison_language']) AND $_POST['comparison_language'] != "") ? trim($_POST['comparison_language']) : "";
			
			$dynamicComparisonTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comparisonTableName."'");
			if(mysqli_num_rows($dynamicComparisonTableCheck) > 0){
				$queryRes = mysqli_query($db,"SELECT QI.id as symptom_id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.synonym_word, QI.strict_synonym, QI.synonym_partial_1, QI.synonym_partial_2, QI.synonym_general, QI.synonym_minor, QI.synonym_nn, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel as quelle_titel, Q.jahr as quelle_jahr, Q.band as quelle_band, Q.auflage as quelle_auflage, Q.autor_or_herausgeber as quelle_autor_or_herausgeber FROM quelle_import_test as QI LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.id = '".$symptomId."'");
				if($comSymptomId != "" AND $iniId != "")
					$queryResDynamicTableCondition = " CT.symptom_id = '".$symptomId."' AND CT.initial_symptom_id = '".$iniId."' LIMIT 1";
				else
					$queryResDynamicTableCondition = " CT.symptom_id = '".$symptomId."' LIMIT 1";
				$queryResDynamicTable = mysqli_query($db,"SELECT CT.id, CT.symptom_id, CT.Symptomnummer, CT.SeiteOriginalVon, CT.SeiteOriginalBis, CT.quelle_id, CT.arznei_id, CT.quelle_code, CT.final_version_de, CT.final_version_en, CT.Beschreibung_de, CT.Beschreibung_en, CT.BeschreibungPlain_de, CT.BeschreibungPlain_en, CT.BeschreibungOriginal_de, CT.BeschreibungOriginal_en, CT.BeschreibungFull_de, CT.BeschreibungFull_en, CT.searchable_text_de, CT.searchable_text_en, CT.is_final_version_available, CT.Fussnote, CT.EntnommenAus, CT.Verweiss, CT.Kommentar, CT.Remedy, CT.symptom_of_different_remedy, CT.synonym_word, CT.strict_synonym, CT.synonym_partial_1, CT.synonym_partial_2, CT.synonym_general, CT.synonym_minor, CT.synonym_nn, CT.comparison_matched_synonyms, CT.BereichID, CT.Unklarheiten, CT.quelle_type_id, CT.quelle_titel, CT.quelle_jahr, CT.quelle_band, CT.quelle_auflage, CT.quelle_autor_or_herausgeber, CT.non_secure_paste, CT.non_secure_connect FROM $comparisonTableName as CT WHERE".$queryResDynamicTableCondition);
				
				if(mysqli_num_rows($queryResDynamicTable) == 0){
					$queryResDynamicTable = mysqli_query($db,"SELECT CT.id, CT.symptom_id, CT.Symptomnummer, CT.SeiteOriginalVon, CT.SeiteOriginalBis, CT.quelle_id, CT.arznei_id, CT.quelle_code, CT.final_version_de, CT.final_version_en, CT.Beschreibung_de, CT.Beschreibung_en, CT.BeschreibungPlain_de, CT.BeschreibungPlain_en, CT.BeschreibungOriginal_de, CT.BeschreibungOriginal_en, CT.BeschreibungFull_de, CT.BeschreibungFull_en, CT.searchable_text_de, CT.searchable_text_en, CT.is_final_version_available, CT.Fussnote, CT.EntnommenAus, CT.Verweiss, CT.Kommentar, CT.Remedy, CT.symptom_of_different_remedy, CT.synonym_word, CT.strict_synonym, CT.synonym_partial_1, CT.synonym_partial_2, CT.synonym_general, CT.synonym_minor, CT.synonym_nn, CT.comparison_matched_synonyms, CT.BereichID, CT.Unklarheiten, CT.quelle_type_id, CT.quelle_titel, CT.quelle_jahr, CT.quelle_band, CT.quelle_auflage, CT.quelle_autor_or_herausgeber, CT.non_secure_paste, CT.non_secure_connect FROM $comparisonTableName as CT WHERE CT.symptom_id = '".$symptomId."' LIMIT 1");
				}
				if(mysqli_num_rows($queryRes) > 0 AND mysqli_num_rows($queryResDynamicTable) > 0){
					
					$row = mysqli_fetch_assoc($queryRes);
					$rowDynamicTable = mysqli_fetch_assoc($queryResDynamicTable);

					$originalQuelleId = $row['quelle_id'];
					$originalSymptomId = $row['symptom_id'];

					// collecting symptom type info
					$symptomType = "";
					$querySympTypeInfo = mysqli_query($db,"SELECT symptom_type_for_whole FROM quelle_symptom_settings WHERE quelle_id =".$originalQuelleId);
					if(mysqli_num_rows($querySympTypeInfo) > 0){
						$rowSympTypeInfo = mysqli_fetch_assoc($querySympTypeInfo);
						$symptomType = (isset($rowSympTypeInfo['symptom_type_for_whole']) AND $rowSympTypeInfo['symptom_type_for_whole'] != "") ? $rowSympTypeInfo['symptom_type_for_whole'] : "";
					}

					$symptomTypeResult = mysqli_query($db, "SELECT * FROM symptom_type_setting WHERE symptom_id = '".$symptomId."'");
					if(mysqli_num_rows($symptomTypeResult) > 0){
						$symptomTypeRow = mysqli_fetch_assoc($symptomTypeResult);
					}
					$symptomType = (isset($symptomTypeRow['symptom_type']) and $symptomTypeRow['symptom_type'] != "")? $symptomTypeRow['symptom_type'] : $symptomType;

					$pruStr = "";
					$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer JOIN pruefer ON symptom_pruefer.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer.symptom_id = '".$row['symptom_id']."'");
					while($prueferRow = mysqli_fetch_array($prueferResult)){
						if($prueferRow['suchname'] != "")
							$pruStr .= $prueferRow['suchname'].", ";
						else
							$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
					}
					$pruStr =rtrim($pruStr, ", ");

					$rmdStr = "";
					$remedyResult = mysqli_query($db,"SELECT arznei.titel FROM symptom_remedy JOIN arznei ON symptom_remedy.remedy_id = arznei.arznei_id WHERE symptom_remedy.symptom_id = '".$row['symptom_id']."'");
					while($remedyRow = mysqli_fetch_array($remedyResult)){
						if($remedyRow['titel'] != "")
							$rmdStr .= $remedyRow['titel'].", ";
					}
					$rmdStr =rtrim($rmdStr, ", ");

					$symptomNumber = $row['Symptomnummer'];
					$symptomPageData = ($row['SeiteOriginalVon'] == $row['SeiteOriginalBis']) ? $row['SeiteOriginalVon'] : $row['SeiteOriginalVon']."-".$row['SeiteOriginalBis'];

					// Apply dynamic conversion
					// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
					// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
					// convertTheSymptom()
					$BeschreibungOriginal_de = ($row['BeschreibungOriginal_de'] != "") ? convertTheSymptom($row['BeschreibungOriginal_de'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";
					// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
					$searchableText_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";		
					// $searchableText = base64_encode($searchableText);
					$BeschreibungOriginal_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";
					// $BeschreibungOriginal = base64_encode($BeschreibungOriginal);
					$searchableText_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";		
					// $searchableText = base64_encode($searchableText);

					$BeschreibungFull_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";
					$BeschreibungFull_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $originalQuelleId, $row['arznei_id'], 0, 0, $row['symptom_id'], $originalSymptomId) : "";

					// Getting connection informations if it is a Final version symptom
					$fv_con_initial_symptom_de = "";
					$fv_con_initial_symptom_en = "";
					$fv_con_initial_source_code = "";
					$fv_con_comparative_symptom_de = "";
					$fv_con_comparative_symptom_en = "";
					$fv_con_comparative_source_code = "";
					$fv_symptom_de = "";
					$fv_symptom_en = "";
					if($rowDynamicTable['is_final_version_available'] != 0){
						$fv_symptom_de = ($rowDynamicTable['final_version_de'] != "") ? convertTheSymptom($rowDynamicTable['final_version_de'], $originalQuelleId, $rowDynamicTable['arznei_id'], $rowDynamicTable['is_final_version_available'], 0, $rowDynamicTable['symptom_id'], $originalSymptomId) : "";
						$fv_symptom_en = ($rowDynamicTable['final_version_en'] != "") ? convertTheSymptom($rowDynamicTable['final_version_en'], $originalQuelleId, $rowDynamicTable['arznei_id'], $rowDynamicTable['is_final_version_available'], 0, $rowDynamicTable['symptom_id'], $originalSymptomId) : "";

						$comparisonConnectionTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comparisonTableName."_connections'");
						if(mysqli_num_rows($comparisonConnectionTableCheck) > 0){
							$connectedSymptomResult = mysqli_query($db,"SELECT initial_symptom_id, comparing_symptom_id, initial_quelle_code, comparing_quelle_code, initial_quelle_id, comparing_quelle_id FROM ".$comparisonTableName."_connections WHERE (initial_symptom_id = '".$symptomId."' OR comparing_symptom_id = '".$symptomId."') AND (connection_type = 'CE' OR connection_type = 'PE') ORDER BY id DESC LIMIT 1");
							if(mysqli_num_rows($connectedSymptomResult) > 0){
								$connInfoRow = mysqli_fetch_assoc($connectedSymptomResult);
								if($symptomId == $connInfoRow['initial_symptom_id']){
									$fv_con_initial_source_code = $rowDynamicTable['quelle_code'];
									$fv_con_comparative_source_code = $connInfoRow['comparing_quelle_code'];
									$fv_con_initial_symptom_de = $searchableText_de;
									$fv_con_initial_symptom_en = $searchableText_en;

									$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available, QI.searchable_text_de, QI.searchable_text_en FROM quelle_import_test as QI WHERE QI.id = '".$connInfoRow['comparing_symptom_id']."'");
									if(mysqli_num_rows($querySymptomRes) > 0)
										$fvCheckRow = mysqli_fetch_assoc($querySymptomRes);
									$symArzneiId = (isset($fvCheckRow['arznei_id']) AND $fvCheckRow['arznei_id'] != "") ? $fvCheckRow['arznei_id'] : "";
									$sym_searchable_text_de = (isset($fvCheckRow['searchable_text_de']) AND $fvCheckRow['searchable_text_de'] != "") ? $fvCheckRow['searchable_text_de'] : "";
									$sym_searchable_text_en = (isset($fvCheckRow['searchable_text_en']) AND $fvCheckRow['searchable_text_en'] != "") ? $fvCheckRow['searchable_text_en'] : "";

									$fetched_com_searchableText_de = ($sym_searchable_text_de != "") ? convertTheSymptom($sym_searchable_text_de, $connInfoRow['comparing_quelle_id'], $symArzneiId, 0, 0, $connInfoRow['comparing_symptom_id'], $connInfoRow['comparing_symptom_id']) : "";
									$fetched_com_searchableText_en = ($sym_searchable_text_en != "") ? convertTheSymptom($sym_searchable_text_en, $connInfoRow['comparing_quelle_id'], $symArzneiId, 0, 0, $connInfoRow['comparing_symptom_id'], $connInfoRow['comparing_symptom_id']) : "";
									$fv_con_comparative_symptom_de = $fetched_com_searchableText_de;	
									$fv_con_comparative_symptom_en = $fetched_com_searchableText_en;
								} else {
									$fv_con_initial_source_code = $connInfoRow['initial_quelle_code'];
									$fv_con_comparative_source_code = $rowDynamicTable['quelle_code'];
									$fv_con_comparative_symptom_de = $searchableText_de;	
									$fv_con_comparative_symptom_en = $searchableText_en;

									$querySymptomRes = mysqli_query($db,"SELECT QI.id, QI.quelle_code, QI.original_symptom_id, QI.original_quelle_id, QI.arznei_id, QI.is_final_version_available, QI.searchable_text_de, QI.searchable_text_en FROM quelle_import_test as QI WHERE QI.id = '".$connInfoRow['initial_symptom_id']."'");
									if(mysqli_num_rows($querySymptomRes) > 0)
										$fvCheckRow = mysqli_fetch_assoc($querySymptomRes);
									$symArzneiId = (isset($fvCheckRow['arznei_id']) AND $fvCheckRow['arznei_id'] != "") ? $fvCheckRow['arznei_id'] : "";
									$sym_searchable_text_de = (isset($fvCheckRow['searchable_text_de']) AND $fvCheckRow['searchable_text_de'] != "") ? $fvCheckRow['searchable_text_de'] : "";
									$sym_searchable_text_en = (isset($fvCheckRow['searchable_text_en']) AND $fvCheckRow['searchable_text_en'] != "") ? $fvCheckRow['searchable_text_en'] : "";

									$fetched_com_searchableText_de = ($sym_searchable_text_de != "") ? convertTheSymptom($sym_searchable_text_de, $connInfoRow['initial_quelle_id'], $symArzneiId, 0, 0, $connInfoRow['initial_symptom_id'], $connInfoRow['initial_symptom_id']) : "";
									$fetched_com_searchableText_en = ($sym_searchable_text_en != "") ? convertTheSymptom($sym_searchable_text_en, $connInfoRow['initial_quelle_id'], $symArzneiId, 0, 0, $connInfoRow['initial_symptom_id'], $connInfoRow['initial_symptom_id']) : "";

									$fv_con_initial_symptom_de = $fetched_com_searchableText_de;
									$fv_con_initial_symptom_en = $fetched_com_searchableText_en;
								}
							}
						}
						// $fv_con_initial_symptom_de = $searchableText_de;
						// $fv_con_initial_symptom_en = $searchableText_en;
						// $fv_con_comparative_symptom_de = $searchableText_de;	
						// $fv_con_comparative_symptom_en = $searchableText_en;
						// $fv_con_initial_source_code = $rowDynamicTable['quelle_code'];
						// $fv_con_comparative_source_code = $rowDynamicTable['quelle_code'];
					} 
					
					// Converted symptom with grading number
					// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
					// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
					// convertTheSymptom()
					$BeschreibungOriginal_with_grading_de = ($row['BeschreibungOriginal_de'] != "") ? convertTheSymptom($row['BeschreibungOriginal_de'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";
					$BeschreibungOriginal_with_grading_en = ($row['BeschreibungOriginal_en'] != "") ? convertTheSymptom($row['BeschreibungOriginal_en'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";
					// Converted symptom with grading number
					$searchable_text_with_grading_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";
					$searchable_text_with_grading_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";
					// Converting the symptom to the original format(means the book format as it was found in the book)
					$BeschreibungOriginal_book_format_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $originalQuelleId, $row['arznei_id']) : "";
					$BeschreibungOriginal_book_format_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $originalQuelleId, $row['arznei_id']) : "";

					$BeschreibungFull_with_grading_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";
					$BeschreibungFull_with_grading_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $originalQuelleId, $row['arznei_id'], 0, 1, $row['symptom_id'], $originalSymptomId) : "";

					$resultData['id'] = $row['symptom_id'];
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
					$resultData['symptom_type'] = $symptomType;
					$resultData['Remedy'] = $rmdStr;
					$resultData['symptom_of_different_remedy'] = $row['symptom_of_different_remedy'];
					$resultData['BereichID'] = $row['BereichID'];
					$resultData['Unklarheiten'] = $row['Unklarheiten'];
					$resultData['comparison_language'] = $individual_comparison_language;

					$resultData['is_final_version_available'] = $rowDynamicTable['is_final_version_available'];
					$resultData['fv_con_initial_symptom_de'] = $fv_con_initial_symptom_de;
					$resultData['fv_con_initial_symptom_en'] = $fv_con_initial_symptom_en;
					$resultData['fv_con_comparative_symptom_de'] = $fv_con_comparative_symptom_de;
					$resultData['fv_con_comparative_symptom_en'] = $fv_con_comparative_symptom_en;
					$resultData['fv_symptom_de'] = $fv_symptom_de;
					$resultData['fv_symptom_en'] = $fv_symptom_en;
					$resultData['fv_con_initial_source_code'] = $fv_con_initial_source_code;
					$resultData['fv_con_comparative_source_code'] = $fv_con_comparative_source_code;
					
					//$resultData['non_secure_connect'] = $row['non_secure_connect'];
					//$resultData['non_secure_paste'] = $row['non_secure_paste'];

					$comparisonMatchedSynonymArr = (isset($rowDynamicTable['comparison_matched_synonyms']) AND $rowDynamicTable['comparison_matched_synonyms'] != "") ? unserialize($rowDynamicTable['comparison_matched_synonyms']) : array();
					$resultData['synonym_word'] = displayFormateOfSynonym($rowDynamicTable['synonym_word']);
					$resultData['strict_synonym'] = displayFormateOfSynonym($rowDynamicTable['strict_synonym']);
					$resultData['synonym_partial_1'] = displayFormateOfSynonym($rowDynamicTable['synonym_partial_1']);
					$resultData['synonym_partial_2'] = displayFormateOfSynonym($rowDynamicTable['synonym_partial_2']);
					$resultData['synonym_general'] = displayFormateOfSynonym($rowDynamicTable['synonym_general']);
					$resultData['synonym_minor'] = displayFormateOfSynonym($rowDynamicTable['synonym_minor']);
					$resultData['synonym_nn'] = displayFormateOfSynonym($rowDynamicTable['synonym_nn']);
					$resultData['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonymArr)) ? implode(',', $comparisonMatchedSynonymArr) : "";

					// Source Data
					$resultData['titel'] = $row['quelle_titel'];
					$resultData['code'] = $row['quelle_code'];
					$resultData['jahr'] = $row['quelle_jahr'];
					$resultData['band'] = $row['quelle_band'];
					$resultData['auflage'] = $row['quelle_auflage'];
					$resultData['autor_or_herausgeber'] = $row['quelle_autor_or_herausgeber'];

					$status = "success";
					$message = "success";
				}
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