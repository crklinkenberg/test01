<?php
	include '../lang/GermanWords.php';
	include '../config/route.php';
	if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 3) && ($_SESSION['user_type'] == 2)) {
		header('Location: '.$absoluteUrl);
	}
	include '../api/mainCall.php';
	/*$benutzer = '';
	$get_data = '';
	$response = '';
	$get_data = callAPI('GET', $baseApiURL.'user/all?is_paginate=0', false);
	$response = json_decode($get_data, true);
	$status = $response['status'];
	switch ($status) {
		case 0:
			header('Location: '.$absoluteUrl.'unauthorised');
			break;
		case 2:
			$benutzer = $response['content']['data'];
			break;
		case 6:
			$error = $response['message'];
			break;
		default:
			break;
	}*/
	include '../inc/header.php';
	include '../inc/sidebar.php';

	/* if(isset($_POST['id']) AND !empty($_POST['id'])){

		foreach ($_POST['id'] as $key => $value) {
			// Delete Temp table data START 
			$masterId = $value;
			
			$tempSymptomResult = mysqli_query($db, "SELECT * FROM quelle_import_test where master_id = '".$masterId."'");
			if(mysqli_num_rows($tempSymptomResult) > 0){
				while($tempSymptomData = mysqli_fetch_array($tempSymptomResult)){

					$deleteTempSymptomPrueferQuery = "DELETE FROM symptom_pruefer WHERE symptom_id = '".$tempSymptomData['id']."'";
					$db->query($deleteTempSymptomPrueferQuery);

					// Deleting Temp Symptom Reference
					$deleteTempSymptomReferenceQuery="DELETE FROM symptom_reference WHERE symptom_id = '".$tempSymptomData['id']."'";
					$db->query($deleteTempSymptomReferenceQuery);
				}
			}

			$deleteTempSymptomQuery = "DELETE FROM quelle_import_test WHERE master_id = '".$masterId."'";
			$db->query($deleteTempSymptomQuery);

			$deleteTempMasterQuery = "DELETE FROM quelle_import_master WHERE id = '".$masterId."'";
			$db->query($deleteTempMasterQuery);
			
			// Delete Temp table data END 
		}
	} */
