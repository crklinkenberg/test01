//Enabling loader if comparison table exist start **
var comparison_table_check = $("#comparison_table").val();
if(comparison_table_check != ""){
    $('#comparison_loader').removeClass("hidden");
}
//Enabling loader if comparison table exist end **

// For stoping recursive shell excution status checking function start **
var stopRecursiveCall = false;
$(window).bind("load", function() {
    if(scroll != ""){
        $('html, body').animate({
            scrollTop: $("#"+scroll).offset().top
        }, 1000);
    }
        
    var show_progress_msg_for_table = $("#show_progress_msg_for_table").val();
    if(show_progress_msg_for_table != ""){
        $("#comparison_table_overlay").removeClass('hidden');
        checkShellExecutionNew(show_progress_msg_for_table);
    }
    else{
        var open_ini_trans_hidden_param = $("#open_ini_trans_hidden_param").val();
        var open_com_trans_hidden_param = $("#open_com_trans_hidden_param").val();
        var open_conn_hidden_param = $("#open_conn_hidden_param").val();
        if(open_conn_hidden_param != "")
            $(".all-connections").click();
        if(open_ini_trans_hidden_param != "")
            $("#all_initial_translation").click();
        if(open_com_trans_hidden_param != "")
            $("#all_comparative_translation").click();
        $("#comparison_table_overlay").addClass('hidden');
    }
});
// For stoping recursive shell excution status checking function end **

//Select2 start **
$('#arznei_id').select2({
    // options 
    searchInputPlaceholder: 'Search Remedy...'
});
// Defining Select2
$('#initial_source').select2({
    // options 
    searchInputPlaceholder: 'Search Source...'
});
$('#comparing_sources').select2({
    // options 
    searchInputPlaceholder: 'Search Source...'
});

