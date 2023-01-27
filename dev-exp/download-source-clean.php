<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Exporting or Downloading Quelle/Souce
	*/
	$htmlContent = "";
	$fileName = "Source-document.docx";
	// (A) LOAD PHPWORD
	require "../vendor/autoload.php";
	// Initialize class 
	$pw = new \PhpOffice\PhpWord\PhpWord();

	if(isset($_POST['submit_hidden'])){
		// $result = mysqli_query($db,"SELECT * FROM quelle_import_test where master_id = 2591 ORDER BY id ASC");
		// $result = mysqli_query($db,"SELECT * FROM quelle_import_test where quelle_id = 11 ORDER BY id ASC");
		// while($row = mysqli_fetch_array($result)){
		// 	$symptom = (isset($row['Beschreibung_en']) AND $row['Beschreibung_en'] != "") ? $row['Beschreibung_en'] : ""; 
		// 	if($symptom != "") 
		// 		$htmlContent .= "<p>".htmlspecialchars($symptom)."</p>";


		// $result = mysqli_query($db,"SELECT * FROM temporary_downloadable_source_bk ORDER BY id ASC");
		//  $i = 0;
		// while($row = mysqli_fetch_array($result)){
		// 	$symptom = (isset($row['symptom']) AND $row['symptom'] != "") ? $row['symptom'] : "";
		// 	// $i++;
		// 	// if($i == 52){
		// 		// Headache sometimes in l. side, &lt; by walking in open air.
		// 		// Headache sometimes in l. side, &lt; by walking in open air.
		// 		$symp = formatSymptomForDownload($symptom);
		// 		// echo htmlentities($symp); exit;
		// 		// $htmlContent .= '<p>Headache sometimes in l. side, &lt; by walking in open air.</p>';
		// 		$htmlContent .= '<p>'.$symp.'</p>';
		// 	// }
			
		// 	// if($i == 52)
		// 	// 	break;
		// }
		// // exit;
		
		// if($htmlContent != ""){
		// 	// (B) ADD HTML CONTENT
		// 	$section = $pw->addSection();
		// 	\PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);

		// 	// (C) SAVE TO DOCX ON SERVER
		// 	// $pw->save($fileName, "Word2007");

		// 	// (D) OR FORCE DOWNLOAD
		// 	header("Content-Type: application/octet-stream");
		// 	header("Content-Disposition: attachment;filename=\"".$fileName."\"");
		// 	$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($pw, "Word2007");
		// 	$objWriter->save("php://output");
		// }
		// exit;

		// // (B) ADD HTML CONTENT
		
		// $htmlContent .= '<p>Line One</p>';
		// $htmlContent .= '<p>Line Two</p>';

		// $section = $pw->addSection();
		// \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);

		// // (C) SAVE TO DOCX ON SERVER
		// // $pw->save($fileName, "Word2007");

		// // (D) OR FORCE DOWNLOAD
		// header("Content-Type: application/octet-stream");
		// header("Content-Disposition: attachment;filename=\"".$fileName."\"");
		// $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($pw, "Word2007");
		// $objWriter->save("php://output");
		// exit;

		$CleanedText = str_replace ( '</em><em>', '', $_POST['symptomtext'] );
		$CleanedText = str_replace ( array (
			"\r",
			"\t" 
		), '', $CleanedText );
		$CleanedText = trim ( $CleanedText );
		$Lines = explode ( "\n", $CleanedText );
		if (count ( $Lines ) > 0) {
			$rownum = 1;
			$Beschreibung = '';
			$Graduierung='';
			$BereichID='';
			$SeiteOriginalVon = '';
			$SeiteOriginalBis = '';
			$Symptomnummer = 1;
			$aLiteraturquellen = array ();
			$Fussnote='';
			$Verweiss = '';
			$Unklarheiten = '';
			$Kommentar = '';
			$prueferFromParray = array ();
			$htmlContent = "";
			$fileName = "Source-document.docx";
			// $deleteResult = mysqli_query($db,"DELETE FROM temporary_downloadable_source WHERE 0");
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

				$insertingSymptom = "";
				$NewSymptomNr = 0;
				$line = trim ($line);
				$cleanline = strip_tags($line);
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

				$code = '';
				$param = '';

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
							$insertingSymptom = '@K:'.$param;
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
				} else {
					$insertingSymptom = $line;
				}
				if($insertingSymptom != ""){
					// $BeschreibungAsItIs = str_replace ( array (
					// 	'<ss>',
					// 	'</ss>' 
					// ), array (
					// 	"<span class=\"text-sperrschrift\">",
					// 	"</span>" 
					// ), $insertingSymptom );
					// $BeschreibungAsItIs = str_replace ( array (
					// 	'<clr',
					// 	'</clr>' 
					// ), array (
					// 	"<span",
					// 	"</span>" 
					// ), $BeschreibungAsItIs );
					// As it is symptom text
					// $Beschreibung = htmlspecialchars($BeschreibungAsItIs);
					// $Beschreibung = $insertingSymptom;
					// $symp = mysqli_real_escape_string($db, $Beschreibung);
					// $masterQuery="INSERT INTO temporary_downloadable_source (symptom) VALUES (NULLIF('".$symp."', ''))";
			  //       $db->query($masterQuery);
					$symp = formatSymptomForDownload($insertingSymptom);
					// echo htmlentities($symp); exit;
					$htmlContent .= '<p>'.$symp.'</p>';
				}
			}

			// $result = mysqli_query($db,"SELECT * FROM temporary_downloadable_source ORDER BY id ASC");
			// while($row = mysqli_fetch_array($result)){
			// 	$symptom = (isset($row['symptom']) AND $row['symptom'] != "") ? $row['symptom'] : "";
			// 	$symp = formatSymptomForDownload($symptom);
			// 	// echo htmlentities($symp); exit;
			// 	$htmlContent .= '<p>'.$symp.'</p>';
			// }
			// exit;
			
			if($htmlContent != ""){
				// (B) ADD HTML CONTENT
				$section = $pw->addSection();
				\PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);

				// (C) SAVE TO DOCX ON SERVER
				// $pw->save($fileName, "Word2007");

				// (D) OR FORCE DOWNLOAD
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment;filename=\"".$fileName."\"");
				$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($pw, "Word2007");
				$objWriter->save("php://output");
			}			
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Import And Download Source</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Font Awesome -->
  	<link rel="stylesheet" href="plugins/font-awesome/css/fontawesome-all.min.css">
  	<!-- Select2 -->
  	<link rel="stylesheet" href="plugins/select2/dist/css/select2.min.css">
  	<!-- custom -->
  	<link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
	<!-- Top navigation bar -->
	<?php include 'includes/top-navbar.php'; ?>
	<!-- Top navigation bar -->
	
	<div class="container">
		<form id="source_import_form" name="source_import_form" action="" method="POST">
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group Text_form_group">
						<label class="control-label">Text Editor<span class="required">*</span></label>
					   	<textarea id="symptomtext" name="symptomtext" class="texteditor" aria-hidden="true"></textarea>	
					   	<span class="error-text"></span>
					</div>
				</div>
			</div>	
			<div class="form-group text-center">
				<!-- <input type="submit" name="submit" class="btn btn-success" value="Submit"> -->
				<input type="hidden" name="submit_hidden" value="Submit">
				<button class="btn btn-success" type="button" onclick="importSource()">Download In Word File</button>
				<!-- <input type="button" onclick="chck()" name="submit" class="btn btn-success" value="Submit"> -->
			</div>
		</form>
	</div>

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

	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.min.js"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.config.js"></script>
	<script type="text/javascript" src="plugins/tinymce/tinymce.min.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script src="assets/js/common.js"></script>
	<script type="text/javascript">
		function importSource(){
			var symptomtext = $("#symptomtext").val();
			var error_count = 0;

			if(symptomtext == ""){
				$("#symptomtext").addClass('text-danger');
				$("#symptomtext").next().html('Can not be empty.');
				$("#symptomtext").next().addClass('text-danger');
				error_count++;
			}else{
				$("#symptomtext").removeClass('text-danger');
				$("#symptomtext").next().html('');
				$("#symptomtext").next().removeClass('text-danger');
			}

			if(error_count == 0){
				$("#source_import_form").submit();
			}else{
				$('html, body').animate({
		            scrollTop: $("#source_import_form").offset().top
		        }, 1000);
				return false;
			}
		}
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>