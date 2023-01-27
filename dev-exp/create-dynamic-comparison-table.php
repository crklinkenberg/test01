<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$resultData = array();
	$status = 'success';
	$message = '';
	try {
		$comparisonTable = (isset($_SERVER['argv'][1]) AND $_SERVER['argv'][1] != "") ? $_SERVER['argv'][1] : "";
		if($comparisonTable != ""){
			$comparisonHighestMatches = $comparisonTable."_highest_matches";

			$checkIfExist = mysqli_query($db,"SELECT id, table_name, similarity_rate, comparison_language, arznei_id, comparison_option, initial_source, comparing_sources, status FROM pre_comparison_master_data  WHERE table_name = '".$comparisonTable."'");
			if(mysqli_num_rows($checkIfExist) != 0){
				$stopWords = array();
				$stopWords = getStopWords();
				
				$masterData = mysqli_fetch_assoc($checkIfExist);
				$preComparisonMasterDatainsertedId = (isset($masterData['id']) AND $masterData['id'] != "") ? $masterData['id'] : "";

				$arzneiId = (isset($masterData['arznei_id']) AND $masterData['arznei_id'] != "") ? $masterData['arznei_id'] : "";
				$initialSourceId = (isset($masterData['initial_source']) AND $masterData['initial_source'] != "") ? $masterData['initial_source'] : "";
				$comparingSourceIds = (isset($masterData['comparing_sources']) AND $masterData['comparing_sources'] != "") ? explode(",", $masterData['comparing_sources']) : array();
				if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
					$comparingSourceIds = explode(",", $comparingSourceIds);
				$comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";
				$similarityRate = (isset($masterData['similarity_rate']) AND $masterData['similarity_rate'] != "") ? $masterData['similarity_rate'] : 20;
				$comparisonOption = (isset($masterData['comparison_option']) AND $masterData['comparison_option'] != "") ? $masterData['comparison_option'] : 1;
				$comparisonLanguage = (isset($masterData['comparison_language']) AND $masterData['comparison_language'] != "") ? $masterData['comparison_language'] : "";
				// Saved comparisons saved connections Array
				$savedComparisonConnectionsArr = array();

				$errorCount = 0;
				if($arzneiId == ""){
					$errorCount = 1;
				}
				if($initialSourceId == ""){
					$errorCount = 1;
				}
				if($comparingSourcesInsertString == ""){
					$errorCount = 1;
				}
				if($comparisonLanguage == ""){
					$errorCount = 1;
				}

				if($errorCount == 0){
		            // Creating Comparasion table
		            $createComparasionTable = "CREATE TABLE $comparisonTable ( 
										id INT NOT NULL AUTO_INCREMENT,
										symptom_id INT NULL DEFAULT NULL,
										initial_symptom_id INT NULL DEFAULT NULL,
										is_initial_symptom ENUM ('0','1') DEFAULT '0',
										quelle_code VARCHAR(100) NULL DEFAULT NULL,
										quelle_titel VARCHAR(255) NULL DEFAULT NULL,
										quelle_type_id INT NULL DEFAULT NULL COMMENT '1 = Bücher or Quelle, 2 = Zeitschriften, 3 = Saved comparison quelle',
										quelle_jahr VARCHAR(50) NULL DEFAULT NULL COMMENT 'Year',
										quelle_band VARCHAR(10) NULL DEFAULT NULL,
										quelle_auflage VARCHAR(50) NULL DEFAULT NULL COMMENT 'edition',
										quelle_autor_or_herausgeber VARCHAR(50) NULL DEFAULT NULL COMMENT 'Author or edition',
										arznei_id INT NULL DEFAULT NULL,
										quelle_id INT NULL DEFAULT NULL,
										original_quelle_id INT NULL DEFAULT NULL,
										Symptomnummer INT NULL DEFAULT NULL,
										SeiteOriginalVon INT NULL DEFAULT NULL,
										SeiteOriginalBis INT NULL DEFAULT NULL,
										final_version_de TEXT DEFAULT NULL,
										final_version_en TEXT DEFAULT NULL,
										Beschreibung_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as it is found in the doc without any modifications',
										Beschreibung_en TEXT DEFAULT NULL,
										BeschreibungOriginal_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will remain unchange after import)',
										BeschreibungOriginal_en TEXT DEFAULT NULL,
										BeschreibungFull_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will be use in operations, and this can get change by edit operation)',
										BeschreibungFull_en TEXT DEFAULT NULL,
										BeschreibungPlain_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as plain means without any html tags',
										BeschreibungPlain_en TEXT DEFAULT NULL,
										searchable_text_de TEXT DEFAULT NULL COMMENT 'This is the searchable symptom string used in comparison. Only symptom string is kept here excluding other data pruefer, remedy, time data etc.',
										searchable_text_en TEXT DEFAULT NULL,
										bracketedString_de VARCHAR(255) NULL DEFAULT NULL,
										bracketedString_en VARCHAR(255) NULL DEFAULT NULL,
										timeString_de VARCHAR(255) NULL DEFAULT NULL,
										timeString_en VARCHAR(255) NULL DEFAULT NULL,
										initial_source_original_language VARCHAR(50) NULL DEFAULT NULL,
										comparing_source_original_language VARCHAR(50) NULL DEFAULT NULL,
										Fussnote VARCHAR(255) NULL DEFAULT NULL COMMENT 'Footnote',
										EntnommenAus VARCHAR(255) NULL DEFAULT NULL COMMENT 'Full literature reference text',
										Verweiss VARCHAR(255) NULL DEFAULT NULL,
										BereichID VARCHAR(255) NULL DEFAULT NULL,
										Kommentar TEXT DEFAULT NULL,
										Unklarheiten TEXT DEFAULT NULL,
										Remedy VARCHAR(255) NULL DEFAULT NULL,
										symptom_of_different_remedy VARCHAR(255) NULL DEFAULT NULL,
										subChapter VARCHAR(255) NULL DEFAULT NULL,
										subSubChapter VARCHAR(255) NULL DEFAULT NULL,
										synonym_word VARCHAR(1200) NULL DEFAULT NULL,
										strict_synonym VARCHAR(1200) NULL DEFAULT NULL,
										synonym_partial_1 VARCHAR(1200) NULL DEFAULT NULL,
										synonym_partial_2 VARCHAR(1200) NULL DEFAULT NULL,
										synonym_general VARCHAR(1200) NULL DEFAULT NULL,
										synonym_minor VARCHAR(1200) NULL DEFAULT NULL,
										synonym_nn VARCHAR(1200) NULL DEFAULT NULL,
										comparison_matched_synonyms VARCHAR(1200) NULL DEFAULT NULL,
										symptom_edit_comment VARCHAR(255) NULL DEFAULT NULL,
										is_excluded_in_comparison TINYINT(1) NULL DEFAULT 0 COMMENT 'Checking is symptom excluded in the comparison process',
										is_final_version_available TINYINT(1) NULL DEFAULT 0 COMMENT '0 = No, 1 = Connect edit, 2 = Paste edit',
										-- symptom TEXT DEFAULT NULL,
										matched_percentage INT NOT NULL,
										connected_with TEXT DEFAULT NULL,
										pasted_with TEXT DEFAULT NULL,
										non_secure_connect ENUM ('0','1') DEFAULT '0',
										non_secure_paste ENUM ('0','1') DEFAULT '0',
										connect_edited INT NULL DEFAULT NULL,
										paste_edited INT NULL DEFAULT NULL,
										swap INT DEFAULT NULL,
										swap_value_en TEXT DEFAULT NULL,
										swap_value_de TEXT DEFAULT NULL,
										swap_ce INT DEFAULT NULL,
										swap_value_ce_en TEXT DEFAULT NULL,
										swap_value_ce_de TEXT DEFAULT NULL,
										marked ENUM ('0','1') DEFAULT '0',
										connection ENUM ('0','1') DEFAULT '0',
										gen_ns ENUM ('0','1') DEFAULT '0',
										gen_ns_comment VARCHAR(255) NULL DEFAULT NULL,
										ip_address VARCHAR(255) NULL DEFAULT NULL,
										stand TIMESTAMP NULL DEFAULT NULL COMMENT 'updated_at',
										bearbeiter_id INT NULL DEFAULT NULL COMMENT 'editor_id',
										ersteller_datum TIMESTAMP NULL DEFAULT NULL COMMENT 'created_at',
										ersteller_id INT NULL DEFAULT NULL COMMENT 'creator_id',
										PRIMARY KEY (id),
										INDEX (symptom_id),
										INDEX (initial_symptom_id),
										INDEX (is_initial_symptom),
										INDEX (marked),
										INDEX (gen_ns),
										INDEX (matched_percentage)
									) ENGINE = InnoDB DEFAULT CHARSET = utf8";
					mysqli_query($db, $createComparasionTable);

					$createHighestMatchTable = "CREATE TABLE $comparisonHighestMatches ( 
										id INT NOT NULL AUTO_INCREMENT,
										comparison_table_id INT NULL DEFAULT NULL,
										symptom_id INT NULL DEFAULT NULL,
										final_version_de TEXT DEFAULT NULL,
										final_version_en TEXT DEFAULT NULL,
										Beschreibung_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as it is found in the doc without any modifications',
										Beschreibung_en TEXT DEFAULT NULL,
										BeschreibungOriginal_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will remain unchange after import)',
										BeschreibungOriginal_en TEXT DEFAULT NULL,
										BeschreibungFull_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here with applicable modifications (This string will be use in operations, and this can get change by edit operation)',
										BeschreibungFull_en TEXT DEFAULT NULL,
										BeschreibungPlain_de TEXT DEFAULT NULL COMMENT 'The whole symptom string is kept here as plain means without any html tags',
										BeschreibungPlain_en TEXT DEFAULT NULL,
										searchable_text_de TEXT DEFAULT NULL COMMENT 'This is the searchable symptom string used in comparison. Only symptom string is kept here excluding other data pruefer, remedy, time data etc.',
										searchable_text_en TEXT DEFAULT NULL,
										synonym_word VARCHAR(1200) NULL DEFAULT NULL,
										strict_synonym VARCHAR(1200) NULL DEFAULT NULL,
										synonym_partial_1 VARCHAR(1200) NULL DEFAULT NULL,
										synonym_partial_2 VARCHAR(1200) NULL DEFAULT NULL,
										synonym_general VARCHAR(1200) NULL DEFAULT NULL,
										synonym_minor VARCHAR(1200) NULL DEFAULT NULL,
										synonym_nn VARCHAR(1200) NULL DEFAULT NULL,
										comparison_matched_synonyms VARCHAR(1200) NULL DEFAULT NULL,
										matched_percentage INT NOT NULL,
										arznei_id INT NULL DEFAULT NULL,
										quelle_id INT NULL DEFAULT NULL,
										original_quelle_id INT NULL DEFAULT NULL,
										quelle_jahr VARCHAR(50) NULL DEFAULT NULL COMMENT 'Year',
										Kommentar TEXT DEFAULT NULL,
										Fussnote VARCHAR(255) NULL DEFAULT NULL COMMENT 'Footnote',
										quelle_code VARCHAR(100) NULL DEFAULT NULL,
										is_excluded_in_comparison TINYINT(1) NULL DEFAULT 0 COMMENT 'Checking is symptom excluded in the comparison process',
										is_final_version_available TINYINT(1) NULL DEFAULT 0 COMMENT '0 = No, 1 = Connect edit, 2 = Paste edit',
										swap INT DEFAULT NULL,
										swap_value_en TEXT DEFAULT NULL,
										swap_value_de TEXT DEFAULT NULL,
										swap_ce INT DEFAULT NULL,
										swap_value_ce_en TEXT DEFAULT NULL,
										swap_value_ce_de TEXT DEFAULT NULL,
										PRIMARY KEY (id),
										INDEX (symptom_id),
										INDEX (matched_percentage)
									) ENGINE = InnoDB DEFAULT CHARSET = utf8";
					mysqli_query($db, $createHighestMatchTable);

					$matchedSymptomIds = array();
					$runningInitialSymptomId = "";

					$savedinitialSourceTable = "";
					// Checking if initial source is a saved comparison
					$savedDataConnectionsTableOfInitialSource = "";
					$initialQuelleResult = mysqli_query($db,"SELECT Q.quelle_type_id, PCM.table_name FROM quelle AS Q JOIN pre_comparison_master_data AS PCM ON Q.quelle_id = PCM.quelle_id WHERE Q.quelle_id = '".$initialSourceId."'");
					if(mysqli_num_rows($initialQuelleResult) > 0){
						$iniQuelleRow = mysqli_fetch_assoc($initialQuelleResult);
						$savedinitialSourceTable = ($iniQuelleRow['quelle_type_id'] == 3) ? trim($iniQuelleRow['table_name'])."_completed" : "";
						$savedDataConnectionsTableOfInitialSource = ($iniQuelleRow['quelle_type_id'] == 3) ? trim($iniQuelleRow['table_name'])."_connections" : "";
					}

					if($savedDataConnectionsTableOfInitialSource != "" AND !in_array($savedDataConnectionsTableOfInitialSource, $savedComparisonConnectionsArr)){
						$savedComparisonConnectionsArr[] = $savedDataConnectionsTableOfInitialSource;
					}

					// $InitialQuelleResult = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.quelle_code, quelle_import_test.BeschreibungPlain_$comparisonLanguage as ini_symptom FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.quelle_id = ".$initialSourceId);
					if($savedinitialSourceTable != "")
					{
						$InitialQuelleResult = mysqli_query($db,"SELECT QI.id, QI.symptom_id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.bracketedString_de, QI.bracketedString_en, QI.timeString_de, QI.timeString_en, QI.symptom_edit_comment, QI.is_excluded_in_comparison, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy,QI.connection, QI.swap, QI.swap_value_en,QI.swap_value_de,QI.swap_ce,QI.swap_value_ce_de,QI.swap_value_ce_en, QI.symptom_of_different_remedy, QI.subChapter, QI.subSubChapter, QI.synonym_word, QI.strict_synonym, QI.synonym_partial_1, QI.synonym_partial_2, QI.synonym_general, QI.synonym_minor, QI.synonym_nn, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, QI.quelle_jahr AS jahr, Q.band, Q.auflage, Q.autor_or_herausgeber, QI.initial_source_original_language FROM ".$savedinitialSourceTable." as QI JOIN quelle_import_master AS QIM ON QI.quelle_id = QIM.quelle_id LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.arznei_id = '".$arzneiId."' AND QI.quelle_id = '".$initialSourceId."'");
					} else {
						$InitialQuelleResult = mysqli_query($db,"SELECT QI.id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.original_symptom_id, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.bracketedString_de, QI.bracketedString_en, QI.timeString_de, QI.timeString_en, QI.symptom_edit_comment, QI.is_excluded_in_comparison, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.subChapter, QI.subSubChapter, QI.synonym_word, QI.strict_synonym, QI.synonym_partial_1, QI.synonym_partial_2, QI.synonym_general, QI.synonym_minor, QI.synonym_nn, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber, Q.sprache FROM quelle_import_test as QI JOIN quelle_import_master AS QIM ON QI.master_id = QIM.id LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.arznei_id = '".$arzneiId."' AND QI.quelle_id = '".$initialSourceId."'");
					}
					if(mysqli_num_rows($InitialQuelleResult) > 0){
						// $myfile = fopen("comparison-writing/".microtime()."-com-process.txt", "w") or die("Unable to open file!");
						while($iniSymRow = mysqli_fetch_array($InitialQuelleResult)){

							// $txt = date('m/d/Y h:i:s a', time())." Test\n";
							// fwrite($myfile, $txt);
							// Taking Symptom id if it is saved comparison
							if($savedinitialSourceTable != "")
								$applicableInitialSymptomId = $iniSymRow['symptom_id'];
							else
								$applicableInitialSymptomId = $iniSymRow['id'];

							// Selecting symptom string depending on comparison option that user selected
							$symptomString_de = "";
							$symptomString_en = "";
							if($iniSymRow['swap'] != 0){
								if($comparisonLanguage == "de")
									$symptomString_de =  $iniSymRow['swap_value_de'];
								else
									$symptomString_en =  $iniSymRow['swap_value_en'];
							}else if($iniSymRow['swap_ce'] != 0){
								if($comparisonLanguage == "de")
									$symptomString_de =  $iniSymRow['swap_value_ce_de'];
								else
									$symptomString_en =  $iniSymRow['swap_value_ce_en'];
							}else{
								if($iniSymRow['is_final_version_available'] != 0){
									$symptomString_de =  $iniSymRow['final_version_de'];
									$symptomString_en =  $iniSymRow['final_version_en'];
								} else{
									if($comparisonOption == 1){
										$symptomString_de =  ($iniSymRow['searchable_text_de'] != "") ? $iniSymRow['searchable_text_de'] : "";
										$symptomString_en =  ($iniSymRow['searchable_text_en'] != "") ? $iniSymRow['searchable_text_en'] : "";
									}else{
										$symptomString_de =  ($iniSymRow['BeschreibungFull_de'] != "") ? $iniSymRow['BeschreibungFull_de'] : "";
										$symptomString_en =  ($iniSymRow['BeschreibungFull_en'] != "") ? $iniSymRow['BeschreibungFull_en'] : "";
									}
								}
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
								$symptomString_de = convertTheSymptom($symptomString_de, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], 0, 0, $applicableInitialSymptomId);
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
								$symptomString_en = convertTheSymptom($symptomString_en, $iniSymRow['original_quelle_id'], $iniSymRow['arznei_id'], 0, 0, $applicableInitialSymptomId);
							}

							if($comparisonLanguage == "en")
								$ini = $symptomString_en;
							else
								$ini = $symptomString_de;

							$runningInitialSymptomId = $applicableInitialSymptomId;
							
							if($savedinitialSourceTable != ""){
								$originInitialSourceLanguage = "";
								if($iniSymRow['initial_source_original_language'] == "de")
									$originInitialSourceLanguage = "de";
								else if($iniSymRow['initial_source_original_language'] == "en") 
									$originInitialSourceLanguage = "en";
							}else{
								$originInitialSourceLanguage = "";
								if($iniSymRow['sprache'] == "deutsch")
									$originInitialSourceLanguage = "de";
								else if($iniSymRow['sprache'] == "englisch") 
									$originInitialSourceLanguage = "en";
							}	
							
							//new edit
							if(isset($iniSymRow['connection']) AND $iniSymRow['connection']!=NULL)
							{
								$connectionValue = $iniSymRow['connection'];
							}
							else
							{
								$connectionValue ="0";
							}

							$iniInsertData = array();
							$iniInsertData['quelle_code'] = mysqli_real_escape_string($db, $iniSymRow['quelle_code']);
							$iniInsertData['titel'] = mysqli_real_escape_string($db, $iniSymRow['titel']);
							$iniInsertData['quelle_type_id'] = mysqli_real_escape_string($db, $iniSymRow['quelle_type_id']);
							$iniInsertData['jahr'] = mysqli_real_escape_string($db, $iniSymRow['jahr']);
							$iniInsertData['band'] = mysqli_real_escape_string($db, $iniSymRow['band']);
							$iniInsertData['auflage'] = mysqli_real_escape_string($db, $iniSymRow['auflage']);
							$iniInsertData['autor_or_herausgeber'] = mysqli_real_escape_string($db, $iniSymRow['autor_or_herausgeber']);
							$iniInsertData['arznei_id'] = mysqli_real_escape_string($db, $iniSymRow['arznei_id']);
							$iniInsertData['quelle_id'] = mysqli_real_escape_string($db, $iniSymRow['quelle_id']);
							$iniInsertData['original_quelle_id'] = mysqli_real_escape_string($db, $iniSymRow['original_quelle_id']);
							$iniInsertData['Symptomnummer'] = mysqli_real_escape_string($db, $iniSymRow['Symptomnummer']);
							$iniInsertData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalVon']);
							$iniInsertData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $iniSymRow['SeiteOriginalBis']);
							$iniInsertData['final_version_de'] = mysqli_real_escape_string($db, $iniSymRow['final_version_de']);
							$iniInsertData['final_version_en'] = mysqli_real_escape_string($db, $iniSymRow['final_version_en']);
							$iniInsertData['Beschreibung_de'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_de']);
							$iniInsertData['Beschreibung_en'] = mysqli_real_escape_string($db, $iniSymRow['Beschreibung_en']);
							$iniInsertData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_de']);
							$iniInsertData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungOriginal_en']);
							$iniInsertData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_de']);
							$iniInsertData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungFull_en']);
							$iniInsertData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_de']);
							$iniInsertData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $iniSymRow['BeschreibungPlain_en']);
							$iniInsertData['searchable_text_de'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_de']);
							$iniInsertData['searchable_text_en'] = mysqli_real_escape_string($db, $iniSymRow['searchable_text_en']);
							$iniInsertData['bracketedString_de'] = mysqli_real_escape_string($db, $iniSymRow['bracketedString_de']);
							$iniInsertData['bracketedString_en'] = mysqli_real_escape_string($db, $iniSymRow['bracketedString_en']);
							$iniInsertData['timeString_de'] = mysqli_real_escape_string($db, $iniSymRow['timeString_de']);
							$iniInsertData['timeString_en'] = mysqli_real_escape_string($db, $iniSymRow['timeString_en']);
							$iniInsertData['Fussnote'] = mysqli_real_escape_string($db, $iniSymRow['Fussnote']);
							$iniInsertData['EntnommenAus'] = mysqli_real_escape_string($db, $iniSymRow['EntnommenAus']);
							$iniInsertData['Verweiss'] = mysqli_real_escape_string($db, $iniSymRow['Verweiss']);
							$iniInsertData['BereichID'] = mysqli_real_escape_string($db, $iniSymRow['BereichID']);
							$iniInsertData['Kommentar'] = mysqli_real_escape_string($db, $iniSymRow['Kommentar']);
							$iniInsertData['Unklarheiten'] = mysqli_real_escape_string($db, $iniSymRow['Unklarheiten']);
							$iniInsertData['Remedy'] = mysqli_real_escape_string($db, $iniSymRow['Remedy']);
							$iniInsertData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $iniSymRow['symptom_of_different_remedy']);
							$iniInsertData['subChapter'] = mysqli_real_escape_string($db, $iniSymRow['subChapter']);
							$iniInsertData['subSubChapter'] = mysqli_real_escape_string($db, $iniSymRow['subSubChapter']);
							$iniInsertData['synonym_word'] = mysqli_real_escape_string($db, $iniSymRow['synonym_word']);
							$iniInsertData['strict_synonym'] = mysqli_real_escape_string($db, $iniSymRow['strict_synonym']);
							$iniInsertData['synonym_partial_1'] = mysqli_real_escape_string($db, $iniSymRow['synonym_partial_1']);
							$iniInsertData['synonym_partial_2'] = mysqli_real_escape_string($db, $iniSymRow['synonym_partial_2']);
							$iniInsertData['synonym_general'] = mysqli_real_escape_string($db, $iniSymRow['synonym_general']);
							$iniInsertData['synonym_minor'] = mysqli_real_escape_string($db, $iniSymRow['synonym_minor']);
							$iniInsertData['synonym_nn'] = mysqli_real_escape_string($db, $iniSymRow['synonym_nn']);
							$iniInsertData['symptom_edit_comment'] = mysqli_real_escape_string($db, $iniSymRow['symptom_edit_comment']);
							$iniInsertData['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $iniSymRow['is_excluded_in_comparison']);
							$iniInsertData['is_final_version_available'] = mysqli_real_escape_string($db, $iniSymRow['is_final_version_available']);
							$iniInsertData['swap'] = mysqli_real_escape_string($db, $iniSymRow['swap']);
							$iniInsertData['swap_value_en'] = mysqli_real_escape_string($db, $iniSymRow['swap_value_en']);
							$iniInsertData['swap_value_de'] = mysqli_real_escape_string($db, $iniSymRow['swap_value_de']);
							$iniInsertData['swap_ce'] = mysqli_real_escape_string($db, $iniSymRow['swap_ce']);
							$iniInsertData['swap_value_ce_en'] = mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_en']);
							$iniInsertData['swap_value_ce_de'] = mysqli_real_escape_string($db, $iniSymRow['swap_value_ce_de']);
							//new edit
							$insertInitial="INSERT INTO $comparisonTable (symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, initial_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, symptom_edit_comment, is_excluded_in_comparison, is_final_version_available, matched_percentage,connection,swap,swap_value_en,swap_value_de,swap_ce,swap_value_ce_en,swap_value_ce_de, ersteller_datum) VALUES (NULLIF('".$applicableInitialSymptomId."', ''), '1', NULLIF('".$iniInsertData['quelle_code']."', ''), NULLIF('".$iniInsertData['titel']."', ''), NULLIF('".$iniInsertData['quelle_type_id']."', ''), NULLIF('".$iniInsertData['jahr']."', ''), NULLIF('".$iniInsertData['band']."', ''), NULLIF('".$iniInsertData['auflage']."', ''), NULLIF('".$iniInsertData['autor_or_herausgeber']."', ''), NULLIF('".$iniInsertData['arznei_id']."', ''), NULLIF('".$iniInsertData['quelle_id']."', ''), NULLIF('".$iniInsertData['original_quelle_id']."', ''), NULLIF('".$iniInsertData['Symptomnummer']."', ''), NULLIF('".$iniInsertData['SeiteOriginalVon']."', ''), NULLIF('".$iniInsertData['SeiteOriginalBis']."', ''), NULLIF('".$iniInsertData['final_version_de']."', ''), NULLIF('".$iniInsertData['final_version_en']."', ''), NULLIF('".$iniInsertData['Beschreibung_de']."', ''), NULLIF('".$iniInsertData['Beschreibung_en']."', ''), NULLIF('".$iniInsertData['BeschreibungOriginal_de']."', ''), NULLIF('".$iniInsertData['BeschreibungOriginal_en']."', ''), NULLIF('".$iniInsertData['BeschreibungFull_de']."', ''), NULLIF('".$iniInsertData['BeschreibungFull_en']."', ''), NULLIF('".$iniInsertData['BeschreibungPlain_de']."', ''), NULLIF('".$iniInsertData['BeschreibungPlain_en']."', ''), NULLIF('".$iniInsertData['searchable_text_de']."', ''), NULLIF('".$iniInsertData['searchable_text_en']."', ''), NULLIF('".$iniInsertData['bracketedString_de']."', ''), NULLIF('".$iniInsertData['bracketedString_en']."', ''), NULLIF('".$iniInsertData['timeString_de']."', ''), NULLIF('".$iniInsertData['timeString_en']."', ''), NULLIF('".$originInitialSourceLanguage."', ''), NULLIF('".$iniInsertData['Fussnote']."', ''), NULLIF('".$iniInsertData['EntnommenAus']."', ''), NULLIF('".$iniInsertData['Verweiss']."', ''), NULLIF('".$iniInsertData['BereichID']."', ''), NULLIF('".$iniInsertData['Kommentar']."', ''), NULLIF('".$iniInsertData['Unklarheiten']."', ''), NULLIF('".$iniInsertData['Remedy']."', ''), NULLIF('".$iniInsertData['symptom_of_different_remedy']."', ''), NULLIF('".$iniInsertData['subChapter']."', ''), NULLIF('".$iniInsertData['subSubChapter']."', ''), NULLIF('".$iniInsertData['synonym_word']."', ''), NULLIF('".$iniInsertData['strict_synonym']."', ''), NULLIF('".$iniInsertData['synonym_partial_1']."', ''), NULLIF('".$iniInsertData['synonym_partial_2']."', ''), NULLIF('".$iniInsertData['synonym_general']."', ''), NULLIF('".$iniInsertData['synonym_minor']."', ''), NULLIF('".$iniInsertData['synonym_nn']."', ''), NULLIF('".$iniInsertData['symptom_edit_comment']."', ''), NULLIF('".$iniInsertData['is_excluded_in_comparison']."', ''), NULLIF('".$iniInsertData['is_final_version_available']."', ''), 1000, NULLIF('".$connectionValue."', ''),NULLIF('".$iniInsertData['swap']."', ''),NULLIF('".$iniInsertData['swap_value_en']."', ''),NULLIF('".$iniInsertData['swap_value_de']."', ''),NULLIF('".$iniInsertData['swap_ce']."', ''),NULLIF('".$iniInsertData['swap_value_ce_en']."', ''),NULLIF('".$iniInsertData['swap_value_ce_de']."', ''),NULLIF('".$date."', ''))";
							$db->query($insertInitial);
							// Inserting only initials 
							$insertedId = $db->insert_id;

							if($iniSymRow['is_excluded_in_comparison'] != 1){
								// Collecting Synonyms of this Symptom START
								$initialSymptomsAllSynonyms = array();
								$wordSynonyms = array();
								$strictSynonyms = array();
								$partial1Synonyms = array();
								$partial2Synonyms = array();
								$generalSynonyms = array();
								$minorSynonyms = array();
								$nnSynonyms = array();
								if(!empty($iniSymRow['synonym_word'])){
									$wordSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_word']);
									$wordSynonyms = (!empty($wordSynonyms)) ? $wordSynonyms : array(); 
								}
								if(!empty($iniSymRow['strict_synonym'])){
									$strictSynonyms = getAllOrganizeSynonyms($iniSymRow['strict_synonym']);
									$strictSynonyms = (!empty($strictSynonyms)) ? $strictSynonyms : array(); 
								}
								if(!empty($iniSymRow['synonym_partial_1'])){
									$partial1Synonyms = getAllOrganizeSynonyms($iniSymRow['synonym_partial_1']);
									$partial1Synonyms = (!empty($partial1Synonyms)) ? $partial1Synonyms : array(); 
								}
								if(!empty($iniSymRow['synonym_partial_2'])){
									$partial2Synonyms = getAllOrganizeSynonyms($iniSymRow['synonym_partial_2']);
									$partial2Synonyms = (!empty($partial2Synonyms)) ? $partial2Synonyms : array(); 
								}
								if(!empty($iniSymRow['synonym_general'])){
									$generalSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_general']);
									$generalSynonyms = (!empty($generalSynonyms)) ? $generalSynonyms : array(); 
								}
								if(!empty($iniSymRow['synonym_minor'])){
									$minorSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_minor']);
									$minorSynonyms = (!empty($minorSynonyms)) ? $minorSynonyms : array(); 
								}
								if(!empty($iniSymRow['synonym_nn'])){
									$nnSynonyms = getAllOrganizeSynonyms($iniSymRow['synonym_nn']);
									$nnSynonyms = (!empty($nnSynonyms)) ? $nnSynonyms : array(); 
								}
								$initialSymptomsAllSynonyms = array_merge($wordSynonyms, $strictSynonyms, $partial1Synonyms, $partial2Synonyms, $generalSynonyms, $minorSynonyms, $nnSynonyms);
								// Collecting Synonyms of this Symptom END

							    $comparingSymptomsArray = array();
							    if(!empty($comparingSourceIds)){
							    	foreach ($comparingSourceIds as $comKey => $comVal) {
							    		$savedComparingSourceTable = "";
							    		$savedDataConnectionsTableOfComparingSource = "";
										// Checking if initial source is a saved comparison
										$comQuelleResult = mysqli_query($db,"SELECT Q.quelle_type_id, PCM.table_name FROM quelle AS Q JOIN pre_comparison_master_data AS PCM ON Q.quelle_id = PCM.quelle_id WHERE Q.quelle_id = '".$comVal."'");
										if(mysqli_num_rows($comQuelleResult) > 0){
											$comQuelleRow = mysqli_fetch_assoc($comQuelleResult);
											$savedComparingSourceTable = ($comQuelleRow['quelle_type_id'] == 3) ? trim($comQuelleRow['table_name'])."_completed" : "";
											$savedDataConnectionsTableOfComparingSource = ($comQuelleRow['quelle_type_id'] == 3) ? trim($comQuelleRow['table_name'])."_connections" : "";
										}

										if($savedDataConnectionsTableOfComparingSource != "" AND !in_array($savedDataConnectionsTableOfComparingSource, $savedComparisonConnectionsArr)){
											$savedComparisonConnectionsArr[] = $savedDataConnectionsTableOfComparingSource;
										}

							    		// Comparing symptoms section
									    // $quelleComparingSymptomResult = mysqli_query($db,"SELECT quelle_import_test.id, quelle_import_test.quelle_code, quelle_import_test.BeschreibungPlain_$comparisonLanguage as com_symptom FROM quelle_import_test JOIN quelle_import_master ON quelle_import_test.master_id = quelle_import_master.id WHERE quelle_import_test.quelle_id IN (".$comparingSourcesInsertString.")");
									    if($savedComparingSourceTable != ""){
									    	$quelleComparingSymptomResult = mysqli_query($db,"SELECT QI.id, QI.symptom_id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.bracketedString_de, QI.bracketedString_en, QI.timeString_de, QI.timeString_en, QI.symptom_edit_comment, QI.is_excluded_in_comparison, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar,QI.connection,QI.swap, QI.swap_value_en,QI.swap_value_de,QI.swap_ce,QI.swap_value_ce_de,QI.swap_value_ce_en, QI.Remedy, QI.symptom_of_different_remedy, QI.subChapter, QI.subSubChapter, QI.synonym_word, QI.strict_synonym, QI.synonym_partial_1, QI.synonym_partial_2, QI.synonym_general, QI.synonym_minor, QI.synonym_nn, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, QI.quelle_jahr AS jahr, Q.band, Q.auflage, Q.autor_or_herausgeber, QI.initial_source_original_language FROM ".$savedComparingSourceTable." as QI JOIN quelle_import_master AS QIM ON QI.quelle_id = QIM.quelle_id LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.arznei_id = '".$arzneiId."' AND QI.quelle_id = '".$comVal."'");
									    } else {
									    	$quelleComparingSymptomResult = mysqli_query($db,"SELECT QI.id, QI.Symptomnummer, QI.SeiteOriginalVon, QI.SeiteOriginalBis, QI.original_symptom_id, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.quelle_code, QI.final_version_de, QI.final_version_en, QI.Beschreibung_de, QI.Beschreibung_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.bracketedString_de, QI.bracketedString_en, QI.timeString_de, QI.timeString_en, QI.symptom_edit_comment, QI.is_excluded_in_comparison, QI.is_final_version_available, QI.Fussnote, QI.EntnommenAus, QI.Verweiss, QI.Kommentar, QI.Remedy, QI.symptom_of_different_remedy, QI.subChapter, QI.subSubChapter, QI.synonym_word, QI.strict_synonym, QI.synonym_partial_1, QI.synonym_partial_2, QI.synonym_general, QI.synonym_minor, QI.synonym_nn, QI.BereichID, QI.Unklarheiten, Q.quelle_type_id, Q.code, Q.titel, Q.jahr, Q.band, Q.auflage, Q.autor_or_herausgeber, Q.sprache FROM quelle_import_test as QI JOIN quelle_import_master AS QIM ON QI.master_id = QIM.id LEFT JOIN quelle as Q ON QI.quelle_id = Q.quelle_id WHERE QI.arznei_id = '".$arzneiId."' AND QI.quelle_id = '".$comVal."'");
									    }
										while($comparingSymRow = mysqli_fetch_array($quelleComparingSymptomResult))
										{
											// Taking Symptom id if it is saved comparison
											if($savedComparingSourceTable != "")
												$applicableComparingSymptomId = $comparingSymRow['symptom_id'];
											else
												$applicableComparingSymptomId = $comparingSymRow['id'];

											// Selecting symptom string depending on comparison option that user selected
											$symptomString_de = "";
											$symptomString_en = "";

											if($comparingSymRow['swap'] != 0){
												if($comparisonLanguage == "de")
													$symptomString_de =  $comparingSymRow['swap_value_de'];
												else
													$symptomString_en =  $comparingSymRow['swap_value_en'];
											}else if($comparingSymRow['swap_ce'] != 0){
												if($comparisonLanguage == "de")
													$symptomString_de =  $comparingSymRow['swap_value_ce_de'];
												else
													$symptomString_en =  $comparingSymRow['swap_value_ce_en'];
											}else{
												if($comparingSymRow['is_final_version_available'] != 0){
													$symptomString_de =  $comparingSymRow['final_version_de'];
													$symptomString_en =  $comparingSymRow['final_version_en'];
												} else {
													if($comparisonOption == 1){
														$symptomString_de =  ($comparingSymRow['searchable_text_de'] != "") ? $comparingSymRow['searchable_text_de'] : "";
														$symptomString_en =  ($comparingSymRow['searchable_text_en'] != "") ? $comparingSymRow['searchable_text_en'] : "";
													}else{
														$symptomString_de =  ($comparingSymRow['BeschreibungFull_de'] != "") ? $comparingSymRow['BeschreibungFull_de'] : "";
														$symptomString_en =  ($comparingSymRow['BeschreibungFull_en'] != "") ? $comparingSymRow['BeschreibungFull_en'] : "";
													}
												}
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
												$symptomString_de = convertTheSymptom($symptomString_de, $comparingSymRow['original_quelle_id'], $comparingSymRow['arznei_id'], 0, 0, $applicableComparingSymptomId);
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
												$symptomString_en = convertTheSymptom($symptomString_en, $comparingSymRow['original_quelle_id'], $comparingSymRow['arznei_id'], 0, 0, $applicableComparingSymptomId);
											}

											if($comparisonLanguage == "en")
												$com = $symptomString_en;
											else
												$com = $symptomString_de;
											// $com = $symptomString_.$comparisonLanguage;
											// $resultArray = newComareSymptom($ini, $com);
											$resultArray = compareSymptomWithSynonyms($ini, $com, $stopWords, $initialSymptomsAllSynonyms);
											$percentage = (isset($resultArray['percentage'])) ? $resultArray['percentage'] : 0;
											$comparisonMatchedSynonyms = (isset($resultArray['comparison_matched_synonyms'])) ? $resultArray['comparison_matched_synonyms'] : array();
									
											if($savedComparingSourceTable != ""){
												$originComparingSourceLanguage = "";
												if($comparingSymRow['initial_source_original_language'] == "de")
													$originComparingSourceLanguage = "de";
												else if($comparingSymRow['initial_source_original_language'] == "en") 
													$originComparingSourceLanguage = "en";
											}else{
												$originComparingSourceLanguage = "";
												if($comparingSymRow['sprache'] == "deutsch")
													$originComparingSourceLanguage = "de";
												else if($comparingSymRow['sprache'] == "englisch") 
													$originComparingSourceLanguage = "en";
											}

											if (!in_array($applicableComparingSymptomId, $matchedSymptomIds))
												array_push($matchedSymptomIds, $applicableComparingSymptomId);

											//new edit
											if(isset($comparingSymRow['connection']) AND $comparingSymRow['connection']!=NULL)
											{
												$connectionValue = $comparingSymRow['connection'];
											}
											else
											{
												$connectionValue ="0";
											}

											$data = array();
											$data['symptom_id'] = $applicableComparingSymptomId;
											$data['initial_symptom_id'] = $runningInitialSymptomId;
											$data['is_initial_symptom'] = '0';
											$data['quelle_code'] = mysqli_real_escape_string($db, $comparingSymRow['quelle_code']);
											$data['quelle_titel'] = mysqli_real_escape_string($db, $comparingSymRow['titel']);
											$data['quelle_type_id'] = mysqli_real_escape_string($db, $comparingSymRow['quelle_type_id']);
											$data['quelle_jahr'] = mysqli_real_escape_string($db, $comparingSymRow['jahr']);
											$data['quelle_band'] = mysqli_real_escape_string($db, $comparingSymRow['band']);
											$data['quelle_auflage'] = mysqli_real_escape_string($db, $comparingSymRow['auflage']);
											$data['quelle_autor_or_herausgeber'] = mysqli_real_escape_string($db, $comparingSymRow['autor_or_herausgeber']);
											$data['arznei_id'] = $comparingSymRow['arznei_id'];
											$data['quelle_id'] = $comparingSymRow['quelle_id'];
											$data['original_quelle_id'] = $comparingSymRow['original_quelle_id'];
											$data['Symptomnummer'] = mysqli_real_escape_string($db, $comparingSymRow['Symptomnummer']);
											$data['SeiteOriginalVon'] = mysqli_real_escape_string($db, $comparingSymRow['SeiteOriginalVon']);
											$data['SeiteOriginalBis'] = mysqli_real_escape_string($db, $comparingSymRow['SeiteOriginalBis']);
											$data['final_version_de'] = mysqli_real_escape_string($db, $comparingSymRow['final_version_de']);
											$data['final_version_en'] = mysqli_real_escape_string($db, $comparingSymRow['final_version_en']);
											$data['Beschreibung_de'] = mysqli_real_escape_string($db, $comparingSymRow['Beschreibung_de']);
											$data['Beschreibung_en'] = mysqli_real_escape_string($db, $comparingSymRow['Beschreibung_en']);
											$data['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungOriginal_de']);
											$data['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungOriginal_en']);
											$data['BeschreibungFull_de'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungFull_de']);
											$data['BeschreibungFull_en'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungFull_en']);
											$data['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungPlain_de']);
											$data['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $comparingSymRow['BeschreibungPlain_en']);
											$data['searchable_text_de'] = mysqli_real_escape_string($db, $comparingSymRow['searchable_text_de']);
											$data['searchable_text_en'] = mysqli_real_escape_string($db, $comparingSymRow['searchable_text_en']);
											$data['bracketedString_de'] = mysqli_real_escape_string($db, $comparingSymRow['bracketedString_de']);
											$data['bracketedString_en'] = mysqli_real_escape_string($db, $comparingSymRow['bracketedString_en']);
											$data['timeString_de'] = mysqli_real_escape_string($db, $comparingSymRow['timeString_de']);
											$data['timeString_en'] = mysqli_real_escape_string($db, $comparingSymRow['timeString_en']);
											$data['comparing_source_original_language'] = mysqli_real_escape_string($db, $originComparingSourceLanguage);
											$data['Fussnote'] = mysqli_real_escape_string($db, $comparingSymRow['Fussnote']);
											$data['EntnommenAus'] = mysqli_real_escape_string($db, $comparingSymRow['EntnommenAus']);
											$data['Verweiss'] = mysqli_real_escape_string($db, $comparingSymRow['Verweiss']);
											$data['BereichID'] = mysqli_real_escape_string($db, $comparingSymRow['BereichID']);
											$data['Kommentar'] = mysqli_real_escape_string($db, $comparingSymRow['Kommentar']);
											//new edit
											$data['swap'] = mysqli_real_escape_string($db, $comparingSymRow['swap']);
											$data['swap_value_en'] = mysqli_real_escape_string($db, $comparingSymRow['swap_value_en']);
											$data['swap_value_de'] = mysqli_real_escape_string($db, $comparingSymRow['swap_value_de']);
											$data['swap_ce'] = mysqli_real_escape_string($db, $comparingSymRow['swap_ce']);
											$data['swap_value_ce_en'] = mysqli_real_escape_string($db, $comparingSymRow['swap_value_ce_en']);
											$data['swap_value_ce_de'] = mysqli_real_escape_string($db, $comparingSymRow['swap_value_ce_de']);
											$data['connection'] = mysqli_real_escape_string($db, $connectionValue);
											$data['Unklarheiten'] = mysqli_real_escape_string($db, $comparingSymRow['Unklarheiten']);
											$data['Remedy'] = mysqli_real_escape_string($db, $comparingSymRow['Remedy']);
											$data['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $comparingSymRow['symptom_of_different_remedy']);
											$data['subChapter'] = mysqli_real_escape_string($db, $comparingSymRow['subChapter']);
											$data['subSubChapter'] = mysqli_real_escape_string($db, $comparingSymRow['subSubChapter']);
											$data['synonym_word'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_word']);
											$data['strict_synonym'] = mysqli_real_escape_string($db, $comparingSymRow['strict_synonym']);
											$data['synonym_partial_1'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_partial_1']);
											$data['synonym_partial_2'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_partial_2']);
											$data['synonym_general'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_general']);
											$data['synonym_minor'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_minor']);
											$data['synonym_nn'] = mysqli_real_escape_string($db, $comparingSymRow['synonym_nn']);
											$data['comparison_matched_synonyms'] = (!empty($comparisonMatchedSynonyms)) ? serialize($comparisonMatchedSynonyms) : "";
											$data['symptom_edit_comment'] = mysqli_real_escape_string($db, $comparingSymRow['symptom_edit_comment']);
											$data['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $comparingSymRow['is_excluded_in_comparison']);
											$data['is_final_version_available'] = mysqli_real_escape_string($db, $comparingSymRow['is_final_version_available']);
											$data['matched_percentage'] = $percentage;
											$data['ersteller_datum'] = $date;
											if(!empty($data))
												$comparingSymptomsArray[] = $data;

											
										}
							    	}
							    }

								if(!empty($comparingSymptomsArray)){
									usort($comparingSymptomsArray, 'sortByOrder');
									foreach ($comparingSymptomsArray as $comRowKey => $comRow) {
										// Inserting comparative symptom in comparison DB table
										$insertComparative="INSERT INTO $comparisonTable (symptom_id, initial_symptom_id, is_initial_symptom, quelle_code, quelle_titel, quelle_type_id, quelle_jahr, quelle_band, quelle_auflage, quelle_autor_or_herausgeber, arznei_id, quelle_id, original_quelle_id, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, comparing_source_original_language, Fussnote, EntnommenAus, Verweiss, BereichID, Kommentar,connection,swap,swap_value_en,swap_value_de,swap_ce,swap_value_ce_en,swap_value_ce_de, Unklarheiten, Remedy, symptom_of_different_remedy, subChapter, subSubChapter, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, symptom_edit_comment, is_excluded_in_comparison, is_final_version_available, matched_percentage, ersteller_datum) VALUES (NULLIF('".$comRow['symptom_id']."', ''), NULLIF('".$comRow['initial_symptom_id']."', ''), NULLIF('".$comRow['is_initial_symptom']."', ''), NULLIF('".$comRow['quelle_code']."', ''), NULLIF('".$comRow['quelle_titel']."', ''), NULLIF('".$comRow['quelle_type_id']."', ''), NULLIF('".$comRow['quelle_jahr']."', ''), NULLIF('".$comRow['quelle_band']."', ''), NULLIF('".$comRow['quelle_auflage']."', ''), NULLIF('".$comRow['quelle_autor_or_herausgeber']."', ''), NULLIF('".$comRow['arznei_id']."', ''), NULLIF('".$comRow['quelle_id']."', ''), NULLIF('".$comRow['original_quelle_id']."', ''), NULLIF('".$comRow['Symptomnummer']."', ''), NULLIF('".$comRow['SeiteOriginalVon']."', ''), NULLIF('".$comRow['SeiteOriginalBis']."', ''), NULLIF('".$comRow['final_version_de']."', ''), NULLIF('".$comRow['final_version_en']."', ''), NULLIF('".$comRow['Beschreibung_de']."', ''), NULLIF('".$comRow['Beschreibung_en']."', ''), NULLIF('".$comRow['BeschreibungOriginal_de']."', ''), NULLIF('".$comRow['BeschreibungOriginal_en']."', ''), NULLIF('".$comRow['BeschreibungFull_de']."', ''), NULLIF('".$comRow['BeschreibungFull_en']."', ''), NULLIF('".$comRow['BeschreibungPlain_de']."', ''), NULLIF('".$comRow['BeschreibungPlain_en']."', ''), NULLIF('".$comRow['searchable_text_de']."', ''), NULLIF('".$comRow['searchable_text_en']."', ''), NULLIF('".$comRow['bracketedString_de']."', ''), NULLIF('".$comRow['bracketedString_en']."', ''), NULLIF('".$comRow['timeString_de']."', ''), NULLIF('".$comRow['timeString_en']."', ''), NULLIF('".$comRow['comparing_source_original_language']."', ''), NULLIF('".$comRow['Fussnote']."', ''), NULLIF('".$comRow['EntnommenAus']."', ''), NULLIF('".$comRow['Verweiss']."', ''), NULLIF('".$comRow['BereichID']."', ''), NULLIF('".$comRow['Kommentar']."', ''),NULLIF('".$comRow['connection']."', ''),NULLIF('".$comRow['swap']."', ''),NULLIF('".$comRow['swap_value_en']."', ''),NULLIF('".$comRow['swap_value_de']."', ''),NULLIF('".$comRow['swap_ce']."', ''),NULLIF('".$comRow['swap_value_ce_en']."', ''),NULLIF('".$comRow['swap_value_ce_de']."', ''), NULLIF('".$comRow['Unklarheiten']."', ''), NULLIF('".$comRow['Remedy']."', ''), NULLIF('".$comRow['symptom_of_different_remedy']."', ''), NULLIF('".$comRow['subChapter']."', ''), NULLIF('".$comRow['subSubChapter']."', ''), NULLIF('".$comRow['synonym_word']."', ''), NULLIF('".$comRow['strict_synonym']."', ''), NULLIF('".$comRow['synonym_partial_1']."', ''), NULLIF('".$comRow['synonym_partial_2']."', ''), NULLIF('".$comRow['synonym_general']."', ''), NULLIF('".$comRow['synonym_minor']."', ''), NULLIF('".$comRow['synonym_nn']."', ''), NULLIF('".$comRow['comparison_matched_synonyms']."', ''), NULLIF('".$comRow['symptom_edit_comment']."', ''), NULLIF('".$comRow['is_excluded_in_comparison']."', ''), NULLIF('".$comRow['is_final_version_available']."', ''), NULLIF('".$comRow['matched_percentage']."', ''), NULLIF('".$comRow['ersteller_datum']."', ''))";
										$db->query($insertComparative);
									}
								}
							}
						}
						// fclose($myfile);

						// $myfile2 = fopen("comparison-writing/".microtime()."-com-process-highest-match.txt", "w") or die("Unable to open file!");
						// $txt2 = date('m/d/Y h:i:s a', time())." <- BEFORE HIGHEST MATCH QUERY EXECUTED\n";
						// fwrite($myfile2, $txt2);
						$highestMatchResult = mysqli_query($db,"SELECT `id`, `symptom_id`, `final_version_de`, `final_version_en`, `Beschreibung_de`, `Beschreibung_en`, `BeschreibungOriginal_de`, `BeschreibungOriginal_en`, `BeschreibungFull_de`, `BeschreibungFull_en`, `BeschreibungPlain_de`, `BeschreibungPlain_en`, `searchable_text_de`, `searchable_text_en`, `synonym_word`, `strict_synonym`, `synonym_partial_1`, `synonym_partial_2`, `synonym_general`, `synonym_minor`, `synonym_nn`, `comparison_matched_synonyms`, MAX(`matched_percentage`) AS `matched_percentage`,`arznei_id`,`quelle_id`,`original_quelle_id`,`quelle_jahr`,`Kommentar`,`Fussnote`,`quelle_code`,`is_excluded_in_comparison`,`is_final_version_available` FROM $comparisonTable WHERE `is_initial_symptom`= '0' GROUP BY `symptom_id`");
						if(mysqli_num_rows($highestMatchResult) > 0){
							while($highestMatchRow = mysqli_fetch_array($highestMatchResult)){
								// $txt2 = date('m/d/Y h:i:s a', time())." Test HM\n";
								// fwrite($myfile2, $txt2);

								$highestMatchInsertData = array();
								$highestMatchInsertData['id'] = mysqli_real_escape_string($db, $highestMatchRow['id']);
								$highestMatchInsertData['symptom_id'] = mysqli_real_escape_string($db, $highestMatchRow['symptom_id']);
								$highestMatchInsertData['final_version_de'] = mysqli_real_escape_string($db, $highestMatchRow['final_version_de']);
								$highestMatchInsertData['final_version_en'] = mysqli_real_escape_string($db, $highestMatchRow['final_version_en']);
								$highestMatchInsertData['Beschreibung_de'] = mysqli_real_escape_string($db, $highestMatchRow['Beschreibung_de']);
								$highestMatchInsertData['Beschreibung_en'] = mysqli_real_escape_string($db, $highestMatchRow['Beschreibung_en']);
								$highestMatchInsertData['BeschreibungOriginal_de'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungOriginal_de']);
								$highestMatchInsertData['BeschreibungOriginal_en'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungOriginal_en']);
								$highestMatchInsertData['BeschreibungFull_de'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungFull_de']);
								$highestMatchInsertData['BeschreibungFull_en'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungFull_en']);
								$highestMatchInsertData['BeschreibungPlain_de'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungPlain_de']);
								$highestMatchInsertData['BeschreibungPlain_en'] = mysqli_real_escape_string($db, $highestMatchRow['BeschreibungPlain_en']);
								$highestMatchInsertData['searchable_text_de'] = mysqli_real_escape_string($db, $highestMatchRow['searchable_text_de']);
								$highestMatchInsertData['searchable_text_en'] = mysqli_real_escape_string($db, $highestMatchRow['searchable_text_en']);
								$highestMatchInsertData['synonym_word'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_word']);
								$highestMatchInsertData['strict_synonym'] = mysqli_real_escape_string($db, $highestMatchRow['strict_synonym']);
								$highestMatchInsertData['synonym_partial_1'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_partial_1']);
								$highestMatchInsertData['synonym_partial_2'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_partial_2']);
								$highestMatchInsertData['synonym_general'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_general']);
								$highestMatchInsertData['synonym_minor'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_minor']);
								$highestMatchInsertData['synonym_nn'] = mysqli_real_escape_string($db, $highestMatchRow['synonym_nn']);
								$highestMatchInsertData['comparison_matched_synonyms'] = mysqli_real_escape_string($db, $highestMatchRow['comparison_matched_synonyms']);
								$highestMatchInsertData['matched_percentage'] = mysqli_real_escape_string($db, $highestMatchRow['matched_percentage']);
								$highestMatchInsertData['arznei_id'] = mysqli_real_escape_string($db, $highestMatchRow['arznei_id']);
								$highestMatchInsertData['quelle_id'] = mysqli_real_escape_string($db, $highestMatchRow['quelle_id']);
								$highestMatchInsertData['original_quelle_id'] = mysqli_real_escape_string($db, $highestMatchRow['original_quelle_id']);
								$highestMatchInsertData['quelle_jahr'] = mysqli_real_escape_string($db, $highestMatchRow['quelle_jahr']);
								$highestMatchInsertData['Kommentar'] = mysqli_real_escape_string($db, $highestMatchRow['Kommentar']);
								$highestMatchInsertData['Fussnote'] = mysqli_real_escape_string($db, $highestMatchRow['Fussnote']);
								$highestMatchInsertData['quelle_code'] = mysqli_real_escape_string($db, $highestMatchRow['quelle_code']);
								$highestMatchInsertData['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $highestMatchRow['is_excluded_in_comparison']);
								$highestMatchInsertData['is_final_version_available'] = mysqli_real_escape_string($db, $highestMatchRow['is_final_version_available']);

								$insertHighestMatchData = "INSERT INTO ".$comparisonHighestMatches." (comparison_table_id, symptom_id, final_version_de, final_version_en, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, comparison_matched_synonyms, matched_percentage, arznei_id, quelle_id, original_quelle_id, quelle_jahr, Kommentar, Fussnote, quelle_code, is_excluded_in_comparison, is_final_version_available) VALUES (NULLIF('".$highestMatchInsertData['id']."', ''), NULLIF('".$highestMatchInsertData['symptom_id']."', ''), NULLIF('".$highestMatchInsertData['final_version_de']."', ''), NULLIF('".$highestMatchInsertData['final_version_en']."', ''), NULLIF('".$highestMatchInsertData['Beschreibung_de']."', ''), NULLIF('".$highestMatchInsertData['Beschreibung_en']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungOriginal_de']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungOriginal_en']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungFull_de']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungFull_en']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungPlain_de']."', ''), NULLIF('".$highestMatchInsertData['BeschreibungPlain_en']."', ''), NULLIF('".$highestMatchInsertData['searchable_text_de']."', ''), NULLIF('".$highestMatchInsertData['searchable_text_en']."', ''), NULLIF('".$highestMatchInsertData['synonym_word']."', ''), NULLIF('".$highestMatchInsertData['strict_synonym']."', ''), NULLIF('".$highestMatchInsertData['synonym_partial_1']."', ''), NULLIF('".$highestMatchInsertData['synonym_partial_2']."', ''), NULLIF('".$highestMatchInsertData['synonym_general']."', ''), NULLIF('".$highestMatchInsertData['synonym_minor']."', ''), NULLIF('".$highestMatchInsertData['synonym_nn']."', ''), NULLIF('".$highestMatchInsertData['comparison_matched_synonyms']."', ''), NULLIF('".$highestMatchInsertData['matched_percentage']."', ''), NULLIF('".$highestMatchInsertData['arznei_id']."', ''), NULLIF('".$highestMatchInsertData['quelle_id']."', ''), NULLIF('".$highestMatchInsertData['original_quelle_id']."', ''), NULLIF('".$highestMatchInsertData['quelle_jahr']."', ''), NULLIF('".$highestMatchInsertData['Kommentar']."', ''), NULLIF('".$highestMatchInsertData['Fussnote']."', ''), NULLIF('".$highestMatchInsertData['quelle_code']."', ''), NULLIF('".$highestMatchInsertData['is_excluded_in_comparison']."', ''), NULLIF('".$highestMatchInsertData['is_final_version_available']."', ''))";
								$db->query($insertHighestMatchData);
							}
							// fclose($myfile2);
						}

						// Comparison connection table
			            $createComparasionSavedDataTable = "CREATE TABLE ".$comparisonTable."_connections ( 
											id INT NOT NULL AUTO_INCREMENT,
											initial_symptom_id INT NULL DEFAULT NULL,
											comparing_symptom_id INT NULL DEFAULT NULL,
											connection_type ENUM ('connect', 'paste', 'CE', 'PE', 'swap','swapCE') DEFAULT NULL COMMENT 'connect, paste, CE, PE, swap for connect swap, swapCE for connect edit swap',
											matched_percentage INT NOT NULL,
											ns_connect ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
											ns_paste ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
											ns_connect_comment VARCHAR(255) NULL DEFAULT NULL,
											ns_paste_comment VARCHAR(255) NULL DEFAULT NULL,
											initial_quelle_id INT NULL DEFAULT NULL,
											comparing_quelle_id INT NULL DEFAULT NULL,
											initial_quelle_code VARCHAR(100) NULL DEFAULT NULL,
											comparing_quelle_code VARCHAR(100) NULL DEFAULT NULL,
											initial_quelle_original_language VARCHAR(20) NULL DEFAULT NULL,
											comparing_quelle_original_language VARCHAR(20) NULL DEFAULT NULL,
											highlighted_initial_symptom_de TEXT DEFAULT NULL,
											highlighted_initial_symptom_en TEXT DEFAULT NULL,
											highlighted_comparing_symptom_de TEXT DEFAULT NULL,
											highlighted_comparing_symptom_en TEXT DEFAULT NULL,
											initial_symptom_de TEXT DEFAULT NULL,
											initial_symptom_en TEXT DEFAULT NULL,
											comparing_symptom_de TEXT DEFAULT NULL,
											comparing_symptom_en TEXT DEFAULT NULL,
											comparison_language VARCHAR(20) NULL DEFAULT NULL COMMENT 'de or en',
											initial_year VARCHAR(50) NULL DEFAULT NULL COMMENT 'initial source year',
											comparing_year VARCHAR(50) NULL DEFAULT NULL COMMENT 'comparing source year',
											is_earlier_connection ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
											free_flag ENUM ('0','1') DEFAULT '0' COMMENT '1 = Yes, 0 = No',
											PRIMARY KEY (id),
											INDEX (initial_symptom_id),
											INDEX (comparing_symptom_id),
											INDEX (initial_quelle_id),
											INDEX (comparing_quelle_id)
										) ENGINE = InnoDB DEFAULT CHARSET = utf8";
						mysqli_query($db, $createComparasionSavedDataTable);

						if(!empty($savedComparisonConnectionsArr)){
							foreach ($savedComparisonConnectionsArr as $savedConnKey => $savedConnVal) {
								$savedConnResult = mysqli_query($db,"SELECT * FROM $savedConnVal");
								if(mysqli_num_rows($savedConnResult) > 0){
									while($savedConnRow = mysqli_fetch_array($savedConnResult)){
										$data = array();
										$data['initial_symptom_id']=mysqli_real_escape_string($db, $savedConnRow['initial_symptom_id']);
										$data['comparing_symptom_id']=mysqli_real_escape_string($db, $savedConnRow['comparing_symptom_id']);
										$data['connection_type']=mysqli_real_escape_string($db, $savedConnRow['connection_type']);
										$data['matched_percentage']=mysqli_real_escape_string($db, $savedConnRow['matched_percentage']);
										$data['ns_connect']=mysqli_real_escape_string($db, $savedConnRow['ns_connect']);
										$data['ns_paste']=mysqli_real_escape_string($db, $savedConnRow['ns_paste']);
										$data['ns_connect_comment']=mysqli_real_escape_string($db, $savedConnRow['ns_connect_comment']);
										$data['ns_paste_comment']=mysqli_real_escape_string($db, $savedConnRow['ns_paste_comment']);
										$data['initial_quelle_id']=mysqli_real_escape_string($db, $savedConnRow['initial_quelle_id']);
										$data['comparing_quelle_id']=mysqli_real_escape_string($db, $savedConnRow['comparing_quelle_id']);
										$data['initial_quelle_code']=mysqli_real_escape_string($db, $savedConnRow['initial_quelle_code']);
										$data['comparing_quelle_code']=mysqli_real_escape_string($db, $savedConnRow['comparing_quelle_code']);
										$data['initial_quelle_original_language']=mysqli_real_escape_string($db, $savedConnRow['initial_quelle_original_language']);
										$data['comparing_quelle_original_language']=mysqli_real_escape_string($db, $savedConnRow['comparing_quelle_original_language']);
										$data['highlighted_initial_symptom_de']=mysqli_real_escape_string($db, $savedConnRow['highlighted_initial_symptom_de']);
										$data['highlighted_initial_symptom_en']=mysqli_real_escape_string($db, $savedConnRow['highlighted_initial_symptom_en']);
										$data['highlighted_comparing_symptom_de']=mysqli_real_escape_string($db, $savedConnRow['highlighted_comparing_symptom_de']);
										$data['highlighted_comparing_symptom_en']=mysqli_real_escape_string($db, $savedConnRow['highlighted_comparing_symptom_en']);
										$data['initial_symptom_de']=mysqli_real_escape_string($db, $savedConnRow['initial_symptom_de']);
										$data['initial_symptom_en']=mysqli_real_escape_string($db, $savedConnRow['initial_symptom_en']);
										$data['comparing_symptom_de']=mysqli_real_escape_string($db, $savedConnRow['comparing_symptom_de']);
										$data['comparing_symptom_en']=mysqli_real_escape_string($db, $savedConnRow['comparing_symptom_en']);
										$data['comparison_language']=mysqli_real_escape_string($db, $savedConnRow['comparison_language']);
										$data['initial_year']=mysqli_real_escape_string($db, $savedConnRow['initial_year']);
										$data['comparing_year']=mysqli_real_escape_string($db, $savedConnRow['comparing_year']);
										$data['is_earlier_connection']=mysqli_real_escape_string($db, $savedConnRow['is_earlier_connection']);

										//not inserting paste symptoms
										if($data['connection_type'] != 'paste'){
											$insertSavedConn = "INSERT INTO ".$comparisonTable."_connections (initial_symptom_id, comparing_symptom_id, connection_type, matched_percentage, ns_connect, ns_paste, ns_connect_comment, ns_paste_comment, initial_quelle_id, comparing_quelle_id, initial_quelle_code, comparing_quelle_code, initial_quelle_original_language, comparing_quelle_original_language, highlighted_initial_symptom_de, highlighted_initial_symptom_en, highlighted_comparing_symptom_de, highlighted_comparing_symptom_en, initial_symptom_de,  initial_symptom_en, comparing_symptom_de, comparing_symptom_en, comparison_language, initial_year, comparing_year, is_earlier_connection, free_flag) VALUES (NULLIF('".$data['initial_symptom_id']."', ''), NULLIF('".$data['comparing_symptom_id']."', ''), NULLIF('".$data['connection_type']."', ''), '".$data['matched_percentage']."', '".$data['ns_connect']."', '".$data['ns_paste']."', NULLIF('".$data['ns_connect_comment']."', ''), NULLIF('".$data['ns_paste_comment']."', ''), NULLIF('".$data['initial_quelle_id']."', ''), NULLIF('".$data['comparing_quelle_id']."', ''), NULLIF('".$data['initial_quelle_code']."', ''), NULLIF('".$data['comparing_quelle_code']."', ''), NULLIF('".$data['initial_quelle_original_language']."', ''), NULLIF('".$data['comparing_quelle_original_language']."', ''), NULLIF('".$data['highlighted_initial_symptom_de']."', ''), NULLIF('".$data['highlighted_initial_symptom_en']."', ''), NULLIF('".$data['highlighted_comparing_symptom_de']."', ''), NULLIF('".$data['highlighted_comparing_symptom_en']."', ''), NULLIF('".$data['initial_symptom_de']."', ''), NULLIF('".$data['initial_symptom_en']."', ''), NULLIF('".$data['comparing_symptom_de']."', ''), NULLIF('".$data['comparing_symptom_en']."', ''), NULLIF('".$data['comparison_language']."', ''), NULLIF('".$data['initial_year']."', ''), NULLIF('".$data['comparing_year']."', ''), '1','1')";
											$db->query($insertSavedConn);	
										}
											
									}
								}
							}
						}

						$endDate = date("Y-m-d H:i:s"); 
						$updateComparisonData = "UPDATE pre_comparison_master_data SET status = 'done', stand = NULLIF('".$endDate."', '') WHERE id = ".$preComparisonMasterDatainsertedId;
						$db->query($updateComparisonData);

						$status = 'success';
					}else{
						$status = 'error';
				   		$message = 'No symptoms found';
					}
					
				}else{
					$status = 'error';
			   		$message = 'Required data not found';
				}
			}else{
				$status = 'error';
		   		$message = 'Table does not exist';
			}
		}else{
			$status = 'error';
	   		$message = 'Required data not found';
		}
	} catch (Exception $e) {
	    $status = 'error';
	    $message = 'Exception error';
	}


	echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
	exit;


	function sortByOrder($a, $b) {
	   return  $b['matched_percentage'] - $a['matched_percentage'];
	}
?>