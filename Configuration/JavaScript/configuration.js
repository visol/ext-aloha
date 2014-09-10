(function(window, undefined) {

	window.Aloha = window.Aloha || {};
	// Manually set the version of jQuery for aloha and free the namespace
	var jQuery = window.jQuery.noConflict(true);
	// Hack, so that page edit doesn't throw js error
	window.TS = {
		PATH_typo3: ' '
	};
	
	window.Aloha.settings = {
		jQuery: jQuery,
		logLevels: {
			'error': true,
			'warn': true,
			'info': true,
			'deprecated': true,
			'debug': false
		},
		errorhandling : false,
		ribbon: false,

		"placeholder": {
			'*': '',
			'#typo3span': 'Placeholder for span'
		},
		"i18n": {
			// you can either let the system detect the users language (set acceptLanguage on server)
			// In PHP this would would be '<?=$_SERVER['HTTP_ACCEPT_LANGUAGE']?>' resulting in
			// "acceptLanguage": 'de-de,de;q=0.8,it;q=0.6,en-us;q=0.7,en;q=0.2'
			// or set current on server side to be in sync with your backend system
			"current": "en"
		},
		toolbar: {
			tabs: [
				// Format Tab
				{
					label: 'tab.format.label',
					showOn: { scope: 'Aloha.continuoustext' },
					components: [
						[
							'bold', 'strong', 'italic', 'emphasis', 'underline', '\n',
							'subscript', 'superscript', 'strikethrough', 'quote'
						], [
							'formatAbbr', 'formatNumeratedHeaders', 'toggleDragDrop', '\n',
							'toggleMetaView', 'wailang', 'toggleFormatlessPaste'
						], [
							'orderedList', 'unorderedList', 'alignCenter', 'alignJustify', '\n',
							'indentList', 'outdentList', 'alignLeft', 'alignRight', 'colorPicker'
						], [
							'formatBlock'
						], [
							'up','newContentElementBelow', 'edit', '\n',
							'down','unhide', 'hide', 'delete'
						]
					]
				},
				// Insert Tab
				/*
				{
					label: "tab.insert.label",
					showOn: { scope: 'Aloha.continuoustext' },
					components: [
						[ "createTable", "characterPicker", "insertLink",
						  "insertImage", "insertAbbr", "insertToc",
						  "insertHorizontalRule", "insertTag"]
					]
				}
				*/
			],
			exclude: [ 'tab.format.label', 'tab.insert.label' ]
		},
		"plugins": {
			"format": {
				//'h3' : ['fo', 'bar'],
				// all elements with no specific configuration get this configuration
				//config : [ 'b', 'i', 'h1', 'h2', 'h3', 'h4', 'p', 'edit', 'up', 'down', 'hide', 'unhide', 'newContentElementBelow', 'move','link','delete'],
				config : [ ],
				editables : {
					// no formatting allowed for title
					'.nostyles'					: [ ],
					'.alohaeditable-header'		: [ 'h1', 'h2', 'h3', 'h4' ],
					'.alohaeditable-text'		: [ 'b', 'i', 'sub', 'sup' ],
					'.alohaeditable-plaintext'	: [ ],
					'.alohaeditable-simplehtml'	: [ 'b', 'i', 'sub', 'sup' ], 
					'.alohaeditable-block'		: [ 'b', 'i', 'edit', 'sub', 'sup' ],
					'.alohaeditable-list'		: [ 'b', 'i', 'edit', 'sub', 'sup' ]
				}
			},
			"list": {
				// all elements with no specific configuration get an UL, just for fun :)
				config : [ ],
				editables : {
					'.nostyles'					: [ ],
					'.alohaeditable-header'		: [ ],
					'.alohaeditable-text'		: [ ],
					'.alohaeditable-plaintext'	: [ ],
					'.alohaeditable-simplehtml'	: [ ], 
					'.alohaeditable-block'		: [ 'ul', 'ol', 'dl' ],
					'.alohaeditable-list'		: [ ]
				},
				
				// Could enable this in bootstrap if aloha is available
				templates: {
					ul: {
						classes: [ ],
						template: '<ul><li>${first}<ul><li>${second}<ul><li>${third}</li></ul></li></ul></li></ul>',
						locale: {
							fallback: {first: 'first layer', second: 'second layer', third: 'third layer'}
						}
					},
					ol: {
						classes: [ ],
						template: '<ul><li>${first}<ul><li>${second}<ul><li>${third}</li></ul></li></ul></li></ul>',
						locale: {
							fallback: {first: 'first layer', second: 'second layer', third: 'third layer'}
						}
					},
					dl: {
						classes: [ ],
						template: '<ul><li>${first}<ul><li>${second}<ul><li>${third}</li></ul></li></ul></li></ul>',
						locale: {
							fallback: {first: 'first item', second: 'second item', third: 'third layer'}
						}
					},
				}
			}
		}
	};
})(window);