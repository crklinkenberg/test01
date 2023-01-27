<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/pruefer.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Neuer Prover
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/autoren/">Prover</a></li>
        <li class="active"> New Prover</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
		            <div class="box-header with-border">
		              <p>The fields marked with * are mandatory<!--Die mit * gekennzeichneten Felder sind Pflichtfelder--></p>
		            </div>
		            <!-- /.box-header -->
		            <!-- form start -->
		            <form class="content-form" id="addAutorenForm" data-action="add" data-source="pruefer" autocomplete="off">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="titel">Title</label>
									<select class="form-control" name="titel" id="titel" autofocus>
										<option value="">Choose title<!--Titel wählen--></option>
										<?php foreach ($autorTitels as $key => $autorTitel) { ?>
										<option value="<?php echo $autorTitel;?>"><?php echo $autorTitel;?></option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group">
									<label for="vorname">First name</label>
									<input type="text" class="form-control" name="vorname" value="<?php if(isset($vorname)) echo $vorname;?>" id="vorname">
								</div>
								<div class="form-group">
									<label for="nachname">Last name*</label><span class="error-text"></span>
									<input type="text" class="form-control" id="nachname" name="nachname" value="<?php if(isset($nachname)) echo $nachname;?>" required>
								</div>
								<!-- <div class="form-group">
									<label for="suchname">Suchname/Search name</label>
									<input type="text" class="form-control"  name="suchname" value="<?php //if(isset($suchname)) echo $suchname;?>" id="suchname">
								</div> -->
								<div class="form-group">
									<label for="kuerzel">Abbreviation (separated with "|")</label>
									<input type="text" class="form-control"  name="kuerzel" value="<?php if(isset($kuerzel)) echo $kuerzel;?>" id="kuerzel">
								</div>
								<div class="form-group">
									<label for="geburtsjahr">Year of birth<!-- Geburtsjahr/ datum --></label>
									<input type="text" class="form-control" name="geburtsdatum" value="<?php if(isset($geburtsjahr)) echo $geburtsjahr;?>" id="geburtsjahr" data-mask="99/99/9999">
								</div>
								
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="todesjahr">Year of death<!-- Todesjahr/ datum --></label>
									<input type="text" class="form-control" name="sterbedatum" value="<?php if(isset($todesjahr)) echo $todesjahr;?>" id="todesjahr" data-mask="99/99/9999">
								</div>
								<div class="form-group">
									<label for="kommentar">Comment</label>
									<textarea id="kommentar" name="kommentar" value="<?php if(isset($kommentar)) echo $kommentar;?>" class="form-control texteditor" aria-hidden="true"></textarea>
								</div>
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <input class="btn btn-success" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/prufer/" id="cancelBtn">Abbrechen</a>
						<button type="reset" id="reset" class="sr-only"></button>
						<a href="<?php echo $absoluteUrl;?>stammdaten/prufer/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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