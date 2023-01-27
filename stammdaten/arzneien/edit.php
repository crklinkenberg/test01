<?php
include '../../lang/GermanWords.php';
include '../../config/route.php';
include '../../api/arznei.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Ändern Arznei
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class=""> <a href="<?php echo $absoluteUrl;?>stammdaten/arzneien">Arzneien</a></li>
        <li class="active"> Ändern Arznei</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
		            <div class="box-header with-border">
		              <p>The fields marked with * are mandatory</p>
		            </div>
		            <!-- /.box-header -->
		            <!-- form start -->
		            <form id="addArzneiForm" autocomplete="off" class="content-form" data-action="update" data-source="arznei" data-source_id_value="<?php echo $arzneien['arznei_id'];?>" data-source_id_name="arznei">
		              <div class="box-body">
		              	<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="titel">Remedy*</label><span class="error-text"></span>
									<input type="text" class="form-control" value="<?php echo $arzneien['titel'];?>" id="titel" name="titel" required autofocus>
								</div>
								<div class="form-group">
									<label for="kuerzel">Abbreviation (separated with "|") <!--Kürzel (mehrere mit "|" trennen!)--></label>
									<input type="text" class="form-control"  name="kuerzel" value="<?php echo $arzneien['kuerzel']; ?>" id="kuerzel">
								</div>
								<div class="form-group">
									<label for="synonyms">Synonyms (separated with ",")</label><span class="error-text"></span>
									<input type="text" class="form-control" value="<?php echo $arzneien['synonyms'];?>" id="synonyms" name="synonyms">
								</div>
								<div class="form-group">
									<label for="quelle">Source (read only field)</label>
									<select id="quelle" readonly class="select2 form-control" multiple="multiple" data-placeholder="Choose source" name="quelle_id[]">
								        <?php foreach ($quellenSelectBox as $key => $quelle) { ?>
											<option value="<?php echo $quelle['quelle_id'];?>" <?php foreach ($quelle_id_selected_values as $quelle_id_selected_value) {
												if($quelle_id_selected_value == $quelle['quelle_id']) echo 'selected';
											}?>><?php 
												$quellen_value = null;
												$quellen_value = $quellen_value.$quelle['code'];
												if(!empty($quelle['jahr'])) $quellen_value .= ' '.$quelle['jahr'];
												if(!empty($quelle['band'])) $quellen_value .= ', Band: '.$quelle['band'];
												if(!empty($quelle['nummer'])) $quellen_value .= ', Nr.: '. $quelle['nummer'];
												if(!empty($quelle['auflage'])) $quellen_value .= ', Auflage: '. $quelle['auflage'];
												 echo $quellen_value; ?></option>
										<?php } ?>
								    </select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="kommentar">Comment</label>
									<textarea id="kommentar" name="kommentar" class="texteditor" aria-hidden="true"><?php echo $arzneien['kommentar'];?></textarea>
								</div>
								<!-- <div class="form-group">
									<label for="unklarheiten">Unklarheiten</label>
									<textarea id="unklarheiten" name="unklarheiten" class="texteditor" aria-hidden="true"><?php //echo $arzneien['unklarheiten'];?></textarea>
								</div> -->
								<!-- <div class="form-group">
										<label for="autor_id">Autor / Herausgeber</label>
										<select id="autor_id" class="select2 form-control" multiple="multiple" data-placeholder="Select Autor / Herausgeber" name="autor_id[]">
									        <?php //foreach ($autorenSelectBox as $key => $autor) { ?>
											<option value="<?php //echo $autor['autor_id'];?>" <?php //foreach ($autor_id_selected_values as $autor_id_selected_value) {
												//if($autor_id_selected_value == $autor['autor_id']) echo 'selected';
											//}?>><?php //if(!empty($autor['suchname']) ) echo $autor['suchname']; else echo $autor['vorname'].' '.$autor['nachname'];  ?></option>
											<?php //} ?>
									    </select>
									</div> -->
							</div>
						</div>
		              </div>
		              <!-- /.box-body -->

		              <div class="box-footer">
		                <button class="btn btn-success" type="submit" id="saveFormBtn">Änderungen speichern</button>
						<a class="btn btn-default" href="<?php echo $absoluteUrl;?>stammdaten/arzneien/" id="cancelBtn">Abbrechen</a>
						<a href="<?php echo $absoluteUrl;?>stammdaten/arzneien/" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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