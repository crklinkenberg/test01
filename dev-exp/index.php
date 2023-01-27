<?php
include '../lang/GermanWords.php';
include '../config/route.php';
include 'sub-section-config.php';

// SUB SECTIONS CODE START
$is_opened_a_saved_comparison = (isset($comparisonTableDataArr['is_opened_a_saved_comparison']) AND !empty($comparisonTableDataArr['is_opened_a_saved_comparison'])) ? $comparisonTableDataArr['is_opened_a_saved_comparison'] : "";
$arzneiId = (isset($_GET['arznei_id_custom']) AND $_GET['arznei_id_custom'] != "") ? $_GET['arznei_id_custom'] : "";
if(isset($_POST['submit_hidden'])){
	if(isset($_POST['settings']) && $_POST['settings'] == "default_setting"){

		/* Rule 1 Start */
		$CleanedText = str_replace ( '</em><em>', '', $_POST['symptomtext'] );

		$CleanedText = str_replace ( array (
			"\r",
			"\t" 
		), '', $CleanedText );
		$CleanedText = trim ( $CleanedText );
		$Lines = explode ( "\n", $CleanedText );
		if (count ( $Lines ) > 0) {
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
			$approvalFor = 0; // (0 = end bracket, 1 = middle bracket)
			$middleBracketApprovalString = "";
			$isCodingWithSymptomNumber = 1;
			$isSymptomNumberMismatch = 0;
			$searchableText = "";
			$isPreDefinedReferenceSection = 0;
			$preDefinedReferenceArray = array();
			$preDefinedReferenceNumberArray = array();
			$preDefinedSymptomReferenceLinkingArray = array();
			$preDefinedaLiteraturquellen = array ();

			/* quelle_import_master table fields start */
			$importRule = (isset($_POST['settings']) AND $_POST['settings'] != "") ? mysqli_real_escape_string($db, $_POST['settings']) : null;
			$importing_language = (isset($_POST['importing_language']) AND $_POST['importing_language'] != "") ? mysqli_real_escape_string($db, $_POST['importing_language']) : null;
			$masterArzneiId = (isset($_POST['arznei_id']) AND $_POST['arznei_id'] != "") ? mysqli_real_escape_string($db, $_POST['arznei_id']) : null;
			$masterQuelleId = (isset($_POST['quelle_id']) AND $_POST['quelle_id'] != "") ? mysqli_real_escape_string($db, $_POST['quelle_id']) : null;
			$importComment = (isset($_POST['import_comment']) AND $_POST['import_comment'] != "") ? mysqli_real_escape_string($db, $_POST['import_comment']) : null;
			$masterQuelleCode = null;
			$masterPrueferIdArray = (isset($_POST['pruefer_id']) AND !empty($_POST['pruefer_id'])) ? $_POST['pruefer_id'] : array();
			$excludingSymptomsChapters = (isset($_POST['excluding_symptoms_chapters']) AND !empty($_POST['excluding_symptoms_chapters'])) ? mysqli_real_escape_string($db, $_POST['excluding_symptoms_chapters']) : null;
			$excludingSymptomsChaptersArray = array();
			if($excludingSymptomsChapters != ""){
				$preTrimedData = explode(",",$excludingSymptomsChapters);
				$preTrimedData = array_map('trim',$preTrimedData);
				$excludingSymptomsChaptersArray = array_map('mb_strtolower',$preTrimedData);
			}

			$masterPrueferIds = null;
			if(!empty($masterPrueferIdArray))
				$masterPrueferIds = implode(",", $masterPrueferIdArray);
			/* quelle_import_master table fields end */

			$isSourceAlreadyExist = 0;
			$checkAlreadyExist = mysqli_query($db, "SELECT id FROM quelle_import_master where arznei_id = '".$masterArzneiId."' AND quelle_id = '".$masterQuelleId."'");
			if(mysqli_num_rows($checkAlreadyExist) > 0){
				$isSourceAlreadyExist = 1;
			}
			$checkAlreadyExistInTemp = mysqli_query($db, "SELECT id FROM temp_quelle_import_master where arznei_id = '".$masterArzneiId."' AND quelle_id = '".$masterQuelleId."'");
			if(mysqli_num_rows($checkAlreadyExistInTemp) > 0){
				$isSourceAlreadyExist = 1;
			}

			if($isSourceAlreadyExist == 1){
				header('Location: '.$baseUrl.'?error=2');
				exit();
			}

			$isThereAnyTransactionError = 0;
			/* MySQL Transaction START */
			try{
				// First of all, let's begin a transaction
				$db->begin_transaction();

				/* Fetching the constant settigs and quelle code of the selected Quelle Start */
				$sourceComment = "";
				if(isset($_POST['quelle_id']) AND $_POST['quelle_id'] != ""){
					$constantResult = mysqli_query($db, "SELECT quelle.is_coding_with_symptom_number, quelle.code, quelle.jahr, quelle.kommentar FROM quelle WHERE quelle.quelle_id = '".$_POST['quelle_id']."'");
					if(mysqli_num_rows($constantResult) > 0){
						$constantData = mysqli_fetch_assoc($constantResult);
						$isCodingWithSymptomNumber = $constantData['is_coding_with_symptom_number'];
						if($constantData['jahr'] != "" AND $constantData['code'] != "")
							$rowQuelleCode = trim(str_replace(trim($constantData['jahr']), '', $constantData['code']));
						else
							$rowQuelleCode = trim($constantData['code']);
						$masterQuelleCode = trim($rowQuelleCode." ".$constantData['jahr']);
						$sourceComment = trim($constantData['kommentar']);
					}
				}
				/* Fetching the constant settigs and quelle code of the selected Quelle End */
				$masterQuery="INSERT INTO temp_quelle_import_master (import_rule, importing_language, arznei_id, quelle_id, pruefer_ids, excluding_symptoms_chapters, import_comment, ersteller_datum) VALUES ('".$importRule."', NULLIF('".$importing_language."', ''), ".$masterArzneiId.", ".$masterQuelleId.", NULLIF('".$masterPrueferIds."', ''),  NULLIF('".$excludingSymptomsChapters."', ''), NULLIF('".$importComment."', ''), '".$date."')";
	            $db->query($masterQuery);
	            $masterId = mysqli_insert_id($db);

	            // If we arrive here, it means that no exception was thrown 
			    // i.e. no query has failed, and we can commit the transaction
			    $db->commit();
			}catch (Exception $e) {
			    // An exception has been thrown
			    // We must rollback the transaction
			    $db->rollback();
			    $isThereAnyTransactionError = 1;
				}
			/* MySQL Transaction END */

			// If No Transaction error occur above
			if($isThereAnyTransactionError == 0){
				/* Collecting Stored available synonyms START */
				$availableSynonyms = array();
				$globalStopWords = getStopWords();
				if($importing_language == "de" OR $importing_language == "en"){
					$synonymResult = mysqli_query($db, "SELECT synonym_id, word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn FROM synonym_".$importing_language);
					if(mysqli_num_rows($synonymResult) > 0){
						while($synonymRow = mysqli_fetch_array($synonymResult)){
							$synonymData = array();
							$synonymData['synonym_id'] = $synonymRow['synonym_id'];
							$synonymData['word'] = mb_strtolower($synonymRow['word']);
							$synonymData['strict_synonym'] = mb_strtolower($synonymRow['strict_synonym']);
							$synonymData['synonym_partial_1'] = mb_strtolower($synonymRow['synonym_partial_1']);
							$synonymData['synonym_partial_2'] = mb_strtolower($synonymRow['synonym_partial_2']);
							$synonymData['synonym_general'] = mb_strtolower($synonymRow['synonym_general']);
							$synonymData['synonym_minor'] = mb_strtolower($synonymRow['synonym_minor']);
							$synonymData['synonym_nn'] = mb_strtolower($synonymRow['synonym_nn']);
							$availableSynonyms[] = $synonymData;
						}
					}
				}
				/* Collecting Stored available synonyms END */
				foreach ( $Lines as $iline => $line ) {
					/* MySQL Transaction START */
					try{
						// First of all, let's begin a transaction
						$db->begin_transaction();
						// Pre defined reference section work start
						$lineStringForPreDefinedReference = trim(strip_tags($line));
						if(strtolower($lineStringForPreDefinedReference) == "literatur start"){
							$isPreDefinedReferenceSection = 1;
							continue;
						}
						if(strtolower($lineStringForPreDefinedReference) == "literatur end"){
							$isPreDefinedReferenceSection = 0;	
							continue;
						}

						if($isPreDefinedReferenceSection == 1){
							$lineExplodedBySpace = explode(" ", $lineStringForPreDefinedReference);
							$referenceNumber = $lineExplodedBySpace[0];
							$referenceNumber = ($referenceNumber != "") ? preg_replace("/[^A-Za-z0-9 ]/", '', $referenceNumber) : $referenceNumber;
							array_shift($lineExplodedBySpace);
					        $preDefinedFullReferenceTxt = implode(" ", $lineExplodedBySpace);
					        $preReferenceAutor = "";
    						$preReferenceTxt = "";
    						$preDefinedFullReferenceInArray = explode(",", $preDefinedFullReferenceTxt);
				        	if(count($preDefinedFullReferenceInArray) >= 2){
				        		$preReferenceAutor = trim($preDefinedFullReferenceInArray[0]);
				        		array_shift($preDefinedFullReferenceInArray);
				        		$preReferenceTxt = implode(",", $preDefinedFullReferenceInArray);
				        	}
				        	if($referenceNumber != "" AND $preReferenceAutor != "" AND $preReferenceTxt != "")
				        	{
				        		$preDefinedReferenceId = "";
				        		$referenceExistanceCheck = mysqli_query($db, "SELECT reference_id FROM reference WHERE full_reference = '".$preDefinedFullReferenceTxt."' LIMIT 1");
								if(mysqli_num_rows($referenceExistanceCheck) > 0){
									$existingReferenceRow = mysqli_fetch_assoc($referenceExistanceCheck);
									$preDefinedReferenceId = $existingReferenceRow['reference_id'];
								} else {
									$newReferenceInsertQuery="INSERT INTO reference (full_reference, autor, reference, ersteller_datum) VALUES (NULLIF('".$preDefinedFullReferenceTxt."', ''), NULLIF('".$preReferenceAutor."', ''), NULLIF('".$preReferenceTxt."', ''), '".$date."')";
						            $db->query($newReferenceInsertQuery);
						            $preDefinedReferenceId = mysqli_insert_id($db);
								}
								if($preDefinedReferenceId != ""){
									$preDefinedReferenceArray[] = array(
										'reference_id' => $preDefinedReferenceId,
										'reference_number' => $referenceNumber,
										'full_reference' => $preDefinedFullReferenceTxt,
									);
									$referenceNumberUppercase = strtoupper($referenceNumber);
									if(!array_key_exists($referenceNumber, $preDefinedReferenceNumberArray))
										$preDefinedReferenceNumberArray[$referenceNumber] = $preDefinedReferenceId;
									if(!array_key_exists($referenceNumberUppercase, $preDefinedReferenceNumberArray))
										$preDefinedReferenceNumberArray[$referenceNumberUppercase] = $preDefinedReferenceId;
								}
				        	}
							continue;
						}
						// Pre defined reference section work end
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
									if(trim(strip_tags($param)) == "END")
										$Graduierung = "";
									else
										$Graduierung = trim($param);
									break;
								
								// Kapitel, setzt in DS "KapitelID"
								// case 'B' :
								case 'K' :
									if(trim(strip_tags($param)) == "END")
										$BereichID = "";
									else
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
							$opentag = mb_strpos ( $line, '(' );
							$p = mb_strpos ( $line, ')' );
							if($opentag !== false AND $p !== false){
								$numericStringPart = trim ( mb_substr ( $line, 0, $p + 1 ) );
								if($numericStringPart != ""){
									$onlyNumericStringPart = trim ( mb_substr ( $numericStringPart, $opentag ) );
									$newLineString = str_replace ($onlyNumericStringPart, '', $line);
									$newLineString = removeBlankTags($newLineString);

									$NewSymptomNr = str_replace ( array ( '(', ')' ), '', $onlyNumericStringPart );
									if (is_numeric ( $NewSymptomNr )) {
										$line = trim($newLineString);
										if($NewSymptomNr != $Symptomnummer)
											$isSymptomNumberMismatch = 1;
										$Beschreibung = $line;
										$cleanline = strip_tags($line);
									} else {
										$NewSymptomNr = 0;
										$Beschreibung = $line;
									}
								}else{
									$NewSymptomNr = 0;
									$Beschreibung = $line;
								}
							} else {
								$NewSymptomNr = 0;
								$Beschreibung = $line;
							}


							// $p = mb_strpos ( $line, ')' );
							// if ($p > 0) {
							// 	$NewSymptomNr = trim ( mb_substr ( $line, 1, $p - 1 ) );
							// 	if (is_numeric ( $NewSymptomNr )) {
							// 		if($NewSymptomNr != $Symptomnummer)
							// 			$isSymptomNumberMismatch = 1;
							// 		$Beschreibung = trim ( mb_substr ( $line, $p + 1 ) );
							// 		$cleanline = trim ( mb_substr ( $cleanline, $p + 1 ) );
							// 	} else {
							// 		$NewSymptomNr = 0;
							// 		$Beschreibung = $line;
							// 	}
							// }
						} else if($FirstChar == '['){
							/* 
							* parseing symptoms nummer which has parentheses between symptom nummer 
							* Eg : [5] Sad, pusillanimous, full of weariness of life.
							*/
							$opentag = mb_strpos ( $line, '[' );
							$p = mb_strpos ( $line, ']' );
							if($opentag !== false AND $p !== false){
								$numericStringPart = trim ( mb_substr ( $line, 0, $p + 1 ) );
								if($numericStringPart != ""){
									$onlyNumericStringPart = trim ( mb_substr ( $numericStringPart, $opentag ) );
									$newLineString = str_replace ($onlyNumericStringPart, '', $line);
									$newLineString = removeBlankTags($newLineString);

									$NewSymptomNr = str_replace ( array ( '[', ']' ), '', $onlyNumericStringPart );
									if (is_numeric ( $NewSymptomNr )) {
										$line = trim($newLineString);
										if($NewSymptomNr != $Symptomnummer)
											$isSymptomNumberMismatch = 1;
										$Beschreibung = $line;
										$cleanline = strip_tags($line);
									} else {
										$NewSymptomNr = 0;
										$Beschreibung = $line;
									}
								}else{
									$NewSymptomNr = 0;
									$Beschreibung = $line;
								}
							}else{
								$NewSymptomNr = 0;
								$Beschreibung = $line;
							}
							// if ($p > 0) {
							// 	$NewSymptomNr = trim ( mb_substr ( $line, 1, $p - 1 ) );
							// 	echo htmlentities($NewSymptomNr);
							// 	if (is_numeric ( $NewSymptomNr )) {
							// 		if($NewSymptomNr != $Symptomnummer)
							// 			$isSymptomNumberMismatch = 1;
							// 		$Beschreibung = trim ( mb_substr ( $line, $p + 1 ) );
							// 		$cleanline = trim ( mb_substr ( $cleanline, $p + 1 ) );
							// 	} else {
							// 		$NewSymptomNr = 0;
							// 		$Beschreibung = $line;
							// 	}
							// }
							// exit;
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
									')',
									']' 
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
							$isExcludedInComparison = 0;
							if(!empty($excludingSymptomsChaptersArray) AND $BereichID != ""){
								$chapterInLowercase = mb_strtolower(trim($BereichID));
								if(in_array($chapterInLowercase, $excludingSymptomsChaptersArray)){
									$isExcludedInComparison = 1;
								}
							}

							if($Kommentar != ""){
								// Setting the import mask comment for all the symptom
								$Kommentar = $Kommentar.", ".$importComment;
							} else {
								$Kommentar = $importComment;
							}
							// Comment that is given in the time of creating the source
							if($sourceComment != "")
								$Kommentar = $Kommentar.", ".$sourceComment;

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
							$FirstOneChar = trim(mb_substr(strip_tags($line), 0, 1));
							$FirstTwoChar = trim(mb_substr(strip_tags($line), 0, 2));
							$FirstThreeChar = trim(mb_substr(strip_tags($line), 0, 3));
							$FirstFourChar = trim(mb_substr(strip_tags($line), 0, 4));
							$FirstFiveChar = trim(mb_substr(strip_tags($line), 0, 5));

							$cleanLineToGetLastChar = rtrim(trim($line), '.');
							$cleanLineToGetLastChar = rtrim(trim($cleanLineToGetLastChar), ',');
							$cleanLineToGetLastChar = rtrim(trim($cleanLineToGetLastChar), ';');
							$LastCharCheck = mb_substr ( trim($cleanLineToGetLastChar), mb_strlen ( trim($cleanLineToGetLastChar) ) - 1 );
							// Checking is there any open tag
							preg_match_all("#<[^/>]*>#i", $line, $matcheOpenTags, PREG_OFFSET_CAPTURE);
							// Count the number of occurance of- *,°
							$totalAsterisks = mb_substr_count($line, "*"); 
							$totalDegrees = mb_substr_count($line, "°");
							
							if($FirstFiveChar == "|||||") {
								// E.g. |||||symptom strong
								$line = ltrim($line,"|||||");
								$line = '<bar-five-normal>'.$line.'</bar-five-normal>';
							} else if($FirstFiveChar == "||||-") {
								// E.g. ||||-symptom strong
								$line = ltrim($line,"||||-");
								$line = '<bar-four-and-half-normal>'.$line.'</bar-four-and-half-normal>';
							} else if($FirstFourChar == "||||") {
								// E.g. ||||symptom strong
								$line = ltrim($line,"||||");
								$line = '<bar-four-normal>'.$line.'</bar-four-normal>';
							} else if($FirstFourChar == "|||-") {
								// E.g. |||-symptom strong
								$line = ltrim($line,"|||-");
								$line = '<bar-three-and-half-normal>'.$line.'</bar-three-and-half-normal>';
							} else if($FirstThreeChar == "|||") {
								// E.g. |||symptom strong
								$line = ltrim($line,"|||");
								$line = '<bar-three-normal>'.$line.'</bar-three-normal>';
							} else if($FirstTwoChar == "||") {
								// E.g. ||symptom strong
								$line = ltrim($line,"||");
								$line = '<bar-two-normal>'.$line.'</bar-two-normal>';
							} else if($FirstOneChar == "|") {
								// E.g. |symptom strong
								$line = ltrim($line,"|");
								$line = '<bar-one-normal>'.$line.'</bar-one-normal>';
							} else if($FirstOneChar == "π") {
								// E.g. πsymptom strong
								$line = ltrim($line,"π");
								$line = '<pi-normal>'.$line.'</pi-normal>';
							} else if($FirstCharCheck == "(" AND $LastCharCheck == ")") {
								// It is format - (Normal)
								$line = '<parentheses-normal>'.$line.'</parentheses-normal>';
							} 
							else if($LastCharCheck == "°" AND $totalDegrees == 1 AND $totalAsterisks == 0)
							{
								// It is format - Kursiv,° Normal,° Fett,°
								$line = structureEndingWithDegreeFormatString($line, 'endwithdegree');
							} else {
								$line = separateTheApplicableStratingSign($line, '*');
								$line = separateTheApplicableStratingSign($line, '°');
								$line = removeBlankTags($line);

								$line = convertPatternPortions($line, '*', 'asterisk');
								$line = convertPatternPortions($line, '°', 'degree');
								// Structure the non * and ° portion strings
								$line = structureNonAsteriskAndDegreePortions($line, 'non-asterisk-degree');
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
					
							$escapeCustomTags = "<parentheses-normal><bar-one-normal><bar-two-normal><bar-three-normal><bar-four-normal><bar-five-normal><bar-four-and-half-normal><bar-three-and-half-normal><pi-normal>";
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
							$searchableText = $BeschreibungOriginal;
							// conversion of pre define reference number in original symptom version
							foreach ($preDefinedReferenceNumberArray as $refNumberKey => $refNumberVal) {
								$searchableReferenceNumber = "[".$refNumberKey."]";
								$BeschreibungOriginal = str_replace($searchableReferenceNumber, "<sup>".$refNumberKey."</sup>", $BeschreibungOriginal);

								$referenceNumberOccurrence = mb_strpos ($searchableText, $searchableReferenceNumber);
								if($referenceNumberOccurrence !== false){
									// removing found reference number sections from this version of symptom
									$searchableText = str_replace($searchableReferenceNumber, "", $searchableText);
									$cleanline = str_replace($searchableReferenceNumber, "", $cleanline);

									$tempPreDefinedReferenceArrKey = get_base_key_in_md_array($refNumberVal, 'reference_id', $preDefinedReferenceArray);
									if($tempPreDefinedReferenceArrKey !== false){
										$preDefineReferenceReturnArr = $preDefinedReferenceArray[$tempPreDefinedReferenceArrKey];
										if(!in_array($refNumberVal, $preDefinedSymptomReferenceLinkingArray))
										{
											$preDefinedSymptomReferenceLinkingArray[] = $refNumberVal;
											$preDefinedaLiteraturquellen[] = $preDefinedReferenceArray[$tempPreDefinedReferenceArrKey]['full_reference'];
										}
									}
								}
							}
							// Removing Or properly arranging the sepecial characters at the ending of the string
							// E.g. *The child was intolerably violent and difficult to quiet, (Hnf. Fsjk yhak)  , . 
							$returnCleanlineData = removeEndingSpecialCharactersForString($cleanline);
							$cleanline = (isset($returnCleanlineData['symptom_string']) AND $returnCleanlineData['symptom_string'] != "") ? $returnCleanlineData['symptom_string'] : $cleanline;
							// echo print_r($returnCleanlineData['last_character_array']);
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
							$cleanlineNewLastChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 1 );
							if ($cleanlineNewLastChar == ')') {
								$endingBracketsArray = getAllEndingBracketedStrings($cleanline, "(", ")");
							}else if($cleanlineNewLastChar == ']'){
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
								$isPreDefinedTagsApproval = 1;
								$ckeckPApproval = 0;
								$tagsApproalStringForPrue = "";
								foreach ($prueferFromParray as $prueferPkey => $prueferPval) {
									$prueferPval = trim($prueferPval);
									$tagsApproalStringForPrue .= $prueferPval."{#^#}";

									$cleanPrueferString = (mb_substr ( $prueferPval, mb_strlen ( $prueferPval ) - 1, 1 ) == '.') ? $prueferPval : $prueferPval.'.';
									$prueferReturnArr = lookupPruefer($cleanPrueferString);
									if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
										$ckeckPApproval = 1;

										if(!empty($prueferReturnArr['data'])){
											foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
												// custom_in_array(needle, needle_field, array)
												if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
													$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
											}
										}
										else{
											$dataArr = array();
											$dataArr['pruefer_id'] = null;
											$dataArr['kuerzel'] = null;
											$dataArr['suchname'] = trim($prueferPval);
											// custom_in_array(needle, needle_field, array)
											if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
												$prueferArray[] = $dataArr;
										}
									}
									else{
										foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
											// custom_in_array(needle, needle_field, array)
											if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
												$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
										}
									}
								}

								/* Literaturquellen data */
								$ckeckRApproval = 0;
								$tagsApproalStringForRef = "";
								foreach ($aLiteraturquellen as $refKey => $refVal) {
									$tagsApproalStringForRef .= $refVal."{#^#}";

									$refVal = trim($refVal);
									$referenceReturnArr = lookupLiteratureReference($refVal);
									if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
										$ckeckRApproval = 1;

										if(!empty($referenceReturnArr['data'])){
											foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
												// custom_in_array(needle, needle_field, array)
												if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true)
													$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
											}
										}
										else{
											$fullReferenceInArray = explode(",", $refVal);
											if(count($fullReferenceInArray) >= 2){
												$referenceAutor = trim($fullReferenceInArray[0]);
								        		array_shift($fullReferenceInArray);
								        		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
											}else{
												$referenceAutor = "";
												$referenceTxt = $refVal;
											}
											
											$dataArr = array();
											$dataArr['reference_id'] = null;
											$dataArr['full_reference'] = $refVal;
											$dataArr['autor'] = $referenceAutor;
											$dataArr['reference'] = $referenceTxt;
											// custom_in_array(needle, needle_field, array)
											if(custom_in_array($dataArr['full_reference'], 'full_reference', $referenceArray) != true)
												$referenceArray[] = $dataArr;
										}

									}else{
										foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
											// custom_in_array(needle, needle_field, array)
											if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
												$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
												$aLiteraturquellen [$refKey] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
											}
										}
									}
								}

								if($ckeckPApproval == 1 OR $ckeckRApproval == 1){
									// Making Reference array empty (for not adding in symptom table column) because it's not use have clear it in Direct Order or reinsert correctly.
									$aLiteraturquellen = array();
									$referenceArray = array();

									$prueferArray = array();

									$tagsApprovalString = $tagsApproalStringForPrue.$tagsApproalStringForRef;
									$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");

									$needApproval = 1;

									$referencePriority = 0;
									$referenceWithNoAuthorPriority = 0;
									$remedyWithSymptomPriority = 0;
									$partOfSymptomPriority = 0;
									$remedyPriority = 0;
									$prueferPriority = 0;
									$aaoHyphenPriority = 0;
									$hyphenPrueferPriority = 0;
									$hyphenReferencePriority = 0;
									$moreThanOneTagStringPriority = 10;
								}else{
									$needApproval = 0;
								}
							}
							else if( count($aLiteraturquellen) > 0 ){
								/* When only @L is present in a symptom */	

								/* Making pruefer Array blank */
								$prueferArray = array ();

								/* Literaturquellen data */
								$isPreDefinedTagsApproval = 1;
								$tagsApproalStringForRef = ""; 
								$ckeckRApproval = 0;
								foreach ($aLiteraturquellen as $refKey => $refVal) {
									$tagsApproalStringForRef .= $refVal."{#^#}";

									$refVal = trim($refVal);
									$referenceReturnArr = lookupLiteratureReference($refVal);
									
									if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
										$ckeckRApproval = 1;

										if(!empty($referenceReturnArr['data'])){
											foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
												if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true)
													$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
											}
										}
										else{
											$fullReferenceInArray = explode(",", $refVal);
											if(count($fullReferenceInArray) >= 2){
												$referenceAutor = trim($fullReferenceInArray[0]);
								        		array_shift($fullReferenceInArray);
								        		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
											}else{
												$referenceAutor = "";
												$referenceTxt = $refVal;
											}
											
											$dataArr = array();
											$dataArr['reference_id'] = null;
											$dataArr['full_reference'] = $refVal;
											$dataArr['autor'] = $referenceAutor;
											$dataArr['reference'] = $referenceTxt;
											if(custom_in_array($dataArr['full_reference'], 'full_reference', $referenceArray) != true)
												$referenceArray[] = $dataArr;
										}

									}else{
										foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
											if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
												$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
												$aLiteraturquellen [$refKey] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
											}
										}
									}
								}

								if($ckeckRApproval == 1){
									$needApproval = 1;

									$aLiteraturquellen = array(); 
									$tagsApprovalString = $tagsApproalStringForRef;
									$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");

									$foundReferenceStringArray = explode("{#^#}", $tagsApprovalString);

									if(count($foundReferenceStringArray) > 1){
										$partOfSymptomPriority = 0;
										$remedyWithSymptomPriority = 0;
										$prueferPriority = 0;
										$remedyPriority = 0;
										$referencePriority = 0;
										$referenceWithNoAuthorPriority = 0;
										$aaoHyphenPriority = 0;
										$hyphenPrueferPriority = 0;
										$hyphenReferencePriority = 0;
										$moreThanOneTagStringPriority = 10;
									}else{
										$partOfSymptomPriority = 0;
										$remedyWithSymptomPriority = 0;
										$prueferPriority = 0;
										$remedyPriority = 0;
										$aaoHyphenPriority = 0;
										$hyphenPrueferPriority = 0;
										$hyphenReferencePriority = 0;
										$moreThanOneTagStringPriority = 0;
										$referenceWithNoAuthorPriority = 0;
										$referencePriority = 10;
									}
								}else{
									$needApproval = 0;
								}
							}
							else if( count($prueferFromParray) > 0 ){
								/* When only @P is present in a symptom */

								$isPreDefinedTagsApproval = 1;
								$ckeckPApproval = 0;
								$tagsApproalStringForPrue = "";
								foreach ($prueferFromParray as $prueferPkey => $prueferPval) {
									$prueferPval = trim($prueferPval);
									$tagsApproalStringForPrue .= $prueferPval."{#^#}";

									$cleanPrueferString = (mb_substr ( $prueferPval, mb_strlen ( $prueferPval ) - 1, 1 ) == '.') ? $prueferPval : $prueferPval.'.'; 
									$prueferReturnArr = lookupPruefer($cleanPrueferString);
									if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
										$ckeckPApproval = 1;
										
										if(!empty($prueferReturnArr['data'])){
											foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
												if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
													$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
											}
										}
										else{
											$dataArr = array();
											$dataArr['pruefer_id'] = null;
											$dataArr['kuerzel'] = null;
											$dataArr['suchname'] = trim($prueferPval);
											if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
												$prueferArray[] = $dataArr;
										}
									}
									else{
										foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
											if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
												$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
										}
									}
								}

								if($ckeckPApproval == 1){
									$needApproval = 1;

									$tagsApprovalString = $tagsApproalStringForPrue;
									$tagsApprovalString = rtrim($tagsApprovalString, "{#^#}");

									$foundPrueferStringArray = explode("{#^#}", $tagsApprovalString);
									if(count($foundPrueferStringArray) > 1){
										$referencePriority = 0;
										$referenceWithNoAuthorPriority = 0;
										$remedyWithSymptomPriority = 0;
										$remedyPriority = 0;
										$partOfSymptomPriority = 0;
										$prueferPriority = 0;
										$aaoHyphenPriority = 0;
										$hyphenPrueferPriority = 0;
										$hyphenReferencePriority = 0;
										$moreThanOneTagStringPriority = 10;
									}else{
										$referencePriority = 0;
										$referenceWithNoAuthorPriority = 0;
										$remedyWithSymptomPriority = 0;
										$remedyPriority = 0;
										$partOfSymptomPriority = 0;
										$moreThanOneTagStringPriority = 0;
										$aaoHyphenPriority = 0;
										$hyphenPrueferPriority = 0;
										$hyphenReferencePriority = 0;
										$prueferPriority = 10;
									}

								}else{
									$needApproval = 0;
								}
							}
							else{
								if(!empty($endingBracketsArray)){
									/* If ending brackets are not null than examining its possibilities START */
									// $approvalFor (0 = end bracket, 1 = middle bracket)
									$approvalFor = 0;
									$lastBracketedString = (isset($endingBracketsArray[0]) AND $endingBracketsArray[0] != "") ? trim($endingBracketsArray[0]) : null;
									if($lastBracketedString != ""){
										// Checking the existance of , - . ; and , a. a. O. and , a.a.O.
										$isAaoExist = mb_strpos($lastBracketedString, 'a. a. O.');
										$isAaoWithoutSpaceExist = mb_strpos($lastBracketedString, 'a.a.O.');
										$isAaoAllLowerWithoutSpaceExist = mb_strpos($lastBracketedString, 'a.a.o.');
										$isAaoAllLowerWithSpaceExist = mb_strpos($lastBracketedString, 'a. a. o.');
										$isCommaExist = mb_substr_count($lastBracketedString,",");
										$isHyphenExist = mb_substr_count($lastBracketedString," - ");
										$isDotExist = mb_substr_count($lastBracketedString, ".");
										$isSemicolonExist = mb_substr_count($lastBracketedString,";");

										if($isCommaExist == 0 AND $isSemicolonExist == 0 AND $isHyphenExist == 0 AND $isAaoExist === false AND $isAaoWithoutSpaceExist === false AND $isAaoAllLowerWithoutSpaceExist === false AND $isAaoAllLowerWithSpaceExist === false)
										{
											// No Comma AND No Semicolon AND No Hyphen AND No , a. a. O. START
											$workingString = trim($lastBracketedString);
											$expectedRemedyArray = array();
											/*
											* COMMON LOOKUP SECTION START
											*/
											if (mb_strpos($workingString, '.') !== false){
												// Split by dot(.)
												$makeStringToExplode = str_replace('.', '.{#^#}', $workingString);
												$expectedRemedyArray = explode("{#^#}", $makeStringToExplode);
											}
											else
												$expectedRemedyArray[] = $workingString;

											/* REMEDY START */
											$checkRemedyApprovalStatus = 0;
											foreach ($expectedRemedyArray as $expectedRemedyKey => $expectedRemedyVal) {
												
												if($expectedRemedyVal == "")
													continue;	

												$cleanExpectedRemedyName = trim($expectedRemedyVal);
												$cleanRemedyString = (mb_substr ( $cleanExpectedRemedyName, mb_strlen ( $cleanExpectedRemedyName ) - 1, 1 ) == '.') ? $cleanExpectedRemedyName : $cleanExpectedRemedyName.'.';
												$remedyReturnArr = newLookupRemedy($cleanRemedyString);
												if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
													$checkRemedyApprovalStatus = 1;
													if(!empty($remedyReturnArr['data'])){
														foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
															if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
																$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
														}
													}
													else{
														$dataArr = array();
														$dataArr['remedy_id'] = null;
														$dataArr['name'] = $cleanExpectedRemedyName;
														$dataArr['kuerzel'] = "";
														if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
															$remedyArray[] = $dataArr;
													}
												}
												else{
													foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
														if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
															$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
													}
												}
											}
											// Setting last operations approval status to main approval checking variable 
											$needApproval = $checkRemedyApprovalStatus; 
											/* REMEDY END */

											/* PRUEFER STRAT */
											if($needApproval == 1){
												// Check multiple prufers
												$checkPrueferApprovalStatus = 0;
												foreach ($expectedRemedyArray as $expectedPrueferKey => $expectedPrueferVal) {
													if($expectedPrueferVal == "")
														continue;

													$cleanPrueferString = trim($expectedPrueferVal); 
													$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
													$prueferReturnArr = lookupPruefer($cleanPrueferString);
													if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
														$checkPrueferApprovalStatus = 1;
														if(!empty($prueferReturnArr['data'])){
															foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																	$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
															}
														}
														else
														{
															$dataArr = array();
															$dataArr['pruefer_id'] = null;
															$dataArr['kuerzel'] = null;
															$dataArr['suchname'] = $cleanPrueferString;
															if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
																$prueferArray[] = $dataArr;
														}
													}
													else{
														$remedyArray = array();
														$referenceArray = array();
														$aLiteraturquellen = array();
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}	
												}

												$needApproval = $checkPrueferApprovalStatus;
												// Check multiple prufers end	
											}
											
											if($needApproval == 1){
												$cleanPrueferString = (mb_substr ( $workingString, mb_strlen ( $workingString ) - 1, 1 ) == '.') ? $workingString : $workingString.'.'; 
												$prueferReturnArr = lookupPruefer($cleanPrueferString);
												if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
													if(!empty($prueferReturnArr['data'])){
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}else{
														$dataArr = array();
														$dataArr['pruefer_id'] = null;
														$dataArr['kuerzel'] = null;
														$dataArr['suchname'] = $workingString;
														if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
															$prueferArray[] = $dataArr;
													}
												}
												else{
													$prueferArray = array();
													$needApproval = 0;
													$remedyArray = array();
													foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
														if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
															$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
													}
												}
											}
											/* PRUEFER END */

											/* REFERENCE WITH NO AUTHOR START */
											if($needApproval == 1){
												$noAuthorWorkingString = "No Author, ".trim($workingString);
												$referenceReturnArr = lookupLiteratureReference($noAuthorWorkingString);
												if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){

													if(!empty($referenceReturnArr['data'])){
														foreach ($referenceReturnArr['data'] as $refKey => $refVal) {
															if(custom_in_array($referenceReturnArr['data'][$refKey]['reference_id'], 'reference_id', $referenceArray) != true)
																$referenceArray[] = $referenceReturnArr['data'][$refKey];
														}
													}
													else{
														$fullReferenceInArray = explode(",", $noAuthorWorkingString);
														if(count($fullReferenceInArray) >= 2){
															$referenceAutor = trim($fullReferenceInArray[0]);
											        		array_shift($fullReferenceInArray);
											        		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
														}else{
															$referenceAutor = "No Author";
															$referenceTxt = $workingString;
														}
														
														$dataArr = array();
														$dataArr['reference_id'] = null;
														$dataArr['full_reference'] = $noAuthorWorkingString;
														$dataArr['autor'] = $referenceAutor;
														$dataArr['reference'] = $referenceTxt;
														if(custom_in_array($dataArr['full_reference'], 'full_reference', $referenceArray) != true)
															$referenceArray[] = $dataArr;
													}

												}else{
													$needApproval = 0;
													$prueferArray = array();
													$remedyArray = array();
													foreach ($referenceReturnArr['data'] as $refKey => $refVal) {
														if(custom_in_array($referenceReturnArr['data'][$refKey]['reference_id'], 'reference_id', $referenceArray) != true){
															$referenceArray[] = $referenceReturnArr['data'][$refKey];
															$aLiteraturquellen [] = ($referenceReturnArr['data'][$refKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$refKey]['full_reference']) : "";
														}
													}
												}
											}
											/* REFERENCE WITH NO AUTHOR END */

											/*
											* COMMON LOOKUP SECTION END
											*/

											$wordsInLastString = explode(" ", $lastBracketedString);
											if(!empty($wordsInLastString)){

												if(count($wordsInLastString) == 1){
													/*
													* (A) SINGLE WORD START
													*/
													
													if($isDotExist != 0){
														// Single word has dot START
														if( isFirstCharacterUppercase($workingString) === true){
															/*
															* (A1) If the word + dot has uppercase (I mean only the first character is uppercase)
															*
															* 1 = chcek for remedy
															* 2 = chcek for part of symptom
															* 3 = chcek for pruefer
															*/
															if($needApproval == 1){
																$referencePriority = 0;
																$referenceWithNoAuthorPriority = 0;
																$remedyWithSymptomPriority = 0;
																$aaoHyphenPriority = 0;
																$hyphenPrueferPriority = 0;
																$hyphenReferencePriority = 0;
																$moreThanOneTagStringPriority = 0;
																$remedyPriority = 8;
																$partOfSymptomPriority = 9;
																$prueferPriority = 10;
															}
														}else{
															/*
															* (A2) If the word + dot is lowercase
															*
															* 1 = chcek for part of symptom
															* 2 = chcek for remedy
															*/
															if($needApproval == 1){
																// As we are not going to ask Pruefer Question, making $prueferArray array empty and $prueferPriority = 0
																$prueferArray = array();
																$referencePriority = 0;
																$referenceWithNoAuthorPriority = 0;
																$remedyWithSymptomPriority = 0;
																$prueferPriority = 0;
																$aaoHyphenPriority = 0;
																$hyphenPrueferPriority = 0;
																$hyphenReferencePriority = 0;
																$moreThanOneTagStringPriority = 0;
																$partOfSymptomPriority = 9;
																$remedyPriority = 10;
															}
														}
														// Single word has dot END
													}else{
														// Single word don't have any dot START
														if( isFirstCharacterUppercase($workingString) === true){
															/*
															* (A4) Single word uppercase without dot (I mean only the first character is uppercase)
															*
															* 1 = chcek for remedy
															* 2 = chcek for part of symptom
															* 3 = chcek for pruefer
															*/
															if($needApproval == 1){
																$referencePriority = 0;
																$referenceWithNoAuthorPriority = 0;
																$remedyWithSymptomPriority = 0;
																$aaoHyphenPriority = 0;
																$hyphenPrueferPriority = 0;
																$hyphenReferencePriority = 0;
																$moreThanOneTagStringPriority = 0;
																$remedyPriority = 8;
																$partOfSymptomPriority = 9;
																$prueferPriority = 10;
															}
														}else{
															/*
															* (A3) Single word lowercase without dot
															*
															* 1 = chcek for part of symptom
															* 2 = chcek for remedy
															* 3 = chcek for pruefer
															*/
															if($needApproval == 1){
																$referencePriority = 0;
																$referenceWithNoAuthorPriority = 0;
																$remedyWithSymptomPriority = 0;
																$aaoHyphenPriority = 0;
																$hyphenPrueferPriority = 0;
																$hyphenReferencePriority = 0;
																$moreThanOneTagStringPriority = 0;
																$partOfSymptomPriority = 8;
																$remedyPriority = 9;
																$prueferPriority = 10;
															}
														}
														// Single word don't have any dot END
													}

													/*
													* (A) SINGLE WORD END
													*/
												}
												else
												{
													/* 
													* (B) MORE THAN ONE WORD (case insensitive i.e., upper or lower case does not matter) START 
													*/

													if($isDotExist != 0){
														/*
														* (B1) Words have one or more than one dot(s)
														*
														* 1 = chcek for part of symptom
														* 2 = chcek for n remedies splited by dot(.)
														* 3 = chcek for pruefer
														* 4 = chcek for reference with no author
														*/

														if($needApproval == 1){
															$remedyWithSymptomPriority = 0;
															$aaoHyphenPriority = 0;
															$hyphenPrueferPriority = 0;
															$hyphenReferencePriority = 0;
															$moreThanOneTagStringPriority = 0;
															$referencePriority = 0;
															$partOfSymptomPriority = 7;
															$remedyPriority = 8;
															$prueferPriority = 9;
															$referenceWithNoAuthorPriority = 10;
														}

													}else{
														/*
														* (B1) Words have NO dot(s)
														*
														* 1 = chcek for part of symptom
														* 2 = chcek for remedy
														* 3 = chcek for pruefer
														*/

														if($needApproval == 1){
															$referencePriority = 0;
															$referenceWithNoAuthorPriority = 0;
															$remedyWithSymptomPriority = 0;
															$aaoHyphenPriority = 0;
															$hyphenPrueferPriority = 0;
															$hyphenReferencePriority = 0;
															$moreThanOneTagStringPriority = 0;
															$partOfSymptomPriority = 8;
															$remedyPriority = 9;
															$prueferPriority = 10;
														}
													}

													/* 
													* (B) MORE THAN ONE WORD (case insensitive i.e., upper or lower case does not matter) END 
													*/
												}

											}
											// No Comma AND No Semicolon AND No Hyphen AND No , a. a. O. END
										}
										else if(($isCommaExist != 0 OR $isSemicolonExist != 0) AND $isHyphenExist == 0 AND $isAaoExist === false AND $isAaoWithoutSpaceExist === false AND $isAaoAllLowerWithoutSpaceExist === false AND $isAaoAllLowerWithSpaceExist === false)
										{
											// echo "Hello 2<pre><br>";
											// With Comma OR Semicolon AND NO Hyphen AND No , a. a. O. START
											if (mb_strpos($lastBracketedString, ',') !== false) 
												$separator = ",";
											else
												$separator = ";";

											$commaFirstOccurrence = mb_stripos ( $lastBracketedString, $separator );
											$beforeTheCommaString = trim( mb_substr ( $lastBracketedString, 0, $commaFirstOccurrence ) );
											$afterTheCommaString = trim( ltrim( mb_substr ( $lastBracketedString, $commaFirstOccurrence ), $separator ));
											$beforeTheCommaStringInArray = explode(" ", $beforeTheCommaString);
											$afterTheCommaStringInArray = explode(" ", $afterTheCommaString);

											$isDotExistInBeforeTheCommaString = mb_substr_count($beforeTheCommaString,".");
											$isDotExistInAfterTheCommaString = mb_substr_count($afterTheCommaString,".");
											
											$upperCaseCheckInBeforeTheCommaStr = isThereAnyUppercase($beforeTheCommaString);
											$upperCaseCheckInAfterTheCommaStr = isThereAnyUppercase($afterTheCommaString);
											$isFirstCharUpperBeforeTheCommaStr = isFirstCharacterUppercase($beforeTheCommaString);

											$workingString = trim($lastBracketedString);

											/*
											* COMMON LOOKUP SECTION START
											*/

											/* REMEDY START */
											$checkRemedyApprovalStatus = 0;
											$expectedRemedyArray = explode($separator, $workingString);
											foreach ($expectedRemedyArray as $expectedRemedyKey => $expectedRemedyVal) {
												
												if($expectedRemedyVal == "")
													continue;	

												$cleanExpectedRemedyName = trim($expectedRemedyVal);
												$cleanRemedyString = (mb_substr ( $cleanExpectedRemedyName, mb_strlen ( $cleanExpectedRemedyName ) - 1, 1 ) == '.') ? $cleanExpectedRemedyName : $cleanExpectedRemedyName.'.'; 
												$remedyReturnArr = newLookupRemedy($cleanRemedyString);
												if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
													$checkRemedyApprovalStatus = 1;
													if(!empty($remedyReturnArr['data'])){
														foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
															// custom_in_array(needle, needle_field, array) 
															if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
																$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
														}

													}
													else{
														$dataArr = array();
														$dataArr['remedy_id'] = null;
														$dataArr['name'] = $cleanExpectedRemedyName;
														$dataArr['kuerzel'] = "";
														if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
															$remedyArray[] = $dataArr;
													}
												}
												else{
													foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
														if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
															$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
													}
												}
											}
											// Setting last operations approval status to main approval checking variable 
											$needApproval = $checkRemedyApprovalStatus; 
											/* REMEDY END */

											/* REFERENCE START */
											if($needApproval == 1){
												$referenceReturnArr = lookupLiteratureReference($workingString);
												if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){

													if(!empty($referenceReturnArr['data'])){
														foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
															if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true)
																$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
														}
													}
													else{
														$fullReferenceInArray = explode(",", $workingString);
														if(count($fullReferenceInArray) >= 2){
															$referenceAutor = trim($fullReferenceInArray[0]);
											        		array_shift($fullReferenceInArray);
											        		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
														}else{
															$referenceAutor = "";
															$referenceTxt = $workingString;
														}
														
														$dataArr = array();
														$dataArr['reference_id'] = null;
														$dataArr['full_reference'] = $workingString;
														$dataArr['autor'] = $referenceAutor;
														$dataArr['reference'] = $referenceTxt;
														if(custom_in_array($dataArr['full_reference'], 'full_reference', $referenceArray) != true)
															$referenceArray[] = $dataArr;
													}

												}else{
													$needApproval = 0;
													$remedyArray = array();
													foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
														if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
															$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
															$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
														}	
													}
												}
											}
											/* REFERENCE END */

											/* PRUEFER START */
											if($needApproval == 1){
												$checkPrueferApprovalStatus = 0;
												$expectedPruefersArray = explode($separator, $workingString);
												foreach ($expectedPruefersArray as $expectedPrueferKey => $expectedPrueferVal) {
													if($expectedPrueferVal == "")
														continue;

													$cleanPrueferString = trim($expectedPrueferVal); 
													$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
													$prueferReturnArr = lookupPruefer($cleanPrueferString);
													if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
														$checkPrueferApprovalStatus = 1;
														if(!empty($prueferReturnArr['data'])){
															foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																	$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
															}
														}
														else{
															$dataArr = array();
															$dataArr['pruefer_id'] = null;
															$dataArr['kuerzel'] = null;
															$dataArr['suchname'] = $cleanPrueferString;
															if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
																$prueferArray[] = $dataArr;
														}
													}
													else{
														$remedyArray = array();
														$referenceArray = array();
														$aLiteraturquellen = array();
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}	
												}

												$needApproval = $checkPrueferApprovalStatus; 
											}
											/* PRUEFER END */
											/*
											* COMMON LOOKUP SECTION END
											*/

											/* Rule 2 Conditions START */
											if(count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString != 0 AND $isDotExistInAfterTheCommaString !=0){
												/*
												* 2.1 Single word + dot before the comma and one or more words + dot after comma (no matter if upper or lower case)
												*
												* 1 = chcek for remedis by spliting by comma
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 10;
												}

											}else if($isCommaExist == 1 AND ((count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString == 1) OR (count($afterTheCommaStringInArray) == 1 AND $isDotExistInAfterTheCommaString == 1))){
												/*
												* 2.2. Single word + dot before the comma or after a comma (only one dot and one comma)
												*
												* 1 = check for remedy with symptom text (Eg: Opi., during the day)(Eg: small boils in crops, Sulph.)
												* 2 = part of symptom
												*/
												if(count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString == 1){
													$similarRemedyString = $beforeTheCommaString;
													$similarSymptomString = $afterTheCommaString;	
												}else{
													$similarRemedyString = $afterTheCommaString;
													$similarSymptomString = $beforeTheCommaString;
												}
												$cleanRemedyWithSymptomString = (mb_substr ( $workingString, mb_strlen ( $workingString ) - 1, 1 ) == '.') ? $workingString : $workingString.'.'; 
												$remedyWithSymptomReturnArr = newLookupRemedyWithSymptom($cleanRemedyWithSymptomString, $similarRemedyString, $similarSymptomString);
												if(isset($remedyWithSymptomReturnArr['need_approval']) AND $remedyWithSymptomReturnArr['need_approval'] == 0){
													$needApproval = 0;
													$remedyArray = array();
													$referenceArray = array();
													$aLiteraturquellen = array();
													$prueferArray = array();
													if(isset($remedyWithSymptomReturnArr['data'][0]['remedy']))
														$remedyArray = $remedyWithSymptomReturnArr['data'][0]['remedy'];
													$symptomOfDifferentRemedy = (isset($remedyWithSymptomReturnArr['data'][0]['symptom_of_different_remedy'])) ? $remedyWithSymptomReturnArr['data'][0]['symptom_of_different_remedy'] : "";
												}else{
													$needApproval = 1;
												}

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$prueferPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 0;
													$remedyWithSymptomPriority = 9;
													$partOfSymptomPriority = 10;
												}

											}else if(count($beforeTheCommaStringInArray) > 1 AND $upperCaseCheckInBeforeTheCommaStr === false AND $isDotExist == 0 AND count($afterTheCommaStringInArray) > 1){
												/*
												* 2.3. More than one word before comma in lower case (no dots) and no single word + dot in the bracket
												*
												* 1 = part of symptom
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$prueferPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 0;
													$remedyWithSymptomPriority = 0;
													$partOfSymptomPriority = 10;
												}
											}else if(count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString == 0 AND $isFirstCharUpperBeforeTheCommaStr === true){ 
												/*
												* 2.4. Single word upper case without dot before the comma
												*
												* 1 = check for reference
												* 2 = part of symptom
												* 3 = chcek for remedis by spliting by comma
												* 4 = pruefer
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$referencePriority = 7;
													$partOfSymptomPriority = 8;
													$remedyPriority = 9;
													$prueferPriority = 10;
												}
											}else if($upperCaseCheckInBeforeTheCommaStr === false AND $isDotExist == 0){
												/*
												* 2.5. One or more words lower case without dot before the comma (no dot in the bracket part)
												*
												* 1 = part of symptom
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$prueferPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 0;
													$remedyWithSymptomPriority = 0;
													$partOfSymptomPriority = 10;
												}
											}else if(count($beforeTheCommaStringInArray) > 1 AND $isFirstCharUpperBeforeTheCommaStr === true AND $isDotExistInBeforeTheCommaString != 0 ){
												/*
												* 2.6. More than one word with at least one dot before the comma(all words upper case)
												*
												* 1 = check for reference
												* 2 = check for pruefer
												* 3 = chcek for remedis by spliting by comma
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$partOfSymptomPriority = 0;
													$referencePriority = 8;
													$prueferPriority = 9;
													$remedyPriority = 10;
												}
											}else if(count($beforeTheCommaStringInArray) > 1 AND $isFirstCharUpperBeforeTheCommaStr === true AND $isDotExistInBeforeTheCommaString == 0){
												/*
												* 2.7. More than one word (no dots) before comma (all words upper case)
												*
												* 1 = check for reference
												* 2 = check for pruefer
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$referencePriority = 9;
													$prueferPriority = 10;
												}
											}else if(count($beforeTheCommaStringInArray) > 1 AND $isDotExistInBeforeTheCommaString == 0 AND $upperCaseCheckInBeforeTheCommaStr === true){
												/*
												* 2.8. More than one word mixed lower & upper case (no dots) before comma(all the words cannot be in one case)
												*
												* 1 = part of symptom
												* 2 = check for reference
												*/
												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$remedyPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 9;
													$referencePriority = 10;
												}
											}else if(count($beforeTheCommaStringInArray) > 1 AND $upperCaseCheckInBeforeTheCommaStr === true AND $isDotExistInBeforeTheCommaString != 0){
												/*
												* 2.9. More than one word mixed lower & upper case with at least one dotbefore comma(all the words cannot be in one case)
												*
												* 1 = chcek for remedis by spliting by comma
												* 2 = check for reference
												*/ 

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 9;
													$referencePriority = 10;
												}
											}
											/* Rule 2 Conditions END */

											// With Comma OR Semicolon AND NO Hyphen AND No , a. a. O. END
										}
										else if(($isAaoExist !== false OR $isAaoWithoutSpaceExist !== false OR $isAaoAllLowerWithoutSpaceExist !== false OR $isAaoAllLowerWithSpaceExist !== false) AND $isHyphenExist != 0)
										{
											// When string has both ", a. a. O." and hyphen START
											$workingString = trim($lastBracketedString);
											$eachElement = explode(" - ", $workingString); 
											$referenceArray = array();
											$aLiteraturquellen = array();
											$prueferArray = array();
											$countUnknownElement = 0;
											foreach ($eachElement as $elementKey => $elementVal) {
												$innerApprovalChecking = 0;
												// Lookup in same import data 
												$elementString = str_replace("a. a. O.", "{#^#}", $elementVal);
												$elementString = str_replace("a.a.O.", "{#^#}", $elementString);
												$elementString = str_replace("a.a.o.", "{#^#}", $elementString);
												$elementString = str_replace("a. a. o.", "{#^#}", $elementString);
												$searchAuthorPreName = trim($elementString);
												$aaoPosition = mb_strpos($searchAuthorPreName, '{#^#}');
												if($aaoPosition !== false){
													$searchAuthorPreName = mb_substr($searchAuthorPreName, 0, $aaoPosition);
												}
												$searchAuthorPreName = str_replace("{#^#}", "", $searchAuthorPreName);
												$searchAuthorName = trim($searchAuthorPreName);

												if($searchAuthorName != ""){
													/* 
													* Check the last appearence of this elemet in temp_approved_pruefer and temp_approved_reference table
													* if no match data found than "aao_hyphen_priority" question will be ask
													*/
													// Checking pruefer
													$cleanPrueferString = trim($searchAuthorName); 
													$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
													$prueferReturnArr = lookupPrueferInCurrentImport($cleanPrueferString, $masterId, null);
													if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
														$innerApprovalChecking = 1;
													}
													else{
														$innerApprovalChecking = 0;
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}

													if($innerApprovalChecking == 1){
														// Check reference
														$cleanReferenceString = trim($searchAuthorName);
														$referenceReturnArr = lookupReferenceInCurrentImport($cleanReferenceString, $masterId, null);
														if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
															$innerApprovalChecking = 1;
														}else{
															$innerApprovalChecking = 0;
															foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																	$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																	$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
																}	
															}
														}
													}

													// Normal lookup
													// Check pruefer
													if($innerApprovalChecking == 1){
														$cleanPrueferString = trim($searchAuthorName); 
														$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
														$prueferReturnArr = lookupPruefer($cleanPrueferString);
														if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
															$innerApprovalChecking = 1;
															if(!empty($prueferReturnArr['data'])){
																foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																	if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true){
																		$prueferReturnArr['data'][$prueferReturnKey]['is_one_unknown_element_in_hyphen'] = 1;
																		$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
																	}
																}
															}
														}
														else{
															$innerApprovalChecking = 0;
															foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																	$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
															}
														}
													}

													// Checking Reference
													if($innerApprovalChecking == 1){
														$cleanReferenceString = trim($searchAuthorName);
														$referenceReturnArr = lookupLiteratureReference($cleanReferenceString);
														if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
															$innerApprovalChecking = 1;
															if(!empty($referenceReturnArr['data'])){
																foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																	if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																		$referenceReturnArr['data'][$referenceReturnKey]['is_one_unknown_element_in_hyphen'] = 1;
																		$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																	}
																}
															}
														}else{
															$innerApprovalChecking = 0;
															foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																	$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																	$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
																}	
															}
														}
													}
												}

												// If unknown Data found incrementing the counter and setting the element value to $hyphenApprovalString
												if($innerApprovalChecking == 1){
													$countUnknownElement++;
													$hyphenApprovalString = trim($elementVal);
												}
											}

											// Set need approval value if unknown data found
											if($countUnknownElement > 0){
												$needApproval = 1;
												if($countUnknownElement != 1)
													$hyphenApprovalString = "";
											}

											/*
											* Rule 3 Last bracket words:  “, a. a. O.” or ", a.a.O." and Hyphen (hyphenhasspacebeforeand after ( - )) (whenbothexist)
											*
											* 1 = Unknown data found with a. a. O. or Hyphen( - )
											*/ 
											if($needApproval == 1){
												if($countUnknownElement == 1){
													$referenceWithNoAuthorPriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$referencePriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenReferencePriority = 9;
													$hyphenPrueferPriority = 10;
												}else{
													// Making pruefer and reference array empty because these elements will be cleared by direct oredr or by correcting the symptom string. Also seting aao_hyphen_priority value for asking the question
													$referenceArray = array();
													$aLiteraturquellen = array();
													$prueferArray = array();

													$referenceWithNoAuthorPriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$referencePriority = 0;
													$hyphenReferencePriority = 0;
													$hyphenPrueferPriority = 0;
													$aaoHyphenPriority = 10;
												}
											}
											// When string has both ", a. a. O." and hyphen END 
										}
										else if($isHyphenExist != 0)
										{
											// When string has hyphen only START 
											$workingString = trim($lastBracketedString);
											$eachElement = explode(" - ", $workingString);
											$referenceArray = array();
											$aLiteraturquellen = array();
											$prueferArray = array();
											$countUnknownElement = 0;

											/* REFERENCE START [FIRST CHECKING THE FULL STRING] */
											$referenceReturnArr = lookupLiteratureReference($workingString);
											if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
												$needApproval = 1;
											}else{
												$needApproval = 0;
												foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
													if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
														$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
														$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
													}	
												}
											}
											/* REFERENCE END [FIRST CHECKING THE FULL STRING] */

											if($needApproval == 1){
												foreach ($eachElement as $elementKey => $elementVal) {
													$innerApprovalChecking = 0;
													/* 
													* Check the last appearence of this elemet in temp_approved_pruefer and temp_approved_reference table
													* if no match data found than "aao_hyphen_priority" question will be ask
													*/
													// Checking pruefer
													$cleanPrueferString = trim($elementVal); 
													$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
													$prueferReturnArr = lookupPruefer($cleanPrueferString);
													if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
														$innerApprovalChecking = 1;
														if(!empty($prueferReturnArr['data'])){
															foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true){
																	$prueferReturnArr['data'][$prueferReturnKey]['is_one_unknown_element_in_hyphen'] = 1;
																	$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
																}
															}
														}
													}
													else{ 
														$innerApprovalChecking = 0;
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}

													if($innerApprovalChecking == 1){
														// Check reference
														$cleanReferenceString = trim($elementVal);
														$referenceReturnArr = lookupLiteratureReference($cleanReferenceString);
														if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
															$innerApprovalChecking = 1;
															if(!empty($referenceReturnArr['data'])){
																foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																	if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																		$referenceReturnArr['data'][$referenceReturnKey]['is_one_unknown_element_in_hyphen'] = 1;
																		$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																	}
																}
															}
														}else{
															$innerApprovalChecking = 0;
															foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																	$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																	$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
																}	
															}
														}
													}

													// If unknown Data found incrementing the counter and setting the element value to $hyphenApprovalString
													if($innerApprovalChecking == 1){
														$countUnknownElement++;
														$hyphenApprovalString = trim($elementVal);
													}
												}
											}

											// Set need approval value if unknown data found
											if($countUnknownElement > 0){
												$needApproval = 1;
												if($countUnknownElement != 1)
													$hyphenApprovalString = "";
											}

											/*
											* Rule 4 Last bracket words:  “, a. a. O.” or ", a.a.O." and Hyphen (hyphenhasspacebeforeand after ( - )) (whenbothexist)
											*
											* 1 = Unknown data found with a. a. O. or Hyphen( - )
											*/ 
											if($needApproval == 1){
												if($countUnknownElement == 1){
													$referenceWithNoAuthorPriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$referencePriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenReferencePriority = 9;
													$hyphenPrueferPriority = 10;
												}else{
													// Making pruefer and reference array empty because these elements will be cleared by direct oredr or by correcting the symptom string. Also seting aao_hyphen_priority value for asking the question
													$referenceArray = array();
													$aLiteraturquellen = array();
													$prueferArray = array();

													$referenceWithNoAuthorPriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$referencePriority = 0;
													$hyphenReferencePriority = 0;
													$hyphenPrueferPriority = 0;
													$aaoHyphenPriority = 10;
												}
											}
											// When string has hyphen only START 
										}
										else if($isAaoExist !== false OR $isAaoWithoutSpaceExist !== false OR $isAaoAllLowerWithoutSpaceExist !== false OR $isAaoAllLowerWithSpaceExist !== false)
										{
											// When string has "a. a. O." only START 
											/* 
											* Check the last appearence of this elemet in temp_approved_pruefer and temp_approved_reference table
											* if no match data found than "aao_hyphen_priority" question will be ask
											*/
											$workingString = trim($lastBracketedString);

											$elementString = str_replace("a. a. O.", "{#^#}", $workingString);
											$elementString = str_replace("a.a.O.", "{#^#}", $elementString);
											$elementString = str_replace("a.a.o.", "{#^#}", $elementString);
											$elementString = str_replace("a. a. o.", "{#^#}", $elementString);
											$searchAuthorPreName = trim($elementString);
											$aaoPosition = mb_strpos($searchAuthorPreName, '{#^#}');
											if($aaoPosition !== false){
												$searchAuthorPreName = mb_substr($searchAuthorPreName, 0, $aaoPosition);
											}
											$searchAuthorPreName = str_replace("{#^#}", "", $searchAuthorPreName);
											$searchAuthorName = trim($searchAuthorPreName);

											if($searchAuthorName != ""){
												$innerApprovalChecking = 0;
												// Checking pruefer
												$cleanPrueferString = trim($searchAuthorName); 
												$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
												$prueferReturnArr = lookupPrueferInCurrentImport($cleanPrueferString, $masterId, null);
												if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
													$innerApprovalChecking = 1;
												}
												else{
													$innerApprovalChecking = 0;
													foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
														if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
															$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
													}
												}

												if($innerApprovalChecking == 1){
													// Check reference
													$cleanReferenceString = trim($searchAuthorName);
													$referenceReturnArr = lookupReferenceInCurrentImport($cleanReferenceString, $masterId, null);
													if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
														$innerApprovalChecking = 1;
													}else{
														$innerApprovalChecking = 0;
														foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
															if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
															}	
														}
													}
												}

												// Normal lookup
												// Check pruefer
												if($innerApprovalChecking == 1){
													$cleanPrueferString = trim($searchAuthorName); 
													$cleanPrueferString = (mb_substr ( $cleanPrueferString, mb_strlen ( $cleanPrueferString ) - 1, 1 ) == '.') ? $cleanPrueferString : $cleanPrueferString.'.'; 
													$prueferReturnArr = lookupPruefer($cleanPrueferString);
													if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
														$prueferArray = array();
														$innerApprovalChecking = 1;
														if(!empty($prueferReturnArr['data'])){
															foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
																if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																	$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
															}
														}
													}
													else{
														$innerApprovalChecking = 0;
														foreach ($prueferReturnArr['data'] as $prueferReturnKey => $prueferReturnVal) {
															if(custom_in_array($prueferReturnArr['data'][$prueferReturnKey]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
																$prueferArray[] = $prueferReturnArr['data'][$prueferReturnKey];
														}
													}
												}

												// Checking Reference
												if($innerApprovalChecking == 1){
													$cleanReferenceString = trim($searchAuthorName);
													$referenceReturnArr = lookupLiteratureReference($cleanReferenceString);
													if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
														$referenceArray = array();
														$aLiteraturquellen = array();
														$innerApprovalChecking = 1;
														if(!empty($referenceReturnArr['data'])){
															foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
																if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true)
																	$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
															}
														}
													}else{
														$innerApprovalChecking = 0;
														foreach ($referenceReturnArr['data'] as $referenceReturnKey => $referenceReturnVal) {
															if(custom_in_array($referenceReturnArr['data'][$referenceReturnKey]['reference_id'], 'reference_id', $referenceArray) != true){
																$referenceArray[] = $referenceReturnArr['data'][$referenceReturnKey];
																$aLiteraturquellen [] = ($referenceReturnArr['data'][$referenceReturnKey]['full_reference'] != "") ? trim($referenceReturnArr['data'][$referenceReturnKey]['full_reference']) : "";
															}	
														}
													}
												}

												if($innerApprovalChecking == 1)
													$needApproval = 1;

												/*
												* Rule 5 Last bracket words:  “, a. a. O.” or ", a.a.O." and Hyphen (hyphenhasspacebeforeand after ( - )) (whenbothexist)
												*
												* 1 = Unknown data found with a. a. O. or Hyphen( - )
												*/ 
												if($needApproval == 1){
													// Making pruefer and reference array empty because these elements will be cleared by direct oredr or by correcting the symptom string. Also seting aao_hyphen_priority value for asking the question
													// $referenceArray = array();
													// $aLiteraturquellen = array();
													// $prueferArray = array();

													$referenceWithNoAuthorPriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$partOfSymptomPriority = 0;
													$remedyPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$referencePriority = 9;
													$prueferPriority = 10;
												}
											}
											// When string has "a. a. O." only START 
										}
									}
									/* If ending brackets are not null than examining its possibilities END */
								}
								else if(!empty($middleBracketArray) AND count($middleBracketArray) == 1){
									/* If middle brackets are not null than examining its possibilities START */
									// $approvalFor (0 = end bracket, 1 = middle bracket)
									$approvalFor = 1;
									$reArrangeMiddleBracketArray = array_values($middleBracketArray);
									$middleBracketString = (isset($reArrangeMiddleBracketArray[0]) AND $reArrangeMiddleBracketArray[0] != "") ? trim($reArrangeMiddleBracketArray[0]) : null;
									if($middleBracketString != ""){
										// Checking the existance of , . ;
										$isCommaExist = mb_substr_count($middleBracketString,",");
										$isSemicolonExist = mb_substr_count($middleBracketString,";");
										$isDotExist = mb_substr_count($middleBracketString, ".");

										/* Rule 6(Middle bracket) Conditions START */
										if(($isCommaExist != 0 OR $isSemicolonExist != 0) AND $isDotExist != 0){
											// With Comma OR Semicolon
											if (mb_strpos($middleBracketString, ',') !== false) 
												$separator = ",";
											else
												$separator = ";";

											$commaFirstOccurrence = mb_stripos ( $middleBracketString, $separator );
											$beforeTheCommaString = trim( mb_substr ( $middleBracketString, 0, $commaFirstOccurrence ) );
											$afterTheCommaString = trim( ltrim( mb_substr ( $middleBracketString, $commaFirstOccurrence ), $separator ));
											$beforeTheCommaStringInArray = explode(" ", $beforeTheCommaString);
											$afterTheCommaStringInArray = explode(" ", $afterTheCommaString);

											$isDotExistInBeforeTheCommaString = mb_substr_count($beforeTheCommaString,".");
											$isDotExistInAfterTheCommaString = mb_substr_count($afterTheCommaString,".");

											$workingString = trim($middleBracketString);

											/* REMEDY START */
											$checkRemedyApprovalStatus = 0;
											$expectedRemedyArray = explode($separator, $workingString);
											foreach ($expectedRemedyArray as $expectedRemedyKey => $expectedRemedyVal) {
												
												if($expectedRemedyVal == "")
													continue;	

												$cleanExpectedRemedyName = trim($expectedRemedyVal);
												$cleanRemedyString = (mb_substr ( $cleanExpectedRemedyName, mb_strlen ( $cleanExpectedRemedyName ) - 1, 1 ) == '.') ? $cleanExpectedRemedyName : $cleanExpectedRemedyName.'.'; 
												$remedyReturnArr = newLookupRemedy($cleanRemedyString);
												if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
													$checkRemedyApprovalStatus = 1;
													if(!empty($remedyReturnArr['data'])){
														foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
															if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
																$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
														}

													}
													else{
														$dataArr = array();
														$dataArr['remedy_id'] = null;
														$dataArr['name'] = $cleanExpectedRemedyName;
														$dataArr['kuerzel'] = "";
														if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
															$remedyArray[] = $dataArr;
													}
												}
												else{
													foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
														if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
															$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
													}
												}
											}
											// Setting last operations approval status to main approval checking variable 
											$needApproval = $checkRemedyApprovalStatus; 
											/* REMEDY END */

											if(count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString != 0 AND $isDotExistInAfterTheCommaString !=0){
												/*
												* 6.1 Single word + dot before the comma and one or more words + dot after comma (no matter if upper or lower case)
												*
												* 1 = chcek for remedis by spliting by comma
												*/

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$remedyWithSymptomPriority = 0;
													$prueferPriority = 0;
													$partOfSymptomPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 10;
												}
											}
											else if($isCommaExist == 1 AND ((count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString == 1) OR (count($afterTheCommaStringInArray) == 1 AND $isDotExistInAfterTheCommaString == 1)))
											{
												/*
												* 6.2. Single word + dot before the comma or after a comma (only one dot and one comma)
												*
												* 1 = check for remedy with symptom text (Eg: Opi., during the day)(Eg: small boils in crops, Sulph.)
												* 2 = part of symptom
												*/
												if(count($beforeTheCommaStringInArray) == 1 AND $isDotExistInBeforeTheCommaString == 1){
													$similarRemedyString = $beforeTheCommaString;
													$similarSymptomString = $afterTheCommaString;	
												}else{
													$similarRemedyString = $afterTheCommaString;
													$similarSymptomString = $beforeTheCommaString;
												}
												$cleanRemedyWithSymptomString = (mb_substr ( $workingString, mb_strlen ( $workingString ) - 1, 1 ) == '.') ? $workingString : $workingString.'.'; 
												$remedyWithSymptomReturnArr = newLookupRemedyWithSymptom($cleanRemedyWithSymptomString, $similarRemedyString, $similarSymptomString);
												if(isset($remedyWithSymptomReturnArr['need_approval']) AND $remedyWithSymptomReturnArr['need_approval'] == 0){
													$needApproval = 0;
													$remedyArray = array();
													$referenceArray = array();
													$aLiteraturquellen = array();
													$prueferArray = array();
													if(isset($remedyWithSymptomReturnArr['data'][0]['remedy']))
														$remedyArray = $remedyWithSymptomReturnArr['data'][0]['remedy'];
													$symptomOfDifferentRemedy = (isset($remedyWithSymptomReturnArr['data'][0]['symptom_of_different_remedy'])) ? $remedyWithSymptomReturnArr['data'][0]['symptom_of_different_remedy'] : "";
												}else{
													$needApproval = 1;
												}

												if($needApproval == 1){
													$referenceWithNoAuthorPriority = 0;
													$aaoHyphenPriority = 0;
													$hyphenPrueferPriority = 0;
													$hyphenReferencePriority = 0;
													$moreThanOneTagStringPriority = 0;
													$prueferPriority = 0;
													$referencePriority = 0;
													$remedyPriority = 0;
													$remedyWithSymptomPriority = 9;
													$partOfSymptomPriority = 10;
												}
											}
										}
										else if(($isCommaExist != 0 OR $isSemicolonExist != 0) OR $isDotExist == 1){
											/*
											* 6.3. One or more comma OR one dot
											*
											* 1 = part of symptom
											* 2 = chcek for remedis by spliting by comma
											*/
											$workingString = trim($middleBracketString);
											if($isCommaExist != 0 OR $isSemicolonExist != 0){
							      				if (mb_strpos($workingString, ',') !== false)
													$separator = ",";
												else if (mb_strpos($workingString, ';') !== false)
													$separator = ";";
												$expectedRemedyArray = explode($separator, $workingString);
											}else{
												if (mb_strpos($workingString, '.') !== false){
													// Split by dot(.)
													$makeStringToExplode = str_replace('.', '.{#^#}', $workingString);
													$expectedRemedyArray = explode("{#^#}", $makeStringToExplode);
												}
												else
													$expectedRemedyArray[] = $workingString;
											}

											if(!empty($expectedRemedyArray)){
												/* REMEDY START */
												$checkRemedyApprovalStatus = 0;
												foreach ($expectedRemedyArray as $expectedRemedyKey => $expectedRemedyVal) {
													
													if($expectedRemedyVal == "")
														continue;	

													$cleanExpectedRemedyName = trim($expectedRemedyVal);
													$cleanRemedyString = (mb_substr ( $cleanExpectedRemedyName, mb_strlen ( $cleanExpectedRemedyName ) - 1, 1 ) == '.') ? $cleanExpectedRemedyName : $cleanExpectedRemedyName.'.'; 
													$remedyReturnArr = newLookupRemedy($cleanRemedyString);
													if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
														$checkRemedyApprovalStatus = 1;
														if(!empty($remedyReturnArr['data'])){
															foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
																// custom_in_array(needle, needle_field, array) 
																if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
																	$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
															}

														}
														else{
															$dataArr = array();
															$dataArr['remedy_id'] = null;
															$dataArr['name'] = $cleanExpectedRemedyName;
															$dataArr['kuerzel'] = "";
															// custom_in_array(needle, needle_field, array)
															if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
																$remedyArray[] = $dataArr;
														}
													}
													else{
														foreach ($remedyReturnArr['data'] as $remedyReturnKey => $remedyReturnVal) {
															// custom_in_array(needle, needle_field, array)
															if(custom_in_array($remedyReturnArr['data'][$remedyReturnKey]['remedy_id'], 'remedy_id', $remedyArray) != true)
																$remedyArray[] = $remedyReturnArr['data'][$remedyReturnKey];
														}
													}
												}
												// Setting last operations approval status to main approval checking variable 
												$needApproval = $checkRemedyApprovalStatus; 
												/* REMEDY END */
											}

											if($needApproval == 1){
												$referenceWithNoAuthorPriority = 0;
												$aaoHyphenPriority = 0;
												$hyphenPrueferPriority = 0;
												$hyphenReferencePriority = 0;
												$moreThanOneTagStringPriority = 0;
												$prueferPriority = 0;
												$referencePriority = 0;
												$remedyWithSymptomPriority = 0;
												$partOfSymptomPriority = 9;
												$remedyPriority = 10;
											}
										}
										/* Rule 6(Middle bracket) Conditions END */	
									}
									/* If middle brackets are not null than examining its possibilities END */	
								}
								// exit;
							}
							/* Extracting Pruefer Data and Literaturquellen data End */
							if( isset($lastBracketedString) AND $lastBracketedString != "" ){
								$searchableText = removLastBracketedPart($searchableText, '(', ')');
								$searchableText = removLastBracketedPart($searchableText, '[', ']');
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

							if(!empty($preDefinedaLiteraturquellen)){
								$aLiteraturquellen = array_merge($aLiteraturquellen,$preDefinedaLiteraturquellen);
							}
							if ($aLiteraturquellen) {
								$EntnommenAus = join ( "\n", $aLiteraturquellen );
							}

							// Removeing blank spaces from the ending of the string..
							$tn = strip_tags($searchableText);
							$symptomStr = removeEndingSpecialCharactersForString($tn);
							$endingFullStopsOrCommasArr = (isset($symptomStr['last_character_array']) AND $symptomStr['last_character_array'] != "") ? $symptomStr['last_character_array'] : array();
							if(!empty($endingFullStopsOrCommasArr)){
								$searchableText = removeUnwantedSpacesFromTheEnding($searchableText, $endingFullStopsOrCommasArr);
							}

							// Finding match synonyms START
							$arrangedSynonymData = array();
							$matchedSynonyms = findMatchedSynonyms($searchableText, $globalStopWords, $availableSynonyms);
							if((isset($matchedSynonyms['status']) AND $matchedSynonyms['status'] == true) AND (isset($matchedSynonyms['return_data']) AND !empty($matchedSynonyms['return_data']))){
								$arrangedSynonymData = arrangeSynonymDataToStore($matchedSynonyms['return_data']);
							}

							$data['synonym_word'] = (isset($arrangedSynonymData['synonym_word']) AND !empty($arrangedSynonymData['synonym_word'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_word'])) : "";
							$data['strict_synonym'] = (isset($arrangedSynonymData['strict_synonym']) AND !empty($arrangedSynonymData['strict_synonym'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['strict_synonym'])) : "";
							$data['synonym_partial_1'] = (isset($arrangedSynonymData['synonym_partial_1']) AND !empty($arrangedSynonymData['synonym_partial_1'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_1'])) : "";
							$data['synonym_partial_2'] = (isset($arrangedSynonymData['synonym_partial_2']) AND !empty($arrangedSynonymData['synonym_partial_2'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_partial_2'])) : "";
							$data['synonym_general'] = (isset($arrangedSynonymData['synonym_general']) AND !empty($arrangedSynonymData['synonym_general'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_general'])) : "";
							$data['synonym_minor'] = (isset($arrangedSynonymData['synonym_minor']) AND !empty($arrangedSynonymData['synonym_minor'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_minor'])) : "";
							$data['synonym_nn'] = (isset($arrangedSynonymData['synonym_nn']) AND !empty($arrangedSynonymData['synonym_nn'])) ? mysqli_real_escape_string($db, serialize($arrangedSynonymData['synonym_nn'])) : "";
							// Finding match synonyms END

							$bracketedString = (!empty($allBrackets)) ? implode(", ", $allBrackets) : null;
							$middleBracketApprovalString = ( isset($middleBracketString) AND $middleBracketString != "" ) ? $middleBracketString : null;
							$approvableString = ( isset($lastBracketedString) AND $lastBracketedString != "" ) ? $lastBracketedString : $tagsApprovalString;
							
							/* quelle_import_test table fields start */
							$data['quelle_code'] = ($masterQuelleCode != "") ? mysqli_real_escape_string($db, trim($masterQuelleCode)) : null;
							if($isCodingWithSymptomNumber == 1){
		 						$data['Symptomnummer']= ( $Symptomnummer != "" and $Symptomnummer != 0 ) ? mysqli_real_escape_string($db, $Symptomnummer) : null;
		 						$data['is_symptom_number_mismatch']= $isSymptomNumberMismatch;
		 					}
		 					else{
		 						$data['Symptomnummer']= null;
		 						$data['is_symptom_number_mismatch']= 0;
		 					}
							$data['SeiteOriginalVon']=($SeiteOriginalVon == '') ? null : mysqli_real_escape_string($db, $SeiteOriginalVon);
							$data['SeiteOriginalBis']=($SeiteOriginalBis == '') ? null : mysqli_real_escape_string($db, $SeiteOriginalBis);
							$data['Beschreibung']=mysqli_real_escape_string($db, $Beschreibung);
							$data['BeschreibungOriginal']=mysqli_real_escape_string($db, $BeschreibungOriginal);
							$data['BeschreibungPlain']=mysqli_real_escape_string($db, $BeschreibungPlain);
							$data['searchable_text']=mysqli_real_escape_string($db, $searchableText);
							$data['bracketedString']= ($bracketedString != "") ? mysqli_real_escape_string($db, $bracketedString) : null;
							$data['timeString']=mysqli_real_escape_string($db, $timeString);
							$data['Fussnote']=mysqli_real_escape_string($db, $Fussnote);
							$data['approval_for'] = ($approvalFor != "") ? mysqli_real_escape_string($db, $approvalFor) : 0;
							/* Checking if any pruefer needs approval */
				            if(isset($needApproval) AND $needApproval == 1){
				            	if($referenceWithNoAuthorPriority == 0 AND $moreThanOneTagStringPriority == 0 AND $aaoHyphenPriority == 0 AND $hyphenPrueferPriority == 0 AND $hyphenReferencePriority == 0 AND $remedyWithSymptomPriority == 0 AND $prueferPriority == 0 AND $partOfSymptomPriority == 0 AND $referencePriority == 0 AND $remedyPriority == 0)
				            		$data['need_approval'] = 0;
				            	else
				            		$data['need_approval'] = 1;
				            }
				            else
				            	$data['need_approval'] = 0;
				            if($hyphenApprovalString != ""){
				            	$data['approval_string'] = ( isset($hyphenApprovalString) AND $hyphenApprovalString != "" ) ? mysqli_real_escape_string($db, $hyphenApprovalString) : null;
				            	$data['full_approval_string_when_hyphen'] = ( isset($approvableString) AND $approvableString != "" ) ? mysqli_real_escape_string($db, $approvableString) : null;
				            	$data['full_approval_string_when_hyphen_unchanged'] = ( isset($approvableString) AND $approvableString != "" ) ? mysqli_real_escape_string($db, $approvableString) : null;
				            }else{
				            	$data['approval_string'] = ( isset($approvableString) AND $approvableString != "" ) ? mysqli_real_escape_string($db, $approvableString) : null;
				            	$data['full_approval_string_when_hyphen'] = ( isset($approvableString) AND $approvableString != "" ) ? mysqli_real_escape_string($db, $approvableString) : null;
				            	$data['full_approval_string_when_hyphen_unchanged'] = ( isset($approvableString) AND $approvableString != "" ) ? mysqli_real_escape_string($db, $approvableString) : null;
				            }
							$data['EntnommenAus']=mysqli_real_escape_string($db, $EntnommenAus);
							$data['Verweiss']=mysqli_real_escape_string($db, $Verweiss);
							$data['Graduierung']=mysqli_real_escape_string($db, $Graduierung);
							$data['BereichID']=mysqli_real_escape_string($db, $BereichID);
							$data['Kommentar']=mysqli_real_escape_string($db, $Kommentar);
							$data['Unklarheiten']=mysqli_real_escape_string($db, $Unklarheiten);
				            $data['symptom_of_different_remedy'] = ( isset($symptomOfDifferentRemedy) AND $symptomOfDifferentRemedy != "" ) ? mysqli_real_escape_string($db, $symptomOfDifferentRemedy) : null;
							/* quelle_import_test table fields end */

				            $query="INSERT INTO temp_quelle_import_test (master_id, arznei_id, quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung, Beschreibung_unchanged, BeschreibungOriginal, BeschreibungPlain, searchable_text, bracketedString, timeString, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, symptom_of_different_remedy, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, need_approval, approval_for, middle_bracket_approval_string, approval_string, full_approval_string_when_hyphen, full_approval_string_when_hyphen_unchanged, is_pre_defined_tags_approval, is_symptom_number_mismatch, pruefer_priority, remedy_priority, part_of_symptom_priority, reference_with_no_author_priority, remedy_with_symptom_priority, more_than_one_tag_string_priority, aao_hyphen_priority, hyphen_pruefer_priority, hyphen_reference_priority, reference_priority, direct_order_priority, is_excluded_in_comparison) VALUES (".$masterId.", ".$masterArzneiId.", ".$masterQuelleId.", NULLIF('".$data['quelle_code']."', ''), NULLIF('".$data['Symptomnummer']."', ''), NULLIF('".$data['SeiteOriginalVon']."', ''), NULLIF('".$data['SeiteOriginalBis']."', ''),'".$data['Beschreibung']."','".$data['Beschreibung']."','".$data['BeschreibungOriginal']."','".$data['BeschreibungPlain']."', NULLIF('".$data['searchable_text']."', ''), NULLIF('".$data['bracketedString']."', ''),'".$data['timeString']."','".$data['Fussnote']."', '".$data['EntnommenAus']."', '".$data['Verweiss']."', '".$data['Graduierung']."', '".$data['BereichID']."', '".$data['Kommentar']."', '".$data['Unklarheiten']."', NULLIF('".$data['symptom_of_different_remedy']."', ''), NULLIF('".$data['synonym_word']."', ''), NULLIF('".$data['strict_synonym']."', ''), NULLIF('".$data['synonym_partial_1']."', ''), NULLIF('".$data['synonym_partial_2']."', ''), NULLIF('".$data['synonym_general']."', ''), NULLIF('".$data['synonym_minor']."', ''), NULLIF('".$data['synonym_nn']."', ''), '".$data['need_approval']."', '".$data['approval_for']."', NULLIF('".$middleBracketApprovalString."', ''), NULLIF('".$data['approval_string']."', ''), NULLIF('".$data['full_approval_string_when_hyphen']."', ''), NULLIF('".$data['full_approval_string_when_hyphen_unchanged']."', ''), ".$isPreDefinedTagsApproval.", '".$data['is_symptom_number_mismatch']."', ".$prueferPriority.", ".$remedyPriority.", ".$partOfSymptomPriority.", ".$referenceWithNoAuthorPriority.", ".$remedyWithSymptomPriority.", ".$moreThanOneTagStringPriority.", ".$aaoHyphenPriority.", ".$hyphenPrueferPriority.", ".$hyphenReferencePriority.", ".$referencePriority.", ".$directOrderPriority.", ".$isExcludedInComparison.")";

				            $db->query($query);
				            $insertedSymtomId = mysqli_insert_id($db);
				            // Grading implementation section START
				            if($Graduierung != "" AND ($Graduierung == 0 OR $Graduierung == 1 OR $Graduierung == 1.5 OR $Graduierung == 2 OR $Graduierung == 2.5 OR $Graduierung == 3 OR $Graduierung == 3.5 OR $Graduierung == 4 OR $Graduierung == 4.5 OR $Graduierung == 5 OR $Graduierung == 5.5))
							{
								$symptomGradingResult = mysqli_query($db, "SELECT symptom_grading_settings_id FROM temp_symptom_grading_settings WHERE symptom_id = '".$insertedSymtomId."' AND master_id ='".$masterId."'");
								if(mysqli_num_rows($symptomGradingResult) > 0){
									$deleteSymptomGrading ="DELETE FROM temp_symptom_grading_settings WHERE symptom_id = '".$insertedSymtomId."' AND master_id ='".$masterId."'";
									$db->query($deleteSymptomGrading);
								}

								$symptomGradingInsertQuery="INSERT INTO temp_symptom_grading_settings (master_id, symptom_id, normal, normal_within_parentheses, normal_end_with_t, normal_end_with_tt, normal_begin_with_degree, normal_end_with_degree, normal_begin_with_asterisk, normal_begin_with_asterisk_end_with_t, normal_begin_with_asterisk_end_with_tt, normal_begin_with_asterisk_end_with_degree, sperrschrift, sperrschrift_begin_with_degree, sperrschrift_begin_with_asterisk, sperrschrift_bold, sperrschrift_bold_begin_with_degree, sperrschrift_bold_begin_with_asterisk, kursiv, kursiv_end_with_t, kursiv_end_with_tt, kursiv_begin_with_degree, kursiv_end_with_degree, kursiv_begin_with_asterisk, kursiv_begin_with_asterisk_end_with_t, kursiv_begin_with_asterisk_end_with_tt, kursiv_begin_with_asterisk_end_with_degree, kursiv_bold, kursiv_bold_begin_with_asterisk_end_with_t, kursiv_bold_begin_with_asterisk_end_with_tt, kursiv_bold_begin_with_degree, kursiv_bold_begin_with_asterisk, kursiv_bold_begin_with_asterisk_end_with_degree, fett, fett_end_with_t, fett_end_with_tt, fett_begin_with_degree, fett_end_with_degree, fett_begin_with_asterisk, fett_begin_with_asterisk_end_with_t, fett_begin_with_asterisk_end_with_tt, fett_begin_with_asterisk_end_with_degree, gross, gross_begin_with_degree, gross_begin_with_asterisk, gross_bold, gross_bold_begin_with_degree, gross_bold_begin_with_asterisk, pi_sign, one_bar, two_bar, three_bar, three_and_half_bar, four_bar, four_and_half_bar, five_bar, ersteller_datum) VALUES (NULLIF('".$masterId."', ''), NULLIF('".$insertedSymtomId."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$data['Graduierung']."', ''), NULLIF('".$date."', ''))";
								$db->query($symptomGradingInsertQuery);
							}
				            // Grading implementation section END

			            	/* Pruefer Start */
			            	if(!empty($prueferArray)){
		            			foreach ($prueferArray as $pruKey => $pruVal) {
		            				if(isset($prueferArray[$pruKey]['pruefer_id']) AND $prueferArray[$pruKey]['pruefer_id'] != ""){
		            					$isOneUnknownElementInHyphen = (isset($prueferArray[$pruKey]['is_one_unknown_element_in_hyphen']) AND $prueferArray[$pruKey]['is_one_unknown_element_in_hyphen'] != "") ? $prueferArray[$pruKey]['is_one_unknown_element_in_hyphen'] : 0; 
					            		$prueferQuery = "INSERT INTO temp_symptom_pruefer (symptom_id, pruefer_id, is_one_unknown_element_in_hyphen) VALUES ('".$insertedSymtomId."', '".$prueferArray[$pruKey]['pruefer_id']."', '".$isOneUnknownElementInHyphen."')";
							            $db->query($prueferQuery);

							            if($data['need_approval'] == 0){
							            	// When a symptom needs no approval than storing it's pruefer details in temp_approved_pruefer for using in a. a. O. search process
							            	$tempApprovedPrueferQuery = "INSERT INTO temp_approved_pruefer (master_id, symptom_id, pruefer_id, approval_string) VALUES ('".$masterId."', '".$insertedSymtomId."', '".$prueferArray[$pruKey]['pruefer_id']."', NULLIF('".$data['approval_string']."', ''))";
							            	$db->query($tempApprovedPrueferQuery);  
							            }
		            				}else{
		            					if(isset($prueferArray[$pruKey]['suchname']) AND $prueferArray[$pruKey]['suchname'] != ""){
		            						$prueferArray[$pruKey]['suchname'] = mysqli_real_escape_string($db, $prueferArray[$pruKey]['suchname']);
											$prueferInsertQuery = "INSERT INTO temp_pruefer (symptom_id, suchname) VALUES ('".$insertedSymtomId."', '".$prueferArray[$pruKey]['suchname']."')";
		            						$db->query($prueferInsertQuery);
		            						$newPrueferId = mysqli_insert_id($db);
		            						
		            						$prueferQuery = "INSERT INTO temp_symptom_pruefer (symptom_id, pruefer_id, is_new) VALUES ('".$insertedSymtomId."', '".$newPrueferId."', 1)";
							            	$db->query($prueferQuery);
		            					}
		            				}
		            			}
			            	}
			            	/* Pruefer End */
			            	/* Remedy Start */
			            	if(!empty($remedyArray)){
			            		// echo "here... <br>";
			            		$remedyText = "";
		            			foreach ($remedyArray as $remdKey => $remdVal) {
		            				$remedyArray[$remdKey]['name'] = mysqli_real_escape_string($db, $remedyArray[$remdKey]['name']);
		            				if(isset($needApproval) AND $needApproval == 1){
		            					if(isset($remedyArray[$remdKey]['remedy_id']) AND $remedyArray[$remdKey]['remedy_id'] != ""){
						            		$remedyQuery = "INSERT INTO temp_remedy (symptom_id, main_remedy_id, name) VALUES ('".$insertedSymtomId."', '".$remedyArray[$remdKey]['remedy_id']."', '".$remedyArray[$remdKey]['name']."')";
						            		$db->query($remedyQuery);
								            // $db->query("INSERT INTO temp_remedy (symptom_id, main_remedy_id, name) VALUES ('659', '12', 'Ammonium causticum')");
								            // echo $remedyQuery."<br>";
			            				}else{
											$remedyQuery = "INSERT INTO temp_remedy (symptom_id, name, is_new) VALUES ('".$insertedSymtomId."', '".$remedyArray[$remdKey]['name']."', 1)";
								            $db->query($remedyQuery);
			            				}
		            				}
		            				else{
		            					$remedyText = $remedyText.$remedyArray[$remdKey]['name'].", ";
		            				}
		            			}
		            			// if(isset($remedyText) AND $remedyText != ""){
		            			// 	$remedyText = rtrim($remedyText, ", ");
            					// 	$symptomUpdateQuery="UPDATE temp_quelle_import_test SET Remedy = '".$remedyText."' WHERE id = '".$insertedSymtomId."'";
								// 	$db->query($symptomUpdateQuery);
            					// }
			            	}
			            	// echo "out.. <br>";
			            	/* Remedy End */
			            	/* Reference Start */
			            	if(!empty($referenceArray)){
		            			foreach ($referenceArray as $refKey => $refVal) {
		            				if(isset($referenceArray[$refKey]['reference_id']) AND $referenceArray[$refKey]['reference_id'] != ""){
		            					$isOneUnknownElementInHyphen = (isset($referenceArray[$refKey]['is_one_unknown_element_in_hyphen']) AND $referenceArray[$refKey]['is_one_unknown_element_in_hyphen'] != "") ? $referenceArray[$refKey]['is_one_unknown_element_in_hyphen'] : 0; 
					            		$referenceQuery = "INSERT INTO temp_symptom_reference (symptom_id, reference_id, is_one_unknown_element_in_hyphen) VALUES ('".$insertedSymtomId."', '".$referenceArray[$refKey]['reference_id']."', '".$isOneUnknownElementInHyphen."')";
							            $db->query($referenceQuery);

							            if($data['need_approval'] == 0){
							            	// When a symptom needs no approval than storing it's reference details in temp_approved_reference for using in a. a. O. search process
							            	$tempApprovedReferenceQuery = "INSERT INTO temp_approved_reference (master_id, symptom_id, reference_id, approval_string) VALUES ('".$masterId."', '".$insertedSymtomId."', '".$referenceArray[$refKey]['reference_id']."', NULLIF('".$data['approval_string']."', ''))";
							            	$db->query($tempApprovedReferenceQuery); 
							            }
		            				}else{
		            					if(isset($referenceArray[$refKey]['full_reference']) AND $referenceArray[$refKey]['full_reference'] != ""){
		            						$referenceArray[$refKey]['full_reference'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['full_reference']);
		            						$referenceArray[$refKey]['autor'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['autor']);
		            						$referenceArray[$refKey]['reference'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['reference']);
											$referenceInsertQuery = "INSERT INTO temp_reference (symptom_id, full_reference, autor, reference) VALUES ('".$insertedSymtomId."', '".$referenceArray[$refKey]['full_reference']."', '".$referenceArray[$refKey]['autor']."', '".$referenceArray[$refKey]['reference']."')";
		            						$db->query($referenceInsertQuery);
		            						$newReferenceId = mysqli_insert_id($db);
		            						
		            						$referenceQuery = "INSERT INTO temp_symptom_reference (symptom_id, reference_id, is_new) VALUES ('".$insertedSymtomId."', '".$newReferenceId."', 1)";
							            	$db->query($referenceQuery);
		            					}
		            				}
		            			}
			            	}
			            	/* Reference End */
			            	/* Pre Defined Reference Set Start */
			            	if(!empty($preDefinedSymptomReferenceLinkingArray)){
			            		foreach ($preDefinedSymptomReferenceLinkingArray as $preDefinedRefKey => $preDefinedRefVal) {
			            			$preDefinedReferenceQuery = "INSERT INTO temp_pre_defined_symptom_reference (symptom_id, reference_id) VALUES ('".$insertedSymtomId."', '".$preDefinedRefVal."')";
							        $db->query($preDefinedReferenceQuery);
			            		}
			            	}
			            	/* Pre Defined Reference Set End */
				            

							if ($Symptomnummer > 0)
								$Symptomnummer += 1;
							
							$Beschreibung = '';
							// $Graduierung = '';
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
							$preDefinedSymptomReferenceLinkingArray = array();
							$preDefinedaLiteraturquellen = array ();
						}
						$rownum ++;

						// If we arrive here, it means that no exception was thrown
			    		// i.e. no query has failed, and we can commit the transaction
			    		$db->commit();
			    		// echo "<br>Yoo<br>";
			    		// exit;

					}catch (Exception $e) {
					    // An exception has been thrown
					    // We must rollback the transaction
					    $db->rollback();
					    $isThereAnyTransactionError = 1;

					    /* Delete Temp table data START */
						deleteSourceImportTempData($masterId);
						/* Delete Temp table data END */

					    break;
					}
					/* MySQL Transaction END */	

				}

			}

			// If No Transaction error occur above
			if($isThereAnyTransactionError == 0){
				/* First check if there is any symptom found with the master_id */
				$isAnySymptomResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test where master_id = '".$masterId."'");
				if(mysqli_num_rows($isAnySymptomResult) > 0){
					
					/* Check is there any unclear symptom found */
					$needApproveSearchResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test where master_id = '".$masterId."' AND need_approval = 1");
					if(mysqli_num_rows($needApproveSearchResult) > 0){
						$parameterString = '?master='.$masterId;
					}else{
						$parameterString = '';
						/* Inserting Temp table data to Main tables START */
						$masterResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_master where id = '".$masterId."'");
						if(mysqli_num_rows($masterResult) > 0){
							$masterData = mysqli_fetch_assoc($masterResult); 

							try{
								// First of all, let's begin a transaction
								$db->begin_transaction();
								if($masterData['pruefer_ids'] != "")
									$masterDataPrueferIdsArray = explode(",", $masterData['pruefer_ids']);
								else
									$masterDataPrueferIdsArray = array();
								$masterData['import_rule'] = ($masterData['import_rule'] != "") ? mysqli_real_escape_string($db, $masterData['import_rule']) : null;
								$masterData['importing_language'] = ($masterData['importing_language'] != "") ? mysqli_real_escape_string($db, $masterData['importing_language']) : null;

					            $masterData['is_symptoms_available_in_de'] = ($masterData['importing_language'] == "de") ? 1 : 0;
					            $masterData['is_symptoms_available_in_en'] = ($masterData['importing_language'] == "en") ? 1 : 0;
					            $masterData['translation_method_of_de'] = ($masterData['importing_language'] == "de") ? "Professional Translation" : null;
					            $masterData['translation_method_of_en'] = ($masterData['importing_language'] == "en") ? "Professional Translation" : null;

								$masterData['arznei_id'] = ($masterData['arznei_id'] != "") ? mysqli_real_escape_string($db, $masterData['arznei_id']) : null;
								$masterData['quelle_id'] = ($masterData['quelle_id'] != "") ? mysqli_real_escape_string($db, $masterData['quelle_id']) : null;
								$masterData['pruefer_ids'] = ($masterData['pruefer_ids'] != "") ? mysqli_real_escape_string($db, $masterData['pruefer_ids']) : null;
								$masterData['excluding_symptoms_chapters'] = ($masterData['excluding_symptoms_chapters'] != "") ? mysqli_real_escape_string($db, $masterData['excluding_symptoms_chapters']) : null;
								$masterData['import_comment'] = ($masterData['import_comment'] != "") ? mysqli_real_escape_string($db, $masterData['import_comment']) : null;
								$masterData['ersteller_datum'] = ($masterData['ersteller_datum'] != "") ? mysqli_real_escape_string($db, $masterData['ersteller_datum']) : null;
								$masterMainInsertQuery="INSERT INTO quelle_import_master (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, pruefer_ids, excluding_symptoms_chapters, import_comment, ersteller_datum) VALUES ('".$masterData['import_rule']."', NULLIF('".$masterData['importing_language']."', ''), NULLIF('".$masterData['is_symptoms_available_in_de']."', ''), NULLIF('".$masterData['is_symptoms_available_in_en']."', ''), NULLIF('".$masterData['translation_method_of_de']."', ''), NULLIF('".$masterData['translation_method_of_en']."', ''), NULLIF('".$masterData['arznei_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['pruefer_ids']."', ''), NULLIF('".$masterData['excluding_symptoms_chapters']."', ''), NULLIF('".$masterData['import_comment']."', ''), '".$date."')";
					            $db->query($masterMainInsertQuery);
					            $mainMasterId = mysqli_insert_id($db);

					            // These are imported quelle(means normal quelle, these are not created form save comparison) so they are linked with normal quelle table not with quelle_backup table here both quelle_id and original_quelle_id is from normal quelle table 
					            $quelleSymptomsMasterBackupInsertQuery="INSERT INTO quelle_import_master_backup (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, original_quelle_id, pruefer_ids, excluding_symptoms_chapters, import_comment, stand, ersteller_datum) VALUES ('".$masterData['import_rule']."', NULLIF('".$masterData['importing_language']."', ''), NULLIF('".$masterData['is_symptoms_available_in_de']."', ''), NULLIF('".$masterData['is_symptoms_available_in_en']."', ''), NULLIF('".$masterData['translation_method_of_de']."', ''), NULLIF('".$masterData['translation_method_of_en']."', ''), NULLIF('".$masterData['arznei_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['pruefer_ids']."', ''), NULLIF('".$masterData['excluding_symptoms_chapters']."', ''), NULLIF('".$masterData['import_comment']."', ''), '".$date."', NULLIF('".$masterData['ersteller_datum']."', ''))";
					            $db->query($quelleSymptomsMasterBackupInsertQuery);
					            $quelleSymptomsMasterBackupId = $db->insert_id;

					            // Making arznei quelle relationship
					            if($masterData['arznei_id'] != "" AND $masterData['quelle_id'] != ""){
					            	$arzneiQuelleResult = mysqli_query($db, "SELECT arznei_id FROM arznei_quelle where arznei_id = '".$masterData['arznei_id']."' AND quelle_id = '".$masterData['quelle_id']."'");
									if(mysqli_num_rows($arzneiQuelleResult) == 0){
										$arzneiQuelleInsertQuery="INSERT INTO arznei_quelle (arznei_id, quelle_id, ersteller_datum) VALUES ('".$masterData['arznei_id']."', '".$masterData['quelle_id']."', '".$date."')";
					            		$db->query($arzneiQuelleInsertQuery);
									}
					            }

								// If we arrive here, it means that no exception was thrown
							    // i.e. no query has failed, and we can commit the transaction
							    $db->commit();
							}catch (Exception $e) {
							    // An exception has been thrown
							    // We must rollback the transaction
							    $db->rollback();
							    $isThereAnyTransactionError = 1;
							}

							try{
								// First of all, let's begin a transaction
								$db->begin_transaction();

								/* Insert Symptoms START */
					            $symptomResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test where master_id = '".$masterId."'");
								if(mysqli_num_rows($symptomResult) > 0){
									while($symptomData = mysqli_fetch_array($symptomResult)){
										$symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
										$symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
										$symptomData['original_quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
										$symptomData['quelle_code'] = ($symptomData['quelle_code'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_code']) : null;
										$symptomData['Symptomnummer'] = mysqli_real_escape_string($db, $symptomData['Symptomnummer']);
										$symptomData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalVon']);
										$symptomData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalBis']);
										$symptomData['Beschreibung_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['Beschreibung']) : null;
										$symptomData['Beschreibung_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['Beschreibung']) : null;
										$symptomData['BeschreibungOriginal_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
										$symptomData['BeschreibungOriginal_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
										$symptomData['BeschreibungFull_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
										$symptomData['BeschreibungFull_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
										$symptomData['BeschreibungPlain_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungPlain']) : null;
										$symptomData['BeschreibungPlain_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungPlain']) : null;
										$symptomData['searchable_text_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['searchable_text']) : null;
										$symptomData['searchable_text_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['searchable_text']) : null;
										$symptomData['bracketedString_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['bracketedString']) : null;
										$symptomData['bracketedString_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['bracketedString']) : null;
										$symptomData['timeString_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['timeString']) : null;
										$symptomData['timeString_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['timeString']) : null;
										$symptomData['Fussnote'] = mysqli_real_escape_string($db, $symptomData['Fussnote']);
										$symptomData['EntnommenAus'] = mysqli_real_escape_string($db, $symptomData['EntnommenAus']);
										$symptomData['Verweiss'] = mysqli_real_escape_string($db, $symptomData['Verweiss']);
										$symptomData['Graduierung'] = mysqli_real_escape_string($db, $symptomData['Graduierung']);
										$symptomData['BereichID'] = mysqli_real_escape_string($db, $symptomData['BereichID']);
										$symptomData['Kommentar'] = mysqli_real_escape_string($db, $symptomData['Kommentar']);
										$symptomData['Unklarheiten'] = mysqli_real_escape_string($db, $symptomData['Unklarheiten']);
										$symptomData['Remedy'] = mysqli_real_escape_string($db, $symptomData['Remedy']);
										$symptomData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $symptomData['symptom_of_different_remedy']);
										$symptomData['synonym_word'] = mysqli_real_escape_string($db, $symptomData['synonym_word']);
										$symptomData['strict_synonym'] = mysqli_real_escape_string($db, $symptomData['strict_synonym']);
										$symptomData['synonym_partial_1'] = mysqli_real_escape_string($db, $symptomData['synonym_partial_1']);
										$symptomData['synonym_partial_2'] = mysqli_real_escape_string($db, $symptomData['synonym_partial_2']);
										$symptomData['synonym_general'] = mysqli_real_escape_string($db, $symptomData['synonym_general']);
										$symptomData['synonym_minor'] = mysqli_real_escape_string($db, $symptomData['synonym_minor']);
										$symptomData['synonym_nn'] = mysqli_real_escape_string($db, $symptomData['synonym_nn']);
										$symptomData['is_symptom_number_mismatch'] = mysqli_real_escape_string($db, $symptomData['is_symptom_number_mismatch']);
										$symptomData['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $symptomData['is_excluded_in_comparison']);

										$mainSymptomInsertQuery="INSERT INTO quelle_import_test (master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, is_excluded_in_comparison, is_symptom_number_mismatch) VALUES (".$mainMasterId.", NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['synonym_word']."', ''), NULLIF('".$symptomData['strict_synonym']."', ''), NULLIF('".$symptomData['synonym_partial_1']."', ''), NULLIF('".$symptomData['synonym_partial_2']."', ''), NULLIF('".$symptomData['synonym_general']."', ''), NULLIF('".$symptomData['synonym_minor']."', ''), NULLIF('".$symptomData['synonym_nn']."', ''), '".$symptomData['is_excluded_in_comparison']."', '".$symptomData['is_symptom_number_mismatch']."')";
								
							            $db->query($mainSymptomInsertQuery);
							            $mainSymtomId = mysqli_insert_id($db);

							            // ADD IN THE BACKUP
										$mainSymptomBackupInsertQuery="INSERT INTO quelle_import_backup (master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, is_excluded_in_comparison, is_symptom_number_mismatch) VALUES (".$quelleSymptomsMasterBackupId.", NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['synonym_word']."', ''), NULLIF('".$symptomData['strict_synonym']."', ''), NULLIF('".$symptomData['synonym_partial_1']."', ''), NULLIF('".$symptomData['synonym_partial_2']."', ''), NULLIF('".$symptomData['synonym_general']."', ''), NULLIF('".$symptomData['synonym_minor']."', ''), NULLIF('".$symptomData['synonym_nn']."', ''), '".$symptomData['is_excluded_in_comparison']."', '".$symptomData['is_symptom_number_mismatch']."')";
							            $db->query($mainSymptomBackupInsertQuery);
							            $mainSymtomBackupId = $db->insert_id;

							            // Symptom grading setting transfer start 
							            $symptomGradingSettingsResult = mysqli_query($db, "SELECT * FROM temp_symptom_grading_settings where symptom_id = '".$symptomData['id']."' AND master_id = '".$masterId."'");
										if(mysqli_num_rows($symptomGradingSettingsResult) > 0){
											while($symptomGradingData = mysqli_fetch_array($symptomGradingSettingsResult))
											{
												$gradingData = array();
												$gradingData['normal']= mysqli_real_escape_string($db, $symptomGradingData['normal']);
											    $gradingData['normal_within_parentheses']= mysqli_real_escape_string($db, $symptomGradingData['normal_within_parentheses']);
											    $gradingData['normal_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_t']);
											    $gradingData['normal_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_tt']);
											    $gradingData['normal_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_degree']);
											    $gradingData['normal_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_degree']);
											    $gradingData['normal_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk']);
											    $gradingData['normal_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_t']); 
											    $gradingData['normal_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_tt']);
											    $gradingData['normal_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_degree']);
											    $gradingData['sperrschrift']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift']);
											    $gradingData['sperrschrift_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_begin_with_degree']);
											    $gradingData['sperrschrift_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_begin_with_asterisk']);
											    $gradingData['sperrschrift_bold']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold']);
											    $gradingData['sperrschrift_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold_begin_with_degree']);
											    $gradingData['sperrschrift_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold_begin_with_asterisk']);
											    $gradingData['kursiv']= mysqli_real_escape_string($db, $symptomGradingData['kursiv']);
											    $gradingData['kursiv_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_t']);
											    $gradingData['kursiv_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_tt']);
											    $gradingData['kursiv_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_degree']);
											    $gradingData['kursiv_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_degree']);
											    $gradingData['kursiv_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk']);
											    $gradingData['kursiv_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_t']);
											    $gradingData['kursiv_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_tt']);
											    $gradingData['kursiv_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_degree']);
											    $gradingData['kursiv_bold']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold']);
											    $gradingData['kursiv_bold_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_t']);
											    $gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_tt']);
											    $gradingData['kursiv_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_degree']);
											    $gradingData['kursiv_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk']);
											    $gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_degree']);
											    $gradingData['fett']= mysqli_real_escape_string($db, $symptomGradingData['fett']);
											    $gradingData['fett_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_t']);
											    $gradingData['fett_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_tt']);
											    $gradingData['fett_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_degree']);
											    $gradingData['fett_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_degree']);
											    $gradingData['fett_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk']);
											    $gradingData['fett_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_t']);
											    $gradingData['fett_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_tt']);
											    $gradingData['fett_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_degree']);
											    $gradingData['gross']= mysqli_real_escape_string($db, $symptomGradingData['gross']);
											    $gradingData['gross_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['gross_begin_with_degree']);
											    $gradingData['gross_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['gross_begin_with_asterisk']);
											    $gradingData['gross_bold']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold']);
											    $gradingData['gross_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold_begin_with_degree']);
											    $gradingData['gross_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold_begin_with_asterisk']);
											    $gradingData['pi_sign']= mysqli_real_escape_string($db, $symptomGradingData['pi_sign']);
											    $gradingData['one_bar']= mysqli_real_escape_string($db, $symptomGradingData['one_bar']);
											    $gradingData['two_bar']= mysqli_real_escape_string($db, $symptomGradingData['two_bar']);
											    $gradingData['three_bar']= mysqli_real_escape_string($db, $symptomGradingData['three_bar']);
											    $gradingData['three_and_half_bar']= mysqli_real_escape_string($db, $symptomGradingData['three_and_half_bar']);
											    $gradingData['four_bar']= mysqli_real_escape_string($db, $symptomGradingData['four_bar']);
											    $gradingData['four_and_half_bar']= mysqli_real_escape_string($db, $symptomGradingData['four_and_half_bar']);
											    $gradingData['five_bar']= mysqli_real_escape_string($db, $symptomGradingData['five_bar']);

											    $symptomGradingInsertQuery="INSERT INTO symptom_grading_settings (symptom_id, normal, normal_within_parentheses, normal_end_with_t, normal_end_with_tt, normal_begin_with_degree, normal_end_with_degree, normal_begin_with_asterisk, normal_begin_with_asterisk_end_with_t, normal_begin_with_asterisk_end_with_tt, normal_begin_with_asterisk_end_with_degree, sperrschrift, sperrschrift_begin_with_degree, sperrschrift_begin_with_asterisk, sperrschrift_bold, sperrschrift_bold_begin_with_degree, sperrschrift_bold_begin_with_asterisk, kursiv, kursiv_end_with_t, kursiv_end_with_tt, kursiv_begin_with_degree, kursiv_end_with_degree, kursiv_begin_with_asterisk, kursiv_begin_with_asterisk_end_with_t, kursiv_begin_with_asterisk_end_with_tt, kursiv_begin_with_asterisk_end_with_degree, kursiv_bold, kursiv_bold_begin_with_asterisk_end_with_t, kursiv_bold_begin_with_asterisk_end_with_tt, kursiv_bold_begin_with_degree, kursiv_bold_begin_with_asterisk, kursiv_bold_begin_with_asterisk_end_with_degree, fett, fett_end_with_t, fett_end_with_tt, fett_begin_with_degree, fett_end_with_degree, fett_begin_with_asterisk, fett_begin_with_asterisk_end_with_t, fett_begin_with_asterisk_end_with_tt, fett_begin_with_asterisk_end_with_degree, gross, gross_begin_with_degree, gross_begin_with_asterisk, gross_bold, gross_bold_begin_with_degree, gross_bold_begin_with_asterisk, pi_sign, one_bar, two_bar, three_bar, three_and_half_bar, four_bar, four_and_half_bar, five_bar, ersteller_datum) VALUES (NULLIF('".$mainSymtomId."', ''), NULLIF('".$gradingData['normal']."', ''), NULLIF('".$gradingData['normal_within_parentheses']."', ''), NULLIF('".$gradingData['normal_end_with_t']."', ''), NULLIF('".$gradingData['normal_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_degree']."', ''), NULLIF('".$gradingData['normal_end_with_degree']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_asterisk']."', ''), NULLIF('".$gradingData['sperrschrift_bold']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv']."', ''), NULLIF('".$gradingData['kursiv_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['fett']."', ''), NULLIF('".$gradingData['fett_end_with_t']."', ''), NULLIF('".$gradingData['fett_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_degree']."', ''), NULLIF('".$gradingData['fett_end_with_degree']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['gross']."', ''), NULLIF('".$gradingData['gross_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_begin_with_asterisk']."', ''), NULLIF('".$gradingData['gross_bold']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['pi_sign']."', ''), NULLIF('".$gradingData['one_bar']."', ''), NULLIF('".$gradingData['two_bar']."', ''), NULLIF('".$gradingData['three_bar']."', ''), NULLIF('".$gradingData['three_and_half_bar']."', ''), NULLIF('".$gradingData['four_bar']."', ''), NULLIF('".$gradingData['four_and_half_bar']."', ''), NULLIF('".$gradingData['five_bar']."', ''), NULLIF('".$date."', ''))";
												$db->query($symptomGradingInsertQuery);
											}
										}
										// Symptom grading setting transfer end

										/* Insert symptom_remedy relation START */
							            $symptomRemedyResult = mysqli_query($db, "SELECT symptom_id, main_remedy_id, is_new FROM temp_remedy where symptom_id = '".$symptomData['id']."'");
										if(mysqli_num_rows($symptomRemedyResult) > 0){
											while($symptomRemedyData = mysqli_fetch_array($symptomRemedyResult)){
												$mainSymptomRemedyInsertQuery = "INSERT INTO symptom_remedy (symptom_id, remedy_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomRemedyData['main_remedy_id']."', '".$date."')";
							            		$db->query($mainSymptomRemedyInsertQuery);

							            		// ADD IN THE BACKUP
								            	$mainSymptomRemedyBackupInsertQuery = "INSERT INTO symptom_remedy_backup (symptom_id, remedy_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomRemedyData['main_remedy_id']."', '".$date."')";
								            	$db->query($mainSymptomRemedyBackupInsertQuery);
											}
										}
										/* Insert symptom_remedy relation END */

							            /* Insert Symptom_pruefer relation START */
							            $hasInlinePrueferOrReference = 0;
							            $symptomPrueferResult = mysqli_query($db, "SELECT symptom_id, pruefer_id, is_new FROM temp_symptom_pruefer where symptom_id = '".$symptomData['id']."'");
										if(mysqli_num_rows($symptomPrueferResult) > 0){
											while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
												$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
							            		$db->query($mainSymptomPrueferInsertQuery);

							            		// ADD IN THE BACKUP
								            	$mainSymptomPrueferBackupInsertQuery = "INSERT INTO symptom_pruefer_backup (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
								            	$db->query($mainSymptomPrueferBackupInsertQuery);
							            		$hasInlinePrueferOrReference = 1;
											}
										}
										/* Insert Symptom_pruefer relation END */

										/* Insert symptom_reference relation START */
							            $symptomReferenceResult = mysqli_query($db, "SELECT symptom_id, reference_id, is_new FROM temp_symptom_reference where symptom_id = '".$symptomData['id']."'");
										if(mysqli_num_rows($symptomReferenceResult) > 0){
											while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
												$mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
								            	$db->query($mainSymptomReferenceInsertQuery);

								            	// ADD IN THE BACKUP
								            	$mainSymptomReferenceBackupInsertQuery = "INSERT INTO symptom_reference_backup (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
								            	$db->query($mainSymptomReferenceBackupInsertQuery);
								            	$hasInlinePrueferOrReference = 1;
											}
										}
										/* Insert symptom_reference relation END */
										/* Insert pre defined reference in symptom_reference relation START */
										$preDefinedSymptomReferenceResult = mysqli_query($db, "SELECT reference_id FROM temp_pre_defined_symptom_reference where symptom_id = '".$symptomData['id']."'");
										if(mysqli_num_rows($preDefinedSymptomReferenceResult) > 0){
											while($preDefinedSymptomReferenceData = mysqli_fetch_array($preDefinedSymptomReferenceResult)){
												$preDefinedInMainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$preDefinedSymptomReferenceData['reference_id']."', '".$date."')";
								            	$db->query($preDefinedInMainSymptomReferenceInsertQuery);

								            	// ADD IN THE BACKUP
								            	$preDefinedInMainSymptomReferenceBackupInsertQuery = "INSERT INTO symptom_reference_backup (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$preDefinedSymptomReferenceData['reference_id']."', '".$date."')";
								            	$db->query($preDefinedInMainSymptomReferenceBackupInsertQuery);
								            	$hasInlinePrueferOrReference = 1;
											}
										}
										/* Insert pre defined reference in symptom_reference relation END */

										/* Whenever we have a reference or Prüfer mentioned for a specific symptom, the main prüfer is not correct and should not be mentioned for that symptom. */
										if($hasInlinePrueferOrReference == 0) {
											foreach ($masterDataPrueferIdsArray as $masterPrufKey => $masterPrufVal) {
												$mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$masterPrufVal."', '".$date."')";
									            $db->query($mainSymptomPrueferInsertQuery);

									            // ADD IN THE BACKUP
								            	$mainSymptomPrueferBackupInsertQuery = "INSERT INTO symptom_pruefer_backup (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$masterPrufVal."', '".$date."')";
								            	$db->query($mainSymptomPrueferBackupInsertQuery);
											}
										}
									}
								}
					            /* Insert Symptoms END */

					            $updateQuelleData = "UPDATE quelle SET is_materia_medica = 1, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$masterQuelleId;
								$db->query($updateQuelleData);


					            // addPreComparisons($masterData['quelle_id'], $masterData['arznei_id']);

					            /* Update quelle table for adding language informations START */
					            /*$quelleUpdData['initially_imported_language'] = ($masterData['importing_language'] != "") ? $masterData['importing_language'] : null;
					            $quelleUpdData['is_symptoms_available_in_german'] = ($masterData['importing_language'] == "de") ? 1 : 0;
					            $quelleUpdData['is_symptoms_available_in_english'] = ($masterData['importing_language'] == "en") ? 1 : 0;
					            $quelleUpdData['translation_method_of_german'] = ($masterData['importing_language'] == "de") ? "Professional Translation" : null;
					            $quelleUpdData['translation_method_of_english'] = ($masterData['importing_language'] == "en") ? "Professional Translation" : null;
					            $quelleUpdData['quelle_id'] = ($masterData['quelle_id'] != "") ? $masterData['quelle_id'] : null;

					            $updateQuelleWithLanguageQuery="UPDATE quelle SET initially_imported_language = NULLIF('".$quelleUpdData['initially_imported_language']."', ''), is_symptoms_available_in_german = NULLIF('".$quelleUpdData['is_symptoms_available_in_german']."', ''), is_symptoms_available_in_english = NULLIF('".$quelleUpdData['is_symptoms_available_in_english']."', ''), translation_method_of_german = NULLIF('".$quelleUpdData['translation_method_of_german']."', ''), translation_method_of_english = NULLIF('".$quelleUpdData['translation_method_of_english']."', '') WHERE quelle_id = '".$quelleUpdData['quelle_id']."'";
								$db->query($updateQuelleWithLanguageQuery);*/
					            /* Update quelle table for adding language informations END */

								// If we arrive here, it means that no exception was thrown
							    // i.e. no query has failed, and we can commit the transaction
							    $db->commit();
							}catch (Exception $e) {
							    // An exception has been thrown
							    // We must rollback the transaction
							    $db->rollback();
							    $isThereAnyTransactionError = 1;
							}
						}
						/* Inserting Temp table data to Main tables END */
						
						if($isThereAnyTransactionError == 0){
							/* Delete Temp table data START */
							$isDeleted = deleteSourceImportTempData($masterId);
							if($isDeleted === false)
								$isThereAnyTransactionError = 1;
							/* Delete Temp table data END */
						}		
					}
				}
				else
				{
					/* If program reach here that means no new symptom are get instered in temp_symptom table, so all imported symptom are may be duplicate either from main table or tamp table or data not get added because of any kind of error or program logic */
					$parameterString = '?error=2';
					$query_delete="DELETE FROM temp_quelle_import_master WHERE id = '".$masterId."'";
					$db->query($query_delete);
				}
			}

			if($isThereAnyTransactionError == 1){
				header('Location: '.$baseUrl.'?error=1');
				exit();
			}		
		}else{
			echo "Please enter valid text";
		}

		header('Location: '.$baseUrl.$parameterString);
		exit();
		/* Rule 1 End */
	}
	else if(isset($_POST['settings']) && $_POST['settings'] == "setting_2")
	{
		/* Rule 2 Start */
		$CleanedText = str_replace ( '</em><em>', '', $_POST['symptomtext'] );

		$CleanedText = str_replace ( array (
			"\r",
			"\t" 
		), '', $CleanedText );
		$CleanedText = trim ( $CleanedText );
		$Lines = explode ( "\n", $CleanedText );

		if (count ( $Lines ) > 0) {
			$rownum = 1;
			$break = false;
			$Symptomnummer = 1;
			$SeiteOriginalVon = '';
			$SeiteOriginalBis = '';
			$PrueferIDs = array ();
			$PrueferID = '';
			$Pruefers = '';
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

			foreach ( $Lines as $iline => $line ) {
				
				/*
				* Replacing Colored sentences's tag to our custom tag "<clr>"
				*/
				// $line = preg_replace("/<span(.*?)style=(\"|')(.*?)letter-spacing:(.+?)>(.+?)<\/span>/is", "<ss>$5</ss>", $line);
				// $line = preg_replace("/<span(.*?)style=(\"|')(.*?)color:(.+?);(.*?)>(.+?)<\/span>/is", "<clr style='color:$4;'>$6</clr>", $line);
				$coloredTextCnt = 0; 
				do { 
					$line = preg_replace("#<span[^>]*style=(\"|')[^>]*color:(.+?);[^>]*(\"|')>(.+?)</span>#is", "<clr style=\"color:$2;\">$4</clr>", $line, -1, $coloredTextCnt ); 
				} while ( $coloredTextCnt > 0 );
				/*
				* Replacing Spaced sentences's tag to our custom tag "<ss>"
				*/
				$letterSpaceCnt = 0; 
				do { 
					$line = preg_replace("#<span[^>]*style=(\"|')[^>]*letter-spacing:[^>]*>(.+?)</span>#is", "<ss>$2</ss>", $line, -1, $letterSpaceCnt ); 
				} while ( $letterSpaceCnt > 0 );
				
				
				$line = strip_tags ( $line, '<b><i><strong><em><u><sup><ss><clr>' );
				$break = false;
				$NewSymptomNr = 0;
				$line = trim ( $line );
				/*$cleanline = trim ( str_replace ( array (
					'&nbsp;',
					', a. a. O.' 
				), array (
					' ',
					'' 
				), strip_tags ( $line ) ) );*/
				$cleanline = trim ( str_replace ( array (
					'&nbsp;' 
				), array (
					' '
				), strip_tags ( $line ) ) );
				
				// Leerzeile
				if (empty ( $cleanline )) {
					$rownum ++;
					continue;
				}
				
				if (mb_strlen ( $cleanline ) < 3) {
					$rownum ++;
					continue;
				}
				// echo $line;
				// exit();
				$FirstChar = mb_substr ( $cleanline, 0, 1 );
				$LastChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 1 );
				$LastTwoChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 2 );

				$code='';
				$param='';
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
							$PrueferIDs [] = $param;
							break;
						
						default :
							$break = true;
							break;
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

				if ($break) {
					$Beschreibung = '';
					break;
				}
				
				if ($Beschreibung) {
					/* Creating Plain Symptom text */
					$BeschreibungPlain = trim ( str_replace ( "\t", '', strip_tags ( $Beschreibung ) ) );

					/* Creating Original Symptom text start */
					$Beschreibung = preg_replace("#<b[^>]*></b>#is", "", $Beschreibung ); 
					$Beschreibung = preg_replace("#<strong[^>]*></strong>#is", "", $Beschreibung ); 
					$Beschreibung = preg_replace("#<i[^>]*></i>#is", "", $Beschreibung ); 
					$Beschreibung = preg_replace("#<em[^>]*></em>#is", "", $Beschreibung ); 
					/* Making a common tag for all bold tags - <commonbold> */
					$BeschreibungWithCommonBoldTag = str_replace ( array (
						'<strong',
						'</strong>',
						'<b',
						'</b>' 
					), array (
						"<commonbold",
						"</commonbold>",
						"<commonbold",
						"</commonbold>" 
					), $Beschreibung );

					/* Making a common tag for all Italic tags - <commonitalic> */
					$BeschreibungWithCommonItalicTag = str_replace ( array (
						'<em',
						'</em>'
					), array (
						"<commonitalic",
						"</commonitalic>"
					), $Beschreibung );

					
					$BeschreibungWithCommonItalicTag = preg_replace("#<i[^>]*>(.+?)</i>#is", '<commonitalic>$1</commonitalic>', $BeschreibungWithCommonItalicTag ); 
					
					
					if( htmlentities(mb_substr($BeschreibungWithCommonBoldTag, 0, mb_strlen('<commonbold><clr style="color: blue;">'))) === htmlentities('<commonbold><clr style="color: blue;">') AND htmlentities(mb_substr($BeschreibungWithCommonBoldTag,-mb_strlen('</clr></commonbold>'))) === htmlentities('</clr></commonbold>') )
					{
						/* Extracting Original Symptom text for BOLD BLUE */
						$BeschreibungOriginal = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $Beschreibung );
						$BeschreibungOriginal = strip_tags ( $BeschreibungOriginal, '<sup><span>' );
						$BeschreibungOriginal = '<strong>|</strong> '.trim($BeschreibungOriginal);
						// echo $BeschreibungOriginal;
					}
					else if( htmlentities(mb_substr($BeschreibungWithCommonBoldTag, 0, mb_strlen('<commonbold><clr style="color: red;">'))) === htmlentities('<commonbold><clr style="color: red;">') AND htmlentities(mb_substr($BeschreibungWithCommonBoldTag,-mb_strlen('</clr></commonbold>'))) === htmlentities('</clr></commonbold>') )
					{
						/* Extracting Original Symptom text for BOLD RED */
						$BeschreibungOriginal = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $Beschreibung );
						$BeschreibungOriginal = strip_tags ( $BeschreibungOriginal, '<sup><span>' );
						$BeschreibungOriginal = '<strong>| |</strong> '.trim($BeschreibungOriginal);
						// echo $BeschreibungOriginal;
					}
					else if( htmlentities(mb_substr($BeschreibungWithCommonItalicTag, 0, mb_strlen('<commonitalic><clr'))) === htmlentities('<commonitalic><clr') AND htmlentities(mb_substr($BeschreibungWithCommonItalicTag,-mb_strlen('</clr></commonitalic>'))) === htmlentities('</clr></commonitalic>') )
					{
						/* Extracting Original Symptom text for ITALIC BLUE & RED(Both for now) */
						$BeschreibungOriginal = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $Beschreibung );
						$BeschreibungOriginal = strip_tags ( $BeschreibungOriginal, '<sup><span>' );
						$BeschreibungOriginal = '|| '.trim($BeschreibungOriginal);
						// echo $BeschreibungOriginal;
					}
					else
					{
						/* Extracting Original Symptom text for REST of the patterns */
						$BeschreibungOriginal = str_replace ( array (
							'<ss>',
							'</ss>' 
						), array (
							"<span class=\"text-sperrschrift\">",
							"</span>" 
						), $Beschreibung );
						$BeschreibungOriginal = trim ( strip_tags ( $BeschreibungOriginal, '<sup><span>' ) );
						// echo $BeschreibungOriginal;
					}
					// exit();
					/* Creating Original Symptom text end */
					
					/* Creating Source or as it is Symtom text start */
					$Beschreibung2 = str_replace ( array (
						'<ss>',
						'</ss>' 
					), array (
						"<span class=\"text-sperrschrift\">",
						"</span>" 
					), $Beschreibung );

					$Beschreibung2 = str_replace ( array (
						'<clr',
						'</clr>' 
					), array (
						"<span",
						"</span>" 
					), $Beschreibung2 );
					if ($Beschreibung2 != $Beschreibung) {
						$Beschreibung = $Beschreibung2;
					}
					/* Creating Source or as it is Symtom text end */


					/* Getting parentheses or square brackets datas start */
					$bracketP = false;

					if ($LastChar == ')' or $LastTwoChar ==').' or $LastTwoChar =='),') {
						//echo $FirstChar." ... ".$LastChar;
						// $FirstOccurrence = mb_stripos ( $cleanline, '(' );
						// if($FirstOccurrence != 0){
							$bracketP = mb_strripos ( $cleanline, '(' );
							//echo $FirstChar." ... ".$LastChar." -> ".$bracketP;
							//exit();
							if ($bracketP > 0) {
								
								/* 
								* Cheching is there any nested parentheses string, and taking the appropriate action 
								* Eg: (95) Leeres Aufstoßen. (n. 1/4 St.) (Hornburg, a. a. O. - (n. 1/2 St.)Kummer, a. a. O.) 
								*/
								$rowParenthesesString = mb_substr ( $cleanline, $bracketP );
								$numberOfClosingParentheses=mb_substr_count($rowParenthesesString,")");
								if( $numberOfClosingParentheses > 1 ){
									while( $numberOfClosingParentheses > 1 ){ 
										$remainingStringFromBegining = mb_substr ( $cleanline, 0, mb_strlen($cleanline)-mb_strlen($rowParenthesesString) );
										$lastOccuranceOfParentheses = mb_strripos ( $remainingStringFromBegining, '(' );
										$prePartString = mb_substr ( $remainingStringFromBegining, $lastOccuranceOfParentheses );
										if( mb_substr_count($prePartString,")") > 0 ){
											$numberOfClosingParentheses = $numberOfClosingParentheses + mb_substr_count($prePartString,")");
										}
										$rowParenthesesString = $prePartString.$rowParenthesesString;
										$numberOfClosingParentheses--;
									}
									$parenthesesStringArray[] = $rowParenthesesString;
									$newString = rtrim( mb_substr ( $cleanline, 0, mb_strlen($cleanline)-mb_strlen($rowParenthesesString) ) );
								}
								else{
									/*
									* Checking if it's time data or not
									*/
									if ((mb_substr($cleanline,-mb_strlen('St)'))==='St)') OR (mb_substr($cleanline,-mb_strlen('St.)'))==='St.)') OR (mb_substr($cleanline,-mb_strlen('St. )'))==='St. )') OR (mb_substr($cleanline,-mb_strlen('St.).'))==='St.).') OR (mb_substr($cleanline,-mb_strlen('Tagen.)'))==='Tagen.)') OR (mb_substr($cleanline,-mb_strlen('Tagen.).'))==='Tagen.).') OR (mb_substr($cleanline,-mb_strlen('Tagen)'))==='Tagen)') OR (mb_substr($cleanline,-mb_strlen('Tagen).'))==='Tagen).') OR (mb_substr($cleanline,-mb_strlen('Nacht)'))==='Nacht)') OR (mb_substr($cleanline,-mb_strlen('Tag)'))==='Tag)') OR (mb_substr($cleanline,-mb_strlen('Tag.)'))==='Tag.)') OR (mb_substr($cleanline,-mb_strlen('Tag.).'))==='Tag.).') OR (mb_substr($cleanline,-mb_strlen('hour.).'))==='hour.).') OR (mb_substr($cleanline,-mb_strlen('hour).'))==='hour).') OR (mb_substr($cleanline,-mb_strlen('hour)'))==='hour)') OR (mb_substr($cleanline,-mb_strlen('hour),'))==='hour),') OR (mb_substr($cleanline,-mb_strlen('hour.),'))==='hour.),') OR (mb_substr($cleanline,-mb_strlen('hours)'))==='hours)') OR (mb_substr($cleanline,-mb_strlen('hours).'))==='hours).') OR (mb_substr($cleanline,-mb_strlen('hours.)'))==='hours.)') OR (mb_substr($cleanline,-mb_strlen('hours.).'))==='hours.).') OR (mb_substr($cleanline,-mb_strlen('hours),'))==='hours),') OR (mb_substr($cleanline,-mb_strlen('hours.),'))==='hours.),') OR (mb_substr($cleanline,-mb_strlen('Hour.).'))==='Hour.).') OR (mb_substr($cleanline,-mb_strlen('Hour).'))==='Hour).') OR (mb_substr($cleanline,-mb_strlen('Hour)'))==='Hour)') OR (mb_substr($cleanline,-mb_strlen('Hour),'))==='Hour),') OR (mb_substr($cleanline,-mb_strlen('Hour.),'))==='Hour.),') OR (mb_substr($cleanline,-mb_strlen('Hours)'))==='Hours)') OR (mb_substr($cleanline,-mb_strlen('Hours).'))==='Hours).') OR (mb_substr($cleanline,-mb_strlen('Hours.)'))==='Hours.)') OR (mb_substr($cleanline,-mb_strlen('Hours.).'))==='Hours.).') OR (mb_substr($cleanline,-mb_strlen('Hours),'))==='Hours),') OR (mb_substr($cleanline,-mb_strlen('Hours.),'))==='Hours.),') OR (mb_substr($cleanline,-mb_strlen('minute.).'))==='minute.).') OR (mb_substr($cleanline,-mb_strlen('minute).'))==='minute).') OR (mb_substr($cleanline,-mb_strlen('minute)'))==='minute)') OR (mb_substr($cleanline,-mb_strlen('minute),'))==='minute),') OR (mb_substr($cleanline,-mb_strlen('minute.),'))==='minute.),') OR (mb_substr($cleanline,-mb_strlen('minutes)'))==='minutes)') OR (mb_substr($cleanline,-mb_strlen('minutes.)'))==='minutes.)') OR (mb_substr($cleanline,-mb_strlen('minutes.).'))==='minutes.).') OR (mb_substr($cleanline,-mb_strlen('minutes),'))==='minutes),') OR (mb_substr($cleanline,-mb_strlen('minutes.),'))==='minutes.),') OR (mb_substr($cleanline,-mb_strlen('Minute.).'))==='Minute.).') OR (mb_substr($cleanline,-mb_strlen('Minute).'))==='Minute).') OR (mb_substr($cleanline,-mb_strlen('Minute)'))==='Minute)') OR (mb_substr($cleanline,-mb_strlen('Minute),'))==='Minute),') OR (mb_substr($cleanline,-mb_strlen('Minute.),'))==='Minute.),') OR (mb_substr($cleanline,-mb_strlen('Minutes)'))==='Minutes)') OR (mb_substr($cleanline,-mb_strlen('Minutes.)'))==='Minutes.)') OR (mb_substr($cleanline,-mb_strlen('Minutes.).'))==='Minutes.).') OR (mb_substr($cleanline,-mb_strlen('Minutes),'))==='Minutes),') OR (mb_substr($cleanline,-mb_strlen('Minutes.),'))==='Minutes.),')){
										$timeStringArray[] = rtrim( mb_substr ( $cleanline, $bracketP + 1, - 1 ), ')' );
									}else{
										$parenthesesStringArray[] = rtrim( mb_substr ( $cleanline, $bracketP + 1, - 1 ), ')' );	
									}
									/*
									* Here storing the remaining from the begning to last occurance of "(" for checking if there more parentheses set 
									*/
									$newString = rtrim( mb_substr ( $cleanline, 0, $bracketP ) );
								}
								 
								while ($newString != "") {
									$cleanedRemainingString = rtrim($newString);
									if (mb_substr($newString,-mb_strlen('und'))==='und') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('und')));
									else if (mb_substr($newString,-mb_strlen('and'))==='and') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('and')));
									else if (mb_substr($newString,-mb_strlen('.'))==='.') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('.')));

									$cleanedRemainingString = rtrim($cleanedRemainingString);
									$newLastChar = mb_substr ( $cleanedRemainingString, mb_strlen ( $cleanedRemainingString ) - 1 );
									
									if( $newLastChar == ')' ){
										$newBracketP = mb_strripos ( $cleanedRemainingString, '(' );
										if ($newBracketP > 0) {
											/* 
											* Cheching is there any nested parentheses string, and taking the appropriate action 
											* Eg: (95) Leeres Aufstoßen. (n. (1/4) St.) (Hornburg, a. a. O. - (n. 1/2 St.)Kummer, a. a. O.) 
											*/
											$newRowParenthesesString = mb_substr ( $cleanedRemainingString, $newBracketP );
											$newNumberOfClosingParentheses=mb_substr_count($newRowParenthesesString,")");
											if( $newNumberOfClosingParentheses > 1 ){
												while( $newNumberOfClosingParentheses > 1 ){ 
													$newRemainingStringFromBegining = mb_substr ( $cleanedRemainingString, 0, mb_strlen($cleanedRemainingString)-mb_strlen($newRowParenthesesString) );
													$newLastOccuranceOfParentheses = mb_strripos ( $newRemainingStringFromBegining, '(' );
													$newPrePartString = mb_substr ( $newRemainingStringFromBegining, $newLastOccuranceOfParentheses );
													if( mb_substr_count($newPrePartString,")") > 0 ){
														$newNumberOfClosingParentheses = $newNumberOfClosingParentheses + mb_substr_count($newPrePartString,")");
													}
													$newRowParenthesesString = $newPrePartString.$newRowParenthesesString;
													$newNumberOfClosingParentheses--;
												}
												$parenthesesStringArray[] = $newRowParenthesesString;
												$newString = rtrim( mb_substr ( $cleanedRemainingString, 0, mb_strlen($cleanedRemainingString)-mb_strlen($newRowParenthesesString) ) );
											}
											else{
												/*
												* Checking if it's time data or not
												*/
												if ((mb_substr($cleanedRemainingString,-mb_strlen('St)'))==='St)') OR (mb_substr($cleanedRemainingString,-mb_strlen('St. )'))==='St. )') OR (mb_substr($cleanedRemainingString,-mb_strlen('St.)'))==='St.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('St.).'))==='St.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tagen.)'))==='Tagen.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tagen.).'))==='Tagen.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tagen)'))==='Tagen)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tagen).'))==='Tagen).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Nacht)'))==='Nacht)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tag)'))==='Tag)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tag.)'))==='Tag.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Tag.).'))==='Tag.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('hour.).'))==='hour.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('hour).'))==='hour).') OR (mb_substr($cleanedRemainingString,-mb_strlen('hour)'))==='hour)') OR (mb_substr($cleanedRemainingString,-mb_strlen('hour),'))==='hour),') OR (mb_substr($cleanedRemainingString,-mb_strlen('hour.),'))==='hour.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('hours)'))==='hours)')  OR (mb_substr($cleanedRemainingString,-mb_strlen('hours).'))==='hours).') OR (mb_substr($cleanedRemainingString,-mb_strlen('hours.)'))==='hours.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('hours.).'))==='hours.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('hours),'))==='hours),') OR (mb_substr($cleanedRemainingString,-mb_strlen('hours.),'))==='hours.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hour.).'))==='Hour.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hour).'))==='Hour).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hour)'))==='Hour)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hour),'))==='Hour),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hour.),'))==='Hour.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours)'))==='Hours)')  OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours).'))==='Hours).')OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours.)'))==='Hours.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours.).'))==='Hours.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours),'))==='Hours),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Hours.),'))==='Hours.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('minute.).'))==='minute.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('minute).'))==='minute).') OR (mb_substr($cleanedRemainingString,-mb_strlen('minute)'))==='minute)') OR (mb_substr($cleanedRemainingString,-mb_strlen('minute),'))==='minute),') OR (mb_substr($cleanedRemainingString,-mb_strlen('minute.),'))==='minute.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes)'))==='minutes)') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes).'))==='minutes).') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes.)'))==='minutes.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes.).'))==='minutes.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes),'))==='minutes),') OR (mb_substr($cleanedRemainingString,-mb_strlen('minutes.),'))==='minutes.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minute.).'))==='Minute.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minute).'))==='Minute).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minute)'))==='Minute)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minute),'))==='Minute),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minute.),'))==='Minute.),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes)'))==='Minutes)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes).'))==='Minutes).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes.)'))==='Minutes.)') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes.).'))==='Minutes.).') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes),'))==='Minutes),') OR (mb_substr($cleanedRemainingString,-mb_strlen('Minutes.),'))==='Minutes.),')){
													$timeStringArray[] = mb_substr ( $cleanedRemainingString, $newBracketP + 1, - 1 );
												}else{
													$parenthesesStringArray[] = mb_substr ( $cleanedRemainingString, $newBracketP + 1, - 1 );
												}
												/*
												* Here storing the remaining from the begning to last occurance of "(" for checking if there more parentheses set 
												*/
												$newString = rtrim( mb_substr ( $cleanedRemainingString, 0, $newBracketP ) );
											}

										}
										else
											$newString = "";
									}
									else
										$newString = "";
								}
								$bracketedString = implode(', ', $parenthesesStringArray);
								$timeString = implode(', ', $timeStringArray);
								// echo $bracketedString." - ".$newString;
								// exit();
							}
						// }
					} else if ($LastChar == ']' or $LastTwoChar =='].') {
						// $FirstOccurrence = mb_stripos ( $cleanline, '[' );
						// if($FirstOccurrence != 0){
							$bracketP = mb_strripos ( $cleanline, '[' );
							//echo $FirstChar." ... ".$LastChar." -> ".$bracketP;
							//exit();
							if ($bracketP > 0) {
								/* 
								* Cheching is there any nested bracketed string, and taking the appropriate action 
								* Eg: (95) Leeres Aufstoßen. [n. 1/4 St.] [Hornburg, a. a. O. - [n. 1/2 St.]Kummer, a. a. O.] 
								*/
								$rowBracketedString = mb_substr ( $cleanline, $bracketP );
								$numberOfClosingBrackets=mb_substr_count($rowBracketedString,"]");
								if( $numberOfClosingBrackets > 1 ){
									while( $numberOfClosingBrackets > 1 ){ 
										$remainingStringFromBegining = mb_substr ( $cleanline, 0, mb_strlen($cleanline)-mb_strlen($rowBracketedString) );
										$lastOccuranceOfBracket = mb_strripos ( $remainingStringFromBegining, '[' );
										$prePartString = mb_substr ( $remainingStringFromBegining, $lastOccuranceOfBracket );
										if( mb_substr_count($prePartString,"]") > 0 ){
											$numberOfClosingBrackets = $numberOfClosingBrackets + mb_substr_count($prePartString,"]");
										}
										$rowBracketedString = $prePartString.$rowBracketedString;
										$numberOfClosingBrackets--;
									}
									$bracketedStringArray[] = $rowBracketedString;
									$newString = rtrim( mb_substr ( $cleanline, 0, mb_strlen($cleanline)-mb_strlen($rowBracketedString) ) );
								}
								else{
									// last characters can be ']' or '].' also so rtrim ']'
									$bracketedStringArray[] = rtrim( mb_substr ( $cleanline, $bracketP + 1, - 1 ), ']' );
									$newString = rtrim( mb_substr ( $cleanline, 0, $bracketP ) );
								}
								
								while ($newString != "") {
									$cleanedRemainingString = rtrim($newString);
									if (mb_substr($newString,-mb_strlen('und'))==='und') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('und')));
									else if (mb_substr($newString,-mb_strlen('and'))==='and') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('and')));
									else if (mb_substr($newString,-mb_strlen('.'))==='.') 
										$cleanedRemainingString = rtrim( mb_substr($newString, 0, mb_strlen($newString)-mb_strlen('.')));

									$cleanedRemainingString = rtrim($cleanedRemainingString);
									$newLastChar = mb_substr ( $cleanedRemainingString, mb_strlen ( $cleanedRemainingString ) - 1 );
									
									if( $newLastChar == ']' ){
										$newBracketP = mb_strripos ( $cleanedRemainingString, '[' );
										if ($newBracketP > 0) {
											/* 
											* Cheching is there any nested bracketed string, and taking the appropriate action 
											* Eg: (95) Leeres Aufstoßen. [n. [1/4] St.] [Hornburg, a. a. O. - [n. 1/2 St.]Kummer, a. a. O.] 
											*/
											$newRowBracketedString = mb_substr ( $cleanedRemainingString, $newBracketP );
											$newNumberOfClosingBrackets=mb_substr_count($newRowBracketedString,"]");
											if( $newNumberOfClosingBrackets > 1 ){
												while( $newNumberOfClosingBrackets > 1 ){ 
													$newRemainingStringFromBegining = mb_substr ( $cleanedRemainingString, 0, mb_strlen($cleanedRemainingString)-mb_strlen($newRowBracketedString) );
													$newLastOccuranceOfBracket = mb_strripos ( $newRemainingStringFromBegining, '[' );
													$newPrePartString = mb_substr ( $newRemainingStringFromBegining, $newLastOccuranceOfBracket );
													if( mb_substr_count($newPrePartString,"]") > 0 ){
														$newNumberOfClosingBrackets = $newNumberOfClosingBrackets + mb_substr_count($newPrePartString,"]");
													}
													$newRowBracketedString = $newPrePartString.$newRowBracketedString;
													$newNumberOfClosingBrackets--;
												}
												$bracketedStringArray[] = $newRowBracketedString;
												$newString = rtrim( mb_substr ( $cleanedRemainingString, 0, mb_strlen($cleanedRemainingString)-mb_strlen($newRowBracketedString) ) );
											}
											else{
												$bracketedStringArray[] = mb_substr ( $cleanedRemainingString, $newBracketP + 1, - 1 );
												$newString = rtrim( mb_substr ( $cleanedRemainingString, 0, $newBracketP ) );
											}

										}
										else
											$newString = "";
									}
									else
										$newString = "";
								}
								$bracketedString = implode(', ', $bracketedStringArray);
								// echo $bracketedString." - ".$newString;
								// exit();
							}
						// }
					}
					
					/* Getting parentheses or square brackets datas end */

					if ($aLiteraturquellen) {
						$EntnommenAus = join ( "\n", $aLiteraturquellen );
					}

					if ($PrueferIDs) {
						$Pruefers = join ( "\n", $PrueferIDs );
					}

					
 					$data['Symptomnummer']= ( $Symptomnummer != "" and $Symptomnummer != 0 ) ? mysqli_real_escape_string($db, $Symptomnummer) : 'null';
					$data['SeiteOriginalVon']=($SeiteOriginalVon == '') ? 0 : $SeiteOriginalVon;
					$data['SeiteOriginalBis']=($SeiteOriginalBis == '') ? 0 : $SeiteOriginalBis;
					$data['Beschreibung']=mysqli_real_escape_string($db, $Beschreibung);
					$data['BeschreibungOriginal']=mysqli_real_escape_string($db, $BeschreibungOriginal);
					$data['BeschreibungPlain']=mysqli_real_escape_string($db, $BeschreibungPlain);
					$data['bracketedString']=mysqli_real_escape_string($db, $bracketedString);
					$data['timeString']=mysqli_real_escape_string($db, $timeString);
					$data['Fussnote']=mysqli_real_escape_string($db, $Fussnote);
					$data['PrueferID']=mysqli_real_escape_string($db, $Pruefers);
					$data['EntnommenAus']=mysqli_real_escape_string($db, $EntnommenAus);
					$data['Verweiss']=mysqli_real_escape_string($db, $Verweiss);
					$data['Graduierung']=mysqli_real_escape_string($db, $Graduierung);
					$data['BereichID']=mysqli_real_escape_string($db, $BereichID);
					$data['Kommentar']=mysqli_real_escape_string($db, $Kommentar);
					$data['Unklarheiten']=mysqli_real_escape_string($db, $Unklarheiten);

					$query="INSERT INTO quelle_import_test (Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung, BeschreibungOriginal, BeschreibungPlain, bracketedString, timeString, Fussnote, PrueferID, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten) VALUES (".$data['Symptomnummer'].",'".$data['SeiteOriginalVon']."','".$data['SeiteOriginalBis']."','".$data['Beschreibung']."','".$data['BeschreibungOriginal']."','".$data['BeschreibungPlain']."','".$data['bracketedString']."','".$data['timeString']."','".$data['Fussnote']."','".$data['PrueferID']."', '".$data['EntnommenAus']."', '".$data['Verweiss']."', '".$data['Graduierung']."', '".$data['BereichID']."', '".$data['Kommentar']."', '".$data['Unklarheiten']."')";
					// echo $query;
					// exit();
		            $db->query($query);

					if ($Symptomnummer > 0)
						$Symptomnummer += 1;
					
					$Beschreibung = '';
					$Graduierung = '';
					$BereichID = '';
					$Fussnote = '';
					$Verweiss = '';
					$Unklarheiten = '';
					$Kommentar = '';
					$PrueferID='';
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
					if ($aLiteraturquellen) {
						$aLiteraturquellen = array ();
						$EntnommenAus = '';
					}
					if ($PrueferIDs) {
						$PrueferIDs = array ();
						$Pruefers = '';
					}
				}
				$rownum ++;
			}
		}else{
			echo "Please enter valid text";
		}

		header('Location: '.$baseUrl);
		exit();
		/* Rule 2 End */
	}else{
		header('Location: '.$baseUrl.'?rule_error=1');
		exit();
	}
}
/* Deleting Temp source import data */
if(isset($_POST['deleteing_master_id']) AND $_POST['deleteing_master_id'] != ""){
	/* Delete Temp table data START */
	deleteSourceImportTempData($_POST['deleteing_master_id']);
	/* Delete Temp table data END */
}
// SUB SECTIONS CODE END

