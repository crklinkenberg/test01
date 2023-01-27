var table= '';
$(document).ready(function () {
	$('#dataTable').on('init.dt',function() {
        $("#dataTable").removeClass('table-loader').show();
	});
	
	table = $('#dataTable').DataTable({
		"stateSave": true,
		"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],  
		"responsive": true,
    	"language": {
           "url": absoluteUrl+"lang/dataTableGerman.json"
       	},
		'columnDefs': [{
		 'targets': 0,
		 'searchable': false,
		 'orderable': false,
		 'className': 'dt-body-center',
		 'render': function (data, type, full, meta){
		     return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
		 }
		}]
	});
	// table
	//     .order( [ 3, 'asc' ] )
	//     .draw();


	// table.on('search.dt', function() {
	// 	 table.rows( { filter : 'applied'} ).order([ 3, 'asc' ]).draw();
	// }); 

	$('.reset-datatable-state a').click( function() {
		table.state.clear();
		//window.location.reload();
	});
	// Handle click on checkbox 
	$('#custom-table input[type="checkbox"]').change( function() {
		alert('checked')
	});

	$('#listViewForm').on('submit', function(e) {
		$(".delete-hidden-ids").remove();
		e.preventDefault();
		var form = $(this);
		var actionType = form.data('action');
    	var sourceType = form.data('source');
    	var sourceIdName = form.data('source_id_name');
    	url = baseApiURL+sourceType+'/'+actionType;
		var count = 0;
		// Iterate over all checkboxes in the table
		table.$('input[type="checkbox"]').each(function() {
			// If checkbox is checked
			if(this.checked) {
			   // Create a hidden element 
			   $(form).append(
			      $('<input>')
			         .attr('type', 'hidden')
			         .attr('class', 'delete-hidden-ids')
			         .attr('name', sourceIdName+'[]')
			         .val(this.value)
			   );
			   count++;
			} 
	  	});
		if(count == 0) {
			e.preventDefault();
		} else {
			swal({
			  title: 'Bist du sicher?',
			  text: "Du kannst diesen Vorgang nicht rückgängig machen!",
			  type: 'warning',
			  showCancelButton: true,
			  confirmButtonColor: '#3085d6',
			  cancelButtonColor: '#d33',
			  confirmButtonText: 'Ja, lösche es!',
			  cancelButtonText: 'Nein, abbrechen!',
			}).then((result) => {
			  	if (result.value) {
			  		var request = $.ajax({
			            type: 'POST',
			            url: url,
			            headers: {
					       "Authorization" : "Bearer "+token
					    },
			            data: new FormData(this),
			            contentType: false,
			            cache: false,
			            processData:false,
			            beforeSend:function() {
							$.blockUI({ message: '<h4><i class="fa fa-refresh fa-spin"></i> Einen Augenblick...</h4>' }); 

						},
						complete:function(jqXHR, status){
							$.unblockUI();
						}
			        });
			        request.done(function(response) {
			        	function jsUcfirst(string) 
						{
			    			return string.charAt(0).toUpperCase() + string.slice(1);
						}
						var responseData = null;
						try {
							responseData = JSON.parse(response); 
						} catch (e) {
							responseData = response;
						}
						var status = responseData.status;
						switch (status) { 
							case 1: 
								console.log(responseData.message);
								break;
							case 2:
								console.log(responseData.message);
								if(sourceType == 'user') {
									sourceType = 'Benutzer';
								}
								var message = jsUcfirst(sourceType)+ ' erfolgreich gelöscht';
								table.$('input[type="checkbox"]').each(function() {
									if($(this).prop("checked") == true) {
										table.row( $(this).parents('tr') ).remove().draw();
									}
						  		});
								swal({
									type: 'success',
									title: 'Glückwunsch',
									text: message,
								});
								break;
							case 3:
								var errorMessage = '';
								for(let key in responseData.content) { 
									errorMessage += `<p>${responseData.content[key]}</p>`;
								}
								swal({
									type: 'error',
									title: 'Hoppla...',
									html: errorMessage,
								}); 
								console.log(errorMessage);
								console.log(responseData.message);
								break;
							case 4: 
								var errorMessage = responseData.message;
								swal({
									type: 'error',
									title: 'Hoppla...',
									html: errorMessage,
								});
								console.log(responseData.message);
								break;
							case 5: 
								var errorMessage = responseData.message;
								swal({
									type: 'error',
									title: 'Hoppla...',
									html: errorMessage,
								});
								console.log(responseData.message);
								break;
							case 6: 
								var errorMessage = responseData.message;
								swal({
									type: 'error',
									title: 'Hoppla...',
									html: errorMessage,
								});
								console.log(responseData.message);
								break;
							default:
								console.log('Unexpected errors');
						}
					});

					request.fail(function(jqXHR, textStatus) {
						var errorData = null;
						try {
						  errorData = JSON.parse(jqXHR); 
						} catch (e) {
						  errorData = jqXHR;
						}
						swal({
							type: 'error',
							title: 'Hoppla...',
							html: errorData,
						});
						console.log("Error : "+errorData);
					});
			   	}  else {
				   	table.$('input[type="checkbox"]').each(function() {
						$(this).prop("checked", false);
			  		});
			   }
			})
		}
		
	});
	
});