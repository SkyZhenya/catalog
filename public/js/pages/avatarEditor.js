var avatarEditor = {
	selectedFile: '',

	showFile: function(input) {
		if (input.files && input.files[0]) {
			if (input.files[0].type == 'image/jpeg' || input.files[0].type == 'image/png') {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#avatarImage').css({
						"background-image" : "url("+ e.target.result +")",
						"background-color": '#000000',
					});
				}
				reader.readAsDataURL(input.files[0]);
				$('[name="removeAvatar"]').val(0);
			}
			else {
				parent.jAlert('You can upload jpg, jpeg, png files only', 'Error');
				$('#avatarImage').css({
					"background-image" : avatarEditor.selectedFile
				});
				$(input).val(null)
			}
		}

	},

	removeFile: function(defaultImageUrl) {
		$('#avatarImage').css({
			"background-image" : "url("+ defaultImageUrl +")"
		});
		$('[name="removeAvatar"]').val(1);
		return false;
	},

	beforeSubmitAvatar: function() {
		var input = document.getElementsByName("avatar")[0];
		if (input.files && input.files[0]) {
			if ( window.webkitURL ) {
				var fileUrl = window.webkitURL.createObjectURL( input.files[0] );
			} else if ( window.URL && window.URL.createObjectURL ) {
				var fileUrl = window.URL.createObjectURL( input.files[0] );
			} else {
				return true;
			}
			var isValidForm = true;
			$.ajax({
				url: fileUrl,
				async: false,
				error: function(data){
					parent.jAlert('Please select correct file', 'Error');
					isValidForm = false;
				}
			});
			return isValidForm;
		}
		else return true;
	},
}

$(document).ready(function() {
	avatarEditor.selectedFile = $('#avatarImage').css('background-image');
});