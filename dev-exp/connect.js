$.fn.connectFunction = function()
{
	///////////////////////WHEN CONNECT LINK ICON IS CLICKED/////////////////////////
	$(document).on("click", ".connect", function (ev) {
		var check_custom_ns = $("#check_custom_ns").val();
		if(!($(this).parents('.comparing').prevAll(".initial").first().find(".gen-ns").hasClass("active"))){
			var language = $("#hidden_comparison_language").val();
			var hasConnections = false;
			var hasConnectionsSwap = false;
			var msg;
			var arrayForEarlierConnection = [];
			var arrayForEarlierConnectionToSent = [];
			var sendingElement = this;
			//Get the Initial and comparative Id's
			var thisId = $(this).parents('div.comparing').attr('id');
			var thisIdArray = thisId.split("_");
			//[0] represents "row". We could instead hard code the word "row"
			var comparativeId = thisIdArray[1];
			var initialId = thisIdArray[0];
			var initial_year = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-year");
			var comparative_year = $(this).parents('div.comparing').attr("data-year");
			var initial_quelle_type = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-quelle-type");
			var comparing_quelle_type = $(this).parents('div.comparing').attr("data-quelle-type");
			var earlierConnectionExist = 1;
			var earlierConnectionExistSwap = 1;
			var operationFlag = 0;	
			var openPopup = 0;	
			var previousConnectionInInitial = 0;	
			var previousSwapConnecectEdit = 0;	

			//A Comparative Symptom that has been pasted with Initial cannot have any kind of connection (C, CE, P, PE, SWAP) thereafter
			if($(this).parents('div.comparing').next(".initialsConnectedPASTE").length > 0 || $(this).parents('div.comparing').next(".initialsConnectedPE").length > 0){				
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if(!$(this).children().hasClass('previousConnection')){
						console.log("Not present");
						earlierConnectionExist = 0;
					}else{
						console.log("Present");
						earlierConnectionExist = 1;
						return false;
					}
				});
				if(earlierConnectionExist == 0)
					$("<div>This Comparative Symptom already has a Paste. Please Unpaste first!</div>").dialog();
			}

			//restricting if swap connection exist
			if($(this).parents('div.comparing').prevAll(".initial").first().find('.swapInitial').hasClass('active')){
				$(this).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
					if($(this).is("[class^=comparativesConnected]")){	
						$(this).children().each(function(){
							if(!$(this).hasClass('previousConnection') && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
								earlierConnectionExist = 0;
								$("<div>You cannot do a connection, because the Initial Symptom already has a Swap Connection</div>").dialog();
								return false;
							}
						});
					}
				});

				//opearation for connect edit previous swap
				if($(this).parents('div.comparing').prevAll(".initial").first().find('.connectEditText').hasClass('active')){
					previousSwapConnecectEdit = 1;
				}				
			}

			//main opeartion starts here
			//First we check whether Comparative is older than Initial
			var connectCase = "normal";
			var sendingElement = this;
			if(earlierConnectionExist == 1){
				if(parseInt(comparative_year) < parseInt(initial_year)){
					connectCase = "swap";
					$(this).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
						if($(this).is("[class^=comparativesConnected]")){	
							$(this).children().each(function(){
								if(!$(this).hasClass('previousConnection')){
									earlierConnectionExistSwap = 2;		
									console.log(earlierConnectionExistSwap);
									$("<div>You cannot do a connection, because the Initial Symptom already has ongoing connections</div>").dialog();
									return false;
								}
							});
						}
					});
					if(confirm("The Comparative Symptom is Older than the Initial Symptom."+"\n"+"Would you like to Swap and Connect?")){
						hasConnectionsSwap = true;
					}
					console.log(earlierConnectionExistSwap);
				}else if(parseInt(comparative_year) == parseInt(initial_year)){
					connectCase = "sameYear";
					$("#mydialog").html("The Comparative Symptom is from the same year as the Initial Symptom."+"<br />"+"Would you like to Swap and Connect?").dialog(
					{
	                    buttons: [
		                    {
		                    	text: "Yes",
						      	click: function() {
						      		hasConnectionsSwap = true;
			                		swapConnectOperation(sendingElement);
			                		$(this).dialog('close');
						      	}
		                    },
		                    {
		                    	text: "No",
						      	click: function() {
							        normalOperation(sendingElement);
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
						normalOperation(sendingElement);
					}
					break;
					case "swap":
					{		
						console.log("swap");
						swapConnectOperation(sendingElement);		
					}
					break;
					default:
						//No code
				}
				function swapConnectOperation(sendingElement){
					if(earlierConnectionExistSwap != 2){
						if(hasConnectionsSwap)
						{
							//If the comparative symtom was already connected or ce then it is prohibited from swapping.
							$(sendingElement).parents('div.comparing').nextUntil(".comparing").each(function(){
								if($(this).is('.initialsConnectedCE')|| $(this).is('.initialsConnectedCD'))
								{
									$(this).children().each(function(){
										if((!$(this).hasClass("previousConnection")) && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
											hasConnections = true;
											msg = "You cannot do a Swap & Connect, because this Symptom already has a Connection";
											return false;
										}
									});	
								}
							});

							if(hasConnections)
								alert(msg);
							else
							{
								arrayForEarlierConnection = [];
								arrayForEarlierConnectionToSent = [];

								$(sendingElement).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
									var earlierConnectedId = 0;
									//console.log(this);
									if($(this).hasClass("comparativesConnectedCE"))
									{	
										console.log("CE");
										$(this).children().each(function(){
											var thisId = $(this).attr('id');
											var thisIdArray = thisId.split("_");
											var comparativeIdCE = thisIdArray[1];
											var initialIdCE = initialId.replace("row","");	
											var free_flag = '0';
											var operationFlag = 1;	
											if($(this).hasClass('previousConnection')){
												var operationFlag = 3;		
											}
											//connections in comparatives class
											//earlier connection, value of operationFlag = 3
											//normal connection, value 1
											arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdCE,initialId:initialIdCE,earlierConnectedId:earlierConnectedId,operationFlag:operationFlag});
										});
									}
									else if($(this).hasClass("comparativesConnectedCD"))
									{
										$(this).children().each(function(){
											var thisId = $(this).attr('id');
											var thisIdArray = thisId.split("_");
											var comparativeIdCD = thisIdArray[1];
											var free_flag = '0';
											var initialIdCD = initialId.replace("row","");
											var operationFlag = 1;	
											if($(this).hasClass('previousConnection')){
												var operationFlag = 3;		
											}
											arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdCD,initialId:initialIdCD,earlierConnectedId:earlierConnectedId,operationFlag:operationFlag});
										});
										console.log("connect");	
									}
									else if($(this).hasClass("comparativesConnectedPASTE"))
									{
										console.log("paste");
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
											var initialIdPaste = initialId.replace("row","");	
											var free_flag = '1';
											arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdPaste,initialId:initialIdPaste,earlierConnectedId:earlierConnectedId,operationFlag:operationFlag});

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
											var initialIdPE = initialId.replace("row","");	
											arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdPE,initialId:initialIdPE,earlierConnectedId:earlierConnectedId,operationFlag:operationFlag});

										});	
									}
								});

								$(sendingElement).parents('div.comparing').nextUntil("div.comparing").each(function(){
									if($(this).is(".initialsConnectedCD") || $(this).is(".initialsConnectedCE")){
										$(this).children().each(function(){
											earlierConnectedId = comparativeId;
											if($(this).hasClass('previousConnection')){
												var thisId = $(this).attr('id');
												var initialIdC = initialId;	
												var free_flag = '0';
												console.log(thisId);
												console.log(earlierConnectedId);
												console.log(initialIdC);
												if($(this).find('.disconnect') || $(this).find('.disconnectCE')){
													console.log("Earlier connect found");
													var thisIdArray = thisId.split("_");
													var comparativeIdToSend = thisIdArray[1];
													var initial_id_to_send = initialIdC.replace("row","");
													var operationFlag = 2;
										    		arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,initialId:initial_id_to_send,operationFlag:operationFlag});
												}		
											}else{
												//pop up should be displayed here
												openPopup = 1;
											}
										});
									}else{
										$(this).children().each(function(){
											earlierConnectedId = comparativeId;
											if($(this).hasClass('previousConnection')){
												var thisId = $(this).attr('id');
												var initialIdP = initialId;
												var free_flag = '1';
												if($(this).find('.disconnectPaste') || $(this).find('.disconnectPE')){
													console.log("Earlier connect found");
													var comparativeIdToSend = thisId.replace("row","");
													var initial_id_to_send = initialIdP.replace("row","");
													var operationFlag = 5;
										    		arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,initialId:initial_id_to_send,operationFlag:operationFlag});
													//console.log(this);
												}		
											}

										});
									}
								});


								var initial_id_to_send = initialId.replace("row","");
								var comparative_symptom_text = $('#' + initialId+'_'+comparativeId).find('.symptom').get(0).innerHTML;
								var initial_symptom_text = $('#' + initialId).find('.symptom').get(0).innerHTML;

								if(arrayForEarlierConnection.length !== 0){
									var operationFlag = arrayForEarlierConnection[0]['operationFlag'];
									var arrayForEarlierConnectionToSent = JSON.stringify(arrayForEarlierConnection);
								}
								//console.log('arrayForEarlierConnection: '+arrayForEarlierConnectionToSent);

								//translated symptom extraction starts
								var initial_symptom_text_lang = "";
								var comapring_symptom_text_lang = "";
								var decoded_initial_symptom_text_lang = "";
								var decoded_comparing_symptom_text_lang = "";

								if(comparison_language == "de"){
									initial_symptom_text_lang = $("#"+initialId).attr('data-initial-symptom-en');
									comapring_symptom_text_lang = $('#' + initialId+'_'+comparativeId).attr('data-comparing-symptom-en');

								}else{
									initial_symptom_text_lang = $("#"+initialId).attr('data-initial-symptom-de');
									comapring_symptom_text_lang = $('#' + initialId+'_'+comparativeId).attr('data-comparing-symptom-de');
								}
								//decoding the symptom text
								decoded_initial_symptom_text_lang = (typeof(initial_symptom_text_lang) != "undefined" && initial_symptom_text_lang !== null && initial_symptom_text_lang != "") ? b64DecodeUnicode(initial_symptom_text_lang) : "";
								decoded_comparing_symptom_text_lang = (typeof(comapring_symptom_text_lang) != "undefined" && comapring_symptom_text_lang !== null && comapring_symptom_text_lang != "") ? b64DecodeUnicode(comapring_symptom_text_lang) : "";
								//translated symptom extraction ends

								//filteration of the symptom text if translation is active starts
								if($(sendingElement).parents('div.comparing').prevAll(".initial").first().find("#translation_display_row"+initial_id_to_send).hasClass("translated-symptom-div")){
									initial_symptom_text = symptomTextFilter(initial_symptom_text);
								}

								if($(sendingElement).parents('div.comparing').find("#translation_display_row"+initial_id_to_send+"_"+comparativeId).hasClass("translated-symptom-div")){
									comparative_symptom_text = symptomTextFilter(comparative_symptom_text);	
								}
								//filteration of the symptom text if translation is active ends
								
								
								/////////////BEGIN SWAP//////////////
								$.ajax({
									async:false,
									type: "POST",
							      	url: "connect-swap-operation.php",
								    data: "initial_symptom_id="+initial_id_to_send+"&comparative_symptom_id="+comparativeId+"&cutoff_percentage="+cutoff_percentage+"&comparative_symptom_text="+comparative_symptom_text+"&initial_symptom_text="+initial_symptom_text+"&initial_symptom_text_lang="+decoded_initial_symptom_text_lang+"&comparing_symptom_text_lang="+decoded_comparing_symptom_text_lang+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type=connectSWAP"+"&operationFlag="+operationFlag+"&arrayForEarlierConnection="+arrayForEarlierConnectionToSent+"&previousSwapConnecectEdit="+previousSwapConnecectEdit,
								    dataType: "JSON",
								    success: function(returnedData){
								    	console.log(returnedData);
								    	try {
											resultData = JSON.parse(returnedData.result_data); 
										} catch (e) {
											resultData = returnedData.result_data;
										}
										console.log(initial_id_to_send);
							    		//Reloading the page here
							    		if(check_custom_ns == 1)
											location.reload();
										else
											reloadConnection(comparativeId);	    

								    },
								    error: function(xhr, textStatus, error){
									    console.log(xhr.statusText);
									    console.log(textStatus);
									    console.log(error);
									}
								});
							}//end else
						}
					}
				}
				function normalOperation(sendingElement){
					var progress = 1;
					$(sendingElement).parents('div.comparing').nextUntil(".comparing").each(function(){
						if($(this).is('.initialsConnectedCE') || $(this).is('.initialsConnectedCD'))
						{
							$(this).children().each(function(){
								if((!$(this).hasClass("previousConnection")) && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
									$("<div>You cannot do a Swap & Connect, because this Symptom already has a Connection</div>").dialog();
									progress = 0;
									return false;
								}
							});	
						}
					});

					if(progress == 1){
						var comparativesConnectedCD_Found = 0;
						//Get the HTML row of the Connected Initial Symptom
						var initialHtml = $('#' + initialId).get(0).outerHTML;
						//Get this Comparing Row HTML to append it to the Initial Id
						var comparativeHtml = $(sendingElement).parents('div.comparing').get(0).outerHTML;
						//We will eliminate any "symptom-connections-btn" if present for this Comparative Html
						const $domComparative = $("<div>").html(comparativeHtml);
						$domComparative.find('.symptom-connections-btn').parent("li").remove();
						comparativeHtml = $domComparative.html();
						//Extracting info from the symptoms
						var connected_percentage = $(sendingElement).parents('div.comparing').find(".percentage").text();
						var connected_percentage = connected_percentage.replace("%","");
						var initial_symptom_text = $('#' + initialId).find('.symptom').get(0).innerHTML;
						var comparative_symptom_text = $(sendingElement).parents('div.comparing').find('.symptom').get(0).innerHTML;
						var comparing_quelle_code = $(sendingElement).parents('div.comparing').find('.source-code').get(0).innerHTML;
						var initial_quelle_code = $('#' + initialId).find('.source-code').get(0).innerHTML;			
						var initial_id_to_send = initialId.replace("row","");
						var comparing_year = $(sendingElement).parents('div.comparing').attr("data-year");
						var comparing_symptom_de = $(sendingElement).parents('div.comparing').attr("data-comparing-symptom-de");
						var comparing_symptom_en = $(sendingElement).parents('div.comparing').attr("data-comparing-symptom-en");
						var comparing_quelle_original_language = $(sendingElement).parents('div.comparing').attr("data-source-original-language");
						var initial_quelle_original_language = $(sendingElement).parents('div.comparing').prevAll(".initial").first().attr("data-source-original-language");
						var comparing_quelle_id = $(sendingElement).parents('div.comparing').attr("data-quell-id");
						var initial_year = $(sendingElement).parents('div.comparing').prevAll(".initial").first().attr("data-year");
						var initial_symptom_de = $(sendingElement).parents('div.comparing').attr("data-initial-symptom-de");
						var initial_symptom_en = $(sendingElement).parents('div.comparing').attr("data-initial-symptom-en");
						var initial_quelle_id = $(sendingElement).parents('div.comparing').prevAll(".initial").first().attr("data-quell-id");
						var connect_type = "connect";
						var comparison_language = language;
						var earlierExist = 0;
						//We will eliminate the symptom-search-btn from the Initial Symptom Row
						//Because we will append this Initial Symptom with all matching Comparative Symptoms in the document
						const $domInitial = $("<div>").html(initialHtml);
						$domInitial.find('.symptom-search-btn').parent("li").remove();
						$domInitial.find('.symptom-connections-btn').parent("li").remove();
						//Initial Symptom does not have Percentage - add the percenatage in this Initial Symptom html
						$domInitial.find('.percentage').text(connected_percentage+'%');
						initialHtml = $domInitial.html();
						//filteration of the symptom text if translation is active starts
						if($(sendingElement).parents('div.comparing').prevAll(".initial").first().find("#translation_display_row"+initial_id_to_send).hasClass("translated-symptom-div")){
							initial_symptom_text = symptomTextFilter(initial_symptom_text);
						}
						if($(sendingElement).parents('div.comparing').find("#translation_display_row"+initial_id_to_send+"_"+comparativeId).hasClass("translated-symptom-div")){
							comparative_symptom_text = symptomTextFilter(comparative_symptom_text);	
						}
						//filteration of the symptom text if translation is active ends
						//Saving the data in database
						$.fn.saveConnects(connect_type, comparativeId, initial_id_to_send, comparison_language,connected_percentage,  comparative_symptom_text, initial_symptom_text, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language);
						//connection for earlier connection **starts
						//establishing links between previous connections
						function comparisonIdValue(){
							return comparativeId;
						}
						$(sendingElement).parents('div.comparing').nextUntil("div.comparing").each(function(){
							//checking previous connection for paste and pe
							if($(this).is(".initialsConnectedPASTE") || $(this).is(".initialsConnectedPE"))
							{
								$(this).children().each(function(){
									var earlierConnectedId = comparisonIdValue();
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var type = 'paste';
										var free_flag = '1';
										if($(this).find('.disconnectPaste') || $(this).find('.disconnectPE')){
											var comparativeIdToSend = thisId.replace("row","");
											//function for saving earlier connected symptoms with the new initial symptom.
											$.fn.saveConnectsEarlier(comparativeIdToSend, earlierConnectedId, initial_id_to_send, initial_symptom_text, initial_quelle_code, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language,comparison_language,type,free_flag);
											earlierExist = earlierExist + 1;
										}	
									}

								});
							}
							//checking previous connection for connect and ce
							if($(this).is(".initialsConnectedCD") || $(this).is(".initialsConnectedCE"))
							{
								$(this).children().each(function(){
									var earlierConnectedId = comparisonIdValue();
									if($(this).hasClass('previousConnection')){
										var thisId = $(this).attr('id');
										var sub_connected_percentage = $(this).find(".percentage").text();
										var sub_connected_percentage = sub_connected_percentage.replace("%","");
										var type = 'connect';
										var free_flag = '0';
										if($(this).find('.disconnect') || $(this).find('.disconnectCE')){
											var thisIdArray = thisId.split("_");
											var comparativeIdToSend = thisIdArray[1];
											$.fn.saveConnectsEarlier(comparativeIdToSend, earlierConnectedId, initial_id_to_send, initial_symptom_text, initial_quelle_code, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language,comparison_language,type,free_flag,sub_connected_percentage);
											earlierExist = earlierExist + 1;
										}		
									}
								});
							}
						});
						if(earlierExist != 0){
							if(check_custom_ns == 1)
								location.reload();
							else
								reloadConnection(initial_id_to_send);
						}
						//connection for earlier connection **ends
						//If initialsConnected are open while connecting with Initial, we will close them first
						$(sendingElement).parents('div.comparing').nextUntil(".comparing").each(function(){
							if($(this).is("[class^=initialsConnected]"))
							{
								$(this).css("display", "none");
								$(this).prevAll('div.comparing').first().find('.symptom-connections-btn i').removeClass('fa-minus');
								$(this).prevAll('div.comparing').first().find('.symptom-connections-btn i').addClass('fa-plus');
							}
						});
						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						{
							if(comparativesConnectedCD_Found != 1){
								if($(this).hasClass("comparativesConnectedCD"))
								{
									$(this).append(comparativeHtml);
									comparativesConnectedCD_Found = 1;
								}
								if($('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.fas').hasClass("fa-plus")){
									$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.toggleInitial').trigger('click');
								}
								$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId);
							}
							if($(this).is('[class^=comparativesConnected]')){
								$(this).children().each(function(){
									if($(this).hasClass('previousConnection')){
										previousConnectionInInitial = 1;
									}
								});
							}
						});
						//reloading the page if the intial is combined source
						if(initial_quelle_type == 3 || comparing_quelle_type==3){
							if(check_custom_ns == 1)
								location.reload();
							else
								reloadConnection(initial_id_to_send);
						}
						//sorting the connections when connecting
						//After every initial symptom sorting order: connect-edit->connect->paste edit->paste.
						if(comparativesConnectedCD_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" && thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
								{
									if($('.'+initialId).find('.fas').hasClass("fa-minus"))
									{
										comparativeHtml = "<div class='comparativesConnectedCD'>"+comparativeHtml+"</div>";
									}
									else
									{
										comparativeHtml = "<div class='comparativesConnectedCD' style='display: none;'>"+comparativeHtml+"</div>";
									}
									//Declaring flags for sorting connection
									var ceVal = connectVal = pasteVal = peVal = 0;
									//Array to save the comparativeConnected classes
									connectionSortArray = [];
									connectionSortArray.length = 2;
									//Finding all the comparatives connected already present
									$(this).nextUntil("div.comparing").each(function(){
										if($(this).is('.comparativesConnectedPE')){
											peVal = 1;
											connectionSortArray[0] = this;
										}else if($(this).is('.comparativesConnectedPASTE')){
											pasteVal = 1;
											connectionSortArray[1] = this;
										}else {
											ceVal = 1;
											connectionSortArray[2] = this;
										}	
									});
									//Calling to sorting according to flag values
									if(ceVal ==1){
										connectionSort(connectionSortArray[2],comparativeHtml,'after');
										console.log("ce");
									}else if(peVal == 1){
										connectionSort(connectionSortArray[0],comparativeHtml,'before');
										console.log("pe");
									}else if(pasteVal == 1){
										connectionSort(connectionSortArray[1],comparativeHtml,'before');
										console.log("paste");
									}else{
										$(this).after(comparativeHtml);
										console.log("normal");
									}	
								}
							});
							$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId);
						}
						//After the Comparative Symptom is connected with the Initial. We have to find other Comparative Symptoms in the document.
						//Next we connect the Initial Symptom with any matching Comparative Symptom other than this Comparative Symptom.
						//But this time we will use a different Container name "initialsConnected". Because this container will contain the Initial Symptom.
						$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
							if(!$(this).parents().is("[class^=comparativesConnected]"))
							{					
								if($(this).next().hasClass("initialsConnectedCD"))
									$(this).next(".initialsConnectedCD").append(initialHtml);
								else if (!$(this).next().hasClass("initialsConnectedCD"))
								{
									if($(this).find('.fas').hasClass("fa-minus"))
									{
					      				$("<div class='initialsConnectedCD' style='display: block;'>"+initialHtml+"</div>").insertAfter($(this));
									}
									else
									{
					      				$("<div class='initialsConnectedCD'>"+initialHtml+"</div>").insertAfter($(this));
									}
								}				
							}
						});
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId);
						//marking tick 
						$("."+initialId).find(".marking").attr("checked","1");
						//gen-ns disabled
						$("."+initialId).find(".gen-ns").addClass("ns-disabled");
						$(sendingElement).parents('div.comparing').addClass("hidden");
					}else{
						return false;	
					}
				}

			}
		}		
	});//End Connect Click

	////////////////////WHEN DISCONNECT LINK ICON IS CLICKED/////////////////////////
	$(document).on("click", ".disconnect", function (ev)
	{
		var hasSwap;
		var operationFlag = 1;
		var progress = 1;
		var check_custom_ns = $("#check_custom_ns").val();
		//checking if swap connections exist
		if($(this).parents('div.comparing').find('.symptom-swap-connect-btn').length==1)
		{
			//checking if swap connection is under comparativesConnectedCD
			if($(this).parents('div.comparing').prevAll(".initial").length == 0){
				//Extracting the years
				var initial_year = $(this).parents('.comparativesConnectedCD').prevAll(".initial").first().attr("data-year");
				var comparative_year = $(this).parents('div.comparing').attr("data-year");
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
					var operation_type  = 'disconnectSWAP';
					if($(this).parents('div.comparing').hasClass('previousConnection'))
					{
						var operation_type  = 'disconnectSWAPConnected';
						//restricting if swap exists
						$(this).parents('div.comparativesConnectedCD').prevAll(".initial").first().nextUntil(".initial").each(function(){
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
						$.ajax({
							async:false,
							type: "POST",
					      	url: "connect-swap-operation.php",
						    data: "initial_symptom_id="+disconnectid_initialId_toSend+"&comparative_symptom_id="+disconnectid_comparativelId+"&cutoff_percentage="+cutoff_percentage+"&comparative_symptom_text="+comparative_symptom_text+"&initial_symptom_text="+initial_symptom_text+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type="+operation_type+"&operationFlag="+operationFlag,
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
									reloadConnection(disconnectid_comparativelId);   

						    },
						    error: function(xhr, textStatus, error){
							    console.log(xhr.statusText);
							    console.log(textStatus);
							    console.log(error);
							}
						});
					}else{
						return false;
					}
				}
			}
		}
		//checking if swap connection is under initialsConnectedCD
		else if($(this).parents('div.initial').find('.symptom-swap-connect-btn').hasClass("active")){
				//Id extraction
				var ids = $(this).parents('div.initial').attr("id").split("_");
				var swapped_initial_id = ids[1];
				var disconnectid_comparativelId = ids[0];
				var disconnectid_comparativelId = disconnectid_comparativelId.replace("row","");
				var main_initial = $(this).parents('div.initialsConnectedCD').prevAll(".initial").first().attr("id");
				main_initial = main_initial.replace("row","");
				var comparative_symptom_text = $(this).parents('div.initial').find('.symptom').get(0).innerHTML;
				var initial_symptom_text = $('[class*=_' + disconnectid_comparativelId + ']').find('.symptom').get(0).innerHTML;
				var operation_type  = 'disconnectSWAP';
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					var operation_type  = 'disconnectSWAPConnected';
					var operationFlag = 10;
					if($(".row"+disconnectid_comparativelId).find('.swapInitial').hasClass("active")){
						$("<div>You cannot do a connection, because the Initial Symptom has already been swapped.</div>").dialog();
						progress = 0;
						return false;
					}
				}
				if(progress == 1){
					var confirmation = confirm("Do you really want to Disconnect and Unswap this Symptom?");
					if(confirmation==true)
					{
						/////////////DISCONNECTING SWAP//////////////
						$.ajax({
							async:false,
							type: "POST",
					      	url: "connect-swap-operation.php",
						    data: "initial_symptom_id="+disconnectid_comparativelId+"&comparative_symptom_id="+swapped_initial_id+"&cutoff_percentage="+cutoff_percentage+"&comparative_symptom_text="+comparative_symptom_text+"&initial_symptom_text="+initial_symptom_text+"&comparison_language="+language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type="+operation_type+"&operationFlag="+operationFlag,
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
									reloadConnection(main_initial);	    

						    },
						    error: function(xhr, textStatus, error){
							    console.log(xhr.statusText);
							    console.log(textStatus);
							    console.log(error);
							}
						});
					}
				}else{
					return false;	
				}			
		}
		//no swap connection exist
		else
		{
			var confirmation;
			var connectionTypeForDisconnection = 'connect';
			confirmation = confirm("Do you really want to Disconnect this Symptom");
			if(confirmation == true)
			{
				var disconnectid_initialId, disconnectid_comparativelId, disconnectedComparativeId, disconnectedInitialId, tmpDisconnectedComparativeId;
				var thisDisconnectedComparativedId = [];
				//If disconnected from the comparative appearing under initial
				if($(this).parents('div.comparativesConnectedCD').length>0)
				{
					//restricting if swap exists
					$(this).parents('div.comparativesConnectedCD').prevAll(".initial").first().nextUntil(".initial").each(function(){
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
						var thisComparativeId = $(this).parents('div.comparing').attr("id");
						var initialParent = $(this).parents('.comparativesConnectedCD').prevAll(".initial").first();
						var ids = $(this).parents('div.comparing').attr("id").split("_");
						disconnectid_initialId = ids[0];
						disconnectid_comparativelId = ids[1];
						var disconnectid_initialId_toSend = disconnectid_initialId.replace("row","");
						//For combined sources, if disconnected then other operations takes place
						if($(this).parents('div.comparing').hasClass('previousConnection'))
						{	
							var disconnect_operation = 'connect';
							$.fn.disconnectEarlierConnection(disconnectid_initialId_toSend,disconnectid_comparativelId,connectionTypeForDisconnection);
							$.fn.deleteSymptoms(disconnectid_initialId_toSend,disconnectid_comparativelId,disconnect_operation);
							if(check_custom_ns == 1)
								location.reload();
							else
								reloadConnection(disconnectid_initialId_toSend);
							
						}else{
							$.fn.deleteSymptoms(disconnectid_initialId_toSend,disconnectid_comparativelId,'connect');
						}
						//(1) Disconnect this comparative symptom i.e., inside compartivesConnected from Initial Symptom above
						if($(this).parents("div.comparativesConnectedCD").children(".comparing").length == 1){
							$(this).parents('div.comparativesConnectedCD').remove();
						}else{
							$(this).parents('div.comparing').remove();

						}
						initialParent.nextAll("#"+ thisComparativeId).first().removeClass("hidden");
						//We have to disconnect this Initial Symptom from initialsConnected under Comparative Symptoms in the document
						$(".initialsConnectedCD").each(function()
						{
							var thisInitialId = $(this).prevAll(".initial").first().attr("id");
							var thisRemovingId = thisInitialId+"_"+disconnectid_comparativelId //This is actually the Comparative Symptom
							//Under which we have the initialsConnect, that contains the initial symptpom with id disconnectid_initialId
							$(this).prevAll("."+thisRemovingId).first().nextUntil(".comparing").each(function()
							{
								if($(this).hasClass("initialsConnectedCD"))
								{
									$(this).find("."+disconnectid_initialId).remove();
									if($(this).children().length == 0)
										$(this).remove();
								}	
							});

						});
						//marking tick 
						markingFunctionOnDisconnection(disconnectid_initialId);
						genNsFunctionOnDisconnection(disconnectid_initialId);
						$.fn.initialsWithComparativesDisconnectedBelow(initialParent.attr("id"));
						$.fn.comparativesWithInitialsDisconnectedBelow(disconnectid_comparativelId, thisComparativeId);
					}else{
						return false;
					}
				}//End If
				//If disconnected from the initial appearing under that comparative
				if($(this).parents('div.initialsConnectedCD').children().length>0)
				{
					//For combined sources, if disconnected then other operations takes place
					if($(this).parents('div.comparing').hasClass('previousConnection'))
					{
						var ids = $(this).parents('div.comparing').attr("id").split("_");
						var disconnect_operation = 'connect';
						disconnectid_initialId = ids[0];
						disconnectid_comparativelId = ids[1];
						var disconnectid_initialId_toSend = disconnectid_initialId.replace("row","");
						var main_initial = $(this).parents('div.initialsConnectedCD').prevAll("div.initial").first().attr("id");
						main_initial = main_initial.replace("row","");
						$.fn.disconnectEarlierConnection(disconnectid_initialId_toSend,disconnectid_comparativelId,connectionTypeForDisconnection);
						$.fn.deleteSymptoms(disconnectid_initialId_toSend,disconnectid_comparativelId,disconnect_operation);	
						if(check_custom_ns == 1)
							location.reload();
						else
							reloadConnection(main_initial);
					}
					else
					{
						disconnectedComparativeId = $(this).parents('div.initialsConnectedCD').prevAll("div.comparing").first().attr("id");
						disconnectedComparativeText = $("#"+disconnectedComparativeId).find(".symptom").text();
						disconnectedInitialId = $(this).parents(".initial").attr("id");
						thisDisconnectedComparativeId = disconnectedComparativeId.split("_");
						//Below is the main Comparative Symtpom Id (i.e., inside comparativesConnectedCD)
						tmpDisconnectedComparativeId = disconnectedInitialId + "_" + thisDisconnectedComparativeId[1];
						var disconnectid_initialId_toSend = disconnectedInitialId.replace("row","");
						var disconnected_comparativeId_toSend = thisDisconnectedComparativeId[1];
						$.fn.deleteSymptoms(disconnectid_initialId_toSend,disconnected_comparativeId_toSend,'connect');
						$(this).parents(".initial").remove();
						$(this).prevAll(".comparing").first().nextUntil(".initial").each(function(){
							if($(this).hasClass("initialsConnectedCD"))
							{
								if($(this).children().length == 0)
									$(this).remove();
							}
						});
						//Next we remove the Comparative Symptom from "comparativesConnected"
						$(".comparativesConnectedCD").each(function()
						{
							if($(this).siblings().hasClass(tmpDisconnectedComparativeId))
							{
								$(this).find("."+tmpDisconnectedComparativeId).remove();
								$(this).nextAll("."+tmpDisconnectedComparativeId).first().removeClass("hidden");
								if($(this).children().length == 0)
									$(this).remove();	
								$.fn.initialsWithComparativesDisconnectedBelow(disconnectedInitialId);	

							}
						});
						//Next we remove this initialSymptom from all initialsConnected div's
						$(".initialsConnectedCD").each(function(){
							var cId = $(this).prevAll("div.comparing").first().attr("id");
							//Initial Ids can be same for various Comparative Symptoms. Check if comparative id is same.
							if (typeof cId !== 'undefined' && cId !== false)
							{
								if(cId.includes(thisDisconnectedComparativeId[1]))
								{
									$(this).find("."+disconnectedInitialId).remove();
									if($(this).children().length == 0)
										$(this).remove();
								}
							}
						});
						//marking tick 
						markingFunctionOnDisconnection(disconnectedInitialId);
						genNsFunctionOnDisconnection(disconnectedInitialId);
						$.fn.comparativesWithInitialsDisconnectedBelow(thisDisconnectedComparativeId[1], tmpDisconnectedComparativeId);
					}
						
				}//End If
			}
			else
				return false;
		}
		return false;	
	});//End disconnect click
}//End main function