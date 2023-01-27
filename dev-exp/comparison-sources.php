<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Displaying the saved comparison souces on backup section
	*/
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Comparison Sources</title>
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
				<h2>Comparison Sources</h2> 
				<div id="comparison_result_cnr" class="master-table-cnr">  
					<form name="result_frm" id="result_frm" action="" method="POST">      
					  	<table class="table table-bordered heading-table heading-table-bg">
						    <thead>
						      	<tr>
						      		<th style="width: 10%;" class="text-center">Jahr</th>
						      		<th>Quelle</th>
						      		<th style="width: 20%;">Arznei</th>
							        <th style="width: 12%;" class="text-center">View</th>
							        <th style="width: 4%;" class="text-center"><a title="Edit comparison name" class="text-info" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a></th>
						      	</tr>
						    </thead>
						</table>
						<table class="table table-bordered">
						    <tbody>
						    	<?php						    	
						    		$sympResult = mysqli_query($db, "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, SC.id as sc_id, SC.comparison_name FROM quelle_import_master AS QIM JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id JOIN saved_comparisons AS SC ON QIM.quelle_id = SC.quelle_id ORDER BY Q.jahr ASC");
									if(mysqli_num_rows($sympResult) > 0){
										while($row = mysqli_fetch_array($sympResult)){
								?>
											<tr id="row_<?php echo $sympRow['id']; ?>">
												<td style="width: 10%;" class="text-center"><?php echo $row['jahr']; ?></td>
												<td id="comparison_name_container_<?php echo $row['quelle_id']; ?>">
													<?php 
														echo $row['comparison_name']; 
													?>
												</td>
												<td style="width: 20%;">
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
												<td style="width: 12%;" class="text-center">
													<a title="View source backups" href="<?php echo $baseUrl; ?>comparison-backups.php?oqid=<?php echo $row['quelle_id']; ?>">View backups</a>
												</td>
												<td id="edit_container_<?php echo $row['quelle_id']; ?>" style="width: 4%;" class="text-center">
													<?php if($row['quelle_type_id'] == 3){ ?>
														<a id="edit_<?php echo $row['quelle_id']; ?>" data-quelle-id="<?php echo $row['quelle_id']; ?>" data-arznei-id="<?php echo $row['arznei_id']; ?>" data-existing-comparison-name="<?php echo $row['comparison_name']; ?>" title="Edit comparison name" class="text-info edit-comparison-name" href="javascript:void(0)"><i class="fas fa-edit mm-fa-icon"></i></a>
													<?php }  ?>
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

	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script type="text/javascript">
		$(window).bind("load", function() {
			console.log('loaded');
			$("#loader").addClass("hidden");
		});

		function deleteTheQuelle(quelle_id){
			var con = confirm("Deleting this Quelle will delete it's all conections and related comparison where this source is used, are you sure you want to delete?");
			if (con)
			{
				$('#delete_'+quelle_id).prop('disabled', true);
				$('#delete_'+quelle_id).html('<img src="assets/img/loader.gif" alt="Loader">');
				$.ajax({
					type: 'POST',
					url: 'delete-quelle.php',
					data: {
						quelle_id: quelle_id
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
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>