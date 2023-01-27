<?php
include '../../lang/GermanWords.php';
include '../../config/route.php'; 
include '../../api/global-grading-setting.php';
include '../../inc/header.php';
include '../../inc/sidebar.php';
?>
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Grade Schriftarten <!-- Global Grading Settings -->
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
        <li class="active">Grade Schriftarten</li> <!--Global Grading Settings -->
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
		            <!-- /.box-header -->
		            <!-- <form id="listViewForm" data-action="delete" data-source="quelle" data-source_id_name="quelle_id"> -->
		            <form class="content-form" id="globalGradingsForm" autocomplete="off" data-action="save" data-source="global-grading-set" enctype="multipart/form-data">
			            <div class="box-body">
		            		<div class="row hidden">
		            			<div class="col-md-4 col-md-offset-4">
		            				<div class="form-group">
										<label for="global_grading_sets">Global grading sets</label><span class="error-text"></span>
										<select name="global_grading_sets_id" id="global_grading_sets_id" class="form-control">
									        <?php 
									        	foreach ($allGlobalGradingSets as $key => $gradingSet) { 
									        		if($gradingSet['global_grading_sets_id'] == 1){
									        ?>
												<option <?php echo ($gradingSet['active'] == 1) ? "selected" : ""; ?> value="<?php echo $gradingSet['global_grading_sets_id'];?>"><?php echo $gradingSet['name']; ?></option>
											<?php 
													}
												} 
											?>
									    </select>
									</div>
		            			</div>
		            		</div>
		            		<div id="format_grade_container" style="margin-top: 12px;">
		            			<?php foreach ($allGlobalGradingSets as $gradingSetKey => $gradingSet) { ?>
			            		<?php if($gradingSet['active'] == 1) { ?>
			            		<?php foreach ($gradingSet['globalgradingsetvalues'] as $gradingSetValueskey => $gradingSetValues) { ?>
				            		<div class="row">
				            			<div class="col-md-6 col-md-offset-3">
				            				<div class="row">
						            			<div class="col-sm-5">
						            				<div class="form-group">
						            					<label><?php echo $gradingSetValues['format_name'] ?></label>
						            					<p>E.g.: <?php echo $gradingSetValues['format_example'] ?></p>
						            				</div>
						            			</div>
						            			<div class="col-sm-7">
						            				<div class="form-group">
					            						<select data-active-grade="<?php echo $gradingSetValues['format_grade']; ?>" name="format_grade_<?php echo $gradingSetValues['global_grading_set_values_id']; ?>" id="format_grade_<?php echo $gradingSetValues['global_grading_set_values_id']; ?>" class="form-control global-grade-options">
					            							<option value=""></option>			
																		<option <?php echo ($gradingSetValues['format_grade'] == "0") ? "selected" : ""; ?> value="0">0</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "1") ? "selected" : ""; ?> value="1">1</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "1.5") ? "selected" : ""; ?> value="1.5">1½</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "2") ? "selected" : ""; ?> value="2">2</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "2.5") ? "selected" : ""; ?> value="2.5">2½</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "3") ? "selected" : ""; ?> value="3">3</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "3.5") ? "selected" : ""; ?> value="3.5">3½</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "4") ? "selected" : ""; ?> value="4">4</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "4.5") ? "selected" : ""; ?> value="4.5">4½</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "5") ? "selected" : ""; ?> value="5">5</option>
																		<option <?php echo ($gradingSetValues['format_grade'] == "5.5") ? "selected" : ""; ?> value="5.5">5.5</option>
													   		 	</select> 
						            				</div>
						            			</div>
						            		</div>
				            			</div>
				            		</div>
			            		<?php } ?>
			            		<?php } ?>
			            		<?php } ?>
		            		</div>
				        </div>
				        <!-- /.box-body -->

				        <div class="box-footer">
		               		<!-- <input class="btn btn-success save-global-grading-btn" type="submit" value="Speichern" name="Speichern" id="saveFormBtn"> -->
							<a class="btn btn-default" href="<?php echo $absoluteUrl;?>" id="cancelBtn">Abbrechen</a>
							<button type="reset" id="reset" class="sr-only"></button>
							<a href="<?php echo $absoluteUrl;?>" class="pull-right btn btn-primary" style="background: #000;">Zurück</a>
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