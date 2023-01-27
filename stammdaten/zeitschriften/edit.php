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
        Ändern Zeitschrift
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften">Zeitschriften</a></li>
        <li class="active"> Ändern Zeitschrift</li>
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
		            <form class="formValid content-form" id="addZeitschriftForm" autocomplete="off" enctype="multipart/form-data" data-action="update" data-source="zeitschrift" data-source_id_value="<?php echo $zeitschriften['quelle_id'];?>" data-source_id_name="quelle">
		            	<input type="hidden" id="zeitschrift_id" name="zeitschrift_id" value="<?php echo $zeitschriften['quelle_id'];?>">
	              		<div class="box-body">
			              	<div class="row">
								<div class="col-md-6">

									<div class="form-group">
										<label for="herkunft_id">Herkunft</label>
										<select id="herkunft_id" class="form-control" name="herkunft_id" autofocus>
											<option value="">Herkunft wählen</option>
											<?php foreach ($herkunfte as $key => $herkunft) { ?>
											<option value="<?php echo $herkunft['herkunft_id'];?>" <?php if($zeitschriften['herkunft_id'] == $herkunft['herkunft_id'])  echo 'selected'; ?>><?php echo $herkunft['titel'];?></option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group">
										<label for="code">Kürzel*</label><span class="error-text"></span>
										<input type="text" class="form-control" value="<?php echo $zeitschriften['code']; ?>" id="code" name="code" required>
									</div>
									<div class="form-group">
										<label for="titel">Titel*</label><span class="error-text"></span>
										<input type="text" class="form-control" value="<?php echo $zeitschriften['titel']; ?>" name="titel" id="titel">
									</div>
									<div class="form-group">
										<label for="jahr">Jahr*</label><span class="error-text"></span>
										<input type="text" class="form-control" id="jahr" name="jahr" value="<?php echo $zeitschriften['jahr'];?>">
									</div>
									<div class="form-group">
										<label for="band">Band</label>
										<input type="text" class="form-control" id="band" name="band" value="<?php echo $zeitschriften['band'];?>">
									</div>
									<div class="form-group">
										<label for="jahrgang">Jahrgang</label>
										<input type="text" class="form-control" id="jahrgang" name="jahrgang" value="<?php echo $zeitschriften['jahrgang'];?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="sprache">Sprache*</label><span class="error-text"></span>
										<select id="sprache" class="form-control" name="sprache">
											<option value="">Sprache wählen</option>
											<option value="deutsch" <?php if($zeitschriften['sprache'] == 'deutsch')  echo 'selected'; ?>>deutsch</option>
											<option value="englisch" <?php if($zeitschriften['sprache'] == 'englisch')  echo 'selected'; ?>>englisch</option>
										</select>
									</div>
									<div class="form-group">
										<label for="nummer">Heft / Stück / Nummer</label>
										<input type="text" class="form-control" id="nummer" name="nummer" value="<?php echo $zeitschriften['nummer'];?>">
									</div>
									<div class="form-group">
										<label for="supplementheft">Supplementheft</label>
										<input type="text" class="form-control" id="supplementheft" name="supplementheft" value="<?php echo $zeitschriften['supplementheft'];?>">
									</div>
									<div class="form-group">
										<label for="autor_id">Autor / Herausgeber</label><span class="error-text"></span>
										<select id="autor_id" class="select2 form-control" multiple="multiple" data-placeholder="Select Autor / Herausgeber" name="autor_id[]">
									        <?php foreach ($autorenSelectBox as $key => $autor) { ?>
											<option value="<?php echo $autor['autor_id'];?>" <?php foreach ($autor_id_selected_values as $autor_id_selected_value) {
												if($autor_id_selected_value == $autor['autor_id']) echo 'selected';
											}?>><?php if(!empty($autor['suchname']) ) echo $autor['suchname']; else echo $autor['vorname'].' '.$autor['nachname'];  ?></option>
											<?php } ?>
									    </select>
									</div>
									<div class="form-group">
										<label for="kommentar">Kommentar<!-- * --></label><span class="error-text"></span>
										<input type="text" class="form-control" id="kommentar" name="kommentar" value="<?php echo $zeitschriften['kommentar'];?>">
									</div>
									<div class="form-group">
										<label>Datei ( nur PDF, DOC und DOCX Dateien sind erlaubt. Maximale Dateigröße 30MB)</label>
										<input name="file_url" data-max-file-size="30M" data-default-file="<?php echo $zeitschriften['file_url'];?>" data-allowed-file-extensions="pdf doc docx" type="file" class="dropify" data-height="100" />
									</div>
								</div>
							</div>
			            </div>
		              <!-- /.box-body -->

			            <div class="box-footer">
			                <input class="btn btn-success" type="submit" value="Änderungen speichern" name="ÄnderungenSpeichern" id="saveFormBtn">
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/zeitschriften/" id="cancelBtn">Abbrechen</a>
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