include '../api/mainCall.php';
$arzneien = [];
$get_data = '';
$response = [];
$get_data = callAPI('GET', $baseApiURL.'arznei/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$arzneien = $response['content']['data'];
		break;
	case 6:
		$error = $response['message'];
		break;
	default:
		break;
}
include '../inc/header.php';
include '../inc/sidebar.php';
?>
<!-- custom -->
<link rel="stylesheet" href="assets/css/custom.css">
<!-- new comparison table style -->
<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
<style type="text/css">
.suggestion-container-scroll {
    overflow-y: scroll;
    overflow-x: hidden;
    height: 200px;
}
.h4-without-top-margin {
	margin-top: 0px;
}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
	    <h1>Source Import</h1>
	    <ol class="breadcrumb">
	    	<li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
	    	<li class="active">Source Import</li>
	    </ol>
	</section>

  	<!-- Main content -->
  	<section class="content">
    <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<?php //if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
            		<!-- <div class="box-header with-border">
		              	<h3 class="box-title">
		              		<a href="<#" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Add</a>
		              	</h3>
	            	</div> -->
			     	<?php  //} ?>
		    		<!-- /.box-header -->
		    		<div class="box-body">
			            	<?php
				                if(isset($_GET['error'])){
				                    switch ($_GET['error']) {
				                        case 1:
				                            $err_msg = "Something went wrong! Could not save the data.";
				                            break;
				                        case 2:
				                            $err_msg = "Imported source already exist in main symptoms or in incomplete source imports.";
				                            break;
				                        
				                        default:
				                            $err_msg = "";
				                            break;
				                    } 
				            ?>	
				                <div class="row text-center"><div class="col-md-12"><span class="text-danger text-center"><strong><?php echo $err_msg; ?></strong></span></div></div>
				                <div class="spacer"></div>
				            <?php 
				                } 
				            ?>
				            <?php
				                $unApprovedMasterResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_master");
				                $unApprovedMasterCount = mysqli_num_rows($unApprovedMasterResult);
				                if( $unApprovedMasterCount > 0){
				                    while($unApprovedMasterRow = mysqli_fetch_array($unApprovedMasterResult)){
				                    $unApprovedSymptomResult = mysqli_query($db,"SELECT id FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$unApprovedMasterRow['id']."'");
				                    $unApprovedSymptomCount = mysqli_num_rows($unApprovedSymptomResult);
				                    if($unApprovedSymptomCount > 0){
				            ?>
				                        <div class="row">
				                        	<div class="col-md-12">
				                        		<form id="unclearNotifyForm<?php echo $unApprovedMasterRow['id'] ?>" name="unclearNotifyForm<?php echo $unApprovedMasterRow['id'] ?>" method="POST" action="">
					                                <div class="alert alert-info">
					                                    Source imported on <strong><?php echo date('d/m/Y h:i A', strtotime($unApprovedMasterRow['ersteller_datum'])); ?></strong> have <strong><?php echo $unApprovedSymptomCount; ?></strong> unclear symptoms, <a title="Complete this source import" href="<?php echo $baseUrl; ?>?master=<?php echo $unApprovedMasterRow['id']; ?>" class="alert-link">Click Here</a> to complete the import process.
					                                    <input type="hidden" name="deleteing_master_id" id="deleteing_master_id" value="<?php echo $unApprovedMasterRow['id'] ?>">
					                                    <a class="pull-right text-danger" title="Delete this source import" href="javascript:void(0)" onclick="deleteUnclearSourceImport('<?php echo $unApprovedMasterRow['id'] ?>')">
					                                        <span class="glyphicon glyphicon-trash"></span>
					                                    </a>
					                                    <?php 
					                                        $symptomNumberMismatchResult = mysqli_query($db,"SELECT id, Symptomnummer FROM temp_quelle_import_test WHERE is_symptom_number_mismatch = 1 AND master_id = '".$unApprovedMasterRow['id']."'");
					                                        if(mysqli_num_rows($symptomNumberMismatchResult) > 0){
					                                    ?>
					                                            <div class="spacer"></div>
					                                            <div class="alert alert-success">
					                                                <b>Alert</b>
					                                                <ul>
					                                                    <?php
					                                                        while($symptomNumberMismatchRow = mysqli_fetch_array($symptomNumberMismatchResult)){
					                                                    ?>
					                                                            <li>Contradiction found in symptom no. <?php echo $symptomNumberMismatchRow['Symptomnummer']; ?> - Symptom missing.</li>
					                                                    <?php
					                                                        }
					                                                    ?>
					                                                </ul>
					                                            </div>
					                                    <?php
					                                        }
					                                    ?>
					                                </div>
					                                <div class="spacer"></div>
					                            </form>
				                        	</div>
				                        </div>
				            <?php
				                        }
				                    }
				                }
				            ?>

				            <?php
					            $showPopup=0;
					            if(isset($_GET['master']) AND $_GET['master'] != ""){

					                $checkUnapprovedResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test WHERE need_approval = 1 AND master_id = '".$_GET['master']."'");	
					                if(mysqli_num_rows($checkUnapprovedResult) > 0){
					                    /* If Un Approved data found START */
					                    $unApprovedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test WHERE need_approval = 1 AND is_skipped = 0 AND master_id = '".$_GET['master']."' ORDER BY id ASC LIMIT 1");
					                    if( mysqli_num_rows($unApprovedResult) > 0){
					                        $unApprovedRow = mysqli_fetch_assoc($unApprovedResult);

					                        $tagParameter = "";
					                        if($unApprovedRow['is_pre_defined_tags_approval'] == 1){
					                            if($unApprovedRow['pruefer_priority'] != 0)
					                                $tagParameter = "pruefer";
					                            else if($unApprovedRow['reference_priority'] != 0)
					                                $tagParameter = "reference";
					                            else if($unApprovedRow['more_than_one_tag_string_priority'] != 0)
					                                $tagParameter = "multitag";
					                        }
					                        // Checking if it is cleared already in this import
					                        if($unApprovedRow['is_rechecked'] == 0){
					                            // 0 = end bracket, 1 = middle bracket
					                            if($unApprovedRow['approval_for'] == 1)
					                                $sendingApprovalString = $unApprovedRow['middle_bracket_approval_string'];
					                            else{
					                                if($unApprovedRow['full_approval_string_when_hyphen'] != "")
					                                    $sendingApprovalString = $unApprovedRow['full_approval_string_when_hyphen'];
					                                else
					                                    $sendingApprovalString = $unApprovedRow['approval_string'];
					                            }
					                            $checkRtn = isClearedInThisImport($unApprovedRow['id'], $sendingApprovalString, $_GET['master'], $unApprovedRow['approval_for'], $unApprovedRow['is_pre_defined_tags_approval'], $tagParameter);
					                            if($checkRtn === true)
					                            {
					                                echo "<script type='text/javascript'>location.reload();</script>"; 
					                                exit();
					                            }
					                        }

					                        $showPopup=1;
					                        $isNoButtonAvailable = 1;
					                        
					                        $lowestPriorityValue = 1;
					                        $noOfQuestion = 0;
					                        if($unApprovedRow['remedy_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['part_of_symptom_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['pruefer_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['reference_with_no_author_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['remedy_with_symptom_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['aao_hyphen_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['hyphen_pruefer_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['hyphen_reference_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['more_than_one_tag_string_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;
					                        if($unApprovedRow['reference_priority'] == 0)
					                            $lowestPriorityValue++;
					                        else
					                            $noOfQuestion++;

					                        // When direct order value is set
					                        if($unApprovedRow['direct_order_priority'] != 0)
					                            $lowestPriorityValue = $unApprovedRow['direct_order_priority'];

					                        // When  symptom edit value is set
					                        if($unApprovedRow['symptom_edit_priority'] != 0)
					                            $lowestPriorityValue = $unApprovedRow['symptom_edit_priority'];

					                        if($unApprovedRow['part_of_symptom_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-part-of-symptom.php";
					                        }else if($unApprovedRow['remedy_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-remedy.php";
					                        }else if($unApprovedRow['pruefer_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-pruefer.php";
					                        }else if($unApprovedRow['reference_with_no_author_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-reference.php";
					                        }else if($unApprovedRow['remedy_with_symptom_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-remedy-with-symptom.php";
					                        }else if($unApprovedRow['aao_hyphen_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-aao-hyphen-string.php";
					                        }else if($unApprovedRow['hyphen_pruefer_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-hyphen-pruefer-string.php";
					                        }else if($unApprovedRow['hyphen_reference_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-hyphen-reference-string.php";
					                        }else if($unApprovedRow['more_than_one_tag_string_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-multi-tag-string.php";
					                        }else if($unApprovedRow['reference_priority'] == $lowestPriorityValue){
					                            $formAction = "approve-as-reference.php";
					                        }else if($unApprovedRow['direct_order_priority'] == $lowestPriorityValue){
					                            $formAction = "direct_order.php";
					                        }else if($unApprovedRow['symptom_edit_priority'] == $lowestPriorityValue){
					                            $formAction = "edit-symptom-text.php";
					                        }
					        ?>
					                        <div id="decisionMakingModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
					                            <div class="modal-dialog modal-lg" role="document">
					                                <form name="decisionMakingForm" id="decisionMakingForm" action="<?php echo $formAction; ?>" method="POST" accept-charset="UTF-8">
					                                    <div class="modal-content">
					                                        <div class="modal-header text-center">
					                                            <?php 
					                                                if(isset($_GET['popup_error']) AND $_GET['popup_error'] != ""){
					                                                    switch ($_GET['popup_error']) {
					                                                        case '1':
					                                                            $popup_error_msg = "No Direct Order found Or There is something wrong in provided Direct Order.";
					                                                            break;
					                                                        case '2':
					                                                            $popup_error_msg = "It may go wrong or program may messed up this symptom Data. Please correct this symptom manually and import again.";
					                                                            break;
					                                                        case '3':
					                                                            $popup_error_msg = "Source already exist in main symptoms or in incomplete source imports.";
					                                                            break;
					                                                        case '4':
					                                                            $popup_error_msg = "Source text contain very few characters, Could not update!";
					                                                            break;
					                                                        case '5':
					                                                            $popup_error_msg = "Something went wrong Could not save the data. Please retry!";
					                                                            break;
					                                                        case '6':
					                                                            $popup_error_msg = "Please take suitable action on the below provided form";
					                                                            break;
					                                                        
					                                                        default:
					                                                            $popup_error_msg = "";
					                                                            break;
					                                                    } 
					                                            ?>	
					                                                <span class="text-danger"><strong><?php echo $popup_error_msg; ?></strong></span>
					                                            <?php } ?>
					                                            <button type="button" class="close" title="Close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					                                            <h4 class="modal-title" id="myModalLabel">Decision Making</h4>
					                                        </div>
					                                        <div class="modal-body text-center">
					                                            <?php if($unApprovedRow['symptom_edit_priority'] != $lowestPriorityValue){ ?>
					                                                <a href="javascript:void(0)">Question left: <span class="badge"><?php if($unApprovedRow['direct_order_priority'] == $lowestPriorityValue) { echo 0; }else{ echo ($noOfQuestion == 0) ? 0 : $noOfQuestion-1; } ?></span></a>
					                                            <?php } ?>
					                                            <?php
					                                                if($unApprovedRow['part_of_symptom_priority'] == $lowestPriorityValue){
					                                                    /* Part Of Symptom checking START */
					                                                    if($unApprovedRow['approval_for'] == 1)
					                                                        $workingApprovalString = $unApprovedRow['middle_bracket_approval_string'];
					                                                    else
					                                                        $workingApprovalString = $unApprovedRow['approval_string'];
					                                            ?>
					                                                    <h3>Is this part of the symptom?</h3>
					                                                    <h2 class="text-danger"><?php echo $workingApprovalString ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    /* Part Of Symptom checking END */
					                                                }
					                                                else if($unApprovedRow['remedy_priority'] == $lowestPriorityValue){
					                                                    /* Remedy checking START */
					                                                    if($unApprovedRow['approval_for'] == 1)
					                                                        $workingApprovalString = $unApprovedRow['middle_bracket_approval_string'];
					                                                    else
					                                                        $workingApprovalString = $unApprovedRow['approval_string'];
					                                                    // Different logics for dot(.) and Comma(,) or Semicolon(;) START
					                                                    $isCommaOrSemicolonExist = 0;
					                                                    $separator = "";
					                                                    if (mb_strpos($workingApprovalString, ',') !== false) {
					                                                        $isCommaOrSemicolonExist = 1;
					                                                        $separator = ",";
					                                                    }
					                                                    else if (mb_strpos($workingApprovalString, ';') !== false) {
					                                                        $isCommaOrSemicolonExist = 1;
					                                                        $separator = ";";
					                                                    }
					                                                    $approvableStringArr = explode(" ", $workingApprovalString);
					                                                    $showSuggestion = 1;
					                                                    $remedyApprovalString = $workingApprovalString;
					                                                    $remedyQuestion = "Is this a Remedy?";
					                                                    if($isCommaOrSemicolonExist == 1 AND $separator != ""){
					                                                        $explodedValue = explode($separator, $workingApprovalString);
					                                                        $newTempRemedyArray = array(); 
					                                                        foreach ($explodedValue as $expKey => $expVal) {
					                                                            if($expVal == "")
					                                                                continue;
					                                                            $newTempRemedyArray[] = $expVal;
					                                                        }
					                                                        $expectedRemedyCount = count($newTempRemedyArray);
					                                                        
					                                                        if( $expectedRemedyCount > 1){
					                                                            // $showSuggestion = 0;
					                                                            $remedyApprovalString = rtrim(implode(",", $explodedValue), ",");
					                                                            $remedyQuestion = "Are these ".$expectedRemedyCount." Remedies?";
					                                                        }
					                                                        else{
					                                                            $remedyApprovalString = $workingApprovalString;
					                                                            $remedyQuestion = "Is this a Remedy?";
					                                                        }

					                                                    }else if($isCommaOrSemicolonExist == 0 AND count($approvableStringArr) > 1){

					                                                        $explodedValue = explode(".", $workingApprovalString);
					                                                        $newTempRemedyArray = array(); 
					                                                        foreach ($explodedValue as $expKey => $expVal) {
					                                                            if($expVal == "")
					                                                                continue;
					                                                            $newTempRemedyArray[] = $expVal;
					                                                        }
					                                                        $expectedRemedyCount = count($newTempRemedyArray);
					                                                        
					                                                        if( $expectedRemedyCount > 1){
					                                                            // $showSuggestion = 0;
					                                                            $remedyApprovalString = rtrim(implode(".,", $explodedValue), ",");
					                                                            $remedyQuestion = "Are these ".$expectedRemedyCount." Remedies?";
					                                                        }
					                                                        else{
					                                                            $remedyApprovalString = $workingApprovalString;
					                                                            $remedyQuestion = "Is this a Remedy?";
					                                                        }

					                                                    }
					                                                    // Different logics for dot(.) and Comma(,) or Semicolon(;) END
					                                                    
					                                            ?>
					                                                    <h3><?php echo $remedyQuestion; ?></h3>
					                                                    <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                        <small class="text-danger">(Found in pre define tag <strong>@A</strong>, please check your document in below refered position for better understanding)</small>
					                                                    <?php }?>
					                                                    <h2 class="text-danger"><?php echo $remedyApprovalString; ?></h2>
					                                                    <div class="spacer"></div>
					                                                    <h4>Take a action from the below provided option set and click on the "Yes" button below to save</h4>
					                                                    <!-- Questining tab Options Start -->
					                                                    <div class="fancy-collapse-panel text-left">
																	        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
																	            <div class="panel panel-default">
																	                <div class="panel-heading" role="tab" id="remedyTabHeadingOne">
																	                    <h4 class="panel-title">
																	                        <a class="collapsed" data-toggle="collapse" href="#remedyTabCollapseOne" aria-expanded="false" aria-controls="remedyTabCollapseOne">SUGGESTIONS & ACTION</a>
																	                    </h4>
																	                </div>
																	                <div id="remedyTabCollapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="remedyTabHeadingOne">
																	                	<div id="suggestion_and_action_container" class="panel-body">
																	                		<div class="row">
																	                			<div class="col-sm-12">
																	                				<h4 class="h4-without-top-margin">Suggestions</h4>
																	                				<div class="suggestion-container-scroll">
																	                					<?php
																		                					$remedySuggestionResult = mysqli_query($db, "SELECT * FROM temp_remedy WHERE symptom_id ='".$unApprovedRow['id']."' AND is_new = 0");
														                                                    $remedySuggestionCount = mysqli_num_rows($remedySuggestionResult);
														                                                    if( $remedySuggestionCount > 0 AND $showSuggestion == 1){
														                                                    	while($remedySuggestionRow = mysqli_fetch_array($remedySuggestionResult)){
														                                                    		?>
														                                                    		<div class="radio">
											                                                                            <label><input type="checkbox" class="suggested-checkbox" name="suggested_remedy[]" value="<?php echo $remedySuggestionRow['main_remedy_id']; ?>"> <?php echo $remedySuggestionRow['name']; ?></label>
											                                                                        </div>
														                                                    		<?php
														                                                    	}
														                                                    } else {
												                                                    	?>
												                                                    			<div>No suggestion found</div>
												                                                    	<?php
														                                                    }
																		                				?>
																	                				</div>
																	                			</div>
																	                		</div>
																	                	</div>
																	                </div>
																	            </div>
																	            <div class="panel panel-default">
																	                <div class="panel-heading" role="tab" id="remedyTabExistingRemedySet">
																	                    <h4 class="panel-title">
																	                        <a data-toggle="collapse" href="#remedyTabCollapseExistingRemedySet" aria-expanded="true" aria-controls="remedyTabCollapseExistingRemedySet">SELECT FROM EXISTING REMEDIES</a>
																	                    </h4>
																	                </div>
																	                <div id="remedyTabCollapseExistingRemedySet" class="panel-collapse collapse" role="tabpanel" aria-labelledby="remedyTabExistingRemedySet">
																	                	<div id="comparison_container" class="panel-body">
																	                		<div class="row">
																	                			<div class="col-sm-12">
																	                				<label>Select remedy</label>
																	                				<select class="select2 form-control" name="import_question_popup_remedy_ids[]" id="import_question_popup_remedy_ids" multiple="multiple" data-placeholder="Search Remedy...">
																	                					<?php 
																							   				echo getRemedySelectBoxOptions();
																								   		?>
																	                				</select>
																	                			</div>
																	                		</div>
																	                	</div>
																	                </div>
																	            </div>
																	           	<div class="panel panel-default">
																	                <div class="panel-heading" role="tab" id="remedyTabHeadingTwo">
																	                    <h4 class="panel-title">
																	                        <a data-toggle="collapse" href="#remedyTabCollapseTwo" aria-expanded="true" aria-controls="remedyTabCollapseTwo">ADD IT AS NEW REMEDY</a>
																	                    </h4>
																	                </div>
																	                <div id="remedyTabCollapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="remedyTabHeadingTwo">
																	                	<div id="comparison_container" class="panel-body">
																	                		<div id="addRemedyCnr" class="row">
																	                			<div class="col-sm-6">
																	                				<label>Remedy title</label>
																	                				<input type="text" name="remedy_title[]" placeholder="Titel" class="form-control" autocomplete="off">
																	                			</div>
																	                			<div class="col-sm-6">
																	                				<label>Abbreviations (separate with "|")</label>
																	                				<input type="text" name="remedy_abbreviations[]" placeholder="Abbreviations" class="form-control" autocomplete="off">
																	                			</div>
																	                		</div>
																	                		<div class="spacer"></div>
																	                		<div class="row">
																	                			<div class="col-sm-6 col-sm-offset-3 text-center">
																	                				<input type="button" title="Add more fields" class="btn btn-info btn-order" id="addMoreRemedySet" value="Add more">
																	                			</div>
																	                		</div>
																	                	</div>
																	                </div>
																	            </div>
        																	</div>
																		</div>
																		<!-- Questining tab Options End -->
					                                                    <!-- <div class="spacer"></div>
					                                                    <h5><span>OR</span></h5>
					                                                    <div class="row">
					                                                        <div class="col-sm-6 col-sm-offset-3 text-left">
					                                                            <label>Enter remedies separated by a comma</label>
					                                                            <input type="text" name="remedies_comma_separated" id="remedies_comma_separated" class="form-control" placeholder="Comma separated remedies" autocomplete="off">
					                                                        </div>
					                                                    </div>
					                                                    <div class="spacer"></div>
					                                                    <div class="row">
					                                                        <div class="col-sm-6 col-sm-offset-3 text-center">
					                                                            <input type="submit" title="Save the comma separated remedies" class="btn btn-info btn-order" name="comma_separated_remedies_ok" id="comma_separated_remedies_ok" value="Ok">
					                                                        </div>
					                                                    </div>
					                                                    <div class="spacer"></div> -->
					                                            <?php
					                                                    /* Remedy checking END */
					                                                }else if($unApprovedRow['pruefer_priority'] == $lowestPriorityValue){
					                                                    /* Pruefer checking START */
					                                                    if(isset($_GET['new-pruefer']) AND $_GET['new-pruefer'] == 1)
					                                                    {
					                                                        /* Show add new Pruefer popup START */
					                                            ?>
					                                                        <h3>Add Pruefer (<span class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></span>)</h3>
					                                                        <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                            <small class="text-danger">(Found in pre define tag <strong>@P</strong>, please check your document in below refered position for better understanding)</small>
					                                                        <?php }?>
					                                                        <div class="spacer"></div>
					                                                        <div class="row">
					                                                            <div class="col-sm-2">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="titel">Titel</label>
					                                                                    <select class="form-control" name="titel" id="titel" autofocus="">
					                                                                        <option value="">Titel wählen</option>
					                                                                        <option value="Prof.">Prof.</option>
					                                                                        <option value="Dr.">Dr.</option>
					                                                                        <option value="Mr.">Mr.</option>
					                                                                        <option value="Prof. Dr.">Prof. Dr.</option>
					                                                                        <option value="Dr. Dr.">Dr. Dr.</option>
					                                                                    </select>
					                                                                    <span class="error-text"></span>
					                                                                </div> 
					                                                            </div>
					                                                            <div class="col-sm-5">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="vorname">Vorname</label>
					                                                                    <input type="text" class="form-control" name="vorname" value="" id="vorname" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-5">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="nachname">Nachname*</label>
					                                                                    <input type="text" class="form-control" id="nachname" name="nachname" value="" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>			
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="suchname">Suchname</label>
					                                                                    <input type="text" class="form-control" name="suchname" value="<?php echo (isset($unApprovedRow['approval_string'])) ? $unApprovedRow['approval_string'] : ''; ?>" id="suchname" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="kuerzel">Kürzel (mehrere mit "|" trennen!)</label>
					                                                                    <input type="text" class="form-control" name="kuerzel" value="<?php echo (isset($unApprovedRow['approval_string'])) ? $unApprovedRow['approval_string'] : ''; ?>" id="kuerzel" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="geburtsjahr">Geburtsjahr/ datum</label>
					                                                                    <input type="text" class="form-control hasDatepicker valid" name="geburtsdatum" value="" id="geburtsjahr" data-mask="99/99/9999" aria-invalid="false" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="todesjahr">Todesjahr/ datum</label>
					                                                                    <input type="text" class="form-control hasDatepicker valid" name="sterbedatum" value="" id="todesjahr" data-mask="99/99/9999" aria-invalid="false" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-12">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="kommentar">Kommentar</label>
					                                                                    <textarea id="kommentar" name="kommentar" value="" class="form-control texteditor" aria-hidden="true"></textarea>
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        /* Show add new Pruefer popup END */
					                                                    }else{
					                                                        /* Asking is it Pruefer popup START */
					                                            ?>
					                                                        <h3>Is this a Pruefer?</h3>
					                                                        <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                            <small class="text-danger">(Found in pre define tag <strong>@P</strong>, please check your document in below refered position for better understanding)</small>
					                                                        <?php }?>
					                                                        <h2 class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></h2>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        $prueferSuggestionResult = mysqli_query($db, "SELECT pruefer.pruefer_id, pruefer.kuerzel, pruefer.suchname FROM pruefer LEFT JOIN temp_symptom_pruefer ON pruefer.pruefer_id = temp_symptom_pruefer.pruefer_id  WHERE temp_symptom_pruefer.symptom_id ='".$unApprovedRow['id']."' AND temp_symptom_pruefer.is_new = 0");
					                                                        $prueferSuggestionCount = mysqli_num_rows($prueferSuggestionResult);
					                                                        if( $prueferSuggestionCount > 0){
					                                            ?>
					                                                        <div class="table-responsive">          
					                                                            <table class="table table-bordered">
					                                                                <thead>
					                                                                    <tr>
					                                                                        <th colspan="3" class="text-center">You can select from found similar pruefer(s) listed below and press Yes (If it's already there!)</th>
					                                                                    </tr>
					                                                                </thead>
					                                                                <tbody>
					                                                                    <tr>
					                                                                        <td colspan="3">	
					                                                                            <button title="Reset the radio button(s)" class="btn btn-default" type="button" onclick="resetRadio('suggested_pruefer')">Reset</button>    
					                                                                        </td>
					                                                                    </tr>
					                                                                    <tr>
					                                                                        <td><strong>Action</strong></td>
					                                                                        <td><strong>Suchname</strong></td>
					                                                                        <td><strong>Kuerzel (Seperate with "|")</strong></td>
					                                                                    </tr>  			
					                                            <?php
					                                                                while($prueferSuggestionRow = mysqli_fetch_array($prueferSuggestionResult))
					                                                                {
					                                            ?>
					                                                                    <tr>
					                                                                        <td>
					                                                                            <div class="radio">
					                                                                                <label><input type="radio" class="suggested-radio" name="suggested_pruefer" value="<?php echo $prueferSuggestionRow['pruefer_id']; ?>"></label>
					                                                                            </div>
					                                                                        </td>
					                                                                        <td><?php echo $prueferSuggestionRow['suchname']; ?></td>
					                                                                        <td>
					                                                                            <input type="text" class="form-control" name="kuerzel_<?php echo $prueferSuggestionRow['pruefer_id']; ?>" id="kuerzel_<?php echo $prueferSuggestionRow['pruefer_id']; ?>" value="<?php echo $prueferSuggestionRow['kuerzel']; ?>" autocomplete="off" placeholder="Kuerzel (Seperate with '|')">
					                                                                        </td>
					                                                                    </tr>			
					                                            <?php
					                                                                }
					                                            ?>
					                                                                </tbody>
					                                                            </table>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        }
					                                                        /* Asking is it Pruefer popup END */
					                                                    } 
					                                            ?>		
					                                            <?php
					                                                    /* Pruefer checking END */
					                                                }else if($unApprovedRow['reference_with_no_author_priority'] == $lowestPriorityValue OR $unApprovedRow['reference_priority'] == $lowestPriorityValue){
					                                                    /* Reference OR Reference With No Author checking START */
					                                                    if($unApprovedRow['reference_with_no_author_priority'] == $lowestPriorityValue)
					                                                        $questionText = "Is this a reference with no author?";
					                                                    else
					                                                        $questionText = "Is this a new reference?";
					                                            ?>
					                                                    <h3><?php echo $questionText; ?></h3>
					                                                    <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                        <small class="text-danger">(Found in pre define tag <strong>@L</strong>, please check your document in below refered position for better understanding)</small>
					                                                    <?php }?>
					                                                    <h2 class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    $referenceSuggestionResult = mysqli_query($db, "SELECT reference.reference_id, reference.full_reference, reference.autor, reference.reference FROM reference LEFT JOIN temp_symptom_reference ON reference.reference_id = temp_symptom_reference.reference_id  WHERE temp_symptom_reference.symptom_id ='".$unApprovedRow['id']."' AND temp_symptom_reference.is_new = 0");
					                                                    $referenceSuggestionCount = mysqli_num_rows($referenceSuggestionResult);
					                                                    if( $referenceSuggestionCount > 0){
					                                            ?>
					                                                        <div class="table-responsive">          
					                                                            <table class="table table-bordered">
					                                                                <thead>
					                                                                    <tr>
					                                                                        <!-- <th colspan="2" class="text-center">You can select form below listed similar reference(s) and press Yes</th> -->
					                                                                        <th colspan="2" class="text-center">You can select from found similar reference(s) listed below and press Yes (If it's already there!)</th>
					                                                                        <!-- <th colspan="2" class="text-center">Similar reference(s) already there in the system are listed below:</th> -->
					                                                                    </tr>
					                                                                </thead>
					                                                                <tbody>
					                                                                    <tr>
					                                                                        <td colspan="2">	
					                                                                            <button title="Reset the checkbox(es)" class="btn btn-default" type="button" onclick="resetRadio('suggested_reference')">Reset</button>    
					                                                                        </td>
					                                                                    </tr>  			
					                                            <?php
					                                                                while($referenceSuggestionRow = mysqli_fetch_array($referenceSuggestionResult)){
					                                            ?>
					                                                                    <tr>
					                                                                        <td>
					                                                                            <div class="radio">
					                                                                                <label><input title="Check to select this item" type="checkbox" class="suggested-checkbox" name="suggested_reference[]" value="<?php echo $referenceSuggestionRow['reference_id']; ?>"></label>
					                                                                            </div>
					                                                                        </td>
					                                                                        <td><?php echo $referenceSuggestionRow['full_reference']; ?></td>
					                                                                    </tr>			
					                                            <?php
					                                                                }
					                                            ?>
					                                                                </tbody>
					                                                            </table>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                    }
					                                                        
					                                            ?>	
					                                            <?php
					                                                    /* Reference OR Reference With No Author checking END */
					                                                }else if($unApprovedRow['remedy_with_symptom_priority'] == $lowestPriorityValue){
					                                                    /* Remedy With Symptom checking START */
					                                                    if($unApprovedRow['approval_for'] == 1)
					                                                        $workingApprovalString = $unApprovedRow['middle_bracket_approval_string'];
					                                                    else
					                                                        $workingApprovalString = $unApprovedRow['approval_string'];
					                                            ?>
					                                                    <h3>Is this a different remedy with symptom text?</h3>
					                                                    <h2 class="text-danger"><?php echo $workingApprovalString ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    /* Remedy With Symptom checking END */
					                                                }else if($unApprovedRow['more_than_one_tag_string_priority'] == $lowestPriorityValue){
					                                                    /* Multi tag checking START */
					                                            ?>
					                                                    <h3>Unknown data found in pre defined tags</h3>
					                                                    <small class="text-danger">(Please check your document in below refered position for better understanding)</small>
					                                                    <h2 class="text-danger"><?php echo str_replace("{#^#}", ", ", $unApprovedRow['approval_string']); ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    /* Multi tag checking END */
					                                                }else if($unApprovedRow['aao_hyphen_priority'] == $lowestPriorityValue){
					                                                    /* Multiple Unknown data in a. a. O., Hyphen START */
					                                            ?>
					                                                    <h3>Multiple unknown data found with a. a. O. or Hyphen( - )</h3>
					                                                    <small class="text-danger">(Please check your document in below refered position for better understanding)</small>
					                                                    <h2 class="text-danger"><?php echo str_replace("{#^#}", ", ", $unApprovedRow['approval_string']); ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    /* Multiple Unknown data in a. a. O., Hyphen END */
					                                                }else if($unApprovedRow['hyphen_pruefer_priority'] == $lowestPriorityValue){
					                                                    /* Unknown data in a. a. O., Hyphen ask pruefer possiblities START */
					                                                    if(isset($_GET['new-pruefer']) AND $_GET['new-pruefer'] == 1)
					                                                    {
					                                                        /* Show add new Pruefer popup START */
					                                                        $prePopulatePrueferString = "";
					                                                        if(isset($unApprovedRow['approval_string']) AND $unApprovedRow['approval_string'] != ""){

					                                                            $prePopulatePrueferString = str_replace("a. a. O.", "", $unApprovedRow['approval_string']);
					                                                            $prePopulatePrueferString = str_replace("a.a.O.", "", $prePopulatePrueferString);
					                                                            $prePopulatePrueferString = str_replace("a.a.o.", "", $prePopulatePrueferString);
					                                                            $prePopulatePrueferString = str_replace("a. a. o.", "", $prePopulatePrueferString);
					                                                            $prePopulatePrueferString = trim($prePopulatePrueferString);
					                                                            $prePopulatePrueferString = (mb_substr ( $prePopulatePrueferString, mb_strlen ( $prePopulatePrueferString ) - 1, 1 ) == ',') ? mb_substr ( $prePopulatePrueferString, 0, mb_strlen ( $prePopulatePrueferString ) - 1 ) : $prePopulatePrueferString;
					                                                            $prePopulatePrueferString = trim($prePopulatePrueferString);
					                                                        }
					                                                        
					                                            ?>
					                                                        <h3>Add Pruefer (<span class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></span>)</h3>
					                                                        <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                            <small class="text-danger">(Found in pre define tag <strong>@P</strong>, please check your document in below refered position for better understanding)</small>
					                                                        <?php }?>
					                                                        <div class="spacer"></div>
					                                                        <div class="row">
					                                                            <div class="col-sm-2">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="titel">Titel</label>
					                                                                    <select class="form-control" name="titel" id="titel" autofocus="">
					                                                                        <option value="">Titel wählen</option>
					                                                                        <option value="Prof.">Prof.</option>
					                                                                        <option value="Dr.">Dr.</option>
					                                                                        <option value="Mr.">Mr.</option>
					                                                                        <option value="Prof. Dr.">Prof. Dr.</option>
					                                                                        <option value="Dr. Dr.">Dr. Dr.</option>
					                                                                    </select>
					                                                                    <span class="error-text"></span>
					                                                                </div> 
					                                                            </div>
					                                                            <div class="col-sm-5">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="vorname">Vorname</label>
					                                                                    <input type="text" class="form-control" name="vorname" value="" id="vorname" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-5">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="nachname">Nachname*</label>
					                                                                    <input type="text" class="form-control" id="nachname" name="nachname" value="" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>			
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="suchname">Suchname</label>
					                                                                    <input type="text" class="form-control" name="suchname" value="<?php echo (isset($prePopulatePrueferString)) ? $prePopulatePrueferString : ''; ?>" id="suchname" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="kuerzel">Kürzel (mehrere mit "|" trennen!)</label>
					                                                                    <input type="text" class="form-control" name="kuerzel" value="<?php echo (isset($prePopulatePrueferString)) ? $prePopulatePrueferString : ''; ?>" id="kuerzel" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="geburtsjahr">Geburtsjahr/ datum</label>
					                                                                    <input type="text" class="form-control hasDatepicker valid" name="geburtsdatum" value="" id="geburtsjahr" data-mask="99/99/9999" aria-invalid="false" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                            <div class="col-sm-6">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="todesjahr">Todesjahr/ datum</label>
					                                                                    <input type="text" class="form-control hasDatepicker valid" name="sterbedatum" value="" id="todesjahr" data-mask="99/99/9999" aria-invalid="false" autocomplete="off">
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="row">
					                                                            <div class="col-sm-12">
					                                                                <div class="form-group new-pruefer">
					                                                                    <label for="kommentar">Kommentar</label>
					                                                                    <textarea id="kommentar" name="kommentar" value="" class="form-control texteditor" aria-hidden="true"></textarea>
					                                                                    <span class="error-text"></span>
					                                                                </div>
					                                                            </div>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        /* Show add new Pruefer popup END */
					                                                    }else{
					                                                        /* Asking is it Pruefer popup START */
					                                            ?>
					                                                        <h3>Is this a Pruefer?</h3>
					                                                        <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                            <small class="text-danger">(Found in pre define tag <strong>@P</strong>, please check your document in below refered position for better understanding)</small>
					                                                        <?php }?>
					                                                        <h2 class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></h2>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        $prueferSuggestionResult = mysqli_query($db, "SELECT pruefer.pruefer_id, pruefer.kuerzel, pruefer.suchname FROM pruefer LEFT JOIN temp_symptom_pruefer ON pruefer.pruefer_id = temp_symptom_pruefer.pruefer_id  WHERE temp_symptom_pruefer.symptom_id ='".$unApprovedRow['id']."' AND temp_symptom_pruefer.is_new = 0 AND temp_symptom_pruefer.is_one_unknown_element_in_hyphen = 1");
					                                                        $prueferSuggestionCount = mysqli_num_rows($prueferSuggestionResult);
					                                                        if( $prueferSuggestionCount > 0){
					                                            ?>
					                                                        <div class="table-responsive">          
					                                                            <table class="table table-bordered">
					                                                                <thead>
					                                                                    <tr>
					                                                                        <th colspan="3" class="text-center">You can select from found similar pruefer(s) listed below and press Yes (If it's already there!)</th>
					                                                                    </tr>
					                                                                </thead>
					                                                                <tbody>
					                                                                    <tr>
					                                                                        <td colspan="3">	
					                                                                            <button title="Reset the radio button(s)" class="btn btn-default" type="button" onclick="resetRadio('suggested_pruefer')">Reset</button>    
					                                                                        </td>
					                                                                    </tr>
					                                                                    <tr>
					                                                                        <td><strong>Action</strong></td>
					                                                                        <td><strong>Suchname</strong></td>
					                                                                        <td><strong>Kuerzel (Seperate with "|")</strong></td>
					                                                                    </tr>  			
					                                            <?php
					                                                                while($prueferSuggestionRow = mysqli_fetch_array($prueferSuggestionResult))
					                                                                {
					                                            ?>
					                                                                    <tr>
					                                                                        <td>
					                                                                            <div class="radio">
					                                                                                <label><input type="radio" class="suggested-radio" name="suggested_pruefer" value="<?php echo $prueferSuggestionRow['pruefer_id']; ?>"></label>
					                                                                            </div>
					                                                                        </td>
					                                                                        <td><?php echo $prueferSuggestionRow['suchname']; ?></td>
					                                                                        <td>
					                                                                            <input type="text" class="form-control" name="kuerzel_<?php echo $prueferSuggestionRow['pruefer_id']; ?>" id="kuerzel_<?php echo $prueferSuggestionRow['pruefer_id']; ?>" value="<?php echo $prueferSuggestionRow['kuerzel']; ?>" autocomplete="off" placeholder="Kuerzel (Seperate with '|')">
					                                                                        </td>
					                                                                    </tr>			
					                                            <?php
					                                                                }
					                                            ?>
					                                                                </tbody>
					                                                            </table>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                        }
					                                                        /* Asking is it Pruefer popup END */
					                                                    } 
					                                            ?>
					                                            <?php
					                                                    /* Unknown data in a. a. O., Hyphen ask pruefer possiblities END */
					                                                }else if($unApprovedRow['hyphen_reference_priority'] == $lowestPriorityValue){
					                                                    /* Unknown data in a. a. O., Hyphen ask reference possiblities START */
					                                            ?>
					                                                    <h3>Is this a new reference?</h3>
					                                                    <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                        <small class="text-danger">(Found in pre define tag <strong>@L</strong>, please check your document in below refered position for better understanding)</small>
					                                                    <?php }?>
					                                                    <h2 class="text-danger"><?php echo $unApprovedRow['approval_string'] ?></h2>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    $referenceSuggestionResult = mysqli_query($db, "SELECT reference.reference_id, reference.full_reference, reference.autor, reference.reference FROM reference LEFT JOIN temp_symptom_reference ON reference.reference_id = temp_symptom_reference.reference_id  WHERE temp_symptom_reference.symptom_id ='".$unApprovedRow['id']."' AND temp_symptom_reference.is_new = 0 AND temp_symptom_reference.is_one_unknown_element_in_hyphen = 1");
					                                                    $referenceSuggestionCount = mysqli_num_rows($referenceSuggestionResult);
					                                                    if( $referenceSuggestionCount > 0){
					                                            ?>
					                                                        <div class="table-responsive">          
					                                                            <table class="table table-bordered">
					                                                                <thead>
					                                                                    <tr>
					                                                                        <th colspan="2" class="text-center">You can select from found similar reference(s) listed below and press Yes (If it's already there!)</th>
					                                                                    </tr>
					                                                                </thead>
					                                                                <tbody>
					                                                                    <tr>
					                                                                        <td colspan="2">	
					                                                                            <button title="Reset the checkbox(es)" class="btn btn-default" type="button" onclick="resetRadio('suggested_reference')">Reset</button>    
					                                                                        </td>
					                                                                    </tr>  			
					                                            <?php
					                                                                while($referenceSuggestionRow = mysqli_fetch_array($referenceSuggestionResult)){
					                                            ?>
					                                                                    <tr>
					                                                                        <td>
					                                                                            <div class="radio">
					                                                                                <label><input title="Check to select this item" type="checkbox" class="suggested-checkbox" name="suggested_reference[]" value="<?php echo $referenceSuggestionRow['reference_id']; ?>"></label>
					                                                                            </div>
					                                                                        </td>
					                                                                        <td><?php echo $referenceSuggestionRow['full_reference']; ?></td>
					                                                                    </tr>			
					                                            <?php
					                                                                }
					                                            ?>
					                                                                </tbody>
					                                                            </table>
					                                                        </div>
					                                                        <div class="spacer"></div>
					                                            <?php
					                                                    }
					                                                        
					                                            ?>
					                                            <?php
					                                                    /* Unknown data in a. a. O., Hyphen ask reference possiblities END */
					                                                }else if($unApprovedRow['direct_order_priority'] == $lowestPriorityValue){
					                                                    /* Direct order checking START */
					                                                    if($unApprovedRow['approval_for'] == 1)
					                                                        $workingApprovalString = $unApprovedRow['middle_bracket_approval_string'];
					                                                    else
					                                                        $workingApprovalString = str_replace("{#^#}", ", ", $unApprovedRow['approval_string']);
					                                            ?>
					                                                    <h3>Direct Order for (<span class="text-danger"><?php echo $workingApprovalString; ?></span>)</h3>
					                                                    <?php if($unApprovedRow['is_pre_defined_tags_approval'] == 1){ ?>
					                                                        <small class="text-danger">(Found in pre define tags, please check your document in below refered position for better understanding)</small>
					                                                    <?php }?>
					                                                    <div class="spacer"></div>
					                                                    <!-- <div class="row"> -->
					                                                        <div class="well direct-order-info">
					                                                            <p>Direct order tags list:</p>
					                                                            <ul>
					                                                                <li>@P:Prüfer</li>
					                                                                <li>@A:Remedy</li>
					                                                                <li>@AT:Similar remedy, Similar symptom text (e.g. Opi., during the day)</li>
					                                                                <li>@TA:Similar symptom text, Similar remedy (e.g. small boils in crops, Sulph.)</li>
					                                                                <li>@L:Reference(Literaturquelle)</li>
					                                                                <li>@L:No Author, Aepli sen. in Hufeland Journ. YYV. (when there is no author reference)</li>
					                                                                <li>@U:Unclear(Unklarheit)</li>
					                                                                <li>@F:Footnote(Fußnote)</li>
					                                                                <li>@T:Text/Symptom text</li>
					                                                                <li>@Z:Time</li>
					                                                                <li>@K:Chapter (Kapitel)</li>
					                                                                <li>@UK:Subchapter</li>
					                                                                <li>@UUK:Sub Subchapter</li>
					                                                                <li>@S:Page</li>
					                                                                <li>@N:Symptom-Nr.</li>
					                                                                <li>@C:Comment(Kommentar)</li>
					                                                                <li>@V:Hint(Verweiss)</li>
					                                                                <li>@G:Grading/Classification(Graduierung)</li>
					                                                            </ul>
					                                                        </div>
					                                                    <!-- </div> -->
					                                                    <div class="spacer"></div>
					                                                    <textarea id="direct_order" name="direct_order" class="form-control" placeholder="Direct order" rows="7"></textarea>
					                                                    <div class="spacer"></div>
					                                            <?php 
					                                                    /* Direct order checking END */
					                                                }else if($unApprovedRow['symptom_edit_priority'] == $lowestPriorityValue){
					                                                    /* Edit Symptom STRAT */
					                                            ?>
					                                                    <h3>Edit Symptom</h3>
					                                                    <div class="spacer"></div>
					                                                    <textarea id="symptom_text" name="symptom_text" class="texteditor" placeholder="Symptom text" rows="5"><?php echo $unApprovedRow['Beschreibung']; ?></textarea>
					                                                    <div class="spacer"></div>
					                                                    <h4 class="text-left">Comment</h4>
					                                                    <textarea id="symptom_edit_comment" name="symptom_edit_comment" maxlength="255" class="form-control" placeholder="Comment" rows="4"><?php echo $unApprovedRow['symptom_edit_comment']; ?></textarea>
					                                                    <div class="spacer"></div>
					                                            <?php
					                                                    /* Edit Symptom END */
					                                                }
					                                            ?>

					                                            <div class="table-responsive">
					                                                <h4 class="text-left"><u>Document reference</u></h4>          
					                                                <table class="table table-bordered">
					                                                    <thead>
					                                                        <tr>
					                                                            <th class="text-center">Symptom No</th>
					                                                            <th class="text-center">Page (@S)</th>
					                                                            <th class="text-center">Source</th>
					                                                            <?php if($unApprovedRow['is_pre_defined_tags_approval'] != 1){ ?>
					                                                                <th class="text-center">Edit</th>
					                                                            <?php } ?>
					                                                        </tr>
					                                                    </thead>
					                                                    <tbody>
					                                                        <tr>
					                                                            <td><?=$unApprovedRow['Symptomnummer']?></td>
					                                                            <td>
					                                                                <?php
					                                                                    if($unApprovedRow['SeiteOriginalVon'] == $unApprovedRow['SeiteOriginalBis'])
					                                                                        echo $unApprovedRow['SeiteOriginalVon'];
					                                                                    else
					                                                                        echo $unApprovedRow['SeiteOriginalVon']."-".$unApprovedRow['SeiteOriginalBis']
					                                                                ?>
					                                                            </td>
					                                                            <td><?=$unApprovedRow['Beschreibung']?></td>
					                                                            <?php if($unApprovedRow['is_pre_defined_tags_approval'] != 1){ ?>
					                                                                <td><button title="Edit symptom" type="submit" name="edit_symptom" id="edit_symptom" value="Edit"><i class="fas fa-pencil-alt"></i></button></td>
					                                                            <?php } ?>
					                                                        </tr>
					                                                    </tbody>
					                                                </table>
					                                            </div>
					                                            <span id="form-msg"></span>
					                                        </div>
					                                        <div class="modal-footer">
					                                            <input type="hidden" name="symptom_id" id="symptom_id" value="<?php echo $unApprovedRow['id']; ?>">
					                                            <input type="hidden" name="master_id" id="master_id" value="<?php echo $unApprovedRow['master_id']; ?>">
					                                            <?php if($unApprovedRow['reference_with_no_author_priority'] == $lowestPriorityValue){ ?>
					                                                <input type="hidden" name="approval_string" id="approval_string" value="<?php echo base64_encode("No Author, ".trim($unApprovedRow['approval_string'])); ?>">
					                                            <?php }else{ ?>
					                                                <input type="hidden" name="approval_string" id="approval_string" value="<?php echo base64_encode($unApprovedRow['approval_string']); ?>">
					                                            <?php } ?>
					                                            <input type="hidden" name="middle_bracket_approval_string" id="middle_bracket_approval_string" value="<?php echo base64_encode($unApprovedRow['middle_bracket_approval_string']); ?>">
					                                            <input type="hidden" name="approval_for" id="approval_for" value="<?php echo $unApprovedRow['approval_for']; ?>">
					                                            <input type="hidden" name="full_symptom_string" id="full_symptom_string" value="<?php echo base64_encode($unApprovedRow['Beschreibung_unchanged']); ?>">
					                                            <input type="hidden" name="full_approval_string_when_hyphen" id="full_approval_string_when_hyphen" value="<?php echo (isset($unApprovedRow['full_approval_string_when_hyphen']) AND $unApprovedRow['full_approval_string_when_hyphen'] != "") ? base64_encode($unApprovedRow['full_approval_string_when_hyphen']) : ''; ?>">
					                                            <input type="hidden" name="full_approval_string_when_hyphen_unchanged" id="full_approval_string_when_hyphen_unchanged" value="<?php echo (isset($unApprovedRow['full_approval_string_when_hyphen_unchanged']) AND $unApprovedRow['full_approval_string_when_hyphen_unchanged'] != "") ? base64_encode($unApprovedRow['full_approval_string_when_hyphen_unchanged']) : ''; ?>">
					                                            <input type="hidden" name="is_pre_defined_tags_approval" id="is_pre_defined_tags_approval" value="<?php echo $unApprovedRow['is_pre_defined_tags_approval'] ?>">
					                                            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> -->
					                                            <!-- <input type="submit" title="Back to previous symptom" class="btn btn-default" name="back_to_previous" id="back_to_previous" value="Back to previous"> -->
					                                            <?php if($unApprovedRow['symptom_edit_priority'] == $lowestPriorityValue) { ?>
					                                                <input title="Save" type="submit" class="btn btn-primary" name="symptom_edit_save" id="symptom_edit_save" value="Save">
					                                                <input  title="Cancel" type="submit" class="btn btn-default" name="symptom_edit_cancel" id="symptom_edit_cancel" value="Cancel">
					                                            <?php }else{ ?>
					                                                <?php if($unApprovedRow['direct_order_priority'] == $lowestPriorityValue) { ?>
					                                                    <input type="submit" class="btn btn-primary" name="submit" id="submit" value="Submit">
					                                                <?php }else{ ?>
					                                                    <?php if(isset($_GET['new-pruefer']) AND $_GET['new-pruefer'] == 1){ ?>
					                                                        <input type="hidden" name="add_pruefer" id="add_pruefer" value="No">
					                                                        <button class="btn btn-primary" type="button" onclick="addNewPruefer()">Save</button>
					                                                    <?php }else{ ?>
					                                                        <?php if($unApprovedRow['more_than_one_tag_string_priority'] != $lowestPriorityValue AND $unApprovedRow['aao_hyphen_priority'] != $lowestPriorityValue){ ?>
					                                                            <input type="submit" title="Yes" class="btn btn-primary" name="yes" id="yes" value="Yes">
					                                                        <?php } ?>
					                                                        <?php if($lowestPriorityValue != 10){ ?>
					                                                            <input type="submit" title="No" class="btn btn-danger" name="no" id="no" value="No">
					                                                        <?php } ?>
					                                                    <?php } ?>
					                                                    <input type="submit" title="Direct Order" class="btn btn-info" name="do" id="do" value="DO">
					                                                <?php } ?>
					                                                <input type="submit" title="Skip for now" class="btn btn-warning" name="later" id="later" value="Later">
					                                                <input type="submit" title="Reset current symptom" class="btn btn-default" name="reset_current" id="reset_current" value="Reset">
					                                            <?php } ?>
					                                        </div>
					                                    </div>
					                                </form>
					                            </div>
					                        </div>
					        <?php
					                    }
					                    /* If Un Approved data found END */
					                }
					                else
					                {
					                    /* If Not Found any Un Approved data START */

					                    /* Inserting Temp table data to Main tables START */
					                    $masterResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_master where id = '".$_GET['master']."'");
					                    if(mysqli_num_rows($masterResult) > 0){
					                        $masterData = mysqli_fetch_assoc($masterResult); 
					                        $isAnyError = 0;
					                        try{
					                            // First of all, let's begin a transaction
					                            $db->begin_transaction();

					                            if($masterData['pruefer_ids'] != "")
					                                $masterDataPrueferIdsArray = explode(",", $masterData['pruefer_ids']);
					                            else
					                                $masterDataPrueferIdsArray = array();
					                            $masterData['import_rule'] = ($masterData['import_rule'] != "") ? mysqli_real_escape_string($db, $masterData['import_rule']) : null;
					                            $masterData['importing_language'] = ($masterData['importing_language'] != "") ? mysqli_real_escape_string($db, $masterData['importing_language']) : null;

					                            $masterData['is_symptoms_available_in_de'] = ($masterData['importing_language'] == "de") ? 1 : 0;
					                            $masterData['is_symptoms_available_in_en'] = ($masterData['importing_language'] == "en") ? 1 : 0;
					                            $masterData['translation_method_of_de'] = ($masterData['importing_language'] == "de") ? "Professional Translation" : null;
					                            $masterData['translation_method_of_en'] = ($masterData['importing_language'] == "en") ? "Professional Translation" : null;

					                            $masterData['arznei_id'] = ($masterData['arznei_id'] != "") ? mysqli_real_escape_string($db, $masterData['arznei_id']) : null;
					                            $masterData['quelle_id'] = ($masterData['quelle_id'] != "") ? mysqli_real_escape_string($db, $masterData['quelle_id']) : null;
					                            $masterData['pruefer_ids'] = ($masterData['pruefer_ids'] != "") ? mysqli_real_escape_string($db, $masterData['pruefer_ids']) : null;
					                            $masterData['excluding_symptoms_chapters'] = ($masterData['excluding_symptoms_chapters'] != "") ? mysqli_real_escape_string($db, $masterData['excluding_symptoms_chapters']) : null;
					                            $masterData['import_comment'] = ($masterData['import_comment'] != "") ? mysqli_real_escape_string($db, $masterData['import_comment']) : null;
					                            $masterData['ersteller_datum'] = ($masterData['ersteller_datum'] != "") ? mysqli_real_escape_string($db, $masterData['ersteller_datum']) : null;
					                            $masterMainInsertQuery="INSERT INTO quelle_import_master (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, pruefer_ids, excluding_symptoms_chapters, import_comment, ersteller_datum) VALUES ('".$masterData['import_rule']."', NULLIF('".$masterData['importing_language']."', ''), NULLIF('".$masterData['is_symptoms_available_in_de']."', ''), NULLIF('".$masterData['is_symptoms_available_in_en']."', ''), NULLIF('".$masterData['translation_method_of_de']."', ''), NULLIF('".$masterData['translation_method_of_en']."', ''), NULLIF('".$masterData['arznei_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['pruefer_ids']."', ''), NULLIF('".$masterData['excluding_symptoms_chapters']."', ''), NULLIF('".$masterData['import_comment']."', ''), '".$date."')";
					                            $db->query($masterMainInsertQuery);
					                            $mainMasterId = mysqli_insert_id($db);

					                            // These are imported quelle(means normal quelle, these are not created form save comparison) so they are linked with normal quelle table not with quelle_backup table here both quelle_id and original_quelle_id is from normal quelle table 
					                            $quelleSymptomsMasterBackupInsertQuery="INSERT INTO quelle_import_master_backup (import_rule, importing_language, is_symptoms_available_in_de, is_symptoms_available_in_en, translation_method_of_de, translation_method_of_en, arznei_id, quelle_id, original_quelle_id, pruefer_ids, excluding_symptoms_chapters, import_comment, stand, ersteller_datum) VALUES ('".$masterData['import_rule']."', NULLIF('".$masterData['importing_language']."', ''), NULLIF('".$masterData['is_symptoms_available_in_de']."', ''), NULLIF('".$masterData['is_symptoms_available_in_en']."', ''), NULLIF('".$masterData['translation_method_of_de']."', ''), NULLIF('".$masterData['translation_method_of_en']."', ''), NULLIF('".$masterData['arznei_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['quelle_id']."', ''), NULLIF('".$masterData['pruefer_ids']."', ''), NULLIF('".$masterData['excluding_symptoms_chapters']."', ''), NULLIF('".$masterData['import_comment']."', ''), '".$date."', NULLIF('".$masterData['ersteller_datum']."', ''))";
					                            $db->query($quelleSymptomsMasterBackupInsertQuery);
					                            $quelleSymptomsMasterBackupId = $db->insert_id;

					                            // Making arznei quelle relationship
					                            if($masterData['arznei_id'] != "" AND $masterData['quelle_id'] != ""){
					                                $arzneiQuelleResult = mysqli_query($db, "SELECT arznei_id FROM arznei_quelle where arznei_id = '".$masterData['arznei_id']."' AND quelle_id = '".$masterData['quelle_id']."'");
					                                if(mysqli_num_rows($arzneiQuelleResult) == 0){
					                                    $arzneiQuelleInsertQuery="INSERT INTO arznei_quelle (arznei_id, quelle_id, ersteller_datum) VALUES ('".$masterData['arznei_id']."', '".$masterData['quelle_id']."', '".$date."')";
					                                    $db->query($arzneiQuelleInsertQuery);
					                                }
					                            }

					                            // If we arrive here, it means that no exception was thrown
					                            // i.e. no query has failed, and we can commit the transaction
					                            $db->commit();
					                        }catch (Exception $e) {
					                            // An exception has been thrown
					                            // We must rollback the transaction
					                            $db->rollback();
					                            $isAnyError = 1;
					                        }

					                        if($isAnyError == 0){
					                            try{
					                                // First of all, let's begin a transaction
					                                $db->begin_transaction();

					                                /* Insert Symptoms START */
					                                $symptomResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test where master_id = '".$_GET['master']."'");
					                                if(mysqli_num_rows($symptomResult) > 0){
					                                    while($symptomData = mysqli_fetch_array($symptomResult)){
					                                        $symptomData['arznei_id'] = ($symptomData['arznei_id'] != "") ? mysqli_real_escape_string($db, $symptomData['arznei_id']) : null;
					                                        $symptomData['quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
					                                        $symptomData['original_quelle_id'] = ($symptomData['quelle_id'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_id']) : null;
					                                        $symptomData['quelle_code'] = ($symptomData['quelle_code'] != "") ? mysqli_real_escape_string($db, $symptomData['quelle_code']) : null;
					                                        $symptomData['Symptomnummer'] = mysqli_real_escape_string($db, $symptomData['Symptomnummer']);
					                                        $symptomData['SeiteOriginalVon'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalVon']);
					                                        $symptomData['SeiteOriginalBis'] = mysqli_real_escape_string($db, $symptomData['SeiteOriginalBis']);
					                                        $symptomData['Beschreibung_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['Beschreibung']) : null;
					                                        $symptomData['Beschreibung_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['Beschreibung']) : null;
					                                        $symptomData['BeschreibungOriginal_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
					                                        $symptomData['BeschreibungOriginal_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
					                                        $symptomData['BeschreibungFull_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
					                                        $symptomData['BeschreibungFull_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungOriginal']) : null;
					                                        $symptomData['BeschreibungPlain_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['BeschreibungPlain']) : null;
					                                        $symptomData['BeschreibungPlain_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['BeschreibungPlain']) : null;
					                                        $symptomData['searchable_text_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['searchable_text']) : null;
					                                        $symptomData['searchable_text_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['searchable_text']) : null;
					                                        $symptomData['bracketedString_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['bracketedString']) : null;
					                                        $symptomData['bracketedString_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['bracketedString']) : null;
					                                        $symptomData['timeString_de'] = ($masterData['importing_language'] == "de") ? mysqli_real_escape_string($db, $symptomData['timeString']) : null;
					                                        $symptomData['timeString_en'] = ($masterData['importing_language'] == "en") ? mysqli_real_escape_string($db, $symptomData['timeString']) : null;
					                                        $symptomData['Fussnote'] = mysqli_real_escape_string($db, $symptomData['Fussnote']);
					                                        $symptomData['EntnommenAus'] = mysqli_real_escape_string($db, $symptomData['EntnommenAus']);
					                                        $symptomData['Verweiss'] = mysqli_real_escape_string($db, $symptomData['Verweiss']);
					                                        $symptomData['Graduierung'] = mysqli_real_escape_string($db, $symptomData['Graduierung']);
					                                        $symptomData['BereichID'] = mysqli_real_escape_string($db, $symptomData['BereichID']);
					                                        $symptomData['Kommentar'] = mysqli_real_escape_string($db, $symptomData['Kommentar']);
					                                        $symptomData['Unklarheiten'] = mysqli_real_escape_string($db, $symptomData['Unklarheiten']);
					                                        $symptomData['Remedy'] = mysqli_real_escape_string($db, $symptomData['Remedy']);
					                                        $symptomData['symptom_of_different_remedy'] = mysqli_real_escape_string($db, $symptomData['symptom_of_different_remedy']);
					                                        $symptomData['synonym_word'] = mysqli_real_escape_string($db, $symptomData['synonym_word']);
					                                        $symptomData['strict_synonym'] = mysqli_real_escape_string($db, $symptomData['strict_synonym']);
					                                        $symptomData['synonym_partial_1'] = mysqli_real_escape_string($db, $symptomData['synonym_partial_1']);
					                                        $symptomData['synonym_partial_2'] = mysqli_real_escape_string($db, $symptomData['synonym_partial_2']);
					                                        $symptomData['synonym_general'] = mysqli_real_escape_string($db, $symptomData['synonym_general']);
					                                        $symptomData['synonym_minor'] = mysqli_real_escape_string($db, $symptomData['synonym_minor']);
					                                        $symptomData['synonym_nn'] = mysqli_real_escape_string($db, $symptomData['synonym_nn']);
					                                        $symptomData['symptom_edit_comment'] = mysqli_real_escape_string($db, $symptomData['symptom_edit_comment']);
					                                        $symptomData['is_excluded_in_comparison'] = mysqli_real_escape_string($db, $symptomData['is_excluded_in_comparison']);

					                                        $mainSymptomInsertQuery="INSERT INTO quelle_import_test (master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, symptom_edit_comment, is_excluded_in_comparison, is_symptom_number_mismatch) VALUES (".$mainMasterId.", NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['synonym_word']."', ''), NULLIF('".$symptomData['strict_synonym']."', ''), NULLIF('".$symptomData['synonym_partial_1']."', ''), NULLIF('".$symptomData['synonym_partial_2']."', ''), NULLIF('".$symptomData['synonym_general']."', ''), NULLIF('".$symptomData['synonym_minor']."', ''), NULLIF('".$symptomData['synonym_nn']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), '".$symptomData['is_excluded_in_comparison']."', '".$symptomData['is_symptom_number_mismatch']."')";
					                                
					                                        $db->query($mainSymptomInsertQuery);
					                                        $mainSymtomId = mysqli_insert_id($db);

					                                        // ADD IN THE BACKUP
					                                        $mainSymptomBackupInsertQuery="INSERT INTO quelle_import_backup (master_id, arznei_id, quelle_id, original_quelle_id, quelle_code, Symptomnummer, SeiteOriginalVon, SeiteOriginalBis, Beschreibung_de, Beschreibung_en, BeschreibungOriginal_de, BeschreibungOriginal_en, BeschreibungFull_de, BeschreibungFull_en, BeschreibungPlain_de, BeschreibungPlain_en, searchable_text_de, searchable_text_en, bracketedString_de, bracketedString_en, timeString_de, timeString_en, Fussnote, EntnommenAus, Verweiss, Graduierung, BereichID, Kommentar, Unklarheiten, Remedy, symptom_of_different_remedy, synonym_word, strict_synonym, synonym_partial_1, synonym_partial_2, synonym_general, synonym_minor, synonym_nn, symptom_edit_comment, is_excluded_in_comparison, is_symptom_number_mismatch) VALUES (".$quelleSymptomsMasterBackupId.", NULLIF('".$symptomData['arznei_id']."', ''), NULLIF('".$symptomData['quelle_id']."', ''), NULLIF('".$symptomData['original_quelle_id']."', ''), NULLIF('".$symptomData['quelle_code']."', ''), NULLIF('".$symptomData['Symptomnummer']."', ''), NULLIF('".$symptomData['SeiteOriginalVon']."', ''), NULLIF('".$symptomData['SeiteOriginalBis']."', ''), NULLIF('".$symptomData['Beschreibung_de']."', ''), NULLIF('".$symptomData['Beschreibung_en']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_de']."', ''), NULLIF('".$symptomData['BeschreibungOriginal_en']."', ''), NULLIF('".$symptomData['BeschreibungFull_de']."', ''), NULLIF('".$symptomData['BeschreibungFull_en']."', ''), NULLIF('".$symptomData['BeschreibungPlain_de']."', ''), NULLIF('".$symptomData['BeschreibungPlain_en']."', ''), NULLIF('".$symptomData['searchable_text_de']."', ''), NULLIF('".$symptomData['searchable_text_en']."', ''), NULLIF('".$symptomData['bracketedString_de']."', ''), NULLIF('".$symptomData['bracketedString_en']."', ''), NULLIF('".$symptomData['timeString_de']."', ''), NULLIF('".$symptomData['timeString_en']."', ''), NULLIF('".$symptomData['Fussnote']."', ''), NULLIF('".$symptomData['EntnommenAus']."', ''), NULLIF('".$symptomData['Verweiss']."', ''), NULLIF('".$symptomData['Graduierung']."', ''), NULLIF('".$symptomData['BereichID']."', ''), NULLIF('".$symptomData['Kommentar']."', ''), NULLIF('".$symptomData['Unklarheiten']."', ''), NULLIF('".$symptomData['Remedy']."', ''), NULLIF('".$symptomData['symptom_of_different_remedy']."', ''), NULLIF('".$symptomData['synonym_word']."', ''), NULLIF('".$symptomData['strict_synonym']."', ''), NULLIF('".$symptomData['synonym_partial_1']."', ''), NULLIF('".$symptomData['synonym_partial_2']."', ''), NULLIF('".$symptomData['synonym_general']."', ''), NULLIF('".$symptomData['synonym_minor']."', ''), NULLIF('".$symptomData['synonym_nn']."', ''), NULLIF('".$symptomData['symptom_edit_comment']."', ''), '".$symptomData['is_excluded_in_comparison']."', '".$symptomData['is_symptom_number_mismatch']."')";
					                                        $db->query($mainSymptomBackupInsertQuery);
					                                        $mainSymtomBackupId = $db->insert_id;

					                                        // Symptom grading setting transfer start 
					                                        $symptomGradingSettingsResult = mysqli_query($db, "SELECT * FROM temp_symptom_grading_settings where symptom_id = '".$symptomData['id']."' AND master_id = '".$_GET['master']."'");
					                                        if(mysqli_num_rows($symptomGradingSettingsResult) > 0){
					                                            while($symptomGradingData = mysqli_fetch_array($symptomGradingSettingsResult))
					                                            {
					                                                $gradingData = array();
					                                                $gradingData['normal']= mysqli_real_escape_string($db, $symptomGradingData['normal']);
					                                                $gradingData['normal_within_parentheses']= mysqli_real_escape_string($db, $symptomGradingData['normal_within_parentheses']);
					                                                $gradingData['normal_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_t']);
					                                                $gradingData['normal_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_tt']);
					                                                $gradingData['normal_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_degree']);
					                                                $gradingData['normal_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_end_with_degree']);
					                                                $gradingData['normal_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk']);
					                                                $gradingData['normal_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_t']); 
					                                                $gradingData['normal_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_tt']);
					                                                $gradingData['normal_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['normal_begin_with_asterisk_end_with_degree']);
					                                                $gradingData['sperrschrift']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift']);
					                                                $gradingData['sperrschrift_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_begin_with_degree']);
					                                                $gradingData['sperrschrift_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_begin_with_asterisk']);
					                                                $gradingData['sperrschrift_bold']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold']);
					                                                $gradingData['sperrschrift_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold_begin_with_degree']);
					                                                $gradingData['sperrschrift_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['sperrschrift_bold_begin_with_asterisk']);
					                                                $gradingData['kursiv']= mysqli_real_escape_string($db, $symptomGradingData['kursiv']);
					                                                $gradingData['kursiv_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_t']);
					                                                $gradingData['kursiv_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_tt']);
					                                                $gradingData['kursiv_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_degree']);
					                                                $gradingData['kursiv_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_end_with_degree']);
					                                                $gradingData['kursiv_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk']);
					                                                $gradingData['kursiv_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_t']);
					                                                $gradingData['kursiv_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_tt']);
					                                                $gradingData['kursiv_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_begin_with_asterisk_end_with_degree']);
					                                                $gradingData['kursiv_bold']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold']);
					                                                $gradingData['kursiv_bold_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_t']);
					                                                $gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_tt']);
					                                                $gradingData['kursiv_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_degree']);
					                                                $gradingData['kursiv_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk']);
					                                                $gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['kursiv_bold_begin_with_asterisk_end_with_degree']);
					                                                $gradingData['fett']= mysqli_real_escape_string($db, $symptomGradingData['fett']);
					                                                $gradingData['fett_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_t']);
					                                                $gradingData['fett_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_tt']);
					                                                $gradingData['fett_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_degree']);
					                                                $gradingData['fett_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_end_with_degree']);
					                                                $gradingData['fett_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk']);
					                                                $gradingData['fett_begin_with_asterisk_end_with_t']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_t']);
					                                                $gradingData['fett_begin_with_asterisk_end_with_tt']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_tt']);
					                                                $gradingData['fett_begin_with_asterisk_end_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['fett_begin_with_asterisk_end_with_degree']);
					                                                $gradingData['gross']= mysqli_real_escape_string($db, $symptomGradingData['gross']);
					                                                $gradingData['gross_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['gross_begin_with_degree']);
					                                                $gradingData['gross_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['gross_begin_with_asterisk']);
					                                                $gradingData['gross_bold']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold']);
					                                                $gradingData['gross_bold_begin_with_degree']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold_begin_with_degree']);
					                                                $gradingData['gross_bold_begin_with_asterisk']= mysqli_real_escape_string($db, $symptomGradingData['gross_bold_begin_with_asterisk']);
					                                                $gradingData['pi_sign']= mysqli_real_escape_string($db, $symptomGradingData['pi_sign']);
					                                                $gradingData['one_bar']= mysqli_real_escape_string($db, $symptomGradingData['one_bar']);
					                                                $gradingData['two_bar']= mysqli_real_escape_string($db, $symptomGradingData['two_bar']);
					                                                $gradingData['three_bar']= mysqli_real_escape_string($db, $symptomGradingData['three_bar']);
					                                                $gradingData['three_and_half_bar']= mysqli_real_escape_string($db, $symptomGradingData['three_and_half_bar']);
					                                                $gradingData['four_bar']= mysqli_real_escape_string($db, $symptomGradingData['four_bar']);
					                                                $gradingData['four_and_half_bar']= mysqli_real_escape_string($db, $symptomGradingData['four_and_half_bar']);
					                                                $gradingData['five_bar']= mysqli_real_escape_string($db, $symptomGradingData['five_bar']);

					                                                $symptomGradingInsertQuery="INSERT INTO symptom_grading_settings (symptom_id, normal, normal_within_parentheses, normal_end_with_t, normal_end_with_tt, normal_begin_with_degree, normal_end_with_degree, normal_begin_with_asterisk, normal_begin_with_asterisk_end_with_t, normal_begin_with_asterisk_end_with_tt, normal_begin_with_asterisk_end_with_degree, sperrschrift, sperrschrift_begin_with_degree, sperrschrift_begin_with_asterisk, sperrschrift_bold, sperrschrift_bold_begin_with_degree, sperrschrift_bold_begin_with_asterisk, kursiv, kursiv_end_with_t, kursiv_end_with_tt, kursiv_begin_with_degree, kursiv_end_with_degree, kursiv_begin_with_asterisk, kursiv_begin_with_asterisk_end_with_t, kursiv_begin_with_asterisk_end_with_tt, kursiv_begin_with_asterisk_end_with_degree, kursiv_bold, kursiv_bold_begin_with_asterisk_end_with_t, kursiv_bold_begin_with_asterisk_end_with_tt, kursiv_bold_begin_with_degree, kursiv_bold_begin_with_asterisk, kursiv_bold_begin_with_asterisk_end_with_degree, fett, fett_end_with_t, fett_end_with_tt, fett_begin_with_degree, fett_end_with_degree, fett_begin_with_asterisk, fett_begin_with_asterisk_end_with_t, fett_begin_with_asterisk_end_with_tt, fett_begin_with_asterisk_end_with_degree, gross, gross_begin_with_degree, gross_begin_with_asterisk, gross_bold, gross_bold_begin_with_degree, gross_bold_begin_with_asterisk, pi_sign, one_bar, two_bar, three_bar, three_and_half_bar, four_bar, four_and_half_bar, five_bar, ersteller_datum) VALUES (NULLIF('".$mainSymtomId."', ''), NULLIF('".$gradingData['normal']."', ''), NULLIF('".$gradingData['normal_within_parentheses']."', ''), NULLIF('".$gradingData['normal_end_with_t']."', ''), NULLIF('".$gradingData['normal_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_degree']."', ''), NULLIF('".$gradingData['normal_end_with_degree']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['normal_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_begin_with_asterisk']."', ''), NULLIF('".$gradingData['sperrschrift_bold']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['sperrschrift_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv']."', ''), NULLIF('".$gradingData['kursiv_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['kursiv_bold_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['fett']."', ''), NULLIF('".$gradingData['fett_end_with_t']."', ''), NULLIF('".$gradingData['fett_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_degree']."', ''), NULLIF('".$gradingData['fett_end_with_degree']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_t']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_tt']."', ''), NULLIF('".$gradingData['fett_begin_with_asterisk_end_with_degree']."', ''), NULLIF('".$gradingData['gross']."', ''), NULLIF('".$gradingData['gross_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_begin_with_asterisk']."', ''), NULLIF('".$gradingData['gross_bold']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_degree']."', ''), NULLIF('".$gradingData['gross_bold_begin_with_asterisk']."', ''), NULLIF('".$gradingData['pi_sign']."', ''), NULLIF('".$gradingData['one_bar']."', ''), NULLIF('".$gradingData['two_bar']."', ''), NULLIF('".$gradingData['three_bar']."', ''), NULLIF('".$gradingData['three_and_half_bar']."', ''), NULLIF('".$gradingData['four_bar']."', ''), NULLIF('".$gradingData['four_and_half_bar']."', ''), NULLIF('".$gradingData['five_bar']."', ''), NULLIF('".$date."', ''))";
					                                                $db->query($symptomGradingInsertQuery);
					                                            }
					                                        }
					                                        // Symptom grading setting transfer end

					                                        /* Insert symptom_remedy relation START */
												            $symptomRemedyResult = mysqli_query($db, "SELECT symptom_id, main_remedy_id, is_new FROM temp_remedy where symptom_id = '".$symptomData['id']."'");
															if(mysqli_num_rows($symptomRemedyResult) > 0){
																while($symptomRemedyData = mysqli_fetch_array($symptomRemedyResult)){
																	$mainSymptomRemedyInsertQuery = "INSERT INTO symptom_remedy (symptom_id, remedy_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomRemedyData['main_remedy_id']."', '".$date."')";
												            		$db->query($mainSymptomRemedyInsertQuery);

												            		// ADD IN THE BACKUP
													            	$mainSymptomRemedyBackupInsertQuery = "INSERT INTO symptom_remedy_backup (symptom_id, remedy_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomRemedyData['main_remedy_id']."', '".$date."')";
													            	$db->query($mainSymptomRemedyBackupInsertQuery);
																}
															}
															/* Insert symptom_remedy relation END */

					                                        /* Insert Symptom_pruefer relation START */
					                                        $hasInlinePrueferOrReference = 0;
					                                        $symptomPrueferResult = mysqli_query($db, "SELECT symptom_id, pruefer_id, is_new FROM temp_symptom_pruefer where symptom_id = '".$symptomData['id']."'");
					                                        if(mysqli_num_rows($symptomPrueferResult) > 0){
					                                            while($symptomPrueferData = mysqli_fetch_array($symptomPrueferResult)){
					                                                $mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
					                                                $db->query($mainSymptomPrueferInsertQuery);

					                                                // ADD IN THE BACKUP
					                                                $mainSymptomPrueferBackupInsertQuery = "INSERT INTO symptom_pruefer_backup (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomPrueferData['pruefer_id']."', '".$date."')";
					                                                $db->query($mainSymptomPrueferBackupInsertQuery);
					                                                $hasInlinePrueferOrReference = 1;
					                                            }
					                                        }
					                                        /* Insert Symptom_pruefer relation END */

					                                        /* Insert Reference relation START */
					                                        $symptomReferenceResult = mysqli_query($db, "SELECT symptom_id, reference_id, is_new FROM temp_symptom_reference where symptom_id = '".$symptomData['id']."'");
					                                        if(mysqli_num_rows($symptomReferenceResult) > 0){
					                                            while($symptomReferenceData = mysqli_fetch_array($symptomReferenceResult)){
					                                                $mainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
					                                                $db->query($mainSymptomReferenceInsertQuery);

					                                                // ADD IN THE BACKUP
					                                                $mainSymptomReferenceBackupInsertQuery = "INSERT INTO symptom_reference_backup (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$symptomReferenceData['reference_id']."', '".$date."')";
					                                                $db->query($mainSymptomReferenceBackupInsertQuery);
					                                                $hasInlinePrueferOrReference = 1;
					                                            }
					                                        }
					                                        /* Insert Reference relation END */

					                                        /* Insert pre defined reference in symptom_reference relation START */
					                                        $preDefinedSymptomReferenceResult = mysqli_query($db, "SELECT reference_id FROM temp_pre_defined_symptom_reference where symptom_id = '".$symptomData['id']."'");
					                                        if(mysqli_num_rows($preDefinedSymptomReferenceResult) > 0){
					                                            while($preDefinedSymptomReferenceData = mysqli_fetch_array($preDefinedSymptomReferenceResult)){
					                                                $preDefinedInMainSymptomReferenceInsertQuery = "INSERT INTO symptom_reference (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$preDefinedSymptomReferenceData['reference_id']."', '".$date."')";
					                                                $db->query($preDefinedInMainSymptomReferenceInsertQuery);

					                                                // ADD IN THE BACKUP
					                                                $preDefinedInMainSymptomReferenceBackupInsertQuery = "INSERT INTO symptom_reference_backup (symptom_id, reference_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$preDefinedSymptomReferenceData['reference_id']."', '".$date."')";
					                                                $db->query($preDefinedInMainSymptomReferenceBackupInsertQuery);
					                                                $hasInlinePrueferOrReference = 1;
					                                            }
					                                        }
					                                        /* Insert pre defined reference in symptom_reference relation END */

					                                        /* Whenever we have a reference or Prüfer mentioned for a specific symptom, the main prüfer is not correct and should not be mentioned for that symptom. */
					                                        if($hasInlinePrueferOrReference == 0) {
					                                            foreach ($masterDataPrueferIdsArray as $masterPrufKey => $masterPrufVal) {
					                                                $mainSymptomPrueferInsertQuery = "INSERT INTO symptom_pruefer (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomId."', '".$masterPrufVal."', '".$date."')";
					                                                $db->query($mainSymptomPrueferInsertQuery);

					                                                // ADD IN THE BACKUP
					                                                $mainSymptomPrueferBackupInsertQuery = "INSERT INTO symptom_pruefer_backup (symptom_id, pruefer_id, ersteller_datum) VALUES ('".$mainSymtomBackupId."', '".$masterPrufVal."', '".$date."')";
					                                                $db->query($mainSymptomPrueferBackupInsertQuery);
					                                            }
					                                        }
					                                    }
					                                }
					                                /* Insert Symptoms END */

					                                $updateQuelleData = "UPDATE quelle SET is_materia_medica = 1, stand = NULLIF('".$date."', '') WHERE quelle_id = ".$masterData['quelle_id'];
													$db->query($updateQuelleData);

					                                /* Update quelle table for adding language informations START */
					                                /*$quelleUpdData['initially_imported_language'] = ($masterData['importing_language'] != "") ? $masterData['importing_language'] : null;
					                                $quelleUpdData['is_symptoms_available_in_german'] = ($masterData['importing_language'] == "de") ? 1 : 0;
					                                $quelleUpdData['is_symptoms_available_in_english'] = ($masterData['importing_language'] == "en") ? 1 : 0;
					                                $quelleUpdData['translation_method_of_german'] = ($masterData['importing_language'] == "de") ? "Professional Translation" : null;
					                                $quelleUpdData['translation_method_of_english'] = ($masterData['importing_language'] == "en") ? "Professional Translation" : null;
					                                $quelleUpdData['quelle_id'] = ($masterData['quelle_id'] != "") ? $masterData['quelle_id'] : null;

					                                $updateQuelleWithLanguageQuery="UPDATE quelle SET initially_imported_language = NULLIF('".$quelleUpdData['initially_imported_language']."', ''), is_symptoms_available_in_german = NULLIF('".$quelleUpdData['is_symptoms_available_in_german']."', ''), is_symptoms_available_in_english = NULLIF('".$quelleUpdData['is_symptoms_available_in_english']."', ''), translation_method_of_german = NULLIF('".$quelleUpdData['translation_method_of_german']."', ''), translation_method_of_english = NULLIF('".$quelleUpdData['translation_method_of_english']."', '') WHERE quelle_id = '".$quelleUpdData['quelle_id']."'";
					                                $db->query($updateQuelleWithLanguageQuery);*/
					                                /* Update quelle table for adding language informations END */

					                                // If we arrive here, it means that no exception was thrown
					                                // i.e. no query has failed, and we can commit the transaction
					                                $db->commit();
					                            }catch (Exception $e) {
					                                // An exception has been thrown
					                                // We must rollback the transaction
					                                $db->rollback();
					                                $isAnyError = 1;
					                            }
					                        }

					                        if($isAnyError == 0){
					                            /* Delete Temp table data START */
					                            deleteSourceImportTempData($_GET['master']);
					                            /* Delete Temp table data END */
					                        }       
					                    }
					                    /* Inserting Temp table data to Main tables END */
					                    
					                    /* If Not Found any Un Approved data END */
					                }

					            }
					        ?>

				            <!-- Source import form section start -->
				            <form id="source_import_form" name="source_import_form" action="" method="POST">
				                <div class="row">
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Select Import Setting<span class="required">*</span></label>
				                            <select class="form-control" name="settings" id="settings">
				                                <option value="">Select</option>
				                                <option value="default_setting">Default Setting [Source: Bold In Original: Double spaced]</option>
				                                <option disabled value="setting_2">Setting 2 [Source: Colored and non colored combiniations In Original: Adding pipes(|) in appropriate symptoms]</option>
				                            </select>	
				                            <span class="error-text"></span>
				                        </div>
				                    </div>
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Language<span class="required">*</span></label>
				                            <select class="form-control" name="importing_language" id="importing_language">
				                                <option value="">Select</option>
				                                <option value="de">German</option>
				                                <option value="en">English</option>
				                            </select>	
				                            <span class="error-text"></span>
				                        </div>
				                    </div>
				                </div>
				                <div class="row">
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Arznei<span class="required">*</span></label>
				                            <select class="select2 form-control" name="arznei_id" id="arznei_id">
				                                <option value="">Select</option>
				                                <?php
				                                    $arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei");
				                                    while($arzneiRow = mysqli_fetch_array($arzneiResult)){
				                                        echo '<option value="'.$arzneiRow['arznei_id'].'">'.$arzneiRow['titel'].'</option>';
				                                    }
				                                ?>
				                            </select>
				                            <span class="error-text"></span>	
				                        </div>
				                    </div>
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Quelle<span class="required">*</span></label>
				                            <div id="quelle_cnr">
				                                <!-- <select class="form-control" name="quelle_id" id="quelle_id">
				                                    <option value="">Select</option>
				                                </select>
				                                <span class="error-text"></span> -->
				                                <?php 
				                                    $html = '<select class="select2 form-control" name="quelle_id" id="quelle_id">';
				                                    $html .= '<option value="">Select</option>';
				                                    $quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id ORDER BY quelle.quelle_type_id ASC");
				                                    // $quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id FROM quelle ORDER BY quelle.quelle_type_id ASC");
				                                    $htmlBucher = '<optgroup label="Bücher/Quelle">';
				                                    $htmlZeitschriften = '<optgroup label="Zeitschriften">';
				                                    $htmlBInner = '';
				                                    $htmlZInner = '';
				                                    while($quelleRow = mysqli_fetch_array($quelleResult)){
				                                        $quellen_value = $quelleRow['code'];
				                                        if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
				                                        if(!empty($quelleRow['titel'])) $quellen_value .= ', '.$quelleRow['titel'];
				                                        if($quelleRow['quelle_type_id'] == 1){
				                                            if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
				                                        }else if($quelleRow['quelle_type_id'] == 2){
				                                            if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
				                                                $zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
				                                            else 
				                                                $zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
				                                            if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
				                                        }
				                                        /*if(!empty($quelleRow['jahr'])) $quellen_value .= ' '.$quelleRow['jahr'];
				                                        if(!empty($quelleRow['band'])) $quellen_value .= ', Band: '.$quelleRow['band'];
				                                        if(!empty($quelleRow['nummer'])) $quellen_value .= ', Nr.: '. $quelleRow['nummer'];
				                                        if(!empty($quelleRow['auflage'])) $quellen_value .= ', Auflage: '. $quelleRow['auflage'];*/

				                                        if($quelleRow['quelle_type_id'] == 1)
				                                            $htmlBInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
				                                        else if($quelleRow['quelle_type_id'] == 2)
				                                            $htmlZInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
				                                    }
				                                    if($htmlBInner == '')
				                                        $htmlBucher .= '<option value="" disabled="disabled">None</option>';
				                                    else
				                                        $htmlBucher .= $htmlBInner;
				                                    if($htmlZInner == '')
				                                        $htmlZeitschriften .= '<option value="" disabled="disabled">None</option>';
				                                    else
				                                        $htmlZeitschriften .= $htmlZInner;
				                                    $htmlBucher .= '</optgroup>';
				                                    $htmlZeitschriften .= '</optgroup>';

				                                    $html .= $htmlBucher;
				                                    $html .= $htmlZeitschriften;
				                                    $html .= '</select>';
				                                    $html .= '<span class="error-text"></span>';
				                                    echo $html;
				                                ?>
				                            </div>
				                        </div>
				                    </div>
				                </div>
				                <div class="row">
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Prüfer</label>
				                            <div id="pruefer_cnr">
				                                <select class="select2 form-control" name="pruefer_id[]" id="pruefer_id" multiple="multiple" data-placeholder="Search Prüfer...">
				                                    <?php
				                                        $prueferResult = mysqli_query($db,"SELECT pruefer_id, titel, vorname, nachname FROM pruefer");
				                                        while($prueferRow = mysqli_fetch_array($prueferResult)){
				                                            $prueferFullname = "";
				                                            $prueferFullname .= ($prueferRow['titel'] != "") ? $prueferRow['titel']." " : "";
				                                            $prueferFullname .= ($prueferRow['vorname'] != "") ? $prueferRow['vorname']." " : "";
				                                            $prueferFullname .= ($prueferRow['nachname'] != "") ? $prueferRow['nachname'] : "";
				                                            if(trim($prueferFullname) != "")
				                                                echo '<option value="'.$prueferRow['pruefer_id'].'">'.$prueferFullname.'</option>';
				                                        }
				                                    ?>
				                                </select>
				                                <span class="error-text"></span>
				                            </div>
				                        </div>
				                    </div>
				                    <div class="col-sm-6">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Chapters to exclude their symptoms in comparison</label>
				                            <small>(Add chapters names seperated by comma)</small>
				                            <div id="chapters_cnr">
				                            	<input class="form-control" type="text" name="excluding_symptoms_chapters" id="excluding_symptoms_chapters" placeholder="Chapters to exclude their symptoms in comparison">
				                                <span class="error-text"></span>
				                            </div>
				                        </div>
				                    </div>
				                </div>
				                <div class="row">
				                    <div class="col-sm-12">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Comment</label>
				                            <div id="comment_cnr">
				                                <!-- <input type="text" name="import_comment" id="import_comment" class="form-control" placeholder="Comment"> -->
				                                <textarea id="import_comment" name="import_comment" class="form-control" aria-hidden="true" placeholder="Comment"></textarea>
				                            </div>
				                        </div>
				                    </div>
				                </div>
				                <div class="row">
				                    <div class="col-sm-12">
				                        <div class="form-group Text_form_group">
				                            <label class="control-label">Text Editor<span class="required">*</span></label>
				                            <textarea id="symptomtext" name="symptomtext" class="texteditor-large" aria-hidden="true"></textarea>	
				                        </div>
				                    </div>
				                </div>
				                <div class="form-group text-center">
				                    <!-- <input type="submit" name="submit" class="btn btn-success" value="Submit"> -->
				                    <input type="hidden" name="submit_hidden" value="Submit">
				                    <button class="btn btn-success" type="button" onclick="importSource()">Submit</button>
				                    <!-- <input type="button" onclick="chck()" name="submit" class="btn btn-success" value="Submit"> -->
				                </div>
				                <div class="spacer"></div>
				                <div class="spacer"></div>
				            </form>
				            <hr>
				            <!-- Source import form section end -->
				            <!-- search tab -->
				            <div class="row">
				            	<div class="spacer"></div>
				            	<div class="spacer"></div>
				                <?php include 'search-arznei-materia-medica.php'; ?>
				            </div>
				            <!-- search tab end -->
				            <!-- symptom list table start -->
				            <div class="row">
				                <!-- <div class="col-sm-12 box"> -->
				                <div class="col-sm-12">  
				                    <div class="master-table-cnr">          
				                        <form id="source_table_form" name="source_table_form" action="" method="POST">
				                            <table class="table table-bordered heading-table">
				                                <thead class="heading-table-bg">
				                                    <tr>
				                                        <th style="width: 5%;">Jahr</th>
				                                        <th style="width: 10%;" class="text-center">Kürzel</th>
				                                        <th style="width: 20%;">Titel</th>
				                                        <th style="width: 10%;">Date</th>
				                                        <th style="width: 15%;">Arznei</th>
				                                        <th style="width: 11%;">Import Setting</th>
				                                        <th style="width: 8%;">View</th>
				                                        <th class="text-center" style="width: 6%;">Original</th>
				                                        <th class="text-center" style="width: 6%;">de</th>
				                                        <th class="text-center" style="width: 6%;">en</th>
				                                        <!-- <th class="text-center" style="width: 3%;"><a title="Download in Word Document" class="text-black" href="javascript:void(0)"><i class="fas fa-download mm-fa-icon"></i></a></th> -->
				                                        <th class="text-center" style="width: 3%;"><a title="Delete" class="text-danger" href="javascript:void(0)"><i class="fas fa-trash-alt mm-fa-icon"></i></a></th>
				                                    </tr>
				                                </thead>
				                            </table>
				                            <table class="table table-bordered table-hover">
				                                <tbody>
				                                    <?php
				                                        //conditions custom
				                                        $conditions = " AND "; 
				                                        $conditions .= !empty( $_GET["arznei_id_custom"] ) ? "QIM.arznei_id =". $_GET['arznei_id_custom'] ." AND " : "";
				                                        $conditions .= !empty( $_GET["jahr_custom"] ) ? "Q.jahr LIKE '%". $_GET['jahr_custom'] ."%' AND " : "";
				                                        $conditions .= !empty( $_GET["date_custom"] ) ? "QIM.ersteller_datum LIKE '%". $_GET['date_custom'] ."%' AND " : "";
				                                        $conditions .= !empty( $_GET["titel_custom"] ) ? "Q.titel LIKE '%". $_GET['titel_custom'] ."%' AND " : "";
				                                        $conditions .= !empty( $_GET["code_custom"] ) ? "Q.code LIKE '%". $_GET['code_custom'] ."%' AND " : "";
				                                        $conditions = rtrim($conditions, " AND");
				                                        
				                                        if(isset($_GET['custom_form_submission'])){
				                                            $result = mysqli_query($db,"SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.sprache, ARZ.titel AS arznei_titel FROM quelle_import_master AS QIM LEFT JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id LEFT JOIN arznei AS ARZ ON QIM.arznei_id = ARZ.arznei_id WHERE Q.quelle_type_id != 3 $conditions ORDER BY QIM.ersteller_datum DESC");
				                                            // $sympResult = mysqli_query($db, "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id FROM quelle_import_master AS QIM LEFT JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id WHERE Q.is_materia_medica = 1 ORDER BY Q.jahr ASC");
				                                            if(mysqli_num_rows($result) > 0) {
					                                            while($row = mysqli_fetch_array($result)){   
					                                                ?>
					                                                <tr>
					                                                    <td style="width: 5%;"><?php echo $row['jahr']; ?></td>
					                                                    <td style="width: 10%;" class="text-center">
					                                                        <?php 
					                                                        if($row['quelle_type_id'] != 3){
					                                                            echo $row['code'];
					                                                        }else{
					                                                            echo "-";
					                                                        }
					                                                        ?>
					                                                    </td>
					                                                    <td style="width: 20%;">
					                                                        <?php
					                                                            if(!empty($row['titel'])) 
					                                                                echo $row["titel"];
					                                                            else
					                                                                echo "-";
					                                                        ?>
					                                                    </td>
					                                                    <td style="width: 10%;"><?php echo date('d/m/Y h:i A', strtotime($row['ersteller_datum'])); ?></td>
					                                                    <td style="width: 15%;">
					                                                        <?php echo $row['arznei_titel']; ?>
					                                                    </td>
					                                                    <td style="width: 11%;"><?php echo ucwords(str_replace("_", " ", $row['import_rule'])); ?></td>
					                                                    <td style="width: 8%;">
					                                                        <a title="View symptoms" target="_blank" href="<?php echo $baseUrl; ?>symptoms.php?mid=<?php echo $row['id']; ?>">View Symptoms</a>
					                                                        <?php /*<span class="text-danger"> / </span>
					                                                        <a title="View connections" href="<?php echo $baseUrl; ?>view-source-connections.php?mid=<?php echo $row['id']; ?>">View connections</a> */ ?>
					                                                    </td>
					                                                    <td  class="text-center" style="width: 6%;">
					                                                        <?php if($row['sprache'] == "deutsch") { echo "de"; } elseif ($row['sprache'] == "englisch") { echo "en"; } else { echo "-"; } ?>
					                                                    </td>
					                                                    <td id="de_translation_btn_container_<?php echo $row['id']; ?>" class="text-center" style="width: 6%;">
					                                                        <?php if($row['is_symptoms_available_in_de'] == 1) { ?>
					                                                            <a id="" title="Symptoms available in German" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>
					                                                            /
					                                                            <a id="download_in_doc_<?php echo $row['quelle_id']; ?>" title="Download in Word Document" class="text-primary download-source" data-quelle-import-master-id="<?php echo $row['id']; ?>" data-language="de" href="javascript:void(0)"><i class="fas fa-download mm-fa-icon"></i></a>
					                                                        <?php } else { ?>
					                                                            <a id="" title="Add German translation" class="text-primary" onclick="addTranslation(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>, <?php echo $row['id']; ?>, 'de')" href="javascript:void(0)"><i class="fas fa-notes-medical mm-fa-icon"></i></a>
					                                                        <?php } ?>
					                                                    </td>
					                                                    <td id="en_translation_btn_container_<?php echo $row['id']; ?>" class="text-center" style="width: 6%;">
					                                                        <?php if($row['is_symptoms_available_in_en'] == 1) { ?>
					                                                            <a id="" title="Symptoms available in English" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>
					                                                            /
					                                                            <a id="download_in_doc_<?php echo $row['quelle_id']; ?>" title="Download in Word Document" class="text-primary download-source" data-quelle-import-master-id="<?php echo $row['id']; ?>" data-language="en" href="javascript:void(0)"><i class="fas fa-download mm-fa-icon"></i></a>
					                                                        <?php } else { ?>
					                                                            <a id="" title="Add English translation" class="text-primary"  onclick="addTranslation(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>, <?php echo $row['id']; ?>, 'en')" href="javascript:void(0)" href="javascript:void(0)"><i class="fas fa-notes-medical mm-fa-icon"></i></a>
					                                                        <?php } ?>
					                                                    </td>
					                                                    <?php /*<td class="text-center" style="width: 3%;">
					                                                        <a id="download_in_doc_<?php echo $row['quelle_id']; ?>" title="Download in Word Document" class="text-black" target="_blank" href="<?php echo $baseUrl; ?>download-in-word-document.php?mid=<?php echo $row['id']; ?>"><i class="fas fa-download mm-fa-icon"></i></a>
					                                                    </td>*/ ?>
					                                                    <td class="text-center" style="width: 3%;">
					                                                        <a id="delete_<?php echo $row['quelle_id']; ?>" title="Delete" class="text-danger"  onclick="deleteTheQuelle(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>)" href="javascript:void(0)"><i class="fas fa-trash-alt mm-fa-icon"></i></a>
					                                                    </td>
					                                                </tr>
					                                                <?php
					                                            }
					                                        }
					                                        else
					                                        {
					                                        ?>
					                                        	<tr>
					                                        		<td class="text-center">No records found.</td>
					                                        	</tr>
					                                        <?php
					                                        }
				                                        }
				                                        else
				                                        {
				                                        ?>
				                                        	<tr>
				                                        		<td class="text-center">No records found.</td>
				                                        	</tr>
				                                        <?php
				                                        }	
				                                    ?>
				                                </tbody>
				                            </table>
				                        </form>
				                    </div>
				                </div>
				            </div>
				            <!-- symptom list table end -->
			            </div>
          			<!-- /.box-body -->
		    	</div>
			</div>
		</div>
	    <!-- /.row -->
  	</section>
  	<!-- /.content -->

  	<!-- Global message modal start -->
    <div class="modal fade" id="globalMsgModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Alert</h4>
                </div>
                <div id="global_msg_container" class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Global message modal end -->

    <!-- Translation modal start -->
    <div class="modal fade" id="translationModal" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="add_translation_form" name="add_translation_form" action="" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close add-translation-modal-btn" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add translation</h4>
                    </div>
                    <div id="translation_container" class="modal-body">
                        <div id="translation_modal_loader" class="form-group text-center hidden">
                            <span class="loading-msg">Process is in progress please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                            <span class="error-msg"></span>
                        </div>
                        <div class="row add-translation-input-field-container">
                            <div class="col-sm-12">
                                <label class="control-label">Translation Method<span class="required">*</span></label>
                            </div>
                            <div class="col-sm-12">
                                <div id="translation_method_radio_buttons">
                                    <label class="radio-inline"><input type="radio" name="translation_method" value="Professional Translation">Professional Translation</label>
                                    <label class="radio-inline"><input type="radio" name="translation_method" value="Google Translation">Google Translation</label>
                                </div>
                                <span class="error-msg"></span>
                                <div class="spacer"></div>
                            </div>
                            <div class="col-sm-12">
                                <label class="control-label">Text Editor<span class="required">*</span></label>
                                <textarea id="translation_symptoms" name="translation_symptoms" class="texteditor" aria-hidden="true"></textarea>
                                <span class="error-msg"></span>	
                                <div class="spacer"></div>
                                <span class="add-translation-global-error-msg"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="add_translation_master_id" id="add_translation_master_id">
                        <input type="hidden" name="add_translation_arznei_id" id="add_translation_arznei_id">
                        <input type="hidden" name="add_translation_quelle_id" id="add_translation_quelle_id">
                        <input type="hidden" name="add_translation_language" id="add_translation_language">
                        <button type="submit" class="btn btn-primary add-translation-modal-btn">Submit</button>
                        <button type="button" class="btn btn-default add-translation-modal-btn" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Translation modal end -->

    <!-- Add translation user approval modal start -->
    <div class="modal fade" id="translationUserApprovalModal" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- <form id="translation_user_approval_form" name="translation_user_approval_form" action="" method="POST"> -->
                    <div class="modal-header">
                        <button type="button" class="close translation-user-approval-modal-cancel-btn" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Need confirmation</h4>
                    </div>
                    <div id="translation_user_approval_container" class="modal-body">
                        <div id="translation_user_approval_modal_loader" class="form-group text-center hidden">
                            <span class="loading-msg">Process is in progress please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                            <span class="error-msg"></span>
                        </div>
                        <div class="row">
                            <div id="translation_user_approval_content" class="col-sm-12">
                                
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="translation_user_approval_master_id" id="translation_user_approval_master_id">
                        <input type="hidden" name="translation_user_approval_arznei_id" id="translation_user_approval_arznei_id">
                        <input type="hidden" name="translation_user_approval_quelle_id" id="translation_user_approval_quelle_id">
                        <input type="hidden" name="translation_user_approval_language" id="translation_user_approval_language">
                        <input type="hidden" name="translation_user_approval_temp_symptom_id" id="translation_user_approval_temp_symptom_id">
                        <button type="submit" id="translation_user_approval_modal_continue_btn" class="btn btn-primary translation-user-approval-modal-continue-btn">Continue</button>
                        <button type="button" id="translation_user_approval_modal_delete_btn" class="btn btn-danger translation-user-approval-modal-delete-btn">Delete</button>
                        <button type="button" class="btn btn-default translation-user-approval-modal-cancel-btn">Cancel</button>
                    </div>
                <!-- </form> -->
            </div>
        </div>
    </div>
    <!-- Add translation user approval modal end -->
