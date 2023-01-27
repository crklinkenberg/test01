<?php
	include '../config/route.php';
	include 'sub-section-config.php';

	$tbl = (isset($_GET['tbl']) AND $_GET['tbl'] != "") ? $_GET['tbl']."_completed" : "";
	$comparison_tbl = (isset($_GET['tbl']) AND $_GET['tbl'] != "") ? $_GET['tbl'] : "";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Final version</title>
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
		}
		.ui-dialog{
			width: auto !important;
			height: auto !important;
		}
		.ui-dialog-titlebar{
			background-color: #1E90FF !important;
		}
  	</style>
</head>
<body>
	<div id="comparison_table_overlay" class="hidden">
		<div class="overlayBody">
			<p>Our record shows that you have not compared these two sources before.</p>
			<p>Please wait while we process the comparison.</p>
			<img width="25px" src="../assets/img/loader.gif" alt="Loading...">
		</div>
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<ul class="comparison-navigation-ul">
					<li><a href="<?php echo $baseUrl ?>comparison-v3.php" title="Comparison" class="btn head-btn active" role="button">Comparison</a></li>
					<li><a href="<?php echo $baseUrl ?>materia-medica.php" title="Materia Medica"  class="btn head-btn" role="button">Materia Medica</a></li>
					<li><a href="<?php echo $baseUrl ?>comparison-table-status.php" title="History" class="btn head-btn" role="button">History</a></li>
				</ul>
				<div class="spacer"></div>
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
			<div class="col-sm-12">
				<?php
					if($tbl != "" AND $comparison_tbl != "")
					{
						$checkIfComparisonCompleteTableExist = mysqli_query($db, "SHOW TABLES LIKE '".$tbl."'");
						if(mysqli_num_rows($checkIfComparisonCompleteTableExist) != 0){
							$comparisonTableResult = mysqli_query($db, "SELECT * FROM $tbl ORDER BY id");
							if(mysqli_num_rows($comparisonTableResult) > 0){
								while($symRow = mysqli_fetch_array($comparisonTableResult)){
									$masterResult = mysqli_query($db, "SELECT * FROM pre_comparison_master_data where table_name = '".$comparison_tbl."'");
									if(mysqli_num_rows($masterResult) > 0){
										$masterData = mysqli_fetch_assoc($masterResult);
									}

									$comparisonOption = (isset($masterData['comparison_option']) AND $masterData['comparison_option'] != "") ? $masterData['comparison_option'] : "";
									$comparisonLanguage = (isset($masterData['comparison_language']) AND $masterData['comparison_language'] != "") ? $masterData['comparison_language'] : "";
									// Selecting symptom string depending on comparison option that user selected
							    	$symptomString_de = "";
									$symptomString_en = "";
							    	if($comparisonOption == 1){
										$symptomString_de =  ($symRow['searchable_text_de'] != "") ? $symRow['searchable_text_de'] : "";
										$symptomString_en =  ($symRow['searchable_text_en'] != "") ? $symRow['searchable_text_en'] : "";
									}else{
										$symptomString_de =  ($symRow['BeschreibungFull_de'] != "") ? $symRow['BeschreibungFull_de'] : "";
										$symptomString_en =  ($symRow['BeschreibungFull_en'] != "") ? $symRow['BeschreibungFull_en'] : "";
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
									if($comparisonLanguage == "en")
										$displayingSymptomString = $symptomString_en;
									else
										$displayingSymptomString = $symptomString_de;

									// Initial symptom
									$runningInitialSymptomDe = $symptomString_without_highlight_de;
									$runningInitialSymptomEn = $symptomString_without_highlight_en;
									$runningInitialSymptomId = $symRow['symptom_id'];
									$uniqueId = "row".$symRow['symptom_id'];
									// storing initial symptom in $runningInitialSymptom for using while comparing with comparing symptom
									$runningInitialSymptom = $displayingSymptomString;
									?>
									<div class="<?php echo $uniqueId; ?> symptom-row initial" id="row<?php echo $symRow['symptom_id']; ?>" data-year="<?php echo $symRow['quelle_jahr']; ?>" data-initial-symptom-de="<?php echo base64_encode($runningInitialSymptomDe); ?>" data-initial-symptom-en="<?php echo base64_encode($runningInitialSymptomEn); ?>" data-comparing-symptom-de="" data-comparing-symptom-en="" data-source-original-language="<?php echo $symRow['initial_source_original_language']; ?>"data-quell-id="<?php echo $symRow['quelle_id']; ?>">
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
													<a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a>	
												</li>			
												<li>				
													<a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a>			
												</li>			
												<li>				
													<a class="symptom-translation-btn" title="translation" href="javascript:void(0)">T</a>			
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
	<div id="mydialog" style="display: none" align="center">
    
	</div>

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
	<script src="connect.js"></script>
	<script src="paste.js"></script>
	<script src="paste-edit.js"></script>
	<script src="connect-edit.js"></script>
	<script src="connect-saved.js"></script>
	<script src="paste-saved.js"></script>
	<script src="paste-edit-saved.js"></script>
	<script src="connect-edit-saved.js"></script>
	<script src="swap-symptom-saved.js"></script>
	<script src="allIconFunctions2.js"></script>
	<script src="assets/js/comparison-icons.js"></script>
	<script src="symptom-icon-functions.js"></script>
	<script src="non-secure-connect.js"></script>
	<script type="text/javascript">
		// For stoping recursive shell excution status checking function 
		var stopRecursiveCall = false;
		var comparison_table_name = '<?php echo $comparisonTable; ?>';
		var comparison_language = '<?php echo $comparisonLanguage; ?>';
		$(window).bind("load", function() {
			var show_progress_msg = $("#show_progress_msg").val();
			if(show_progress_msg == 1){
				$("#comparison_table_overlay").removeClass('hidden');
				checkShellExecution();
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
					var request = $.ajax({
					  	url: "check-if-comparison-table-exist.php",
					  	type: "POST",
					  	data: {arznei_id : arznei_id, initial_source : initial_source, comparing_sources : comparing_sources, similarity_rate : similarity_rate, comparison_option : comparison_option, comparison_language : comparison_language, per_page_initial_symptom_number : per_page_initial_symptom_number},
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
							$("#comparison_table_overlay").removeClass('hidden');
							checkShellExecution();
							// createDynamicComparisonTable(arznei_id, initial_source, comparing_sources, similarity_rate, comparison_option, comparison_language, per_page_initial_symptom_number);
						}else{
							window.location.href = "<?php echo $baseUrl; ?>comparison-v3.php";
						}
					});

					request.fail(function(jqXHR, textStatus) {
					  	console.log("Request failed: " + textStatus);
					});
					
				}
			}
		});

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
				// console.log(responseData);
				if(responseData.status == "success"){
					if(responseData.result_data.script_status == "Complete"){
						$("#comparison_table_overlay").addClass('hidden');
						// location.reload();
						window.location.href = "<?php echo $baseUrl; ?>comparison-v3.php";
					}else{
						setTimeout(checkShellExecution, 5000);
					}
				}else{
					$("#comparison_table_overlay").addClass('hidden');
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
		

		///////////JAY's JQUERY CODE STARTS FROM HERE////////////
		//Variables for Simple Connnect Disconnect
		//var connected_symptoms_saved = ["row62543_62590","row62543_62606","row62545_62592"];
		//var connected_edited_symptoms_saved = ["row62542{#%ID%#}Früh, beim Erwachen, wie von Kummer niedergedrückt, ohne helles Bewußtseyn.||###||62606{#%ID%#}Grosse Unruhe im Schlafe, mit heftigem Weinen und trostlosem Jammern, ohne gehöriges Bewusstsein.||###||row62542{#%ID%#}Früh, beim Erwachen, wie von Kummer niedergedrückt, 123 ohne helles Bewußtseyn.", "row62549{#%ID%#}Aengstlichkeit mit äußerer Hitze und Unruhe, als habe sie Böses begangen.||###||62603{#%ID%#}Zögerndes Einschlafen, wegen Hitze und Unruhe in allen Gliedern.||###||row62549{#%ID%#}Aengstlichkeit mit äußerer Hitze und Unruhe, als habe sie Böses 5454 begangen."];
		//var connected_edited_symptoms_saved = ["row62540{#%ID%#}Es kommen ihr immer traurige Gedanken in den Kopf, die sie zum Weinen nöthigen, mit Unruhe und Bangigkeit, als wenn ihr Böses geschehen sollte; was sie nur ansieht, erfüllt sie mit Traurigkeit.||###||62606{#%ID%#}Grosse <cite style=\"background: #f7c77b;\">Unruhe</cite> im Schlafe, mit heftigem <cite style=\"background: #f7c77b;\">Weinen</cite> und trostlosem Jammern, ohne gehöriges Bewusstsein.||###||row62540{#%ID%#}Es kommen ihr immer traurige Gedanken in den Kopf, die sie zum Weinen nöthigen, mit Unruhe und Bangigkeit, als wenn ihr Böses geschehen sollte; was sie nur ansieht, erfüllt sie mit Traurigkeit666.","row62541{#%ID%#}Unwillkührliches Aechzen und Stöhnen, wie in großen Schmerzen, ohne daß er es selbst weiß.||###||62590{#%ID%#}Anfall von ängstlichem <cite style=\"background: #f7c77b;\">Stöhnen</cite> mit abwechselndem Lachkrampf und Weinen||###||row62541{#%ID%#}Unwillkührliches Aechzen und Stöhnen, wie in großen Schmerzen, ohne daß er es selbst weiß.6666"];
		//var pasted_symptoms_saved = ["row62540_62590","row62540_62606","row62543_62604"];
		//var pasted_edited_symptoms_saved = ["62590{#%ID%#}Anfall von ängstlichem Stöhnen mit abwechselndem Lachkrampf und <cite style=\"background: #f7c77b;\">Weinen</cite>.||###|| 62590{#%ID%#}<p>Anfall von ängstlichem Stöhnen mit abwechselndem Lachkrampf und Weinen 123.</p>||###||row62540{#%ID%#}Es kommen ihr immer traurige Gedanken in den Kopf, die sie zum Weinen nöthigen, mit Unruhe und Bangigkeit, als wenn ihr Böses geschehen sollte; was sie nur ansieht, erfüllt sie mit Traurigkeit.","62606{#%ID%#}Grosse <cite style=\"background: #f7c77b;\">Unruhe</cite> im Schlafe, mit heftigem <cite style=\"background: #f7c77b;\">Weinen</cite> und trostlosem Jammern, ohne gehöriges Bewusstsein.||###||62606{#%ID%#}<p>Grosse Unruhe im Schlafe, mit heftigem Weinen und trostlosem Jammern, ohne gehöriges Bewusstsein456.</p>||###||row62540{#%ID%#}Es kommen ihr immer traurige Gedanken in den Kopf, die sie zum Weinen nöthigen, mit Unruhe und Bangigkeit, als wenn ihr Böses geschehen sollte; was sie nur ansieht, erfüllt sie mit Traurigkeit.","62592{#%ID%#}<strong>Grosse Müdigkeit und Mattigkeit</strong>, besonders in den unteren Extremitäten, und Neigung zum Niederlegen, beim Liegen aber <cite style=\"background: #f7c77b;\">Unruhe</cite> in den Gliedern und Vermehrung der Mattigkeit.||###||62592{#%ID%#}<p><strong>Grosse Müdigkeit und Mattigkeit</strong>, besonders in den unteren Extremitäten, und Neigung zum Niederlegen, beim Liegen aber Unruhe in den Gliedern und Vermehrung der Mattigkeit789.</p>||###||row62545{#%ID%#}Bangigkeit mit vieler Unruhe, den ganzen Tag."];
		var swappedSymptoms = [<?php echo $savedSwap; ?>];
		var connected_symptoms_saved = [<?php echo $savedConnect; ?>];
		var connected_edited_symptoms_saved = [<?php echo $savedConnectEdit; ?>];
		var pasted_symptoms_saved = [<?php echo $savedPaste; ?>];
		var pasted_edited_symptoms_saved = [<?php echo $savedPasteEdit; ?>];
		
		//var swappedSymptoms = ["row62554###row62554_62586","row62541###row62541_62590"];
		//var initial_symptoms =[];
		//var comparative_symptoms =[];

		//Variables for Paste
		//var initial_symptoms_paste =[];
		//var comparative_symptoms_paste =[];
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
		var cutoff_percentage = 5;
		var initial_symptoms_original =[];
		var initial_symptoms_edited =[];
		var comparative_symptoms_connected =[];
		var ce_initials = {};
		var edited_initial;
		var ceComparativeId, ceInitialId;
		var initialSymptom, comparativeSymptom;
		var language, translation;
		var pass_through = 0;
		var swapped = false;
		//var swappedSymptoms = ["row62586||###||row62586_62554", "row62593||###||row62593_62548"];	//Array where all Swapped symptoms are saved
		
		var swapped_modal_initial, swapped_modal_comparative;

		$(document).ready(function(){
			$.fn.connectEditFunction();
			$.fn.pasteFunction();
			$.fn.connectFunction();
			$.fn.pasteEditFunction();

			/********Added on 7.9.21 @Jay***********/
			if(swappedSymptoms.length > 0)
			{
				swappedSymptoms.forEach((symptoms) => {
					$.fn.swapSymptom(symptoms);
				});
			}
			if(connected_symptoms_saved.length > 0)
			{
				connected_symptoms_saved.forEach((comparativeSymptomConnected) => {
					$.fn.connectSave(comparativeSymptomConnected);
				});
			}
			if(connected_edited_symptoms_saved.length > 0)
			{
				connected_edited_symptoms_saved.forEach((connectEdit) => {
					$.fn.connectEditSave(connectEdit);
				});
			}
			if(pasted_edited_symptoms_saved.length > 0)
			{
				pasted_edited_symptoms_saved.forEach((pasteEdit) => {
					$.fn.pasteEditSave(pasteEdit);
				});
			}
			if(pasted_symptoms_saved.length > 0)
			{
				pasted_symptoms_saved.forEach((symptoms) => {
					$.fn.pasteSave(symptoms);
				});
			}

			/********Added on 7.9.21 @Jay***********/

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
	});
	
	if($(this).find('i').hasClass('fa-plus'))
		$(this).find('i').removeClass('fa-plus').addClass('fa-minus');
	else
		$(this).find('i').removeClass('fa-minus').addClass('fa-plus');
});
/////////////////////////DELETE SWAPPED SYMPTOMS//////////////////////////
$.fn.deleteSwappedSymptoms = function(initialSymp, comparativeSymp){
	var item = initialSymp+"###"+comparativeSymp;
	var index = swappedSymptoms.indexOf(item);
	if (index !== -1) {
	  swappedSymptoms.splice(index, 1);
	}
}
/////////////////////////DELETE SYMPTOM WHEN DISCONNECT LINK IS CLICKED/////////////////////////
$.fn.deleteSymptoms = function(comparativeSymp){ 
	
    /*var item = comparativeSymp;
	var index = connected_symptoms.indexOf(item);
	if (index !== -1) {
	  connected_symptoms.splice(index, 1);
	}*/
    $.ajax({
				async:false,
				type: "POST",
		      	url: "connection-delete-script.php",
			    data: "type=connect&symptom="+comparativeSymp,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
    //console.log(comparativeSymp);
}
/////////////////////////DELETE PASTED SYMPTOM WHEN UPASTE IS CLICKED/////////////////////////
$.fn.deleteSymptomsPaste = function(comparativeSymp){ 
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-delete-script.php",
			    data: "type=paste&symptom="+comparativeSymp,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	//console.log(comparativeSymp);
}
/////////////////////////DELETE SYMPTOM WHEN DISCONNECT CE LINK IS CLICKED/////////////////////////
$.fn.deleteSymptomsCE = function(initialSymp, comparativeSymp){
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-delete-script.php",
			    data: "type=connect_edit&symptom="+initialSymp+"||###||"+comparativeSymp,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	var tmp_initial_symptoms = [];
	var tmp_comparative_symptoms = [];
	var tmp_initial_symptoms_edited = [];
	var j = 0;
    for(var i = 0; i < initial_symptoms_original.length; i++)
    {
    	var initial_symptoms_original_split = initial_symptoms_original[i].split("{#%ID%#}");
    	var comparative_symptoms_connected_split = comparative_symptoms_connected[i].split("{#%ID%#}");
    	var initial_symptoms_edited_split = initial_symptoms_edited[i].split("{#%ID%#}");
    	if(initial_symptoms_original_split[0] === initialSymp && comparative_symptoms_connected_split[0] === comparativeSymp)
    	{
    		$("." + initialSymp +"_"+comparativeSymp).find(".symptom").html(comparative_symptoms_connected_split[1]);
    		//Put back the original Initial Symptom
    		$('.' + initialSymp).each(function(){
    			//$(this).find(".symptom").text(initial_symptoms_original_split[1]);
    			$(this).find(".symptom").html(initial_symptoms_original_split[1]);
    		});
    		continue;
    	}
    	else
    	{
    		tmp_initial_symptoms[j] = initial_symptoms_original[i];
    		tmp_comparative_symptoms[j] = comparative_symptoms_connected[i];
    		tmp_initial_symptoms_edited[j] = initial_symptoms_edited[i];
    		j++;
    	}
    }
    
    initial_symptoms_original = [];
    comparative_symptoms_connected = [];
    initial_symptoms_edited = [];
    initial_symptoms_original = JSON.parse(JSON.stringify(tmp_initial_symptoms));
    comparative_symptoms_connected = JSON.parse(JSON.stringify(tmp_comparative_symptoms));
    initial_symptoms_edited = JSON.parse(JSON.stringify(tmp_initial_symptoms_edited));
    //console.log(initialSymp+", "+comparativeSymp);
}
/////////////////////////DELETE SYMPTOM WHEN DISCONNECT PE LINK IS CLICKED/////////////////////////
$.fn.deleteSymptomsPE = function(initialSymp, comparativeSymp){
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-delete-script.php",
			    data: "type=paste_edit&symptom="+initialSymp+"||###||"+comparativeSymp,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	var tmp_initial_symptoms = [];
	var tmp_comparative_symptoms = [];
	var tmp_comparative_symptoms_edited = [];
	var j = 0;
    for(var i = 0; i < comparative_symptoms_original_pe.length; i++)
    {
    	var comparative_symptoms_original_split = comparative_symptoms_original_pe[i].split("{#%ID%#}");
    	var comparative_symptoms_edited_split = comparative_symptoms_edited_pe[i].split("{#%ID%#}");
    	var initial_symptoms_connected_split = initial_symptoms_connected_pe[i].split("{#%ID%#}");
    	if(comparative_symptoms_original_split[0] === comparativeSymp && initial_symptoms_connected_split[0] === initialSymp)
    	{
    		//Put back the original Initial Symptom
    		//$('.' + initialSymp).each(function(){
    		//	$(this).find(".symptom").text(initial_symptoms_original_split[1]);
    		//});
    		continue;
    	}
    	else
    	{
    		tmp_initial_symptoms[j] = initial_symptoms_connected_pe[i];
    		tmp_comparative_symptoms[j] = comparative_symptoms_original_pe[i];
    		tmp_comparative_symptoms_edited[j] = comparative_symptoms_edited_pe[i];
    		j++;
    	}
    }
    
    initial_symptoms_connected_pe = [];
    comparative_symptoms_original_pe = [];
    comparative_symptoms_edited_pe = [];

    initial_symptoms_connected_pe = JSON.parse(JSON.stringify(tmp_initial_symptoms));
    comparative_symptoms_original_pe = JSON.parse(JSON.stringify(tmp_comparative_symptoms));
    comparative_symptoms_edited_pe = JSON.parse(JSON.stringify(tmp_comparative_symptoms_edited));
    //console.log(initialSymp+", "+comparativeSymp);
}
$.fn.bringBackOriginalPercentages = function(initialParent, initialId, comparativeId)
{
	if(initialId in ce_initials)
		{
			initialParent.not(".initialsConnectedCE .initial").nextUntil(".initial").each(function()
			{
				if($(this).hasClass(comparativeId))
				$(this).removeClass("hidden");

				if($(this).hasClass("comparing"))
				{
					$(this).find(".percentage").text(ce_initials[initialId][$(this).attr("id")]);
					//console.log(ce_initials[sectionInitialId][$(this).attr("id")]);
				}
				if($(this).hasClass("comparativesConnectedCD"))
				{
					$(this).find(".comparing").each(function(){
						$(this).find(".percentage").text(ce_initials[initialId][$(this).attr("id")]);
					});
				}
			});
		}
}
/////////////////////////SAVING WITH AJAX/////////////////////////
$.fn.saveConnects = function(symptom, initialHtml)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-save-script.php",
			    data: "type=connect&symptom="+symptom+"&initialHtml="+initialHtml,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	//console.log("type=connect&symptom="+symptom+"&initialHtml="+initialHtml);
}
$.fn.savePastes = function(symptom, initialHtml)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-save-script.php",
			    data: "type=paste&symptom="+symptom+"&initialHtml="+initialHtml,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
			//console.log("Pastes: "+symptom);
			//console.log("Initial: "+initialHtml);
}
$.fn.saveConnectEdits = function(initialSymptomOriginal, comparativeSymptom, initialSymptomEdited, initialHtml, percentage)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-save-script.php",
			    data: "type=connect_edit&symptom="+initialSymptomOriginal+"||###||"+comparativeSymptom+"||###||"+initialSymptomEdited+"&initialHtml="+initialHtml+"&percentage="+percentage,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	/*console.log(initialSymptomOriginal+"||###||"+comparativeSymptom+"||###||"+initialSymptomEdited);
	console.log(initialHtml);
	console.log(percentage);*/
}
$.fn.savePasteEdits = function(comparativeSymptomOriginal, comparativeSymptomEdited, initialSymptom, initialHtml)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-save-script.php",
			    data: "type=paste_edit&symptom="+comparativeSymptomOriginal+"||###||"+comparativeSymptomEdited+"||###||"+initialSymptom+"&initialHtml="+initialHtml,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	//console.log("PE: "+comparativeSymptomOriginal+"||###||"+comparativeSymptomEdited+"||###||"+initialSymptom);
	//console.log("Initial: "+initialHtml);
}
$.fn.swapSymptoms = function(symptom)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-save-script.php",
			    data: "type=swap&symptom="+symptom,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	//console.log("Swapped: "+symptom);
}
$.fn.unswapSymptoms = function(symptom)
{
	$.ajax({
				async:false,
				type: "POST",
		      	url: "connection-delete-script.php",
			    data: "type=swap&symptom="+symptom,
			    dataType: "JSON",
			    success: function(returnedData){
		    		//console.log(returnedData);
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
	//console.log("Unswapped: "+symptom);
}
////////////////////////////END SAVING///////////////////////////
//Connectedit button click
$(document).on('click', '.symptom-connect-edit-btn', function(e){
	var hasConnections = false;
	var ce_allowed = true;
	var msg;
	//Clearing the Paste and PE array before any CE
	var comparatives_connected_paste = [];
	var comparatives_connected_pe = [];

	var initial_year = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-year");
	var comparative_year = $(this).parents('div.comparing').attr("data-year");
	language = $("#hidden_comparison_language").val();

	if(parseInt(comparative_year) < parseInt(initial_year))
	{
		if(confirm("The Comparative Symptom is Older than the Initial Symptom."+"\n"+"Would you like to Swap and Connect Edit?"))
		{
			$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
				if($(this).hasClass("initialsConnectedCD"))
				{
					hasConnections = true;
					msg = "You cannot do a Swap & Connect Edit, because this Symptom alread has a former Connection";
				}
				if($(this).hasClass("initialsConnectedCE"))
				{
					hasConnections = true;
					msg = "You cannot do a Swap & Connect Edit, because this Symptom alread has a former Connect Edit";
				}
			});
			if(hasConnections)
				alert(msg);
			//The Initial Symtpom already has Connections i.e., comparativesConnected, so not allowed to Swap
			else if($(this).parents('div.comparing').prevAll(".initial").first().hasClass("swap"))
			{
				alert("This Initial Symptom already has Connections or Pastes"+"\n"+"Please Disconnect or Unpaste before Swapping");
			}
			//The Initial Symtpom has no prior connections i.e., No comparativesConnected, hence allowed to Swap
			//Once swapped, this initial Symptom will be treated as an Initial.
			else
			{
				//Before Swap we check if there are any previous Paste or PE. If present, then we will first Unpaste them
				//Then we will again paste them with the new initial symptom
				$(this).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
					if($(this).hasClass("comparativesConnectedPASTE"))
					{
						$(this).children(".comparing").each(function(){
							comparatives_connected_paste.push($(this).attr("id"));
							$(this).find(".disconnectPaste").click();
						});
					}
					if($(this).hasClass("comparativesConnectedPE"))
					{
						$(this).children(".comparing").each(function(){
							comparatives_connected_pe.push($(this).attr("id"));
							//$(this).find(".disconnectPE").click();
						});
					}
				});
				/////////////BEGIN SWAP//////////////
					var tmp_str;
					var ids = [];
					var tmp_array = [];
					var nameArray = [];
					var comparativeIds = [];
					var matching = [];
					swapped = true;
					swapped_modal_initial = $(this).parents('div.comparing').prevAll(".initial").first().attr("id");
					swapped_modal_comparative = $(this).parents('div.comparing').attr("id");
					//We extract the encoded Initial Symptom
					if(language == 'de')
					{
						var initialSymptomEncoded = $(this).parents('div.comparing').prevAll(".initial").first().attr('data-initial-symptom-de');
						var comparativeSymptomEncoded = $(this).parents('div.comparing').attr('data-comparing-symptom-de');
						//Replacing the encoded symptoms
						var tmpElementEncoded = comparativeSymptomEncoded;
						$(this).parents('div.comparing').attr('data-comparing-symptom-de', initialSymptomEncoded);
						$(this).parents('div.comparing').prevAll(".initial").first().attr('data-initial-symptom-de', tmpElementEncoded);
					}
					else if(language == 'en')
					{
						var initialSymptomEncoded = $(this).parents('div.comparing').prevAll(".initial").first().attr('data-initial-symptom-en');
						var comparativeSymptomEncoded = $(this).parents('div.comparing').attr('data-comparing-symptom-en');
						//Replacing the encoded symptoms
						var tmpElementEncoded = comparativeSymptomEncoded;
						$(this).parents('div.comparing').attr('data-comparing-symptom-en', initialSymptomEncoded);
						$(this).parents('div.comparing').prevAll(".initial").first().attr('data-initial-symptom-en', tmpElementEncoded);
					}
					//We start doing the SWAP below
					//For this we are only interchanging the Symptoms, Source-Codes and Modify the Id's accordingly
					//Then we will send and Ajax request with the Initial Symptom and all the Comparative Symptoms
					var initialElementSymptom = $(this).parents('div.comparing').prevAll(".initial").first().find(".symptom").html();
					var initialElementId = $(this).parents('div.comparing').prevAll(".initial").first().attr('id');
					initialElementId = initialElementId.replace("row", "");

					var comparativeElementSymptom = $(this).parents('div.comparing').find(".symptom").html();
					var comparativeElementId = $(this).parents('div.comparing').attr('id');
					var initialElementSourceCode = $(this).parents('div.comparing').prevAll(".initial").first().find(".source-code").html();
					var comparativeElementSourceCode = $(this).parents('div.comparing').find(".source-code").html();
					//Swapping/ Interchanging the year
					var comparativeElementYear = $(this).parents('div.comparing').attr("data-year");
					var initialElementYear = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-year");
					var tmpElementYear = comparativeElementYear;
					$(this).parents('div.comparing').attr("data-year", initialElementYear);
					$(this).parents('div.comparing').prevAll(".initial").first().attr("data-year", tmpElementYear);
					var tmpElementSymptom = comparativeElementSymptom;
					tmpElementSymptom = tmpElementSymptom.replace(/<cite style="background: #f7c77b;">/g, '');
					tmpElementSymptom = tmpElementSymptom.replace(/<\/cite>/g, '');
					var tmpElementSourceCode = comparativeElementSourceCode
					var tmpElementId = comparativeElementId;
					var ids = tmpElementId.split("_");
					var initial_part = ids[0];
					var second_part = ids[1];

					//Interchanging the values of Initial and Comparative
					$(this).parents('div.comparing').attr("id", "row"+second_part+"_"+initialElementId);
					//Replace this Comparative Symptom class that has same id with the new Class/ id
					$(this).parents('div.comparing').attr("class", "row"+second_part+"_"+initialElementId+" symptom-row comparing");
					$(this).parents('div.comparing').find(".symptom").html(initialElementSymptom);
					$(this).parents('div.comparing').prevAll(".initial").first().attr('id', "row"+second_part);
					$(this).parents('div.comparing').prevAll(".initial").first().attr('class', "row"+second_part+" symptom-row initial");

					//Replace Symptom & Source Code
					$(this).parents('div.comparing').prevAll(".initial").first().find(".symptom").html(tmpElementSymptom);
					$(this).parents('div.comparing').find(".source-code").html(initialElementSourceCode);
					$(this).parents('div.comparing').prevAll(".initial").first().find(".source-code").html(tmpElementSourceCode);

					$(".row"+second_part).nextUntil(".initial").each(function()
					{
						
						var idVal = $(this).attr("id");
						if (typeof idVal !== 'undefined' && idVal !== false)
						{
							comparativeIds = idVal.split("_");
							var newId = "row"+second_part+"_"+comparativeIds[1];
							//Now we start changing the ids of each Comparative symptom
							$(this).attr("id", newId);
							$(this).attr("class", newId+" symptom-row comparing");
							//Here again we extract the Encoded Comparative Symptom
							if(language == 'de')
								var symp = $(this).attr('data-comparing-symptom-de');
							else if(language == 'en')
								var symp = $(this).attr('data-comparing-symptom-en');
							tmp_array.push($(this).attr("id")+"{#%ID%#}"+b64DecodeUnicode(symp));//Creates an array that we would send as Ajax post
							tmp_str = tmp_array.join("{#%%#}");//Converts the array to the desired String format
						//console.log("initial_symptom="+"row"+second_part+"{#%ID%#}"+comparativeSymptomEncoded+"&comparative_symptom="+tmp_str);
						}
					});
					//Add a class claed Swap in both, indicating that they have been swaped
						$(this).parents('div.comparing').addClass("swap");
						$(this).parents('div.comparing').prevAll(".initial").first().addClass("swap");
					$.ajax({
								async:false,
								type: "POST",
						      	url: "compare-for-swap.php",
							    data: "initial_symptom="+"row"+second_part+"{#%ID%#}"+b64DecodeUnicode(comparativeSymptomEncoded)+"&comparative_symptom="+tmp_str,
							    dataType: "JSON",
							    success: function(returnedData){
							    $.each(returnedData, function( key, value ) {
					    		nameArray.push({id:value.comparing_symptom_id, symptom:value.comparing_symptom_highlighted, percentage: value.percentage});
			        			});
							    },
							    error: function(xhr, textStatus, error){
								    console.log(xhr.statusText);
								    console.log(textStatus);
								    console.log(error);
								}
							});
					//Now populate the comparatives with the new text and percentages
					//console.log(nameArray[0]['symptom']);
					$.each(nameArray, function(key, value)
					{
						$("."+value.id).find(".symptom").html(value.symptom);
						$("."+value.id).find(".percentage").text(value.percentage+"%");
					});
					$.fn.swapSymptoms("row"+second_part + "||###||" + "row"+second_part+"_"+initialElementId);
					/////////////END SWAP//////////////
					////////////NOW WE CONNECT//////////////
					//alert(".row"+second_part+"_"+initialElementId);
					//Add back any comparativesConnectedPASTE or comparativesConnectedPE if present
					if(comparatives_connected_paste.length > 0)
					$.each(comparatives_connected_paste, function(key, value){
						var comparativePart = value.split("_");
						var newId = "row"+second_part+"_"+comparativePart[1];
						$(".row"+second_part+".initial").nextAll("."+newId).first().find(".symptom-paste-btn").click();
						//$(".row"+second_part+".initial").nextAll("."+newId).first().addClass("hidden");
					});
					if(comparatives_connected_pe.length > 0)
					$.each(comparatives_connected_pe, function(key, value){
						var comparativePart = value.split("_");
						var newId = "row"+second_part+"_"+comparativePart[1];
						$(".row"+second_part+".initial").nextAll("."+newId).first().addClass("hidden");
					});
					$(".row"+second_part+"_"+initialElementId).find(".symptom-connect-edit-btn").click();
					///////////END CONNECT/////////////
					//Next we have to show the Swap icon for every Comparative Symptom (which now became initial)
					$.fn.swapIconsCE(second_part);
			}
		}
		else
		{
			return false;
		}
	}
	//End if Comparative Symptom year is older than Initial Symptom year
	//Begin Connect Edit
	else
	{
			
			var uniqueId = $(this).parents('div.comparing').attr('id');
			var initial_source_symptom_de = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-de');
			var initial_source_symptom_en = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-en');
			var comparing_source_symptom_de = $("#"+uniqueId).attr('data-comparing-symptom-de');
			var comparing_source_symptom_en = $("#"+uniqueId).attr('data-comparing-symptom-en');
			var hidden_comparison_language = $("#hidden_comparison_language").val();

			var decoded_initial_source_symptom_de = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
			var decoded_initial_source_symptom_en = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";	
			var decoded_comparing_source_symptom_de = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
			var decoded_comparing_source_symptom_en = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
			
			$("#connect_edit_modal_loader .loading-msg").removeClass('hidden');
			$("#connect_edit_modal_loader .error-msg").html('');
			if($("#connect_edit_modal_loader").hasClass('hidden'))
				$("#connect_edit_modal_loader").removeClass('hidden');
			$("#connect_edit_symptom_de_container").addClass('hidden');
			$("#connect_edit_symptom_en_container").addClass('hidden');
			$("#connect_edit_submit_btn").addClass('hidden');
			$("#populated_connect_edit_modal_data").remove();

			var html = '';
			html += '<div id="populated_connect_edit_modal_data">';
			html += '	<div class="row">';
			html += '		<div class="col-sm-12"><div class="spacer"></div><p><b>Symptoms that are in action</b></p></div>';
			html += '		<div class="col-sm-12">';
			// html += '			<div class="spacer"></div>';
			html += '			<table id="resultTable" class="table table-bordered">';
			html += '				<thead class="heading-table-bg">';
			html += '					<tr>';
			html += '						<th style="width: 5%;">#</th>';
			html += '						<th style="width: 48%;">Initial Symptom</th>';
			html += '						<th>Comparative Symptom</th>';
			html += '					</tr>';
			html += '				</thead>';
			html += '				<tbody>';
			html += '					<tr>';
			html += '						<th>DE</th>';
			html += '						<td>'+decoded_initial_source_symptom_de+'</td>';
			html += '						<td>'+decoded_comparing_source_symptom_de+'</td>';
			html += '					</tr>';
			html += '					<tr>';
			html += '						<th>EN</th>';
			html += '						<td>'+decoded_initial_source_symptom_en+'</td>';
			html += '						<td>'+decoded_comparing_source_symptom_en+'</td>';
			html += '					</tr>';
			html += '				</tbody>';
			html += '			</table>';
			html += '		</div>';
			html += '	</div>';
			html += '	<div class="row">';
			html += '		<div class="col-sm-12"><p class="common-error-text text-danger"></p></div>';
			html += '	</div>';
			html += '</div>';


		/*if($(this).parents('div.comparing').next(".initialsConnectedCE").length > 0){
			$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
			$("<div>This Comparative Symptom already has a Connect Edit. Please Disconnect.</div>").dialog();
		}*/
		$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
				if($(this).hasClass("initialsConnectedCE"))
				{
					ce_allowed = false;
					$("<div>This Comparative Symptom already has a Connect Edit. Please Disconnect.</div>").dialog();
				}
				else if($(this).hasClass("initialsConnectedPE"))
				{
					ce_allowed = false;
					$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
				}
				else if($(this).hasClass("initialsConnectedPASTE"))
				{
					ce_allowed = false;
					$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
				}
		});
		/*else if($(this).parents('div.comparing').next(".initialsConnectedPE").length > 0){
			$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
		}*/
		/*$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
				if($(this).hasClass("initialsConnectedPE"))
					$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
		});*/
		//A Comparative Symptom that has been pasted with Initial cannot have any kind of connection (C, CE, P, PE, SWAP) thereafter
		/*else if($(this).parents('div.comparing').next(".initialsConnectedPASTE").length > 0){
			$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
			$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
		}*/
		/*$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
				if($(this).hasClass("initialsConnectedPASTE"))
					$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
		});*/
		if(ce_allowed == true)
		{
			var thisId = $(this).parents('div.comparing').attr('id');
			var thisIdArray = thisId.split("_");
			ceComparativeId = thisIdArray[1];
			ceInitialId = thisIdArray[0];
			
			//Cannot CE again in the same section
			if(initial_symptoms_original.some(res => res.includes(ceInitialId+"{#%ID%#}")) === true){
				$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
				//$("#connect_edit_modal_loader .error-msg").html('YOU CANNOT CE AGAIN IN THIS SECTION UNLESS YOU DISCONNECT THE FIRST CE.');
				$("<div>You cannot CE again in this section unless you disconnect the first CE.</div>").dialog();
			}
			else
			{
				//initialSymptom = $('#' + ceInitialId).find(".symptom").text();
				//comparativeSymptom = $("."+thisId).find(".symptom").text();
				initialSymptom = $('#' + ceInitialId).find(".symptom").html();
				comparativeSymptom = $("."+thisId).find(".symptom").html();

				$("#fv_symptom_de").val(decoded_initial_source_symptom_de);
				$("#fv_symptom_en").val(decoded_initial_source_symptom_en);
				$("#connect_edit_modal_container").append(html);
				$("#connectEditModal").modal('show');
				if(!$("#connect_edit_modal_loader").hasClass('hidden'))
					$("#connect_edit_modal_loader").addClass('hidden');
				if($("#connect_edit_symptom_de_container").hasClass('hidden'))
					$("#connect_edit_symptom_de_container").removeClass('hidden');
				if($("#connect_edit_symptom_en_container").hasClass('hidden'))
					$("#connect_edit_symptom_en_container").removeClass('hidden');
				if($("#connect_edit_submit_btn").hasClass('hidden'))
					$("#connect_edit_submit_btn").removeClass('hidden');
			}
		}
	}
});
$(document).on('click', '.btn-default', function(e){
	if(swapped)
	{
		language = $("#hidden_comparison_language").val();
		var new_id, tmp_str;
		var tmp_array = [];
		var nameArray = [];
		var comparatives_connected_paste = [];
		var comparatives_connected_pe = [];

		var comparative_array = swapped_modal_comparative.split("_");
		var swappedComparativeId = "row"+comparative_array[1]+"_"+comparative_array[0].replace("row", "");
		var initialId = "row"+comparative_array[1];
		//Before Swap we check if there are any previous Paste or PE. If present, then we will first Unpaste them
		//Then we will again paste them with the new initial symptom - later down
		$("."+initialId+".swap").nextUntil(".initial").each(function(){
			if($(this).hasClass("comparativesConnectedPASTE"))
			{
				$(this).children(".comparing").each(function(){
					comparatives_connected_paste.push($(this).attr("id"));
					$(this).find(".disconnectPaste").click();
				});
			}
			if($(this).hasClass("comparativesConnectedPE"))
			{
				$(this).children(".comparing").each(function(){
					comparatives_connected_pe.push($(this).attr("id"));
				});
			}
		});
		//Now we do the Unswap
		$.fn.unswapSymptoms(initialId+"||###||"+swappedComparativeId);
		//Swap Symptom
		var comparativeSymptom_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().find(".symptom").html();
		comparativeSymptom_swap = comparativeSymptom_swap.replace(/<cite style="background: #f7c77b;">/g, '');
		comparativeSymptom_swap = comparativeSymptom_swap.replace(/<\/cite>/g, '');
		var initialSymptom_swap = $("."+initialId+".swap").find(".symptom").html();
		var tmp_symptom_swap = comparativeSymptom_swap;
		$("."+initialId+".swap").nextAll("."+swappedComparativeId).first().find(".symptom").html(initialSymptom_swap);
		$("."+initialId+".swap").find(".symptom").html(tmp_symptom_swap);

		//Swap Source Code
		var comparativeSourceCode_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().find(".source-code").html();
		var initialSourceCode_swap = $("."+initialId+".swap").find(".source-code").html();
		var tmp_source_code_swap = comparativeSourceCode_swap;
		$("."+initialId+".swap").nextAll("."+swappedComparativeId).first().find(".source-code").html(initialSourceCode_swap);
		$("."+initialId+".swap").find(".source-code").html(tmp_source_code_swap);

		//Swap Data Year
		var comparativeDataYear_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-year");
		var initialDataYear_swap = $("."+initialId+".swap").attr("data-year");
		var tmp_data_year_swap = comparativeDataYear_swap;
		$("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-year",initialDataYear_swap);
		$("."+initialId+".swap").attr("data-year",tmp_data_year_swap);

		//Swap Encoded Data
		if(language == 'de')
		{
			var comparativeSymptomEncoded_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-comparing-symptom-de");
			var initialSymptomEncoded_swap = $("."+initialId+".swap").attr('data-initial-symptom-de');
			//Replacing the encoded symptoms
			tmp_encoded_swap = comparativeSymptomEncoded_swap;
			$("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-comparing-symptom-de", initialSymptomEncoded_swap);
			$("."+initialId+".swap").attr('data-initial-symptom-de', tmp_encoded_swap);
		}
		else if(language == 'en')
		{
			var comparativeSymptomEncoded_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-comparing-symptom-en");
			var initialSymptomEncoded_swap = $("."+initialId+".swap").attr('data-initial-symptom-en');
			//Replacing the encoded symptoms
			tmp_encoded_swap = comparativeSymptomEncoded_swap;
			$("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("data-comparing-symptom-en", initialSymptomEncoded_swap);
			$("."+initialId+".swap").attr('data-initial-symptom-en', tmp_encoded_swap);
		}

		//Swap Id & Class
		var comparativeId_swap = $("."+initialId+".swap").nextAll("."+swappedComparativeId).first().attr("id");
		var initialId_swap = $("."+initialId+".swap").attr("id");
		var ids_swap = comparativeId_swap.split("_");
		var new_initial_id_swap = "row"+ids_swap[1];
		var new_comparative_id_swap = new_initial_id_swap+"_"+ids_swap[0].replace("row", "");

		$("."+initialId+".swap").attr("id",new_initial_id_swap);
		$("."+initialId+".swap").attr("class","symptom-row initial "+new_initial_id_swap);
		//End for Unswap
		//Now we have to change the Ids of all Comparative Symptoms in this section
		$("."+new_initial_id_swap).nextUntil(".initial").each(function()
		{
			var idVal = $(this).attr("id");
			if (typeof idVal !== 'undefined' && idVal !== false)
			{
				comparativeIds_swap = idVal.split("_");
				if($(this).hasClass("swap"))
				{
					new_id = new_comparative_id_swap;
				}
				else
					new_id = new_initial_id_swap+"_"+comparativeIds_swap[1];

				//Here again we extract the Encoded Comparative Symptom
				if(language == 'de')
					var symp = $(this).attr('data-comparing-symptom-de');
				else if(language == 'en')
					var symp = $(this).attr('data-comparing-symptom-en');

				//Now we start changing the ids of each Comparative symptom
				$(this).attr("id", new_id);
				$(this).attr("class", new_id+" symptom-row comparing");
			
				tmp_array.push(new_id+"{#%ID%#}"+b64DecodeUnicode(symp));
				
				tmp_str = tmp_array.join("{#%%#}");//Converts the array to the desired String format
				//console.log("initial_symptom="+new_initial_id_swap+"{#%ID%#}"+b64DecodeUnicode(tmp_encoded_swap)+"&comparative_symptom="+tmp_str);
			}
		});
		//Now do the Ajax call for text highlighting and percentage
		$.ajax({
					async:false,
					type: "POST",
			      	url: "compare-for-swap.php",
				    data: "initial_symptom="+new_initial_id_swap+"{#%ID%#}"+b64DecodeUnicode(tmp_encoded_swap)+"&comparative_symptom="+tmp_str,
				    dataType: "JSON",
				    success: function(returnedData){
				    $.each(returnedData, function( key, value ) {
		    		nameArray.push({id:value.comparing_symptom_id, symptom:value.comparing_symptom_highlighted, percentage: value.percentage});
        			});
				    },
				    error: function(xhr, textStatus, error){
					    console.log(xhr.statusText);
					    console.log(textStatus);
					    console.log(error);
					}
				});
		$.each(nameArray, function(key, value)
				{
					$("."+value.id).find(".symptom").html(value.symptom);
					$("."+value.id).find(".percentage").text(value.percentage+"%");
				});
		//Now we will bring back any Pasted or Paste Edit Symptoms
		if(comparatives_connected_paste.length > 0)
		{
			$.each(comparatives_connected_paste, function(key, value){
				var newComparative = value.split("_");
				$("."+new_initial_id_swap+".initial").nextAll("."+new_initial_id_swap+"_"+newComparative[1]).first().find(".symptom-paste-btn").click();
				//$("."+new_initial_id_swap+".initial").nextAll("."+new_initial_id_swap+"_"+newComparative[1]).first().addClass("hidden");
			});
		}
		if(comparatives_connected_pe.length > 0)
		{
			$.each(comparatives_connected_pe, function(key, value){
				var newComparative = value.split("_");
				//$("."+new_initial_id_swap+".initial").nextAll("."+new_initial_id_swap+"_"+newComparative[1]).first().find(".symptom-paste-edit-btn").click();
				$("."+new_initial_id_swap+".initial").nextAll("."+new_initial_id_swap+"_"+newComparative[1]).first().addClass("hidden");
			});
		}
	}
	else
	{
		return false;
	}
});
			//Paste edit button click
			$(document).on('click', '.symptom-paste-edit-btn', function(e){
				var pe_allowed = true;
				var uniqueId = $(this).parents('div.comparing').attr('id');
				var initial_source_symptom_de = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-de');
				var initial_source_symptom_en = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-en');
				var comparing_source_symptom_de = $("#"+uniqueId).attr('data-comparing-symptom-de');
				var comparing_source_symptom_en = $("#"+uniqueId).attr('data-comparing-symptom-en');
				var hidden_comparison_language = $("#hidden_comparison_language").val();

				var decoded_initial_source_symptom_de = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
				var decoded_initial_source_symptom_en = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";	
				var decoded_comparing_source_symptom_de = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
				var decoded_comparing_source_symptom_en = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
				
				$("#paste_edit_modal_loader .loading-msg").removeClass('hidden');
				$("#paste_edit_modal_loader .error-msg").html('');
				if($("#paste_edit_modal_loader").hasClass('hidden'))
					$("#paste_edit_modal_loader").removeClass('hidden');
				$("#paste_edit_symptom_de_container").addClass('hidden');
				$("#paste_edit_symptom_en_container").addClass('hidden');
				$("#paste_edit_submit_btn").addClass('hidden');
				$("#populated_paste_edit_modal_data").remove();

				var html = '';
				html += '<div id="populated_paste_edit_modal_data">';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><div class="spacer"></div><p><b>Symptoms that are in action</b></p></div>';
				html += '		<div class="col-sm-12">';
				// html += '			<div class="spacer"></div>';
				html += '			<table id="resultTable" class="table table-bordered">';
				html += '				<thead class="heading-table-bg">';
				html += '					<tr>';
				html += '						<th style="width: 5%;">#</th>';
				html += '						<th style="width: 48%;">Initial Symptom</th>';
				html += '						<th>Comparative Symptom</th>';
				html += '					</tr>';
				html += '				</thead>';
				html += '				<tbody>';
				html += '					<tr>';
				html += '						<th>DE</th>';
				html += '						<td>'+decoded_initial_source_symptom_de+'</td>';
				html += '						<td>'+decoded_comparing_source_symptom_de+'</td>';
				html += '					</tr>';
				html += '					<tr>';
				html += '						<th>EN</th>';
				html += '						<td>'+decoded_initial_source_symptom_en+'</td>';
				html += '						<td>'+decoded_comparing_source_symptom_en+'</td>';
				html += '					</tr>';
				html += '				</tbody>';
				html += '			</table>';
				html += '		</div>';
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><p class="common-error-text text-danger"></p></div>';
				html += '	</div>';
				html += '</div>';

				

				//Cannot PE same comparative symptom anywhere in the comparison
				/*if(comparative_symptoms_original_pe.some(pes => pes.includes(peComparativeId+"{#%ID%#}")) === true)
					$("<div>This comparative symptom already has a Paste Edit.<br /> Please unpaste first 00xxx.</div>").dialog();
				else if($(this).parents('div.comparing').next(".initialsConnectedPASTE").length > 0)
					$("<div>This comparative symptom already has a Paste.<br /> Please unpaste first 001.</div>").dialog();
				else if($(this).parents('div.comparing').next(".initialsConnectedCD").length > 0)
					$("<div>This comparative symptom already has a connection.<br /> Please disconnect first 002.</div>").dialog();
				else if($(this).parents('div.comparing').next(".initialsConnectedCE").length > 0)
					$("<div>This comparative symptom already has a connect edit.<br /> Please disconnect first 003.</div>").dialog();
				*/
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if($(this).hasClass("initialsConnectedCE"))
					{
						pe_allowed = false;
						$("<div>This Comparative Symptom already has a Connect Edit. Please Disconnect.</div>").dialog();
					}
					else if($(this).hasClass("initialsConnectedPE"))
					{
						pe_allowed = false;
						$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
					}
					else if($(this).hasClass("initialsConnectedPASTE"))
					{
						pe_allowed = false;
						$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
					}
					else if($(this).hasClass("initialsConnectedCD"))
					{
						pe_allowed = false;
						$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
					}
				});
				if(pe_allowed == true)
				{
					var thisId = $(this).parents('div.comparing').attr('id');
					var thisIdArray = thisId.split("_");
					peComparativeId = thisIdArray[1];
					peInitialId = thisIdArray[0];
					language = hidden_comparison_language;
				//else{
					initialSymptomPE = $('#' + peInitialId).find(".symptom").html();
					comparativeSymptomPE = $("."+thisId).find(".symptom").html();

					$("#fv_pe_symptom_de").val(decoded_comparing_source_symptom_de);
					$("#fv_pe_symptom_en").val(decoded_comparing_source_symptom_en);
					$("#paste_edit_modal_container").append(html);
					$("#pasteEditModal").modal('show');
					if(!$("#paste_edit_modal_loader").hasClass('hidden'))
						$("#paste_edit_modal_loader").addClass('hidden');
					if($("#paste_edit_symptom_de_container").hasClass('hidden'))
						$("#paste_edit_symptom_de_container").removeClass('hidden');
					if($("#paste_edit_symptom_en_container").hasClass('hidden'))
						$("#paste_edit_symptom_en_container").removeClass('hidden');
					if($("#paste_edit_submit_btn").hasClass('hidden'))
						$("#paste_edit_submit_btn").removeClass('hidden');
				}
			});

		//Non secure connection starts
		var non_secure_connect_string = [<?php echo $nonSecureConnect; ?>];
		//non secure connection active icon load
		if(non_secure_connect_string.length > 0)
		{
			non_secure_connect_string.forEach((nonSecureConnected) => {
				$.fn.nonSecureConnectFn(nonSecureConnected);
			});
		}
		//Non secure connection ends

		//Non secure paste starts
		var non_secure_connect_string_paste = [<?php echo $nonSecureConnectPaste; ?>];
		//non secure connection active icon load
		if(non_secure_connect_string_paste.length > 0)
		{
			non_secure_connect_string_paste.forEach((nonSecureConnectedPaste) => {
				$.fn.nonSecureConnectPasteFn(nonSecureConnectedPaste);
			});
		}
		//Non secure paste ends

		//Comments on load function starts
		var comments_on_load = [<?php echo $commentsOnLoad; ?>];
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
		//non secure connection active icon load
		if(footnote_on_load.length > 0)
		{
			footnote_on_load.forEach((footnoteLoad) => {
				$.fn.footnoteOnLoadFn(footnoteLoad);
			});
		}
		//Footnote on load function ends

		});//Emd document ready function
	</script>
</body>
</html>