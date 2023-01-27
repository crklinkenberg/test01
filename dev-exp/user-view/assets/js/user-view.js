$(document).ready(function(){
	//symptom info in dashboard click
	$("#symtomInfoSettings").on("click", function(){
		innerHtml = '<h4 id="settingsHeading">Symptom Information</h4>';
		innerHtml = innerHtml +'<div class="symptomInfoHtml" id="formContent">';
		innerHtml = innerHtml + '<div class="mb-3">';
		innerHtml =	innerHtml + '<div class="form-check">';
		innerHtml =	innerHtml + '<input class="form-check-input" type="radio" name="symInfoRadio" id="flexRadioDefault2" value="metaDataInfoOn" checked>';
		innerHtml =	innerHtml + '<label class="form-check-label" for="flexRadioDefault2"><i class="fas fa-info-circle"></i> Link to Metadata</label></div>';
		innerHtml =	innerHtml + '<div class="form-check">';
		innerHtml =	innerHtml + '<input class="form-check-input" type="radio" name="symInfoRadio" id="flexRadioDefault1" value="metaDataInfoOff">';
		innerHtml =	innerHtml + '<label class="form-check-label" for="flexRadioDefault1">Make no offer</label></div></div>';
		innerHtml =	innerHtml + '<input type="hidden" name="settingsApplied" value="1">';
		innerHtml =	innerHtml + '<button type="submit" class="btn btn-success">Submit</button>';
		innerHtml =	innerHtml + '</div>';
		if($("#settingsApplied").val()==1){
			$("#symptomDisplay").remove();
			if(!$("#formContent").hasClass('symptomInfoHtml')){
				$('#settingsHeading').remove();
				$("#formContent").remove();
				$('#settingsForm').append(innerHtml);
			}
		}else{
			if(!$("#formContent").hasClass('symptomInfoHtml')){
				$('#settingsHeading').remove();
				$("#formContent").remove();
				$('#settingsForm').append(innerHtml);
			}
		}	
	});

	//symptom history in dashboard click
	$("#symtomHistorySettings").on("click", function(){
		innerHtml = '<h4 id="settingsHeading">Symptom History</h4>';
		innerHtml = innerHtml +'<div class="symptomHistoryHtml" id="formContent">';
		innerHtml = innerHtml + '<div class="mb-3">';
		innerHtml =	innerHtml + '<div class="form-check">';
		innerHtml =	innerHtml + '<input class="form-check-input" type="radio" name="symHistoryRadio" id="flexRadioDefault2" value="metaDataHistoryOn" checked>';
		innerHtml =	innerHtml + '<label class="form-check-label" for="flexRadioDefault2"><i class="fas fa-history"></i> Link to Metadata</label></div>';
		innerHtml =	innerHtml + '<div class="form-check">';
		innerHtml =	innerHtml + '<input class="form-check-input" type="radio" name="symHistoryRadio" id="flexRadioDefault1" value="metaDataHistoryOff">';
		innerHtml =	innerHtml + '<label class="form-check-label" for="flexRadioDefault1">Make no offer</label></div></div>';
		innerHtml =	innerHtml + '<input type="hidden" name="settingsApplied" value="1">';
		innerHtml =	innerHtml + '<button type="submit" class="btn btn-success">Submit</button>';
		innerHtml =	innerHtml + '</div>';
		if($("#settingsApplied").val()==1){
			$("#symptomDisplay").remove();
			if(!$("#formContent").hasClass('symptomHistoryHtml')){
				$('#settingsHeading').remove();
				$("#formContent").remove();
				$('#settingsForm').append(innerHtml);
			}
		}else{
			if(!$("#formContent").hasClass('symptomHistoryHtml')){
				$('#settingsHeading').remove();
				$("#formContent").remove();
				$('#settingsForm').append(innerHtml);
			}
		}
	});

	//symptom info setting radio changes input value
	$(document).on('change', 'input[type=radio][name=symInfoRadio]', function(){
		var val = $(this).attr("value");
		if(val == 'metaDataInfoOn'){
			$("#infoRadioVal").attr("value","metaDataInfoOn");
		}else{
			$("#infoRadioVal").attr("value","metaDataInfoOff");
		}
	});

	//symptom history setting radio changes input value
	$(document).on('change', 'input[type=radio][name=symHistoryRadio]', function(){
		var val = $(this).attr("value");
		console.log(val);
		if(val == 'metaDataHistoryOn'){
			$("#historyRadioVal").attr("value","metaDataHistoryOn");
		}else{
			$("#historyRadioVal").attr("value","metaDataHistoryOff");
		}
	});

	//symptom info pop up when clicked
	$(".fa-info-circle").on("click", function(){
		if($(this).hasClass('symptom-info')){
			alert('Symptom Info will be displayed');
		}
	});
});