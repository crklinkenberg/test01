<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Displaying a comparison backup set in comparison format.
	*/
?>
<?php  
	$scId = (isset($_GET['scid']) AND $_GET['scid'] != "") ? $_GET['scid'] : null;
	$savedComparisonQuelleId = "";
	$comparisonName = null;
	$arzneiId = null;
	$initialSourceId = null;
	$comparingSourceIds = array();
	$savedComparisonComparingSourceIdsCommaSeparated = "";
	$similarityRate = 20;
	$comparisonOption = 1;
	$comparisonLanguage = "";
	if($scId != ""){
		$scResult = mysqli_query($db,"SELECT SC.*, Q.is_materia_medica FROM saved_comparisons_backup AS SC LEFT JOIN quelle AS Q ON SC.quelle_id = Q.quelle_id WHERE SC.id = '".$scId."'");
		if(mysqli_num_rows($scResult) > 0) {
			$scRow = mysqli_fetch_assoc($scResult);
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
			header("Location: ".$baseUrl."comparison-sources.php");
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
												   	<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="arznei_id" id="arznei_id">
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
													   	<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="initial_source" id="initial_source">
													   		<option value="">Select</option>
													   		<?php
													   			if($arzneiId != ""){
														   			$html = ''; 
														   			$htmlComparisons = '<optgroup label="Comparisons">';
														   			$htmlSingleSources = '<optgroup label="Single sources">';
																	$htmlSingleSourcesInner = '';
																	$htmlComparisonsInner = '';
														   			$quelleArzneiResult = mysqli_query($db,"SELECT AQ.quelle_id, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM arznei_quelle as AQ LEFT JOIN quelle as Q ON AQ.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE Q.quelle_id = '".$initialSourceId."' AND AQ.arznei_id = '".$arzneiId."' GROUP BY AQ.quelle_id ORDER BY Q.jahr ASC");
																	while($quelleRow = mysqli_fetch_array($quelleArzneiResult)){
																		
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
																		$selected = ($initialSourceId == $quelleRow['quelle_id']) ? 'selected' : '';
																		
																		if($quelleRow['quelle_type_id'] == 3)
																			$htmlComparisonsInner .= '<option '.$selected.' value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
																		else
																			$htmlSingleSourcesInner .= '<option '.$selected.' value="'.$quelleRow['quelle_id'].'">'.$quellen_value.'</option>';
																			
																	}

																	if($htmlComparisonsInner == '')
																		$htmlComparisons .= '<option value="" disabled="disabled">None</option>';
																	else
																		$htmlComparisons .= $htmlComparisonsInner;
																	if($htmlSingleSourcesInner == '')
																		$htmlSingleSources .= '<option value="" disabled="disabled">None</option>';
																	else
																		$htmlSingleSources .= $htmlSingleSourcesInner;

																	$htmlComparisons .= '</optgroup>';
																	$htmlSingleSources .= '</optgroup>';
																	
																	$html .= $htmlComparisons.$htmlSingleSources;
																	echo $html;
																}
													   		?>
													   	</select>
													   	<span class="error-text"></span>
													</div>	
												</div>
											</div>
											<div class="col-sm-6">
												<div class="form-group Text_form_group">
													<label class="control-label">Comparing source(s)<span class="required">*</span></label>
													<div id="comparing_source_cnr">
														<select <?php if($scId != ""){ ?> readonly <?php } ?> class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">
												   			<option value="">Select</option>
												   			<?php 
												   				if($arzneiId != ""){
													   				$html = ''; 
													   				$htmlComparisons = '<optgroup label="Comparisons">';
													   				$htmlSingleSources = '<optgroup label="Single sources">';
																	$htmlSingleSourcesInner = '';
																	$htmlComparisonsInner = '';
																	// fetching compared source ids which are related to the selected initial source id.
																	$allComparedSourcers = array();
																	array_push($allComparedSourcers, $initialSourceId);
																	if(!empty($allComparedSourcers)){
																		$returnedIds = getAllComparedSourceIds($allComparedSourcers);
																		if(!empty($returnedIds)){
																			foreach ($returnedIds as $IdVal) {
																				if(!in_array($IdVal, $allComparedSourcers))
																					array_push($allComparedSourcers, $IdVal);
																			}
																		}	
																	}

																	$comparingSourceIdsCommaSeparat = implode(',', $comparingSourceIds);
														   			$quelleArzneiResult = mysqli_query($db,"SELECT AQ.quelle_id, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM arznei_quelle as AQ LEFT JOIN quelle as Q ON AQ.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE FIND_IN_SET(Q.quelle_id, '".$comparingSourceIdsCommaSeparat."') AND AQ.arznei_id = '".$arzneiId."' GROUP BY AQ.quelle_id ORDER BY Q.jahr ASC");
																	while($quelleRow = mysqli_fetch_array($quelleArzneiResult)){
																		$disabledHtml = '';
																		$is_disabled = 0;
																		if(in_array($quelleRow['quelle_id'], $allComparedSourcers)){
																			$is_disabled = 1;
																		}else{
																			$sourceIdsToSend = array();
																			$getComparedSourcesQuery = $db->query("SELECT initial_source_id, comparing_source_ids FROM saved_comparisons WHERE quelle_id = ".$quelleRow['quelle_id']);
																			if($getComparedSourcesQuery->num_rows > 0){
																				$comparedSourcesData = mysqli_fetch_assoc($getComparedSourcesQuery);
																				$initialSourceInSave = (isset($comparedSourcesData['initial_source_id']) AND $comparedSourcesData['initial_source_id'] != "") ? trim($comparedSourcesData['initial_source_id']) : null;
																				$comparingSourcesInSave = (isset($comparedSourcesData['comparing_source_ids']) AND $comparedSourcesData['comparing_source_ids'] != "") ? explode(',', $comparedSourcesData['comparing_source_ids']) : array();
																				if(in_array($initialSourceInSave, $allComparedSourcers))
																					$is_disabled = 1;
																				array_push($sourceIdsToSend, $initialSourceInSave);

																				foreach ($comparingSourcesInSave as $cSourceKey => $cSourceVal) {
																					if(in_array($cSourceVal, $allComparedSourcers))
																						$is_disabled = 1;
																					array_push($sourceIdsToSend, $cSourceVal);
																				}

																				$newComparedSourcesIds = array();
																				if($is_disabled == 0 AND !empty($sourceIdsToSend)){
																					//$sourceIdsToSend = array(296, 2);
																					$returnedIds = getAllComparedSourceIds($sourceIdsToSend);
																					if(!empty($returnedIds)){
																						foreach ($returnedIds as $IdVal) {
																							if(!in_array($IdVal, $newComparedSourcesIds))
																								array_push($newComparedSourcesIds, $IdVal);
																						}
																					}
																				}

																				if(in_array($initialSourceId, $newComparedSourcesIds))
																					$is_disabled = 1;

																				foreach ($allComparedSourcers as $comparedSKey => $comparedSVal) {
																					if(in_array($comparedSVal, $newComparedSourcesIds))
																						$is_disabled = 1;
																				}
																			}
																		}
																		if($is_disabled == 1)
																			$disabledHtml = 'disabled="disabled"';

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
																		$selected = (in_array($quelleRow['quelle_id'], $comparingSourceIds)) ? 'selected' : '';
																		
																		if($quelleRow['quelle_type_id'] == 3)
																			$htmlComparisonsInner .= '<option '.$selected.' value="'.$quelleRow['quelle_id'].'" '.$disabledHtml.'>'.$quellen_value.'</option>';
																		else
																			$htmlSingleSourcesInner .= '<option '.$selected.' value="'.$quelleRow['quelle_id'].'" '.$disabledHtml.'>'.$quellen_value.'</option>';
																		
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
																}
													   		?>
												   		</select>
												   		<span class="error-text"></span>
													</div>
												</div>
											</div>
										</div>
										
										<div class="form-group">
											<div class="spacer15"></div>
											<input type="hidden" name="comparison_name" id="comparison_name" value="<?php echo $comparisonName; ?>" class="form-control save-data" placeholder="Name of the comparison">
											<input type="hidden" name="scid" id="scid" value="<?php echo $scId; ?>">
											<input type="hidden" name="saved_arznei_id" id="saved_arznei_id" value="<?php echo $arzneiId; ?>">
											<input type="hidden" name="saved_initial_source_id" id="saved_initial_source_id" value="<?php echo $initialSourceId; ?>">
											<input type="hidden" name="saved_comparison_comparing_source_ids_comma_separated" id="saved_comparison_comparing_source_ids_comma_separated" value="<?php echo $savedComparisonComparingSourceIdsCommaSeparated; ?>">
											<input type="hidden" name="saved_comparison_quelle_id" id="saved_comparison_quelle_id" value="<?php echo $savedComparisonQuelleId; ?>">
											<input type="hidden" name="saved_comparisons_backup_id" id="saved_comparisons_backup_id" value="<?php echo $scId; ?>">
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
			            	</ul>
						</li>
			        </ul>
			        <div class="spacer"></div>
				  	<table id="resultTable" class="table table-bordered heading-table append-recognizer">
					    <thead class="heading-table-bg">
					      	<tr>
					      		<th style="width: 12%;" class="text-center">Source</th>
						        <th id="column_heading_symptom">Symptom</th>
						        <th style="width: 5%;" class="text-center">%</th>
						        <th style="width: 15%;" class="text-center linkage-column comparison-only-column">INFO & LINKAGE</th>
						        <th style="width: 19%;" class="text-center comparison-only-column">Command</th>
					      	</tr>
					    </thead>
					    <tbody>
							<tr class="no-records-found">
								<td colspan="5" class="text-center">No records found</td>
							</tr>
					    </tbody>
					</table>
					<?php /*<button class="btn btn-custom-black result-sub-btn bottom hidden" type="button">SAVE</button>*/ ?>
				</div>
			</div>  
		</div>
	</div>

	<!-- Info modal start -->
	<div class="modal fade" id="symptomInfoModal" role="dialog">
	    <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<!-- <h4 class="modal-title">Symptominformation</h4> -->
		        </div>
		        <div id="info_container" class="modal-body">
		          	<div id="info_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- Info modal end -->

	<!-- Comment modal start -->
	<div class="modal fade" id="symptomCommentModal" role="dialog" data-backdrop="static" data-keyboard="false">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Symptom Comment</h4>
		        </div>
		        <div id="comment_container" class="modal-body">
		          	<div id="comment_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		        	<?php /*<button type="button" onclick="updateComment()" class="btn btn-primary">Save</button>*/ ?>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- Comment modal end -->

	<!-- Footnote modal start -->
	<div class="modal fade" id="symptomFootnoteModal" role="dialog" data-backdrop="static" data-keyboard="false">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Symptom Footnote</h4>
		        </div>
		        <div id="footnote_container" class="modal-body">
		          	<div id="footnote_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		          	<?php /*<button type="button" onclick="updateFootnote()" class="btn btn-primary">Save</button>*/ ?>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- Footnote modal end -->

	<!-- NSC note modal start -->
	<div class="modal fade" id="nscNoteModal" role="dialog" data-backdrop="static" data-keyboard="false">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Nicht Sicher Kommentar</h4>
		        </div>
		        <div id="nsc_note_container" class="modal-body">
		          	<div id="nsc_note_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		          	<?php /*<button type="button" onclick="addnscNote()" class="btn btn-primary">Save</button>*/ ?>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- NSC note modal end -->

	<!-- NSP note modal start -->
	<div class="modal fade" id="nspNoteModal" role="dialog" data-backdrop="static" data-keyboard="false">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Nicht Sicher Kommentar</h4>
		        </div>
		        <div id="nsp_note_container" class="modal-body">
		          	<div id="nsp_note_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		          	<?php /*<button type="button" onclick="addnspNote()" class="btn btn-primary">Save</button>*/ ?>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- NSP note modal end -->

	<!-- Save comparison need user action modal start -->
	<div class="modal fade" id="saveComparisonModal" role="dialog" data-backdrop="static" data-keyboard="false">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Save</h4>
		        </div>
		        <div id="save_comparison_modal_container" class="modal-body">
		          	<div id="save_comparison_modal_loader" class="form-group text-center">
		          		<span class="loading-msg">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
		          		<span class="error-msg"></span>
					</div>
		        </div>
		        <div class="modal-footer">
		          	<button type="button" id="save_on_existing_btn" onclick="saveComparisonOnExisting()" class="btn btn-info">Save</button>
		          	<!-- <button type="button" onclick="saveComparisonAsNew()" class="btn btn-primary">Save as new</button> -->
		          	<button type="button" onclick="saveComparisonCancel()" class="btn btn-default">Cancel</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- Save comparison need user action modal end -->

	<!-- Global message modal start -->
	<div class="modal fade" id="reloadPageModal" role="dialog">
	    <div class="modal-dialog">
		    <div class="modal-content">
		        <div class="modal-header">
		          	<button type="button" class="close" data-dismiss="modal">&times;</button>
		          	<h4 class="modal-title">Alert</h4>
		        </div>
		        <div id="reload_page_modal_container" class="modal-body">
		          	
		        </div>
		        <div class="modal-footer">
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- Global message modal end -->

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

	<!-- </form> -->
	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script src="assets/js/common.js"></script>
	<script type="text/javascript">
		$(window).bind("load", function() {
			console.log('loaded');
			$("#loader").addClass("hidden");
			$("#comparison_container").removeClass('unclickable');
			$("#search_container").removeClass('unclickable');
			var records = $("#totalNumofRecords").val();
			if(records != ""){
				$("#numberOfRecord").html(records);
				$("#totalNumberDisplay").removeClass('hidden');
			}

			var scId = $("#scid").val();
			if(scId != ""){
				$("#symptom_comparison_form").submit();
				// $("#compare_submit_btn").click();
			}
		});
		$('#arznei_id').select2({
			// options 
    		searchInputPlaceholder: 'Search Arznei...'
		});
		// Defining Select2
		$('#initial_source').select2({
			// options 
    		searchInputPlaceholder: 'Search Quelle...'
		});
		$('#comparing_sources').select2({
			// options 
    		searchInputPlaceholder: 'Search Quelle...'
		});

		$('#search_sources').select2({
			searchInputPlaceholder: 'Search Quelle...',
			// width: '100%',
			// allowClear: true,
			// placeholder: function() {
			//     $(this).data('placeholder');
			// }
		});

		// Fetching Quelle
		$('#arznei_id').on('select2:select', function (e) {
		    // console.log(e.params.data);
		    if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
		    	$("#initial_source").prop("disabled", true);
		    	$("#comparing_sources").prop("disabled", true);
		    	var request = $.ajax({
				  	url: "get_arznei_quelle.php",
				  	type: "POST",
				  	data: {arznei_id : e.params.data.id},
				  	dataType: "json"
				});

				request.done(function(responseData) {
					console.log(responseData);
					var resultData = null;
					try {
						resultData = JSON.parse(responseData); 
					} catch (e) {
						resultData = responseData;
					}

					var saved_initial_source_id = $("#saved_initial_source_id").val(); 
					var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
					var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
					var initialSourceHtml = "";
					var comparingSourceHtml = "";

					// Initial source select box
					initialSourceHtml += '<select class="form-control save-data" name="initial_source" id="initial_source">';
					initialSourceHtml += '<option value="">Select</option>';

					var htmlComparisons = '<optgroup label="Comparisons">';
					var htmlSingleSources = '<optgroup label="Single sources">';
					var htmlComparisonsInner = ''; 
					var htmlSingleSourcesInner = '';

					// Comparing source select box
					comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
					comparingSourceHtml += '<option value="">Select</option>';

					var comHtmlComparisons = '<optgroup label="Comparisons">';
					var comHtmlSingleSources = '<optgroup label="Single sources">';
					var comHtmlComparisonsInner = ''; 
					var comHtmlSingleSourcesInner = '';
					


					$.each(resultData, function( key, value ) {
						// Initial source select box
						var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
						// Comparing source select box
						var comSelected = (split_saved_comparing_source_ids.indexOf(value.quelle_id) !== -1) ? 'selected' : '';
						if(value.quelle_type_id == 3){
							htmlComparisonsInner += '<option '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
							comHtmlComparisonsInner += '<option '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
						} else {
							htmlSingleSourcesInner += '<option '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
							comHtmlSingleSourcesInner += '<option '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
						}

					}) ;
					// Initial source select box
					if(htmlComparisonsInner == '')
						htmlComparisons += '<option value="" disabled="disabled">None</option>';
					else
						htmlComparisons += htmlComparisonsInner;
					if(htmlSingleSourcesInner == '')
						htmlSingleSources += '<option value="" disabled="disabled">None</option>';
					else
						htmlSingleSources += htmlSingleSourcesInner;
					
					// Comparing source select box
					if(comHtmlComparisonsInner == '')
						comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
					else
						comHtmlComparisons += comHtmlComparisonsInner;
					if(comHtmlSingleSourcesInner == '')
						comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
					else
						comHtmlSingleSources += comHtmlSingleSourcesInner;
					

					// Initial source select box
					htmlComparisons += '</optgroup>';
					htmlSingleSources += '</optgroup>';
					initialSourceHtml += htmlComparisons+htmlSingleSources;
					initialSourceHtml += '</select>';
					initialSourceHtml += '<span class="error-text"></span>';
					$("#initial_source_cnr").html( initialSourceHtml );
				 	$('#initial_source').select2({
						// options 
			    		searchInputPlaceholder: 'Search Quelle...'
					});

					// Comparing source select box
					comHtmlComparisons += '</optgroup>';
					comHtmlSingleSources += '</optgroup>';
					comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
					comparingSourceHtml += '</select>';
					comparingSourceHtml += '<span class="error-text"></span>';
					$("#comparing_source_cnr").html( comparingSourceHtml );
				 	$('#comparing_sources').select2({
						// options 
			    		searchInputPlaceholder: 'Search Quelle...'
					});
				 	$("#initial_source").prop("disabled", false);
		    		$("#comparing_sources").prop("disabled", false);
				});

				request.fail(function(jqXHR, textStatus) {
				  	console.log("Request failed: " + textStatus);
				  	$("#initial_source").prop("disabled", false);
		    		$("#comparing_sources").prop("disabled", false);
				});
		    }
		});

		$(document).on('select2:select', '#initial_source', function(e){
		// $('#initial_source').on('select2:select', function (e) {
			var arznei_id = $("#arznei_id").val();
			if((typeof(e.params.data.id) != "undefined" && e.params.data.id !== null) && arznei_id != ""){
				$("#comparing_sources").prop("disabled", true);
				var request = $.ajax({
				  	url: "get_comparing_quelle.php",
				  	type: "POST",
				  	data: {initial_source : e.params.data.id, arznei_id : arznei_id},
				  	dataType: "json"
				});

				request.done(function(responseData) {
					console.log(responseData);
					var resultData = null;
					try {
						resultData = JSON.parse(responseData); 
					} catch (e) {
						resultData = responseData;
					}

					var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
					var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
					var comparingSourceHtml = "";

					// Comparing source select box
					comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
					comparingSourceHtml += '<option value="">Select</option>';

					var comHtmlComparisons = '<optgroup label="Comparisons">';
					var comHtmlSingleSources = '<optgroup label="Single sources">';
					var comHtmlComparisonsInner = '';
					var comHtmlSingleSourcesInner = '';


					$.each(resultData, function( key, value ) {
						var conditionDisabled = "";
						if(value.is_disabled == 1)
							conditionDisabled = 'disabled="disabled"';
						// Initial source select box
						var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
						// Comparing source select box
						// var comSelected = (split_saved_comparing_source_ids.indexOf(value.quelle_id) !== -1) ? 'selected' : '';
						var comSelected = '';
						if(value.quelle_type_id == 3){
							comHtmlComparisonsInner += '<option '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
						} else {
							comHtmlSingleSourcesInner += '<option '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
						}
						

					});
					// Comparing source select box
					if(comHtmlComparisonsInner == '')
						comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
					else
						comHtmlComparisons += comHtmlComparisonsInner;
					if(comHtmlSingleSourcesInner == '')
						comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
					else
						comHtmlSingleSources += comHtmlSingleSourcesInner;
					

					// Comparing source select box
					comHtmlSingleSources += '</optgroup>';
					comHtmlComparisons += '</optgroup>';
					comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
					comparingSourceHtml += '</select>';
					comparingSourceHtml += '<span class="error-text"></span>';
					$("#comparing_source_cnr").html( comparingSourceHtml );
				 	$('#comparing_sources').select2({
						// options 
			    		searchInputPlaceholder: 'Search Quelle...'
					});
					$("#comparing_sources").prop("disabled", false);
				});

				request.fail(function(jqXHR, textStatus) {
					$("#comparing_sources").prop("disabled", false);
				  	console.log("Request failed: " + textStatus);
				});
			}
		});

		$(document).on('mouseenter', '.linkage', function(){
		    var uniqueId = $(this).attr("data-unique-id");
			var item = $(this).attr("data-item");
			var inActiveIcon = item+".png";
			var activeIcon = item+"-green.png";

			if (!$(this).hasClass("active"))
				$(this).find('img').attr("src","assets/img/"+activeIcon);
		}).on('mouseleave','.linkage',  function(){
			var uniqueId = $(this).attr("data-unique-id");
			var item = $(this).attr("data-item");
			var inActiveIcon = item+".png";
			var activeIcon = item+"-green.png";
		    
		    if (!$(this).hasClass("active"))
				$(this).find('img').attr("src","assets/img/"+inActiveIcon);
		});

		$(document).on('click', '.vbtn-has-connection', function(){
			var $th = $(this);
    		if($th.hasClass('processing'))
    			return;
    		$th.addClass('processing');
		    var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

		    var comparisonInitialSourceId = $(this).attr("data-comparison-initial-source-id");
		    var sourceArzneiId = $(this).attr("data-source-arznei-id");
		    var parentUniqueId = $(this).attr("data-unique-id");
		    var initialSymptomId = $(this).attr("data-initial-symptom-id");
		    var comparingSymptomId = $(this).attr("data-comparing-symptom-id");
		    var activeSymptomType = $(this).attr("data-active-symptom-type");
		    var comparingSourceIds = $(this).attr("data-comparing-source-ids");
		    var isConnectionLoaded = $(this).attr("data-is-connection-loaded");
		    var removableRowClassChain = $(this).attr("data-removable-row-class-chain");
		    var vPadding = $(this).attr("data-v-padding");
		    var isRecompare = parseInt($(this).attr("data-is-recompare"));
		    var initialSourceId = $("#initial_source_id_"+parentUniqueId).val();
		    var comparingSourceId = $("#comparing_source_id_"+parentUniqueId).val();
		    var savedComparisonComparingSourceIds = $(this).attr("data-saved-comparison-comparing-source-ids");
		    var mainParentInitialSymptomId = $(this).attr("data-main-parent-initial-symptom-id");
		    var connectionsMainParentSymptomId = $(this).attr("data-connections-main-parent-symptom-id");
		    // $(this).attr("data-is-connection-loaded", 1);
		    var matched_symptom_ids_string = $("#matched_symptom_ids_"+parentUniqueId).val();
		    var similarity_rate = $("#similarity_rate_"+parentUniqueId).val();
		    var comparison_option = $("#comparison_option_"+parentUniqueId).val();
		    var is_unmatched_symptom = $("#is_unmatched_symptom_"+parentUniqueId).val();
		    var individual_comparison_language = $("#individual_comparison_language_"+parentUniqueId).val();

		    var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+parentUniqueId).val();

		    var rowClass = "removable-"+parentUniqueId;
			removableRowClassChain += rowClass+' ';
		    if(isConnectionLoaded == 1){
		    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 0);
		    	$("#v_btn_"+parentUniqueId).html('<i class="fas fa-plus"></i>');
		    	$("#v_btn_"+parentUniqueId).promise().done(function(){
		    		$('#connection_loder_'+parentUniqueId).remove();
			    	$(".removable-"+parentUniqueId).remove();
			    	$th.removeClass('processing');

			    	// Removing the connection check box checked if there is no open connection
			    	var totConn = $( ".vbtn-has-connection" ).length; 
			    	var closedConnCount = 0;
			    	$( ".vbtn-has-connection" ).each(function() {
						var checkIsConnectionLoaded = $(this).attr("data-is-connection-loaded");
						if(checkIsConnectionLoaded != 1)
							closedConnCount++;
					})
					if(parseInt(totConn) == parseInt(closedConnCount))
						$("#show_all_connections").prop("checked", false);
		    	})	    	
		    }else{
		    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
				$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
				$("#v_btn_"+parentUniqueId).promise().done(function(){
					$(".removable-"+parentUniqueId).remove();
					$('#connection_loder_'+parentUniqueId).remove();
			    	var loadingHtml = '';
			    	loadingHtml += '<tr id="connection_loder_'+parentUniqueId+'">';
					loadingHtml += ' 	<td colspan="5" class="text-center">';
					loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
					loadingHtml += ' 	</td>';
					loadingHtml += '</tr>';
					$("#row_"+parentUniqueId).after(loadingHtml);

					$.ajax({
						type: 'POST',
						url: 'get-backup-symptom-connections.php',
						data: {
							comparison_initial_source_id: comparisonInitialSourceId,
							source_arznei_id: sourceArzneiId,
							main_parent_initial_symptom_id: mainParentInitialSymptomId,
							connections_main_parent_symptom_id: connectionsMainParentSymptomId,
							initial_symptom_id: initialSymptomId,
							comparing_symptom_id: comparingSymptomId,
							active_symptom_type: activeSymptomType,
							is_recompare: isRecompare,
							initial_source_id: initialSourceId,
							comparing_source_id: comparingSourceId,
							comparing_source_ids: comparingSourceIds,
							saved_comparison_comparing_source_ids: savedComparisonComparingSourceIds,
							saved_comparison_quelle_id: saved_comparison_quelle_id,
							saved_comparisons_backup_id: saved_comparisons_backup_id,
							individual_comparison_language: individual_comparison_language
						},
						dataType: "json",
						success: function( response ) {
							console.log(response);
							if(response.status == "invalid"){
								$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
								$("#reloadPageModal").modal('show');
							} else if(response.status == "success"){
								var resultData = null;
								try {
									resultData = JSON.parse(response.result_data); 
								} catch (e) {
									resultData = response.result_data;
								}

								var html = "";
								$.each(resultData, function( key, value ) {
									var uniqueId = parentUniqueId+value.initial_source_symptom_id+value.comparing_source_symptom_id;
									
									// if(vPadding == 15){
									// 	var vbuttonleftPadding = 2;
									// 	var setVpadding = 15;
									// }else{
										var setVpadding = parseInt(vPadding) + 16;
										var vbuttonleftPadding = parseInt(setVpadding) - 16;
										
									// }

							  		var commentClasses = "";
							  		var footnoteClasses = "";
							  		var FVBtnClasses = "FV-btn";

							  		if(value.is_final_version_available != 0)
							  			FVBtnClasses += " active";

							  		if(value.has_connections == 1){
							  			if(value.is_further_connections_are_saved == 1){
							  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved';
							  				FVBtnClasses += " link-active-saved";
							  			}
							  			else{
							  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active';
							  				FVBtnClasses += " link-active";
							  			}
							  			var vBtnTitle = 'Earlier connections';
							  			var vBtnDisable = '';
							  		} else {
							  			var vBtnClasses = 'vbtn';
							  			var vBtnTitle = 'Earlier connections';
							  			var vBtnDisable = 'link-disabled unclickable';
							  		}

							  		var nsc_btn_disabled = 'link-disabled unclickable';
							  		var connection_btn_disabled = '';
							  		var nsp_btn_disabled = 'link-disabled unclickable';
							  		var paste_btn_disabled = '';
							  		var connect_btn_class = 'connecting-btn';
							  		var paste_btn_class = 'paste-btn';
							  		var nscClasses = 'nsc';
							  		var nspClasses = 'nsp';
							  		if(value.is_connected == 1){
							  			nsc_btn_disabled = '';
								  		connection_btn_disabled = '';
								  		nsp_btn_disabled = 'link-disabled unclickable';
								  		paste_btn_disabled = 'link-disabled unclickable';
								  		if(value.is_saved_connections == 1){
								  			connect_btn_class = 'connecting-btn active link-active-saved';
								  			FVBtnClasses += " link-active-saved";
								  		} else {
								  			connect_btn_class = 'connecting-btn active link-active';
								  			FVBtnClasses += " link-active";
								  		}
							  		}

							  		if(value.is_pasted == 1){
							  			nsc_btn_disabled = 'link-disabled unclickable';
								  		connection_btn_disabled = 'link-disabled unclickable';
								  		nsp_btn_disabled = '';
								  		paste_btn_disabled = '';
								  		if(value.is_saved_connections == 1){
								  			paste_btn_class = 'paste-btn active link-active-saved';
								  			FVBtnClasses += " link-active-saved";
								  		} else {
								  			paste_btn_class = 'paste-btn active link-active';
								  			FVBtnClasses += " link-active";
								  		}
							  		}

							  		if(value.is_ns_connect == 1){
							  			if(value.is_saved_connections == 1)
							  				nscClasses = 'nsc active link-active-saved';
							  			else
							  				nscClasses = 'nsc active link-active';
							  		}
							  		if(value.is_ns_paste == 1){
							  			if(value.is_saved_connections == 1)
							  				nspClasses = 'nsp active link-active-saved';
							  			else
							  				nspClasses = 'nsp active link-active';
							  		}

							  		var instantReflectionClass = 'instant-reflection-set-'+mainParentInitialSymptomId;

							  		if(value.is_saved_connections == 1)
							  			rowBgColorClass = ' saved-connection-row';
							  		else
							  			rowBgColorClass = ' unsaved-connection-row';
							  		
							  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
						  			var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

						  			var translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
						  			var additional_class_for_original_symptom = "";
						  			var additional_class_for_hidden_symptom = "hidden";
						  			if($("#show_all_comparative_translation").prop("checked") == true) {
						  				translation_toggle_btn_additional_class += " active";
						  				additional_class_for_original_symptom = "table-original-symptom-bg";
						  				additional_class_for_hidden_symptom = "";
						  			}
							  		//console.log(comparingSymptomHighlightedEndcod);
							  		if(value.is_initial_source == 1){
							  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
								  			commentClasses = ' active';
								  		}
								  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
								  			footnoteClasses = ' active';
								  		}

							  			// Not allowing cmparison initial source symptoms to show it's connection to prevent from infinit nesting
							  			if(comparisonInitialSourceId == value.initial_source_id)
							  			{
							  				vBtnClasses = 'vbtn';
								  			vBtnTitle = 'Earlier connections';
								  			vBtnDisable = 'link-disabled unclickable';
							  			}
							  			// Making the V/+ button disabled for it's child section if the clicked symptom is the main initial source symptom to avoide infinit nesting
							  			if(mainParentInitialSymptomId == initialSymptomId)
							  			{
							  				vBtnClasses = 'vbtn';
								  			vBtnTitle = 'Earlier connections';
								  			vBtnDisable = 'link-disabled unclickable';
							  			}

							  			var saved_version_source_code = "";
							  			if(value.initial_source_code != value.initial_saved_version_source_code)
						  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';

							  			var activeSymptomTypeIndividual = "initial";
							  			// var initialSourceSymptomHighlighted = $('<div/>').html(value.initial_source_symptom_highlighted).text();
							  			var initialSourceSymptomHighlighted_de = (typeof(value.initial_source_symptom_highlighted_de) != "undefined" && value.initial_source_symptom_highlighted_de !== null && value.initial_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.initial_source_symptom_highlighted_de) : "";
							  			var initialSourceSymptomHighlighted_en = (typeof(value.initial_source_symptom_highlighted_en) != "undefined" && value.initial_source_symptom_highlighted_en !== null && value.initial_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.initial_source_symptom_highlighted_en) : "";
							  			var displaySymptomString = "";

							  			if(value.connection_language == "en"){

							  				displaySymptomString = initialSourceSymptomHighlighted_en;
							  				
							  				if(initial_source_original_language == "en"){
							  					var tmpString = "";
							  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom '+additional_class_for_original_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";
							  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
							  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initialSourceSymptomHighlighted_en+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				}
							  			} else {
							  				displaySymptomString = initialSourceSymptomHighlighted_de;

							  				if(initial_source_original_language == "de"){
							  					var tmpString = "";
							  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom '+additional_class_for_original_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
							  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				} else {
							  					var tmpString = "";
							  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";
							  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initialSourceSymptomHighlighted_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  			}

							  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
							  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;

							  			if(is_unmatched_symptom == 1){
							  				// translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
							  				instantReflectionClass += ' instant-reflection-unmatched-row';
							  			}
							  			
								  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+' '+removableRowClassChain+rowBgColorClass+'">';
								  		html += '	<td style="width: 12%;" class="text-center">'+value.initial_source_code+saved_version_source_code+'</td>';
								  		html += '	<td><!-- <i style="padding-left:'+vbuttonleftPadding+'px; padding-right:6px;" class="fas fa-angle-right"></i> -->'+displaySymptomString+'</td>';
								  		html += '	<td style="width: 5%;" class="text-center">'+value.matching_percentage+'%</td>';
								  		html += '	<th style="width: 15%;">';
								  		html += '		<ul class="info-linkage-group">';
								  		html += '			<li>';
								  		html += '				<a onclick="showInfo('+value.initial_source_symptom_id+', \''+value.initial_source_type+'\', this)" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-info-circle"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+value.initial_source_symptom_id+', \''+value.initial_source_type+'\', this)" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-comment-alt"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+value.initial_source_symptom_id+', \''+value.initial_source_type+'\', this)" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-sticky-note"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="translation-toggle-btn '+translation_toggle_btn_additional_class+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
								  		html += '			</li>';
								  		if(value.is_final_version_available != 0){
								  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								  			var fvName = "";
								  			var fvTitle = "";
								  			if(value.is_final_version_available == 1){
								  				fvName = "CE";
								  				fvTitle = "Connect edit";
								  			} else if(value.is_final_version_available == 2){
								  				fvName = "PE";
								  				fvTitle = "Paste edit";
								  			}
								  			html += '			<li>';
									  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
									  		html += '			</li>';
								  		}
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-is-recompare="'+isRecompare+'" data-initial-source-id="'+initialSourceId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-connections-main-parent-symptom-id="'+connectionsMainParentSymptomId+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="initial" data-is-connection-loaded="0" data-comparing-source-ids="'+comparingSourceIds+'" data-source-arznei-id="'+sourceArzneiId+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
								  		html += '			</li>';
								  		html += '		</ul>';
							  		}
							  		else
							  		{
							  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
								  			commentClasses = ' active';
								  		}
								  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
								  			footnoteClasses = ' active';
								  		}

							  			// Not allowing cmparison initial source symptoms to show it's connection to prevent from infinit nesting
							  			if(comparisonInitialSourceId == value.comparing_source_id)
							  			{
							  				vBtnClasses = 'vbtn';
								  			vBtnTitle = 'Earlier connections';
								  			vBtnDisable = 'link-disabled unclickable';
							  			}
							  			// Making the V/+ button disabled for it's child section if the clicked symptom is the main initial source symptom to avoide infinit nesting
							  			if(mainParentInitialSymptomId == initialSymptomId)
							  			{
							  				vBtnClasses = 'vbtn';
								  			vBtnTitle = 'Earlier connections';
								  			vBtnDisable = 'link-disabled unclickable';
							  			}
							  			
							  			var saved_version_source_code = "";
							  			if(value.comparing_source_code != value.comparing_saved_version_source_code)
						  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';

							  			var activeSymptomTypeIndividual = "comparing";
							  			// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
							  			var comparingSymptomHighlightedEndcod_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
							  			var comparingSymptomHighlightedEndcod_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
							  			var displaySymptomString = "";
							  			if(value.connection_language == "en"){

							  				displaySymptomString = comparingSymptomHighlightedEndcod_en;
							  				if(comparing_source_original_language == "en"){
							  					var tmpString = "";
							  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom '+additional_class_for_original_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";
							  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
							  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				}
							  			} else {
							  				displaySymptomString = comparingSymptomHighlightedEndcod_de;
							  				if(comparing_source_original_language == "de"){
							  					var tmpString = "";
							  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom '+additional_class_for_original_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
							  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				} else {
							  					var tmpString = "";
							  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";
							  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  			}

							  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
							  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;

							  			if(is_unmatched_symptom == 1){
							  				// translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
							  				instantReflectionClass += ' instant-reflection-unmatched-row';
							  			}
							  			
							  			html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+' '+removableRowClassChain+rowBgColorClass+'">';
								  		html += '	<td style="width: 12%;" class="text-center">'+value.comparing_source_code+saved_version_source_code+'</td>';
								  		html += '	<td><!-- <i style="padding-left:'+vbuttonleftPadding+'px; padding-right:6px;" class="fas fa-angle-right"></i> -->'+displaySymptomString+'</td>';
								  		html += '	<td style="width: 5%;" class="text-center">'+value.matching_percentage+'%</td>';
								  		html += '	<th style="width: 15%;">';
								  		html += '		<ul class="info-linkage-group">';
								  		html += '			<li>';
								  		html += '				<a onclick="showInfo('+value.comparing_source_symptom_id+', \''+value.comparing_source_type+'\', this)" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-info-circle"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+value.comparing_source_symptom_id+', \''+value.comparing_source_type+'\', this)" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-comment-alt"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+value.comparing_source_symptom_id+', \''+value.comparing_source_type+'\', this)" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-sticky-note"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="translation-toggle-btn '+translation_toggle_btn_additional_class+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
								  		html += '			</li>';
								  		if(value.is_final_version_available != 0){
								  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								  			var fvName = "";
								  			var fvTitle = "";
								  			if(value.is_final_version_available == 1){
								  				fvName = "CE";
								  				fvTitle = "Connect edit";
								  			} else if(value.is_final_version_available == 2){
								  				fvName = "PE";
								  				fvTitle = "Paste edit";
								  			}
								  			html += '			<li>';
									  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
									  		html += '			</li>';
								  		}
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-is-recompare="'+isRecompare+'" data-initial-source-id="'+initialSourceId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-connections-main-parent-symptom-id="'+connectionsMainParentSymptomId+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="comparing" data-is-connection-loaded="0" data-comparing-source-ids="'+comparingSourceIds+'" data-source-arznei-id="'+sourceArzneiId+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
								  		html += '			</li>';
								  		html += '		</ul>';
							  		}
							  		html += '		<input type="hidden" name="saved_comparisons_backup_id[]" id="saved_comparisons_backup_id_'+uniqueId+'" value="'+saved_comparisons_backup_id+'">';
							  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+sourceArzneiId+'">';
							  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
							  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
							  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
							  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
							  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
							  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
							  		
							  		// Initial German
							  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';

							  		// Initial English
							  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';

							  		// Comparing German
							  		html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';

							  		// Comparing English
							  		html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';
							  		
							  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';
							  		html += '		<input type="hidden" name="individual_connection_language[]" id="individual_connection_language_'+uniqueId+'" value="'+value.connection_language+'">';
							  		
							  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
							  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.matching_percentage+'">';
							  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="'+value.is_connected+'">';
							  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="'+value.is_ns_connect+'">';
							  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="'+value.ns_connect_note+'">';
							  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
							  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
							  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
							  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
							  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+matched_symptom_ids_string+'">';
							  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+mainParentInitialSymptomId+'">';
							  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+connectionsMainParentSymptomId+'">';
							  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+similarity_rate+'">';
							  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+activeSymptomTypeIndividual+'">';
					  				html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+comparingSourceIds+'">';
					  				html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+comparison_option+'">';
					  				html += '		<input type="hidden" name="comparison_initial_source_id[]" id="comparison_initial_source_id_'+uniqueId+'" value="'+comparisonInitialSourceId+'">';
					  				html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+savedComparisonComparingSourceIds+'">';
					  				html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+is_unmatched_symptom+'">';
							  		html += '	</th>';
							  		html += '	<th style="width: 19%;" class="">';
							  		html += '		<ul class="command-group">';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-link"></i></a>';
							  		html += '			</li>';
							  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
							  		if(value.connection_or_paste_type == 3){
							  			if(value.is_saved_connections == 1)
							  				connectEditIndicatorClasses = 'active link-active-saved';
							  			else
							  				connectEditIndicatorClasses = 'active link-active';
							  			html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connectEditIndicatorClasses+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-connection-or-paste-type="3">CE</a>';
								  		html += '			</li>';
							  		}
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'">P</a>';
							  		html += '			</li>';
							  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
							  		if(value.connection_or_paste_type == 4){
							  			if(value.is_saved_connections == 1)
							  				pasteEditIndicatorClasses = 'active link-active-saved';
							  			else
							  				pasteEditIndicatorClasses = 'active link-active';
							  			html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+pasteEditIndicatorClasses+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-connection-or-paste-type="4">PE</a>';
								  		html += '			</li>';
							  		}
							  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
							  		if(value.connection_or_paste_type == 2){
							  			if(value.is_saved_connections == 1)
							  				swapIndicatorClasses = 'active link-active-saved';
							  			else
							  				swapIndicatorClasses = 'active link-active';
							  			html += '		<li>';
								  		html += '			<a href="javascript:void(0)" id="swap_connect_indicator_btn_'+uniqueId+'" class="'+swapIndicatorClasses+'" title="Swap connection indicator"><i class="fas fa-recycle"></i></a>';
								  		html += '		</li>';
							  		}
							  		html += '		</ul>';
							  		html += '	</th>';
							  		html += '</tr>';
								});

								$('#connection_loder_'+parentUniqueId).remove();
								$("#row_"+parentUniqueId).after(html);
								$th.removeClass('processing');
								// $("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
								// $("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');

							}else{
								$('#connection_loder_'+parentUniqueId).html('<td colspan="5" class="text-center">Something went wrong! Could not load the data.</td>');
								$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
								$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
								$th.removeClass('processing');
								// setTimeout(function() {
								//     $('#connection_loder_'+parentUniqueId).remove();
								// }, 2000);
							}
						}
					}).fail(function (response) {
						console.log(response);
						$('#connection_loder_'+parentUniqueId).html('<td colspan="5" class="text-center">Something went wrong! Could not load the data.</td>');
						$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
						$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
						$th.removeClass('processing');
						// setTimeout(function() {
						//     $('#connection_loder_'+parentUniqueId).remove();
						// }, 2000);
					});
				})
		    }
		});

		$(document).on('hidden.bs.modal', '#reloadPageModal', function(){
		  	location.reload();
		});

		$(document).on('hidden.bs.modal', '.global-msg-modal-reload', function(){
		  	location.reload();
		});

		$(document).on('click', '.nsc', function(){
			var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

			var uniqueId = $(this).attr("data-unique-id");
		    var initial_source_symptom_id = $("#initial_source_symptom_id_"+uniqueId).val();
			var comparing_source_symptom_id = $("#comparing_source_symptom_id_"+uniqueId).val();
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+uniqueId).val();
			var error_count = 0;

			$('#connecting_btn_'+uniqueId).prop('disabled', true);
			$('#nsc_btn_'+uniqueId).prop('disabled', true);
			$('#nsc_btn_'+uniqueId).html('<img src="assets/img/loader.gif" alt="Loader">');

			if(uniqueId == ""){
				error_count++;
			}
			if(initial_source_symptom_id == ""){
				error_count++;
			}
			if(comparing_source_symptom_id == ""){
				error_count++;
			}

			if(error_count == 0){
				$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
				$("#nsc_note_modal_loader .error-msg").html('');
				if($("#nsc_note_modal_loader").hasClass('hidden'))
					$("#nsc_note_modal_loader").removeClass('hidden');

				$("#populated_nsc_note_data").remove();
				$.ajax({
					type: 'POST',
					url: 'symptom-backup-connection-operations.php',
					data: {
						unique_id: uniqueId,
						initial_source_symptom_id: initial_source_symptom_id,
						comparing_source_symptom_id: comparing_source_symptom_id,
						saved_comparison_quelle_id: saved_comparison_quelle_id,
						saved_comparisons_backup_id: saved_comparisons_backup_id,
						action: 'get_nsc_note'
					},
					dataType: "json",
					success: function( response ) {
						console.log(response);
						if(response.status == "invalid"){
							$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
							$("#reloadPageModal").modal('show');
						} else if(response.status == "success"){
							var resultData = null;
							try {
								resultData = JSON.parse(response.result_data); 
							} catch (e) {
								resultData = response.result_data;
							}
							var ns_connect_note = (resultData.ns_connect_note != "" && resultData.ns_connect_note != null) ? resultData.ns_connect_note : "Not available";
							var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
							var html = '';
							html += '<div id="populated_nsc_note_data">';
							html += '	<div class="row">';
							html += '		<div class="col-sm-12 text-center">';
							html += '			'+ns_connect_note+'';
							// html += '			<textarea name="nsc_note" id="nsc_note" class="form-control" rows="5" cols="50">'+ns_connect_note+'</textarea>';
							html += '			<span class="error-text"></span>';
							html += '			<input type="hidden" name="unique_id_nsc_note_modal" id="unique_id_nsc_note_modal" value="'+uniqueId+'">';
							html += '			<input type="hidden" name="connection_row_id_nsc_note_modal" id="connection_row_id_nsc_note_modal" value="'+id+'">';
							html += '		</div>';
							html += '	</div>';
							html += '</div>';

							if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
							$("#nsc_note_modal_loader").addClass('hidden');
							$("#nsc_note_container").append(html);
							$("#nscNoteModal").modal('show');
						}else{
							$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
							$("#globalMsgModal").modal('show');

							$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
							$('#nsc_btn_'+uniqueId).prop('disabled', false);
							$('#connecting_btn_'+uniqueId).prop('disabled', false);
						}
					}
				}).fail(function (response) {
					console.log(response);
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
					$("#globalMsgModal").modal('show');

					$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
					$('#nsc_btn_'+uniqueId).prop('disabled', false);
					$('#connecting_btn_'+uniqueId).prop('disabled', false);

				});
			}
			else
			{
				$("#global_msg_container").html('<p class="text-center">Operation failed. Required data not found, Please retry!</p>');
				$("#globalMsgModal").modal('show');

				$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
				$('#nsc_btn_'+uniqueId).prop('disabled', false);
				$('#connecting_btn_'+uniqueId).prop('disabled', false);
			}
		});

		$('#nscNoteModal').on('hidden.bs.modal', function () {
		  	var uniqueId = $("#unique_id_nsc_note_modal").val();
		  	$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
			$('#nsc_btn_'+uniqueId).prop('disabled', false);
			$('#connecting_btn_'+uniqueId).prop('disabled', false);
		})

		$(document).on('click', '.nsp', function(){
			var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

			var uniqueId = $(this).attr("data-unique-id");
		    var initial_source_symptom_id = $("#initial_source_symptom_id_"+uniqueId).val();
			var comparing_source_symptom_id = $("#comparing_source_symptom_id_"+uniqueId).val();
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+uniqueId).val();
			var error_count = 0;

			$('#paste_btn_'+uniqueId).prop('disabled', true);
			$('#nsp_btn_'+uniqueId).prop('disabled', true);
			$('#nsp_btn_'+uniqueId).html('<img src="assets/img/loader.gif" alt="Loader">');

			if(uniqueId == ""){
				error_count++;
			}
			if(initial_source_symptom_id == ""){
				error_count++;
			}
			if(comparing_source_symptom_id == ""){
				error_count++;
			}

			if(error_count == 0){
				$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
				$("#nsp_note_modal_loader .error-msg").html('');
				if($("#nsp_note_modal_loader").hasClass('hidden'))
					$("#nsp_note_modal_loader").removeClass('hidden');

				$("#populated_nsp_note_data").remove();
				$.ajax({
					type: 'POST',
					url: 'symptom-backup-connection-operations.php',
					data: {
						unique_id: uniqueId,
						initial_source_symptom_id: initial_source_symptom_id,
						comparing_source_symptom_id: comparing_source_symptom_id,
						saved_comparison_quelle_id: saved_comparison_quelle_id,
						saved_comparisons_backup_id: saved_comparisons_backup_id,
						action: 'get_nsp_note'
					},
					dataType: "json",
					success: function( response ) {
						console.log(response);
						if(response.status == "invalid"){
							$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
							$("#reloadPageModal").modal('show');
						} else if(response.status == "success"){
							var resultData = null;
							try {
								resultData = JSON.parse(response.result_data); 
							} catch (e) {
								resultData = response.result_data;
							}
							var ns_paste_note = (resultData.ns_paste_note != "" && resultData.ns_paste_note != null) ? resultData.ns_paste_note : "Not available";
							var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
							var html = '';
							html += '<div id="populated_nsp_note_data">';
							html += '	<div class="row">';
							html += '		<div class="col-sm-12 text-center">';
							html += '			'+ns_paste_note+'';
							// html += '			<textarea name="nsp_note" id="nsp_note" class="form-control" rows="5" cols="50">'+ns_paste_note+'</textarea>';
							html += '			<span class="error-text"></span>';
							html += '			<input type="hidden" name="unique_id_nsp_note_modal" id="unique_id_nsp_note_modal" value="'+uniqueId+'">';
							html += '			<input type="hidden" name="connection_row_id_nsp_note_modal" id="connection_row_id_nsp_note_modal" value="'+id+'">';
							html += '		</div>';
							html += '	</div>';
							html += '</div>';

							if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
							$("#nsp_note_modal_loader").addClass('hidden');
							$("#nsp_note_container").append(html);
							$("#nspNoteModal").modal('show');
						}else{
							$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
							$("#globalMsgModal").modal('show');

							$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
							$('#nsp_btn_'+uniqueId).prop('disabled', false);
							$('#paste_btn_'+uniqueId).prop('disabled', false);
						}
					}
				}).fail(function (response) {
					console.log(response);
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
					$("#globalMsgModal").modal('show');

					$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
					$('#nsp_btn_'+uniqueId).prop('disabled', false);
					$('#paste_btn_'+uniqueId).prop('disabled', false);

				});
			}
			else
			{
				$("#global_msg_container").html('<p class="text-center">Operation failed. Required data not found, Please retry!</p>');
				$("#globalMsgModal").modal('show');

				$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
				$('#nsp_btn_'+uniqueId).prop('disabled', false);
				$('#paste_btn_'+uniqueId).prop('disabled', false);
			}
		});

		$('#nspNoteModal').on('hidden.bs.modal', function () {
		  	var uniqueId = $("#unique_id_nsp_note_modal").val();
		  	$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
			$('#nsp_btn_'+uniqueId).prop('disabled', false);
			$('#paste_btn_'+uniqueId).prop('disabled', false);
		})

		$('body').on( 'submit', '#symptom_comparison_form', function(e) {
			e.preventDefault();

			var initial_source = $("#initial_source").val();
			var arznei_id = $("#arznei_id").val();
			var comparing_sources = $("#comparing_sources").val();
			var scId = $("#scid").val();
			var saved_comparison_comparing_source_ids_comma_separated = $("#saved_comparison_comparing_source_ids_comma_separated").val();
			var error_count = 0;

			if(arznei_id == ""){
				$("#arznei_id").next().next().html('Please select arznei');
				$("#arznei_id").next().next().addClass('text-danger');
				error_count++;
			}else{
				$("#arznei_id").next().next().html('');
				$("#arznei_id").next().next().removeClass('text-danger');
			}
			if(initial_source == ""){
				$("#initial_source").next().next().html('Please select initial source');
				$("#initial_source").next().next().addClass('text-danger');
				error_count++;
			}else{
				$("#initial_source").next().next().html('');
				$("#initial_source").next().next().removeClass('text-danger');
			}
			if(comparing_sources == ""){
				$("#comparing_sources").next().next().html('Please select comparing source');
				$("#comparing_sources").next().next().addClass('text-danger');
				error_count++;
			}else{
				$("#comparing_sources").next().next().html('');
				$("#comparing_sources").next().next().removeClass('text-danger');
			}

			if(error_count == 0){

				$('.batch-search-result-form').remove();
				$('.batch-result-form').remove();
				$('#symptom_comparison_form').addClass('unclickable');
				$('#compare_submit_btn').prop('disabled', true);
				$('#search_submit_btn').prop('disabled', true);
				$("#comparison_name").val('');
				
				if(!$(".result-sub-btn").hasClass('hidden'))
					$(".result-sub-btn").addClass('hidden');

				if($('.comparison-only-column').hasClass('hidden'))
					$('.comparison-only-column').removeClass('hidden');
				$("#numberOfRecord").html(0);

				if(!$(".head-panel-sub-ul").hasClass('hidden'))
					$(".head-panel-sub-ul").addClass('hidden');

				$("#column_heading_symptom").html('Symptom');
				var loadingHtml = '';
				loadingHtml += '<tr id="loadingTr">';
				loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
				loadingHtml += '</tr>';

				$('#resultTable tbody').html(loadingHtml);

				var data = $(this).serialize();

				$(".progress-thead").remove();
				var progressBarHtml = '';
				progressBarHtml += '<thead class="progress-thead heading-table-bg">';
				progressBarHtml += '	<tr>';
				progressBarHtml += ' 		<th colspan="5">';
				progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
				progressBarHtml += ' 			<div class="progress comparison-progress">';
				progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
				progressBarHtml += ' 			</div>';
				progressBarHtml += ' 		</th>';
				progressBarHtml += '	</tr>';
				progressBarHtml += '</thead>';
				$('#resultTable thead').after(progressBarHtml);

				// start the process
				var matchedSymptomIds = [];
				self.process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds, self );
			}else{
				$('html, body').animate({
		            scrollTop: $("#symptom_comparison_form").offset().top
		        }, 1000);
				return false;
			}
		});

		// Calling this function to get compare result batches When comparing
		function process_step( step, number_of_records, total_batches_in_part1, total_batches_in_part2, is_stage2_checked, un_matched_symptoms_set_number, data, matched_symptom_ids, self ) {
			var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

			var nummerOfRecordFetch = number_of_records;
			var saved_comparison_comparing_source_ids_comma_separated = $("#saved_comparison_comparing_source_ids_comma_separated").val();
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id").val();
			$.ajax({
				type: 'POST',
				url: 'get-compare-result-batch-backup.php',
				data: {
					form: data,
					step: step,
					total_batches_in_part1: total_batches_in_part1,
					total_batches_in_part2: total_batches_in_part2,
					is_stage2_checked: is_stage2_checked,
					un_matched_symptoms_set_number: un_matched_symptoms_set_number,
					saved_comparison_comparing_source_ids_comma_separated: saved_comparison_comparing_source_ids_comma_separated,
					matched_symptom_ids: matched_symptom_ids,
					saved_comparison_quelle_id: saved_comparison_quelle_id,
					saved_comparisons_backup_id: saved_comparisons_backup_id
				},
				dataType: "json",
				success: function( response ) {
					console.log(response);

					if((typeof(response.is_invalid_quelle) != "undefined" && response.is_invalid_quelle !== null) && response.is_invalid_quelle == 1) {
						$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
						$("#reloadPageModal").modal('show');
					}
					else
					{
						if(typeof(response.result_data) != "undefined" && response.result_data !== null) {
							var resultData = null;
							try {
								resultData = JSON.parse(response.result_data); 
							} catch (e) {
								resultData = response.result_data;
							}
							//console.log(resultData);
							var html = "";
							$.each(resultData, function( key, value ) {
								
								var uniqueId = value.initial_source_symptom_id+value.comparing_source_symptom_id;
						  		var commentClasses = "";
						  		var footnoteClasses = "";
						  		var FVBtnClasses = "FV-btn";

						  		if(value.is_final_version_available != 0)
						  			FVBtnClasses += " active";

						  		if(value.has_connections == 1){
						  			if(value.is_further_connections_are_saved == 1){
						  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved unclickable';
						  				FVBtnClasses += " link-active-saved";
						  			}
						  			else{
						  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active unclickable';
						  				FVBtnClasses += " link-active";
						  			}
						  			var vBtnTitle = 'Earlier connections';
						  			var vBtnDisable = '';
						  		} else {
						  			var vBtnClasses = 'vbtn unclickable';
						  			var vBtnTitle = 'Earlier connections';
						  			var vBtnDisable = 'link-disabled unclickable';
						  		}

						  		var nsc_btn_disabled = 'link-disabled unclickable';
						  		var connection_btn_disabled = '';
						  		var nsp_btn_disabled = 'link-disabled unclickable';
						  		var paste_btn_disabled = '';
						  		// var connect_btn_class = 'connecting-btn btn btn-default';
						  		var paste_btn_class = 'paste-btn';
						  		// var nscClasses = 'nsc btn btn-default';
						  		var nspClasses = 'nsp';
						  		var connection_edit_btn_class = "connecting-edit-btn";
						  		var paste_edit_btn_class = "paste-edit-btn";

						  		if(value.is_pasted == 1){
						  			connection_btn_disabled = 'link-disabled unclickable'; 
						  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
						  			nsp_btn_disabled = '';
						  			paste_btn_class = 'paste-btn active link-active';
						  			if(value.is_ns_paste == 1){
						  				nspClasses = 'nsp active link-active';
						  			}
						  		}

						  		if(value.is_ns_connect_disabled == 0)
						  			nsc_btn_disabled = '';
						  		else
						  			nsc_btn_disabled = 'link-disabled unclickable';
						  		if(value.is_connect_disabled == 1){
						  			connection_btn_disabled = 'link-disabled unclickable'; 
						  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
						  		}
						  		if(value.is_ns_paste_disabled == 1)
						  			nsp_btn_disabled = 'link-disabled unclickable'; 
						  		if(value.is_paste_disabled == 1){
						  			paste_btn_disabled = 'link-disabled unclickable';
						  			paste_edit_btn_class += ' link-disabled unclickable';
						  		}

						  		var translation_toggle_btn_type = "";

						  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
						  		var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

						  		// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
						  		
						  		var rowClass = "";
						  		var saved_version_source_code = "";
						  		var instantReflectionClass = 'instant-reflection-set-'+value.main_parent_initial_symptom_id;
						  		if(value.active_symptom_type == "comparing"){

						  			translation_toggle_btn_type = "comparative";

						  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
						  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
						  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
						  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
						  			var activeSymptomId = value.comparing_source_symptom_id;
						  			var activeSymptomType = "comparing";
						  			var displaySourceCode = (typeof(value.comparing_source_code) != "undefined" && value.comparing_source_code !== null && value.comparing_source_code != "") ? value.comparing_source_code : "";
						  			
						  			var comparing_source_symptom_highlighted_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
						  			var comparing_source_symptom_highlighted_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
						  			var displaySymptomString = "";

						  			if(value.comparison_language == "en"){
						  				displaySymptomString = comparing_source_symptom_highlighted_en;
						  				
						  				if(comparing_source_original_language == "en"){
						  					var tmpString = "";
						  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+comparing_source_symptom_highlighted_en+'</div>' : "";
						  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  				else{
						  					var tmpString = "";
						  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
						  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparing_source_symptom_highlighted_en+'</div>' : "";

						  					displaySymptomString = tmpString;
						  				}
						  			}
						  			else{
						  				displaySymptomString = comparing_source_symptom_highlighted_de;

						  				if(comparing_source_original_language == "de"){
						  					var tmpString = "";
						  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+comparing_source_symptom_highlighted_de+'</div>' : "";
						  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  				else{
						  					var tmpString = "";
						  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
						  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparing_source_symptom_highlighted_de+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  			}

						  			
						  			var displayPercentage = value.percentage+"%";
						  			var rowInlineStyle = 'style="border-top: dotted; border-color: #ddd;"';
						  			// var symptomColumnInlineStyle = 'style="padding-left: 40px;"';
						  			var symptomColumnInlineStyle = '';
						  			var commandColumnClass = ' unclickable';

						  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
							  			commentClasses += ' active';
							  		}
							  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
							  			footnoteClasses += ' active';
							  		}
							  		if(displaySourceCode != value.comparing_saved_version_source_code)
							  			saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';
						  		}else{

						  			translation_toggle_btn_type = "initial";

						  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
						  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
						  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
						  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
						  			var activeSymptomId = value.initial_source_symptom_id;
						  			var activeSymptomType = "initial";
						  			var displaySourceCode = (typeof(value.initial_source_code) != "undefined" && value.initial_source_code !== null && value.initial_source_code != "") ? value.initial_source_code : "";
						  			
						  			var initial_source_symptom_de = (typeof(value.initial_source_symptom_de) != "undefined" && value.initial_source_symptom_de !== null && value.initial_source_symptom_de != "") ? b64DecodeUnicode(value.initial_source_symptom_de) : "";
						  			var initial_source_symptom_en = (typeof(value.initial_source_symptom_en) != "undefined" && value.initial_source_symptom_en !== null && value.initial_source_symptom_en != "") ? b64DecodeUnicode(value.initial_source_symptom_en) : "";
						  			var displaySymptomString = "";

						  			if(value.comparison_language == "en"){
						  				displaySymptomString = initial_source_symptom_en;

						  				if(initial_source_original_language == "en"){
						  					var tmpString = "";
						  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+initial_source_symptom_en+'</div>' : "";
						  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+initial_source_symptom_de+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  				else{
						  					var tmpString = "";
						  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+initial_source_symptom_de+'</div>' : "";
						  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initial_source_symptom_en+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  			}
						  			else{
						  				displaySymptomString = initial_source_symptom_de;

						  				if(initial_source_original_language == "de"){
						  					var tmpString = "";
						  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+initial_source_symptom_de+'</div>' : "";
						  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+initial_source_symptom_en+'</div>' : "";
						  					
						  					displaySymptomString = tmpString;
						  				}
						  				else{
						  					var tmpString = "";
						  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+initial_source_symptom_en+'</div>' : "";
						  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initial_source_symptom_de+'</div>' : "";

						  					displaySymptomString = tmpString;
						  				}
						  			}
						  			
						  			var displayPercentage = "";
						  			var rowInlineStyle = '';
						  			var symptomColumnInlineStyle = '';
						  			var commandColumnClass = '';

						  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
							  			commentClasses += ' active';
							  		}
							  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
							  			footnoteClasses += ' active';
							  		}
							  		if(response.un_matched_symptoms_set_number == 0)
							  			rowClass = " initial-source-symptom-row";

							  		if(displaySourceCode != value.initial_saved_version_source_code)
							  			saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';
						  		}

						  		if(value.is_unmatched_symptom == 1){
						  			instantReflectionClass += ' instant-reflection-unmatched-row';
						  			translation_toggle_btn_type = "comparative";
						  		}
						  		// var rowClass = "";
						  		// if(value.is_initial_source == 1)
						  		// 	var rowClass = "initial-source-symptom-row";

						  		// Matched symptom ids
						  		var matched_symptom_ids_string = "";
						  		if(typeof(response.matched_symptom_ids) != "undefined" && response.matched_symptom_ids !== null) {
						  			matched_symptom_ids_string = response.matched_symptom_ids.join();	
						  		}
						  		$('.matched-symptom-ids').val(matched_symptom_ids_string);

						  		//console.log(comparingSymptomHighlightedEndcod);
						  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+rowClass+'" '+rowInlineStyle+'>';
						  		html += '	<td style="width: 12%;" class="text-center">'+displaySourceCode+saved_version_source_code+'</td>';
						  		html += '	<td '+symptomColumnInlineStyle+'>'+displaySymptomString+'</td>';
						  		html += '	<td style="width: 5%;" class="text-center">'+displayPercentage+'</td>';
						  		html += '	<th style="width: 15%;">';
						  		html += '		<ul class="info-linkage-group">';
						  		html += '			<li>';
						  		html += '				<a onclick="showInfo('+activeSymptomId+',\'original\', this)" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-info-circle"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+activeSymptomId+', \'original\', this)" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-comment-alt"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+activeSymptomId+', \'original\', this)" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-sticky-note"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
						  		html += '			</li>';
						  		if(value.is_final_version_available != 0 && value.has_connections == 1){
						  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
						  			var fvName = "";
						  			var fvTitle = "";
						  			if(value.is_final_version_available == 1){
						  				fvName = "CE";
						  				fvTitle = "Connect edit";
						  			} else if(value.is_final_version_available == 2){
						  				fvName = "PE";
						  				fvTitle = "Paste edit";
						  			}
						  			html += '			<li>';
							  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
							  		html += '			</li>';
						  		}
						  		html += '			<li>';
						  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="0" data-is-recompare="0" data-initial-source-id="'+value.initial_source_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-connections-main-parent-symptom-id="'+value.connections_main_parent_symptom_id+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="'+activeSymptomType+'" data-is-connection-loaded="0" data-comparing-source-ids="'+response.comparing_source_ids+'" data-source-arznei-id="'+value.source_arznei_id+'" data-saved-comparison-comparing-source-ids="'+response.saved_comparison_comparing_source_ids+'" data-removable-row-class-chain=""><i class="fas fa-plus"></i></a>';
						  		html += '			</li>';
						  		html += '		</ul>';
						  		html += '		<input type="hidden" name="saved_comparisons_backup_id[]" id="saved_comparisons_backup_id_'+uniqueId+'" value="'+saved_comparisons_backup_id+'">';
						  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+value.source_arznei_id+'">';
						  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
						  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
						  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
						  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
						  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
						  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
						  		
						  		// Initial German
						  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';

						  		// Initial English
						  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
						  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';

						  		// Comparing German
						  		html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';

						  		// Comparing English
						  		html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
						  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';
						  		
						  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';
						  		
						  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
						  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.percentage+'">';
						  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="0">';
						  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="0">';
						  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="">';
						  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
						  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
						  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
						  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
						  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+matched_symptom_ids_string+'">';
						  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+value.main_parent_initial_symptom_id+'">';
						  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+value.connections_main_parent_symptom_id+'">';
						  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+value.similarity_rate+'">';
						  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+value.active_symptom_type+'">';
						  		html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+response.comparing_source_ids+'">';
						  		html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+value.comparison_option+'">';
						  		html += '		<input type="hidden" name="comparison_initial_source_id[]" id="comparison_initial_source_id_'+uniqueId+'" value="'+value.comparison_initial_source_id+'">';
						  		html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+response.saved_comparison_comparing_source_ids+'">';
						  		html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+value.is_unmatched_symptom+'">';
						  		html += '	</th>';
						  		if(value.active_symptom_type == "comparing"){
							  		html += '	<th style="width: 19%;" class="command-column'+commandColumnClass+'">';
							  		html += '		<ul class="command-group">';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="nsc '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="connecting-btn '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-link"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connection_edit_btn_class+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="3">CE</a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'">P</a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+paste_edit_btn_class+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="4">PE</a>';
							  		html += '			</li>';
							  		html += '		</ul>';
							  		html += '	</th>';
						  		}
						  		else{
						  			html += '	<th style="width: 19%;" class="">';
						  			html += '	</th>';	
						  		}
						  		html += '</tr>';

						  		nummerOfRecordFetch = nummerOfRecordFetch + 1;

							});
							if(html != ""){
						  		// Removing hidden fields of saving comparison result
						  		var hiddenformElements = '';
								$( ".save-data" ).each(function() {
								  	var inputValue = $(this).val();
								  	var inputName = $(this).attr('name');
								  	var inputId = $(this).attr('id');
								  	if(inputName == "comparison_name")
								  		hiddenformElements += '<input class="hidden-save-data comparison-name" type="hidden" name="'+inputName+'" id="'+inputId+'_save" value="'+inputValue+'">';
								  	else
								  		hiddenformElements += '<input class="hidden-save-data" type="hidden" name="'+inputName+'" id="'+inputId+'_save" value="'+inputValue+'">';
								});
						  		var batchTable ='<form id="batch_result_form_'+step+'" class="batch-result-form append-recognizer">';
						  		if(response.un_matched_symptoms_set_number == 1)
						  		{
						  			batchTable +='	<table class="table">';
						  			batchTable +='		<tr>';
						  			batchTable +='			<td colspan="5" class="text-center" style="padding-top: 20px; padding-bottom: 20px; background-color: #F1FB3A; font-weight: 600; font-size: 16px;">Verbleibende, nicht übereinstimmende Symptome</td>';
						  			batchTable +='		</tr>';
						  			batchTable +='	</table>';
						  		}
						  		batchTable +='	<table class="table table-bordered">';
						  		batchTable += html;
						  		batchTable +='	</table>';
						  		batchTable += hiddenformElements;
						  		batchTable +='</form>';
						  		$(".no-records-found").remove();
						  		$('#loadingTr').remove();
						  		$('.append-recognizer').last().after(batchTable);
						  	}
						}
						$(document).ready(function () {

							$('.label-currently-processing').html("STEP "+response.process_stage);
							$('.progress-thead .progress-bar').attr('aria-valuenow', response.progress_percentage).css('width', response.progress_percentage+"%");
							$('.progress-thead .progress-bar').html(response.progress_percentage+"%");
						    console.log('I m loaded!');
						    $("#numberOfRecord").html(nummerOfRecordFetch);
						  	if( 'done' == response.step ) {
								setTimeout(function() {
									$(".result-sub-btn").removeClass('hidden');
									$(".head-panel-sub-ul").removeClass('hidden');
									$(".command-column").removeClass('unclickable');
									$(".vbtn").removeClass('unclickable');
									$("#comparison_name").val(response.system_generated_comparison_name);
								    $(".progress-thead").remove();
								    $('#symptom_comparison_form').removeClass('unclickable');
								    $('#compare_submit_btn').prop('disabled', false);
								    $('#search_submit_btn').prop('disabled', false);
								}, 2000);

							} else if( 'error' == response.step ) {
								if ( window.console && window.console.log ) {
									console.log( "Exception error" );
									console.log( response );
								}
							}else {
								// $('.edd-progress div').animate({
								// 	width: response.percentage + '%',
								// }, 50, function() {
								// 	// Animation complete.
								// });
								setTimeout(function() {
								    self.process_step( parseInt( response.step ), nummerOfRecordFetch, parseInt( response.total_batches_in_part1 ), parseInt( response.total_batches_in_part2 ), parseInt( response.is_stage2_checked ), parseInt( response.un_matched_symptoms_set_number ), data, response.matched_symptom_ids, self );
								}, 3000);
							}
						});
					}
				}
			}).fail(function (response) {
				if ( window.console && window.console.log ) {
					console.log( response );
				}
				$('#symptom_comparison_form').removeClass('unclickable');
				$('#compare_submit_btn').prop('disabled', false);
				$('#search_submit_btn').prop('disabled', false);
			});
		}

		// Show symptom informations
		function showInfo(symptomId, source_type, el){
			var uniqueId = $(el).attr("data-unique-id");
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+uniqueId).val();
			var comparingSourceIds = $("#comparing_source_ids_"+uniqueId).val();
			var sourceArzneiId = $("#source_arznei_id_"+uniqueId).val();
			var comparisonOption = $("#comparison_option_"+uniqueId).val();
			var comparisonInitialSourceId = $("#comparison_initial_source_id_"+uniqueId).val();

			$("#info_modal_loader .loading-msg").removeClass('hidden');
			$("#info_modal_loader .error-msg").html('');
			if($("#info_modal_loader").hasClass('hidden'))
				$("#info_modal_loader").removeClass('hidden');

			$("#populated_info_data").remove();
			$("#symptomInfoModal").modal('show');
			$.ajax({
				type: 'POST',
				url: 'get-backup-symptom-info.php',
				data: {
					symptom_id: symptomId,
					source_type: source_type,
					comparison_initial_source_id: comparisonInitialSourceId,
					comparison_comparing_source_ids: comparingSourceIds,
					arznei_id: sourceArzneiId,
					comparison_option: comparisonOption,
					saved_comparisons_backup_id: saved_comparisons_backup_id
				},
				dataType: "json",
				success: function( response ) {
					if(response.status == "success"){
						var resultData = null;
						try {
							resultData = JSON.parse(response.result_data); 
						} catch (e) {
							resultData = response.result_data;
						}
						if(!$("#info_modal_loader").hasClass('hidden'))
							$("#info_modal_loader").addClass('hidden');

						var Beschreibung_de = (resultData.Beschreibung_de != "" && resultData.Beschreibung_de != null) ? resultData.Beschreibung_de : "-";
						var Beschreibung_en = (resultData.Beschreibung_en != "" && resultData.Beschreibung_en != null) ? resultData.Beschreibung_en : "-";
						var BeschreibungOriginal_book_format_de = (resultData.BeschreibungOriginal_book_format_de != "" && resultData.BeschreibungOriginal_book_format_de != null) ? resultData.BeschreibungOriginal_book_format_de : "-";
						var BeschreibungOriginal_book_format_en = (resultData.BeschreibungOriginal_book_format_en != "" && resultData.BeschreibungOriginal_book_format_en != null) ? resultData.BeschreibungOriginal_book_format_en : "-";
						var BeschreibungOriginal_de = (resultData.BeschreibungOriginal_de != "" && resultData.BeschreibungOriginal_de != null) ? resultData.BeschreibungOriginal_de : "-";
						var BeschreibungOriginal_en = (resultData.BeschreibungOriginal_en != "" && resultData.BeschreibungOriginal_en != null) ? resultData.BeschreibungOriginal_en : "-";
						var BeschreibungOriginal_with_grading_de = (resultData.BeschreibungOriginal_with_grading_de != "" && resultData.BeschreibungOriginal_with_grading_de != null) ? resultData.BeschreibungOriginal_with_grading_de : "-";
						var BeschreibungOriginal_with_grading_en = (resultData.BeschreibungOriginal_with_grading_en != "" && resultData.BeschreibungOriginal_with_grading_en != null) ? resultData.BeschreibungOriginal_with_grading_en : "-";
						var searchable_text_with_grading_de = (resultData.searchable_text_with_grading_de != "" && resultData.searchable_text_with_grading_de != null) ? resultData.searchable_text_with_grading_de : "-";
						var searchable_text_with_grading_en = (resultData.searchable_text_with_grading_en != "" && resultData.searchable_text_with_grading_en != null) ? resultData.searchable_text_with_grading_en : "-";
						var BeschreibungFull_with_grading_de = (resultData.BeschreibungFull_with_grading_de != "" && resultData.BeschreibungFull_with_grading_de != null) ? resultData.BeschreibungFull_with_grading_de : "-";
						var BeschreibungFull_with_grading_en = (resultData.BeschreibungFull_with_grading_en != "" && resultData.BeschreibungFull_with_grading_en != null) ? resultData.BeschreibungFull_with_grading_en : "-";
						var Fussnote = (resultData.Fussnote != "" && resultData.Fussnote != null) ? resultData.Fussnote : "-";
						var Verweiss = (resultData.Verweiss != "" && resultData.Verweiss != null) ? resultData.Verweiss : "-";
						var Kommentar = (resultData.Kommentar != "" && resultData.Kommentar != null) ? resultData.Kommentar : "-";
						var Remedy = (resultData.Remedy != "" && resultData.Remedy != null) ? resultData.Remedy : "-";
						var EntnommenAus = (resultData.EntnommenAus != "" && resultData.EntnommenAus != null) ? resultData.EntnommenAus : "-";
						var Pruefer = (resultData.Pruefer != "" && resultData.Pruefer != null) ? resultData.Pruefer : "-";
						var symptom_of_different_remedy = (resultData.symptom_of_different_remedy != "" && resultData.symptom_of_different_remedy != null) ? resultData.symptom_of_different_remedy : "-";
						var BereichID = (resultData.BereichID != "" && resultData.BereichID != null) ? resultData.BereichID : "-";
						var Unklarheiten = (resultData.Unklarheiten != "" && resultData.Unklarheiten != null) ? resultData.Unklarheiten : "-";
						// Source Data
						var titel = (resultData.titel != "" && resultData.titel != null) ? resultData.titel : "-";
						var code = (resultData.code != "" && resultData.code != null) ? resultData.code : "-";
						var autor_or_herausgeber = (resultData.autor_or_herausgeber != "" && resultData.autor_or_herausgeber != null) ? resultData.autor_or_herausgeber : "-";
						var jahr = (resultData.jahr != "" && resultData.jahr != null) ? resultData.jahr : "-";
						var band = (resultData.band != "" && resultData.band != null) ? resultData.band : "-";
						var auflage = (resultData.auflage != "" && resultData.auflage != null) ? resultData.auflage : "-";

						var is_final_version_available = (resultData.is_final_version_available != "" && resultData.is_final_version_available != null) ? resultData.is_final_version_available : 0;
						var fv_con_initial_symptom_de = (resultData.fv_con_initial_symptom_de != "" && resultData.fv_con_initial_symptom_de != null) ? resultData.fv_con_initial_symptom_de : "-";
						var fv_con_initial_symptom_en = (resultData.fv_con_initial_symptom_en != "" && resultData.fv_con_initial_symptom_en != null) ? resultData.fv_con_initial_symptom_en : "-";
						var fv_con_comparative_symptom_de = (resultData.fv_con_comparative_symptom_de != "" && resultData.fv_con_comparative_symptom_de != null) ? resultData.fv_con_comparative_symptom_de : "-";
						var fv_con_comparative_symptom_en = (resultData.fv_con_comparative_symptom_en != "" && resultData.fv_con_comparative_symptom_en != null) ? resultData.fv_con_comparative_symptom_en : "-";
						var fv_symptom_de = (resultData.fv_symptom_de != "" && resultData.fv_symptom_de != null) ? resultData.fv_symptom_de : "-";
						var fv_symptom_en = (resultData.fv_symptom_en != "" && resultData.fv_symptom_en != null) ? resultData.fv_symptom_en : "-";

						var fv_con_initial_source_code = (resultData.fv_con_initial_source_code != "" && resultData.fv_con_initial_source_code != null) ? resultData.fv_con_initial_source_code : "-";
						var fv_con_comparative_source_code = (resultData.fv_con_comparative_source_code != "" && resultData.fv_con_comparative_source_code != null) ? resultData.fv_con_comparative_source_code : "-";

						var symptom_number = (resultData.symptom_number != "" && resultData.symptom_number != null) ? resultData.symptom_number : "-";
						var symptom_page = (resultData.symptom_page != "" && resultData.symptom_page != null) ? resultData.symptom_page : "-";

						var html = '';
						html += '<div id="populated_info_data">';
						html += '	<div class="row">';
						html += '		<div class="col-sm-12"><h4>Symptominformation</h4></div>';
						html += '	</div>';
						html += '	<div class="row">';
						html += '		<!-- <div class="col-sm-4"><p><b>Imported symptom</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Beschreibung_de+'</p></div> -->';
						html += '		<div class="col-sm-12"><p><b>Originalsymptom</b></p></div>';
						html += '		<div class="col-sm-4"><p>Deutsch (de)</p></div>';
						html += '		<div class="col-sm-8"><p>'+BeschreibungOriginal_book_format_de+'</p></div>';
						html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
						html += '		<div class="col-sm-8"><p>'+BeschreibungOriginal_book_format_en+'</p></div>';
						html += '		<div class="col-sm-12"><p><b>Konvertiertes Symptom</b></p></div>';
						html += '		<div class="col-sm-4"><p>Deutsch (de)</p></div>';
						html += '		<div class="col-sm-8"><p>'+BeschreibungFull_with_grading_de+'</p></div>';
						html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
						html += '		<div class="col-sm-8"><p>'+BeschreibungFull_with_grading_en+'</p></div>';

						// html += '		<div class="col-sm-4"><p>Deutsch (de)</p></div>';
						// html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_de+'</p></div>';
						// html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
						// html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_en+'</p></div>';

						if(is_final_version_available != 0){
							html += '		<div class="col-sm-4"><p><b>Final version Symptom</b></p></div>';
							html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6"><p><u>Deutsch (de)</u></p></div><div class="col-sm-6"><p><u>Englisch (en)</u></p></div></div></div>';
							html += '		<div class="col-sm-4">Initial<span class="pull-right">'+fv_con_initial_source_code+'</span></div>';
							html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_con_initial_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_con_initial_symptom_en+'</p></div></div></div>';
							html += '		<div class="col-sm-4">Comparative<span class="pull-right">'+fv_con_comparative_source_code+'</span></div>';
							html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_con_comparative_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_con_comparative_symptom_en+'</p></div></div></div>';
							html += '		<div class="col-sm-4">Final version</div>';
							html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_symptom_en+'</p></div></div></div>';
						}
						html += '		<div class="col-sm-4"><p><b>Arznei</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Remedy+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Fußnote</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Fussnote+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Prüfer</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Pruefer+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Literatur</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+EntnommenAus+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Kapitel</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+BereichID+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Kommentar</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Kommentar+'</p></div>';
						
						/*html += '		<div class="col-sm-4"><p><b>Verweiss</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Verweiss+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Symptom of different remedy</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+symptom_of_different_remedy+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Unklarheiten</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+Unklarheiten+'</p></div>';*/
						html += '	</div>';
						// html += '	<hr>';
						html += '	<div class="row">';
						html += '		<div class="col-sm-12"><h4>Information der Quelle</h4></div>';
						html += '	</div>';
						html += '	<div class="row">';
						html += '		<div class="col-sm-4"><p><b>Titel</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+titel+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Kürzel/Code</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+code+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Autor/Herausgeber</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+autor_or_herausgeber+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Jahr</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+jahr+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Band</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+band+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Auflage</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+auflage+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Symptomnummer</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+symptom_number+'</p></div>';
						html += '		<div class="col-sm-4"><p><b>Seite</b></p></div>';
						html += '		<div class="col-sm-8"><p>'+symptom_page+'</p></div>';
						html += '	</div>';
						html += '</div>';

						$("#info_container").append(html);
					}else{
						$("#info_modal_loader .loading-msg").addClass('hidden');
						$("#info_modal_loader .error-msg").html('Something went wrong!');
						console.log(response);
					}
				}
			}).fail(function (response) {
				$("#info_modal_loader .loading-msg").addClass('hidden');
				$("#info_modal_loader .error-msg").html('Something went wrong!');
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		}

		function showComment(symptomId, source_type, el){
			var uniqueId = $(el).attr("data-unique-id");
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+uniqueId).val();
			var comparingSourceIds = $("#comparing_source_ids_"+uniqueId).val();
			var sourceArzneiId = $("#source_arznei_id_"+uniqueId).val();
			var comparisonOption = $("#comparison_option_"+uniqueId).val();
			var comparisonInitialSourceId = $("#comparison_initial_source_id_"+uniqueId).val();

			$("#comment_modal_loader .loading-msg").removeClass('hidden');
			$("#comment_modal_loader .error-msg").html('');
			if($("#comment_modal_loader").hasClass('hidden'))
				$("#comment_modal_loader").removeClass('hidden');

			$("#populated_comment_data").remove();
			$("#symptomCommentModal").modal('show');
			$.ajax({
				type: 'POST',
				url: 'get-backup-symptom-info.php',
				data: {
					symptom_id: symptomId,
					source_type: source_type,
					comparison_initial_source_id: comparisonInitialSourceId,
					comparison_comparing_source_ids: comparingSourceIds,
					arznei_id: sourceArzneiId,
					comparison_option: comparisonOption,
					saved_comparisons_backup_id: saved_comparisons_backup_id
				},
				dataType: "json",
				success: function( response ) {
					if(response.status == "success"){
						var resultData = null;
						try {
							resultData = JSON.parse(response.result_data); 
						} catch (e) {
							resultData = response.result_data;
						}
						if(!$("#comment_modal_loader").hasClass('hidden'))
							$("#comment_modal_loader").addClass('hidden');

						var Kommentar = (resultData.Kommentar != "" && resultData.Kommentar != null) ? resultData.Kommentar : "No comment available.";
						var html = '';
						html += '<div id="populated_comment_data">';
						html += '	<div class="row">';
						html += '		<div class="col-sm-12 text-center">';
						html += '			'+Kommentar+'';
						// html += '			<textarea name="symptom_comment_modal" id="symptom_comment_modal" placeholder="Comment" class="form-control" rows="5" cols="50">'+Kommentar+'</textarea>';
						html += '			<span class="error-text"></span>';
						html += '			<input type="hidden" name="symptom_id_comment_modal" id="symptom_id_comment_modal" value="'+symptomId+'">';
						html += '			<input type="hidden" name="comment_modal_unique_id" id="comment_modal_unique_id" value="'+uniqueId+'">';
						html += '		</div>';
						html += '	</div>';
						html += '</div>';

						$("#comment_container").append(html);
					}else{
						$("#comment_modal_loader .loading-msg").addClass('hidden');
						$("#comment_modal_loader .error-msg").html('Something went wrong!');
						console.log(response);
					}
				}
			}).fail(function (response) {
				$("#comment_modal_loader .loading-msg").addClass('hidden');
				$("#comment_modal_loader .error-msg").html('Something went wrong!');
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		}

		function showFootnote(symptomId, source_type, el){
			var uniqueId = $(el).attr("data-unique-id");
			var saved_comparisons_backup_id = $("#saved_comparisons_backup_id_"+uniqueId).val();
			var comparingSourceIds = $("#comparing_source_ids_"+uniqueId).val();
			var sourceArzneiId = $("#source_arznei_id_"+uniqueId).val();
			var comparisonOption = $("#comparison_option_"+uniqueId).val();
			var comparisonInitialSourceId = $("#comparison_initial_source_id_"+uniqueId).val();

			$("#footnote_modal_loader .loading-msg").removeClass('hidden');
			$("#footnote_modal_loader .error-msg").html('');
			if($("#footnote_modal_loader").hasClass('hidden'))
				$("#footnote_modal_loader").removeClass('hidden');

			$("#populated_footnote_data").remove();
			$("#symptomFootnoteModal").modal('show');
			$.ajax({
				type: 'POST',
				url: 'get-backup-symptom-info.php',
				data: {
					symptom_id: symptomId,
					source_type: source_type,
					comparison_initial_source_id: comparisonInitialSourceId,
					comparison_comparing_source_ids: comparingSourceIds,
					arznei_id: sourceArzneiId,
					comparison_option: comparisonOption,
					saved_comparisons_backup_id: saved_comparisons_backup_id
				},
				dataType: "json",
				success: function( response ) {
					if(response.status == "success"){
						var resultData = null;
						try {
							resultData = JSON.parse(response.result_data); 
						} catch (e) {
							resultData = response.result_data;
						}
						if(!$("#footnote_modal_loader").hasClass('hidden'))
							$("#footnote_modal_loader").addClass('hidden');

						var Fussnote = (resultData.Fussnote != "" && resultData.Fussnote != null) ? resultData.Fussnote : "No footnote available.";
						var html = '';
						html += '<div id="populated_footnote_data">';
						html += '	<div class="row">';
						html += '		<div class="col-sm-12 text-center">';
						html += '			'+Fussnote+'';
						// html += '			<textarea name="symptom_footnote_modal" id="symptom_footnote_modal" placeholder="Footnote" class="form-control" rows="5" cols="50">'+Fussnote+'</textarea>';
						html += '			<span class="error-text"></span>';
						html += '			<input type="hidden" name="symptom_id_footnote_modal" id="symptom_id_footnote_modal" value="'+symptomId+'">';
						html += '			<input type="hidden" name="footnote_modal_unique_id" id="footnote_modal_unique_id" value="'+uniqueId+'">';
						html += '		</div>';
						html += '	</div>';
						html += '</div>';

						$("#footnote_container").append(html);
					}else{
						$("#footnote_modal_loader .loading-msg").addClass('hidden');
						$("#footnote_modal_loader .error-msg").html('Something went wrong!');
						console.log(response);
					}
				}
			}).fail(function (response) {
				$("#footnote_modal_loader .loading-msg").addClass('hidden');
				$("#footnote_modal_loader .error-msg").html('Something went wrong!');
				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		}
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>