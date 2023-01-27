$(window).bind("load", function() {
	console.log('loaded');
	$("#loader").addClass("hidden");
});

$(document).on('click', '.comparison-history-header-btn', function(e){
    e.preventDefault();
    $(this).find('span').html(function(_, value) {
  		return value == '<i class="fas fa-angle-up"></i>' ? '<i class="fas fa-angle-down"></i>' : '<i class="fas fa-angle-up"></i>'
	});
	var rowId = $(this).parent().parent().attr('id');
    $('.sub_'+rowId).slideToggle(100, function() {});
});

$(document).on('click', '.reactivate-quelle', function(){
	var $th = $(this);
	if($th.hasClass('processing'))
		return;
	$th.addClass('processing');
	var comparisonHistoryId = $(this).attr("data-comparison-history-id");
    var arzneiId = $(this).attr("data-arznei-id");
    var quelleId = $(this).attr("data-quelle-id");

	var con = confirm("Are you sure you want to reactivate this state?");
	if (con)
	{
		if(comparisonHistoryId != "" && arzneiId != "" && quelleId != ""){
			$('#reactivate_'+comparisonHistoryId).prop('disabled', true);
			$('#reactivate_'+comparisonHistoryId).html('<img src="assets/img/loader.gif" alt="Loader">');
			$.ajax({
				type: 'POST',
				url: 'reactivate-a-comparison-history.php',
				data: {
					comparison_history_id: comparisonHistoryId,
					quelle_id: quelleId,
					arznei_id: arzneiId
				},
				dataType: "json",
				success: function( response ) {
					console.log(response);
					if(response.status == "success"){
						// $('#reactivate_'+comparisonHistoryId).prop('disabled', false);
						// $('#reactivate_'+comparisonHistoryId).html('Reactivate');
						// $("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
						// $("#globalMsgModal").modal('show');
						//location.reload();
						$th.removeClass('processing');
						window.location.href = "<?php echo $baseUrl ?>materia-medica.php";
						
					}else{
						$th.removeClass('processing');
						$('#reactivate_'+comparisonHistoryId).prop('disabled', false);
						$('#reactivate_'+comparisonHistoryId).html('Reactivate');
						$("#global_msg_container").html('<p class="text-center">'+response.message+'</p>');
						$("#globalMsgModal").modal('show');
					}
				}
			}).fail(function (response) {
				$th.removeClass('processing');
				$('#reactivate_'+comparisonHistoryId).prop('disabled', false);
				$('#reactivate_'+comparisonHistoryId).html('Reactivate');
				$("#global_msg_container").html('<p class="text-center">Operation failed. Please reload and try!</p>');
				$("#globalMsgModal").modal('show');

				if ( window.console && window.console.log ) {
					console.log( response );
				}
			});
		} else {
			$th.removeClass('processing');
			$('#reactivate_'+comparisonHistoryId).prop('disabled', false);
			$('#reactivate_'+comparisonHistoryId).html('Reactivate');
			$("#global_msg_container").html('<p class="text-center">Operation failed, some required data not found. Please reload and try!</p>');
			$("#globalMsgModal").modal('show');
		}
	}
	else
	{
		$th.removeClass('processing');
		return false;
	}
});

//arznei custom search starts
$('#arznei_id').select2({
	// options 
	searchInputPlaceholder: 'Search Arznei...'
});

$('body').on( 'submit', '#arznei_search_medica', function(e) {
	var arznei_id = $("#arznei_id").val();
	console.log(arznei_id);
});
//arznei custom search ends