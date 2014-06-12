$(document).ready(function(){
	var change_label = function (el){
			var active = $(el).is(':checked');
			var label= $(el).parent().find('label');
			if (active){
				label.html($(el).data('active'));
			} else {
				label.html($(el).data('inactive'));
			}
		}
		$.each($('.changeabledata_check'), function(){
			change_label(this);
		});
		$('.changeabledata_check').change(function(){
			change_label(this);
		});
})