$('#search_sources').select2({
    searchInputPlaceholder: 'Search Source...',
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
//Select2 end **

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
                // conditionDisabled = ""; // I have to remove this line to make the disabled concept (comparing source will always be younger then initial source) active again
                if(value.quelle_type_id == 3){
                    comHtmlComparisonsInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
                } else {
                    comHtmlSingleSourcesInner += '<option data-is-symptoms-available-in-de='+value.is_symptoms_available_in_de+' data-is-symptoms-available-in-en='+value.is_symptoms_available_in_en+' data-is-synonyms-up-to-date='+value.is_synonyms_up_to_date+' '+comSelected+' value="'+value.quelle_id+'" '+conditionDisabled+'>'+value.source+'</option>';
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

$(document).ready(function(){
    //Comparison form submit start **
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
            // Checking if selected initial and comparing sources are up to date with synonym.
            var initialSourceSynonymUptoDate = $("#initial_source").find(':selected').attr("data-is-synonyms-up-to-date");
            var comparingSourceSynonymUptoDate = $("#comparing_sources option:selected").map(function() {
                return $(this).attr("data-is-synonyms-up-to-date");
            }).get();
            comparingSourceSynonymUptoDate.push(initialSourceSynonymUptoDate);
            if($.inArray("0", comparingSourceLanguage) !== -1){
                $("#global_msg_container").html('<p class="text-center">There is/are source(s) in the Initial source or in the Comparing source(s) which are not available in the language that you have selecetd to compare, Please check and try again!</p>');
                $("#globalMsgModal").modal('show');
                return false;
            }else if($.inArray("0", comparingSourceSynonymUptoDate) !== -1){
                $("#global_msg_container").html('<p class="text-center">The source(s) are not up to date with the synonyms, Please go to Materia Medica page and make the source(s) up to date with synonyms.</p>');
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
                    if(resultData.result_data.is_table_exist == 0){
                        $("#comparison_loader").addClass('hidden');
                        $("#comparison_table_overlay").removeClass('hidden');
                        stopRecursiveCall = false;
                        var dynamic_table_name = (typeof(resultData.result_data.dynamic_table_name) != "undefined" && resultData.result_data.dynamic_table_name !== null && resultData.result_data.dynamic_table_name != "") ? resultData.result_data.dynamic_table_name : "";
                        checkShellExecutionNew(dynamic_table_name);
                    }else{
                        window.location.href = baseUrl+"comparison.php";
                    }
                });

                request.fail(function(jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
                
            }
        }
    });
    //Comparison form submit end **

    //Shell execution start **
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
                            window.location.href = baseUrl+"comparison.php";
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
                    window.location.href = baseUrl+"comparison.php";
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
    //Shell execution end **

    //Toggle Icons start**
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

    if(latestIdArray != null){
        latestInitialId = latestIdArray.initial_symptom_id;
        latestComparingId = latestIdArray.comparing_symptom_id;
        $('.row'+latestInitialId).each(function(){
            if($(this).find('.toggleInitial')){
                $(this).find('.toggleInitial').trigger('click');
            }
        });	
    }    
    //Toggle Icons end**

    //Disconnection for normal connect and paste start **
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
    //Disconnection for normal connect and paste end **

    //Saving of connections start **
    $.fn.saveConnects = function(connect_type, comparativeId, initial_id_to_send, comparison_language,connected_percentage,  comparative_symptom_text, initial_symptom_text, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language)
    {
        $.ajax({
                async:false,
                type: "POST",
                url: "connection-save-script.php",
                data:{
                    connect_type : connect_type,
                    comparing_symptom_id: comparativeId,
                    initial_symptom_id: initial_id_to_send,
                    comparison_language: comparison_language,
                    matched_percentage : connected_percentage,
                    initial_quelle_id : initial_quelle_id,
                    comparative_symptom_text: comparative_symptom_text,
                    initial_symptom_text: initial_symptom_text,
                    comparing_quelle_code: comparing_quelle_code,
                    initial_quelle_code: initial_quelle_code,
                    comparing_year : comparing_year,
                    comparing_symptom_de : comparing_symptom_de,
                    comparing_symptom_en : comparing_symptom_en,
                    comparing_quelle_original_language : comparing_quelle_original_language,
                    comparing_quelle_id : comparing_quelle_id,
                    initial_year : initial_year,
                    initial_symptom_de : initial_symptom_de,
                    initial_symptom_en : initial_symptom_en,
                    initial_quelle_id : initial_quelle_id,	
                    initial_quelle_original_language : initial_quelle_original_language	
                },
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

    $.fn.saveConnectsEarlier = function(comparativeId, earlierConnectedId,initial_id_to_send, initial_symptom_text, initial_quelle_code, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language,comparison_language,type,free_flag,sub_connected_percentage)
    {
        $.ajax({
                async:false,
                type: "POST",
                url: "connection-save-script-earlier.php",
                data:{
                    comparing_symptom_id: comparativeId,
                    initial_symptom_id: initial_id_to_send,
                    earlier_symptom_id: earlierConnectedId,
                    initial_quelle_id : initial_quelle_id,
                    initial_symptom_text: initial_symptom_text,
                    initial_quelle_code: initial_quelle_code,
                    initial_year : initial_year,
                    initial_symptom_de : initial_symptom_de,
                    initial_symptom_en : initial_symptom_en,
                    initial_quelle_id : initial_quelle_id,	
                    initial_quelle_original_language : initial_quelle_original_language,
                    comparison_language : comparison_language,	
                    type : type,
                    free_flag : free_flag,	
                    sub_connected_percentage : sub_connected_percentage,
                    comparison_option : comparison_option
                },
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
    //Saving of connections end **

    //Saving a comparison function start **
    function savingComparison(param){
        $("#temp_unmark_check").val(param);
        $("#saveSubmit").submit();
    }
    //Saving a comparison function end **

    //Alerts pn saving start ***
    $(".comparison-table-save-btn").on("click", function (x){
        confirmation = confirm("Do you really want to save this comparison?");
        if(confirmation == true){
            $.ajax({
                async:false,
                type: "POST",
                url: "check-unmarked-symptoms.php",
                data: {
                    similarityRate:cutoff_percentage,
                    role:role,
                    comparisonTableName:comparison_table_name
                },
                dataType: "JSON",
                success: function(returnedData){
                    console.log(returnedData);
                    try {
                        resultData = JSON.parse(returnedData.result_data);  
                        returnType = JSON.parse(returnedData.returnType);  
                    } catch (e) {
                        resultData = returnedData.result_data;
                        returnType = returnedData.returnType;
                    }
                    console.log(returnType);
                    switch(returnType){
                        case "unmarked":{
                            if(resultData > 0){
                                confirmationUnmarked = confirm("There are "+resultData+" unmarked symptoms. Do you want to check it?");
                                if(confirmationUnmarked == true){
                                    savingComparison(1);
                                }
                            }else{
                                savingComparison(0);
                            }
                        }break;
                        case "ns_connect":{
                            if(resultData > 0){
                                confirmationUnmarked = confirm("There are "+resultData+" non secure connect symptoms. Do you want to check it?");
                                if(confirmationUnmarked == true){
                                    $('.ns-normal-btn').click();
                                }
                            }
                        }break;
                        case "ns_paste":{
                            if(resultData > 0){
                                confirmationUnmarked = confirm("There are "+resultData+" non secure paste symptoms. Do you want to check it?");
                                if(confirmationUnmarked == true){
                                    $('.ns-normal-btn-p').click();
                                }
                            }
                        }break;
                        case "ns_general":{
                            if(resultData > 0){
                                confirmationUnmarked = confirm("There are "+resultData+" non secure initial symptoms. Do you want to check it?");
                                if(confirmationUnmarked == true){
                                    $('.gen-ns-btn').click();
                                }
                            }
                        }break;
                        default:{
                            //final save
                            savingComparison(0);
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
    });
    //Alerts pn saving end ***


    //Non secure list icon operations start **
    $(".ns-normal-btn").on("click", function (x){
        if($(".ns-normal-btn").hasClass("ns-c-active")){
            window.location.href = baseUrl+"comparison.php";
        }else{
            var checkingInConnection = checkInConnectionTable('1', comparison_table_name);
            if(checkingInConnection == 0)
                alert("There are no non secure connections.");
            else
                $("#saveSubmitNs").submit();
        }
    });

    $(".gen-ns-btn").on("click", function (x){
        if($(".gen-ns-btn").hasClass("ns-g-active")){
            window.location.href = baseUrl+"comparison.php";
        }else{
            var checkingInConnection = checkInConnectionTable('3', comparison_table_name);
            if(checkingInConnection == 0)
                alert("There are no general non secure connections.");
            else
                $("#saveSubmitGenNs").submit();
        }
    });

    $(".ns-normal-btn-p").on("click", function (x){
        if($(".ns-normal-btn-p").hasClass("ns-p-active")){
            window.location.href = baseUrl+"comparison.php";
        }else{
            var checkingInConnection = checkInConnectionTable('2', comparison_table_name);
            if(checkingInConnection == 0)
                alert("There are no non secure paste connections.");
            else
                $("#saveSubmitNsP").submit();
        }
    });

    $(".ns-normal-btn-f").on("click", function (x){

        if($(".ns-normal-btn-f").hasClass("ns-f-active")){
            window.location.href = baseUrl+"comparison.php";
        }else{
            $("#saveSubmitNsF").submit();
        }
    });
    //Non secure list icon operations ends **

    //checking if the document is ready for hiding loader start **
    $(function() {
        if(comparison_table_check != "")
            $('#comparison_loader').addClass("hidden");
    });
    //checking if the document is ready for hiding loader end **

    //Translation operation start **
    $('body').on( 'click', '#all_initial_translation', function(e) {
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_ini_trans=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-initial-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if (!$(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
            
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_ini_trans=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-initial-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        } 
    });

    $('body').on( 'click', '#all_comparative_translation', function(e) {
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_com_trans=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-comparative-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if (!$(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
            
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_com_trans=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".symptom-comparative-translation-btn").each(function(){
                    var uniqueId = $(this).attr("data-unique-id");
                    if ($(this).parent().parent().parent().parent().children("div.symptom").find("div#translation_display_"+uniqueId).length){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        } 
    });
    //Translation operation end **

    //Symptom translation button in info & linkage column start ** 
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
    //Symptom translation button in info & linkage column end **

    //Show all connection checkbox start **
    $('body').on("click", ".all-connections", function (x){
        if($(this).prop("checked") == true) {
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href+"&open_conn=1";
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-plus')){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        }else{
            $.when().done(function () {
                $("#common_small_loader").removeClass('hidden');
                $(".page-link").each(function(){
                    var href = $(this).prop('href');
                    var newHref = href.replace('&open_conn=1','');
                    $(this).attr("href", newHref);
                });
            }).then(function () {
                $(".toggleInitial").each(function(){
                    if($(this).children('.fas').hasClass('fa-minus')){
                        $(this).click();
                    }
                }).promise().done( function(){ $("#common_small_loader").addClass('hidden'); } );
            });
        }
    });
    //Show all connection checkbox end **

    //Marking checkbox start **
    $('body').on("click", ".marking", function (x){
        var markedValue = "0";
        if($(this).prop("checked") == true) {
            markedValue = "1";
        }
        var initialSymptom = $(this).attr("value");
        initialSymptom = initialSymptom.replace("row","");
        console.log(initialSymptom);
        $.ajax({
            async:false,
            type: "POST",
            url: "update-marking-symptoms.php",
            data: {
                initialSymptom:initialSymptom,
                markedValue:markedValue,
                comparisonTableName:comparison_table_name
            },
            dataType: "JSON",
            success: function(returnedData){
                try {
                    resultData = JSON.parse(returnedData.result_data); 
                } catch (e) {
                    resultData = returnedData.result_data;
                }

                console.log(returnedData);
            },
            error: function(xhr, textStatus, error){
                console.log(xhr.statusText);
                console.log(textStatus);
                console.log(error);
            }
        });
    });
    //Marking checkbox end **

    //Non secure radio for supervisor confirmation start **
    $(document).on('change', 'input[type=radio][name=ns_radio]', function(){
        //var initialIdNs = $(this).parents('div#populated_nsc_note_data').find("#initial_id_nsc_note_modal").attr('value');
        var val = $(this).attr("value");
        var className = $(this).attr("class");
        if(className == "ns-confirm"){
            if(val == 0){
                $(this).attr("value","1");
                $(".ns-new").attr("value","0");
            }
        }else{
            if(val == 0){
                $(this).attr("value","1");
                $(".ns-confirm").attr("value","0");
            }
        }
    });
    //Non secure radio for supervisor confirmation end **
});