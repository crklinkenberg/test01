<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Here we are displying the Materia medicas. The sources which are available for comparison are listed here. 
	* Once a particular source is used in creating another source(means saved comparison) then that source no longer will be the part of the materia medica.  
	*/
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Materia Medica</title>
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
		<div id="loader" class="form-group text-center">
			Loading is not complete please wait <img src="assets/img/loader.gif" alt="Loading...">
		</div>
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
				<h2>Materia Medica</h2> 
				<div id="comparison_result_cnr" class="master-table-cnr">  
					<form name="result_frm" id="result_frm" action="" method="POST">      
					  	<table class="table table-bordered heading-table heading-table-bg">
						    <thead>
						      	<tr>
						      		<th style="width: 10%;" class="text-center">Jahr</th>
						      		<th style="width: 26%;">Quelle</th>
						      		<th style="width: 12%;">Arznei</th>
							        <th style="width: 15%;">Date</th>
							        <th style="width: 17%;" class="text-center">View</th>
							        <th style="width: 4%;" class="text-center"><a title="Edit comparison name" class="text-info" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a></th>
							        <th class="text-center" style="width: 4%;">de</th>
						        	<th class="text-center" style="width: 4%;">en</th>
							        <th style="width: 4%;" class="text-center"><a title="Download in Word Document" class="text-black"  href="javascript:void(0)"><i class="fas fa-download mm-fa-icon"></i></a></th>
							        <th style="width: 4%;" class="text-center"><a title="Delete" class="text-danger" href="javascript:void(0)"><i class="fas fa-trash-alt mm-fa-icon"></i></a></th>
						      	</tr>
						    </thead>
						</table>
						<table class="table table-bordered">
						    <tbody>
						    	<?php
						    		$sympResult = mysqli_query($db, "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle_import_master AS QIM LEFT JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE Q.is_materia_medica = 1 ORDER BY Q.jahr ASC");
									if(mysqli_num_rows($sympResult) > 0){
										while($row = mysqli_fetch_array($sympResult)){
								?>
											<tr id="row_<?php echo $row['id']; ?>">
												<td style="width: 10%;" class="text-center"><?php echo $row['jahr']; ?></td>
												<td id="comparison_name_container_<?php echo $row['quelle_id']; ?>" style="width: 26%;">
													<?php
														$quelle_name = $row['code'];
														if($row['quelle_type_id'] != 3){
															// if(!empty($row['jahr'])) $quelle_name .= ', '.$row['jahr'];
															// if($row['code'] != $row['titel'])
															// 	if(!empty($row['titel'])) $quelle_name .= ', '.$row['titel'];
															// if($row['quelle_type_id'] == 1){
															// 	if(!empty($row['bucher_autor_or_herausgeber'])) $quelle_name .= ', '.$row['bucher_autor_or_herausgeber'];
															// }else if($row['quelle_type_id'] == 2){
															// 	if(!empty($row['zeitschriften_autor_suchname']) ) 
															// 		$zeitschriften_autor = $row['zeitschriften_autor_suchname']; 
															// 	else 
															// 		$zeitschriften_autor = $row['zeitschriften_autor_vorname'].' '.$row['zeitschriften_autor_nachname'];
															// 	if(!empty($zeitschriften_autor)) $quelle_name .= ', '.$zeitschriften_autor;
															// }

															$quelle_name = (!empty($row['titel'])) ? $row['titel'] : "";
															if($row['quelle_type_id'] == 1){
																if(!empty($row['bucher_autor_or_herausgeber'])) $quelle_name .= ', '.$row['bucher_autor_or_herausgeber'];
															}else if($row['quelle_type_id'] == 2){
																if(!empty($row['zeitschriften_autor_suchname']) ) 
																	$zeitschriften_autor = $row['zeitschriften_autor_suchname']; 
																else if($row['zeitschriften_autor_vorname'] != "" AND $row['zeitschriften_autor_nachname'] != "") 
																	$zeitschriften_autor = $row['zeitschriften_autor_vorname'].' '.$row['zeitschriften_autor_nachname'];
																else
																	$zeitschriften_autor = "";
																if(!empty($zeitschriften_autor)) $quelle_name .= ', '.$zeitschriften_autor;
															}
															if(!empty($row['jahr'])) $quelle_name .= ', '.$row['jahr'];
														} else {
															$scResult = mysqli_query($db,"SELECT comparison_name FROM saved_comparisons WHERE quelle_id = '".$row['quelle_id']."'");
															if(mysqli_num_rows($scResult) > 0){
																$scData = mysqli_fetch_assoc($scResult);
																$quelle_name = (isset($scData['comparison_name']) AND $scData['comparison_name'] != "") ? $scData['comparison_name'] : "";
															}
														}
														
														echo $quelle_name; 
													?>
												</td>
												<td style="width: 12%;">
													<?php
														$arzneiTitle = "";
														$arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei WHERE arznei_id = '".$row['arznei_id']."'");
														if(mysqli_num_rows($arzneiResult) > 0){
															$arzneiData = mysqli_fetch_assoc($arzneiResult);
															$arzneiTitle = (isset($arzneiData['titel']) AND $arzneiData['titel'] != "") ? $arzneiData['titel'] : "";
														}
														echo $arzneiTitle;
													?>
												</td>
												<td style="width: 15%;"><?php echo date('d/m/Y h:i A', strtotime($row['ersteller_datum'])); ?></td>
												<td style="width: 17%;" class="text-center">
													<a title="View symptoms" href="<?php echo $baseUrl; ?>symptoms.php?mid=<?php echo $row['id']; ?>">View symptoms</a>
													<?php if($row['quelle_type_id'] == 3){ ?>
														<?php
															$scId = "";
															$scResult = mysqli_query($db,"SELECT id as sc_id, quelle_id FROM saved_comparisons WHERE quelle_id = '".$row['quelle_id']."'");
															if(mysqli_num_rows($scResult) > 0){
																$scRow = mysqli_fetch_assoc($scResult);
																$scId = (isset($scRow['sc_id']) AND $scRow['sc_id'] != "") ? $scRow['sc_id'] : "";
															}
														?>
														<span class="text-danger"> / </span>
														<!-- Now Showing the view connection page information from the backup section. In the below "View connections" linke page we are displaying the last saved generated source(A_B) in none editable mode -->
														<?php
															$sympResultFrmBackup = mysqli_query($db, "SELECT QIM.id FROM quelle_import_master_backup AS QIM WHERE QIM.original_quelle_id = '".$row['quelle_id']."' ORDER BY QIM.ersteller_datum DESC LIMIT 1");
															if(mysqli_num_rows($sympResultFrmBackup) > 0)
																$backupSecData = mysqli_fetch_assoc($sympResultFrmBackup);
															$backupSecMasterId = (isset($backupSecData['id']) AND $backupSecData['id'] != "") ? $backupSecData['id'] : "";
														?>
														<a title="View connections" href="<?php echo $baseUrl; ?>view-source-connections.php?mid=<?php echo $backupSecMasterId; ?>">View connections</a>
														<!-- <a title="View connections" href="<?php //echo $baseUrl; ?>view-source-connections.php?mid=<?php //echo $row['id']; ?>">View connections</a> -->
														<span class="text-danger"> / </span> 
														<a title="View" href="<?php echo $baseUrl; ?>comparison.php?scid=<?php echo $scId; ?>">View raw</a>
													<?php } ?> 
												</td>
												<td id="edit_container_<?php echo $row['quelle_id']; ?>" style="width: 4%;" class="text-center">
													<?php if($row['quelle_type_id'] == 3){ ?>
														<a id="edit_<?php echo $row['quelle_id']; ?>" data-quelle-id="<?php echo $row['quelle_id']; ?>" data-arznei-id="<?php echo $row['arznei_id']; ?>" data-existing-comparison-name="<?php echo $quelle_name; ?>" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>
													<?php }  ?>
												</td>
												<td id="de_translation_btn_container_<?php echo $row['id']; ?>" class="text-center" style="width: 4%;">
													<?php if($row['is_symptoms_available_in_de'] == 1) { ?>
														<a id="" title="Symptoms available in German" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>
													<?php } else { ?>
														<a id="" title="Add German translation" class="text-primary" onclick="addTranslation(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>, <?php echo $row['id']; ?>, 'de')" href="javascript:void(0)"><i class="fas fa-notes-medical mm-fa-icon"></i></a>
													<?php } ?>
												</td>
												<td id="en_translation_btn_container_<?php echo $row['id']; ?>" class="text-center" style="width: 4%;">
													<?php if($row['is_symptoms_available_in_en'] == 1) { ?>
														<a id="" title="Symptoms available in English" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>
													<?php } else { ?>
														<a id="" title="Add English translation" class="text-primary"  onclick="addTranslation(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>, <?php echo $row['id']; ?>, 'en')" href="javascript:void(0)" href="javascript:void(0)"><i class="fas fa-notes-medical mm-fa-icon"></i></a>
													<?php } ?>
												</td>
												<td style="width: 4%;" class="text-center">
													<a id="download_in_doc_<?php echo $row['quelle_id']; ?>" title="Download in Word Document" class="text-black" target="_blank" href="<?php echo $baseUrl; ?>download-in-word-document.php?mid=<?php echo $row['id']; ?>"><i class="fas fa-download mm-fa-icon"></i></a>
												</td>
												<td style="width: 4%;" class="text-center">
													<a id="delete_<?php echo $row['quelle_id']; ?>" title="Delete" class="text-danger" onclick="deleteTheQuelle(<?php echo $row['quelle_id']; ?>, <?php echo $row['arznei_id']; ?>)" href="javascript:void(0)"><i class="fas fa-trash-alt mm-fa-icon"></i></a>
												</td>
											</tr>
								<?php
										}
									}else{
								?>
										<tr>
											<td colspan="4" class="text-center">No records found</td>
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
		$(window).bind("load", function() {
			console.log('loaded');
			$("#loader").addClass("hidden");
		});

		function deleteTheQuelle(quelle_id, arznei_id){
			var con = confirm("Deleting this Quelle will delete it's all conections and related comparison where this source is used, are you sure you want to delete?");
			if (con)
			{
				$('#delete_'+quelle_id).prop('disabled', true);
				$('#delete_'+quelle_id).html('<img src="assets/img/loader.gif" alt="Loader">');
				$.ajax({
					type: 'POST',
					url: 'delete-quelle.php',
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


		$(document).on('click', '.edit-comparison-name', function(){
			var quelleId = $(this).attr("data-quelle-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    var arzneiId = $(this).attr("data-arznei-id");
		    existingComparisonName = existingComparisonName.trim();
		    $("#comparison_name_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');

		    var comNameHtml = "";
		    comNameHtml += '<div class="input-group">';
		    comNameHtml += '	<input type="text" autocomplete="off" name="edit_comparison_name_'+quelleId+'" id="edit_comparison_name_'+quelleId+'" value="'+existingComparisonName+'" class="form-control" placeholder="Comparison name">';
		    comNameHtml += '	<div class="input-group-btn">';
		    comNameHtml += '		<button id="save_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Save" class="btn btn-default save-edit" type="button"><i class="fas fa-save mm-fa-icon text-success"></i></button>';
		    comNameHtml += '	</div>';
		    comNameHtml += '</div>';

		    var cancelHtml = '<a id="cancel_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Cancel" class="text-danger edit-cancel" href="javascript:void(0)"><i class="fas fa-window-close mm-fa-icon"></i></a>';

		    $("#comparison_name_container_"+quelleId).html(comNameHtml);
		    $("#edit_container_"+quelleId).html(cancelHtml);
		});

		$(document).on('click', '.edit-cancel', function(){
			var quelleId = $(this).attr("data-quelle-id");
			var arzneiId = $(this).attr("data-arznei-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    existingComparisonName = existingComparisonName.trim();
		    $("#comparison_name_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');

		    var editHtml = '<a id="edit_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>';

		    $("#comparison_name_container_"+quelleId).html(existingComparisonName);
		    $("#edit_container_"+quelleId).html(editHtml);
		});

		$(document).on('click', '.save-edit', function(){
			var quelleId = $(this).attr("data-quelle-id");
			var arzneiId = $(this).attr("data-arznei-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    existingComparisonName = existingComparisonName.trim();

		    var comparison_name = $("#edit_comparison_name_"+quelleId).val().trim();
		    $("#comparison_name_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+quelleId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    if(comparison_name != ""){
		    	$.ajax({
					type: 'POST',
					url: 'update-comparison-name.php',
					data: {
						comparison_name: comparison_name,
						quelle_id: quelleId,
						arznei_id: arzneiId,
						existing_comparison_name: existingComparisonName
					},
					dataType: "json",
					success: function( response ) {
						console.log(response);
						if(response.status == "success"){
						    var editHtml = '<a id="edit_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+comparison_name+'" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>';

						    $("#comparison_name_container_"+quelleId).html(comparison_name);
		    				$("#edit_container_"+quelleId).html(editHtml);
						}else{
							var comNameHtml = "";
						    comNameHtml += '<div class="input-group">';
						    comNameHtml += '	<input type="text" autocomplete="off" name="edit_comparison_name_'+quelleId+'" id="edit_comparison_name_'+quelleId+'" value="'+comparison_name+'"  class="form-control" placeholder="Comparison name">';
						    comNameHtml += '	<div class="input-group-btn">';
						    comNameHtml += '		<button id="save_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Save" class="btn btn-default save-edit" type="button"><i class="fas fa-save mm-fa-icon text-success"></i></button>';
						    comNameHtml += '	</div>';
						    comNameHtml += '</div>';

						    var cancelHtml = '<a id="cancel_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Cancel" class="text-danger edit-cancel" href="javascript:void(0)"><i class="fas fa-window-close mm-fa-icon"></i></a>';

						    $("#comparison_name_container_"+quelleId).html(comNameHtml);
						    $("#edit_container_"+quelleId).html(cancelHtml);

							$("#global_msg_container").html('<p class="text-center text-danger">'+response.message+'</p>');
							$("#globalMsgModal").modal('show');
							
						}
					}
				}).fail(function (response) {
					var editHtml = '<a id="edit_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>';
					$("#comparison_name_container_"+quelleId).html(existingComparisonName);
		    		$("#edit_container_"+quelleId).html(editHtml);
					$("#global_msg_container").html('<p class="text-center text-danger">Operation failed. Somethig went wrong!</p>');
					$("#globalMsgModal").modal('show');

					if ( window.console && window.console.log ) {
						console.log( response );
					}
				});
		    } else {
		    	var editHtml = '<a id="edit_'+quelleId+'" data-quelle-id="'+quelleId+'" data-arznei-id="'+arzneiId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>';

		    	$("#comparison_name_container_"+quelleId).html(existingComparisonName);
		    	$("#edit_container_"+quelleId).html(editHtml);
		    }
		});

		function addTranslation(quelle_id, arznei_id, master_id, language){
			var $th = $("#"+language+"_translation_btn_container_"+master_id);
    		if($th.hasClass('processing'))
    			return;
    		$th.addClass('processing');
			var error_count = 0;

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
							if(add_translation_language == "de")
								$("#de_translation_btn_container_"+add_translation_master_id).html('<a id="" title="Symptoms available in German" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');
							else
								$("#en_translation_btn_container_"+add_translation_master_id).html('<a id="" title="Symptoms available in English" class="text-success" onclick="" href="javascript:void(0)"><i class="fas fa-clipboard-check mm-fa-icon"></i></a>');

							$("#translationModal").modal('hide');
							
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
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>