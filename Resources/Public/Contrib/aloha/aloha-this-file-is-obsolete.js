function fo(event, eventProperties) {
	var el = eventProperties.editable.obj;
	if (jQuery(el).hasClass('action-move')) {
		//	alert('yes');
		jQuery('button.button_down').removeClass('hide');
		jQuery('button.button_up').removeClass('hide');
	} else {
		alert('no');
		jQuery('button.button_down').addClass('hide');
		jQuery('button.button_up').addClass('hide');
	}
}

function saveEditable(event, eventProperties) {
	$.ajax({
		url: alohaUrl,
		data: ({
			content: eventProperties.editable.getContents(),
			'identifier': eventProperties.editable.getId(),
			'action':'save'
		}),
		cache: false,
		type: 'POST',

		success: function(xhr){
			$().el7r_notify({
				'text':xhr,
				'skin':'silver'
			});
		},
		error: function(xhr){
			$().el7r_notify({
				'text':xhr.responseText,
				'skin':'darkblue'
			});
		}
	});
}

GENTICS.Aloha.settings = {
	"i18n": {
		"current": "en"
	},
	"ribbon": false,
	"plugins": {
		"com.gentics.aloha.plugins.GCN": {
			"enabled": false
		},
		"com.gentics.aloha.plugins.List": {
			editables : {
				'.nostyles'	: [ ]
			}
		},
		"com.gentics.aloha.plugins.Link": {
			editables : {
				'.nostyles'	: [ ]
			}
		},
		"it.ringer.aloha.plugins.TYPO3": {
			"enabled": true,
			config : {
					// No links in the title.
				'.action-move'	: [ 'move' ]
			}
		},
		"com.gentics.aloha.plugins.Format": {
			config : [ 'b', 'i','u','del','sub','sup', 'p', 'title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'removeFormat'],
			editables : {
				'.nostyles'	: [ ]
			}
		}
	}
};

jQuery(document).ready(function() {
	$('.alohaeditable').aloha();



});

GENTICS.Aloha.EventRegistry.subscribe(GENTICS.Aloha, "editableDeactivated", saveEditable);