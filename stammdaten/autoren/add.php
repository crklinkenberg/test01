<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/autoren.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Neuer Autor
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/autoren/">Autoren</a></li>
        <li class="active"> Neuer Autor</li>
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
		            <form class="content-form" id="addAutorenForm" data-action="add" data-source="autor" autocomplete="off">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="titel">Titel</label>
									<select class="form-control" name="titel" id="titel" autofocus>
										<option value="">Titel wählen</option>
										<?php foreach ($autorTitels as $key => $autorTitel) { ?>
										<option value="<?php echo $autorTitel;?>"><?php echo $autorTitel;?></option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label for="vorname">Vorname</label>
									<input type="text" class="form-control" name="vorname" value="<?php if(isset($vorname)) echo $vorname;?>" id="vorname">
								</div>
								<div class="form-group">
									<label for="nachname">Nachname*</label><span class="error-text"></span>
									<input type="text" class="form-control" id="nachname" name="nachname" value="<?php if(isset($nachname)) echo $nachname;?>" required>
								</div>
								<div class="form-group">
									<label for="suchname">Suchname</label>
									<input type="text" class="form-control"  name="suchname" value="<?php if(isset($suchname)) echo $suchname;?>" id="suchname">
								</div>
								<div class="form-group">
									<label for="geburtsjahr">Geburtsjahr/ datum</label>
									<input type="text" class="form-control" name="geburtsdatum" value="<?php if(isset($geburtsjahr)) echo $geburtsjahr;?>" id="geburtsjahr" data-mask="99/99/9999">
								</div>
								
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="todesjahr">Todesjahr/ datum</label>
									<input type="text" class="form-control" name="sterbedatum" value="<?php if(isset($todesjahr)) echo $todesjahr;?>" id="todesjahr" data-mask="99/99/9999">
								</div>
								<div class="form-group">
									<label for="kommentar">Kommentar</label>
									<textarea id="kommentar" name="kommentar" value="<?php if(isset($kommentar)) echo $kommentar;?>" class="form-control texteditor" aria-hidden="true"></textarea>
								</div>
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <input class="btn btn-success" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/autoren/" id="cancelBtn">Abbrechen</a>
						<button type="reset" id="reset" class="sr-only"></button>
						<a href="<?php echo $absoluteUrl;?>stammdaten/autoren/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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