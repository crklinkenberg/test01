//Non secure connect function update
function addnscNote(){
	var nsc_note = $("#symptom_nsc_note_modal").val();
	var symptom_id_nsc_note_modal = $("#symptom_id_nsc_note_modal").val();
	var initial_id_nsc_note_modal = $("#initial_id_nsc_note_modal").val();
	var nsc_note_modal_unique_id = $("#nsc_note_modal_unique_id").val();
	var nsc_note_modal_symptom_row_id = $("#nsc_note_modal_symptom_row_id").val();
	var checkingNS = $("#checkingNS").attr("data-mark");
	var ns_confirm = 0;
	var ns_new = 0;
	var ns_value = '1';
	if(checkingNS == 1){
		ns_confirm = $(".ns-confirm").val();
		ns_new = $(".ns-new").val();
	}
	if(ns_new == 1){
		ns_value = '0';
		if($(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnect")){
			$(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnect").click();
		}
		if($(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectCE")){
			$(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectCE").click();
		}
		$("#nscNoteModal").modal('hide');	
	}
	var error_display_msg = "";
	var error_count = 0;
	if(symptom_id_nsc_note_modal == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(nsc_note_modal_unique_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(nsc_note_modal_symptom_row_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(error_count == 0){
		if($("#nsc_note_modal_loader").hasClass('hidden'))
			$("#nsc_note_modal_loader").removeClass('hidden');
		$.ajax({
			type: 'POST',
			url: 'update-symptom-info-comparison-table.php',
			data: {
				symptom_id: symptom_id_nsc_note_modal,
				initial_id: initial_id_nsc_note_modal,
				Nsc_note: nsc_note,
				ns_value: ns_value,
				ns_confirm: ns_confirm,
				ns_new: ns_new,
				comparison_table_name: comparison_table_name,
				update_filed: 'Nsc_note'
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){ 
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
						confirmation = JSON.parse(response.confirmation); 
					} catch (e) {
						resultData = response.result_data;
						confirmation = response.confirmation;
					}
					if(confirmation == 1){
						location.reload();
					}else{
						if(!$("."+nsc_note_modal_symptom_row_id ).find('.symptom-soft-connect-btn').hasClass('active'))
							$("."+nsc_note_modal_symptom_row_id ).find('.symptom-soft-connect-btn').addClass('active');

						if(!$(".row"+initial_id_nsc_note_modal+"_"+ symptom_id_nsc_note_modal ).find('.symptom-soft-connect-btn').hasClass('active'))
							$(".row"+initial_id_nsc_note_modal+"_"+ symptom_id_nsc_note_modal ).find('.symptom-soft-connect-btn').addClass('active');

						$('[class*=_' + symptom_id_nsc_note_modal + ']:not(".'+symptom_id_nsc_note_modal+'")').each(function()
						{
							if($(this).next().hasClass("initialsConnectedCD"))
				 			{
				 				$(this).next(".initialsConnectedCD").children(".row"+initial_id_nsc_note_modal).find(".symptom-soft-connect-btn").addClass("active");
				 			}	
				 			if($(this).next().hasClass("initialsConnectedCE"))
				 			{
				 				$(this).next(".initialsConnectedCE").children(".row"+initial_id_nsc_note_modal).find(".symptom-soft-connect-btn").addClass("active");
				 			}		
						  	
						});
						if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
							$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
						$("#nsc_note_modal_loader .error-msg").html('Updated successfully');
						setTimeout(function() { 
							$("#nsc_note_modal_loader .error-msg").html('');
							$("#nsc_note_modal_loader").addClass('hidden');
							console.log("yes");
						}, 1000);
						$("#nscNoteModal").modal('hide');
					}
				}else{
					$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader .error-msg").html('Could not save the data!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
			$("#nsc_note_modal_loader .error-msg").html('Something went wrong!');
			if ( window.console && window.console.log ) {
				//console.log( response);
			}
		});
	}else if(error_display_msg != ""){
		if($("#nsc_note_modal_loader").hasClass('hidden'))
			$("#nsc_note_modal_loader").removeClass('hidden');
		if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
			$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
		$("#nsc_note_modal_loader .error-msg").html(error_display_msg);
	}
}
// NonSecureCconnect Or softconnect
$(document).on('click', '.symptom-soft-connect-btn', function(){
	if(!$(this).hasClass('ns-disabled')){
		if($(this).parents(".comparing").attr("id")){
			var thisId = $(this).parents(".comparing").attr("id");
			var thisIdArray = thisId.split("_");
			//[0] represents "row". We could instead hard code the word "row"
			var comparativeId = thisIdArray[1];
			var initialId = thisIdArray[0];
			var initialId = thisIdArray[0].replace("row","");
			var symptomId = comparativeId;
		}
		else{
			var thisId = $(this).parents(".initial").attr("id");
			var initialId = thisId.replace("row","");
			if($(this).parents(".initialsConnectedCD").prevAll(".comparing").first().attr("id"))
			{
				var symptomIdFind = $(this).parents(".initialsConnectedCD").prevAll(".comparing").first().attr("id");
			}
			if($(this).parents(".initialsConnectedCE").prevAll(".comparing").first().attr("id"))
			{
				var symptomIdFind = $(this).parents(".initialsConnectedCE").prevAll(".comparing").first().attr("id");
			}
			var thisIdArray = symptomIdFind.split("_");
			var symptomId = thisIdArray[1];
		}
		var checkingNS = $("#checkingNS").attr("data-mark");
		var symptomRowId = $(this).parents(".symptom-row").attr("id");
		$.ajax({
			type: 'POST',
			url: 'get-non-secure-info-comparison-table.php',
			data: {
				symptom_id: symptomId,
				initial_id: initialId,
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
					if(!$("#nsc_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader").addClass('hidden');
					$("#nscNoteModal").modal('show');
					$("#populated_nsc_note_data").remove();
					var Nsc_note = (resultData.ns_connect_comment != "" && resultData.ns_connect_comment != null) ? resultData.ns_connect_comment : "";
					var html = '';
					html += '<div id="populated_nsc_note_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<textarea name="symptom_nsc_note_modal" id="symptom_nsc_note_modal" placeholder="Non secure Note" class="form-control" rows="5" cols="50">'+Nsc_note+'</textarea>';
					if(checkingNS == 1){
						html += '<div class="radio"><label><input type="radio" name="ns_radio" class="ns-confirm" value="1" checked>Confirm</label></div><div class="radio"><label><input type="radio" name="ns_radio" class="ns-new" value="0">New</label></div>';
					}
					html += '			<span class="error-text"></span>';
					html += '			<input type="hidden" name="symptom_id_nsc_note_modal" id="symptom_id_nsc_note_modal" value="'+symptomId+'">';
					html += '			<input type="hidden" name="initial_id_nsc_note_modal" id="initial_id_nsc_note_modal" value="'+initialId+'">';
					html += '			<input type="hidden" name="nsc_note_modal_unique_id" id="nsc_note_modal_unique_id" value="'+symptomId+'">';
					html += '			<input type="hidden" name="nsc_note_modal_symptom_row_id" id="nsc_note_modal_symptom_row_id" value="'+symptomRowId+'">';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';
					$("#nsc_note_container").append(html);
				}else{
					$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader .error-msg").html('Something went wrong!');
				}
			}
			}).fail(function (response) {
				$("#nsc_note_modal_loader .loading-msg").addClass('hidden');
				$("#nsc_note_modal_loader .error-msg").html('Something went wrong!');
				if ( window.console && window.console.log ) {
					console.log( response);
				}
			});
	}	
});

//non secure connection on load function
$.fn.nonSecureConnectFn = function(nonSecureConnected)
{
	var params = nonSecureConnected.split("||#id-separator#||");
	var symptom_id = params[0];
	var dataIdPart = params[1];
	var restParams = dataIdPart.split("||#data-separator#||");
	var initial_symptom_id = restParams[0];
	var non_secure_connect = restParams[1];
	if($("#row"+initial_symptom_id+"_"+ symptom_id ).find('.symptom-soft-connect-btn'))
	{
		$("#row"+initial_symptom_id+"_"+ symptom_id ).find('.symptom-soft-connect-btn').addClass("active");
	}
	$('[class*=_' + symptom_id + ']').each(function()
	{
	  	if($(this).next().hasClass("initialsConnectedCD"))
		{
			$(this).next(".initialsConnectedCD").children(".row"+initial_symptom_id).find(".symptom-soft-connect-btn").addClass("active");
		}
		if($(this).next().hasClass("initialsConnectedCE"))
		{
			$(this).next(".initialsConnectedCE").children(".row"+initial_symptom_id).find(".symptom-soft-connect-btn").addClass("active");
		}
	});
}

//Non secure paste function update
function addnscNotePaste(){
	var nsc_note = $("#symptom_nsc_note_modal_paste").val();
	var symptom_id_nsc_note_modal = $("#symptom_id_nsc_note_modal_paste").val();
	var initial_id_nsc_note_modal = $("#initial_id_nsc_note_modal_paste").val();
	var nsc_note_modal_unique_id = $("#nsc_note_modal_unique_id_paste").val();
	var nsc_note_modal_symptom_row_id = $("#nsc_note_modal_symptom_row_id_paste").val();
	var checkingNS = $("#checkingNS").attr("data-mark");
	var ns_confirm = 0;
	var ns_new = 0;
	var ns_value = '1';
	if(checkingNS == 1){
		ns_confirm = $(".ns-confirm").val();
		ns_new = $(".ns-new").val();
	}
	if(ns_new == 1){
		ns_value = '0';
		if($(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectPaste")){
			$(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectPaste").click();
		}
		if($(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectPE")){
			$(".row"+initial_id_nsc_note_modal+"_"+symptom_id_nsc_note_modal).find(".disconnectPE").click();
		}
		$("#nscNoteModalPaste").modal('hide');
	}
	var error_display_msg = "";
	var error_count = 0;
	if(symptom_id_nsc_note_modal == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(nsc_note_modal_unique_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(nsc_note_modal_symptom_row_id == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}
	if(error_count == 0){
		if($("#nsc_note_modal_loader_paste").hasClass('hidden'))
			$("#nsc_note_modal_loader_paste").removeClass('hidden');
		$.ajax({
			type: 'POST',
			url: 'update-symptom-info-comparison-table.php',
			data: {
				symptom_id: symptom_id_nsc_note_modal,
				initial_id: initial_id_nsc_note_modal,
				Nsc_note_paste: nsc_note,
				ns_value: ns_value,
				ns_confirm: ns_confirm,
				ns_new: ns_new,
				comparison_table_name: comparison_table_name,
				update_filed: 'Nsc_note_paste'
			},
			dataType: "json",
			success: function( response ) {
				if(response.status == "success"){ 
					var resultData = null;
					try {
						resultData = JSON.parse(response.result_data); 
						confirmation = JSON.parse(response.confirmation); 
					} catch (e) {
						resultData = response.result_data;
						confirmation = response.confirmation;
					}
					if(confirmation == 1){
						location.reload();
					}else{
						if(!$("#"+nsc_note_modal_symptom_row_id ).find('.symptom-soft-paste-btn').hasClass('active'))
							$("#"+nsc_note_modal_symptom_row_id ).find('.symptom-soft-paste-btn').addClass('active');

						if(!$("#row"+initial_id_nsc_note_modal+"_"+ symptom_id_nsc_note_modal ).find('.symptom-soft-paste-btn').hasClass('active'))
							$("#row"+initial_id_nsc_note_modal+"_"+ symptom_id_nsc_note_modal ).find('.symptom-soft-paste-btn').addClass('active');

						$('[class*=_' + symptom_id_nsc_note_modal + ']:not(".'+symptom_id_nsc_note_modal+'")').each(function()
						{
							
							if($(this).next().hasClass("initialsConnectedPASTE"))
				 			{
				 				$(this).next(".initialsConnectedPASTE").children(".row"+initial_id_nsc_note_modal).find(".symptom-soft-paste-btn").addClass("active");
				 				//console.log("xyz"+initial_id_nsc_note_modal);
				 			}	
				 			if($(this).next().hasClass("initialsConnectedPE"))
				 			{
				 				$(this).next(".initialsConnectedPE").children(".row"+initial_id_nsc_note_modal).find(".symptom-soft-paste-btn").addClass("active");
				 				//console.log("xyz"+initial_id_nsc_note_modal);
				 			}		
						  	
						});
						if(!$("#nsc_note_modal_loader_paste .loading-msg").hasClass('hidden'))
							$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
						
						$("#nsc_note_modal_loader_paste .error-msg").html('Updated successfully');
						setTimeout(function() { 
							$("#nsc_note_modal_loader_paste .error-msg").html('');
							$("#nsc_note_modal_loader_paste").addClass('hidden');
						}, 1000);
						$("#nscNoteModalPaste").modal('hide');
					}
				}else{
					$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader_paste .error-msg").html('Could not save the data!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
			$("#nsc_note_modal_loader_paste .error-msg").html('Something went wrong!');
			if ( window.console && window.console.log ) {
				//console.log( response);
			}
		});
	}
	else if(error_display_msg != ""){
		if($("#nsc_note_modal_loader_paste").hasClass('hidden'))
			$("#nsc_note_modal_loader_paste").removeClass('hidden');
		if(!$("#nsc_note_modal_loader_paste .loading-msg").hasClass('hidden'))
			$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
		$("#nsc_note_modal_loader_paste .error-msg").html(error_display_msg);
	}
}

// NonSecurePaste Or softpaste
$(document).on('click', '.symptom-soft-paste-btn', function(){
	if(!$(this).hasClass('ns-disabled')){
		var checkingNS = $("#checkingNS").attr("data-mark");
		if($(this).parents(".comparing").attr("id")){
			var thisId = $(this).parents(".comparing").attr("id");
			var thisIdArray = thisId.split("_");
			//[0] represents "row". We could instead hard code the word "row"
			var comparativeId = thisIdArray[1];
			var initialId = thisIdArray[0];
			var initialId = thisIdArray[0].replace("row","");
			var symptomId = comparativeId;
		}
		else{
			var thisId = $(this).parents(".initial").attr("id");
			var initialId = thisId.replace("row","");
			if($(this).parents(".initialsConnectedPASTE").prevAll(".comparing").first().attr("id"))
			{
				var symptomIdFind = $(this).parents(".initialsConnectedPASTE").prevAll(".comparing").first().attr("id");
			}
			if($(this).parents(".initialsConnectedPE").prevAll(".comparing").first().attr("id"))
			{
				var symptomIdFind = $(this).parents(".initialsConnectedPE").prevAll(".comparing").first().attr("id");
			}
			var thisIdArray = symptomIdFind.split("_");
			var symptomId = thisIdArray[1];
		}
		var symptomRowId = $(this).parents(".symptom-row").attr("id");
		$.ajax({
			type: 'POST',
			url: 'get-non-secure-info-comparison-table.php',
			data: {
				symptom_id: symptomId,
				initial_id: initialId,
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
					if(!$("#nsc_note_modal_loader_paste .loading-msg").hasClass('hidden'))
						$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader_paste").addClass('hidden');
					$("#nscNoteModalPaste").modal('show');
					$("#populated_nsc_note_data_paste").remove();
					var Nsc_note = (resultData.ns_paste_comment != "" && resultData.ns_paste_comment != null) ? resultData.ns_paste_comment : "";
					var html = '';
					html += '<div id="populated_nsc_note_data_paste">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<textarea name="symptom_nsc_note_modal_paste" id="symptom_nsc_note_modal_paste" placeholder="Non secure Note" class="form-control" rows="5" cols="50">'+Nsc_note+'</textarea>';
					if(checkingNS == 1){
						html += '<div class="radio"><label><input type="radio" name="ns_radio" class="ns-confirm" value="1" checked>Confirm</label></div><div class="radio"><label><input type="radio" name="ns_radio" class="ns-new" value="0">New</label></div>';
					}
					html += '			<span class="error-text"></span>';
					html += '			<input type="hidden" name="symptom_id_nsc_note_modal_paste" id="symptom_id_nsc_note_modal_paste" value="'+symptomId+'">';
					html += '			<input type="hidden" name="initial_id_nsc_note_modal_paste" id="initial_id_nsc_note_modal_paste" value="'+initialId+'">';
					html += '			<input type="hidden" name="nsc_note_modal_unique_id_paste" id="nsc_note_modal_unique_id_paste" value="'+symptomId+'">';
					html += '			<input type="hidden" name="nsc_note_modal_symptom_row_id_paste" id="nsc_note_modal_symptom_row_id_paste" value="'+symptomRowId+'">';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';
					$("#nsc_note_container_paste").append(html);
				}else{
					$("#nsc_note_modal_loader_paste .loading-msg").addClass('hidden');
					$("#nsc_note_modal_loader_paste .error-msg").html('Something went wrong!');
				}
			}
		});
	}
});

//non secure paste on load function
$.fn.nonSecureConnectPasteFn = function(nonSecureConnectedPaste)
{
	var params = nonSecureConnectedPaste.split("||#id-separator#||");
	var symptom_id = params[0];
	var dataIdPart = params[1];
	var restParams = dataIdPart.split("||#data-separator#||");
	var initial_symptom_id = restParams[0];
	var non_secure_connect = restParams[1];
	if($("#row"+initial_symptom_id+"_"+ symptom_id ).find('.symptom-soft-paste-btn'))
	{
		$("#row"+initial_symptom_id+"_"+ symptom_id ).find('.symptom-soft-paste-btn').addClass("active");
	}
	else
		console.log("no");
	$('[class*=_' + symptom_id + ']').each(function()
	{
	  	if($(this).next().hasClass("initialsConnectedPASTE"))
		{
			$(this).next(".initialsConnectedPASTE").children(".row"+initial_symptom_id).find(".symptom-soft-paste-btn").addClass("active");
		}
		if($(this).next().hasClass("initialsConnectedPE"))
		{
			$(this).next(".initialsConnectedPE").children(".row"+initial_symptom_id).find(".symptom-soft-paste-btn").addClass("active");
		}
	});
}

//disconnect earlier connected symptoms
$.fn.disconnectEarlierConnection = function(initial_id,comparing_id,connectionTypeForDisconnection){
	$.ajax({
		async:false,
		type: "POST",
      	url: "update-symptom-info-comparison-table.php",
	    data: {
			symptom_id: comparing_id,
			initial_id: initial_id,
			comparison_table_name: comparison_table_name,
			connectionTypeForDisconnection: connectionTypeForDisconnection,
			update_filed: 'Disconnect_earlier_connection'
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

//Non secure on load
$.fn.nonSecureOnLoad = function(nonSecureLoadId, initialNS){
	$('[class*=_' + nonSecureLoadId + ']').find('.symptom-soft-paste-btn').addClass('active');
	$('[class*=_' + nonSecureLoadId + ']').find('.symptom-soft-connect-btn').addClass('active');

	//adding in initial symptoms
	$('.row' + initialNS).each(function()
	{
		$(".row"+initialNS ).find('.symptom-soft-connect-btn').addClass("active");
		$(".row"+initialNS ).find('.symptom-soft-paste-btn').addClass("active");
	});
}

//Non secure connect function update
function addGenNscNote(param){
	var gen_nsc_note = $("#symptom_gen_nsc_note_modal").val();
	var initial_id_gen_nsc_note_modal = $("#initial_id_gen_nsc_note_modal").val();
	var checkingNS = $("#checkingNS").attr("data-mark");
	var error_display_msg = "";
	var error_count = 0;
	var gen_ns_value = param;
	var ns_confirm = 0;
	var ns_new = 0;
	if(checkingNS == 1){
		ns_confirm = $(".ns-confirm").val();
		ns_new = $(".ns-new").val();
	}

	if(ns_new == 1){
		gen_ns_value = '0';
	}
	if(initial_id_gen_nsc_note_modal == ""){
		error_display_msg = "Required data missing, Please retry!";
		error_count++;
	}

	if(error_count == 0){
		if($("#gen_nsc_note_modal_loader").hasClass('hidden'))
			$("#gen_nsc_note_modal_loader").removeClass('hidden');

		$.ajax({
			type: 'POST',
			url: 'update-symptom-info-comparison-table.php',
			data: {
				initial_id: initial_id_gen_nsc_note_modal,
				Nsc_note: gen_nsc_note,
				comparison_table_name: comparison_table_name,
				gen_ns_value: gen_ns_value,
				ns_confirm: ns_confirm,
				ns_new: ns_new,
				update_filed: 'Gen_nsc_note'
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
					if(gen_ns_value == '1'){
						if(!$("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').hasClass('active'))
								$("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').addClass('active');
						$("#row"+initial_id_gen_nsc_note_modal).find(".marking").attr("checked","1");
					}else{
						if($("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').hasClass('active'))
							$("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').removeClass('active');
						$("#row"+initial_id_gen_nsc_note_modal).find(".marking").removeAttr("checked");
					}

					if(ns_confirm == '1'){
						if($("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').hasClass('active'))
							$("#row"+initial_id_gen_nsc_note_modal ).find('.gen-ns').removeClass('active');
					}

					if(!$("#gen_nsc_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#gen_nsc_note_modal_loader .error-msg").html('Updated successfully');

					setTimeout(function() { 
						$("#gen_nsc_note_modal_loader .error-msg").html('');
						$("#gen_nsc_note_modal_loader").addClass('hidden');
						$("#genNscNoteModal").modal('hide');
					}, 2000);
				}else{
					$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#gen_nsc_note_modal_loader .error-msg").html('Could not save the data!');
					console.log(response);
				}
			}
		}).fail(function (response) {
			$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
			$("#gen_nsc_note_modal_loader .error-msg").html('Something went wrong!');
			console.log(response);
			if ( window.console && window.console.log ) {
				//console.log( response);
			}
		});
	}
	else if(error_display_msg != ""){
		if($("#gen_nsc_note_modal_loader").hasClass('hidden'))
			$("#gen_nsc_note_modal_loader").removeClass('hidden');
		if(!$("#gen_nsc_note_modal_loader .loading-msg").hasClass('hidden'))
			$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
		$("#gen_nsc_note_modal_loader .error-msg").html(error_display_msg);
	}
}

//general non secure button
$(document).on('click', '.gen-ns', function(){
	if(!($(this).hasClass("ns-disabled"))){
		var thisId = $(this).parents(".initial").attr("id");
		var initialId = thisId.replace("row","");
		var checkingNS = $("#checkingNS").attr("data-mark");
		$.ajax({
			type: 'POST',
			url: 'get-general-non-secure-info-comparison-table.php',
			data: {
				initial_id: initialId,
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
					if(!$("#gen_nsc_note_modal_loader .loading-msg").hasClass('hidden'))
						$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#gen_nsc_note_modal_loader").addClass('hidden');
					$("#genNscNoteModal").modal('show');
					$("#populated_gen_nsc_note_data").remove();
					var gen_ns_comment = (resultData.gen_ns_comment != "" && resultData.gen_ns_comment != null) ? resultData.gen_ns_comment : "";
					var html = '';
					html += '<div id="populated_gen_nsc_note_data">';
					html += '	<div class="row">';
					html += '		<div class="col-sm-12">';
					html += '			<textarea name="symptom_gen_nsc_note_modal" id="symptom_gen_nsc_note_modal" placeholder="Non secure Note" class="form-control" rows="5" cols="50">'+gen_ns_comment+'</textarea>';
					if(checkingNS == 1){
						html += '<div class="radio"><label><input type="radio" name="ns_radio" class="ns-confirm" value="1" checked>Confirm</label></div><div class="radio"><label><input type="radio" name="ns_radio" class="ns-new" value="0">New</label></div>';
					}
					html += '			<span class="error-text"></span>';
					html += '			<input type="hidden" name="initial_id_gen_nsc_note_modal" id="initial_id_gen_nsc_note_modal" value="'+initialId+'">';
					html += '		</div>';
					html += '	</div>';
					html += '</div>';
					$("#gen_nsc_note_container").append(html);
				}else{
					$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
					$("#gen_nsc_note_modal_loader .error-msg").html('Something went wrong!');
				}
				}
			}).fail(function (response) {
				$("#gen_nsc_note_modal_loader .loading-msg").addClass('hidden');
				$("#gen_nsc_note_modal_loader .error-msg").html('Something went wrong!');
				if ( window.console && window.console.log ) {
					console.log( response);
				}
		});
	}
});
