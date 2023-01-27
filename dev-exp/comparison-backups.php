<?php
	include '../config/route.php';
	include 'sub-section-config.php';
	/*
	* Displaying all the backup sets of a particular source
	*/
?>
<?php
	$oqid = (isset($_GET['oqid']) AND $_GET['oqid'] != "") ? $_GET['oqid'] : null;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Comparison Backups</title>
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
				<h2>Comparison Backups</h2> 
				<div id="comparison_result_cnr" class="master-table-cnr">  
					<form name="result_frm" id="result_frm" action="" method="POST">      
					  	<table class="table table-bordered heading-table heading-table-bg">
						    <thead>
						      	<tr>
						      		<th style="width: 10%;" class="text-center">Jahr</th>
						      		<th style="width: 38%;">Quelle</th>
						      		<th style="width: 12%;">Arznei</th>
							        <th style="width: 15%;">Date</th>
							        <th style="width: 17%;" class="text-center">View</th>
							        <th style="width: 8%;" class="text-center"><a title="Reactivate" class="text-info" href="javascript:void(0)"><i class="fas fa-redo-alt mm-fa-icon"></i></a></th>
						      	</tr>
						    </thead>
						</table>
						<table class="table table-bordered">
						    <tbody>
						    	<?php
						    	if($oqid != ""){

						    		$sympResult = mysqli_query($db, "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, SC.id as scid, SC.comparison_name FROM quelle_import_master_backup AS QIM JOIN quelle_backup AS Q ON QIM.quelle_id = Q.quelle_id JOIN saved_comparisons_backup AS SC ON QIM.quelle_id = SC.quelle_id WHERE QIM.original_quelle_id = '".$oqid."' ORDER BY QIM.ersteller_datum DESC");
									if(mysqli_num_rows($sympResult) > 0){
										while($row = mysqli_fetch_array($sympResult)){
								?>
											<tr id="row_<?php echo $row['id']; ?>">
												<td style="width: 10%;" class="text-center"><?php echo $row['jahr']; ?></td>
												<td id="comparison_name_container_<?php echo $row['quelle_id']; ?>" style="width: 38%;">
													<?php
														echo $row['comparison_name']; 
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
													<a title="View symptoms" href="<?php echo $baseUrl; ?>backup-symptoms.php?mid=<?php echo $row['id']; ?>">View symptoms</a>
													<?php if($row['quelle_type_id'] == 3){ ?>
														<span class="text-danger"> / </span>
														<a title="View connections" href="<?php echo $baseUrl; ?>view-source-connections.php?mid=<?php echo $row['id']; ?>">View connections</a>
														<span class="text-danger"> / </span> 
														<a title="View" href="<?php echo $baseUrl; ?>backup-comparison.php?scid=<?php echo $row['scid']; ?>">View raw</a>
													<?php } ?> 
												</td>
												<td id="edit_container_<?php echo $row['quelle_id']; ?>" style="width: 8%;" class="text-center">
													<a id="reactivate_<?php echo $row['quelle_id']; ?>" title="Reactivate" class="text-info reactivate-quelle" data-original-quelle-id="<?php echo $row['original_quelle_id']; ?>" data-arznei-id="<?php echo $row['arznei_id']; ?>" data-quelle-id="<?php echo $row['quelle_id']; ?>" href="javascript:void(0)">Reactivate</a>
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
								} else {
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

		$(document).on('click', '.reactivate-quelle', function(){
			var $th = $(this);
    		if($th.hasClass('processing'))
    			return;
    		$th.addClass('processing');
    		var originalQuelleId = $(this).attr("data-original-quelle-id");
		    var arzneiId = $(this).attr("data-arznei-id");
		    var quelleId = $(this).attr("data-quelle-id");

			var con = confirm("Reactivating this source will delete it's all previously active connections and comparisons. Are you sure you want to reactivate?");
			if (con)
			{
				if(originalQuelleId != "" && arzneiId != ""){
					$('#reactivate_'+quelleId).prop('disabled', true);
					$('#reactivate_'+quelleId).html('<img src="assets/img/loader.gif" alt="Loader">');
					$.ajax({
						type: 'POST',
						url: 'reactivate-a-comparison-backup.php',
						data: {
							original_quelle_id: originalQuelleId,
							quelle_id: quelleId,
							arznei_id: arzneiId
						},
						dataType: "json",
						success: function( response ) {
							console.log(response);
							if(response.status == "success"){
								// $('#reactivate_'+quelleId).prop('disabled', false);
								// $('#reactivate_'+quelleId).html('Reactivate');
								// $("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
								// $("#globalMsgModal").modal('show');
								//location.reload();
								$th.removeClass('processing');
								window.location.href = "<?php echo $baseUrl ?>comparison-backups.php?oqid="+response.result_data.original_quelle_id;
								
							}else{
								$th.removeClass('processing');
								$('#reactivate_'+quelleId).prop('disabled', false);
								$('#reactivate_'+quelleId).html('Reactivate');
								$("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
								$("#globalMsgModal").modal('show');
							}
						}
					}).fail(function (response) {
						$th.removeClass('processing');
						$('#reactivate_'+quelleId).prop('disabled', false);
						$('#reactivate_'+quelleId).html('Reactivate');
						$("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try!</p>');
						$("#globalMsgModal").modal('show');

						if ( window.console && window.console.log ) {
							console.log( response );
						}
					});
				} else {
					$th.removeClass('processing');
					$('#reactivate_'+quelleId).prop('disabled', false);
					$('#reactivate_'+quelleId).html('Reactivate');
					$("#global_msg_container").html('<p class="text-center">Operation failed, some required data not found. Please reload and try!</p>');
					$("#globalMsgModal").modal('show');
				}
			}
			else
			{
				$th.removeClass('processing');
				return false;
			}
		});
	</script>
</body>
</html>
<?php
	include 'includes/php-foot-includes.php';
?>