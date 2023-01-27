<?php
include '../../../lang/GermanWords.php';
include '../../../config/route.php';
include '../../../api/reference.php';
include '../../../inc/header.php';
include '../../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ändern Literatur <!-- Edit Reference -->
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li> Stemmdaten</li>
        <li> <a href="<?php echo $absoluteUrl;?>stammdaten/reference/de"> Literatur DE</a></li>
        <li class="active"> Ändern Literatur</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
		            <div class="box-header with-border">
		              <p>Die mit * gekennzeichneten Felder sind Pflichtfelder</p>
		            </div>
		            <!-- /.box-header -->
		            <!-- form start -->
		            <form class="content-form" id="addReferenceForm" data-action="update" data-source="reference" data-source_id_value="<?php echo $reference['reference_id'];?>" data-source_id_name="reference" autocomplete="off">
		              	<div class="box-body">
			              	<div class="row">
	                            <div class="col-md-12">
	                                <div class="callout callout-info">
	                                    <p>Example citation: Matthioli, Comment. in Diosc. lib. IV. Cap. 73.</p>
	                                </div>
	                            </div>
	                        </div>
			              	<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="full_reference">Literatur*</label><span class="error-text"></span>
										<input type="text" class="form-control" name="full_reference" value="<?php echo $reference['full_reference'];?>" id="full_reference">
									</div>
									<div class="form-group">
										<label for="kommentar">Kommentar</label>
										<textarea id="kommentar" name="kommentar" class="form-control texteditor" aria-hidden="true"><?php echo $reference['kommentar'];?></textarea>
									</div>
									<div class="form-group">
										<label for="unklarheiten">Unklar</label>
										<input type="text" class="form-control"  name="unklarheiten" value="<?php echo $reference['unklarheiten'];?>" id="unklarheiten">
									</div>
								</div>
							</div>
		              	</div>
		              <!-- /.box-body -->

		               	<div class="box-footer">
			              	<div class="box-footer">
				              	<input class="btn btn-success" type="submit" value="Änderungen speichern" name="ÄnderungenSpeichern" id="saveFormBtn">
								<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/reference/de/" id="cancelBtn">Abbrechen</a>
								<a href="<?php echo $absoluteUrl;?>stammdaten/reference/de/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
			              </div>
		              	</div>
		            </form>
		          </div>
			</div>
		</div>
      <!-- /.row -->
	</section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include '../../../inc/footer.php';
?>