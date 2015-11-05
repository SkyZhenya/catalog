var adminGrid = {
	grid: null,
	sortOrder : 'des',
	sortOrderBy : 1,
	
	enableSmartRendering: true,
	enablePreRendering: true,
	awaitedRowHeight: 33,
	
	onpage: 50,
	listUrl: '',
	
	defaults: {
		'colAlign': 'center',
		'colType' : 'ro',
		'colSorting' : 'na',
		'resizing' : 'false',
		'filter' : '#rspan'
	},
	
	filters: [],
	filterBoxes : [],
	
	init: function(options) {
		dhtmlx.CustomScroll.init();
		common.setGridHeight();  
		this.grid = new dhtmlXGridObject('gridbox');
		this.setOptions(options);
		this.setColumns(options.columns);
		
		if (this.enableSmartRendering) {
			this.grid.enableSmartRendering(true,this.onpage);
		}
		if (this.enablePreRendering) {
			this.grid.enablePreRendering(this.onpage);
		}
		this.grid.setAwaitedRowHeight(this.awatedRowHeight);
		
		this.grid.init();
				
		this.grid.attachEvent("onBeforeSorting",function(ind,type,dir){
			adminGrid.sortOrderBy=ind;
			adminGrid.sortOrder=dir;
			adminGrid.filterBy();
		    return false;
		});
		
		if (typeof options.events !== 'undefined') {
			for (var j in options.events) {
				this.grid.attachEvent(options.events[j].name,options.events[j].callback);
			}
		}
		this.grid.setSizes();
		this.attachFilters(this.filtersBoxes);
		
		this.filterBy();
	},
	
	setColumns: function(columns) {
		var headerLine = [];
		var widthsLine = [];
		var colAlignLine = [];
		var colTypeLine = [];
		var colSortingLine = [];
		var resizingLine = [];
		
		var filtersLine = [];
		var filtersBoxes = [];
		
		for (var i in columns) {
			headerLine[i] = columns[i].title;
			widthsLine[i] = columns[i].width;
			
			if (typeof  columns[i].colAlign == 'undefined') {
				columns[i].colAlign = this.defaults.colAlign;
			}
			colAlignLine[i] = columns[i].colAlign;
			
			if (typeof  columns[i].colType == 'undefined') {
				columns[i].colType = this.defaults.colType;
			}
			colTypeLine[i] = columns[i].colType;
			
			if (typeof  columns[i].colSorting == 'undefined') {
				columns[i].colSorting = this.defaults.colSorting;
			}
			colSortingLine[i] = columns[i].colSorting;
			
			if (typeof  columns[i].resizing == 'undefined') {
				columns[i].resizing = this.defaults.resizing;
			}
			resizingLine[i] = columns[i].resizing;
			
			if (typeof columns[i].filter !== 'undefined') {
				var gridFilterBoxId = "column_filter_"+i;
				filtersLine[i] = "<div id='"+gridFilterBoxId+"'><\/div>";
				filtersBoxes[i] = {'id': gridFilterBoxId, 'selector': columns[i].filter.sourceContainerSelector}
				this.addFilter(columns[i].filter);
			}
			else {
				if (columns[i].title == '#cspan') {
					filtersLine[i-1] = "";
					filtersLine[i] = "";
				}
				else {
					filtersLine[i] = "#rspan";
				}
			}
		}
		
		this.grid.setHeader(headerLine.join());
		this.grid.setInitWidths(widthsLine.join());
		this.grid.setColAlign(colAlignLine.join());
		this.grid.setColTypes(colTypeLine.join());
		this.grid.setColSorting(colSortingLine.join());
		this.grid.enableResizing(resizingLine.join());
		
		this.grid.attachHeader(filtersLine.join());
		this.filtersBoxes = filtersBoxes;
	},
	
	setOptions: function(options) {
		if (typeof options.sortOrder !== 'undefined') {
			this.sortOrder = options.sortOrder;
		}
		
		if (typeof options.sortOrderBy !== 'undefined') {
			this.sortOrderBy = options.sortOrderBy;
		}
		
		if (typeof options.onpage !== 'undefined') {
			this.onpage = options.onpage;
		}
		
		if (typeof options.listUrl !== 'undefined') {
			this.listUrl = options.listUrl;
		}
		
		if (typeof options.enableSmartRendering !== 'undefined') {
			this.enableSmartRendering = options.enableSmartRendering;
		}
		
		if (typeof options.enablePreRendering !== 'undefined') {
			this.enablePreRendering = options.enablePreRendering;
		}
		
		if (typeof options.awaitedRowHeight !== 'undefined') {
			this.awaitedRowHeight = options.awaitedRowHeight;
		}
	},
	
	addFilter: function(filter) {
		 for (var i in filter.values) {
			 if (typeof (filter.values[i].fieldSelector) == 'undefined') {
				 filter.values[i].field = $(filter.sourceContainerSelector).find('.js-filter-value');
			 }
			 else {
				filter.values[i].field = $(filter.values[i].fieldSelector); 
			 }
			 
			 this.filters.push({
				 name: filter.values[i].name,
				 field: filter.values[i].field
			 });
			 filter.values[i].field.attr('onclick', '(arguments[0]||window.event).cancelBubble=true;');
		 }
	},
	
	attachFilters: function(filtersBoxes) {
		for (var i in filtersBoxes) {
			$('#'+filtersBoxes[i].id).append($(filtersBoxes[i].selector));
		}
	},
	
	getFilterQuery: function() {
		var query = '';
		for (var i in this.filters) {
			query += "&"+this.filters[i].name+"="+encodeURIComponent(this.filters[i].field.val());
		}
		
		return query;
	},
	
	filterBy: function() {
		this.grid.clearAll();
		var getListLinkR = '';
		getListLinkR = this.listUrl+"&order="+this.sortOrder+"&orderby="+this.sortOrderBy+this.getFilterQuery();
		this.grid.load(getListLinkR, function() {
			adminGrid.grid.setSortImgState(true,adminGrid.sortOrderBy,adminGrid.sortOrder);    //set a correct sorting image
			$('.objbox').each(function () { dhtmlx.CustomScroll._mouse_out_timed.call(this); });	
			common.setGridHeight();
		});
		
	}
}