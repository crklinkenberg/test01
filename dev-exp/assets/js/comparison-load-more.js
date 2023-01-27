$(window).bind("load", function() {
	console.log('loaded');
	$("#loader").addClass("hidden");
	$("#comparison_container").removeClass('unclickable');
	$("#search_container").removeClass('unclickable');
	var records = $("#totalNumofRecords").val();
	if(records != ""){
		$("#numberOfRecord").html(records);
		$("#totalNumberDisplay").removeClass('hidden');
	}

	var scId = $("#scid").val();
	if(scId != ""){
		$("#compare_submit_btn").click();
	}
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
					htmlComparisonsInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
					comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
				} else {
					htmlSingleSourcesInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
					comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
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
			// console.log(responseData);
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
				// var comSelected = (split_saved_comparing_source_ids.indexOf(value.quelle_id) !== -1) ? 'selected' : '';
				var comSelected = '';
				if(value.quelle_type_id == 3){
					comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
				} else {
					comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de="'+value.is_symptoms_available_in_de+'" data-is-symptoms-available-in-en="'+value.is_symptoms_available_in_en+'" data-quelle-code="'+value.quelle_code+'" data-year="'+value.year+'" '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
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

$(document).on('click', '.vbtn-has-connection-make-it-not-work', function(){
	var $th = $(this);
	if($th.hasClass('processing'))
		return;
	$th.addClass('processing');
    var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

    var comparisonInitialSourceId = $(this).attr("data-comparison-initial-source-id");
    var sourceArzneiId = $(this).attr("data-source-arznei-id");
    var parentUniqueId = $(this).attr("data-unique-id");
    var initialSymptomId = $(this).attr("data-initial-symptom-id");
    var comparingSymptomId = $(this).attr("data-comparing-symptom-id");
    var activeSymptomType = $(this).attr("data-active-symptom-type");
    var comparingSourceIds = $(this).attr("data-comparing-source-ids");
    var isConnectionLoaded = $(this).attr("data-is-connection-loaded");
    var removableRowClassChain = $(this).attr("data-removable-row-class-chain");
    var vPadding = $(this).attr("data-v-padding");
    var isRecompare = parseInt($(this).attr("data-is-recompare"));
    var initialSourceId = $("#initial_source_id_"+parentUniqueId).val();
    var comparingSourceId = $("#comparing_source_id_"+parentUniqueId).val();
    var savedComparisonComparingSourceIds = $(this).attr("data-saved-comparison-comparing-source-ids");
    var mainParentInitialSymptomId = $(this).attr("data-main-parent-initial-symptom-id");
    var connectionsMainParentSymptomId = $(this).attr("data-connections-main-parent-symptom-id");
    var matched_symptom_ids_string = $("#matched_symptom_ids_"+parentUniqueId).val();
    var similarity_rate = $("#similarity_rate_"+parentUniqueId).val();
    var comparison_option = $("#comparison_option_"+parentUniqueId).val();
    var is_unmatched_symptom = $("#is_unmatched_symptom_"+parentUniqueId).val();
    var individual_comparison_language = $("#individual_comparison_language_"+parentUniqueId).val();

    var rowClass = "removable-"+parentUniqueId;
	removableRowClassChain += rowClass+' ';
    if(isConnectionLoaded == 1){
    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 0);
    	$("#v_btn_"+parentUniqueId).html('<i class="fas fa-plus"></i>');
    	$("#v_btn_"+parentUniqueId).promise().done(function(){
    		$('#connection_loder_'+parentUniqueId).remove();
	    	$(".removable-"+parentUniqueId).remove();
	    	$th.removeClass('processing');

	    	// Removing the connection check box checked if there is no open connection
	    	var totConn = $( ".vbtn-has-connection" ).length; 
	    	var closedConnCount = 0;
	    	$( ".vbtn-has-connection" ).each(function() {
				var checkIsConnectionLoaded = $(this).attr("data-is-connection-loaded");
				if(checkIsConnectionLoaded != 1)
					closedConnCount++;
			})
			if(parseInt(totConn) == parseInt(closedConnCount))
				$("#show_all_connections").prop("checked", false);
    	})	    	
    }else{
    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
		$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
		$("#v_btn_"+parentUniqueId).promise().done(function(){
			$(".removable-"+parentUniqueId).remove();
			$('#connection_loder_'+parentUniqueId).remove();
	    	var loadingHtml = '';
	    	loadingHtml += '<tr id="connection_loder_'+parentUniqueId+'">';
			loadingHtml += ' 	<td colspan="5" class="text-center">';
			loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
			loadingHtml += ' 	</td>';
			loadingHtml += '</tr>';
			$("#row_"+parentUniqueId).after(loadingHtml);

			$.ajax({
				type: 'POST',
				url: 'get-symptom-connections.php',
				data: {
					comparison_initial_source_id: comparisonInitialSourceId,
					source_arznei_id: sourceArzneiId,
					main_parent_initial_symptom_id: mainParentInitialSymptomId,
					connections_main_parent_symptom_id: connectionsMainParentSymptomId,
					initial_symptom_id: initialSymptomId,
					comparing_symptom_id: comparingSymptomId,
					active_symptom_type: activeSymptomType,
					is_recompare: isRecompare,
					initial_source_id: initialSourceId,
					comparing_source_id: comparingSourceId,
					comparing_source_ids: comparingSourceIds,
					saved_comparison_comparing_source_ids: savedComparisonComparingSourceIds,
					saved_comparison_quelle_id: saved_comparison_quelle_id,
					individual_comparison_language: individual_comparison_language
				},
				dataType: "json",
				success: function( response ) {
					// console.log(response);
					if(response.status == "invalid"){
						$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
						$("#reloadPageModal").modal('show');
					} else if(response.status == "success"){
						var resultData = null;
						try {
							resultData = JSON.parse(response.result_data); 
						} catch (e) {
							resultData = response.result_data;
						}

						var html = "";
						$.each(resultData, function( key, value ) {
							var uniqueId = parentUniqueId+value.initial_source_symptom_id+value.comparing_source_symptom_id;
							
							// if(vPadding == 15){
							// 	var vbuttonleftPadding = 2;
							// 	var setVpadding = 15;
							// }else{
								var setVpadding = parseInt(vPadding) + 16;
								var vbuttonleftPadding = parseInt(setVpadding) - 16;
								
							// }

					  		var commentClasses = "";
					  		var footnoteClasses = "";
					  		var FVBtnClasses = "FV-btn";

					  		if(value.is_final_version_available != 0)
					  			FVBtnClasses += " active";

					  		if(value.has_connections == 1){
					  			if(value.is_further_connections_are_saved == 1){
					  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved';
					  				FVBtnClasses += " link-active-saved";
					  			}
					  			else{
					  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active';
					  				FVBtnClasses += " link-active";
					  			}
					  			var vBtnTitle = 'Earlier connections';
					  		} else {
					  			var vBtnClasses = 'vbtn link-disabled unclickable';
					  			var vBtnTitle = 'Earlier connections';
					  		}

					  		var nsc_btn_disabled = 'link-disabled unclickable';
					  		var connection_btn_disabled = '';
					  		var nsp_btn_disabled = 'link-disabled unclickable';
					  		var paste_btn_disabled = '';
					  		var connect_btn_class = 'connecting-btn';
					  		var paste_btn_class = 'paste-btn';
					  		var nscClasses = 'nsc';
					  		var nspClasses = 'nsp';
					  		if(value.is_connected == 1){
					  			nsc_btn_disabled = '';
						  		connection_btn_disabled = '';
						  		nsp_btn_disabled = 'link-disabled unclickable';
						  		paste_btn_disabled = 'link-disabled unclickable';
						  		if(value.is_saved_connections == 1){
						  			connect_btn_class = 'connecting-btn active link-active-saved';
						  			FVBtnClasses += " link-active-saved";
						  		}
						  		else{
						  			connect_btn_class = 'connecting-btn active link-active';
						  			FVBtnClasses += " link-active";
						  		}
					  		}

					  		if(value.is_pasted == 1){
					  			nsc_btn_disabled = 'link-disabled unclickable';
						  		connection_btn_disabled = 'link-disabled unclickable';
						  		nsp_btn_disabled = '';
						  		paste_btn_disabled = '';
						  		if(value.is_saved_connections == 1){
						  			paste_btn_class = 'paste-btn active link-active-saved';
						  			FVBtnClasses += " link-active-saved";
						  		}
						  		else{
						  			paste_btn_class = 'paste-btn active link-active';
						  			FVBtnClasses += " link-active";
						  		}
					  		}

					  		if(value.is_ns_connect == 1){
					  			if(value.is_saved_connections == 1)
					  				nscClasses = 'nsc active link-active-saved';
					  			else
					  				nscClasses = 'nsc active link-active';
					  		}
					  		if(value.is_ns_paste == 1){
					  			if(value.is_saved_connections == 1)
					  				nspClasses = 'nsp active link-active-saved';
					  			else
					  				nspClasses = 'nsp active link-active';
					  		}

					  		var instantReflectionClass = 'instant-reflection-set-'+mainParentInitialSymptomId;

					  		if(value.is_saved_connections == 1)
					  			rowBgColorClass = ' saved-connection-row';
					  		else{
					  			if(activeSymptomType == "comparing")
					  				rowBgColorClass = ' unsaved-connection-row on-comparative-part';
					  			else
					  				rowBgColorClass = ' unsaved-connection-row';
					  		}
					  		
					  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
				  			var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

				  			var translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
				  			var additional_class_for_original_symptom = "";
				  			var additional_class_for_hidden_symptom = "hidden";
				  			if($("#show_all_comparative_translation").prop("checked") == true) {
				  				translation_toggle_btn_additional_class += " active";
				  				additional_class_for_original_symptom = "table-original-symptom-bg";
				  				additional_class_for_hidden_symptom = "";
				  			}
					  		//console.log(comparingSymptomHighlightedEndcod);
					  		if(value.is_initial_source == 1){
					  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
						  			commentClasses = 'active';
						  		}
						  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
						  			footnoteClasses = 'active';
						  		}

					  			// Not allowing cmparison initial source symptoms to show it's connection to prevent from infinit nesting
					  			if(comparisonInitialSourceId == value.initial_source_id)
					  			{
					  				vBtnClasses = 'vbtn link-disabled unclickable';
						  			vBtnTitle = 'Earlier connections';
					  			}
					  			// Making the V/+ button disabled for it's child section if the clicked symptom is the main initial source symptom to avoide infinit nesting
					  			if(mainParentInitialSymptomId == initialSymptomId)
					  			{
					  				vBtnClasses = 'vbtn link-disabled unclickable';
						  			vBtnTitle = 'Earlier connections';
					  			}

					  			var saved_version_source_code = "";
					  			if(value.initial_source_code != value.initial_saved_version_source_code)
				  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';

					  			var activeSymptomTypeIndividual = "initial";

					  			// var initialSourceSymptomHighlighted = $('<div/>').html(value.initial_source_symptom_highlighted).text();
					  			var initialSourceSymptomHighlighted_de = (typeof(value.initial_source_symptom_highlighted_de) != "undefined" && value.initial_source_symptom_highlighted_de !== null && value.initial_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.initial_source_symptom_highlighted_de) : "";
					  			var initialSourceSymptomHighlighted_en = (typeof(value.initial_source_symptom_highlighted_en) != "undefined" && value.initial_source_symptom_highlighted_en !== null && value.initial_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.initial_source_symptom_highlighted_en) : "";
					  			var displaySymptomString = "";
					  			if(value.connection_language == "en"){

					  				displaySymptomString = initialSourceSymptomHighlighted_en;
					  				
					  				if(initial_source_original_language == "en"){
					  					var tmpString = "";
					  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom '+additional_class_for_original_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";
					  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
					  					
					  					displaySymptomString = tmpString;
					  				}
					  				else{
					  					var tmpString = "";
					  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
					  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initialSourceSymptomHighlighted_en+'</div>' : "";

					  					displaySymptomString = tmpString;
					  				}
					  			} else {
					  				displaySymptomString = initialSourceSymptomHighlighted_de;

					  				if(initial_source_original_language == "de"){
					  					var tmpString = "";
					  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom '+additional_class_for_original_symptom+'">'+initialSourceSymptomHighlighted_de+'</div>' : "";
					  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";

					  					displaySymptomString = tmpString;
					  				} else {
					  					var tmpString = "";
					  					tmpString += (initialSourceSymptomHighlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+initialSourceSymptomHighlighted_en+'</div>' : "";
					  					tmpString += (initialSourceSymptomHighlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initialSourceSymptomHighlighted_de+'</div>' : "";
					  					
					  					displaySymptomString = tmpString;
					  				}
					  			}
					  			
					  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
					  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
					  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
					  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;

					  			if(is_unmatched_symptom == 1){
					  				// translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
					  				instantReflectionClass += ' instant-reflection-unmatched-row';
					  			}
					  			
						  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+' '+removableRowClassChain+rowBgColorClass+'">';
						  		html += '	<td style="width: 12%;" class="text-center">'+value.initial_source_code+saved_version_source_code+'</td>';
						  		html += '	<td><!-- <i style="padding-left:'+vbuttonleftPadding+'px; padding-right:6px;" class="fas fa-angle-right"></i> -->'+displaySymptomString+'</td>';
						  		html += '	<td style="width: 5%;" class="text-center">'+value.matching_percentage+'%</td>';
						  		html += '	<th style="width: 17%;">';
						  		html += '		<ul class="info-linkage-group">';
						  		html += '			<li>';
						  		html += '				<a onclick="showInfo('+value.initial_source_symptom_id+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+value.initial_source_symptom_id+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+value.initial_source_symptom_id+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="translation-toggle-btn '+translation_toggle_btn_additional_class+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
						  		html += '			</li>';
						  		if(value.is_final_version_available != 0){
						  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
						  			var fvName = "";
						  			var fvTitle = "";
						  			if(value.is_final_version_available == 1){
						  				fvName = "CE";
						  				fvTitle = "Connect edit";
						  			} else if(value.is_final_version_available == 2){
						  				fvName = "PE";
						  				fvTitle = "Paste edit";
						  			}
						  			html += '			<li>';
							  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
							  		html += '			</li>';
						  		}
						  		html += '			<li>';
						  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-is-recompare="'+isRecompare+'" data-initial-source-id="'+initialSourceId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-connections-main-parent-symptom-id="'+connectionsMainParentSymptomId+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="initial" data-is-connection-loaded="0" data-comparing-source-ids="'+comparingSourceIds+'" data-source-arznei-id="'+sourceArzneiId+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
						  		html += '			</li>';
						  		html += '		</ul>';
					  		}
					  		else
					  		{
					  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
						  			commentClasses = 'active';
						  		}
						  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
						  			footnoteClasses = 'active';
						  		}

					  			// Not allowing cmparison initial source symptoms to show it's connection to prevent from infinit nesting
					  			if(comparisonInitialSourceId == value.comparing_source_id)
					  			{
					  				vBtnClasses = 'vbtn link-disabled unclickable';
						  			vBtnTitle = 'Earlier connections';
					  			}
					  			// Making the V/+ button disabled for it's child section if the clicked symptom is the main initial source symptom to avoide infinit nesting
					  			if(mainParentInitialSymptomId == initialSymptomId)
					  			{
					  				vBtnClasses = 'vbtn link-disabled unclickable';
						  			vBtnTitle = 'Earlier connections';
					  			}

					  			var saved_version_source_code = "";
					  			if(value.comparing_source_code != value.comparing_saved_version_source_code)
				  					saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';

					  			var activeSymptomTypeIndividual = "comparing";
					  			
					  			// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
					  			var comparingSymptomHighlightedEndcod_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
					  			var comparingSymptomHighlightedEndcod_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
					  			var displaySymptomString = "";
					  			if(value.connection_language == "en"){

					  				displaySymptomString = comparingSymptomHighlightedEndcod_en;
					  				if(comparing_source_original_language == "en"){
					  					var tmpString = "";
					  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom '+additional_class_for_original_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";
					  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
					  					
					  					displaySymptomString = tmpString;
					  				}
					  				else{
					  					var tmpString = "";
					  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
					  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";

					  					displaySymptomString = tmpString;
					  				}
					  			} else {
					  				displaySymptomString = comparingSymptomHighlightedEndcod_de;
					  				if(comparing_source_original_language == "de"){
					  					var tmpString = "";
					  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom '+additional_class_for_original_symptom+'">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
					  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";

					  					displaySymptomString = tmpString;
					  				} else {
					  					var tmpString = "";
					  					tmpString += (comparingSymptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom '+additional_class_for_original_symptom+' '+additional_class_for_hidden_symptom+'">'+comparingSymptomHighlightedEndcod_en+'</div>' : "";
					  					tmpString += (comparingSymptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparingSymptomHighlightedEndcod_de+'</div>' : "";
					  					
					  					displaySymptomString = tmpString;
					  				}
					  			}
					  			
					  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
					  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
					  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
					  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;

					  			if(is_unmatched_symptom == 1){
					  				// translation_toggle_btn_additional_class = "translation-toggle-btn-comparative";
					  				instantReflectionClass += ' instant-reflection-unmatched-row';
					  			}
					  			
					  			html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+' '+removableRowClassChain+rowBgColorClass+'">';
						  		html += '	<td style="width: 12%;" class="text-center">'+value.comparing_source_code+saved_version_source_code+'</td>';
						  		html += '	<td><!-- <i style="padding-left:'+vbuttonleftPadding+'px; padding-right:6px;" class="fas fa-angle-right"></i> -->'+displaySymptomString+'</td>';
						  		html += '	<td style="width: 5%;" class="text-center">'+value.matching_percentage+'%</td>';
						  		html += '	<th style="width: 17%;">';
						  		html += '		<ul class="info-linkage-group">';
						  		html += '			<li>';
						  		html += '				<a onclick="showInfo('+value.comparing_source_symptom_id+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+value.comparing_source_symptom_id+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+value.comparing_source_symptom_id+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
						  		html += '			</li>';
						  		html += '			<li>';
						  		html += '				<a class="translation-toggle-btn '+translation_toggle_btn_additional_class+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
						  		html += '			</li>';
						  		if(value.is_final_version_available != 0){
						  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
						  			var fvName = "";
						  			var fvTitle = "";
						  			if(value.is_final_version_available == 1){
						  				fvName = "CE";
						  				fvTitle = "Connect edit";
						  			} else if(value.is_final_version_available == 2){
						  				fvName = "PE";
						  				fvTitle = "Paste edit";
						  			}
						  			html += '			<li>';
							  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
							  		html += '			</li>';
						  		}
						  		html += '			<li>';
						  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="'+setVpadding+'" data-is-recompare="'+isRecompare+'" data-initial-source-id="'+initialSourceId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-connections-main-parent-symptom-id="'+connectionsMainParentSymptomId+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="comparing" data-is-connection-loaded="0" data-comparing-source-ids="'+comparingSourceIds+'" data-source-arznei-id="'+sourceArzneiId+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain="'+removableRowClassChain+'"><i class="fas fa-plus"></i></a>';
						  		html += '			</li>';
						  		html += '		</ul>';
					  		}
					  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+sourceArzneiId+'">';
					  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
					  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
					  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
					  		html += '		<input type="hidden" name="initial_source_year[]" id="initial_source_year_'+uniqueId+'" value="'+value.initial_source_year+'">';
					  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
					  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
					  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
					  		html += '		<input type="hidden" name="comparing_source_year[]" id="comparing_source_year_'+uniqueId+'" value="'+value.comparing_source_year+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';

					  		// Initial German
					  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';

					  		// Initial English
					  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
					  		html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';

					  		// Comparing German
					  		html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';

					  		// Comparing English
					  		html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
					  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';

					  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';
					  		html += '		<input type="hidden" name="individual_connection_language[]" id="individual_connection_language_'+uniqueId+'" value="'+value.connection_language+'">';

					  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
					  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.matching_percentage+'">';
					  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="'+value.is_connected+'">';
					  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="'+value.is_ns_connect+'">';
					  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="'+value.ns_connect_note+'">';
					  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
					  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
					  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
					  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
					  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+matched_symptom_ids_string+'">';
					  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+mainParentInitialSymptomId+'">';
					  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+connectionsMainParentSymptomId+'">';
					  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+similarity_rate+'">';
					  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+activeSymptomTypeIndividual+'">';
			  				html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+comparingSourceIds+'">';
			  				html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+comparison_option+'">';
			  				html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+savedComparisonComparingSourceIds+'">';
			  				html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+is_unmatched_symptom+'">';
					  		html += '	</th>';
					  		html += '	<th style="width: 19%;" class="">';
					  		html += '		<ul class="command-group">';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'"><i class="fas fa-link"></i></a>';
					  		html += '			</li>';
					  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
					  		if(value.connection_or_paste_type == 3){
					  			if(value.is_saved_connections == 1)
					  				connectEditIndicatorClasses = 'active link-active-saved';
					  			else
					  				connectEditIndicatorClasses = 'active link-active';
					  			html += '			<li>';
						  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connectEditIndicatorClasses+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-connection-or-paste-type="3">CE</a>';
						  		html += '			</li>';
					  		}
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+mainParentInitialSymptomId+'" data-comparison-initial-source-id="'+comparisonInitialSourceId+'">P</a>';
					  		html += '			</li>';
					  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
					  		if(value.connection_or_paste_type == 4){
					  			if(value.is_saved_connections == 1)
					  				pasteEditIndicatorClasses = 'active link-active-saved';
					  			else
					  				pasteEditIndicatorClasses = 'active link-active';
					  			html += '			<li>';
						  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+pasteEditIndicatorClasses+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-connection-or-paste-type="4">PE</a>';
						  		html += '			</li>';
					  		}
					  		// 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
					  		if(value.connection_or_paste_type == 2){
					  			if(value.is_saved_connections == 1)
					  				swapIndicatorClasses = 'active link-active-saved';
					  			else
					  				swapIndicatorClasses = 'active link-active';
					  			html += '		<li>';
						  		html += '			<a href="javascript:void(0)" id="swap_connect_indicator_btn_'+uniqueId+'" class="'+swapIndicatorClasses+'" title="Swap connection indicator"><i class="fas fa-recycle"></i></a>';
						  		html += '		</li>';
					  		}
					  		html += '		</ul>';
					  		html += '	</th>';
					  		html += '</tr>';
						});

						$('#connection_loder_'+parentUniqueId).remove();
						$("#row_"+parentUniqueId).after(html);
						$th.removeClass('processing');
						// $("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
						// $("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');

					}else{
						$('#connection_loder_'+parentUniqueId).html('<td colspan="5" class="text-center">Something went wrong! Could not load the data.</td>');
						$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
						$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
						$th.removeClass('processing');
						// setTimeout(function() {
						//     $('#connection_loder_'+parentUniqueId).remove();
						// }, 2000);
					}
				}
			}).fail(function (response) {
				console.log(response);
				$('#connection_loder_'+parentUniqueId).html('<td colspan="5" class="text-center">Something went wrong! Could not load the data.</td>');
				$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
				$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
				$th.removeClass('processing');
				// setTimeout(function() {
				//     $('#connection_loder_'+parentUniqueId).remove();
				// }, 2000);
			});
		})
    }
});

$(document).on('click', '.vbtn-has-connection', function(){
    var parentUniqueId = $(this).attr("data-unique-id");
    var isConnectionLoaded = $(this).attr("data-is-connection-loaded");
    if(isConnectionLoaded == 1){
    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 0);
    	$("#v_btn_"+parentUniqueId).html('<i class="fas fa-plus"></i>');
    	$("#v_btn_"+parentUniqueId).promise().done(function(){
    		// $("#connected-row-"+parentUniqueId).removeClass('show');
    		// $("#connected-row-"+parentUniqueId).addClass('hide');
    		$(".connected-row-"+parentUniqueId).hide();
	    	// Removing the connection check box checked if there is no open connection
	    	var totConn = $( ".vbtn-has-connection" ).length; 
	    	var closedConnCount = 0;
	    	$( ".vbtn-has-connection" ).each(function() {
				var checkIsConnectionLoaded = $(this).attr("data-is-connection-loaded");
				if(checkIsConnectionLoaded != 1)
					closedConnCount++;
			})
			if(parseInt(totConn) == parseInt(closedConnCount))
				$("#show_all_connections").prop("checked", false);
    	})	    	
    }else{
    	$("#v_btn_"+parentUniqueId).attr("data-is-connection-loaded", 1);
		$("#v_btn_"+parentUniqueId).html('<i class="fas fa-minus"></i>');
		$("#v_btn_"+parentUniqueId).promise().done(function(){
			// $("#connected-row-"+parentUniqueId).removeClass('hide');
			// $("#connected-row-"+parentUniqueId).addClass('show');
			$(".connected-row-"+parentUniqueId).show();
		})
	}
});

function symptomConnecting(dataArray, sub_connetions_array, updateable_symptom_ids, removable_sets){
	if (typeof dataArray !== 'undefined' && dataArray !== null){
		// console.log("task 1");
		// console.log(dataArray);

		var uniqueId = (typeof dataArray['uniqueId'] !== 'undefined' && dataArray['uniqueId'] !== null && dataArray['uniqueId'] != "") ? dataArray['uniqueId'] : "";
	    var source_arznei_id = (typeof dataArray['source_arznei_id'] !== 'undefined' && dataArray['source_arznei_id'] !== null && dataArray['source_arznei_id'] != "") ? dataArray['source_arznei_id'] : "";
	    var initial_source_id = (typeof dataArray['initial_source_id'] !== 'undefined' && dataArray['initial_source_id'] !== null && dataArray['initial_source_id'] != "") ? dataArray['initial_source_id'] : "";
	    var initial_original_source_id = (typeof dataArray['initial_original_source_id'] !== 'undefined' && dataArray['initial_original_source_id'] !== null && dataArray['initial_original_source_id'] != "") ? dataArray['initial_original_source_id'] : "";
	    var initial_source_code = (typeof dataArray['initial_source_code'] !== 'undefined' && dataArray['initial_source_code'] !== null && dataArray['initial_source_code'] != "") ? dataArray['initial_source_code'] : "";
	    var comparing_source_id = (typeof dataArray['comparing_source_id'] !== 'undefined' && dataArray['comparing_source_id'] !== null && dataArray['comparing_source_id'] != "") ? dataArray['comparing_source_id'] : "";
	    var comparing_original_source_id = (typeof dataArray['comparing_original_source_id'] !== 'undefined' && dataArray['comparing_original_source_id'] !== null && dataArray['comparing_original_source_id'] != "") ? dataArray['comparing_original_source_id'] : "";
	    var comparing_source_code = (typeof dataArray['comparing_source_code'] !== 'undefined' && dataArray['comparing_source_code'] !== null && dataArray['comparing_source_code'] != "") ? dataArray['comparing_source_code'] : "";
	    var initial_source_symptom_id = (typeof dataArray['initial_source_symptom_id'] !== 'undefined' && dataArray['initial_source_symptom_id'] !== null && dataArray['initial_source_symptom_id'] != "") ? dataArray['initial_source_symptom_id'] : "";
	    
	    var initial_source_symptom_de = (typeof dataArray['initial_source_symptom_de'] !== 'undefined' && dataArray['initial_source_symptom_de'] !== null && dataArray['initial_source_symptom_de'] != "") ? dataArray['initial_source_symptom_de'] : "";
	    var initial_source_symptom_en = (typeof dataArray['initial_source_symptom_en'] !== 'undefined' && dataArray['initial_source_symptom_en'] !== null && dataArray['initial_source_symptom_en'] != "") ? dataArray['initial_source_symptom_en'] : "";

	    var comparing_source_symptom_de = (typeof dataArray['comparing_source_symptom_de'] !== 'undefined' && dataArray['comparing_source_symptom_de'] !== null && dataArray['comparing_source_symptom_de'] != "") ? dataArray['comparing_source_symptom_de'] : "";
	    var comparing_source_symptom_en = (typeof dataArray['comparing_source_symptom_en'] !== 'undefined' && dataArray['comparing_source_symptom_en'] !== null && dataArray['comparing_source_symptom_en'] != "") ? dataArray['comparing_source_symptom_en'] : "";

	    var initial_source_symptom_highlighted_de = (typeof dataArray['initial_source_symptom_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? dataArray['initial_source_symptom_highlighted_de'] : "";
	    var initial_source_symptom_highlighted_en = (typeof dataArray['initial_source_symptom_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_en'] !== null && dataArray['initial_source_symptom_highlighted_en'] != "") ? dataArray['initial_source_symptom_highlighted_en'] : "";

	    var comparing_source_symptom_highlighted_de = (typeof dataArray['comparing_source_symptom_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? dataArray['comparing_source_symptom_highlighted_de'] : "";
	    var comparing_source_symptom_highlighted_en = (typeof dataArray['comparing_source_symptom_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_en'] !== null && dataArray['comparing_source_symptom_highlighted_en'] != "") ? dataArray['comparing_source_symptom_highlighted_en'] : "";

	    var initial_source_symptom_before_conversion_de = (typeof dataArray['initial_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_de'] !== null && dataArray['initial_source_symptom_before_conversion_de'] != "") ? dataArray['initial_source_symptom_before_conversion_de'] : "";
	    var initial_source_symptom_before_conversion_en = (typeof dataArray['initial_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_en'] !== null && dataArray['initial_source_symptom_before_conversion_en'] != "") ? dataArray['initial_source_symptom_before_conversion_en'] : "";

		var comparing_source_symptom_before_conversion_de = (typeof dataArray['comparing_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_de'] !== null && dataArray['comparing_source_symptom_before_conversion_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_de'] : "";
		var comparing_source_symptom_before_conversion_en = (typeof dataArray['comparing_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_en'] !== null && dataArray['comparing_source_symptom_before_conversion_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_en'] : "";

		var initial_source_symptom_before_conversion_highlighted_de = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_de'] : "";
		var initial_source_symptom_before_conversion_highlighted_en = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_en'] : "";			    
		var comparing_source_symptom_before_conversion_highlighted_de = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_de'] : "";
		var comparing_source_symptom_before_conversion_highlighted_en = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_en'] : "";

		var individual_comparison_language = (typeof dataArray['individual_comparison_language'] !== 'undefined' && dataArray['individual_comparison_language'] !== null && dataArray['individual_comparison_language'] != "") ? dataArray['individual_comparison_language'] : "";

		var comparing_source_symptom_id = (typeof dataArray['comparing_source_symptom_id'] !== 'undefined' && dataArray['comparing_source_symptom_id'] !== null && dataArray['comparing_source_symptom_id'] != "") ? dataArray['comparing_source_symptom_id'] : "";
		var matching_percentage = (typeof dataArray['matching_percentage'] !== 'undefined' && dataArray['matching_percentage'] !== null && dataArray['matching_percentage'] != "") ? dataArray['matching_percentage'] : "";
		var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
		var is_ns_connect = (typeof dataArray['is_ns_connect'] !== 'undefined' && dataArray['is_ns_connect'] !== null && dataArray['is_ns_connect'] != "") ? dataArray['is_ns_connect'] : "";
		var ns_connect_note = (typeof dataArray['ns_connect_note'] !== 'undefined' && dataArray['ns_connect_note'] !== null && dataArray['ns_connect_note'] != "") ? dataArray['ns_connect_note'] : "";
		var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
		var is_ns_paste = (typeof dataArray['is_ns_paste'] !== 'undefined' && dataArray['is_ns_paste'] !== null && dataArray['is_ns_paste'] != "") ? dataArray['is_ns_paste'] : "";
		var ns_paste_note = (typeof dataArray['ns_paste_note'] !== 'undefined' && dataArray['ns_paste_note'] !== null && dataArray['ns_paste_note'] != "") ? dataArray['ns_paste_note'] : "";
		var is_initial_source = (typeof dataArray['is_initial_source'] !== 'undefined' && dataArray['is_initial_source'] !== null && dataArray['is_initial_source'] != "") ? dataArray['is_initial_source'] : "";
		var similarity_rate = (typeof dataArray['similarity_rate'] !== 'undefined' && dataArray['similarity_rate'] !== null && dataArray['similarity_rate'] != "") ? dataArray['similarity_rate'] : "";
		var active_symptom_type = (typeof dataArray['active_symptom_type'] !== 'undefined' && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
		var comparing_source_ids = (typeof dataArray['comparing_source_ids'] !== 'undefined' && dataArray['comparing_source_ids'] !== null && dataArray['comparing_source_ids'] != "") ? dataArray['comparing_source_ids'] : "";
		var matched_symptom_ids = (typeof dataArray['matched_symptom_ids'] !== 'undefined' && dataArray['matched_symptom_ids'] !== null && dataArray['matched_symptom_ids'] != "") ? dataArray['matched_symptom_ids'] : "";
		var comparison_option = (typeof dataArray['comparison_option'] !== 'undefined' && dataArray['comparison_option'] !== null && dataArray['comparison_option'] != "") ? dataArray['comparison_option'] : "";
		var savedComparisonComparingSourceIds = (typeof dataArray['savedComparisonComparingSourceIds'] !== 'undefined' && dataArray['savedComparisonComparingSourceIds'] !== null && dataArray['savedComparisonComparingSourceIds'] != "") ? dataArray['savedComparisonComparingSourceIds'] : "";
		var is_unmatched_symptom = (typeof dataArray['is_unmatched_symptom'] !== 'undefined' && dataArray['is_unmatched_symptom'] !== null && dataArray['is_unmatched_symptom'] != "") ? dataArray['is_unmatched_symptom'] : "";
		var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
		var comparison_initial_source_id = (typeof dataArray['comparison_initial_source_id'] !== 'undefined' && dataArray['comparison_initial_source_id'] !== null && dataArray['comparison_initial_source_id'] != "") ? dataArray['comparison_initial_source_id'] : "";
		var connections_main_parent_symptom_id = (typeof dataArray['connections_main_parent_symptom_id'] !== 'undefined' && dataArray['connections_main_parent_symptom_id'] !== null && dataArray['connections_main_parent_symptom_id'] != "") ? dataArray['connections_main_parent_symptom_id'] : "";
		var error_count = (typeof dataArray['error_count'] !== 'undefined' && dataArray['error_count'] !== null && dataArray['error_count'] != "") ? dataArray['error_count'] : "";
		var mainParentInitialSymptomIdsArr = (typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'] != "") ? dataArray['mainParentInitialSymptomIdsArr'] : [];

		var saved_comparison_quelle_id = (typeof dataArray['saved_comparison_quelle_id'] !== 'undefined' && dataArray['saved_comparison_quelle_id'] !== null && dataArray['saved_comparison_quelle_id'] != "") ? dataArray['saved_comparison_quelle_id'] : "";
		
		var sub_connetions_array = (typeof sub_connetions_array !== 'undefined' && sub_connetions_array !== null && sub_connetions_array != "") ? sub_connetions_array : [];
		var updateable_symptom_ids = (typeof updateable_symptom_ids !== 'undefined' && updateable_symptom_ids !== null && updateable_symptom_ids != "") ? updateable_symptom_ids : [];
		var removable_sets = (typeof removable_sets !== 'undefined' && removable_sets !== null && removable_sets != "") ? removable_sets : [];

		var connection_type = (typeof dataArray['connection_type'] !== 'undefined' && dataArray['connection_type'] !== null && dataArray['connection_type'] != "") ? dataArray['connection_type'] : "";
		// This field is not there in the hidden input fields of the table rows
		var connection_or_paste_type = (typeof dataArray['connection_or_paste_type'] !== 'undefined' && dataArray['connection_or_paste_type'] !== null && dataArray['connection_or_paste_type'] != "") ? dataArray['connection_or_paste_type'] : ""; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit
		console.log(connection_or_paste_type);
		// Making connection operation start
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: uniqueId,
				source_arznei_id: source_arznei_id,
				initial_source_id: initial_source_id,
				initial_original_source_id: initial_original_source_id,
				initial_source_code: initial_source_code,
				comparing_source_id: comparing_source_id,
				comparing_original_source_id: comparing_original_source_id,
				comparing_source_code: comparing_source_code,
				initial_source_symptom_id: initial_source_symptom_id,
				initial_source_symptom_de: initial_source_symptom_de,
				initial_source_symptom_en: initial_source_symptom_en,
				comparing_source_symptom_de: comparing_source_symptom_de,
				comparing_source_symptom_en: comparing_source_symptom_en,
				initial_source_symptom_highlighted_de: initial_source_symptom_highlighted_de,
				initial_source_symptom_highlighted_en: initial_source_symptom_highlighted_en,
				comparing_source_symptom_highlighted_de: comparing_source_symptom_highlighted_de,
				comparing_source_symptom_highlighted_en: comparing_source_symptom_highlighted_en,
				initial_source_symptom_before_conversion_de: initial_source_symptom_before_conversion_de,
				initial_source_symptom_before_conversion_en: initial_source_symptom_before_conversion_en,
				comparing_source_symptom_before_conversion_de: comparing_source_symptom_before_conversion_de,
				comparing_source_symptom_before_conversion_en: comparing_source_symptom_before_conversion_en,
				initial_source_symptom_before_conversion_highlighted_de: initial_source_symptom_before_conversion_highlighted_de,
				initial_source_symptom_before_conversion_highlighted_en: initial_source_symptom_before_conversion_highlighted_en,
				comparing_source_symptom_before_conversion_highlighted_de: comparing_source_symptom_before_conversion_highlighted_de,
				comparing_source_symptom_before_conversion_highlighted_en: comparing_source_symptom_before_conversion_highlighted_en,
				individual_comparison_language: individual_comparison_language,
				comparing_source_symptom_id: comparing_source_symptom_id,
				matching_percentage: matching_percentage,
				is_connected: is_connected,
				is_ns_connect: is_ns_connect,
				ns_connect_note: ns_connect_note,
				is_pasted: is_pasted,
				is_ns_paste: is_ns_paste,
				ns_paste_note: ns_paste_note,
				is_initial_source: is_initial_source,
				comparing_source_ids: comparing_source_ids,
				active_symptom_type: active_symptom_type,
				main_parent_initial_symptom_id: main_parent_initial_symptom_id,
				comparison_option: comparison_option,
				sub_connetions_array: sub_connetions_array,
				updateable_symptom_ids: updateable_symptom_ids,
				removable_sets: removable_sets,
				saved_comparison_quelle_id: saved_comparison_quelle_id,
				connection_or_paste_type: connection_or_paste_type,
				action: 'connect'
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} else if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					console.log(resultData.is_connected);
					
					if(typeof resultData.sub_connetions_array !== 'undefined' && resultData.sub_connetions_array !== null && resultData.sub_connetions_array != "")
					{
						symptomConnecting(dataArray, resultData.sub_connetions_array, resultData.updateable_symptom_ids, resultData.removable_sets);	
					}
					else
					{
						if(resultData.does_it_requires_full_reload == 1){

							// Re-Calling the main comprasion function to get the updated data.
							var initial_source = $('#batch_result_form_1').find("#initial_source_save").val();
							var comparing_sources = $('#batch_result_form_1').find("#comparing_sources_save").val();
							var arznei_id = $('#batch_result_form_1').find("#arznei_id_save").val();
							var similarity_rate = $('#batch_result_form_1').find("#similarity_rate_save").val();
							var comparison_option = $('#batch_result_form_1').find("#comparison_option_save").val();
							var comparison_language = $('#batch_result_form_1').find("#comparison_language_save").val();
							var error_count = 0;

							if(initial_source == ""){
								error_count++;
							}
							if(comparing_sources == ""){
								error_count++;
							}
							if(arznei_id == ""){
								error_count++;
							}
							if(similarity_rate == ""){
								error_count++;
							}
							if(comparison_option == ""){
								error_count++;
							}
							if(comparison_language == ""){
								error_count++;
							}
							
							if(error_count == 0){
								$(".progress-connection-thead").remove();
								$('.batch-search-result-form').remove();
								$('.batch-result-form').remove();
								$('#symptom_comparison_form').addClass('unclickable');
								$('#compare_submit_btn').prop('disabled', true);
								$('#search_submit_btn').prop('disabled', true);
								$("#comparison_name").val('');
								
								if(!$(".result-sub-btn").hasClass('hidden'))
									$(".result-sub-btn").addClass('hidden');

								if(!$(".head-panel-sub-ul").hasClass('hidden'))
									$(".head-panel-sub-ul").addClass('hidden');

								if($('.comparison-only-column').hasClass('hidden'))
									$('.comparison-only-column').removeClass('hidden');
								$("#numberOfRecord").html(0);

								$("#column_heading_symptom").html('Symptom');
								var loadingHtml = '';
								loadingHtml += '<tr id="loadingTr">';
								loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
								loadingHtml += '</tr>';

								$('#resultTable tbody').html(loadingHtml);

								var data = 'initial_source='+initial_source+'&comparing_sources='+comparing_sources+'&arznei_id='+arznei_id+'&similarity_rate='+similarity_rate+'&comparison_option='+comparison_option+'&comparison_language='+comparison_language;

								$(".progress-thead").remove();
								var progressBarHtml = '';
								progressBarHtml += '<thead class="progress-thead heading-table-bg">';
								progressBarHtml += '	<tr>';
								progressBarHtml += ' 		<th colspan="5">';
								progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
								progressBarHtml += ' 			<div class="progress comparison-progress">';
								progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
								progressBarHtml += ' 			</div>';
								progressBarHtml += ' 		</th>';
								progressBarHtml += '	</tr>';
								progressBarHtml += '</thead>';
								$('#resultTable thead').after(progressBarHtml);

								// start the process
								var matchedSymptomIds = [];
								process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds );

							} else {
								$("#global_msg_container").html('<p class="text-center">Something went wrong, Could not update the contect. Please reload the comparison and try again!</p>');
								$("#globalMsgModal").modal('show');
								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');
							}
							
						}
						else
						{
							if(typeof resultData.removable_sets !== 'undefined' && resultData.removable_sets !== null && resultData.removable_sets != "")
							{
								$.each(resultData.removable_sets, function( key, value ) {
									dataArray['removable_sets'].push(value);
								});
							}
							
							if(typeof resultData.updateable_symptom_ids !== 'undefined' && resultData.updateable_symptom_ids !== null && resultData.updateable_symptom_ids != "")
							{
								$.each(resultData.updateable_symptom_ids, function( key, value ) {
									$( ".instant-reflection-row-"+value ).each(function() {
									  	var rowUniqueId = $(this).attr("id");
									  	rowUniqueId = rowUniqueId.split( "row_" ).pop();	
									  	var mainParentInitialSymptomId = $('#main_parent_initial_symptom_id_'+rowUniqueId).val();
									  	if(typeof(mainParentInitialSymptomId) != "undefined" && mainParentInitialSymptomId !== null && mainParentInitialSymptomId != ""){
									  		if ($.inArray(mainParentInitialSymptomId, dataArray['mainParentInitialSymptomIdsArr']) == -1)
												dataArray['mainParentInitialSymptomIdsArr'].push(mainParentInitialSymptomId);
									  	}
									});
								});

								// Making updateable data unclickable
								if(typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'].length > 0) {
									$.each(dataArray['mainParentInitialSymptomIdsArr'], function( key, value ) {
										var eachMainParentId = value;
									  	var loadingHtml = '';
								    	loadingHtml += '<tr id="instant_reflection_loder_'+eachMainParentId+'" style="display:none;">';
										loadingHtml += ' 	<td colspan="5" class="text-center">';
										loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
										loadingHtml += ' 	</td>';
										loadingHtml += '</tr>';
										$('.instant-reflection-set-'+eachMainParentId).first().before(loadingHtml);
										// $('.instant-reflection-set-'+eachMainParentId).addClass('unclickable');
									});
								}

								// Making unmatched symptoms unclickable if they needs to update
								// if(dataArray['is_connected'] == 1 && parseInt(dataArray['matching_percentage']) < parseInt(dataArray['similarity_rate']) && dataArray['matched_symptom_ids'] != ""){
									var loadingHtml = '';
							    	loadingHtml += '<tr id="instant_reflection_unmatched_rows_loder" style="display:none;">';
									loadingHtml += ' 	<td colspan="5" class="text-center">';
									loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
									loadingHtml += ' 	</td>';
									loadingHtml += '</tr>';
									$('.instant-reflection-unmatched-row').first().before(loadingHtml);
									// $('.instant-reflection-unmatched-row').addClass('unclickable');
								// }

								if(resultData.is_connected == 1){
									$('#paste_btn_'+uniqueId).prop('disabled', true);
									$('#nsp_btn_'+uniqueId).prop('disabled', true);
									$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
									$('#connecting_btn_'+uniqueId).prop('disabled', false);
									$('#nsc_btn_'+uniqueId).prop('disabled', false);
									$("#is_connected_"+uniqueId).val(1);
									$('#connecting_btn_'+uniqueId).addClass("btn-success");
								}else{
									$('#paste_btn_'+uniqueId).prop('disabled', false);
									$('#nsc_btn_'+uniqueId).prop('disabled', true);
									$("#is_connected_"+uniqueId).val(0);
									$("#ns_connect_note_"+uniqueId).val('');
									// $("#nsc_btn_"+uniqueId).attr('data-nsc-note', '');
									$("#is_ns_connect_"+uniqueId).val(0);
									$("#nsc_btn_"+uniqueId).removeClass("btn-success");
									$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
									$('#connecting_btn_'+uniqueId).removeClass("btn-success");
									$('#connecting_btn_'+uniqueId).prop('disabled', false);
								}
								// If a symptom is disconnected from save comparison/Materia medica it will appear in the bottom as initial symptom of That Materia medica
								if(typeof resultData.appendable_materia_medica_symptom_id !== 'undefined' && resultData.appendable_materia_medica_symptom_id !== null && resultData.appendable_materia_medica_symptom_id != "")
								{
									if ($.inArray(resultData.appendable_materia_medica_symptom_id, dataArray['mainParentInitialSymptomIdsArr']) == -1){
										dataArray['mainParentInitialSymptomIdsArr'].push(resultData.appendable_materia_medica_symptom_id);
										dataArray['appendable_materia_medica_symptom_id'] = resultData.appendable_materia_medica_symptom_id;
									}
								}
								if(connection_type == "swap") {
									swapTheConnectedInitialandComparativeSymptom(dataArray);
								} else {
									instantReflectionMatchedSections(dataArray);
								}
							}
							else
							{
								$("#global_msg_container").html('<p class="text-center">Something went wrong. Please reload and try!</p>');
								$("#globalMsgModal").modal('show');
								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');

								$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
								$('#connecting_btn_'+uniqueId).prop('disabled', false);
								$('#paste_btn_'+uniqueId).prop('disabled', false);
							}
						}
							
					}
				}else{
					var err_msg = (typeof response.message !== 'undefined' && response.message !== null && response.message != "") ? response.message : "Operation failed. Please reload the page and try!";
					// $("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try1!</p>');
					$("#global_msg_container").html('<p class="text-center">'+err_msg+'</p>');
					$("#globalMsgModal").modal('show');
					$(".progress-connection-thead").remove();
					$('.batch-result-form').removeClass('unclickable');

					$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
					$('#connecting_btn_'+uniqueId).prop('disabled', false);
					$('#paste_btn_'+uniqueId).prop('disabled', false);
				}
			}
		}).fail(function (response) {
			console.log(response);
			$("#global_msg_container").html('<p class="text-center">Operation failed. Something went worng, please reload the page and try again!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');

			$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
			$('#connecting_btn_'+uniqueId).prop('disabled', false);
			$('#paste_btn_'+uniqueId).prop('disabled', false);
		})
		// Making connection operation end
	}
}

function swapTheConnectedInitialandComparativeSymptom(dataArray) {
	if (typeof dataArray !== 'undefined' && dataArray !== null) {
		var uniqueId = (typeof dataArray['uniqueId'] !== 'undefined' && dataArray['uniqueId'] !== null && dataArray['uniqueId'] != "") ? dataArray['uniqueId'] : "";
	    var source_arznei_id = (typeof dataArray['source_arznei_id'] !== 'undefined' && dataArray['source_arznei_id'] !== null && dataArray['source_arznei_id'] != "") ? dataArray['source_arznei_id'] : "";
	    var initial_source_id = (typeof dataArray['initial_source_id'] !== 'undefined' && dataArray['initial_source_id'] !== null && dataArray['initial_source_id'] != "") ? dataArray['initial_source_id'] : "";
	    var initial_original_source_id = (typeof dataArray['initial_original_source_id'] !== 'undefined' && dataArray['initial_original_source_id'] !== null && dataArray['initial_original_source_id'] != "") ? dataArray['initial_original_source_id'] : "";
	    var initial_source_code = (typeof dataArray['initial_source_code'] !== 'undefined' && dataArray['initial_source_code'] !== null && dataArray['initial_source_code'] != "") ? dataArray['initial_source_code'] : "";
	    var comparing_source_id = (typeof dataArray['comparing_source_id'] !== 'undefined' && dataArray['comparing_source_id'] !== null && dataArray['comparing_source_id'] != "") ? dataArray['comparing_source_id'] : "";
	    var comparing_original_source_id = (typeof dataArray['comparing_original_source_id'] !== 'undefined' && dataArray['comparing_original_source_id'] !== null && dataArray['comparing_original_source_id'] != "") ? dataArray['comparing_original_source_id'] : "";
	    var comparing_source_code = (typeof dataArray['comparing_source_code'] !== 'undefined' && dataArray['comparing_source_code'] !== null && dataArray['comparing_source_code'] != "") ? dataArray['comparing_source_code'] : "";
	    var initial_source_symptom_id = (typeof dataArray['initial_source_symptom_id'] !== 'undefined' && dataArray['initial_source_symptom_id'] !== null && dataArray['initial_source_symptom_id'] != "") ? dataArray['initial_source_symptom_id'] : "";
	    
	    var initial_source_symptom_de = (typeof dataArray['initial_source_symptom_de'] !== 'undefined' && dataArray['initial_source_symptom_de'] !== null && dataArray['initial_source_symptom_de'] != "") ? dataArray['initial_source_symptom_de'] : "";
	    var initial_source_symptom_en = (typeof dataArray['initial_source_symptom_en'] !== 'undefined' && dataArray['initial_source_symptom_en'] !== null && dataArray['initial_source_symptom_en'] != "") ? dataArray['initial_source_symptom_en'] : "";

	    var comparing_source_symptom_de = (typeof dataArray['comparing_source_symptom_de'] !== 'undefined' && dataArray['comparing_source_symptom_de'] !== null && dataArray['comparing_source_symptom_de'] != "") ? dataArray['comparing_source_symptom_de'] : "";
	    var comparing_source_symptom_en = (typeof dataArray['comparing_source_symptom_en'] !== 'undefined' && dataArray['comparing_source_symptom_en'] !== null && dataArray['comparing_source_symptom_en'] != "") ? dataArray['comparing_source_symptom_en'] : "";

	    var initial_source_symptom_highlighted_de = (typeof dataArray['initial_source_symptom_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? dataArray['initial_source_symptom_highlighted_de'] : "";
	    var initial_source_symptom_highlighted_en = (typeof dataArray['initial_source_symptom_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_en'] !== null && dataArray['initial_source_symptom_highlighted_en'] != "") ? dataArray['initial_source_symptom_highlighted_en'] : "";

	    var comparing_source_symptom_highlighted_de = (typeof dataArray['comparing_source_symptom_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? dataArray['comparing_source_symptom_highlighted_de'] : "";
	    var comparing_source_symptom_highlighted_en = (typeof dataArray['comparing_source_symptom_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_en'] !== null && dataArray['comparing_source_symptom_highlighted_en'] != "") ? dataArray['comparing_source_symptom_highlighted_en'] : "";

	    var initial_source_symptom_before_conversion_de = (typeof dataArray['initial_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_de'] !== null && dataArray['initial_source_symptom_before_conversion_de'] != "") ? dataArray['initial_source_symptom_before_conversion_de'] : "";
	    var initial_source_symptom_before_conversion_en = (typeof dataArray['initial_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_en'] !== null && dataArray['initial_source_symptom_before_conversion_en'] != "") ? dataArray['initial_source_symptom_before_conversion_en'] : "";

		var comparing_source_symptom_before_conversion_de = (typeof dataArray['comparing_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_de'] !== null && dataArray['comparing_source_symptom_before_conversion_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_de'] : "";
		var comparing_source_symptom_before_conversion_en = (typeof dataArray['comparing_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_en'] !== null && dataArray['comparing_source_symptom_before_conversion_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_en'] : "";

		var initial_source_symptom_before_conversion_highlighted_de = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_de'] : "";
		var initial_source_symptom_before_conversion_highlighted_en = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_en'] : "";			    
		var comparing_source_symptom_before_conversion_highlighted_de = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_de'] : "";
		var comparing_source_symptom_before_conversion_highlighted_en = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_en'] : "";

		var individual_comparison_language = (typeof dataArray['individual_comparison_language'] !== 'undefined' && dataArray['individual_comparison_language'] !== null && dataArray['individual_comparison_language'] != "") ? dataArray['individual_comparison_language'] : "";
		
		var comparing_source_symptom_id = (typeof dataArray['comparing_source_symptom_id'] !== 'undefined' && dataArray['comparing_source_symptom_id'] !== null && dataArray['comparing_source_symptom_id'] != "") ? dataArray['comparing_source_symptom_id'] : "";
		var matching_percentage = (typeof dataArray['matching_percentage'] !== 'undefined' && dataArray['matching_percentage'] !== null && dataArray['matching_percentage'] != "") ? dataArray['matching_percentage'] : "";
		var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
		var is_ns_connect = (typeof dataArray['is_ns_connect'] !== 'undefined' && dataArray['is_ns_connect'] !== null && dataArray['is_ns_connect'] != "") ? dataArray['is_ns_connect'] : "";
		var ns_connect_note = (typeof dataArray['ns_connect_note'] !== 'undefined' && dataArray['ns_connect_note'] !== null && dataArray['ns_connect_note'] != "") ? dataArray['ns_connect_note'] : "";
		var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
		var is_ns_paste = (typeof dataArray['is_ns_paste'] !== 'undefined' && dataArray['is_ns_paste'] !== null && dataArray['is_ns_paste'] != "") ? dataArray['is_ns_paste'] : "";
		var ns_paste_note = (typeof dataArray['ns_paste_note'] !== 'undefined' && dataArray['ns_paste_note'] !== null && dataArray['ns_paste_note'] != "") ? dataArray['ns_paste_note'] : "";
		var is_initial_source = (typeof dataArray['is_initial_source'] !== 'undefined' && dataArray['is_initial_source'] !== null && dataArray['is_initial_source'] != "") ? dataArray['is_initial_source'] : "";
		var similarity_rate = (typeof dataArray['similarity_rate'] !== 'undefined' && dataArray['similarity_rate'] !== null && dataArray['similarity_rate'] != "") ? dataArray['similarity_rate'] : "";
		var active_symptom_type = (typeof dataArray['active_symptom_type'] !== 'undefined' && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
		var comparing_source_ids = (typeof dataArray['comparing_source_ids'] !== 'undefined' && dataArray['comparing_source_ids'] !== null && dataArray['comparing_source_ids'] != "") ? dataArray['comparing_source_ids'] : "";
		var matched_symptom_ids = (typeof dataArray['matched_symptom_ids'] !== 'undefined' && dataArray['matched_symptom_ids'] !== null && dataArray['matched_symptom_ids'] != "") ? dataArray['matched_symptom_ids'] : "";
		var comparison_option = (typeof dataArray['comparison_option'] !== 'undefined' && dataArray['comparison_option'] !== null && dataArray['comparison_option'] != "") ? dataArray['comparison_option'] : "";
		var savedComparisonComparingSourceIds = (typeof dataArray['savedComparisonComparingSourceIds'] !== 'undefined' && dataArray['savedComparisonComparingSourceIds'] !== null && dataArray['savedComparisonComparingSourceIds'] != "") ? dataArray['savedComparisonComparingSourceIds'] : "";
		var is_unmatched_symptom = (typeof dataArray['is_unmatched_symptom'] !== 'undefined' && dataArray['is_unmatched_symptom'] !== null && dataArray['is_unmatched_symptom'] != "") ? dataArray['is_unmatched_symptom'] : "";
		var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
		var comparison_initial_source_id = (typeof dataArray['comparison_initial_source_id'] !== 'undefined' && dataArray['comparison_initial_source_id'] !== null && dataArray['comparison_initial_source_id'] != "") ? dataArray['comparison_initial_source_id'] : "";
		var connections_main_parent_symptom_id = (typeof dataArray['connections_main_parent_symptom_id'] !== 'undefined' && dataArray['connections_main_parent_symptom_id'] !== null && dataArray['connections_main_parent_symptom_id'] != "") ? dataArray['connections_main_parent_symptom_id'] : "";
		var error_count = (typeof dataArray['error_count'] !== 'undefined' && dataArray['error_count'] !== null && dataArray['error_count'] != "") ? dataArray['error_count'] : "";
		var mainParentInitialSymptomIdsArr = (typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'] != "") ? dataArray['mainParentInitialSymptomIdsArr'] : [];
		var removable_sets = (typeof dataArray['removable_sets'] !== 'undefined' && dataArray['removable_sets'] !== null && dataArray['removable_sets'] != "") ? dataArray['removable_sets'] : [];
		var appendable_materia_medica_symptom_id = (typeof dataArray['appendable_materia_medica_symptom_id'] !== 'undefined' && dataArray['appendable_materia_medica_symptom_id'] !== null && dataArray['appendable_materia_medica_symptom_id'] != "") ? dataArray['appendable_materia_medica_symptom_id'] : "";
		var saved_comparison_quelle_id = (typeof dataArray['saved_comparison_quelle_id'] !== 'undefined' && dataArray['saved_comparison_quelle_id'] !== null && dataArray['saved_comparison_quelle_id'] != "") ? dataArray['saved_comparison_quelle_id'] : "";
		// This field is not there in the hidden input fields of the table rows
		var connection_or_paste_type = (typeof dataArray['connection_or_paste_type'] !== 'undefined' && dataArray['connection_or_paste_type'] !== null && dataArray['connection_or_paste_type'] != "") ? dataArray['connection_or_paste_type'] : ""; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

		// Making connection operation start
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: uniqueId,
				source_arznei_id: source_arznei_id,
				initial_source_id: initial_source_id,
				initial_original_source_id: initial_original_source_id,
				initial_source_code: initial_source_code,
				comparing_source_id: comparing_source_id,
				comparing_original_source_id: comparing_original_source_id,
				comparing_source_code: comparing_source_code,
				initial_source_symptom_id: initial_source_symptom_id,
				initial_source_symptom_de: initial_source_symptom_de,
				initial_source_symptom_en: initial_source_symptom_en,
				comparing_source_symptom_de: comparing_source_symptom_de,
				comparing_source_symptom_en: comparing_source_symptom_en,
				initial_source_symptom_highlighted_de: initial_source_symptom_highlighted_de,
				initial_source_symptom_highlighted_en: initial_source_symptom_highlighted_en,
				comparing_source_symptom_highlighted_de: comparing_source_symptom_highlighted_de,
				comparing_source_symptom_highlighted_en: comparing_source_symptom_highlighted_en,
				initial_source_symptom_before_conversion_de: initial_source_symptom_before_conversion_de,
				initial_source_symptom_before_conversion_en: initial_source_symptom_before_conversion_en,
				comparing_source_symptom_before_conversion_de: comparing_source_symptom_before_conversion_de,
				comparing_source_symptom_before_conversion_en: comparing_source_symptom_before_conversion_en,
				initial_source_symptom_before_conversion_highlighted_de: initial_source_symptom_before_conversion_highlighted_de,
				initial_source_symptom_before_conversion_highlighted_en: initial_source_symptom_before_conversion_highlighted_en,
				comparing_source_symptom_before_conversion_highlighted_de: comparing_source_symptom_before_conversion_highlighted_de,
				comparing_source_symptom_before_conversion_highlighted_en: comparing_source_symptom_before_conversion_highlighted_en,
				individual_comparison_language: individual_comparison_language,
				comparing_source_symptom_id: comparing_source_symptom_id,
				matching_percentage: matching_percentage,
				is_connected: is_connected,
				is_ns_connect: is_ns_connect,
				ns_connect_note: ns_connect_note,
				is_pasted: is_pasted,
				is_ns_paste: is_ns_paste,
				ns_paste_note: ns_paste_note,
				is_initial_source: is_initial_source,
				comparing_source_ids: comparing_source_ids,
				comparison_initial_source_id: comparison_initial_source_id,
				active_symptom_type: active_symptom_type,
				main_parent_initial_symptom_id: main_parent_initial_symptom_id,
				comparison_option: comparison_option,
				saved_comparison_quelle_id: saved_comparison_quelle_id,
				connection_or_paste_type: connection_or_paste_type,
				action: 'swap_connection'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} else if(response.status == "success"){
					// instantReflectionMatchedSections(dataArray);

					// Re-Calling the main comprasion function to get the updated data.
					var initial_source = $('#batch_result_form_1').find("#initial_source_save").val();
					var comparing_sources = $('#batch_result_form_1').find("#comparing_sources_save").val();
					var arznei_id = $('#batch_result_form_1').find("#arznei_id_save").val();
					var similarity_rate = $('#batch_result_form_1').find("#similarity_rate_save").val();
					var comparison_option = $('#batch_result_form_1').find("#comparison_option_save").val();
					var comparison_language = $('#batch_result_form_1').find("#comparison_language_save").val();
					var error_count = 0;

					if(initial_source == ""){
						error_count++;
					}
					if(comparing_sources == ""){
						error_count++;
					}
					if(arznei_id == ""){
						error_count++;
					}
					if(similarity_rate == ""){
						error_count++;
					}
					if(comparison_option == ""){
						error_count++;
					}
					if(comparison_language == ""){
						error_count++;
					}
					
					if(error_count == 0){
						$(".progress-connection-thead").remove();
						$('.batch-search-result-form').remove();
						$('.batch-result-form').remove();
						$('#symptom_comparison_form').addClass('unclickable');
						$('#compare_submit_btn').prop('disabled', true);
						$('#search_submit_btn').prop('disabled', true);
						$("#comparison_name").val('');
						
						if(!$(".result-sub-btn").hasClass('hidden'))
							$(".result-sub-btn").addClass('hidden');

						if(!$(".head-panel-sub-ul").hasClass('hidden'))
							$(".head-panel-sub-ul").addClass('hidden');

						if($('.comparison-only-column').hasClass('hidden'))
							$('.comparison-only-column').removeClass('hidden');
						$("#numberOfRecord").html(0);

						$("#column_heading_symptom").html('Symptom');
						var loadingHtml = '';
						loadingHtml += '<tr id="loadingTr">';
						loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
						loadingHtml += '</tr>';

						$('#resultTable tbody').html(loadingHtml);

						var data = 'initial_source='+initial_source+'&comparing_sources='+comparing_sources+'&arznei_id='+arznei_id+'&similarity_rate='+similarity_rate+'&comparison_option='+comparison_option+'&comparison_language='+comparison_language;

						$(".progress-thead").remove();
						var progressBarHtml = '';
						progressBarHtml += '<thead class="progress-thead heading-table-bg">';
						progressBarHtml += '	<tr>';
						progressBarHtml += ' 		<th colspan="5">';
						progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
						progressBarHtml += ' 			<div class="progress comparison-progress">';
						progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
						progressBarHtml += ' 			</div>';
						progressBarHtml += ' 		</th>';
						progressBarHtml += '	</tr>';
						progressBarHtml += '</thead>';
						$('#resultTable thead').after(progressBarHtml);

						// start the process
						var matchedSymptomIds = [];
						process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds );

					} else {
						$("#global_msg_container").html('<p class="text-center">Something went wrong, Could not update the contect. Please reload the comparison and try again!</p>');
						$("#globalMsgModal").modal('show');
						$(".progress-connection-thead").remove();
						$('.batch-result-form').removeClass('unclickable');
					}

				}else{
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try!</p>');
					$("#globalMsgModal").modal('show');
					$(".progress-connection-thead").remove();
					$('.batch-result-form').removeClass('unclickable');

					$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
					$('#connecting_btn_'+uniqueId).prop('disabled', false);
					$('#paste_btn_'+uniqueId).prop('disabled', false);
				}
			}
		}).fail(function (response) {
			console.log(response);
			$("#global_msg_container").html('<p class="text-center">Operation failed. Something went worng, please reload the page and try again!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');

			$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
			$('#connecting_btn_'+uniqueId).prop('disabled', false);
			$('#paste_btn_'+uniqueId).prop('disabled', false);
		})
		// Making connection operation end
	}
}

function instantReflectionMatchedSections(dataArray){
	if (typeof dataArray !== 'undefined' && dataArray !== null){
		console.log("task 2");
		var uniqueId = (typeof dataArray['uniqueId'] !== 'undefined' && dataArray['uniqueId'] !== null && dataArray['uniqueId'] != "") ? dataArray['uniqueId'] : "";
	    var source_arznei_id = (typeof dataArray['source_arznei_id'] !== 'undefined' && dataArray['source_arznei_id'] !== null && dataArray['source_arznei_id'] != "") ? dataArray['source_arznei_id'] : "";
	    var initial_source_id = (typeof dataArray['initial_source_id'] !== 'undefined' && dataArray['initial_source_id'] !== null && dataArray['initial_source_id'] != "") ? dataArray['initial_source_id'] : "";
	    var initial_original_source_id = (typeof dataArray['initial_original_source_id'] !== 'undefined' && dataArray['initial_original_source_id'] !== null && dataArray['initial_original_source_id'] != "") ? dataArray['initial_original_source_id'] : "";
	    var initial_source_code = (typeof dataArray['initial_source_code'] !== 'undefined' && dataArray['initial_source_code'] !== null && dataArray['initial_source_code'] != "") ? dataArray['initial_source_code'] : "";
	    var comparing_source_id = (typeof dataArray['comparing_source_id'] !== 'undefined' && dataArray['comparing_source_id'] !== null && dataArray['comparing_source_id'] != "") ? dataArray['comparing_source_id'] : "";
	    var comparing_original_source_id = (typeof dataArray['comparing_original_source_id'] !== 'undefined' && dataArray['comparing_original_source_id'] !== null && dataArray['comparing_original_source_id'] != "") ? dataArray['comparing_original_source_id'] : "";
	    var comparing_source_code = (typeof dataArray['comparing_source_code'] !== 'undefined' && dataArray['comparing_source_code'] !== null && dataArray['comparing_source_code'] != "") ? dataArray['comparing_source_code'] : "";
	    var initial_source_symptom_id = (typeof dataArray['initial_source_symptom_id'] !== 'undefined' && dataArray['initial_source_symptom_id'] !== null && dataArray['initial_source_symptom_id'] != "") ? dataArray['initial_source_symptom_id'] : "";
	    
	    var initial_source_symptom_de = (typeof dataArray['initial_source_symptom_de'] !== 'undefined' && dataArray['initial_source_symptom_de'] !== null && dataArray['initial_source_symptom_de'] != "") ? dataArray['initial_source_symptom_de'] : "";
	    var initial_source_symptom_en = (typeof dataArray['initial_source_symptom_en'] !== 'undefined' && dataArray['initial_source_symptom_en'] !== null && dataArray['initial_source_symptom_en'] != "") ? dataArray['initial_source_symptom_en'] : "";

	    var comparing_source_symptom_de = (typeof dataArray['comparing_source_symptom_de'] !== 'undefined' && dataArray['comparing_source_symptom_de'] !== null && dataArray['comparing_source_symptom_de'] != "") ? dataArray['comparing_source_symptom_de'] : "";
	    var comparing_source_symptom_en = (typeof dataArray['comparing_source_symptom_en'] !== 'undefined' && dataArray['comparing_source_symptom_en'] !== null && dataArray['comparing_source_symptom_en'] != "") ? dataArray['comparing_source_symptom_en'] : "";

	    var initial_source_symptom_highlighted_de = (typeof dataArray['initial_source_symptom_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? dataArray['initial_source_symptom_highlighted_de'] : "";
	    var initial_source_symptom_highlighted_en = (typeof dataArray['initial_source_symptom_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_en'] !== null && dataArray['initial_source_symptom_highlighted_en'] != "") ? dataArray['initial_source_symptom_highlighted_en'] : "";

	    var comparing_source_symptom_highlighted_de = (typeof dataArray['comparing_source_symptom_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? dataArray['comparing_source_symptom_highlighted_de'] : "";
	    var comparing_source_symptom_highlighted_en = (typeof dataArray['comparing_source_symptom_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_en'] !== null && dataArray['comparing_source_symptom_highlighted_en'] != "") ? dataArray['comparing_source_symptom_highlighted_en'] : "";

	    var initial_source_symptom_before_conversion_de = (typeof dataArray['initial_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_de'] !== null && dataArray['initial_source_symptom_before_conversion_de'] != "") ? dataArray['initial_source_symptom_before_conversion_de'] : "";
	    var initial_source_symptom_before_conversion_en = (typeof dataArray['initial_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_en'] !== null && dataArray['initial_source_symptom_before_conversion_en'] != "") ? dataArray['initial_source_symptom_before_conversion_en'] : "";

		var comparing_source_symptom_before_conversion_de = (typeof dataArray['comparing_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_de'] !== null && dataArray['comparing_source_symptom_before_conversion_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_de'] : "";
		var comparing_source_symptom_before_conversion_en = (typeof dataArray['comparing_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_en'] !== null && dataArray['comparing_source_symptom_before_conversion_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_en'] : "";

		var initial_source_symptom_before_conversion_highlighted_de = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_de'] : "";
		var initial_source_symptom_before_conversion_highlighted_en = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_en'] : "";			    
		var comparing_source_symptom_before_conversion_highlighted_de = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_de'] : "";
		var comparing_source_symptom_before_conversion_highlighted_en = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_en'] : "";

		var individual_comparison_language = (typeof dataArray['individual_comparison_language'] !== 'undefined' && dataArray['individual_comparison_language'] !== null && dataArray['individual_comparison_language'] != "") ? dataArray['individual_comparison_language'] : "";	
		
		var comparing_source_symptom_id = (typeof dataArray['comparing_source_symptom_id'] !== 'undefined' && dataArray['comparing_source_symptom_id'] !== null && dataArray['comparing_source_symptom_id'] != "") ? dataArray['comparing_source_symptom_id'] : "";
		var matching_percentage = (typeof dataArray['matching_percentage'] !== 'undefined' && dataArray['matching_percentage'] !== null && dataArray['matching_percentage'] != "") ? dataArray['matching_percentage'] : "";
		var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
		var is_ns_connect = (typeof dataArray['is_ns_connect'] !== 'undefined' && dataArray['is_ns_connect'] !== null && dataArray['is_ns_connect'] != "") ? dataArray['is_ns_connect'] : "";
		var ns_connect_note = (typeof dataArray['ns_connect_note'] !== 'undefined' && dataArray['ns_connect_note'] !== null && dataArray['ns_connect_note'] != "") ? dataArray['ns_connect_note'] : "";
		var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
		var is_ns_paste = (typeof dataArray['is_ns_paste'] !== 'undefined' && dataArray['is_ns_paste'] !== null && dataArray['is_ns_paste'] != "") ? dataArray['is_ns_paste'] : "";
		var ns_paste_note = (typeof dataArray['ns_paste_note'] !== 'undefined' && dataArray['ns_paste_note'] !== null && dataArray['ns_paste_note'] != "") ? dataArray['ns_paste_note'] : "";
		var is_initial_source = (typeof dataArray['is_initial_source'] !== 'undefined' && dataArray['is_initial_source'] !== null && dataArray['is_initial_source'] != "") ? dataArray['is_initial_source'] : "";
		var similarity_rate = (typeof dataArray['similarity_rate'] !== 'undefined' && dataArray['similarity_rate'] !== null && dataArray['similarity_rate'] != "") ? dataArray['similarity_rate'] : "";
		var active_symptom_type = (typeof dataArray['active_symptom_type'] !== 'undefined' && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
		var comparing_source_ids = (typeof dataArray['comparing_source_ids'] !== 'undefined' && dataArray['comparing_source_ids'] !== null && dataArray['comparing_source_ids'] != "") ? dataArray['comparing_source_ids'] : "";
		var matched_symptom_ids = (typeof dataArray['matched_symptom_ids'] !== 'undefined' && dataArray['matched_symptom_ids'] !== null && dataArray['matched_symptom_ids'] != "") ? dataArray['matched_symptom_ids'] : "";
		var comparison_option = (typeof dataArray['comparison_option'] !== 'undefined' && dataArray['comparison_option'] !== null && dataArray['comparison_option'] != "") ? dataArray['comparison_option'] : "";
		var savedComparisonComparingSourceIds = (typeof dataArray['savedComparisonComparingSourceIds'] !== 'undefined' && dataArray['savedComparisonComparingSourceIds'] !== null && dataArray['savedComparisonComparingSourceIds'] != "") ? dataArray['savedComparisonComparingSourceIds'] : "";
		var is_unmatched_symptom = (typeof dataArray['is_unmatched_symptom'] !== 'undefined' && dataArray['is_unmatched_symptom'] !== null && dataArray['is_unmatched_symptom'] != "") ? dataArray['is_unmatched_symptom'] : "";
		var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
		var comparison_initial_source_id = (typeof dataArray['comparison_initial_source_id'] !== 'undefined' && dataArray['comparison_initial_source_id'] !== null && dataArray['comparison_initial_source_id'] != "") ? dataArray['comparison_initial_source_id'] : "";
		var connections_main_parent_symptom_id = (typeof dataArray['connections_main_parent_symptom_id'] !== 'undefined' && dataArray['connections_main_parent_symptom_id'] !== null && dataArray['connections_main_parent_symptom_id'] != "") ? dataArray['connections_main_parent_symptom_id'] : "";
		var error_count = (typeof dataArray['error_count'] !== 'undefined' && dataArray['error_count'] !== null && dataArray['error_count'] != "") ? dataArray['error_count'] : "";
		var mainParentInitialSymptomIdsArr = (typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'] != "") ? dataArray['mainParentInitialSymptomIdsArr'] : [];
		var removable_sets = (typeof dataArray['removable_sets'] !== 'undefined' && dataArray['removable_sets'] !== null && dataArray['removable_sets'] != "") ? dataArray['removable_sets'] : [];
		var appendable_materia_medica_symptom_id = (typeof dataArray['appendable_materia_medica_symptom_id'] !== 'undefined' && dataArray['appendable_materia_medica_symptom_id'] !== null && dataArray['appendable_materia_medica_symptom_id'] != "") ? dataArray['appendable_materia_medica_symptom_id'] : "";

		// Upading the updatable data start
		if(main_parent_initial_symptom_id != "" && comparing_source_ids != ""){
			if (typeof mainParentInitialSymptomIdsArr !== 'undefined' && mainParentInitialSymptomIdsArr !== null && mainParentInitialSymptomIdsArr.length > 0) {
				var errors = 0; 
				var countElement = mainParentInitialSymptomIdsArr.length;
				$.each(mainParentInitialSymptomIdsArr, function( key, value ) {
					var eachMainParentId = value;
					// Loop is not getting called one by one that is why it is gat the same value for dataArray['matched_symptom_ids'] even if i change the value below. I will look into this 
					$.ajax({
						type: 'POST',
						url: 'get-instant-reflection-data.php',
						data: {
							initial_symptom_id: eachMainParentId,
							initial_source_id: comparison_initial_source_id,
							comparing_source_ids: comparing_source_ids,
							matched_symptom_ids: dataArray['matched_symptom_ids'],
							arznei_id: source_arznei_id,
							similarity_rate: similarity_rate,
							comparison_option: comparison_option,
							individual_comparison_language: individual_comparison_language,
							action: 'matched_section',
						},
						dataType: "json",
						success: function( response ) {
							console.log(response);
							if(response.status == "success"){
								var resultData = null;
								try {
									resultData = JSON.parse(response.result_data); 
								} catch (e) {
									resultData = response.result_data;
								}
								var returnedMatchIds = (typeof response.matched_symptom_ids !== 'undefined' && response.matched_symptom_ids !== null && response.matched_symptom_ids != "") ? response.matched_symptom_ids : dataArray['matched_symptom_ids'];
								var html = "";
								$.each(resultData, function( key, value ) {
									
									var uniqueId = value.initial_source_symptom_id+value.comparing_source_symptom_id;
							  		var commentClasses = "";
							  		var footnoteClasses = "";
							  		var FVBtnClasses = "FV-btn";

							  		if(value.is_final_version_available != 0)
							  			FVBtnClasses += " active";

							  		if(value.has_connections == 1){
							  			if(value.is_further_connections_are_saved == 1){
							  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved';
							  				FVBtnClasses += " link-active-saved";
							  			}
							  			else{
							  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active';
							  				FVBtnClasses += " link-active";
							  			}
							  			var vBtnTitle = 'Earlier connections';
							  			var vBtnDisable = '';
							  		} else {
							  			var vBtnClasses = 'vbtn';
							  			var vBtnTitle = 'Earlier connections';
							  			var vBtnDisable = 'link-disabled unclickable';
							  		}

							  		var nsc_btn_disabled = 'link-disabled unclickable';
							  		var connection_btn_disabled = '';
							  		var nsp_btn_disabled = 'link-disabled unclickable';
							  		var paste_btn_disabled = '';
							  		// var connect_btn_class = 'connecting-btn btn btn-default';
							  		var paste_btn_class = 'paste-btn';
							  		// var nscClasses = 'nsc btn btn-default';
							  		var nspClasses = 'nsp';
							  		var connection_edit_btn_class = "connecting-edit-btn";
								  	var paste_edit_btn_class = "paste-edit-btn";
							  		if(value.is_pasted == 1){
							  			connection_btn_disabled = 'link-disabled unclickable'; 
							  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
							  			nsp_btn_disabled = '';
							  			paste_btn_class = 'paste-btn active link-active';
							  			if(value.is_ns_paste == 1){
							  				nspClasses = 'nsp active link-active';
							  			}
							  		}

							  		if(value.is_ns_connect_disabled == 0)
							  			nsc_btn_disabled = '';
							  		else
							  			nsc_btn_disabled = 'link-disabled unclickable';
							  		if(value.is_connect_disabled == 1){
							  			connection_btn_disabled = 'link-disabled unclickable'; 
							  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
							  		}
							  		if(value.is_ns_paste_disabled == 1)
							  			nsp_btn_disabled = 'link-disabled unclickable'; 
							  		if(value.is_paste_disabled == 1){
							  			paste_btn_disabled = 'link-disabled unclickable';
							  			paste_edit_btn_class += ' link-disabled unclickable';
							  		}

							  		// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
							  		
							  		var rowClass = "";
							  		var saved_version_source_code = "";
							  		var instantReflectionClass = 'instant-reflection-set-'+value.main_parent_initial_symptom_id;

							  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
				  					var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

				  					var translation_toggle_btn_type = "";
				  					var active_symptom_id = "";
				  					var active_original_source_id = "";

							  		if(value.active_symptom_type == "comparing"){
							  			active_symptom_id = value.comparing_source_symptom_id;
				  						active_original_source_id = value.comparing_original_source_id;
							  			translation_toggle_btn_type = "comparative";

							  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
							  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
							  			var activeSymptomId = value.comparing_source_symptom_id;
							  			var activeSymptomType = "comparing";
							  			var displaySourceCode = value.comparing_source_code;
							  			// var displaySymptomString = comparingSymptomHighlightedEndcod;
							  			
							  			var comparing_source_symptom_highlighted_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
							  			var comparing_source_symptom_highlighted_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
							  			var displaySymptomString = "";

							  			if(value.comparison_language == "en"){
											displaySymptomString = comparing_source_symptom_highlighted_en;
							  				
							  				if(comparing_source_original_language == "en"){
							  					var tmpString = "";
							  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+comparing_source_symptom_highlighted_en+'</div>' : "";
							  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
							  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparing_source_symptom_highlighted_en+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				}
							  			} else {
							  				displaySymptomString = comparing_source_symptom_highlighted_de;

							  				if(comparing_source_original_language == "de"){
							  					var tmpString = "";
							  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+comparing_source_symptom_highlighted_de+'</div>' : "";
							  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
							  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparing_source_symptom_highlighted_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  			}

							  			var displayPercentage = value.percentage+"%";
							  			var rowInlineStyle = 'style="border-top: dotted; border-color: #ddd;"';
							  			// var symptomColumnInlineStyle = 'style="padding-left: 40px;"';
							  			var symptomColumnInlineStyle = '';

							  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
								  			commentClasses += ' active';
								  		}
								  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
								  			footnoteClasses += ' active';
								  		}

								  		if(displaySourceCode != value.comparing_saved_version_source_code)
				  							saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';
							  		}else{
							  			active_symptom_id = value.initial_source_symptom_id;
				  						active_original_source_id = value.initial_original_source_id;
							  			translation_toggle_btn_type = "initial";

							  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
							  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
							  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
							  			var activeSymptomId = value.initial_source_symptom_id;
							  			var activeSymptomType = "initial";
							  			var displaySourceCode = value.initial_source_code;
							  			
							  			var initial_source_symptom_de = (typeof(value.initial_source_symptom_de) != "undefined" && value.initial_source_symptom_de !== null && value.initial_source_symptom_de != "") ? b64DecodeUnicode(value.initial_source_symptom_de) : "";
							  			var initial_source_symptom_en = (typeof(value.initial_source_symptom_en) != "undefined" && value.initial_source_symptom_en !== null && value.initial_source_symptom_en != "") ? b64DecodeUnicode(value.initial_source_symptom_en) : "";
							  			var displaySymptomString = "";

							  			if(value.comparison_language == "en"){
							  				displaySymptomString = initial_source_symptom_en;

							  				if(initial_source_original_language == "en"){
							  					var tmpString = "";
							  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+initial_source_symptom_en+'</div>' : "";
							  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+initial_source_symptom_de+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+initial_source_symptom_de+'</div>' : "";
							  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initial_source_symptom_en+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  			} else {
							  				displaySymptomString = initial_source_symptom_de;

							  				if(initial_source_original_language == "de"){
							  					var tmpString = "";
							  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+initial_source_symptom_de+'</div>' : "";
							  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+initial_source_symptom_en+'</div>' : "";
							  					
							  					displaySymptomString = tmpString;
							  				}
							  				else{
							  					var tmpString = "";
							  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+initial_source_symptom_en+'</div>' : "";
							  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initial_source_symptom_de+'</div>' : "";

							  					displaySymptomString = tmpString;
							  				}
							  			}
							  			var displayPercentage = "";
							  			var rowInlineStyle = '';
							  			var symptomColumnInlineStyle = '';

							  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
								  			commentClasses += ' active';
								  		}
								  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
								  			footnoteClasses += ' active';
								  		}
								  		var rowClass = " initial-source-symptom-row";

								  		if(displaySourceCode != value.initial_saved_version_source_code)
				  							saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';
							  		}

							  		if(value.is_unmatched_symptom == 1){
							  			translation_toggle_btn_type = "comparative";
							  			instantReflectionClass += ' instant-reflection-unmatched-row';
							  		}

							  		// Matched symptom ids
							  		$('.matched-symptom-ids').val(returnedMatchIds);

							  		//console.log(comparingSymptomHighlightedEndcod);
							  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+rowClass+'" '+rowInlineStyle+'>';
							  		html += '	<td style="width: 12%;" class="text-center">'+displaySourceCode+saved_version_source_code+'</td>';
							  		html += '	<td '+symptomColumnInlineStyle+'>'+displaySymptomString+'</td>';
							  		html += '	<td style="width: 5%;" class="text-center">'+displayPercentage+'</td>';
							  		html += '	<th style="width: 17%;">';
							  		html += '		<ul class="info-linkage-group">';
							  		html += '			<li>';
							  		html += '				<a onclick="showInfo('+activeSymptomId+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)" data-item="edit" data-unique-id="'+uniqueId+'" data-active-symptom-id="'+active_symptom_id+'" data-active-original-source-id="'+active_original_source_id+'"><i class="fas fa-pencil-alt"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+activeSymptomId+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+activeSymptomId+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
							  		html += '			</li>';
							  		html += '			<li>';
							  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
							  		html += '			</li>';
							  		if(value.is_final_version_available != 0){
							  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
							  			var fvName = "";
							  			var fvTitle = "";
							  			if(value.is_final_version_available == 1){
							  				fvName = "CE";
							  				fvTitle = "Connect edit";
							  			} else if(value.is_final_version_available == 2){
							  				fvName = "PE";
							  				fvTitle = "Paste edit";
							  			}
							  			html += '			<li>';
								  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
								  		html += '			</li>';
							  		}
							  		html += '			<li>';
							  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="0" data-is-recompare="0" data-initial-source-id="'+value.initial_source_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-connections-main-parent-symptom-id="'+value.connections_main_parent_symptom_id+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="'+activeSymptomType+'" data-is-connection-loaded="0" data-comparing-source-ids="'+comparing_source_ids+'" data-source-arznei-id="'+value.source_arznei_id+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain=""><i class="fas fa-plus"></i></a>';
							  		html += '			</li>';
							  		html += '		</ul>';
							  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+value.source_arznei_id+'">';
							  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
							  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
							  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
							  		html += '		<input type="hidden" name="initial_source_year[]" id="initial_source_year_'+uniqueId+'" value="'+value.initial_source_year+'">';
							  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
							  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
							  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
							  		html += '		<input type="hidden" name="comparing_source_year[]" id="comparing_source_year_'+uniqueId+'" value="'+value.comparing_source_year+'">';
							  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
							  		
							  		// Initial German
							  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
									html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
									html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
									html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';
									// Initial English
							  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
									html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
									html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
									html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';
									// Comparing German
									html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';
							  		// Comparing English
									html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
							  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';
							  		
							  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';

							  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
							  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.percentage+'">';
							  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="0">';
							  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="0">';
							  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="">';
							  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
							  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
							  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
							  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
							  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+returnedMatchIds+'">';
							  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+value.main_parent_initial_symptom_id+'">';
							  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+value.connections_main_parent_symptom_id+'">';
							  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+value.similarity_rate+'">';
							  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+value.active_symptom_type+'">';
							  		html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+comparing_source_ids+'">';
							  		html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+value.comparison_option+'">';
							  		html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+savedComparisonComparingSourceIds+'">';
							  		html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+value.is_unmatched_symptom+'">';
							  		html += '	</th>';
							  		if(value.active_symptom_type == "comparing"){
								  		html += '	<th style="width: 19%;" class="">';
								  		html += '		<ul class="command-group">';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="nsc '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="connecting-btn '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-link"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connection_edit_btn_class+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="3">CE</a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'">P</a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+paste_edit_btn_class+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="4">PE</a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="swap_connect_btn_'+uniqueId+'" class="swap-connect-btn" title="Swap connect" data-item="swap-connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-should-swap-connect-be-active="'+value.should_swap_connect_be_active+'"><i class="fas fa-recycle"></i></a>';
								  		html += '			</li>';
								  		html += '		</ul>';
								  		html += '	</th>';
							  		}
							  		else{
							  			html += '	<th style="width: 19%;" class="">';
							  			html += '	</th>';	
							  		}
							  		html += '</tr>';
								});

								if(html != ""){
									if(eachMainParentId == appendable_materia_medica_symptom_id) {
										// If a symptom is disconnected from save comparison/Materia medica it will appear in the bottom as initial symptom of That Materia medica
										// $('.initial-source-symptom-row').last().after(html);
										var trId =$('.initial-source-symptom-row').last().attr('id'); // table row ID row_10096
										var res = trId.split("_");
										if(typeof res[1] !== 'undefined' && res[1] !== null && res[1] != "")
											$('.instant-reflection-set-'+res[1]).last().after(html);
									} else {
										$('.instant-reflection-set-'+eachMainParentId).remove();
										$('#instant_reflection_loder_'+eachMainParentId).after(html);
										$('#instant_reflection_loder_'+eachMainParentId).remove();
									}
									
								}

							}else{
								console.log( response );
								$("#global_msg_container").html('<p class="text-center">Could not reflect the changes instantly!, please reload and try.</p>');
								$("#globalMsgModal").modal('show');

								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');
								//$('.instant-reflection-set-'+eachMainParentId).removeClass('unclickable');
								$('#instant_reflection_loder_'+eachMainParentId).remove();
							}
						}
					}).fail(function (response) {
						errors++;
						console.log( response );
					}).then(function(){
						if(--countElement == 0){
							if(errors != 0){
								$("#global_msg_container").html('<p class="text-center">Could not reflect the changes instantly!, please reload the page and try again.</p>');
								$("#globalMsgModal").modal('show');

								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');
								//$('.instant-reflection-set-'+eachMainParentId).removeClass('unclickable');
								$('#instant_reflection_loder_'+eachMainParentId).remove();
							}else{
								instantReflectionUnmatchedSections(dataArray);
							}
						}
					});
					
				});
				
				if(errors != 0)
				{
					$("#globalMsgModal").addClass('global-msg-modal-reload');
					$("#global_msg_container").html('<p class="text-center">Could not reflect the changes instantly!, Need a page reload.</p>');
					$("#globalMsgModal").modal('show');

					$(".progress-connection-thead").remove();
					$('.batch-result-form').removeClass('unclickable');
					//$('.instant-reflection-set-'+eachMainParentId).removeClass('unclickable');
					$('#instant_reflection_loder_'+eachMainParentId).remove();
				}
			}
			else
				instantReflectionUnmatchedSections(dataArray);
		}
		else
		{
			$("#global_msg_container").html('<p class="text-center">Operation could not complete. Please reload the page and try again!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');
		}
	}
}

function instantReflectionUnmatchedSections(dataArray){
	if (typeof dataArray !== 'undefined' && dataArray !== null){
		console.log("task 3");
		var uniqueId = (typeof dataArray['uniqueId'] !== 'undefined' && dataArray['uniqueId'] !== null && dataArray['uniqueId'] != "") ? dataArray['uniqueId'] : "";
	    var source_arznei_id = (typeof dataArray['source_arznei_id'] !== 'undefined' && dataArray['source_arznei_id'] !== null && dataArray['source_arznei_id'] != "") ? dataArray['source_arznei_id'] : "";
	    var initial_source_id = (typeof dataArray['initial_source_id'] !== 'undefined' && dataArray['initial_source_id'] !== null && dataArray['initial_source_id'] != "") ? dataArray['initial_source_id'] : "";
	    var initial_original_source_id = (typeof dataArray['initial_original_source_id'] !== 'undefined' && dataArray['initial_original_source_id'] !== null && dataArray['initial_original_source_id'] != "") ? dataArray['initial_original_source_id'] : "";
	    var initial_source_code = (typeof dataArray['initial_source_code'] !== 'undefined' && dataArray['initial_source_code'] !== null && dataArray['initial_source_code'] != "") ? dataArray['initial_source_code'] : "";
	    var comparing_source_id = (typeof dataArray['comparing_source_id'] !== 'undefined' && dataArray['comparing_source_id'] !== null && dataArray['comparing_source_id'] != "") ? dataArray['comparing_source_id'] : "";
	    var comparing_original_source_id = (typeof dataArray['comparing_original_source_id'] !== 'undefined' && dataArray['comparing_original_source_id'] !== null && dataArray['comparing_original_source_id'] != "") ? dataArray['comparing_original_source_id'] : "";
	    var comparing_source_code = (typeof dataArray['comparing_source_code'] !== 'undefined' && dataArray['comparing_source_code'] !== null && dataArray['comparing_source_code'] != "") ? dataArray['comparing_source_code'] : "";
	    var initial_source_symptom_id = (typeof dataArray['initial_source_symptom_id'] !== 'undefined' && dataArray['initial_source_symptom_id'] !== null && dataArray['initial_source_symptom_id'] != "") ? dataArray['initial_source_symptom_id'] : "";
	    
	    var initial_source_symptom_de = (typeof dataArray['initial_source_symptom_de'] !== 'undefined' && dataArray['initial_source_symptom_de'] !== null && dataArray['initial_source_symptom_de'] != "") ? dataArray['initial_source_symptom_de'] : "";
	    var initial_source_symptom_en = (typeof dataArray['initial_source_symptom_en'] !== 'undefined' && dataArray['initial_source_symptom_en'] !== null && dataArray['initial_source_symptom_en'] != "") ? dataArray['initial_source_symptom_en'] : "";

	    var comparing_source_symptom_de = (typeof dataArray['comparing_source_symptom_de'] !== 'undefined' && dataArray['comparing_source_symptom_de'] !== null && dataArray['comparing_source_symptom_de'] != "") ? dataArray['comparing_source_symptom_de'] : "";
	    var comparing_source_symptom_en = (typeof dataArray['comparing_source_symptom_en'] !== 'undefined' && dataArray['comparing_source_symptom_en'] !== null && dataArray['comparing_source_symptom_en'] != "") ? dataArray['comparing_source_symptom_en'] : "";

	    var initial_source_symptom_highlighted_de = (typeof dataArray['initial_source_symptom_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? dataArray['initial_source_symptom_highlighted_de'] : "";
	    var initial_source_symptom_highlighted_en = (typeof dataArray['initial_source_symptom_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_en'] !== null && dataArray['initial_source_symptom_highlighted_en'] != "") ? dataArray['initial_source_symptom_highlighted_en'] : "";

	    var comparing_source_symptom_highlighted_de = (typeof dataArray['comparing_source_symptom_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? dataArray['comparing_source_symptom_highlighted_de'] : "";
	    var comparing_source_symptom_highlighted_en = (typeof dataArray['comparing_source_symptom_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_en'] !== null && dataArray['comparing_source_symptom_highlighted_en'] != "") ? dataArray['comparing_source_symptom_highlighted_en'] : "";

	    var initial_source_symptom_before_conversion_de = (typeof dataArray['initial_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_de'] !== null && dataArray['initial_source_symptom_before_conversion_de'] != "") ? dataArray['initial_source_symptom_before_conversion_de'] : "";
	    var initial_source_symptom_before_conversion_en = (typeof dataArray['initial_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_en'] !== null && dataArray['initial_source_symptom_before_conversion_en'] != "") ? dataArray['initial_source_symptom_before_conversion_en'] : "";

		var comparing_source_symptom_before_conversion_de = (typeof dataArray['comparing_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_de'] !== null && dataArray['comparing_source_symptom_before_conversion_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_de'] : "";
		var comparing_source_symptom_before_conversion_en = (typeof dataArray['comparing_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_en'] !== null && dataArray['comparing_source_symptom_before_conversion_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_en'] : "";

		var initial_source_symptom_before_conversion_highlighted_de = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_de'] : "";
		var initial_source_symptom_before_conversion_highlighted_en = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_en'] : "";			    
		var comparing_source_symptom_before_conversion_highlighted_de = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_de'] : "";
		var comparing_source_symptom_before_conversion_highlighted_en = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_en'] : "";

		var individual_comparison_language = (typeof dataArray['individual_comparison_language'] !== 'undefined' && dataArray['individual_comparison_language'] !== null && dataArray['individual_comparison_language'] != "") ? dataArray['individual_comparison_language'] : "";
		
		var comparing_source_symptom_id = (typeof dataArray['comparing_source_symptom_id'] !== 'undefined' && dataArray['comparing_source_symptom_id'] !== null && dataArray['comparing_source_symptom_id'] != "") ? dataArray['comparing_source_symptom_id'] : "";
		var matching_percentage = (typeof dataArray['matching_percentage'] !== 'undefined' && dataArray['matching_percentage'] !== null && dataArray['matching_percentage'] != "") ? dataArray['matching_percentage'] : "";
		var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
		var is_ns_connect = (typeof dataArray['is_ns_connect'] !== 'undefined' && dataArray['is_ns_connect'] !== null && dataArray['is_ns_connect'] != "") ? dataArray['is_ns_connect'] : "";
		var ns_connect_note = (typeof dataArray['ns_connect_note'] !== 'undefined' && dataArray['ns_connect_note'] !== null && dataArray['ns_connect_note'] != "") ? dataArray['ns_connect_note'] : "";
		var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
		var is_ns_paste = (typeof dataArray['is_ns_paste'] !== 'undefined' && dataArray['is_ns_paste'] !== null && dataArray['is_ns_paste'] != "") ? dataArray['is_ns_paste'] : "";
		var ns_paste_note = (typeof dataArray['ns_paste_note'] !== 'undefined' && dataArray['ns_paste_note'] !== null && dataArray['ns_paste_note'] != "") ? dataArray['ns_paste_note'] : "";
		var is_initial_source = (typeof dataArray['is_initial_source'] !== 'undefined' && dataArray['is_initial_source'] !== null && dataArray['is_initial_source'] != "") ? dataArray['is_initial_source'] : "";
		var similarity_rate = (typeof dataArray['similarity_rate'] !== 'undefined' && dataArray['similarity_rate'] !== null && dataArray['similarity_rate'] != "") ? dataArray['similarity_rate'] : "";
		var active_symptom_type = (typeof dataArray['active_symptom_type'] !== 'undefined' && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
		var comparing_source_ids = (typeof dataArray['comparing_source_ids'] !== 'undefined' && dataArray['comparing_source_ids'] !== null && dataArray['comparing_source_ids'] != "") ? dataArray['comparing_source_ids'] : "";
		var matched_symptom_ids = (typeof dataArray['matched_symptom_ids'] !== 'undefined' && dataArray['matched_symptom_ids'] !== null && dataArray['matched_symptom_ids'] != "") ? dataArray['matched_symptom_ids'] : "";
		var comparison_option = (typeof dataArray['comparison_option'] !== 'undefined' && dataArray['comparison_option'] !== null && dataArray['comparison_option'] != "") ? dataArray['comparison_option'] : "";
		var savedComparisonComparingSourceIds = (typeof dataArray['savedComparisonComparingSourceIds'] !== 'undefined' && dataArray['savedComparisonComparingSourceIds'] !== null && dataArray['savedComparisonComparingSourceIds'] != "") ? dataArray['savedComparisonComparingSourceIds'] : "";
		var is_unmatched_symptom = (typeof dataArray['is_unmatched_symptom'] !== 'undefined' && dataArray['is_unmatched_symptom'] !== null && dataArray['is_unmatched_symptom'] != "") ? dataArray['is_unmatched_symptom'] : "";
		var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
		var comparison_initial_source_id = (typeof dataArray['comparison_initial_source_id'] !== 'undefined' && dataArray['comparison_initial_source_id'] !== null && dataArray['comparison_initial_source_id'] != "") ? dataArray['comparison_initial_source_id'] : "";
		var connections_main_parent_symptom_id = (typeof dataArray['connections_main_parent_symptom_id'] !== 'undefined' && dataArray['connections_main_parent_symptom_id'] !== null && dataArray['connections_main_parent_symptom_id'] != "") ? dataArray['connections_main_parent_symptom_id'] : "";
		var error_count = (typeof dataArray['error_count'] !== 'undefined' && dataArray['error_count'] !== null && dataArray['error_count'] != "") ? dataArray['error_count'] : "";
		var mainParentInitialSymptomIdsArr = (typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'] != "") ? dataArray['mainParentInitialSymptomIdsArr'] : [];
		var removable_sets = (typeof dataArray['removable_sets'] !== 'undefined' && dataArray['removable_sets'] !== null && dataArray['removable_sets'] != "") ? dataArray['removable_sets'] : [];

		// Upading the updatable data start
		if(main_parent_initial_symptom_id != "" && comparing_source_ids != ""){
			console.log(main_parent_initial_symptom_id+" "+comparing_source_ids);
			// Update the remaining unmatch symptoms if required
			// if((is_connected == 1 || is_pasted == 1) && parseInt(matching_percentage) < parseInt(similarity_rate) && matched_symptom_ids != ""){
				// Updating the remaining unmatch symptoms start
				$.ajax({
					type: 'POST',
					url: 'get-instant-reflection-data.php',
					data: {
						// removable_symptom_id: removableSymptomId,
						// connected_with_symptom_id: connectedWithSymptomId,
						removable_sets: removable_sets,
						initial_source_id: comparison_initial_source_id,
						comparing_source_ids: comparing_source_ids,
						matched_symptom_ids: matched_symptom_ids,
						arznei_id: source_arznei_id,
						similarity_rate: similarity_rate,
						active_matching_percentage: matching_percentage,
						comparison_option: comparison_option,
						individual_comparison_language: individual_comparison_language,
						action: 'unmatched_section',
					},
					dataType: "json",
					success: function( response ) {
						console.log(response);
						if(response.status == "success"){
							// if(response.is_update_needed_unmatch_symptoms == 1){
								if(typeof(response.result_data) != "undefined" && response.result_data !== null && response.result_data != ""){
									var resultData = null;
									try {
										resultData = JSON.parse(response.result_data); 
									} catch (e) {
										resultData = response.result_data;
									}

									var html = "";
									var countElement = resultData.length;
									$.each(resultData, function( key, value ) {
										
										var uniqueId = value.initial_source_symptom_id+value.comparing_source_symptom_id;
								  		var commentClasses = "";
								  		var footnoteClasses = "";
								  		var FVBtnClasses = "FV-btn";

								  		if(value.is_final_version_available != 0)
								  			FVBtnClasses += " active";

								  		if(value.has_connections == 1){
								  			if(value.is_further_connections_are_saved == 1){
								  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved';
								  				FVBtnClasses += " link-active-saved";
								  			}
								  			else{
								  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active';
								  				FVBtnClasses += " link-active";
								  			}
								  			var vBtnTitle = 'Earlier connections';
								  			var vBtnDisable = '';
								  		} else {
								  			var vBtnClasses = 'vbtn';
								  			var vBtnTitle = 'Earlier connections';
								  			var vBtnDisable = 'link-disabled unclickable';
								  		}

								  		var nsc_btn_disabled = 'link-disabled unclickable';
								  		var connection_btn_disabled = '';
								  		var nsp_btn_disabled = 'link-disabled unclickable';
								  		var paste_btn_disabled = '';
								  		// var connect_btn_class = 'connecting-btn btn btn-default';
								  		var paste_btn_class = 'paste-btn';
								  		// var nscClasses = 'nsc btn btn-default';
								  		var nspClasses = 'nsp';
								  		var connection_edit_btn_class = "connecting-edit-btn";
								  		var paste_edit_btn_class = "paste-edit-btn";
								  		if(value.is_pasted == 1){
								  			connection_btn_disabled = 'link-disabled unclickable'; 
								  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
								  			nsp_btn_disabled = '';
								  			paste_btn_class = 'paste-btn active link-active';
								  			if(value.is_ns_paste == 1){
								  				nspClasses = 'nsp active link-active';
								  			}
								  		}

								  		if(value.is_ns_connect_disabled == 0)
								  			nsc_btn_disabled = '';
								  		else
								  			nsc_btn_disabled = 'link-disabled unclickable';
								  		if(value.is_connect_disabled == 1){
								  			connection_btn_disabled = 'link-disabled unclickable'; 
								  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
								  		}
								  		if(value.is_ns_paste_disabled == 1)
								  			nsp_btn_disabled = 'link-disabled unclickable'; 
								  		if(value.is_paste_disabled == 1){
								  			paste_btn_disabled = 'link-disabled unclickable';
								  			paste_edit_btn_class += ' link-disabled unclickable';
								  		}

								  		// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
								  		
								  		var rowClass = "";
								  		var saved_version_source_code = "";
								  		var instantReflectionClass = 'instant-reflection-set-'+value.main_parent_initial_symptom_id;

								  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
				  						var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

				  						var translation_toggle_btn_type = "comparative";
				  						var active_symptom_id = "";
				  						var active_original_source_id = "";
								  		if(value.active_symptom_type == "comparing"){
								  			active_symptom_id = value.comparing_source_symptom_id;
				  							active_original_source_id = value.comparing_original_source_id;
								  			translation_toggle_btn_type = "comparative";

								  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
								  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
								  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
								  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
								  			var activeSymptomId = value.comparing_source_symptom_id;
								  			var activeSymptomType = "comparing";
								  			var displaySourceCode = value.comparing_source_code;
								  			// var displaySymptomString = comparingSymptomHighlightedEndcod;
								  			
								  			var comparing_source_symptom_highlighted_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
								  			var comparing_source_symptom_highlighted_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
								  			var displaySymptomString = "";

								  			if(value.comparison_language == "en"){
												displaySymptomString = comparing_source_symptom_highlighted_en;
								  				
								  				if(comparing_source_original_language == "en"){
								  					var tmpString = "";
								  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+comparing_source_symptom_highlighted_en+'</div>' : "";
								  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  				else{
								  					var tmpString = "";
								  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
								  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparing_source_symptom_highlighted_en+'</div>' : "";

								  					displaySymptomString = tmpString;
								  				}
								  			} else {
								  				displaySymptomString = comparing_source_symptom_highlighted_de;

								  				if(comparing_source_original_language == "de"){
								  					var tmpString = "";
								  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+comparing_source_symptom_highlighted_de+'</div>' : "";
								  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  				else{
								  					var tmpString = "";
								  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
								  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparing_source_symptom_highlighted_de+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  			}

								  			var displayPercentage = "";
								  			var rowInlineStyle = 'style="border-top: dotted; border-color: #ddd;"';
								  			// var symptomColumnInlineStyle = 'style="padding-left: 40px;"';
								  			var symptomColumnInlineStyle = '';

								  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
									  			commentClasses += ' active';
									  		}
									  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
									  			footnoteClasses += ' active';
									  		}

									  		if(displaySourceCode != value.comparing_saved_version_source_code)
				  								saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';
								  		}else{
								  			active_symptom_id = value.initial_source_symptom_id;
				  							active_original_source_id = value.initial_original_source_id;
								  			translation_toggle_btn_type = "comparative";

								  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
								  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
								  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
								  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
								  			var activeSymptomId = value.initial_source_symptom_id;
								  			var activeSymptomType = "initial";
								  			var displaySourceCode = value.initial_source_code;

								  			var initial_source_symptom_de = (typeof(value.initial_source_symptom_de) != "undefined" && value.initial_source_symptom_de !== null && value.initial_source_symptom_de != "") ? b64DecodeUnicode(value.initial_source_symptom_de) : "";
								  			var initial_source_symptom_en = (typeof(value.initial_source_symptom_en) != "undefined" && value.initial_source_symptom_en !== null && value.initial_source_symptom_en != "") ? b64DecodeUnicode(value.initial_source_symptom_en) : "";
								  			var displaySymptomString = "";

								  			if(value.comparison_language == "en"){
								  				displaySymptomString = initial_source_symptom_en;

								  				if(initial_source_original_language == "en"){
								  					var tmpString = "";
								  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+initial_source_symptom_en+'</div>' : "";
								  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+initial_source_symptom_de+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  				else{
								  					var tmpString = "";
								  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+initial_source_symptom_de+'</div>' : "";
								  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initial_source_symptom_en+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  			}
								  			else{
								  				displaySymptomString = initial_source_symptom_de;

								  				if(initial_source_original_language == "de"){
								  					var tmpString = "";
								  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+initial_source_symptom_de+'</div>' : "";
								  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+initial_source_symptom_en+'</div>' : "";
								  					
								  					displaySymptomString = tmpString;
								  				}
								  				else{
								  					var tmpString = "";
								  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+initial_source_symptom_en+'</div>' : "";
								  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initial_source_symptom_de+'</div>' : "";

								  					displaySymptomString = tmpString;
								  				}
								  			}

								  			var displayPercentage = "";
								  			var rowInlineStyle = '';
								  			var symptomColumnInlineStyle = '';

								  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
									  			commentClasses += ' active';
									  		}
									  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
									  			footnoteClasses += ' active';
									  		}
									  		// color not needed for remaining un-matched symptoms
									  		// var rowClass = " initial-source-symptom-row";
									  		var rowClass = "";
									  		if(displaySourceCode != value.initial_saved_version_source_code)
				  								saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';

								  		}

								  		if(value.is_unmatched_symptom == 1){
								  			translation_toggle_btn_type = "comparative";
								  			instantReflectionClass += ' instant-reflection-unmatched-row';
								  		}

								  		// var rowClass = "";
								  		// if(value.is_initial_source == 1)
								  		// 	var rowClass = "initial-source-symptom-row";

								  		// Matched symptom ids
								  		$('.matched-symptom-ids').val(response.matched_symptom_ids);

								  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+rowClass+'" '+rowInlineStyle+'>';
								  		html += '	<td style="width: 12%;" class="text-center">'+displaySourceCode+saved_version_source_code+'</td>';
								  		html += '	<td '+symptomColumnInlineStyle+'>'+displaySymptomString+'</td>';
								  		html += '	<td style="width: 5%;" class="text-center">'+displayPercentage+'</td>';
								  		html += '	<th style="width: 17%;">';
								  		html += '		<ul class="info-linkage-group">';
								  		html += '			<li>';
								  		html += '				<a onclick="showInfo('+activeSymptomId+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)" data-item="edit" data-unique-id="'+uniqueId+'" data-active-symptom-id="'+active_symptom_id+'" data-active-original-source-id="'+active_original_source_id+'"><i class="fas fa-pencil-alt"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+activeSymptomId+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+activeSymptomId+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
								  		html += '			</li>';
								  		html += '			<li>';
								  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
								  		html += '			</li>';
								  		if(value.is_final_version_available != 0){
								  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
								  			var fvName = "";
								  			var fvTitle = "";
								  			if(value.is_final_version_available == 1){
								  				fvName = "CE";
								  				fvTitle = "Connect edit";
								  			} else if(value.is_final_version_available == 2){
								  				fvName = "PE";
								  				fvTitle = "Paste edit";
								  			}
								  			html += '			<li>';
									  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
									  		html += '			</li>';
								  		}
								  		html += '			<li>';
								  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+' '+vBtnDisable+'" title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="0" data-is-recompare="0" data-initial-source-id="'+value.initial_source_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-connections-main-parent-symptom-id="'+value.connections_main_parent_symptom_id+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="'+activeSymptomType+'" data-is-connection-loaded="0" data-comparing-source-ids="'+comparing_source_ids+'" data-source-arznei-id="'+value.source_arznei_id+'" data-saved-comparison-comparing-source-ids="'+savedComparisonComparingSourceIds+'" data-removable-row-class-chain=""><i class="fas fa-plus"></i></a>';
								  		html += '			</li>';
								  		html += '		</ul>';
								  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+value.source_arznei_id+'">';
								  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
								  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
								  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
								  		html += '		<input type="hidden" name="initial_source_year[]" id="initial_source_year_'+uniqueId+'" value="'+value.initial_source_year+'">';
								  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
								  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
								  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
								  		html += '		<input type="hidden" name="comparing_source_year[]" id="comparing_source_year_'+uniqueId+'" value="'+value.comparing_source_year+'">';
								  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
								  		
								  		// Initial German
								  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
										html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
										html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
										html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';
										// Initial English
								  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
										html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
										html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
										html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';
										// Comparing German
										html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';
								  		// Comparing English
										html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
								  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';
								  		
								  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';

								  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
								  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.percentage+'">';
								  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="0">';
								  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="0">';
								  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="">';
								  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
								  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
								  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
								  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
								  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+response.matched_symptom_ids+'">';
								  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+value.main_parent_initial_symptom_id+'">';
								  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+value.connections_main_parent_symptom_id+'">';
								  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+value.similarity_rate+'">';
								  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+value.active_symptom_type+'">';
								  		html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+comparing_source_ids+'">';
								  		html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+value.comparison_option+'">';
								  		html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+savedComparisonComparingSourceIds+'">';
								  		html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+value.is_unmatched_symptom+'">';
								  		html += '	</th>';
								  		if(value.active_symptom_type == "comparing"){
									  		html += '	<th style="width: 19%;" class="">';
									  		html += '		<ul class="command-group">';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="nsc '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="connecting-btn '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-link"></i></a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connection_edit_btn_class+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="3">CE</a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'">P</a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+paste_edit_btn_class+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="4">PE</a>';
									  		html += '			</li>';
									  		html += '			<li>';
									  		html += '				<a href="javascript:void(0)" id="swap_connect_btn_'+uniqueId+'" class="swap-connect-btn" title="Swap connect" data-item="swap-connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-should-swap-connect-be-active="'+value.should_swap_connect_be_active+'"><i class="fas fa-arrows-alt-v"></i></a>';
									  		html += '			</li>';
									  		html += '		</ul>';
									  		html += '	</th>';
								  		}
								  		else{
								  			html += '	<th style="width: 19%;" class="">';
								  			html += '	</th>';	
								  		}
								  		html += '</tr>';


								  		if(--countElement == 0){
								  			if(html != ""){
												$('.instant-reflection-unmatched-row').remove();
												$('#instant_reflection_unmatched_rows_loder').after(html);
												$('#instant_reflection_unmatched_rows_loder').remove();
											}
											symptomConnectFinal(dataArray);
								  		}
									});
								}
								else
								{
									symptomConnectFinal(dataArray);
									$('#instant_reflection_unmatched_rows_loder').remove();
									$('.instant-reflection-unmatched-row').remove();
								}
							// }
							// else
							// 	symptomConnectFinal(dataArray);
						}else{
							symptomConnectFinal(dataArray);
							console.log( response );
							$("#global_msg_container").html('<p class="text-center">Could not reflect the changes instantly!, please reload and try.</p>');
							$("#globalMsgModal").modal('show');

							// $('.instant-reflection-unmatched-row').removeClass('unclickable');
							$('#instant_reflection_unmatched_rows_loder').remove();
						}
					}
				}).fail(function (response) {
					symptomConnectFinal(dataArray);
					console.log( response );
					$("#global_msg_container").html('<p class="text-center">Could not reflect the changes instantly!, please reload the page and try again.</p>');
					$("#globalMsgModal").modal('show');

					// $('.instant-reflection-unmatched-row').removeClass('unclickable');
					$('#instant_reflection_unmatched_rows_loder').remove();
				});
				// Updating the remaining unmatch symptoms end
			// }
			// else
			// 	symptomConnectFinal(dataArray);
		}
		else
		{
			$("#global_msg_container").html('<p class="text-center">Operation could not complete. Please reload the page and try again!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');
		}
	}
}

function symptomConnectFinal(dataArray){
	console.log("Hello");
	var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
	var operation = (typeof dataArray['operation'] !== 'undefined' && dataArray['operation'] !== null && dataArray['operation'] != "") ? dataArray['operation'] : "";
	var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
	var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
	if(operation == 'connect' && is_connected == 0)
		$("#v_btn_"+main_parent_initial_symptom_id).click();
	else if(operation == 'paste' && is_pasted == 0)
		$("#v_btn_"+main_parent_initial_symptom_id).click();
	$(".progress-connection-thead").remove();
	$('.batch-result-form').removeClass('unclickable');
}

$(document).on('hidden.bs.modal', '#reloadPageModal', function(){
  	location.reload();
});

$(document).on('hidden.bs.modal', '.global-msg-modal-reload', function(){
  	location.reload();
});

var connectionDataArray = [];

$(document).on('click', '.connecting-btn', function(){
	console.log(connectionDataArray);
	var uniqueId = $(this).attr("data-unique-id");
	var dataArray = [];

    dataArray['saved_comparison_quelle_id'] = $("#saved_comparison_quelle_id").val();
    dataArray['uniqueId'] = $(this).attr("data-unique-id");
    dataArray['source_arznei_id'] = $("#source_arznei_id_"+uniqueId).val();
    dataArray['initial_source_id'] = $("#initial_source_id_"+uniqueId).val();
    dataArray['initial_original_source_id'] = $("#initial_original_source_id_"+uniqueId).val();
    dataArray['initial_source_code'] = $("#initial_source_code_"+uniqueId).val();
    dataArray['comparing_source_id'] = $("#comparing_source_id_"+uniqueId).val();
    dataArray['comparing_original_source_id'] = $("#comparing_original_source_id_"+uniqueId).val();
    dataArray['comparing_source_code'] = $("#comparing_source_code_"+uniqueId).val();
    dataArray['initial_source_symptom_id'] = $("#initial_source_symptom_id_"+uniqueId).val();
    dataArray['initial_source_symptom_de'] = $("#initial_source_symptom_de_"+uniqueId).val();
    dataArray['initial_source_symptom_en'] = $("#initial_source_symptom_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_de'] = $("#comparing_source_symptom_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_en'] = $("#comparing_source_symptom_en_"+uniqueId).val();
    dataArray['initial_source_symptom_highlighted_de'] = $("#initial_source_symptom_highlighted_de_"+uniqueId).val();
    dataArray['initial_source_symptom_highlighted_en'] = $("#initial_source_symptom_highlighted_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_highlighted_de'] = $("#comparing_source_symptom_highlighted_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_highlighted_en'] = $("#comparing_source_symptom_highlighted_en_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_de'] = $("#initial_source_symptom_before_conversion_de_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_en'] = $("#initial_source_symptom_before_conversion_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_de'] = $("#comparing_source_symptom_before_conversion_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_en'] = $("#comparing_source_symptom_before_conversion_en_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_highlighted_de'] = $("#initial_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_highlighted_en'] = $("#initial_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_highlighted_de'] = $("#comparing_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_highlighted_en'] = $("#comparing_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
	dataArray['individual_comparison_language'] = $("#individual_comparison_language_"+uniqueId).val();
	dataArray['comparing_source_symptom_id'] = $("#comparing_source_symptom_id_"+uniqueId).val();
	dataArray['matching_percentage'] = $("#matching_percentage_"+uniqueId).val();
	dataArray['is_connected'] = $("#is_connected_"+uniqueId).val();
	dataArray['is_ns_connect'] = $("#is_ns_connect_"+uniqueId).val();
	dataArray['ns_connect_note'] = $("#ns_connect_note_"+uniqueId).val();
	dataArray['is_pasted'] = $("#is_pasted_"+uniqueId).val();
	dataArray['is_ns_paste'] = $("#is_ns_paste_"+uniqueId).val();
	dataArray['ns_paste_note'] = $("#ns_paste_note_"+uniqueId).val();
	dataArray['is_initial_source'] = $("#is_initial_source_"+uniqueId).val();
	dataArray['similarity_rate'] = $("#similarity_rate_"+uniqueId).val();
	dataArray['active_symptom_type'] = $("#active_symptom_type_"+uniqueId).val();
	dataArray['comparing_source_ids'] = $("#comparing_source_ids_"+uniqueId).val();
	dataArray['matched_symptom_ids'] = $("#matched_symptom_ids_"+uniqueId).val();
	dataArray['comparison_option'] = $("#comparison_option_"+uniqueId).val();
	dataArray['savedComparisonComparingSourceIds'] = $("#saved_comparison_comparing_source_ids_"+uniqueId).val();
	dataArray['is_unmatched_symptom'] = $("#is_unmatched_symptom_"+uniqueId).val();
	dataArray['main_parent_initial_symptom_id'] = $(this).attr("data-main-parent-initial-symptom-id");
	dataArray['comparison_initial_source_id'] = $(this).attr("data-comparison-initial-source-id");
	dataArray['connections_main_parent_symptom_id'] = $("#connections_main_parent_symptom_id_"+uniqueId).val();
	dataArray['error_count'] = 0;
	dataArray['mainParentInitialSymptomIdsArr'] = [];
	dataArray['removable_sets'] = [];
	dataArray['operation'] = 'connect';
	dataArray['connection_type'] = 'normal';
	// This field is not there in the hidden input fields of the table rows
	dataArray['connection_or_paste_type'] = 1; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

	dataArray['initial_source_year'] = $("#initial_source_year_"+uniqueId).val();
	dataArray['comparing_source_year'] = $("#comparing_source_year_"+uniqueId).val();

	var sub_connetions_array = [];
	var updateable_symptom_ids = [];
	var removable_sets = [];

	if(dataArray['is_connected'] == 1)
	{
		if(dataArray['active_symptom_type'] == "initial"){
			uniqueId  = dataArray['initial_source_symptom_id']+dataArray['comparing_source_symptom_id'];
		}
		var numConnectedRow = $('.connected-row-'+dataArray['initial_source_symptom_id']).length;
		$("#row_"+uniqueId).remove();
		$("#row_before_connection_"+uniqueId).attr("id","row_"+uniqueId);
		$("#row_"+uniqueId).show();
		if(numConnectedRow < 2){
			$("#v_btn_"+dataArray['initial_source_symptom_id']).attr("data-is-connection-loaded", 0);
	    	$("#v_btn_"+dataArray['initial_source_symptom_id']).html('<i class="fas fa-plus"></i>');
	    	$("#v_btn_"+dataArray['initial_source_symptom_id']).removeClass();
	    	$("#v_btn_"+dataArray['initial_source_symptom_id']).addClass('vbtn link-disabled');
		}
		

		$(".instant-reflection-row-"+dataArray['comparing_source_symptom_id']).each(function() {
			var trId =$(this).attr('id');
			var splitIds = trId.split("_");
			var seperatedUniqueId = (typeof(splitIds[1]) != "undefined" && splitIds[1] !== null && splitIds[1] != "") ? splitIds[1] : "";
			$("#v_btn_"+seperatedUniqueId).attr("data-is-connection-loaded", 0);
    		$("#v_btn_"+seperatedUniqueId).html('<i class="fas fa-plus"></i>');
			$("#v_btn_"+seperatedUniqueId).removeClass();
			$("#v_btn_"+seperatedUniqueId).addClass('vbtn link-disabled');
			var mainParentInitialSymptomId = $("#main_parent_initial_symptom_id_"+seperatedUniqueId).val();
			var newUniqueId = dataArray['initial_source_symptom_id']+dataArray['comparing_source_symptom_id']+mainParentInitialSymptomId;
			console.log(newUniqueId);
			$("#row_"+newUniqueId).remove();
		})
		// var con = confirm("Are you sure you want to disconnect?");
		// if (con)
		// {

		// }
		// else
		// {
		// 	return false;
		// }
	}
	else
	{
		var tempArr = [];
		if(dataArray['active_symptom_type'] == "comparing"){
			tempArr['active_symptom_type'] = "comparing";
			tempArr['initial_symptom_id'] = dataArray['initial_source_symptom_id'];
			tempArr['comparing_symptom_id'] = dataArray['comparing_source_symptom_id']; 
		}else if(dataArray['active_symptom_type'] == "initial"){
			tempArr['active_symptom_type'] = "initial";
			tempArr['initial_symptom_id'] = dataArray['initial_source_symptom_id'];
			tempArr['comparing_symptom_id'] = dataArray['comparing_source_symptom_id'];
		}

		var symptomText = (typeof(dataArray['comparing_source_symptom_highlighted_de']) != "undefined" && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? b64DecodeUnicode(dataArray['comparing_source_symptom_highlighted_de']) : "";
		var iniSymptomText = (typeof(dataArray['initial_source_symptom_highlighted_de']) != "undefined" && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? b64DecodeUnicode(dataArray['initial_source_symptom_highlighted_de']) : "";
		
		$("#v_btn_"+dataArray['initial_source_symptom_id']).attr("data-is-connection-loaded", 1);
		$("#v_btn_"+dataArray['initial_source_symptom_id']).html('<i class="fas fa-minus"></i>');
		$("#v_btn_"+dataArray['initial_source_symptom_id']).removeClass('link-disabled');
		$("#v_btn_"+dataArray['initial_source_symptom_id']).addClass('vbtn-has-connection active link-active');
		// $("#v_btn_"+dataArray['initial_source_symptom_id']).click();

		// $("#row_"+uniqueId).remove();
		$("#row_"+uniqueId).attr("id","row_before_connection_"+uniqueId);
		$("#row_before_connection_"+uniqueId).hide();
		var commentClasses = "";
		var footnoteClasses = "";
		var translation_toggle_btn_type = "";
		var vBtnClasses = "vbtn link-disabled";
		var nscClasses = "nsc";
		var nsc_btn_disabled = "";
		var nspClasses = "nsp";
		var nsp_btn_disabled = "link-disabled unclickable";
		var connect_btn_class = "connecting-btn active link-active";
		var connection_btn_disabled = "";
		var paste_btn_class = "paste-btn";
		var paste_btn_disabled = "link-disabled unclickable";
		var html = '';
		html += '<tr id="row_'+uniqueId+'" class="unsaved-connection-row connected-row-'+dataArray['initial_source_symptom_id']+'" style="display: ;">';
		html += '	<td style="width: 12%;" class="text-center">'+dataArray['comparing_source_code']+'</td>';
		html += '	<td>'+symptomText+'</td>';
		html += '	<td style="width: 5%;" class="text-center">'+dataArray['matching_percentage']+'%</td>';
		html += '	<th style="width: 17%;">';
		html += '		<ul class="info-linkage-group">';
  		html += '			<li>';
  		html += '				<a onclick="showInfo('+dataArray['comparing_source_symptom_id']+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+dataArray['comparing_source_symptom_id']+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+dataArray['comparing_source_symptom_id']+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'">T</a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+'" data-unique-id="'+uniqueId+'"><i class="fas fa-plus"></i></a>';
  		html += '			</li>';
  		html += '		</ul>';
  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="1">';
  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+dataArray['initial_source_symptom_id']+'">';
  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+dataArray['comparing_source_symptom_id']+'">';
  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+dataArray['main_parent_initial_symptom_id']+'">';
  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="comparing">';
		html += '	</th>';
		html += '	<th style="width: 19%;">';
		html += '		<ul class="command-group">';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'"><i class="fas fa-link"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'">P</a>';
  		html += '			</li>';
  		html += '		</ul>';
		html += '	</th>';
		html += '</tr>';
		$('#row_'+dataArray['main_parent_initial_symptom_id']).after(html);

		$(".instant-reflection-row-"+dataArray['comparing_source_symptom_id']).each(function() {
			var trId =$(this).attr('id');
			var splitIds = trId.split("_");
			var seperatedUniqueId = (typeof(splitIds[1]) != "undefined" && splitIds[1] !== null && splitIds[1] != "") ? splitIds[1] : "";
			$("#v_btn_"+seperatedUniqueId).removeClass('link-disabled');
			$("#v_btn_"+seperatedUniqueId).addClass('vbtn-has-connection active link-active');
			var mainParentInitialSymptomId = $("#main_parent_initial_symptom_id_"+seperatedUniqueId).val();
			var newUniqueId = dataArray['initial_source_symptom_id']+dataArray['comparing_source_symptom_id']+mainParentInitialSymptomId;

			var commentClasses = "";
			var footnoteClasses = "";
			var translation_toggle_btn_type = "";
			var vBtnClasses = "vbtn link-disabled";
			var nscClasses = "nsc";
			var nsc_btn_disabled = "";
			var nspClasses = "nsp";
			var nsp_btn_disabled = "link-disabled unclickable";
			var connect_btn_class = "connecting-btn active link-active";
			var connection_btn_disabled = "";
			var paste_btn_class = "paste-btn";
			var paste_btn_disabled = "link-disabled unclickable";
			var iniHtml = "";
			iniHtml += '<tr id="row_'+newUniqueId+'" class="unsaved-connection-row connected-row-'+seperatedUniqueId+'" style="display: none;">';
			iniHtml += '	<td style="width: 12%;" class="text-center">'+dataArray['initial_source_code']+'</td>';
			iniHtml += '	<td>'+iniSymptomText+'</td>';
			iniHtml += '	<td style="width: 5%;" class="text-center">'+dataArray['matching_percentage']+'%</td>';
			iniHtml += '	<th style="width: 17%;">';
			iniHtml += '		<ul class="info-linkage-group">';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a onclick="showInfo('+dataArray['initial_source_symptom_id']+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+newUniqueId+'"><i class="fas fa-info-circle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="'+commentClasses+'" id="comment_icon_'+newUniqueId+'" onclick="showComment('+dataArray['initial_source_symptom_id']+', '+newUniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+newUniqueId+'"><i class="fas fa-comment-alt"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+newUniqueId+'" onclick="showFootnote('+dataArray['initial_source_symptom_id']+', '+newUniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+newUniqueId+'"><i class="fas fa-sticky-note"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+newUniqueId+'">T</a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="v_btn_'+newUniqueId+'" class="'+vBtnClasses+'" data-unique-id="'+newUniqueId+'"><i class="fas fa-plus"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '		</ul>';
	  		iniHtml += '		<input type="hidden" name="is_connected[]" id="is_connected_'+newUniqueId+'" value="1">';
	  		iniHtml += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+newUniqueId+'" value="'+dataArray['initial_source_symptom_id']+'">';
	  		iniHtml += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+newUniqueId+'" value="'+dataArray['comparing_source_symptom_id']+'">';
	  		iniHtml += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+newUniqueId+'" value="'+mainParentInitialSymptomId+'">';
	  		iniHtml += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+newUniqueId+'" value="initial">';
	  		iniHtml += '	</th>';
			iniHtml += '	<th style="width: 19%;">';
			iniHtml += '		<ul class="command-group">';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="nsc_btn_'+newUniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+newUniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="connecting_btn_'+newUniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+newUniqueId+'"><i class="fas fa-link"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="nsp_btn_'+newUniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+newUniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="paste_btn_'+newUniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+newUniqueId+'">P</a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '		</ul>';
			iniHtml += '	</th>';
			iniHtml += '</tr>';
			$('#'+trId).after(iniHtml);
		});
	}
});

$(document).on('click', '.swap-connect-btn', function(){
	var uniqueId = $(this).attr("data-unique-id");
    var initial_original_source_id = $("#initial_original_source_id_"+uniqueId).val();
    var comparing_original_source_id = $("#comparing_original_source_id_"+uniqueId).val();
	var should_swap_connect_be_active = $(this).attr("data-should-swap-connect-be-active");
	if(should_swap_connect_be_active == 0){
		$("#global_msg_container").html('<p class="text-center">Error! This symptom has already been connected.</p>');
		$("#globalMsgModal").modal('show');
	} else if (initial_original_source_id == comparing_original_source_id){
		$("#global_msg_container").html('<p class="text-center">Error! You can not perform this action, both symptoms are from the same source originally please check!</p>');
		$("#globalMsgModal").modal('show');
	}else {
		// Loding the confirm modal content
		$("#swap_connect_yes_btn").hide();
		$("#swap_connection_confirm_modal_loader .loading-msg").removeClass('hidden');
		$("#swap_connection_confirm_modal_loader .error-msg").html('');
		if($("#swap_connection_confirm_modal_loader").hasClass('hidden'))
			$("#swap_connection_confirm_modal_loader").removeClass('hidden');

		$("#populated_swap_connection_confirm_data").remove();
		$("#swapConnectionConfirmModal").modal('show');
		
		var uniqueId = $(this).attr("data-unique-id");
		var main_parent_initial_symptom_id = $(this).attr("data-main-parent-initial-symptom-id");
		var comparison_initial_source_id = $(this).attr("data-comparison-initial-source-id");
		var initial_source_symptom_id = $("#initial_source_symptom_id_"+uniqueId).val();
		var comparing_source_symptom_id = $("#comparing_source_symptom_id_"+uniqueId).val();
		var comparison_option = $("#comparison_option_"+uniqueId).val();
		var active_symptom_type = $("#active_symptom_type_"+uniqueId).val();
		var individual_comparison_language = $("#individual_comparison_language_"+uniqueId).val();

		var active_symptom_id = "";
		if(active_symptom_type == "comparing")
			active_symptom_id = comparing_source_symptom_id;
		else
			active_symptom_id = initial_source_symptom_id;

		$.ajax({
			type: 'POST',
			url: 'get-symptom-info.php',
			data: {
				symptom_id: active_symptom_id,
				individual_comparison_language: individual_comparison_language
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					if(!$("#swap_connection_confirm_modal_loader").hasClass('hidden'))
						$("#swap_connection_confirm_modal_loader").addClass('hidden');

					var BeschreibungOriginal_de = (resultData.BeschreibungOriginal_de != "" && resultData.BeschreibungOriginal_de != null) ? resultData.BeschreibungOriginal_de : "";
					var BeschreibungOriginal_en = (resultData.BeschreibungOriginal_en != "" && resultData.BeschreibungOriginal_en != null) ? resultData.BeschreibungOriginal_en : "";
					var searchable_text_de = (resultData.searchable_text_de != "" && resultData.searchable_text_de != null) ? resultData.searchable_text_de : "";
					var searchable_text_en = (resultData.searchable_text_en != "" && resultData.searchable_text_en != null) ? resultData.searchable_text_en : "";
					var BeschreibungFull_de = (resultData.BeschreibungFull_de != "" && resultData.BeschreibungFull_de != null) ? resultData.BeschreibungFull_de : "";
					var BeschreibungFull_en = (resultData.BeschreibungFull_en != "" && resultData.BeschreibungFull_en != null) ? resultData.BeschreibungFull_en : "";
					var symptom_string_de = "";
					var symptom_string_en = "";
					if(comparison_option == 1){
						symptom_string_de = searchable_text_de;
						symptom_string_en = searchable_text_en;
					}
					else{
						symptom_string_de = BeschreibungFull_de;
						symptom_string_en = BeschreibungFull_en;
					}
					var symptom_string = "";
					if(individual_comparison_language == "en")
						symptom_string = symptom_string_en;
					else
						symptom_string = symptom_string_de;

					var html = '';
					html += '<div id="populated_swap_connection_confirm_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<p class="">Do you really want to connect viceversa? <span class="text-danger">Please copy the symptom!</span></p>';
					html += '			<input type="hidden" name="swap_connection_confirm_unique_id" id="swap_connection_confirm_unique_id" value='+uniqueId+'>';
					html += '			<input type="hidden" name="swap_connection_confirm_main_parent_initial_symptom_id" id="swap_connection_confirm_main_parent_initial_symptom_id" value='+main_parent_initial_symptom_id+'>';
					html += '			<input type="hidden" name="swap_connection_confirm_comparison_initial_source_id" id="swap_connection_confirm_comparison_initial_source_id" value='+comparison_initial_source_id+'>';
					html += '		</div>';
					html += '	</div>';
					html += '	<div class="row">';
					html += '		<div class="col-sm-10 col-sm-offset-1">';
					html += '			<p class="text-center text-success" id="copy_confirmation"></p>';
					html += '			<p class="copy-text-box" title="Click to copy the symptom" onclick="copy(this)">'+symptom_string+'</p>';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';

					$("#swap_connection_confirm_modal_container").append(html);
					$("#swap_connect_yes_btn").show();
				}else{
					$("#swap_connect_yes_btn").hide();
					$("#swap_connection_confirm_modal_loader .loading-msg").addClass('hidden');
					$("#swap_connection_confirm_modal_loader .error-msg").html('Something went wrong!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#swap_connect_yes_btn").hide();
			$("#swap_connection_confirm_modal_loader .loading-msg").addClass('hidden');
			$("#swap_connection_confirm_modal_loader .error-msg").html('Something went wrong!');
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
});

function copy(that){
	var inp =document.createElement('input');
	document.body.appendChild(inp)
	inp.value =that.textContent
	inp.select();
	document.execCommand('copy',false);
	inp.remove();
	$("#copy_confirmation").html('<strong>COPIED</strong>');
	setTimeout(function(){
	    $("#copy_confirmation").html('');
	}, 2000);
}

// Perform the swap connect
$(document).on('click', '#swap_connect_yes_btn', function(){
	var uniqueId = $("#swap_connection_confirm_unique_id").val();
	$("#swapConnectionConfirmModal").modal('hide');
	if(uniqueId != ""){
		var dataArray = [];

	    dataArray['saved_comparison_quelle_id'] = $("#saved_comparison_quelle_id").val();

	    dataArray['uniqueId'] = uniqueId;
	    dataArray['source_arznei_id'] = $("#source_arznei_id_"+uniqueId).val();
	    dataArray['initial_source_id'] = $("#initial_source_id_"+uniqueId).val();
	    dataArray['initial_original_source_id'] = $("#initial_original_source_id_"+uniqueId).val();
	    dataArray['initial_source_code'] = $("#initial_source_code_"+uniqueId).val();
	    dataArray['comparing_source_id'] = $("#comparing_source_id_"+uniqueId).val();
	    dataArray['comparing_original_source_id'] = $("#comparing_original_source_id_"+uniqueId).val();
	    dataArray['comparing_source_code'] = $("#comparing_source_code_"+uniqueId).val();
	    dataArray['initial_source_symptom_id'] = $("#initial_source_symptom_id_"+uniqueId).val();
	    dataArray['initial_source_symptom_de'] = $("#initial_source_symptom_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_en'] = $("#initial_source_symptom_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_de'] = $("#comparing_source_symptom_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_en'] = $("#comparing_source_symptom_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_highlighted_de'] = $("#initial_source_symptom_highlighted_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_highlighted_en'] = $("#initial_source_symptom_highlighted_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_highlighted_de'] = $("#comparing_source_symptom_highlighted_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_highlighted_en'] = $("#comparing_source_symptom_highlighted_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_de'] = $("#initial_source_symptom_before_conversion_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_en'] = $("#initial_source_symptom_before_conversion_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_de'] = $("#comparing_source_symptom_before_conversion_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_en'] = $("#comparing_source_symptom_before_conversion_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_highlighted_de'] = $("#initial_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_highlighted_en'] = $("#initial_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_highlighted_de'] = $("#comparing_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_highlighted_en'] = $("#comparing_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
		dataArray['individual_comparison_language'] = $("#individual_comparison_language_"+uniqueId).val();
		dataArray['comparing_source_symptom_id'] = $("#comparing_source_symptom_id_"+uniqueId).val();
		dataArray['matching_percentage'] = $("#matching_percentage_"+uniqueId).val();
		dataArray['is_connected'] = $("#is_connected_"+uniqueId).val();
		dataArray['is_ns_connect'] = $("#is_ns_connect_"+uniqueId).val();
		dataArray['ns_connect_note'] = $("#ns_connect_note_"+uniqueId).val();
		dataArray['is_pasted'] = $("#is_pasted_"+uniqueId).val();
		dataArray['is_ns_paste'] = $("#is_ns_paste_"+uniqueId).val();
		dataArray['ns_paste_note'] = $("#ns_paste_note_"+uniqueId).val();
		dataArray['is_initial_source'] = $("#is_initial_source_"+uniqueId).val();
		dataArray['similarity_rate'] = $("#similarity_rate_"+uniqueId).val();
		dataArray['active_symptom_type'] = $("#active_symptom_type_"+uniqueId).val();
		dataArray['comparing_source_ids'] = $("#comparing_source_ids_"+uniqueId).val();
		dataArray['matched_symptom_ids'] = $("#matched_symptom_ids_"+uniqueId).val();
		dataArray['comparison_option'] = $("#comparison_option_"+uniqueId).val();
		dataArray['savedComparisonComparingSourceIds'] = $("#saved_comparison_comparing_source_ids_"+uniqueId).val();
		dataArray['is_unmatched_symptom'] = $("#is_unmatched_symptom_"+uniqueId).val();
		dataArray['main_parent_initial_symptom_id'] = $("#swap_connection_confirm_main_parent_initial_symptom_id").val();
		dataArray['comparison_initial_source_id'] = $("#swap_connection_confirm_comparison_initial_source_id").val();
		dataArray['connections_main_parent_symptom_id'] = $("#connections_main_parent_symptom_id_"+uniqueId).val();
		dataArray['error_count'] = 0;
		dataArray['mainParentInitialSymptomIdsArr'] = [];
		dataArray['removable_sets'] = [];
		dataArray['operation'] = 'connect';
		dataArray['connection_type'] = 'swap';
		// This field is not there in the hidden input fields of the table rows
		dataArray['connection_or_paste_type'] = 2; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

		var sub_connetions_array = [];
		var updateable_symptom_ids = [];
		var removable_sets = [];

		$('#paste_btn_'+uniqueId).prop('disabled', true);
		$('#connecting_btn_'+uniqueId).prop('disabled', true);
		$('#connecting_btn_'+uniqueId).html('<img src="assets/img/loader.gif" alt="Loader">');

		var progressBarHtml = '';
		progressBarHtml += '<thead class="progress-connection-thead heading-table-bg">';
		progressBarHtml += '	<tr>';
		progressBarHtml += ' 		<th colspan="5">';
		progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;">Updating the content please wait.. <img src="assets/img/loader.gif" alt="Loader"></div>';
		progressBarHtml += ' 			</div>';
		progressBarHtml += ' 		</th>';
		progressBarHtml += '	</tr>';
		progressBarHtml += '</thead>';
		$('#resultTable thead').after(progressBarHtml);

		$('.batch-result-form').addClass('unclickable');

		if(uniqueId == ""){
			dataArray['error_count']++;
		}
		if(dataArray['initial_source_symptom_id'] == ""){
			dataArray['error_count']++;
		}
		if(dataArray['comparing_source_symptom_id'] == ""){
			dataArray['error_count']++;
		}

		if(dataArray['error_count'] == 0){

			symptomConnecting(dataArray, sub_connetions_array, updateable_symptom_ids, removable_sets);

		}else{
			$("#global_msg_container").html('<p class="text-center">Operation failed. Required data not found, Please reload and try!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');

			$('#connecting_btn_'+uniqueId).html('<i class="fas fa-link"></i>');
			$('#connecting_btn_'+uniqueId).prop('disabled', false);
			$('#paste_btn_'+uniqueId).prop('disabled', false);
		}
	} else {
		$("#global_msg_container").html('<p class="text-center">Something went wrong, Please reload and try!</p>');
		$("#globalMsgModal").modal('show');
	}
});

$(document).on('click', '.nsc', function(){
	var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

	var uniqueId = $(this).attr("data-unique-id");
    var initial_source_symptom_id = $("#initial_source_symptom_id_"+uniqueId).val();
	var comparing_source_symptom_id = $("#comparing_source_symptom_id_"+uniqueId).val();
	var error_count = 0;

	$('#connecting_btn_'+uniqueId).prop('disabled', true);
	$('#nsc_btn_'+uniqueId).prop('disabled', true);
	$('#nsc_btn_'+uniqueId).html('<img src="assets/img/loader.gif" alt="Loader">');

	if(uniqueId == ""){
		error_count++;
	}
	if(initial_source_symptom_id == ""){
		error_count++;
	}
	if(comparing_source_symptom_id == ""){
		error_count++;
	}

	if(error_count == 0){
		$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
		$("#nsc_note_modal_loader .error-msg").html('');
		if($("#nsc_note_modal_loader").hasClass('hidden'))
			$("#nsc_note_modal_loader").removeClass('hidden');

		$("#populated_nsc_note_data").remove();
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: uniqueId,
				initial_source_symptom_id: initial_source_symptom_id,
				comparing_source_symptom_id: comparing_source_symptom_id,
				saved_comparison_quelle_id: saved_comparison_quelle_id,
				action: 'get_nsc_note'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} else if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					var ns_connect_note = (resultData.ns_connect_note != "" && resultData.ns_connect_note != null) ? resultData.ns_connect_note : "";
					var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
					var html = '';
					html += '<div id="populated_nsc_note_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<textarea name="nsc_note" id="nsc_note" class="form-control" rows="5" cols="50">'+ns_connect_note+'</textarea>';
					html += '			<span class="error-text"></span>';
					html += '			<input type="hidden" name="unique_id_nsc_note_modal" id="unique_id_nsc_note_modal" value="'+uniqueId+'">';
					html += '			<input type="hidden" name="connection_row_id_nsc_note_modal" id="connection_row_id_nsc_note_modal" value="'+id+'">';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';

					if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader").addClass('hidden');
					$("#nsc_note_container").append(html);
					$("#nscNoteModal").modal('show');
				}else{
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
					$("#globalMsgModal").modal('show');

					$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
					$('#nsc_btn_'+uniqueId).prop('disabled', false);
					$('#connecting_btn_'+uniqueId).prop('disabled', false);
				}
			}
		}).fail(function (response) {
			console.log(response);
			$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
			$("#globalMsgModal").modal('show');

			$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
			$('#nsc_btn_'+uniqueId).prop('disabled', false);
			$('#connecting_btn_'+uniqueId).prop('disabled', false);

		});
	}
	else
	{
		$("#global_msg_container").html('<p class="text-center">Operation failed. Required data not found, Please retry!</p>');
		$("#globalMsgModal").modal('show');

		$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
		$('#nsc_btn_'+uniqueId).prop('disabled', false);
		$('#connecting_btn_'+uniqueId).prop('disabled', false);
	}
});

$('#nscNoteModal').on('hidden.bs.modal', function () {
  	var uniqueId = $("#unique_id_nsc_note_modal").val();
  	$('#nsc_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
	$('#nsc_btn_'+uniqueId).prop('disabled', false);
	$('#connecting_btn_'+uniqueId).prop('disabled', false);
})

function addnscNote(){
	var nsc_note = $("#nsc_note").val();
	var unique_id_nsc_note_modal = $("#unique_id_nsc_note_modal").val();
	var connection_row_id_nsc_note_modal = $("#connection_row_id_nsc_note_modal").val();
	var error_count = 0;

	if(unique_id_nsc_note_modal == ""){
		error_count++;
	}
	if(connection_row_id_nsc_note_modal == ""){
		error_count++;
	}

	if(error_count == 0){
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: unique_id_nsc_note_modal,
				row_id: connection_row_id_nsc_note_modal,
				nsc_note: nsc_note,
				action: 'save_nsc_note'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					var is_ns_connect = (resultData.is_ns_connect != "" && resultData.is_ns_connect != null) ? resultData.is_ns_connect : "";
					var ns_connect_note = (resultData.ns_connect_note != "" && resultData.ns_connect_note != null) ? resultData.ns_connect_note : "";
					var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
					if(ns_connect_note != "" && is_ns_connect == 1){
						$("#nsc_btn_"+unique_id_nsc_note_modal).addClass("active");
						// $("#nsc_btn_"+unique_id_nsc_note_modal).attr('data-nsc-note', nsc_note);
						// $("#nscNoteModal").modal('hide');
						$("#ns_connect_note_"+unique_id_nsc_note_modal).val(ns_connect_note);
						$("#is_ns_connect_"+unique_id_nsc_note_modal).val(1);

						if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
						if($("#nsc_note_modal_loader").hasClass('hidden'))
							$("#nsc_note_modal_loader").removeClass('hidden');
						$("#nsc_note_modal_loader .error-msg").html('NSC note has been set');
						setTimeout(function() { 
							if($("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
							$("#nsc_note_modal_loader .error-msg").html('');
							$("#nsc_note_modal_loader").addClass('hidden');
							$("#nscNoteModal").modal('hide');
						}, 2000);
					}
					else
					{
						$("#nsc_btn_"+unique_id_nsc_note_modal).removeClass("active");
						// $("#nsc_btn_"+unique_id_nsc_note_modal).attr('data-nsc-note', '');
						$("#ns_connect_note_"+unique_id_nsc_note_modal).val('');
						$("#is_ns_connect_"+unique_id_nsc_note_modal).val(0);

						if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
						if($("#nsc_note_modal_loader").hasClass('hidden'))
							$("#nsc_note_modal_loader").removeClass('hidden');
						$("#nsc_note_modal_loader .error-msg").html('You have set NSC note blank');
						setTimeout(function() { 
							if($("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
							$("#nsc_note_modal_loader .error-msg").html('');
							$("#nsc_note_modal_loader").addClass('hidden');
							$("#nscNoteModal").modal('hide');
						}, 2000);
					}

				}else{
					if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
					if($("#nsc_note_modal_loader").hasClass('hidden'))
						$("#nsc_note_modal_loader").removeClass('hidden');
					$("#nsc_note_modal_loader .error-msg").html('Operation failed. Please retry!');
					setTimeout(function() { 
						if($("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
						$("#nsc_note_modal_loader .error-msg").html('');
						$("#nsc_note_modal_loader").addClass('hidden');
					}, 2000);
				}
			}
		}).fail(function (response) {
			console.log(response);
			if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
				$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
			if($("#nsc_note_modal_loader").hasClass('hidden'))
				$("#nsc_note_modal_loader").removeClass('hidden');
			$("#nsc_note_modal_loader .error-msg").html('Operation failed. Please retry!');
			setTimeout(function() { 
				if($("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
					$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
				$("#nsc_note_modal_loader .error-msg").html('');
				$("#nsc_note_modal_loader").addClass('hidden');
			}, 2000);
		});
	}
	else
	{
		if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
			$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
		if($("#nsc_note_modal_loader").hasClass('hidden'))
			$("#nsc_note_modal_loader").removeClass('hidden');
		$("#nsc_note_modal_loader .error-msg").html('Operation failed. Required data not found!');
		setTimeout(function() { 
			if($("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
				$("#nsc_note_modal_loader .loading-msg").removeClass('hidden');
			$("#nsc_note_modal_loader .error-msg").html('');
			$("#nsc_note_modal_loader").addClass('hidden');
			$("#nscNoteModal").modal('hide');
		}, 2000);
	}
}

function symptomPaste(dataArray, sub_connetions_array, updateable_symptom_ids, removable_sets){
	if (typeof dataArray !== 'undefined' && dataArray !== null){
		console.log("task 1");
		var uniqueId = (typeof dataArray['uniqueId'] !== 'undefined' && dataArray['uniqueId'] !== null && dataArray['uniqueId'] != "") ? dataArray['uniqueId'] : "";
	    var source_arznei_id = (typeof dataArray['source_arznei_id'] !== 'undefined' && dataArray['source_arznei_id'] !== null && dataArray['source_arznei_id'] != "") ? dataArray['source_arznei_id'] : "";
	    var initial_source_id = (typeof dataArray['initial_source_id'] !== 'undefined' && dataArray['initial_source_id'] !== null && dataArray['initial_source_id'] != "") ? dataArray['initial_source_id'] : "";
	    var initial_original_source_id = (typeof dataArray['initial_original_source_id'] !== 'undefined' && dataArray['initial_original_source_id'] !== null && dataArray['initial_original_source_id'] != "") ? dataArray['initial_original_source_id'] : "";
	    var initial_source_code = (typeof dataArray['initial_source_code'] !== 'undefined' && dataArray['initial_source_code'] !== null && dataArray['initial_source_code'] != "") ? dataArray['initial_source_code'] : "";
	    var comparing_source_id = (typeof dataArray['comparing_source_id'] !== 'undefined' && dataArray['comparing_source_id'] !== null && dataArray['comparing_source_id'] != "") ? dataArray['comparing_source_id'] : "";
	    var comparing_original_source_id = (typeof dataArray['comparing_original_source_id'] !== 'undefined' && dataArray['comparing_original_source_id'] !== null && dataArray['comparing_original_source_id'] != "") ? dataArray['comparing_original_source_id'] : "";
	    var comparing_source_code = (typeof dataArray['comparing_source_code'] !== 'undefined' && dataArray['comparing_source_code'] !== null && dataArray['comparing_source_code'] != "") ? dataArray['comparing_source_code'] : "";
	    var initial_source_symptom_id = (typeof dataArray['initial_source_symptom_id'] !== 'undefined' && dataArray['initial_source_symptom_id'] !== null && dataArray['initial_source_symptom_id'] != "") ? dataArray['initial_source_symptom_id'] : "";
	    var initial_source_symptom_de = (typeof dataArray['initial_source_symptom_de'] !== 'undefined' && dataArray['initial_source_symptom_de'] !== null && dataArray['initial_source_symptom_de'] != "") ? dataArray['initial_source_symptom_de'] : "";
	    var initial_source_symptom_en = (typeof dataArray['initial_source_symptom_en'] !== 'undefined' && dataArray['initial_source_symptom_en'] !== null && dataArray['initial_source_symptom_en'] != "") ? dataArray['initial_source_symptom_en'] : "";

	    var comparing_source_symptom_de = (typeof dataArray['comparing_source_symptom_de'] !== 'undefined' && dataArray['comparing_source_symptom_de'] !== null && dataArray['comparing_source_symptom_de'] != "") ? dataArray['comparing_source_symptom_de'] : "";
	    var comparing_source_symptom_en = (typeof dataArray['comparing_source_symptom_en'] !== 'undefined' && dataArray['comparing_source_symptom_en'] !== null && dataArray['comparing_source_symptom_en'] != "") ? dataArray['comparing_source_symptom_en'] : "";

	    var initial_source_symptom_highlighted_de = (typeof dataArray['initial_source_symptom_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? dataArray['initial_source_symptom_highlighted_de'] : "";
	    var initial_source_symptom_highlighted_en = (typeof dataArray['initial_source_symptom_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_highlighted_en'] !== null && dataArray['initial_source_symptom_highlighted_en'] != "") ? dataArray['initial_source_symptom_highlighted_en'] : "";

	    var comparing_source_symptom_highlighted_de = (typeof dataArray['comparing_source_symptom_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? dataArray['comparing_source_symptom_highlighted_de'] : "";
	    var comparing_source_symptom_highlighted_en = (typeof dataArray['comparing_source_symptom_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_highlighted_en'] !== null && dataArray['comparing_source_symptom_highlighted_en'] != "") ? dataArray['comparing_source_symptom_highlighted_en'] : "";

	    var initial_source_symptom_before_conversion_de = (typeof dataArray['initial_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_de'] !== null && dataArray['initial_source_symptom_before_conversion_de'] != "") ? dataArray['initial_source_symptom_before_conversion_de'] : "";
	    var initial_source_symptom_before_conversion_en = (typeof dataArray['initial_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_en'] !== null && dataArray['initial_source_symptom_before_conversion_en'] != "") ? dataArray['initial_source_symptom_before_conversion_en'] : "";

		var comparing_source_symptom_before_conversion_de = (typeof dataArray['comparing_source_symptom_before_conversion_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_de'] !== null && dataArray['comparing_source_symptom_before_conversion_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_de'] : "";
		var comparing_source_symptom_before_conversion_en = (typeof dataArray['comparing_source_symptom_before_conversion_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_en'] !== null && dataArray['comparing_source_symptom_before_conversion_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_en'] : "";

		var initial_source_symptom_before_conversion_highlighted_de = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_de'] : "";
		var initial_source_symptom_before_conversion_highlighted_en = (typeof dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['initial_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['initial_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['initial_source_symptom_before_conversion_highlighted_en'] : "";			    
		var comparing_source_symptom_before_conversion_highlighted_de = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_de'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_de'] : "";
		var comparing_source_symptom_before_conversion_highlighted_en = (typeof dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== 'undefined' && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] !== null && dataArray['comparing_source_symptom_before_conversion_highlighted_en'] != "") ? dataArray['comparing_source_symptom_before_conversion_highlighted_en'] : "";

		var individual_comparison_language = (typeof dataArray['individual_comparison_language'] !== 'undefined' && dataArray['individual_comparison_language'] !== null && dataArray['individual_comparison_language'] != "") ? dataArray['individual_comparison_language'] : "";

		var comparing_source_symptom_id = (typeof dataArray['comparing_source_symptom_id'] !== 'undefined' && dataArray['comparing_source_symptom_id'] !== null && dataArray['comparing_source_symptom_id'] != "") ? dataArray['comparing_source_symptom_id'] : "";
		var matching_percentage = (typeof dataArray['matching_percentage'] !== 'undefined' && dataArray['matching_percentage'] !== null && dataArray['matching_percentage'] != "") ? dataArray['matching_percentage'] : "";
		var is_connected = (typeof dataArray['is_connected'] !== 'undefined' && dataArray['is_connected'] !== null && dataArray['is_connected'] != "") ? dataArray['is_connected'] : "";
		var is_ns_connect = (typeof dataArray['is_ns_connect'] !== 'undefined' && dataArray['is_ns_connect'] !== null && dataArray['is_ns_connect'] != "") ? dataArray['is_ns_connect'] : "";
		var ns_connect_note = (typeof dataArray['ns_connect_note'] !== 'undefined' && dataArray['ns_connect_note'] !== null && dataArray['ns_connect_note'] != "") ? dataArray['ns_connect_note'] : "";
		var is_pasted = (typeof dataArray['is_pasted'] !== 'undefined' && dataArray['is_pasted'] !== null && dataArray['is_pasted'] != "") ? dataArray['is_pasted'] : "";
		var is_ns_paste = (typeof dataArray['is_ns_paste'] !== 'undefined' && dataArray['is_ns_paste'] !== null && dataArray['is_ns_paste'] != "") ? dataArray['is_ns_paste'] : "";
		var ns_paste_note = (typeof dataArray['ns_paste_note'] !== 'undefined' && dataArray['ns_paste_note'] !== null && dataArray['ns_paste_note'] != "") ? dataArray['ns_paste_note'] : "";
		var is_initial_source = (typeof dataArray['is_initial_source'] !== 'undefined' && dataArray['is_initial_source'] !== null && dataArray['is_initial_source'] != "") ? dataArray['is_initial_source'] : "";
		var similarity_rate = (typeof dataArray['similarity_rate'] !== 'undefined' && dataArray['similarity_rate'] !== null && dataArray['similarity_rate'] != "") ? dataArray['similarity_rate'] : "";
		var active_symptom_type = (typeof dataArray['active_symptom_type'] !== 'undefined' && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
		var comparing_source_ids = (typeof dataArray['comparing_source_ids'] !== 'undefined' && dataArray['comparing_source_ids'] !== null && dataArray['comparing_source_ids'] != "") ? dataArray['comparing_source_ids'] : "";
		var matched_symptom_ids = (typeof dataArray['matched_symptom_ids'] !== 'undefined' && dataArray['matched_symptom_ids'] !== null && dataArray['matched_symptom_ids'] != "") ? dataArray['matched_symptom_ids'] : "";
		var comparison_option = (typeof dataArray['comparison_option'] !== 'undefined' && dataArray['comparison_option'] !== null && dataArray['comparison_option'] != "") ? dataArray['comparison_option'] : "";
		var savedComparisonComparingSourceIds = (typeof dataArray['savedComparisonComparingSourceIds'] !== 'undefined' && dataArray['savedComparisonComparingSourceIds'] !== null && dataArray['savedComparisonComparingSourceIds'] != "") ? dataArray['savedComparisonComparingSourceIds'] : "";
		var is_unmatched_symptom = (typeof dataArray['is_unmatched_symptom'] !== 'undefined' && dataArray['is_unmatched_symptom'] !== null && dataArray['is_unmatched_symptom'] != "") ? dataArray['is_unmatched_symptom'] : "";
		var main_parent_initial_symptom_id = (typeof dataArray['main_parent_initial_symptom_id'] !== 'undefined' && dataArray['main_parent_initial_symptom_id'] !== null && dataArray['main_parent_initial_symptom_id'] != "") ? dataArray['main_parent_initial_symptom_id'] : "";
		var comparison_initial_source_id = (typeof dataArray['comparison_initial_source_id'] !== 'undefined' && dataArray['comparison_initial_source_id'] !== null && dataArray['comparison_initial_source_id'] != "") ? dataArray['comparison_initial_source_id'] : "";
		var connections_main_parent_symptom_id = (typeof dataArray['connections_main_parent_symptom_id'] !== 'undefined' && dataArray['connections_main_parent_symptom_id'] !== null && dataArray['connections_main_parent_symptom_id'] != "") ? dataArray['connections_main_parent_symptom_id'] : "";
		var error_count = (typeof dataArray['error_count'] !== 'undefined' && dataArray['error_count'] !== null && dataArray['error_count'] != "") ? dataArray['error_count'] : "";
		var mainParentInitialSymptomIdsArr = (typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'] != "") ? dataArray['mainParentInitialSymptomIdsArr'] : [];
		
		var saved_comparison_quelle_id = (typeof dataArray['saved_comparison_quelle_id'] !== 'undefined' && dataArray['saved_comparison_quelle_id'] !== null && dataArray['saved_comparison_quelle_id'] != "") ? dataArray['saved_comparison_quelle_id'] : "";
		// This field is not there in the hidden input fields of the table rows
		var connection_or_paste_type = (typeof dataArray['connection_or_paste_type'] !== 'undefined' && dataArray['connection_or_paste_type'] !== null && dataArray['connection_or_paste_type'] != "") ? dataArray['connection_or_paste_type'] : ""; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

		var sub_connetions_array = (typeof sub_connetions_array !== 'undefined' && sub_connetions_array !== null && sub_connetions_array != "") ? sub_connetions_array : [];
		var updateable_symptom_ids = (typeof updateable_symptom_ids !== 'undefined' && updateable_symptom_ids !== null && updateable_symptom_ids != "") ? updateable_symptom_ids : [];
		var removable_sets = (typeof removable_sets !== 'undefined' && removable_sets !== null && removable_sets != "") ? removable_sets : [];

		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: uniqueId,
				source_arznei_id: source_arznei_id,
				initial_source_id: initial_source_id,
				initial_original_source_id: initial_original_source_id,
				initial_source_code: initial_source_code,
				comparing_source_id: comparing_source_id,
				comparing_original_source_id: comparing_original_source_id,
				comparing_source_code: comparing_source_code,
				initial_source_symptom_id: initial_source_symptom_id,
				initial_source_symptom_de: initial_source_symptom_de,
				initial_source_symptom_en: initial_source_symptom_en,
				comparing_source_symptom_de: comparing_source_symptom_de,
				comparing_source_symptom_en: comparing_source_symptom_en,
				initial_source_symptom_highlighted_de: initial_source_symptom_highlighted_de,
				initial_source_symptom_highlighted_en: initial_source_symptom_highlighted_en,
				comparing_source_symptom_highlighted_de: comparing_source_symptom_highlighted_de,
				comparing_source_symptom_highlighted_en: comparing_source_symptom_highlighted_en,
				initial_source_symptom_before_conversion_de: initial_source_symptom_before_conversion_de,
				initial_source_symptom_before_conversion_en: initial_source_symptom_before_conversion_en,
				comparing_source_symptom_before_conversion_de: comparing_source_symptom_before_conversion_de,
				comparing_source_symptom_before_conversion_en: comparing_source_symptom_before_conversion_en,
				initial_source_symptom_before_conversion_highlighted_de: initial_source_symptom_before_conversion_highlighted_de,
				initial_source_symptom_before_conversion_highlighted_en: initial_source_symptom_before_conversion_highlighted_en,
				comparing_source_symptom_before_conversion_highlighted_de: comparing_source_symptom_before_conversion_highlighted_de,
				comparing_source_symptom_before_conversion_highlighted_en: comparing_source_symptom_before_conversion_highlighted_en,
				individual_comparison_language: individual_comparison_language,
				comparing_source_symptom_id: comparing_source_symptom_id,
				matching_percentage: matching_percentage,
				is_connected: is_connected,
				is_ns_connect: is_ns_connect,
				ns_connect_note: ns_connect_note,
				is_pasted: is_pasted,
				is_ns_paste: is_ns_paste,
				ns_paste_note: ns_paste_note,
				is_initial_source: is_initial_source,
				comparing_source_ids: comparing_source_ids,
				active_symptom_type: active_symptom_type,
				main_parent_initial_symptom_id: main_parent_initial_symptom_id,
				comparison_option: comparison_option,
				sub_connetions_array: sub_connetions_array,
				updateable_symptom_ids: updateable_symptom_ids,
				removable_sets: removable_sets,
				saved_comparison_quelle_id: saved_comparison_quelle_id,
				connection_or_paste_type: connection_or_paste_type,
				action: 'paste'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} else if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}

					if(typeof resultData.sub_connetions_array !== 'undefined' && resultData.sub_connetions_array !== null && resultData.sub_connetions_array != "")
					{
						symptomPaste(dataArray, resultData.sub_connetions_array, resultData.updateable_symptom_ids, resultData.removable_sets);	
					}
					else
					{
						if(resultData.does_it_requires_full_reload == 1) {
							// Re-Calling the main comprasion function to get the updated data.
							var initial_source = $('#batch_result_form_1').find("#initial_source_save").val();
							var comparing_sources = $('#batch_result_form_1').find("#comparing_sources_save").val();
							var arznei_id = $('#batch_result_form_1').find("#arznei_id_save").val();
							var similarity_rate = $('#batch_result_form_1').find("#similarity_rate_save").val();
							var comparison_option = $('#batch_result_form_1').find("#comparison_option_save").val();
							var comparison_language = $('#batch_result_form_1').find("#comparison_language_save").val();
							var error_count = 0;

							if(initial_source == ""){
								error_count++;
							}
							if(comparing_sources == ""){
								error_count++;
							}
							if(arznei_id == ""){
								error_count++;
							}
							if(similarity_rate == ""){
								error_count++;
							}
							if(comparison_option == ""){
								error_count++;
							}
							if(comparison_language == ""){
								error_count++;
							}
							
							if(error_count == 0){
								$(".progress-connection-thead").remove();
								$('.batch-search-result-form').remove();
								$('.batch-result-form').remove();
								$('#symptom_comparison_form').addClass('unclickable');
								$('#compare_submit_btn').prop('disabled', true);
								$('#search_submit_btn').prop('disabled', true);
								$("#comparison_name").val('');
								
								if(!$(".result-sub-btn").hasClass('hidden'))
									$(".result-sub-btn").addClass('hidden');

								if(!$(".head-panel-sub-ul").hasClass('hidden'))
									$(".head-panel-sub-ul").addClass('hidden');

								if($('.comparison-only-column').hasClass('hidden'))
									$('.comparison-only-column').removeClass('hidden');
								$("#numberOfRecord").html(0);

								$("#column_heading_symptom").html('Symptom');
								var loadingHtml = '';
								loadingHtml += '<tr id="loadingTr">';
								loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
								loadingHtml += '</tr>';

								$('#resultTable tbody').html(loadingHtml);

								var data = 'initial_source='+initial_source+'&comparing_sources='+comparing_sources+'&arznei_id='+arznei_id+'&similarity_rate='+similarity_rate+'&comparison_option='+comparison_option+'&comparison_language='+comparison_language;

								$(".progress-thead").remove();
								var progressBarHtml = '';
								progressBarHtml += '<thead class="progress-thead heading-table-bg">';
								progressBarHtml += '	<tr>';
								progressBarHtml += ' 		<th colspan="5">';
								progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
								progressBarHtml += ' 			<div class="progress comparison-progress">';
								progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
								progressBarHtml += ' 			</div>';
								progressBarHtml += ' 		</th>';
								progressBarHtml += '	</tr>';
								progressBarHtml += '</thead>';
								$('#resultTable thead').after(progressBarHtml);

								// start the process
								var matchedSymptomIds = [];
								process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds );

							} else {
								$("#global_msg_container").html('<p class="text-center">Something went wrong, Could not update the contect. Please reload the comparison and try again!</p>');
								$("#globalMsgModal").modal('show');
								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');
							}
						} else {
							if(typeof resultData.removable_sets !== 'undefined' && resultData.removable_sets !== null && resultData.removable_sets != "")
							{
								$.each(resultData.removable_sets, function( key, value ) {
									dataArray['removable_sets'].push(value);
								});
							}

							if(typeof resultData.updateable_symptom_ids !== 'undefined' && resultData.updateable_symptom_ids !== null && resultData.updateable_symptom_ids != "")
							{
								$.each(resultData.updateable_symptom_ids, function( key, value ) {
									$( ".instant-reflection-row-"+value ).each(function() {
									  	var rowUniqueId = $(this).attr("id");
									  	rowUniqueId = rowUniqueId.split( "row_" ).pop();	
									  	var mainParentInitialSymptomId = $('#main_parent_initial_symptom_id_'+rowUniqueId).val();
									  	if(typeof(mainParentInitialSymptomId) != "undefined" && mainParentInitialSymptomId !== null && mainParentInitialSymptomId != ""){
									  		if ($.inArray(mainParentInitialSymptomId, dataArray['mainParentInitialSymptomIdsArr']) == -1)
												dataArray['mainParentInitialSymptomIdsArr'].push(mainParentInitialSymptomId);
									  	}
									});
								});

								// Making updateable data unclickable
								if(typeof dataArray['mainParentInitialSymptomIdsArr'] !== 'undefined' && dataArray['mainParentInitialSymptomIdsArr'] !== null && dataArray['mainParentInitialSymptomIdsArr'].length > 0) {
									$.each(dataArray['mainParentInitialSymptomIdsArr'], function( key, value ) {
										var eachMainParentId = value;
									  	var loadingHtml = '';
								    	loadingHtml += '<tr id="instant_reflection_loder_'+eachMainParentId+'" style="display:none;">';
										loadingHtml += ' 	<td colspan="5" class="text-center">';
										loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
										loadingHtml += ' 	</td>';
										loadingHtml += '</tr>';
										$('.instant-reflection-set-'+eachMainParentId).first().before(loadingHtml);
										// $('.instant-reflection-set-'+eachMainParentId).addClass('unclickable');
									});
								}

								// Making unmatched symptoms unclickable if they needs to update
								// if(dataArray['is_connected'] == 1 && parseInt(dataArray['matching_percentage']) < parseInt(dataArray['similarity_rate']) && dataArray['matched_symptom_ids'] != ""){
									var loadingHtml = '';
							    	loadingHtml += '<tr id="instant_reflection_unmatched_rows_loder" style="display:none;">';
									loadingHtml += ' 	<td colspan="5" class="text-center">';
									loadingHtml += ' 		Loading... <img src="assets/img/loader.gif" alt="Loader">';
									loadingHtml += ' 	</td>';
									loadingHtml += '</tr>';
									$('.instant-reflection-unmatched-row').first().before(loadingHtml);
									// $('.instant-reflection-unmatched-row').addClass('unclickable');
								// }

								if(resultData.is_pasted == 1){
									$('#nsc_btn_'+uniqueId).prop('disabled', true);
									$('#connecting_btn_'+uniqueId).prop('disabled', true);
									$('#nsp_btn_'+uniqueId).prop('disabled', false);
									$("#is_pasted_"+uniqueId).val(1);
									$('#paste_btn_'+uniqueId).html('P');
									$('#paste_btn_'+uniqueId).addClass("btn-success");
									$('#paste_btn_'+uniqueId).prop('disabled', false);
								}else{
									$('#connecting_btn_'+uniqueId).prop('disabled', false);
									$('#nsp_btn_'+uniqueId).prop('disabled', true);
									$("#is_pasted_"+uniqueId).val(0);
									$("#ns_paste_note_"+uniqueId).val('');
									// $("#nsp_btn_"+uniqueId).attr('data-nsp-note', '');
									$("#is_ns_paste_"+uniqueId).val(0);
									$('#nsp_btn_'+uniqueId).removeClass("btn-success");
									$('#paste_btn_'+uniqueId).html('P');
									$('#paste_btn_'+uniqueId).removeClass("btn-success");
									$('#paste_btn_'+uniqueId).prop('disabled', false);
								}
								instantReflectionMatchedSections(dataArray);
							}
							else
							{
								$("#global_msg_container").html('<p class="text-center">Something went wrong. Please reload and try!</p>');
								$("#globalMsgModal").modal('show');
								$(".progress-connection-thead").remove();
								$('.batch-result-form').removeClass('unclickable');

								$('#paste_btn_'+uniqueId).html('P');
								$('#paste_btn_'+uniqueId).prop('disabled', false);
								$('#connecting_btn_'+uniqueId).prop('disabled', false);
							}
						}
					}
				}else{
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try!</p>');
					$("#globalMsgModal").modal('show');
					$(".progress-connection-thead").remove();
					$('.batch-result-form').removeClass('unclickable');

					$('#paste_btn_'+uniqueId).html('P');
					$('#paste_btn_'+uniqueId).prop('disabled', false);
					$('#connecting_btn_'+uniqueId).prop('disabled', false);
				}
			}
		}).fail(function (response) {
			console.log(response);
			$("#global_msg_container").html('<p class="text-center">Operation failed. Something went worng, please reload and try!</p>');
			$("#globalMsgModal").modal('show');
			$(".progress-connection-thead").remove();
			$('.batch-result-form').removeClass('unclickable');

			$('#paste_btn_'+uniqueId).html('P');
			$('#paste_btn_'+uniqueId).prop('disabled', false);
			$('#connecting_btn_'+uniqueId).prop('disabled', false);
		});
	}
}

$(document).on('click', '.paste-btn', function(){

	var uniqueId = $(this).attr("data-unique-id");
	var dataArray = [];

	dataArray['saved_comparison_quelle_id'] = $("#saved_comparison_quelle_id").val();
	dataArray['uniqueId'] = $(this).attr("data-unique-id");
    dataArray['source_arznei_id'] = $("#source_arznei_id_"+uniqueId).val();
    dataArray['initial_source_id'] = $("#initial_source_id_"+uniqueId).val();
    dataArray['initial_original_source_id'] = $("#initial_original_source_id_"+uniqueId).val();
    dataArray['initial_source_code'] = $("#initial_source_code_"+uniqueId).val();
    dataArray['comparing_source_id'] = $("#comparing_source_id_"+uniqueId).val();
    dataArray['comparing_original_source_id'] = $("#comparing_original_source_id_"+uniqueId).val();
    dataArray['comparing_source_code'] = $("#comparing_source_code_"+uniqueId).val();
    dataArray['initial_source_symptom_id'] = $("#initial_source_symptom_id_"+uniqueId).val();
    dataArray['initial_source_symptom_de'] = $("#initial_source_symptom_de_"+uniqueId).val();
    dataArray['initial_source_symptom_en'] = $("#initial_source_symptom_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_de'] = $("#comparing_source_symptom_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_en'] = $("#comparing_source_symptom_en_"+uniqueId).val();
    dataArray['initial_source_symptom_highlighted_de'] = $("#initial_source_symptom_highlighted_de_"+uniqueId).val();
    dataArray['initial_source_symptom_highlighted_en'] = $("#initial_source_symptom_highlighted_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_highlighted_de'] = $("#comparing_source_symptom_highlighted_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_highlighted_en'] = $("#comparing_source_symptom_highlighted_en_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_de'] = $("#initial_source_symptom_before_conversion_de_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_en'] = $("#initial_source_symptom_before_conversion_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_de'] = $("#comparing_source_symptom_before_conversion_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_en'] = $("#comparing_source_symptom_before_conversion_en_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_highlighted_de'] = $("#initial_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
    dataArray['initial_source_symptom_before_conversion_highlighted_en'] = $("#initial_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_highlighted_de'] = $("#comparing_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
    dataArray['comparing_source_symptom_before_conversion_highlighted_en'] = $("#comparing_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
	dataArray['individual_comparison_language'] = $("#individual_comparison_language_"+uniqueId).val();
	dataArray['comparing_source_symptom_id'] = $("#comparing_source_symptom_id_"+uniqueId).val();
	dataArray['matching_percentage'] = $("#matching_percentage_"+uniqueId).val();
	dataArray['is_connected'] = $("#is_connected_"+uniqueId).val();
	dataArray['is_ns_connect'] = $("#is_ns_connect_"+uniqueId).val();
	dataArray['ns_connect_note'] = $("#ns_connect_note_"+uniqueId).val();
	dataArray['is_pasted'] = $("#is_pasted_"+uniqueId).val();
	dataArray['is_ns_paste'] = $("#is_ns_paste_"+uniqueId).val();
	dataArray['ns_paste_note'] = $("#ns_paste_note_"+uniqueId).val();
	dataArray['is_initial_source'] = $("#is_initial_source_"+uniqueId).val();
	dataArray['similarity_rate'] = $("#similarity_rate_"+uniqueId).val();
	dataArray['active_symptom_type'] = $("#active_symptom_type_"+uniqueId).val();
	dataArray['comparing_source_ids'] = $("#comparing_source_ids_"+uniqueId).val();
	dataArray['matched_symptom_ids'] = $("#matched_symptom_ids_"+uniqueId).val();
	dataArray['comparison_option'] = $("#comparison_option_"+uniqueId).val();
	dataArray['savedComparisonComparingSourceIds'] = $("#saved_comparison_comparing_source_ids_"+uniqueId).val();
	dataArray['is_unmatched_symptom'] = $("#is_unmatched_symptom_"+uniqueId).val();
	dataArray['main_parent_initial_symptom_id'] = $(this).attr("data-main-parent-initial-symptom-id");
	dataArray['comparison_initial_source_id'] = $(this).attr("data-comparison-initial-source-id");
	dataArray['connections_main_parent_symptom_id'] = $("#connections_main_parent_symptom_id_"+uniqueId).val();
	dataArray['error_count'] = 0;
	dataArray['mainParentInitialSymptomIdsArr'] = [];
	dataArray['removable_sets'] = [];
	dataArray['operation'] = 'paste';
	// This field is not there in the hidden input fields of the table rows
	dataArray['connection_or_paste_type'] = 1; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

	var sub_connetions_array = [];
	var updateable_symptom_ids = [];
	var removable_sets = [];

	if(dataArray['is_pasted'] == 1)
	{
		
	}
	else
	{
		var tempArr = [];
		if(dataArray['active_symptom_type'] == "comparing"){
			tempArr['active_symptom_type'] = "comparing";
			tempArr['initial_symptom_id'] = dataArray['initial_source_symptom_id'];
			tempArr['comparing_symptom_id'] = dataArray['comparing_source_symptom_id']; 
		}else if(dataArray['active_symptom_type'] == "initial"){
			tempArr['active_symptom_type'] = "initial";
			tempArr['initial_symptom_id'] = dataArray['initial_source_symptom_id'];
			tempArr['comparing_symptom_id'] = dataArray['comparing_source_symptom_id'];
		}

		var symptomText = (typeof(dataArray['comparing_source_symptom_highlighted_de']) != "undefined" && dataArray['comparing_source_symptom_highlighted_de'] !== null && dataArray['comparing_source_symptom_highlighted_de'] != "") ? b64DecodeUnicode(dataArray['comparing_source_symptom_highlighted_de']) : "";
		var iniSymptomText = (typeof(dataArray['initial_source_symptom_highlighted_de']) != "undefined" && dataArray['initial_source_symptom_highlighted_de'] !== null && dataArray['initial_source_symptom_highlighted_de'] != "") ? b64DecodeUnicode(dataArray['initial_source_symptom_highlighted_de']) : "";

		$("#v_btn_"+dataArray['initial_source_symptom_id']).removeClass('link-disabled');
		$("#v_btn_"+dataArray['initial_source_symptom_id']).addClass('vbtn-has-connection active link-active');
		// $("#v_btn_"+dataArray['initial_source_symptom_id']).click();

		$("#row_"+uniqueId).remove();
		var commentClasses = "";
		var footnoteClasses = "";
		var translation_toggle_btn_type = "";
		var vBtnClasses = "vbtn link-disabled";
		var nscClasses = "nsc";
		var nsc_btn_disabled = "link-disabled unclickable";
		var nspClasses = "nsp";
		var nsp_btn_disabled = "";
		var connect_btn_class = "connecting-btn";
		var connection_btn_disabled = "link-disabled unclickable";
		var paste_btn_class = "paste-btn active link-active";
		var paste_btn_disabled = "";
		var html = '';
		html += '<tr id="row_'+uniqueId+'" class="unsaved-connection-row connected-row-'+dataArray['initial_source_symptom_id']+'" style="display: none;">';
		html += '	<td style="width: 12%;" class="text-center">'+dataArray['comparing_source_code']+'</td>';
		html += '	<td>'+symptomText+'</td>';
		html += '	<td style="width: 5%;" class="text-center">'+dataArray['matching_percentage']+'%</td>';
		html += '	<th style="width: 17%;">';
		html += '		<ul class="info-linkage-group">';
  		html += '			<li>';
  		html += '				<a onclick="showInfo('+dataArray['comparing_source_symptom_id']+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+dataArray['comparing_source_symptom_id']+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+dataArray['comparing_source_symptom_id']+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'">T</a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+'" data-unique-id="'+uniqueId+'"><i class="fas fa-plus"></i></a>';
  		html += '			</li>';
  		html += '		</ul>';
		html += '	</th>';
		html += '	<th style="width: 19%;">';
		html += '		<ul class="command-group">';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'"><i class="fas fa-link"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
  		html += '			</li>';
  		html += '			<li>';
  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'">P</a>';
  		html += '			</li>';
  		html += '		</ul>';
		html += '	</th>';
		html += '</tr>';
		$('#row_'+dataArray['main_parent_initial_symptom_id']).after(html);


		$(".instant-reflection-row-"+dataArray['comparing_source_symptom_id']).each(function() {
			var trId =$(this).attr('id');
			var splitIds = trId.split("_");
			var seperatedUniqueId = (typeof(splitIds[1]) != "undefined" && splitIds[1] !== null && splitIds[1] != "") ? splitIds[1] : "";
			$("#v_btn_"+seperatedUniqueId).removeClass('link-disabled');
			$("#v_btn_"+seperatedUniqueId).addClass('vbtn-has-connection active link-active');
			var newUniqueId = dataArray['initial_source_symptom_id']+dataArray['comparing_source_symptom_id'];

			var commentClasses = "";
			var footnoteClasses = "";
			var translation_toggle_btn_type = "";
			var vBtnClasses = "vbtn link-disabled";
			var nscClasses = "nsc";
			var nsc_btn_disabled = "link-disabled unclickable";
			var nspClasses = "nsp";
			var nsp_btn_disabled = "";
			var connect_btn_class = "connecting-btn";
			var connection_btn_disabled = "link-disabled unclickable";
			var paste_btn_class = "paste-btn active link-active";
			var paste_btn_disabled = "";
			var iniHtml = "";
			iniHtml += '<tr id="row_'+newUniqueId+'" class="unsaved-connection-row connected-row-'+seperatedUniqueId+'" style="display: none;">';
			iniHtml += '	<td style="width: 12%;" class="text-center">'+dataArray['initial_source_code']+'</td>';
			iniHtml += '	<td>'+iniSymptomText+'</td>';
			iniHtml += '	<td style="width: 5%;" class="text-center">'+dataArray['matching_percentage']+'%</td>';
			iniHtml += '	<th style="width: 17%;">';
			iniHtml += '		<ul class="info-linkage-group">';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a onclick="showInfo('+dataArray['initial_source_symptom_id']+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+newUniqueId+'"><i class="fas fa-info-circle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="'+commentClasses+'" id="comment_icon_'+newUniqueId+'" onclick="showComment('+dataArray['initial_source_symptom_id']+', '+newUniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+newUniqueId+'"><i class="fas fa-comment-alt"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+newUniqueId+'" onclick="showFootnote('+dataArray['initial_source_symptom_id']+', '+newUniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+newUniqueId+'"><i class="fas fa-sticky-note"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+newUniqueId+'">T</a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="v_btn_'+newUniqueId+'" class="'+vBtnClasses+'" data-unique-id="'+newUniqueId+'"><i class="fas fa-plus"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '		</ul>';
	  		iniHtml += '	</th>';
			iniHtml += '	<th style="width: 19%;">';
			iniHtml += '		<ul class="command-group">';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="nsc_btn_'+newUniqueId+'" class="'+nscClasses+' '+nsc_btn_disabled+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+newUniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="connecting_btn_'+newUniqueId+'" class="'+connect_btn_class+' '+connection_btn_disabled+'" title="connect" data-item="connect" data-unique-id="'+newUniqueId+'"><i class="fas fa-link"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="nsp_btn_'+newUniqueId+'" class="'+nspClasses+' '+nsp_btn_disabled+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+newUniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '			<li>';
	  		iniHtml += '				<a href="javascript:void(0)" id="paste_btn_'+newUniqueId+'" class="'+paste_btn_class+' '+paste_btn_disabled+'" title="Paste" data-item="paste" data-unique-id="'+newUniqueId+'">P</a>';
	  		iniHtml += '			</li>';
	  		iniHtml += '		</ul>';
			iniHtml += '	</th>';
			iniHtml += '</tr>';
			$('#'+trId).after(iniHtml);
		});

		$('#v_btn_'+dataArray['initial_source_symptom_id']).click();
	}
});

$(document).on('click', '.nsp', function(){
	var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

	var uniqueId = $(this).attr("data-unique-id");
    var initial_source_symptom_id = $("#initial_source_symptom_id_"+uniqueId).val();
	var comparing_source_symptom_id = $("#comparing_source_symptom_id_"+uniqueId).val();
	var error_count = 0;

	$('#paste_btn_'+uniqueId).prop('disabled', true);
	$('#nsp_btn_'+uniqueId).prop('disabled', true);
	$('#nsp_btn_'+uniqueId).html('<img src="assets/img/loader.gif" alt="Loader">');

	if(uniqueId == ""){
		error_count++;
	}
	if(initial_source_symptom_id == ""){
		error_count++;
	}
	if(comparing_source_symptom_id == ""){
		error_count++;
	}

	if(error_count == 0){
		$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
		$("#nsp_note_modal_loader .error-msg").html('');
		if($("#nsp_note_modal_loader").hasClass('hidden'))
			$("#nsp_note_modal_loader").removeClass('hidden');

		$("#populated_nsp_note_data").remove();
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: uniqueId,
				initial_source_symptom_id: initial_source_symptom_id,
				comparing_source_symptom_id: comparing_source_symptom_id,
				saved_comparison_quelle_id: saved_comparison_quelle_id,
				action: 'get_nsp_note'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} else if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					var ns_paste_note = (resultData.ns_paste_note != "" && resultData.ns_paste_note != null) ? resultData.ns_paste_note : "";
					var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
					var html = '';
					html += '<div id="populated_nsp_note_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<textarea name="nsp_note" id="nsp_note" class="form-control" rows="5" cols="50">'+ns_paste_note+'</textarea>';
					html += '			<span class="error-text"></span>';
					html += '			<input type="hidden" name="unique_id_nsp_note_modal" id="unique_id_nsp_note_modal" value="'+uniqueId+'">';
					html += '			<input type="hidden" name="connection_row_id_nsp_note_modal" id="connection_row_id_nsp_note_modal" value="'+id+'">';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';

					if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
					$("#nsp_note_modal_loader").addClass('hidden');
					$("#nsp_note_container").append(html);
					$("#nspNoteModal").modal('show');
				}else{
					$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
					$("#globalMsgModal").modal('show');

					$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
					$('#nsp_btn_'+uniqueId).prop('disabled', false);
					$('#paste_btn_'+uniqueId).prop('disabled', false);
				}
			}
		}).fail(function (response) {
			console.log(response);
			$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!</p>');
			$("#globalMsgModal").modal('show');

			$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
			$('#nsp_btn_'+uniqueId).prop('disabled', false);
			$('#paste_btn_'+uniqueId).prop('disabled', false);

		});
	}
	else
	{
		$("#global_msg_container").html('<p class="text-center">Operation failed. Required data not found, Please retry!</p>');
		$("#globalMsgModal").modal('show');

		$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
		$('#nsp_btn_'+uniqueId).prop('disabled', false);
		$('#paste_btn_'+uniqueId).prop('disabled', false);
	}
});

$('#nspNoteModal').on('hidden.bs.modal', function () {
  	var uniqueId = $("#unique_id_nsp_note_modal").val();
  	$('#nsp_btn_'+uniqueId).html('<i class="fas fa-exclamation-triangle"></i>');
	$('#nsp_btn_'+uniqueId).prop('disabled', false);
	$('#paste_btn_'+uniqueId).prop('disabled', false);
})

function addnspNote(){
	var nsp_note = $("#nsp_note").val();
	var unique_id_nsp_note_modal = $("#unique_id_nsp_note_modal").val();
	var connection_row_id_nsp_note_modal = $("#connection_row_id_nsp_note_modal").val();
	var error_count = 0;

	if(unique_id_nsp_note_modal == ""){
		error_count++;
	}
	if(connection_row_id_nsp_note_modal == ""){
		error_count++;
	}

	if(error_count == 0){
		$.ajax({
			type: 'POST',
			url: 'symptom-connection-operations.php',
			data: {
				unique_id: unique_id_nsp_note_modal,
				row_id: connection_row_id_nsp_note_modal,
				nsp_note: nsp_note,
				action: 'save_nsp_note'
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					var is_ns_paste = (resultData.is_ns_paste != "" && resultData.is_ns_paste != null) ? resultData.is_ns_paste : "";
					var ns_paste_note = (resultData.ns_paste_note != "" && resultData.ns_paste_note != null) ? resultData.ns_paste_note : "";
					var id = (resultData.id != "" && resultData.id != null) ? resultData.id : "";
					if(ns_paste_note != "" && is_ns_paste == 1){
						$("#nsp_btn_"+unique_id_nsp_note_modal).addClass("active");
						// $("#nsp_btn_"+unique_id_nsp_note_modal).attr('data-nsp-note', nsp_note);
						$("#ns_paste_note_"+unique_id_nsp_note_modal).val(ns_paste_note);
						$("#is_ns_paste_"+unique_id_nsp_note_modal).val(1);

						if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
						if($("#nsp_note_modal_loader").hasClass('hidden'))
							$("#nsp_note_modal_loader").removeClass('hidden');
						$("#nsp_note_modal_loader .error-msg").html('NSP note has been set');
						setTimeout(function() { 
							if($("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
							$("#nsp_note_modal_loader .error-msg").html('');
							$("#nsp_note_modal_loader").addClass('hidden');
							$("#nspNoteModal").modal('hide');
						}, 2000);
					}
					else
					{
						$("#nsp_btn_"+unique_id_nsp_note_modal).removeClass("active");
						//$("#nsp_btn_"+unique_id_nsp_note_modal).attr('data-nsp-note', '');
						$("#ns_paste_note_"+unique_id_nsp_note_modal).val('');
						$("#is_ns_paste_"+unique_id_nsp_note_modal).val(0);

						if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
						if($("#nsp_note_modal_loader").hasClass('hidden'))
							$("#nsp_note_modal_loader").removeClass('hidden');
						$("#nsp_note_modal_loader .error-msg").html('You have set NSP note blank');
						setTimeout(function() { 
							if($("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
								$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
							$("#nsp_note_modal_loader .error-msg").html('');
							$("#nsp_note_modal_loader").addClass('hidden');
							$("#nspNoteModal").modal('hide');
						}, 2000);
					}

				}else{
					if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
					if($("#nsp_note_modal_loader").hasClass('hidden'))
						$("#nsp_note_modal_loader").removeClass('hidden');
					$("#nsp_note_modal_loader .error-msg").html('Operation failed. Please retry!');
					setTimeout(function() { 
						if($("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
						$("#nsp_note_modal_loader .error-msg").html('');
						$("#nsp_note_modal_loader").addClass('hidden');
					}, 2000);
				}
			}
		}).fail(function (response) {
			console.log(response);
			if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
				$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
			if($("#nsp_note_modal_loader").hasClass('hidden'))
				$("#nsp_note_modal_loader").removeClass('hidden');
			$("#nsp_note_modal_loader .error-msg").html('Operation failed. Please retry!');
			setTimeout(function() { 
				if($("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
					$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
				$("#nsp_note_modal_loader .error-msg").html('');
				$("#nsp_note_modal_loader").addClass('hidden');
			}, 2000);
		});
	}
	else
	{
		if(!$("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
			$("#nsp_note_modal_loader .loading-msg").addClass('hidden');
		if($("#nsp_note_modal_loader").hasClass('hidden'))
			$("#nsp_note_modal_loader").removeClass('hidden');
		$("#nsp_note_modal_loader .error-msg").html('Operation failed. Required data not found!');
		setTimeout(function() { 
			if($("#nsp_note_modal_loader .loading-msg").hasClass('hidden'))
				$("#nsp_note_modal_loader .loading-msg").removeClass('hidden');
			$("#nsp_note_modal_loader .error-msg").html('');
			$("#nsp_note_modal_loader").addClass('hidden');
			$("#nspNoteModal").modal('hide');
		}, 2000);
	}
}

$('body').on( 'submit', '#symptom_search_form', function(e) {
	e.preventDefault();
	var search_keyword = $("#search_keyword").val();
	var search_sources = $("#search_sources").val();
	var error_count = 0;

	if(search_keyword == ""){
		$("#search_keyword").next().html('Please search keyword');
		$("#search_keyword").next().addClass('text-danger');
		error_count++;
	}else{
		$("#search_keyword").next().html('');
		$("#search_keyword").next().removeClass('text-danger');
	}
	if(search_sources == ""){
		$("#search_sources").next().next().html('Please select sources');
		$("#search_sources").next().next().addClass('text-danger');
		error_count++;
	}else{
		$("#search_sources").next().next().html('');
		$("#search_sources").next().next().removeClass('text-danger');
	}
	// Removing hidden fields of saving comparison result
	$('.hidden-save-data').remove();

	if(error_count == 0){
		$('.batch-search-result-form').remove();
		$('.batch-result-form').remove();

		$('#search_submit_btn').prop('disabled', true);
		$('#compare_submit_btn').prop('disabled', true);

		if(!$(".result-sub-btn").hasClass('hidden'))
			$(".result-sub-btn").addClass('hidden');

		if(!$(".head-panel-sub-ul").hasClass('hidden'))
			$(".head-panel-sub-ul").addClass('hidden');

		if(!$('.comparison-only-column').hasClass('hidden'))
			$('.comparison-only-column').addClass('hidden');

		$("#numberOfRecord").html(0);

		$("#column_heading_symptom").html('Symptom | Searching: <span style="font-weight: 300;">'+search_keyword+'</span>');
		var loadingHtml = '';
		loadingHtml += '<tr id="loadingTr">';
		loadingHtml += '	<td colspan="3" class="text-center"><!-- Data loading.. --></td>';
		loadingHtml += '</tr>';

		$('#resultTable tbody').html(loadingHtml);
		var data = $(this).serialize();

		$(".progress-thead").remove();
		var progressBarHtml = '';
		progressBarHtml += '<thead class="progress-thead heading-table-bg">';
		progressBarHtml += '	<tr>';
		progressBarHtml += ' 		<th colspan="3">';
		progressBarHtml += ' 			<div class="progress comparison-progress">';
		progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
		progressBarHtml += ' 			</div>';
		progressBarHtml += ' 		</th>';
		progressBarHtml += '	</tr>';
		progressBarHtml += '</thead>';
		$('#resultTable thead').after(progressBarHtml);

		// start the process
		search_process_step( 1, 0, data );
	}else{
		// $("#form-msg").addClass("text-danger");
		// $("#form-msg").html("Please correct all errors");
		$('html, body').animate({
            scrollTop: $("#symptom_search_form").offset().top
        }, 1000);
		return false;
	}
});

function search_process_step( step, number_of_records, data ){
	var nummerOfRecordFetch = number_of_records;
	$.ajax({
		type: 'POST',
		url: 'get-search-result-batch.php',
		data: {
			form: data,
			step: step,
			total_data: ""
		},
		dataType: "json",
		success: function( response ) {
			console.log(response);
			if(typeof(response.result_data) != "undefined" && response.result_data !== null) {
				var resultData = null;
				try {
					resultData = JSON.parse(response.result_data); 
				} catch (e) {
					resultData = response.result_data;
				}
				//console.log(resultData);
				var html = "";
				$.each(resultData, function( key, value ) {

			  		// var symptomHighlightedEndcod = $('<div/>').html(value.symptom_highlighted).text();
			  		var symptomHighlightedEndcod_de = (typeof(value.symptom_highlighted_de) != "undefined" && value.symptom_highlighted_de !== null && value.symptom_highlighted_de != "") ? b64DecodeUnicode(value.symptom_highlighted_de) : "";
			  		var symptomHighlightedEndcod_en = (typeof(value.symptom_highlighted_en) != "undefined" && value.symptom_highlighted_de !== null && value.symptom_highlighted_en != "") ? b64DecodeUnicode(value.symptom_highlighted_en) : "";

			  		var displaySymptomString = "";
			  		if(value.source_original_language == "en"){
	  					var tmpString = "";
	  					tmpString += (symptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom table-original-symptom-bg">'+symptomHighlightedEndcod_en+'</div>' : "";
	  					tmpString += (symptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de">'+symptomHighlightedEndcod_de+'</div>' : "";
	  					
	  					displaySymptomString = tmpString;
	  				}
	  				else{
	  					var tmpString = "";
	  					tmpString += (symptomHighlightedEndcod_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom table-original-symptom-bg">'+symptomHighlightedEndcod_de+'</div>' : "";
	  					tmpString += (symptomHighlightedEndcod_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+symptomHighlightedEndcod_en+'</div>' : "";

	  					displaySymptomString = tmpString;
	  				}

			  		html += '<tr>';
			  		html += '	<td style="width:12%;" class="text-center">'+value.source_code+'</td>';
			  		// html += '	<td>'+symptomHighlightedEndcod+'</td>';
			  		html += '	<td>'+displaySymptomString+'</td>';
			  		html += '	<td style="width:5%;">'+value.percentage+'%</td>';
			  		html += '</tr>';

			  		nummerOfRecordFetch = nummerOfRecordFetch + 1;
				});
				if(html != ""){
			  		var batchTable ='<form class="batch-search-result-form append-recognizer">';
			  		batchTable +='	<table class="table table-bordered">';
			  		batchTable += html;
			  		batchTable +='	</table>';
			  		batchTable +='</form>';
			  		$(".no-records-found").remove();
			  		$('#loadingTr').remove();
			  		$('.append-recognizer').last().after(batchTable);	
			  	}
			}
			$(document).ready(function () {

				$('.progress-thead .progress-bar').attr('aria-valuenow', response.progress_percentage).css('width', response.progress_percentage+"%");
				$('.progress-thead .progress-bar').html(response.progress_percentage+"%");
			    console.log('I m loaded!');
			    $("#numberOfRecord").html(nummerOfRecordFetch);
			  	if( 'done' == response.step ) {
					setTimeout(function() {
					    $(".progress-thead").remove();
					    $('#compare_submit_btn').prop('disabled', false);
					    $('#search_submit_btn').prop('disabled', false);
					}, 3000);

				} else if( 'error' == response.step ) {
					if ( window.console && window.console.log ) {
						console.log( "Exception error" );
						console.log( response );
					}
				}else {
					search_process_step( parseInt( response.step ), nummerOfRecordFetch, data );
				}
			});
		}
	}).fail(function (response) {
		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

$('body').on( 'click', '.result-sub-btn', function() {
	var comparison_name = $("#comparison_name").val();
	if(comparison_name == "")
	{
		$("#comparison_name").next().html('Please give a comparison name');
		$("#comparison_name").next().addClass('text-danger');
		
		$('html, body').animate({
            scrollTop: $("#symptom_comparison_form").offset().top
        }, 1000);
	}else{
		$("#comparison_name").next().html('');
		$("#comparison_name").next().removeClass('text-danger');

		$( ".comparison-name" ).each(function() {
		  	$(this).val(comparison_name);
		});

		$('#compare_submit_btn').prop('disabled', true);
	    $('#search_submit_btn').prop('disabled', true);
	 		$('.result-sub-btn').prop('disabled', true);
	 		$('.show-all-translation').prop('disabled', true);
		$(".batch-result-form").addClass('unclickable');

		var totalBatches = $('.batch-result-form').length;
		var arzine = $('.batch-result-form').length;
		var step = 1;
		saveTheComparison(step, totalBatches, null);
	}
});

function saveTheComparison(step, totalBatches, masterId){
	var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();

	var master_id = (typeof(masterId) != "undefined" && masterId !== null) ? masterId : null;
  	var data = $("#batch_result_form_"+step).serialize();
		$.ajax({
		type: 'POST',
		url: 'save-comarison.php',
		data: {
			form: data,
			step: step,
			total_batches: totalBatches,
			master_id: master_id,
			is_save_on_existing: 0,
			saved_comparison_quelle_id: saved_comparison_quelle_id
		},
		dataType: "json",
		success: function( response ) {
			console.log(response);
			$(document).ready(function () {
				var resultData = null;
				try {
					resultData = JSON.parse(response.result_data); 
				} catch (e) {
					resultData = response.result_data;
				}
				
				if(response.status == "invalid"){
					$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
					$("#reloadPageModal").modal('show');
				} 
				else if(response.status == "used_further")
				{
					var saved_comparison_name_list_html = "";
					var saved_comparisons = "";
					$.each(resultData, function( key, value ) {
						saved_comparisons += '<li><a target="_blank" href="view-source-connections.php?mid='+value.saved_comparison_master_id+'" title="View connections">'+value.saved_comparison_name+'</a></li>';
					});
					if(saved_comparisons != "")
						saved_comparison_name_list_html += "<ul>"+saved_comparisons+"</ul>";


					var saved_comparison_name = (typeof resultData.saved_comparison_name !== 'undefined' && resultData.saved_comparison_name !== null && resultData.saved_comparison_name != "") ? resultData.saved_comparison_name : "";
					var saved_comparison_master_id = (typeof resultData.saved_comparison_master_id !== 'undefined' && resultData.saved_comparison_master_id !== null && resultData.saved_comparison_master_id != "") ? resultData.saved_comparison_master_id : "";
					$("#save_comparison_modal_loader .loading-msg").removeClass('hidden');
					$("#save_comparison_modal_loader .error-msg").html('');
					if($("#save_comparison_modal_loader").hasClass('hidden'))
						$("#save_comparison_modal_loader").removeClass('hidden');

					$("#populated_save_comparison_data").remove();

					var html = '';
					html += '<div id="populated_save_comparison_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<p>This comparison has further connections with other source(s) or it is uesd in other comparisons. If you save this comparison it\'s related connections and comparisons will be deleted.</p>';
					if(saved_comparison_name_list_html != ""){
						html += '			<p>Below is the list of it\'s related comparisons:<p/>';
						html += '			'+saved_comparison_name_list_html+'';
					}
					html += '		</div>';
					html += '	</div>';
					html += '</div>';

					if(!$("#save_comparison_modal_loader .loading-msg").hasClass('hidden'))
						$("#save_comparison_modal_loader .loading-msg").addClass('hidden');
					$("#save_comparison_modal_loader").addClass('hidden');
					$("#save_comparison_modal_container").append(html);
					$("#saveComparisonModal").modal('show');
				} 
				else if(response.status == "success")
				{
					var saved_comparison_id = (typeof resultData.saved_comparison_id !== 'undefined' && resultData.saved_comparison_id !== null && resultData.saved_comparison_id != "") ? resultData.saved_comparison_id : "";
					$('#compare_submit_btn').prop('disabled', false);
				    $('#search_submit_btn').prop('disabled', false);
		   	 		$('.result-sub-btn').prop('disabled', false);
		   	 		$('.show-all-translation').prop('disabled', false);
					$(".batch-result-form").removeClass('unclickable');
					$("#global_msg_container").html('<p class="text-center">'+response.message+'<input type="hidden" name="global_modal_saved_comparison_id" id="global_modal_saved_comparison_id" value="'+saved_comparison_id+'"></p>');
					$("#globalMsgModal").addClass('save-comparison-modal');
					$("#globalMsgModal").modal('show');
				}
				else
				{
					$('#compare_submit_btn').prop('disabled', false);
				    $('#search_submit_btn').prop('disabled', false);
		   	 		$('.result-sub-btn').prop('disabled', false);
		   	 		$('.show-all-translation').prop('disabled', false);
					$(".batch-result-form").removeClass('unclickable');
					$("#global_msg_container").html('<p class="text-center">'+response.message+'<input type="hidden" name="global_modal_saved_comparison_id" id="global_modal_saved_comparison_id" value=""></p>');
					$("#globalMsgModal").addClass('save-comparison-modal');
					$("#globalMsgModal").modal('show');
				}
			});
		}
	}).fail(function (response) {
		$('#compare_submit_btn').prop('disabled', false);
	    $('#search_submit_btn').prop('disabled', false);
	 		$('.result-sub-btn').prop('disabled', false);
	 		$('.show-all-translation').prop('disabled', false);
		$(".batch-result-form").removeClass('unclickable');
		$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!<input type="hidden" name="global_modal_saved_comparison_id" id="global_modal_saved_comparison_id" value=""></p>');
		$("#globalMsgModal").addClass('save-comparison-modal');
		$("#globalMsgModal").modal('show');


		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

function saveComparisonOnExisting(){
	var totalBatches = $('.batch-result-form').length;
	var step = 1;
	var master_id = null;
  	var data = $("#batch_result_form_"+step).serialize();

	$("#saveComparisonModal").modal('hide');
	$("#global_msg_container").html('<p class="text-center">Loading informations please wait <img src="assets/img/loader.gif" alt="Loading..."></p>');
	$("#globalMsgModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'save-comarison.php',
		data: {
			form: data,
			step: step,
			total_batches: totalBatches,
			master_id: master_id,
			is_save_on_existing: 1,
		},
		dataType: "json",
		success: function( response ) {
			var resultData = null;
			try {
				resultData = JSON.parse(response.result_data); 
			} catch (e) {
				resultData = response.result_data;
			}
			console.log(response);
			$(document).ready(function () {
				var saved_comparison_id = (typeof resultData.saved_comparison_id !== 'undefined' && resultData.saved_comparison_id !== null && resultData.saved_comparison_id != "") ? resultData.saved_comparison_id : "";
				$('#compare_submit_btn').prop('disabled', false);
			    $('#search_submit_btn').prop('disabled', false);
	   	 		$('.result-sub-btn').prop('disabled', false);
	   	 		$('.show-all-translation').prop('disabled', false);
				$(".batch-result-form").removeClass('unclickable');
				$("#global_msg_container").html('<p class="text-center">'+response.message+'<input type="hidden" name="global_modal_saved_comparison_id" id="global_modal_saved_comparison_id" value="'+saved_comparison_id+'"></p>');
				$("#globalMsgModal").addClass('save-comparison-modal');
				$("#globalMsgModal").modal('show');
			});
		}
	}).fail(function (response) {
		$('#compare_submit_btn').prop('disabled', false);
	    $('#search_submit_btn').prop('disabled', false);
	 		$('.result-sub-btn').prop('disabled', false);
	 		$('.show-all-translation').prop('disabled', false);
		$(".batch-result-form").removeClass('unclickable');
		$("#global_msg_container").html('<p class="text-center">Operation failed. Please retry!<input type="hidden" name="global_modal_saved_comparison_id" id="global_modal_saved_comparison_id" value=""></p>');
		$("#globalMsgModal").addClass('save-comparison-modal');
		$("#globalMsgModal").modal('show');

		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

function saveComparisonCancel(){
	$('#compare_submit_btn').prop('disabled', false);
    $('#search_submit_btn').prop('disabled', false);
 		$('.result-sub-btn').prop('disabled', false);
 		$('.show-all-translation').prop('disabled', false);
	$(".batch-result-form").removeClass('unclickable');
	$("#saveComparisonModal").modal('hide');
}

$(document).on('hidden.bs.modal', '.save-comparison-modal', function(){
  	var saved_comparison_id = $("#global_modal_saved_comparison_id").val();
  	if(typeof saved_comparison_id !== 'undefined' && saved_comparison_id !== null && saved_comparison_id != ""){
  		window.location.replace("materia-medica.php");
  	}else{
  		$("#global_msg_container").html('<p class="text-center">Something went wrong, Please reload the comparison.</p>');
		$("#globalMsgModal").modal('show');
  	}
});

$('body').on( 'submit', '#symptom_comparison_form', function(e) {
	e.preventDefault();
	var initial_source = $("#initial_source").val();
	var arznei_id = $("#arznei_id").val();
	var comparing_sources = $("#comparing_sources").val();
	var scId = $("#scid").val();
	var saved_comparison_comparing_source_ids_comma_separated = $("#saved_comparison_comparing_source_ids_comma_separated").val();
	var comparison_language = $("#comparison_language").val();
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

	if(error_count == 0){
		// Form data
		var data = $(this).serialize();
		// Checking if selected initial and comparing sources are available in selecetd comparison language.
		var initialSourceLanguage = $("#initial_source").find(':selected').attr("data-is-symptoms-available-in-"+comparison_language);
		var comparingSourceLanguage = $("#comparing_sources option:selected").map(function() {
		  	return $(this).attr("data-is-symptoms-available-in-"+comparison_language);
		}).get();
		comparingSourceLanguage.push(initialSourceLanguage);

		if($.inArray("0", comparingSourceLanguage) !== -1){
			$("#global_msg_container").html('<p class="text-center">There is/are source(s) in the Initial source or in the Comparing source(s) which are not available in the language that you have selecetd to compare, Please check and try again!</p>');
			$("#globalMsgModal").modal('show');
		}else{
			// Making the call to the main comparison function from here, as sources are available in selected language    
			$('.batch-search-result-form').remove();
			$('.batch-result-form').remove();
			$('#symptom_comparison_form').addClass('unclickable');
			$('#compare_submit_btn').prop('disabled', true);
			$('#search_submit_btn').prop('disabled', true);
			$("#comparison_name").val('');
			
			if(!$(".result-sub-btn").hasClass('hidden'))
				$(".result-sub-btn").addClass('hidden');

			if(!$(".head-panel-sub-ul").hasClass('hidden'))
				$(".head-panel-sub-ul").addClass('hidden');

			if($('.comparison-only-column').hasClass('hidden'))
				$('.comparison-only-column').removeClass('hidden');
			$("#numberOfRecord").html(0);

			$("#column_heading_symptom").html('Symptom');
			var loadingHtml = '';
			loadingHtml += '<tr id="loadingTr">';
			loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
			loadingHtml += '</tr>';

			$('#resultTable tbody').html(loadingHtml);
			// $("#loader").removeClass("hidden");
			// $("#comparison_container").addClass('unclickable');
			// $("#search_container").addClass('unclickable');
			// $("#comparison_result_cnr").addClass("less-visible");
			// $("#symptom_comparison_form").submit();
			
			$(".progress-thead").remove();
			var progressBarHtml = '';
			progressBarHtml += '<thead class="progress-thead heading-table-bg">';
			progressBarHtml += '	<tr>';
			progressBarHtml += ' 		<th colspan="5">';
			progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
			progressBarHtml += ' 			<div class="progress comparison-progress">';
			progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
			progressBarHtml += ' 			</div>';
			progressBarHtml += ' 		</th>';
			progressBarHtml += '	</tr>';
			progressBarHtml += '</thead>';
			// $('#resultTable thead').after(progressBarHtml);

			// start the process
			var matchedSymptomIds = [];
			process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds );
		}
	}else{
		$('html, body').animate({
            scrollTop: $("#symptom_comparison_form").offset().top
        }, 1000);
		return false;
	}
});

// Calling this function to get compare result batches When comparing
function process_step( step, number_of_records, total_batches_in_part1, total_batches_in_part2, is_stage2_checked, un_matched_symptoms_set_number, data, matched_symptom_ids ) {
	var saved_comparison_quelle_id = $("#saved_comparison_quelle_id").val();
	var nummerOfRecordFetch = number_of_records;
	var saved_comparison_comparing_source_ids_comma_separated = $("#saved_comparison_comparing_source_ids_comma_separated").val();
	$(".btn-load-more").remove();
	$.ajax({
		type: 'POST',
		url: 'get-compare-result-batch.php',
		data: {
			form: data,
			step: step,
			total_batches_in_part1: total_batches_in_part1,
			total_batches_in_part2: total_batches_in_part2,
			is_stage2_checked: is_stage2_checked,
			un_matched_symptoms_set_number: un_matched_symptoms_set_number,
			saved_comparison_comparing_source_ids_comma_separated: saved_comparison_comparing_source_ids_comma_separated,
			matched_symptom_ids: matched_symptom_ids,
			saved_comparison_quelle_id: saved_comparison_quelle_id
		},
		dataType: "json",
		success: function( response ) {
			console.log(response);
			if((typeof(response.is_invalid_quelle) != "undefined" && response.is_invalid_quelle !== null) && response.is_invalid_quelle == 1) {
				$("#reload_page_modal_container").html('<p class="text-center">Could not perform this action, System needs to reload the page. Closing this message box will automatically reload the page.</p>');
				$("#reloadPageModal").modal('show');
			}
			else
			{
				if(typeof(response.result_data) != "undefined" && response.result_data !== null) {
					var resultData = "";
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					//console.log(resultData);
					var html = "";
					$.each(resultData, function( key, value ) {
						
						var uniqueId = value.initial_source_symptom_id+value.comparing_source_symptom_id;
				  		var commentClasses = "";
				  		var footnoteClasses = "";
				  		var FVBtnClasses = "FV-btn";

				  		if(value.is_final_version_available != 0)
				  			FVBtnClasses += " active";

				  		if(value.has_connections == 1){
				  			if(value.is_further_connections_are_saved == 1){
				  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active-saved unclickable';
				  				FVBtnClasses += " link-active-saved";
				  			}
				  			else{
				  				var vBtnClasses = 'vbtn vbtn-has-connection active link-active unclickable';
				  				FVBtnClasses += " link-active";
				  			}
				  			var vBtnTitle = 'Earlier connections';
				  			var vBtnDisable = '';
				  		} else {
				  			var vBtnClasses = 'vbtn link-disabled unclickable';
				  			var vBtnTitle = 'Earlier connections';
				  			var vBtnDisable = 'disabled';
				  		}

				  		var nsc_btn_class = 'nsc link-disabled unclickable';
				  		var connection_btn_class = 'connecting-btn';
				  		// var connect_btn_class = 'connecting-btn btn btn-default';
				  		var paste_btn_class = 'paste-btn';
				  		// var nscClasses = 'nsc btn btn-default';
				  		var nspClasses = 'nsp link-disabled unclickable';
				  		var connection_edit_btn_class = "connecting-edit-btn";
				  		var paste_edit_btn_class = "paste-edit-btn";

				  		if(value.is_pasted == 1){
				  			connection_btn_class = 'connecting-btn link-disabled unclickable'; 
				  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
				  			nspClasses = 'nsp';
				  			paste_btn_class = 'paste-btn active link-active';
				  			
				  			if(value.is_ns_paste == 1){
				  				nspClasses = 'nsp active link-active';
				  			}
				  		}

				  		if(value.is_ns_connect_disabled == 0)
				  			nsc_btn_class = 'nsc';
				  		else
				  			nsc_btn_class = 'nsc link-disabled unclickable';
				  		if(value.is_connect_disabled == 1){
				  			connection_btn_class = 'connecting-btn link-disabled unclickable'; 
				  			connection_edit_btn_class = 'connecting-edit-btn link-disabled unclickable'; 
				  		}
				  		if(value.is_ns_paste_disabled == 1)
				  			nspClasses += ' link-disabled unclickable'; 
				  		if(value.is_paste_disabled == 1){
				  			paste_btn_class += ' link-disabled unclickable';
				  			paste_edit_btn_class += ' link-disabled unclickable';
				  		}

				  		var translation_toggle_btn_type = "";


				  		// var comparingSymptomHighlightedEndcod = $('<div/>').html(value.comparing_source_symptom_highlighted).text();
				  		
				  		var rowClass = "";
				  		var saved_version_source_code = "";
				  		var instantReflectionClass = 'instant-reflection-set-'+value.main_parent_initial_symptom_id;

				  		var initial_source_original_language = (typeof(value.initial_source_original_language) != "undefined" && value.initial_source_original_language !== null && value.initial_source_original_language != "") ? value.initial_source_original_language : "";
				  		var comparing_source_original_language = (typeof(value.comparing_source_original_language) != "undefined" && value.comparing_source_original_language !== null && value.comparing_source_original_language != "") ? value.comparing_source_original_language : "";

				  		var active_symptom_id = "";
				  		var active_original_source_id = "";
				  		if(value.active_symptom_type == "comparing"){

				  			active_symptom_id = value.comparing_source_symptom_id;
				  			active_original_source_id = value.comparing_original_source_id;
				  			translation_toggle_btn_type = "comparative";

				  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
				  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
				  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
				  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
				  			var activeSymptomId = value.comparing_source_symptom_id;
				  			var activeSymptomType = "comparing";
				  			var displaySourceCode = (typeof(value.comparing_source_code) != "undefined" && value.comparing_source_code !== null && value.comparing_source_code != "") ? value.comparing_source_code : "";

				  			var comparing_source_symptom_highlighted_de = (typeof(value.comparing_source_symptom_highlighted_de) != "undefined" && value.comparing_source_symptom_highlighted_de !== null && value.comparing_source_symptom_highlighted_de != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_de) : "";
				  			var comparing_source_symptom_highlighted_en = (typeof(value.comparing_source_symptom_highlighted_en) != "undefined" && value.comparing_source_symptom_highlighted_en !== null && value.comparing_source_symptom_highlighted_en != "") ? b64DecodeUnicode(value.comparing_source_symptom_highlighted_en) : "";
				  			var displaySymptomString = "";

				  			if(value.comparison_language == "en"){
				  				displaySymptomString = comparing_source_symptom_highlighted_en;
				  				
				  				if(comparing_source_original_language == "en"){
				  					var tmpString = "";
				  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+comparing_source_symptom_highlighted_en+'</div>' : "";
				  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  				else{
				  					var tmpString = "";
				  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+comparing_source_symptom_highlighted_de+'</div>' : "";
				  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+comparing_source_symptom_highlighted_en+'</div>' : "";

				  					displaySymptomString = tmpString;
				  				}
				  			}
				  			else{
				  				displaySymptomString = comparing_source_symptom_highlighted_de;

				  				if(comparing_source_original_language == "de"){
				  					var tmpString = "";
				  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+comparing_source_symptom_highlighted_de+'</div>' : "";
				  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  				else{
				  					var tmpString = "";
				  					tmpString += (comparing_source_symptom_highlighted_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+comparing_source_symptom_highlighted_en+'</div>' : "";
				  					tmpString += (comparing_source_symptom_highlighted_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+comparing_source_symptom_highlighted_de+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  			}
				  			var displayPercentage = value.percentage+"%";
				  			var rowInlineStyle = 'style="border-top: dotted; border-color: #ddd;"';
				  			// var symptomColumnInlineStyle = 'style="padding-left: 40px;"';
				  			var symptomColumnInlineStyle = '';
				  			var commandColumnClass = ' unclickable';

				  			if(typeof(value.comparing_source_symptom_comment) != "undefined" && value.comparing_source_symptom_comment !== null && value.comparing_source_symptom_comment != ""){
					  			commentClasses += ' active';
					  		}
					  		if(typeof(value.comparing_source_symptom_footnote) != "undefined" && value.comparing_source_symptom_footnote !== null && value.comparing_source_symptom_footnote != ""){
					  			footnoteClasses += ' active';
					  		}
					  		if(displaySourceCode != value.comparing_saved_version_source_code)
					  			saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.comparing_saved_version_source_code+'</span>';
				  		}else{

				  			active_symptom_id = value.initial_source_symptom_id;
				  			active_original_source_id = value.initial_original_source_id;
				  			translation_toggle_btn_type = "initial";

				  			if(typeof(value.initial_source_symptom_id) != "undefined" && value.initial_source_symptom_id !== null && value.initial_source_symptom_id != "")
				  				instantReflectionClass += ' instant-reflection-row-'+value.initial_source_symptom_id;
				  			if(typeof(value.comparing_source_symptom_id) != "undefined" && value.comparing_source_symptom_id !== null && value.comparing_source_symptom_id != "")
				  				instantReflectionClass += ' instant-reflection-row-'+value.comparing_source_symptom_id;
				  			var activeSymptomId = value.initial_source_symptom_id;
				  			var activeSymptomType = "initial";
				  			var displaySourceCode = (typeof(value.initial_source_code) != "undefined" && value.initial_source_code !== null && value.initial_source_code != "") ? value.initial_source_code : "";

				  			var initial_source_symptom_de = (typeof(value.initial_source_symptom_de) != "undefined" && value.initial_source_symptom_de !== null && value.initial_source_symptom_de != "") ? b64DecodeUnicode(value.initial_source_symptom_de) : "";
				  			var initial_source_symptom_en = (typeof(value.initial_source_symptom_en) != "undefined" && value.initial_source_symptom_en !== null && value.initial_source_symptom_en != "") ? b64DecodeUnicode(value.initial_source_symptom_en) : "";
				  			var displaySymptomString = "";

				  			if(value.comparison_language == "en"){
				  				displaySymptomString = initial_source_symptom_en;

				  				if(initial_source_original_language == "en"){
				  					var tmpString = "";
				  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en table-original-symptom">'+initial_source_symptom_en+'</div>' : "";
				  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de hidden">'+initial_source_symptom_de+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  				else{
				  					var tmpString = "";
				  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-de table-original-symptom hidden">'+initial_source_symptom_de+'</div>' : "";
				  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-en">'+initial_source_symptom_en+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  			}
				  			else{
				  				displaySymptomString = initial_source_symptom_de;

				  				if(initial_source_original_language == "de"){
				  					var tmpString = "";
				  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de table-original-symptom">'+initial_source_symptom_de+'</div>' : "";
				  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en hidden">'+initial_source_symptom_en+'</div>' : "";
				  					
				  					displaySymptomString = tmpString;
				  				}
				  				else{
				  					var tmpString = "";
				  					tmpString += (initial_source_symptom_en != "") ? '<div class="table-symptom-cnr table-symptom-hidden table-symptom-en table-original-symptom hidden">'+initial_source_symptom_en+'</div>' : "";
				  					tmpString += (initial_source_symptom_de != "") ? '<div class="table-symptom-cnr table-symptom-visible table-symptom-de">'+initial_source_symptom_de+'</div>' : "";

				  					displaySymptomString = tmpString;
				  				}
				  			}
				  			var displayPercentage = "";
				  			var rowInlineStyle = '';
				  			var symptomColumnInlineStyle = '';
				  			var commandColumnClass = '';

				  			if(typeof(value.initial_source_symptom_comment) != "undefined" && value.initial_source_symptom_comment !== null && value.initial_source_symptom_comment != ""){
					  			commentClasses += ' active';
					  		}
					  		if(typeof(value.initial_source_symptom_footnote) != "undefined" && value.initial_source_symptom_footnote !== null && value.initial_source_symptom_footnote != ""){
					  			footnoteClasses += ' active';
					  		}
					  		if(response.un_matched_symptoms_set_number == 0)
					  			rowClass = " initial-source-symptom-row";

					  		if(displaySourceCode != value.initial_saved_version_source_code)
					  			saved_version_source_code = '<br><span class= "saved-version-source-code">'+value.initial_saved_version_source_code+'</span>';
				  		}

				  		if(value.is_unmatched_symptom == 1){
				  			instantReflectionClass += ' instant-reflection-unmatched-row';
				  			translation_toggle_btn_type = "comparative";
				  		}
				  		// var rowClass = "";
				  		// if(value.is_initial_source == 1)
				  		// 	var rowClass = "initial-source-symptom-row";

				  		// Matched symptom ids
				  		var matched_symptom_ids_string = "";
				  		if(typeof(response.matched_symptom_ids) != "undefined" && response.matched_symptom_ids !== null) {
				  			matched_symptom_ids_string = response.matched_symptom_ids.join();	
				  		}
				  		$('.matched-symptom-ids').val(matched_symptom_ids_string);

				  		//console.log(comparingSymptomHighlightedEndcod);
				  		html += '<tr id="row_'+uniqueId+'" class="'+instantReflectionClass+rowClass+'" '+rowInlineStyle+'>';
				  		html += '	<td style="width: 12%;" class="text-center">'+displaySourceCode+saved_version_source_code+'</td>';
				  		html += '	<td '+symptomColumnInlineStyle+'>'+displaySymptomString+'</td>';
				  		html += '	<td style="width: 5%;" class="text-center">'+displayPercentage+'</td>';
				  		html += '	<th style="width: 17%;">';
				  		html += '		<ul class="info-linkage-group">';
				  		html += '			<li>';
				  		html += '				<a onclick="showInfo('+activeSymptomId+')" title="info" href="javascript:void(0)" data-item="info" data-unique-id="'+uniqueId+'"><i class="fas fa-info-circle"></i></a>';
				  		html += '			</li>';
				  		html += '			<li>';
				  		html += '				<a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)" data-item="edit" data-unique-id="'+uniqueId+'" data-active-symptom-id="'+active_symptom_id+'" data-active-original-source-id="'+active_original_source_id+'"><i class="fas fa-pencil-alt"></i></a>';
				  		html += '			</li>';
				  		html += '			<li>';
				  		html += '				<a class="'+commentClasses+'" id="comment_icon_'+uniqueId+'" onclick="showComment('+activeSymptomId+', '+uniqueId+')" title="comment" href="javascript:void(0)" data-item="comment" data-unique-id="'+uniqueId+'"><i class="fas fa-comment-alt"></i></a>';
				  		html += '			</li>';
				  		html += '			<li>';
				  		html += '				<a class="'+footnoteClasses+'" id="footnote_icon_'+uniqueId+'" onclick="showFootnote('+activeSymptomId+', '+uniqueId+')" title="footnote" href="javascript:void(0)" data-item="footnote" data-unique-id="'+uniqueId+'"><i class="fas fa-sticky-note"></i></a>';
				  		html += '			</li>';
				  		html += '			<li>';
				  		html += '				<a class="translation-toggle-btn translation-toggle-btn-'+translation_toggle_btn_type+'" title="translation" href="javascript:void(0)" data-item="translate" data-unique-id="'+uniqueId+'"><!-- <i class="fas fa-language"></i> -->T</a>';
				  		html += '			</li>';
				  		if(value.is_final_version_available != 0){
				  			// is_final_version_available values (0 = No, 1 = Connect edit, 2 = Paste edit)
				  			var fvName = "";
				  			var fvTitle = "";
				  			if(value.is_final_version_available == 1){
				  				fvName = "CE";
				  				fvTitle = "Connect edit";
				  			} else if(value.is_final_version_available == 2){
				  				fvName = "PE";
				  				fvTitle = "Paste edit";
				  			}
				  			html += '			<li>';
					  		html += '				<a class="'+FVBtnClasses+'" title="'+fvTitle+'" href="javascript:void(0)" data-item="FV" data-unique-id="'+uniqueId+'">'+fvName+'</a>';
					  		html += '			</li>';
				  		}
				  		html += '			<li>';
				  		html += '				<a href="javascript:void(0)" id="v_btn_'+uniqueId+'" class="'+vBtnClasses+'" '+vBtnDisable+' title="'+vBtnTitle+'" data-unique-id="'+uniqueId+'" data-v-padding="0" data-is-recompare="0" data-initial-source-id="'+value.initial_source_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-connections-main-parent-symptom-id="'+value.connections_main_parent_symptom_id+'" data-initial-symptom-id="'+value.initial_source_symptom_id+'" data-comparing-symptom-id="'+value.comparing_source_symptom_id+'" data-active-symptom-type="'+activeSymptomType+'" data-is-connection-loaded="0" data-comparing-source-ids="'+response.comparing_source_ids+'" data-source-arznei-id="'+value.source_arznei_id+'" data-saved-comparison-comparing-source-ids="'+response.saved_comparison_comparing_source_ids+'" data-removable-row-class-chain=""><i class="fas fa-plus"></i></a>';
				  		html += '			</li>';
				  		html += '		</ul>';
				  		html += '		<input type="hidden" name="source_arznei_id[]" id="source_arznei_id_'+uniqueId+'" value="'+value.source_arznei_id+'">';
				  		html += '		<input type="hidden" name="initial_source_id[]" id="initial_source_id_'+uniqueId+'" value="'+value.initial_source_id+'">';
				  		html += '		<input type="hidden" name="initial_original_source_id[]" id="initial_original_source_id_'+uniqueId+'" value="'+value.initial_original_source_id+'">';
				  		html += '		<input type="hidden" name="initial_source_code[]" id="initial_source_code_'+uniqueId+'" value="'+value.initial_source_code+'">';
				  		html += '		<input type="hidden" name="initial_source_year[]" id="initial_source_year_'+uniqueId+'" value="'+value.initial_source_year+'">';
				  		html += '		<input type="hidden" name="comparing_source_id[]" id="comparing_source_id_'+uniqueId+'" value="'+value.comparing_source_id+'">';
				  		html += '		<input type="hidden" name="comparing_original_source_id[]" id="comparing_original_source_id_'+uniqueId+'" value="'+value.comparing_original_source_id+'">';
				  		html += '		<input type="hidden" name="comparing_source_code[]" id="comparing_source_code_'+uniqueId+'" value="'+value.comparing_source_code+'">';
				  		html += '		<input type="hidden" name="comparing_source_year[]" id="comparing_source_year_'+uniqueId+'" value="'+value.comparing_source_year+'">';
				  		html += '		<input type="hidden" name="initial_source_symptom_id[]" id="initial_source_symptom_id_'+uniqueId+'" value="'+value.initial_source_symptom_id+'">';
				  		// Initial German
				  		html += '		<input type="hidden" name="initial_source_symptom_de[]" id="initial_source_symptom_de_'+uniqueId+'" value="'+value.initial_source_symptom_de+'">';
						html += '		<input type="hidden" name="initial_source_symptom_highlighted_de[]" id="initial_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_de+'">';
						html += '		<input type="hidden" name="initial_source_symptom_before_conversion_de[]" id="initial_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_de+'">';
						html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_de[]" id="initial_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_de+'">';
						// Initial English
				  		html += '		<input type="hidden" name="initial_source_symptom_en[]" id="initial_source_symptom_en_'+uniqueId+'" value="'+value.initial_source_symptom_en+'">';
						html += '		<input type="hidden" name="initial_source_symptom_highlighted_en[]" id="initial_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_highlighted_en+'">';
						html += '		<input type="hidden" name="initial_source_symptom_before_conversion_en[]" id="initial_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_en+'">';
						html += '		<input type="hidden" name="initial_source_symptom_before_conversion_highlighted_en[]" id="initial_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.initial_source_symptom_before_conversion_highlighted_en+'">';
						// Comparing German
						html += '		<input type="hidden" name="comparing_source_symptom_de[]" id="comparing_source_symptom_de_'+uniqueId+'" value="'+value.comparing_source_symptom_de+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_de[]" id="comparing_source_symptom_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_de+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_de[]" id="comparing_source_symptom_before_conversion_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_de+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_de[]" id="comparing_source_symptom_before_conversion_highlighted_de_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_de+'">';
				  		// Comparing English
						html += '		<input type="hidden" name="comparing_source_symptom_en[]" id="comparing_source_symptom_en_'+uniqueId+'" value="'+value.comparing_source_symptom_en+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_highlighted_en[]" id="comparing_source_symptom_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_highlighted_en+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_en[]" id="comparing_source_symptom_before_conversion_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_en+'">';
				  		html += '		<input type="hidden" name="comparing_source_symptom_before_conversion_highlighted_en[]" id="comparing_source_symptom_before_conversion_highlighted_en_'+uniqueId+'" value="'+value.comparing_source_symptom_before_conversion_highlighted_en+'">';
				  		
				  		html += '		<input type="hidden" name="individual_comparison_language[]" id="individual_comparison_language_'+uniqueId+'" value="'+value.comparison_language+'">';

				  		html += '		<input type="hidden" name="comparing_source_symptom_id[]" id="comparing_source_symptom_id_'+uniqueId+'" value="'+value.comparing_source_symptom_id+'">';
				  		html += '		<input type="hidden" name="matching_percentage[]" id="matching_percentage_'+uniqueId+'" value="'+value.percentage+'">';
				  		html += '		<input type="hidden" name="is_connected[]" id="is_connected_'+uniqueId+'" value="0">';
				  		html += '		<input type="hidden" name="is_ns_connect[]" id="is_ns_connect_'+uniqueId+'" value="0">';
				  		html += '		<input type="hidden" name="ns_connect_note[]" id="ns_connect_note_'+uniqueId+'" value="">';
				  		html += '		<input type="hidden" name="is_pasted[]" id="is_pasted_'+uniqueId+'" value="'+value.is_pasted+'">';
				  		html += '		<input type="hidden" name="is_ns_paste[]" id="is_ns_paste_'+uniqueId+'" value="'+value.is_ns_paste+'">';
				  		html += '		<input type="hidden" name="ns_paste_note[]" id="ns_paste_note_'+uniqueId+'" value="'+value.ns_paste_note+'">';
				  		html += '		<input type="hidden" name="is_initial_source[]" id="is_initial_source_'+uniqueId+'" value="'+value.is_initial_source+'">';
				  		html += '		<input type="hidden" class="matched-symptom-ids" name="matched_symptom_ids[]" id="matched_symptom_ids_'+uniqueId+'" value="'+matched_symptom_ids_string+'">';
				  		html += '		<input type="hidden" name="main_parent_initial_symptom_id[]" id="main_parent_initial_symptom_id_'+uniqueId+'" value="'+value.main_parent_initial_symptom_id+'">';
				  		html += '		<input type="hidden" name="connections_main_parent_symptom_id[]" id="connections_main_parent_symptom_id_'+uniqueId+'" value="'+value.connections_main_parent_symptom_id+'">';
				  		html += '		<input type="hidden" name="similarity_rate_individual[]" id="similarity_rate_'+uniqueId+'" value="'+value.similarity_rate+'">';
				  		html += '		<input type="hidden" name="active_symptom_type[]" id="active_symptom_type_'+uniqueId+'" value="'+value.active_symptom_type+'">';
				  		html += '		<input type="hidden" name="comparing_source_ids_individual[]" id="comparing_source_ids_'+uniqueId+'" value="'+response.comparing_source_ids+'">';
				  		html += '		<input type="hidden" name="comparison_option_individual[]" id="comparison_option_'+uniqueId+'" value="'+value.comparison_option+'">';
				  		html += '		<input type="hidden" name="saved_comparison_comparing_source_ids_individual[]" id="saved_comparison_comparing_source_ids_'+uniqueId+'" value="'+response.saved_comparison_comparing_source_ids+'">';
				  		html += '		<input type="hidden" name="is_unmatched_symptom[]" id="is_unmatched_symptom_'+uniqueId+'" value="'+value.is_unmatched_symptom+'">';
				  		html += '	</th>';
				  		if(value.active_symptom_type == "comparing"){
					  		html += '	<th style="width: 19%;" class="command-column'+commandColumnClass+'">';
					  		html += '		<ul class="command-group">';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="nsc_btn_'+uniqueId+'" class="'+nsc_btn_class+'" title="Non secure connection" data-item="non-secure-connect" data-nsc-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="connecting_btn_'+uniqueId+'" class="'+connection_btn_class+'" title="connect" data-item="connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'"><i class="fas fa-link"></i></a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="connecting_edit_btn_'+uniqueId+'" class="'+connection_edit_btn_class+'" title="Connect edit" data-item="connect-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="3">CE</a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="nsp_btn_'+uniqueId+'" class="'+nspClasses+'" title="Non secure paste" data-item="non-secure-paste" data-nsp-note="" data-unique-id="'+uniqueId+'"><i class="fas fa-exclamation-triangle"></i></a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="paste_btn_'+uniqueId+'" class="'+paste_btn_class+'" title="Paste" data-item="paste" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'">P</a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="paste_edit_btn_'+uniqueId+'" class="'+paste_edit_btn_class+'" title="Paste edit" data-item="paste-edit" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparative-symptom-id="'+activeSymptomId+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-connection-or-paste-type="4">PE</a>';
					  		html += '			</li>';
					  		html += '			<li>';
					  		html += '				<a href="javascript:void(0)" id="swap_connect_btn_'+uniqueId+'" class="swap-connect-btn" title="Swap connect" data-item="swap-connect" data-unique-id="'+uniqueId+'" data-main-parent-initial-symptom-id="'+value.main_parent_initial_symptom_id+'" data-comparison-initial-source-id="'+value.comparison_initial_source_id+'" data-should-swap-connect-be-active="'+value.should_swap_connect_be_active+'"><i class="fas fa-recycle"></i></a>';
					  		html += '			</li>';
					  		html += '		</ul>';
					  		html += '	</th>';
				  		} else {
				  			html += '	<th style="width: 19%;" class="">';
				  			html += '	</th>';	
				  		}
				  		html += '</tr>';

				  		nummerOfRecordFetch = nummerOfRecordFetch + 1;

					});
					if(html != ""){
				  		// Removing hidden fields of saving comparison result
				  		var hiddenformElements = '';
						$( ".save-data" ).each(function() {
						  	var inputValue = $(this).val();
						  	var inputName = $(this).attr('name');
						  	var inputId = $(this).attr('id');
						  	if(inputName == "comparison_name")
						  		hiddenformElements += '<input class="hidden-save-data comparison-name" type="hidden" name="'+inputName+'" id="'+inputId+'_save" value="'+inputValue+'">';
						  	else
						  		hiddenformElements += '<input class="hidden-save-data" type="hidden" name="'+inputName+'" id="'+inputId+'_save" value="'+inputValue+'">';
						});
				  		var batchTable ='<form id="batch_result_form_'+step+'" class="batch-result-form append-recognizer">';
				  		if(response.un_matched_symptoms_set_number == 1)
				  		{
				  			batchTable +='	<table class="table">';
				  			batchTable +='		<tr>';
				  			batchTable +='			<td colspan="5" class="text-center" style="padding-top: 20px; padding-bottom: 20px; background-color: #F1FB3A; font-weight: 600; font-size: 16px;">Verbleibende, nicht übereinstimmende Symptome</td>';
				  			batchTable +='		</tr>';
				  			batchTable +='	</table>';
				  		}
				  		batchTable +='	<table class="table table-bordered">';
				  		batchTable += html;
				  		batchTable +='	</table>';
				  		batchTable += hiddenformElements;
				  		batchTable +='</form>';
				  		$(".no-records-found").remove();
				  		$('#loadingTr').remove();
				  		$('.append-recognizer').last().after(batchTable);
				  	}
				}
				$(document).ready(function () {
					// progress bar
					// $('.label-currently-processing').html("STEP "+response.process_stage);
					// $('.progress-thead .progress-bar').attr('aria-valuenow', response.progress_percentage).css('width', response.progress_percentage+"%");
					// $('.progress-thead .progress-bar').html(response.progress_percentage+"%");
				    // console.log('I m loaded!');
				    $('#loadingTr').remove();
				    $("#numberOfRecord").html(nummerOfRecordFetch);
				  	if( 'done' == response.step ) {
				  		$(".loading-more").remove();
						setTimeout(function() {
							$(".result-sub-btn").removeClass('hidden');
							$(".head-panel-sub-ul").removeClass('hidden');
							$(".command-column").removeClass('unclickable');
							$(".vbtn").removeClass('unclickable');
							$("#comparison_name").val(response.system_generated_comparison_name);
						    $(".progress-thead").remove();
						    $('#symptom_comparison_form').removeClass('unclickable');
						    $('#compare_submit_btn').prop('disabled', false);
						    $('#search_submit_btn').prop('disabled', false);
						}, 500);
						// var export_form = $('.edd-export-form');
						// export_form.find('.spinner').remove();
						// export_form.find('.edd-progress').remove();
						// window.location = response.url;
					} else if( 'error' == response.step ) {
						$(".loading-more").remove();
						if ( window.console && window.console.log ) {
							console.log( "Exception error" );
							console.log( response );
						}
					}else {
						$(".loading-more").remove();
						$(".command-column").removeClass('unclickable');
						$(".vbtn").removeClass('unclickable');

						var matched_symptom_ids_parameter_string = "";
						if(typeof(response.matched_symptom_ids) != "undefined" && response.matched_symptom_ids !== null) {
							matched_symptom_ids_parameter_string = response.matched_symptom_ids.join('-');	
						}
						var loadMoreBtn ='<button class="btn btn-default btn-load-more" onclick="loadMore( '+parseInt( response.step )+', '+nummerOfRecordFetch+', '+parseInt( response.total_batches_in_part1 )+', '+parseInt( response.total_batches_in_part2 )+', '+parseInt( response.is_stage2_checked )+', '+parseInt( response.un_matched_symptoms_set_number )+', \''+matched_symptom_ids_parameter_string+'\')" type="button">Load More</button>';
						$('.append-recognizer').last().after(loadMoreBtn);
						// setTimeout(function() {
						//     process_step( parseInt( response.step ), nummerOfRecordFetch, parseInt( response.total_batches_in_part1 ), parseInt( response.total_batches_in_part2 ), parseInt( response.is_stage2_checked ), parseInt( response.un_matched_symptoms_set_number ), data, response.matched_symptom_ids );
						// }, 500);
					}
				});
			}
		}
	}).fail(function (response) {
		if ( window.console && window.console.log ) {
			console.log( response );
		}
		$('#symptom_comparison_form').removeClass('unclickable');
		$('#compare_submit_btn').prop('disabled', false);
		$('#search_submit_btn').prop('disabled', false);
	});
}

function loadMore(step, number_of_records, total_batches_in_part1, total_batches_in_part2, is_stage2_checked, un_matched_symptoms_set_number, matched_symptom_ids){

	var loadingMore ='<div class="loading-more">Loading.. <img src="assets/img/loader.gif" alt="Loading..."></div>';
	$('.append-recognizer').last().after(loadingMore);

	var initial_source = $('#batch_result_form_1').find("#initial_source_save").val();
	var comparing_sources = $('#batch_result_form_1').find("#comparing_sources_save").val();
	var arznei_id = $('#batch_result_form_1').find("#arznei_id_save").val();
	var similarity_rate = $('#batch_result_form_1').find("#similarity_rate_save").val();
	var comparison_option = $('#batch_result_form_1').find("#comparison_option_save").val();
	var comparison_language = $('#batch_result_form_1').find("#comparison_language_save").val();

	var matched_symptom_ids_array = [];
	if(typeof(matched_symptom_ids) != "undefined" && matched_symptom_ids !== null) {
		matched_symptom_ids_array = matched_symptom_ids.split('-');	
	}
	var error_count = 0;

	if(initial_source == ""){
		error_count++;
	}
	if(comparing_sources == ""){
		error_count++;
	}
	if(arznei_id == ""){
		error_count++;
	}
	if(similarity_rate == ""){
		error_count++;
	}
	if(comparison_option == ""){
		error_count++;
	}
	if(comparison_language == ""){
		error_count++;
	}
	
	if(error_count == 0){
		var data = 'initial_source='+initial_source+'&comparing_sources='+comparing_sources+'&arznei_id='+arznei_id+'&similarity_rate='+similarity_rate+'&comparison_option='+comparison_option+'&comparison_language='+comparison_language;
	
		process_step( step, number_of_records, total_batches_in_part1, total_batches_in_part2, is_stage2_checked, un_matched_symptoms_set_number, data, matched_symptom_ids_array );
	}
}


$('body').on( 'click', '.symptom-edit-modal-submit-btn', function(e) {
	$(".symptom_edit_modal_loader .loading-msg").removeClass('hidden');
	$(".symptom_edit_modal_loader .error-msg").html('');
	if($(".symptom_edit_modal_loader").hasClass('hidden'))
		$(".symptom_edit_modal_loader").removeClass('hidden');

	$("#symptom_edit_container").hide();
	$("#symptom_edit_settings_container").hide();

	var symptom_edit_modal_original_source_id = $("#symptom_edit_modal_original_source_id").val();
	var symptom_edit_modal_symptom_id = $("#symptom_edit_modal_symptom_id").val();
	var symptom_edit_de = $("#symptom_edit_de").val();
    var symptom_edit_en = $("#symptom_edit_en").val();
	var error_count = 0;

	$(".symptom-edit-de-error").html("");
    $(".symptom-edit-en-error").html("");
    $('.symptom-edit-common-error-text').html("");
	if(symptom_edit_de == ""){
		$(".symptom-edit-de-error").html("This field cannot be empty");
		error_count++;
	}else{
		$(".symptom-edit-de-error").html('');
	}
	if(symptom_edit_en == ""){
		$(".symptom-edit-en-error").html("This field cannot be empty");
		error_count++;
	}else{
		$(".symptom-edit-en-error").html('');
	}
	if(symptom_edit_modal_original_source_id == "")
		error_count++;
	if(symptom_edit_modal_symptom_id == "")
		error_count++;

	if(error_count != 0){
		$('.symptom-edit-common-error-text').html("Required data not found.");
		$("#symptom_edit_container").show();
		$("#symptom_edit_settings_container").show();
		if(!$(".symptom_edit_modal_loader").hasClass('hidden'))
			$(".symptom_edit_modal_loader").addClass('hidden');
    	return false;
	} else {
		var data = $("#symptom_edit_form").serialize();
		$.ajax({
			type: 'POST',
			url: 'update-symptom-and-settings.php',
			data: {
				form: data
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "success"){
					// Re-Calling the main comprasion function to get the updated data.
					var initial_source = $('#batch_result_form_1').find("#initial_source_save").val();
					var comparing_sources = $('#batch_result_form_1').find("#comparing_sources_save").val();
					var arznei_id = $('#batch_result_form_1').find("#arznei_id_save").val();
					var similarity_rate = $('#batch_result_form_1').find("#similarity_rate_save").val();
					var comparison_option = $('#batch_result_form_1').find("#comparison_option_save").val();
					var comparison_language = $('#batch_result_form_1').find("#comparison_language_save").val();
					var error_count = 0;

					if(initial_source == ""){
						error_count++;
					}
					if(comparing_sources == ""){
						error_count++;
					}
					if(arznei_id == ""){
						error_count++;
					}
					if(similarity_rate == ""){
						error_count++;
					}
					if(comparison_option == ""){
						error_count++;
					}
					if(comparison_language == ""){
						error_count++;
					}
					
					if(error_count == 0){
						$(".progress-connection-thead").remove();
						$('.batch-search-result-form').remove();
						$('.batch-result-form').remove();
						$('#symptom_comparison_form').addClass('unclickable');
						$('#compare_submit_btn').prop('disabled', true);
						$('#search_submit_btn').prop('disabled', true);
						$("#comparison_name").val('');
						
						if(!$(".result-sub-btn").hasClass('hidden'))
							$(".result-sub-btn").addClass('hidden');

						if(!$(".head-panel-sub-ul").hasClass('hidden'))
							$(".head-panel-sub-ul").addClass('hidden');

						if($('.comparison-only-column').hasClass('hidden'))
							$('.comparison-only-column').removeClass('hidden');
						$("#numberOfRecord").html(0);

						$("#column_heading_symptom").html('Symptom');
						var loadingHtml = '';
						loadingHtml += '<tr id="loadingTr">';
						loadingHtml += '	<td colspan="5" class="text-center">Data loading..</td>';
						loadingHtml += '</tr>';

						$('#resultTable tbody').html(loadingHtml);

						var data = 'initial_source='+initial_source+'&comparing_sources='+comparing_sources+'&arznei_id='+arznei_id+'&similarity_rate='+similarity_rate+'&comparison_option='+comparison_option+'&comparison_language='+comparison_language;

						$(".progress-thead").remove();
						var progressBarHtml = '';
						progressBarHtml += '<thead class="progress-thead heading-table-bg">';
						progressBarHtml += '	<tr>';
						progressBarHtml += ' 		<th colspan="5">';
						progressBarHtml += ' 			<div class="text-center" style="margin-bottom: 5px;"><span class="label label-default label-currently-processing"></span></div>';
						progressBarHtml += ' 			<div class="progress comparison-progress">';
						progressBarHtml += ' 				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div>';
						progressBarHtml += ' 			</div>';
						progressBarHtml += ' 		</th>';
						progressBarHtml += '	</tr>';
						progressBarHtml += '</thead>';
						$('#resultTable thead').after(progressBarHtml);

						// start the process
						var matchedSymptomIds = [];
						process_step( 1, 0, 1, 1, 0, 0, data, matchedSymptomIds );

						$("#symptom_edit_container").show();
						$("#symptom_edit_settings_container").show();
						if(!$(".symptom_edit_modal_loader").hasClass('hidden'))
							$(".symptom_edit_modal_loader").addClass('hidden');
						$("#symptomEditModal").modal('hide'); 
					} else {
						$("#global_msg_container").html('<p class="text-center">Something went wrong, Could not update the contect. Please reload the comparison and try again!</p>');
						$("#globalMsgModal").modal('show');
						$(".progress-connection-thead").remove();
						$('.batch-result-form').removeClass('unclickable');
					}
				}else{
					$('.symptom-edit-common-error-text').html("Something went wrong!");
					$("#symptom_edit_container").show();
					$("#symptom_edit_settings_container").show();
					if(!$(".symptom_edit_modal_loader").hasClass('hidden'))
						$(".symptom_edit_modal_loader").addClass('hidden');
					console.log( response );
				}
			}
		}).fail(function (response) {
			$('.symptom-edit-common-error-text').html("Something went wrong!");
			$("#symptom_edit_container").show();
			$("#symptom_edit_settings_container").show();
			if(!$(".symptom_edit_modal_loader").hasClass('hidden'))
				$(".symptom_edit_modal_loader").addClass('hidden');
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
});

// 
$('body').on( 'click', '.symptom-edit-btn', function(e) {
	$("#symptom_edit_container").show();
	$("#symptom_edit_settings_container").show();

	$(".symptom_edit_modal_loader .loading-msg").removeClass('hidden');
	$(".symptom_edit_modal_loader .error-msg").html('');
	if($(".symptom_edit_modal_loader").hasClass('hidden'))
		$(".symptom_edit_modal_loader").removeClass('hidden');

	$("#symptom_edit_modal_symptom_id").val("");
	$("#symptom_edit_modal_original_source_id").val("");
	$(".symptom-edit-de-error").html('');
	$(".symptom-edit-en-error").html('');
	$('.symptom-edit-common-error-text').html("");

	$("#populated_connect_edit_modal_data").remove();
	$("#symptomEditModal").modal('show'); 

	var uniqueId = $(this).attr("data-unique-id");
	var active_symptom_id = $(this).attr("data-active-symptom-id");
	var active_original_source_id = $(this).attr("data-active-original-source-id");
	// console.log(uniqueId+" / "+active_symptom_id);
	$.ajax({
		type: 'POST',
		url: 'fetch-symptom-edit-info.php',
		data: {
			symptom_id: active_symptom_id,
			original_source_id: active_original_source_id
		},
		dataType: "json",
		success: function( response ) {
			console.log(response);
			if(response.status == "success"){
				if(typeof(response.result_data) != "undefined" && response.result_data !== null) {
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}

					Beschreibung_de = (typeof(resultData.Beschreibung_de) != "undefined" && resultData.Beschreibung_de !== null && resultData.Beschreibung_de != "") ? resultData.Beschreibung_de : "";
					Beschreibung_en = (typeof(resultData.Beschreibung_en) != "undefined" && resultData.Beschreibung_en !== null && resultData.Beschreibung_en != "") ? resultData.Beschreibung_en : "";
					$("#symptom_edit_de").val(Beschreibung_de);
					$("#symptom_edit_en").val(Beschreibung_en);

					// Symptom type
					(typeof resultData.symptom_type !== 'undefined' && resultData.symptom_type !== null && resultData.symptom_type != "") ? $("#symptom_type option[value='"+resultData.symptom_type+"']").prop('selected', true) : $("#symptom_type option[value='']").prop('selected', true);

					// Grading section start
					(typeof resultData.normal !== 'undefined' && resultData.normal !== null && resultData.normal != "") ? $("#normal option[value='"+resultData.normal+"']").prop('selected', true) : $("#normal option[value='']").prop('selected', true);
					(typeof resultData.normal_within_parentheses !== 'undefined' && resultData.normal_within_parentheses !== null && resultData.normal_within_parentheses != "") ? $("#normal_within_parentheses option[value='"+resultData.normal_within_parentheses+"']").prop('selected', true) : $("#normal_within_parentheses option[value='']").prop('selected', true);
					(typeof resultData.normal_end_with_t !== 'undefined' && resultData.normal_end_with_t !== null && resultData.normal_end_with_t != "") ? $("#normal_end_with_t option[value='"+resultData.normal_end_with_t+"']").prop('selected', true) : $("#normal_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.normal_end_with_tt !== 'undefined' && resultData.normal_end_with_tt !== null && resultData.normal_end_with_tt != "") ? $("#normal_end_with_tt option[value='"+resultData.normal_end_with_tt+"']").prop('selected', true) : $("#normal_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.normal_begin_with_degree !== 'undefined' && resultData.normal_begin_with_degree !== null && resultData.normal_begin_with_degree != "") ? $("#normal_begin_with_degree option[value='"+resultData.normal_begin_with_degree+"']").prop('selected', true) : $("#normal_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.normal_end_with_degree !== 'undefined' && resultData.normal_end_with_degree !== null && resultData.normal_end_with_degree != "") ? $("#normal_end_with_degree option[value='"+resultData.normal_end_with_degree+"']").prop('selected', true) : $("#normal_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.normal_begin_with_asterisk !== 'undefined' && resultData.normal_begin_with_asterisk !== null && resultData.normal_begin_with_asterisk != "") ? $("#normal_begin_with_asterisk option[value='"+resultData.normal_begin_with_asterisk+"']").prop('selected', true) : $("#normal_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.normal_begin_with_asterisk_end_with_t !== 'undefined' && resultData.normal_begin_with_asterisk_end_with_t !== null && resultData.normal_begin_with_asterisk_end_with_t != "") ? $("#normal_begin_with_asterisk_end_with_t option[value='"+resultData.normal_begin_with_asterisk_end_with_t+"']").prop('selected', true) : $("#normal_begin_with_asterisk_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.normal_begin_with_asterisk_end_with_tt !== 'undefined' && resultData.normal_begin_with_asterisk_end_with_tt !== null && resultData.normal_begin_with_asterisk_end_with_tt != "") ? $("#normal_begin_with_asterisk_end_with_tt option[value='"+resultData.normal_begin_with_asterisk_end_with_tt+"']").prop('selected', true) : $("#normal_begin_with_asterisk_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.normal_begin_with_asterisk_end_with_degree !== 'undefined' && resultData.normal_begin_with_asterisk_end_with_degree !== null && resultData.normal_begin_with_asterisk_end_with_degree != "") ? $("#normal_begin_with_asterisk_end_with_degree option[value='"+resultData.normal_begin_with_asterisk_end_with_degree+"']").prop('selected', true) : $("#normal_begin_with_asterisk_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift !== 'undefined' && resultData.sperrschrift !== null && resultData.sperrschrift != "") ? $("#sperrschrift option[value='"+resultData.sperrschrift+"']").prop('selected', true) : $("#sperrschrift option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift_begin_with_degree !== 'undefined' && resultData.sperrschrift_begin_with_degree !== null && resultData.sperrschrift_begin_with_degree != "") ? $("#sperrschrift_begin_with_degree option[value='"+resultData.sperrschrift_begin_with_degree+"']").prop('selected', true) : $("#sperrschrift_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift_begin_with_asterisk !== 'undefined' && resultData.sperrschrift_begin_with_asterisk !== null && resultData.sperrschrift_begin_with_asterisk != "") ? $("#sperrschrift_begin_with_asterisk option[value='"+resultData.sperrschrift_begin_with_asterisk+"']").prop('selected', true) : $("#sperrschrift_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift_bold !== 'undefined' && resultData.sperrschrift_bold !== null && resultData.sperrschrift_bold != "") ? $("#sperrschrift_bold option[value='"+resultData.sperrschrift_bold+"']").prop('selected', true) : $("#sperrschrift_bold option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift_bold_begin_with_degree !== 'undefined' && resultData.sperrschrift_bold_begin_with_degree !== null && resultData.sperrschrift_bold_begin_with_degree != "") ? $("#sperrschrift_bold_begin_with_degree option[value='"+resultData.sperrschrift_bold_begin_with_degree+"']").prop('selected', true) : $("#sperrschrift_bold_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.sperrschrift_bold_begin_with_asterisk !== 'undefined' && resultData.sperrschrift_bold_begin_with_asterisk !== null && resultData.sperrschrift_bold_begin_with_asterisk != "") ? $("#sperrschrift_bold_begin_with_asterisk option[value='"+resultData.sperrschrift_bold_begin_with_asterisk+"']").prop('selected', true) : $("#sperrschrift_bold_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.kursiv !== 'undefined' && resultData.kursiv !== null && resultData.kursiv != "") ? $("#kursiv option[value='"+resultData.kursiv+"']").prop('selected', true) : $("#kursiv option[value='']").prop('selected', true);
					(typeof resultData.kursiv_end_with_t !== 'undefined' && resultData.kursiv_end_with_t !== null && resultData.kursiv_end_with_t != "") ? $("#kursiv_end_with_t option[value='"+resultData.kursiv_end_with_t+"']").prop('selected', true) : $("#kursiv_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.kursiv_end_with_tt !== 'undefined' && resultData.kursiv_end_with_tt !== null && resultData.kursiv_end_with_tt != "") ? $("#kursiv_end_with_tt option[value='"+resultData.kursiv_end_with_tt+"']").prop('selected', true) : $("#kursiv_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.kursiv_begin_with_degree !== 'undefined' && resultData.kursiv_begin_with_degree !== null && resultData.kursiv_begin_with_degree != "") ? $("#kursiv_begin_with_degree option[value='"+resultData.kursiv_begin_with_degree+"']").prop('selected', true) : $("#kursiv_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.kursiv_end_with_degree !== 'undefined' && resultData.kursiv_end_with_degree !== null && resultData.kursiv_end_with_degree != "") ? $("#kursiv_end_with_degree option[value='"+resultData.kursiv_end_with_degree+"']").prop('selected', true) : $("#kursiv_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.kursiv_begin_with_asterisk !== 'undefined' && resultData.kursiv_begin_with_asterisk !== null && resultData.kursiv_begin_with_asterisk != "") ? $("#kursiv_begin_with_asterisk option[value='"+resultData.kursiv_begin_with_asterisk+"']").prop('selected', true) : $("#kursiv_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.kursiv_begin_with_asterisk_end_with_t !== 'undefined' && resultData.kursiv_begin_with_asterisk_end_with_t !== null && resultData.kursiv_begin_with_asterisk_end_with_t != "") ? $("#kursiv_begin_with_asterisk_end_with_t option[value='"+resultData.kursiv_begin_with_asterisk_end_with_t+"']").prop('selected', true) : $("#kursiv_begin_with_asterisk_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.kursiv_begin_with_asterisk_end_with_tt !== 'undefined' && resultData.kursiv_begin_with_asterisk_end_with_tt !== null && resultData.kursiv_begin_with_asterisk_end_with_tt != "") ? $("#kursiv_begin_with_asterisk_end_with_tt option[value='"+resultData.kursiv_begin_with_asterisk_end_with_tt+"']").prop('selected', true) : $("#kursiv_begin_with_asterisk_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.kursiv_begin_with_asterisk_end_with_degree !== 'undefined' && resultData.kursiv_begin_with_asterisk_end_with_degree !== null && resultData.kursiv_begin_with_asterisk_end_with_degree != "") ? $("#kursiv_begin_with_asterisk_end_with_degree option[value='"+resultData.kursiv_begin_with_asterisk_end_with_degree+"']").prop('selected', true) : $("#kursiv_begin_with_asterisk_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold !== 'undefined' && resultData.kursiv_bold !== null && resultData.kursiv_bold != "") ? $("#kursiv_bold option[value='"+resultData.kursiv_bold+"']").prop('selected', true) : $("#kursiv_bold option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold_begin_with_asterisk_end_with_t !== 'undefined' && resultData.kursiv_bold_begin_with_asterisk_end_with_t !== null && resultData.kursiv_bold_begin_with_asterisk_end_with_t != "") ? $("#kursiv_bold_begin_with_asterisk_end_with_t option[value='"+resultData.kursiv_bold_begin_with_asterisk_end_with_t+"']").prop('selected', true) : $("#kursiv_bold_begin_with_asterisk_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold_begin_with_asterisk_end_with_tt !== 'undefined' && resultData.kursiv_bold_begin_with_asterisk_end_with_tt !== null && resultData.kursiv_bold_begin_with_asterisk_end_with_tt != "") ? $("#kursiv_bold_begin_with_asterisk_end_with_tt option[value='"+resultData.kursiv_bold_begin_with_asterisk_end_with_tt+"']").prop('selected', true) : $("#kursiv_bold_begin_with_asterisk_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold_begin_with_degree !== 'undefined' && resultData.kursiv_bold_begin_with_degree !== null && resultData.kursiv_bold_begin_with_degree != "") ? $("#kursiv_bold_begin_with_degree option[value='"+resultData.kursiv_bold_begin_with_degree+"']").prop('selected', true) : $("#kursiv_bold_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold_begin_with_asterisk !== 'undefined' && resultData.kursiv_bold_begin_with_asterisk !== null && resultData.kursiv_bold_begin_with_asterisk != "") ? $("#kursiv_bold_begin_with_asterisk option[value='"+resultData.kursiv_bold_begin_with_asterisk+"']").prop('selected', true) : $("#kursiv_bold_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.kursiv_bold_begin_with_asterisk_end_with_degree !== 'undefined' && resultData.kursiv_bold_begin_with_asterisk_end_with_degree !== null && resultData.kursiv_bold_begin_with_asterisk_end_with_degree != "") ? $("#kursiv_bold_begin_with_asterisk_end_with_degree option[value='"+resultData.kursiv_bold_begin_with_asterisk_end_with_degree+"']").prop('selected', true) : $("#kursiv_bold_begin_with_asterisk_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.fett !== 'undefined' && resultData.fett !== null && resultData.fett != "") ? $("#fett option[value='"+resultData.fett+"']").prop('selected', true) : $("#fett option[value='']").prop('selected', true);
					(typeof resultData.fett_end_with_t !== 'undefined' && resultData.fett_end_with_t !== null && resultData.fett_end_with_t != "") ? $("#fett_end_with_t option[value='"+resultData.fett_end_with_t+"']").prop('selected', true) : $("#fett_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.fett_end_with_tt !== 'undefined' && resultData.fett_end_with_tt !== null && resultData.fett_end_with_tt != "") ? $("#fett_end_with_tt option[value='"+resultData.fett_end_with_tt+"']").prop('selected', true) : $("#fett_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.fett_begin_with_degree !== 'undefined' && resultData.fett_begin_with_degree !== null && resultData.fett_begin_with_degree != "") ? $("#fett_begin_with_degree option[value='"+resultData.fett_begin_with_degree+"']").prop('selected', true) : $("#fett_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.fett_end_with_degree !== 'undefined' && resultData.fett_end_with_degree !== null && resultData.fett_end_with_degree != "") ? $("#fett_end_with_degree option[value='"+resultData.fett_end_with_degree+"']").prop('selected', true) : $("#fett_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.fett_begin_with_asterisk !== 'undefined' && resultData.fett_begin_with_asterisk !== null && resultData.fett_begin_with_asterisk != "") ? $("#fett_begin_with_asterisk option[value='"+resultData.fett_begin_with_asterisk+"']").prop('selected', true) : $("#fett_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.fett_begin_with_asterisk_end_with_t !== 'undefined' && resultData.fett_begin_with_asterisk_end_with_t !== null && resultData.fett_begin_with_asterisk_end_with_t != "") ? $("#fett_begin_with_asterisk_end_with_t option[value='"+resultData.fett_begin_with_asterisk_end_with_t+"']").prop('selected', true) : $("#fett_begin_with_asterisk_end_with_t option[value='']").prop('selected', true);
					(typeof resultData.fett_begin_with_asterisk_end_with_tt !== 'undefined' && resultData.fett_begin_with_asterisk_end_with_tt !== null && resultData.fett_begin_with_asterisk_end_with_tt != "") ? $("#fett_begin_with_asterisk_end_with_tt option[value='"+resultData.fett_begin_with_asterisk_end_with_tt+"']").prop('selected', true) : $("#fett_begin_with_asterisk_end_with_tt option[value='']").prop('selected', true);
					(typeof resultData.fett_begin_with_asterisk_end_with_degree !== 'undefined' && resultData.fett_begin_with_asterisk_end_with_degree !== null && resultData.fett_begin_with_asterisk_end_with_degree != "") ? $("#fett_begin_with_asterisk_end_with_degree option[value='"+resultData.fett_begin_with_asterisk_end_with_degree+"']").prop('selected', true) : $("#fett_begin_with_asterisk_end_with_degree option[value='']").prop('selected', true);
					(typeof resultData.gross !== 'undefined' && resultData.gross !== null && resultData.gross != "") ? $("#gross option[value='"+resultData.gross+"']").prop('selected', true) : $("#gross option[value='']").prop('selected', true);
					(typeof resultData.gross_begin_with_degree !== 'undefined' && resultData.gross_begin_with_degree !== null && resultData.gross_begin_with_degree != "") ? $("#gross_begin_with_degree option[value='"+resultData.gross_begin_with_degree+"']").prop('selected', true) : $("#gross_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.gross_begin_with_asterisk !== 'undefined' && resultData.gross_begin_with_asterisk !== null && resultData.gross_begin_with_asterisk != "") ? $("#gross_begin_with_asterisk option[value='"+resultData.gross_begin_with_asterisk+"']").prop('selected', true) : $("#gross_begin_with_asterisk option[value='']").prop('selected', true);
					(typeof resultData.gross_bold !== 'undefined' && resultData.gross_bold !== null && resultData.gross_bold != "") ? $("#gross_bold option[value='"+resultData.gross_bold+"']").prop('selected', true) : $("#gross_bold option[value='']").prop('selected', true);
					(typeof resultData.gross_bold_begin_with_degree !== 'undefined' && resultData.gross_bold_begin_with_degree !== null && resultData.gross_bold_begin_with_degree != "") ? $("#gross_bold_begin_with_degree option[value='"+resultData.gross_bold_begin_with_degree+"']").prop('selected', true) : $("#gross_bold_begin_with_degree option[value='']").prop('selected', true);
					(typeof resultData.gross_bold_begin_with_asterisk !== 'undefined' && resultData.gross_bold_begin_with_asterisk !== null && resultData.gross_bold_begin_with_asterisk != "") ? $("#gross_bold_begin_with_asterisk option[value='"+resultData.gross_bold_begin_with_asterisk+"']").prop('selected', true) : $("#gross_bold_begin_with_asterisk option[value='']").prop('selected', true);
					// Grading section end

					$("#symptom_edit_modal_symptom_id").val(active_symptom_id);
					$("#symptom_edit_modal_original_source_id").val(active_original_source_id);

					if(!$(".symptom_edit_modal_loader").hasClass('hidden'))
						$(".symptom_edit_modal_loader").addClass('hidden');
				} else {
					$(".symptom_edit_modal_loader .loading-msg").addClass('hidden');
					$(".symptom_edit_modal_loader .error-msg").html('Something went wrong!');
					console.log(response);
				}
			} else {
				$(".symptom_edit_modal_loader .loading-msg").addClass('hidden');
				$(".symptom_edit_modal_loader .error-msg").html('Something went wrong!');
				console.log(response);
			}
		}
	}).fail(function (response) {
		$(".symptom_edit_modal_loader .loading-msg").addClass('hidden');
		$(".symptom_edit_modal_loader .error-msg").html('Something went wrong!');
		if ( window.console && window.console.log ) {
			console.log( response );
		}
		// $('#symptom_comparison_form').removeClass('unclickable');
		// $('#compare_submit_btn').prop('disabled', false);
		// $('#search_submit_btn').prop('disabled', false);
	});
});

// 
$('body').on( 'click', '.connecting-edit-btn, .paste-edit-btn', function(e) {
	var uniqueId = $(this).attr("data-unique-id");
	var connectionInitialSymptomId = $(this).attr("data-main-parent-initial-symptom-id");
	var connectionComparativeSymptomId = $(this).attr("data-comparative-symptom-id");
	var currentConnectionOperationType = $(this).attr("data-connection-or-paste-type");
	var fv_comparison_option = $("#comparison_option_"+uniqueId).val();

	// Making the error msg empty
	$(".fv-symptom-de-error").html("");
    $(".fv-symptom-en-error").html("");
    $('.common-error-text').html("");
    $('#fv_unique_id').val(uniqueId);

	var workingUniqueId = "";
	$("#connect_edit_modal_loader .loading-msg").removeClass('hidden');
	$("#connect_edit_modal_loader .error-msg").html('');
	if($("#connect_edit_modal_loader").hasClass('hidden'))
		$("#connect_edit_modal_loader").removeClass('hidden');

	$("#populated_connect_edit_modal_data").remove();
	$("#connectEditModal").modal('show');
	if($("#connect_edit_symptom_de_container").hasClass('hidden'))
		$("#connect_edit_symptom_de_container").removeClass('hidden');
	if($("#connect_edit_symptom_en_container").hasClass('hidden'))
		$("#connect_edit_symptom_en_container").removeClass('hidden');
	if(uniqueId != "" && connectionInitialSymptomId != "" && connectionComparativeSymptomId != "" && currentConnectionOperationType != ""){
		$('#fv_connection_or_paste_type').val(currentConnectionOperationType);
		var editableSymptomDe = "";
		var editableSymptomEn = "";
		var initial_source_symptom_de = $("#initial_source_symptom_de_"+connectionInitialSymptomId).val();
		var initial_source_symptom_en = $("#initial_source_symptom_en_"+connectionInitialSymptomId).val();
		var comparing_source_symptom_de = $("#comparing_source_symptom_de_"+uniqueId).val();
		var comparing_source_symptom_en = $("#comparing_source_symptom_en_"+uniqueId).val();

		var fv_original_quelle_id = "";
		var fv_arznei_id = $("#source_arznei_id_"+uniqueId).val();
		$('#fv_arznei_id').val(fv_arznei_id);
		$('#fv_initial_source_symptom_id').val(connectionInitialSymptomId);
		$('#fv_comparing_source_symptom_id').val(connectionComparativeSymptomId);
		$('#fv_comparison_option').val(fv_comparison_option);
		if(currentConnectionOperationType == "3") {
			$('#fv_symptom_id').val(connectionInitialSymptomId);
			fv_original_quelle_id = $("#initial_original_source_id_"+connectionInitialSymptomId).val();
			// Connect Edit
			$("#connectEditModal .modal-title").html("Connect Edit");
			workingUniqueId = connectionInitialSymptomId;
		    editableSymptomDe = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
			editableSymptomEn = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";
		} else if(currentConnectionOperationType == "4") {
			$('#fv_symptom_id').val(connectionComparativeSymptomId);
			fv_original_quelle_id = $("#comparing_original_source_id_"+uniqueId).val();
			// Paste Edit
			$("#connectEditModal .modal-title").html("Paste Edit");
			workingUniqueId = uniqueId;
			editableSymptomDe = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
			editableSymptomEn = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
		}
		$('#fv_original_quelle_id').val(fv_original_quelle_id);

		if(workingUniqueId != ""){

			if(!$("#connect_edit_modal_loader").hasClass('hidden'))
				$("#connect_edit_modal_loader").addClass('hidden');
			
			if(editableSymptomDe != ""){
				$("#fv_symptom_de").val(editableSymptomDe);
				$("#check_fv_symptom_de").val(1);
			}
			else{
				$("#check_fv_symptom_de").val(0);
				$("#fv_symptom_de").val("");
				// $("#connect_edit_symptom_de_container").addClass('hidden');
			}
			
			if(editableSymptomEn != ""){
				$("#check_fv_symptom_en").val(1);
				$("#fv_symptom_en").val(editableSymptomEn);
			}
			else{
				$("#check_fv_symptom_en").val(0);
				$("#fv_symptom_en").val("");
				// $("#connect_edit_symptom_en_container").addClass('hidden');
			}
		
			var decoded_initial_source_symptom_de = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
			var decoded_initial_source_symptom_en = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";	
			var decoded_comparing_source_symptom_de = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
			var decoded_comparing_source_symptom_en = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
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
			$("#connect_edit_modal_container").append(html);

		}else{
			$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
			$("#connect_edit_modal_loader .error-msg").html('Something went wrong!');
		}
	}else{
		$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
		$("#connect_edit_modal_loader .error-msg").html('Something went wrong!');
	}
});

$('body').on( 'submit', '#connect_edit_form', function(e) {
	e.preventDefault();
	var $th = $(this);
	if($th.hasClass('processing'))
		return;
	$th.addClass('processing');

	var vPadding = $(this).attr("data-v-padding");
    var isRecompare = parseInt($(this).attr("data-is-recompare"));
    var fv_symptom_de = $("#fv_symptom_de").val();
    var fv_symptom_en = $("#fv_symptom_en").val();
    var fv_symptom_id = $("#fv_symptom_id").val();
    var fv_initial_source_symptom_id = $("#fv_initial_source_symptom_id").val();
    var fv_comparing_source_symptom_id = $("#fv_comparing_source_symptom_id").val();
    var fv_unique_id = $("#fv_unique_id").val();
    var fv_connection_or_paste_type = $("#fv_connection_or_paste_type").val();
    var error_count = 0;

    $(".fv-symptom-de-error").html("");
    $(".fv-symptom-en-error").html("");
    $('.common-error-text').html("");
	/*if(fv_symptom_de == ""){
		$(".fv-symptom-de-error").html("This field cannot be empty");
		error_count++;
	}
	if(fv_symptom_en == ""){
		$(".fv-symptom-en-error").html("This field cannot be empty");
		error_count++;
	}*/

	// FOR PRESENTATION ONLY ON 10-10-2020
	if(fv_symptom_en == "" && fv_symptom_de == ""){
		// $(".fv-symptom-en-error").html("This field cannot be empty");
		error_count++;
	} 

    if(fv_symptom_id == "")
		error_count++;
	if(fv_unique_id == "")
		error_count++;
	if(fv_connection_or_paste_type == "")
		error_count++;

    if(error_count != 0){
    	$('.common-error-text').html("Required data not found.");
    	$th.removeClass('processing');
    	return false;
    } else {
    	
    	var uniqueId = fv_unique_id;
		var dataArray = [];

	    dataArray['saved_comparison_quelle_id'] = $("#saved_comparison_quelle_id").val();
	    dataArray['uniqueId'] = uniqueId;
	    dataArray['source_arznei_id'] = $("#source_arznei_id_"+uniqueId).val();
	    dataArray['initial_source_id'] = $("#initial_source_id_"+uniqueId).val();
	    dataArray['initial_original_source_id'] = $("#initial_original_source_id_"+uniqueId).val();
	    dataArray['initial_source_code'] = $("#initial_source_code_"+uniqueId).val();
	    dataArray['comparing_source_id'] = $("#comparing_source_id_"+uniqueId).val();
	    dataArray['comparing_original_source_id'] = $("#comparing_original_source_id_"+uniqueId).val();
	    dataArray['comparing_source_code'] = $("#comparing_source_code_"+uniqueId).val();
	    dataArray['initial_source_symptom_id'] = $("#initial_source_symptom_id_"+uniqueId).val();
	    dataArray['initial_source_symptom_de'] = $("#initial_source_symptom_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_en'] = $("#initial_source_symptom_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_de'] = $("#comparing_source_symptom_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_en'] = $("#comparing_source_symptom_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_highlighted_de'] = $("#initial_source_symptom_highlighted_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_highlighted_en'] = $("#initial_source_symptom_highlighted_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_highlighted_de'] = $("#comparing_source_symptom_highlighted_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_highlighted_en'] = $("#comparing_source_symptom_highlighted_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_de'] = $("#initial_source_symptom_before_conversion_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_en'] = $("#initial_source_symptom_before_conversion_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_de'] = $("#comparing_source_symptom_before_conversion_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_en'] = $("#comparing_source_symptom_before_conversion_en_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_highlighted_de'] = $("#initial_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
	    dataArray['initial_source_symptom_before_conversion_highlighted_en'] = $("#initial_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_highlighted_de'] = $("#comparing_source_symptom_before_conversion_highlighted_de_"+uniqueId).val();
	    dataArray['comparing_source_symptom_before_conversion_highlighted_en'] = $("#comparing_source_symptom_before_conversion_highlighted_en_"+uniqueId).val();
		dataArray['individual_comparison_language'] = $("#individual_comparison_language_"+uniqueId).val();
		dataArray['comparing_source_symptom_id'] = $("#comparing_source_symptom_id_"+uniqueId).val();
		dataArray['matching_percentage'] = $("#matching_percentage_"+uniqueId).val();
		dataArray['is_connected'] = $("#is_connected_"+uniqueId).val();
		dataArray['is_ns_connect'] = $("#is_ns_connect_"+uniqueId).val();
		dataArray['ns_connect_note'] = $("#ns_connect_note_"+uniqueId).val();
		dataArray['is_pasted'] = $("#is_pasted_"+uniqueId).val();
		dataArray['is_ns_paste'] = $("#is_ns_paste_"+uniqueId).val();
		dataArray['ns_paste_note'] = $("#ns_paste_note_"+uniqueId).val();
		dataArray['is_initial_source'] = $("#is_initial_source_"+uniqueId).val();
		dataArray['similarity_rate'] = $("#similarity_rate_"+uniqueId).val();
		dataArray['active_symptom_type'] = $("#active_symptom_type_"+uniqueId).val();
		dataArray['comparing_source_ids'] = $("#comparing_source_ids_"+uniqueId).val();
		dataArray['matched_symptom_ids'] = $("#matched_symptom_ids_"+uniqueId).val();
		dataArray['comparison_option'] = $("#comparison_option_"+uniqueId).val();
		dataArray['savedComparisonComparingSourceIds'] = $("#saved_comparison_comparing_source_ids_"+uniqueId).val();
		dataArray['is_unmatched_symptom'] = $("#is_unmatched_symptom_"+uniqueId).val();
		dataArray['main_parent_initial_symptom_id'] = $("#connecting_edit_btn_"+uniqueId).attr("data-main-parent-initial-symptom-id");
		dataArray['comparison_initial_source_id'] = $("#connecting_edit_btn_"+uniqueId).attr("data-comparison-initial-source-id");
		dataArray['connections_main_parent_symptom_id'] = $("#connections_main_parent_symptom_id_"+uniqueId).val();
		dataArray['error_count'] = 0;
		dataArray['mainParentInitialSymptomIdsArr'] = [];
		dataArray['removable_sets'] = [];

		var curOperation = "";
		if(fv_connection_or_paste_type == "3")
			curOperation = "connect";
		else
			curOperation = "paste";
		dataArray['operation'] = curOperation;
		dataArray['connection_type'] = 'normal';
		// This field is not there in the hidden input fields of the table rows
		dataArray['connection_or_paste_type'] = fv_connection_or_paste_type; // 1 = Normal connection or paste, 2 = Swap connection or paste, 3 = Connect edit, 4 = Paste edit

		dataArray['initial_source_year'] = $("#initial_source_year_"+uniqueId).val();
		dataArray['comparing_source_year'] = $("#comparing_source_year_"+uniqueId).val();

		var sub_connetions_array = [];
		var updateable_symptom_ids = [];
		var removable_sets = [];


		var addedFormFields = "";
		if(dataArray['individual_comparison_language'] != ""){
			addedFormFields = "&fv_comparison_language=" + dataArray['individual_comparison_language'];
		}

		var data = $(this).serialize() + addedFormFields;
		$.ajax({
			type: 'POST',
			url: 'add-symptom-final-version.php',
			data: {
				form: data
			},
			dataType: "json",
			success: function( response ) {
				console.log(response);
				if(response.status == "success"){
					var fieldPrefix = (typeof(dataArray['active_symptom_type']) != "undefined" && dataArray['active_symptom_type'] !== null && dataArray['active_symptom_type'] != "") ? dataArray['active_symptom_type'] : "";
					if(fieldPrefix != ""){
						var resultData = null;
						try {
							resultData = JSON.parse(response.result_data); 
						} catch (e) {
							resultData = response.result_data;
						}

						var initial_source_symptom_de = (typeof(resultData.initial_source_symptom_de) != "undefined" && resultData.initial_source_symptom_de != "" && resultData.initial_source_symptom_de !== null) ? resultData.initial_source_symptom_de : "";
						var initial_source_symptom_en = (typeof(resultData.initial_source_symptom_en) != "undefined" && resultData.initial_source_symptom_en != "" && resultData.initial_source_symptom_en !== null) ? resultData.initial_source_symptom_en : "";
						var initial_source_symptom_highlighted_de = (typeof(resultData.initial_source_symptom_highlighted_de) != "undefined" && resultData.initial_source_symptom_highlighted_de != "" && resultData.initial_source_symptom_highlighted_de !== null) ? resultData.initial_source_symptom_highlighted_de : "";
						var initial_source_symptom_highlighted_en = (typeof(resultData.initial_source_symptom_highlighted_en) != "undefined" && resultData.initial_source_symptom_highlighted_en != "" && resultData.initial_source_symptom_highlighted_en !== null) ? resultData.initial_source_symptom_highlighted_en : "";
						var initial_source_symptom_before_conversion_de = (typeof(resultData.initial_source_symptom_before_conversion_de) != "undefined" && resultData.initial_source_symptom_before_conversion_de != "" && resultData.initial_source_symptom_before_conversion_de !== null) ? resultData.initial_source_symptom_before_conversion_de : "";
						var initial_source_symptom_before_conversion_en = (typeof(resultData.initial_source_symptom_before_conversion_en) != "undefined" && resultData.initial_source_symptom_before_conversion_en != "" && resultData.initial_source_symptom_before_conversion_en !== null) ? resultData.initial_source_symptom_before_conversion_en : "";
						var initial_source_symptom_before_conversion_highlighted_de = (typeof(resultData.initial_source_symptom_before_conversion_highlighted_de) != "undefined" && resultData.initial_source_symptom_before_conversion_highlighted_de != "" && resultData.initial_source_symptom_before_conversion_highlighted_de !== null) ? resultData.initial_source_symptom_before_conversion_highlighted_de : "";
						var initial_source_symptom_before_conversion_highlighted_en = (typeof(resultData.initial_source_symptom_before_conversion_highlighted_en) != "undefined" && resultData.initial_source_symptom_before_conversion_highlighted_en != "" && resultData.initial_source_symptom_before_conversion_highlighted_en !== null) ? resultData.initial_source_symptom_before_conversion_highlighted_en : "";
						var comparing_source_symptom_de = (typeof(resultData.comparing_source_symptom_de) != "undefined" && resultData.comparing_source_symptom_de != "" && resultData.comparing_source_symptom_de !== null) ? resultData.comparing_source_symptom_de : "";
						var comparing_source_symptom_en = (typeof(resultData.comparing_source_symptom_en) != "undefined" && resultData.comparing_source_symptom_en != "" && resultData.comparing_source_symptom_en !== null) ? resultData.comparing_source_symptom_en : "";
						var comparing_source_symptom_highlighted_de = (typeof(resultData.comparing_source_symptom_highlighted_de) != "undefined" && resultData.comparing_source_symptom_highlighted_de != "" && resultData.comparing_source_symptom_highlighted_de !== null) ? resultData.comparing_source_symptom_highlighted_de : "";
						var comparing_source_symptom_highlighted_en = (typeof(resultData.comparing_source_symptom_highlighted_en) != "undefined" && resultData.comparing_source_symptom_highlighted_en != "" && resultData.comparing_source_symptom_highlighted_en !== null) ? resultData.comparing_source_symptom_highlighted_en : "";
						var comparing_source_symptom_before_conversion_de = (typeof(resultData.comparing_source_symptom_before_conversion_de) != "undefined" && resultData.comparing_source_symptom_before_conversion_de != "" && resultData.comparing_source_symptom_before_conversion_de !== null) ? resultData.comparing_source_symptom_before_conversion_de : "";
						var comparing_source_symptom_before_conversion_en = (typeof(resultData.comparing_source_symptom_before_conversion_en) != "undefined" && resultData.comparing_source_symptom_before_conversion_en != "" && resultData.comparing_source_symptom_before_conversion_en !== null) ? resultData.comparing_source_symptom_before_conversion_en : "";
						var comparing_source_symptom_before_conversion_highlighted_de = (typeof(resultData.comparing_source_symptom_before_conversion_highlighted_de) != "undefined" && resultData.comparing_source_symptom_before_conversion_highlighted_de != "" && resultData.comparing_source_symptom_before_conversion_highlighted_de !== null) ? resultData.comparing_source_symptom_before_conversion_highlighted_de : "";
						var comparing_source_symptom_before_conversion_highlighted_en = (typeof(resultData.comparing_source_symptom_before_conversion_highlighted_en) != "undefined" && resultData.comparing_source_symptom_before_conversion_highlighted_en != "" && resultData.comparing_source_symptom_before_conversion_highlighted_en !== null) ? resultData.comparing_source_symptom_before_conversion_highlighted_en : "";
						var percentage = (typeof(resultData.percentage) != "undefined" && resultData.percentage != "" && resultData.percentage !== null) ? resultData.percentage : "";

						dataArray['initial_source_symptom_de'] = initial_source_symptom_de;
						dataArray['initial_source_symptom_en'] = initial_source_symptom_en;
						dataArray['initial_source_symptom_highlighted_de'] = initial_source_symptom_highlighted_de;
						dataArray['initial_source_symptom_highlighted_en'] = initial_source_symptom_highlighted_en;
						dataArray['initial_source_symptom_before_conversion_de'] = initial_source_symptom_before_conversion_de;
						dataArray['initial_source_symptom_before_conversion_en'] = initial_source_symptom_before_conversion_en;
						dataArray['initial_source_symptom_before_conversion_highlighted_de'] = initial_source_symptom_before_conversion_highlighted_de;
						dataArray['initial_source_symptom_before_conversion_highlighted_en'] = initial_source_symptom_before_conversion_highlighted_en;
						
						dataArray['comparing_source_symptom_de'] = comparing_source_symptom_de;
						dataArray['comparing_source_symptom_en'] = comparing_source_symptom_en;
						dataArray['comparing_source_symptom_highlighted_de'] = comparing_source_symptom_highlighted_de;
						dataArray['comparing_source_symptom_highlighted_en'] = comparing_source_symptom_highlighted_en;
						dataArray['comparing_source_symptom_before_conversion_de'] = comparing_source_symptom_before_conversion_de;
						dataArray['comparing_source_symptom_before_conversion_en'] = comparing_source_symptom_before_conversion_en;
						dataArray['comparing_source_symptom_before_conversion_highlighted_de'] = comparing_source_symptom_before_conversion_highlighted_de;
						dataArray['comparing_source_symptom_before_conversion_highlighted_en'] = comparing_source_symptom_before_conversion_highlighted_en;
						dataArray['matching_percentage'] = percentage;

						if(fv_connection_or_paste_type == "3")
							symptomConnecting(dataArray, sub_connetions_array, updateable_symptom_ids, removable_sets);
						else if(fv_connection_or_paste_type == "4")
							symptomPaste(dataArray, sub_connetions_array, updateable_symptom_ids, removable_sets);
						$th.removeClass('processing');
						$("#connectEditModal").modal('hide'); 
						
					} else {
						$th.removeClass('processing');
						$('.common-error-text').html("Page reload is required, please re-load the page.");
					}	
				}else{
					$th.removeClass('processing');
					$('.common-error-text').html("Something went wrong!");
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}
			}
		}).fail(function (response) {
			$th.removeClass('processing');
			$('.common-error-text').html("Something went wrong!");
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
    }
});

// Show symptom informations
function showInfo(symptomId){
	$("#info_modal_loader .loading-msg").removeClass('hidden');
	$("#info_modal_loader .error-msg").html('');
	if($("#info_modal_loader").hasClass('hidden'))
		$("#info_modal_loader").removeClass('hidden');

	$("#populated_info_data").remove();
	$("#symptomInfoModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info.php',
		data: {
			symptom_id: symptomId
		},
		dataType: "json",
		success: function( response ) {
			if(response.status == "success"){
				var resultData = null;
				try {
					resultData = JSON.parse(response.result_data); 
				} catch (e) {
					resultData = response.result_data;
				}
				if(!$("#info_modal_loader").hasClass('hidden'))
					$("#info_modal_loader").addClass('hidden');

				var Beschreibung_de = (resultData.Beschreibung_de != "" && resultData.Beschreibung_de != null) ? resultData.Beschreibung_de : "-";
				var Beschreibung_en = (resultData.Beschreibung_en != "" && resultData.Beschreibung_en != null) ? resultData.Beschreibung_en : "-";
				var BeschreibungOriginal_book_format_de = (resultData.BeschreibungOriginal_book_format_de != "" && resultData.BeschreibungOriginal_book_format_de != null) ? resultData.BeschreibungOriginal_book_format_de : "-";
				var BeschreibungOriginal_book_format_en = (resultData.BeschreibungOriginal_book_format_en != "" && resultData.BeschreibungOriginal_book_format_en != null) ? resultData.BeschreibungOriginal_book_format_en : "-";
				var BeschreibungOriginal_de = (resultData.BeschreibungOriginal_de != "" && resultData.BeschreibungOriginal_de != null) ? resultData.BeschreibungOriginal_de : "-";
				var BeschreibungOriginal_en = (resultData.BeschreibungOriginal_en != "" && resultData.BeschreibungOriginal_en != null) ? resultData.BeschreibungOriginal_en : "-";
				var BeschreibungOriginal_with_grading_de = (resultData.BeschreibungOriginal_with_grading_de != "" && resultData.BeschreibungOriginal_with_grading_de != null) ? resultData.BeschreibungOriginal_with_grading_de : "-";
				var BeschreibungOriginal_with_grading_en = (resultData.BeschreibungOriginal_with_grading_en != "" && resultData.BeschreibungOriginal_with_grading_en != null) ? resultData.BeschreibungOriginal_with_grading_en : "-";
				var searchable_text_with_grading_de = (resultData.searchable_text_with_grading_de != "" && resultData.searchable_text_with_grading_de != null) ? resultData.searchable_text_with_grading_de : "-";
				var searchable_text_with_grading_en = (resultData.searchable_text_with_grading_en != "" && resultData.searchable_text_with_grading_en != null) ? resultData.searchable_text_with_grading_en : "-";
				var Fussnote = (resultData.Fussnote != "" && resultData.Fussnote != null) ? resultData.Fussnote : "-";
				var Verweiss = (resultData.Verweiss != "" && resultData.Verweiss != null) ? resultData.Verweiss : "-";
				var Kommentar = (resultData.Kommentar != "" && resultData.Kommentar != null) ? resultData.Kommentar : "-";
				var Remedy = (resultData.Remedy != "" && resultData.Remedy != null) ? resultData.Remedy : "-";
				var EntnommenAus = (resultData.EntnommenAus != "" && resultData.EntnommenAus != null) ? resultData.EntnommenAus : "-";
				var Pruefer = (resultData.Pruefer != "" && resultData.Pruefer != null) ? resultData.Pruefer : "-";
				var symptom_of_different_remedy = (resultData.symptom_of_different_remedy != "" && resultData.symptom_of_different_remedy != null) ? resultData.symptom_of_different_remedy : "-";
				var BereichID = (resultData.BereichID != "" && resultData.BereichID != null) ? resultData.BereichID : "-";
				var Unklarheiten = (resultData.Unklarheiten != "" && resultData.Unklarheiten != null) ? resultData.Unklarheiten : "-";
				// Source Data
				var titel = (resultData.titel != "" && resultData.titel != null) ? resultData.titel : "-";
				var code = (resultData.code != "" && resultData.code != null) ? resultData.code : "-";
				var autor_or_herausgeber = (resultData.autor_or_herausgeber != "" && resultData.autor_or_herausgeber != null) ? resultData.autor_or_herausgeber : "-";
				var jahr = (resultData.jahr != "" && resultData.jahr != null) ? resultData.jahr : "-";
				var band = (resultData.band != "" && resultData.band != null) ? resultData.band : "-";
				var auflage = (resultData.auflage != "" && resultData.auflage != null) ? resultData.auflage : "-";

				var is_final_version_available = (resultData.is_final_version_available != "" && resultData.is_final_version_available != null) ? resultData.is_final_version_available : 0;
				var fv_con_initial_symptom_de = (resultData.fv_con_initial_symptom_de != "" && resultData.fv_con_initial_symptom_de != null) ? resultData.fv_con_initial_symptom_de : "-";
				var fv_con_initial_symptom_en = (resultData.fv_con_initial_symptom_en != "" && resultData.fv_con_initial_symptom_en != null) ? resultData.fv_con_initial_symptom_en : "-";
				var fv_con_comparative_symptom_de = (resultData.fv_con_comparative_symptom_de != "" && resultData.fv_con_comparative_symptom_de != null) ? resultData.fv_con_comparative_symptom_de : "-";
				var fv_con_comparative_symptom_en = (resultData.fv_con_comparative_symptom_en != "" && resultData.fv_con_comparative_symptom_en != null) ? resultData.fv_con_comparative_symptom_en : "-";
				var fv_symptom_de = (resultData.fv_symptom_de != "" && resultData.fv_symptom_de != null) ? resultData.fv_symptom_de : "-";
				var fv_symptom_en = (resultData.fv_symptom_en != "" && resultData.fv_symptom_en != null) ? resultData.fv_symptom_en : "-";
				
				var fv_con_initial_source_code = (resultData.fv_con_initial_source_code != "" && resultData.fv_con_initial_source_code != null) ? resultData.fv_con_initial_source_code : "-";
				var fv_con_comparative_source_code = (resultData.fv_con_comparative_source_code != "" && resultData.fv_con_comparative_source_code != null) ? resultData.fv_con_comparative_source_code : "-";

				var symptom_number = (resultData.symptom_number != "" && resultData.symptom_number != null) ? resultData.symptom_number : "-";
				var symptom_page = (resultData.symptom_page != "" && resultData.symptom_page != null) ? resultData.symptom_page : "-";
				var html = '';
				html += '<div id="populated_info_data">';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><h4>Symptominformation</h4></div>';
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<!-- <div class="col-sm-4"><p><b>Imported symptom</b></p></div>';
				html += '		<div class="col-sm-8"><p> '+Beschreibung_de+'</p></div> -->';
				html += '		<div class="col-sm-12"><p><b>Originalsymptom</b></p></div>';
				html += '		<div class="col-sm-4"><p>Deutsch (de)</p></div>';
				html += '		<div class="col-sm-8"><p>'+BeschreibungOriginal_book_format_de+'</p></div>';
				html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
				html += '		<div class="col-sm-8"><p>'+BeschreibungOriginal_book_format_en+'</p></div>';
				html += '		<div class="col-sm-12"><p><b>Konvertiertes Symptom</b></p></div>';
				html += '		<div class="col-sm-4"><p>Deutsch (de)</p></div>';
				html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_de+'</p></div>';
				html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
				html += '		<div class="col-sm-8"><p>'+searchable_text_with_grading_en+'</p></div>';

				if(is_final_version_available != 0){
					html += '		<div class="col-sm-4"><p><b>Final version Symptom</b></p></div>';
					html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6"><p><u>Deutsch (de)</u></p></div><div class="col-sm-6"><p><u>Englisch (en)</u></p></div></div></div>';
					html += '		<div class="col-sm-4">Initial<span class="pull-right">'+fv_con_initial_source_code+'</span></div>';
					html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_con_initial_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_con_initial_symptom_en+'</p></div></div></div>';
					html += '		<div class="col-sm-4">Comparative<span class="pull-right">'+fv_con_comparative_source_code+'</span></div>';
					html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_con_comparative_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_con_comparative_symptom_en+'</p></div></div></div>';
					html += '		<div class="col-sm-4">Final version</div>';
					html += '		<div class="col-sm-8"><div class="row"><div class="col-sm-6">'+fv_symptom_de+'</p></div><div class="col-sm-6"><p>'+fv_symptom_en+'</p></div></div></div>';
				}
				html += '		<div class="col-sm-4"><p><b>Arznei</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Remedy+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Fußnote</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Fussnote+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Prüfer</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Pruefer+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Literatur</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+EntnommenAus+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Kapitel</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+BereichID+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Kommentar</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Kommentar+'</p></div>';
				/*html += '		<div class="col-sm-4"><p><b>Verweiss</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Verweiss+'</p></div>';*/
				/*html += '		<div class="col-sm-4"><p><b>Symptom of different remedy</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+symptom_of_different_remedy+'</p></div>';*/
				/*html += '		<div class="col-sm-4"><p><b>Unklarheiten</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+Unklarheiten+'</p></div>';*/
				html += '	</div>';
				// html += '	<hr>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><h4>Information der Quelle</h4></div>';
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-4"><p><b>Titel</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+titel+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Kürzel/Code</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+code+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Autor/Herausgeber</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+autor_or_herausgeber+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Jahr</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+jahr+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Band</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+band+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Auflage</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+auflage+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Symptomnummer</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+symptom_number+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Seite</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+symptom_page+'</p></div>';
				html += '	</div>';
				html += '</div>';

				$("#info_container").append(html);
			}else{
				$("#info_modal_loader .loading-msg").addClass('hidden');
				$("#info_modal_loader .error-msg").html('Something went wrong!');
				console.log(response);
			}
		}
	}).fail(function (response) {
		$("#info_modal_loader .loading-msg").addClass('hidden');
		$("#info_modal_loader .error-msg").html('Something went wrong!');
		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

function showComment(symptomId, uniqueId){
	$("#comment_modal_loader .loading-msg").removeClass('hidden');
	$("#comment_modal_loader .error-msg").html('');
	if($("#comment_modal_loader").hasClass('hidden'))
		$("#comment_modal_loader").removeClass('hidden');

	$("#populated_comment_data").remove();
	$("#symptomCommentModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info.php',
		data: {
			symptom_id: symptomId
		},
		dataType: "json",
		success: function( response ) {
			if(response.status == "success"){
				var resultData = null;
				try {
					resultData = JSON.parse(response.result_data); 
				} catch (e) {
					resultData = response.result_data;
				}
				if(!$("#comment_modal_loader").hasClass('hidden'))
					$("#comment_modal_loader").addClass('hidden');

				var Kommentar = (resultData.Kommentar != "" && resultData.Kommentar != null) ? resultData.Kommentar : "";
				var html = '';
				html += '<div id="populated_comment_data">';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12">';
				html += '			<textarea name="symptom_comment_modal" id="symptom_comment_modal" placeholder="Comment" class="form-control" rows="5" cols="50">'+Kommentar+'</textarea>';
				html += '			<span class="error-text"></span>';
				html += '			<input type="hidden" name="symptom_id_comment_modal" id="symptom_id_comment_modal" value="'+symptomId+'">';
				html += '			<input type="hidden" name="comment_modal_unique_id" id="comment_modal_unique_id" value="'+uniqueId+'">';
				html += '		</div>';
				html += '	</div>';
				html += '</div>';

				$("#comment_container").append(html);
			}else{
				$("#comment_modal_loader .loading-msg").addClass('hidden');
				$("#comment_modal_loader .error-msg").html('Something went wrong!');
				console.log(response);
			}
		}
	}).fail(function (response) {
		$("#comment_modal_loader .loading-msg").addClass('hidden');
		$("#comment_modal_loader .error-msg").html('Something went wrong!');
		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

function updateComment(){
	$("#comment_modal_loader .loading-msg").removeClass('hidden');
	$("#comment_modal_loader .error-msg").html('');
	var symptom_comment_modal = $("#symptom_comment_modal").val();
	var symptom_id_comment_modal = $("#symptom_id_comment_modal").val();
	var comment_modal_unique_id = $("#comment_modal_unique_id").val();
	var error_display_msg = "";
	var error_count = 0;

	if(symptom_id_comment_modal == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(comment_modal_unique_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}

	if(error_count == 0){
		if($("#comment_modal_loader").hasClass('hidden'))
			$("#comment_modal_loader").removeClass('hidden');

		$.ajax({
			type: 'POST',
			url: 'update-symptom-info.php',
			data: {
				symptom_id: symptom_id_comment_modal,
				Kommentar: symptom_comment_modal,
				update_filed: 'Kommentar'
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}

					if(symptom_comment_modal == ""){
						$("#comment_icon_"+comment_modal_unique_id).removeClass('active');
					}else{
						if(!$("#comment_icon_"+comment_modal_unique_id).hasClass('active'))
							$("#comment_icon_"+comment_modal_unique_id).addClass('active');
					}

					if(!$("#comment_modal_loader .loading-msg").hasClass('hidden'))
						$("#comment_modal_loader .loading-msg").addClass('hidden');
					$("#comment_modal_loader .error-msg").html('Updated successfully');
					setTimeout(function() { 
						$("#comment_modal_loader .error-msg").html('');
						$("#comment_modal_loader").addClass('hidden');
					}, 2000);

				}else{
					$("#comment_modal_loader .loading-msg").addClass('hidden');
					$("#comment_modal_loader .error-msg").html('Could not save the data!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#comment_modal_loader .loading-msg").addClass('hidden');
			$("#comment_modal_loader .error-msg").html('Something went wrong!');
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
	else if(error_display_msg != ""){
		if($("#comment_modal_loader").hasClass('hidden'))
			$("#comment_modal_loader").removeClass('hidden');
		if(!$("#comment_modal_loader .loading-msg").hasClass('hidden'))
			$("#comment_modal_loader .loading-msg").addClass('hidden');
		$("#comment_modal_loader .error-msg").html(error_display_msg);
	}
}

function showFootnote(symptomId, uniqueId){
	$("#footnote_modal_loader .loading-msg").removeClass('hidden');
	$("#footnote_modal_loader .error-msg").html('');
	if($("#footnote_modal_loader").hasClass('hidden'))
		$("#footnote_modal_loader").removeClass('hidden');

	$("#populated_footnote_data").remove();
	$("#symptomFootnoteModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info.php',
		data: {
			symptom_id: symptomId
		},
		dataType: "json",
		success: function( response ) {
			if(response.status == "success"){
				var resultData = null;
				try {
					resultData = JSON.parse(response.result_data); 
				} catch (e) {
					resultData = response.result_data;
				}
				if(!$("#footnote_modal_loader").hasClass('hidden'))
					$("#footnote_modal_loader").addClass('hidden');

				var Fussnote = (resultData.Fussnote != "" && resultData.Fussnote != null) ? resultData.Fussnote : "";
				var html = '';
				html += '<div id="populated_footnote_data">';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12">';
				html += '			<textarea name="symptom_footnote_modal" id="symptom_footnote_modal" placeholder="Footnote" class="form-control" rows="5" cols="50">'+Fussnote+'</textarea>';
				html += '			<span class="error-text"></span>';
				html += '			<input type="hidden" name="symptom_id_footnote_modal" id="symptom_id_footnote_modal" value="'+symptomId+'">';
				html += '			<input type="hidden" name="footnote_modal_unique_id" id="footnote_modal_unique_id" value="'+uniqueId+'">';
				html += '		</div>';
				html += '	</div>';
				html += '</div>';

				$("#footnote_container").append(html);
			}else{
				$("#footnote_modal_loader .loading-msg").addClass('hidden');
				$("#footnote_modal_loader .error-msg").html('Something went wrong!');
				console.log(response);
			}
		}
	}).fail(function (response) {
		$("#footnote_modal_loader .loading-msg").addClass('hidden');
		$("#footnote_modal_loader .error-msg").html('Something went wrong!');
		if ( window.console && window.console.log ) {
			console.log( response );
		}
	});
}

function updateFootnote(){
	$("#footnote_modal_loader .loading-msg").removeClass('hidden');
	$("#footnote_modal_loader .error-msg").html('');
	var symptom_footnote_modal = $("#symptom_footnote_modal").val();
	var symptom_id_footnote_modal = $("#symptom_id_footnote_modal").val();
	var footnote_modal_unique_id = $("#footnote_modal_unique_id").val();
	var error_display_msg = "";
	var error_count = 0;

	if(symptom_id_footnote_modal == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(footnote_modal_unique_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}

	if(error_count == 0){
		if($("#footnote_modal_loader").hasClass('hidden'))
			$("#footnote_modal_loader").removeClass('hidden');

		$.ajax({
			type: 'POST',
			url: 'update-symptom-info.php',
			data: {
				symptom_id: symptom_id_footnote_modal,
				Fussnote: symptom_footnote_modal,
				update_filed: 'Fussnote'
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}

					if(symptom_footnote_modal == ""){
						$("#footnote_icon_"+footnote_modal_unique_id).removeClass('active');
					}else{
						if(!$("#footnote_icon_"+footnote_modal_unique_id).hasClass('active'))
							$("#footnote_icon_"+footnote_modal_unique_id).addClass('active');
					}

					if(!$("#footnote_modal_loader .loading-msg").hasClass('hidden'))
						$("#footnote_modal_loader .loading-msg").addClass('hidden');
					$("#footnote_modal_loader .error-msg").html('Updated successfully');
					setTimeout(function() { 
						$("#footnote_modal_loader .error-msg").html('');
						$("#footnote_modal_loader").addClass('hidden');
					}, 2000);

				}else{
					$("#footnote_modal_loader .loading-msg").addClass('hidden');
					$("#footnote_modal_loader .error-msg").html('Could not save the data!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#footnote_modal_loader .loading-msg").addClass('hidden');
			$("#footnote_modal_loader .error-msg").html('Something went wrong!');
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});
	}
	else if(error_display_msg != ""){
		if($("#footnote_modal_loader").hasClass('hidden'))
			$("#footnote_modal_loader").removeClass('hidden');
		if(!$("#footnote_modal_loader .loading-msg").hasClass('hidden'))
			$("#footnote_modal_loader .loading-msg").addClass('hidden');
		$("#footnote_modal_loader .error-msg").html(error_display_msg);
	}
}