</div>
<!-- /.content-wrapper -->
<?php
include '../inc/footer.php';
?>
<script src="assets/js/common.js"></script>
<!-- <script src="http://dev.newrepertory.com/dev-exp/assets/js/select2-custom-search-box-placeholder.js"></script>
<script src="http://dev.newrepertory.com/dev-exp/assets/js/common.js"></script> -->
<?php //$baseUrl = "http://dev.newrepertory.com/dev-exp/"; ?>
<script type="text/javascript">
    // Defining Select2
    $('#arznei_id').select2({
        // options 
        searchInputPlaceholder: 'Search Arznei...'
    });
    $('#quelle_id').select2({
        // options 
        searchInputPlaceholder: 'Search Quelle...'
    });
    $('#pruefer_id').select2({
        // options 
        searchInputPlaceholder: 'Search Prüfer...'
    });

    $(document).on('click', '.download-source', function(e){
        var mid =  $(this).attr("data-quelle-import-master-id");
        var lang =  $(this).attr("data-language");
        if(mid != "" && lang != ""){
            var win = window.open("<?php echo $baseUrl; ?>download-in-word-document.php?mid="+mid+"&lang="+lang, "_blank");
            if (win) {
                //Browser has allowed it to be opened
                win.focus();
            } else {
                //Browser has blocked it
                // alert('Please allow popups for this website');
                $("#global_msg_container").html('<p class="text-center">Please allow popups for this website</p>');
                $("#globalMsgModal").modal('show');
            }
        }
        else
        {
            $("#global_msg_container").html('<p class="text-center">Could not start the download, required data not found.</p>');
            $("#globalMsgModal").modal('show');
        }
    });

    // Fetching Pruefer
    $('#quelle_id').on('select2:select', function (e) {
        // console.log(e.params.data);
        $("#pruefer_id").prop("disabled", true);
        if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
            var request = $.ajax({
                url: "get_quelle_pruefer.php",
                type: "POST",
                data: {quelle_id : e.params.data.id},
                dataType: "html"
            });

            request.done(function(responseData) {
                // console.log(responseData);
                $("#pruefer_cnr").html( responseData );
                $('#pruefer_id').select2({
                    // options 
                    searchInputPlaceholder: 'Search Prüfer...'
                });
                $("#pruefer_id").prop("disabled", false);
            });

            request.fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                $("#pruefer_id").prop("disabled", false);
            });
        }
    });

    function deleteTheQuelle(quelle_id, arznei_id){
        var con = confirm("Deleting this Quelle will delete it's all conections and related comparison where this source is used, are you sure you want to delete?");
        if (con)
        {
            $('#delete_'+quelle_id).prop('disabled', true);
            $('#delete_'+quelle_id).html('<img src="assets/img/loader.gif" alt="Loader">');
            $.ajax({
                type: 'POST',
                url: 'delete-quelle-new.php',
                data: {
                    quelle_id: quelle_id,
                    arznei_id: arznei_id
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        location.reload();
                        // $('#delete_'+quelle_id).prop('disabled', false);
                        // $('#delete_'+quelle_id).html('<i class="fas fa-trash-alt"></i>');
                        // $("#row_"+quelle_id).remove();
                    }else{
                        $('#delete_'+quelle_id).prop('disabled', false);
                        $('#delete_'+quelle_id).html('<i class="fas fa-trash-alt"></i>');
                        $("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
                        $("#globalMsgModal").modal('show');
                    }
                }
            }).fail(function (response) {
                $('#delete_'+quelle_id).prop('disabled', false);
                $('#delete_'+quelle_id).html('<i class="fas fa-trash-alt"></i>');
                $("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
                $("#globalMsgModal").modal('show');

                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });
        }
        else
        {
            return false;
        }
    }

    function chck(){
        var content =  tinyMCE.get('symptomtext');
        var c=content.getContent();
        console.log(c);
        alert(c);
    }

    function resetRadio(name){
        $(".suggested-checkbox").prop("checked", false);
        $(".suggested-radio").prop("checked", false);
        $('.btn-order').removeAttr('disabled');

        // $("input:radio[name='" + name + "']").each(function (i) {
        //     var $this = $(this);
        //     $this.prop("checked", false);
        // });
        return false;
    }

    $('.suggested-checkbox').click(function(){
        if($('.suggested-checkbox:checkbox:checked').length > 0)
            $('.btn-order').attr('disabled', 'disabled');
        else
            $('.btn-order').removeAttr('disabled');
    });

    function importSource(){
        var settings = $("#settings").val();
        var arznei_id = $("#arznei_id").val();
        var quelle_id = $("#quelle_id").val();
        var importing_language = $("#importing_language").val();
        var error_count = 0;

        if(settings == ""){
            $("#settings").addClass('text-danger');
            $("#settings").next().html('Please Select a import rule');
            $("#settings").next().addClass('text-danger');
            error_count++;
        }else{
            $("#settings").removeClass('text-danger');
            $("#settings").next().html('');
            $("#settings").next().removeClass('text-danger');
        }
        if(arznei_id == ""){
            $("#arznei_id").next().next().html('Please Select arznei');
            $("#arznei_id").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#arznei_id").next().next().html('');
            $("#arznei_id").next().next().removeClass('text-danger');
        }
        if(quelle_id == ""){
            $("#quelle_id").next().next().html('Please Select quelle');
            $("#quelle_id").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#quelle_id").next().next().html('');
            $("#quelle_id").next().next().removeClass('text-danger');
        }
        if(importing_language == ""){
            $("#importing_language").addClass('text-danger');
            $("#importing_language").next().html('Please Select importing language');
            $("#importing_language").next().addClass('text-danger');
            error_count++;
        }else{
            $("#importing_language").removeClass('text-danger');
            $("#importing_language").next().html('');
            $("#importing_language").next().removeClass('text-danger');
        }

        if(error_count == 0){
            // $("#form-msg").removeClass("text-danger");
            // $("#form-msg").html("");
            $("#source_import_form").submit();
        }else{
            // $("#form-msg").addClass("text-danger");
            // $("#form-msg").html("Please correct all errors");
            $('html, body').animate({
                scrollTop: $("#source_import_form").offset().top
            }, 1000);
            return false;
        }
    }

    function addNewPruefer(){
        var nachname = $("#nachname").val();
        var error_count = 0;

        if(nachname == ""){
            $("#nachname").addClass('text-danger');
            $("#nachname").next().html('Nachname is mandatory');
            $("#nachname").next().addClass('text-danger');
            error_count++;
        }else{
            $("#nachname").removeClass('text-danger');
            $("#nachname").next().html('');
            $("#nachname").next().removeClass('text-danger');
        }

        if(error_count == 0){
            $("#form-msg").removeClass("text-danger");
            $("#form-msg").html("");
            $("#add_pruefer").val("Yes");
            $("#decisionMakingForm").submit();
        }else{
            $("#form-msg").addClass("text-danger");
            $("#form-msg").html("Please correct all errors");
            $("#add_pruefer").val("No");
            return false;
        }
    }

    function deleteUnclearSourceImport(master_id){
        $("#unclearNotifyForm"+master_id).submit();
    }

    $('#decisionMakingModal').on('hidden.bs.modal', function () {
        window.location.replace("<?php echo $baseUrl; ?>");
    })

    function addTranslation(quelle_id, arznei_id, master_id, language){
        var $th = $("#"+language+"_translation_btn_container_"+master_id);
        if($th.hasClass('processing'))
            return;
        $th.addClass('processing');
        var error_count = 0;
        console.log("Here");

        if(master_id == "")
            error_count++;
        if(arznei_id == "")
            error_count++;
        if(quelle_id == "")
            error_count++;

        if(error_count == 0) {
            $.ajax({
                type: 'POST',
                url: 'get-translation-approvable-data.php',
                data: {
                    add_translation_master_id: master_id,
                    add_translation_quelle_id: quelle_id,
                    add_translation_arznei_id: arznei_id,
                    add_translation_language: language
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        if (typeof(response.result_data) != "undefined" && response.result_data !== null && response.result_data != ""){
                            // $("#translation_user_approval_modal_loader").addClass('hidden');
                            var resultData = null;
                            try {
                                resultData = JSON.parse(response.result_data); 
                            } catch (e) {
                                resultData = response.result_data;
                            }
                            var Beschreibung_de = (typeof(resultData.Beschreibung_de) != "undefined" && resultData.Beschreibung_de !== null && resultData.Beschreibung_de != "") ? b64DecodeUnicode(resultData.Beschreibung_de) : "";
                            var Beschreibung_en = (typeof(resultData.Beschreibung_en) != "undefined" && resultData.Beschreibung_en !== null && resultData.Beschreibung_en != "") ? b64DecodeUnicode(resultData.Beschreibung_en) : "";
                            var html = '';
                            html += '<table class="table table-bordered">';
                            html += '	<tr>';
                            html += '		<th class="text-center" style="width:50%">German</th>';
                            html += '		<th class="text-center" style="width:50%">English</th>';
                            html += '	</tr>';
                            html += '	<tr>';
                            html += '		<td>'+Beschreibung_de+'</td>';
                            html += '		<td>'+Beschreibung_en+'</td>';
                            html += '	</tr>';
                            html += '</table>';

                            $("#translation_user_approval_master_id").val(master_id);
                            $("#translation_user_approval_arznei_id").val(arznei_id);
                            $("#translation_user_approval_quelle_id").val(quelle_id);
                            $("#translation_user_approval_language").val(language);
                            $("#translation_user_approval_temp_symptom_id").val(resultData.temp_symptom_id);
                            $("#translation_user_approval_content").html(html);

                            // Open translation user approval modal
                            $("#translation_user_approval_modal_loader .error-msg").html('');
                            if(!$("#translation_user_approval_modal_loader").hasClass('hidden'))
                                $("#translation_user_approval_modal_loader").addClass('hidden');
                            $("#translationUserApprovalModal").modal('show');
                            $th.removeClass('processing');
                        } else {

                            $("#add_translation_master_id").val(master_id);
                            $("#add_translation_arznei_id").val(arznei_id);
                            $("#add_translation_quelle_id").val(quelle_id);
                            $("#add_translation_language").val(language);
                            $("#translationModal").modal('show');
                            $th.removeClass('processing');
                        }
                        
                    }else{
                        var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                        $("#global_msg_container").html('<p class="text-center">'+msg+'</p>');
                        $("#globalMsgModal").modal('show');
                        $th.removeClass('processing');
                    }
                }
            }).fail(function (response) {
                $("#global_msg_container").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
                $("#globalMsgModal").modal('show');
                $th.removeClass('processing');
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });
        } else {
            $("#global_msg_container").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
            $("#globalMsgModal").modal('show');
            $th.removeClass('processing');
        }
    }

    $('#translationModal').on('hidden.bs.modal', function () {
        $('#add_translation_form').trigger("reset");
        $("#translation_symptoms").next().html('');
        $("#translation_symptoms").next().removeClass('text-danger');
        $("#translation_method_radio_buttons").next().html('');
        $("#translation_method_radio_buttons").next().removeClass('text-danger');
        $(".add-translation-global-error-msg").html('');
        $(".add-translation-global-error-msg").removeClass('text-danger');
        $("#add_translation_master_id").val("");
        $("#add_translation_arznei_id").val("");
        $("#add_translation_quelle_id").val("");
        $("#add_translation_language").val("");
        if(!$('#translation_modal_loader').hasClass('hidden'))
            $('#translation_modal_loader').addClass('hidden');
        $(".add-translation-input-field-container").removeClass('hidden');
        $('.add-translation-modal-btn').prop('disabled', false);
        $("#add_translation_form").removeClass('processing');
    })

    $('body').on( 'submit', '#add_translation_form', function(e) {
        e.preventDefault();
        var $th = $(this);
        if($th.hasClass('processing'))
            return;
        $th.addClass('processing');

        if(!$('#translation_modal_loader').hasClass('hidden'))
            $('#translation_modal_loader').addClass('hidden');
        $(".add-translation-input-field-container").removeClass('hidden');
        $('.add-translation-modal-btn').prop('disabled', false);

        var translation_symptoms = $("#translation_symptoms").val();
        var add_translation_arznei_id = $("#add_translation_arznei_id").val();
        var add_translation_master_id = $("#add_translation_master_id").val();
        var add_translation_quelle_id = $("#add_translation_quelle_id").val();
        var add_translation_language = $("#add_translation_language").val();
        var error_count = 0;

        if(translation_symptoms == ""){
            $("#translation_symptoms").next().html('Please input translated symptoms');
            $("#translation_symptoms").next().addClass('text-danger');
            error_count++;
        }else{
            $("#translation_symptoms").next().html('');
            $("#translation_symptoms").next().removeClass('text-danger');
        }
        if ($('input[name="translation_method"]:checked').length == 0) {
            $("#translation_method_radio_buttons").next().html('Please select translation method');
            $("#translation_method_radio_buttons").next().addClass('text-danger');
            error_count++;
        }else{
            $("#translation_method_radio_buttons").next().html('');
            $("#translation_method_radio_buttons").next().removeClass('text-danger');
        }
        if(add_translation_arznei_id == ""){
            $(".add-translation-global-error-msg").html('Some internal required data not found, please re-load the page and try again.');
            $(".add-translation-global-error-msg").addClass('text-danger');
            error_count++;
        }else{
            $(".add-translation-global-error-msg").html('');
            $(".add-translation-global-error-msg").removeClass('text-danger');
        }
        if(add_translation_master_id == ""){
            $(".add-translation-global-error-msg").html('Some internal required data not found, please re-load the page and try again.');
            $(".add-translation-global-error-msg").addClass('text-danger');
            error_count++;
        }else{
            $(".add-translation-global-error-msg").html('');
            $(".add-translation-global-error-msg").removeClass('text-danger');
        }
        if(add_translation_quelle_id == ""){
            $(".add-translation-global-error-msg").html('Some internal required data not found, please re-load the page and try again.');
            $(".add-translation-global-error-msg").addClass('text-danger');
            error_count++;
        }else{
            $(".add-translation-global-error-msg").html('');
            $(".add-translation-global-error-msg").removeClass('text-danger');
        }
        if(add_translation_language == ""){
            $(".add-translation-global-error-msg").html('Some internal required data not found, please re-load the page and try again.');
            $(".add-translation-global-error-msg").addClass('text-danger');
            error_count++;
        }else{
            $(".add-translation-global-error-msg").html('');
            $(".add-translation-global-error-msg").removeClass('text-danger');
        }

        if(error_count == 0){
            $('.add-translation-modal-btn').prop('disabled', true);
            $("#translation_modal_loader").removeClass('hidden');
            $(".add-translation-input-field-container").addClass('hidden');
            
            // Form data
            var data = $(this).serialize();

            // Checking if all the selected sources symptoms available in selected language
            $.ajax({
                type: 'POST',
                url: 'add-source-translation.php',
                data: {
                    form: data
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        // if(add_translation_language == "de")
                        // 	$("#de_translation_btn_container_"+add_translation_master_id).html('<a id="" title="Symptoms available in German" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');
                        // else
                        // 	$("#en_translation_btn_container_"+add_translation_master_id).html('<a id="" title="Symptoms available in English" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');

                        $("#translationModal").modal('hide');
                        location.reload();
                        
                    }else if(response.status == "need_approval"){
                        $("#translationModal").modal('hide');
                        translationUserApproval(add_translation_master_id, add_translation_quelle_id, add_translation_arznei_id, add_translation_language);
                    }else{
                        var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                        $('#translation_modal_loader').removeClass('hidden');
                        $("#translation_modal_loader .loading-msg").addClass('hidden');
                        $('#translation_modal_loader .error-msg').html(msg);
                        setTimeout(function(){
                            if(!$('#translation_modal_loader').hasClass('hidden')){
                                $('#translation_modal_loader').addClass('hidden');
                                $("#translation_modal_loader .loading-msg").removeClass('hidden');
                                $('#translation_modal_loader .error-msg').html('');
                            }
                            $th.removeClass('processing');
                            $(".add-translation-input-field-container").removeClass('hidden');
                            $('.add-translation-modal-btn').prop('disabled', false);
                            console.log(response);
                        }, 3000);
                        // $th.removeClass('processing');
                        // if(!$('#translation_modal_loader').hasClass('hidden'))
                        // 	$('#translation_modal_loader').addClass('hidden');
                        // $(".add-translation-input-field-container").removeClass('hidden');
                        // $('.add-translation-modal-btn').prop('disabled', false);
                        // console.log(response);
                    }
                }
            }).fail(function (response) {
                $th.removeClass('processing');
                if(!$('#translation_modal_loader').hasClass('hidden'))
                    $('#translation_modal_loader').addClass('hidden');
                $(".add-translation-input-field-container").removeClass('hidden');
                $('.add-translation-modal-btn').prop('disabled', false);
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });

        } else {
            $th.removeClass('processing');
            return false;
        }
    });

    function translationUserApproval(add_translation_master_id, add_translation_quelle_id, add_translation_arznei_id, add_translation_language){
        $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
        $("#translation_user_approval_modal_loader .error-msg").html('');
        if($("#translation_user_approval_modal_loader").hasClass('hidden'))
            $("#translation_user_approval_modal_loader").removeClass('hidden');
        $("#translationUserApprovalModal").modal('show');
        // $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
        // $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
        // $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);

        $.ajax({
            type: 'POST',
            url: 'get-translation-approvable-data.php',
            data: {
                add_translation_master_id: add_translation_master_id,
                add_translation_quelle_id: add_translation_quelle_id,
                add_translation_arznei_id: add_translation_arznei_id,
                add_translation_language: add_translation_language
            },
            dataType: "json",
            success: function( response ) {
                console.log(response);
                $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                if(response.status == "success"){
                    if (typeof(response.result_data) != "undefined" && response.result_data !== null && response.result_data != ""){
                        $("#translation_user_approval_modal_loader").addClass('hidden');
                        var resultData = null;
                        try {
                            resultData = JSON.parse(response.result_data); 
                        } catch (e) {
                            resultData = response.result_data;
                        }
                        var Beschreibung_de = (typeof(resultData.Beschreibung_de) != "undefined" && resultData.Beschreibung_de !== null && resultData.Beschreibung_de != "") ? b64DecodeUnicode(resultData.Beschreibung_de) : "";
                        var Beschreibung_en = (typeof(resultData.Beschreibung_en) != "undefined" && resultData.Beschreibung_en !== null && resultData.Beschreibung_en != "") ? b64DecodeUnicode(resultData.Beschreibung_en) : "";
                        var html = '';
                        html += '<table class="table table-bordered">';
                        html += '	<tr>';
                        html += '		<th class="text-center" style="width:50%">German</th>';
                        html += '		<th class="text-center" style="width:50%">English</th>';
                        html += '	</tr>';
                        html += '	<tr>';
                        html += '		<td>'+Beschreibung_de+'</td>';
                        html += '		<td>'+Beschreibung_en+'</td>';
                        html += '	</tr>';
                        html += '</table>';

                        $("#translation_user_approval_master_id").val(add_translation_master_id);
                        $("#translation_user_approval_arznei_id").val(add_translation_arznei_id);
                        $("#translation_user_approval_quelle_id").val(add_translation_quelle_id);
                        $("#translation_user_approval_language").val(add_translation_language);
                        $("#translation_user_approval_temp_symptom_id").val(resultData.temp_symptom_id);
                        $("#translation_user_approval_content").html(html);

                    } else {
                        var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                        $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                        $("#translation_user_approval_modal_loader .error-msg").html(msg);
                        $('.translation-user-approval-modal-continue-btn').prop('disabled', true);
                    }
                    
                }else{
                    var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                    $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                    $("#translation_user_approval_modal_loader .error-msg").html(msg);
                    $('.translation-user-approval-modal-continue-btn').prop('disabled', true);
                }
            }
        }).fail(function (response) {
            $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
            $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
            $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
            var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
            $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
            $("#translation_user_approval_modal_loader .error-msg").html(msg);
            var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
            $('.translation-user-approval-modal-continue-btn').prop('disabled', true);
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        });
    }

    
    $(document).on('click', '#translation_user_approval_modal_continue_btn', function(e){
        // e.preventDefault();
        // var $th = $(this);
        // if($th.hasClass('processing'))
            // return;
        // $th.addClass('processing');
        $('.translation-user-approval-modal-continue-btn').prop('disabled', true);
        $('.translation-user-approval-modal-delete-btn').prop('disabled', true);
        $('.translation-user-approval-modal-cancel-btn').prop('disabled', true);

        var translation_user_approval_master_id = $("#translation_user_approval_master_id").val();
        var translation_user_approval_arznei_id = $("#translation_user_approval_arznei_id").val();
        var translation_user_approval_quelle_id = $("#translation_user_approval_quelle_id").val();
        var translation_user_approval_language = $("#translation_user_approval_language").val();
        var translation_user_approval_temp_symptom_id = $("#translation_user_approval_temp_symptom_id").val();
        var error_count = 0;

        if(translation_user_approval_master_id == "")
            error_count++;
        if(translation_user_approval_arznei_id == "")
            error_count++;
        if(translation_user_approval_quelle_id == "")
            error_count++;
        if(translation_user_approval_temp_symptom_id == "")
            error_count++;

        if(error_count == 0) {
            $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
            $("#translation_user_approval_modal_loader .error-msg").html('');
            if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                $("#translation_user_approval_modal_loader").removeClass('hidden');

            $.ajax({
                type: 'POST',
                url: 'translation-approvable-actions.php',
                data: {
                    add_translation_master_id: translation_user_approval_master_id,
                    add_translation_quelle_id: translation_user_approval_quelle_id,
                    add_translation_arznei_id: translation_user_approval_arznei_id,
                    add_translation_language: translation_user_approval_language,
                    temp_symptom_id: translation_user_approval_temp_symptom_id,
                    action: 'continue'
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        var resultData = null;
                        try {
                            resultData = JSON.parse(response.result_data); 
                        } catch (e) {
                            resultData = response.result_data;
                        }
                        if (typeof(resultData.need_approval) != "undefined" && resultData.need_approval !== null && resultData.need_approval != ""){
                            // $th.removeClass('processing');
                            translationUserApproval(translation_user_approval_master_id, translation_user_approval_quelle_id, translation_user_approval_arznei_id, translation_user_approval_language)
                        }else{
                            // $th.removeClass('processing');
                            // Removing "Processing" class from add translation icon button to make it working
                            // var $another_th = $("#"+translation_user_approval_language+"_translation_btn_container_"+translation_user_approval_master_id);
                            // $another_th.removeClass('processing'); 

                            $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
                            $("#translation_user_approval_modal_loader .error-msg").html('');
                            if(!$("#translation_user_approval_modal_loader").hasClass('hidden'))
                                $("#translation_user_approval_modal_loader").addClass('hidden');
                            
                            $("#translation_user_approval_master_id").val('');
                            $("#translation_user_approval_arznei_id").val('');
                            $("#translation_user_approval_quelle_id").val('');
                            $("#translation_user_approval_language").val('');
                            $("#translation_user_approval_temp_symptom_id").val('');
                            $("#translation_user_approval_content").html('');
                            
                            if(translation_user_approval_language == "de")
                                $("#de_translation_btn_container_"+translation_user_approval_master_id).html('<a id="" title="Symptoms available in German" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');
                            else
                                $("#en_translation_btn_container_"+translation_user_approval_master_id).html('<a id="" title="Symptoms available in English" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');

                            $("#translationUserApprovalModal").modal('hide');
                            $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                            $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                            $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                        }
                        
                    }else{
                        var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                        if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                            $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                        $("#translation_user_approval_modal_loader .error-msg").html(msg);
                        if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                            $("#translation_user_approval_modal_loader").removeClass('hidden');
                        $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                        $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                        $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                        // $th.removeClass('processing');
                    }
                }
            }).fail(function (response) {
                if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                    $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                $("#translation_user_approval_modal_loader .error-msg").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
                if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                    $("#translation_user_approval_modal_loader").removeClass('hidden');
                $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                // $th.removeClass('processing');
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });

        } else {
            if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
            $("#translation_user_approval_modal_loader .error-msg").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
            if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                $("#translation_user_approval_modal_loader").removeClass('hidden');
            $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
            $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
            $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
            // $th.removeClass('processing');
        }

    });

    $(document).on('click', '#translation_user_approval_modal_delete_btn', function(e){
        
        $('.translation-user-approval-modal-continue-btn').prop('disabled', true);
        $('.translation-user-approval-modal-delete-btn').prop('disabled', true);
        $('.translation-user-approval-modal-cancel-btn').prop('disabled', true);

        var translation_user_approval_master_id = $("#translation_user_approval_master_id").val();
        var translation_user_approval_arznei_id = $("#translation_user_approval_arznei_id").val();
        var translation_user_approval_quelle_id = $("#translation_user_approval_quelle_id").val();
        var translation_user_approval_language = $("#translation_user_approval_language").val();
        var error_count = 0;

        if(translation_user_approval_master_id == "")
            error_count++;
        if(translation_user_approval_arznei_id == "")
            error_count++;
        if(translation_user_approval_quelle_id == "")
            error_count++;

        if(error_count == 0) {
            $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
            $("#translation_user_approval_modal_loader .error-msg").html('');
            if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                $("#translation_user_approval_modal_loader").removeClass('hidden');

            $.ajax({
                type: 'POST',
                url: 'translation-approvable-actions.php',
                data: {
                    add_translation_master_id: translation_user_approval_master_id,
                    add_translation_quelle_id: translation_user_approval_quelle_id,
                    add_translation_arznei_id: translation_user_approval_arznei_id,
                    add_translation_language: translation_user_approval_language,
                    action: 'delete'
                },
                dataType: "json",
                success: function( response ) {
                    console.log(response);
                    if(response.status == "success"){
                        
                        // Removing "Processing" class from add translation icon button to make it working
                        // var $th = $("#"+translation_user_approval_language+"_translation_btn_container_"+translation_user_approval_master_id);
                        // $th.removeClass('processing'); 

                        $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
                        $("#translation_user_approval_modal_loader .error-msg").html('');
                        if(!$("#translation_user_approval_modal_loader").hasClass('hidden'))
                            $("#translation_user_approval_modal_loader").addClass('hidden');
                        
                        $("#translation_user_approval_master_id").val('');
                        $("#translation_user_approval_arznei_id").val('');
                        $("#translation_user_approval_quelle_id").val('');
                        $("#translation_user_approval_language").val('');
                        $("#translation_user_approval_temp_symptom_id").val('');
                        $("#translation_user_approval_content").html('');
                        
                        $("#translationUserApprovalModal").modal('hide');
                        
                    }else{
                        var msg = (typeof(response.message) != "undefined" && response.message !== null && response.message != "") ? response.message : "Something went wrong, Please reload the page and try again."; 
                        if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                            $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                        $("#translation_user_approval_modal_loader .error-msg").html(msg);
                        if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                            $("#translation_user_approval_modal_loader").removeClass('hidden');
                        $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                        $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                        $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                        // $th.removeClass('processing');
                    }
                }
            }).fail(function (response) {
                if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                    $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
                $("#translation_user_approval_modal_loader .error-msg").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
                if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                    $("#translation_user_approval_modal_loader").removeClass('hidden');
                $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
                $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
                $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
                // $th.removeClass('processing');
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });

        } else {
            if(!$("#translation_user_approval_modal_loader .loading-msg").hasClass('hidden'))
                $("#translation_user_approval_modal_loader .loading-msg").addClass('hidden');
            $("#translation_user_approval_modal_loader .error-msg").html('<p class="text-center">Something went wrong, Please reload the page and try again!</p>');
            if($("#translation_user_approval_modal_loader").hasClass('hidden'))
                $("#translation_user_approval_modal_loader").removeClass('hidden');
            $('.translation-user-approval-modal-continue-btn').prop('disabled', false);
            $('.translation-user-approval-modal-delete-btn').prop('disabled', false);
            $('.translation-user-approval-modal-cancel-btn').prop('disabled', false);
            // $th.removeClass('processing');
        }
        
    });

    $(document).on('click', '.translation-user-approval-modal-cancel-btn', function(){
        var translation_user_approval_master_id = $("#translation_user_approval_master_id").val();
        var translation_user_approval_arznei_id = $("#translation_user_approval_arznei_id").val();
        var translation_user_approval_language = $("#translation_user_approval_language").val();
        // Removing "Processing" class from add translation icon button to make it working
        // var $th = $("#"+translation_user_approval_language+"_translation_btn_container_"+translation_user_approval_master_id);
        // $th.removeClass('processing'); 

        $("#translation_user_approval_modal_loader .loading-msg").removeClass('hidden');
        $("#translation_user_approval_modal_loader .error-msg").html('');
        if(!$("#translation_user_approval_modal_loader").hasClass('hidden'))
            $("#translation_user_approval_modal_loader").addClass('hidden');
        
        $("#translation_user_approval_master_id").val('');
        $("#translation_user_approval_arznei_id").val('');
        $("#translation_user_approval_quelle_id").val('');
        $("#translation_user_approval_language").val('');
        $("#translation_user_approval_temp_symptom_id").val('');
        $("#translation_user_approval_content").html('');
        
        $("#translationUserApprovalModal").modal('hide');
    });
</script>
<?php
    if($showPopup == 1){ 
?>
    <script type="text/javascript">
        $(".bs-example-modal-lg").modal('show');
    </script>
<?php
    } 
?>
<script type="text/javascript">
	$("#addMoreRemedySet").click(function(e) {
	    e.preventDefault();
	    var html = '';
	    html += '<div class="col-sm-6">';
	    html += '	<label>Remedy title</label>';
	    html += '	<input type="text" name="remedy_title[]" placegolder="Titel" class="form-control" autocomplete="off">';
	    html += '</div>';
	    html += '<div class="col-sm-6">';
	    html += '	<label>Abbreviations (separate with "|")</label>';
	    html += '	<input type="text" name="remedy_abbreviations[]" placegolder="Abbreviations" class="form-control" autocomplete="off">';
	    html += '</div>';
	    $("#addRemedyCnr").append(html);
	});
</script>