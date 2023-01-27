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
							    <li class="active"><a data-toggle="tab" href="#fonts">Schriftart</a></li> <!-- Fonts -->
							    <li><a data-toggle="tab" href="#symptom_types">Symptomart</a></li><!-- Symptom types -->
							</ul>
							<div class="tab-content">
								<!-- Font tab start -->
								<div id="fonts" class="tab-pane fade in active">
									<div id="grading_format_container" class="row" style="margin-top: 25px;">
										<div class="col-md-6">
											<div class="form-group">
												<label for="normal">Normal</label><span class="error-text">Normal</span>
												<select name="normal" id="normal" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_within_parentheses">Normal in Klammern</label><span class="error-text">(Normal)</span> <!-- Normal within parentheses -->
												<select name="normal_within_parentheses" id="normal_within_parentheses" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_end_with_t">Normal mit t.</label><span class="error-text">Normal, t.</span> <!-- Normal end with t -->
												<select name="normal_end_with_t" id="normal_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_end_with_tt">Normal mit tt.</label><span class="error-text">Normal, tt.</span> <!-- Normal end with tt -->
												<select name="normal_end_with_tt" id="normal_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_begin_with_degree">Normal mit ° am Anfang</label><span class="error-text">°Normal</span> <!-- Normal begin with degree -->
												<select name="normal_begin_with_degree" id="normal_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_end_with_degree">Normal mit ° am Ende</label><span class="error-text">Normal,°</span> <!-- Normal end with degree -->
												<select name="normal_end_with_degree" id="normal_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_begin_with_asterisk">Normal mit * am Anfang</label><span class="error-text">*Normal</span> <!-- Normal begin with asterisk -->
												<select name="normal_begin_with_asterisk" id="normal_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_begin_with_asterisk_end_with_t">Normal mit * am Anfang und t.</label><span class="error-text">*Normal, t.</span> <!-- Normal begin with asterisk end with t -->
												<select name="normal_begin_with_asterisk_end_with_t" id="normal_begin_with_asterisk_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_begin_with_asterisk_end_with_tt">Normal mit * am Anfang und tt.</label><span class="error-text">*Normal, tt.</span> <!-- Normal begin with asterisk end with tt -->
												<select name="normal_begin_with_asterisk_end_with_tt" id="normal_begin_with_asterisk_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="normal_begin_with_asterisk_end_with_degree">Normal mit * am Anfang und °</label><span class="error-text">*Normal,°</span> <!-- Normal begin with asterisk end with degree -->
												<select name="normal_begin_with_asterisk_end_with_degree" id="normal_begin_with_asterisk_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift">Sperrschrift</label><span class="error-text text-sperrschrift">Sperrschrift</span>
												<select name="sperrschrift" id="sperrschrift" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift_begin_with_degree">Sperrschrift mit ° am Anfang</label><span class="error-text text-sperrschrift">°Sperrschrift</span> <!-- Sperrschrift begin with degree -->
												<select name="sperrschrift_begin_with_degree" id="sperrschrift_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift_begin_with_asterisk">Sperrschrift mit * am Anfang</label><span class="error-text text-sperrschrift">*Sperrschrift</span><!-- Sperrschrift begin with asterisk -->
												<select name="sperrschrift_begin_with_asterisk" id="sperrschrift_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift_bold">Sperrschrift fett</label><span class="error-text text-sperrschrift"><b>Sperrschrift</b></span><!-- Sperrschrift bold -->
												<select name="sperrschrift_bold" id="sperrschrift_bold" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift_bold_begin_with_degree">Sperrschrift fett mit ° am Anfang</label><span class="error-text text-sperrschrift"><b>°Sperrschrift</b></span><!-- Sperrschrift bold begin with degree -->
												<select name="sperrschrift_bold_begin_with_degree" id="sperrschrift_bold_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="sperrschrift_bold_begin_with_asterisk">Sperrschrift fett mit * am Anfang</label><span class="error-text text-sperrschrift"><b>*Sperrschrift</b></span> <!-- Sperrschrift bold begin with asterisk -->
												<select name="sperrschrift_bold_begin_with_asterisk" id="sperrschrift_bold_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv">Kursiv</label><span class="error-text"><i>Kursiv</i></span>
												<select name="kursiv" id="kursiv" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_end_with_t">Kursiv mit t.</label><span class="error-text"><i>Kursiv, t.</i></span> <!-- Kursiv end with t -->
												<select name="kursiv_end_with_t" id="kursiv_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_end_with_tt">Kursiv mit tt.</label><span class="error-text"><i>Kursiv, tt.</i></span><!-- Kursiv end with tt -->
												<select name="kursiv_end_with_tt" id="kursiv_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_begin_with_degree">Kursiv mit ° am Anfang</label><span class="error-text"><i>°Kursiv</i></span> <!-- Kursiv begin with degree -->
												<select name="kursiv_begin_with_degree" id="kursiv_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_end_with_degree">Kursiv mit ° am Ende</label><span class="error-text"><i>Kursiv,°</i></span> <!-- Kursiv end with degree -->
												<select name="kursiv_end_with_degree" id="kursiv_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_begin_with_asterisk">Kursiv mit * am Anfang</label><span class="error-text"><i>*Kursiv</i></span> <!-- Kursiv begin with asterisk -->
												<select name="kursiv_begin_with_asterisk" id="kursiv_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_begin_with_asterisk_end_with_t">Kursiv mit * am Anfang und t.</label><span class="error-text"><i>*Kursiv, t.</i></span> <!-- Kursiv begin with asterisk end with t -->
												<select name="kursiv_begin_with_asterisk_end_with_t" id="kursiv_begin_with_asterisk_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_begin_with_asterisk_end_with_tt">Kursiv mit * am Anfang und tt.</label><span class="error-text"><i>*Kursiv, tt.</i></span><!-- Kursiv begin with asterisk end with tt -->
												<select name="kursiv_begin_with_asterisk_end_with_tt" id="kursiv_begin_with_asterisk_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_begin_with_asterisk_end_with_degree">Kursiv mit * am Anfang und ° am Ende</label><span class="error-text"><i>*Kursiv,°</i></span><!-- Kursiv begin with asterisk end with degree -->
												<select name="kursiv_begin_with_asterisk_end_with_degree" id="kursiv_begin_with_asterisk_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_bold">Kursiv fett</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>Kursiv</b></i></span> <!-- Kursiv bold -->
												<select name="kursiv_bold" id="kursiv_bold" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_bold_begin_with_asterisk_end_with_t">Kursiv fett mit * am Anfang und t.</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>*Kursiv, t.</b></i></span> <!-- Kursiv bold begin with asterisk end with t -->
												<select name="kursiv_bold_begin_with_asterisk_end_with_t" id="kursiv_bold_begin_with_asterisk_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
										</div>

										<div class="col-md-6">
											<div class="form-group">
												<label for="kursiv_bold_begin_with_asterisk_end_with_tt">Kursiv fett mit * am Anfang und tt.</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>*Kursiv, tt.</b></i></span> <!-- Kursiv bold begin with asterisk end with tt -->
												<select name="kursiv_bold_begin_with_asterisk_end_with_tt" id="kursiv_bold_begin_with_asterisk_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_bold_begin_with_degree">Kursiv fett mit ° am Anfang</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>°Kursiv</b></i></span> <!-- Kursiv bold begin with degree -->
												<select name="kursiv_bold_begin_with_degree" id="kursiv_bold_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_bold_begin_with_asterisk">Kursiv fett mit * am Anfang</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>*Kursiv</b></i></span> <!-- Kursiv bold begin with asterisk -->
												<select name="kursiv_bold_begin_with_asterisk" id="kursiv_bold_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="kursiv_bold_begin_with_asterisk_end_with_degree">Kursiv fett mit * am Anfang und ° am Ende</label><span class="error-text kursiv-blod kursiv-blod-example-text"><i><b>*Kursiv,°</b></i></span> <!-- Kursiv bold begin with asterisk end with degree -->
												<select name="kursiv_bold_begin_with_asterisk_end_with_degree" id="kursiv_bold_begin_with_asterisk_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett">Fett</label> <span class="error-text"><b>Fett</b></span> 
												<select name="fett" id="fett" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_end_with_t">Fett mit t.</label><span class="error-text"><b>Fett, t.</b></span> <!-- Fett end with t -->
												<select name="fett_end_with_t" id="fett_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_end_with_tt">Fett mit tt.</label><span class="error-text"><b>Fett, tt.</b></span> <!-- Fett end with tt -->
												<select name="fett_end_with_tt" id="fett_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_begin_with_degree">Fett mit ° am Anfang</label><span class="error-text"><b>°Fett</b></span> <!-- Fett begin with degree -->
												<select name="fett_begin_with_degree" id="fett_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_end_with_degree">Fett mit ° am Ende</label><span class="error-text"><b>Fett,°</b></span> <!-- Fett end with degree -->
												<select name="fett_end_with_degree" id="fett_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_begin_with_asterisk">Fett mit * am Anfang</label><span class="error-text"><b>*Fett</b></span> <!-- Fett begin with asterisk -->
												<select name="fett_begin_with_asterisk" id="fett_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_begin_with_asterisk_end_with_t">Fett mit * am Anfang und t.</label><span class="error-text"><b>*Fett, t.</b></span> <!-- Fett begin with asterisk end with t -->
												<select name="fett_begin_with_asterisk_end_with_t" id="fett_begin_with_asterisk_end_with_t" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_begin_with_asterisk_end_with_tt">Fett mit * am Anfang und tt.</label><span class="error-text"><b>*Fett, tt.</b></span> <!-- Fett begin with asterisk end with tt -->
												<select name="fett_begin_with_asterisk_end_with_tt" id="fett_begin_with_asterisk_end_with_tt" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="fett_begin_with_asterisk_end_with_degree">Fett mit * am Anfang und °</label><span class="error-text"><b>*Fett,°</b></span> <!-- Fett begin with asterisk end with degree -->
												<select name="fett_begin_with_asterisk_end_with_degree" id="fett_begin_with_asterisk_end_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross">Gross</label><span class="error-text">GROSS</span>
												<select name="gross" id="gross" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross_begin_with_degree">Gross mit ° am Anfang</label><span class="error-text">°GROSS</span> <!-- Gross begin with degree -->
												<select name="gross_begin_with_degree" id="gross_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross_begin_with_asterisk">Gross mit * am Anfang</label><span class="error-text">*GROSS</span> <!-- Gross begin with asterisk -->
												<select name="gross_begin_with_asterisk" id="gross_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross_bold">Gross fett</label><span class="error-text"><b>GROSS</b></span> <!-- Gross bold -->
												<select name="gross_bold" id="gross_bold" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross_bold_begin_with_degree">Gross fett mit ° am Anfang</label><span class="error-text"><b>°GROSS</b></span> <!-- Gross bold begin with degree -->
												<select name="gross_bold_begin_with_degree" id="gross_bold_begin_with_degree" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="gross_bold_begin_with_asterisk">Gross fett mit * am Anfang</label><span class="error-text"><b>*GROSS</b></span> <!-- Gross bold begin with asterisk -->
												<select name="gross_bold_begin_with_asterisk" id="gross_bold_begin_with_asterisk" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="pi_sign">Pi-Zeichen</label><span class="error-text">π</span>
												<select name="pi_sign" id="pi_sign" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="one_bar">Ein Balken</label><span class="error-text">|</span>
												<select name="one_bar" id="one_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="two_bar">Zwei Balken</label><span class="error-text">||</span>
												<select name="two_bar" id="two_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="three_bar">Drei Balken</label><span class="error-text">|||</span>
												<select name="three_bar" id="three_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="three_and_half_bar">dreieinhalb Takte</label><span class="error-text">|||-</span>
												<select name="three_and_half_bar" id="three_and_half_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="four_bar">Vier Balken</label><span class="error-text">||||</span>
												<select name="four_bar" id="four_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="four_and_half_bar">viereinhalb Takte</label><span class="error-text">||||-</span>
												<select name="four_and_half_bar" id="four_and_half_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
											<div class="form-group">
												<label for="five_bar">fünf Balken</label><span class="error-text">|||||</span>
												<select name="five_bar" id="five_bar" class="form-control">
													<option value="">Grade wählen</option>
													<option value="0">0</option>
													<option value="1">1</option>
													<option value="1.5">1½</option>
													<option value="2">2</option>
													<option value="2.5">2½</option>
													<option value="3">3</option>
													<option value="3.5">3½</option>
													<option value="4">4</option>
													<option value="4.5">4½</option>
													<option value="5">5</option>
													<option value="5.5">5½</option>
											    </select>
											</div>
										</div>
									</div>
								</div>
								<!-- Font tab end -->
								<!-- symptom type tab start -->
								<div id="symptom_types" class="tab-pane fade">
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