//array taken for keeping earlier connected symptoms and establishing new relationship
var arrayForEarlierConnection = [];
var earlierConnectionPe = 0;
var initial_quelle_type=0;
var comparing_quelle_type=0;
//Paste edit button click
$(document).on('click', '.symptom-paste-edit-btn', function(e){
	if(!($(this).parents('.comparing').prevAll(".initial").first().find(".gen-ns").hasClass("active"))){
		var thisId = $(this).parents('div.comparing').attr('id');
		var thisIdArray = thisId.split("_");
		peComparativeId = thisIdArray[1];
		peInitialId = thisIdArray[0];
		language = hidden_comparison_language;
		initialSymptomPE = $('#' + peInitialId).find(".symptom").html();
		comparativeSymptomPE = $("."+thisId).find(".symptom").html();
		var earlierConnectionExistPe= 1;
		var earlierConnectionExistPeMsg = 1;
		var pe_allowed = true;
		var uniqueId = $(this).parents('div.comparing').attr('id');
		var initial_source_symptom_de = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-de');
		var initial_source_symptom_en = $("#"+uniqueId).prevAll(".initial").first().attr('data-initial-symptom-en');
		var comparing_source_symptom_de = $("#"+uniqueId).attr('data-comparing-symptom-de');
		var comparing_source_symptom_en = $("#"+uniqueId).attr('data-comparing-symptom-en');
		var hidden_comparison_language = $("#hidden_comparison_language").val();
		var decoded_initial_source_symptom_de = (typeof(initial_source_symptom_de) != "undefined" && initial_source_symptom_de !== null && initial_source_symptom_de != "") ? b64DecodeUnicode(initial_source_symptom_de) : "";
		var decoded_initial_source_symptom_en = (typeof(initial_source_symptom_en) != "undefined" && initial_source_symptom_en !== null && initial_source_symptom_en != "") ? b64DecodeUnicode(initial_source_symptom_en) : "";	
		var decoded_comparing_source_symptom_de = (typeof(comparing_source_symptom_de) != "undefined" && comparing_source_symptom_de !== null && comparing_source_symptom_de != "") ? b64DecodeUnicode(comparing_source_symptom_de) : "";
		var decoded_comparing_source_symptom_en = (typeof(comparing_source_symptom_en) != "undefined" && comparing_source_symptom_en !== null && comparing_source_symptom_en != "") ? b64DecodeUnicode(comparing_source_symptom_en) : "";
		$("#paste_edit_modal_loader .loading-msg").removeClass('hidden');
		$("#paste_edit_modal_loader .error-msg").html('');
		if($("#paste_edit_modal_loader").hasClass('hidden'))
			$("#paste_edit_modal_loader").removeClass('hidden');
		$("#paste_edit_symptom_de_container").addClass('hidden');
		$("#paste_edit_symptom_en_container").addClass('hidden');
		$("#paste_edit_submit_btn").addClass('hidden');
		$("#populated_paste_edit_modal_data").remove();
		var html = '';
		html += '<div id="populated_paste_edit_modal_data">';
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
		html += '					</tr>';
		html += '				</thead>';
		html += '				<tbody>';
		html += '					<tr>';
		html += '						<th>DE</th>';
		html += '						<td>'+decoded_initial_source_symptom_de+'</td>';
		html += '						<td>'+decoded_comparing_source_symptom_de+'</td>';
		html += '					</tr>';
		html += '					<tr>';
		html += '						<th>EN</th>';
		html += '						<td>'+decoded_initial_source_symptom_en+'</td>';
		html += '						<td>'+decoded_comparing_source_symptom_en+'</td>';
		html += '					</tr>';
		html += '				</tbody>';
		html += '			</table>';
		html += '		</div>';
		html += '	</div>';
		html += '	<div class="row">';
		html += '		<div class="col-sm-12"><p class="common-error-text text-danger"></p></div>';
		html += '	</div>';
		html += '</div>';
		$(this).parents('div.comparing').nextUntil(".comparing").each(function(){
			if(!$(this).children().hasClass('previousConnection')){
				console.log("Not present");
				if($(this).hasClass("initialsConnectedCE"))
				{
					pe_allowed = false;
					earlierConnectionExistPe = 0;
					earlierConnectionExistPeMsg = 1;
				}
				else if($(this).hasClass("initialsConnectedPE"))
				{
					pe_allowed = false;
					earlierConnectionExistPe = 0;
					earlierConnectionExistPeMsg = 2;
				}
				else if($(this).hasClass("initialsConnectedPASTE"))
				{
					pe_allowed = false;
					earlierConnectionExistPe = 0;
					earlierConnectionExistPeMsg = 3;
				}
				else if($(this).hasClass("initialsConnectedCD"))
				{
					pe_allowed = false;
					earlierConnectionExistPe = 0;
					earlierConnectionExistPeMsg = 4;
				}	
			}else{
				earlierConnectionExistPe = 1;
				earlierConnectionExistPeMsg = 0;
				console.log("Present");
				return false;
			}
		});
		if(earlierConnectionExistPeMsg == 1 && earlierConnectionExistPe==0){	
			$("<div>This Comparative Symptom already has a Connect Edit. Please Unpaste.</div>").dialog();
		}else if(earlierConnectionExistPeMsg == 2 && earlierConnectionExistPe==0){
			$("<div>This Comparative Symptom already has a Paste Edit. Please Unpaste first.</div>").dialog();
		}else if(earlierConnectionExistPeMsg == 3 && earlierConnectionExistPe==0){
			$("<div>This Comparative Symptom already has a Paste. Please Unpaste.</div>").dialog();
		}else if(earlierConnectionExistPeMsg == 4 && earlierConnectionExistPe==0){
			$("<div>This Comparative Symptom already has a Connect. Please Unpaste.</div>").dialog();
		}
		function comparisonIdValuePe(){
			return peComparativeId;
		}
		//restricting if swap connection exist
		if($(this).parents('div.comparing').prevAll(".initial").first().find('.swapInitial').hasClass('active')){
			$(this).parents('div.comparing').prevAll(".initial").first().nextUntil(".initial").each(function(){
				if($(this).is("[class^=comparativesConnected]")){	
					$(this).children().each(function(){
						if(!$(this).hasClass('previousConnection') && $(this).find('.symptom-swap-connect-btn').hasClass('active')){
							earlierConnectionExistPe = 0;
							pe_allowed = false;
							$("<div>You cannot do a paste edit here, because the Initial Symptom already has a Swap Connection</div>").dialog();
							return false;
						}
					});
				}
			});
		}
		//main opeartion starts here
		if(earlierConnectionExistPe == 1){
			if(pe_allowed == true)
			{
				arrayForEarlierConnection = [];
				$(this).parents('div.comparing').nextUntil("div.comparing").each(function(){
					if($(this).is(".initialsConnectedCD") || $(this).is(".initialsConnectedCE")){
						$(this).children().each(function(){
							earlierConnectedId = comparisonIdValuePe();
							if($(this).hasClass('previousConnection')){
								var thisId = $(this).attr('id');
								var type = 'connect';
								var free_flag = '0';
								if($(this).find('.disconnect') || $(this).find('.disconnectCE')){
									console.log("Earlier connect found");
									var thisIdArray = thisId.split("_");
									var comparativeIdToSend = thisIdArray[1];
									var initial_id_to_send = peInitialId.replace("row","");
									var earlierConnectionPe = 1;
						    		arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,earlierConnectionPe:earlierConnectionPe});
								}		
							}
						});
					}else{
						$(this).children().each(function(){
							earlierConnectedId = comparisonIdValuePe();
							if($(this).hasClass('previousConnection')){
								var thisId = $(this).attr('id');
								var type = 'connect';
								var free_flag = '1';
								if($(this).find('.disconnectPaste') || $(this).find('.disconnectPE')){
									console.log("Earlier connect found");
									var comparativeIdToSend = thisId.replace("row","");
									var initial_id_to_send = peInitialId.replace("row","");
									console.log(comparativeIdToSend+' ****');
									var earlierConnectionPe = 2;
						    		arrayForEarlierConnection.push({comparativeIdToSend:comparativeIdToSend,earlierConnectedId:earlierConnectedId,earlierConnectionPe:earlierConnectionPe});
								}		
							}

						});
					}
				});
				$("#fv_pe_symptom_de").val(decoded_comparing_source_symptom_de);
				$("#fv_pe_symptom_en").val(decoded_comparing_source_symptom_en);
				$("#paste_edit_modal_container").append(html);
				$("#pasteEditModal").modal('show');
				if(!$("#paste_edit_modal_loader").hasClass('hidden'))
					$("#paste_edit_modal_loader").addClass('hidden');
				if($("#paste_edit_symptom_de_container").hasClass('hidden'))
					$("#paste_edit_symptom_de_container").removeClass('hidden');
				if($("#paste_edit_symptom_en_container").hasClass('hidden'))
					$("#paste_edit_symptom_en_container").removeClass('hidden');
				if($("#paste_edit_submit_btn").hasClass('hidden'))
					$("#paste_edit_submit_btn").removeClass('hidden');
			}
		}	
	}	
});

