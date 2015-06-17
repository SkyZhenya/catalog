// jQuery Alert Dialogs Plugin
//
// Version 1.0
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 29 December 2008
//
// Visit http://abeautifulsite.net/notebook/87 for more information
//
// Usage:
//		jAlert( message, [title, callback] )
//		jConfirm( message, [title, callback] )
//		jPrompt( message, [value, title, callback] )
//
// History:
//
//		1.00 - Released (29 December 2008)
//
// License:
//
//		This plugin is licensed under the GNU General Public License: http://www.gnu.org/licenses/gpl.html
//
(function($) {

	$.alerts = {

		// These properties can be read/written by accessing $.alerts.propertyName from your scripts at any time

		verticalOffset: -50,                // vertical offset of the dialog from center screen, in pixels
		horizontalOffset: 0,                // horizontal offset of the dialog from center screen, in pixels/
		repositionOnResize: true,           // re-centers the dialog on window resize
		draggable: false,                    // make the dialogs draggable (requires UI Draggables plugin)
		okButton: 'OK',         // text for the OK button
		cancelButton: 'Cancel', // text for the Cancel button
		dialogClass: null,                  // if specified, this class will be applied to all dialogs

		// Public methods

		alert: function(message, title, id, callback) {
			if( title == null ) title = '';
			if( id == null || id == '' ) id = 'alert';
			$.alerts._show(message, title, id, null, 'alert', function(result) {
				if( callback ) callback(result);
			});
		},

		confirm: function(message, title, id, callback) {
			if( title == null ) title = '';
			if( id == null || id == '' ) id = 'confirm';
			$.alerts._show(message, title, id, null, 'confirm', function(result) {
				if( callback ) callback(result);
			});
		},

		prompt: function(message, title, id, value, callback) {
			if( title == null ) title = '';
			if( id == null || id == '' ) id = 'prompt';
			$.alerts._show(message, title, id, value, 'prompt', function(result) {
				if( callback ) callback(result);
			});
		},

		popup: function(message, title, id, callback) {
			if( title == null ) title = '';
			if( id == null || id == '' ) id = 'popup';
			$.alerts._show(message, title, id, null, 'popup', function(result) {
				if( callback ) callback(result);
			});
		},

		jclose: function(id) {
			id = $("#"+id);
			$.alerts._hide(id);
		},


		// Private methods

		_show: function(msg, title, id, value, type, callback) {
			if($('#'+id).length){
				//alert('Popop with same ID already exists!');
			}else{
				$('.popup_container').css('zIndex', 99);

				$("BODY").append(
				  '<div id="'+id+'" class="popup_container '+type+'_popup_type">' +
					'<div class="popup_header_cust"><div class="close"></div>' +
				    ( title ? '<div class="popup_title">' +	title + '</div>' : '') +
				    '</div><div class="popup_content">' +
				      '<div class="popup_message">' +
				      	msg +
				      '</div>' +
						'</div>' +
						
				  '</div>'
				);
				$.alerts._overlay('show');
				var id = $("#"+id);

				if( $.alerts.dialogClass ) id.addClass($.alerts.dialogClass);

				id.css({
					position: 'absolute',
					zIndex: 101,
					padding: 0,
					margin: 0,
					minWidth: 360,
					maxWidth: 1000
				});

				$.alerts._reposition(id);
				$.alerts._maintainPosition(true);

				id.find(".close").click( function() {
					$.alerts._hide(id);
					if( callback ) callback(false);
				});
				$(".popup_overlay").click( function() {
					$.alerts._hide(id);
					if( callback ) callback(false);
				});
				
				switch( type ) {
					case 'alert':
						id.find(".popup_message").after('<div class="popup_panel"><input type="button" class="popup_ok blueButton" value="' + $.alerts.okButton + '" /></div>');
						id.find(".popup_ok").click( function() {
							$.alerts._hide(id);
							if( callback ) callback(true);
						});
						id.find(".popup_ok").focus().keypress( function(e) {
							if( e.keyCode == 13 || e.keyCode == 27 )id.find(".popup_ok").trigger('click');
						});
					break;
					case 'confirm':
						id.find(".popup_message").after('<div class="popup_panel"><input type="button" class="popup_ok blueButton" value="' + $.alerts.okButton + '" /> <input type="button" class="popup_cancel blueButton" value="' + $.alerts.cancelButton + '" /></div>');
						id.find(".popup_ok").click( function() {
							$.alerts._hide(id);
							if( callback ) callback(true);
						});
						id.find(".popup_cancel").click( function() {
							$.alerts._hide(id);
							if( callback ) callback(false);
						});
						id.find(".popup_ok").focus();
						id.find(".popup_ok, popup_cancel").keypress( function(e) {
							if( e.keyCode == 13 ) id.find(".popup_ok").trigger('click');
							if( e.keyCode == 27 ) id.find(".popup_cancel").trigger('click');
						});
					break;
					case 'prompt':
						id.find(".popup_message").append('<br /><input type="text" size="30" class="popup_prompt" />').after('<div class="popup_panel"><input type="button" class="popup_ok blueButton" value="' + $.alerts.okButton + '" /> <input type="button" class="popup_cancel blueButton" value="' + $.alerts.cancelButton + '" /></div>');
						id.find(".popup_prompt").width( id.find(".popup_message").width() );
						id.find(".popup_ok").click( function() {
							var val = id.find(".popup_prompt").val();
							$.alerts._hide(id);
							if( callback ) callback( val );
						});
						id.find(".popup_cancel").click( function() {
							$.alerts._hide(id);
							if( callback ) callback( null );
						});
						id.find(".popup_prompt, .popup_ok, .popup_cancel").keypress( function(e) {
							if( e.keyCode == 13 ) id.find(".popup_ok").trigger('click');
							if( e.keyCode == 27 ) id.find(".popup_cancel").trigger('click');
						});
						if( value ) id.find(".popup_prompt").val(value);
						id.find(".popup_prompt").focus().select();
					break;
					case 'popup':
						id.find(".popup_cancel").click( function() {
							$.alerts._hide(id);
							if( callback ) callback( null );
						});
						id.find(".popup_cancel").keypress( function(e) {
							if( e.keyCode == 27 ) id.find(".popup_cancel").trigger('click');
						});
					break;
				}

				// Make draggable
				if( $.alerts.draggable && id.find(".popup_title").size() > 0) {
					try {
						id.draggable({ handle: id.find(".popup_title") });
						id.find(".popup_title").css({ cursor: 'move' });
					} catch(e) { /* requires jQuery UI draggables */ }
				}
			}
		},

		_hide: function(id) {
			id.remove();
			$('.popup_container:last').css('zIndex', 101);
			$.alerts._overlay();
			//$.alerts._maintainPosition(false);
		},

		_overlay: function(status) {
			if($('.popup_container').length){
				if(!$('.popup_overlay').length){
					$("body").append('<div class="popup_overlay"></div>');
				}
			}else{
				$(".popup_overlay").remove();
			}
		},

		_reposition: function(id) {
			var wsize = $.alerts._windowWorkSize(); // баАаЗаМаЕбб "баАаБаОбаЕаЙ аОаБаЛаАббаИ"
			var testElemHei = id.innerHeight();
			if((testElemHei - $.alerts.verticalOffset) > wsize){
				var top = 10 + (document.body.scrollTop || document.documentElement.scrollTop);
			}else{
				var top = wsize/2 - testElemHei/2 + (document.body.scrollTop || document.documentElement.scrollTop) + $.alerts.verticalOffset;
			}
			//var left = (($(window).width() / 2) - (id.outerWidth() / 2)) + $.alerts.horizontalOffset;
			if( top < 0 ) top = 0;
			//if( left < 0 ) left = 0;

			// IE6 fix
			id.css({
				top: top + 'px',
				marginLeft: '-' + (id.outerWidth() / 2) + 'px',
			});
			$(".popup_overlay").height( $(document).height() );
		},

		_maintainPosition: function(status, id) {
			if( $.alerts.repositionOnResize ) {
				switch(status) {
					case true:
						$(window).bind('resize', function() {
							$.alerts._reposition(id);
						});
					break;
					case false:
						$(window).unbind('resize');
					break;
				}
			}
		},

		_windowWorkSize: function() {
			if( window.innerHeight !== undefined ){
				var wwSize = window.innerHeight	// аДаЛб аОбаНаОаВаНбб аБбаАбаЗаЕбаОаВ
			}else{	// аДаЛб "аОбаОаБаО аОаДаАббаНаНбб" (аа6-8)
				var wwSizeIE = (document.body.clientWidth) ? document.body : document.documentElement;
				var wwSize = [wwSizeIE.clientHeight];
			};
			return wwSize;
		}
	}

	// Shortuct functions
	jAlert = function(message, title, id, callback) {
		$.alerts.alert(message, title, id, callback);
	}

	jConfirm = function(message, title, id, callback) {
		$.alerts.confirm(message, title, id, callback);
	};

	jPrompt = function(message, title, id, callback, value) {
		$.alerts.prompt(message, title, id, value, callback);
	};

	jPopup = function(message, title, id, callback) {
		$.alerts.popup(message, title, id, callback);
	}

	jClose = function(id) {
		$.alerts.jclose(id);
	}

})(jQuery);