?>
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Source Import
      </h1>
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
	          <div class="box-header with-border">
							<h3 class="box-title">
								<a href="<?php echo $absoluteUrl;?>comparenew" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Import New Source</a>
							</h3>
	          </div>
	        	<!-- /.box-header -->
	          <div class="box-body">
	            <form id="listViewForm-custom" data-action="delete" data-source="user" data-source_id_name="id" method="POST">
	          		<div class="table-responsive">
			            <table id="dataTable" class="table-loader table table-bordered table-striped display table-hover custom-table">
		                <thead>
			                <tr>
			                	<th class="rowlink-skip dt-body-center no-sort"><button type="button" onclick="myconfirm();" class="btn btn-danger btn-sm delete-row"  title="Löschen"><i class="fa fa-trash"></i></button></th>
			                	<th>Jahr</th>
			                	<th>Kürzel</th>
			                	<th>Titel</th>
								        <th>Datum</th>
								        <th>Arznei</th>
								        <th>Import Setting</th>
								        <th class="no-sort">Aktion</th>
		                	</tr>
		                </thead>
			                <tbody data-link="row" class="rowlink">
			                	<?php 
			                		$result = mysqli_query($db,"SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id FROM quelle_import_master AS QIM LEFT JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id WHERE Q.quelle_type_id != 3 ORDER BY Q.quelle_id DESC");
			                		// $result = mysqli_query($db,"SELECT * FROM quelle_import_master");
													while($row = mysqli_fetch_array($result)){
			                			?>
				                		<tr>
				                			<td class="rowlink-skip">
				                				<?php echo $row['quelle_id']; ?>
				                			</td>
				                			<td class="rowlink-skip">
				                				<?php echo $row['jahr']; ?>
				                				<input type="hidden" name="arznei_id[]" value="<?php echo $row['arznei_id']; ?>">		
				                				<input type="hidden" name="quelle_id[]" value="<?php echo $row['quelle_id']; ?>">		
				                			</td>
				                			<td class="rowlink-skip">
				                				<?php echo $row['code']; ?>
				                			</td>
				                			<td class="rowlink-skip">
				                				<?php
																// $quellen_value = "";
																// $quelleResult = mysqli_query($db,"SELECT quelle.quelle_id, quelle.code, quelle.titel, quelle.jahr, quelle.band, quelle.nummer, quelle.auflage, quelle.quelle_type_id, quelle.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname FROM quelle LEFT JOIN quelle_autor ON quelle.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id WHERE quelle.quelle_id = '".$row['quelle_id']."' ORDER BY quelle.quelle_type_id ASC");
																// if(mysqli_num_rows($quelleResult) > 0){
																// 	$quelleRow = mysqli_fetch_assoc($quelleResult);

																// 	// $quellen_value = $quelleRow['code'];
																// 	// if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];
																// 	// if(!empty($quelleRow['titel'])) $quellen_value .= ', '.$quelleRow['titel'];
																// 	// if($quelleRow['quelle_type_id'] == 1){
																// 	// 	if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
																// 	// }else if($quelleRow['quelle_type_id'] == 2){
																// 	// 	if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
																// 	// 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
																// 	// 	else 
																// 	// 		$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
																// 	// 	if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
																// 	// }


																// 	$quellen_value = (!empty($quelleRow['titel'])) ? $quelleRow['titel'] : "";
																// 	if($quelleRow['quelle_type_id'] == 1){
																// 		if(!empty($quelleRow['bucher_autor_or_herausgeber'])) $quellen_value .= ', '.$quelleRow['bucher_autor_or_herausgeber'];
																// 	}else if($quelleRow['quelle_type_id'] == 2){
																// 		if(!empty($quelleRow['zeitschriften_autor_suchname']) ) 
																// 			$zeitschriften_autor = $quelleRow['zeitschriften_autor_suchname']; 
																// 		else if($quelleRow['zeitschriften_autor_vorname'] != "" AND $quelleRow['zeitschriften_autor_nachname'] != "") 
																// 			$zeitschriften_autor = $quelleRow['zeitschriften_autor_vorname'].' '.$quelleRow['zeitschriften_autor_nachname'];
																// 		else
																// 			$zeitschriften_autor = "";
																// 		if(!empty($zeitschriften_autor)) $quellen_value .= ', '.$zeitschriften_autor;
																// 	}
																// 	if(!empty($quelleRow['jahr'])) $quellen_value .= ', '.$quelleRow['jahr'];

																// 	/*if(!empty($quelleRow['jahr'])) $quellen_value .= ' '.$quelleRow['jahr'];
																// 	if(!empty($quelleRow['band'])) $quellen_value .= ', Band: '.$quelleRow['band'];
																// 	if(!empty($quelleRow['nummer'])) $quellen_value .= ', Nr.: '. $quelleRow['nummer'];
																// 	if(!empty($quelleRow['auflage'])) $quellen_value .= ', Auflage: '. $quelleRow['auflage'];*/
																// }
																//From here new
																//echo $quellen_value;
																echo $row['titel'];
																?>
															</td>
															<td class="rowlink-skip">
																<?php 
																	echo date('d/m/Y h:i A', strtotime($row['ersteller_datum'])); 
																	//echo date('h:i A', strtotime($row['ersteller_datum']));  
																?>
															</td>
															<td class="rowlink-skip">
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
															<td class="rowlink-skip">
																<?php echo ucwords(str_replace("_", " ", $row['import_rule'])); ?>
															</td>
															<td class="rowlink-skip">
																<a class="btn btn-warning btn-sm" title="View symptoms" target="_blank" href="<?php echo $absoluteUrl; ?>comparenew/symptoms.php?mid=<?php echo $row['id']; ?>">View Symptoms</a>
															</td>
				                		</tr>
				                		<?php 
			                		}
			                	?>
				            </tbody>
			            </table>
				        </div>
			        </form> 
		        </div>
	          <!-- /.box-body -->
	        </div>
				</div>
			</div> 
			<!-- /.row (main row) -->
    </section>
    <!-- /.content -->
</div>
  <!-- /.content-wrapper -->
<?php
include '../inc/footer.php';
?>
<script type="text/javascript">
	function myconfirm() {
		var count =0;
		$('input[type="checkbox"]').each(function() {
			// If checkbox is checked
			if(this.checked) {
			   count++;
			} 
	  	});
		if(count == 0) {
			return false;
		}else{
			
			swal({
			  title: 'Bist du sicher?',
			  text: "Du kannst diesen Vorgang nicht rückgängig machen!",
			  type: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Ja, lösche es!',
			  cancelButtonText: 'Nein, abbrechen!',
			}).then((result) => {
				if (result.value) {
					deleteTheSources();
					// $("#listViewForm-custom").submit();
				}  else {
				   	$('input[type="checkbox"]').each(function() {
						$(this).prop("checked", false);
			  		});
			   }
				
			})

		}
	}

	function deleteTheSources(){
		// Form data
		var data = $("#listViewForm-custom").serialize();

		$.ajax({
			type: 'POST',
			url: '../comparenew/delete-quelle-for-main-application.php',
			data: {
				form: data
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "success"){
					location.reload();
				}else{
					location.reload();
				}
			}
		}).fail(function (response) {
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
</script>
