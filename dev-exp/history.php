<?php
    include '../lang/GermanWords.php';
    include '../config/route.php';
    include 'sub-section-config.php';
    include '../api/mainCall.php';
?>
<?php  
	$is_opened_a_saved_comparison = (isset($comparisonTableDataArr['is_opened_a_saved_comparison']) AND !empty($comparisonTableDataArr['is_opened_a_saved_comparison'])) ? $comparisonTableDataArr['is_opened_a_saved_comparison'] : "";
	$arzneiId = (isset($_GET['arznei_id_custom']) AND $_GET['arznei_id_custom'] != "") ? $_GET['arznei_id_custom'] : "";
?>
<?php
    include '../inc/header.php';
    include '../inc/sidebar.php';
?>
<!-- custom -->
<link rel="stylesheet" href="assets/css/custom.css">
<!-- new comparison table style -->
<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
	    <h1>History</h1>
	    <ol class="breadcrumb">
	    	<li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
	    	<li class="active">History</li>
	    </ol>
	</section>

  	<!-- Main content -->
  	<section class="content">
    <!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<?php //if(isset($_SESSION['user_type']) && ($_SESSION['user_type'] == 1 || $_SESSION['user_type'] == 2)) { ?>
            		<!-- <div class="box-header with-border">
		              	<h3 class="box-title">
		              		<a href="<#" class="btn btn-success"><i class="fa fa-plus"></i> &nbsp; Add</a>
		              	</h3>
	            	</div> -->
			     	<?php  //} ?>
		    		<!-- /.box-header -->
		    		<div class="box-body">
                        <div id="loader" class="form-group text-center">
                            Loading is not complete please wait <img src="assets/img/loader.gif" alt="Loading...">
                        </div>
                        <div class="row">
                            <!-- materia medica arznei search list -->
                            <?php include 'search-arznei-materia-medica.php'; ?>
                        </div>
                        <div class="row">  
                            <div class="col-sm-12">
                                <h2>History</h2> 
                                <div id="comparison_result_cnr" class="master-table-cnr">  
                                    <form name="result_frm" id="result_frm" action="" method="POST">      
                                        <table class="table table-bordered heading-table heading-table-bg table-vertical-middle-td">
                                            <thead>
                                                <tr>
                                                    <!-- <th style="width: 5%;">Status</th> -->
                                                    <th style="width: 3%;" class="text-center">...</th>
                                                    <th style="width: 5%;" class="text-center">Jahr</th>
                                                    <th style="width: 10%;" class="text-center">Kürzel</th>
                                                    <th style="width: 38%;">Titel</th>
                                                    <th style="width: 18%;">Date</th>
                                                    <th style="width: 18%;">Arznei</th>
                                                    <th style="width: 8%;">Reactivate</th>
                                                    <!-- <th style="width: 15%;">Ongoing vesrion</th> -->
                                                    <!-- <th style="width: 10%;">Approved</th> -->
                                                    <!-- <th style="width: 6%;">Approved</th> -->
                                                </tr>
                                            </thead>
                                        </table>
                                        <table class="table table-bordered table-vertical-middle-td table-hover">
                                            <tbody>
                                                <?php
                                                    //conditions custom
                                                    $conditions = ""; 
                                                    $conditions .= !empty( $_GET["arznei_id_custom"] ) ? "QIM.arznei_id =". $_GET['arznei_id_custom'] ." AND " : "";
                                                    $conditions .= !empty( $_GET["jahr_custom"] ) ? "Q.jahr LIKE '%". $_GET['jahr_custom'] ."%' AND " : "";
                                                    $conditions .= !empty( $_GET["date_custom"] ) ? "QIM.ersteller_datum LIKE '%". $_GET['date_custom'] ."%' AND " : "";
                                                    $conditions .= !empty( $_GET["titel_custom"] ) ? "Q.titel LIKE '%". $_GET['titel_custom'] ."%' AND " : "";
                                                    $conditions .= !empty( $_GET["code_custom"] ) ? "Q.code LIKE '%". $_GET['code_custom'] ."%' AND " : "";
                                                    //$conditions = rtrim($conditions, " AND");

                                                    // $comparisonCompletedResult = mysqli_query($db, "SELECT C.*, A.titel FROM pre_comparison_master_data AS C LEFT JOIN arznei AS A ON C.arznei_id = A.arznei_id WHERE C.status = 'done'");
                                                    $query = "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, Q.is_materia_medica, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, PCM.id AS pre_comparison_master_data_id, PCM.comparison_save_status, PCM.is_comparison_renamed, PCM.final_view FROM quelle_import_master AS QIM JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id LEFT JOIN pre_comparison_master_data AS PCM ON QIM.quelle_id = PCM.quelle_id WHERE $conditions Q.is_materia_medica = 0 OR PCM.is_comparison_renamed = 1 OR PCM.final_view = '1' ORDER BY Q.quelle_id DESC";
                                                    //echo $query;
                                                    $comparisonCompletedResult = mysqli_query($db, $query);
                                                    if(mysqli_num_rows($comparisonCompletedResult) > 0){
                                                        while($row = mysqli_fetch_array($comparisonCompletedResult)){
                                                            ?>
                                                            <tr id="row_<?php echo $row['id']; ?>" class="comparison-history-header">
                                                                <td style="width: 3%;" class="text-center"><?php if($row['is_comparison_renamed'] == 1){ echo '<a class="comparison-history-header-btn btn-info" href="javascript:void(0)"><span><i class="fas fa-angle-up"></i></span></a>'; } else { echo '-'; } ?></td>
                                                                <td style="width: 5%;" class="text-center"><?php echo $row['jahr']; ?></td>
                                                                <td style="width: 10%;" class="text-center">
                                                                    <?php 
                                                                    if($row['quelle_type_id'] != 3){
                                                                        echo $row['code'];
                                                                    }else{
                                                                        echo "-";
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td id="comparison_name_container_<?php echo $row['id']; ?>" style="width: 38%;">
                                                                    <?php 
                                                                        if($row['pre_comparison_master_data_id'] != ""){ 
                                                                    ?>
                                                                        <a class="text-info history-table-anchor-tag" href="comparison-history.php?comid=<?php echo $row['pre_comparison_master_data_id']; ?>"><?php echo $row['titel']; ?></a>
                                                                    <?php 
                                                                        }
                                                                        else
                                                                        {
                                                                            $quelle_name = $row['code']; 
                                                                            if(!empty($row['jahr'])){
                                                                                if (mb_strpos($quelle_name, $row['jahr']) === false)
                                                                                {
                                                                                    $quelle_name .= ', '.$row['jahr'];
                                                                                }
                                                                            }
                                                                            $quelle_name .= (!empty($row['titel'])) ? ', '.$row['titel'] : "";
                                                                            if($row['quelle_type_id'] == 1){
                                                                                if(!empty($row['bucher_autor_or_herausgeber'])) $quelle_name .= ', '.$row['bucher_autor_or_herausgeber'];
                                                                            }else if($row['quelle_type_id'] == 2){
                                                                                if(!empty($row['zeitschriften_autor_suchname']) ) 
                                                                                    $zeitschriften_autor = $row['zeitschriften_autor_suchname']; 
                                                                                else if($row['zeitschriften_autor_vorname'] != "" AND $row['zeitschriften_autor_nachname'] != "") 
                                                                                    $zeitschriften_autor = $row['zeitschriften_autor_vorname'].' '.$row['zeitschriften_autor_nachname'];
                                                                                else
                                                                                    $zeitschriften_autor = "";
                                                                                if(!empty($zeitschriften_autor)) $quelle_name .= ', '.$zeitschriften_autor;
                                                                            }
                                                                            // if(!empty($row['jahr'])) $quelle_name .= ', '.$row['jahr'];
                                                                            //echo $quelle_name;
                                                                            echo $row["titel"];

                                                                        } 
                                                                    ?>
                                                                </td>
                                                                <td style="width: 18%;"><?php echo date('d/m/Y h:i A', strtotime($row['ersteller_datum'])); ?></td>
                                                                <td style="width: 18%;">
                                                                    <?php
                                                                        $arzneiTitle = "";
                                                                        $arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei WHERE arznei_id = '".$row['arznei_id']."'");
                                                                        if(mysqli_num_rows($arzneiResult) > 0){
                                                                            $arzneiData = mysqli_fetch_assoc($arzneiResult);
                                                                            $arzneiTitle = (isset($arzneiData['titel']) AND $arzneiData['titel'] != "") ? $arzneiData['titel'] : "";
                                                                        }
                                                                        echo $arzneiTitle;
                                                                    ?>
                                                                </td>
                                                                <td style="width: 8%;" class="text-center">--</td>
                                                            </tr>
                                                            <?php
                                                                if($row['pre_comparison_master_data_id'] != "")
                                                                {
                                                                    $comparisonHistoryVersionsResult = mysqli_query($db, "SELECT QIM.*, Q.code, Q.titel, Q.jahr, Q.band, Q.nummer, Q.auflage, Q.quelle_type_id, Q.autor_or_herausgeber as bucher_autor_or_herausgeber, autor.suchname as zeitschriften_autor_suchname, autor.vorname as zeitschriften_autor_vorname, autor.nachname as zeitschriften_autor_nachname, PCMH.id as history_id, PCMH.comparison_name as history_comparison_name, PCMH.ersteller_datum as history_created_at FROM quelle_import_master AS QIM LEFT JOIN quelle AS Q ON QIM.quelle_id = Q.quelle_id LEFT JOIN quelle_autor ON Q.quelle_id = quelle_autor.quelle_id LEFT JOIN autor ON quelle_autor.autor_id = autor.autor_id LEFT JOIN pre_comparison_master_data_for_history AS PCMH ON QIM.quelle_id = PCMH.quelle_id WHERE $conditions PCMH.pre_comparison_master_id = ".$row['pre_comparison_master_data_id']." ORDER BY PCMH.ersteller_datum DESC");
                                                                    if(mysqli_num_rows($comparisonHistoryVersionsResult) > 0)
                                                                    {
                                                                    while($historyRow = mysqli_fetch_array($comparisonHistoryVersionsResult))
                                                                    {
                                                                    ?>
                                                                        <tr id="row_<?php echo $historyRow['history_id']; ?>" class="history-sub-row sub_row_<?php echo $row['id']; ?>">
                                                                            <td style="width: 3%;"></td>
                                                                            <td style="width: 5%;" class="text-center"><?php echo $row['jahr']; ?></td>
                                                                            <td style="width: 10%;" class="text-center">
                                                                                <?php 
                                                                                if($row['quelle_type_id'] != 3){
                                                                                    echo $row['code'];
                                                                                }else{
                                                                                    echo "-";
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 38%;">
                                                                                <?php 
                                                                                    if($historyRow['history_id'] != "")
                                                                                    { 
                                                                                        echo $historyRow['history_comparison_name'];
                                                                                ?>
                                                                                    <?php /*<a class="text-info history-table-anchor-tag" href="comparison.php?comid=<?php echo $historyRow['pre_comparison_master_data_id']; ?>&ishistory=1"><?php echo $historyRow['titel']; ?></a>*/ ?>
                                                                                <?php 
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 18%;"><?php echo date('d/m/Y h:i A', strtotime($historyRow['history_created_at'])); ?></td>
                                                                            <td style="width: 18%;">
                                                                                <?php
                                                                                    $arzneiTitle = "";
                                                                                    $arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei WHERE arznei_id = '".$historyRow['arznei_id']."'");
                                                                                    if(mysqli_num_rows($arzneiResult) > 0){
                                                                                        $arzneiData = mysqli_fetch_assoc($arzneiResult);
                                                                                        $arzneiTitle = (isset($arzneiData['titel']) AND $arzneiData['titel'] != "") ? $arzneiData['titel'] : "";
                                                                                    }
                                                                                    echo $arzneiTitle;
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 8%;" class="text-center">
                                                                                <a id="reactivate_<?php echo $historyRow['history_id']; ?>" title="Reactivate" class="text-info reactivate-quelle" data-comparison-history-id="<?php echo $historyRow['history_id']; ?>" data-arznei-id="<?php echo $historyRow['arznei_id']; ?>" data-quelle-id="<?php echo $historyRow['quelle_id']; ?>" href="javascript:void(0)">Reactivate</a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">No records found</td>
                                                        </tr>
                                                        <?php
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Global message modal start -->
                        <div class="modal fade" id="globalMsgModal" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Alert</h4>
                                    </div>
                                    <div id="global_msg_container" class="modal-body">
                                        
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Global message modal end -->

                        <!-- Translation modal start -->
                        <div class="modal fade" id="translationModal" role="dialog" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form id="add_translation_form" name="add_translation_form" action="" method="POST">
                                        <div class="modal-header">
                                            <button type="button" class="close add-translation-modal-btn" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Add translation</h4>
                                        </div>
                                        <div id="translation_container" class="modal-body">
                                            <div id="translation_modal_loader" class="form-group text-center hidden">
                                                <span class="loading-msg">Process is in progress please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                                                <span class="error-msg"></span>
                                            </div>
                                            <div class="row add-translation-input-field-container">
                                                <div class="col-sm-12">
                                                    <label class="control-label">Translation Method<span class="required">*</span></label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div id="translation_method_radio_buttons">
                                                        <label class="radio-inline"><input type="radio" name="translation_method" value="Professional Translation">Professional Translation</label>
                                                        <label class="radio-inline"><input type="radio" name="translation_method" value="Google Translation">Google Translation</label>
                                                    </div>
                                                    <span class="error-msg"></span>
                                                    <div class="spacer"></div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <label class="control-label">Text Editor<span class="required">*</span></label>
                                                    <textarea id="translation_symptoms" name="translation_symptoms" class="texteditor" aria-hidden="true"></textarea>
                                                    <span class="error-msg"></span>	
                                                    <div class="spacer"></div>
                                                    <span class="add-translation-global-error-msg"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" name="add_translation_master_id" id="add_translation_master_id">
                                            <input type="hidden" name="add_translation_arznei_id" id="add_translation_arznei_id">
                                            <input type="hidden" name="add_translation_quelle_id" id="add_translation_quelle_id">
                                            <input type="hidden" name="add_translation_language" id="add_translation_language">
                                            <button type="submit" class="btn btn-primary add-translation-modal-btn">Submit</button>
                                            <button type="button" class="btn btn-default add-translation-modal-btn" data-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Translation modal end -->

                        <!-- Add translation user approval modal start -->
                        <div class="modal fade" id="translationUserApprovalModal" role="dialog" data-backdrop="static" data-keyboard="false">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <!-- <form id="translation_user_approval_form" name="translation_user_approval_form" action="" method="POST"> -->
                                        <div class="modal-header">
                                            <button type="button" class="close translation-user-approval-modal-cancel-btn" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Need confirmation</h4>
                                        </div>
                                        <div id="translation_user_approval_container" class="modal-body">
                                            <div id="translation_user_approval_modal_loader" class="form-group text-center hidden">
                                                <span class="loading-msg">Process is in progress please wait <img src="assets/img/loader.gif" alt="Loading..."></span>
                                                <span class="error-msg"></span>
                                            </div>
                                            <div class="row">
                                                <div id="translation_user_approval_content" class="col-sm-12">
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" name="translation_user_approval_master_id" id="translation_user_approval_master_id">
                                            <input type="hidden" name="translation_user_approval_arznei_id" id="translation_user_approval_arznei_id">
                                            <input type="hidden" name="translation_user_approval_quelle_id" id="translation_user_approval_quelle_id">
                                            <input type="hidden" name="translation_user_approval_language" id="translation_user_approval_language">
                                            <input type="hidden" name="translation_user_approval_temp_symptom_id" id="translation_user_approval_temp_symptom_id">
                                            <button type="submit" id="translation_user_approval_modal_continue_btn" class="btn btn-primary translation-user-approval-modal-continue-btn">Continue</button>
                                            <button type="button" id="translation_user_approval_modal_delete_btn" class="btn btn-danger translation-user-approval-modal-delete-btn">Delete</button>
                                            <button type="button" class="btn btn-default translation-user-approval-modal-cancel-btn">Cancel</button>
                                        </div>
                                    <!-- </form> -->
                                </div>
                            </div>
                        </div>
                        <!-- Add translation user approval modal end -->
			        </div>
          			<!-- /.box-body -->
		    	</div>
			</div>
		</div>
	    <!-- /.row -->
  	</section>
  	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
include '../inc/footer.php';
?>
<script src="assets/js/common.js"></script>
<script>
    $(window).bind("load", function() {
        console.log('loaded');
        $("#loader").addClass("hidden");
    });

    $(document).on('click', '.comparison-history-header-btn', function(e){
        e.preventDefault();
        $(this).find('span').html(function(_, value) {
            return value == '<i class="fas fa-angle-up"></i>' ? '<i class="fas fa-angle-down"></i>' : '<i class="fas fa-angle-up"></i>'
        });
        var rowId = $(this).parent().parent().attr('id');
        $('.sub_'+rowId).slideToggle(100, function() {});
    });

    $(document).on('click', '.reactivate-quelle', function(){
        var $th = $(this);
        if($th.hasClass('processing'))
            return;
        $th.addClass('processing');
        var comparisonHistoryId = $(this).attr("data-comparison-history-id");
        var arzneiId = $(this).attr("data-arznei-id");
        var quelleId = $(this).attr("data-quelle-id");

        var con = confirm("Are you sure you want to reactivate this state?");
        if (con)
        {
            if(comparisonHistoryId != "" && arzneiId != "" && quelleId != ""){
                $('#reactivate_'+comparisonHistoryId).prop('disabled', true);
                $('#reactivate_'+comparisonHistoryId).html('<img src="assets/img/loader.gif" alt="Loader">');
                $.ajax({
                    type: 'POST',
                    url: 'reactivate-a-comparison-history.php',
                    data: {
                        comparison_history_id: comparisonHistoryId,
                        quelle_id: quelleId,
                        arznei_id: arzneiId
                    },
                    dataType: "json",
                    success: function( response ) {
                        console.log(response);
                        if(response.status == "success"){
                            // $('#reactivate_'+comparisonHistoryId).prop('disabled', false);
                            // $('#reactivate_'+comparisonHistoryId).html('Reactivate');
                            // $("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
                            // $("#globalMsgModal").modal('show');
                            //location.reload();
                            $th.removeClass('processing');
                            window.location.href = "<?php echo $baseUrl ?>materia-medica.php";
                            
                        }else{
                            $th.removeClass('processing');
                            $('#reactivate_'+comparisonHistoryId).prop('disabled', false);
                            $('#reactivate_'+comparisonHistoryId).html('Reactivate');
                            $("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
                            $("#globalMsgModal").modal('show');
                        }
                    }
                }).fail(function (response) {
                    $th.removeClass('processing');
                    $('#reactivate_'+comparisonHistoryId).prop('disabled', false);
                    $('#reactivate_'+comparisonHistoryId).html('Reactivate');
                    $("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try!</p>');
                    $("#globalMsgModal").modal('show');

                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });
            } else {
                $th.removeClass('processing');
                $('#reactivate_'+comparisonHistoryId).prop('disabled', false);
                $('#reactivate_'+comparisonHistoryId).html('Reactivate');
                $("#global_msg_container").html('<p class="text-center">Operation failed, some required data not found. Please reload and try!</p>');
                $("#globalMsgModal").modal('show');
            }
        }
        else
        {
            $th.removeClass('processing');
            return false;
        }
    });

    //arznei custom search starts
    $('#arznei_id').select2({
        // options 
        searchInputPlaceholder: 'Search Arznei...'
    });

    $('body').on( 'submit', '#arznei_search_medica', function(e) {
        var arznei_id = $("#arznei_id").val();
        console.log(arznei_id);
    });
    //arznei custom search ends
</script>