function openDialog(content, title, additional, dontCloseDialogWindows, widthDialog, additionClass) {
	(typeof (additional['fixed']) === "undefined") ? additional['fixed'] = true : '';
	var dontCloseDialogWindows = dontCloseDialogWindows || false;
	var widthDialog = widthDialog || 300;
	if (dontCloseDialogWindows !== true) {
		$('.ui-dialog').remove();
		$('.dialogContainerBox').remove();
	}
	var title = title || "";
	var additional = additional || {};
	var openedtime = (new Date).getTime();
	var dialogID = 'dialog' + openedtime;
	$('html').append('<div id="' + dialogID + '" openedtime="' + openedtime + '" class="dialogContainerBox " title="' + title + '">\n\
<div class="dialogContent">' + content + '</div>\n\
</div>');
	var dialogParams = {
		modal: true,
		dialogClass: additionClass,
		resizable: false,
		width: widthDialog,
		draggable: false,
		position: {my: 'center', at: 'center', of: 'body'},
		open: function() {
			if (typeof (additional['onopen']) === 'function') {
				setTimeout(additional['onopen'], 300);
			}
			if ((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
				$(".ui-widget-overlay").css({"zIndex": "10000", "position": "absolute"})
				$(".ui-dialog").css({"zIndex": "20000", "position": "absolute"})
			}
			$("#" + dialogID).find("input[type='submit']").focus();
		},
	};
	if (additional['fixed'] === true) {
		additional['create'] = function(event, ui) {
			if ($(event.target).parent().height() < $(window).height()) {
				$(event.target).parent().css('position', 'fixed');
			}
			else {
				//TODO scrolltop
			}
		};
		additional['resizeStop'] = function(event, ui) {
			var position = [(Math.floor(ui.position.left) - $(window).scrollLeft()),
				(Math.floor(ui.position.top) - $(window).scrollTop())];
			$(event.target).parent().css('position', 'fixed');
			$(dlg).dialog('option', 'position', position);
		};
		delete additional['fixed'];
	}
	for (var key in additional) {
		dialogParams[key] = additional[key];
	}
	$('#' + dialogID).dialog(dialogParams);
	return false;
}

function showWarning(text, okClickFunction) {
	openDialog(text, null, {
		dialogClass: 'noticeDialog btnsAlCenter',
		buttons: [
			{
				text: "Ok",
				"class": 'greenblue button',
				click: function() {
					$(this).dialog("close");
					if (okClickFunction && typeof (okClickFunction) == 'function') {
						okClickFunction();
					}
				}
			},
		]
	});
	return false;
}
function showPopupWarning(text, title, okClickFunction, classAd) {

	openDialog(text, title, {
		dialogClass: 'noticeDialog ' + classAd,
		width: 450,
		modal: true,
		buttons: [
			{
				text: "Ok",
				"class": 'greenblue button',
				click: function() {
					$(this).dialog("close");
					if (okClickFunction && typeof (okClickFunction) == 'function') {
						okClickFunction();
					}
				}
			},
		]
	});
	return false;
}
function showConfirmDelete(text, title, okClickFunction, classAd, isYesNo) {
	var btnsOk, btnsCancel;
	if (typeof isYesNo !== "undefined" && isYesNo == true) {
		btnsOk = "Yes";
		btnsCancel = "No";
	}
	else {
		btnsOk = "Ok";
		btnsCancel = "Cancel";
	}
	openDialog(text, title, {
		dialogClass: 'noticeDialog ' + classAd,
		width: 450,
		modal: true,
		buttons: [
			{
				text: btnsOk,
				"class": 'greenblue button',
				click: function() {
					$(this).dialog("close");
					if (okClickFunction && typeof (okClickFunction) == 'function') {
						okClickFunction();
					}
				}
			},
			{
				text: btnsCancel,
				"class": 'lightorange closeBtn button',
				click: function() {
					$(this).dialog("close");
				}
			}
		]
	});
	if ($(window).height() - $(".noticeDialog." + classAd).height() - parseInt($(".noticeDialog." + classAd).css("margin-top")) < 0) {
		$(".noticeDialog." + classAd).css("margin-top", '15px');
	}
	var maxH = $(window).height() - $(".noticeDialog." + classAd + " .ui-dialog-titlebar").outerHeight() - $(".noticeDialog." + classAd + " .ui-dialog-buttonpane").outerHeight() - 40;
	$(".noticeDialog." + classAd + " .ui-dialog-content").css("maxHeight", maxH);
	return false;
}

function forgotPass() {
	newAjax.request({
		controller: 'user',
		action: 'forgotpassword',
		data: {},
		success: openForgotPassPopup,
	});
}

function openForgotPassPopup(data, content) {
	if (typeof (content) != 'undefined') {
		openDialog(content, data['title'], {
		}, {}, 580);
	}
	else {
		showWarning(data.data.content);
	}
}
function close_dialog() {
	$(".ui-dialog").hide();
	$(".ui-widget-overlay").remove();
}

function back(url) {
	if (typeof (url) == 'undefined')
		url = dir;
	location.href = url;
}

function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
					results = regex.exec(location.search);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function showReloadPopup(redirectUrl, isReload, text) {
	var popClass = '';
	var popId = '';
	text = text || 'This test has been changed.';
	if (isReload !== false) {
		popClass = 'redirectPage';
		popId = 'windowRedirect';
		$("body").append("<span class='needRedirect' data-url='" + redirectUrl + "' style='display:none;'></span>");
	}
	else {
		popClass = 'reloadPage';
		popId = 'windowReload';
		$("body").append("<span class='needReload' style='display:none;'></span>");
	}
	if ($(".withSidebar").length) {
		$(".loaderWrapp").hide();
		showPopupWarning(text, 'Refresh page', function() {
			window.location.href = redirectUrl;
		}, popClass);
	}
	else {
		jAlert(text, 'Refresh page', popId, function(data) {
			window.location.href = redirectUrl;
		});
	}
}
