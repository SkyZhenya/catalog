var common = {	

		needClose: 0,	
		refreshGrid: 0,
		selectedRow: {
			id: 0,
			num:0,
		},
		changedForm: false,
				
		changeFormAction: function(form){
			var el = $(form);
			var action = el.attr('action');
			action+='?lang='+this.currentLang+'&menuItem='+this.currentMenuItem;
			el.attr('action', action);
		},
		
		removeItem: function(id){
			mygrid.deleteRow(id);
		},
		
		
		confdel: function(url, id, callback){
			jConfirm(deleteQuestion, 'Delete', 'window', function(data){
		    if(data){
					$.ajax({
						url:	url,
						type:	'POST',
						data:	({id : id}),
						success: function(result){
								callback(id);
 						},
 						error: function(jqXHR, textStatus) {
  						alert( "Error: " + jqXHR.responseText );
						}		
					});
				}
			});
		},
		
		reloadPage: function(){
			window.location.reload();
		},
		
		
		setGridHeight: function(){
			var windowHeight = $(window).height();
			var headerHeight = $('.header').height();
			var footerHeight = $('.footer').height();
			var blockBtnsHeight = $('.block-button-no-fix').outerHeight() || 0;
			var height = $('.xhdr').height();
			var midleHeight = windowHeight -(headerHeight)-footerHeight-5 +parseInt($('.middle').css("padding-top")) ;
			var midleHeight2 = windowHeight -(headerHeight)-footerHeight-5  ;
			$('.middle').css('height', midleHeight);
			$('#gridbox').css('height', midleHeight2);
			$('.objbox').css({'maxHeight': midleHeight2-blockBtnsHeight-height,"height":"auto"});	
	},
	
	backToList: function(id){
		window.location.assign(common.listUrl);
	},
	
	initTinyMCE: function(custom_settings){
		var default_settings = {
		    mode : "specific_textareas",
		    editor_selector : "mceEditor",

		    theme : "advanced",
		    theme_advanced_resizing_min_height : 75,
		    plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,openmanager",

		    // Theme options
		    theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,",
		    theme_advanced_buttons2 : "justifyright,justifyfull,|,formatselect,fontselect",
		    theme_advanced_buttons3 : "fontsizeselect",
		    theme_advanced_buttons4 : "forecolor,backcolor",
		    theme_advanced_buttons5 : "cut,copy,paste,pastetext,pasteword,",
		    theme_advanced_buttons6 : "search,replace,code",
		    theme_advanced_buttons7 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,",
		    theme_advanced_toolbar_location : "top",
		    theme_advanced_toolbar_align : "left",
		    theme_advanced_statusbar_location : "bottom",
		    theme_advanced_resizing : true,
		    theme_advanced_source_editor_width : 300,
		    theme_advanced_source_editor_height : 250,


		    // Skin options
		    skin : "o2k7",
		    skin_variant : "silver",
		    setup : function(ed) {
		        ed.onKeyUp.add(function(ed, l) {
          		if (typeof(common.changeMCEValue) != 'undefined')
		             common.changeMCEValue(ed);
		        });
		        ed.onChange.add(function(ed, l) {
                parent.common.changedForm = true;  
          	});
			 },
			 entity_encoding : "raw",
			 force_br_newlines : true,
       force_p_newlines : false,
       forced_root_block : ''

		};
		var settings = $.extend({}, default_settings, custom_settings); 
		tinyMCE.init(settings);
	},
	
	readonlyForm: function(){
		var form = $('form');
		form.find('input[type="text"]').attr('readonly', 'readonly');
		form.find('select,input[type="checkbox"],input[type="file"],input[type="radio"],input[type="password"]').attr('disabled', 'disabled');
		form.find('input[type="submit"]').parent().remove();
	},
	
	changeTab: function(tab, tabClass){
		$('.tab-item-link').removeClass('active');
		$('.'+tabClass+tab).addClass('active');
		$('.'+tabClass).hide();
		$('#'+tabClass+tab).show();
		var width = $('#'+tabClass+tab).width()+30;
		parent.$('.fancybox-inner').width(width);
		parent.$('.fancybox-wrap').width(width);
		return false;
	},
	
	changeLang: function(tab, tabClass){
		$('.tab-item-link').removeClass('active');
		$('.'+tabClass+tab).addClass('active');
		$('.locfields').parent().parent().hide();
		$('.locfields'+tab).parent().parent().show();
		$('.locfields'+tab).parent().find('.mceLayout').css('width', $('.locfields'+tab).parent().width());
		return false;
	},
	
	beforeCloseFancybox: function(){
		if ((typeof(parent.mygrid) !== 'undefined') && (typeof(parent.common.refreshDataLink)!=='undefined') && (!parent.common.refreshGrid)){
			filterBy();
		}
		if ((typeof(parent.mygrid) !== 'undefined') && (parent.common.refreshGrid)){
			parent.filterBy();
			parent.common.refreshGrid = 0;
		}
		parent.common.needClose = 0;
	},
	
	prepareWindowToClose: function() {
		parent.common.changedForm = false;
	},
	
	selectItemInList: function(contentId, rowNum, urlToContentEdit) {
		common.selectedRow.id = contentId;
	  common.selectedRow.num = rowNum;
	  var link = urlToContentEdit + contentId;
		initFancybox(link);
		return false;
	}
}

function initFancybox(href){
 $.fancybox({
    openEffect : 'none',
    closeEffect : 'none',
    prevEffect : 'none',
    nextEffect : 'none',
    type: 'iframe',
    href: href,
    scrolling : 'auto',
	  width   : 540,
	  fitToView   : false,
	  autoSize    : true,
    arrows : false,
    helpers : {
      media : {},
      buttons : {}
    },
    beforeClose: function(){
    	if (parent.common.changedForm) {
				jConfirm(closeQuestion, 'Close', 'window', function(data){
					if (data) {
						common.prepareWindowToClose();
						parent.$.fancybox.close();
					}
				});
				return false;
    	}
    	else {
				common.beforeCloseFancybox();
    	}
    },
    beforeShow: function() {
			common.prepareWindowToClose();
    }
 });
}

function close_fancybox(){
	common.prepareWindowToClose();
	parent.$.fancybox.close();
}

$(document).ready(function(){	

	
	if (parent.common.needClose>1){
		common.prepareWindowToClose();
			
		parent.$.fancybox.close();
	}
	$('#iframe').find('form').find('input,select,textarea').change(function(){
		parent.common.changedForm = true;
	});

});


function toggleCust(item) {
	$(item).parent().parent().find(".singleDP").toggle();
	$(item).parent().parent().find(".rangeDP").toggle();
	$(item).hasClass("showDatepickersLeft") == true ? $(item).removeClass("showDatepickersLeft").addClass("showDatepickersRight") : $(item).removeClass("showDatepickersRight").addClass("showDatepickersLeft");
}
function clearDate(item) {
	$(item).parent().find(".hasDatepicker").val('');
	if ($(item).parents(".startTest").length) {
		$("#end").datepicker("option", "minDate", null);
		$("#end_from").datepicker("option", "minDate", null);
		$("#end_to").datepicker("option", "minDate", null);
	}
	filterBy();
}
