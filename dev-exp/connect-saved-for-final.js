$.fn.connectSave = function(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type, connection_type,is_earlier_connection,free_flag, connection_id)
{
	var onLoad = 0;
	switch(connection_type) {
	case "connect":
	{
	  	if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}
		var earlier_connection = "";
		var previous_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}
		if(free_flag=="1")
		{
			previous_connection = " previousConnection";
		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+' symptom-row initial'+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'" data-year="'+initial_year+'" data-source-original-language="'+initial_quelle_original_language+'" data-quell-id="'+initial_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="0">';
		initialHtml = initialHtml+'<div class="source-code">'+initial_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+initial_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'">T</a> </li> </ul></div>';
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect '+earlierSavedConnection+' active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';			

		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="0">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect '+earlierSavedConnection+'active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 
		
		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{
			var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
			
			if(is_earlier_connection=="1"){
				combinedId = initial_symptom_id;
				 var html = comparativeHtml; 
			}
			else{
				combinedId = comparing_symptom_id;
				var html = initialHtml;
			}

			
			
			$('[class*=_' + combinedId + ']').each(function(){
				//Adding saved connections for comparative symptoms..

				// if($(this).find("symptom-swap-connect-btn"))
				// 	console.log("yes");

				if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE")&& !$(this).parents().hasClass("comparativesConnectedPE")&& !$(this).hasClass("previousConnection"))
				{
					//console.log(this);
					if($(this).next().hasClass("initialsConnectedCD")){
						if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass('row'+initial_symptom_id))
						//if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass(thisId))
							$(this).next(".initialsConnectedCD").append(html);
					}
					else
					{
			      		$('<div class="initialsConnectedCD">'+html+'</div>').insertAfter($(this));
			      	}
			      	if(is_earlier_connection=="1"){
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, combinedId, thisId, is_earlier_connection);	
			      		// console.log('yes '+initial_symptom_id+' '+initialId+' '+thisId);
			      	}	
					else{
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId, is_earlier_connection);	
						// console.log('no '+initial_symptom_id+' '+initialId+' '+thisId);
					}
				}
			});
			
		}
		else if(source_type == "combinedSourceInitials")
		{
			var comparativesConnectedCD_Found = 0;
			if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
			{
				if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
				{

					//(1) First Connect Comparative with Initial above
					//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
					//so that comparativesConnectedCE does not fall in between
					
					//new edit 21 march
					// $('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
					// {
					// 	if($(this).hasClass("comparativesConnectedCD"))
					// 	{
					// 		$(this).append(comparativeHtml);
					// 		comparativesConnectedCD_Found = 1;
					// 	}
					// });
					
					if(comparativesConnectedCD_Found === 0)
					{
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
						{
							var thisConnectedParent = $(this).parent().attr("class");
							if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" && thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
							{
								//Now append comparativeHtml to Initial Symptom
								$(this).after('<div class="comparativesConnectedCD" style="display: none;">'+comparativeHtml+'</div>');
							}
						});
					}
					//(3) Finally we hide this Comparative symptom
					$("."+thisId).not(".comparativesConnectedCD ."+thisId).addClass("hidden");
					onLoad = 1;
			      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);	

				}
			}
		}
		else
		{

			var comparativesConnectedCD_Found = 0;

			
			if(source_type == "singleSourceInitial")
			{
				//Get the HTML row of the Connected Initial Symptom
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						
						//If initialsConnected are open while connecting with Initial, we will close them first
						$("."+thisId).nextUntil(".comparing").each(function(){
							//console.log($(this).attr("class")+" , "+$(this).find(".percentage").text());
							if($(this).hasClass("initialsConnectedCD"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedCE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPASTE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPE"))
								$(this).css("display", "none");
						});

						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						{
							if($(this).hasClass("comparativesConnectedCD"))
							{
								$(this).append(comparativeHtml);
								comparativesConnectedCD_Found = 1;
							}
						});
						if(comparativesConnectedCD_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedCD" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}
						
						//(3) Finally we hide this Comparative symptom
						$("."+thisId).not(".comparativesConnectedCD ."+thisId).addClass("hidden");
		
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);	

					}
				}
			}
			else
			{
				//(2) Next we connect the Initial Symptom with any matching Comparative Symptom other than this
				$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
					//Preventing any initialsConnectedCD under comparativesConnectedCD
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
					{
						if($(this).next().hasClass("initialsConnectedCD"))
							$(this).next(".initialsConnectedCD").append(initialHtml);
						else
						{
				      		$('<div class="initialsConnectedCD">'+initialHtml+'</div>').insertAfter($(this));
				      	}
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId, is_earlier_connection);
					}
				});

			}
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	}    
	break;
	case "CE":
	{
		if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}

		var earlier_connection = "";
		var previous_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}

		if(free_flag=="1")
		{
			previous_connection = " previousConnection";
		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+' symptom-row initial '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'" data-year="'+initial_year+'" data-source-original-language="'+initial_quelle_original_language+'" data-quell-id="'+initial_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		initialHtml = initialHtml+'<div class="source-code">'+initial_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+initial_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'">T</a> </li> </ul></div>';
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE active" href="javascript:void(0)" title="Connect edit">CE</a></li></ul></div></div>';			

		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li>  <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE '+earlierSavedConnection+' active" href="javascript:void(0)" title="Connect edit">CE</a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 
		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{
			var combinedId = comparing_symptom_id;
			var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
				
			if(is_earlier_connection=="1")
			{
				//$('[class*=_' + comparativeId + ']').each(function(){
				$('[class*=_' + initial_symptom_id + ']').each(function(){
					//Adding saved connections for comparative symptoms.
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE") && !$(this).hasClass("previousConnection"))
					{
						if($(this).next().hasClass("initialsConnectedCE")){
							//if(!$(this).next(".initialsConnectedCE").next('.'+thisId))
							$(this).next(".initialsConnectedCE").append(comparativeHtml);
						}
						else
						{
				      		$('<div class="initialsConnectedCE">'+comparativeHtml+'</div>').insertAfter($(this));
				      	}
						//$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId);	
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, initial_symptom_id, thisId,is_earlier_connection);	
						//console.log(this);
					}
				});
				// console.log(combinedId);
				// console.log(thisId);
				// console.log(comparativeId);
			}else
			{
				$('[class*=_' + comparativeId + ']').each(function(){
				//$('[class*=_' + initial_symptom_id + ']').each(function(){
					//Adding saved connections for comparative symptoms.
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
					{
						if($(this).next().hasClass("initialsConnectedCE")){
							//if(!$(this).next(".initialsConnectedCE").next('.'+thisId))
							$(this).next(".initialsConnectedCE").append(initialHtml);
						}
						else
						{
				      		$('<div class="initialsConnectedCE">'+initialHtml+'</div>').insertAfter($(this));
				      	}
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId, is_earlier_connection);	
						//$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, initial_symptom_id, thisId);	
						//console.log(this);
					}
				});
				// console.log(combinedId);
				// console.log(thisId);
				// console.log(comparativeId);
			}

		}
		else if(source_type == "combinedSourceInitials")
		{
			var comparativesConnectedCE_Found = 0;
			if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
			{
				if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
				{

					//(1) First Connect Comparative with Initial above
					//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
					//so that comparativesConnectedCE does not fall in between
					
					//new edit 21 march
					// $('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
					// {
					// 	if($(this).hasClass("comparativesConnectedCE"))
					// 	{
					// 		$(this).append(comparativeHtml);
					// 		comparativesConnectedCE_Found = 1;
					// 	}
					// });

					if(comparativesConnectedCE_Found === 0)
					{
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
						{
							var thisConnectedParent = $(this).parent().attr("class");
							if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" && thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
							{
								//Now append comparativeHtml to Initial Symptom
								$(this).after('<div class="comparativesConnectedCE" style="display: none;">'+comparativeHtml+'</div>');
							}
						});
					}
					//(3) Finally we hide this Comparative symptom
					$("."+thisId).not(".comparativesConnectedCE ."+thisId).addClass("hidden");
					onLoad = 1;
			      	$.fn.initialsWithComparativesConnectedBelow('CE', initialId, thisId,onLoad,is_earlier_connection);	

				}
			}
		}
		else
		{
			var comparativesConnectedCE_Found = 0;
			if(source_type == "singleSourceInitial")
			{
				//Get the HTML row of the Connected Initial Symptom
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						
						//If initialsConnected are open while connecting with Initial, we will close them first
						$("."+thisId).nextUntil(".comparing").each(function(){
							//console.log($(this).attr("class")+" , "+$(this).find(".percentage").text());
							if($(this).hasClass("initialsConnectedCD"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedCE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPASTE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPE"))
								$(this).css("display", "none");
						});

						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						{
							if($(this).hasClass("comparativesConnectedCE"))
							{
								$(this).append(comparativeHtml);
								comparativesConnectedCE_Found = 1;
							}
						});
						if(comparativesConnectedCE_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedCE" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}

						//(3) Finally we hide this Comparative symptom
						$("."+thisId).not(".comparativesConnectedCE ."+thisId).addClass("hidden");
						$("."+combinedId).each(function(){
							$(this).addClass("hidden");
						});
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CE', initialId, thisId,onLoad,is_earlier_connection);	
						// $.fn.initialsTopIcons(initialId);
						// $.fn.comparativesInitialTopIcons("CD", thisId);
					}
				}
			}
			else
			{
				//(2) Next we connect the Initial Symptom with any matching Comparative Symptom other than this
				$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
					//Preventing any initialsConnectedCD under comparativesConnectedCD
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
					{
						if($(this).next().hasClass("initialsConnectedCE"))
							$(this).next(".initialsConnectedCD").append(initialHtml);
						else
						{
				      		$('<div class="initialsConnectedCE">'+initialHtml+'</div>').insertAfter($(this));
				      	}
				      	$.fn.comparativesWithInitialsConnectedBelow('CE', initialId, comparativeId, thisId, is_earlier_connection);
					}
				});
			}		
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	}
	break;
	case "paste":
	{
	  	if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}
		var earlier_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+' symptom-row initial '+earlier_connection+'" id="row'+initial_symptom_id+'" data-year="'+initial_year+'" data-source-original-language="'+initial_quelle_original_language+'" data-quell-id="'+initial_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="0">';
		initialHtml = initialHtml+'<div class="source-code">'+initial_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+initial_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'">T</a> </li> </ul></div>';
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-paste-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPaste active" href="javascript:void(0)" title="Paste">P</a></li></ul></div></div>';			

		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="0">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-paste-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPaste active" href="javascript:void(0)" title="Paste">P</a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 
		

		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{
			var comparativeId = initial_symptom_id;
			var combinedId = comparing_symptom_id;
			var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
			if(is_earlier_connection==1)
			{
				//earlier connection not showing. commented on 30.03.2022
				// $('[class*=_' + combinedId + ']').each(function(){
				// 	//Adding saved connections for comparative symptoms.
				// 	if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
				// 	{
				// 		if($(this).next().hasClass("initialsConnectedPASTE"))
				// 		{
				// 			if(!$(this).next(".initialsConnectedPASTE").next('.row'+initial_symptom_id))
				// 				$(this).next(".initialsConnectedPASTE").append(initialHtml);
				// 		}
				// 		else
				// 		{
				//       		$('<div class="initialsConnectedPASTE">'+initialHtml+'</div>').insertAfter($(this));
				//       	}
				// 		$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, combinedId, thisId);	
				// 	}
				// });

			}
			else
			{
				var comparativesConnectedPASTE_Found = 0;
				if(free_flag != 1){
					//This section is for the comparatives connected below.
			      	$('[class*=_' + combinedId + ']').each(function(){
						//Adding saved connections for comparative symptoms.
						if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
						{
							if($(this).next().hasClass("initialsConnectedPASTE")){
								if(!$(this).next(".initialsConnectedPASTE").next('.row'+initial_symptom_id))
									$(this).next(".initialsConnectedPASTE").append(initialHtml);
							}
							else
							{
					      		$('<div class="initialsConnectedPASTE">'+initialHtml+'</div>').insertAfter($(this));
					      	}
							$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, combinedId, thisId, is_earlier_connection);	
						}
					});
				}
					
				
			}	
		}
		else if(source_type == "combinedSourceInitials")
		{
			var comparativesConnectedCD_Found = 0;
			if(is_earlier_connection==1){
				//earlier connection not showing. commented on 30.03.2022
				// if($("."+combinedId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				// {
				// 	if($("#" + thisId).length !== 0 || $("#" + combinedId).length !== 0 ) 
				// 	{
				// 		//(1) First Connect Comparative with Initial above
				// 		//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
				// 		//so that comparativesConnectedCE does not fall in between
				// 		$('.'+combinedId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
				// 		{
				// 			if($(this).hasClass("comparativesConnectedPASTE"))
				// 			{
				// 				if(!$(this).next(".comparativesConnectedPASTE").next('.row'+initial_symptom_id))
				// 					$(this).append(initialHtml);
				// 				comparativesConnectedCD_Found = 1;
				// 			}
				// 		});


				// 		if(comparativesConnectedCD_Found === 0)
				// 		{
				// 			$('.'+combinedId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
				// 			{
				// 				var thisConnectedParent = $(this).parent().attr("class");
				// 				if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
				// 				{
				// 					//Now append comparativeHtml to Initial Symptom
				// 					$(this).after('<div class="comparativesConnectedPASTE" style="display: none;">'+initialHtml+'</div>');
				// 				}
				// 			});
				// 		}
				// 		//(3) Finally we hide this Comparative symptom
				// 		$("."+thisId).not(".comparativesConnectedPASTE ."+thisId).addClass("hidden");
				// 		$('[class*=_' + comparing_symptom_id + ']:not(".'+thisId+'")').each(function(){
				// 			$(this).addClass("hidden");
				// 		});
				//       	$.fn.initialsWithComparativesConnectedBelow('CD', combinedId, thisId,1);	

				// 	}
				// }
			}
			else{
				var comparativesConnectedPASTE_Found = 0;
				//This section is for the comapartives with the initial connected above.
				//Get the HTML row of the Connected Initial Symptom
				
				//*********************
				//return false;
				
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						// $('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						// {
							

						// 	if($(this).hasClass("comparativesConnectedPASTE"))
						// 	{
						// 		$(this).append(comparativeHtml);
						// 		comparativesConnectedPASTE_Found = 1;
						// 	}
						// });
						
						if(comparativesConnectedPASTE_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedPASTE" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}

						if(free_flag != 1){
							//(3) Finally we hide this Comparative symptom
							$("."+thisId).not(".comparativesConnectedPASTE ."+thisId).addClass("hidden");
						}
							
						
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);
					}
				}
			}

				

		}
		else
		{

			var comparativesConnectedCD_Found = 0;

			
			if(source_type == "singleSourceInitial")
			{
				//Get the HTML row of the Connected Initial Symptom
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						
						//If initialsConnected are open while connecting with Initial, we will close them first
						$("."+thisId).nextUntil(".comparing").each(function(){
							//console.log($(this).attr("class")+" , "+$(this).find(".percentage").text());
							if($(this).hasClass("initialsConnectedCD"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedCE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPASTE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPE"))
								$(this).css("display", "none");
						});

						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						{
							if($(this).hasClass("comparativesConnectedPASTE"))
							{
								$(this).append(comparativeHtml);
								comparativesConnectedCD_Found = 1;
							}
						});
						if(comparativesConnectedCD_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedPASTE" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}

						//(3) Finally we hide this Comparative symptom
						$("."+thisId).not(".comparativesConnectedPASTE ."+thisId).addClass("hidden");
						$("."+combinedId).each(function(){
							$(this).addClass("hidden");
						});
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);	

					}
				}
			}
			else
			{
				//(2) Next we connect the Initial Symptom with any matching Comparative Symptom other than this
				$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
					//Preventing any initialsConnectedCD under comparativesConnectedCD
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
					{
						if($(this).next().hasClass("initialsConnectedPASTE"))
							$(this).next(".initialsConnectedPASTE").append(initialHtml);
						else
						{
				      		$('<div class="initialsConnectedPASTE">'+initialHtml+'</div>').insertAfter($(this));
				      	}
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId, is_earlier_connection);
					}
				});

			}
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	} 
	break; 
	case "PE":
	{
	  	if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}
		var earlier_connection = "";
		var previous_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}

		if(free_flag=="1")
		{
			previous_connection = " previousConnection";
		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+' symptom-row initial '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'" data-year="'+initial_year+'" data-source-original-language="'+initial_quelle_original_language+'" data-quell-id="'+initial_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		initialHtml = initialHtml+'<div class="source-code">'+initial_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+initial_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'">T</a> </li> </ul></div>';
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-paste-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPE active" href="javascript:void(0)" title="Paste Edit">PE</a></li></ul></div></div>';			

		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-edit-btn" title="Symptom edit" href="javascript:void(0)"><i class="fas fa-pencil-alt"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-paste-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectPE active" href="javascript:void(0)" title="Paste Edit">PE</a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 

		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{
			var comparativeId = initial_symptom_id;
			var combinedId = comparing_symptom_id;
			var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
			if(is_earlier_connection==1)
			{
				//Not showing the earlier connected paste edit symptoms
				// $('[class*=_' + combinedId + ']').each(function(){
				// //Adding saved connections for comparative symptoms.
				// 	if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
				// 	{
				// 		if($(this).next().hasClass("initialsConnectedPE"))
				// 		{
				// 			if(!$(this).next(".initialsConnectedPE").next('.row'+initial_symptom_id))
				// 				$(this).next(".initialsConnectedPE").append(initialHtml);
				// 		}
				// 		else
				// 		{
				//       		$('<div class="initialsConnectedPE">'+initialHtml+'</div>').insertAfter($(this));
				//       	}
				// 		$.fn.comparativesWithInitialsConnectedBelow('PE', initialId, combinedId, thisId, is_earlier_connection);	
				// 	}
				// });
			}
			else
			{
				var comparativesConnectedPE_Found = 0;
				if(free_flag != 1){
					//This section is for the comparatives connected below.
			      	$('[class*=_' + combinedId + ']').each(function(){
						//Adding saved connections for comparative symptoms.
						if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
						{
							if($(this).next().hasClass("initialsConnectedPE")){
								if(!$(this).next(".initialsConnectedPE").next('.row'+initial_symptom_id))
									$(this).next(".initialsConnectedPE").append(initialHtml);
							}
							else
							{
					      		$('<div class="initialsConnectedPE">'+initialHtml+'</div>').insertAfter($(this));
					      	}
							$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, combinedId, thisId, is_earlier_connection);	
						}
					});
				}
					
		    }
			
		}
		else if(source_type == "combinedSourceInitials")
		{
			var comparativesConnectedPE_Found = 0;
			if(is_earlier_connection==1){
				//Not showing the earlier connected paste edit symptoms
				// if($("."+combinedId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				// {
				// 	if($("#" + thisId).length !== 0 || $("#" + combinedId).length !== 0 ) 
				// 	{
				// 		if(comparativesConnectedPE_Found === 0)
				// 		{
				// 			$('.'+combinedId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
				// 			{
				// 				var thisConnectedParent = $(this).parent().attr("class");
				// 				if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
				// 				{
				// 					//Now append comparativeHtml to Initial Symptom
				// 					$(this).after('<div class="comparativesConnectedPE" style="display: none;">'+initialHtml+'</div>');
				// 				}
				// 			});
				// 		}
				// 		//(3) Finally we hide this Comparative symptom
				// 		$("."+thisId).not(".comparativesConnectedPE ."+thisId).addClass("hidden");
				// 		$('[class*=_' + comparing_symptom_id + ']:not(".'+thisId+'")').each(function(){
				// 			$(this).addClass("hidden");
				// 		});
				// 		onLoad = 1;
				//       	$.fn.initialsWithComparativesConnectedBelow('PE', combinedId, thisId,onLoad,is_earlier_connection);	
				// 	}
				// }
			}else{
				//This section is for the comapartives with the initial connected above.
				//Get the HTML row of the Connected Initial Symptom
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						// $('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						// {
							
						// 	if($(this).hasClass("comparativesConnectedPE"))
						// 	{
						// 		$(this).append(comparativeHtml);
						// 		comparativesConnectedPE_Found = 1;
						// 	}
						// });

						if(comparativesConnectedPE_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedPE" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}

						//(3) Finally we hide this Comparative symptom
						if(free_flag != 1){
							$("."+thisId).not(".comparativesConnectedPE ."+thisId).addClass("hidden");
							$("."+combinedId).each(function(){
								$(this).addClass("hidden");
							});
						}
							
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);
					}
				}
			}
				
		}
		else
		{

			var comparativesConnectedCD_Found = 0;

			
			if(source_type == "singleSourceInitial")
			{
				//Get the HTML row of the Connected Initial Symptom
				if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
				{

					//if (typeof thisId !== 'undefined' && thisId !== false)
					if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
					{
						
						//If initialsConnected are open while connecting with Initial, we will close them first
						$("."+thisId).nextUntil(".comparing").each(function(){
							//console.log($(this).attr("class")+" , "+$(this).find(".percentage").text());
							if($(this).hasClass("initialsConnectedCD"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedCE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPASTE"))
								$(this).css("display", "none");
							if($(this).hasClass("initialsConnectedPE"))
								$(this).css("display", "none");
						});

						//(1) First Connect Comparative with Initial above
						//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
						//so that comparativesConnectedCE does not fall in between
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
						{
							if($(this).hasClass("comparativesConnectedPE"))
							{
								$(this).append(comparativeHtml);
								comparativesConnectedCD_Found = 1;
							}
						});
						if(comparativesConnectedCD_Found === 0)
						{
							$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
							{
								var thisConnectedParent = $(this).parent().attr("class");
								if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE")
								{
									//Now append comparativeHtml to Initial Symptom
									$(this).after('<div class="comparativesConnectedPE" style="display: none;">'+comparativeHtml+'</div>');
								}
							});
						}

						//(3) Finally we hide this Comparative symptom
						$("."+thisId).not(".comparativesConnectedPE ."+thisId).addClass("hidden");
						$("."+combinedId).each(function(){
							$(this).addClass("hidden");
						});
						//Update icons
						onLoad = 1;
				      	$.fn.initialsWithComparativesConnectedBelow('CD', initialId, thisId,onLoad,is_earlier_connection);	

					}
				}
			}
			else
			{
				//(2) Next we connect the Initial Symptom with any matching Comparative Symptom other than this
				$('[class*=_' + comparativeId + ']:not(".'+thisId+'")').each(function(){
					//Preventing any initialsConnectedCD under comparativesConnectedCD
					if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
					{
						if($(this).next().hasClass("initialsConnectedPE"))
							$(this).next(".initialsConnectedPE").append(initialHtml);
						else
						{
				      		$('<div class="initialsConnectedPE">'+initialHtml+'</div>').insertAfter($(this));
				      	}
						$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, comparativeId, thisId, is_earlier_connection);
					}
				});

			}
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	}   
	break;
	case "swap":
	{
	  	if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}
		var earlier_connection = "";
		var previous_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}

		if(free_flag=="1")
		{
			previous_connection = " previousConnection";
		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row initial '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		initialHtml = initialHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li>  <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';	
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li><li><a class="symptom-swap-connect-btn active" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li></ul></div></div>';			


		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 

		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{	
			var comparativesConnectedCD_Found = 0;
			var thisId = "row"+comparing_symptom_id+'_'+initial_symptom_id;
			initialId = "row"+comparing_symptom_id;
			//$.fn.swapComparativesIcons('CD',initial_symptom_id);	

			$('[class*=_' + initial_symptom_id + ']').each(function(){
				//Adding saved connections for comparative symptoms.
				if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
				{
					
					// if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass('row'+initial_symptom_id+'_'+comparing_symptom_id)){
		   //    			$('<div class="initialsConnectedCD">'+initialHtml+'</div>').insertAfter($(this));
					// }

					if($(this).next().hasClass("initialsConnectedCD")){
						if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass('row'+initial_symptom_id+'_'+comparing_symptom_id))
						//if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass(thisId))
							$(this).next(".initialsConnectedCD").append(initialHtml);
					}
					else
					{
			      		$('<div class="initialsConnectedCD">'+initialHtml+'</div>').insertAfter($(this));
			      	}
					
					// if($(this).next().hasClass("initialsConnectedCD")){
					// 	//if(!$(this).next(".initialsConnectedCD").next('.row'+comparing_symptom_id+'_'+initial_symptom_id))
					// 		$(this).next(".initialsConnectedCD").append(initialHtml);
					// }
					// else{
			  //     		$('<div class="initialsConnectedCD">'+initialHtml+'</div>').insertAfter($(this));
			  //     	}
					$.fn.comparativesWithInitialsConnectedBelow('CD', initialId, initial_symptom_id, thisId, is_earlier_connection);	
				}
			});
			
		}
		//source type combinedSourceInitials
		else
		{
			var comparativesConnectedCD_Found = 0;
			if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
			{
				if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
				{
					
					//(1) First Connect Comparative with Initial above
					//Here we are writing 2 statements to ensure that comparativesConnectedCD are together
					//so that comparativesConnectedCE does not fall in between
					
					// $('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").nextUntil(".initial").each(function()
					// {
					// 	if($(this).hasClass("comparativesConnectedCD"))
					// 	{
					// 		//This is not working
					// 		if(!$(this).next(".comparativesConnectedCD").next('.row'+initialId)){
					// 			$(this).append(comparativeHtml);
					// 		}
					// 		//This is working
					// 		$(this).append(comparativeHtml);

					// 		comparativesConnectedCD_Found = 1;
					// 	}
					// });
					if(comparativesConnectedCD_Found === 0)
					{
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
						{
							var thisConnectedParent = $(this).parent().attr("class");
							if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" && thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
							{
								//Now append comparativeHtml to Initial Symptom
								$(this).after('<div class="comparativesConnectedCD" style="display: none;">'+comparativeHtml+'</div>');
							}
						});
					}
					//(3) Finally we hide this Comparative symptom
					$("."+thisId).not(".comparativesConnectedCD ."+thisId).addClass("hidden");
					onLoad = 1;
			      	$.fn.initialsWithComparativesConnectedBelow('SWAP', initialId, thisId,onLoad,is_earlier_connection);	
					$.fn.swapComparativesIcons('CD',comparing_symptom_id);	


				}
			}
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	}    
	break;
	case "swapCE":
	{
	  	if(comparison_language=="en"){
			var initial_symptom_text = highlighted_initial_symptom_en;
			var comparing_symptom_text = highlighted_comparing_symptom_en;
		}
		else{
			var initial_symptom_text = highlighted_initial_symptom_de;
			var comparing_symptom_text = highlighted_comparing_symptom_de;
		}
		var earlier_connection = "";
		var previous_connection = "";
		var earlierSavedConnection = " ";

		if(is_earlier_connection=="1")
		{
			earlier_connection = " earlierConnection";
			earlierSavedConnection = " earlierSavedConnection ";

		}

		if(free_flag=="1")
		{
			previous_connection = " previousConnection";
		}
		//making initial div
		var initialHtml = '<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row initial '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		initialHtml = initialHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		initialHtml = initialHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		initialHtml = initialHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		initialHtml = initialHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-initial-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		initialHtml = initialHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="disconnectCE active" href="javascript:void(0)" title="Connect edit">CE</a></li><li><a class="symptom-swap-connect-btn active" href="javascript:void(0)" title="Swap connect"><i class="fas fa-recycle"></i></a></li></ul></div></div>';			


		
		//making comparative div
		var comparativeHtml ='<div class="row'+initial_symptom_id+'_'+comparing_symptom_id+' symptom-row comparing '+earlier_connection+previous_connection+'" id="row'+initial_symptom_id+'_'+comparing_symptom_id+'" data-year="'+comparing_year+'" data-source-original-language="'+comparing_quelle_original_language+'" data-quell-id="'+comparing_quelle_id+'" data-initial-symptom-de="'+initial_symptom_de+'" data-initial-symptom-en="'+initial_symptom_en+'" data-comparing-symptom-de="'+comparing_symptom_de+'" data-comparing-symptom-en="'+comparing_symptom_en+'" data-connection-id="'+connection_id+'" data-is-non-symptom-editable-connection="1">';
		comparativeHtml = comparativeHtml+'<div class="source-code is-excluded">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code include-it">-</div>';
		comparativeHtml = comparativeHtml+'<div class="source-code">'+comparing_quelle_code+'</div>';
		comparativeHtml = comparativeHtml+'<div class="symptom">'+comparing_symptom_text+'</div>';
		comparativeHtml = comparativeHtml+'<div class="percentage">'+matched_percentage+'%</div>';
		comparativeHtml = comparativeHtml+'<div class="info"><ul class="info-linkage-group"><li><a class="symptom-info-btn" title="info" href="javascript:void(0)"><i class="fas fa-info-circle"></i></a> </li> <li> <a class="symptom-comment-btn" title="comment" href="javascript:void(0)"><i class="fas fa-comment-alt"></i></a> </li> <li> <a class="symptom-footnote-btn" title="footnote" href="javascript:void(0)"><i class="fas fa-sticky-note"></i></a> </li> <li> <a class="symptom-translation-btn symptom-comparative-translation-btn" title="translation" href="javascript:void(0)" data-unique-id="row'+initial_symptom_id+'_'+comparing_symptom_id+'">T</a> </li> </ul></div>';
		comparativeHtml = comparativeHtml+'<div class="command"><ul class="command-group"><li><a class="symptom-soft-connect-btn" href="javascript:void(0)" title="Non secure connection"><i class="fas fa-exclamation-triangle"></i></a></li><li><a class="symptom-connect-btn disconnect active" href="javascript:void(0)" title="connect"><i class="fas fa-link"></i></a></li></ul></div></div>';


		var thisId = "row"+initial_symptom_id+'_'+comparing_symptom_id;
		var comparativeId = comparing_symptom_id;
		var initialId = "row"+initial_symptom_id;
		var combinedId = "row"+comparing_symptom_id; 
		
		//Checking if combined source comparison
		if(source_type == "combinedSourceComparative")
		{	
			var comparativesConnectedCD_Found = 0;
			var thisId = "row"+comparing_symptom_id+'_'+initial_symptom_id;
			initialId = "row"+comparing_symptom_id;
			//$.fn.swapComparativesIcons('CE',initial_symptom_id);	

			$('[class*=_' + initial_symptom_id + ']').each(function(){
				//Adding saved connections for comparative symptoms.
				if(!$(this).parents().hasClass("comparativesConnectedCD") && !$(this).parents().hasClass("comparativesConnectedCE") && !$(this).parents().hasClass("comparativesConnectedPASTE") && !$(this).parents().hasClass("comparativesConnectedPE"))
				{
					// $(this).parents('div.comparing').nextUntil(".comparing").each(function(){
					// 	console.log(this);
					// });
					
					if($(this).next().hasClass("initialsConnectedCE")){
						if(!$(this).next(".initialsConnectedCE").children('.initial').hasClass('row'+initial_symptom_id+'_'+comparing_symptom_id))
						//if(!$(this).next(".initialsConnectedCD").children('.initial').hasClass(thisId))
							$(this).next(".initialsConnectedCE").append(initialHtml);
					}
					else
					{
			      		$('<div class="initialsConnectedCE">'+initialHtml+'</div>').insertAfter($(this));
			      	}
					
					// if($(this).next().hasClass("initialsConnectedCE")){
					// 	$(this).next(".initialsConnectedCE").children().each(function(){
					// 		if(!($(this).hasClass('row'+initial_symptom_id+'_'+comparing_symptom_id))){
					// 			$(this).next(".initialsConnectedCE").append(initialHtml);
					// 		}
					// 	});
					// }
					// else
					// {
			  //     		$('<div class="initialsConnectedCE">'+initialHtml+'</div>').insertAfter($(this));
			  //     	}
					$.fn.comparativesWithInitialsConnectedBelow('CE', initialId, initial_symptom_id, thisId, is_earlier_connection);	
				}
			});
		}
		//source type combinedSourceInitials
		else
		{
			var comparativesConnectedCD_Found = 0;
			if($("."+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPE .initial").length != 0)
			{
				if($("#" + thisId).length !== 0 || $("#" + initialId).length !== 0 ) 
				{
					if(comparativesConnectedCD_Found === 0)
					{
						$('.'+initialId).not(".initialsConnectedCD .initial").not(".initialsConnectedCE .initial").not(".initialsConnectedPASTE .initial").not(".initialsConnectedPE .initial").each(function()
						{
							var thisConnectedParent = $(this).parent().attr("class");
							if(thisConnectedParent != "initialsConnectedCE" && thisConnectedParent != "initialsConnectedCD" && thisConnectedParent != "initialsConnectedPASTE" && thisConnectedParent != "initialsConnectedPE" && thisConnectedParent != "comparativesConnectedPE" && thisConnectedParent != "comparativesConnectedPASTE")
							{
								//Now append comparativeHtml to Initial Symptom
								$(this).after('<div class="comparativesConnectedCE" style="display: none;">'+comparativeHtml+'</div>');
							}
						});
					}
					//(3) Finally we hide this Comparative symptom
					$("."+thisId).not(".comparativesConnectedCE ."+thisId).addClass("hidden");
					onLoad = 1;
			      	$.fn.initialsWithComparativesConnectedBelow('SWAP', initialId, thisId,onLoad,is_earlier_connection);	
					$.fn.swapComparativesIcons('CE',comparing_symptom_id);	


				}
			}
		}
		//gen-ns disabled
		if(!($("."+initialId).find(".gen-ns").hasClass("ns-disabled"))){
			$("."+initialId).find(".gen-ns").addClass("ns-disabled");
		}
	}    
	break;
	default:
	    // code block
	} 
		
}