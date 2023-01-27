<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* This is the main comparison page here we compares one initial source with other comparing source(s) 
	*/
?>
<?php  
	$savedComparisonId = (isset($_GET['scid']) AND $_GET['scid'] != "") ? $_GET['scid'] : null;
	$savedComparisonQuelleId = "";
	$comparisonName = null;
	$arzneiId = null;
	$initialSourceId = null;
	$comparingSourceIds = array();
	$savedComparisonComparingSourceIdsCommaSeparated = "";
	$similarityRate = 20;
	$comparisonOption = 1;
	$comparisonLanguage = "";
	if($savedComparisonId != ""){
		$scResult = mysqli_query($db,"SELECT SC.*, Q.is_materia_medica FROM saved_comparisons AS SC LEFT JOIN quelle AS Q ON SC.quelle_id = Q.quelle_id WHERE SC.id = '".$savedComparisonId."'");
		if(mysqli_num_rows($scResult) > 0) {
			$scRow = mysqli_fetch_assoc($scResult);
			if($scRow['is_materia_medica'] == 0)
			{
				header("Location: ".$baseUrl."materia-medica.php");
				die();
			}

			$savedComparisonQuelleId = $scRow['quelle_id'];	
			$comparisonName = trim($scRow['comparison_name']);
			$arzneiId = $scRow['arznei_id'];
			$initialSourceId = $scRow['initial_source_id'];
			$comparingSourceIds = (!empty($scRow['comparing_source_ids'])) ? explode(',', $scRow['comparing_source_ids']) : array();
			$savedComparisonComparingSourceIdsCommaSeparated = (!empty($scRow['comparing_source_ids'])) ? $scRow['comparing_source_ids'] : "";
			$similarityRate = $scRow['similarity_rate'];
			$comparisonOption = $scRow['comparison_option'];
			$comparisonLanguage = $scRow['comparison_language'];
		} else {
			header("Location: ".$baseUrl."materia-medica.php");
			die();
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Comparison</title>
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
	<!-- <form id="symptom_comparison_form" name="symptom_comparison_form" action="" method="POST"> -->
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<a href="<?php echo $baseUrl ?>comparison.php" title="Comparison" class="btn btn-default" role="button">Comparison</a>
				<a href="<?php echo $baseUrl ?>materia-medica.php" title="Materia Medica"  class="btn btn-default" role="button">Materia Medica</a>
				<a href="<?php echo $baseUrl ?>comparison-sources.php" title="Backups" class="btn btn-default" role="button">Backups</a>
				<div class="spacer"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="fancy-collapse-panel">
	                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	                    <div class="panel panel-default">
	                        <div class="panel-heading" role="tab" id="headingOne">
	                            <h4 class="panel-title">
	                                <a class="collapsed" data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">SEARCH</a>
	                            </h4>
	                        </div>
	                        <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
		                        <form id="symptom_search_form" name="symptom_search_form" action="" method="POST">
		                            <div id="search_container" class="panel-body unclickable">
		                                <!-- Search fields Strat -->
		                            	<div class="row">
											<div class="col-sm-8">
												<div class="form-group Text_form_group">
													<label class="search-label">Search</label>
													<input type="text" name="search_keyword" id="search_keyword" class="form-control" placeholder="Search for words or part of words, from one or multiple sources">
													<span class="error-text"></span>
												</div>
											</div>
											<div class="col-sm-4"></div>
										</div>
										<div class="row">
											<div class="col-sm-8">
												<div class="form-group Text_form_group">
													<label class="search-sources-label">Source(s)</label>
													<select class="form-control" name="search_sources[]" id="search_sources" multiple="multiple" data-placeholder="Select one or multiple sources">
											   			<option value="">Select</option>
											   			<?php 
											   				$html = ''; 
											   				$htmlComparisons = '<optgroup label="Comparisons">';
											   				$htmlSingleSources = '<optgroup label="Single sources">';
											   				$htmlComparisonsInner = '';
															$htmlSingleSourcesInner = '';
															
												   			$quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle.is_materia_medica = 1 ORDER BY quelle.quelle_type_id ASC");
															while($quelleRow = mysqli_fetch_array($quelleResult)){
																$quelleSymptomResultSearch = mysqli_query($db,"SELECT id FROM quelle_import_test WHERE quelle_id = '".$quelleRow['quelle_id']."'");
					    										if(mysqli_num_rows($quelleSymptomResultSearch) > 0){
					    											$quellen_value = $quelleRow['code'];
																	if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
																	if($quelleRow['code'] != $quelleRow['titel'])
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
																	if($quelleRow['quelle_type_id'] == 3)
																		$htmlComparisonsInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
																	else
																		$htmlSingleSourcesInner .= '<option value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>'; 
					    										}
															}
															if($htmlComparisonsInner == '')
																$htmlComparisons .= '<option value="" disabled="disabled">None</option>';
															else
																$htmlComparisons .= $htmlComparisonsInner;
															if($htmlSingleSourcesInner == '')
																$htmlSingleSources .= '<option value="" disabled="disabled">None</option>';
															else
																$htmlSingleSources .= $htmlSingleSourcesInner;
															
															$htmlSingleSources .= '</optgroup>';
															$htmlComparisons .= '</optgroup>';
															$html .= $htmlComparisons.$htmlSingleSources;
															echo $html;
												   		?>
											   		</select>
											   		<span class="error-text"></span>
												</div>
											</div>
											<div class="col-sm-4"></div>
										</div>
										<div class="form-group">
											<div class="spacer15"></div>
											<button type="submit" id="search_submit_btn" class="btn btn-custom-green" type="button">SEARCH</button>
										</div>
										<!-- Search fields End -->
		                            </div>
		                        </form>
	                        </div>
	                    </div>
	                    <div class="panel panel-default">
	                        <div class="panel-heading" role="tab" id="headingTwo">
	                            <h4 class="panel-title">
	                                <a data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">SOURCE COMPARISON</a>
	                            </h4>
	                        </div>
	                        <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
		                        <form id="symptom_comparison_form" name="symptom_comparison_form" action="" method="POST">	
		                            <div id="comparison_container" class="panel-body unclickable">
		                            	<!-- Source comparison fields Strat -->
		                            	<div class="row">
											<div class="col-sm-4">
												<div class="form-group Text_form_group">
													<label class="similarity-rate">Matched percentage(cut-off)<span class="required">*</span></label>
												   	<select class="form-control save-data" name="similarity_rate" id="similarity_rate">
												   		<?php
												   			$i=0;
												   			While($i <= 100)
															{
																?>
																<option <?php echo ($i == $similarityRate) ? 'selected' : ''; ?> value="<?php echo $i; ?>"><?php echo $i." %"; ?></option>
																<?php
															  $i = $i+5;
															}
												   		?>
												   	</select>
												   	<span class="error-text"></span>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group Text_form_group">
													<label class="language-label">Language<span class="required">*</span></label>
												   	<select class="form-control save-data" name="comparison_language" id="comparison_language">
												   		<option value="">Select</option>
												   		<option <?php echo (isset($comparisonLanguage) AND $comparisonLanguage == 'de') ? 'selected' : ''; ?> value="de">German</option>
												   		<option <?php echo (isset($comparisonLanguage) AND $comparisonLanguage == 'en') ? 'selected' : ''; ?> value="en">English</option>
												   	</select>	
												   	<span class="error-text"></span>
												</div>
											</div>
											<div class="col-sm-4">
												<div class="form-group Text_form_group">
													<label class="stop-word-label">Stop words</label>
													<div><a title="Stop words" target="_blank" href="<?php echo $baseUrl."stop-words.php"; ?>">Click here</a> to see the active stop words</div>
													<span class="error-text"></span>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-6">
												<div class="form-group Text_form_group">
													<label class="control-label">Arznei<span class="required">*</span></label>
												   	<select <?php if($savedComparisonId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="arznei_id" id="arznei_id">
												   		<option value="">Select</option>
												   		<?php
															$arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei");
															while($arzneiRow = mysqli_fetch_array($arzneiResult)){
																$selected = ($arzneiRow['arznei_id'] == $arzneiId) ? 'selected' : '';
																echo '<option '.$selected.' value="'.$arzneiRow['arznei_id'].'">'.$arzneiRow['titel'].'</option>';
															}
														?>
												   	</select>
												   	<span class="error-text"></span>	
												</div>
											</div>
											<div class="col-sm-6">
												<div class="form-group Text_form_group">
													<label class="comparing-option-label">Comparison option</label>
													<select class="form-control save-data" name="comparison_option" id="comparison_option">
														<option value="1" <?php echo ($comparisonOption == 1) ? 'selected' : ''; ?>>Compare only symptoms</option>
														<option value="2" <?php echo ($comparisonOption == 2) ? 'selected' : ''; ?>>Compare whole symptom text</option>
													</select>
													<span class="error-text"></span>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-6">
												<div class="form-group Text_form_group">
													<label class="control-label">Initial source<span class="required">*</span></label>
													<div id="initial_source_cnr">
													   	<select <?php if($savedComparisonId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="initial_source" id="initial_source">
													   		<option value="">Select</option>
													   	</select>
													   	<span class="error-text"></span>
													</div>	
												</div>
											</div>
											<div class="col-sm-6">
												<div class="form-group Text_form_group">
													<label class="control-label">Comparing source(s)<span class="required">*</span></label>
													<div id="comparing_source_cnr">
														<select <?php if($savedComparisonId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">
												   			<option value="">Select</option>
												   		</select>
												   		<span class="error-text"></span>
													</div>
												</div>
											</div>
										</div>
										
										<div class="form-group">
											<div class="spacer15"></div>
											<input type="hidden" name="comparison_name" id="comparison_name" value="<?php echo $comparisonName; ?>" class="form-control save-data" placeholder="Name of the comparison">
											<input type="hidden" name="scid" id="scid" value="<?php echo $savedComparisonId; ?>">
											<input type="hidden" name="saved_arznei_id" id="saved_arznei_id" value="<?php echo $arzneiId; ?>">
											<input type="hidden" name="saved_initial_source_id" id="saved_initial_source_id" value="<?php echo $initialSourceId; ?>">
											<input type="hidden" name="saved_comparison_comparing_source_ids_comma_separated" id="saved_comparison_comparing_source_ids_comma_separated" value="<?php echo $savedComparisonComparingSourceIdsCommaSeparated; ?>">
											<input type="hidden" name="saved_comparison_quelle_id" id="saved_comparison_quelle_id" value="<?php echo $savedComparisonQuelleId; ?>">
											<button type="submit" id="compare_submit_btn" class="btn btn-custom-green" >COMPARE</button>
										</div>
		                            	<!-- Source comparison fields end -->
		                            </div>
		                        </form>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <div id="loader" class="form-group text-center">
					Loading is not complete please wait <img src="assets/img/loader.gif" alt="Loading...">
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row"> 
			<div class="col-sm-12">
				<div id="comparison_result_cnr" class="master-table-cnr">
					<div class="row"> 
					    <div class="col-sm-12">
					        <ul class="head-panel-before-comparison-table">
					            <li id="totalNumberDisplay">Total No. of records: <span id="numberOfRecord">0</span></li>
					            <li class="pull-right">
					            	<ul class="head-panel-sub-ul hidden">
					            		<li>
					            			<label class="checkbox-inline">Open translations</label>
					            			<label class="checkbox-inline">
		      									<input class="show-all-translation" name="show_all_translation" id="show_all_translation" type="checkbox" value="1">All
		    								</label>
					            		</li>
					            		<li>
					            			<label class="checkbox-inline">
		      									<input class="show-all-initial-translation" name="show_all_initial_translation" id="show_all_initial_translation" type="checkbox" value="1">Initial
		    								</label>
					            		</li>
					            		<li>
					            			<label class="checkbox-inline">
		      									<input class="show-all-comparative-translation" name="show_all_comparative_translation" id="show_all_comparative_translation" type="checkbox" value="1">Comparative
		    								</label>
					            		</li>
					            		<li>|</li>
					            		<li>
					            			<label class="checkbox-inline">
		      									<input class="show-all-connections" name="show_all_connections" id="show_all_connections" type="checkbox" value="1">Connections
		    								</label>
					            		</li>
					            		<li><a class="btn btn-custom-black result-sub-btn hidden" type="button">SAVE</a></li>
					            	</ul>
					            	
								</li>
					        </ul>
					        <div class="spacer"></div>
					    </div>
					</div>
				  	<table id="resultTable" class="table table-bordered heading-table append-recognizer">
					    <thead class="heading-table-bg">
					      	<tr>
					      		<th style="width: 12%;" class="text-center">Source</th>
						        <th id="column_heading_symptom">Symptom</th>
						        <th style="width: 5%;" class="text-center">%</th>
						        <th style="width: 17%;" class="linkage-column comparison-only-column">INFO & LINKAGE</th>
						        <th style="width: 19%;" class="comparison-only-column">Command</th>
					      	</tr>
					    </thead>
					    <tbody>
							<tr class="no-records-found">
								<td colspan="5" class="text-center">No records found</td>
							</tr>
					    </tbody>
					</table>
					<button class="btn btn-custom-black result-sub-btn bottom hidden" type="button">SAVE</button>
				</div>
			</div>  
		</div>
	</div>

	<!-- Including Modals html START -->
	<?php include 'includes/comparison-page-modals.php'; ?>
	<!-- Including Modals html END -->

	<!-- </form> -->
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
	<script src="assets/js/comparison-load-more.js"></script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>