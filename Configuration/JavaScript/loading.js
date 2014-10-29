window.Aloha.ready( function() {
	var $ = window.Aloha.jQuery;
	// For back-compatibility
	window.alohaQuery = $;

		// If not in iframe, show "loader" and set margin on body.
	if(self==top) {
		// $('#aloha-not-loaded').show();
		$('body').css('margin-top','47px');
	}

	$('.alohaeditable').aloha();

		// If not in iframe, show topbar and hide loader, hide in iframe
	setTimeout(function () {
		if(self==top) {
			$('#aloha-not-loaded').hide();
			$('#aloha-top-bar').fadeIn("slow");
		} else {
			$('#aloha-not-loaded').hide();
			$('#aloha-top-bar').hide();
				// Update save and discard
			$.ajax({
				url: alohaUrl,
				data: ({
					'action':'updateSaveState'
				}),
				cache: false,
				dataType : 'html',
				type: 'POST',
				success: function(xhr){
					$().el7r_notify({
						'text':xhr,
						'skin':'hidden'
					});
				},
				error: function(xhr){
				}
			});
		}
	});

	$('#aloha-discardButton').click(function() {
			// Check if iframe exists, then commit save from that instead to get the correct page id
		if ($('#tx_pxafoundation_viewpage_iframe').length) {
			var iframe = $('#tx_pxafoundation_viewpage_iframe');
			$('#aloha-discardButton', iframe.contents()).trigger('click');
		} else {
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
						'skin':'success'
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
		}
	});
	$('#aloha-saveButton').click(function() {
			// Check if iframe exists, then commit save from that instead to get the correct page id
		if ($('#tx_pxafoundation_viewpage_iframe').length) {
			var iframe = $('#tx_pxafoundation_viewpage_iframe');
			$('#aloha-saveButton', iframe.contents()).trigger('click');
		} else {
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
						'skin':'success'
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
		}
	});

	$('#alohaeditor-icon').click(function(el) {
		$('#alohaeditor-icon').toggleClass('active');
		$('#alohaeditor-info').toggle();
	});
});