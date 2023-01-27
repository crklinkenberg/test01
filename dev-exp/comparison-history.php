<?php
	include '../lang/GermanWords.php';
	include '../config/route.php';
	include 'sub-section-config.php';
	include '../api/mainCall.php';
?>
<?php
    //new comparison button
	if(isset($_POST['new_comparison_btn']))
	{
		$_SESSION['comparison_table_data_for_history'] = array();
		header("Location: ".$baseUrl."history.php");
		die();
	}
	$historyPage = 1;
	$comId = (isset($_GET['comid']) AND $_GET['comid'] != "") ? $_GET['comid'] : "";
	if($comId != ""){
		$checkIfExist = mysqli_query($db,"SELECT * FROM pre_comparison_master_data  WHERE id = '".$comId."'");
		if(mysqli_num_rows($checkIfExist) != 0){
			$existingTableRow = mysqli_fetch_assoc($checkIfExist);
			$arzneiId = (isset($existingTableRow['arznei_id']) AND $existingTableRow['arznei_id'] != "") ? $existingTableRow['arznei_id'] : "";
			$initialSourceId = (isset($existingTableRow['initial_source']) AND $existingTableRow['initial_source'] != "") ? $existingTableRow['initial_source'] : "";
			$comparingSourceIds = (isset($existingTableRow['comparing_sources']) AND !empty($existingTableRow['comparing_sources'])) ? $existingTableRow['comparing_sources'] : array();
			if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
				$comparingSourceIds = explode(",", $comparingSourceIds);
			$comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";
			$similarityRate = (isset($existingTableRow['similarity_rate']) AND $existingTableRow['similarity_rate'] != "") ? $existingTableRow['similarity_rate'] : 20;
			$comparisonOption = (isset($existingTableRow['comparison_option']) AND $existingTableRow['comparison_option'] != "") ? $existingTableRow['comparison_option'] : 1;
			$comparisonLanguage = (isset($existingTableRow['comparison_language']) AND $existingTableRow['comparison_language'] != "") ? $existingTableRow['comparison_language'] : "";
			$per_page_initial_symptom_number = (isset($existingTableRow['per_page_initial_symptom_number']) AND $existingTableRow['per_page_initial_symptom_number'] != "") ? $existingTableRow['per_page_initial_symptom_number'] : 5;
			$per_page_initial_symptom_number = (isset($_SESSION['comparison_table_data']['per_page_initial_symptom_number']) AND $_SESSION['comparison_table_data']['per_page_initial_symptom_number'] != "") ? $_SESSION['comparison_table_data']['per_page_initial_symptom_number'] : $per_page_initial_symptom_number;
			$comparisonTable = (isset($existingTableRow['table_name']) AND $existingTableRow['table_name'] != "") ? $existingTableRow['table_name'] : "";
			// Comparison only initials table name
			$comparisonOnlyInitialTable = $comparisonTable."_initials";

			// Storing comparison table data in sesssion Start
			$_SESSION['comparison_table_data_for_history'] = array();
			$tempData = array();
			$tempData['arznei_id'] = $arzneiId;
			$tempData['initial_source'] = $initialSourceId;
			$tempData['comparing_sources'] = $comparingSourceIds;
			$tempData['similarity_rate'] = $similarityRate;
			$tempData['comparison_option'] = $comparisonOption;
			$tempData['comparison_language'] = $comparisonLanguage;
			$tempData['comparison_table'] = $comparisonTable;
			$tempData['comparison_only_initial_table'] = $comparisonOnlyInitialTable;
			$tempData['per_page_initial_symptom_number'] = $per_page_initial_symptom_number;
			$tempData['is_opened_a_saved_comparison'] = 1;

			$_SESSION['comparison_table_data_for_history'] = $tempData;
			// Storing comparison table data in sesssion End
		} else {
			$_SESSION['comparison_table_data_for_history'] = array();
			header("Location: ".$baseUrl."history.php");
			die();
		}
	} else {
		$_SESSION['comparison_table_data_for_history'] = array();
		header("Location: ".$baseUrl."history.php");
		die();
	}
	
	// Getting main comparison data array from session
	$comparisonTableDataArr = (isset($_SESSION['comparison_table_data_for_history']) AND !empty($_SESSION['comparison_table_data_for_history'])) ? $_SESSION['comparison_table_data_for_history'] : array(); 

	$is_opened_a_saved_comparison = (isset($comparisonTableDataArr['is_opened_a_saved_comparison']) AND !empty($comparisonTableDataArr['is_opened_a_saved_comparison'])) ? $comparisonTableDataArr['is_opened_a_saved_comparison'] : "";

	// Comparison table don't exist in DB then the session data and other required data empty. 
	$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : ""; 
	
	if($comparisonTable != ""){
		$checkIfComparisonTableExist = $db->prepare("SHOW TABLES LIKE '".$comparisonTable."'");
		$checkIfComparisonTableExist->execute();
		$checkIfComparisonTableExist->store_result();
		if($checkIfComparisonTableExist->num_rows == 0){
			$_SESSION['comparison_table_data_for_history'] = array();
			$comparisonTable = "";
			$comparisonTableDataArr = array();
		}
	}

	$savedComparisonComparingSourceIdsCommaSeparated = "";
	$comparisonTableStatus = "";
	$showProgressMsgForTable = "";
	$error_msg = array();

	$arzneiId = (isset($comparisonTableDataArr['arznei_id']) AND $comparisonTableDataArr['arznei_id'] != "") ? $comparisonTableDataArr['arznei_id'] : "";
	$initialSourceId = (isset($comparisonTableDataArr['initial_source']) AND $comparisonTableDataArr['initial_source'] != "") ? $comparisonTableDataArr['initial_source'] : "";
	$comparingSourceIds = (isset($comparisonTableDataArr['comparing_sources']) AND !empty($comparisonTableDataArr['comparing_sources'])) ? $comparisonTableDataArr['comparing_sources'] : array();
	if(!empty($comparingSourceIds) AND !is_array($comparingSourceIds))
		$comparingSourceIds = explode(",", $comparingSourceIds);
	$comparingSourcesInsertString = (!empty($comparingSourceIds)) ? implode(",", $comparingSourceIds) : "";

	// Fetching all involved source Ids(for combined/saved sources)
	$allInvolvedSourcesIdsOfComparingSourceArr = array();
	if(!empty($comparingSourceIds)){
		$returnedIds = getAllComparedSourceIdsFromNewTable($comparingSourceIds);
		if(!empty($returnedIds)){
			foreach ($returnedIds as $IdVal) {
				if(!in_array($IdVal, $allInvolvedSourcesIdsOfComparingSourceArr))
					array_push($allInvolvedSourcesIdsOfComparingSourceArr, $IdVal);
			}
		}	
	}
	$allInvolvedSourcesIdsOfComparingSource = (!empty($allInvolvedSourcesIdsOfComparingSourceArr)) ? implode(',', $allInvolvedSourcesIdsOfComparingSourceArr) : "";


	$similarityRate = (isset($comparisonTableDataArr['similarity_rate']) AND $comparisonTableDataArr['similarity_rate'] != "") ? $comparisonTableDataArr['similarity_rate'] : 20;
	$comparisonOption = (isset($comparisonTableDataArr['comparison_option']) AND $comparisonTableDataArr['comparison_option'] != "") ? $comparisonTableDataArr['comparison_option'] : 1;
	$comparisonLanguage = (isset($comparisonTableDataArr['comparison_language']) AND $comparisonTableDataArr['comparison_language'] != "") ? $comparisonTableDataArr['comparison_language'] : "";
	$comparisonTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table'] : "";
	$perPageInitialSymptomNumber = (isset($comparisonTableDataArr['per_page_initial_symptom_number']) AND $comparisonTableDataArr['per_page_initial_symptom_number'] != "") ? $comparisonTableDataArr['per_page_initial_symptom_number'] : 5;
	$comparisonSavedDataTable = (isset($comparisonTableDataArr['comparison_table']) AND $comparisonTableDataArr['comparison_table'] != "") ? $comparisonTableDataArr['comparison_table']."_connections" : "";
	// checking if table insertion is complete
	if($comparisonTable != ""){
		$checkIfExist = $db->prepare("SELECT id, table_name, status FROM pre_comparison_master_data  WHERE table_name = ?");
		$checkIfExist->bind_param("s",$comparisonTable);
		$checkIfExist->execute();
		$checkIfExist->store_result();
		$checkIfExist->bind_result($masterDataID,$masterDataName, $masterDataStatus);
		$checkIfExist->fetch();
		if($checkIfExist->num_rows != 0){
			$comparisonTableStatus = $masterDataStatus;
			if($comparisonTableStatus == "processing")
				$showProgressMsgForTable = $masterDataName;
		}

		//Array Declaration for saved connection loading
		$singleConnectionsInitials = array();
		$singleConnectionsComparative = array();
		$combinedConnectionsInitials = array();
		$combinedConnectionsComparative = array();

		//comments, footnote and translations on page load
		$dataOnLoad = mysqli_query($db, "SELECT `id` as symptom_id,`Kommentar`,`Fussnote`,`searchable_text_de`,`searchable_text_en`,`BeschreibungFull_de`,`BeschreibungFull_en` FROM quelle_import_test WHERE `arznei_id`=$arzneiId");
		if(mysqli_num_rows($dataOnLoad) > 0){
			while($onLoadFetchedData = mysqli_fetch_array($dataOnLoad)){
				$commentsString = "";
				$footnoteString = "";
				$translationDataString = "";
				$symptomString_de = "";
				$symptomString_en = "";

				if($onLoadFetchedData['Kommentar'] != "")
					$commentsString .= $onLoadFetchedData['symptom_id']; 
				if($commentsString != "")
						$commentsDataArray[] = "'".$commentsString."'"; 
				if($onLoadFetchedData['Fussnote'] != "")
					$footnoteString .= $onLoadFetchedData['symptom_id']; 
				if($footnoteString != "")
						$footnoteDataArray[] = "'".$footnoteString."'"; 

		    	if($comparisonOption == 1){
					$symptomString_de =  ($onLoadFetchedData['searchable_text_de'] != "") ? $onLoadFetchedData['searchable_text_de'] : "";
					$symptomString_en =  ($onLoadFetchedData['searchable_text_en'] != "") ? $onLoadFetchedData['searchable_text_en'] : "";
				}else{
					$symptomString_de =  ($onLoadFetchedData['BeschreibungFull_de'] != "") ? $onLoadFetchedData['BeschreibungFull_de'] : "";
					$symptomString_en =  ($onLoadFetchedData['BeschreibungFull_en'] != "") ? $onLoadFetchedData['BeschreibungFull_en'] : "";
				}
				if($comparisonLanguage=="en")
				{
					if($symptomString_de != "")
						$translationDataString .= $onLoadFetchedData['symptom_id'];
				}
				else
				{
					if($symptomString_en != "")
						$translationDataString .= $onLoadFetchedData['symptom_id'];
				}
				
				if($translationDataString != "")
					$translationDataArray[] = "'".$translationDataString."'";

			}
		}
	}
	
	$commentsOnLoad = (!empty($commentsDataArray)) ? implode(',', $commentsDataArray) : "";
	$footnoteOnLoad = (!empty($footnoteDataArray)) ? implode(',', $footnoteDataArray) : "";
	$translations = (!empty($translationDataArray)) ? implode(',', $translationDataArray) : "";
	
	$savedSwapArray = array();
	$savedConnectionsComparativeIdsArray = array();
	$savedSortedIdsArray = array();
	$pastedIdInfoArray = array();
	$dataPastedId = array();
	$comparisonDataInfo = array();
	$savedPasteEditIdArray = array();
	$savedConnectIdArray = array();
	$savedNonSecureIdArray = array();
	$savedSwappedIdArray = array();
	if($comparisonSavedDataTable != ""){
		
		//Fetching Connection Arrays
		$saveTableCheck = mysqli_query($db,"SHOW TABLES LIKE '".$comparisonSavedDataTable."'");
		if(mysqli_num_rows($saveTableCheck) > 0){
			$savedConnectionResult = mysqli_query($db, "SELECT `comparing_symptom_id`,`initial_symptom_id`,`connection_type`,`ns_connect`,`ns_paste`,`is_earlier_connection` FROM $comparisonSavedDataTable");
			if(mysqli_num_rows($savedConnectionResult) > 0){
				$count=0;
				while($savedData = mysqli_fetch_array($savedConnectionResult)){
					//PasteEdit Symptoms are inserted separatly.
					if($savedData['connection_type']=='PE')
						array_push($savedPasteEditIdArray,$savedData['comparing_symptom_id']);

					//paste and paste edit ids are taken in a separate array for sorting 
					if($savedData['connection_type']=='paste' OR $savedData['connection_type']=='PE'){
						if($savedData['is_earlier_connection']=='0'){
							array_push($savedConnectionsComparativeIdsArray,$savedData['comparing_symptom_id']);
						}else{
							array_push($savedConnectionsComparativeIdsArray,$savedData['initial_symptom_id']);
						}
						//array_push($savedConnectionsComparativeIdsArray,$savedData['comparing_symptom_id']);

						$pastedIdInfoArray['initial_symptom_id'] = $savedData['initial_symptom_id'];
						$pastedIdInfoArray['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
						$pastedIdInfoArray['is_earlier_connection'] = $savedData['is_earlier_connection'];
						array_push($dataPastedId,$pastedIdInfoArray);
					}

					//Connect and connect edit Symptoms are inserted separatly.
					if($savedData['connection_type']=='CE' || $savedData['connection_type']=='connect'){
						$savedConnectIdArray[$count]['initial_symptom_id'] = $savedData['initial_symptom_id'];
						$savedConnectIdArray[$count]['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
						$savedConnectIdArray[$count]['connection_type'] = $savedData['connection_type'];
						$count++;
					}

					//Non secure connect and paste are inserted separatly.
					if($savedData['ns_connect']=='1' || $savedData['ns_paste']=='1')
						array_push($savedNonSecureIdArray,$savedData['comparing_symptom_id']);

					//Swap connections are inserted separatly.
					if($savedData['connection_type']=='swap' || $savedData['connection_type']=='swapCE'){
						$savedSwappedIdArray[$count]['initial_symptom_id'] = $savedData['initial_symptom_id'];
						$savedSwappedIdArray[$count]['comparing_symptom_id'] = $savedData['comparing_symptom_id'];
						$savedSwappedIdArray[$count]['connection_type'] = $savedData['connection_type'];
						$count++;
					}		
				}
				$savedPasteEditIdArray = (isset($savedPasteEditIdArray) AND !empty($savedPasteEditIdArray)) ? array_unique($savedPasteEditIdArray) : "";
				$savedNonSecureIdArray = (isset($savedNonSecureIdArray) AND !empty($savedNonSecureIdArray)) ? array_unique($savedNonSecureIdArray) : "";
			}
		}
	}

	//Pagination 
	$page = (isset($_GET['page']) AND $_GET['page'] != "") ? $_GET['page'] : 1;//Pages
	$perPageInitialSymptomNumber = (isset($_GET['per_page_initial_symptom_number']) AND $_GET['per_page_initial_symptom_number'] != "") ? $_GET['per_page_initial_symptom_number'] : $perPageInitialSymptomNumber;//How many Initial Ids per page

	if($comparisonTable != ""){
		if($db->query("DESCRIBE $comparisonTable"))
		{
			$count = mysqli_query($db, "SELECT `symptom_id` FROM $comparisonTable WHERE is_initial_symptom = '1' AND connection = '0'");
			if(mysqli_num_rows($count) > 0){
				$totalCount = mysqli_num_rows($count);
			}
			$totalPage = ceil($totalCount/$perPageInitialSymptomNumber);
			$startFrom = ($page-1) * $perPageInitialSymptomNumber; 
		}
	}
	//restricting initial symptoms per page to "1" when matched percentage is "0"
	if($similarityRate == 0)
		$perPageInitialSymptomNumber = 1;
?>
<?php
	include '../inc/header.php';
	include '../inc/sidebar.php';
?>
<!-- custom -->
<link rel="stylesheet" href="assets/css/custom-temp.css">
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
	    	<li class="active">Comparison History</li>
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
                        <div id="comparison_table_overlay" class="hidden">
                            <div class="overlayBody">
                                <p>Our record shows that you have not compared these two sources before.</p>
                                <p>Please wait while we process the comparison.</p>
                                <img width="25px" src="../assets/img/loader.gif" alt="Loading...">
                            </div>
                        </div>
                        <div id="comparison_loader" class="hidden">
                            <div class="overlayLoaderBody">
                                <p>Please wait. Comparison is loading.. <img src="../assets/img/loader.gif" alt="Loading..."></p>
                            </div>
                        </div>
                        <div id="common_small_loader" class="hidden">
                            <div class="overlayLoaderBody">
                                <p>Please wait. Data is loading.. <img src="../assets/img/loader.gif" alt="Loading..."></p>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <!-- Search and comparison tab html -->
                                <?php include 'search-and-comparison-tab-html.php'; ?>
                                <!-- Search and comparison tab html -->
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label class="control-label" style="font-size: 12px">Number of initial symptom/page: <span class="required">*</span></label>
                                        <select class="form-control" name="per_page_initial_symptom_number" id="per_page_initial_symptom_number">
                                            <?php
                                                $j=1; 
                                                while($j<=50) { ?>
                                                <option <?php if($perPageInitialSymptomNumber == $j) { echo 'selected'; } ?> value="<?php echo $j; ?>"><?php echo $j; ?></option>
                                            <?php
                                                    $j =$j+1;
                                                    if($j>2){
                                                        $j = $j+2;
                                                    }
                                                    if($j>5){
                                                        $j= $j+2;
                                                    }
                                                } ?>
                                        </select>
                                    </div>	
                                </div>
                                <div class="col-sm-10">
                                    <ul class="show-all-checkbox-container pull-right">
                                        <li>
                                            <label class="checkbox-show-all">
                                            <input class="all-translation" name="all_translation" id="all_translation" type="checkbox" value="1">  Show all translation</label>
                                        </li>
                                        <li>
                                            <label class="checkbox-show-all">
                                            <input class="all-connections" name="all_connections" id="all-connections" type="checkbox" value="1">  Show all connections</label>
                                        </li>
                                    </ul>							
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 sticky-head">
                                    <div class="symptom-row heading" id="A">
                                        <div class="source-code heading text-center">Source</div>
                                        <div class="symptom heading text-center">Symptom</div>
                                        <div class="percentage heading text-center">Match (%)</div>
                                        <div class="info heading text-center">INFO & LINKAGE</div>
                                        <div class="command heading text-center">Command</div>
                                    </div>
                                </div>
                                <div id="comparison_result_container" class="col-sm-12">
                                    <input type="hidden" name="comparison_table" id="comparison_table" value="<?php echo $comparisonTable; ?>">
                                    <input type="hidden" name="show_progress_msg_for_table" id="show_progress_msg_for_table" value="<?php echo $showProgressMsgForTable; ?>">
                                    <?php
                                        $totalSymptoms = 0;
                                        $translationSymptomsArray = array();
                                        if($comparisonTable != "" AND $comparisonTableStatus != "processing")
                                        {
                                            $matchedSymptomIds = array();
                                            $cutOff = $similarityRate;
                                            $runningInitialSymptomId = "";
                                            $runningInitialSymptomDe = "";
                                            $runningInitialSymptomEn = "";
                                            $runningInitialSymptom = "";

                                            //Pagination
                                            $savedResult = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE is_initial_symptom = '1' AND connection = '0' LIMIT $startFrom, $perPageInitialSymptomNumber");
                                            if(mysqli_num_rows($savedResult) > 0){
                                                while($savedData = mysqli_fetch_array($savedResult)){
                                                    $value = $savedData['symptom_id'];

                                                    //Sending the id for searching saved connections
                                                    if($savedData['connection']=='0'){
                                                        if($savedData['quelle_type_id']==1 || $savedData['quelle_type_id']==2){
                                                            array_push($singleConnectionsInitials,$savedData['symptom_id']);
                                                        }
                                                        else{
                                                            array_push($combinedConnectionsInitials,$savedData['symptom_id']);
                                                        }
                                                    }
                                                        

                                                    // Selecting symptom string depending on comparison option that user selected
                                                    $symptomString_de = "";
                                                    $symptomString_en = "";
                            
                                                    // if($savedData['is_final_version_available'] != 0){
                                                    // 	$symptomString_de =  $savedData['final_version_de'];
                                                    // 	$symptomString_en =  $savedData['final_version_en'];
                                                    // } else {
                                                    // 	if($comparisonOption == 1){
                                                    // 		$symptomString_de =  $savedData['searchable_text_de'];
                                                    // 		$symptomString_en =  $savedData['searchable_text_en'];
                                                    // 	}else{
                                                    // 		$symptomString_de =  $savedData['BeschreibungFull_de'];
                                                    // 		$symptomString_en =  $savedData['BeschreibungFull_en'];
                                                    // 	}
                                                    // }

                                                    if($savedData['swap_ce'] !=0){
                                                        $symptomString_de =  $savedData['swap_value_ce_de'];
                                                        $symptomString_en =  $savedData['swap_value_ce_en'];
                                                    }else{
                                                        if($savedData['swap'] != 0){
                                                            $symptomString_de =  $savedData['swap_value_de'];
                                                            $symptomString_en =  $savedData['swap_value_en'];
                                                        }else{
                                                            if($savedData['is_final_version_available'] != 0){
                                                                $symptomString_de =  $savedData['final_version_de'];
                                                                $symptomString_en =  $savedData['final_version_en'];
                                                            }else{
                                                                if($comparisonOption == 1){
                                                                    $symptomString_de =  $savedData['searchable_text_de'];
                                                                    $symptomString_en =  $savedData['searchable_text_en'];
                                                                }else{
                                                                    $symptomString_de =  $savedData['BeschreibungFull_de'];
                                                                    $symptomString_en =  $savedData['BeschreibungFull_en'];
                                                                }
                                                            }
                                                        }
                                                    }
                                                        

                                                    if($symptomString_de != ""){
                                                        // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                        // [1st parameter] $symptom symptom string
                                                        // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                        // [3rd parameter] $arzneiId arzneiId 
                                                        // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                        // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                        // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                        // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                        $symptomString_de = convertTheSymptom($symptomString_de, $savedData['quelle_id'], $savedData['arznei_id'], 0, 0, $savedData['symptom_id']);
                                                    }
                                                    if($symptomString_en != ""){
                                                        // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                        // [1st parameter] $symptom symptom string
                                                        // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                        // [3rd parameter] $arzneiId arzneiId 
                                                        // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                        // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                        // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                        // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                        $symptomString_en = convertTheSymptom($symptomString_en, $savedData['quelle_id'], $savedData['arznei_id'], 0, 0, $savedData['symptom_id']);
                                                    }

                                                    // Displayable symptom string without highlighting
                                                    $symptomString_without_highlight_en = $symptomString_en;
                                                    $symptomString_without_highlight_de = $symptomString_de;

                                                    // Preparing Symptom string with available language divs
                                                    $displayingSymptomString = "";
                                                    if($comparisonLanguage == "en"){
                                                        $displayingSymptomString = $symptomString_en;
                                                        $translationSymptomsArray['row'.$savedData['symptom_id'].'_translated_symptom'] = ($symptomString_de != "") ? $symptomString_de : 'Translation is not available';
                                                    }
                                                    else
                                                    {
                                                        $displayingSymptomString = $symptomString_de;
                                                        $translationSymptomsArray['row'.$savedData['symptom_id'].'_translated_symptom'] = ($symptomString_en != "") ? $symptomString_en : 'Translation is not available';
                                                    }

                                                    if($savedData['quelle_type_id']==1 || $savedData['quelle_type_id']==2)
                                                    {
                                                        if($savedData['is_initial_symptom'] == '1'){
                                                            // Initial symptom
                                                            $runningInitialSymptomDe = $symptomString_without_highlight_de;
                                                            $runningInitialSymptomEn = $symptomString_without_highlight_en;
                                                            $runningInitialSymptomId = $savedData['symptom_id'];
                                                            $uniqueId = "row".$savedData['symptom_id'];
                                                            // storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
                                                            $runningInitialSymptom = $displayingSymptomString;
                                                            ?>
                                                            <div class="<?php echo $uniqueId; ?> symptom-row initial" id="row<?php echo $savedData['symptom_id']; ?>" data-year="<?php echo $savedData['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-source-original-language="<?php echo $savedData['initial_source_original_language']; ?>"data-quell-id="<?php echo $savedData['quelle_id']; ?>" data-quelle-type = "<?php echo $savedData['quelle_type_id']; ?>">
                                                                <div class="source-code"><?php echo $savedData['quelle_code']; ?></div>
                                                                <div class="symptom"><?php echo $displayingSymptomString; ?></div>
                                                                <div class="percentage"></div>
                                                                <div class="info">
                                                                    <ul class="info-linkage-group">			
                                                                        <li>				
                                                                            <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                        </li>			
                                                                        <li>				
                                                                            <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                        </li>			
                                                                        <li>				
                                                                            <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                        </li>			
                                                                        <li>				
                                                                            <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                        </li>			
                                                                        <li>				
                                                                            <a class="symptom-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
                                                                        </li>	
                                                                        <li>				
                                                                            <a class="symptom-search-btn" title="Search" href="javascript:void(0)"><i class="fas fa-search"></i></a>			
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="command"><ul class="command-group"></ul></div>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if($savedData['connection']==0)
                                                        {
                                                            if($savedData['is_initial_symptom'] == '1'){
                                                                // Initial symptom
                                                                $runningInitialSymptomDe = $symptomString_without_highlight_de;
                                                                $runningInitialSymptomEn = $symptomString_without_highlight_en;
                                                                $runningInitialSymptomId = $savedData['symptom_id'];
                                                                $uniqueId = "row".$savedData['symptom_id'];
                                                                // storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
                                                                $runningInitialSymptom = $displayingSymptomString;
                                                                ?>
                                                                <div class="<?php echo $uniqueId; ?> symptom-row initial" id="row<?php echo $savedData['symptom_id']; ?>" data-year="<?php echo $savedData['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-source-original-language="<?php echo $savedData['initial_source_original_language']; ?>"data-quell-id="<?php echo $savedData['quelle_id']; ?>" data-quelle-type = "<?php echo $savedData['quelle_type_id']; ?>">
                                                                    <div class="source-code"><?php echo $savedData['quelle_code']; ?></div>
                                                                    <div class="symptom"><?php echo $displayingSymptomString; ?></div>
                                                                    <div class="percentage"></div>
                                                                    <div class="info">
                                                                        <ul class="info-linkage-group">			
                                                                            <li>				
                                                                                <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
                                                                            </li>	
                                                                            <li>				
                                                                                <a class="symptom-search-btn" title="Search" href="javascript:void(0)"><i class="fas fa-search"></i></a>			
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="command"><ul class="command-group"></ul></div>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                        

                                                    // if($savedData['quelle_type_id']==1 || $savedData['quelle_type_id']==2)
                                                    // {
                                                    // 	$result = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE initial_symptom_id = $value AND `matched_percentage`>=$cutOff");
                                                    // }
                                                    // else
                                                    // {
                                                    // 	$result = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE initial_symptom_id = $value AND `matched_percentage`>=$cutOff AND `connection`='0'");
                                                    // }
                                                    $result = mysqli_query($db, "SELECT * FROM $comparisonTable WHERE initial_symptom_id = $value AND `matched_percentage`>=$cutOff AND `connection`='0' ORDER BY `matched_percentage` DESC");
                                                    if(mysqli_num_rows($result) > 0){
                                                        $output = array();
                                                        while($symRow = mysqli_fetch_array($result)){
                                                            $totalSymptoms++;

                                                            //Sending the id for searching saved connections
                                                            if($symRow['connection']=='0'){
                                                                if($symRow['quelle_type_id']==1 || $symRow['quelle_type_id']==2){
                                                                    array_push($singleConnectionsComparative,$symRow['symptom_id']);
                                                                }
                                                                else{
                                                                    array_push($combinedConnectionsComparative,$symRow['symptom_id']);
                                                                }
                                                            }
                                                                

                                                            // Selecting symptom string depending on comparison option that user selected
                                                            $symptomString_de = "";
                                                            $symptomString_en = "";
                                                    
                                                            if($symRow['swap_ce'] !=0){
                                                                $symptomString_de =  $symRow['swap_value_ce_de'];
                                                                $symptomString_en =  $symRow['swap_value_ce_en'];
                                                            }else{
                                                                if($symRow['swap'] != 0){
                                                                    $symptomString_de =  $symRow['swap_value_de'];
                                                                    $symptomString_en =  $symRow['swap_value_en'];
                                                                }else{
                                                                    if($symRow['is_final_version_available'] != 0){
                                                                        $symptomString_de =  $symRow['final_version_de'];
                                                                        $symptomString_en =  $symRow['final_version_en'];
                                                                    }else{
                                                                        if($comparisonOption == 1){
                                                                            $symptomString_de =  $symRow['searchable_text_de'];
                                                                            $symptomString_en =  $symRow['searchable_text_en'];
                                                                        }else{
                                                                            $symptomString_de =  $symRow['BeschreibungFull_de'];
                                                                            $symptomString_en =  $symRow['BeschreibungFull_en'];
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                                

                                                            if($symptomString_de != ""){
                                                                // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                                // [1st parameter] $symptom symptom string
                                                                // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                                // [3rd parameter] $arzneiId arzneiId 
                                                                // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                                // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                                // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                                // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                                $symptomString_de = convertTheSymptom($symptomString_de, $symRow['quelle_id'], $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
                                                            }
                                                            if($symptomString_en != ""){
                                                                // Converting the symptoms to it's applicable format according to the settings to present it in front of the user
                                                                // [1st parameter] $symptom symptom string
                                                                // [2nd parameter] $originalQuelleId the quelle_id of the symptom, that the particular symptom originaly belongs. 
                                                                // [3rd parameter] $arzneiId arzneiId 
                                                                // [4th parameter] $isFinalVersionAvailable values (0 = No, 1 = Connect edit, 2 = Paste edit)
                                                                // [5th parameter] $includeGrade values (0 = Gragde number will not include, 1 = Will include Grade number)
                                                                // [6th parameter] $symptomId the symptom_id of the symptombelong
                                                                // [7th parameter(only if available)] $originalSymptomId the symptom_id of the symptom where he originally belongs.(where he fist created)
                                                                $symptomString_en = convertTheSymptom($symptomString_en, $symRow['quelle_id'], $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
                                                            }

                                                            // Displayable symptom string without highlighting
                                                            $symptomString_without_highlight_en = $symptomString_en;
                                                            $symptomString_without_highlight_de = $symptomString_de;

                                                            // Preparing Symptom string with available language divs
                                                            $displayingSymptomString = "";
                                                            if($comparisonLanguage == "en"){
                                                                $displayingSymptomString = $symptomString_en;
                                                                $translationSymptomsArray['row'.$runningInitialSymptomId."_".$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_de != "") ? $symptomString_de : 'Translation is not available';
                                                            }
                                                            else
                                                            {
                                                                $displayingSymptomString = $symptomString_de;
                                                                $translationSymptomsArray['row'.$runningInitialSymptomId."_".$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_en != "") ? $symptomString_en : 'Translation is not available';
                                                            }

                                                            {
                                                                // Comparing symptom
                                                                array_push($matchedSymptomIds, $symRow['symptom_id']);
                                                                $uniqueId = "row".$runningInitialSymptomId."_".$symRow['symptom_id'];
                                                                // For heighlighting match words
                                                                $compareResult = newComareSymptom($runningInitialSymptom, $displayingSymptomString);
                                                                $highlightedComparingSymptom = (isset($compareResult['comparing_source_symptom_highlighted']) AND $compareResult['comparing_source_symptom_highlighted'] != "") ? $compareResult['comparing_source_symptom_highlighted'] : "";
                                                                // updating $symptomString_en and $symptomString_de data with the heighlighted comparing symptom
                                                                if($comparisonLanguage == "en")
                                                                    $symptomString_en = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $symptomString_en;
                                                                else
                                                                    $symptomString_de = ($highlightedComparingSymptom != "") ? $highlightedComparingSymptom : $symptomString_de;
                                                                ?>
                                                                <div class="<?php echo $uniqueId; ?> symptom-row comparing" id="row<?php echo $runningInitialSymptomId."_".$symRow['symptom_id']; ?>" data-year="<?php echo $symRow['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="<?php echo base64_encode($symptomString_without_highlight_de); ?>" data-comparing-symptom-en="<?php echo base64_encode($symptomString_without_highlight_en); ?>" data-source-original-language="<?php echo $symRow['comparing_source_original_language']; ?>" data-quell-id="<?php echo $symRow['quelle_id']; ?>" data-quelle-type = "<?php echo $symRow['quelle_type_id']; ?>">
                                                                    <div class="source-code"><?php echo $symRow['quelle_code']; ?></div>
                                                                    <div class="symptom"><?php echo $highlightedComparingSymptom; ?></div>
                                                                    <div class="percentage"><?php echo $symRow['matched_percentage']; ?>%</div>
                                                                    <div class="info">
                                                                        <ul class="info-linkage-group">			
                                                                            <li>				
                                                                                <a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a>
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="command">
                                                                        <ul class="command-group">						
                                                                            <li>				
                                                                                <a class="symptom-connect-btn connect" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a>			
                                                                            </li>	
                                                                            <li>				
                                                                                <a class="symptom-connect-edit-btn" href="javascript:void(0)" title="Connect edit">CE</a>
                                                                            </li>						
                                                                            <li>				
                                                                                <a class="symptom-paste-btn" href="javascript:void(0)" title="Paste">P</a>			
                                                                            </li>			
                                                                            <li>				
                                                                                <a class="symptom-paste-edit-btn" href="javascript:void(0)" title="Paste edit">PE</a>			
                                                                            </li>			
                                                                            <!-- <li>				
                                                                                <a class="btn symptom-swap-connect-btn" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a>
                                                                            </li> -->		
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            }

                                                        }
                                                    }
                                                }
                                            }

                                            if($page==$totalPage)
                                            {
                                                ?>
                                                <div style="text-align: center; background-color: #F1FB3A;"><a href="<?php echo $baseUrl?>unmatched-symptoms.php?matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>" class="btn"><h4>Show unmatched symptoms</h4></a></div>							
                                                <?php
                                            }

                                            // <!-- Pagination -->
                                            
                                            ?>
                                            <div class="text-center col-sm-12">
                                                <nav aria-label="Page navigation example">
                                                    <ul class="pagination">
                                                    <?php
                                                        //Advance Pagination
                                                        $pageDisplayToLeft = 3;
                                                        $pageDisplayToRight = 7;
                                                        $currentPage = $page;
                                                        $fixedInitialPage =1 ;
                                                        
                                                        if(($currentPage == $totalPage) && ($currentPage == 1))
                                                        {
                                                            echo '<li class="page-item"><a href="javascript:void(0)"></a></li>';
                                                        }
                                                        else
                                                        {
                                                            if($page>4)
                                                            {
                                                                ?>
                                                                    <li class="page-item">
                                                                    <a class="page-link  text-primary" href="comparison-history.php?page=<?php echo $fixedInitialPage;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&comid=<?php echo $comId; ?>">1</a>
                                                                    </li>
                                                                <?php
                                                            }

                                                            //Left Page
                                                            if(($currentPage - $pageDisplayToLeft) > 1) 
                                                            {
                                                                echo ' <li class="page-item"><a href="javascript:void(0)">...</a></li> ';
                                                            }
                                                            $pageDisplay = max(1, $currentPage - $pageDisplayToLeft);
                                                            $pageVarLeft0=$page;
                                                            $pageVarLeft = 0;
                                                            $decrementLeft = $perPageInitialSymptomNumber*($page-1);
                                                            
                                        
                                                            while($pageDisplay < $currentPage) 
                                                            {

                                                                $pageVarLeft = $pageDisplay* $perPageInitialSymptomNumber - $perPageInitialSymptomNumber;
                                                                ?>
                                                                    <li class="page-item"><a class="page-link  text-dark " href="comparison-history.php?page=<?php echo $pageDisplay;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $pageVarLeft;?>&comid=<?php echo $comId; ?>"><?php echo $pageDisplay?></a></li>
                                                                <?php
                                                                $pageDisplay++;
                                                                $decrementLeft = $decrementLeft - $perPageInitialSymptomNumber;
                                                                $pageVarLeft0++;
                                                            }
                                                            ?>
                                                            <?php 
                                                                //Current Page
                                                                $currentPageVar=$perPageInitialSymptomNumber*$page - $perPageInitialSymptomNumber;
                                                                //echo $currentPageVar."<br>".$currentPage;
                                                                if($currentPage == $page)
                                                                {
                                                                    $pageClassStyle = "active";
                                                                }

                                                            ?>
                                                                <li class="page-item <?php echo $pageClassStyle?>"><a class="page-link  text-light bg-danger " href="comparison-history.php?page=<?php echo $page;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $currentPageVar;?>&comid=<?php echo $comId; ?>"><?php echo $currentPage?></a></li>
                                                            <?php
                                                                //Right Page

                                                                $pageDisplay = min($totalPage, $currentPage + 1);
                                                                $pageVarRight0 = $page+1;
                                                            while($pageDisplay < min($currentPage + $pageDisplayToRight, $totalPage)) 
                                                            {
                                                                $pageVarRight = $pageVarRight0*$perPageInitialSymptomNumber - $perPageInitialSymptomNumber;   
                                                                ?>
                                                                    <li class="page-item"><a class="page-link  text-dark " href="comparison-history.php?page=<?php echo $pageDisplay;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $pageVarRight;?>&comid=<?php echo $comId; ?>"><?php echo $pageDisplay?></a></li>
                                                                <?php
                                                                $pageDisplay++;
                                                                $pageVarRight0++;

                                                            }
                                                            if(($currentPage + $pageDisplayToRight) < $totalPage) 
                                                            {
                                                                echo '<li class="page-item"><a href="javascript:void(0)">...</a></li> ';
                                                            }

                                                            if($currentPage<$totalPage)
                                                            {
                                                                $lastPage = $totalPage*$perPageInitialSymptomNumber - $perPageInitialSymptomNumber;
                                                            ?>
                                                                <li class="page-item">
                                                                <a class="page-link  text-primary" href="comparison-history.php?page=<?php echo $totalPage;?>&per_page_initial_symptom_number=<?php echo $perPageInitialSymptomNumber?>&id_to_continue=<?php echo $lastPage;?>&comid=<?php echo $comId; ?>"><?php echo $totalPage?></a>
                                                                </li>
                                                            <?php
                                                            }	
                                                        }

                                                        $db->close();           
                                                    ?>
                                                    </ul>
                                                    <br><br><br>
                                                </nav>
                                            </div>
                                            <?php
                                        }
                                        else
                                        {
                                    ?>
                                        <div class="symptom-row text-center">
                                            <div class="full-length-row">No records found.</div>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="loaderCEOverlay" class="hidden">
                            <div id="loaderCE" align="center" style="background: #EEE;">
                                <div>
                                    <p>Please Wait. Connect edit opeartion is going on.</p>
                                </div>
                                <img src="../assets/img/loader.gif">
                            </div>
                        </div>
                        
                        <div id="mydialog" style="display: none" align="center">
                        
                        <?php
                            //Save connection array modification
                            $singleConnectionsComparative = (isset($singleConnectionsComparative) AND !empty($singleConnectionsComparative)) ? array_unique($singleConnectionsComparative) : "";
                            $singleConnectionsComparativeString = (!empty($singleConnectionsComparative)) ? implode(',', $singleConnectionsComparative) : "";
                            $singleConnectionsInitialsString = (!empty($singleConnectionsInitials)) ? implode(',', $singleConnectionsInitials) : "";
                            $combinedConnectionsIntialsString = (!empty($combinedConnectionsInitials)) ? implode(',', $combinedConnectionsInitials) : "";
                            $combinedConnectionsComparative = (isset($combinedConnectionsComparative) AND !empty($combinedConnectionsComparative)) ? array_unique($combinedConnectionsComparative) : "";
                            $combinedConnectionsComparativeString = (!empty($combinedConnectionsComparative)) ? implode(',', $combinedConnectionsComparative) : "";

                            //Non secure array modification
                            $savedNonSecureIdArrayString = (!empty($savedNonSecureIdArray)) ? implode(',', $savedNonSecureIdArray) : "";
                        ?>
                        </div>
                        <!-- Including Modals html START -->
                        <?php include 'includes/comparison-table-page-modals.php'; ?>
                        <!-- Including Modals html END -->
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
<script src="allIconFunctions2-jay.js"></script>
<script src="connect-saved.js"></script>
<script type="text/javascript">
    var translationArray = {}; 
    <?php foreach($translationSymptomsArray as $tranKey => $tranVal){ ?>
        translationArray['<?php echo $tranKey; ?>'] = '<?php echo $tranVal; ?>';
    <?php } ?>
</script>
<!-- If the compariosn not open from history section then add below js -->
<script src="assets/js/comparison-icons.js"></script>
<script src="symptom-icon-functions.js"></script>
<script type="text/javascript">
    //Enabling loader if comparison table exist
    var comparison_table_check = $("#comparison_table").val();
    if(comparison_table_check != ""){
        $('#comparison_loader').removeClass("hidden");
    }

    // For stoping recursive shell excution status checking function 
    var stopRecursiveCall = false;
    var comparison_table_name = '<?php echo $comparisonTable; ?>';
    var comparison_language = '<?php echo $comparisonLanguage; ?>';
    $(window).bind("load", function() {
        var show_progress_msg_for_table = $("#show_progress_msg_for_table").val();
        if(show_progress_msg_for_table != ""){
            $("#comparison_table_overlay").removeClass('hidden');
            checkShellExecutionNew(show_progress_msg_for_table);
        }
        else
            $("#comparison_table_overlay").addClass('hidden');
    });

    $('#arznei_id').select2({
        // options 
        searchInputPlaceholder: 'Search Arznei...'
    });
    // Defining Select2
    $('#initial_source').select2({
        // options 
        searchInputPlaceholder: 'Search Quelle...'
    });
    $('#comparing_sources').select2({
        // options 
        searchInputPlaceholder: 'Search Quelle...'
    });

    $('#search_sources').select2({
        searchInputPlaceholder: 'Search Quelle...',
    });

    // Fetching Quelle/Sources of the arznei
    $('#arznei_id').on('select2:select', function (e) {
        if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
            $("#initial_source").prop("disabled", true);
            $("#comparing_sources").prop("disabled", true);
            var request = $.ajax({
                url: "get_arznei_quelle.php",
                type: "POST",
                data: {arznei_id : e.params.data.id},
                dataType: "json"
            });

            request.done(function(responseData) {
                console.log(responseData);
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }

                var saved_initial_source_id = $("#saved_initial_source_id").val(); 
                var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
                var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
                var initialSourceHtml = "";
                var comparingSourceHtml = "";

                // Initial source select box
                initialSourceHtml += '<select class="form-control save-data" name="initial_source" id="initial_source">';
                initialSourceHtml += '<option value="">Select</option>';

                var htmlComparisons = '<optgroup label="Comparisons">';
                var htmlSingleSources = '<optgroup label="Single sources">';
                var htmlComparisonsInner = ''; 
                var htmlSingleSourcesInner = '';

                // Comparing source select box
                comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
                comparingSourceHtml += '<option value="">Select</option>';

                var comHtmlComparisons = '<optgroup label="Comparisons">';
                var comHtmlSingleSources = '<optgroup label="Single sources">';
                var comHtmlComparisonsInner = ''; 
                var comHtmlSingleSourcesInner = '';
                


                $.each(resultData, function( key, value ) {
                    // Initial source select box
                    var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
                    // Comparing source select box
                    var comSelected = (split_saved_comparing_source_ids.indexOf(value.quelle_id) !== -1) ? 'selected' : '';
                    if(value.quelle_type_id == 3){
                        htmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                    } else {
                        htmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                    }

                }) ;
                // Initial source select box
                if(htmlComparisonsInner == '')
                    htmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    htmlComparisons += htmlComparisonsInner;
                if(htmlSingleSourcesInner == '')
                    htmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    htmlSingleSources += htmlSingleSourcesInner;
                
                // Comparing source select box
                if(comHtmlComparisonsInner == '')
                    comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlComparisons += comHtmlComparisonsInner;
                if(comHtmlSingleSourcesInner == '')
                    comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlSingleSources += comHtmlSingleSourcesInner;
                

                // Initial source select box
                htmlComparisons += '</optgroup>';
                htmlSingleSources += '</optgroup>';
                initialSourceHtml += htmlComparisons+htmlSingleSources;
                initialSourceHtml += '</select>';
                initialSourceHtml += '<span class="error-text"></span>';
                $("#initial_source_cnr").html( initialSourceHtml );
                $('#initial_source').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });

                // Comparing source select box
                comHtmlComparisons += '</optgroup>';
                comHtmlSingleSources += '</optgroup>';
                comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
                comparingSourceHtml += '</select>';
                comparingSourceHtml += '<span class="error-text"></span>';
                $("#comparing_source_cnr").html( comparingSourceHtml );
                $('#comparing_sources').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });
                $("#initial_source").prop("disabled", false);
                $("#comparing_sources").prop("disabled", false);
            });

            request.fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                $("#initial_source").prop("disabled", false);
                $("#comparing_sources").prop("disabled", false);
            });
        }
    });

    // Changing comparative source(selectbox) select options depending on initial source selection.  
    $(document).on('select2:select', '#initial_source', function(e){
        var arznei_id = $("#arznei_id").val();
        if((typeof(e.params.data.id) != "undefined" && e.params.data.id !== null) && arznei_id != ""){
            $("#comparing_sources").prop("disabled", true);
            var request = $.ajax({
                url: "get_comparing_quelle.php",
                type: "POST",
                data: {initial_source : e.params.data.id, arznei_id : arznei_id},
                dataType: "json"
            });

            request.done(function(responseData) {
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }

                var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
                var split_saved_comparing_source_ids = saved_comparing_source_ids.split(",");
                var comparingSourceHtml = "";

                // Comparing source select box
                comparingSourceHtml += '<select class="form-control save-data" name="comparing_sources[]" id="comparing_sources" multiple="multiple" data-placeholder="Search comparing source(s)">';
                comparingSourceHtml += '<option value="">Select</option>';

                var comHtmlComparisons = '<optgroup label="Comparisons">';
                var comHtmlSingleSources = '<optgroup label="Single sources">';
                var comHtmlComparisonsInner = '';
                var comHtmlSingleSourcesInner = '';


                $.each(resultData, function( key, value ) {
                    var conditionDisabled = "";
                    if(value.is_disabled == 1)
                        conditionDisabled = 'disabled="disabled"';
                    // Initial source select box
                    var selected = (saved_initial_source_id == value.quelle_id) ? 'selected' : '';
                    // Comparing source select box
                    var comSelected = '';
                    conditionDisabled = "";
                    if(value.quelle_type_id == 3){
                        comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
                    } else {
                        comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
                    }
                    

                });
                // Comparing source select box
                if(comHtmlComparisonsInner == '')
                    comHtmlComparisons += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlComparisons += comHtmlComparisonsInner;
                if(comHtmlSingleSourcesInner == '')
                    comHtmlSingleSources += '<option value="" disabled="disabled">None</option>';
                else
                    comHtmlSingleSources += comHtmlSingleSourcesInner;
                

                // Comparing source select box
                comHtmlSingleSources += '</optgroup>';
                comHtmlComparisons += '</optgroup>';
                comparingSourceHtml += comHtmlComparisons+comHtmlSingleSources;
                comparingSourceHtml += '</select>';
                comparingSourceHtml += '<span class="error-text"></span>';
                $("#comparing_source_cnr").html( comparingSourceHtml );
                $('#comparing_sources').select2({
                    // options 
                    searchInputPlaceholder: 'Search Quelle...'
                });
                $("#comparing_sources").prop("disabled", false);
            });

            request.fail(function(jqXHR, textStatus) {
                $("#comparing_sources").prop("disabled", false);
                console.log("Request failed: " + textStatus);
            });
        }
    });

    $('body').on( 'submit', '#symptom_comparison_form', function(e) {
        e.preventDefault();
        var initial_source = $("#initial_source").val();
        var arznei_id = $("#arznei_id").val();
        var comparing_sources = $("#comparing_sources").val();
        var comparison_language = $("#comparison_language").val();
        var similarity_rate = $("#similarity_rate").val();
        var comparison_option = $("#comparison_option").val();
        var per_page_initial_symptom_number = $("#per_page_initial_symptom_number").val();
        var is_opened_a_saved_comparison = $("#is_opened_a_saved_comparison").val();
        var error_count = 0;

        if(arznei_id == ""){
            $("#arznei_id").next().next().html('Please select arznei');
            $("#arznei_id").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#arznei_id").next().next().html('');
            $("#arznei_id").next().next().removeClass('text-danger');
        }
        if(initial_source == ""){
            $("#initial_source").next().next().html('Please select initial source');
            $("#initial_source").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#initial_source").next().next().html('');
            $("#initial_source").next().next().removeClass('text-danger');
        }
        if(comparing_sources == ""){
            $("#comparing_sources").next().next().html('Please select comparing source');
            $("#comparing_sources").next().next().addClass('text-danger');
            error_count++;
        }else{
            $("#comparing_sources").next().next().html('');
            $("#comparing_sources").next().next().removeClass('text-danger');
        }
        if(comparison_language == ""){
            $("#comparison_language").next().html('Please select comparison language');
            $("#comparison_language").next().addClass('text-danger');
            error_count++;
        }else{
            $("#comparison_language").next().html('');
            $("#comparison_language").next().removeClass('text-danger');
        }
        if(error_count != 0){
            return false;
        }else{
            // Checking if selected initial and comparing sources are available in selecetd comparison language.
            var initialSourceLanguage = $("#initial_source").find(':selected').attr("data-is-symptoms-available-in-"+comparison_language);
            var comparingSourceLanguage = $("#comparing_sources option:selected").map(function() {
                return $(this).attr("data-is-symptoms-available-in-"+comparison_language);
            }).get();
            comparingSourceLanguage.push(initialSourceLanguage);
            
            if($.inArray("0", comparingSourceLanguage) !== -1){
                $("#global_msg_container").html('<p class="text-center">There is/are source(s) in the Initial source or in the Comparing source(s) which are not available in the language that you have selecetd to compare, Please check and try again!</p>');
                $("#globalMsgModal").modal('show');
                return false;
            }else{
                // Showing the normal overlay loader here
                $("#comparison_loader").removeClass('hidden');

                var request = $.ajax({
                    url: "check-if-comparison-table-exist.php",
                    type: "POST",
                    data: {arznei_id : arznei_id, initial_source : initial_source, comparing_sources : comparing_sources, similarity_rate : similarity_rate, comparison_option : comparison_option, comparison_language : comparison_language, per_page_initial_symptom_number : per_page_initial_symptom_number, is_opened_a_saved_comparison : is_opened_a_saved_comparison},
                    dataType: "json"
                });

                request.done(function(responseData) {
                    var resultData = null;
                    try {
                        resultData = JSON.parse(responseData); 
                    } catch (e) {
                        resultData = responseData;
                    }
                    // console.log(resultData);
                    if(resultData.result_data.is_table_exist == 0){
                        $("#comparison_loader").addClass('hidden');
                        $("#comparison_table_overlay").removeClass('hidden');
                        stopRecursiveCall = false;
                        var dynamic_table_name = (typeof(resultData.result_data.dynamic_table_name) != "undefined" && resultData.result_data.dynamic_table_name !== null && resultData.result_data.dynamic_table_name != "") ? resultData.result_data.dynamic_table_name : "";
                        checkShellExecutionNew(dynamic_table_name);
                    }else{
                        window.location.href = "<?php echo $baseUrl?>comparison-history.php?comid=<?php echo $comId; ?>";
                    }
                });

                request.fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
                
            }
        }
    });

    function checkShellExecutionNew(dynamicTableName = ""){
        // If cancell is true the recusrive function calling will stop.
        if (stopRecursiveCall) {
            return;
        }
        if(dynamicTableName != "")
        {
            var request = $.ajax({
                type: "POST",
                url: "check-shell-execution-new.php",
                dataType: "json",
                data: {
                    dynamic_table_name: dynamicTableName
                }
            });

            request.done(function(responseData) {
                var resultData = null;
                try {
                    resultData = JSON.parse(responseData); 
                } catch (e) {
                    resultData = responseData;
                }
                if(responseData.status == "success"){
                    if(responseData.result_data.script_status == "Complete"){
                        setTimeout(function(){
                            $("#comparison_table_overlay").addClass('hidden');
                            $("#comparison_loader").addClass('hidden');
                            window.location.href = "<?php echo $baseUrl; ?>comparison-history.php?comid=<?php echo $comId; ?>";
                        }, 2000);
                    }else{
                        console.log('Again');
                        setTimeout(function(){
                            checkShellExecutionNew(dynamicTableName);
                        }, 5000);
                    }
                }else{

                    $("#comparison_table_overlay").addClass('hidden');
                    $("#comparison_loader").addClass('hidden');
                    console.log("Not Successfull: " + responseData);
                    $("#global_msg_container").html(responseData.message);
                    $("#globalMsgModal").modal('show');
                }
            });

            request.fail(function(jqXHR, textStatus) {
                console.log("Request failed: " + textStatus);
                
                $("#comparison_table_overlay").addClass('hidden');
                $("#comparison_loader").addClass('hidden');
                $("#global_msg_container").html('<p class="text-center">Something went wrong!</p>');
                $("#globalMsgModal").modal('show');
            });
        }
    }

    function checkShellExecution(){
        // If cancell is true the recusrive function calling will stop.
        if (stopRecursiveCall) {
            return;
        } 

        var request = $.ajax({
            url: "check-shell-execution.php",
            type: "POST",
            dataType: "json"
        });

        request.done(function(responseData) {
            var resultData = null;
            try {
                resultData = JSON.parse(responseData); 
            } catch (e) {
                resultData = responseData;
            }
            console.log(responseData);
            if(responseData.status == "success"){
                if(responseData.result_data.script_status == "Complete"){
                    $("#comparison_table_overlay").addClass('hidden');
                    $("#comparison_loader").addClass('hidden');
                    // location.reload();
                    window.location.href = "<?php echo $baseUrl?>comparison-history.php?comid=<?php echo $comId; ?>";
                }else{
                    setTimeout(checkShellExecution, 5000);
                }
            }else{
                $("#comparison_table_overlay").addClass('hidden');
                $("#comparison_loader").addClass('hidden');
                console.log("Not Successfull: " + responseData);
                $("#global_msg_container").html(responseData.message);
                $("#globalMsgModal").modal('show');
            }
        });

        request.fail(function(jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });
    }

    // Closing the processing overlay clicking on that
    // $('body').on( 'click', '#comparison_table_overlay', function(e) {
    // 	stopRecursiveCall = true;
    // 	$(this).addClass('hidden');
    // });

    $(document).on('change', '#per_page_initial_symptom_number', function(e){
        console.log(1);
        $("#symptom_comparison_form").submit();
    });
    
    var swappedSymptoms = [];
    var connected_symptoms_saved = [];
    var connected_edited_symptoms_saved = [];
    var pasted_symptoms_saved = [];
    var pasted_edited_symptoms_saved = [];
    var connected_symptoms =[];
    var pasted_symptoms = [];
    //Paste Edit Variables
    var comparative_symptoms_original_pe =[];
    var comparative_symptoms_edited_pe =[];
    var initial_symptoms_connected_pe =[];
    var edited_comparative;
    var peComparativeId, peInitialId;
    var initialSymptomPE, comparativeSymptomPE;
    //Varibales for Connect Edit
    var cutoff_percentage = <?php echo $similarityRate;?>;
    var comparison_option = <?php echo $comparisonOption;?>;
    //console.log(cutoff_percentage);
    var initial_symptoms_original =[];
    var initial_symptoms_edited =[];
    var comparative_symptoms_connected =[];
    var ce_initials = {};
    var edited_initial;
    var ceComparativeId, ceInitialId;
    var initialSymptom, comparativeSymptom;
    var language, translation;
    language = "<?php echo $comparisonLanguage?>";
    var pass_through = 0;
    var swapped = false;	
    var swapped_modal_initial, swapped_modal_comparative;
    var latestIdArray;
    var latestInitialId="";
    var latestComparingId="";
    $(document).ready(function(){
        // If the comparison is opened from history section then some functions are not allowed to perform START
        //Connection functions
        // $.fn.connectEditFunction();
        // $.fn.pasteFunction();
        // $.fn.connectFunction();
        // $.fn.pasteEditFunction();
        // If the comparison is opened from history section then some functions are not allowed to perform END

        var singleConnectionComparativeCheck = [<?php echo $singleConnectionsComparativeString; ?>];
        var singleConnectionInitialCheck = [<?php echo $singleConnectionsInitialsString; ?>];
        var combinedConnectionIntialsCheck = [<?php echo $combinedConnectionsIntialsString; ?>];
        var combinedConnectionComparativeCheck = [<?php echo $combinedConnectionsComparativeString; ?>];

        console.log(singleConnectionInitialCheck);
        console.log(singleConnectionComparativeCheck);
        console.log(combinedConnectionIntialsCheck);
        console.log(combinedConnectionComparativeCheck);
        
        //Sending ID's for saved connections check
        if(singleConnectionComparativeCheck.length > 0)
        {
            var symptomType = "comparative";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations-for-history.php",
                data: {
                    singleConnectionComparativeCheck:singleConnectionComparativeCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                    }

                    //console.log(resultData);

                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type ="singleSourceComparative";
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
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(singleConnectionInitialCheck.length > 0)
        {
            var symptomType = "initial";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations-for-history.php",
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

                    console.log(resultData);
                    console.log(latestIdArray);
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
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }

                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(combinedConnectionIntialsCheck.length > 0)
        {
            var symptomType = "combined-initial";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations-for-history.php",
                data: {
                    combinedConnectionIntialsCheck:combinedConnectionIntialsCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    console.log(returnedData);
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                        latestIdArray = JSON.parse(returnedData.latestIdResult); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                        latestIdArray = returnedData.latestIdResult;
                    }

                    console.log(resultData);
                    console.log(latestIdArray);
            
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type = "combinedSourceInitials";
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
                        var is_earlier_connection = resultData[i].is_earlier_connection;
                        var free_flag = resultData[i].free_flag;
                        var non_encoded_comparing_symptom_de = resultData[i].non_encoded_comparing_symptom_de;
                        var non_encoded_comparing_symptom_en = resultData[i].non_encoded_comparing_symptom_en;
                        var non_encoded_initial_symptom_de = resultData[i].non_encoded_initial_symptom_de;
                        var non_encoded_initial_symptom_en = resultData[i].non_encoded_initial_symptom_en;
                        var connection_type = resultData[i].connection_type;
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        if(combinedConnectionComparativeCheck.length > 0)
        {
            var symptomType = "combined-comparative";
            $.ajax({
                async:false,
                type: "POST",
                url: "symptom-connection-operations-for-history.php",
                data: {
                    combinedConnectionComparativeCheck:combinedConnectionComparativeCheck,
                    symptomType: symptomType
                },
                dataType: "JSON",
                success: function(returnedData){
                    try {
                        resultData = JSON.parse(returnedData.result_data); 
                    } catch (e) {
                        resultData = returnedData.result_data;
                    }
                    
                    console.log(resultData);
                    for (var i=0; i<resultData.length; i++)
                    {
                        var source_type = "combinedSourceComparative";
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
                        $.fn.connectSave(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type,connection_type,is_earlier_connection,free_flag);

                        // Translation Array
                        var tarnKeyCom = "row"+initial_symptom_id+"_"+comparing_symptom_id+"_translated_symptom";
                        var tarnKeyIni = "row"+initial_symptom_id+"_translated_symptom";
                        if(comparison_language == "de"){
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_en;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_en;
                        }
                        else{
                            translationArray[tarnKeyCom] = non_encoded_comparing_symptom_de;
                            translationArray[tarnKeyIni] = non_encoded_initial_symptom_de;
                        }

                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }

        $(document).on("click", ".all-connections", function (x){

            if($(this).prop("checked") == true) {
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-plus')){
                        $(this).click();

                    }
                });

                $(".toggleComparative").each(function(){
                    if($(this).children('.fas').hasClass('fa-plus')){
                        $(this).click();

                    }
                });
            }else{
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-minus')){
                        $(this).click();

                    }
                });

                $(".toggleComparative").each(function(){
                    if($(this).children('.fas').hasClass('fa-minus')){
                        $(this).click();

                    }
                });
            }
        });

        /////////////////////////Toggle Icons/////////////////////////
        $(document).on("click", ".toggleInitial", function (ev) {
            $(this).parents('div.initial').nextUntil(".initial").each(function()
            {
                if($(this).hasClass('comparativesConnectedCD'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedCE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPASTE'))
                    $(this).toggle();
            });
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus')
            else
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus')
        });

        $(document).on("click", ".toggleComparative", function (ev) {
            $(this).parents('div.comparing').nextUntil('.comparing').each(function(){
                if($(this).hasClass('initialsConnectedCD'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedCE'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedPE'))
                    $(this).toggle();
                if($(this).hasClass('initialsConnectedPASTE'))
                    $(this).toggle();
                if($(this).hasClass('comparativesConnectedPASTE'))
                    $(this).toggle();
            });
            
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
            else
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        });

        //opening the latest connections
        //console.log(latestIdArray);
        if(latestIdArray != null){
            latestInitialId = latestIdArray.initial_symptom_id;
            latestComparingId = latestIdArray.comparing_symptom_id;
            $('.row'+latestInitialId).each(function(){
                if($(this).find('.toggleInitial')){
                    $(this).find('.toggleInitial').trigger('click');
                }
            });	
        }
        /////////////////////////DELETE SYMPTOM WHEN DISCONNECT LINK IS CLICKED /////////////////////////
        $.fn.deleteSymptoms = function(initialSymp, comparativeSymp,operation){ 
            $.ajax({
                        async:false,
                        type: "POST",
                        url: "connection-delete-script.php",
                        data: "type=normal&initialSymptom="+initialSymp+"&symptom="+comparativeSymp+"&operation="+operation,
                        dataType: "JSON",
                        success: function(returnedData){
                            console.log(returnedData);
                        },
                        error: function(xhr, textStatus, error){
                            console.log(xhr.statusText);
                            console.log(textStatus);
                            console.log(error);
                        }
                    });
        }

        //Comments on load function starts
        var comments_on_load = [<?php echo $commentsOnLoad; ?>];
        //console.log(comments_on_load);
        //non secure connection active icon load
        if(comments_on_load.length > 0)
        {
            comments_on_load.forEach((commentsLoad) => {
                $.fn.commentsOnLoadFn(commentsLoad);
            });
        }
        //Comments on load function ends

        //Footnote on load function starts
        var footnote_on_load = [<?php echo $footnoteOnLoad; ?>];
        //console.log(footnote_on_load);
        //non secure connection active icon load
        if(footnote_on_load.length > 0)
        {
            footnote_on_load.forEach((footnoteLoad) => {
                $.fn.footnoteOnLoadFn(footnoteLoad);
            });
        }
        //Footnote on load function ends

        //Translations on load function starts
        var translation_on_load = [<?php echo $translations; ?>];
        //console.log(translation_on_load);
        if(translation_on_load.length > 0)
        {
            translation_on_load.forEach((translationLoad) => {
                $.fn.translationOnLoadFn(translationLoad);
            });
        }
        //Translations on load function ends	

        //Non secure connections on load starts
        var non_secure_on_load = [<?php echo $savedNonSecureIdArrayString; ?>];
        console.log(non_secure_on_load);
        if(non_secure_on_load.length > 0)
        {
            non_secure_on_load.forEach((nonSecureLoad) => {
                $.fn.nonSecureOnLoad(nonSecureLoad);
            });
        }
        //Non secure connections on load ends
    });//End document ready function

    //checking if the document is ready for hiding loader 
    $(function() {
        if(comparison_table_check != "")
            $('#comparison_loader').addClass("hidden");
    });

    $('body').on( 'click', '#all_translation', function(e) {
        if($(this).prop("checked") == true) {
            $.when(
                $("#common_small_loader").removeClass('hidden')
                ).then(function () {
                    $(".symptom-translation-btn").each(function(){
                        var uniqueId = $(this).attr("data-unique-id");
                        // console.log(uniqueId+" <-WoW");
                        if (!$(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                            $(this).click();
                        }
                    }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
                });
            // $("#common_small_loader").removeClass('hidden');
            
        }else{
            $.when(
                $("#common_small_loader").removeClass('hidden')
                ).then(function () {
                    $(".symptom-translation-btn").each(function(){
                        var uniqueId = $(this).attr("data-unique-id");
                        // console.log(uniqueId+" <-UNWoW");
                        if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                            $(this).click();
                        }
                    }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
                });
            
        } 
    });

    $('body').on( 'click', '.symptom-translation-btn', function(e) {
        e.preventDefault();
        var uniqueId = $(this).attr("data-unique-id");
        var keyString = uniqueId+'_translated_symptom';
        var symptomTranslation = (typeof(translationArray[keyString]) != "undefined" && translationArray[keyString] !== null && translationArray[keyString] != "") ? translationArray[keyString] : "";

        if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
            $(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).remove();
        }else{
            if(symptomTranslation == "" || symptomTranslation == "Translation is not available"){
                $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'"></div>');
            }else{
                $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'" class="translated-symptom-div">'+symptomTranslation+'</div>');
            }
        }
    });
</script>