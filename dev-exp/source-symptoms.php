<?php
	include '../lang/GermanWords.php';
	include '../config/route.php';
	include 'sub-section-config.php';
?>
<?php
    $masterId = (isset($_GET['mid']) AND $_GET['mid'] != "") ? $_GET['mid'] : "";
    $quellen_value = "";
	$importedLanguage = "";
	$quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, quelle_import_master.importing_language FROM quelle JOIN quelle_import_master ON quelle.quelle_id = quelle_import_master.quelle_id LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle_import_master.id = '".$masterId."' ORDER BY quelle.quelle_type_id ASC");
	if(mysqli_num_rows($quelleResult) > 0){
		$quelleRow = mysqli_fetch_assoc($quelleResult);

		$importedLanguage = $quelleRow['importing_language'];
		$quellen_value = $quelleRow['code'];
		if($quelleRow['quelle_type_id'] != 3){
			// if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
			// if(!empty($quelleRow['titel'])) $quellen_value .= ', '.$quelleRow['titel'];
			// if($quelleRow['quelle_type_id'] == 1){
			// 	if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
			// }else if($quelleRow['quelle_type_id'] == 2){
			// 	if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
			// 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
			// 	else 
			// 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
			// 	if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
			// }

			$quellen_value = (!empty($quelleRow['titel'])) ? $quelleRow['titel'] : "";
			if($quelleRow['quelle_type_id'] == 1){
				if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
			}else if($quelleRow['quelle_type_id'] == 2){
				if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
					$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
				else if($quelleRow['zeitschriften_autor_vorname'] != "" AND $quelleRow['zeitschriften_autor_nachname'] != "") 
					$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
				else
					$zeitschriften_autor = "";
				if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
			}
			if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];

			/*if(!empty($quelleRow['jahr'])) $quellen_value .= ' '.$quelleRow['jahr'];
			if(!empty($quelleRow['band'])) $quellen_value .= ', Band: '.$quelleRow['band'];
			if(!empty($quelleRow['nummer'])) $quellen_value .= ', Nr.: '. $quelleRow['nummer'];
			if(!empty($quelleRow['auflage'])) $quellen_value .= ', Auflage: '. $quelleRow['auflage'];*/
		}
		// if(!empty($quelleRow['titel']))
		// 	$quellen_value = $quelleRow['titel'];
		// else
		// 	$quellen_value = rtrim(trim($quellen_value),",");
		$quellen_value = rtrim(trim($quellen_value),",");
	}
?>
<?php
	include '../inc/header.php';
	include '../inc/sidebar.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
	    <h1>Source Import</h1>
	    <ol class="breadcrumb">
	    	<li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
	    	<li class="active">Source Import</li>
	    </ol>
	</section>

  	<!-- Main content -->
  	<section class="content">
    <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<?php //if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
            		<!-- <div class="box-header with-border">
		              	<h3 class="box-title">
		              		<a href="<#" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Add</a>
		              	</h3>
	            	</div> -->
			     	<?php  //} ?>
		    		<!-- /.box-header -->
		    		<div class="box-body">
                        <form id="source_symptoms_form" name="source_symptoms_form" method="POST">
			            	<button id="symptom_include_btn" type="submit" class="btn btn-success" style="display:none;">Include The Symptoms</button>
			            	<table class="table full-symptom-details-table table-bordered table-sticky-head table-hover">
							    <thead>
							      	<tr>
								        <th width="2%">Is Excluded</th>
								        <th width="2%">Include It</th>
								        <th width="48%">Symptom DE</th>
								        <th width="48%">Symptom EN</th>
							      	</tr>
							    </thead>
							    <tbody>
							    	<?php                                                                   
										$result = mysqli_query($db,"SELECT * FROM quelle_import_test WHERE master_id = '".$masterId."' ORDER BY id ASC");
										if(mysqli_num_rows($result) > 0){
											while($row = mysqli_fetch_array($result)){
												?>
												<tr>
													<td><?php if($row['is_excluded_in_comparison'] == 1) { ?><a title="Symptom is excluded in the comparison process" href="javascript:void(0)"><i class="fas fa-check"></i></a><?php }else{ echo "-"; } ?></td>
													<td><?php if($row['is_excluded_in_comparison'] == 1) { ?>
														<input type="checkbox" name="exluded_symptoms[]" class="excluded-symptoms" value="<?php echo $row['id']; ?>">
														<?php }else{ echo "-"; } ?></td>
													<td><?php echo ($row['BeschreibungFull_de'] != "") ? $row['BeschreibungFull_de'] : "-"; ?></td>
													<td><?php echo ($row['BeschreibungFull_en'] != "") ? $row['BeschreibungFull_en'] : "-"; ?></td>
												</tr>
												<?php
											}
										}else{
											?>
											<tr>
												<td colspan="2" class="text-center">No records found.</td>
											</tr>
											<?php
										}
									?>
								</tbody>
							</table>
							<input type="hidden" name="db_table_name" id="db_table_name" value="quelle_import_test">
						</form>
			        </div>
          			<!-- /.box-body -->
		    	</div>
			</div>
		</div>
	    <!-- /.row -->
  	</section>
  	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include '../inc/footer.php';
?>
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
<script type="text/javascript">
	$('body').on( 'click', '.excluded-symptoms', function(e) {
		var numItems = $('.excluded-symptoms').length;
		var numChecked = $('input.excluded-symptoms:checked').length;
		if(numChecked != 0){
			$("#symptom_include_btn").show();
		}else{
			$("#symptom_include_btn").hide();
		}
		var action = "";
		if($(this).prop("checked") == true) {
			action = "check";
		}else{
			action = "uncheck";
		}
		console.log(action+" : "+numItems+" : "+numChecked);
	});

	$('body').on( 'submit', '#source_symptoms_form', function(e) {
			e.preventDefault();
			var numChecked = $('input.excluded-symptoms:checked').length;
			var dbTableName = $('#db_table_name').val();
			var error_count = 0;

			if(numChecked == 0){
				error_count++;
			}
			if(dbTableName == ""){
				error_count++;
			}
			if(error_count != 0){
				$("#global_msg_container").html('<p class="text-center text-danger">Required data not found.</p>');
				$("#globalMsgModal").modal('show');
				return false;
			}else{
				var data = $("#source_symptoms_form").serialize();
				var request = $.ajax({
				  	url: "excluded-symptoms-operation.php",
				  	type: "POST",
				  	data: {
						form: data
					},
				  	dataType: "json"
				});
				request.done(function(response) {
					console.log(response);
					if(response.status == "success"){
						location.reload();
					}else{
						$("#global_msg_container").html('<p class="text-center text-danger">Something went wrong!</p>');
						$("#globalMsgModal").modal('show');
					}
				});
				request.fail(function(jqXHR, textStatus) {
				  	console.log("Request failed: " + textStatus);
				});
			}
		});
</script>