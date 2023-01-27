
//Comments On Load
$.fn.commentsOnLoadFn = function(commentsLoad)
{
	var symptom_id = commentsLoad;
	$('[class*=_' + symptom_id + ']').each(function()
	{
		$(this).find(".symptom-comment-btn").addClass("active"); 	
	});
	//adding in initial symptoms
	$('.row' + symptom_id).each(function()
	{
		$(".row"+symptom_id ).find('.symptom-comment-btn').addClass("active");
	});
}

//Footnote On Load
$.fn.footnoteOnLoadFn = function(footnoteLoad)
{
	var symptom_id = footnoteLoad;
	$('[class*=_' + symptom_id + ']').each(function()
	{
		$(this).find(".symptom-footnote-btn").addClass("active"); 	
	});
	//adding in initial symptoms
	$('.row' + symptom_id).each(function()
	{
		$(".row"+symptom_id ).find('.symptom-footnote-btn').addClass("active");
	});
}

//Translations On Load
$.fn.translationOnLoadFn = function(translationLoad)
{
	var symptom_id = translationLoad;
	//adding in initial symptoms
	$('.row' + symptom_id).each(function()
	{
		$(".row"+symptom_id ).find('.symptom-translation-btn').addClass("active");
	});
	//adding in comparative symptoms
	$('[class*=_' + symptom_id + ']').each(function()
	{
			$(this).find(".symptom-translation-btn").addClass("active"); 	
	});	
}

