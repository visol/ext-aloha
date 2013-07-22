define([
	'aloha',
	'jquery',
	'aloha/plugin',
	'ui/ui',
	'ui/scopes',
	'ui/button',
	'i18n!typo3/nls/i18n',
	'i18n!aloha/nls/i18n',
	'css!typo3/css/typo3.css'],
function(
	Aloha,
	jQuery,
	Plugin,
	Ui,
	Scopes,
	Button,
	i18n,
	i18nCore
) {
	"use strict";
	var
		$ = jQuery,
		GENTICS = window.GENTICS;

	/**
	 * register the plugin with unique name
	 */
	return Plugin.create('typo3', {
		languages: ['en'],
		config: [],

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.createButtons();
			this.subscribeEvents();
		},

		/**
		 * Create the buttons
		 */
		createButtons: function () {
			var that = this;

			this.buttons = {};
			var buttons = [ 'up', 'down', 'edit', 'hide', 'unhide', 'newContentElementBelow', 'move','link','delete'];

			jQuery.each(buttons, function(j, button) {
				var position = 99;
				var askUserForAction = false;
				var size = 'small';
				var menuTab = 'Format';

				switch (button) {
					case 'edit':
						menuTab = 'Advanced';
						position = 2;
						break;
					case 'hide':
					case 'unhide':
						menuTab = 'Advanced';
						position = 2;
						askUserForAction = true;
						break;
					case 'up':
					case 'down':
						menuTab = 'Advanced';
						position = 1;
						break;
					case 'move':
						menuTab = 'Advanced';
						position = 2;
						break;
					case 'newContentElementBelow':
						position = 4;
						break;
					case 'link':
						position = 1;
						break;
					case 'delete':
						position = 4;
						askUserForAction = true;
						break;
				}

				that.buttons[button] = Ui.adopt(button, Button, {
					tooltip: i18n.t('button.' + button),
					icon: 'button_' + button,
					size : size,
					click: function() {
						var split = Aloha.activeEditable.getId().split('--');
						var customCloseUrl =  typo3BackendUrl + '../typo3conf/ext/aloha/Resources/Public/Contrib/shadowbox/close.html';
						var generalUrl = typo3BackendUrl + 'alt_doc.php?noView=1&returnUrl=' + customCloseUrl + '&';

						if (button == 'edit') {
							var url = generalUrl + 'edit[' + split[0] + '][' + split[2] + ']=edit';
							that.openLightbox(url);
						} else if(button == 'newContentElementBelow') {
							var url = generalUrl + 'edit[' + split[0] + '][-' + split[2] + ']=new';
							that.openLightbox(url);
						} else if(button == 'move') {
							var url = typo3BackendUrl + 'move_el.php?returnUrl=' + customCloseUrl + '&' + 'table=' + split[0] + '&uid=' + split[2];
							that.openLightbox(url);
						} else if(button == 'link') {
							var url = alohaUrl + '&rte=1';
							var url = typo3BackendUrl + '../typo3conf/ext/aloha/Classes/BrowseLink/index.php'; //@todo: check that path
							var vHWin = window.open(url,'FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');
							vHWin.focus()
//							that.openLightbox(url);
						} else {
							if (askUserForAction && (confirm(i18n.t('confirm.' + button))) || (!askUserForAction)) {
								that.ajaxRequest(button);
							}
						}
					}
				});

			});

		},

		/**
		 * Subscribe for events
		 */
		subscribeEvents: function () {
			var that = this;
			Aloha.bind(
				'aloha-editable-activated', function (jEvent, params) {
					that.disableSaveButton();

					jQuery.each(that.buttons, function(index, button) {
						if (jQuery(params.editable.obj).hasClass('action-' + index)) {
							that.buttons[index].show();
						} else {
							that.buttons[index].hide();
						}
					});
				}
			);

			Aloha.bind(
				'aloha-smart-content-changed',
				function (jEvent, args) {
					that.saveEditable(args.editable);
				}
			);


		},

		ajaxRequest: function (button) {
			$.ajax({
				url: alohaUrl,
				data: ({
					'action' : button ,
					'identifier': Aloha.activeEditable.getId()
				}),
				cache: false,
				dataType : 'html',
				type: 'POST',

				success: function(xhr){
					$().el7r_notify({
						'text':xhr,
						'skin':'silver'
					});
				},
				error: function(xhr){
					$().el7r_notify({
						'text':xhr.statusText,
						'skin':'darkblue'
					});
				}
			});
		},

		saveEditable:function(editable) {
			if (editable.isModified()) {
				var that = this;
				$.ajax({
					url: alohaUrl,
					data: ({
						content: editable.getContents(),
						'identifier': editable.getId(),
						'action':'save'
					}),
					cache: false,
					type: 'POST',

					success: function(xhr){
						$().el7r_notify({
							'text':xhr,
							'skin':'silver'
						});
						editable.setUnmodified();
					},
					error: function(xhr){
						$().el7r_notify({
							'text':xhr.responseText,
							'skin':'darkblue'
						});
					}
				});
			}
		},

		enableSaveButton:function() {
			jQuery('#aloha-saveButton').show();
		},
		disableSaveButton:function() {
			jQuery('#aloha-saveButton').hide();
		},

		openLightbox:function(url) {
			Shadowbox.open({ content: url, player:'iframe'});
		}

	});
});



function renderPopup_addLink2(theLink, cur_target, cur_class, cur_title) {
	// current selection or cursor position
	range = Aloha.Selection.getRangeObject();

	// if selection is collapsed then extend to the word.
	if (range.isCollapsed()) {
		window.GENTICS.Utils.Dom.extendToWord(range);
	}

	if ( range.isCollapsed() ) {
		// insert a link with text here
		linkText = this.i18n('newlink.defaulttext');
		newLink = window.alohaQuery('<a href="' + theLink + '">' + linkText + '</a>');
		window.GENTICS.Utils.Dom.insertIntoDOM(newLink, range, jQuery(Aloha.activeEditable.obj));
		range.startContainer = range.endContainer = newLink.contents().get(0);
		range.startOffset = 0;
		range.endOffset = linkText.length;
	} else {
		newLink = window.alohaQuery('<a href="' + theLink + '"></a>');
		GENTICS.Utils.Dom.addMarkup(range, newLink, false);
	}
	range.select();
}
