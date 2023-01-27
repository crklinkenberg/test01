//array taken for keeping earlier connected symptoms and establishing new relationship
var arrayForEarlierConnection = [];
var arrayForEarlierConnectionToSent = [];
var earlierConnectionCe = 0;
var swapCe = 0;
var operationFlag = 0;
var swapCheck = 0;
var comparingStringCeEn = "";
var comparingStringCeDe = "";
//Connectedit button click
$(document).on('click', '.symptom-connect-edit-btn', function(e){
	if(!($(this).parents('.comparing').prevAll(".initial").first().find(".gen-ns").hasClass("active"))){
		var comparingStringCeEn = "";
		var comparingStringCeDe = "";
		var thisId = $(this).parents('div.comparing').attr('id');
		var thisIdArray = thisId.split("_");
		ceComparativeId = thisIdArray[1];
		ceInitialId = thisIdArray[0];
		var alreadyCePresent = 0;
		var openPopup = 0;
		var hasConnections = false;
		var hasConnectionsSwap = false;
		var hasConnectionsConfirm = false;
		var ce_allowed = true;
		var earlierConnectionExistCe = 1;
		var earlierExistCe = 0;
		var msg;
		var msgSwap;
		var ceModalDisplay=0;
		var sendingElement = this;
		//Flag for change of symptom text in case of swap
		var stringChange=0;
		//Clearing the Paste and PE array before any CE
		var initial_year = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-year");
		var comparative_year = $(this).parents('div.comparing').attr("data-year");
		var initial_symptom_text = $('#' + ceInitialId).find('.symptom').get(0).innerHTML;
		var initial_quelle_code = $('#' + ceInitialId).find('.source-code').get(0).innerHTML;			
		var initial_quelle_original_language = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-source-original-language");
		var initial_symptom_de = $(this).parents('div.comparing').attr("data-initial-symptom-de");
		var initial_symptom_en = $(this).parents('div.comparing').attr("data-initial-symptom-en");
		var initial_quelle_id = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-quell-id");
		var comparison_language = language;
		language = $("#hidden_comparison_language").val();
		var connectCase = "normal";
		if(parseInt(comparative_year) < parseInt(initial_year)){
			connectCase = "swap";
			if(confirm("The Comparative Symptom is Older than the Initial Symptom."+"\n"+"Would you like to Swap and Connect Edit?"))
			{
				hasConnectionsConfirm = true;
			}
		}else if(parseInt(comparative_year) == parseInt(initial_year)){
			connectCase = "sameYear";
				$("#mydialog").html("The Comparative Symptom is from the same year as the Initial Symptom."+"<br />"+"Would you like to Swap and Connect Edit?").dialog(
				{
	                buttons: [
	                    {
	                    	text: "Yes",
					      	click: function() {
					      		hasConnectionsConfirm = true;
					      		swapCheck = 1;
		                		swapConnectCeOperation(ceModalDisplay,sendingElement);
		                		$(this).dialog('close');
					      	}
	                    },
	                    {
	                    	text: "No",
					      	click: function() {
						        ceModalDisplay = 1;
								ceNormalOperation(ceModalDisplay,sendingElement);
						        $(this).dialog('close');
					      	}
					  	},
					  	{
					  		text: "Esc",
					      	click: function() {
						        $(this).dialog('close');
					      	}
					  	}
	            	]
	            });
		}else{
			connectCase = "normal";
		}
		switch(connectCase){
			case "normal":
			{
				console.log("Normal");
				ceModalDisplay = 1;
				swapCheck = 0;
				ceNormalOperation(ceModalDisplay,sendingElement);
			}
			break;
			case "swap":
			{		
				console.log("swap");
				if(hasConnectionsConfirm){
					swapCheck = 1;
					swapConnectCeOperation(ceModalDisplay,sendingElement);		
				}
			}
			break;
			default:
				//No code
		}
		function ceModalDisplayFn(ceModalDisplay,sendingElement){
			if(ceModalDisplay ==1){
				var uniqueId = $(sendingElement).parents('div.comparing').attr('id');
				console.log(uniqueId);
				var initial_source_symptom_de = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-de');
				var initial_source_symptom_en = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-en');
				var comparing_source_symptom_de = $("#"+uniqueId).attr('data-comparing-symptom-de');
				var comparing_source_symptom_en = $("#"+uniqueId).attr('data-comparing-symptom-en');
				var hidden_comparison_language = $("#hidden_comparison_language").val();
				var decoded_initial_source_symptom_de = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
				var decoded_initial_source_symptom_en = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";	
				var decoded_comparing_source_symptom_de = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
				var decoded_comparing_source_symptom_en = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
				$("#connect_edit_modal_loader .loading-msg").removeClass('hidden');
				$("#connect_edit_modal_loader .error-msg").html('');
				if($("#connect_edit_modal_loader").hasClass('hidden'))
					$("#connect_edit_modal_loader").removeClass('hidden');
				$("#connect_edit_symptom_de_container").addClass('hidden');
				$("#connect_edit_symptom_en_container").addClass('hidden');
				$("#connect_edit_submit_btn").addClass('hidden');
				$("#populated_connect_edit_modal_data").remove();
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
				html += '						<th class="hidden" id="ceYear">'+initial_year+'####'+									comparative_year+'</th>';
				html += '					</tr>';
				html += '				</thead>';
				if(stringChange == 1){
					html += '				<tbody>';
					html += '					<tr>';
					html += '						<th>DE</th>';
					html += '						<td>'+decoded_comparing_source_symptom_de+'</td>';
					html += '						<td>'+decoded_initial_source_symptom_de+'</td>';
					html += '						<td id="jointStringDe" class="hidden">'+												decoded_initial_source_symptom_de+'#####'+											decoded_comparing_source_symptom_de+'</td>';
					html += '					</tr>';
					html += '					<tr>';
					html += '						<th>EN</th>';
					html += '						<td>'+decoded_comparing_source_symptom_en+'</td>';
					html += '						<td>'+decoded_initial_source_symptom_en+'</td>';
					html += '						<td id="jointStringEn" class="hidden">'+												decoded_initial_source_symptom_en+'#####'+											decoded_comparing_source_symptom_en+'</td>';
					html += '					</tr>';
					html += '				</tbody>';
				}else{
					html += '				<tbody>';
					html += '					<tr>';
					html += '						<th>DE</th>';
					html += '						<td>'+decoded_initial_source_symptom_de+'</td>';
					html += '						<td id="decodedComparingStringDe">'+decoded_comparing_source_symptom_de+'</td>';
					html += '						<td id="jointStringDe" class="hidden">'+												decoded_initial_source_symptom_de+'#####'+											decoded_comparing_source_symptom_de+'</td>';
					html += '					</tr>';
					html += '					<tr>';
					html += '						<th>EN</th>';
					html += '						<td>'+decoded_initial_source_symptom_en+'</td>';
					html += '						<td id="decodedComparingStringEn">'+decoded_comparing_source_symptom_en+'</td>';
					html += '						<td id="jointStringEn" class="hidden">'+												decoded_initial_source_symptom_en+'#####'+											decoded_comparing_source_symptom_en+'</td>';
					html += '					</tr>';
					html += '				</tbody>';
				}
				html += '			</table>';
				html += '		</div>';
				html += '	</div>';
				html += '	<div class="row">';
				html += '		<div class="col-sm-12"><p class="common-error-text text-danger"></p></div>';
				html += '	</div>';
				html += '</div>';
				//pop up messages
				$(sendingElement).parents('div.comparing').nextUntil(".comparing").each(function(){
					if(!$(this).children().hasClass('previousConnection')){
						if($(this).hasClass("initialsConnectedCE"))
						{
							ce_allowed = false;
							earlierConnectionExistCe = 0;
							$("<div>This Comparative Symptom already has a Connect Edit. Please Disconnect.</div>").dialog();
						}
						else if($(this).hasClass("initialsConnectedPE"))
						{
							ce_allowed = false;
							earlierConnectionExistCe = 0;
							$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
						}
						else if($(this).hasClass("initialsConnectedPASTE"))
						{
							ce_allowed = false;
							earlierConnectionExistCe = 0;
							$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
						}
						else if($(this).hasClass("initialsConnectedCD"))
						{
							ce_allowed = false;
							earlierConnectionExistCe = 0;
							$("<div>This Comparative Symptom already has a Connect. Please Disconnect.</div>").dialog();
						}

					}else{
						earlierConnectionExistCe = 1;
						return false;
					}
				});
				function comparisonIdValueCe(){
					return ceComparativeId;
				}
				if(earlierConnectionExistCe == 1){
					if(ce_allowed == true)
					{
						arrayForEarlierConnection = [];
						$(sendingElement).parents('div.comparing').nextUntil("div.comparing").each(function(){
							if($(this).is(".initialsConnectedCD") || $(this).is(".initialsConnectedCE")){
								$(this).children().each(function(){
									var earlierConnectedId = comparisonIdValueCe();
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var type = 'connect';
										var free_flag = '0';
										if($(this).find('.disconnect') || $(this).find('.disconnectCE')){
											console.log("Earlier connect found");
											var thisIdArray = thisId.split("_");
											var comparativeIdToSend = thisIdArray[1];
											var initial_id_to_send = ceInitialId.replace("row","");
											var earlierConnectionCe = 1;
											var operationFlag = 2;
								    		arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initial_id_to_send,operationFlag:operationFlag,swapCe:swapCe});
										}		
									}else{
										openPopup =2;
										alreadyCePresent = 1;
										return false;
									}
								});
							}else{
								$(this).children().each(function(){
									var earlierConnectedId = comparisonIdValueCe();
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var type = 'connect';
										var free_flag = '1';
										if($(this).find('.disconnectPaste') || $(this).find('.disconnectPE')){
											console.log("Earlier connect found");
											var comparativeIdToSend = thisId.replace("row","");
											var initial_id_to_send = ceInitialId.replace("row","");
											console.log(comparativeIdToSend+' ****');
											var earlierConnectionCe = 2;
											var operationFlag = 5;
											arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initial_id_to_send,operationFlag:operationFlag,swapCe:swapCe});
										}		
									}

								});
							}
						});
						if(earlierExistCe != 0){
							var ceInitialIdReload = comparisonIdValueCe();
							reloadConnection(ceInitialIdReload);
						}
						$(sendingElement).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
							var earlierConnectedId = comparisonIdValueCe();
							if($(this).hasClass("comparativesConnectedCE"))
							{
								$(this).children().each(function(){
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var thisIdArray = thisId.split("_");
										var comparativeIdCE = thisIdArray[1];
										var initialIdCE = ceInitialId.replace("row","");	
										var free_flag = '0';
										var operationFlag = 1;		
										var earlierConnectionCe = 3;		
										//connections in comparatives class
										//earlier connection, value of operationFlag = 3
										//normal connection, value 1
										arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdCE,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initialIdCE,operationFlag:operationFlag,swapCe:swapCe});
									}else{		
										alreadyCePresent = 1;
										openPopup = 1;
										return false;
									}
									
								});
		 
							}
							else if($(this).hasClass("comparativesConnectedCD"))
							{
								$(this).children().each(function(){
									var thisId = $(this).attr('id');
									var thisIdArray = thisId.split("_");
									var comparativeIdCD = thisIdArray[1];
									var free_flag = '0';
									var initialIdCD = ceInitialId.replace("row","");
									var operationFlag = 1;	
									var earlierConnectionCe = 4;	
									if($(this).hasClass('previousConnection')){
										var operationFlag = 3;		
									}
									arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdCD,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initialIdCD,operationFlag:operationFlag,swapCe:swapCe});
								});
							}
							else if($(this).hasClass("comparativesConnectedPASTE"))
							{
								$(this).children().each(function(){
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var comparativeIdPaste = thisId.replace("row","");
										var operationFlag = 4;		
									}else{
										var thisId = $(this).attr('id');
										var thisIdArray = thisId.split("_");
										var comparativeIdPaste = thisIdArray[1];
										var operationFlag = 1;	
									}
									var initialIdPaste = ceInitialId.replace("row","");	
									var free_flag = '1';
									var earlierConnectionCe = 5;
									arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdPaste,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initialIdPaste,operationFlag:operationFlag,swapCe:swapCe});

								});
								
							}
							else if($(this).hasClass("comparativesConnectedPE"))
							{
								console.log("PE");
								$(this).children().each(function(){
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var comparativeIdPE = thisId.replace("row","");	
										var operationFlag = 4;	
									}else{
										var thisId = $(this).attr('id');
										var thisIdArray = thisId.split("_");
										var comparativeIdPE = thisIdArray[1];
										var operationFlag = 1;	
									}
									var free_flag = '1';
									var earlierConnectionCe = 6;
									var initialIdPE = ceInitialId.replace("row","");
									arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdPE,earlierConnectedId:earlierConnectedId,earlierConnectionCe:earlierConnectionCe,initialId:initialIdPE,operationFlag:operationFlag,swapCe:swapCe});
								});	
							}
						});
						//restricting if swap connection exist
						if($(sendingElement).parents('div.comparing').prevAll(".initial").first().find('.swapInitial').hasClass('active')){
							$(sendingElement).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
								if($(this).is("[class^=comparativesConnected]")){	
									$(this).children().each(function(){
										if(!$(this).hasClass('previousConnection')  && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
											openPopup = 3;
											alreadyCePresent = 1;
											$("<div>You cannot do a connection, because the Initial Symptom already has a Swap Connection</div>").dialog();
											return false;
										}
									});
								}
							});
						}
						//displaying popups
						if(openPopup ==1){
							$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
							$("<div>You cannot CE again in this section unless you disconnect the first CE.</div>").dialog();
						}else if(openPopup == 2){
							$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
							$("<div>You cannot do a Swap & Connect Edit, because this Symptom already has a Connection.</div>").dialog();
						}else if(openPopup == 3){
							$("#connect_edit_modal_loader .loading-msg").addClass('hidden');
							$("<div>You cannot do a connect edit here, because the Initial Symptom already has a Swap Connection</div>").dialog();
						}else{
							//here
						}
						if(alreadyCePresent==0){
							initialSymptom = $('#' + ceInitialId).find(".symptom").html();
							comparativeSymptom = $("."+thisId).find(".symptom").html();
							if(stringChange ==1){
								$("#fv_symptom_de").val(decoded_comparing_source_symptom_de);
								$("#fv_symptom_en").val(decoded_comparing_source_symptom_en);
							}else{
								$("#fv_symptom_de").val(decoded_initial_source_symptom_de);
								$("#fv_symptom_en").val(decoded_initial_source_symptom_en);
							}
							
							$("#connect_edit_modal_container").append(html);
							$("#connectEditModal").modal('show');
							if(!$("#connect_edit_modal_loader").hasClass('hidden'))
								$("#connect_edit_modal_loader").addClass('hidden');
							if($("#connect_edit_symptom_de_container").hasClass('hidden'))
								$("#connect_edit_symptom_de_container").removeClass('hidden');
							if($("#connect_edit_symptom_en_container").hasClass('hidden'))
								$("#connect_edit_symptom_en_container").removeClass('hidden');
							if($("#connect_edit_submit_btn").hasClass('hidden'))
								$("#connect_edit_submit_btn").removeClass('hidden');
						}					
					}
				}	
			}else{
				return false;
			}
		}
		function swapConnectCeOperation(ceModalDisplay,sendingElement){
			$(sendingElement).parents('div.comparing').nextUntil(".comparing").each(function(){
				if($(this).hasClass('initialsConnectedCE')|| $(this).hasClass('initialsConnectedCD'))
				{
					$(this).children().each(function(){
						if((!$(this).hasClass("previousConnection")) && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
							hasConnections = true;
							msg = "You cannot do a Swap & Connect Edit, because this Symptom already has a swap connection.";
							return false;
						}
					});			
				}
			});
			//If initial has ongoing connection, then no swap is possible.
			$(sendingElement).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
				if($(this).is("[class^=comparativesConnected]")){	
					$(this).children().each(function(){
						if(!$(this).hasClass('previousConnection')){
							hasConnectionsSwap = true;
							msgSwap = "You cannot do a connection, because the Initial Symptom already has ongoing connections";
							return false;
						}
					});
				}
			});``
			if(hasConnections)
				alert(msg);
			else if(hasConnectionsSwap){
				alert(msgSwap);
			}
			//The Initial Symtpom already has Connections i.e., comparativesConnected, so not allowed to Swap
			else 
			{
				swapCe = 1;
			}
			//The Initial Symtpom has no prior connections i.e., No comparativesConnected, hence allowed to Swap
			//Once swapped, this initial Symptom will be treated as an Initial.
			if(swapCe == 1)
			{
				ceModalDisplay = 1;
				stringChange = 1;
			}
			ceModalDisplayFn(ceModalDisplay,sendingElement);
		}
		function ceNormalOperation(ceModalDisplay,sendingElement){
			ceModalDisplayFn(ceModalDisplay,sendingElement);
		}
	}	
});
$.fn.connectEditFunction = function()
{
	// Connect Edit Starts here when Connect edit Modal submit button is clicked
	$(document).on('click', '.connect-edit-modal-submit-btn', function(e){
		var check_custom_ns = $("#check_custom_ns").val();
		//checking here earlier connection and establishing earlier connection with new initial
		if(arrayForEarlierConnection.length !== 0){
			var earlierConnectionCe = arrayForEarlierConnection[0]['earlierConnectionCe'];
			var operationFlag = arrayForEarlierConnection[0]['operationFlag'];
			var arrayForEarlierConnectionToSent = JSON.stringify(arrayForEarlierConnection);
		}
		if(language == 'de')
		{
			edited_initial = $("#fv_symptom_de").val();
			translation = $("#fv_symptom_en").val();
		}
		else if(language == 'en')
		{
			edited_initial = $("#fv_symptom_en").val();
			translation = $("#fv_symptom_de").val();
		}
		var edited_initial_symptom_de = $("#fv_symptom_de").val();
		var	edited_initial_symptom_en = $("#fv_symptom_en").val();
		var	joint_string_de = $("#jointStringDe").text();
		var	joint_string_en = $("#jointStringEn").text();
		var	ceYear = $("#ceYear").text();
		// removing <p> tag added by editor 
		edited_initial = edited_initial.replace(/^\<p\>/,"").replace(/\<\/p\>$/,"");
		initialSymptom = initialSymptom.replace(/^\<p\>/,"").replace(/\<\/p\>$/,"");
		translation = translation.replace(/^\<p\>/,"").replace(/\<\/p\>$/,"");
		$("#connectEditModal").modal('hide');
		if(edited_initial !== initialSymptom)//Checks if Initial Symptom is changed or not
		{
			var percentage;
			var comparativesConnectedCE_Found = 0;
			var ceComparativeIdFull = ceInitialId+"_"+ceComparativeId;
			var ceInitialIdToSend = ceInitialId.replace("row","");
			var connected_percentage = $("."+ceComparativeIdFull).find(".percentage").text();
			connected_percentage = connected_percentage.replace("%","");
			joint_string_de = joint_string_de.split("#####");
			initial_symptom_text_de = joint_string_de[0];
			comparing_symptom_text_de = joint_string_de[1];
			joint_string_en = joint_string_en.split("#####");
			initial_symptom_text_en = joint_string_en[0];
			comparing_symptom_text_en = joint_string_en[1];
			comparingStringCeDe = $("#"+ceComparativeIdFull).attr('data-comparing-symptom-de');
			comparingStringCeEn = $("#"+ceComparativeIdFull).attr('data-comparing-symptom-en');
			var decoded_comparing_source_symptom_de = (typeof(comparingStringCeDe) != "undefined" && comparingStringCeDe !== null && comparingStringCeDe != "") ? b64DecodeUnicode(comparingStringCeDe) : "";
			var decoded_comparing_source_symptom_en = (typeof(comparingStringCeEn) != "undefined" && comparingStringCeEn !== null && comparingStringCeEn != "") ? b64DecodeUnicode(comparingStringCeEn) : "";
			console.log(decoded_comparing_source_symptom_de);
			console.log(decoded_comparing_source_symptom_en);
			ceYear = ceYear.split("####");
			initial_year = ceYear[0];
			comparative_year = ceYear[1];
			var operation_type = "connectCE";
			if(swapCheck == 1){
				var edited_comparing_symptom_en = edited_initial_symptom_en;
				var edited_comparing_symptom_de = edited_initial_symptom_de;
				var operation_type = "connectCESwap";
				$('.'+ceInitialId).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
				{
					$(this).remove();	
				});
				$("#loaderCE").insertAfter($('.'+ceInitialId));
				$("#loaderCEOverlay").removeClass("hidden");
				setTimeout(connectEditSwapOperation,1000);
			} else {
				$('.'+ceInitialId).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
				{
					$(this).remove();	
				});
				$("#loaderCE").insertAfter($('.'+ceInitialId));
				$("#loaderCEOverlay").removeClass("hidden");
				setTimeout(connectEditOperation,1000); 
			}			
				
		}//End Initial Symptom is changed
		function connectEditOperation(){
			//Sending data for connect edit operation 
			$.ajax({
				async:false,
				type: "POST",
		      	url: "connect-edit-operation.php",
			    data: "initial_symptom_id="+ceInitialIdToSend+"&comparative_symptom_id="+ceComparativeId+"&cutoff_percentage="+cutoff_percentage+"&edited_initial_symptom_de="+edited_initial_symptom_de+"&edited_initial_symptom_en="+edited_initial_symptom_en+"&edited_comparing_symptom_de="+decoded_comparing_source_symptom_de+"&edited_comparing_symptom_en="+decoded_comparing_source_symptom_en+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type="+operation_type+"&arrayForEarlierConnection="+arrayForEarlierConnectionToSent+"&earlierConnectionCe="+earlierConnectionCe,
			    dataType: "JSON",
			    success: function(returnedData){
			    	console.log(returnedData);
			    	try {
						resultData = JSON.parse(returnedData.result_data); 
					} catch (e) {
						resultData = returnedData.result_data;
					}
		    		//Reloading the page here	
		    		if(check_custom_ns == 1)
						location.reload();
					else
		    			reloadConnection(ceInitialIdToSend);	
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
		}
		//swap function for connect edit
		function connectEditSwapOperation(){
			//starting swap
			$.ajax({
				async:false,
				type: "POST",
		      	url: "connect-swap-operation.php",
			    data: "initial_symptom_id="+ceInitialIdToSend+"&comparative_symptom_id="+ceComparativeId+"&cutoff_percentage="+cutoff_percentage+"&edited_initial_symptom_de="+initial_symptom_text_de+"&edited_initial_symptom_en="+initial_symptom_text_en+"&edited_comparing_symptom_de="+edited_comparing_symptom_de+"&edited_comparing_symptom_en="+edited_comparing_symptom_en+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type="+operation_type+"&operationFlag="+operationFlag+"&arrayForEarlierConnection="+arrayForEarlierConnectionToSent,
			    dataType: "JSON",
			    success: function(returnedData){
			    	console.log(returnedData);
			    	try {
						resultData = JSON.parse(returnedData.result_data); 
					} catch (e) {
						resultData = returnedData.result_data;
					}
		    		//Reloading the page here
	    			if(check_custom_ns == 1)
						location.reload();
					else
    					reloadConnection(ceComparativeId);	    
			    },
			    error: function(xhr, textStatus, error){
				    console.log(xhr.statusText);
				    console.log(textStatus);
				    console.log(error);
				}
			});
		}
	});//End CE Edit Click
	////////////////////WHEN DISCONNECT CE LINK ICON IS CLICKED/////////////////////////
	$(document).on("click", ".disconnectCE", function (ev)
	{
		var operationFlag = 1;
		var progress = 1;
		var connectionTypeForDisconnection = 'CE';
		var check_custom_ns = $("#check_custom_ns").val();
		var no_of_initials_combined = $("#no_of_initials_combined").val();
		var no_of_initials_combined = $("#no_of_initials_combined").val();
		if($(this).parents('div.comparing').find('.symptom-swap-connect-btn').hasClass("active")){
			//checking if swap connection is under comparativesConnectedCE
			//Id extraction
			var ids = $(this).parents('div.comparing').attr("id").split("_");
			disconnectid_initialId = ids[0];
			disconnectid_comparativelId = ids[1];
			var disconnectid_initialId_toSend = disconnectid_initialId.replace("row","");
			//Symptom Text extraction
			var comparative_symptom_text = $('#row' + disconnectid_initialId_toSend+'_'+disconnectid_comparativelId).find('.symptom').get(0).innerHTML;
			var initial_symptom_text = $('#row' + disconnectid_initialId_toSend).find('.symptom').get(0).innerHTML;
			var confirmation = confirm("Do you really want to Disconnect and Unswap this Symptom?");
			if(confirmation==true)
			{
				/////////////DISCONNECTING SWAP//////////////
				var operation_type  = 'disconnectSWAPCE';
				if($(this).parents('div.comparing').hasClass('previousConnection'))
				{
					var operation_type  = 'disconnectSWAPConnectedCE';
					//restricting if swap exists
					$(this).parents('div.comparativesConnectedCE').prevAll(".initial").first().nextUntil(".initial").each(function(){
						if($(this).is("[class^=comparativesConnected]")){	
							$(this).children().each(function(){
								if(!$(this).hasClass('previousConnection') && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
									$("<div>You cannot do a connection, because the Initial Symptom has already been swapped.</div>").dialog();
									progress = 0;
									return false;
								}
							});
						}
					});
				}
				if(progress == 1){
					$('.'+disconnectid_initialId).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
					{
						$(this).remove();	
					});
					$("#loaderCE").insertAfter($('.'+disconnectid_initialId));
					$("#loaderCEOverlay").removeClass("hidden");
					setTimeout(connectEditDisconnectSwapOperation,1000,disconnectid_initialId_toSend,disconnectid_comparativelId,cutoff_percentage,language, comparison_option,comparison_table_name,operation_type,comparative_symptom_text,initial_symptom_text,operationFlag,'comparing',0);	
				}else{
					return false;
				}		
			} 	
		}else if($(this).parents('div.initial').find('.symptom-swap-connect-btn').hasClass("active")){
			//checking if swap connection is under initialsConnectedCD
			//Id extraction
			var ids = $(this).parents('div.initial').attr("id").split("_");
			var swapped_initial_id = ids[1];
			var disconnectid_comparativelId = ids[0];
			var disconnectid_comparativelId = disconnectid_comparativelId.replace("row","");
			var main_initial = $(this).parents('div.initialsConnectedCE').prevAll("div.initial").first().attr("id");
			main_initial = main_initial.replace("row","");
			var comparative_symptom_text = $(this).parents('div.initial').find('.symptom').get(0).innerHTML;
			var initial_symptom_text = $('[class*=_' + disconnectid_comparativelId + ']').find('.symptom').get(0).innerHTML;
			var confirmation = confirm("Do you really want to Disconnect and Unswap this Symptom?");
			if(confirmation==true)
			{
				/////////////DISCONNECTING SWAP//////////////
				var operation_type  = 'disconnectSWAPCE';
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					var operation_type  = 'disconnectSWAPConnectedCE';
					var operationFlag  = 10;
					if($(".row"+disconnectid_comparativelId).find('.swapInitial').hasClass("active")){
						$("<div>You cannot do a connection, because the Initial Symptom has already been swapped.</div>").dialog();
						progress = 0;
						return false;
					}
				}
				if(progress == 1){
					$('.'+disconnectid_initialId).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
					{
						$(this).remove();	
					});
					$("#loaderCE").insertAfter($('.'+disconnectid_initialId));
					$("#loaderCEOverlay").removeClass("hidden");
					setTimeout(connectEditDisconnectSwapOperation,1000,disconnectid_comparativelId,swapped_initial_id,cutoff_percentage,language, comparison_option,comparison_table_name,operation_type,comparative_symptom_text,initial_symptom_text,operationFlag,'initial',main_initial);	
				}else{
					return false;
				}		
			}
		}else {
			//No swap
			var swap = false;
			var confirmation;
			language = $("#hidden_comparison_language").val();
			//Ask for Confirmation
			confirmation = confirm("Do you really want to Disconnect this Symptom");
			if(confirmation == true)
			{
				var disconnectid_initialId, disconnectid_comparativelId, tmpDisconnectedComparativeId;
				var thisDisconnectedComparativedId = [];
				//If disconnected from the comparativeCE
				if($(this).parents('div.comparativesConnectedCE').length>0)
				{
					var disconnectedFrom = "comparative";
					var thisId = $(this).parents('div.comparing').attr('id');
					var thisIdArray = thisId.split("_");
					ceComparativeId = thisIdArray[1];
					ceInitialId = thisIdArray[0];
					//restricting if swap exists
					$(this).parents('div.comparativesConnectedCE').prevAll(".initial").first().nextUntil(".initial").each(function(){
						if($(this).is("[class^=comparativesConnected]")){	
							$(this).children().each(function(){
								if(!$(this).hasClass('previousConnection') && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
									$("<div>You cannot do a connection, because the Initial Symptom has already been swapped.</div>").dialog();
									progress = 0;
									return false;
								}
							});
						}
					});
					if(progress == 1){
						//For combined sources, if disconnected then other operations takes place
						if($(this).parents('div.comparing').hasClass('previousConnection'))
						{
							var disconnect_operation = 'CE';
							var ceInitialIdToSend = ceInitialId.replace("row","");
							$.fn.disconnectEarlierConnection(ceInitialIdToSend,ceComparativeId,connectionTypeForDisconnection);
							$.fn.deleteSymptoms(ceInitialIdToSend,ceComparativeId,disconnect_operation);
							if(check_custom_ns == 1)
								location.reload();
							else
								reloadConnection(ceInitialId); 
							return false;
						}

						$('.'+ceInitialId).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
						{
							$(this).remove();	
						});

						var thisComparativeId = $(this).parents('div.comparing').attr("id");
						var initialParent = $(this).parents('.comparativesConnectedCE').prevAll(".initial").first();
						var sectionInitialId = initialParent.attr("id");
						var ids = thisComparativeId.split("_");
						disconnectid_initialId = ids[0];
						disconnectid_comparativelId = ids[1];
						var ceInitialIdToSend = disconnectid_initialId.replace("row","");
						$("#loaderCE").insertAfter($('.'+ceInitialId));
						$("#loaderCEOverlay").removeClass("hidden");
						setTimeout(connectEditDisconnectOperation,1000,ceInitialIdToSend,disconnectid_comparativelId,cutoff_percentage,language, comparison_option,comparison_table_name,'comparing',0);
					}else{
						return false;
					}
					
				}//End If
				
				//If the disconnect click is NOT done in the comparativesConnectedCE, but is done in the initialsConnectedCE.
				//Again we have to disconnect Initial Symptom from comparativesConnectedCE
				if($(this).parents('div.initialsConnectedCE').length>0)
				{
					//For combined sources, if disconnected then other operations takes place
					if($(this).parents('div.comparing').hasClass('previousConnection'))
					{
						var disconnect_operation = 'CE';
						var ids = $(this).parents('div.comparing').attr("id").split("_");
						disconnectid_initialId = ids[0];
						disconnectid_comparativelId = ids[1];
						var disconnectid_initialId_toSend = disconnectid_initialId.replace("row","");
						var main_initial = $(this).parents('div.initialsConnectedCE').prevAll("div.initial").first().attr("id");
						main_initial = main_initial.replace("row","");
						$.fn.disconnectEarlierConnection(disconnectid_initialId_toSend,disconnectid_comparativelId,connectionTypeForDisconnection);
						$.fn.deleteSymptoms(disconnectid_initialId_toSend,disconnectid_comparativelId,disconnect_operation);
						if(check_custom_ns == 1)
							location.reload();
						else
							reloadConnection(main_initial);
						return false;
					}
					else
					{
						var disconnectedFrom = "initial";
						var thisComparativeId = $(this).parents('div.initialsConnectedCE').prevAll("div.comparing").first().attr("id");
						var main_initial = $(this).parents('div.initialsConnectedCE').prevAll("div.initial").first().attr("id");
						main_initial = main_initial.replace("row","");
						disconnectedComparativeText = $("."+thisComparativeId).find(".symptom").text();
						var thisInitialId = $(this).parents(".initial").attr("id");
						ceInitialId = thisInitialId;
						thisDisconnectedComparativedId = thisComparativeId.split("_");
						ceComparativeId = thisDisconnectedComparativedId[1];
						tmpDisconnectedComparativeId = thisInitialId + "_" + thisDisconnectedComparativedId[1];
						var disconnectedInitialId = thisInitialId.replace("row","");
						$('.'+thisDisconnectedComparativedId[0]).not('[class^=initialsConnected] .initial').nextUntil(".initial").each(function()
						{
							$(this).remove();	
						});
						$("#loaderCE").insertAfter($('.'+thisDisconnectedComparativedId[0]));
						$("#loaderCEOverlay").removeClass("hidden");
						setTimeout(connectEditDisconnectOperation,1000,disconnectedInitialId, thisDisconnectedComparativedId[1],cutoff_percentage,language,comparison_option, comparison_table_name,'initial',main_initial);
					}	
				}//End If
				function connectEditDisconnectOperation(ceInitialIdToSend,disconnectid_comparativelId,cutoff_percentage,language, comparison_option,comparison_table_name,disconnectType,main_initial){
					$.ajax({
						async:false,
						type: "POST",
				      	url: "connect-edit-operation.php",
					    data: "initial_symptom_id="+ceInitialIdToSend+"&comparative_symptom_id="+disconnectid_comparativelId+"&cutoff_percentage="+cutoff_percentage+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type=disconnectCE",
					    dataType: "JSON",
					    success: function(returnedData){
					    	console.log(returnedData);
					    	try {
								resultData = JSON.parse(returnedData.result_data); 
							} catch (e) {
								resultData = returnedData.result_data;
							}
				    		//Reloading the page
		    				if(disconnectType == 'initial'){
		    					if(check_custom_ns == 1)
									location.reload();
								else
									reloadConnection(main_initial);	
		    				}else{
		    					if(check_custom_ns == 1)
									location.reload();
								else
									reloadConnection(ceInitialIdToSend);	
		    				}
					    },
					    error: function(xhr, textStatus, error){
						    console.log(xhr.statusText);
						    console.log(textStatus);
						    console.log(error);
						}
					});
				}
			}
			else
				return false;
		}
		
	});//End DisconnectCE click
}

