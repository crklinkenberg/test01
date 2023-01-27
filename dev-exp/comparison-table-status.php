<?php
	include '../config/route.php';
	include 'sub-section-config.php';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Comparison History & Status</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Font Awesome -->
  	<link rel="stylesheet" href="plugins/font-awesome/css/fontawesome-all.min.css">
  	<!-- Select2 -->
  	<link rel="stylesheet" href="plugins/select2/dist/css/select2.min.css">
  	<!-- custom -->
  	<link rel="stylesheet" href="assets/css/custom.css">
  	<!-- new comparison table style -->
  	<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
  	<style type="text/css">
  		.comparison-navigation-ul li {
		    display: inline-block;
		}
  	</style>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<ul class="comparison-navigation-ul">
					<li><a href="<?php echo $baseUrl ?>comparison-v3.php" title="Comparison" class="btn head-btn" role="button">Comparison</a></li>
					<li><a href="<?php echo $baseUrl ?>materia-medica.php" title="Materia Medica"  class="btn head-btn" role="button">Materia Medica</a></li>
					<li><a href="<?php echo $baseUrl ?>comparison-table-status.php" title="History" class="btn head-btn active" role="button">History</a></li>
				</ul>
				<div class="spacer"></div>
			</div>
		</div>
		<div class="row">  
			<div class="col-sm-12">
				<h2>Comparison History & Status</h2> 
				<div id="comparison_result_cnr" class="master-table-cnr">  
					<form name="result_frm" id="result_frm" action="" method="POST">      
					  	<table class="table table-bordered heading-table heading-table-bg">
						    <thead>
						      	<tr>
						      		<th>Comparison name</th>
							        <th style="width: 10%;">Status</th>
							        <th style="width: 13%;">Started at</th>
							        <th style="width: 13%;">Ended at</th>
							        <th style="width: 10%;">Change Name</th>
							        <th style="width: 12%;">Edit Comparison</th>
							        <th style="width: 8%;">Delete</th>
						      	</tr>
						    </thead>
						</table>
						<table class="table table-bordered">
						    <tbody>
						    	<?php						    	
						    		$sympResult = mysqli_query($db, "SELECT id, comparison_name, status, stand, ersteller_datum FROM pre_comparison_master_data");
									if(mysqli_num_rows($sympResult) > 0){
										while($row = mysqli_fetch_array($sympResult)){
								?>
										<tr>
											<td id="comparison_name_container_<?php echo $row['id']; ?>"><?php echo $row['comparison_name']; ?></td>
											<td style="width: 10%;"><?php if($row['status'] == "done"){ echo "<b class='text-success'>Available</b>"; }else{ echo "<b class='text-danger'>".ucfirst($row['status'])."</b>"; } ?></td>
											<td style="width: 13%;"><?php if($row['ersteller_datum'] != ""){ echo date("d/m/y  h:i A", strtotime($row['ersteller_datum'])); } ?></td>
											<td style="width: 13%;"><?php if($row['stand'] != ""){ echo date("d/m/y  h:i A", strtotime($row['stand'])); } ?></td>
											<td id="edit_container_<?php echo $row['id']; ?>" style="width: 10%;">
												<?php if($row['status'] == "done"){ ?>
													<a class="text-info history-table-anchor-tag edit-comparison-name" data-id="<?php echo $row['id']; ?>" data-existing-comparison-name="<?php echo $row['comparison_name']; ?>" href="javascript:void(0)">Change Name</a>
												<?php } ?>
											</td>
											<td style="width: 12%;">
												<?php if($row['status'] == "done"){ ?>
													<a class="text-info history-table-anchor-tag" href="comparison.php?comid=<?php echo $row['id']; ?>">Edit Comparison</a>
												<?php } ?>
											</td>
											<td style="width: 8%;">
												<?php if($row['status'] == "done"){ ?>
													<a id="delete_<?php echo $row['id']; ?>" onclick="deleteTheComparison('<?php echo $row['id']; ?>')" class="text-info history-table-anchor-tag" href="javascript:void(0)">Delete</a>
												<?php } ?>
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

	<!-- Including Modals html START -->
	<?php include 'includes/comparison-table-page-modals.php'; ?>
	<!-- Including Modals html END -->

	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script type="text/javascript">
		$(document).on('click', '.edit-comparison-name', function(){
			var id = $(this).attr("data-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    existingComparisonName = existingComparisonName.trim();
		    $("#comparison_name_container_"+id).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+id).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');

		    var comNameHtml = "";
		    comNameHtml += '<div class="input-group">';
		    comNameHtml += '	<input type="text" autocomplete="off" name="edit_comparison_name_'+id+'" id="edit_comparison_name_'+id+'" value="'+existingComparisonName+'" class="form-control" placeholder="Comparison name">';
		    comNameHtml += '	<div class="input-group-btn">';
		    comNameHtml += '		<button id="save_'+id+'" data-id="'+id+'" data-existing-comparison-name="'+existingComparisonName+'" title="Save" class="btn btn-default save-edit" type="button"><i class="fas fa-save mm-fa-icon text-success"></i></button>';
		    comNameHtml += '	</div>';
		    comNameHtml += '</div>';

		    var cancelHtml = '<a id="cancel_'+id+'" data-id="'+id+'" data-existing-comparison-name="'+existingComparisonName+'" title="Cancel" class="text-danger history-table-anchor-tag edit-cancel" href="javascript:void(0)">Cancel</a>';

		    $("#comparison_name_container_"+id).html(comNameHtml);
		    $("#edit_container_"+id).html(cancelHtml);
		});

		$(document).on('click', '.edit-cancel', function(){
			var rowId = $(this).attr("data-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    existingComparisonName = existingComparisonName.trim();
		    $("#comparison_name_container_"+rowId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+rowId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');

		    var editHtml = '<a id="edit_'+rowId+'" data-id="'+rowId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info history-table-anchor-tag edit-comparison-name" href="javascript:void(0)">Change Name</a>';

		    $("#comparison_name_container_"+rowId).html(existingComparisonName);
		    $("#edit_container_"+rowId).html(editHtml);
		});

		$(document).on('click', '.save-edit', function(){
			var rowId = $(this).attr("data-id");
		    var existingComparisonName = $(this).attr("data-existing-comparison-name");
		    existingComparisonName = existingComparisonName.trim();

		    var comparison_name = $("#edit_comparison_name_"+rowId).val().trim();
		    $("#comparison_name_container_"+rowId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    $("#edit_container_"+rowId).html('<span><img src="assets/img/loader.gif" alt="Loader"></span>');
		    if(comparison_name != ""){
		    	$.ajax({
					type: 'POST',
					url: 'change-comparison-name-new.php',
					data: {
						comparison_name: comparison_name,
						id: rowId,
						existing_comparison_name: existingComparisonName
					},
					dataType: "json",
					success: function( response ) {
						if(response.status == "success"){
						    var editHtml = '<a id="edit_'+rowId+'" data-id="'+rowId+'" data-existing-comparison-name="'+comparison_name+'" title="Edit comparison name" class="text-info history-table-anchor-tag edit-comparison-name" href="javascript:void(0)">Change Name</a>';

						    $("#comparison_name_container_"+rowId).html(comparison_name);
		    				$("#edit_container_"+rowId).html(editHtml);
						}else{
							$("#global_msg_container").html('<p class="text-center text-danger">'+response.message+'</p>');
							$("#globalMsgModal").modal('show');
						}
					}
				}).fail(function (response) {
					var editHtml = '<a id="edit_'+rowId+'" data-id="'+rowId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info history-table-anchor-tag edit-comparison-name" href="javascript:void(0)">Change Name</a>';
					$("#comparison_name_container_"+rowId).html(existingComparisonName);
		    		$("#edit_container_"+rowId).html(editHtml);
					$("#global_msg_container").html('<p class="text-center text-danger">Operation failed. Somethig went wrong!</p>');
					$("#globalMsgModal").modal('show');

					if ( window.console && window.console.log ) {
						console.log( response );
					}
				});
		    } else {
		    	var editHtml = '<a id="edit_'+rowId+'" data-id="'+rowId+'" data-existing-comparison-name="'+existingComparisonName+'" title="Edit comparison name" class="text-info history-table-anchor-tag edit-comparison-name" href="javascript:void(0)">Change Name</a>';

		    	$("#comparison_name_container_"+rowId).html(existingComparisonName);
		    	$("#edit_container_"+rowId).html(editHtml);
		    }
		});

		function deleteTheComparison(id){
			var con = confirm("Are you sure you want to delete?");
			if (con)
			{
				$('#delete_'+id).prop('disabled', true);
				$('#delete_'+id).html('<img src="assets/img/loader.gif" alt="Loader">');
				$.ajax({
					type: 'POST',
					url: 'delete-comparison-new.php',
					data: {
						id: id
					},
					dataType: "json",
					success: function( response ) {
						console.log(response);
						if(response.status == "success"){
							location.reload();
						}else{
							var deleteHtml = '<a id="delete_'+id+'" onclick="deleteTheComparison('+id+')" class="text-info history-table-anchor-tag" href="javascript:void(0)">Delete</a>';
							
							$('#delete_'+id).prop('disabled', false);
							$('#delete_'+id).html(deleteHtml);
							$("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
							$("#globalMsgModal").modal('show');
						}
					}
				}).fail(function (response) {
					var deleteHtml = '<a id="delete_'+id+'" onclick="deleteTheComparison('+id+')" class="text-info history-table-anchor-tag" href="javascript:void(0)">Delete</a>';

					$('#delete_'+id).prop('disabled', false);
					$('#delete_'+id).html(deleteHtml);
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
	</script>
</body>
</html>