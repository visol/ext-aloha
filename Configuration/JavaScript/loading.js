require(["aloha/jquery"],
	function(aQuery) {

		require.ready(function() {
			// Prepare
			var $ = aQuery,
			$body = $('body');
			// Bind to Aloha Ready Event
			$body.bind('aloha',function() {
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
							that.changeSaveButton();
						},
						error: function(xhr){
							$().el7r_notify({
								'text':xhr.responseText,
								'skin':'darkblue'
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
							that.changeSaveButton();
						},
						error: function(xhr){
							$().el7r_notify({
								'text':xhr.responseText,
								'skin':'darkblue'
							});
						}
					});
				});

				$('#alohaeditor-icon').click(function(el) {
					$('#alohaeditor-icon').toggleClass('active');
					$('#alohaeditor-info').toggle();
				});


			});

		});

	});