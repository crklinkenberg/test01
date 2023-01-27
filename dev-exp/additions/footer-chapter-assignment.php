<script src="assets/js/common.js"></script>
<script type="text/javascript">

	//arznei custom search starts
	$('#arznei_id').select2({
		// options 
		searchInputPlaceholder: 'Search Arznei...'
	});
    // Fetching Quelle/Sources of the arznei
    $('#arznei_id').on('select2:select', function (e) {
        if(typeof(e.params.data.id) != "undefined" && e.params.data.id !== null){
            console.log(e.params.data.id);
            return false;
           
            // $("#initial_source").prop("disabled", true);
            // $("#comparing_sources").prop("disabled", true);
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
                        htmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                    } else {
                        htmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+selected+' value="'+value.quelle_id+'">'+value.source+'</option>';
                        comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'">'+value.source+'</option>';
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

</script>
</body>
</html>