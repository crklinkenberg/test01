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
		if(isset($_POST['form']) AND !empty($_POST['form'])){	
			parse_str( $_POST['form'], $formData );
			
			$translation_symptoms = (isset($formData['translation_symptoms']) AND $formData['translation_symptoms'] != "") ? $formData['translation_symptoms'] : "";
			$translation_method = (isset($formData['translation_method']) AND $formData['translation_method'] != "") ? $formData['translation_method'] : "";
			$add_translation_master_id = (isset($formData['add_translation_master_id']) AND $formData['add_translation_master_id'] != "") ? $formData['add_translation_master_id'] : "";
			$add_translation_arznei_id = (isset($formData['add_translation_arznei_id']) AND $formData['add_translation_arznei_id'] != "") ? $formData['add_translation_arznei_id'] : "";
			$add_translation_quelle_id = (isset($formData['add_translation_quelle_id']) AND $formData['add_translation_quelle_id'] != "") ? $formData['add_translation_quelle_id'] : "";
			$add_translation_language = (isset($formData['add_translation_language']) AND $formData['add_translation_language'] != "") ? $formData['add_translation_language'] : "";


			if($translation_symptoms == "" OR $translation_method == "" OR $add_translation_master_id == "" OR $add_translation_arznei_id == "" OR $add_translation_quelle_id == "" OR $add_translation_language == ""){
				$status = 'error';
	    		$message = 'Some required data not found. Please reload the page and try again!';
				echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
				exit;
			}

			// Checking is this translation already available
			$importingLanguageSuffix = "";
			$oppositeLanguageSuffix = "";
			$is_translation_added = 0;
			if($add_translation_language == "de"){
				$importingLanguageSuffix = "de";
				$oppositeLanguageSuffix = "en";
			}
			if($add_translation_language == "en"){
				$importingLanguageSuffix = "en";
				$oppositeLanguageSuffix = "de";
			}


			if($importingLanguageSuffix != ""){
				$queryRes = mysqli_query($db,"SELECT QIM.id FROM quelle_import_master AS QIM JOIN arznei_quelle AS AQ ON QIM.quelle_id = AQ.quelle_id WHERE QIM.is_symptoms_available_in_".$importingLanguageSuffix." = 1 AND QIM.id = '".$add_translation_master_id."' AND AQ.quelle_id = '".$add_translation_quelle_id."' AND AQ.arznei_id = '".$add_translation_arznei_id."'");
				if(mysqli_num_rows($queryRes) > 0)
					$is_translation_added = 1;
			}

			if($is_translation_added == 0){

				$rownum = 1;
				$break = false;
				$Symptomnummer = 1;
				$SeiteOriginalVon = '';
				$SeiteOriginalBis = '';
				$prueferFromParray = array ();
				$prueferIDarray = array ();
				//$Pruefers = '';
				$Beschreibung = '';
				$Fussnote='';
				$Graduierung='';
				$BereichID='';
				$aLiteraturquellen = array ();
				$EntnommenAus='';
				$Verweiss = '';
				$Unklarheiten = '';
				$Kommentar = '';
				$bracketedString='';
				$timeString='';
				$parenthesesStringArray= array ();
				$timeStringArray= array ();
				$bracketedStringArray= array ();
				$strongRedStringArray= array ();
				$strongBlueStringArray= array ();
				$needApproval = 0;
				$remedyArray = array();
				$prueferArray = array();
				$referenceArray = array();
				$prueferPriority = 0;
				$remedyPriority = 0;
				$partOfSymptomPriority = 0;
				$referenceWithNoAuthorPriority = 0;
				$referencePriority = 0;
				$remedyWithSymptomPriority = 0;
				$moreThanOneTagStringPriority = 0;
				$aaoHyphenPriority = 0;
				$hyphenPrueferPriority = 0;
				$hyphenReferencePriority = 0;
				$hyphenApprovalString = "";
				$directOrderPriority = 0;
				$tagsApprovalString = "";
				$isPreDefinedTagsApproval = 0;
				$symptomOfDifferentRemedy = "";
				$allBrackets = array();
				$endingBracketsArray = array();
				$middleBracketArray = array();
				$approvalFor = 0;
				$middleBracketApprovalString = "";
				$isCodingWithSymptomNumber = 1;
				$isSymptomNumberMismatch = 0;
				$searchableText = "";
				$middleBracketString = "";


				// Add translation process starts
				$symptomIdArrInDB = array();
				// Checking if it is a Combined source then the dynamic "_completed" table will be used
				$isCombinedSource = 0;
				$combinedSourceCompletedTable = "";
				$isCombinedSourceSaved = "";
				$checkQuelle = mysqli_query($db,"SELECT Q.quelle_id FROM quelle AS Q WHERE Q.quelle_type_id = 3 AND Q.quelle_id = '".$add_translation_quelle_id."'");
				if(mysqli_num_rows($checkQuelle) > 0){
					$isCombinedSource = 1;
					$preComparisonMasterDataQuery = $db->query("SELECT table_name, comparison_save_status FROM pre_comparison_master_data WHERE quelle_id = '".$add_translation_quelle_id."' AND arznei_id = '".$add_translation_arznei_id."'");
					if($preComparisonMasterDataQuery->num_rows > 0)
						$preComparisonMasterData = mysqli_fetch_assoc($preComparisonMasterDataQuery);
					$combinedSourceCompletedTable = (isset($preComparisonMasterData['table_name']) AND $preComparisonMasterData['table_name'] != "") ? $preComparisonMasterData['table_name']."_completed" : "";
					// 0 = Initial stage when compared(Blue), 1 = State when user saved comparison(Yellow), 2 = State when admin approved the saved comparison(Green)
					$isCombinedSourceSaved = (isset($preComparisonMasterData['comparison_save_status']) AND $preComparisonMasterData['comparison_save_status'] != 2) ? 0 : 1;
				}
				if($isCombinedSource == 1){
					if($isCombinedSourceSaved != 1 AND $combinedSourceCompletedTable == ""){
						$status = 'error';
			    		$message = 'You need to save the comparison first!';
						echo json_encode( array( 'status' => $status, 'result_data' => $resultData, 'message' => $message) ); 
						exit;	
					}
					else
					{
						$symptomResult = $db->query("SELECT symptom_id, bracketedString_".$oppositeLanguageSuffix." FROM ".$combinedSourceCompletedTable);
						if($symptomResult->num_rows > 0){
							while($symptomData = mysqli_fetch_array($symptomResult)){
								$does_it_have_approval_string = (isset($symptomData["bracketedString_".$oppositeLanguageSuffix]) AND $symptomData["bracketedString_".$oppositeLanguageSuffix] != "") ? 1 : 0;
								if($does_it_have_approval_string == 0){
									// Checking if there there is anything added form pre defined tags @P or @L
									$symptomPrueferResult = $db->query("SELECT symptom_id FROM symptom_pruefer WHERE symptom_id = ".$symptomData['symptom_id']);
									if($symptomPrueferResult->num_rows > 0)
										$does_it_have_approval_string = 1;
									$symptomReferenceResult = $db->query("SELECT symptom_id FROM symptom_reference WHERE symptom_id = ".$symptomData['symptom_id']);
									if($symptomReferenceResult->num_rows > 0)
										$does_it_have_approval_string = 1;

								}
								$dataInDB =  array();
								$dataInDB['symptom_id'] = $symptomData['symptom_id'];
								$dataInDB['original_symptom_id'] = $symptomData['symptom_id'];
								$dataInDB['does_it_have_approval_string'] = $does_it_have_approval_string; // 0= No, 1= Yes
								$symptomIdArrInDB[] = $dataInDB;
							}
						}
					}
				}
				else
				{
					$symptomResult = $db->query("SELECT id, original_symptom_id, bracketedString_".$oppositeLanguageSuffix." FROM quelle_import_test WHERE master_id = ".$add_translation_master_id);
					if($symptomResult->num_rows > 0){
						while($symptomData = mysqli_fetch_array($symptomResult)){
							$does_it_have_approval_string = (isset($symptomData["bracketedString_".$oppositeLanguageSuffix]) AND $symptomData["bracketedString_".$oppositeLanguageSuffix] != "") ? 1 : 0;
							if($does_it_have_approval_string == 0){
								// Checking if there there is anything added form pre defined tags @P or @L
								$symptomPrueferResult = $db->query("SELECT symptom_id FROM symptom_pruefer WHERE symptom_id = ".$symptomData['id']);
								if($symptomPrueferResult->num_rows > 0)
									$does_it_have_approval_string = 1;
								$symptomReferenceResult = $db->query("SELECT symptom_id FROM symptom_reference WHERE symptom_id = ".$symptomData['id']);
								if($symptomReferenceResult->num_rows > 0)
									$does_it_have_approval_string = 1;

							}
							$dataInDB =  array();
							$dataInDB['symptom_id'] = $symptomData['id'];
							$dataInDB['original_symptom_id'] = $symptomData['original_symptom_id'];
							$dataInDB['does_it_have_approval_string'] = $does_it_have_approval_string; // 0= No, 1= Yes
							$symptomIdArrInDB[] = $dataInDB;
						}
					}
				}
				$resultData['in_db_data'] = $symptomIdArrInDB;

				// Updating the single/individual sources language status START
				if($isCombinedSource == 1 AND $isCombinedSourceSaved == 1 AND $combinedSourceCompletedTable != ""){
					$add_translation_quelle_ids = ($add_translation_quelle_id != "") ? explode(',', $add_translation_quelle_id) : array();
					$allInvolvedIndividualQuelleIds = (!empty($add_translation_quelle_ids)) ? getAllRelatedIndividualSourcesNew($add_translation_quelle_ids) : array();
					if(!empty($allInvolvedIndividualQuelleIds)){
						foreach ($allInvolvedIndividualQuelleIds as $indiQuelleKey => $indiQuelleVal) {
							$updateQuelleMasterWithLanguageQuery = "UPDATE quelle_import_master SET is_symptoms_available_in_".$importingLanguageSuffix." = 1, translation_method_of_".$importingLanguageSuffix." = NULLIF('".$translation_method."', '') WHERE quelle_id = '".$indiQuelleVal."' AND arznei_id = '".$add_translation_arznei_id."'";
							$db->query($updateQuelleMasterWithLanguageQuery);
						}
					}
				}
				// Updating the single/individual sources language status END

				$CleanedText = str_replace ( '</em><em>', '', $translation_symptoms );
				$CleanedText = str_replace ( array (
					"\r",
					"\t" 
				), '', $CleanedText );
				$CleanedText = trim ( $CleanedText );
				$Lines = explode ( "\n", $CleanedText );

				$symptomDataArrInImportedData = array();
				foreach ( $Lines as $iline => $line ) {
					$line = strip_tags ( $line, '<b><i><strong><em><u><sup><span>' );
					$line = trim ( str_replace ( '&nbsp;', ' ', htmlentities( $line ) ) );
					$line = html_entity_decode($line);
					// Replacing Colored sentences's tag to our custom tag "<clr>"
					$coloredTextCnt = 0; 
					do { 
						$line = preg_replace("#<span[^>]*style=(\"|')[^>]*color:(.+?);[^>]*(\"|')>(.+?)</span>#is", "<clr style=\"color:$2;\">$4</clr>", $line, -1, $coloredTextCnt ); 
					} while ( $coloredTextCnt > 0 );
					// Replacing Spaced sentences's tag to our custom tag "<ss>"
					$letterSpaceCntV1 = 0; 
					do { 
						$line = preg_replace("#<span[^>]*style=(\"|')[^>]*letter-spacing:[^>]*>(.+?)</span>#is", "<ss>$2</ss>", $line, -1, $letterSpaceCntV1 ); 
					} while ( $letterSpaceCntV1 > 0 );
					$letterSpaceCntV2 = 0; 
					do { 
						$line = preg_replace("#<span[^>]*class=(\"|')text-sperrschrift(\"|')>(.+?)</span>#is", "<ss>$3</ss>", $line, -1, $letterSpaceCntV2 ); 
					} while ( $letterSpaceCntV2 > 0 );

					
					$line = strip_tags ( $line, '<b><i><strong><em><u><sup><ss><clr>' );
					$break = false;
					$NewSymptomNr = 0;
					$line = trim ( $line );
					
					$cleanline = strip_tags($line);

					// Leerzeile
					if (empty ( $cleanline )) {
						$rownum ++;
						continue;
					}
					
					if (mb_strlen ( $cleanline ) < 3) { //added
						$rownum ++;
						continue;
					}
					$FirstChar = mb_substr ( $cleanline, 0, 1 );
					$LastChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 1 );
					$LastTwoChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 2 );

					$code='';
					$param='';
					$Beschreibung = '';

					if($FirstChar == '@'){
						$Beschreibung = '';
						$p = mb_strpos ( $cleanline, ':' );
						if ($p > 0) {
							$code = mb_substr ( $cleanline, 1, $p - 1 );
							$param = mb_substr ( $cleanline, $p + 1 );
						} else {
							$code = mb_substr ( $cleanline, 1 );
							$param = '';
						}
						
						$code = mb_strtoupper ( $code );

						switch ($code) {
							// Graduierung
							case 'G' :
								$Graduierung = $param;
								break;
							
							// Kapitel, setzt in DS "KapitelID"
							// case 'B' :
							case 'K' :
								$BereichID = $param;
								break;
							
							// Seite, setzt in DS "Seite"
							case 'S' :
								$tmp = explode ( '-', $param );
								$SeiteOriginalVon = $tmp [0] + 0;
								if (sizeof ( $tmp ) > 1)
									$SeiteOriginalBis = $tmp [1] + 0;
								else
									$SeiteOriginalBis = $SeiteOriginalVon;
								break;
							
							// Symptom-Nr., setzt in DS "Symptomnummer"
							case 'N' :
								$NewSymptomNr = $param + 0;
								if ($NewSymptomNr == 0) {
									//$NewSymptomNr = 1;
									$Symptomnummer = 0;
								}
								break;
							
							// Literaturquelle, setzt in DS "EntnommenAus"
							case 'L' :
								$aLiteraturquellen [] = $param;
								break;
							
							// Fußnote
							case 'F' :
								$Fussnote = $param;
								break;
							
							// Verweiss
							case 'V' :
								$Verweiss = $param;
								break;
							
							// @U: (Unklarheit, steht wie auch @F und @L VOR dem einen Symptom, welches betroffen ist)
							case 'U' :
								$Unklarheiten = $param;
								break;
							
							// @C: (Kommentar, steht wie auch @F und @L VOR dem einen Symptom, welches betroffen ist)
							case 'C' :
								$Kommentar = $param;
								break;
							
							// @P: Prüfer als Kürzel
							case 'P' :
								// $PrueferID = $this->LookupPruefer ( $param, $rownum );
								// $PrueferID = $param;
								// if ($PrueferID > 0) {
								// 	$PrueferIDs [] = $PrueferID;
								// } 
								$prueferFromParray [] = $param;
								break;
							
							default :
								continue;
						}
						//continue;
					} else if ($FirstChar == '(') {
						/* 
						* parseing symptoms nummer which has parentheses between symptom nummer 
						* Eg : (90) Fauleier-Geschmack im Munde, außer dem Essen. (Fr. Hahnemann.)
						*/
						$p = mb_strpos ( $line, ')' );
						if ($p > 0) {
							$NewSymptomNr = trim ( mb_substr ( $line, 1, $p - 1 ) );
							if (is_numeric ( $NewSymptomNr )) {
								if($NewSymptomNr != $Symptomnummer)
									$isSymptomNumberMismatch = 1;
								$Beschreibung = trim ( mb_substr ( $line, $p + 1 ) );
								$cleanline = trim ( mb_substr ( $cleanline, $p + 1 ) );
							} else {
								$NewSymptomNr = 0;
								$Beschreibung = $line;
							}
						}
					} else {
						$isSymptomNum = is_numeric ( $FirstChar );
						$Beschreibung = '';
						
						if ($isSymptomNum) {
							/* 
							* parseing symptoms nummer which has space between symptom nummer and symptom string 
							* Eg : 30 Merklich vermindertes Gehör. (n. 30 St.)
							*/
							$p = mb_strpos ( $line, ' ' );
							$num = str_replace ( array (
								':',
								'.', 
								')' 
							), '', mb_substr ( $line, 0, $p ) );
							if( is_numeric($num) ){
								$NewSymptomNr = $num;
								if($NewSymptomNr != $Symptomnummer)
									$isSymptomNumberMismatch = 1;
								$Beschreibung = trim ( mb_substr ( $line, $p + 1 ) );
								$cleanline = trim ( mb_substr ( $cleanline, $p + 1 ) );
							}else{
								/* 
								* parseing symptoms nummer which are attached with Synptom string 
								* Eg : 10Drückender Schmerz in der Stirne.
								*/
								$charCount = 2;
								$NewSymptomNr = $FirstChar;
								while ( $charCount > 0 ) {
									$checkSymptomNumber = mb_substr ( $line, 0, $charCount );
									if( is_numeric($checkSymptomNumber) ){
										$NewSymptomNr = $checkSymptomNumber;
										if($NewSymptomNr != $Symptomnummer)
											$isSymptomNumberMismatch = 1;
										$charCount++;
									}else
										$charCount = 0;
								}

								if (mb_substr($line, 0, mb_strlen($NewSymptomNr)) == $NewSymptomNr) {
								    $Beschreibung = trim ( mb_substr($line, mb_strlen($NewSymptomNr)) );
								    $cleanline = trim ( mb_substr($cleanline, mb_strlen($NewSymptomNr)) );
								}else{
									$Beschreibung = $line;
								} 
							}
						} else {
							$NewSymptomNr = 0;
							$Beschreibung = $line;
						}
					}

					if ( $NewSymptomNr > 0 ) {
						$Symptomnummer = $NewSymptomNr;
					}

					if ($Beschreibung) {

						/* Creating Plain Symptom text */
						$BeschreibungPlain = trim ( str_replace ( "\t", '', strip_tags ( $Beschreibung ) ) );

						/* Creating Source or as it is Symtom text */
						$BeschreibungAsItIs = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $Beschreibung );
						$BeschreibungAsItIs = str_replace ( array (
							'<clr',
							'</clr>' 
						), array (
							"<span",
							"</span>" 
						), $BeschreibungAsItIs );

						/* Creating Original Symptom text start */
						$line = $Beschreibung;
						// Get the First and Last character to check format- (Normal) Kursiv,° Normal,° Fett,°  
						$FirstCharCheck = mb_substr ( trim($line), 0, 1 );

						$cleanLineToGetLastChar = rtrim(trim($line), '.');
						$cleanLineToGetLastChar = rtrim(trim($cleanLineToGetLastChar), ',');
						$cleanLineToGetLastChar = rtrim(trim($cleanLineToGetLastChar), ';');
						$LastCharCheck = mb_substr ( trim($cleanLineToGetLastChar), mb_strlen ( trim($cleanLineToGetLastChar) ) - 1 );
						// Checking is there any open tag
						preg_match_all("#<[^/>]*>#i", $line, $matcheOpenTags, PREG_OFFSET_CAPTURE);
						// Count the number of occurance of- *,°
						$totalAsterisks = mb_substr_count($line, "*"); 
						$totalDegrees = mb_substr_count($line, "°"); 
						
						if(($FirstCharCheck == "(" AND $LastCharCheck == ")") AND (isset($matcheOpenTags[0]) AND empty($matcheOpenTags[0]))) {
							// It is format - (Normal)
							$line = '<parentheses-normal>'.$line.'</parentheses-normal>';
						} else if($LastCharCheck == "°" AND $totalDegrees == 1 AND $totalAsterisks == 0) {
							// It is format - Kursiv,° Normal,° Fett,°
							$line = structureEndingWithDegreeFormatString($line, 'endwithdegree');
						} else {
							// echo $line."<br>";
							$line = separateTheApplicableStratingSign($line, '*');
							$line = separateTheApplicableStratingSign($line, '°');
							// echo htmlentities($line)."<br><br>";
							$line = removeBlankTags($line);

							$line = convertPatternPortions($line, '*', 'asterisk');
							$line = convertPatternPortions($line, '°', 'degree');
							// Structure the non * and ° portion strings
							$line = structureNonAsteriskAndDegreePortions($line, 'non-asterisk-degree');
							// echo json_encode( array( 'status' => $status, 'result_data' => $line, 'message' => $message) ); 
							// exit;
						}
						$line = removeBlankTags($line);
						$line = removeCustomParentTags($line);
						$line = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $line );
						$line = str_replace ( array (
							'<clr',
							'</clr>' 
						), array (
							"<span",
							"</span>" 
						), $line );
						
						$escapeCustomTags = "<parentheses-normal>";
						$parentCustomTagArr = array('endwithdegree', 'asterisk', 'degree', 'non-asterisk-degree', 'asterisk-degree');
						foreach ($parentCustomTagArr as $tagKey => $tagVal) {
							$escapeCustomTags .= "<".$tagVal."-ssbold>";
							$escapeCustomTags .= "<".$tagVal."-embold>";
							$escapeCustomTags .= "<".$tagVal."-ssem>";
							$escapeCustomTags .= "<".$tagVal."-ss>";
							$escapeCustomTags .= "<".$tagVal."-em>";
							$escapeCustomTags .= "<".$tagVal."-normalgross>";
							$escapeCustomTags .= "<".$tagVal."-normal>";
							$escapeCustomTags .= "<".$tagVal."-bold>";
							$escapeCustomTags .= "<".$tagVal."-grossbold>";
						}

						$BeschreibungOriginal = strip_tags ( $line, '<b><i><strong><em><u><sup><span>'.$escapeCustomTags );
						$BeschreibungOriginal = removeBlankTags($BeschreibungOriginal);
						// $searchableText here we are going to store only symptom part excluding prufer, remedi, time data, etc.
						// $searchableText = $BeschreibungPlain;
						$searchableText = $BeschreibungOriginal;
						/* Creating Original Symptom text end */
						
						// As it is symptom text
						$Beschreibung = $BeschreibungAsItIs;
						
						/* Creating Source or as it is Symtom text end */

						/* Find all time data in the entire Symptom string */
						$allTimeStringsArray = getAllTimeData($cleanline, $timeStringEndTagArray);
						if(!empty($allTimeStringsArray)){
							$timeString = implode(', ', $allTimeStringsArray);
						}

						// Removing time strings
						if(!empty($allTimeStringsArray)){
							foreach ($allTimeStringsArray as $timeStrKey => $timeStrVal) {
								if(mb_strpos($cleanline, $timeStrVal) !== false)
									$cleanline = str_replace($timeStrVal, "", $cleanline);
								if(mb_strpos($searchableText, $timeStrVal) !== false)
									$searchableText = str_replace($timeStrVal, "", $searchableText);
							}
						}

						/* Getting ending bracketed strings */
						if ($LastChar == ')' OR $LastTwoChar ==').' OR $LastTwoChar =='),') {
							$endingBracketsArray = getAllEndingBracketedStrings($cleanline, "(", ")");
						}else if($LastChar == ']' OR $LastTwoChar =='].' OR $LastTwoChar =='],'){
							$endingBracketsArray = getAllEndingBracketedStrings($cleanline, "[", "]");
						}

						/* Getting all bracketed strings */
						$allParentheses = getAllbracketedStrings($cleanline, "(", ")");
						$allSquareBrackets = getAllbracketedStrings($cleanline, "[", "]");
						$allBrackets = array_merge($allParentheses, $allSquareBrackets);
						$middleBracketArray =array_diff($allBrackets,$endingBracketsArray);

						/* Extracting Pruefer Data and Literaturquellen data Start */
						if( count($aLiteraturquellen) > 0 AND  count($prueferFromParray) > 0 ){
							/* When @L nad @P both are present in a symptom */
							$tagsApproalStringForPrue = "";
							foreach ($prueferFromParray as $prueferPkey => $prueferPval) {
								$prueferPval = trim($prueferPval);
								$tagsApproalStringForPrue .= $prueferPval."{#^#}";
							}
							$tagsApproalStringForRef = "";
							foreach ($aLiteraturquellen as $refKey => $refVal) {
								$tagsApproalStringForRef .= $refVal."{#^#}";
							}

							$aLiteraturquellen = array();
							$referenceArray = array();
							$prueferArray = array();
							$tagsApprovalString = $tagsApproalStringForPrue.$tagsApproalStringForRef;
							if($tagsApprovalString != "")
								$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");
						}else if( count($aLiteraturquellen) > 0 ){
							$tagsApproalStringForRef = ""; 
							foreach ($aLiteraturquellen as $refKey => $refVal) {
								$tagsApproalStringForRef .= $refVal."{#^#}";
							}

							$aLiteraturquellen = array(); 
							$tagsApprovalString = $tagsApproalStringForRef;
							if($tagsApprovalString != "")
								$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");
						}else if( count($prueferFromParray) > 0 ){
							/* When only @P is present in a symptom */
							$tagsApproalStringForPrue = "";
							foreach ($prueferFromParray as $prueferPkey => $prueferPval) {
								$prueferPval = trim($prueferPval);
								$tagsApproalStringForPrue .= $prueferPval."{#^#}";
							}

							$tagsApprovalString = $tagsApproalStringForPrue;
							if($tagsApprovalString != "")
								$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");
						}else{
							if(!empty($endingBracketsArray)){
								$lastBracketedString = (isset($endingBracketsArray[0]) AND $endingBracketsArray[0] != "") ? trim($endingBracketsArray[0]) : "";
							}else if(!empty($middleBracketArray) AND count($middleBracketArray) == 1){
								$reArrangeMiddleBracketArray = array_values($middleBracketArray);
								$middleBracketString = (isset($reArrangeMiddleBracketArray[0]) AND $reArrangeMiddleBracketArray[0] != "") ? trim($reArrangeMiddleBracketArray[0]) : "";
							}
						}

						if( isset($lastBracketedString) AND $lastBracketedString != "" ){
							// removing bracket brackets data from searchable text
							if(mb_strpos($searchableText, $lastBracketedString) !== false){
								$searchableText = str_replace($lastBracketedString, "", $searchableText);
							}
						}

						// Removing blank tags
						$searchableText = removeBlankTags($searchableText);
						// Removing blank parentheses
						$searchableText = preg_replace('#\(\s*\)#', '', $searchableText);
						$searchableText = str_replace("()", "", $searchableText);
						$searchableText = removeBlankParenthesesFormSearchableText($searchableText);
						$searchableText = removeBlankTags($searchableText);
						// Removing blank square brackets
						$searchableText = preg_replace('#\[\s*\]#', '', $searchableText);
						$searchableText = str_replace("[]", "", $searchableText);
						$searchableText = removeBlankBracketsFormSearchableText($searchableText);
						$searchableText = removeBlankTags($searchableText);

						$bracketedString = (!empty($allBrackets)) ? implode(", ", $allBrackets) : null;
						$middleBracketApprovalString = ( isset($middleBracketString) AND $middleBracketString != "" ) ? $middleBracketString : null;
						$approvableStringLastOrMiddle = ( isset($lastBracketedString) AND $lastBracketedString != "" ) ? $lastBracketedString : "";
						if(isset($lastBracketedString) AND $lastBracketedString != "")
							$approvableStringLastOrMiddle = $lastBracketedString;
						else if(isset($middleBracketString) AND $middleBracketString != "")
							$approvableStringLastOrMiddle = $middleBracketString;
						$approvableString = ( isset($approvableStringLastOrMiddle) AND $approvableStringLastOrMiddle != "" ) ? $approvableStringLastOrMiddle : $tagsApprovalString;

						$data = array();
						$data['Beschreibung']=mysqli_real_escape_string($db, $Beschreibung);
						$data['BeschreibungOriginal']=mysqli_real_escape_string($db, $BeschreibungOriginal);
						$data['BeschreibungPlain']=mysqli_real_escape_string($db, $BeschreibungPlain);
						$data['searchable_text']=mysqli_real_escape_string($db, $searchableText);
						$data['bracketedString']= ($bracketedString != "") ? mysqli_real_escape_string($db, $bracketedString) : null;
						$data['timeString']=mysqli_real_escape_string($db, $timeString);
						$data['approval_string'] = ( isset($approvableString) AND $approvableString != "" ) ? $approvableString : null;
						$symptomDataArrInImportedData[] = $data;


						if ($Symptomnummer > 0)
							$Symptomnummer += 1;
						
						$Beschreibung = '';
						$Graduierung = '';
						// $BereichID = '';
						$Fussnote = '';
						$Verweiss = '';
						$Unklarheiten = '';
						$Kommentar = '';
						$bracketedString = '';
						$timeString = '';
						if($parenthesesStringArray){
							$parenthesesStringArray= array ();
						}
						if($timeStringArray){
							$timeStringArray= array ();
						}
						if($bracketedStringArray){
							$bracketedStringArray= array ();
						}
						if($strongRedStringArray){
							$strongRedStringArray= array ();
						}
						if($strongBlueStringArray){
							$strongBlueStringArray= array ();
						}
						if ($aLiteraturquellen) {
							$aLiteraturquellen = array ();
							$EntnommenAus = '';
						}
						if ($prueferFromParray) {
							$prueferFromParray = array ();
							//$Pruefers = '';
						}
						$prueferIDarray = array();

						$needApproval = 0;
						$remedyArray = array();
						$prueferArray = array();
						$referenceArray = array();
						$prueferPriority = 0;
						$remedyPriority = 0;
						$partOfSymptomPriority = 0;
						$referencePriority = 0;
						$referenceWithNoAuthorPriority = 0;
						$remedyWithSymptomPriority = 0;
						$hyphenPrueferPriority = 0;
						$hyphenReferencePriority = 0;
						$hyphenApprovalString = "";
						$moreThanOneTagStringPriority = 0;
						$directOrderPriority = 0;
						$tagsApprovalString = "";
						$lastBracketedString = "";
						$isPreDefinedTagsApproval = 0;
						$symptomOfDifferentRemedy = "";
						$workingString = "";
						$allBrackets = array();
						$endingBracketsArray = array();
						$middleBracketArray = array();
						$approvalFor = 0;
						$middleBracketApprovalString = "";
						$isSymptomNumberMismatch = 0;
						$searchableText = "";
						$middleBracketString = "";
					}
				}
				$resultData['imported_data'] = $symptomDataArrInImportedData;

				if(count($symptomIdArrInDB) == 0 OR count($symptomDataArrInImportedData) == 0 OR count($symptomIdArrInDB) != count($symptomDataArrInImportedData)){
					$status = "error";
					$message = "Total number of the symptoms not matched.";
				} else {
					
					$confirmationNeeded = 0;
					foreach ($symptomDataArrInImportedData as $importedDataKey => $importedDataVal) {
						$needClearance = 0; // 0= No, 1= Yes
						// if( ($symptomDataArrInImportedData[$importedDataKey]['approval_string'] == "" AND $symptomIdArrInDB[$importedDataKey]['does_it_have_approval_string'] == 1) OR ($symptomDataArrInImportedData[$importedDataKey]['approval_string'] != "" AND $symptomIdArrInDB[$importedDataKey]['does_it_have_approval_string'] == 0) ){
						// 	$needClearance = 1;
						// 	$confirmationNeeded = 1;
						// }
						$insertData = array();
						$insertData['Beschreibung_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['Beschreibung'] : null;
						$insertData['Beschreibung_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['Beschreibung'] : null;
						$insertData['BeschreibungOriginal_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'] : null;
						$insertData['BeschreibungOriginal_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'] : null;
						$insertData['BeschreibungFull_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'] : null;
						$insertData['BeschreibungFull_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'] : null;
						$insertData['BeschreibungPlain_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungPlain'] : null;
						$insertData['BeschreibungPlain_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['BeschreibungPlain'] : null;
						$insertData['searchable_text_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['searchable_text'] : null;
						$insertData['searchable_text_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['searchable_text'] : null;
						$insertData['bracketedString_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['bracketedString'] : null;
						$insertData['bracketedString_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['bracketedString'] : null;
						$insertData['timeString_de'] = (isset($add_translation_language) AND $add_translation_language == "de") ? $symptomDataArrInImportedData[$importedDataKey]['timeString'] : null;
						$insertData['timeString_en'] = (isset($add_translation_language) AND $add_translation_language == "en") ? $symptomDataArrInImportedData[$importedDataKey]['timeString'] : null;
						$insertData['symptom_id'] = $symptomIdArrInDB[$importedDataKey]['symptom_id'];

						$tempTransSymptomInsertQuery = "INSERT INTO temp_translation_symptoms (master_id, arznei_id, quelle_id, symptom_id, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, need_approval, ersteller_datum) VALUES (NULLIF('".$add_translation_master_id."', ''), NULLIF('".$add_translation_arznei_id."', ''), NULLIF('".$add_translation_quelle_id."', ''), NULLIF('".$insertData['symptom_id']."', ''), NULLIF('".$insertData['Beschreibung_de']."', ''), NULLIF('".$insertData['Beschreibung_en']."', ''), NULLIF('".$insertData['BeschreibungOriginal_de']."', ''), NULLIF('".$insertData['BeschreibungOriginal_en']."', ''), NULLIF('".$insertData['BeschreibungFull_de']."', ''), NULLIF('".$insertData['BeschreibungFull_en']."', ''), NULLIF('".$insertData['BeschreibungPlain_de']."', ''), NULLIF('".$insertData['BeschreibungPlain_en']."', ''), NULLIF('".$insertData['searchable_text_de']."', ''), NULLIF('".$insertData['searchable_text_en']."', ''), NULLIF('".$insertData['bracketedString_de']."', ''), NULLIF('".$insertData['bracketedString_en']."', ''), NULLIF('".$insertData['timeString_de']."', ''), NULLIF('".$insertData['timeString_en']."', ''), ".$needClearance.", '".$date."')";
						$db->query($tempTransSymptomInsertQuery);
					}

					if($confirmationNeeded == 1){
						$tempTransQuelleInsertQuery = "INSERT INTO temp_translation_quelle (master_id, quelle_id, arznei_id, translation_language, translation_method, ersteller_datum) VALUES (NULLIF('".$add_translation_master_id."', ''), NULLIF('".$add_translation_quelle_id."', ''), NULLIF('".$add_translation_arznei_id."', ''), NULLIF('".$add_translation_language."', ''), NULLIF('".$translation_method."', ''), '".$date."')";
						$db->query($tempTransQuelleInsertQuery);

						$status = "need_approval";
						$message = "Need uesr approval";
					} else {
						$updateQuelleMasterWithLanguageQuery = "UPDATE quelle_import_master SET is_symptoms_available_in_".$importingLanguageSuffix." = 1, translation_method_of_".$importingLanguageSuffix." = NULLIF('".$translation_method."', '') WHERE id = '".$add_translation_master_id."'";
						$db->query($updateQuelleMasterWithLanguageQuery);

						// Updating the single/individual sources language status START
						if($isCombinedSource == 1 AND $isCombinedSourceSaved == 1 AND $combinedSourceCompletedTable != ""){
							$add_translation_quelle_ids = ($add_translation_quelle_id != "") ? explode(',', $add_translation_quelle_id) : array();
							$allInvolvedIndividualQuelleIds = (!empty($add_translation_quelle_ids)) ? getAllRelatedIndividualSourcesNew($add_translation_quelle_ids) : array();
							if(!empty($allInvolvedIndividualQuelleIds)){
								foreach ($allInvolvedIndividualQuelleIds as $indiQuelleKey => $indiQuelleVal) {
									$updateQuelleMasterWithLanguageQuery = "UPDATE quelle_import_master SET is_symptoms_available_in_".$importingLanguageSuffix." = 1, translation_method_of_".$importingLanguageSuffix." = NULLIF('".$translation_method."', '') WHERE quelle_id = '".$indiQuelleVal."' AND arznei_id = '".$add_translation_arznei_id."'";
									$db->query($updateQuelleMasterWithLanguageQuery);
								}
							}
						}
						// Updating the single/individual sources language status END

						foreach ($symptomDataArrInImportedData as $importedDataKey => $importedDataVal) {
							$updData = array();
							$updData['Beschreibung_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['Beschreibung'];
							$updData['BeschreibungOriginal_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'];
							$updData['BeschreibungFull_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['BeschreibungOriginal'];
							$updData['BeschreibungPlain_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['BeschreibungPlain'];
							$updData['searchable_text_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['searchable_text'];
							$updData['bracketedString_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['bracketedString'];
							$updData['timeString_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$importedDataKey]['timeString'];
							$updData['symptom_id'] = $symptomIdArrInDB[$importedDataKey]['symptom_id'];
							$updData['original_symptom_id'] = $symptomIdArrInDB[$importedDataKey]['original_symptom_id'];


							// For combined sources dynamic "_completed" table updates SATRT
							if($isCombinedSource == 1 AND $isCombinedSourceSaved == 1 AND $combinedSourceCompletedTable != ""){
								$checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$combinedSourceCompletedTable."'");
								if(mysqli_num_rows($checkIfComparisonCompleteTableExist) == 0)
								{
									$updMainSymptomsQuery = "UPDATE ".$combinedSourceCompletedTable." SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE symptom_id = '".$updData['symptom_id']."'";
									$db->query($updMainSymptomsQuery);
								}
							}
							// For combined sources dynamic "_completed" table updates END

							$updMainSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$updData['symptom_id']."'";
							$db->query($updMainSymptomsQuery);

							if ($translation_method == "Professional Translation") {
								$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE original_symptom_id = '".$updData['symptom_id']."'";
								$db->query($updSavedComparisonSymptomsQuery);

								$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE original_symptom_id = '".$updData['original_symptom_id']."'";
								$db->query($updSavedComparisonSymptomsQuery);

								// For symptom backup table
								$updSavedComparisonBackupSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE original_symptom_id = '".$updData['symptom_id']."'";
								$db->query($updSavedComparisonBackupSymptomsQuery);

								$updSavedComparisonBackupSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE original_symptom_id = '".$updData['original_symptom_id']."'";
								$db->query($updSavedComparisonBackupSymptomsQuery);
							} else {
								$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE original_symptom_id = '".$updData['symptom_id']."' AND (Beschreibung_".$importingLanguageSuffix." IS NULL OR Beschreibung_".$importingLanguageSuffix." = '')");
								if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
									while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
										$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
										$db->query($updSavedComparisonSymptomsQuery);
									}
								}

								$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE original_symptom_id = '".$updData['original_symptom_id']."' AND (Beschreibung_".$importingLanguageSuffix." IS NULL OR Beschreibung_".$importingLanguageSuffix." = '')");
								if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
									while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
										$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_test SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
										$db->query($updSavedComparisonSymptomsQuery);
									}
								}

								// For symptom backup table
								$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_backup WHERE original_symptom_id = '".$updData['symptom_id']."' AND (Beschreibung_".$importingLanguageSuffix." IS NULL OR Beschreibung_".$importingLanguageSuffix." = '')");
								if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
									while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
										$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
										$db->query($updSavedComparisonSymptomsQuery);
									}
								}

								$getSymptomTranslationInfo = mysqli_query($db,"SELECT id FROM quelle_import_backup WHERE original_symptom_id = '".$updData['original_symptom_id']."' AND (Beschreibung_".$importingLanguageSuffix." IS NULL OR Beschreibung_".$importingLanguageSuffix." = '')");
								if(mysqli_num_rows($getSymptomTranslationInfo) > 0){
									while($symptomRow = mysqli_fetch_array($getSymptomTranslationInfo)){
										$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
										$db->query($updSavedComparisonSymptomsQuery);
									}
								}
							}
						}
						
						// Collecting all backup sets and updating them accordingly.
						$getQuelleMasterBackupQuery = mysqli_query($db,"SELECT id FROM quelle_import_master_backup WHERE arznei_id = '".$add_translation_arznei_id."' AND original_quelle_id = '".$add_translation_quelle_id."'");
						if(mysqli_num_rows($getQuelleMasterBackupQuery) > 0){
							while($quelleMasterBackupRow = mysqli_fetch_array($getQuelleMasterBackupQuery)){
								$backupSouceSymptomInfo = mysqli_query($db,"SELECT id, Beschreibung_".$importingLanguageSuffix." FROM quelle_import_backup WHERE master_id = '".$quelleMasterBackupRow['id']."'");
								$totalBackupSouceSymptoms = mysqli_num_rows($backupSouceSymptomInfo);

								if(count($symptomDataArrInImportedData) == $totalBackupSouceSymptoms){
									$arrayKey = 0;
									while($symptomRow = mysqli_fetch_array($backupSouceSymptomInfo)){
										$updData = array();
										$updData['Beschreibung_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['Beschreibung'];
										$updData['BeschreibungOriginal_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['BeschreibungOriginal'];
										$updData['BeschreibungFull_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['BeschreibungOriginal'];
										$updData['BeschreibungPlain_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['BeschreibungPlain'];
										$updData['searchable_text_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['searchable_text'];
										$updData['bracketedString_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['bracketedString'];
										$updData['timeString_'.$importingLanguageSuffix] = $symptomDataArrInImportedData[$arrayKey]['timeString'];

										if ($translation_method == "Professional Translation") {
											$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
											$db->query($updSavedComparisonSymptomsQuery);
										} else {
											if($symptomRow['Beschreibung_'.$importingLanguageSuffix] == ""){
												$updSavedComparisonSymptomsQuery = "UPDATE quelle_import_backup SET Beschreibung_".$importingLanguageSuffix." = NULLIF('".$updData['Beschreibung_'.$importingLanguageSuffix]."', ''), BeschreibungOriginal_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungOriginal_'.$importingLanguageSuffix]."', ''), BeschreibungFull_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungFull_'.$importingLanguageSuffix]."', ''), BeschreibungPlain_".$importingLanguageSuffix." = NULLIF('".$updData['BeschreibungPlain_'.$importingLanguageSuffix]."', ''), searchable_text_".$importingLanguageSuffix." = NULLIF('".$updData['searchable_text_'.$importingLanguageSuffix]."', ''), bracketedString_".$importingLanguageSuffix." = NULLIF('".$updData['bracketedString_'.$importingLanguageSuffix]."', ''), timeString_".$importingLanguageSuffix." = NULLIF('".$updData['timeString_'.$importingLanguageSuffix]."', '') WHERE id = '".$symptomRow['id']."'";
												$db->query($updSavedComparisonSymptomsQuery);
											}
										}
										
										$arrayKey++;
									}

									// For quelle master backup table
									$updateQuelleMasterBackupWithLanguageQuery = "UPDATE quelle_import_master_backup SET is_symptoms_available_in_".$importingLanguageSuffix." = 1, translation_method_of_".$importingLanguageSuffix." = NULLIF('".$translation_method."', '') WHERE id = '".$quelleMasterBackupRow['id']."'";
									$db->query($updateQuelleMasterBackupWithLanguageQuery);
								}
							}
						}

						// DELETING THE TEMP TRANSLATION APPROVAL DATA OF TEMP TABLE
						$deleteTempTransQuelleQuery="DELETE FROM temp_translation_quelle WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
						$db->query($deleteTempTransQuelleQuery);

						$deleteTempTransSymptomQuery="DELETE FROM temp_translation_symptoms WHERE arznei_id = '".$add_translation_arznei_id."' AND quelle_id = '".$add_translation_quelle_id."' AND master_id = '".$add_translation_master_id."'";
						$db->query($deleteTempTransSymptomQuery);
						
						$status = "success";
						$message = "success";
					}
				}

			} else {
				$status = "error";
				$message = "Source may already has this language";
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