// //Show Comment 
$('body').on('click','.symptom-comment-btn', function(e){
	// varriable isHistory is defained in the comparison.php page
	// If the comparison is opened from history section then some functions are not allowed to perform
	if(isHistory != "")
		return false;
	var initialId = "";
	var comparingSymptomId = "";
	var symptomId = "";
	if($(this).parents(".comparing").attr("id")){
		var thisId = $(this).parents(".comparing").attr("id");
		var thisIdArray = thisId.split("_");
		//[0] represents "row". We could instead hard code the word "row"
		comparingSymptomId = (thisIdArray[1] != "") ? thisIdArray[1] : "";
		initialId = (thisIdArray[0] != "") ? thisIdArray[0].replace("row","") : "";
		symptomId = comparingSymptomId;
	}
	else if($(this).parents(".unmatched").attr("id")){
		var thisId = $(this).parents(".unmatched").attr("id");
		initialId = thisId.replace("row","");
		symptomId = initialId;
	}
	else{
		var thisId = $(this).parents(".initial").attr("id");
		initialId = thisId.replace("row","");
		symptomId = initialId;
	}
	var symptomRowId = $(this).parents(".symptom-row").attr("id");
	$("#comment_modal_loader .loading-msg").removeClass('hidden');
	$("#comment_modal_loader .error-msg").html('');
	if($("#comment_modal_loader").hasClass('hidden'))
		$("#comment_modal_loader").removeClass('hidden');
	$("#populated_comment_data").remove();
	$("#symptomCommentModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info-comparison-table.php',
		data: {
			initial_symptom_id: initialId,
			comparing_symptom_id: comparingSymptomId,
			comparison_table_name: comparison_table_name,
			comparison_language: comparison_language
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
				html += '			<input type="hidden" name="comment_modal_unique_id" id="comment_modal_unique_id" value="'+symptomId+'">';
				html += '			<input type="hidden" name="comment_modal_symptom_row_id" id="comment_modal_symptom_row_id" value="'+symptomRowId+'">';
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
});

//Update Comment
function updateComment(){
	$("#comment_modal_loader .loading-msg").removeClass('hidden');
	$("#comment_modal_loader .error-msg").html('');
	var symptom_comment_modal = $("#symptom_comment_modal").val();
	var symptom_id_comment_modal = $("#symptom_id_comment_modal").val();
	var comment_modal_unique_id = $("#comment_modal_unique_id").val();
	var comment_modal_symptom_row_id = $("#comment_modal_symptom_row_id").val();
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
	if(comment_modal_symptom_row_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(error_count == 0){
		if($("#comment_modal_loader").hasClass('hidden'))
			$("#comment_modal_loader").removeClass('hidden');
		$.ajax({
			type: 'POST',
			url: 'update-symptom-info-comparison-table.php',
			data: {
				symptom_id: symptom_id_comment_modal,
				Kommentar: symptom_comment_modal,
				comparison_table_name: comparison_table_name,
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
						//deactivating in initial symptoms
						$('.'+comment_modal_symptom_row_id).each(function()
						{
				 			$(this).find(".symptom-comment-btn").removeClass("active");	
						});
						//disabling active state from other appearing symptoms
						$('[class*=_' + symptom_id_comment_modal + ']').each(function()
						{
				 			$(this).find(".symptom-comment-btn").removeClass("active"); 	
						});
					}else{
						//activating in initial symptoms
						$('.'+comment_modal_symptom_row_id).each(function()
						{
				 			$(this).find(".symptom-comment-btn").addClass("active");	
						});

						//activating active state in other appearing comparative symptoms
						$('[class*=_' + symptom_id_comment_modal + ']').each(function()
						{
				 			$(this).find(".symptom-comment-btn").addClass("active"); 	
						});
					}
					if(!$("#comment_modal_loader .loading-msg").hasClass('hidden'))
						$("#comment_modal_loader .loading-msg").addClass('hidden');
					$("#comment_modal_loader .error-msg").html('Updated successfully');
					setTimeout(function() { 
						$("#comment_modal_loader .error-msg").html('');
						$("#comment_modal_loader").addClass('hidden');
						$('#symptomCommentModal').modal('toggle');
					}, 2000);
					console.log(response);


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
				console.log( response);
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

//Show Foot Note
$('body').on('click','.symptom-footnote-btn', function(e){
	// varriable isHistory is defained in the comparison.php page
	// If the comparison is opened from history section then some functions are not allowed to perform
	if(isHistory != "")
		return false;
	var initialId = "";
	var comparingSymptomId = "";
	var symptomId = "";
	if($(this).parents(".comparing").attr("id")){
		var thisId = $(this).parents(".comparing").attr("id");
		var thisIdArray = thisId.split("_");
		//[0] represents "row". We could instead hard code the word "row"
		comparingSymptomId = (thisIdArray[1] != "") ? thisIdArray[1] : "";
		initialId = (thisIdArray[0] != "") ? thisIdArray[0].replace("row","") : "";
		symptomId = comparingSymptomId;
	}
	else if($(this).parents(".unmatched").attr("id")){
		var thisId = $(this).parents(".unmatched").attr("id");
		initialId = thisId.replace("row","");
		symptomId = initialId;
	}
	else{
		var thisId = $(this).parents(".initial").attr("id");
		initialId = thisId.replace("row","");
		symptomId = initialId;
	}
	var symptomRowId = $(this).parents(".symptom-row").attr("id");
	$("#footnote_modal_loader .loading-msg").removeClass('hidden');
	$("#footnote_modal_loader .error-msg").html('');
	if($("#footnote_modal_loader").hasClass('hidden'))
		$("#footnote_modal_loader").removeClass('hidden');
	$("#populated_footnote_data").remove();
	$("#symptomFootnoteModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info-comparison-table.php',
		data: {
			initial_symptom_id: initialId,
			comparing_symptom_id: comparingSymptomId,
			comparison_table_name: comparison_table_name,
			comparison_language: comparison_language
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
				html += '			<input type="hidden" name="footnote_modal_unique_id" id="footnote_modal_unique_id" value="'+symptomId+'">';
				html += '			<input type="hidden" name="footnote_modal_symptom_row_id" id="footnote_modal_symptom_row_id" value="'+symptomRowId+'">';
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
});

//Update Foot Note
function updateFootnote(){
	$("#footnote_modal_loader .loading-msg").removeClass('hidden');
	$("#footnote_modal_loader .error-msg").html('');
	var symptom_footnote_modal = $("#symptom_footnote_modal").val();
	var symptom_id_footnote_modal = $("#symptom_id_footnote_modal").val();
	var footnote_modal_unique_id = $("#footnote_modal_unique_id").val();
	var footnote_modal_symptom_row_id = $("#footnote_modal_symptom_row_id").val();
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
	if(footnote_modal_symptom_row_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(error_count == 0){
		if($("#footnote_modal_loader").hasClass('hidden'))
			$("#footnote_modal_loader").removeClass('hidden');

		$.ajax({
			type: 'POST',
			url: 'update-symptom-info-comparison-table.php',
			data: {
				symptom_id: symptom_id_footnote_modal,
				Fussnote: symptom_footnote_modal,
				comparison_table_name: comparison_table_name,
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
						//deactivating in initial symptoms
						$('.'+footnote_modal_symptom_row_id).each(function()
						{
				 			$(this).find(".symptom-footnote-btn").removeClass("active");	
						});
	
						//disabling active state from other appearing symptoms
						$('[class*=_' + symptom_id_footnote_modal + ']').each(function()
						{
				 			$(this).find(".symptom-footnote-btn").removeClass("active");	 	
						});
					}else{
						//activating in initial symptoms
						$('.'+footnote_modal_symptom_row_id).each(function()
						{
				 			$(this).find(".symptom-footnote-btn").addClass("active");	
						});
						//activating active state in other appearing symptoms
						$('[class*=_' + symptom_id_footnote_modal + ']').each(function()
						{	
				 			$(this).find(".symptom-footnote-btn").addClass("active");	 	
						});
					}
					if(!$("#footnote_modal_loader .loading-msg").hasClass('hidden'))
						$("#footnote_modal_loader .loading-msg").addClass('hidden');
					$("#footnote_modal_loader .error-msg").html('Updated successfully');
					setTimeout(function() { 
						$("#footnote_modal_loader .error-msg").html('');
						$("#footnote_modal_loader").addClass('hidden');
						$('#symptomFootnoteModal').modal('toggle');
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

// Show Symptom Informations
$('body').on('click','.symptom-info-btn', function(e){
	var initialId = "";
	var comparingSymptomId = "";
	if($(this).parents(".comparing").attr("id")){
		var thisId = $(this).parents(".comparing").attr("id");
		var thisIdArray = thisId.split("_");
		//[0] represents "row". We could instead hard code the word "row"
		comparingSymptomId = (thisIdArray[1] != "") ? thisIdArray[1] : "";
		initialId = (thisIdArray[0] != "") ? thisIdArray[0].replace("row","") : "";
		// var symptomId = comparativeId;
	}
	else if($(this).parents(".unmatched").attr("id")){
		var thisId = $(this).parents(".unmatched").attr("id");
		initialId = thisId.replace("row","");
		// var symptomId = initialId;
	}
	else{
		var thisId = $(this).parents(".initial").attr("id");
		initialId = thisId.replace("row","");
		// var symptomId = initialId;
	}
	
	$("#info_modal_loader .loading-msg").removeClass('hidden');
	$("#info_modal_loader .error-msg").html('');
	if($("#info_modal_loader").hasClass('hidden'))
		$("#info_modal_loader").removeClass('hidden');

	$("#populated_info_data").remove();
	$("#symptomInfoModal").modal('show');
	$.ajax({
		type: 'POST',
		url: 'get-symptom-info-comparison-table.php',
		data: {
			initial_symptom_id: initialId,
			comparing_symptom_id: comparingSymptomId,
			comparison_table_name: comparison_table_name,
			comparison_language: comparison_language
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
				var BeschreibungFull_with_grading_de = (resultData.BeschreibungFull_with_grading_de != "" && resultData.BeschreibungFull_with_grading_de != null) ? resultData.BeschreibungFull_with_grading_de : "-";
				var BeschreibungFull_with_grading_en = (resultData.BeschreibungFull_with_grading_en != "" && resultData.BeschreibungFull_with_grading_en != null) ? resultData.BeschreibungFull_with_grading_en : "-";

				var Fussnote = (resultData.Fussnote != "" && resultData.Fussnote != null) ? resultData.Fussnote : "-";
				var Verweiss = (resultData.Verweiss != "" && resultData.Verweiss != null) ? resultData.Verweiss : "-";
				var Kommentar = (resultData.Kommentar != "" && resultData.Kommentar != null) ? resultData.Kommentar : "-";
				var Remedy = (resultData.Remedy != "" && resultData.Remedy != null) ? resultData.Remedy : "-";
				var symptom_type = (resultData.symptom_type != "" && resultData.symptom_type != null) ? resultData.symptom_type : "-";
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
				
				var synonym_word = (resultData.synonym_word != "" && resultData.synonym_word != null) ? resultData.synonym_word : "-";
				var strict_synonym = (resultData.strict_synonym != "" && resultData.strict_synonym != null) ? resultData.strict_synonym : "-";
				var strict_synonym = (resultData.strict_synonym != "" && resultData.strict_synonym != null) ? resultData.strict_synonym : "-";
				var synonym_partial_1 = (resultData.synonym_partial_1 != "" && resultData.synonym_partial_1 != null) ? resultData.synonym_partial_1 : "-";
				var synonym_partial_2 = (resultData.synonym_partial_2 != "" && resultData.synonym_partial_2 != null) ? resultData.synonym_partial_2 : "-";
				var synonym_general = (resultData.synonym_general != "" && resultData.synonym_general != null) ? resultData.synonym_general : "-";
				var synonym_minor = (resultData.synonym_minor != "" && resultData.synonym_minor != null) ? resultData.synonym_minor : "-";
				var synonym_nn = (resultData.synonym_nn != "" && resultData.synonym_nn != null) ? resultData.synonym_nn : "-";

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
				html += '		<div class="col-sm-8"><p>'+BeschreibungFull_with_grading_de+'</p></div>';
				html += '		<div class="col-sm-4"><p>Englisch (en)</p></div>';
				html += '		<div class="col-sm-8"><p>'+BeschreibungFull_with_grading_en+'</p></div>';
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
				html += '		<div class="col-sm-4"><p><b>Symptom Type</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+symptom_type+'</p></div>';
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
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><h4>Synonyms</h4></div>';
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-4"><p><b>Synonym Word</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_word+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Strict Synonym</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+strict_synonym+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Synonym Partial 1</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_partial_1+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Synonym Partial 2</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_partial_2+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Synonym General</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_general+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Synonym Minor</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_minor+'</p></div>';
				html += '		<div class="col-sm-4"><p><b>Synonym NN</b></p></div>';
				html += '		<div class="col-sm-8"><p>'+synonym_nn+'</p></div>';
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
				if ($("#info_container").children("div#populated_info_data").length){
					$("#info_container").children("div#populated_info_data").remove();
				}
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
});

//Symptom Edit Button 
$('body').on( 'click', '.symptom-edit-btn', function(e) {
	// varriable isHistory is defained in the comparison.php page
	// If the comparison is opened from history section then some functions are not allowed to perform
	if(isHistory != "")
		return false;
	var isNonSymptomEditableConnection = $(this).parents(".symptom-row").attr("data-is-non-symptom-editable-connection");
	if(isNonSymptomEditableConnection == 1){
		$("#global_msg_container").html('<p class="text-center text-danger">This symptom is not available for edit.</p>');
		$("#globalMsgModal").modal('show');
		return false;
	}
	$("#symptom_edit_container").show();
	$("#symptom_edit_settings_container").show();

	$(".symptom_edit_modal_loader .loading-msg").removeClass('hidden');
	$(".symptom_edit_modal_loader .error-msg").html('');
	if($(".symptom_edit_modal_loader").hasClass('hidden'))
		$(".symptom_edit_modal_loader").removeClass('hidden');

	$("#symptom_edit_modal_connection_id").val("");
	$("#symptom_edit_modal_symptom_id").val("");
	$("#symptom_edit_modal_original_source_id").val("");
	$(".symptom-edit-de-error").html('');
	$(".symptom-edit-en-error").html('');
	$('.symptom-edit-common-error-text').html("");
	$("#populated_connect_edit_modal_data").remove();
	$("#symptomEditModal").modal('show'); 
	var thisId = $(this).parents('div.symptom-row').attr('id');
	var thisIdArray = thisId.split("_");
	comparativeId = (typeof thisIdArray[1] !== 'undefined' && thisIdArray[1] !== null && thisIdArray[1] != "") ? thisIdArray[1].replace('row', '') : "";
	initialId = (typeof thisIdArray[0] !== 'undefined' && thisIdArray[0] !== null && thisIdArray[0] != "") ? thisIdArray[0].replace('row', '') : "";
	var quelleId = $(this).parents(".symptom-row").attr("data-quell-id");
	var connectionId = $(this).parents(".symptom-row").attr("data-connection-id");
	var uniqueId = thisId;
	var active_symptom_id = (comparativeId != "") ? comparativeId : initialId;
	var source_id = quelleId;
	var comparison_table = $("#comparison_table").val();
	if(active_symptom_id != "" && source_id != "" && comparison_table != ""){
		var sendingData = {};
		if(typeof connectionId !== 'undefined' && connectionId !== null && connectionId != "") {
			sendingData = {
				symptom_id: active_symptom_id,
				initial_id: initialId,
				source_id: source_id,
				comparison_table: comparison_table,
				connection_id: connectionId,
				is_connected_symptom: 1
			};
		} else {
			sendingData = {
				symptom_id: active_symptom_id,
				initial_id: initialId,
				source_id: source_id,
				comparison_table: comparison_table
			};
		}
		$.ajax({
			type: 'POST',
			url: 'fetch-symptom-edit-info-comparison-table.php',
			data: sendingData,
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

						BeschreibungOriginal_de = (typeof(resultData.BeschreibungOriginal_de) != "undefined" && resultData.BeschreibungOriginal_de !== null && resultData.BeschreibungOriginal_de != "") ? resultData.BeschreibungOriginal_de : "";
						BeschreibungOriginal_en = (typeof(resultData.BeschreibungOriginal_en) != "undefined" && resultData.BeschreibungOriginal_en !== null && resultData.BeschreibungOriginal_en != "") ? resultData.BeschreibungOriginal_en : "";
						$("#symptom_edit_de").val(BeschreibungOriginal_de);
						$("#symptom_edit_en").val(BeschreibungOriginal_en);

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
						(typeof resultData.pi_sign !== 'undefined' && resultData.pi_sign !== null && resultData.pi_sign != "") ? $("#pi_sign option[value='"+resultData.pi_sign+"']").prop('selected', true) : $("#pi_sign option[value='']").prop('selected', true);
						(typeof resultData.one_bar !== 'undefined' && resultData.one_bar !== null && resultData.one_bar != "") ? $("#one_bar option[value='"+resultData.one_bar+"']").prop('selected', true) : $("#one_bar option[value='']").prop('selected', true);
						(typeof resultData.two_bar !== 'undefined' && resultData.two_bar !== null && resultData.two_bar != "") ? $("#two_bar option[value='"+resultData.two_bar+"']").prop('selected', true) : $("#two_bar option[value='']").prop('selected', true);
						(typeof resultData.three_bar !== 'undefined' && resultData.three_bar !== null && resultData.three_bar != "") ? $("#three_bar option[value='"+resultData.three_bar+"']").prop('selected', true) : $("#three_bar option[value='']").prop('selected', true);
						(typeof resultData.three_and_half_bar !== 'undefined' && resultData.three_and_half_bar !== null && resultData.three_and_half_bar != "") ? $("#three_and_half_bar option[value='"+resultData.three_and_half_bar+"']").prop('selected', true) : $("#three_bar option[value='']").prop('selected', true);
						(typeof resultData.four_bar !== 'undefined' && resultData.four_bar !== null && resultData.four_bar != "") ? $("#four_bar option[value='"+resultData.four_bar+"']").prop('selected', true) : $("#four_bar option[value='']").prop('selected', true);
						(typeof resultData.four_and_half_bar !== 'undefined' && resultData.four_and_half_bar !== null && resultData.four_and_half_bar != "") ? $("#four_and_half_bar option[value='"+resultData.four_and_half_bar+"']").prop('selected', true) : $("#four_and_half_bar option[value='']").prop('selected', true);
						(typeof resultData.five_bar !== 'undefined' && resultData.five_bar !== null && resultData.five_bar != "") ? $("#five_bar option[value='"+resultData.five_bar+"']").prop('selected', true) : $("#five_bar option[value='']").prop('selected', true);
						// Grading section end

						if(typeof connectionId !== 'undefined' && connectionId !== null && connectionId != "")
							$("#symptom_edit_modal_connection_id").val(connectionId);
						$("#symptom_edit_modal_symptom_id").val(active_symptom_id);
						$("#symptom_edit_modal_original_source_id").val(source_id);
						$("#symptom_edit_modal_comparison_table").val(comparison_table);

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
		});
	}else{
		$(".symptom_edit_modal_loader .loading-msg").addClass('hidden');
		$(".symptom_edit_modal_loader .error-msg").html('Something went wrong!');
	}
});

$('body').on( 'click', '.symptom-edit-modal-submit-btn', function(e) {
	$(".symptom_edit_modal_loader .loading-msg").removeClass('hidden');
	$(".symptom_edit_modal_loader .error-msg").html('');
	if($(".symptom_edit_modal_loader").hasClass('hidden'))
		$(".symptom_edit_modal_loader").removeClass('hidden');
	$("#symptom_edit_container").hide();
	$("#symptom_edit_settings_container").hide();
	var symptom_edit_modal_original_source_id = $("#symptom_edit_modal_original_source_id").val();
	var symptom_edit_modal_symptom_id = $("#symptom_edit_modal_symptom_id").val();
	var symptom_edit_modal_comparison_table = $("#symptom_edit_modal_comparison_table").val();
	var symptom_edit_de = $("#symptom_edit_de").val();
    var symptom_edit_en = $("#symptom_edit_en").val();
	var error_count = 0;
	$(".symptom-edit-de-error").html("");
    $(".symptom-edit-en-error").html("");
    $('.symptom-edit-common-error-text').html("");
	if(symptom_edit_de == "" && symptom_edit_en == ""){
		$(".symptom-edit-de-error").html("This field is empty");
		$(".symptom-edit-en-error").html("This field is empty");
		error_count++;
	}else{
		$(".symptom-edit-de-error").html('');
		$(".symptom-edit-en-error").html('');
	}
	if(symptom_edit_modal_original_source_id == "")
		error_count++;
	if(symptom_edit_modal_symptom_id == "")
		error_count++;
	if(symptom_edit_modal_comparison_table == "")
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
			url: 'update-symptom-and-settings-comparison-table.php',
			data: {
				form: data
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){
					location.reload();
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

//Master function for comments and footnote
function symptomIcons(incoming,symptomId)
{
	var incoming = incoming;
	switch(incoming)
	{
		case "updateComment":
			updateComment();
			break;
		case "updateFootnote":
			updateFootnote();
			break;
	} 
}

// Symptom search icon functions
$('body').on('click','.symptom-search-btn', function(e){
	$("#searchResultTbody").html('<div class="symptom-row text-center"><div class="full-length-row">No records found.</div></div>');
	$("#footnote_modal_loader .loading-msg").removeClass('hidden');
	$("#footnote_modal_loader .error-msg").html('');
	if($("#footnote_modal_loader").hasClass('hidden'))
		$("#footnote_modal_loader").removeClass('hidden');
	$("#symptomSearchModal").modal('show');
	var parentRowId = $(this).parents('div.symptom-row').attr('id');
	var initialSymptom = $("#"+parentRowId ).find('.symptom').html();
	var initialSymptomInText = $("#"+parentRowId ).find('.symptom').text();
	if(initialSymptom != ""){
		if(!$("#search_modal_loader .loading-msg").hasClass('hidden'))
			$("#search_modal_loader .loading-msg").addClass('hidden');
		$("#search_modal_loader .error-msg").html('');

		$("#searchInitialSymptom").html(initialSymptom);
		$("#searching_symptom").val(initialSymptomInText);
	}else{
		$("#search_modal_loader .loading-msg").addClass('hidden');
		$("#search_modal_loader .error-msg").html('Could not find the symptom.');
	}
});

$('body').on( 'submit', '#symptom_search_modal_form', function(e) {
	e.preventDefault();
	// showing loader
	$("#searchResultTbody").html('<div class="symptom-row text-center"><div class="full-length-row">Loading... <img src="assets/img/loader.gif" alt="Loader"></div></div>');
	var searching_symptom = $("#searching_symptom").val();
	var comparing_source_ids_for_search = $("#comparing_source_ids_for_search").val();
	var error_count = 0;
	if(searching_symptom == ""){
		$("#searching_symptom").next().html('Please enter search keyword');
		$("#searching_symptom").next().addClass('text-danger');
		error_count++;
	}else{
		$("#searching_symptom").next().html('');
		$("#searching_symptom").next().removeClass('text-danger');
	}
	if(comparing_source_ids_for_search == ""){
		if(!$("#search_modal_loader .loading-msg").hasClass('hidden'))
			$("#search_modal_loader .loading-msg").addClass('hidden');
		$("#search_modal_loader .error-msg").html('Could not find the comparing sources.');
		$("#search_modal_loader .error-msg").addClass('text-danger');
		error_count++;
	}else{
		if(!$("#search_modal_loader .loading-msg").hasClass('hidden'))
			$("#search_modal_loader .loading-msg").addClass('hidden');
		$("#search_modal_loader .error-msg").removeClass('text-danger');
		$("#search_modal_loader .error-msg").html('');
	}

	if(error_count == 0){
		// console.log(searching_symptom);
		var data = $(this).serialize();

		// var nummerOfRecordFetch = number_of_records;
		$.ajax({
			type: 'POST',
			url: 'get-symptom-search-result.php',
			data: {
				form: data
			},
			dataType: "json",
			success: function( response ) {
				if(typeof(response.result_data) != "undefined" && response.result_data !== null) {
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
					} catch (e) {
						resultData = response.result_data;
					}
					var html = "";
					$.each(resultData, function( key, value ) {
				  		var displaySymptomString = (typeof(value.symptom_highlighted) != "undefined" && value.symptom_highlighted !== null && value.symptom_highlighted != "") ? value.symptom_highlighted : "";
				  		html += '<div class="symptom-row">';
				  		html += '	<div style="width: 12%;" class="source-code">'+value.source_code+'</div>';
				  		html += '	<div style="width: 76%;" class="symptom">'+displaySymptomString+'</div>';
				  		html += '	<div style="width: 12%;" class="percentage">'+value.percentage+'%</div>';
				  		html += '</div>';
					});
					if(html != ""){
						$("#searchResultTbody").html(html);
				  	}else{
				  		$("#searchResultTbody").html('<div class="symptom-row text-center"><div class="full-length-row">No records found.</div></div>');
				  	}
				}
			}
		}).fail(function (response) {
			$("#searchResultTbody").html('<div class="symptom-row text-center"><div class="full-length-row">Operation failed.</div></div>');
			if ( window.console && window.console.log ) {
				console.log( response );
			}
		});

	}else{
		$("#searchResultTbody").html('<div class="symptom-row text-center"><div class="full-length-row">Required data not found.</div></div>');
		return false;
	}
}); 

// Making translation display modal movable START
(function ($) {
    $.fn.drags = function (opt) {
        opt = $.extend({ handle: "", cursor: "move" }, opt);
        var $el = null;
        if (opt.handle === "") {
            $el = this;
        } else {
            $el = this.find(opt.handle);
        }
        return $el.css('cursor', opt.cursor).on("mousedown", function (e) {
            var $drag = null;
            if (opt.handle === "") {
                $drag = $(this).parents('.modal-dialog').addClass('draggable');
            } else {
                $drag = $(this).parents('.modal-dialog').addClass('active-handle').parent().addClass('draggable');
            }
            var z_idx = $drag.css('z-index'),
                drg_h = $drag.outerHeight(),
                drg_w = $drag.outerWidth(),
                pos_y = $drag.offset().top + drg_h - e.pageY,
                pos_x = $drag.offset().left + drg_w - e.pageX;
            $drag.css('z-index', 1000).parents().on("mousemove", function (e) {
                $('.draggable').offset({
                    top: e.pageY + pos_y - drg_h,
                    left: e.pageX + pos_x - drg_w
                }).on("mouseup", function () {
                    $(this).removeClass('draggable').css('z-index', z_idx);
                });
            });
            e.preventDefault(); // disable selection
        }).on("mouseup", function () {
            if (opt.handle === "") {
                $(this).removeClass('draggable');
            } else {
                $(this).removeClass('active-handle').parent().removeClass('draggable');
            }
        });
    }
})(jQuery);

$(document).ready(function () {
  	$('#translationModal').on('shown.bs.modal', function () {
        $(this).find('.translation-popup-header').drags();
    });
});
// Making translation display modal movable END

//General Non Secure On Load
$.fn.genNsOnLoadFn = function(genNsLoad)
{
	var symptom_id = genNsLoad;
	//adding in initial symptoms
	$('.row' + symptom_id).each(function()
	{
		$(".row"+symptom_id ).find('.gen-ns').addClass("active");
	});
}