$.fn.pasteEditFunction = function()
{
	//Paste Edit connection starts here when paste edit icon is clicked.
	$(document).on('click', '.paste-edit-modal-submit-btn', function(e)
	{
		var check_custom_ns = $("#check_custom_ns").val();
		var initial_quelle_type = $('#'+peInitialId).attr("data-quelle-type");
		var comparing_quelle_type = $('#'+peInitialId+'_'+peComparativeId).attr("data-quelle-type");
		//checking here earlier connection and establishing earlier connection with new initial
		var earlierConnectionPe = 0;
		if(arrayForEarlierConnection.length !== 0){
			earlierConnectionPe = arrayForEarlierConnection[0]['earlierConnectionPe'];
		}
		var arrayForEarlierConnectionToSent = JSON.stringify(arrayForEarlierConnection);
		var comparativesConnectedPE_Found = 0;
		if(comparison_language == 'de')
		{
			edited_comparative = $("#fv_pe_symptom_de").val();
			translation = $("#fv_pe_symptom_en").val();
		}
		else if(comparison_language == 'en')
		{
			edited_comparative = $("#fv_pe_symptom_en").val();
			translation = $("#fv_pe_symptom_de").val();
		}
		edited_comparative_symptom_de = $("#fv_pe_symptom_de").val();
		edited_comparative_symptom_en = $("#fv_pe_symptom_en").val();
		$("#pasteEditModal").modal('hide');
		if(edited_comparative !== comparativeSymptomPE)//Checks if Comparative Symptom is changed or not
		{
			var percentage, symptomText,comparingString;
			var peInitialIdToSend = peInitialId.replace("row","");
			var peComparativeIdFull = peInitialId+"_"+peComparativeId;
			var connected_percentage = $("."+peComparativeIdFull).find(".percentage").text();
			connected_percentage = connected_percentage.replace("%","");
			var previousConnectionInInitial = 0;
			$.ajax({
					async:false,
					type: "POST",
			      	url: "paste-edit-operation.php",
				    data: "initial_symptom_id="+peInitialIdToSend+"&comparative_symptom_id="+peComparativeId+"&cutoff_percentage="+cutoff_percentage+"&edited_comparative_symptom_de="+edited_comparative_symptom_de+"&edited_comparative_symptom_en="+edited_comparative_symptom_en+"&comparison_language="+comparison_language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type=pastePE"+"&arrayForEarlierConnection="+arrayForEarlierConnectionToSent+"&earlierConnectionPe="+earlierConnectionPe,
				    dataType: "JSON",
				    success: function(returnedData){
							try {
								resultData = JSON.parse(returnedData.resultArray); 
							} catch (e) {
								resultData = returnedData.resultArray;
							}
							percentage = resultData.percentage;
							symptomText = resultData.symptomText;
							comparingString = resultData.comparingString;
							//reloading
							if(check_custom_ns == 1)
								location.reload();
							else
								reloadConnection(peInitialIdToSend);    
							
				    },
				    error: function(xhr, textStatus, error){
					    console.log(xhr.statusText);
					    console.log(textStatus);
					    console.log(error);
					}
			});
			//Get the HTML row of the Connected Initial Symptom
			var initialHtml = $('#' + peInitialId).get(0).outerHTML;
			const $domInitial = $("<div>").html(initialHtml);
			$domInitial.find('.symptom-search-btn').parent("li").remove();
			$domInitial.find('.symptom-connections-btn').parent("li").remove();
			//Initial Symptom does not have Percentage - add the percenatage in this Initial Symptom html
			$domInitial.find('.percentage').text(percentage+'%');
			initialHtml = $domInitial.html();
			//Get this Comparing Row HTML to append it to the Initial Id
			var comparativeHtml = $('#' + peComparativeIdFull).get(0).outerHTML;
			//We will eliminate any "symptom-connections-btn" if present for this Comparative Html
			const $domComparative = $("<div>").html(comparativeHtml);
			$domComparative.find('.percentage').text(percentage+'%');
			$domComparative.find('.symptom').html(symptomText);
			if(comparison_language == "de")
				$domComparative.find('.'+peComparativeIdFull).attr('data-comparing-symptom-de',comparingString);
			else
				$domComparative.find('.'+peComparativeIdFull).attr('data-comparing-symptom-en',comparingString);
			$domComparative.find('.symptom-connections-btn').parent("li").remove();
			comparativeHtml = $domComparative.html();
			//(1) First Connect Comparative with Initial above
			$('.'+peInitialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
			{
				if(comparativesConnectedPE_Found != 1){
					if($(this).hasClass("comparativesConnectedPE"))
					{
						//Unlike Connect Edit, Paste Edit can have multiple symptoms under "comparativesConnectedPE"
						$(this).append(comparativeHtml);
						comparativesConnectedPE_Found = 1;
					}
					if($('.'+peInitialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.fas').hasClass("fa-plus")){
						$('.'+peInitialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").find('.toggleInitial').trigger('click');
					}
					$.fn.initialsWithComparativesConnectedBelow('PE', peInitialId, peComparativeIdFull);
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
					reloadConnection(peInitialIdToSend);
			}
			if(comparativesConnectedPE_Found === 0)
			{
				$('.'+peInitialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").not(".comparativesConnectedPE .initial").not(".comparativesConnectedPASTE .initial").each(function()
				{
					var thisConnectedParent = $(this).parent().attr("class");
					if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" &&thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
					{
						if($('.'+peInitialId).find('.fas').hasClass("fa-minus"))
						{
							comparativeHtml = "<div class='comparativesConnectedPE'>"+comparativeHtml+"</div>";
						}
						else
						{
							comparativeHtml = "<div class='comparativesConnectedPE' style='display: none;'>"+comparativeHtml+"</div>";
						}
						//Declaring flags for sorting connection
						var ceVal = connectVal = pasteVal = peVal = 0;
						//Array to save the comparativeConnected classes
						connectionSortArray = [];
						connectionSortArray.length = 2;
						//Finding all the comparatives connected already present
						$(this).nextUntil("div.comparing").each(function(){
							if($(this).is('.comparativesConnectedCD')){
								connectVal = 1;
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
						if(connectVal ==1){
							connectionSort(connectionSortArray[0],comparativeHtml,'after');
							console.log("connect");
						}else if(pasteVal == 1){
							connectionSort(connectionSortArray[1],comparativeHtml,'before');
							console.log("paste");
						}else if(ceVal == 1){
							connectionSort(connectionSortArray[2],comparativeHtml,'after');
							console.log("ce");
						}else{
							$(this).after(comparativeHtml);
							console.log("normal");
						}
					}
					//Now append it to Initial Symptom
				});
				$.fn.initialsWithComparativesConnectedBelow('PE', peInitialId, peComparativeIdFull);
			}
			//Next we connect the Initial Symptom with any matching Comparative Symptom other than this
			$('[class*=_' + peComparativeId + ']:not(".'+peComparativeIdFull+'")').each(function()
			{
			    if($(this).next().hasClass("initialsConnectedPE"))
					$(this).next(".initialsConnectedPE").append(initialHtml);
				else if (!$(this).next().hasClass("initialsConnectedPE"))
				{
					if($(this).find('.fas').hasClass("fa-minus"))
					{
	      				$("<div class='initialsConnectedPE' style='display: none'>"+initialHtml+"</div>").insertAfter($(this));
					}
					else
					{
	      				$("<div class='initialsConnectedPE'>"+initialHtml+"</div>").insertAfter($(this));
					}
				}
			});
			//Finally we hide this Comparative symptom
			$('.'+peComparativeIdFull).addClass("hidden");
			//But not the one that has 'comparativesConnectedPE' as parent
			$('div.comparativesConnectedPE > div.'+peComparativeIdFull).removeClass("hidden");
			//marking tick 
			$("."+peInitialId).find(".marking").attr("checked","1");
			$("."+peInitialId).find(".gen-ns").addClass("ns-disabled");
			$.fn.comparativesWithInitialsConnectedBelow('PE', peInitialId, peComparativeId, peComparativeIdFull);
		}//End If
	});//End Paste-Edit Click

	////////////////////WHEN DISCONNECT CE LINK ICON IS CLICKED/////////////////////////
	$(document).on("click", ".disconnectPE", function (ev)
	{
		var confirmation;
		var operation = "";
		var check_custom_ns = $("#check_custom_ns").val();
		confirmation = confirm("Do you really want to Unpaste this Symptom");
		if(confirmation == true)
		{
			var disconnectid_initialId, disconnectid_comparativelId;
			//If Unpaste link is clicked within comparativesConnectedPE
			if($(this).parents('div.comparativesConnectedPE').length>0)
			{
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					var thisComparativeId = $(this).parents('div.initial').attr("id");
					thisComparativeId = thisComparativeId.replace("row","");
					var initialParent = $(this).parents('.comparativesConnectedPE').prevAll(".initial").first();
					var sectionInitialId = initialParent.attr("id");
					sectionInitialId = sectionInitialId.replace("row","");
					var operation = 'PE';
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
					var initialParent = $(this).parents('.comparativesConnectedPE').prevAll(".initial").first();
					var ids = $(this).parents('div.comparing').attr("id").split("_");
					var percentage, symptomText,comparingString;

					disconnectid_initialId = ids[0];
					disconnectid_initialId_toSend = disconnectid_initialId.replace("row","");
					disconnectid_comparativelId = ids[1];
					peComparativeIdFull=disconnectid_initialId+"_"+disconnectid_comparativelId;

					$.ajax({
						async:false,
						type: "POST",
				      	url: "paste-edit-operation.php",
					    data: "initial_symptom_id="+disconnectid_initialId_toSend+"&comparative_symptom_id="+disconnectid_comparativelId+"&cutoff_percentage="+cutoff_percentage+"&comparison_language="+comparison_language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation_type=disconnectPE",
					    dataType: "JSON",
					    success: function(returnedData){
					        	console.log(returnedData);
								try {
									resultData = JSON.parse(returnedData.resultArray); 
								} catch (e) {
									resultData = returnedData.resultArray;
								}
								percentage = resultData.percentage;
								symptomText = resultData.symptomText;
								comparingString = resultData.comparingString;
								//Reloading the page
    							//location.reload();
    							//reloadConnection(disconnectid_initialId_toSend);
					    },
					    error: function(xhr, textStatus, error){
						    console.log(xhr.statusText);
						    console.log(textStatus);
						    console.log(error);
						}
					});

					//(1) Disconnect this comparative symptom i.e., inside compartivesConnected from Initial Symptom above
					//If this comparativesConnected has no children, remove it
					if($(this).parents('div.comparativesConnectedPE').children().length == 1)
						$(this).parents('div.comparativesConnectedPE').remove();
					else
						$(this).parents('div.comparing').remove();

					//Comparative symptom is given the percentage and symptom text
					$('#' + peComparativeIdFull).find('.percentage').text(percentage+'%');
					$('#' + peComparativeIdFull).find('.symptom').html(symptomText);
					if(comparison_language == "de")
						$('#' + peComparativeIdFull).attr('data-comparing-symptom-de',comparingString);
					else
						$('#' + peComparativeIdFull).attr('data-comparing-symptom-en',comparingString);

					initialParent.nextAll("#"+ thisComparativeId).first().removeClass("hidden");
					//(2) Next remove the initial symptom from all initialsConnectedPE
					$('[class*=_' + disconnectid_comparativelId + ']').each(function(){
						if($(this).next(".initialsConnectedPE")){
							var thisInitialId = $(this).next(".initialsConnectedPE").find('.initial').attr('id');
							if(thisInitialId == initialParent.attr("id")){
								if($(this).next("div.initialsConnectedPE").children(".initial").length == 1){
									$(this).next(".initialsConnectedPE").remove();
									console.log("Done");
								}else{
									$(this).next(".initialsConnectedPE").find('.'+thisInitialId).remove();
									console.log("Done 2");	
								}
							}
						}
					});
					$.fn.initialsWithComparativesDisconnectedBelow(initialParent.attr("id"));
					$.fn.comparativesWithInitialsDisconnectedBelow(disconnectid_comparativelId, thisComparativeId);
					//marking tick 
					markingFunctionOnDisconnection(disconnectid_initialId);
					genNsFunctionOnDisconnection(disconnectid_initialId);

				}
					
			}
			//If the Unpaste click is NOT done in the comparativesConnectedPE, but is done in the initialsConnectedPE.
			//Again we have to unpaste Initial Symptom from comparativesConnectedPE
			if($(this).parents('div.initialsConnectedPE').length>0)
			{
				var disconnectedPreviousInitialIdToSend = "";
				var operation = "";
				//For combined sources, if disconnected then other operations takes place
				if($(this).parents('div.initial').hasClass('previousConnection'))
				{
					disconnectedComparativeId = $(this).parents('div.initialsConnectedPE').prevAll("div.comparing").first().attr("id");
					thisDisconnectedComparativedId = disconnectedComparativeId.split("_");
					disconnectid_comparativelId = thisDisconnectedComparativedId[1];
					disconnectedInitialId = $(this).parents(".initial").attr("id");
					var disconnectid_initialId_toSend = disconnectedInitialId.replace("row","");
					var operation = 'PE_previous';
					var disconnectedPreviousInitialId = $(this).parents('div.initialsConnectedPE').prevAll("div.initial").first().attr("id");
					var disconnectedPreviousInitialIdToSend = disconnectedPreviousInitialId.replace("row","");
    	 			var main_initial = $(this).parents('div.initialsConnectedPE').prevAll("div.initial").first().attr("id");
					main_initial = main_initial.replace("row","");
    	 			if(check_custom_ns == 1)
						location.reload();
					else
						reloadConnection(main_initial);	
				}else{
					var thisComparativeId = $(this).parents('div.initialsConnectedPE').prevAll("div.comparing").first().attr("id");
					var thisInitialId = $(this).parents(".initial").attr("id");
					var thisDisconnectedComparativeId = thisComparativeId.split("_");
					var actualDisconnectedComparativeId = thisInitialId + "_" + thisDisconnectedComparativeId[1];
					var disconnectid_initialId_toSend = thisInitialId.replace("row","");
					var disconnectid_comparativelId = thisDisconnectedComparativeId[1];
				}
				$.ajax({
					async:false,
					type: "POST",
			      	url: "paste-edit-operation.php",
				    data: "initial_symptom_id="+disconnectid_initialId_toSend+"&disconnectedPreviousInitialIdToSend="+disconnectedPreviousInitialIdToSend+"&comparative_symptom_id="+disconnectid_comparativelId+"&cutoff_percentage="+cutoff_percentage+"&comparison_language="+comparison_language+"&comparison_option="+comparison_option+"&comparison_table_name="+comparison_table_name+"&operation="+operation+"&operation_type=disconnectPE",
				    dataType: "JSON",
				    success: function(returnedData){
				        	console.log(returnedData);
							try {
								resultData = JSON.parse(returnedData.resultArray); 
							} catch (e) {
								resultData = returnedData.resultArray;
							}
							percentage = resultData.percentage;
							symptomText = resultData.symptomText;
							comparingString = resultData.comparingString;
				    },
				    error: function(xhr, textStatus, error){
					    console.log(xhr.statusText);
					    console.log(textStatus);
					    console.log(error);
					}
				});
				$(this).parents("div.initialsConnectedPE").remove();
				if(operation == 'PE_previous'){
					if(check_custom_ns == 1)
						location.reload();
					else
						reloadConnection(disconnectid_initialId_toSend);	
				}
				//Next we remove the Comparative Symptom from "comparativesConnected"
				$(".comparativesConnectedPE").each(function()
				{
					if($(this).siblings().hasClass(actualDisconnectedComparativeId))
					{
						$(this).find("."+actualDisconnectedComparativeId).remove();
						$(this).nextAll("."+actualDisconnectedComparativeId).first().removeClass("hidden");
						
						if($(this).children().length == 0)
							$(this).remove();
						$.fn.initialsWithComparativesDisconnectedBelow(thisInitialId);
					}
				});
				//Comparative symptom is given the percentage and symptom text
				$('#' + actualDisconnectedComparativeId).find('.percentage').text(percentage+'%');
				$('#' + actualDisconnectedComparativeId).find('.symptom').html(symptomText);
				if(comparison_language == "de")
					$('#' + actualDisconnectedComparativeId).attr('data-comparing-symptom-de',comparingString);
				else
					$('#' + actualDisconnectedComparativeId).attr('data-comparing-symptom-en',comparingString);
				//Next we remove this initialSymptom from all initialsConnected under .comparing div's
				$(".initialsConnectedPE").each(function(){
					var cId = $(this).prevAll("div.comparing").first().attr("id");
					//Initial Ids can be same for various Comparative Symptoms. Check if comparative id is same.
					if(cId.includes(thisDisconnectedComparativeId[1]))
					{
						$(this).find("."+thisInitialId).remove();
						if($(this).children().length == 0)
							$(this).remove();
					}
				});
				$.fn.comparativesWithInitialsDisconnectedBelow(thisDisconnectedComparativeId[1], actualDisconnectedComparativeId);
				//marking tick 
				markingFunctionOnDisconnection(thisInitialId);
				genNsFunctionOnDisconnection(thisInitialId);	
			}
		}
		else
			return false;
		});
}//End PasteEdit Function