$.fn.initialsWithComparativesConnectedBelow = function(type, initialId, comparativeId,onLoad,is_earlier_connection){
	var onLoadChange = 'minus';
	if(onLoad==1)
	{
		var onLoadChange = 'plus';
	}
	var earlierSavedConnection = " ";
	var nsDisabled = " ";
	if(is_earlier_connection == "1"){
		var earlierSavedConnection = " earlierSavedConnection";
		var nsDisabled = " ns-disabled";
	}
	if(type == 'CE'){
		if(!($('.'+initialId).not('[class^=initialsConnected] .initial').find(".connectEditText").length)){
		$('.'+initialId).not('[class^=initialsConnected] .initial').find(".symptom-connections-btn").parent('li').remove();
		$('.'+initialId).not('[class^=initialsConnected] .initial').not('[class^=comparativesConnected] .initial').find(".info-linkage-group").append('<li><a class="active connectEditText" href="javascript:void(0)" title="Connect edit">CE</a></li><li><a class="active symptom-connections-btn toggleInitial '+earlierSavedConnection+'" title="Earlier connections" href="javascript:void(0)"><i class="fas fa-'+onLoadChange+'"></i></a></li>');
		}
	}
	else if(type == 'SWAP'){
		if(!$('.'+initialId).not('[class^=initialsConnected] .initial').find(".swapInitial").hasClass("active")){
			$('.'+initialId).not('[class^=initialsConnected] .initial').find(".symptom-connections-btn").parent('li').remove();
			$('.'+initialId).not('[class^=initialsConnected] .initial').not('[class^=comparativesConnected] .initial').find(".info-linkage-group").append('<li><a class="active swapInitial" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li><li><a class="active symptom-connections-btn toggleInitial '+earlierSavedConnection+'" title="Earlier connections" href="javascript:void(0)"><i class="fas fa-'+onLoadChange+'"></i></a></li>');
		}
	}
	else{
		if(!$('.'+initialId).not('[class^=initialsConnected] .initial').find(".symptom-connections-btn").length)
			$('.'+initialId).not('[class^=initialsConnected] .initial').not('[class^=comparativesConnected] .initial').find(".info-linkage-group").append('<li><a class="active symptom-connections-btn toggleInitial '+earlierSavedConnection+'" title="Earlier connections" href="javascript:void(0)"><i class="fas fa-'+onLoadChange+'"></i></a></li>');
	}
	
	if(type == 'CD'){
		$(".comparativesConnectedCD "+"."+comparativeId).find(".command-group").empty();
		$(".comparativesConnectedCD "+"."+comparativeId).find(".command-group").append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn '+earlierSavedConnection+' disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li>');
	}

	if(type == 'CE'){

		$(".comparativesConnectedCE "+"."+comparativeId).find(".command-group").empty();
		$(".comparativesConnectedCE "+"."+comparativeId).find(".command-group").append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE '+earlierSavedConnection+' active" href="javascript:void(0)" title="Connect edit">CE</a></li>');
	}

	if(type == 'PASTE'){
		$(".comparativesConnectedPASTE "+"."+comparativeId).find(".command-group").empty();
		$(".comparativesConnectedPASTE "+"."+comparativeId).find(".command-group").append('<li><a class="symptom-soft-paste-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPaste '+earlierSavedConnection+' active" href="javascript:void(0)" title="Paste">P</a></li>');
	}

	if(type == 'PE'){
		$(".comparativesConnectedPE "+"."+comparativeId).find(".command-group").empty();
		$(".comparativesConnectedPE "+"."+comparativeId).find(".command-group").append('<li><a class="symptom-soft-paste-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPE '+earlierSavedConnection+' active" href="javascript:void(0)" title="Paste Edit">PE</a></li>');
	}

	if(type == 'SWAP'){
		$(".comparativesConnectedCD "+"."+comparativeId).find(".command-group").empty();
		$(".comparativesConnectedCD "+"."+comparativeId).find(".command-group").append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect '+earlierSavedConnection+' active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li><li><a class="symptom-swap-connect-btn" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li>');
	}
}
$.fn.comparativesWithInitialsConnectedBelow = function(type, initialId, comparativeId, thisId, is_earlier_connection){
	var earlierSavedConnection = " ";
	var nsDisabled = " ";
	if(is_earlier_connection == "1"){
		var earlierSavedConnection = " earlierSavedConnection";
		var nsDisabled = " ns-disabled";
	}
	$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
		if(!$(this).parents().is("[class^=comparativesConnected]"))
		{
			if(!$(this).find(".symptom-connections-btn").length)
				$(this).find(".info-linkage-group").append('<li><a class="active symptom-connections-btn toggleComparative '+earlierSavedConnection+'" title="Earlier connections" href="javascript:void(0)"><i class="fas fa-plus"></i></a></li>');
		}
	});
	if(type == 'CD'){
		$(".initialsConnectedCD ."+initialId).find(".command-group").each(function()
		{
			$(this).empty();
			$(this).append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect '+earlierSavedConnection+' active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li>');
		});
	}
	if(type == 'CE'){
		$(".initialsConnectedCE ."+initialId).find(".command-group").each(function()
		{
			$(this).empty();
			$(this).append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE '+earlierSavedConnection+' active" href="javascript:void(0)" title="Connect edit">CE</a></li>');
		});
	}

	if(type == 'PASTE'){
		$(".initialsConnectedPASTE ."+initialId).find(".command-group").each(function()
		{
			$(this).empty();
			$(this).append('<li><a class="symptom-soft-paste-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPaste '+earlierSavedConnection+' active" href="javascript:void(0)" title="Paste">P</a></li>');
		});
	}

	if(type == 'PE'){
		$(".initialsConnectedPE ."+initialId).find(".command-group").each(function()
		{
			$(this).empty();
			$(this).append('<li><a class="symptom-soft-paste-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPE '+earlierSavedConnection+' active" href="javascript:void(0)" title="Paste Edit">PE</a></li>');
		});
	}
}
$.fn.initialsWithComparativesDisconnectedBelow = function(initialId){
	if(!$('.'+initialId).not('[class^=initialsConnected] .initial').next().is("[class^=comparativesConnected]")){
		console.log(initialId+" /**");
		var infoLinkageGroupHtml = $('.'+initialId).not('[class^=initialsConnected] .initial').find(".info-linkage-group").get(0).innerHTML;
		const $dom = $("<div>").html(infoLinkageGroupHtml);
		$dom.find('.toggleInitial').parent('li').remove();
		var newInfoLinkageGroupHtml = $dom.html();

		$('.'+initialId).not('[class^=initialsConnected] .initial').find(".info-linkage-group").empty();
		$('.'+initialId).not('[class^=initialsConnected] .initial').find(".info-linkage-group").append(newInfoLinkageGroupHtml);
			
	}
}
$.fn.comparativesWithInitialsDisconnectedBelow = function(disconnectid_comparativelId, thisId){
	$('[class*=_' + disconnectid_comparativelId + ']').each(function(){
		if(!$(this).next().is("[class^=initialsConnected]")){
			var infoLinkageGroupHtml = $(this).find(".info-linkage-group").get(0).innerHTML;
			const $dom = $("<div>").html(infoLinkageGroupHtml);
			$dom.find('.toggleComparative').parent('li').remove();
			var newInfoLinkageGroupHtml = $dom.html();

			$(this).find(".info-linkage-group").empty();
			$(this).find(".info-linkage-group").append(newInfoLinkageGroupHtml);
		}
	});
}
$.fn.swapComparativesIcons = function(type, comparativeId, is_earlier_connection){
	var earlierSavedConnection = " ";
	var nsDisabled = " ";
	if(is_earlier_connection == "1"){
		var earlierSavedConnection = " earlierSavedConnection";
		var nsDisabled = " ns-disabled";
	}
	if(type == 'CD'){
		$('[class*=_' + comparativeId + ']').each(function(){
			$(this).find(".command-group").empty();
			$(this).find(".command-group").append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active '+earlierSavedConnection+'" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li><li><a class="symptom-swap-connect-btn active" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li>');
		});
		
	}

	if(type == 'CE'){
		$('[class*=_' + comparativeId + ']').each(function(){
			$(this).find(".command-group").empty();
			$(this).find(".command-group").append('<li><a class="symptom-soft-connect-btn '+nsDisabled+'" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE active '+earlierSavedConnection+'" href="javascript:void(0)" title="connect">CE</a></li><li><a class="symptom-swap-connect-btn active" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li>');
		});
		
	}		
}