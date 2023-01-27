<?php
	include '../lang/GermanWords.php';
	include '../config/route.php';
	include '../api/quellen.php';
?>
<?php  
	include 'includes/php-head-includes-integrated.php';
	/*
	* Here we are displying the Materia medicas. The sources which are available for comparison are listed here. 
	* Once a particular source is used in creating another source(means saved comparison) then that source no longer will be the part of the materia medica.  
	*/
	$is_opened_a_saved_comparison = (isset($comparisonTableDataArr['is_opened_a_saved_comparison']) AND !empty($comparisonTableDataArr['is_opened_a_saved_comparison'])) ? $comparisonTableDataArr['is_opened_a_saved_comparison'] : "";
	$arzneiId = (isset($_GET['arznei_id_custom']) AND $_GET['arznei_id_custom'] != "") ? $_GET['arznei_id_custom'] : "";
	$arzneiHead = "Materia Medica";
	if($arzneiId != ""){
		$arzneiHeadResult = mysqli_query($db,"SELECT titel FROM arznei WHERE arznei_id = $arzneiId");
		if(mysqli_num_rows($arzneiHeadResult) > 0){
			$arzneiHeadData = mysqli_fetch_assoc($arzneiHeadResult);
			$arzneiHead = (isset($arzneiHeadData['titel']) AND $arzneiHeadData['titel'] != "") ? $arzneiHeadData['titel'] : "Materia Medica";
		}
	}
?>
<?php
	include '../inc/header-new.php';
	include 'additions/header-materia-medica.php';
	include '../inc/header-end.php';
	include '../inc/sidebar.php';
?>
<body>
	<div class="content-wrapper">
		<section class="content-header">
			<h1>Chapter Assignment</h1>
			<ol class="breadcrumb">
				<li><a href="http://www.newrepertory.com/"><i class="fa fa-dashboard"></i> Home</a></li>
				<li class="active">Chapter Assignment</li>
			</ol>
		</section>
		<section class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="box box-success">
						<!-- <div class="box-header with-border">
							<h3 class="box-title">
								Example
							</h3>
			            </div> -->
			            <!-- /.box-header -->
						<div class="box-body">
							<h4>Chapter Assignment</h4>
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group Text_form_group">
                                            <label class="control-label">Select Remedy<span class="required">*</span></label>
                                            <select class="form-control save-data" name="arznei_id" id="arznei_id" <?php if($is_opened_a_saved_comparison == 1){ ?> readonly <?php } ?>>
                                                <option value="">Select</option>
                                                <?php
                                                    $finalArzneiArray = array();
                                                    $finalResultSelection = mysqli_query($db,"SELECT arznei_id, quelle_id FROM pre_comparison_master_data WHERE comparison_save_status=2");
                                                    while($finalResultSelectionRow = mysqli_fetch_array($finalResultSelection)){
                                                        $finalArznei = $finalResultSelectionRow['arznei_id'];
                                                        $finalQuelleId = $finalResultSelectionRow['quelle_id'];
                                                        $quelleResult = mysqli_query($db,"SELECT quelle_id, is_materia_medica FROM quelle WHERE quelle_id = $finalQuelleId AND is_materia_medica = 1");
                                                        while($quelleResultRow = mysqli_fetch_array($quelleResult)){
                                                            $quelleIdToSend = $quelleResultRow['quelle_id'];
                                                            $arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei WHERE arznei_id = $finalArznei");
                                                            array_push($finalArzneiArray, $arzneiResult);
                                                        }
                                                    }
													$finalArzneiArray = array_unique($finalArzneiArray);
                                                    foreach($finalArzneiArray as $arrayData){
                                                        while($arzneiRow = mysqli_fetch_array($arrayData)){
                                                            $selected = ($arzneiRow['arznei_id'] == $arzneiId) ? 'selected' : '';
                                                            echo '<option '.$selected.' value="'.$arzneiRow['arznei_id'].'">'.$arzneiRow['titel'].'</option>';
                                                        }
                                                    }
                                                    
                                                ?>
                                            </select>
                                            <span class="error-text <?php if(isset($error_msg['arznei_id']) AND $error_msg['arznei_id'] != ""){ echo 'text-danger'; } ?>"><?php if(isset($error_msg['arznei_id']) AND $error_msg['arznei_id'] != ""){ echo $error_msg['arznei_id']; } ?></span>	
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
						</div>
					</div>
				</div>
			</div>
		</section>
		
	</div>
<?php
	include '../inc/footer-new.php';
	include 'additions/footer-chapter-assignment.php';
?>		