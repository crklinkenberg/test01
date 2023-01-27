<?php
	include '../config/route.php';
    include 'sub-section-config.php';
	include 'additions/chapter-structure.php';

    function chapterOptionValueEdit($string){
        $string = strtolower($string);
        $string = str_replace(" ","_",$string);
        return $string;
    }

    function chapterDataSelectOptios($dataArray){
        $chapterDataSelectOptions = array();
        $chapterDataSelectOptionsValue = array();
        $chapterDataSelectOptionsReturn = array();
        foreach($dataArray as $mainChapter => $innerChapter){
            $mainChapterString = chapterOptionValueEdit($mainChapter);
            array_push($chapterDataSelectOptions, $mainChapter);
            array_push($chapterDataSelectOptionsValue, $mainChapterString);
            foreach($innerChapter as $innerChapterName => $subChapters){
                $innerChapterNameString = chapterOptionValueEdit($innerChapterName);
                $parentsLinkOne = $mainChapter.'->'.$innerChapterName;
                array_push($chapterDataSelectOptions, $parentsLinkOne);
                array_push($chapterDataSelectOptionsValue, $innerChapterNameString);
                foreach($subChapters as $subChapterName){
                    $subChapterNameString = chapterOptionValueEdit($subChapterName);
                    $parentsLinkTwo = $mainChapter.'->'.$innerChapterName.'->'.$subChapterName;
                    array_push($chapterDataSelectOptions, $parentsLinkTwo);
                    array_push($chapterDataSelectOptionsValue, $subChapterNameString);
                }
            }
        }
        $chapterDataSelectOptionsReturn["chapter_names"] = $chapterDataSelectOptions;
        $chapterDataSelectOptionsReturn["chapter_values"] = $chapterDataSelectOptionsValue;
        return $chapterDataSelectOptionsReturn;
    }   

    // $x = chapterDataSelectOptios($chapters);
    // print_r($x);
    // exit();

    $completedTable = "";
    $totalCount = 0;
    $page = 1;
    $totalPage = 1;
    $comparisonOption = 1;
    $fetchedName = "";
    $singleConnectionsInitials = array();
	$quelleToAssign = (isset($_GET['quelle_to_assign']) AND $_GET['quelle_to_assign'] != "") ? $_GET['quelle_to_assign'] : 0;
	$page = (isset($_GET['page']) AND $_GET['page'] != "") ? $_GET['page'] : 1;
    $comparisonNameFetch = mysqli_query($db,"SELECT table_name FROM pre_comparison_master_data WHERE quelle_id = $quelleToAssign");
    while($comparisonNameFetchRow = mysqli_fetch_array($comparisonNameFetch)){
        $fetchedName = $comparisonNameFetchRow['table_name'];
        $completedTable = $fetchedName."_completed";
    }
	if($completedTable !=""){
        $count = mysqli_query($db, "SELECT id FROM $completedTable ORDER BY `id` ASC");
        if(mysqli_num_rows($count) > 0){
            $totalCount = mysqli_num_rows($count);
        }
        $totalPage = ceil($totalCount/1);
        $startFrom = ($page-1) * 1; 
    }

    // Storing comparison table data in sesssion Start
    $_SESSION['comparison_table_data'] = array();
    $tempData = array();
    $tempData['comparison_table'] = $fetchedName;
    $_SESSION['comparison_table_data'] = $tempData;
    // Storing comparison table data in sesssion End
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Chapter assignment Test</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!--jQuery UI -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<!-- Font Awesome -->
  	<link rel="stylesheet" href="plugins/font-awesome/css/fontawesome-all.min.css">
  	<!-- Select2 -->
  	<link rel="stylesheet" href="plugins/select2/dist/css/select2.min.css">
  	<!-- custom -->
  	<link rel="stylesheet" href="assets/css/custom-temp.css">
  	<!-- new comparison table style -->
  	<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
  	<style type="text/css">
  		.comparison-navigation-ul li {
		    display: inline-block;
		    margin-bottom: 10px;
		}
		.ui-dialog{
			width: auto !important;
			height: auto !important;
		}
		.ui-dialog-titlebar{
			background-color: #1E90FF !important;
		}
		#loaderCEOverlay{
			position: fixed;
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 10;
		}
        .chapter-head-style{
            text-align: center;
        }
        .arrowPage{
            padding-top: 15px;
            transform: scale(1.3,1);
        }
        #symptomNoSmallInput{
            width: 30px;
        }
        #symptomNoSmallInputSection{
            padding-left: 15px;
            padding-top: 5px;
        }
        .chapter-assign-head{
            padding-top: 20px;
        }
        .chapter-assign-head .panel{
            border-radius: 0px;
        }
        .chapterWeightRow{
            padding-left: 20px;
        }
        .chapter-button-group{
            text-align: center;
            padding-left: 70px;
        }
  	</style>
