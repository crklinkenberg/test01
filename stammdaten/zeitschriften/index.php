<?php
include '../../config/route.php';
include '../../lang/GermanWords.php';
include '../../api/mainCall.php';

$zeitschriften = [];
$get_data = '';
$response = [];
$get_data = callAPI('GET', $baseApiURL.'zeitschrift/all?is_paginate=0', false);
$response = json_decode($get_data, true);
$status = $response['status'];
switch ($status) {
	case 0:
		header('Location: '.$absoluteUrl.'unauthorised');
		break;
	case 2:
		$zeitschriften = $response['content']['data'];
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
        Zeitschriften
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i><?php echo $home; ?></a></li>
        <li class="active">Zeitschriften</li>
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
		              	<a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften/add" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Neue Zeitschrift</a>
		              </h3>
		            </div>
		            <?php  } ?>
		            <!-- /.box-header -->
		            <div class="box-body"> 
		            	<form id="listViewForm" data-action="delete" data-source="zeitschrift" data-source_id_name="quelle_id"
		            		<div class="table-responsive">
					            <table id="dataTable" class="table-loader table table-bordered table-striped display table-hover custom-table">
					                <thead>
						                <tr>
						                	<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
						                	<th class="rowlink-skip dt-body-center no-sort"><button class="btn btn-danger btn-sm delete-row"  title="Löschen"><i class="fa fa-trash"></i></button></th>
						                	<?php  } ?>
											<th>Kürzel</th>
											<th>Titel</th>
											<th>Jahr</th>
											 <?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
											<?php if($_SESSION['user_type'] == 1 ) { ?>
											<th>Autor / Herausgeber</th>
											<th>Angelegt durch</th>
											<th>Bearbeiter</th>
											<?php  } ?>
											<th class="no-sort">Aktionen</th>
											<?php  } ?>
						                </tr>
					                </thead>
					                <tbody data-link="row" class="rowlink">
					                	<?php 
					                	if($zeitschriften != null && $zeitschriften != '') { 
					                		foreach ($zeitschriften as $key => $zeitschrift) { ?>
					                		<?php
					                			$autor_or_herausgeber = "";
					                			if(!empty($zeitschrift['autoren'])){
					                				foreach ($zeitschrift['autoren'] as $autorKey => $autorVal) {
					                					$autor_or_herausgeber .= $autorVal['suchname'].', ';
					                				}
					                			}
					                			$autor_or_herausgeber = ($autor_or_herausgeber != "") ? rtrim($autor_or_herausgeber, ', ') : '';
					                		?>
							                <tr>
							                	<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
							                	<td class="rowlink-skip"><?php echo $zeitschrift['quelle_id']; ?></td>
							                	<?php  } ?>
												<td><a href="#rowlinkModal" 
														data-id="<?php echo $zeitschrift['quelle_id']; ?>" data-type="zeitschrift" data-title="Zeitschrift" 
														data-toggle="modal"><?php echo $zeitschrift['code'];  ?></a></td>
												<td><?php echo $zeitschrift['titel']; ?></td>
												<td><?php echo $zeitschrift['jahr']; ?></td>
												<?php if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
												<?php if($_SESSION['user_type'] == 1 ) { ?>
												<td><?php echo $autor_or_herausgeber; ?></td>
												<td><?php echo $zeitschrift['ersteller']; ?></td>
												<td><?php echo $zeitschrift['bearbeiter']; ?></td>
												<?php } ?>
												<td class="rowlink-skip">
													<a class="btn btn-warning btn-sm" href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften/edit?zeitschrift_id=<?php echo $zeitschrift['quelle_id']; ?>" title="Ändern"><i class="fa fa-edit"></i></a>
			            	       	            </td>
			            	       	            <?php  } ?>
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