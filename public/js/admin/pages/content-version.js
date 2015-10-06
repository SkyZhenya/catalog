var contentVersion = {
	
	showPopup: function() {
		jAlert('The data was changed, please start edit once again', 'Update the page', 'window', function() {
			window.location.href = window.location.href;
		});
	},
	
	init: function() {
		if ($('.updated.element .error li').length > 0) {
			$('.updated.element .error').hide();
			contentVersion.showPopup();
		}
	}
}