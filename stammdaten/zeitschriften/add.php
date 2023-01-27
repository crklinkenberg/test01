<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../lang/GermanWords.php';
include '../../api/zeitschriften.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Neue Zeitschrift
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften">Zeitschriften</a></li>
        <li class="active"> Neue Zeitschrift</li>
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
		            <form class="content-form" id="addZeitschriftForm" autocomplete="off" enctype="multipart/form-data" data-action="add" data-source="zeitschrift">
		              	<div class="box-body">
			              	<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="herkunft_id">Herkunft</label>
										<select id="herkunft_id" class="form-control" name="herkunft_id" autofocus>
											<option value="">Herkunft wählen</option>
											<?php foreach ($herkunfte as $key => $herkunft) { ?>
											<option value="<?php echo $herkunft['herkunft_id'];?>"><?php echo $herkunft['titel'];?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label for="code">Kürzel*</label><span class="error-text"></span>
										<input type="text" class="form-control" value="<?php if(isset($code)) echo $code;?>" id="code" name="code" required>
									</div>
									<div class="form-group">
										<label for="titel">Titel*</label><span class="error-text"></span>
										<input type="text" class="form-control" value="<?php if(isset($titel)) echo $titel;?>" name="titel" id="titel">
									</div>
									<div class="form-group">
										<label for="jahr">Jahr*</label><span class="error-text"></span>
										<input type="text" class="form-control" id="jahr" name="jahr" value="<?php if(isset($jahr)) echo $jahr;?>">
									</div>
									<div class="form-group">
										<label for="band">Band</label>
										<input type="text" class="form-control" id="band" name="band" value="<?php if(isset($band)) echo $band;?>">
									</div>
									<div class="form-group">
										<label for="jahrgang">Jahrgang</label>
										<input type="text" class="form-control" id="jahrgang" name="jahrgang" value="<?php if(isset($jahrgang)) echo $jahrgang;?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="sprache">Sprache*</label><span class="error-text"></span>
										<select id="sprache" class="form-control" name="sprache">
											<option value="">Sprache wählen</option>
											<option value="deutsch">deutsch</option>
											<option value="englisch">englisch</option>
										</select>
									</div>
									<div class="form-group">
										<label for="nummer">Heft / Stück / Nummer</label>
										<input type="text" class="form-control" id="nummer" name="nummer" value="<?php if(isset($nummer)) echo $nummer;?>">
									</div>
									<div class="form-group">
										<label for="supplementheft">Supplementheft</label>
										<input type="text" class="form-control" id="supplementheft" name="supplementheft" value="<?php if(isset($supplementheft)) echo $supplementheft;?>">
									</div>
									<div class="form-group">
										<label for="autor_id">Autor / Herausgeber*</label><span class="error-text"></span>
										<select id="autor_id" class="select2 form-control" multiple="multiple" data-placeholder="Select Autor / Herausgeber" name="autor_id[]">
									        <?php foreach ($autorenSelectBox as $key => $autor) { ?>
											<option value="<?php echo $autor['autor_id'];?>"><?php if(!empty($autor['suchname']) ) echo $autor['suchname']; else echo $autor['vorname'].' '.$autor['nachname'];  ?></option>
											<?php } ?>
									    </select>
									</div>
									<div class="form-group">
										<label for="kommentar">Kommentar<!-- * --></label><span class="error-text"></span>
										<input type="text" class="form-control" id="kommentar" name="kommentar">
									</div>
									<div class="form-group">
										<label>Datei ( nur PDF, DOC und DOCX Dateien sind erlaubt. Maximale Dateigröße 30MB )</label>
										<input type="file" data-max-file-size="30M" data-allowed-file-extensions="pdf doc docx" name="file_url" class="dropify" data-height="100"/>
									</div>
								</div>
							</div>
		              	</div>
		              	<!-- /.box-body -->

		              	<div class="box-footer">
		               		<input class="btn btn-success" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften/" id="cancelBtn">Abbrechen</a>
							<button type="reset" id="reset" class="sr-only"></button>
							<a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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