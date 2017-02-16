var photoEditor = {
	selectedFile: '',

	showFile: function(input) {
		if (input.files && input.files[0]) {
			if (input.files[0].type == 'image/jpeg' || input.files[0].type == 'image/png') {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#photoImage').css({
						"background-image" : "url("+ e.target.result +")",
						"background-color": '#000000',
					});
				}
				reader.readAsDataURL(input.files[0]);
				$('[name="removePhoto"]').val(0);
			}
			else {
				parent.jAlert('You can upload jpg, jpeg, png files only', 'Error');
				$('#photoImage').css({
					"background-image" : photoEditor.selectedFile
				});
				$(input).val(null)
			}
		}
	},

	showF: function(input, i) {
		if (input.files && input.files[0]) {
			if (input.files[0].type == 'image/jpeg' || input.files[0].type == 'image/png') {
				var reader = new FileReader();

				reader.onload = function (e) {
					$('#photoImage'+i).css({
						"background-image" : "url("+ e.target.result +")",
						"background-color": '#000000',
					});
				}
				reader.readAsDataURL(input.files[0]);
				$('[name="removePhoto"]').val(0);
			}
			else {
				parent.jAlert('You can upload jpg, jpeg, png files only', 'Error');
				$('#photoImage'+i).css({
					"background-image" : photoEditor.selectedFile
				});
				$(input).val(null)
			}
		}
	},

	removePhoto: function(url, nameId, photo, id) {
		
		$.ajax({
			url: url,
			type: 'POST',
			data: ({urlPhoto: photo, id: id}),
			success: function(result) {
				$('.photo_container[name="photo['+ nameId +']"]').remove();
			}
		});
		
	},

	removeFile: function(i) {
		$('.photo_container[name="photoContainer['+ i +']"').remove();
		$('[name="removePhoto'+ i +'"]').val(1);
		return false;
	},


}