<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/quellen.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
<style type="text/css">
	.remove-btn-existing-reference-set {
    padding: 4px 22px;
    margin: 21px 0px;
    font-size: 20px;
	}
</style>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ändern Buch
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""><a href="<?php echo $absoluteUrl;?>stammdaten/quellen">Bücher</a></li>
        <li class="active"> Ändern Buch</li>
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
		            <form class="content-form" id="addQuelleForm" data-action="update" data-source="quelle" data-source_id_value="<?php echo $quellen['quelle_id'];?>" data-source_id_name="quelle" autocomplete="off" enctype="multipart/form-data">
		            	<input type="hidden" name="quelle_id" value="<?php echo $quellen['quelle_id'];?>">
			            <div class="box-body">
			              	<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="code">Kürzel*</label><span class="error-text"></span>
														<input type="text" class="form-control" value="<?php echo $quellen['code']; ?>" id="code" name="code" autofocus required>
													</div>
													<div class="form-group">
														<label for="titel">Titel*</label><span class="error-text"></span>
														<input type="text" class="form-control" value="<?php echo $quellen['titel']; ?>" name="titel" id="titel">
													</div>
													<div class="form-group">
														<label for="autor_or_herausgeber">Autor / Herausgeber</label>
														<input type="text" class="form-control" id="autor_or_herausgeber" name="autor_or_herausgeber" value="<?php echo $quellen['autor_or_herausgeber'];?>">
													</div>
													<div class="form-group">
														<label for="sprache">Sprache*</label><span class="error-text"></span>
														<select id="sprache" class="form-control" name="sprache">
															<option value="">Sprache wählen</option>
															<option value="deutsch" <?php if($quellen['sprache'] == 'deutsch')  echo 'selected'; ?>>deutsch</option>
															<option value="englisch" <?php if($quellen['sprache'] == 'englisch')  echo 'selected'; ?>>englisch</option>
														</select>
													</div>
													<div class="form-group">
														<label for="herkunft_id">Herkunft</label>
														<select id="herkunft_id" class="form-control" name="herkunft_id">
															<option value="">Herkunft wählen</option>
															<?php foreach ($herkunfte as $key => $herkunft) { ?>
															<option value="<?php echo $herkunft['herkunft_id'];?>" <?php if($quellen['herkunft_id'] == $herkunft['herkunft_id'])  echo 'selected'; ?>><?php echo $herkunft['titel'];?></option>
															<?php } ?>
														</select>
													</div>
													<div class="form-group">
														<label for="quelle_schema_id">Schema</label>
														<select id="quelle_schema_id" class="form-control" name="quelle_schema_id">
															<option value="">Schema wählen</option>
															<?php foreach ($schemas as $key => $schema) { ?>
															<option value="<?php echo $key;?>" <?php if($quellen['quelle_schema_id'] == $key)  echo 'selected'; ?>><?php echo $schema;?></option>
															<?php } ?>
														</select>
													</div>
													<div class="form-group">
														<label for="jahr">Jahr*</label><span class="error-text"></span>
														<input type="text" class="form-control" id="jahr" name="jahr" value="<?php echo $quellen['jahr'];?>">
													</div>
													<div class="form-group">
														<label for="source_type">Source type</label><span class="error-text"></span>
														<select id="source_type" class="form-control" name="source_type">
															<option value="">Select source type</option>
															<option value="Primary source" <?php if($quellen['source_type'] == "Primary source")  echo 'selected'; ?>>Primary source</option>
															<option value="Secondary source" <?php if($quellen['source_type'] == "Secondary source")  echo 'selected'; ?>>Secondary source</option>
															<option value="Primary & secondary source mixture" <?php if($quellen['source_type'] == "Primary & secondary source mixture")  echo 'selected'; ?>>Primary & secondary source mixture</option>
															<option value="Tertiary source" <?php if($quellen['source_type'] == "Tertiary source")  echo 'selected'; ?>>Tertiary source</option>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="band">Band</label>
														<input type="text" class="form-control" id="band" name="band" value="<?php echo $quellen['band'];?>">
													</div>
													<div class="form-group">
														<label for="nummer">Nummer</label>
														<input type="text" class="form-control" id="nummer" name="nummer" value="<?php echo $quellen['nummer'];?>">
													</div>
													<div class="form-group">
														<label for="auflage">Auflage<!-- * --></label><span class="error-text"></span>
														<input type="text" class="form-control" id="auflage" name="auflage" value="<?php echo $quellen['auflage'];?>">
													</div>
													<div class="form-group">
														<label for="verlag_id">Verlag<!-- * --></label><span class="error-text"></span>
														<select id="verlag_id" class="form-control" name="verlag_id">
															<option value="">Verlag wählen</option>
															<?php foreach ($verlage as $key => $verlag) { ?>
															<option value="<?php echo $verlag['verlag_id'];?>" <?php if($quellen['verlag_id'] == $verlag['verlag_id'])  echo 'selected'; ?>><?php echo $verlag['titel'];?></option>
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
																		<option <?php if(in_array($prufVal['pruefer_id'], $pruefer_id_selected_values)){ echo 'selected'; } ?> value="<?php echo $prufVal['pruefer_id'];?>"><?php echo trim($prueferFullname);  ?></option>
															<?php
																	} 
																} 
															?>
													    </select>
													</div>
													<div class="form-group">
														<label for="kommentar">Kommentar<!-- * --></label><span class="error-text"></span>
														<input type="text" class="form-control" id="kommentar" name="kommentar" value="<?php echo $quellen['kommentar'];?>">
													</div>
													<div class="form-group">
														<label for="is_coding_with_symptom_number">Coding with symptom number needed?</label>
														<div>
															<label class="radio-inline">
														    	<input type="radio" name="is_coding_with_symptom_number" value="1" <?php if($quellen['is_coding_with_symptom_number'] == "1")  echo 'checked'; ?>>Yes
														    </label>
														    <label class="radio-inline">
														    	<input type="radio" name="is_coding_with_symptom_number" value="0" <?php if($quellen['is_coding_with_symptom_number'] == "0")  echo 'checked'; ?>>No
														    </label>
														</div>
													</div>
													<div class="form-group">
														<label>Datei ( nur PDF, DOC und DOCX Dateien sind erlaubt. Maximale Dateigröße 30MB )</label>
														<input name="file_url" data-max-file-size="30M" data-default-file="<?php echo $quellen['file_url'];?>" data-allowed-file-extensions="pdf doc docx" type="file" class="dropify" data-height="100" />
													</div>
												</div>
											</div>
											<?php 
												if(!empty($quellen['reference'])){ 
													foreach ($quellen['reference'] as $refKey => $refVal) {
														$fullReference = ($refVal['full_reference'] != "") ? $refVal['full_reference'] : "";
														$refNumber = (isset($refVal['pivot']['reference_number']) AND $refVal['pivot']['reference_number'] != "") ? $refVal['pivot']['reference_number'] : "";
														if($fullReference != ""){
															?>
																<div class="row">
																	<div class="col-md-1">
																		<button type="button" class="input-button remove-btn-existing-reference-set btn-danger"><i class="fa fa-minus" aria-hidden="true"></i></button>
																	</div>
																	<div class="col-md-5">
																		<div class="form-group">
																			<label for="reference-number">Literatur number</label><span class="error-text"></span>
																			<input type="text" class="form-control" name="reference_number[]" value="<?php echo $refNumber; ?>">
																		</div>
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			<label for="Literatur">Literatur</label><span class="error-text"></span>
																			<input type="text" class="form-control input-field" name="reference[]" value="<?php echo $fullReference; ?>"/>
																			<small>Example literatur: Matthioli, Comment. in Diosc. lib. IV. Cap. 73.</small>
																		</div>
																	</div>
																</div>
															<?php
														}
													}
												}
											?>
			            </div>
			              <!-- /.box-body -->

			            <div class="box-footer">
			                <input class="btn btn-success" type="submit" value="Änderungen speichern" name="ÄnderungenSpeichern" id="saveFormBtn">
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/quellen/" id="cancelBtn">Abbrechen</a>
							<a href="<?php echo $absoluteUrl;?>stammdaten/quellen/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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