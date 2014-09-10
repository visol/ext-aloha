window.Aloha.ready( function() {
	var $ = window.Aloha.jQuery;
	// For back-compatibility
	window.alohaQuery = $;

	$('.alohaeditable').aloha();
	$('#aloha-not-loaded').hide();	
	$('#aloha-top-bar').show();

	$('#aloha-discardButton').click(function() {
		$.ajax({
			url: alohaUrl,
			data: ({
				'action':'discardSavings'
			}),
			cache: false,
			dataType : 'html',
			type: 'POST',
			success: function(xhr){
				$().el7r_notify({
					'text':xhr,
					'skin':'silver'
				});
				$('#aloha-saveButton').hide();
			},
			error: function(xhr){
				$().el7r_notify({
					'text':xhr.responseText,
					'skin':'darkred'
				});
			}
		});
	});
	$('#aloha-saveButton').click(function() {
		$.ajax({
			url: alohaUrl,
			data: ({
				'action':'commitSavings'
			}),
			cache: false,
			dataType : 'html',
			type: 'POST',
			success: function(xhr){
				$().el7r_notify({
					'text':xhr,
					'skin':'silver'
				});
				$('#aloha-saveButton').hide();
			},
			error: function(xhr){
				$().el7r_notify({
					'text':xhr.responseText,
					'skin':'darkred'
				});
			}
		});
	});

	$('#alohaeditor-icon').click(function(el) {
		$('#alohaeditor-icon').toggleClass('active');
		$('#alohaeditor-info').toggle();
	});
});