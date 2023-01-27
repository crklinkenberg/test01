<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/verlage.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ändern Verlage
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/verlage/">Verlage</a></li>
        <li class="active"> Ändern Verlage</li>
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
		            <form class="content-form" id="addVerlageForm" data-action="update" data-source="verlage" data-source_id_value="<?php echo $verlage['verlag_id'];?>" data-source_id_name="verlag" autocomplete="off">
		            <input type="hidden" name="verlag_id" value="<?php echo $verlage['verlag_id'];?>">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="code">Kürzel</label>
									<input type="text" class="form-control" name="code" value="<?php echo $verlage['code']; ?>" id="code" autofocus>
								</div>
								<div class="form-group">
									<label for="titel">Titel*</label><span class="error-text"></span>
									<input type="text" class="form-control" name="titel" value="<?php echo $verlage['titel']; ?>" id="titel">
								</div>
								
								<div class="form-group">
									<label for="strasse">Strasse</label>
									<input type="text" class="form-control" name="strasse" value="<?php echo $verlage['strasse']; ?>" id="strasse">
								</div>
								<div class="form-group">
									<label for="plz">PLZ</label>
									<input type="text" class="form-control" id="plz" name="plz" value="<?php echo $verlage['plz']; ?>">
								</div>
								<div class="form-group">
									<label for="ort">Ort</label>
									<input type="text" class="form-control"  name="ort" value="<?php echo $verlage['ort']; ?>" id="ort">
								</div>
								<div class="form-group">
									<label for="land_id">Land</label>
									<select class="form-control" name="land_id" id="land_id">
										<option value="">Land wählen</option>
										<?php foreach ($lands as $key => $land) { ?>
										<option value="<?php echo $key;?>" <?php echo ($verlage['land_id'] == $key ? 'selected' : ''); ?>><?php echo $land;?></option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label for="telefon">Telefon</label>
									<input type="text" class="form-control" name="telefon" value="<?php echo $verlage['telefon']; ?>" id="telefon" data-mask="99999-99999">
								</div>
								
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="fax">Fax</label>
									<input type="text" class="form-control" name="fax" value="<?php echo $verlage['fax']; ?>" id="fax" data-mask="99999-99999">
								</div>
								<div class="form-group">
									<label for="email">Email</label><span class="error-text"></span>
									<input type="email" class="form-control" name="email" value="<?php echo $verlage['email']; ?>" id="email">
								</div>
								<div class="form-group">
									<label for="homepage">Homepage</label>
									<input type="homepage" class="form-control" name="homepage" value="<?php echo $verlage['homepage']; ?>" id="homepage">
								</div>
								<div class="form-group">
									<label for="bemerkungen">Bemerkungen</label>
									<textarea id="bemerkungen" name="bemerkungen" class="form-control texteditor" aria-hidden="true"><?php echo $verlage['bemerkungen']; ?></textarea>
								</div>
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <input class="btn btn-success" type="submit" value="Änderungen speichern" name="ÄnderungenSpeichern" id="saveFormBtn">
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/verlage/" id="cancelBtn">Abbrechen</a>
						<a href="<?php echo $absoluteUrl;?>stammdaten/verlage/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
		              </div>
		            </form>
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