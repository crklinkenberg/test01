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
        Neuer Verlag
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/verlage/">Verlage</a></li>
        <li class="active"> Neuer Verlag</li>
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
		            <form class="content-form" id="addVerlageForm" data-action="add" data-source="verlage">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="code">Kürzel</label>
									<input type="text" class="form-control" name="code" value="<?php if(isset($code)) echo $code;?>" id="code" autofocus>
								</div>
								<div class="form-group">
									<label for="titel">Titel*</label><span class="error-text"></span>
									<input type="text" class="form-control" name="titel" value="<?php if(isset($titel)) echo $titel;?>" id="titel">
								</div>
								
								<div class="form-group">
									<label for="strasse">Strasse</label>
									<input type="text" class="form-control" name="strasse" value="<?php if(isset($strasse)) echo $strasse;?>" id="strasse">
								</div>
								<div class="form-group">
									<label for="plz">PLZ</label>
									<input type="text" class="form-control" id="plz" name="plz" value="<?php if(isset($plz)) echo $plz;?>">
								</div>
								<div class="form-group">
									<label for="ort">Ort</label>
									<input type="text" class="form-control"  name="ort" value="<?php if(isset($ort)) echo $ort;?>" id="ort">
								</div>
								<div class="form-group">
									<label for="land_id">Land</label>
									<select class="form-control" name="land_id" id="land_id">
										<option value="">Land wählen</option>
										<?php foreach ($lands as $key => $land) { ?>
										<option value="<?php echo $key;?>"><?php echo $land;?></option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label for="telefon">Telefon</label>
									<input type="text" class="form-control" name="telefon" value="<?php if(isset($telefon)) echo $telefon;?>" id="telefon" data-mask="99999-99999">
								</div>
								
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="fax">Fax</label>
									<input type="text" class="form-control" name="fax" value="<?php if(isset($fax)) echo $fax;?>" id="fax" data-mask="99999-99999">
								</div>
								<div class="form-group">
									<label for="email">Email</label><span class="error-text"></span>
									<input type="email" class="form-control" name="email" value="<?php if(isset($email)) echo $email;?>" id="email">
								</div>
								<div class="form-group">
									<label for="homepage">Homepage</label>
									<input type="homepage" class="form-control" name="homepage" value="<?php if(isset($homepage)) echo $homepage;?>" id="homepage">
								</div>
								<div class="form-group">
									<label for="bemerkungen">Bemerkungen</label>
									<textarea id="bemerkungen" name="bemerkungen" value="<?php if(isset($bemerkungen)) echo $bemerkungen;?>" class="form-control texteditor" aria-hidden="true"></textarea>
								</div>
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <input class="btn btn-success" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/verlage/" id="cancelBtn">Abbrechen</a>
						<button type="reset" id="reset" class="sr-only"></button>
						<a href="<?php echo $absoluteUrl;?>stammdaten/verlage/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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
include '../../inc/footer.php';
?>