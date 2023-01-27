//This function takes html element and helps in sorting of connected symptoms.
//sorting order: connect-edit->connect->paste edit->paste.
function connectionSort(htmlElement,comparativeHtml,type){
	if(type == "after"){
		$(htmlElement).after(comparativeHtml);
	} else {
		$(htmlElement).before(comparativeHtml);
	}
}

//Filteration of symptom text when translation is open
function symptomTextFilter(text){
	var filterText = text.match(/.+(?=<div)/gm);
	var filterTextFinal = filterText[0];
	return filterTextFinal;
}

//Reloading function with url modified when translation and connections open or closed
function reloadConnection(initialId){
	var initialId = String(initialId);
	var allConectionOn = 0;
	var allTranslationOn = 0;
	var allComparativeTranslationOn = 0;
	var addresssUrl = window.location.href;
	addresssUrl = String(addresssUrl);
	if($(".all-connections").prop("checked") == true) {
		allConectionOn = 1;
	}

	if($("#all_initial_translation").prop("checked") == true) {
		allTranslationOn = 1;
	}
	if($("#all_comparative_translation").prop("checked") == true) {
		allComparativeTranslationOn = 1;
	}
	var newAddress = window.location.href;
	if(newAddress.includes("?scroll")){
		newAddress = newAddress.split('?scroll')[0];
	}

	if(newAddress.includes("&scroll")){
		newAddress = newAddress.split('&scroll')[0];
	}

	if(allConectionOn === 0 && allTranslationOn === 0){
		newAddress = newAddress.replace("&open_conn=1","");
		newAddress = newAddress.replace("?open_conn=1&","?");
		newAddress = newAddress.replace("&open_ini_trans=1","");
		newAddress = newAddress.replace("?open_ini_trans=1&","?");
		newAddress = newAddress.replace("?open_ini_trans=1","");
		newAddress = newAddress.replace("?open_conn=1","");
	}else if(allConectionOn === 0 && allTranslationOn === 1){
		if(addresssUrl.includes("?open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = window.location.href.replace("?open_conn=1&","?");
			newAddress = newAddress+initialId;
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("?open_ini_trans=1"))){
			newAddress = window.location.href.replace("&open_conn=1","");
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = window.location.href.replace("&open_conn=1","");
		}else{
			if(window.location.href == baseUrlOperation+"comparison.php" || newAddress == baseUrlOperation+"comparison.php"){
				newAddress += "?open_ini_trans=1";
			}else if(addresssUrl.includes("?open_conn=1")){
				newAddress = window.location.href.replace("?open_ini_trans=1","");
			}else if(addresssUrl.includes("&open_conn=1")){
				newAddress = window.location.href.replace("&open_ini_trans=1","");
			}else if(addresssUrl.includes("&open_ini_trans=1") || (addresssUrl.includes("?open_ini_trans=1"))){
				newAddress = newAddress;
			}else{
				newAddress += "&open_ini_trans=1";
			}
		} 
	}else if(allConectionOn === 1 && allTranslationOn === 0){
		if(addresssUrl.includes("?open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = window.location.href.replace("&open_ini_trans=1","");
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("?open_ini_trans=1"))){
			newAddress = window.location.href.replace("?open_ini_trans=1","?");
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = window.location.href.replace("&open_ini_trans=1","");
		}else{
			if(window.location.href == baseUrlOperation+"comparison.php" || newAddress == baseUrlOperation+"comparison.php"){
				newAddress += "?open_conn=1";
			}else if(addresssUrl.includes("?open_ini_trans=1")){
				newAddress = window.location.href.replace("?open_conn=1","");
			}else if(addresssUrl.includes("&open_ini_trans=1")){
				newAddress = window.location.href.replace("&open_conn=1","");
			}else if(addresssUrl.includes("&open_conn=1") || (addresssUrl.includes("?open_conn=1"))){
				newAddress = newAddress;
			}else{
				newAddress += "&open_conn=1";
			}
		}
	}else{
		if(addresssUrl.includes("?open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = newAddress;
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("?open_ini_trans=1"))){
			newAddress = newAddress;
		}else if(addresssUrl.includes("&open_conn=1") && (addresssUrl.includes("&open_ini_trans=1"))){
			newAddress = newAddress;
		}else{
			if(window.location.href == baseUrlOperation+"comparison.php" || newAddress == baseUrlOperation+"comparison.php"){
				newAddress += "?open_ini_trans=1&open_conn=1";
			}else if(addresssUrl.includes("open_conn=1")){
				newAddress += "&open_ini_trans=1";
			}else if(addresssUrl.includes("open_ini_trans=1")){
				newAddress += "&open_conn=1";
			}else{
				newAddress += "&open_ini_trans=1&open_conn=1";
			}
		}
	}
	//Comparative Translation
	if(allComparativeTranslationOn === 1){
		if(addresssUrl.includes("?open_com_trans=1") || (addresssUrl.includes("&open_com_trans=1"))){
			newAddress = newAddress;
		}else{
			if(window.location.href == baseUrlOperation+"comparison.php" || newAddress == baseUrlOperation+"comparison.php"){
				newAddress += "?open_com_trans=1";
			}else{
				newAddress += "&open_com_trans=1";
			}
		}
	}else{
		newAddress = newAddress.replace("&open_com_trans=1","");
		newAddress = newAddress.replace("?open_com_trans=1&","?");
	}
	if(newAddress == baseUrlOperation+"comparison.php"){
		newAddress += "?scroll="+initialId;
	}else{
		newAddress += "&scroll="+initialId;
	}
	location.href = newAddress;	
}

//marking Function same page
function markingFunctionOnDisconnection(initialId){
	var markField = 0;
	$("."+initialId).nextUntil(".initial").each(function(){
		if($(this).is("[class^=comparativesConnected]")){
			markField = 1;
		}
	});
	if(markField == 0){
		$("."+initialId).find(".marking").removeAttr("checked");
	}
}

//gen-ns enabled Function same page
function genNsFunctionOnDisconnection(initialId){
	var gen_ns = 0;
	$("."+initialId).nextUntil(".initial").each(function(){
		if($(this).is("[class^=comparativesConnected]")){
			gen_ns = 1;
		}
	});
	if(gen_ns == 0){
		$("."+initialId).find(".gen-ns").removeClass("ns-disabled");;
	}
}

//Reload function with non secure connection
function reloadConnectionNonSecureCheck(check_custom_ns, no_of_initials_combined, no_of_initials_single, initialId){
	if(check_custom_ns == 1){
		if(no_of_initials_combined == 1 || no_of_initials_single == 1){
			reloadConnection(initialId);
		}
		else{
			location.reload();
		}
	}
	else{
		reloadConnection(initialId); 
	}
}

//check connections from connection table
function checkInConnectionTable(param, comparison_table_name){

	$.ajax({
		async:false,
		type: "POST",
      	url: "check-in-connection-table.php",
	    data: {
	    	param:param,
	    	comparison_table_name:comparison_table_name
	    },
	    dataType: "JSON",
	    success: function(returnedData){
	    	try {
				resultData = JSON.parse(returnedData.returnValue); 
			} catch (e) {
				resultData = returnedData.returnValue;
			}

    		console.log(returnedData);
	    },
	    error: function(xhr, textStatus, error){
		    console.log(xhr.statusText);
		    console.log(textStatus);
		    console.log(error);
		}
	});
	return resultData;
}