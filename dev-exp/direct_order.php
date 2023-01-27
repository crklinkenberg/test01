<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Performing the Direct Order provided by user from the direct order form which appears on the quelle import process or performing any other action provided by user e.g. Later, Skip, etc. 
	*/
?>
<?php
/*
* Error meanings
*
* popup_error = 1
* Meaning = No Direct order found Or worng Direct Order.
* Message = No Direct Order found Or There is something wrong in provided Direct Order.
*
* popup_error = 2
* Meaning = It may go wrong if program correct this symptom
* Message = It may go wrong or program may messed up this symptom Data. Please correct this symptom manually and import again.
*
* popup_error = 5
* Meaning = MySQl Transacation error
* Message = Something went wrong Could not save the data. Please retry! 
*
*/


if(isset($_POST['submit']) AND $_POST['submit'] == "Submit"){
	$parameterString = "";

	if(isset($_POST['direct_order']) AND $_POST['direct_order'] != ""){

		/* Working on the provided Direct Oreder START */
		$cleanedText = str_replace ( '</em><em>', '', $_POST['direct_order'] );	
		$cleanedText = str_replace ( array (
			"\r",
			"\t" 
		), '', $cleanedText );
		$cleanedText = trim ( $cleanedText );
		$lines = explode ( "\n", $cleanedText );
		if (count ( $lines ) > 0) {
			
			$rownum = 1;
			$Beschreibung = '';
			$Graduierung='';
			$BereichID='';
			$Symptomnummer = 1;
			$SeiteOriginalVon = '';
			$SeiteOriginalBis = '';
			$aLiteraturquellen = array ();
			$EntnommenAus='';
			$Fussnote='';
			$Verweiss = '';
			$Unklarheiten = '';
			$Kommentar = '';
			$prueferFromParray = array ();
			$remedyFromRarray = array ();
			$remedyArray = array();
			$partOfSymptomText ='';
			$timeString = '';
			$subChapter = '';
			$subSubChapter = '';
			$break = false;
			$needApproval = 0;
			$prueferArray = array();
			$referenceArray = array();

			$error_status = 0;
			$symptomUpdateQueryArray = array();
			$canProceed = 0;

			foreach ( $lines as $iline => $line ) {

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
				$NewSymptomNr = 0;
				$line = trim ( $line );

				$cleanline = trim ( str_replace ( array (
					'&nbsp;' 
				), array (
					' '
				), strip_tags ( $line ) ) );

				if (empty ( $cleanline )) {
					$rownum ++;
					continue;
				}
				
				if (mb_strlen ( $cleanline ) < 3) {
					$rownum ++;
					continue;
				}
				$firstChar = mb_substr ( $cleanline, 0, 1 );
				$lastChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 1 );
				$lastTwoChar = mb_substr ( $cleanline, mb_strlen ( $cleanline ) - 2 );
				$code='';
				$param='';

				if($firstChar == '@'){
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
							$Graduierung=mysqli_real_escape_string($db, $Graduierung);
							if($Graduierung != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Graduierung = '".$Graduierung."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// Kapitel, setzt in DS "KapitelID"
						// case 'B' :
						case 'K' :
							$BereichID = $param;
							$BereichID=mysqli_real_escape_string($db, $BereichID);
							if($BereichID != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET BereichID = '".$BereichID."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// Seite, setzt in DS "Seite"
						case 'S' :
							$tmp = explode ( '-', $param );
							$SeiteOriginalVon = $tmp [0] + 0;
							if (sizeof ( $tmp ) > 1)
								$SeiteOriginalBis = $tmp [1] + 0;
							else
								$SeiteOriginalBis = $SeiteOriginalVon;

							if(($SeiteOriginalVon != "" AND $SeiteOriginalVon != 0) OR ($SeiteOriginalBis !="" AND $SeiteOriginalBis != 0)){
								$SeiteOriginalVon=mysqli_real_escape_string($db, $SeiteOriginalVon);
								$SeiteOriginalBis=mysqli_real_escape_string($db, $SeiteOriginalBis);
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET SeiteOriginalVon = '".$SeiteOriginalVon."', SeiteOriginalBis = '".$SeiteOriginalBis."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// Symptom-Nr., setzt in DS "Symptomnummer"
						case 'N' :
							$NewSymptomNr = $param + 0;
							if($NewSymptomNr != 0){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Symptomnummer = '".$NewSymptomNr."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// Literaturquelle, setzt in DS "EntnommenAus"
						case 'L' :
							$canProceed = 1;
							$referenceString = trim($param);
							$referenceReturnArr = lookupLiteratureReference($referenceString);
							if(isset($referenceReturnArr['need_approval']) AND $referenceReturnArr['need_approval'] == 1){
								$fullReferenceInArray = explode(",", $referenceString);
								if(count($fullReferenceInArray) >= 2){
									$referenceAutor = trim($fullReferenceInArray[0]);
					        		array_shift($fullReferenceInArray);
					        		$referenceTxt = rtrim(implode(",", $fullReferenceInArray), ",");
								}else{
									$referenceAutor = "";
									$referenceTxt = $referenceString;
								}

								$dataArr = array();
								$dataArr['reference_id'] = null;
								$dataArr['full_reference'] = $referenceString;
								$dataArr['autor'] = $referenceAutor;
								$dataArr['reference'] = $referenceTxt;
								// custom_in_array(needle, needle_field, array)
								if(custom_in_array($dataArr['full_reference'], 'full_reference', $referenceArray) != true){
									$referenceArray[] = $dataArr;
									$aLiteraturquellen [] = $referenceString;
								}
							}
							else{
								if(isset($referenceReturnArr['data'][0]) AND !empty($referenceReturnArr['data'][0])){
									if(isset($referenceReturnArr['data'][0]['reference_id']) AND $referenceReturnArr['data'][0]['reference_id'] != "")
									{
										// custom_in_array(needle, needle_field, array)
										if(custom_in_array($referenceReturnArr['data'][0]['reference_id'], 'reference_id', $referenceArray) != true){
											$referenceArray[] = $referenceReturnArr['data'][0];
											$aLiteraturquellen [] = $referenceString;
										}
									}
								}
							}
							break;
						// Fußnote
						case 'F' :
							$Fussnote = $param;
							$Fussnote=mysqli_real_escape_string($db, $Fussnote);
							if($Fussnote != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Fussnote = '".$Fussnote."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// Verweiss
						case 'V' :
							$Verweiss = $param;
							$Verweiss=mysqli_real_escape_string($db, $Verweiss);
							if($Verweiss != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Verweiss = '".$Verweiss."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// @U: (Unklarheit, steht wie auch @F und @L VOR dem einen Symptom, welches betroffen ist)
						case 'U' :
							$Unklarheiten = $param;
							$Unklarheiten=mysqli_real_escape_string($db, $Unklarheiten);
							if($Unklarheiten != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Unklarheiten = '".$Unklarheiten."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// @C: (Kommentar, steht wie auch @F und @L VOR dem einen Symptom, welches betroffen ist)
						case 'C' :
							$Kommentar = $param;
							$Kommentar=mysqli_real_escape_string($db, $Kommentar);
							if($Kommentar != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Kommentar = '".$Kommentar."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// @P: Prüfer als Kürzel
						case 'P' :
							$canProceed = 1;
							$prueferString = trim($param);
							// checking for comma's existance and taking approptiate action (e.g. W. E. Wislicenus, in einemAufsatze.)
							if (mb_strpos($prueferString, ',') !== false){
								$separator = ",";
								$commaFirstOccurrence = mb_stripos ( $prueferString, $separator );
								$beforeTheCommaString = trim( mb_substr ( $prueferString, 0, $commaFirstOccurrence ) );
								$prueferString = $beforeTheCommaString;
							}
							$cleanPrueferString = (mb_substr ( $prueferString, mb_strlen ( $prueferString ) - 1, 1 ) == '.') ? $prueferString : $prueferString.'.'; 
							$prueferReturnArr = lookupPruefer($cleanPrueferString);
							if(isset($prueferReturnArr['need_approval']) AND $prueferReturnArr['need_approval'] == 1){
								$dataArr = array();
								$dataArr['pruefer_id'] = null;
								$dataArr['kuerzel'] = $prueferString;
								$dataArr['suchname'] = $prueferString;
								// custom_in_array(needle, needle_field, array)
								if(custom_in_array($dataArr['suchname'], 'suchname', $prueferArray) != true)
									$prueferArray[] = $dataArr;
							}
							else{
								if(isset($prueferReturnArr['data'][0]) AND !empty($prueferReturnArr['data'][0])){
									if(isset($prueferReturnArr['data'][0]['pruefer_id']) AND $prueferReturnArr['data'][0]['pruefer_id'] != ""){
										// custom_in_array(needle, needle_field, array)
										if(custom_in_array($prueferReturnArr['data'][0]['pruefer_id'], 'pruefer_id', $prueferArray) != true)
											$prueferArray[] = $prueferReturnArr['data'][0];
									}
								}
							}
							
							break;
						// @A: Remedy
						case 'A' :
							$canProceed = 1;
							$remedyString = trim($param);
							$cleanRemedyString = (mb_substr ( $remedyString, mb_strlen ( $remedyString ) - 1, 1 ) == '.') ? $remedyString : $remedyString.'.';
							$remedyReturnArr = lookupRemedy($cleanRemedyString);
							if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
								$dataArr = array();
								$dataArr['remedy_id'] = null;
								$dataArr['name'] = $remedyString;
								// custom_in_array(needle, needle_field, array)
								if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
									$remedyArray[] = $dataArr;
							}
							else{
								if(isset($remedyReturnArr['data'][0]) AND !empty($remedyReturnArr['data'][0])){
									if(isset($remedyReturnArr['data'][0]['remedy_id']) AND $remedyReturnArr['data'][0]['remedy_id'] != ""){
										// custom_in_array(needle, needle_field, array)
										if(custom_in_array($remedyReturnArr['data'][0]['remedy_id'], 'remedy_id', $remedyArray) != true)
											$remedyArray[] = $remedyReturnArr['data'][0];
									}	
								}
							}
							
							break;
						// @AT: Similar remedy, Similar symptom text (e.g. Opi., during the day)
						case 'AT' :
							if (mb_strpos($param, ',') !== false){
								$separator = ",";
								$symptomOfDifferentRemedy = trim($param);
								$commaFirstOccurrence = mb_stripos ( $param, $separator );
								$beforeTheCommaString = trim( mb_substr ( $param, 0, $commaFirstOccurrence ) );
								$afterTheCommaString = trim( ltrim( mb_substr ( $param, $commaFirstOccurrence ), $separator ));

								$similarRemedy = $beforeTheCommaString;
								$similarSymptom = $afterTheCommaString;
								if($similarRemedy != "" AND $similarSymptom != ""){
									// Inserting remedy in Remedy table if it is not already there
									$cleanRemedyString = (mb_substr ( $similarRemedy, mb_strlen ( $similarRemedy ) - 1, 1 ) == '.') ? $similarRemedy : $similarRemedy.'.';
									$remedyReturnArr = lookupRemedy($cleanRemedyString);
									if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
										$dataArr = array();
										$dataArr['remedy_id'] = null;
										$dataArr['name'] = $remedyString;
										// custom_in_array(needle, needle_field, array)
										if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
											$remedyArray[] = $dataArr;
									}
									else{
										if(isset($remedyReturnArr['data'][0]) AND !empty($remedyReturnArr['data'][0])){
											if(isset($remedyReturnArr['data'][0]['remedy_id']) AND $remedyReturnArr['data'][0]['remedy_id'] != ""){
												// custom_in_array(needle, needle_field, array)
												if(custom_in_array($remedyReturnArr['data'][0]['remedy_id'], 'remedy_id', $remedyArray) != true)
													$remedyArray[] = $remedyReturnArr['data'][0];
											}	
										}
									}

									$symptomOfDifferentRemedy = mysqli_real_escape_string($db, $symptomOfDifferentRemedy);
									$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET symptom_of_different_remedy = '".$symptomOfDifferentRemedy."' WHERE id = '".$_POST['symptom_id']."'";
									$canProceed = 1;
								}
								else
									$error_status = 1; 
							}else
								$error_status = 1; 
							
							break;
						// @TA: Similar remedy, Similar symptom text (e.g. small boils in crops, Sulph.)
						case 'TA' :
							if (mb_strpos($param, ',') !== false){
								$separator = ",";
								$symptomOfDifferentRemedy = trim($param);
								$commaFirstOccurrence = mb_stripos ( $param, $separator );
								$beforeTheCommaString = trim( mb_substr ( $param, 0, $commaFirstOccurrence ) );
								$afterTheCommaString = trim( ltrim( mb_substr ( $param, $commaFirstOccurrence ), $separator ));

								$similarRemedy = $afterTheCommaString;
								$similarSymptom = $beforeTheCommaString;
								if($similarRemedy != "" AND $similarSymptom != ""){
									// Inserting remedy in Remedy table if it is not already there
									$cleanRemedyString = (mb_substr ( $similarRemedy, mb_strlen ( $similarRemedy ) - 1, 1 ) == '.') ? $similarRemedy : $similarRemedy.'.';
									$remedyReturnArr = lookupRemedy($cleanRemedyString);
									if(isset($remedyReturnArr['need_approval']) AND $remedyReturnArr['need_approval'] == 1){
										$dataArr = array();
										$dataArr['remedy_id'] = null;
										$dataArr['name'] = $remedyString;
										// custom_in_array(needle, needle_field, array)
										if(custom_in_array($dataArr['name'], 'name', $remedyArray) != true)
											$remedyArray[] = $dataArr;
									}
									else{
										if(isset($remedyReturnArr['data'][0]) AND !empty($remedyReturnArr['data'][0])){
											if(isset($remedyReturnArr['data'][0]['remedy_id']) AND $remedyReturnArr['data'][0]['remedy_id'] != ""){
												// custom_in_array(needle, needle_field, array)
												if(custom_in_array($remedyReturnArr['data'][0]['remedy_id'], 'remedy_id', $remedyArray) != true)
													$remedyArray[] = $remedyReturnArr['data'][0];
											}	
										}
									}

									$symptomOfDifferentRemedy = mysqli_real_escape_string($db, $symptomOfDifferentRemedy);
									$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET symptom_of_different_remedy = '".$symptomOfDifferentRemedy."' WHERE id = '".$_POST['symptom_id']."'";
									$canProceed = 1;
								}
								else
									$error_status = 1; 
							}else
								$error_status = 1; 
							
							break;
						// @T: text/symptom text
						case 'T' :
							$canProceed = 1;
							$partOfSymptomText = $param;
							$symptomTextResult = mysqli_query($db, "SELECT Beschreibung, BeschreibungOriginal, BeschreibungPlain, searchable_text, bracketedString, approval_string FROM temp_quelle_import_test where id = '".$_POST['symptom_id']."'");
							if(mysqli_num_rows($symptomTextResult) > 0){
								$symptomTextData = mysqli_fetch_assoc($symptomTextResult);
								$Beschreibung = $symptomTextData['Beschreibung'];
								$BeschreibungOriginal = $symptomTextData['BeschreibungOriginal'];
								$BeschreibungPlain = $symptomTextData['BeschreibungPlain'];
								$searchableText = $symptomTextData['searchable_text'];
								$bracketedString = $symptomTextData['bracketedString'];
								
								// $approval_string = $symptomTextData['approval_string'];
								if($_POST['approval_for'] == 1)
									$workingApprovalString = base64_decode($_POST['middle_bracket_approval_string']);
								else
									$workingApprovalString = base64_decode($_POST['approval_string']);

								if(mb_substr_count($Beschreibung, $workingApprovalString) > 1)
									$error_status = 2;
								if(mb_substr_count($BeschreibungOriginal, $workingApprovalString) > 1)
									$error_status = 2;
								if(mb_substr_count($BeschreibungPlain, $workingApprovalString) > 1)
									$error_status = 2;
								if(mb_substr_count($bracketedString, $workingApprovalString) > 1)
									$error_status = 2;

								if($error_status != 2){
									if (mb_strpos($Beschreibung, $workingApprovalString) !== false)
										$Beschreibung = str_replace($workingApprovalString, $partOfSymptomText, $Beschreibung);
									else
										$Beschreibung = $Beschreibung." (".$partOfSymptomText.")";
									if (mb_strpos($BeschreibungOriginal, $workingApprovalString) !== false)
										$BeschreibungOriginal = str_replace($workingApprovalString, $partOfSymptomText, $BeschreibungOriginal);
									else
										$BeschreibungOriginal = $BeschreibungOriginal." (".$partOfSymptomText.")";
									if (mb_strpos($BeschreibungPlain, $workingApprovalString) !== false)
										$BeschreibungPlain = str_replace($workingApprovalString, $partOfSymptomText, $BeschreibungPlain);
									else
										$BeschreibungPlain = $BeschreibungPlain." (".$partOfSymptomText.")";
									if (mb_strpos($searchableText, $workingApprovalString) !== false)
										$searchableText = str_replace($workingApprovalString, $partOfSymptomText, $searchableText);
									else
										$searchableText = $searchableText." (".$partOfSymptomText.")";

									$bracketedString = ($bracketedString != "") ? str_replace($workingApprovalString, $partOfSymptomText, $bracketedString) : $partOfSymptomText;

									$Beschreibung = mysqli_real_escape_string($db, $Beschreibung);
									$BeschreibungOriginal = mysqli_real_escape_string($db, $BeschreibungOriginal);
									$BeschreibungPlain = mysqli_real_escape_string($db, $BeschreibungPlain);
									$searchableText = mysqli_real_escape_string($db, $searchableText);
									$bracketedString = mysqli_real_escape_string($db, $bracketedString);
									$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET Beschreibung = '".$Beschreibung."', BeschreibungOriginal = '".$BeschreibungOriginal."', BeschreibungPlain = '".$BeschreibungPlain."', searchable_text = '".$searchableText."', bracketedString = '".$bracketedString."'  WHERE id = '".$_POST['symptom_id']."'";	
								}

							}
							break;
						// @Z:time
						case 'Z' :
							$timeString = $param;
							$timeString=mysqli_real_escape_string($db, $timeString);
							if($timeString != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET timeString = '".$timeString."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// @UK:subchapter
						case 'UK' :
							$subChapter = $param;
							$subChapter=mysqli_real_escape_string($db, $subChapter);
							if($subChapter != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET subChapter = '".$subChapter."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						// @UUK:subsubchapter
						case 'UUK' :
							$subSubChapter = $param;
							$subSubChapter=mysqli_real_escape_string($db, $subSubChapter);
							if($subSubChapter != ""){
								$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET subSubChapter = '".$subSubChapter."' WHERE id = '".$_POST['symptom_id']."'";
								$canProceed = 1;
							}
							break;
						
						default :
							$break = true;
							break;

					}
				}

			}

			if($canProceed == 0)
				$error_status = 1;


			/* After collecting each lines Direct Order Here the Whole Data storing START */
			switch ($error_status) {
				case '1':
					if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
						$parameterString = "?master=".$_POST['master_id']."&popup_error=1";

					header('Location: '.$baseUrl.$parameterString);
					exit();
					break;
				case '2':
					if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
						$parameterString = "?master=".$_POST['master_id']."&popup_error=2";

					header('Location: '.$baseUrl.$parameterString);
					exit();
					break;
				
				default:

					try {
					    // First of all, let's begin a transaction
					    $db->begin_transaction();

					    /*
					    * Cleaning Previous related data if there(Should not be there. Doing this just for Sureity) 
					    */
						$deleteTempRemedyQuery="DELETE FROM temp_remedy WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempRemedyQuery);

						$deleteTempPrueferQuery = "DELETE FROM temp_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempPrueferQuery);

						$deleteTempSymptomPrueferQuery = "DELETE FROM temp_symptom_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempSymptomPrueferQuery);

						$deleteTempReferenceQuery = "DELETE FROM temp_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempReferenceQuery);

						$deleteTempSymptomReferenceQuery = "DELETE FROM temp_symptom_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempSymptomReferenceQuery);

						// Deleting Temp Approved Reference
						$deleteTempApprovedReferenceQuery="DELETE FROM temp_approved_reference WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempApprovedReferenceQuery);

						// Deleting Temp Approved Pruefer
						$deleteTempApprovedPrueferQuery="DELETE FROM temp_approved_pruefer WHERE symptom_id = '".$_POST['symptom_id']."'";
						$db->query($deleteTempApprovedPrueferQuery);
						/*
						* Cleaning End
						*/

					    /* Pruefer Start */
		            	if(!empty($prueferArray)){
	            			foreach ($prueferArray as $pruKey => $pruVal) {
	            				if(isset($prueferArray[$pruKey]['pruefer_id']) AND $prueferArray[$pruKey]['pruefer_id'] != ""){
	            					$newPrueferId = $prueferArray[$pruKey]['pruefer_id'];
	            				}else{
	            					$prueferArray[$pruKey]['kuerzel'] = mysqli_real_escape_string($db, $prueferArray[$pruKey]['kuerzel']);
	            					$prueferArray[$pruKey]['suchname'] = mysqli_real_escape_string($db, $prueferArray[$pruKey]['suchname']);
	            					$prueferQuery = "INSERT INTO pruefer (kuerzel, suchname, ersteller_datum) VALUES (NULLIF('".$prueferArray[$pruKey]['kuerzel']."', ''), NULLIF('".$prueferArray[$pruKey]['suchname']."', ''), '".$date."')";
									$db->query($prueferQuery);
						        	$newPrueferId = mysqli_insert_id($db);
	            				}

	            				$symptomPrueferQuery = "INSERT INTO temp_symptom_pruefer (symptom_id, pruefer_id) VALUES ('".$_POST['symptom_id']."', '".$newPrueferId."')";
						        $db->query($symptomPrueferQuery);

						        // When a symptom needs no approval than storing it's pruefer details in temp_approved_pruefer for using in a. a. O. search process
						        $approval_string = base64_decode($_POST['approval_string']);
						        $tempApprovedPrueferQuery = "INSERT INTO temp_approved_pruefer (master_id, symptom_id, pruefer_id, approval_string) VALUES ('".$_POST['master_id']."', '".$_POST['symptom_id']."', '".$newPrueferId."', NULLIF('".$approval_string."', ''))";
						        $db->query($tempApprovedPrueferQuery);
	            			}
		            	}
		            	/* Pruefer End */
		            	/* Remedy Start */
		            	if(!empty($remedyArray)){
		            		$remedyString = "";
	            			foreach ($remedyArray as $remdKey => $remdVal) {
	            				if(!isset($remedyArray[$remdKey]['remedy_id']) OR $remedyArray[$remdKey]['remedy_id'] == ""){
	            					$remedyArray[$remdKey]['name'] = mysqli_real_escape_string($db, $remedyArray[$remdKey]['name']);
	            					$remedyQuery = "INSERT INTO remedy (name, ersteller_datum) VALUES ('".$remedyArray[$remdKey]['name']."', '".$date."')";
									$db->query($remedyQuery);
	            				}

	            				$remedyString.=$remedyArray[$remdKey]['name'].", ";
	            			}
	            			$remedyString = rtrim($remedyString, ", ");
	            			if($remedyString != ""){
	            				$remedyString = mysqli_real_escape_string($db, $remedyString);
		            			$symptomUpdateQuery="UPDATE temp_quelle_import_test SET Remedy = '".$remedyString."' WHERE id = '".$_POST['symptom_id']."'";
								$db->query($symptomUpdateQuery);
							}
		            	}
		            	/* Remedy End */

		            	/* Literaturquellen data */
		            	if(!empty($referenceArray)){
	            			foreach ($referenceArray as $refKey => $refVal) {
	            				if(isset($referenceArray[$refKey]['reference_id']) AND $referenceArray[$refKey]['reference_id'] != ""){
	            					$newReferenceId = $referenceArray[$refKey]['reference_id'];
	            				}else{
	            					$referenceArray[$refKey]['full_reference'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['full_reference']);
	            					$referenceArray[$refKey]['autor'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['autor']);
	            					$referenceArray[$refKey]['reference'] = mysqli_real_escape_string($db, $referenceArray[$refKey]['reference']);
	            					$referenceInsertQuery = "INSERT INTO reference (full_reference, autor, reference, ersteller_datum) VALUES (NULLIF('".$referenceArray[$refKey]['full_reference']."', ''), NULLIF('".$referenceArray[$refKey]['autor']."', ''), NULLIF('".$referenceArray[$refKey]['reference']."', ''), '".$date."')";
									$db->query($referenceInsertQuery);
									$newReferenceId = mysqli_insert_id($db);
	            				}

								$referenceQuery = "INSERT INTO temp_symptom_reference (symptom_id, reference_id) VALUES ('".$_POST['symptom_id']."', '".$newReferenceId."')";
	    						$db->query($referenceQuery);

	    						// When a symptom needs no approval than storing it's reference details in temp_approved_reference for using in a. a. O. search process
	    						$approval_string = base64_decode($_POST['approval_string']);
	    						$tempApprovedReferenceQuery = "INSERT INTO temp_approved_reference (master_id, symptom_id, reference_id, approval_string) VALUES ('".$_POST['master_id']."', '".$_POST['symptom_id']."', '".$newReferenceId."', NULLIF('".$approval_string."', ''))";
	    						$db->query($tempApprovedReferenceQuery);
	            			}
	            			if( count($aLiteraturquellen) > 0 ){
								$EntnommenAus = join ( "\n", $aLiteraturquellen );
								$EntnommenAus=mysqli_real_escape_string($db, $EntnommenAus);
								if($EntnommenAus != "")
									$symptomUpdateQueryArray [] = "UPDATE temp_quelle_import_test SET EntnommenAus = '".$EntnommenAus."' WHERE id = '".$_POST['symptom_id']."'";
							}
		            	}
						

						/* Run all the collected Direct Order Queries */
						if(!empty($symptomUpdateQueryArray)){
							foreach ($symptomUpdateQueryArray as $queryKey => $queryVal) {
								$db->query($queryVal);		
							}
						}

						/* Finally Approving the Symptom By Direct Order */
						$finalSymptomUpdateQuery="UPDATE temp_quelle_import_test SET part_of_symptom_priority = 0, remedy_priority = 0, pruefer_priority = 0, reference_with_no_author_priority = 0, remedy_with_symptom_priority = 0, more_than_one_tag_string_priority = 0, aao_hyphen_priority = 0, hyphen_pruefer_priority = 0, hyphen_reference_priority = 0, reference_priority = 0, direct_order_priority = 0, need_approval = 0 WHERE id = '".$_POST['symptom_id']."'";
						$db->query($finalSymptomUpdateQuery);

						// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
						$unSkippedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
						$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
						if( $unSkippedRowCount > 0){
							if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
								$parameterString = "?master=".$_POST['master_id'];
						}else{
							// In this case the rediraction URL will be - Location: $baseUrl 
							$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
							$db->query($makeUnskippedQuery);

							$leftToApproveResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
							$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
							if( $leftToApproveRowCount == 0){
								if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
									$parameterString = "?master=".$_POST['master_id'];
							}
						}

						// If we arrive here, it means that no exception was thrown
					    // i.e. no query has failed, and we can commit the transaction
					    $db->commit();

						header('Location: '.$baseUrl.$parameterString);
						exit();
					    
					} catch (Exception $e) {
					    // An exception has been thrown
					    // We must rollback the transaction
					    $db->rollback();

					    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
							$parameterString = "?master=".$_POST['master_id']."&popup_error=5";

						header('Location: '.$baseUrl.$parameterString);
						exit();
					}
					
					break;
			}
			/* After collecting each lines Direct Order Here the Whole Data storing END */
			

		}else{
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id']."&popup_error=1";

			header('Location: '.$baseUrl.$parameterString);
			exit();
		}
		/* Working on the provided Direct Oreder END */

	}else{
		if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id']."&popup_error=1";

		header('Location: '.$baseUrl.$parameterString);
		exit();
	}
	
}else if(isset($_POST['later']) AND $_POST['later'] == "Later"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET is_skipped = 1, is_rechecked = 0 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

		// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
		$unSkippedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
		$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
		if( $unSkippedRowCount > 0){
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id'];
		}else{
			// In this case the rediraction URL will be - Location: $baseUrl 
			$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
			$db->query($makeUnskippedQuery);

			$leftToApproveResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
			$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
			if( $leftToApproveRowCount == 0){
				if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
			}
		}

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}else if(isset($_POST['reset_current']) AND $_POST['reset_current'] == "Reset"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    $parameterString = "";
	    if($_POST['is_pre_defined_tags_approval'] == 1){
	    	$unApprovedResult = mysqli_query($db, "SELECT * FROM temp_quelle_import_test WHERE id = '".$_POST['symptom_id']."'");
			if( mysqli_num_rows($unApprovedResult) > 0){
				$unApprovedRow = mysqli_fetch_assoc($unApprovedResult);

				$tagParameter = "";
				if($unApprovedRow['pruefer_priority'] != 0)
					$tagParameter = "pruefer";
				else if($unApprovedRow['reference_priority'] != 0)
					$tagParameter = "reference";
				else if($unApprovedRow['more_than_one_tag_string_priority'] != 0)
					$tagParameter = "multitag";

				if($_POST['approval_for'] == 1)
					$workingApprovalString = $_POST['middle_bracket_approval_string'];
				else
					$workingApprovalString =(isset($_POST['full_approval_string_when_hyphen_unchanged']) AND $_POST['full_approval_string_when_hyphen_unchanged'] != "") ? $_POST['full_approval_string_when_hyphen_unchanged'] : $_POST['approval_string'];
				ruleReimplementation($_POST['symptom_id'], $workingApprovalString, $_POST['master_id'], $_POST['approval_for'], $_POST['is_pre_defined_tags_approval'], $tagParameter);
			}
	    }
	    else{
	    	repopulateDataOnSymptomEditOrReset($_POST['symptom_id'], $_POST['master_id'], $_POST['full_symptom_string'], NULL);
	    }
	    	

		// If there is no more unskipped and unapproved data left than we will make the skipped data as unskipped.
		$unSkippedResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test Where need_approval = 1 AND is_skipped = 0 AND master_id = '".$_POST['master_id']."'");
		$unSkippedRowCount = mysqli_num_rows($unSkippedResult);
		if( $unSkippedRowCount > 0){
			if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
				$parameterString = "?master=".$_POST['master_id'];
		}else{
			// In this case the rediraction URL will be - Location: $baseUrl 
			$makeUnskippedQuery="UPDATE temp_quelle_import_test SET is_skipped = 0 WHERE master_id = '".$_POST['master_id']."'";
			$db->query($makeUnskippedQuery);

			$leftToApproveResult = mysqli_query($db, "SELECT id FROM temp_quelle_import_test Where need_approval = 1 AND master_id = '".$_POST['master_id']."'");
			$leftToApproveRowCount = mysqli_num_rows($leftToApproveResult);
			if( $leftToApproveRowCount == 0){
				if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
					$parameterString = "?master=".$_POST['master_id'];
			}
		}

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}else if(isset($_POST['edit_symptom']) AND $_POST['edit_symptom'] == "Edit"){
	try {
	    // First of all, let's begin a transaction
	    $db->begin_transaction();

	    $parameterString = "";
		$symptomUpdateQuery="UPDATE temp_quelle_import_test SET symptom_edit_priority = 12 WHERE id = '".$_POST['symptom_id']."'";
		$db->query($symptomUpdateQuery);

	    // If we arrive here, it means that no exception was thrown
	    // i.e. no query has failed, and we can commit the transaction
	    $db->commit();

	    if(isset($_POST['master_id']) AND $_POST['master_id'] != "")
			$parameterString = "?master=".$_POST['master_id'];

		header('Location: '.$baseUrl.$parameterString);
		exit();
	} catch (Exception $e) {
	    // An exception has been thrown
	    // We must rollback the transaction
	    $db->rollback();
	    header('Location: '.$baseUrl.'?error=1');
		exit();
	}
}
?>