$(document).ready(function(){

});
function getUserEditForm(userid) {
	var url = dir + '/admin/user/add';
	if (typeof userid != "undefined") {
		url = dir + '/admin/user/edit/' + userid;
	}
	$.ajax({
		url: url ,
		type: 'GET',
		success: function(data) {
			if (typeof data == "string") {
				data = JSON.parse(data);
			}
			if (typeof data.content != "undefined") {
				var nowDate = new Date();
				nowDate = nowDate.getUTCMinutes() + '' + nowDate.getUTCMilliseconds();
				jPopup(data.content, 'Send notification for all', 'sendNotice' + nowDate, '');
				$(".userEditForm").on("submit", function() {
					if ($(this).hasClass("sent")) {
						return false;
					}
					newAjax.submitForm(".userEditForm", showErrors);
					return false;
				});
			}
		}
	});
}

function showErrors(data, content) {
	if (typeof data.data != "undefined") {
		data = data.data;
	}
	if (typeof data.content != "undefined") {
		data = data.content;
	}
	if (typeof data.errors !== "undefined") {
		$(".userEditForm * ").removeClass("highlited");
		$(".userEditForm div.error").remove();
		$(".userEditForm p.error").hide();
		var redirectUrl;
		var isReload;
		redirectUrl = data.errors.redirectUrl || location.href;
		typeof data.errors.redirectUrl == "undefined" ? isReload = false : isReload = true;
		if (typeof data.errors.updated !== "undefined" || typeof data.errors.notFound !== "undefined") {
			var errorsArr = data.errors.updated || data.errors.notFound;
			if (typeof errorsArr != "string") {
				errorsArr = errorsArr[first(errorsArr)];
			}
			showReloadPopup(redirectUrl, isReload, errorsArr);
		}
		$.each(data.errors, function(key, val) {
			$(".userEditForm .element." + key).addClass("highlited");
			$(".userEditForm .element." + key).append("<div class='error'><ul><li>" + val[first(val)] + "</li></ul></div>");
		});
	}
	else {
		location.reload();
	}
}