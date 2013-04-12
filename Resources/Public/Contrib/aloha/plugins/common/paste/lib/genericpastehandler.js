/*!
* Aloha Editor
* Author & Copyright (c) 2010 Gentics Software GmbH
* aloha-sales@gentics.com
* Licensed unter the terms of http://www.aloha-editor.com/license.html
*/
define(
['paste/abstractpastehandler'],
function(AbstractPasteHandler) {
	"use strict";

	var
		jQuery = window.alohaQuery || window.jQuery, $ = jQuery,
		GENTICS = window.GENTICS,
		Aloha = window.Aloha;

	/**
	 * Register the generic paste handler
	 */
	var GenericPasteHandler = AbstractPasteHandler.extend({
		/**
		 * Handle the pasting. Remove all unwanted stuff.
		 * @param jqPasteDiv
		 */
		handlePaste: function(jqPasteDiv) {
			// If we find an aloha-block inside the pasted content,
			// we do not modify the pasted stuff, as it most probably
			// comes from Aloha and not from other sources, and does
			// not need to be cleaned up.
			if (jqPasteDiv.find('.aloha-block').length > 0) {
				return;
			}
			// transform tables
			this.transformTables(jqPasteDiv);

			// remove comments
			this.removeComments(jqPasteDiv);

			// unwrap font and span tags
			this.unwrapTags(jqPasteDiv);

			// remove styles
			this.removeStyles(jqPasteDiv);

			// remove namespaced elements
			this.removeNamespacedElements(jqPasteDiv);

			// transform formattings
			this.transformFormattings(jqPasteDiv);
		},

		/**
		 * Transform tables which were pasted
		 * @param jqPasteDiv
		 */
		transformTables: function(jqPasteDiv) {
			// remove border, cellspacing, cellpadding from all tables
			jqPasteDiv.find('table').each(function() {
				jQuery(this).removeAttr('border').removeAttr('cellspacing').removeAttr('cellpadding');
			});
			// remove width, height and valign from all table cells
			jqPasteDiv.find('td').each(function() {
				jQuery(this).removeAttr('width').removeAttr('height').removeAttr('valign');
			});
		},

		/**
		 * Transform formattings
		 * @param jqPasteDiv
		 */
		transformFormattings: function(jqPasteDiv) {
			// find all formattings we will transform
			jqPasteDiv.find('strong,em,s,u').each(function() {
				if (this.nodeName.toLowerCase() == 'strong') {
					// transform strong to b
					Aloha.Markup.transformDomObject(jQuery(this), 'b');
				} else if (this.nodeName.toLowerCase() == 'em') {
					// transform em to i
					Aloha.Markup.transformDomObject(jQuery(this), 'i');
				} else if (this.nodeName.toLowerCase() == 's') {
					// transform s to del
					Aloha.Markup.transformDomObject(jQuery(this), 'del');
				} else if (this.nodeName.toLowerCase() == 'u') {
					// transform u?
					jQuery(this).contents().unwrap();
				}
			});
		},

		/**
		 * Remove all comments
		 * @param jqPasteDiv
		 */
		removeComments: function(jqPasteDiv) {
			var that = this;

			// ok, remove all comments
			jqPasteDiv.contents().each(function() {
				if (this.nodeType == 8) {
					jQuery(this).remove();
				} else {
					// do recursion
					that.removeComments(jQuery(this));
				}
			});
		},

		/**
		 * Remove some unwanted tags from content pasted from word
		 * @param jqPasteDiv
		 */
		unwrapTags: function(jqPasteDiv) {
			// unwrap contents of span,font and div tags
			jqPasteDiv.find('span,font,div').each(function() {
				jQuery(this).contents().unwrap();
			});
		},

		/**
		 * Remove styles
		 * @param jqPasteDiv
		 */
		removeStyles: function(jqPasteDiv) {
			// completely remove style tags
			jqPasteDiv.find('style').remove();

			// remove style attributes and classes
			jqPasteDiv.find('*').each(function() {
				jQuery(this).removeAttr('style').removeClass();
			});
		},

		/**
		 * Remove all elements which are in different namespaces
		 * @param jqPasteDiv
		 */
		removeNamespacedElements: function(jqPasteDiv) {
			// get all elements
			jqPasteDiv.find('*').each(function() {
				// try to determine the namespace prefix ('prefix' works for W3C
				// compliant browsers, 'scopeName' for IE)

				var nsPrefix = this.prefix ? this.prefix
						: (this.scopeName ? this.scopeName : undefined);
				// when the prefix is set (and different from 'HTML'), we remove the
				// element
				if ((nsPrefix && nsPrefix != 'HTML') || this.nodeName.indexOf(':') >= 0 ) {
					jQuery(this).contents().unwrap();
				}
			});
		}
	});

	return GenericPasteHandler;

});
