<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/mainCall.php';
$arzneien = [];
$get_data = '';
$response = [];
$get_data = callAPI('GET', $baseApiURL.'arznei/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$arzneien = $response['content']['data'];
		break;
	case 6:
		$error = $response['message'];
		break;
	default:
		break;
}
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Arzneien
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class="active">Arzneien</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
		            <div class="box-header with-border">
		              <h3 class="box-title">
		              	<a href="<?php echo $absoluteUrl;?>stammdaten/arzneien/add" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Neue Arzneien</a>
		              </h3>
		            </div>
		            <?php  } ?>
		            <!-- /.box-header -->
		            <div class="box-body">
		            	 <form id="listViewForm" data-action="delete" data-source="arznei" data-source_id_name="arznei_id">
		            		<div class="table-responsive">
					            <table id="dataTable" class="table-loader table table-bordered table-striped display table-hover custom-table">
					                <thead>
						                <tr>
							                	<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
							                	<th class="rowlink-skip dt-body-center no-sort"><button class="btn btn-danger btn-sm delete-row"  title="Löschen"><i class="fa fa-trash"></i></button></th>
							                	<?php  } ?>
															 	<th>Remedy</th>
															 	<th>Synonyms</th>
															 	<th>Abbreviation</th>
															 	<th>Synonym Source</th>
															 	<th>Comment</th>
																<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
																<?php if($_SESSION['user_type'] == 1 ) { ?>
																<th>Created by</th>
																<th>Edited by</th>
																<?php  } ?>
																<th class="no-sort">Actions</th>
																<?php  } ?>
						                </tr>
					                </thead>
					                <tbody data-link="row" class="rowlink">
					                	<?php 
					                	if($arzneien != null && $arzneien != '') { 
					                		foreach ($arzneien as $key => $arznei) { ?>
					                			<?php
					                				$quellen_value = "";
																	$quellen = $arznei['quelle'];
																	foreach ($quellen as $key => $quelle) {
																		if($key > 0) $quellen_value .= ', ';
																		$quellen_value = $quellen_value.$quelle['code'];
																		if(!empty($quelle['jahr'])) $quellen_value .= ' '.$quelle['jahr'];
																		if(!empty($quelle['band'])) $quellen_value .= ' Band: '.$quelle['band'];
																		if(!empty($quelle['nummer'])) $quellen_value .= ' Nr.: '. $quelle['nummer'];
																		if(!empty($quelle['auflage'])) $quellen_value .= ', Auflage: '. $quelle['auflage'];
																	}
					                			?>
							                <tr>
							                	<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
							                	<td class="rowlink-skip"><?php echo $arznei['arznei_id']; ?></td>
							                	<?php  } ?>
																<td><a href="#rowlinkModal" 
																		data-id="<?php echo $arznei['arznei_id']; ?>" data-type="arznei" data-title="Anzeigen Arznei" 
																		data-toggle="modal"><?php echo $arznei['titel'];?></a></td>
																<td><?php echo $arznei['synonyms']; ?></td>
																<td><?php echo $arznei['kuerzel']; ?></td>
																<td><?php echo $quellen_value; ?></td>
																<td><?php echo $arznei['kommentar']; ?></td>
																<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
																<?php if($_SESSION['user_type'] == 1 ) { ?>
																<td><?php echo $arznei['ersteller']; ?></td>
																<td><?php echo $arznei['bearbeiter']; ?></td>
																<?php } ?>
																<td class="rowlink-skip">
																	<a class="btn btn-warning btn-sm" href="<?php echo $absoluteUrl;?>stammdaten/arzneien/edit?arznei_id=<?php echo $arznei['arznei_id']; ?>" title="Ändern"><i class="fa fa-edit"></i></a>
			            	       	    </td>
			            	       	    <?php } ?>
							                </tr>
							            <?php } 
							            } ?>
						            </tbody>
					            </table>
					        </div>
				        </form> 
			        </div>
			            <!-- /.box-body -->
		        </div>
			</div>
		</div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
        
      </div>
      <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php
include '../../inc/footer.php';
?>