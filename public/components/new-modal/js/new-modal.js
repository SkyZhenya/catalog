var newModal = {
	options : {
		showHeader : false,
		headerContent : "Header",
		showFooter : false,
		footerContent: "Footer",
		addClass: false,
		modalId : "newModal",
		contentSourceType: "ajax" /* ajax(link to source) or html(DOM selector) */,
		contentSourse: "",
		afterUploadCallback: function(){
		},
		afterCloseCallback: function(){
		},

		/* for ajax  */
		requestType: "GET",
	},

	params : {},

	modalWrap : {},

	closeModal : function (){
		var main = this;
		var container = $("[data-newmodal='container']");
		container.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function(){
			container.hide();
			container.remove();
			main.params = main.options;
			main.params.afterCloseCallback();
		});

		container.removeClass("in");
	},

	showSpinner: function(){
		var main = this;
		var spinner = $("<div></div>").addClass("newModalSpiner");

		$("<span></span>").addClass("spinnerIcon").appendTo(spinner);

		spinner.appendTo(main.modalWrap.find(".newModalBody"));
	},

	hideSpinner: function() {
		var main = this;
		main.modalWrap.find(".newModalSpiner").remove();
	},

	createModal : function() {
		var main = this;
		var modalWrap = $("<div></div>").addClass("newModal fade")
			.attr("data-newmodal", 'container');

		main.modalWrap = modalWrap;

		if (main.params.modalId) { main.modalWrap.attr("id", main.params.modalId)};
		if (main.params.addClass) { main.modalWrap.addClass(main.params.addClass)};

		var modalContent = $("<div></div>").addClass("newModalContent");
		var modalDialod = $("<div></div>").addClass("newModalDialog");

		var topBtnClose = $("<button></button>").attr("data-newmodal","hide")
			.addClass("newModalBtnClose")
			.html("<span>×</span>");

		topBtnClose.appendTo(modalContent);


		if (main.params.showHeader) {
			var headerContent = $("<span></span>").addClass("newModalHeaderContent").html(main.params.headerContent);
			var modalHeader = $("<div></div>").addClass("newModalHeader")
				.append(headerContent)
				.appendTo(modalContent);
		}

		var modalBody = $("<div></div>").addClass("newModalBody")
			.appendTo(modalContent);

		if (main.params.showFooter) {
			var footerContent = $("<span></span>").addClass("newModalFooterContent").html(main.params.footerContent);
			var modalHeader = $("<div></div>").addClass("newModalFooter")
				.append(footerContent)
				.appendTo(modalContent);
		}

		modalDialod.html(modalContent);

		modalDialod.appendTo(main.modalWrap);

		main.modalWrap.appendTo("body");
		main.modalWrap.on("click", function(event){
			if (!$(event.target).closest(".newModalContent").length) {
				main.closeModal(modalWrap);
			}
		});

		main.uploadContent(modalBody);

		return main.modalWrap;
	},

	uploadContent: function (modalBody) {
		var main = this;
		main.showSpinner();

		if (main.params.contentSourceType == "ajax") {
			$.ajax({
				type: main.params.requestType,
				url: main.params.contentSourse,
				success: function(data) {
					main.hideSpinner();
					$.when(modalBody.html(data)).then(function(){
						main.params.afterUploadCallback();
					});
				},
				error: function(){
					console.log(data);
				}
			})
		} else if (main.params.contentSourceType == "html") {
			main.hideSpinner();


			$.when($(newModal.params.contentSourse).clone().appendTo(modalBody)).then(function(){
				main.params.afterUploadCallback();
			});
		}

	},

	show : function(options){
		var main = this;

		$(".newModal").hide();
		main.params = $.extend({}, main.options, options);


		if (!$(main.params.modalId).length && !$(main.params.modalId).hasClass("newModal")) {
			var modalElem = main.createModal(main.params);
		} else {
			modalElem = $(main.params.modalId);
			console.log(123);
		}

		$.when(modalElem.show())
			.then(function(){
			modalElem.addClass("in");
		});
	},
};

$(function(){
	$(document).on("click", "[data-newmodal='hide']", function(event){
		event.preventDefault();
		newModal.closeModal()
	});

	$(document).on("click", "[data-newmodal='show']", function(event){
		event.preventDefault();
		var btn = $(this);
		var contentSource;
		var options = {};
		if ( this.href != undefined) {
			contentSource = this.href
		}
		if (btn.attr("data-href") != undefined) {
			contentSource = btn.attr("data-href")
		}

		var contentSourseObj = {
			contentSourse: contentSource
		};

		if (btn.attr("data-newmodal-options") != undefined) {
			options = btn.attr("data-newmodal-options");
			options = $.parseJSON(options);
		}

		options = $.extend(options, contentSourseObj);

		newModal.show(options);
	});
});