//Toggle Icons Plus
$(document).on("click", ".toggleInitial", function (ev) {
	if($(this).children().hasClass('fa-plus'))
	{

		if($(this).parents('.symptom-row').hasClass('initial'))
		{
			console.log("yes");
			var thisId = $(this).parents('div.initial').attr('id');
			var symptomId = thisId.replace("row","");
			console.log(thisId);
			var symptomType = "initial";
			var comparativeHtml = '<div class="comparativesConnectedCD" id="control'+symptomId+'">';

			//Collecting data from connections table
			$.ajax({
				type: 'POST',
				url: './symptom-connection-operations.php',
				data: {
					symptom_id: symptomId,
					symptom_type: symptomType
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
						console.log(resultData);
						$.each(resultData, function( key, value ) {
							var yearExtract = value.quelle_code.split(" ");
							var year = yearExtract[1];
							comparativeHtml = comparativeHtml+'<div class="row'+value.connected_symptom_id+'_'+value.symptom_id+' symptom-row comparing" id="row'+value.connected_symptom_id+'_'+value.symptom_id+'" data-year="'+year+'" data-source-original-language="'+value.comparison_language+'" data-quell-id="'+value.quelle_id+'">';
							comparativeHtml = comparativeHtml+'<div class="source-code">'+value.quelle_code+'</div>';
							comparativeHtml = comparativeHtml+'<div class="symptom">'+value.symptom_text_de+'</div>';
							comparativeHtml = comparativeHtml+'<div class="percentage">'+value.matched_percentage+'%</div>';
							comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)">T</a> </li> </ul></div>';
							comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';						
						});
							comparativeHtml = comparativeHtml + '</div>';
							//console.log(comparativeHtml);
							$(comparativeHtml).insertAfter($('.'+thisId));
					}else{
						console.log("error");
						console.log(response);
					}
				}
			});

			//$('.'+symptomId).append(comparativeHtml);
			console.log(symptomId+" "+symptomType);

		}

		if($(this).parents('.symptom-row').hasClass('comparing'))
		{
			console.log("yes");
			var thisId = $(this).parents('div.comparing').attr('id');
			var thisIdArray = thisId.split("_");
			var comparativeId = thisIdArray[1];
			var initialId = thisIdArray[0];
			initialId = initialId.replace("row","");
			var symptomType = "comparing";
			var symptomId = comparativeId;
			var initialHtml = '<div class="initialsConnectedCD" id="control'+symptomId+'">';

			//Collecting data from connections table
			$.ajax({
				type: 'POST',
				url: './symptom-connection-operations.php',
				data: {
					symptom_id: symptomId,
					symptom_type: symptomType
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
						console.log(resultData);
						$.each(resultData, function( key, value ) {
							var yearExtract = value.quelle_code_connected.split(" ");
							var year = yearExtract[1];
							initialHtml = initialHtml+'<div class="row'+value.connected_symptom_id+' symptom-row initial" id="row'+value.connected_symptom_id+'" data-year="'+year+'" data-source-original-language="'+value.comparison_language+'" data-quell-id="'+value.quelle_id_connected+'">';
							initialHtml = initialHtml+'<div class="source-code">'+value.quelle_code_connected+'</div>';
							initialHtml = initialHtml+'<div class="symptom">'+value.symptom_text_de+'</div>';
							initialHtml = initialHtml+'<div class="percentage">'+value.matched_percentage+'%</div>';
							initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)">T</a> </li> </ul></div>';
							initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';						
						});
							initialHtml = initialHtml + '</div>';
							console.log(initialHtml);
							//$(initialHtml).insertAfter($('.'+thisId));
							//$('.'+thisId).append(initialHtml);
							//$(initialHtml).insertAfter($('.'+thisId));
							$('.'+thisId).each(function(){
								$(initialHtml).insertAfter($(this));
							});

							console.log(thisId);

					}else{
						console.log("error");
						console.log(response);
					}
				}
			});

			//$('.'+symptomId).append(comparativeHtml);
			console.log(symptomId+" "+symptomType);

		}
		
		if($(this).find('i').hasClass('fa-plus'))
			$(this).find('i').removeClass('fa-plus').addClass('fa-minus')
		else
			$(this).find('i').removeClass('fa-minus').addClass('fa-plus')
	}
	else
	{
		
		var thisId = $(this).parents('div.initial').attr('id');
		var symptomId = thisId.replace("row","");
		$("#control"+symptomId).remove();
		if($(this).find('i').hasClass('fa-plus'))
			$(this).find('i').removeClass('fa-plus').addClass('fa-minus')
		else
			$(this).find('i').removeClass('fa-minus').addClass('fa-plus')
	}

	// $(this).parents('div.initial').nextUntil(".initial").each(function()
	// {
	// 	if($(this).hasClass('comparativesConnectedCD'))
	// 		$(this).toggle();
	// 	if($(this).hasClass('comparativesConnectedCE'))
	// 		$(this).toggle();
	// 	if($(this).hasClass('comparativesConnectedPE'))
	// 		$(this).toggle();
	// 	if($(this).hasClass('comparativesConnectedPASTE'))
	// 		$(this).toggle();
	// });
	
});

