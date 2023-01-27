$.fn.pasteFunction = function()
{
	///////////////////////WHEN PASTE P ICON IS CLICKED//////////////////////////
	$(document).on("click", ".symptom-paste-btn", function (ev) {
		var check_custom_ns = $("#check_custom_ns").val();
		if(!($(this).parents('.comparing').prevAll(".initial").first().find(".gen-ns").hasClass("active"))){
			var earlierConnectionExist = 1;
			var earlierConnectionExistPe = 1;
			var swapInInitial = 0;
			//Checking conditions
			if($(this).parents('div.comparing').next(".initialsConnectedPASTE").length > 0)
			{
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if(!$(this).children().hasClass('previousConnection')){
						console.log("Not present");
						earlierConnectionExist = 0;
						earlierConnectionExistPe = 0;
					}else{
						console.log("Present");
						earlierConnectionExist = 1;
						return false;
					}
				});
			}
			if($(this).parents('div.comparing').next(".initialsConnectedPE").length > 0)
			{
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if(!$(this).children().hasClass('previousConnection')){
						console.log("Not present");
						earlierConnectionExist = 0;
						earlierConnectionExistPe = 0;	
					}else{
						console.log("Present");
						earlierConnectionExist = 1;
						return false;
					}
				})

			}
			if($(this).parents('div.comparing').next(".initialsConnectedCE").length > 0)
			{
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if(!$(this).children().hasClass('previousConnection')){
						console.log("Not present");
						earlierConnectionExist = 0;
					}else if((!$(this).hasClass("previousConnection")) && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
						swapInInitial = 1;
						return false;
					}else{
						console.log("Present");
						earlierConnectionExist = 1;
						return false;
					}
				});
			} else if($(this).parents('div.comparing').next(".initialsConnectedCD").length > 0) {
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
			}
			if(earlierConnectionExistPe == 0){
				$("<div>This Comparative Symptom already has a Paste.<br /> Please Unpaste first.</div>").dialog();
			}else if(earlierConnectionExist == 0){
				$("<div>This Comparative Symptom already has a Connection.<br /> Please disconnect first.</div>").dialog();
			}
			//restricting if swap connection exist
			if($(this).parents('div.comparing').prevAll(".initial").first().find('.swapInitial').hasClass('active')){
				$(this).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
					if($(this).is("[class^=comparativesConnected]")){	
						$(this).children().each(function(){
							//console.log(this);
							if(!$(this).hasClass('previousConnection') && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
								earlierConnectionExist = 0;
								$("<div>You cannot do a paste here, because the Initial Symptom already has a Swap Connection</div>").dialog();
								return false;
							}
						});
					}
				});
			}
			//main opeartion starts here
			if(earlierConnectionExist == 1)
			{
				var comparativesConnectedPASTE_Found = 0;
				var previousConnectionInInitial = 0;
				//Get the Initial and comparative Id's
				var thisId = $(this).parents('div.comparing').attr('id');
				var thisIdArray = thisId.split("_");
				//[0] represents "row". We could instead hard code the word "row"
				var comparativeId = thisIdArray[1];
				var initialId = thisIdArray[0];
				var language = $("#hidden_comparison_language").val();
				//Get the HTML row of the Connected Initial Symptom
				var initialHtml = $('#' + initialId).get(0).outerHTML;
				//Get this Comparing Row HTML to append it to the Initial Id
				var comparativeHtml = $(this).parents('div.comparing').get(0).outerHTML;
				//We will eliminate any "symptom-connections-btn" if present for this Comparative Html
				const $domComparative = $("<div>").html(comparativeHtml);
				$domComparative.find('.symptom-connections-btn').parent("li").remove();
				comparativeHtml = $domComparative.html();
				//Extracting info from the symptoms
				var connected_percentage = $(this).parents('div.comparing').find(".percentage").text();
				var connected_percentage = connected_percentage.replace("%","");
				var initial_symptom_text = $('#' + initialId).find('.symptom').get(0).innerHTML;
				var comparative_symptom_text = $(this).parents('div.comparing').find('.symptom').get(0).innerHTML;
				var comparing_quelle_code = $(this).parents('div.comparing').find('.source-code').get(0).innerHTML;
				var initial_quelle_code = $('#' + initialId).find('.source-code').get(0).innerHTML;			
				var initial_id_to_send = initialId.replace("row","");
				var comparing_year = $(this).parents('div.comparing').attr("data-year");
				var comparing_symptom_de = $(this).parents('div.comparing').attr("data-comparing-symptom-de");
				var comparing_symptom_en = $(this).parents('div.comparing').attr("data-comparing-symptom-en");
				var comparing_quelle_original_language = $(this).parents('div.comparing').attr("data-source-original-language");
				var initial_quelle_original_language = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-source-original-language");
				var comparing_quelle_id = $(this).parents('div.comparing').attr("data-quell-id");
				var initial_year = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-year");
				var initial_symptom_de = $(this).parents('div.comparing').attr("data-initial-symptom-de");
				var initial_symptom_en = $(this).parents('div.comparing').attr("data-initial-symptom-en");
				var initial_quelle_id = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-quell-id");
				var initial_quelle_type = $(this).parents('div.comparing').prevAll(".initial").first().attr("data-quelle-type");
				var comparing_quelle_type = $(this).parents('div.comparing').attr("data-quelle-type");
				var connect_type = "paste";
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
				if($(this).parents('div.comparing').prevAll(".initial").first().find("#translation_display_row"+initial_id_to_send).hasClass("translated-symptom-div")){
					initial_symptom_text = symptomTextFilter(initial_symptom_text);
				}
				if($(this).parents('div.comparing').find("#translation_display_row"+initial_id_to_send+"_"+comparativeId).hasClass("translated-symptom-div")){
					comparative_symptom_text = symptomTextFilter(comparative_symptom_text);	
				}
				//filteration of the symptom text if translation is active ends
				$.fn.saveConnects(connect_type, comparativeId, initial_id_to_send, comparison_language,connected_percentage,  comparative_symptom_text, initial_symptom_text, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language, comparing_quelle_id, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language);
				//establishing links between previous connections
				function comparisonIdValue(){
					return comparativeId;
				}
				$(this).parents('div.comparing').nextUntil("div.comparing").each(function(){
					if($(this).is(".initialsConnectedPASTE") || $(this).is(".initialsConnectedPE"))
					{
						$(this).children().each(function(){
							earlierConnectedId = comparisonIdValue();
							if($(this).hasClass('previousConnection')){
								console.log(this);
								var thisId = $(this).attr('id');
								var type = 'paste';
								var free_flag = '1';
								if($(this).find('.disconnectPaste') || $(this).find('.disconnectPE')){
									var comparativeIdToSend = thisId.replace("row","");
									$.fn.saveConnectsEarlier(comparativeIdToSend, earlierConnectedId, initial_id_to_send, initial_symptom_text, initial_quelle_code, initial_year, initial_symptom_de, initial_symptom_en, initial_quelle_id, initial_quelle_original_language,comparison_language,type,free_flag);
									earlierExist = earlierExist + 1;
								}
							}

						});
					}
					if($(this).is(".initialsConnectedCD") || $(this).is(".initialsConnectedCE")){
						$(this).children().each(function(){
							earlierConnectedId = comparisonIdValue();
							if($(this).hasClass('previousConnection')){
								console.log(this);
								var thisId = $(this).attr('id');
								var sub_connected_percentage = $(this).find(".percentage").text();
								var sub_connected_percentage = sub_connected_percentage.replace("%","");
								var type = 'connect';
								var free_flag = '0';
								console.log(thisId);
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
				//If initialsConnected are open while connecting with Initial, we will close them first
				$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					if($(this).hasClass("initialsConnectedCD"))
						$(this).css("display", "none");
					if($(this).hasClass("initialsConnectedCE"))
						$(this).css("display", "none");
					if($(this).hasClass("initialsConnectedPASTE"))
						$(this).css("display", "none");
				});
				//(1) First Connect Comparative with Initial above
				//Here we are writing 2 statements to ensure that comparativesConnectedPASTE are together
				//so that comparativesConnectedCE does not fall in between
				$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
				{
					if(comparativesConnectedPASTE_Found != 1){
						if($(this).hasClass("comparativesConnectedPASTE"))
						{
							$(this).append(comparativeHtml);
							comparativesConnectedPASTE_Found = 1;
						}
						if($('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.fas').hasClass("fa-plus")){
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.toggleInitial').trigger('click');
						}
						$.fn.initialsWithComparativesConnectedBelow('PASTE', initialId, thisId);
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
				if(comparativesConnectedPASTE_Found === 0)
				{
					$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
					{
						var thisConnectedParent = $(this).parent().attr("class");
						if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" &&thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
						{
							if($('.'+initialId).find('.fas').hasClass("fa-minus"))
							{
								comparativeHtml = "<div class='comparativesConnectedPASTE'>"+comparativeHtml+"</div>";
							}
							else
							{
								comparativeHtml = "<div class='comparativesConnectedPASTE' style='display: none;'>"+comparativeHtml+"</div>";
							}
							//Declaring flags for sorting connection
							var ceVal = connectVal = pasteVal = peVal = 0;
							var connectionSortType = "after";
							//Array to save the comparativeConnected classes
							connectionSortArray = [];
							connectionSortArray.length = 2;
							//Finding all the comparatives connected already present
							$(this).nextUntil("div.comparing").each(function(){
								if($(this).is('.comparativesConnectedPE')){
									peVal = 1;
									connectionSortArray[0] = this;
								}else if($(this).is('.comparativesConnectedCD')){
									connectVal = 1;
									connectionSortArray[1] = this;
								}else {
									ceVal = 1;
									connectionSortArray[2] = this;
								}	
							});
							//Calling to sorting according to flag values
							if(peVal ==1){
								connectionSort(connectionSortArray[0],comparativeHtml,connectionSortType);
								console.log("pe");
							}else if(connectVal == 1){
								connectionSort(connectionSortArray[1],comparativeHtml,connectionSortType);
								console.log("connect");
							}else if(ceVal == 1){
								connectionSort(connectionSortArray[2],comparativeHtml,connectionSortType);
								console.log("ce");
							}else{
								$(this).after(comparativeHtml);
								console.log("normal");
							}
						}
					});
					$.fn.initialsWithComparativesConnectedBelow('PASTE', initialId, thisId);
				}
				//Next we connect the Initial Symptom with any matching Comparative Symptom other than this
				$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
					if(!$(this).parents().is("[class^=comparativesConnected]"))
					{					
						if($(this).next().hasClass("initialsConnectedPASTE"))
							$(this).next(".initialsConnectedPASTE").append(initialHtml);
						else if (!$(this).next().hasClass("initialsConnectedPASTE"))
						{
							if($(this).find('.fas').hasClass("fa-minus"))
							{
			      				$("<div class='initialsConnectedPASTE' style='display: block;'>"+initialHtml+"</div>").insertAfter($(this));
							}
							else
							{
			      				$("<div class='initialsConnectedPASTE'>"+initialHtml+"</div>").insertAfter($(this));
							}
						}				
					}
				});
				$.fn.comparativesWithInitialsConnectedBelow('PASTE', initialId, comparativeId, thisId);
				//marking tick 
				$("."+initialId).find(".marking").attr("checked","1");
				//gen-ns disabled
				$("."+initialId).find(".gen-ns").addClass("ns-disabled");
				$(this).parents('div.comparing').addClass("hidden");

			}// End if condition for Paste Click
		}		
	});//End PASTE Click

	////////////////////WHEN DISCONNECT PASTE ICON IS CLICKED/////////////////////////
	$(document).on("click", ".disconnectPaste", function (ev)
	{
		var confirmation;
		var check_custom_ns = $("#check_custom_ns").val();
		confirmation = confirm("Do you really want to Unpaste this Symptom");
		if(confirmation == true)
		{
			var disconnectid_initialId, disconnectid_comparativelId, disconnectedComparativeId, disconnectedInitialId, tmpDisconnectedComparativeId;
			if($(this).parents('div.comparativesConnectedPASTE').length>0)
			{
				//For combined sources, if disconnected then other operations takes place
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					var thisComparativeId = $(this).parents('div.initial').attr("id");
					thisComparativeId = thisComparativeId.replace("row","");
					var initialParent = $(this).parents('.comparativesConnectedPASTE').prevAll(".initial").first();
					var sectionInitialId = initialParent.attr("id");
					sectionInitialId = sectionInitialId.replace("row","");
					var operation = 'paste';
					$.fn.deleteSymptoms(thisComparativeId,sectionInitialId,operation);
					//Reloading the page
    				if(check_custom_ns == 1)
						location.reload();
					else
						reloadConnection(sectionInitialId);	
				}
				else
				{
					var thisComparativeId = $(this).parents('div.comparing').attr("id");
					var initialParent = $(this).parents('.comparativesConnectedPASTE').prevAll(".initial").first();
					var ids = $(this).parents('div.comparing').attr("id").split("_");
					disconnectid_initialId = ids[0];
					disconnectid_initialIdToSend = disconnectid_initialId.replace("row","");
					disconnectid_comparativelId = ids[1];
					$.fn.deleteSymptoms(disconnectid_initialIdToSend, disconnectid_comparativelId,'paste');
					//(1) Disconnect this comparative symptom i.e., inside compartivesConnected from Initial Symptom above
					if($(this).parents('div.comparativesConnectedPASTE').children(".comparing").length == 1){
						$(this).parents('div.comparativesConnectedPASTE').remove();
					}
					else{
						$(this).parents('div.comparing').remove();
					}
					//marking tick
					markingFunctionOnDisconnection(disconnectid_initialId);
					genNsFunctionOnDisconnection(disconnectid_initialId);
					initialParent.nextAll("#"+ thisComparativeId).first().removeClass("hidden");
					//We have to disconnect this Initial Symptom from initialsConnected under Comparative Symptoms in the document
					$(".initialsConnectedPASTE").each(function()
					{
						var thisInitialId = $(this).prevAll(".initial").first().attr("id");
						var thisRemovingId = thisInitialId+"_"+disconnectid_comparativelId //This is actually the Comparative Symptom
						//Under which we have the initialsConnect, that contains the initial symptpom with id disconnectid_initialId
						$(this).prevAll("."+thisRemovingId).first().nextUntil(".comparing").each(function()
						{
							if($(this).hasClass("initialsConnectedPASTE"))
							{
								$(this).find("."+disconnectid_initialId).remove();
								if($(this).children().length == 0)
									$(this).remove();
							}	
						});
					});
					$.fn.initialsWithComparativesDisconnectedBelow(initialParent.attr("id"));
					$.fn.comparativesWithInitialsDisconnectedBelow(disconnectid_comparativelId, thisComparativeId);
				}	
			}//End If
			//Disconnecting Initial Symptoms that are inside initialsConnected from Comparative Symptom above
			if($(this).parents('div.initialsConnectedPASTE').children().length>0)
			{
				//For combined sources, if disconnected then other operations takes place
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					disconnectedComparativeId = $(this).parents('div.initialsConnectedPASTE').prevAll("div.comparing").first().attr("id");
					thisDisconnectedComparativedId = disconnectedComparativeId.split("_");
					disconnectid_comparativelId = thisDisconnectedComparativedId[1];

					disconnectedInitialId = $(this).parents(".initial").attr("id");
					disconnectedInitialIdToSend = disconnectedInitialId.replace("row","");
					var operation = 'paste';
					$.fn.deleteSymptoms(disconnectedInitialIdToSend, disconnectid_comparativelId,operation);	
					var main_initial = $(this).parents('div.initialsConnectedPASTE').prevAll("div.initial").first().attr("id");
					main_initial = main_initial.replace("row","");
					//Reloading the page
    				if(check_custom_ns == 1)
						location.reload();
					else
						reloadConnection(main_initial);	
				}
				else
				{
					disconnectedComparativeId = $(this).parents('div.initialsConnectedPASTE').prevAll("div.comparing").first().attr("id");
					disconnectedComparativeText = $("#"+disconnectedComparativeId).find(".symptom").text();
					disconnectedInitialId = $(this).parents(".initial").attr("id");
					thisDisconnectedComparativedId = disconnectedComparativeId.split("_");
					tmpDisconnectedComparativeId = disconnectedInitialId + "_" + thisDisconnectedComparativedId[1];
					disconnectid_comparativelId = thisDisconnectedComparativedId[1];
					disconnectedInitialIdToSend = disconnectedInitialId.replace("row","");
					$.fn.deleteSymptoms(disconnectedInitialIdToSend, disconnectid_comparativelId,'paste');				
					$(this).parents(".initial").remove();
					$(this).prevAll(".comparing").first().nextUntil(".initial").each(function(){
						if($(this).hasClass("initialsConnectedPASTE"))
						{
							if($(this).children().length == 0)
								$(this).remove();
						}
					});
					//Next we remove the Comparative Symptom from "comparativesConnected"
					$(".comparativesConnectedPASTE").each(function(){
						if($(this).siblings().hasClass(tmpDisconnectedComparativeId))
						{
							$(this).find("."+tmpDisconnectedComparativeId).remove();
							$(this).nextAll("."+tmpDisconnectedComparativeId).first().removeClass("hidden");

							if($(this).children().length == 0)
								$(this).remove();
							$.fn.initialsWithComparativesDisconnectedBelow(disconnectedInitialId);

						}
					});
					$(".initialsConnectedPASTE").each(function(){
						var cId = $(this).prevAll("div.comparing").first().attr("id");
						//Initial Ids can be same for various Comparative Symptoms. Check if comparative id is same.
						if(cId.includes(thisDisconnectedComparativedId[1]))
						{
							$(this).find("."+disconnectedInitialId).remove();
							if($(this).children().length == 0)
								$(this).remove();
						}
					});
					//marking tick 
					markingFunctionOnDisconnection(disconnectedInitialId);
					genNsFunctionOnDisconnection(disconnectedInitialId);
					$.fn.comparativesWithInitialsDisconnectedBelow(thisDisconnectedComparativedId[1], tmpDisconnectedComparativeId);
				}	
			}//End If
		}
		else
			return false;
	});//End disconnectPaste click
}//End Paste Function
