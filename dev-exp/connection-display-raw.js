$.fn.connectionsDisplayRaw = function(comparing_symptom_id, initial_symptom_id, matched_percentage, comparing_quelle_id,initial_quelle_id, highlighted_comparing_symptom_en, highlighted_comparing_symptom_de, highlighted_initial_symptom_en, highlighted_initial_symptom_de, comparison_language, comparing_quelle_code, initial_quelle_code, comparing_year, comparing_symptom_de, comparing_symptom_en, comparing_quelle_original_language,initial_quelle_original_language, initial_year, initial_symptom_de, initial_symptom_en,source_type, connection_type,is_earlier_connection,free_flag, connection_id,  comparing_symptom_de_raw, comparing_symptom_en_raw)
{
	var onLoad = 0;
	var comparing_symptom_text = comparing_symptom_de_raw;
	//making comparative div
	var comparativeHtml = '<div class="connected-symptom">&nbsp;<i class="fas fa-plus"></i> '+comparing_symptom_text+'</div>';
	$('.row'+initial_symptom_id).each(function(){
		//Now append comparativeHtml to Initial Symptom
		$(this).after('<div class="connected-symptom">'+comparativeHtml+'</div>');
	});
} 