<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Displaying a saved comparison's(which is in backups) all symptoms and their connections
	*/
?>


<?php  
	$mid = (isset($_GET['mid']) AND $_GET['mid'] != "") ? $_GET['mid'] : null;
	$qid = "";
	if($mid != ""){
		$scResult = mysqli_query($db,"SELECT Q.quelle_id, Q.is_materia_medica FROM quelle AS Q LEFT JOIN quelle_import_master_backup AS QM ON Q.quelle_id = QM.quelle_id WHERE QM.id = '".$mid."'");
		if(mysqli_num_rows($scResult) > 0){
			$scRow = mysqli_fetch_assoc($scResult);
			$qid = (isset($scRow['quelle_id']) AND $scRow['quelle_id'] != "") ? $scRow['quelle_id'] : "";	
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Source Connections</title>
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
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
	            <div id="loader" class="form-group text-center">
					Loading is not complete please wait <img src="assets/img/loader.gif" alt="Loading...">
				</div>
			</div>
		</div>
	</div>
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
				<div id="comparison_result_cnr" class="master-table-cnr">
					<input type="hidden" name="qid" id="qid" value="<?php echo $qid; ?>">
					<p><font size="4" color="red">*</font>  This is a view only page you can not perform connect or past operations.<br></p>
					<ul class="head-checkbox-panel-before-table">
	            		<li><label>Open translations</label></li>
	            		<li>
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
				  	<table id="resultTable" class="table table-bordered heading-table append-recognizer">
					    <thead class="heading-table-bg">
					      	<tr>
					      		<th style="width: 12%;">Source</th>
						        <th id="column_heading_symptom">Symptom</th>
						        <th style="width: 5%;" class="text-center">%</th>
						        <th style="width: 15%;" class="text-center linkage-column comparison-only-column">INFO & LINKAGE</th>
						        <th style="width: 19%;" class="text-center comparison-only-column">Command</th>
					      	</tr>
					    </thead>
					</table>
					<form id="batch_result_form_1" class="batch-result-form append-recognizer">
						<table class="table table-bordered">
							<?php
								if($mid != "")
								{
									$symResult = mysqli_query($db, "SELECT QI.id, QI.original_symptom_id, QI.quelle_code, QI.quelle_id, QI.original_quelle_id, QI.arznei_id, QI.final_version_de, QI.final_version_en, QI.BeschreibungPlain_de, QI.BeschreibungPlain_en, QI.BeschreibungOriginal_de, QI.BeschreibungOriginal_en, QI.BeschreibungFull_de, QI.BeschreibungFull_en, QI.searchable_text_de, QI.searchable_text_en, QI.is_final_version_available, QI.Kommentar, QI.Fussnote, QIM.arznei_id, SC.comparison_option, SC.comparison_language, SC.initial_source_id as comparison_initial_source_id, SC.comparing_source_ids as comparing_source_ids, SC.id as saved_comparisons_backup_id FROM quelle_import_backup AS QI JOIN saved_comparisons_backup as SC ON QI.quelle_id = SC.quelle_id JOIN quelle_import_master_backup AS QIM ON QI.quelle_id = QIM.quelle_id WHERE QIM.id = '".$mid."' AND QI.master_id = '".$mid."'");
									if(mysqli_num_rows($symResult) > 0){
										while($symRow = mysqli_fetch_array($symResult)){
											// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
											if($symRow['is_final_version_available'] != 0){
												$iniSymptomString_de =  $symRow['final_version_de'];
												$iniSymptomString_en =  $symRow['final_version_en'];
											} else {
												if(isset($symRow['comparison_option']) AND $symRow['comparison_option'] == 1){
													$iniSymptomString_de =  $symRow['searchable_text_de'];
													$iniSymptomString_en =  $symRow['searchable_text_en'];
												}
												else{
													$iniSymptomString_de =  $symRow['BeschreibungFull_de'];
													$iniSymptomString_en =  $symRow['BeschreibungFull_en'];
												}
											}
												
											// initial source symptom string Bfore convertion(this string is used to store in the connecteion table)  
											$iniSymptomStringBeforeConversion_de = ($iniSymptomString_de != "") ? base64_encode($iniSymptomString_de) : "";
											$iniSymptomStringBeforeConversion_en = ($iniSymptomString_en != "") ? base64_encode($iniSymptomString_en) : "";

											// Apply dynamic conversion (this string is used in displying the symptom)
											// [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
											// [5th parameter(Optional)] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
											// convertTheSymptom()
											$iniSymptomString_de = ($iniSymptomString_de != "") ? convertTheSymptom($iniSymptomString_de, $symRow['original_quelle_id'], $symRow['arznei_id'], $symRow['is_final_version_available'], 0, $symRow['id'], $symRow['original_symptom_id']) : "";
											$iniSymptomString_en = ($iniSymptomString_en != "") ? convertTheSymptom($iniSymptomString_en, $symRow['original_quelle_id'], $symRow['arznei_id'], $symRow['is_final_version_available'], 0, $symRow['id'], $symRow['original_symptom_id']) : "";
											// $iniSymptomString = base64_encode($iniSymptomString);

											$commentClasses = "";
									  		$footnoteClasses = "";
									  		if($symRow['Kommentar'] != ""){
									  			$commentClasses .= ' active';
									  		}
									  		if($symRow['Fussnote'] != ""){
									  			$footnoteClasses .= ' active';
									  		}

											$iniHasConnections = 0;
											$isFurtherConnectionsAreSaved = 1;
											$ceheckConnectionResult = mysqli_query($db,"SELECT id, is_saved FROM symptom_connections_backup WHERE ((initial_source_symptom_id = '".$symRow['id']."' AND initial_source_type = 'backup') OR (comparing_source_symptom_id = '".$symRow['id']."' AND comparing_source_type = 'backup')) AND (is_connected = 1 OR is_pasted = 1)");
											if(mysqli_num_rows($ceheckConnectionResult) > 0){
												$iniHasConnections = 1;
												while($checkConRow = mysqli_fetch_array($ceheckConnectionResult)){
													if($checkConRow['is_saved'] == 0){
														$isFurtherConnectionsAreSaved = 0;
														break;
													}
												}
											}
											$FVBtnClasses = "FV-btn";
											if($symRow['is_final_version_available'] != 0)
						  						$FVBtnClasses .= " active";

											if($iniHasConnections == 1){
												if($isFurtherConnectionsAreSaved == 1){
													$vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved';
													$FVBtnClasses .= " link-active-saved";
												} else {
									  				$vBtnClasses = 'vbtn vbtn-has-connection active link-active';
									  				$FVBtnClasses .= " link-active";
												}
									  			$vBtnTitle = 'Earlier connections';
									  			$vBtnDisable = '';
									  		} else {
									  			$vBtnClasses = 'vbtn unclickable';
									  			$vBtnTitle = 'Earlier connections';
									  			$vBtnDisable = 'link-disabled unclickable';
									  		}

									  		$completeSourceCodeHtml = $symRow['quelle_code'];
											$getQuelleResult = mysqli_query($db,"SELECT code, jahr, quelle_type_id FROM quelle_backup WHERE quelle_id = '".$symRow['quelle_id']."'");
											if(mysqli_num_rows($getQuelleResult) > 0){
												$quelleRow = mysqli_fetch_assoc($getQuelleResult);
												if($quelleRow['quelle_type_id'] == 3)
													$preparedQuelleCode = $quelleRow['code'];
												else{
													if($quelleRow['jahr'] != "" AND $quelleRow['code'] != "")
														$rowQuelleCode = trim(str_replace(trim($quelleRow['jahr']), '', $quelleRow['code']));
													else
														$rowQuelleCode = trim($quelleRow['code']);
													$preparedQuelleCode = trim($rowQuelleCode." ".$quelleRow['jahr']);
												}

												$initial_saved_version_source_code = ($preparedQuelleCode != "") ? $preparedQuelleCode : "";
												if($symRow['quelle_code'] != $initial_saved_version_source_code)
													$completeSourceCodeHtml .= '<br><span class= "saved-version-source-code">'.$initial_saved_version_source_code.'</span>';
											}

											$originSourceYear = "";
											$originSourceLanguage = "";
											$originQuelleResult = mysqli_query($db,"SELECT quelle.jahr, quelle.quelle_type_id, quelle.sprache FROM quelle WHERE quelle.quelle_id = '".$symRow['original_quelle_id']."'");
											if(mysqli_num_rows($originQuelleResult) > 0){
												$originQuelleRow = mysqli_fetch_assoc($originQuelleResult);
												$originSourceYear = $originQuelleRow['jahr'];
												if($originQuelleRow['sprache'] == "deutsch")
													$originSourceLanguage = "de";
												else if($originQuelleRow['sprache'] == "englisch") 
													$originSourceLanguage = "en";
											}

											$displaySymptomString = "";
											if($originSourceLanguage == "en"){
												$displaySymptomString = ($iniSymptomString_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'.$iniSymptomString_en.'</div>' : "";
												$displaySymptomString .= ($iniSymptomString_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'.$iniSymptomString_de.'</div>' : "";
											} else {
												$displaySymptomString = ($iniSymptomString_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'.$iniSymptomString_de.'</div>' : "";
												$displaySymptomString .= ($iniSymptomString_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'.$iniSymptomString_en.'</div>' : "";
											}
											?>
											<tr id="row_<?php echo $symRow['id']; ?>" class="initial-source-symptom-row">
												<td style="width: 12%;" class="text-center"><?php echo $completeSourceCodeHtml; ?></td>
												<td><?php echo $displaySymptomString; ?></td>
												<td style="width: 5%;" class="text-center"></td>
												<th style="width: 15%;">
													<ul class="info-linkage-group">
														<li>
															<a onclick="showInfo(<?php echo $symRow['id']; ?>, 'backup', this)" title="info" href="javascript:void(0)" data-item="info" data-unique-id="<?php echo $symRow['id']; ?>" data-comparison-initial-source-id="<?php echo $symRow['comparison_initial_source_id']; ?>"><i class="fas fa-info-circle"></i></a>
														</li>
														<li>
															<a class="<?php echo $commentClasses; ?>" id="comment_icon_<?php echo $symRow['id']; ?>" onclick="showComment(<?php echo $symRow['id']; ?>, 'backup', this)" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="<?php echo $symRow['id']; ?>" data-comparison-initial-source-id="<?php echo $symRow['comparison_initial_source_id']; ?>"><i class="fas fa-comment-alt"></i></a>
														</li>
														<li>
															<a class="<?php echo $footnoteClasses; ?>" id="footnote_icon_<?php echo $symRow['id']; ?>" onclick="showFootnote(<?php echo $symRow['id']; ?>, 'backup', this)" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="<?php echo $symRow['id']; ?>" data-comparison-initial-source-id="<?php echo $symRow['comparison_initial_source_id']; ?>"><i class="fas fa-sticky-note"></i></a>
														</li>
														<li>
															<a class="translation-toggle-btn translation-toggle-btn-initial" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="<?php echo $symRow['id']; ?>"><!-- <i class="fas fa-language"></i> -->T</a>
														</li>
														<?php
															if($symRow['is_final_version_available'] != 0){
																// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
																$fvName = "";
													  			$fvTitle = "";
													  			if($symRow['is_final_version_available'] == 1){
													  				$fvName = "CE";
													  				$fvTitle = "Connect edit";
													  			} else if($symRow['is_final_version_available'] == 2){
													  				$fvName = "PE";
													  				$fvTitle = "Paste edit";
													  			}
													  			?>
													  			<li>
																	<a class="<?php echo $FVBtnClasses; ?>" title="<?php echo $fvTitle; ?>" href="javascript:void(0)" data-item="FV" data-unique-id="<?php echo $symRow['id']; ?>"><?php echo $fvName; ?></a>
																</li>
													  			<?php
															} 
														?>
														<li>
															<a href="javascript:void(0)" id="v_btn_<?php echo $symRow['id']; ?>" class="<?php echo $vBtnClasses.' '.$vBtnDisable; ?>" title="Earlier connections" data-unique-id="<?php echo $symRow['id']; ?>" data-v-padding="0" data-source-arznei-id="<?php echo $symRow['arznei_id']; ?>" data-comparison-initial-source-id="<?php echo $symRow['comparison_initial_source_id']; ?>" data-main-parent-initial-symptom-id="<?php echo $symRow['id']; ?>" data-is-connection-loaded="0" data-initial-symptom-id="<?php echo $symRow['id']; ?>" data-removable-row-class-chain=""><i class="fas fa-plus"></i></a>
														</li>
													</ul>
													<input type="hidden" name="saved_comparisons_backup_id[]" id="saved_comparisons_backup_id_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['saved_comparisons_backup_id']; ?>">
													<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['arznei_id']; ?>">
													<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['comparing_source_ids']; ?>">
													<input type="hidden" name="comparison_option_individual[]" id="comparison_option_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['comparison_option']; ?>">
													<input type="hidden" name="comparison_initial_source_id[]" id="comparison_initial_source_id_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['comparison_initial_source_id']; ?>">
													<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_<?php echo $symRow['id']; ?>" value="<?php echo $symRow['comparison_language']; ?>">
												</th>
												<th style="width: 19%;" class=""></th>
											</tr>
											<?php
										}
									}
									else
									{
										echo '<tr class="no-records-found"><td colspan="5" class="text-center">No records found</td></tr>';
									}
								}
								else
								{
									echo '<tr class="no-records-found"><td colspan="5" class="text-center">No records found</td></tr>';
								}
							?>
						</table>
					</form>
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
		        	<?php /*<button type="button" onclick="updateComment()" class="btn btn-primary">Save</button> */ ?>
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
		          	<?php /* <button type="button" onclick="updateFootnote()" class="btn btn-primary">Save</button> */ ?>
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
		          	<?php /*<button type="button" onclick="addnscNote()" class="btn btn-primary">Save</button> */ ?>
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
		          	<?php /*<button type="button" onclick="addnspNote()" class="btn btn-primary">Save</button> */ ?>
		          	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        </div>
		    </div>
	    </div>
	</div>
	<!-- NSP note modal end -->

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
			// var records = $("#totalNumofRecords").val();
			// if(records != ""){
			// 	$("#numberOfRecord").html(records);
			// 	$("#totalNumberDisplay").removeClass('hidden');
			// }
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

    		var comparisonInitialSourceId = $(this).attr("data-comparison-initial-source-id");
		    var parentUniqueId = $(this).attr("data-unique-id");
		    var sourceArzneiId = $(this).attr("data-source-arznei-id");
		    var initialSymptomId = $(this).attr("data-initial-symptom-id");
		    var mainParentInitialSymptomId = $(this).attr("data-main-parent-initial-symptom-id");
		    var isConnectionLoaded = $(this).attr("data-is-connection-loaded");
		    var removableRowClassChain = $(this).attr("data-removable-row-class-chain");
		    var vPadding = $(this).attr("data-v-padding");
		    var rowClass = "removable-"+parentUniqueId;
			removableRowClassChain += rowClass+' ';

			var comparingSourceIds = $("#comparing_source_ids_"+parentUniqueId).val();
    		var comparison_option = $("#comparison_option_"+parentUniqueId).val();

    		var individual_comparison_language = $("#individual_comparison_language_"+parentUniqueId).val();

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
						url: 'get-backup-symptom-connections-view-only.php',
						data: {
							source_arznei_id: sourceArzneiId,
							initial_symptom_id: initialSymptomId,
							individual_comparison_language: individual_comparison_language
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

						  			var vBtnClasses = 'vbtn';
						  			var vBtnTitle = 'Earlier connections';
						  			var vBtnDisable = 'link-disabled unclickable';

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
								  		}
								  		else{
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
								  		}
								  		else{
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

							  			var saved_version_source_code = "";
							  			if(value.initial_source_code != value.initial_saved_version_source_code)
						  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';

								  		html += '<tr id="row_'+uniqueId+'" class="'+removableRowClassChain+rowBgColorClass+'">';
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
								  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'"  data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-active-symptom-type="initial" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-is-connection-loaded="0" data-source-arznei-id="'+sourceArzneiId+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
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

							  			var saved_version_source_code = "";
							  			if(value.comparing_source_code != value.comparing_saved_version_source_code)
						  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';
							  			
							  			
							  			html += '<tr id="row_'+uniqueId+'" class="'+removableRowClassChain+rowBgColorClass+'">';
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
								  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-active-symptom-type="comparing" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-is-connection-loaded="0" data-source-arznei-id="'+sourceArzneiId+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
								  		html += '			</li>';
								  		html += '		</ul>';
							  		}
							  		html += '		<input type="hidden" name="saved_comparisons_backup_id[]" id="saved_comparisons_backup_id_'+uniqueId+'" value="'+value.saved_comparisons_backup_id+'">';
							  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+sourceArzneiId+'">';
							  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
							  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
							  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
							  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
							  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
							  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+mainParentInitialSymptomId+'">';
							  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+activeSymptomTypeIndividual+'">';
							  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="'+value.is_connected+'">';
							  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="'+value.is_ns_connect+'">';
							  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="'+value.ns_connect_note+'">';
							  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
							  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
							  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
							  		html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+comparingSourceIds+'">';
					  				html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+comparison_option+'">';
					  				html += '		<input type="hidden" name="comparison_initial_source_id[]" id="comparison_initial_source_id_'+uniqueId+'" value="'+comparisonInitialSourceId+'">';
					  				html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';
							  		html += '		<input type="hidden" name="individual_connection_language[]" id="individual_connection_language_'+uniqueId+'" value="'+value.connection_language+'">';
							  		html += '	</th>';
							  		html += '	<th style="width: 19%;" class="">';
							  		html += '		<ul class="command-group">';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'"><i class="fas fa-link"></i></a>';
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
							  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'">P</a>';
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

		$(document).on('click', '.nsc', function(){
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
					url: 'symptom-backup-connection-operations-view-only.php',
					data: {
						initial_source_symptom_id: initial_source_symptom_id,
						comparing_source_symptom_id: comparing_source_symptom_id,
						saved_comparisons_backup_id: saved_comparisons_backup_id,
						action: 'get_nsc_note'
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
					url: 'symptom-backup-connection-operations-view-only.php',
					data: {
						unique_id: uniqueId,
						initial_source_symptom_id: initial_source_symptom_id,
						comparing_source_symptom_id: comparing_source_symptom_id,
						saved_comparisons_backup_id: saved_comparisons_backup_id,
						action: 'get_nsp_note'
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
					console.log(response);
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
						html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_de+'</p></div>';
						html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
						html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_en+'</p></div>';

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
					console.log(response);
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