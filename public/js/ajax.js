var newAjax = {
	defaultParams: {
		controller: false,
		url:"",
		action: false,
		unimportant: false,
		data: {},
		type: "post",
		success: false
	},
	error: {
		connection: "Connection error. Please try to reload the page.",
		data: "Data error. Please try to reload the page."
	},
	/**
	 * Send ajax request to server and parce standard responces
	 * 
	 * @param {object} params 
	 * @returns boolean
	 */
	request: function(params) {
		var self = this;
		for (var param in this.defaultParams) {
			if (!params[param]) {
				params[param] = this.defaultParams[param];
			}
		}
		if (params && params !== null )
		{  
			 if(params.url ==="" &&  params.controller && params.controller !== null && params.action && params.action !== null)
			 {
				 params.url=(window.location.protocol === 'https:' ? app_https_dir : app_dir) + params.controller + "/" + params.action;
			 }
			return $.ajax({
				url: params.url,
				type: params.type,
				dataType: 'html',
				data: params.data,
				success: function(data) {
					newAjax.parseResponse(data, params.success);
				},
				error: function() {
					if (params['unimportant'] !== true) {
						newAjax.showError(self.error.connection);
					}
				}
			});
		}
	},
	parseResponse: function(data, callback) {
		data = this.parseData(data);
		if (!data || !data['status'] || !data['action']) {
			this.showError(this.error.data);
			return false;
		}
		if (data['status'] === 'success') {
			if (typeof (this.actions[data['action']]) === 'function') {
				this.actions[data['action']](data, callback);
			}
		} else if (data['status'] === 'error') {
			var popupClass = '';
			if (typeof data['data']['message']['popClass'] !== "undefined"){
				popupClass = data['data']['message']['popClass'];
			}
			if (typeof data['data']['message']['title'] !== "undefined") {
				this.showError(data['data']['message']['error'], data['data']['message']['title'], popupClass);
			}
			else {
				this.showError(data['data']['message']);
			}
			$(".loaderWrapp").hide();
		}
	},
	parseData: function(data) {
		var jdata = IsJson(data);
		if (jdata) {
			return JSON.parse(data);
		} else {
			return false;
		}
	},
	showError: function(text, title, popClass) {
		popClass = 'reloadPage ' + popClass;
		if (typeof title !== "undefined") {
			showPopupWarning(text, title, '', popClass); /* TODO: replace to founction of showing error*/
		}
		else {
			showWarning(text);
		}
	},
	submitForm: function(selector, callback, progress) {
		var self = this;
		var selector = selector || false;	
		$(".loaderWrapp").show().children("img").css("top", parseInt($(window).height()) / 2);
		var callback = callback || function(){};
		var progress = progress || function(){};
		if (callback && callback!==null && selector && selector !== null) {
			$(selector).ajaxSubmit({
				success: function(data) {
					$(".loaderWrapp").hide();
					newAjax.parseResponse(data, callback);
				},
				uploadProgress: progress,
				error: function() {
					$(".loaderWrapp").hide();
					newAjax.showError(self.error.connection);
				}
			});
		} else {
				$(".loaderWrapp").hide();
		}
	},
	actions: {
		redirect: function(data, callback) {
/*			var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig; */
			if (typeof (data.data[0]) === 'string') {  /*&& data.match(exp)*/
				window.location = data.data[0];
			}
			else{
				if (callback) {
					callback(data);
				}
			}
		},
		login: function(data, callback) {
			if(callback) {
				callback(data);
			}
			location.href = dir;
		},
		alert: function(data, callback) {
			if (typeof (data.data.content) === 'string') {
				showWarning(data.data.content); /* TODO: replace to founction of showing warning */
			}
			if(callback) {
				callback(data);
			}
			$(".loaderWrapp").hide();
		},
		none: function(data, callback) {
			if(callback) {
				callback(data);
			}
			$(".loaderWrapp").hide();
			return true;
		},
		content: function(data, callback) {
			$(".loaderWrapp").hide();
			if (typeof (callback) === 'function') {
				callback(data.data, data.content);
			}
		},
		replaceContent: function(data, callback) {
			if(data.data.elementId) {
				$('#'+data.data.elementId).html(data.content);
			}
			else if(data.data.elementSelector) {
				$(data.data.elementSelector).html(data.content);
			}
			$(".loaderWrapp").hide();
			if (typeof (callback) === 'function') {
				callback(data.data, data.content);
				
			}
		},
	}
};

function IsJson(str) {
	try {
		JSON.parse(str);
	} catch (e) {
		/*log('Bad JSON: ' + str);*/
		return false;
	}
	return true;
}

function getJson(str, def) {
	var def = def || false;
	try {
		var res = JSON.parse(str);
	} catch (e) {
		log('Bad JSON: ',str,' return Default: ',def);
		var res = def;
	}
	if (res === null) {
		res = def;
	}
	return res;
}

function android_2_check(data, callback) {
	var ua = navigator.userAgent.toLowerCase();
	if (ua.indexOf("android 2") > -1)/*&& ua.indexOf("mobile");}*/
	{
			location.href = app_dir + 'message/index/0';
	}
	else {
		var params = {success: callback};
		newAjax.parseResponse(data, callback);
	}
}