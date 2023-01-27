<?php
	include '../lang/GermanWords.php';
	include '../config/route.php';
	include 'sub-section-config.php';
	include '../api/mainCall.php';
?>
<?php  
	$cutOff = (isset($_GET['matched_percentage']) AND $_GET['matched_percentage'] != "") ? $_GET['matched_percentage'] : "";
	$comparisonOption = (isset($_GET['comparison_option']) AND $_GET['comparison_option'] != "") ? $_GET['comparison_option'] : 1;
	$comparisonLanguage = (isset($_GET['comparison_language']) AND $_GET['comparison_language'] != "") ? $_GET['comparison_language'] : "";
	$comparisonTable= (isset($_GET['table']) AND $_GET['table'] != "") ? $_GET['table'] : "";
	$highestMatchTable = $comparisonTable."_highest_matches";
	$perPageUnmatchedSymptomNumber= (isset($_GET['per_page_unmatched_symptom_number']) AND $_GET['per_page_unmatched_symptom_number'] != "") ? $_GET['per_page_unmatched_symptom_number'] : 100;
	$arzneiId = (isset($_GET['arznei_id']) AND $_GET['arznei_id'] != "") ? $_GET['arznei_id'] : "";
	$page = (isset($_GET['page']) AND $_GET['page'] != "") ? $_GET['page'] : 1;//Pages
	$finalView = 0;
	$flag = 0;
	//checking
	if($cutOff!="" && $comparisonLanguage !="" && $comparisonTable !="")
	{
		$flag = 1;
		$limit_page = $perPageUnmatchedSymptomNumber; 
		$pr_query = $db->prepare("SELECT `symptom_id` from $highestMatchTable WHERE matched_percentage <?");
		$pr_query->bind_param("i",$cutOff);
		$pr_query->execute();
		$pr_query->store_result();
		$total_rec= $pr_query->num_rows;
		$total_page = ceil($total_rec/$limit_page);

		$startPaginationLimit= ($page - 1)*$perPageUnmatchedSymptomNumber;
		$endPaginationLimit = $perPageUnmatchedSymptomNumber;

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
	//Comments storing
	$commentsOnLoad = (!empty($commentsDataArray)) ? implode(',', $commentsDataArray) : "";
	$footnoteOnLoad = (!empty($footnoteDataArray)) ? implode(',', $footnoteDataArray) : "";
	$translations = (!empty($translationDataArray)) ? implode(',', $translationDataArray) : "";

	//Array for connected symptoms check
	$savedConnectArray = array();
?>
<?php
	
	include '../inc/header.php';
	include '../inc/sidebar.php';
?>
<!-- custom -->
<link rel="stylesheet" href="assets/css/custom-temp.css">
<!-- new comparison table style -->
<link rel="stylesheet" href="assets/css/new-comparison-table-style.css">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
	    <h1>Unmatched Symptoms</h1>
	    <ol class="breadcrumb">
	    	<li><a href="<?php echo $absoluteUrl;?>"><i class="fa fa-dashboard"></i> <?php echo $home; ?></a></li>
	    	<li class="active">Comparison</li>
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
						<div class="container-fluid">
							<div class="row">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-6">
											<div class="row">
												<div>
													<!-- <div class="col-sm-3 " style="position:absolute;bottom:0;left:0;"> -->
													<div class="col-sm-9">
														<div class="row form-group Text_form_group">
															<div class="col-sm-5">
																<label class="control-label">No. of unmatched symptom per page: <span class="required">*</span></label>
																<select class="btn btn-mini form-control"  id="per_page_unmatched_symptom_number" name="per_page_unmatched_symptom_number">
																	<?php for ($j=5; $j <= 100; $j+=5) { ?>
																		<option <?php if($perPageUnmatchedSymptomNumber == $j) { echo 'selected'; } ?> value="<?php echo $j; ?>"><?php echo $j; ?></option>
																	<?php } ?>
																</select>	
															</div>
															<div class="col-sm-4">
																
															</div>
														</div>
													</div>
													<div class="col-sm-3"></div>
												</div>
											</div>
										</div>
										<div class="col-sm-6">
											
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12 sticky-head">
									<div style="text-align: center; background-color: #F1FB3A;">
										<div class="btn">
											<h4>Showing unmatched symptoms</h4>
										</div>
									</div>
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
									<?php
										$translationSymptomsArray = array();
										if($flag == 1)
										{
										
											$runningInitialSymptomId = "";
											$runningInitialSymptomDe = "";
											$runningInitialSymptomEn = "";
											
											$symptomResult = $db->prepare("SELECT * FROM $highestMatchTable  WHERE matched_percentage <? LIMIT ?,?");
											$symptomResult->bind_param("iii",$cutOff,$startPaginationLimit,$endPaginationLimit);
											$symptomResult->execute();
											$symptomResult->store_result();
											$symptomResult->execute();
											$symptomResult->store_result();
											if($symptomResult->num_rows > 0)
											{
												$meta = $symptomResult->result_metadata();
												while ( $field = $meta->fetch_field() ){
													$parameters[] = &$symRow[$field->name]; 
												}
												call_user_func_array(array($symptomResult, 'bind_result'), $parameters);

												while ( $symptomResult->fetch() ){
													$x = array();
													foreach( $symRow as $key => $val ) 
													{
														$x[$key] = $val;
													}
													$results[] = $x;
												}
												
												foreach ($results as $symRow){
													//Pushing the values in array
													array_push($savedConnectArray,$symRow['symptom_id']);

													$originalQuelleDataQuery = mysqli_query($db,"SELECT quelle_id FROM quelle_import_test WHERE id = '".$symRow['symptom_id']."' AND arznei_id = '".$symRow['arznei_id']."'");
													if(mysqli_num_rows($originalQuelleDataQuery) > 0){
														$originalQuelleData = mysqli_fetch_assoc($originalQuelleDataQuery);
													}	
													$originalQuelleIdForConversion = (isset($originalQuelleData['quelle_id']) AND $originalQuelleData['quelle_id'] != "") ? $originalQuelleData['quelle_id'] : "";
													if($originalQuelleIdForConversion == "")
														$originalQuelleIdForConversion = $symRow['quelle_id'];

													// Selecting symptom string depending on comparison option that user selected
													$symptomString_de = "";
													$symptomString_en = "";
													if($symRow['is_final_version_available'] != 0){
														$symptomString_de =  $symRow['final_version_de'];
														$symptomString_en =  $symRow['final_version_en'];
													} else {
														if($comparisonOption == 1){
															$symptomString_de =  ($symRow['searchable_text_de'] != "") ? $symRow['searchable_text_de'] : "";
															$symptomString_en =  ($symRow['searchable_text_en'] != "") ? $symRow['searchable_text_en'] : "";
														}else{
															$symptomString_de =  ($symRow['BeschreibungFull_de'] != "") ? $symRow['BeschreibungFull_de'] : "";
															$symptomString_en =  ($symRow['BeschreibungFull_en'] != "") ? $symRow['BeschreibungFull_en'] : "";
														}
													}
													if($comparisonLanguage == "en"){
														$displayingSymptomString = $symptomString_en;
														$translationSymptomsArray['row'.$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_de != "") ? $symptomString_de : 'Translation is not available';
													}
													else
													{
														$displayingSymptomString = $symptomString_de;
														$translationSymptomsArray['row'.$symRow['symptom_id'].'_translated_symptom'] = ($symptomString_en != "") ? $symptomString_en : 'Translation is not available';
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
														$symptomString_de = convertTheSymptom($symptomString_de, $originalQuelleIdForConversion, $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
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
														$symptomString_en = convertTheSymptom($symptomString_en,$originalQuelleIdForConversion, $symRow['arznei_id'], 0, 0, $symRow['symptom_id']);
													}

													// Displayable symptom string without highlighting
													$symptomString_without_highlight_en = $symptomString_en;
													$symptomString_without_highlight_de = $symptomString_de;

													// Preparing Symptom string with available language divs
													$displayingSymptomString = "";
													if($comparisonLanguage == "en")
														$displayingSymptomString = $symptomString_en;
													else
														$displayingSymptomString = $symptomString_de;

													$runningInitialSymptomDe = $symptomString_without_highlight_de;
													$runningInitialSymptomEn = $symptomString_without_highlight_en;
													$runningInitialSymptomId = $symRow['symptom_id'];
													$uniqueId = "row".$symRow['symptom_id'];
													// storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
													$runningInitialSymptom = $displayingSymptomString;
													?>
													<div class="<?php echo $uniqueId; ?> symptom-row unmatched" id="row<?php echo $symRow['symptom_id']; ?>" data-year="<?php echo $symRow['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-quell-id="<?php echo $symRow['quelle_id']; ?>">
																<div class="source-code"><?php echo $symRow['quelle_code']; ?></div>
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
																			<a class="symptom-comment-btn <?php if($symRow['Kommentar'] != ""){ echo "active"; } ?>" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
																		</li>			
																		<li>				
																			<a class="symptom-footnote-btn <?php if($symRow['Fussnote'] != ""){ echo "active"; } ?>" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
																		</li>			
																		<li>				
																			<a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="<?php echo $uniqueId; ?>">T</a>			
																		</li>
																		<!-- <li>				
																			<a class="symptom-search-btn" title="Search" href="javascript:void(0)"><i class="fas fa-search"></i></a>			
																		</li> -->	
																	</ul>
																</div>
																<div class="command"><ul class="command-group"></ul></div>
															</div>
													<?php
													
												}
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
														
														if(($currentPage == $total_page) && ($currentPage == 1))
														{
															echo '<li class="page-item"><a href="javascript:void(0)"></a></li>';
														}
														else
														{
															if($page>4)
															{
																?>
																<li class="page-item">
																<a class="page-link  text-primary" href="unmatched-symptoms.php?page=<?php echo $fixedInitialPage;?>&per_page_unmatched_symptom_number=<?php echo $perPageUnmatchedSymptomNumber?>&matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>">1</a>
																</li>
																<?php
															}

															//Left Page
															if(($currentPage - $pageDisplayToLeft) > 1) 
															{
																echo ' <li class="page-item"><a href="javascript:void(0)">...</a></li> ';
															}
															$pageDisplay = max(1, $currentPage - $pageDisplayToLeft);			
										
															while($pageDisplay < $currentPage) 
															{
																?>
																	<li class="page-item"><a class="page-link  text-dark " href="unmatched-symptoms.php?page=<?php echo $pageDisplay;?>&per_page_unmatched_symptom_number=<?php echo $perPageUnmatchedSymptomNumber?>&matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>"><?php echo $pageDisplay?></a></li>
																<?php
																$pageDisplay++;
															}
															?>
															<?php 
																//Current Page
																if($currentPage == $page)
																{
																	$pageClassStyle = "active";
																}

															?>
																<li class="page-item <?php echo $pageClassStyle?>"><a class="page-link  text-light bg-danger " href="javascript:void(0)"><?php echo $currentPage?></a></li>
															<?php
																//Right Page

																$pageDisplay = min($total_page, $currentPage + 1);
																while($pageDisplay < min($currentPage + $pageDisplayToRight, $total_page)) 
																{
																	?>
																		<li class="page-item"><a class="page-link  text-dark " href="unmatched-symptoms.php?page=<?php echo $pageDisplay;?>&per_page_unmatched_symptom_number=<?php echo $perPageUnmatchedSymptomNumber?>&matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>"><?php echo $pageDisplay?></a></li>
																	<?php
																	$pageDisplay++;

																}
																if(($currentPage + $pageDisplayToRight) < $total_page) 
																{
																	echo '<li class="page-item"><a href="javascript:void(0)">...</a></li> ';
																}

																if($currentPage<$total_page)
																{
																?>
																	<li class="page-item">
																	<a class="page-link  text-primary" href="unmatched-symptoms.php?page=<?php echo $total_page;?>&per_page_unmatched_symptom_number=<?php echo $perPageUnmatchedSymptomNumber?>&matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>"><?php echo $total_page?></a>
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
						<?php 
							$savedConnectString = (!empty($savedConnectArray)) ? implode(',', $savedConnectArray) : "";
						?>
						<div id="mydialog" style="display: none" align="center">
						
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
<script src="assets/js/comparison-icons.js"></script>
<script src="symptom-icon-functions.js"></script>
<script type="text/javascript">
    var isHistory = "";
    var translationArray = {}; 
    <?php foreach($translationSymptomsArray as $tranKey => $tranVal){ ?>
        translationArray["<?php echo $tranKey; ?>"] = "<?php echo $tranVal; ?>";
    <?php } ?>
    console.log(translationArray);

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

    // For stoping recursive shell excution status checking function 
    var stopRecursiveCall = false;
    var comparison_table_name = '<?php echo $comparisonTable; ?>';
    var comparison_language = '<?php echo $comparisonLanguage; ?>';

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
                    conditionDisabled = '';
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

    // NonSecureCconnect Or softconnect
    $(document).on('click', '.symptom-soft-connect-btn', function(){
        if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
            $("#nsc_note_modal_loader .loading-msg").addClass('hidden');
        $("#nsc_note_modal_loader").addClass('hidden');
        $("#nscNoteModal").modal('show');
    });

    //Unmatched Symptoms Code
    $(document).ready(function()
    {
        //Unmatched control function
        $.fn.unmatchedConnection = function(symptom_id){
            if(symptom_id != '')
            {
                $('.row'+symptom_id).addClass('hidden');
                console.log('Symptom '+symptom_id+' hidden');
            }
            
        }

        //Control of unmatched symptoms already connected
        var savedConnections = [<?php echo $savedConnectString; ?>];
        console.log(savedConnections);
        if(savedConnections.length > 0)
        {
            $.ajax({
                async:false,
                type: "POST",
                url: "unmatched-connections-check.php",
                data: {
                    savedConnections:savedConnections
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
                        var symptom_id = resultData[i].symptom_id;
                        $.fn.unmatchedConnection(symptom_id);
                    }
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        }
        
        //Per page umatched symptom
        $(document).on("change", "#per_page_unmatched_symptom_number", function()
        {
            var y  = $(this).val();
            //console.log(y);
            var url = "unmatched-symptoms.php?page=1&per_page_unmatched_symptom_number="+y+"&matched_percentage=<?php echo $cutOff?>&table=<?php echo $comparisonTable?>&comparison_option=<?php echo $comparisonOption?>&comparison_language=<?php echo $comparisonLanguage?>&arznei_id=<?php echo $arzneiId?>";
            $(location).attr('href',url);
        });
    });
    $(document).ready(function(){
        
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
            /*$(this).parents('div.comparing').nextAll('[class^="initialsConnected"]').nextUntil('.initial').toggle();
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus')
            else
                $(this).find('i').removeClass('fa-minus').addClass('fa-plus')*/
            $(this).parents('div.comparing').nextUntil('.comparing').each(function(){
                if($(this).is('[class^="initialsConnected"]'))
                {
                    $(this).toggle();
                }
            });
            if($(this).find('i').hasClass('fa-plus'))
                $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
            else
                $(this).find('i').removeClass('fa-mius').addClass('fa-plus');
        });

        ////////////////////////Translation Display//////////////////
        $('body').on( 'click', '.symptom-translation-btn', function(e) {
            e.preventDefault();
            var uniqueId = $(this).attr("data-unique-id");
            var keyString = uniqueId+'_translated_symptom';
            var symptomTranslation = (typeof(translationArray[keyString]) != "undefined" && translationArray[keyString] !== null && translationArray[keyString] != "") ? translationArray[keyString] : "";

            if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                $(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).remove();
                console.log("100");
            }else{
                if(symptomTranslation == "" || symptomTranslation == "Translation is not available"){
                    $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'"></div>');
                    console.log("200");
                }else{
                    $(this).parents('div#'+uniqueId).find("div.symptom").append('<div id="translation_display_'+uniqueId+'" class="translated-symptom-div">'+symptomTranslation+'</div>');
                    console.log("300");
                }
            }
        });
    });//End document ready function
</script>