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
        Settings
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class="active"><a href="<?php echo $absoluteUrl;?>stammdaten/quelle-settings">Settings</a></li><!-- Quelle Settings -->
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
		            <!-- <form class="content-form" id="quelleSettingsForm" autocomplete="off" data-action="add" data-source="quelle" enctype="multipart/form-data"> -->
		            <form class="content-form" id="quelleSettingsForm" autocomplete="off" data-action="save-quelle-settings" data-source="quelle" enctype="multipart/form-data">
		              	<div class="box-body">
		              		<div class="row">
			              		<div class="col-md-8 col-md-offset-2">
			              			<div class="form-group">
										<label for="quelle_settings_quelle_id">Quelle</label><span class="error-text"></span>
										<select name="quelle_settings_quelle_id" id="quelle_settings_quelle_id" class="form-control">
											<option value="">Quelle wählen</option>
									        <?php foreach ($allQuelleZeitschriftSelectBox as $key => $quelleZeitschrift) { ?>
												<option value="<?php echo $quelleZeitschrift['quelle_id'];?>"><?php 
													$quellen_value = null;
													$quellen_value = $quellen_value.$quelleZeitschrift['code'];
													if(!empty($quelleZeitschrift['jahr'])) $quellen_value .= ' '.$quelleZeitschrift['jahr'];
													if(!empty($quelleZeitschrift['band'])) $quellen_value .= ', Band: '.$quelleZeitschrift['band'];
													if(!empty($quelleZeitschrift['nummer'])) $quellen_value .= ', Nr.: '. $quelleZeitschrift['nummer'];
													if(!empty($quelleZeitschrift['auflage'])) $quellen_value .= ', Auflage: '. $quelleZeitschrift['auflage'];
													 echo $quellen_value; ?></option>
											<?php } ?>
									    </select>
									</div>
			              		</div>
			              	</div>

			              	<div id="quelle_grading_loader" class="row hidden" style="margin-top: 45px;">
			              		<div class="col-md-8 col-md-offset-2 text-center">
			              			Loading... <img src="<?php echo $absoluteUrl;?>assets/img/loader.gif" alt="Loader">
			              		</div>
			              	</div>
			              	<!-- Tab start -->
			              	<ul class="nav nav-tabs" id="myTab">
							    <li class="active"><a data-toggle="tab" href="#symptom_types">Symptomart</a></li><!-- Symptom types -->
							</ul>
							<div class="tab-content">
								<!-- symptom type tab start -->
								<div id="symptom_types" class="tab-pane fade in active">
									<div id="symptom_types_container" class="row" style="margin-top: 25px;">
										<div class="col-md-6">
											<div class="form-group">
												<label for="symptom_type_for_whole">Symptom type for the whole source</label><span class="error-text"></span>
												<select name="symptom_type_for_whole" id="symptom_type_for_whole" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="symptoms_with_reference">Symptoms with reference</label><span class="error-text"></span>
												<select name="symptoms_with_reference" id="symptoms_with_reference" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="symptoms_without_reference">Symptoms without reference</label><span class="error-text"></span>
												<select name="symptoms_without_reference" id="symptoms_without_reference" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="symptoms_with_provers">Symptoms with provers</label><span class="error-text"></span>
												<select name="symptoms_with_provers" id="symptoms_with_provers" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="symptoms_without_provers">Symptoms without provers</label><span class="error-text"></span>
												<select name="symptoms_without_provers" id="symptoms_without_provers" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="symptom_with_A_f_d_H">Symptom with Archiv f. d. homöop. Heilk. V. III as reference, or [A.f.d.H.] behind the symptom.</label><span class="error-text"></span>
												<select name="symptom_with_A_f_d_H" id="symptom_with_A_f_d_H" class="form-control">
													<option value="">wählen</option>
													<option value="proving">Proving symptom</option>
													<option value="intoxication">Intoxication</option>
													<option value="clinical">Clinical symptom</option>
													<option value="proving_intoxication_clinical_not_defined">Proving symptom / Intoxication / Clinical symptom not clearly defined</option>
													<option value="characteristic">Characteristic symptom</option>
													<option value="characteristic_not_defined">Characteristic symptom not clearly identified / defined</option>
											    </select>
											</div>
										</div>
									</div>
								</div>
								<!-- symptom type tab end -->
							</div>
							<!-- Tab end -->
		              	</div>
		              	<!-- /.box-body -->

		              	<div class="box-footer">
		               		<input class="btn btn-success save-quelle-settings-btn" type="submit" value="Speichern" name="Speichern" id="saveFormBtn">
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>" id="cancelBtn">Abbrechen</a>
							<button type="reset" id="reset" class="sr-only"></button>
							<a href="<?php echo $absoluteUrl;?>" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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