$(window).bind("load", function() {
	console.log('loaded');
	$("#loader").addClass("hidden");
	$("#comparison_container").removeClass('unclickable');
	$("#search_container").removeClass('unclickable');
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

    	var saved_initial_source_id = $("#saved_initial_source_id").val(); 
		var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
    	var request = $.ajax({
		  	url: "get_arznei_quelle_select_box.php",
		  	type: "POST",
		  	data: {arznei_id : e.params.data.id, saved_initial_source_id : saved_initial_source_id, saved_comparing_source_ids : saved_comparing_source_ids},
		  	dataType: "HTML"
		});

		request.done(function(responseData) {
			// console.log(responseData);
			var splitSelectBox = responseData.split("(#$$#)");
			$("#initial_source_cnr").html( splitSelectBox[0] );
		 	$('#initial_source').select2({
				// options 
	    		searchInputPlaceholder: 'Search Quelle...'
			});

			$("#comparing_source_cnr").html( splitSelectBox[1] );
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
		var saved_comparing_source_ids = $("#saved_comparison_comparing_source_ids_comma_separated").val();
		var request = $.ajax({
		  	url: "get_comparing_quelle_select_box.php",
		  	type: "POST",
		  	data: {initial_source : e.params.data.id, arznei_id : arznei_id, saved_comparing_source_ids : saved_comparing_source_ids},
		  	dataType: "HTML"
		});

		request.done(function(responseData) {
			// console.log(responseData);
			$("#comparing_source_cnr").html( responseData );
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