</head>
<body>
    <?php 
        if(isset($_GET['quelle_to_assign']) AND $_GET['quelle_to_assign'] != "0"){
            ?>
                <form action="" method="GET" id="assignForm" name="assignForm">
                    <input type="hidden" value="<?php echo $quelleToAssign;?>" id="quelle_to_assign" name="quelle_to_assign">
                    <input type="hidden" value="0" id="page_navigate" name="page">
                </form>
                <div class="container">
                    <h4><?php echo $quelleToAssign;?></h4><br>
                    <?php
                        if($completedTable !=""){
                            ?>
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        <h3 class="panel-title chapter-head-style">Assign Symptom to Chapter</h3>
                                    </div>
                                    <div class="container">
                            <?php
                                $symptomListResult = mysqli_query($db, "SELECT * FROM $completedTable ORDER BY `id` ASC LIMIT $startFrom, 1");
                                while($symptomListRow = mysqli_fetch_array($symptomListResult)){
                                if($symptomListRow['swap_ce'] !=0){
                                    $symptomString_de =  $symptomListRow['swap_value_ce_de'];
                                    $symptomString_en =  $symptomListRow['swap_value_ce_en'];
                                }else{
                                    if($symptomListRow['swap'] != 0){
                                        $symptomString_de =  $symptomListRow['swap_value_de'];
                                        $symptomString_en =  $symptomListRow['swap_value_en'];
                                    }else{
                                        if($symptomListRow['is_final_version_available'] != 0){
                                            $symptomString_de =  $symptomListRow['final_version_de'];
                                            $symptomString_en =  $symptomListRow['final_version_en'];
                                        }else{
                                            if($comparisonOption == 1){
                                                $symptomString_de =  $symptomListRow['searchable_text_de'];
                                                $symptomString_en =  $symptomListRow['searchable_text_en'];
                                            }else{
                                                $symptomString_de =  $symptomListRow['BeschreibungFull_de'];
                                                $symptomString_en =  $symptomListRow['BeschreibungFull_en'];
                                            }
                                        }
                                    }
                                }
                                $symptomId = $symptomListRow['symptom_id'];
                                $idToDisplay = "row".$symptomId;
                                array_push($singleConnectionsInitials,$symptomListRow['symptom_id']);
                                ?>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="panel-body">
                                                    <div class="<?php echo $idToDisplay;?>"><?php echo $symptomString_de; ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <i class="fa fa-3x fa-caret-left arrowPage" id="navigateLeftArrow" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;
                                                <i class="fa fa-3x fa-caret-right arrowPage" id="navigateRightArrow" aria-hidden="true"></i>
                                                <div id="symptomNoSmallInputSection">
                                                    <input type="text" value="<?php echo $page;?>" id="symptomNoSmallInput" name = "symptomNoSmallInput"> / <?php echo $totalPage;?> &nbsp;
                                                    <i class="fa fa-check-circle hidden inputPageSubmit" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chapter-assign-head">
                                            <div class="panel panel-success">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title chapter-head-style">Chapter Assignments</h3>
                                                </div>
                                            </div>
                                            <div class="container">
                                                    <div class="row chapterWeightRow">
                                                        <div class="col-md-4">
                                                            <select class="form-control">
                                                                <option>Weight</option>
                                                                <option value="1">Primary</option>
                                                                <option value="2">Primary (more)</option>
                                                                <option value="3">Secondary</option>
                                                                <option value="4">Tertiary</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <select class="form-control" name="chapter_data" id="chapter_data">
                                                                <option value="">Select</option>
                                                                <?php
                                                                    $chapterDataArray = chapterDataSelectOptios($chapters);
                                                                    $count =0;
                                                                    foreach($chapterDataArray["chapter_names"] as $chapterName){
                                                                        //echo '<option value="'.$chapterDataArray["chapter_values"][$count].'">'.$chapterName.'</option>';
                                                                        echo '<option value="1">'.$chapterName.'</option>';
                                                                        $count++;
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="chapter-button-group">
                                                                <button type="button" class="btn btn-primary">Save</button>
                                                                <button type="button" class="btn btn-primary">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }else{
                            "No record found.";
                        }
                    ?>
                </div>
            <?php
        }else{
            ?>
                <form action="" method="GET" id="assignForm">
                    <input type="hidden" value="<?php echo $quelleToAssign;?>" id="quelle_to_assign" name="quelle_to_assign">
                </form>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="form-group Text_form_group">
                                <label class="control-label">Select Remedy<span class="required">*</span></label>
                                <select class="form-control save-data" name="arznei_id" id="arznei_id" <?php if($is_opened_a_saved_comparison == 1){ ?> readonly <?php } ?>>
                                    <option value="">Select</option>
                                    <?php
                                        $finalArzneiArray = array();
                                        $arzneiArray = array();
                                        $finalResultSelection = mysqli_query($db,"SELECT arznei_id, quelle_id FROM pre_comparison_master_data WHERE comparison_save_status=2");
                                        while($finalResultSelectionRow = mysqli_fetch_array($finalResultSelection)){
                                            $finalArznei = $finalResultSelectionRow['arznei_id'];
                                            $finalQuelleId = $finalResultSelectionRow['quelle_id'];
                                            $quelleResult = mysqli_query($db,"SELECT quelle_id, is_materia_medica FROM quelle WHERE quelle_id = $finalQuelleId AND is_materia_medica = 1");
                                            while($quelleResultRow = mysqli_fetch_array($quelleResult)){
                                                $quelleIdToSend = $quelleResultRow['quelle_id'];
                                                $arzneiResult = mysqli_query($db,"SELECT arznei_id, titel FROM arznei WHERE arznei_id = $finalArznei");
                                                //array_push($finalArzneiArray, $arzneiResult);
                                                while($arzneiRow = mysqli_fetch_array($arzneiResult)){
                                                    $arzneiArray["arznei_id"] = $arzneiRow["arznei_id"];
                                                    $arzneiArray["titel"] = $arzneiRow["titel"];
                                                    array_push($finalArzneiArray, $arzneiArray);
                                                }
                                            }
                                        }
                                        $finalArzneiArray = array_unique($finalArzneiArray, SORT_REGULAR);
                                        foreach($finalArzneiArray as $arrayData){
                                            $selected = ($arrayData['arznei_id'] == $arzneiId) ? 'selected' : '';
                                            echo '<option '.$selected.' value="'.$arrayData['arznei_id'].'">'.$arrayData['titel'].'</option>';
                                        }
                                        
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br><hr><br>
                    <div class="row">
                        <div class="col">
                            <div class="form-group Text_form_group">
                                <label class="control-label">Source<span class="required">*</span></label>
                                <div id="quelle_final">
                                    <select id="quelle_options" class="form-control">
                                        <option class="quelle_option" >Select</option>
                                    </select>
                                </div>	
                            </div>
                        </div>
                    </div>
                </div>
            <?php
        }
    ?>
    
    <?php
        $singleConnectionsInitialsString = (!empty($singleConnectionsInitials)) ? implode(',', $singleConnectionsInitials) : "";
    ?>
	<script type="text/javascript" src="plugins/jquery/jquery/jquery-3.3.1.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.min.js"></script>
	<script type="text/javascript" src="plugins/tinymce/jquery.tinymce.config.js"></script>
	<script type="text/javascript" src="plugins/tinymce/tinymce.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<!-- Select2 -->
	<script src="plugins/select2/dist/js/select2.full.min.js"></script>
	<script src="assets/js/select2-custom-search-box-placeholder.js"></script>
	<script src="assets/js/common.js"></script>
	<script src="connection-display-raw.js"></script>
	<script type="text/javascript">
        //Initial display select box
		$('#arznei_id').select2({
			// options 
			searchInputPlaceholder: 'Search Remedy...'
		});

        //Select box for chapters
		$('#chapter_data').select2({
			// options 
			searchInputPlaceholder: 'Search Chapters...'
		});
        
        // Fetching Quelle/Sources of the arznei
        $('#arznei_id').on('select2:select', function (e) {
            if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
                var request = $.ajax({
                    url: "get-quelle.php",
                    type: "POST",
                    data: {arznei_id : e.params.data.id},
                    dataType: "json"
                });
                request.done(function(responseData) {
                    //console.log(responseData);
                    var resultData = null;
                    try {
                        resultData = JSON.parse(responseData); 
                    } catch (e) {
                        resultData = responseData;
                    }
                    $('#quelle_options .quelle_option').remove();
                    var optionString = "";
                    optionString = optionString+"<option class='quelle_option'>Select</option>";
                    $("#quelle_options").append(optionString);


                    $.each(resultData, function( key, value ) {
                        var comparison_name = value.comparison_name;
                        var quelle_id = value.quelle_id;
                        optionString = '<option value='+quelle_id+' class="quelle_option">'+comparison_name+'</option>';
                        $("#quelle_options").append(optionString);       
                    }) ;
                });

                request.fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            }
        });

        //Operation on selecting quelle
        $('#quelle_options').change(function(e){ 
            var value = $(this).val();
            $('#quelle_to_assign').val(value);
            
            //Form submission
            $("#assignForm").submit();
        });

        $(document).ready(function(){
            //Connected symptoms fetching
            var singleConnectionInitialCheck = [<?php echo $singleConnectionsInitialsString; ?>];
            if(singleConnectionInitialCheck.length > 0)
            {
                var symptomType = "initial";
                $.ajax({
                    async:false,
                    type: "POST",
                    url: "symptom-connection-operations.php",
                    data: {
                        singleConnectionInitialCheck:singleConnectionInitialCheck,
                        symptomType: symptomType
                    },
                    dataType: "JSON",
                    success: function(returnedData){
                        try {
                            resultData = JSON.parse(returnedData.result_data); 
                            latestIdArray = JSON.parse(returnedData.latestIdResult); 
                        } catch (e) {
                            resultData = returnedData.result_data;
                            latestIdArray = returnedData.latestIdResult;
                        }
                        for (var i=0; i<resultData.length; i++)
                        {
                            var source_type ="singleSourceInitial";
                            var comparing_symptom_id = resultData[i].comparing_symptom_id;
                            var initial_symptom_id = resultData[i].initial_symptom_id;
                            var matched_percentage = resultData[i].matched_percentage;
                            var comparing_quelle_id = resultData[i].comparing_quelle_id;
                            var initial_quelle_id = resultData[i].initial_quelle_id;
                            var highlighted_comparing_symptom_en = resultData[i].highlighted_comparing_symptom_en;
                            var highlighted_comparing_symptom_de = resultData[i].highlighted_comparing_symptom_de;
                            var highlighted_initial_symptom_en = resultData[i].highlighted_initial_symptom_en;
                            var highlighted_initial_symptom_de = resultData[i].highlighted_initial_symptom_de;
                            var comparison_language = resultData[i].comparison_language;
                            var comparing_quelle_code = resultData[i].comparing_quelle_code;
                            var initial_quelle_code = resultData[i].initial_quelle_code;
                            var comparing_year = resultData[i].comparing_year;
                            var comparing_symptom_de = resultData[i].comparing_symptom_de;
                            var comparing_symptom_en = resultData[i].comparing_symptom_en;
                            var comparing_quelle_original_language = resultData[i].comparing_quelle_original_language;
                            var initial_quelle_original_language = resultData[i].initial_quelle_original_language;
                            var initial_year = resultData[i].initial_year;
                            var initial_symptom_de = resultData[i].initial_symptom_de;
                            var initial_symptom_en = resultData[i].initial_symptom_en;
                            var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                            var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                            var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                            var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                            var connection_type = resultData[i].connection_type;
                            var is_earlier_connection = resultData[i].is_earlier_connection;
                            var free_flag = resultData[i].free_flag;
                            var connection_id = resultData[i].connection_id;
                            var comparing_symptom_de_raw = resultData[i].comparing_symptom_de_raw;
                            var comparing_symptom_en_raw = resultData[i].comparing_symptom_en_raw;
                            $.fn.connectionsDisplayRaw(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag, connection_id, comparing_symptom_de_raw, comparing_symptom_en_raw);

                            // // Translation Array
                            // var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                            // var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                            // if(comparison_language == "de"){
                            //     // if(translationArray[tarnKeyCom] == undefined)
                            //         translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            //     if(translationArray[tarnKeyIni] == undefined)
                            //         translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                            // }
                            // else{
                            //     // if(translationArray[tarnKeyCom] == undefined)
                            //         translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            //     if(translationArray[tarnKeyIni] == undefined)
                            //         translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                            // }

                        }

                    },
                    error: function(xhr, textStatus, error){
                        console.log(xhr.statusText);
                        console.log(textStatus);
                        console.log(error);
                    }
                });
            }

            //Navigartion arrows starts
            var page = <?php echo $page;?>;
            var totalPage = <?php echo $totalPage;?>;
            $("#navigateLeftArrow").on("click", function(){
                if(page == 1){
                    alert("Showing the first symptom.");
                    return false;
                }else{
                    page = page - 1;
                    $("#page_navigate").val(page);
                    //Form submission
                    $("#assignForm").submit();

                }
            });

            $("#navigateRightArrow").on("click", function(){
                if(page == totalPage){
                    alert("Showing the last symptom.");
                    return false;
                }else{
                    page = page + 1;
                    $("#page_navigate").val(page);
                    //Form submission
                    $("#assignForm").submit();

                }
            });
            //Navigartion arrows ends

            //Small input for page number
            $('input[name=symptomNoSmallInput]').on("change", function() {
                var val = $(this).val();
                var valCheck = /^\d+$/;
                if (valCheck.test(val)) {
                    if(val == 0 || val ==(totalPage + 1)){
                        alert("No symptoms to show.");
                        return false;
                    }
                    $(".inputPageSubmit").removeClass("hidden");
                    $("#page_navigate").val(val);
                    //Form submission
                    $("#assignForm").submit();
                }else{
                    alert("No symptoms to show.");
                    return false;
                }
            });
        });
       
	</script>
</body>
</html>