<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/quellen.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Neues Buch
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""><a href="<?php echo $absoluteUrl;?>stammdaten/quellen">Bücher</a></li>
        <li class="active"> Neues Buch</li>
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
		            <form class="content-form" id="addQuelleForm" autocomplete="off" data-action="add" data-source="quelle" enctype="multipart/form-data">
		              	<div class="box-body">
			              	<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="code">Kürzel*</label><span class="error-text"></span>
														<input type="text" class="form-control" id="code" name="code" autofocus required>
													</div>
													<div class="form-group">
														<label for="titel">Titel*</label><span class="error-text"></span>
														<input type="text" class="form-control" name="titel" id="titel">
													</div>
													<div class="form-group">
														<label for="autor_or_herausgeber">Autor / Herausgeber</label>
														<input type="text" class="form-control" id="autor_or_herausgeber" name="autor_or_herausgeber">
													</div>
													<div class="form-group">
														<label for="sprache">Sprache*</label><span class="error-text"></span>
														<select id="sprache" class="form-control" name="sprache">
															<option value="">Sprache wählen</option>
															<option value="deutsch">deutsch</option>
															<option value="englisch">englisch</option>
														</select>
													</div>
													<div class="form-group">
														<label for="herkunft_id">Herkunft</label>
														<select id="herkunft_id" class="form-control" name="herkunft_id">
															<option value="">Herkunft wählen</option>
															<?php foreach ($herkunfte as $key => $herkunft) { ?>
															<option value="<?php echo $herkunft['herkunft_id'];?>"><?php echo $herkunft['titel'];?></option>
															<?php } ?>
														</select>
													</div>
													<div class="form-group">
														<label for="quelle_schema_id">Schema</label>
														<select id="quelle_schema_id" class="form-control" name="quelle_schema_id">
															<option value="">Schema wählen</option>
															<?php foreach ($schemas as $key => $schema) { ?>
															<option value="<?php echo $key;?>"><?php echo $schema;?></option>
															<?php } ?>
														</select>
													</div>
													<div class="form-group">
														<label for="jahr">Jahr*</label><span class="error-text"></span>
														<input type="text" class="form-control" id="jahr" name="jahr">
													</div>
													<div class="form-group">
														<label for="source_type">Source type</label><span class="error-text"></span>
														<select id="source_type" class="form-control" name="source_type">
															<option value="">Select source type</option>
															<option value="Primary source">Primary source</option>
															<option value="Secondary source">Secondary source</option>
															<option value="Primary & secondary source mixture">Primary & secondary source mixture</option>
															<option value="Tertiary source">Tertiary source</option>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="band">Band</label>
														<input type="text" class="form-control" id="band" name="band">
													</div>
													<div class="form-group">
														<label for="nummer">Nummer</label>
														<input type="text" class="form-control" id="nummer" name="nummer">
													</div>
													<div class="form-group">
														<label for="auflage">Auflage<!-- * --></label><span class="error-text"></span>
														<input type="text" class="form-control" id="auflage" name="auflage">
													</div>
													<div class="form-group">
														<label for="verlag_id">Verlag<!-- * --></label><span class="error-text"></span>
														<select id="verlag_id" class="form-control" name="verlag_id">
															<option value="">Verlag wählen</option>
															<?php foreach ($verlage as $key => $verlag) { ?>
															<option value="<?php echo $verlag['verlag_id'];?>"><?php echo $verlag['titel'];?></option>
															<?php } ?>
														</select>
													</div>
													<div class="form-group">
														<label for="pruefer_id">Prüfer</label>
														<select id="pruefer_id" class="select2 form-control" multiple="multiple" data-placeholder="Select Prüfer" name="pruefer_id[]">
													        <?php 
													        	foreach ($prueferSelectBox as $prufKey => $prufVal) { 
													        		$prueferFullname = "";
																	$prueferFullname .= ($prufVal['titel'] != "") ? $prufVal['titel']." " : "";
																	$prueferFullname .= ($prufVal['vorname'] != "") ? $prufVal['vorname']." " : "";
																	$prueferFullname .= ($prufVal['nachname'] != "") ? $prufVal['nachname'] : "";
																	if(trim($prueferFullname) != "")
																	{
													        ?>
																		<option value="<?php echo $prufVal['pruefer_id'];?>"><?php echo trim($prueferFullname);  ?></option>
															<?php
																	} 
																} 
															?>
													    </select>
													</div>
													<div class="form-group">
														<label for="kommentar">Kommentar<!-- * --></label><span class="error-text"></span>
														<input type="text" class="form-control" id="kommentar" name="kommentar">
													</div>
													<div class="form-group">
														<label for="is_coding_with_symptom_number">Coding with symptom number needed?</label>
														<div>
															<label class="radio-inline">
														    	<input type="radio" name="is_coding_with_symptom_number" value="1">Yes
														    </label>
														    <label class="radio-inline">
														    	<input type="radio" name="is_coding_with_symptom_number" value="0" checked>No
														    </label>
														</div>
													</div>
													<div class="form-group">
														<label>Datei ( nur PDF, DOC und DOCX Dateien sind erlaubt. Maximale Dateigröße 30MB )</label>
														<input type="file" data-allowed-file-extensions="pdf doc docx" data-max-file-size="30M" name="file_url" class="dropify" data-height="100"/>
													</div>
												</div>
											</div>
		              	</div>
		              	<!-- /.box-body -->

		              	<div class="box-footer">
		               		<input class="btn btn-success" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/quellen/" id="cancelBtn">Abbrechen</a>
							<button type="reset" id="reset" class="sr-only"></button>
							<a href="<?php echo $absoluteUrl;?>stammdaten/quellen/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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