//disconnection function
function connectEditDisconnectSwapOperation(disconnectid_initialId_toSend,disconnectid_comparativelId,cutoff_percentage,language, comparison_option,comparison_table_name,operation_type,comparative_symptom_text,initial_symptom_text,operationFlag,disconnectType,main_initial){
	$.ajax({
		async:false,
		type: "POST",
      	url: "connect-swap-operation.php",
	    data: "initial_symptom_id="+disconnectid_initialId_toSend+"&comparative_symptom_id="+disconnectid_comparativelId+"&cutoff_percentage="+cutoff_percentage+"&comparative_symptom_text="+comparative_symptom_text+"&initial_symptom_text="+initial_symptom_text+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type="+operation_type+"&operationFlag="+operationFlag,
	    dataType: "JSON",
	    success: function(returnedData){
	    	try {
				resultData = JSON.parse(returnedData.result_data); 
			} catch (e) {
				resultData = returnedData.result_data;
			}
    		//Reloading the page here
			if(disconnectType == 'initial'){
				if(check_custom_ns == 1)
					location.reload();
				else
					reloadConnection(main_initial);    
			}else{
				if(check_custom_ns == 1)
					location.reload();
				else
					reloadConnection(disconnectid_comparativelId);    
			}

	    },
	    error: function(xhr, textStatus, error){
		    console.log(xhr.statusText);
		    console.log(textStatus);
		    console.log(error);
		}
	});
}