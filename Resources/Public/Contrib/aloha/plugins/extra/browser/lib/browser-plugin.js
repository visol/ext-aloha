/*!--------------------------------------*
  | Gentics Software GmbH                |
  +--------------------------------------+
  | Aloha.Browser                        |
  +--------------------------------------+
  | browser.js                           |
  *--------------------------------------*/

/**
 * Aloha.Browser
 *
 * The browser is an interface to interact with a Repository Managers.
 *
 * Reference:
 *		www.aloha-editor.org/wiki/Repository
 * 3rd party tools:
 *		www.jstree.com/documentation/core
 *		www.trirand.com/blog/ (jqGrid)
 *		layout.jquery-dev.net/
 */
define(
[
 'aloha/jquery',
 'aloha/plugin', 
 'aloha/floatingmenu', 
 'i18n!browser/nls/i18n', 
 'i18n!aloha/nls/i18n',
 'css!browser/css/browsercombined.css',
 'jquery-plugin!browser/jquery.ui',
 'jquery-plugin!browser/ui-layout',
 'jquery-plugin!browser/grid.locale.en',
 'jquery-plugin!browser/jquery.jqGrid',
 'jquery-plugin!browser/jquery.jstree'
],
function(jQuery, Plugin, FloatingMenu, i18n, i18nCore) {
	"use strict";
	
	var $ = jQuery,
		GENTICS = window.GENTICS,
		Aloha = window.Aloha;

	
	
	// Create local reference for quicker look-up
	var console = window.console;
	if (typeof console == typeof undefined) {
		console = {
			log   : function () {},
			warn  : function () {},
			error : function () {}
		};
	}
	
	return Plugin.create('browser', {
	
		// ------------------------------------------------------------------------
		// Local (helper) variables
		// ------------------------------------------------------------------------
		
		ns : 'aloha-browser',
		uid : +(new Date),
		// namespaced classnames
		nsClasses : {},
				
		init: function() {
			this.nsClasses = {
				tree              : this.nsClass('tree'),
				'tree-header'     : this.nsClass('tree-header'),
				'grab-handle'     : this.nsClass('grab-handle'),
				shadow            : this.nsClass('shadow'),
				'rounded-top'     : this.nsClass('rounded-top'),
				list              : this.nsClass('list'),
				'list-altrow'     : this.nsClass('list-altrow'),
				'list-resizable'  : this.nsClass('list-resizable'),
				'list-pager'      : this.nsClass('list-pager'),
				'list-pager-left' : this.nsClass('list-pager-left'),
				'list-btns'       : this.nsClass('list-btns'),
				'search-btn'      : this.nsClass('search-btn'),
				'search-field'    : this.nsClass('search-field'),
				'search-icon'     : this.nsClass('search-icon'),
				'close-btn'       : this.nsClass('close-btn'),
				btn               : this.nsClass('btn'),
				btns              : this.nsClass('btns'),
				grid              : this.nsClass('grid'),
				clear             : this.nsClass('clear'),
				inner             : this.nsClass('inner'),
				'modal-overlay'   : this.nsClass('modal-overlay'),
				'modal-window'    : this.nsClass('modal-window'),
				'grap-handle'     : this.nsClass('grap-handle')
			};
			
			//TODO load options from configuration
			var opts = {};
			// Defaults
			var options = $.extend({
				// Set to true for development and debugging
				verbose: false,
				// The repository manager which this browser will interface with
				repositoryManager: Aloha.RepositoryManager,
				objectTypeFilter: [],
				renditionFilter: ['cmis:none'], // ['*']
				filter: ['url'],
				// DOMObject to which this instance of browser is bound to
				element: undefined,
				// root folder id
				rootFolderId: 'aloha',
				// root path to where Browser resources are located
				rootPath: Aloha.getPluginUrl('browser'),
				treeWidth: 300,
				listWidth: 'auto',
				pageSize: 10,
				columns: {
					icon:    {title: '',        width: 30,  sortable: false, resizable: false},
					name:    {title: 'Name',    width: 250, sorttype: 'text'},
					url:     {title: 'URL',     width: 250, sorttype: 'text'},
					preview: {title: 'Preview', width: 200, sorttype: 'text'}
				}
			}, opts || {});
			
			// If no element, the we will an overlay element onto which we will bind
			// our browser
			if (typeof options.element != 'object') {
				options.element = this.createOverlay();
			}
			
			// Hash to store callbacks functions for each instance of browser
			this._callbacks = {};
			// Cache of repository objects
			this._objs = {};
			//
			this._searchQuery = null;
			this._orderBy = null;
			
			//---------------------------------------------------------------------
			// paging properties
			//---------------------------------------------------------------------
			this._pagingOffset = 0;
			// Total number of objects in a given folder. We don't use null because
			// isNaN(null) == false ! *sigh*
			this._pagingCount = undefined;
			this._pagingBtns = {
				first : null,
				end   : null,
				next  : null,
				prev  : null
			};
			
			// Register user defined implement methods and callbacks, and remove
			// them from the options object
			// TODO: Consider deprecating this altogether
			if (typeof options.implement === 'object') {
				$.each(options.implement, function (k, v) {
					that[k] = v;
				});
				
				delete options.implement;
			}
			
			// TODO: Consider deprecating this too
			if (typeof options.callbacks === 'object') {
				$.each(options.callbacks, function () {
					that.callback(this[0], this[1]);
				});
				
				delete options.callbacks;
			}
			
			// Insert the remaining options as properties of this object
			$.extend(this, options);
			var that = this,
				tree_width = this.treeWidth,
				give = tree_width / 5;
			
			this.preloadImages();
			
			this.uid = ++this.uid;
			
			this.element.attr('data-aloha-browser', this.uid).html('');
			
			this.grid = this.createGrid(this.element);
			this.tree = this.createTree(this.grid.find('.ui-layout-west'));
			this.list = this.createList(this.grid.find('.ui-layout-center'));
			
			this.grid.layout({
				west__size    : tree_width - 1,
				west__minSize : tree_width - give,
				west__maxSize : tree_width + give,
				center__size  : this.listWidth,
				paneClass     : 'ui-layout-pane',
				resizerClass  : 'ui-layout-resizer',
				togglerClass  : 'ui-layout-toggler',
				onresize      : function (name, element) {
					if (name == 'center') {
						that.list.setGridWidth(element.width());
					}
				}
				// , applyDefaultStyles: true // debugging
			})
			// *** Fix for a ui-layout bug in chrome ***
			.sizePane('west', tree_width);
			
			this.preventSelection();
			
			// Not working
			$('body').bind('aloha-repository-error', function (error) {
				console.warn(
					'Error occured on request to repository: ', error.repository.repositoryId +
					'\nMessage: "' + error.message + '"'
				);
			});
			
			this.close();
		},
		
		// ------------------------------------------------------------------------
		// Local (helper) functions
		// ------------------------------------------------------------------------
		
		// TODO: This suffices for now. But we are to consider a more robust
		//		 templating engine.
		// TODO: Offer parameter to define left and right delimiters in case the
		//		 default "{", and "}" are problematic
		supplant : function(str, obj) {
			return str.replace(/\{([a-z0-9\-\_]+)\}/ig, function (str, p1, offset, s) {
				var replacement = obj[p1] || str;
				return (typeof replacement == 'function') ? replacement() : replacement;
			});
		},
		
		/**
		 * Wrapper to all the supplant method on a given string, taking the
		 * nsClasses object as the associative array containing the replacement
		 * pairs
		 *
		 * @param {String} str
		 * @return {String}
		 */
		renderTemplate : function(str) {
			return (typeof str === 'string') ? this.supplant(str, this.nsClasses) : str;
		},
		
		/**
		 * Generates a selector string with this component's namepsace prefixed the
		 * each classname
		 *
		 * Usage:
		 *		nsSel('header,', 'main,', 'foooter ul')
		 *		will return
		 *		".aloha-myplugin-header, .aloha-myplugin-main, .aloha-mypluzgin-footer ul"
		 *
		 * @return {String}
		 */
		nsSel : function() {
			var strBldr = [], prx = this.ns;
			$.each(arguments, function () { strBldr.push('.' + (this == '' ? prx : prx + '-' + this)); });
			return strBldr.join(' ').trim();
		},
		
		/**
		 * Generates s string with this component's namepsace prefixed the each
		 * classname
		 *
		 * Usage:
		 *		nsClass('header', 'innerheaderdiv')
		 *		will return
		 *		"aloha-myplugin-header aloha-myplugin-innerheaderdiv"
		 *
		 * @return {String}
		 */
		nsClass : function (){
			var strBldr = [], prx = this.ns;
			$.each(arguments, function () { strBldr.push(this == '' ? prx : prx + '-' + this); });
			return strBldr.join(' ').trim();
		},
		
		
		
			
		instanceOf: 'Aloha.Browser',
		
		// ui components
		grid: null,
		tree: null,
		list: null,
		
					
		preventSelection: function () {
			this.grid
				.attr('unselectable', 'on')
				.css('-moz-user-select', 'none')
				.each(function() {
					this.onselectstart = function() {
						return false;
					};
				});
		},
		
		preloadImages: function () {
			var that = this;
			$.each([
				'arrow-000-medium.png',
				'arrow-180.png',
				'arrow-315-medium.png',
				'arrow-stop-180.png',
				'arrow-stop.png',
				'arrow.png',
				'control-stop-square-small.png',
				'folder-horizontal-open.png',
				'folder-open.png',
				'magnifier-left.png',
				'page.png',
				'picture.png',
				'sort-alphabet-descending.png',
				'sort-alphabet.png'
			], function () {(new Image()).src = that.rootPath + '/img/' + this;});
		},
		
		/**
		 * TODO: Is there a way we can prevent potential infinite loops caused
		 * by programmer error?
		 *
		 * @param String fn - Name of any Browser method
		 */
		trigger: function (fn, returned) {
			var cb = this._callbacks,
				i, l,
				func = cb[fn];
			
			if (typeof func === 'object') {
				for (i = 0, l = func.length; i < l; i++) {
					if (typeof func[i] === 'function') {
						func[i].call(this, returned);
					}
				}
			}
		},
		
		/**
		 * Handles the binding of callbacks to any method in the browser.
		 * Removes the necessity for functions to manually trigger callbacks
		 * events within themselves by wrapping them within an function that
		 * does it on their behalf when necessary.
		 * A user can simply do the following:
		 *		browser.callback('anyFunction', function () { alert('called back'); });
		 * This will work regardless of whether browser.anyFunction manually
		 * triggers any events.
		 *
		 * this.enableCallbacks actually does most of the heavy lifting.
		 *
		 * @param String fn - Name of any browser method
		 * @param Object cb - Callback function to invoke
		 */
		callback: function (fn, cb) {
			if (typeof this[fn] != 'function') {
				this.warn(
					'Unable to add a callback to "' + fn +
					'" because it is not a method in Aloha.Browser.'
				);
				
				return this;
			}
			
			if (typeof cb != 'function') {
				this.warn(
					'Unable to add a callback to "' + fn + '" because '	+
					'the callback object that was given is of type "'	+
					(typeof cb) + '". '									+
					'The callback object needs to be of type "function".'
				);
				
				return this;
			}
			
			if (this._callbacks[fn] == undefined) {
				if (this.enableCallbacks(fn)) {
					this._callbacks[fn] = [cb];
				}
			} else {
				this._callbacks[fn].push(cb);
			}
			
			return this;
		},
		
		/**
		 * Work-horse for this.callback method
		 */
		enableCallbacks: function (fn) {
			var browser_inst = this,
				func = this[fn];
			if (typeof func === 'function') {
				this[fn] = function () {
					var returned = func.apply(browser_inst, arguments);
					browser_inst.trigger.call(browser_inst, fn, returned);
					return returned;
				};
				
				return true;
			} else {
				this.warn(
					'Cannot enable callbacks for function "' + fn +
					'" because no such method was found in Aloha.Browser.'
				);
				
				return false;
			}
		},
		
		getRepoChildren: function (params, callback) {
			var that = this;
			this.repositoryManager.getChildren(params, function (items) {
				that.processRepoResponse(items, callback);
			});
		},
		
		queryRepository: function (params, callback) {
			var that = this;
			
			this.repositoryManager.query(params, function (response) {
				that.processRepoResponse(
					(response.results > 0) ? response.items : [],
					callback
				);
			});
		},
		
		processRepoResponse: function (items, callback) {
			var that = this,
				data = [];
			
			$.each(items, function () {
				data.push(that.harvestRepoObject(this));
			});
			
			if (typeof callback === 'function') {
				callback.call(this, data);
			}
		},
		
		/**
		 * Convert a repository object into an object that can be used with our
		 * tree component. Also add a reference to this object in our objs hash.
		 * According to the Repository specification, each object will at least
		 * have the following properties at least: id, name, url, and type. Any
		 * and all other attributes are optional.
		 */
		harvestRepoObject: function (obj) {
			this.uid++;
			
			var repo_obj = this._objs[this.uid] = $.extend(obj, {
				uid: this.uid,
				loaded: false
			});
			
			return this.processRepoObject(repo_obj);
		},
		
		/**
		 * Should return an object that is usable with your tree component
		 */
		processRepoObject: function (obj) {
			var icon = '';
			
			switch (obj.baseType) {
			case 'folder':
				icon = 'folder';
				break;
			case 'document':
				icon = 'document';
				break;
			}
			
			return {
				data: {
					title : obj.name, 
					attr  : {'data-rep-oobj': obj.uid}, 
					icon  : icon
				},
				state: (obj.hasMoreItems || obj.baseType === 'folder') ? 'closed' : null,
				resource: obj
			};
		},
		
		fetchRepoRoot: function (callback) {
			this.getRepoChildren(
				{inFolderId: this.rootFolderId},
				function (data) {
					if (typeof callback === 'function') {
						callback(data);
					}
				}
			);
		},
		
		/**
		 * User should implement this according to their needs
		 * @param Object item - Repository resource for a row
		 */
		renderRowCols: function (item) {
			var row = {};
			
			$.each(this.columns, function (colName, v) {
				switch (colName) {
				case 'icon':
					row.icon = '<div class="aloha-browser-icon aloha-browser-icon-' + item.type + '"></div>';
					break;
				default:
					row[colName] = item[colName] || '--';
				}
			});
			
			return row;
		},
		
		/**
		 * User should implement this according to their needs
		 * @param Object item - Repository resource for a row
		 */
		onSelect: function (item) {},
		
		/**
		 * Fetch an object's  children if we haven't already done so
		 */
		fetchChildren: function (obj, callback) {
			var that = this;
			
			if (obj.hasMoreItems == true || obj.baseType === 'folder') {
				if (obj.loaded == false) {
					this.getRepoChildren(
						{
							inFolderId   : obj.id,
							repositoryId : obj.repositoryId
						},
						function (data) {
							that._objs[obj.uid].loaded = true;
							
							if (typeof callback === 'function') {
								callback(data);
							}
						}
					);
				}
			}
		},
		
		getObjectFromCache: function (node) {
			var uid,
				obj;
			
			if (typeof node === 'object') {
				uid = node.find('a:first').attr('data-rep-oobj');
				obj = this._objs[uid];
			}
			
			return obj;
		},
		
		rowClicked: function (event) {
			var row = $(event.target).parent('tr'),
				item = null,
				uid;
			
			if (row.length > 0) {
				uid = row.attr('id');
				item = this._objs[uid];
				this.onSelect(item);
				this.close();
			}
			
			return item;
		},
		
		createTree: function (container) {
			var that = this,
				tree = $(that.renderTemplate('<div class="{tree}">')),
				header = $(that.renderTemplate(
					'<div class="{tree-header} {grab-handle}">\
						Repository Browser\
					</div>'
				));
			
			container.append(header, tree);
			
			tree.height(this.grid.height() - header.outerHeight(true))
				.bind('before.jstree', function (event, data) {
					//console.log(data.func);
				})
				.bind('loaded.jstree', function (event, data) {
					$('>ul>li', this).first().css('padding-top', 5);
				})
				.bind('select_node.jstree', function (event, data) {
					// This fixes a bug in jsTree
					if (data.args[0].context) {
						return;
					}
					
					var node = data.rslt.obj,
						folder = that.getObjectFromCache(node);
					
					if (typeof folder === 'object') {
						that._pagingOffset = 0;
						that._searchQuery = null;
						that._currentFolder = folder;
						that.fetchItems(folder, that.processItems);
					}
				})
				.jstree({
					rootFolderId: '3',
					plugins: ['themes', 'json_data', 'ui'],
					core: {
						// TODO: Override the standard animation by
						// setting this to 0, hiding the child node and
						// re-animating it in our own way
						animation: 250
					},
					themes: {
						theme : 'browser',
						url   : that.rootPath + '/css/jstree.css',
						dots  : true,
						icons : true
					},
					json_data: {
						data: function (/* node, callback */) {
							that.fetchSubnodes.apply(that, arguments);
						},
						correct_state: true
					},
					ui: {select_limit: 1}
				});
			
			return tree;
		},
		
		createGrid: function (container) {
			var grid = $(this.renderTemplate(
				'<div class="{grid} {shadow} {rounded-top}"> \
					<div class="ui-layout-west"></div>		 \
					<div class="ui-layout-center"></div>	 \
				</div>'
			));
			
			container.append(grid);
			
			return grid;
		},
		
		createList: function (container) {
			var that = this,
				list = $(that.renderTemplate(
						'<table id="jqgrid_needs_something_anything_here"\
							class="{list}"></table>'
					)),
				colNames = [''],
				colModel = [{ // This is a hidden utility column to help us with auto sorting
					name	 : 'id',
					sorttype : 'int',
					firstsortorder: 'asc',
					hidden	 : true
				}];
			
			$.each(this.columns, function (k, v) {
				colNames.push(v.title);
				colModel.push({
					name	  : k,
					width	  : v.width,
					sortable  : v.sortable,
					sorttype  : v.sorttype,
					resizable : v.resizable
				});
			});
			
			container.append(
				list,
				$(this.renderTemplate('<div id="{list-pager}">'))
			);
			
			list.jqGrid({
				datatype	: 'local',
				width		: container.width(),
				shrinkToFit	: true,
				colNames	: colNames,
				colModel	: colModel,
				caption		: '&nbsp;',
				altRows		: true,
				altclass	: that.nsClass('list-altrow'),
				resizeclass : that.nsClass('list-resizable'),
				pager		: '#' + that.nsClass('list-pager'), // http://www.trirand.com/jqgridwiki/doku.php?id=wiki:pager&s[]=pager
			//	rowNum		: this.pageSize,	  // # of records to view in the grid. Passed as parameter to url when retrieving data from server
				viewrecords	: true,
				// Event handlers: http://www.trirand.com/jqgridwiki/doku.php?id=wiki:events
				// fires after click on [page button] and before populating the data
				onPaging	: function (button) {},
				// called if the request fails
				loadError	: function (xhr, status, error) {},
				// Raised immediately after row was double clicked. 
				ondblClickRow: function (rowid, iRow, iCol, e) {},
				// fires after all the data is loaded into the grid and all other processes are complete
				gridComplete : function () {},
				// executed immediately after every server request 
				loadComplete : function (data) {}
			});
			
			container.find('.ui-jqgrid-bdiv').css(
				'height',
				this.grid.height() - (
					container.find('.ui-jqgrid-titlebar').outerHeight(true) +
					container.find('.ui-jqgrid-hdiv').outerHeight(true) + 
					container.find('.ui-jqgrid-pager').outerHeight(true)
				)
			);
			
			list.dblclick(function () {
				that.rowClicked.apply(that, arguments);
			});
			
			// Override jqGrid pageing
			container.find('.ui-pg-button').unbind().find('>span.ui-icon').each(function () {
				var dir = this.className.match(/ui\-icon\-seek\-([a-z]+)/)[1];
				
				that._pagingBtns[dir] = $(this).parent()
					.addClass('ui-state-disabled')
					.click(function () {
						if (!$(this).hasClass('ui-state-disabled')) {
							that.doPaging(dir);
						}
					});
			});
			
			// TODO: implement this once repositories can handle it, hidding it for now
			container.find('.ui-pg-input').parent().hide()
			container.find('.ui-separator').parent().css('opacity', 0).first().hide();
			container.find('#' + that.nsClass('list-pager-left')).hide();
			
			this.createTitlebar(container);
			
			//this.grid.find('.loading').html('Loading...');
			
			// Override jqGrid sorting
			var listProps = list[0].p;
			container.find('.ui-jqgrid-view tr:first th div').each(function(i) {
				$(this).unbind().click(function (event) {
					event.stopPropagation();
					that.sortList(listProps.colModel[i], this);
				});
			});
			
			return list;
		},
		
		createTitlebar: function (container) {
			var that = this,
				search,
				bar = container.find('.ui-jqgrid-titlebar'),
				btns = $(that.renderTemplate(
					'<div class="{btns}">							 \
						<input type="text" class="{search-field}" /> \
						<span class="{btn} {search-btn}">			 \
							<span class="{search-icon}"></span>		 \
						</span>										 \
						<span class="{btn} {close-btn}">Close</span> \
						<div class="{clear}"></div>					 \
					</div>'
				));
			
			bar.addClass(that.nsClass('grab-handle')).append(btns);
			bar.find(that.nsSel('search-btn')).click(function () {
				that.triggerSearch();
			});
			bar.find(that.nsSel('search-field')).keypress(function (event) {
				if (event.keyCode == 13) { // on Enter
					that.triggerSearch();
				}
			});
			bar.find(that.nsSel('close-btn')).click(function () {
				that.close();
			});
			bar.find(that.nsSel('btn')).mousedown(function () {
				$(this).addClass(that.nsClass('pressed'));
			}).mouseup(function () {
				$(this).removeClass(that.nsClass('pressed'));
			});
		},
		
		triggerSearch: function () {
			var search = this.grid.find('input' + this.nsSel('search-field'));
			
			this._pagingOffset = 0;
			this._searchQuery = search.val();
			
			this.fetchItems(this._currentFolder, this.processItems);
		},
		
		/**
		 * TODO: Fix this so that sorting does toggle between desc and asc
		 *		 when you click on a column on which we were not sorting.
		 */
		sortList: function(colModel, el){
			// reset sort properties in all column headers
			$('span.ui-grid-ico-sort').addClass('ui-state-disabled');
			
			colModel.sortorder = (colModel.sortorder == 'asc') ? 'desc' : 'asc';
			
			$(el)
				.find('span.s-ico').show()
					.find('.ui-icon-' + colModel.sortorder)
						.removeClass('ui-state-disabled');
			
			this.setSortOrder(colModel.name, colModel.sortorder)
				.fetchItems(this._currentFolder, this.processItems);
		},
		
		/**
		 * This function adds new sort fields into the _orderBy array.
		 * If a field already exists, it will be spliced from where it is and
		 * unshifted to the end of the array
		 */
		setSortOrder: function (by, order) {
			var sortItem = {};
			sortItem[by] = order || 'asc';
			
			var orderBy = this._orderBy || [];
			var field;
			var orderItem;
			var found = false;
			
			for (var i = 0, j = orderBy.length; i < j; i++) {
				orderItem = orderBy[i];
				
				for (field in orderItem) {
					if (field === by) {
						orderBy.splice(i, 1);
						orderBy.unshift(sortItem);
						found = true;
						break;
					}
				}
				
				if (found) {
					break;
				}
			}
			
			found || orderBy.unshift(sortItem);
			
			this._orderBy = orderBy;
			
			return this;
		},
		
		getFieldOfHeader: function (th) {
			return th.find('div.ui-jqgrid-sortable').attr('id').replace('jqgh_', '');
		},
		
		doPaging: function (dir) {
			switch (dir) {
			case 'first':
				this._pagingOffset = 0;
				break;
			case 'end':
				this._pagingOffset = this._pagingCount - this.pageSize;
				break;
			case 'next':
				this._pagingOffset += this.pageSize;
				break;
			case 'prev':
				this._pagingOffset -= this.pageSize;
				break;
			}
			
			// TODO: animate
			//var grid = this.grid.find('.ui-jqgrid-bdiv');
			//grid.animate({marginLeft: -grid.width()}, 500);
			
			this.fetchItems(this._currentFolder, this.processItems);
		},
		
		fetchItems: function (folder, callback) {
			this.list.setCaption(
				(typeof this._searchQuery === 'string')
					? 'Searching for "' + this._searchQuery + '" in ' + folder.name
					: 'Browsing: ' + folder.name
			);
			
			this.list.hide();
			this.grid.find('.loading').show();
			
			var that = this;
			
			this.queryRepository(
				{
					queryString		 : this._searchQuery,
					inFolderId		 : folder.id,
					skipCount		 : this._pagingOffset,
					maxItems		 : this.pageSize,
					objectTypeFilter : this.objectTypeFilter,
					renditionFilter	 : this.renditionFilter,
					filter			 : this.filter,
					orderBy			 : this._orderBy
				//	repositoryId	 : obj.repositoryId
				},
				function (data) {
					if (typeof callback === 'function') {
						callback.call(that, data);
					}
				}
			);
		},
		
		fetchSubnodes: function (node, callback) {
			if (node === -1) {
				this.fetchRepoRoot(callback);
			} else {
				var obj = this.getObjectFromCache(node);
				if (typeof obj === 'object') {
					this.fetchChildren(obj, callback);
				}
			}
		},
		
		listItems: function (items) {
			var that = this,
				data = [],			
				list = this.list.clearGridData(),
				obj;
			
			$.each(items, function () {
				obj = this.resource;
				list.addRowData(
					obj.uid,
					$.extend({id: obj.id}, that.renderRowCols(obj))
				);
			});
		},
		
		processItems: function (data) {
			var btns = this._pagingBtns,
				disabled = 'ui-state-disabled';
			
			this.grid.find('.loading').hide();
			this.list.show();
			this.listItems(data);
			
			if (this._pagingOffset <= 0) {
				btns.first.add(btns.prev).addClass(disabled);
			} else {
				btns.first.add(btns.prev).removeClass(disabled);
			}
			
			if (isNaN(this._pagingCount)) {
				btns.end.addClass(disabled);
				if (data.length == 0) {
					btns.next.addClass(disabled);
				} else {
					btns.next.removeClass(disabled);
				}
			} else if (this._pagingOffset + this.pageSize >= this._pagingCount) {
				btns.end.add(btns.next).addClass(disabled);
			} else {
				btns.end.add(btns.next).removeClass(disabled);
			}
			
			this.grid.find('.ui-paging-info').html(
				'Viewing ' +		  (data.length == 0 ? 0 : this._pagingOffset + 1)
						   + ' - '  + (data.length == 0 ? 0 : this._pagingOffset + data.length)
						   + ' of ' + (this._pagingCount || 'numerous')
			);
		},
		
		createOverlay: function () {
			var that = this;
			
			$('body').append(that.renderTemplate(
				'<div class="{modal-overlay}" style="top: -99999px; z-index: 99999;"></div>' +
				'<div class="{modal-window}"  style="top: -99999px; z-index: 99999;"></div>'
			));
			
			$(that.nsSel('modal-overlay')).click(function () {
				that.close();
			});
			
			return $(that.nsSel('modal-window'));
		},
		
		setObjectTypeFilter: function (otf) {
			this.objectTypeFilter = typeof otf === 'string' ? [otf] : otf;
		},
		
		getObjectTypeFilter: function () {
			return this.objectTypeFilter;
		},
		
		show: function () {
			this.fetchRepoRoot();
			
			var el = this.element;
			
			$(this.nsSel('modal-overlay'))
				.css({top: 0, left: 0})
				.add(el).stop().show();
			
			var	w = el.width(),
				h = el.height(),
				win	= $(window);
			
			el.css({
				left : (win.width() - w) / 2,
				top  : (win.height() - h) / 3
			}).draggable({
				handle: el.find(this.nsSel('grab-handle'))
			}).resizable();
			
			// Do wake-up animation
			this.grid.css({
				marginTop: 30,
				opacity: 0
			}).animate({
				marginTop: 0,
				opacity: 1
			}, 1500, 'easeOutExpo');
		},
		
		close: function () {
			var that = this;
			this.element.fadeOut(
				250, function () {
					$(this).css('top', 0).hide();
					$(that.nsSel('modal-overlay')).hide();
				}
			);
		},
		
		log: function (msg) {
			if (this.verbose == true) {
				console.log('%s LOG: ', this.instanceOf, msg);
			}
		},
		
		warn: function (msg, force) {
			if (this.verbose == true || force == true) {
				console.warn('%s WARNING: ', this.instanceOf, msg);
			}
		},
		
		error: function (msg, force) {
			if (this.verbose == true || force == true) {
				console.error('%s ERROR: ', this.instanceOf, msg);
			}
		}
	
	});
});