//Initial top icons
$.fn.initialsTopIcons = function(initialId){
	initialId = "row"+initialId;
	$("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").each(function(){
		$(this).find(".info").empty();
		$(this).find(".info").append(initialsTopIconsCD_info_close);
		// if($(this).next().is('[class^="comparativesConnected"]'))
		// {
		// 	$(this).find(".info").empty();
		// 	if($(this).next().next('[class^="comparativesConnected"]').is(':visible') || $(this).next('[class^="comparativesConnected"]').is(':visible'))
		// 	{
		// 		$(this).nextUntil(".comparing").each(function(){
		// 			if($(this).is('[class^="comparativesConnected"]'))
		// 				$(this).css("display", "block");
		// 		});
		// 		$(this).find(".info").append(initialsTopIconsCD_info_open);
		// 	}
		// 	else if($(this).next().is(':hidden'))
		// 		$(this).find(".info").append(initialsTopIconsCD_info_close);
		// }
		// else
		// {
		// 	$(this).find(".info").empty();
		// 	$(this).find(".info").append(initialsNormalIconsCD_info);
		// }
	});
	console.log(initialId);
}

//Comparatives connected with Initial below
$.fn.comparativesInitialBottomIcons = function(comparativeId, sectionComparativeId){
	//$('[class*=_' + comparativeId + ']:not(".'+sectionComparativeId+'")').each(function(){
	$('[class*=_' + comparativeId + ']').each(function(){
		// if($(this).next().is('[class^="initialsConnected"]') && $(this).is(":visible"))
		// {
		// 	$(this).find(".info").empty();
		// 	if($(this).next().next('[class^="initialsConnected"]').is(':visible') || $(this).next('[class^="initialsConnected"]').is(':visible'))
		// 	{
		// 		//$(this).find(".info").append(comparativesInitialBottomIconsCD_info_open);
		// 		//$(this).nextAll('[class^="initialsConnected"]').nextUntil(".comparing").css("display", "block");
		// 		//$(this).nextAll('.initialsConnectedCD').first().css("display", "block");
		// 		//$(this).nextAll('.initialsConnectedCD').nextUntil(".comparing").css("display", "block");
		// 		$(this).nextUntil(".comparing").each(function(){
		// 			if($(this).is('[class^="initialsConnected"]'))
		// 				$(this).css("display", "block");
		// 		});
		// 		$(this).find(".info").append(comparativesInitialBottomIconsCD_info_open);
		// 	}
		// 	else if($(this).next().is(':hidden'))
		// 		//if($(this).next().css('display') == 'none')
		// 			$(this).find(".info").append(comparativesInitialBottomIconsCD_info_close);
		// }
		// else
		// {
		// 	$(this).find(".info").empty();
		// 	$(this).find(".info").append(comparativesNormalIconsCD_info);
		// }
		console.log("polo");
		$(this).find(".info").empty();
		$(this).find(".info").append(comparativesInitialBottomIconsCD_info_close);
	});
}