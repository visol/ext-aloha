/*!
* Aloha Editor
* Author & Copyright (c) 2010 Gentics Software GmbH
* aloha-sales@gentics.com
* Licensed unter the terms of http://www.aloha-editor.com/license.html
*/

define(
['aloha/plugin', 'css!highlighteditables/css/highlighteditables.css'],
function(Plugin) {
	"use strict";
	var
		jQuery = window.alohaQuery || window.jQuery, $ = jQuery,
		GENTICS = window.GENTICS,
		Aloha = window.Aloha;

	return Plugin.create('highlighteditables', {

		init: function () {

			// remember refernce to this class for callback
			var that = this;

			// highlight editables as long as the mouse is moving
			GENTICS.Utils.Position.addMouseMoveCallback(function () {
				var i, editable;

				for ( i = 0; i < Aloha.editables.length; i++) {
					editable = Aloha.editables[i];
					if (!Aloha.activeEditable && !editable.isDisabled()) {
						editable.obj.addClass('aloha-editable-highlight');
					}
				}
			});

			// fade editable borders when mouse stops moving
			GENTICS.Utils.Position.addMouseStopCallback(function () {
				that.fade();
			});

			// mark active Editable with a css class
			Aloha.bind(
					"aloha-editable-activated",
					function (jEvent, aEvent) {
						aEvent.editable.obj.addClass('aloha-editable-active');
						that.fade();
					}
			);

			// remove active Editable ccs class
			Aloha.bind("aloha-editable-deactivated",
					function (jEvent, aEvent) {
						aEvent.editable.obj.removeClass('aloha-editable-active');
					}
			);

		},
		/**
		 * fades all highlighted editables
		 */
		fade: function () {
			var
				i, editable,
				animateEnd = function () {
					jQuery(this).css('outline', '');
				};
			for ( i = 0; i < Aloha.editables.length; i++) {
				editable = Aloha.editables[i].obj;
				if (editable.hasClass('aloha-editable-highlight')) {
					editable.removeClass('aloha-editable-highlight')
						.css('outline', '2px solid #D9E273')
						.animate({
							outlineWidth : '0px'
						}, 300, 'swing', animateEnd);
				}
			}
		}

	});
});

