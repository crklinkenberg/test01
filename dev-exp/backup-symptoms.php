<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Displaying the symptoms and their all related data in raw format of a particular source(For backup sets) 
	*/
?>
<?php
	$masterId = (isset($_GET['mid']) AND $_GET['mid'] != "") ? $_GET['mid'] : ""; 
	if($masterId == ""){
		header('Location: '.$baseUrl);
		exit();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Symptoms</title>
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
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12 symptoms-container">
				<?php
					$quellen_value = "";
					$quelleResult = mysqli_query($db,"SELECT quelle_backup.quelle_id, quelle_backup.code, quelle_backup.titel, quelle_backup.jahr, quelle_backup.band, quelle_backup.nummer, quelle_backup.auflage, quelle_backup.quelle_type_id, quelle_backup.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle_backup JOIN quelle_import_master_backup ON quelle_backup.quelle_id = quelle_import_master_backup.quelle_id LEFT JOIN quelle_autor ON quelle_backup.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle_import_master_backup.id = '".$masterId."' ORDER BY quelle_backup.quelle_type_id ASC");
					if(mysqli_num_rows($quelleResult) > 0){
						$quelleRow = mysqli_fetch_assoc($quelleResult);

						$quellen_value = $quelleRow['code'];
						if($quelleRow['quelle_type_id'] != 3){
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
						}
						if(!empty($quelleRow['titel']))
							$quellen_value = $quelleRow['titel'];
						else
							$quellen_value = rtrim(trim($quellen_value),",");
					}
				?>
				<h2>Symptoms of Source:  <?php echo $quellen_value; ?></h2>    
				<div class="spacer"></div>      
				<ul class="head-checkbox-panel-before-table">
            		<li><label>Open translations </label></li>
            		<li>
            			<label class="checkbox-inline">
								<input class="show-all-translation" name="show_all_translation" id="show_all_translation" type="checkbox" value="1">All
						</label>
            		</li>
		        </ul>  
				<div class="">          
				  	<table class="table full-symptom-details-table table-bordered table-sticky-head">
					    <thead>
					      	<tr>
						        <th style="width:2%;">Symp- tom No (@N)</th>
						        <th style="width:2%;">Page (@S)</th>
						        <th style="width: 8%;">Imported symptom</th>
						        <th style="width: 8%;">Original symptom</th>
						        <th style="width: 8%;">Converted symptom</th>
						        <!-- <th style="width: 8%;">Converted symptom full</th> -->
						        <th style="width: 8%;">Graduation (@G)</th>
						        <th style="width: 4%;">Bracketed part</th>
						        <th style="width: 3%;">Time (@Z)</th>
						        <th style="width: 3%;">Prover (@P)</th>
						        <th style="width: 4%;">Reference (@L)</th>
						        <th>Remedy(@A)</th>
						        <th>Symptom Of Different Remedy(@AT/@TA)</th>
						        <th>Footnote (@F)</th>
						        <th>Symptom edit comment</th>
						        <th>Hint (@V)</th>
						        <th>Chapter (@K)</th>
						        <th>Comment (@C)</th> <!-- Kommentar -->
						        <th>Ambiguities (@U)</th> <!-- Unklarheiten -->
					      	</tr>
					    </thead>
					    <tbody>
					    	<?php  
								$result = mysqli_query($db,"SELECT * FROM quelle_import_backup WHERE master_id = '".$masterId."' ORDER BY id ASC");
								while($row = mysqli_fetch_array($result)){   
									$originSourceYear = "";
									$originSourceLanguage = "";
									$originQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$row['original_quelle_id']."'");
									if(mysqli_num_rows($originQuelleResult) > 0){
										$originQuelleRow = mysqli_fetch_assoc($originQuelleResult);
										$originSourceYear = $originQuelleRow['jahr'];
										if($originQuelleRow['sprache'] == "deutsch")
											$originSourceLanguage = "de";
										else if($originQuelleRow['sprache'] == "englisch") 
											$originSourceLanguage = "en";
									}
									if($originSourceLanguage != ""){
										$importedSymptom = ""; 
										$originalSymptom = ""; 
										$originalSymptom_de = ($row['BeschreibungOriginal_de'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_de'], $row['original_quelle_id'], $row['arznei_id']) : ""; 
										$originalSymptom_en = ($row['BeschreibungOriginal_en'] != "") ? convertSymptomToOriginal($row['BeschreibungOriginal_en'], $row['original_quelle_id'], $row['arznei_id']) : ""; 

										$convertedSymptom = ""; 
										$convertedSymptomFull = ""; 
										// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
										// convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0)
										$convertedSymptom_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 
										$convertedSymptom_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 

										$convertedSymptomFull_de = ($row['BeschreibungFull_de'] != "") ? convertTheSymptom($row['BeschreibungFull_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 
										$convertedSymptomFull_en = ($row['BeschreibungFull_en'] != "") ? convertTheSymptom($row['BeschreibungFull_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 0, $row['id'], $row['original_symptom_id']) : ""; 

										$bracketedPart = "";
										$time = "";

										$graduation = "";
										// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
										// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
										// convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1)
										$graduation_de = ($row['searchable_text_de'] != "") ? convertTheSymptom($row['searchable_text_de'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : ""; 
										$graduation_en = ($row['searchable_text_en'] != "") ? convertTheSymptom($row['searchable_text_en'], $row['original_quelle_id'], $row['arznei_id'], 0, 1, $row['id'], $row['original_symptom_id']) : "";

										if($originSourceLanguage == "en"){
											$importedSymptom = ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$row['Beschreibung_en'].'</div>' : "";
											$importedSymptom .= ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$row['Beschreibung_de'].'</div>' : "";

											$originalSymptom = ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$originalSymptom_en.'</div>' : "";
											$originalSymptom .= ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$originalSymptom_de.'</div>' : "";

											$convertedSymptom = ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$convertedSymptom_en.'</div>' : "";
											$convertedSymptom .= ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$convertedSymptom_de.'</div>' : "";

											$convertedSymptomFull = ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$convertedSymptomFull_en.'</div>' : "";
											$convertedSymptomFull .= ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$convertedSymptomFull_de.'</div>' : "";

											$bracketedPart = $row['bracketedString_en'];

											$time = $row['timeString_en'];

											$graduation = ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$graduation_en.'</div>' : "";
											$graduation .= ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$graduation_de.'</div>' : "";
										} else {
											$importedSymptom = ($row['Beschreibung_de'] != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$row['Beschreibung_de'].'</div>' : "";
											$importedSymptom .= ($row['Beschreibung_en'] != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$row['Beschreibung_en'].'</div>' : "";

											$originalSymptom = ($originalSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$originalSymptom_de.'</div>' : "";
											$originalSymptom .= ($originalSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$originalSymptom_en.'</div>' : "";

											$convertedSymptom = ($convertedSymptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$convertedSymptom_de.'</div>' : "";
											$convertedSymptom .= ($convertedSymptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$convertedSymptom_en.'</div>' : "";

											$convertedSymptomFull = ($convertedSymptomFull_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$convertedSymptomFull_de.'</div>' : "";
											$convertedSymptomFull .= ($convertedSymptomFull_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$convertedSymptomFull_en.'</div>' : "";

											$bracketedPart = $row['bracketedString_de'];

											$time = $row['timeString_de'];

											$graduation = ($graduation_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$graduation_de.'</div>' : "";
											$graduation .= ($graduation_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$graduation_en.'</div>' : "";
										}
									?>
										<tr>
											<td><?=$row['Symptomnummer']?></td>
											<td>
												<?php
													if($row['SeiteOriginalVon'] == $row['SeiteOriginalBis'])
														echo $row['SeiteOriginalVon'];
													else
														echo $row['SeiteOriginalVon']."-".$row['SeiteOriginalBis']
												?>
											</td>
											<td><?php echo $importedSymptom; ?></td>
											<td><?php echo $originalSymptom; ?></td>
											<!-- <td><?php //echo $convertedSymptom; ?></td> -->
											<td><?php echo $convertedSymptomFull; ?></td>
											<?php /*<td><?=$row['Graduierung']?></td>*/ ?>
											<td><?php echo $graduation; ?></td>
											<td><?php echo $bracketedPart; ?></td>
											<td><?php echo $time;?></td>
											<td>
												<?php
													$pruStr = "";
													$prueferResult = mysqli_query($db,"SELECT pruefer.pruefer_id, pruefer.suchname, pruefer.vorname, pruefer.nachname FROM symptom_pruefer_backup JOIN pruefer ON symptom_pruefer_backup.pruefer_id	= pruefer.pruefer_id WHERE symptom_pruefer_backup.symptom_id = '".$row['id']."'");
													while($prueferRow = mysqli_fetch_array($prueferResult)){
														if($prueferRow['suchname'] != "")
															$pruStr .= $prueferRow['suchname'].", ";
														else
															$pruStr .= $prueferRow['vorname']." ".$prueferRow['nachname'].", ";
													}
													$pruStr =rtrim($pruStr, ", ");
													echo $pruStr;
												?>
											</td>
											<td><?php echo trim(str_replace('No Author,', '', $row['EntnommenAus'])); ?></td>
											<td><?=$row['Remedy']?></td>
											<td><?=($row['symptom_of_different_remedy'] != "" AND $row['symptom_of_different_remedy'] != "null") ? $row['symptom_of_different_remedy'] : ""?></td>
											<td><?=$row['Fussnote']?></td>
											<td><?=($row['symptom_edit_comment'] != "" AND $row['symptom_edit_comment'] != "null") ? $row['symptom_edit_comment'] : ""?></td>
											<td><?=$row['Verweiss']?></td>
											<td><?=$row['BereichID']?></td>
											<td><?=$row['Kommentar']?></td>
											<td><?=$row['Unklarheiten']?></td>
										</tr>
									<?php
									}
								}
					    	?>
					    </tbody>
				  	</table>
				</div>
			</div>
		</div>
				
	</div>
	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.min.js"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.config.js"></script>
	<script type="text/javascript" src="plugins/tinymce/tinymce.min.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script>
		$('body').on( 'change', '#show_all_translation', function(e) {
			var action = "";
			var is_data_found = 0;

			if($(this).prop("checked") == true) {
				action = "check";
			}else{
				action = "uncheck";
			}

			if(action == "check"){
				$(".table-symptom-hidden").removeClass('hidden');
				$('.table-original-symptom').addClass('table-original-symptom-bg');
			}else if(action == "uncheck"){
				$(".table-symptom-hidden").addClass('hidden');
				$('.table-original-symptom').removeClass('table-original-symptom-bg');
			}

		});
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>