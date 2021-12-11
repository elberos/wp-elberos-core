"use strict";

/*!
 *  Elberos Forms
 *
 *  (c) Copyright 2020 Elberos Team <support@elberos.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Functions
 */
function getCookie(name)
{
	var matches = document.cookie.match
	(
		new RegExp("(?:^|; )" +
		name.replace(/([\.$?*|{}\(\)\[\]\\/\+^])/g, "\$1") + "=([^;]*)"	)
	);
	return matches ? decodeURIComponent(matches[1]) : null;
}
function setCookie(name, value, options = {})
{
  options = { path: '/', ...options };
  if (options.expires instanceof Date) options.expires = options.expires.toUTCString();
  let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
  for (let optionKey in options) {
    updatedCookie += "; " + optionKey;
    let optionValue = options[optionKey];
    if (optionValue !== true) {
      updatedCookie += "=" + optionValue;
    }
  }
  document.cookie = updatedCookie;
}
function addGet(s, key, value)
{
	key = encodeURI(key); value = (value != undefined) ? encodeURI(value) : value;
	var arr = s.split("?");
	var s0 = arr[0] || "";
	var s1 = arr[1] || "";
	var arr2 = s1.split('&');
	var i = arr2.length - 1;
	while (i >= 0)
	{
		var x = arr2[i].split('=');
		if (x.length == 2 && x[0] == key)
		{
			if ((value || false) == false)
			{
				arr2[i] = "";
			}
			else
			{
				x[1] = value;
				arr2[i] = x.join('=');
			}
			break;
		}
		i--;
	}
	if (i < 0 && (value || false) != false) { arr2.push( [key, value].join('=') ); }
	arr2 = arr2.filter(function (s){return s!="";});
	var s2 = arr2.join('&');
	if (s2 == "") return s0;
	return s0 + "?" + s2;
}
function formatMoney(num)
{
	var r = "";
	var num = num.toString();
	var p = num.indexOf(".");
	if (p != -1)
	{
		r = num.substring(p);
		r = r.substring(1, 3);
		if (r.length == 0) r = r + "0";
		if (r.length == 1) r = r + "0";
		num = num.substring(0, p);
	}
	else
	{
		r = "00";
	}
	num = num.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1 ');
	if (r != "") num = num + "." + r;
	return num;
}
if (typeof isset == "undefined")
	window["isset"] = function (val)
	{
		return (val != null) && ((typeof val) != "undefined");
	};
	
if (typeof htmlEscape == "undefined")
	window["htmlEscape"] = function htmlEscape(s)
	{
		return (new String(s))
			.replaceAll("&", "&amp;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&apos;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
		;
	};


/**
 * Send ajax
 */
function elberos_post_send(url, send_data, callback)
{
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	var processData = true;
	if (send_data instanceof FormData)
	{
		contentType = false;
		processData = false;
	}
	
	$.ajax({
		url: url,
		data: send_data,
		dataType: 'json',
		method: 'post',
		
		cache: false,
		contentType: contentType,
		processData: processData,
		xhrFields: { withCredentials: true },
		
		success: (function(callback){
			return function(data, textStatus, jqXHR)
			{
				if (data == null)
				{
					if (callback) callback({
						code: -100,
						message: "Result is null",
					});
				}
				else
				{
					if (callback) callback(data);
				}
			}
		})(callback),
		
		error: (function(callback){
			return function(data, textStatus, jqXHR){
				
				var json = data.responseJSON;
				if (json == null)
				{
					json =
					{
						"code": -data.status,
						"message": data.status + " (" + data.statusText + ")",
					};
				}
				
				if (callback) callback({
					code: -100,
					message: json.message || "System error",
					error_code: json.code || -100,
				});
				
			}
		})(callback),
	});
}



/**
 * Send api request
 */
function elberos_api_send(namespace, route, send_data, callback)
{
	var url = "/api/" + namespace + "/" + route + "/?_=" + Date.now();
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	var processData = true;
	if (send_data instanceof FormData)
	{
		contentType = false;
		processData = false;
	}
	
	if ((typeof site_locale_prefix) != "undefined")
	{
		url = site_locale_prefix + url;
	}
	
	$.ajax({
		url: url,
		data: send_data,
		dataType: 'json',
		method: 'post',
		
		cache: false,
		contentType: contentType,
		processData: processData,
		xhrFields: { withCredentials: true },
		
		success: (function(callback){
			return function(data, textStatus, jqXHR)
			{
				if (data == null)
				{
					if (callback) callback({
						code: -100,
						message: "Result is null",
					});
				}
				else
				{
					if (callback) callback(data);
				}
			}
		})(callback),
		
		error: (function(callback){
			return function(data, textStatus, jqXHR){
				
				var json = data.responseJSON;
				if (json == null)
				{
					json =
					{
						"code": -data.status,
						"message": data.status + " (" + data.statusText + ")",
					};
				}
				
				if (callback) callback({
					code: -100,
					message: json.message || "System error",
					error_code: json.code || -100,
				});
				
			}
		})(callback),
	});
}



/**
 * Send data
 */
function elberos_send_data ( namespace, route, send_data, callback )
{
	if (send_data['data'] == undefined) send_data['data'] = {};
	if (send_data['utm'] == undefined) send_data['utm'] = {};
	
	/* Google client id */
	var gclid = null;
	try{
		gclid = ga.getAll()[0].get('clientId');
	}
	catch(ex){
	}
	if (gclid)
	{
		send_data['utm']['gclid'] = gclid;
	}
	
	/* Yandex client id */
	if (typeof yaClientID != "undefined" && yaClientID != null)
	{
		send_data['utm']['yclid'] = yaClientID;
	}
	
	/* Send api */
	elberos_api_send
	(
		namespace,
		route,
		send_data,
		callback
	);
}



/**
 * Get data
 */
function ElberosFormGetData ( $form )
{
	/* Get data */
	var data = {};
	var arr = $form.find('.web_form__value, .web_form_value');
	for (var i=0; i<arr.length; i++)
	{
		var $item = $(arr[i]);
		var name = $item.attr('data-name');
		var item = $item.get(0);
		var name = $item.attr('data-name');
		var type = $item.attr('type');
		if (type=='radio')
		{
			if (item.checked)
			{
				data[name] = $item.val();
			}
		}
		else
		{
			data[name] = ElberosFormGetFieldValue(arr[i]);
		}
	}
	return data;
}


/**
 * Get forms data
 */
function ElberosFormGetFieldValue (elem)
{
	var $elem = $(elem);
	var type = $elem.attr('type');
	var tag = $elem.prop("tagName").toLowerCase();
	
	if (typeof elem.controller != "undefined" && elem.controller != null)
	{
		return elem.controller.getData();
	}
	else if ($elem.hasClass('ckeditor_type'))
	{
		var instance = CKEDITOR.instances[elem.id];
		if (typeof instance != 'undefined'){
			var value = instance.getData();
			return value;
		}
	}
	else if ($elem.hasClass('multiselect'))
	{
		var arr = $elem.select2('data');
		var res = [];
		for (var i in arr){
			res.push(arr[i].id);
		}
		return res;
	}
	else if (tag == 'input' && type == 'checkbox')
	{
		if ($elem.prop("checked"))
			return 1;
		return 0;
	}
	else if (tag == 'input' && type == 'radio')
	{
		return null;
	}
	
	else if (tag == 'input' && type == 'file')
	{
		var multiple = $elem.attr('multiple');
		if (typeof multiple != "undefined" && multiple !== false){
			return $elem.get(0).files;
		}
		return $elem.get(0).files[0];
	}
	
	return $elem.val();
}


/**
 * Set response
 */
function ElberosFormSetWaitMessage ( $form )
{
	$form.find('.web_form_result').html('Ожидайте идёт отправка запроса');
	$form.find('.web_form__result').html('Ожидайте идёт отправка запроса');
}



/**
 * Set response
 */
function ElberosFormSetResponse ( $form, res, settings )
{
	ElberosFormClearResult($form);
	if (res.code == 1)
	{
		$form.find('.web_form__result').addClass('web_form__result--success');
		$form.find('.web_form_result').addClass('web_form__result--success');
		if (settings == undefined || settings.success_message == undefined)
		{
			$form.find('.web_form__result').html(res.message);
			$form.find('.web_form_result').html(res.message);
		}
		else
		{
			$form.find('.web_form__result').html(settings.success_message);
			$form.find('.web_form_result').html(settings.success_message);
		}
	}
	else
	{
		$form.find('.web_form__result').addClass('web_form__result--error');
		$form.find('.web_form__result').html(res.message);
		$form.find('.web_form_result').addClass('web_form__result--error');
		$form.find('.web_form_result').html(res.message);
		
		ElberosFormSetFieldsResult($form, res);
	}
}



/**
 * Set error response
 */
function ElberosFormSetErrorResponse ( $form, data, settings )
{
	var json = data.responseJSON;
	if (json == null) json = {};
	ElberosFormSetResponse($form, {
		code: -100,
		message: json.message || "System error",
		error_code: json.code || -100,
	});
}



/**
 * Clear fields result
 */
function ElberosFormClearResult($form)
{
	$form.find('.web_form__value, .web_form_value').each(function(){
		$(this).removeClass('web_form__value--error');
		$(this).removeClass('web_form_value--error');
	});
	$form.find('.web_form__field_result, .web_form_field_result').each(function(){
		var def_value = $(this).attr("data-default");
		if (def_value == undefined)
		{
			$(this).html('');
		}
		else
		{
			$(this).html(def_value);
		}
	});
	$form.find('.web_form_result, .web_form__result').each(function(){
		var def_value = $(this).attr("data-default");
		if (def_value == undefined)
		{
			$(this).html('');
		}
		else
		{
			$(this).html(def_value);
		}
	});
	$form.find('.web_form__field_result').removeClass('web_form__field_result--error');
	$form.find('.web_form_field_result').removeClass('web_form_field_result--error');
	$form.find('.web_form__result').removeClass('web_form__result--error');
	$form.find('.web_form__result').removeClass('web_form_result--error');
	$form.find('.web_form__result').removeClass('web_form__result--success');
	$form.find('.web_form__result').removeClass('web_form_result--success');
	$form.find('.web_form_result').removeClass('web_form__result--error');
	$form.find('.web_form_result').removeClass('web_form_result--error');
	$form.find('.web_form_result').removeClass('web_form__result--success');
	$form.find('.web_form_result').removeClass('web_form_result--success');
}



/**
 * Clear fields result
 */
function ElberosFormClearFieldsResult($form)
{
	$form.find('.web_form__field_result').each(function(){
		var def_value = $(this).attr("data-default");
		if (def_value == undefined)
		{
			$(this).html('');
		}
		else
		{
			$(this).html(def_value);
		}
	});
	$form.find('.web_form__field_result').removeClass('web_form__field_result--error');
}



/**
 * Set result message
 */
function ElberosFormSetResultMessage ( $form, message )
{
	$form.find('.web_form__result').html(message);
}



/**
 * Set fields result
 */
function ElberosFormSetFieldsResult($form, data)
{
	var arr = $form.find('.web_form__field_result, .web_form_field_result');
	for (var i=0; i<arr.length; i++)
	{
		var $item = $(arr[i]);
		var name = $item.attr('data-name');
		var def_value = $item.attr('data-default');
		var msg = (def_value == undefined) ? '' : def_value;
		if (data.fields != undefined && data.fields[name] != undefined)
		{
			msg = data.fields[name].join('<br/>');
			if ($item.hasClass('web_form__field_result')) $item.addClass('web_form__field_result--error');
			if ($item.hasClass('web_form_field_result')) $item.addClass('web_form_field_result--error');
			$item.parents('.web_form__row').find('.web_form__value').addClass('web_form__value--error');
			$item.parents('.web_form__row').find('.web_form_value').addClass('web_form_value--error');
			$item.html(msg);
		}
	}
}



/**
 * Submit form
 */
function ElberosFormSubmit ( $form, settings, callback )
{
	var validation = settings.validation;
	var form_api_name = settings.api_name;
	var form_title = settings.form_title != undefined ? settings.form_title : "";
	var form_position = settings.form_position != undefined ? settings.form_position : "";
	var wp_nonce = $form.find('.web_form__wp_nonce').val();
	
	/* Get data */
	var data = ElberosFormGetData($form);
	
	/* Validation */
	if (typeof validation == "function")
	{
		var res = validation({
			"form": $form,
			"data": data,
			"settings": settings,
		});
		if (res == false)
		{
			return;
		}
	}
	
	/* Result */
	ElberosFormClearResult( $form );
	$form.find('.web_form__result').html('Ожидайте идёт отправка запроса');
	
	var send_data =
	{
		'_wpnonce': wp_nonce,
		'form_api_name': form_api_name,
		'form_title': form_title,
		'form_position': form_position,
		'data': data
	};
	
	elberos_send_data
	(
		"elberos_forms", "submit_form", send_data,
		(function($form, settings, callback){ return function (res)
		{
			var metrika_event = settings.metrika_event;
			var redirect = settings.redirect;
			
			ElberosFormSetResponse($form, res, settings);
			
			if (res.code == 1)
			{
				sendSiteEvent('metrika_event', metrika_event);
				if (redirect != undefined)
				{
					setTimeout
					(
						(function(redirect){ return function(){ document.location = redirect; }})(redirect),
						500
					);
				}
			}
			
			if (callback != undefined && callback != null) callback(res);
			
		}})($form, settings, callback)
	);
}



/**
 * Rename id
 */
function ElberosFormRenameID($el)
{
	var gen_id = Math.random();
	$el.find(".web_form__input").each(function(){
		var id = $(this).attr("id");
		if (id != undefined)
		{
			var new_id = id + "_" + gen_id;
			$el.find("label[for=" + id + "]").attr("for", new_id);
			$(this).attr("id", new_id)
		}
	});
}



/**
 * Show form dialog
 */
function ElberosFormShowDialog($content, settings)
{
	var class_name = window['ElberosFormDialog'];
	if (settings.dialog_class_name != undefined && window[settings.dialog_class_name] != undefined)
	{
		class_name = window[settings.dialog_class_name];
	}
	
	var dialog = new class_name();
	dialog.setSettings(settings.dialog);
	dialog.setContent($content.html());
	dialog.open();
	if (settings.dialog_title != undefined) dialog.setTitle(settings.dialog_title);
	
	var $el = dialog.$el;
	
	/* Rename id */
	ElberosFormRenameID($el);
	
	/* Result */
	$el.find('.web_form__result').html('');
	ElberosFormClearResult( $el );
	
	var callback = null;
	if (typeof settings.callback != "undefined") callback = settings.callback;
	else
	{
		callback = function (dialog, settings)
		{
			return function(res)
			{
				if (typeof settings.onSubmitCallback != "undefined")
				{
					settings.onSubmitCallback(dialog, settings, res);
				}
				else if (res.code == 1)
				{
					if (settings.auto_close == undefined) dialog.close();
					else if (settings.auto_close == true) dialog.close();
				}
			}
		};
	}
	
	if (settings.button_submit_text != undefined)
	{
		dialog.$el.find('.button--submit').html(settings.button_submit_text);
	}
	
	dialog.$el.find('.button--submit').click(
		(function(dialog, settings){return function(){
			
			var $form = dialog.$el.find('form');
			
			if (settings.onSubmit == undefined)
			{
				ElberosFormSubmit
				(
					$form,
					settings,
					(callback != null) ? callback(dialog, settings) : null
				);
			}
			
			else
			{
				settings.onSubmit(dialog, $form, settings, (callback != null) ? callback(dialog, settings) : null);
			}
			
		}})(dialog, settings)
	);
	
	if (settings.onCreate != undefined)
	{
		settings.onCreate(dialog, settings);
	}
	
	return dialog;
}



/**
 * Show dialog
 */
function ElberosShowDialog($content, settings)
{
	var class_name = window['ElberosFormDialog'];
	if (settings == undefined) settings = {};
	if (settings.dialog_class_name != undefined && window[settings.dialog_class_name] != undefined)
	{
		class_name = window[settings.dialog_class_name];
	}
	
	var dialog = new class_name();
	dialog.setSettings(settings);
	dialog.setContent($content.html());
	dialog.open();
	if (settings.dialog_title != undefined) dialog.setTitle(settings.dialog_title);
	
	return dialog;
}


function ElberosDialog()
{
	this.$shadow = null;
	this.$el = null;
	this.is_modal = false;
	this.styles = ['standart'];
	this.title = '';
	this.content = '';
	this.buttons = [];
	this.events = {};
	this.has_scroll_lock = true;
}


Object.assign( ElberosDialog.prototype, {
	
	isset: function(val){ return (val != null) && ((typeof val) != "undefined"); },
	
	setSettings: function(settings)
	{
		if (settings == undefined) return;
	},
	
	setElem: function($el)
	{
		if ($el != null) $el.get(0).controller = null;
		this.$el = $el;
		if (this.$el != null) this.$el.get(0).controller = this;
	},
	
	getElem: function($el)
	{
		return this.$el;
	},
	
	isModal: function()
	{
		return this.is_modal;
	},
	
	setContent: function(val)
	{
		this.content = val;
		if (this.$el != null)
		{
			this.$el.find('.elberos_dialog__content').html(val);
		}
	},
	
	getContent: function()
	{
		return this.content;
	},
	
	setTitle: function(val)
	{
		this.title = val;
		if (this.$el != null)
		{
			this.$el.find('.elberos_dialog__title').html(val);
		}
	},
	
	getTitle: function()
	{
		return this.title;
	},
	
	
	close: function()
	{
		this.$el.remove();
		//$('.elberos_dialog__shadow').remove();
		this.$shadow.remove();
		
		if ($('.elberos_dialog__box').length == 0)
		{
			$('body').removeClass('scroll-lock');
		}
		
		this.sendEvent('close');
		this.setElem(null);
	},

	open: function()
	{
		var $box = this.getDialogBox();
		$box.find('td').append(this.getDialogHtml());
		
		this.$shadow = $('<div class="elberos_dialog__shadow"></div>');
		this.setElem($box);
		$('body').append(this.$shadow);
		$('body').append($box);
		
		// Scroll lock
		if (this.has_scroll_lock) $('body').addClass('scroll-lock');
		this.sendEvent('open');
	},
	
	getDialogStyles: function()
	{
		var styles = '';
		if (this.styles instanceof Array)
		{
			var styles_arr = [];
			for (var i in this.styles)
			{
				styles_arr.push('elberos_dialog__box--' + this.styles[i])
			}
			styles = styles_arr.join(' ');
		}
		return styles;
	},
	
	getDialogBox: function()
	{
		var styles = this.getDialogStyles();
		
		var $obj = $('<div class="elberos_dialog__box ' + styles + '"><table class="elberos_dialog__box_table"><tr class="elberos_dialog__box_tr"><td class="elberos_dialog__box_td"></td></tr></table></div>');
		
		//this.$shadow = $('<div class="elberos_dialog__shadow"></div>');
		//$obj.prepend(this.$shadow);
		
		return $obj;
	},
	
	getDialogHtml: function()
	{
		var $dialog = $('<div class="elberos_dialog"></div>');
		
		$dialog.append(this.getButtonCloseHtml());
		$dialog.append(this.getTitleHtml());
		$dialog.append(this.getContentHtml());
		$dialog.append(this.getButtonsHtml());
		$dialog.append("<div class='elberos_dialog__result'></div>");
		$dialog.append("<div class='clear'></div>");
		
		return $dialog;
	},
	
	getTitleHtml: function()
	{
		var $title = $("<div class='elberos_dialog__title'></div>");
		$title.append(this.getTitle());
		return $title;
	},
	
	getContentHtml: function()
	{
		var $content = $("<div class='elberos_dialog__content'></div>");
		$content.append(this.getContent());
		return $content;
	},
	
	getButtonCloseHtml: function()
	{
		return '<button class="elberos_dialog__button_close"><div></div></button>';
	},
	
	getButtonsHtml: function()
	{
		
		if (this.buttons instanceof Array)
		{
			var $html = $('<div class="elberos_dialog__buttons"></div>');
			for (var i=0; i<this.buttons.length; i++){
				var button = this.buttons[i];
				
				var $button = this.getButtonHtml(button);
				$html.append($button);
			}
			
			return $html;
		}
		else if (this.buttons !== null)
		{
			return $('<div class="elberos_dialog__buttons">'+this.buttons+'</div>');
		}
	},
	
	getButtonHtml: function(button)
	{
		var cls = this.isset(button['class']) ? button['class'] : 'button';
		var text = this.isset(button['text']) ? button['text'] : 'Text';
		var click = this.isset(button['click']) ? button['click'] : function(dialog, $button){
			dialog.close();
		}
		
		// Create button
		var $button = $("<button class='" + cls + "'>"+ text +"</button>");
		$button.click((function(dialog, $button, button){
			return function(){
				button['click'](dialog, $button);
			}
		})(this, $button, button));
		
		return $button;
	},
	
	
	
	/**
	 * Subscribe on form event
	 */
	subscribe: function(event, callback)
	{
		
		if (!this.isset(this.events[event]))
		{
			this.events[event] = new Array();
		}
		
		this.events[event].push(callback);
	},
	
	
	
	/**
	 * Send form event
	 */
	sendEvent: function(event, data)
	{
		if (!this.isset(data)) data = null;
		
		var res = null;
		if (this.isset(this.events[event]))
		{
			var events = this.events[event];
			for (var i=0; i<events.length; i++)
			{
				res = events[i](event, data);
			}
		}
		
		return res;
	},
});



$(document).on('click', '.elberos_dialog__button_close', function(){
	var $box = $(this).parents('.elberos_dialog__box');
	if ($box.length == 0)
		return;
	if ($box.controller == 0)
		return;
	var box = $box.get(0);
	if (box.controller instanceof ElberosDialog)
	{
		box.controller.close();
	}
});


$(document).on('mousedown', '.elberos_dialog__box .elberos_dialog__box_table', function(e){
	
	var $box = $(e.target);
	if (
		$box.hasClass('elberos_dialog__box_table') ||
		$box.hasClass('elberos_dialog__box_tr') || 
		$box.hasClass('elberos_dialog__box_td') ||
		$box.hasClass('elberos_dialog__shadow')
	){
		$box = $box.parents('.elberos_dialog__box');
	}
	if (!$box.hasClass('elberos_dialog__box') && !$(e.target).hasClass('elberos_dialog__shadow'))
		return;
	if ($box.length == 0)
		return;
	if ($box.controller == 0)
		return;
	var box = $box.get(0);
	if (box.controller instanceof ElberosDialog)	
	{
		if (!box.controller.isModal())
			box.controller.close();
	}
});


/** Web Form Dialog **/

function ElberosFormDialog()
{
	ElberosDialog.call(this);
}

ElberosFormDialog.prototype = Object.create(ElberosDialog.prototype);
ElberosFormDialog.prototype.constructor = ElberosDialog;

Object.assign( ElberosFormDialog.prototype, {
	getDialogHtml: function()
	{
		var $dialog = $('<div class="elberos_dialog"></div>');
		
		$dialog.append(this.getButtonCloseHtml());
		$dialog.append(this.getTitleHtml());
		$dialog.append(this.getContentHtml());
		$dialog.append("<div class='clear'></div>");
		
		return $dialog;
	},
});


/** Web Image Dialog **/

function ElberosImageDialog()
{
	ElberosDialog.call(this);
	
	this.styles.push('image');
	this.images = [];
	this.pos = 0;
}

ElberosImageDialog.prototype = Object.create(ElberosDialog.prototype);
ElberosImageDialog.prototype.constructor = ElberosDialog;

Object.assign( ElberosImageDialog.prototype, {
	
	getDialogHtml: function(){
		var $dialog = $('<div class="elberos_dialog"></div>');
		
		var $img = $('<div class="elberos_dialog__image"></div>')
		$img.append("<img class='elberos_dialog__image_preview' unselectable='on'></img>");
		$img.append(this.getButtonCloseHtml());
		$img.append("<div class='elberos_dialog__image_title'></div>");
		
		$dialog.append($img);
		$dialog.append("<div class='clear'></div>");
		if (this.images.length > 1)
		{
			$dialog.append("<div class='elberos_dialog__arrow elberos_dialog__arrow--left'></div>");
			$dialog.append("<div class='elberos_dialog__arrow elberos_dialog__arrow--right'></div>");
		}
		
		// Disable select
		$dialog.find('.elberos_dialog__image_preview').on('selectstart', false);
		
		// Arrow click
		$dialog.find('.elberos_dialog__arrow--left').click((function(obj){
			return function(e){ 
				obj.showPrevImage();
				e.preventDefault();
				return false;
			}
		})(this));
		
		$dialog.find('.elberos_dialog__arrow--right').click((function(obj){
			return function(e){ 
				obj.showNextImage();
				e.preventDefault();
				return false;
			}
		})(this));
		
		// Mouse move
		$dialog.find('.elberos_dialog__arrow').mousemove((function(obj){
			return function(e){ 
				e.preventDefault();
				return false;
			}
		})(this));
		
		return $dialog;
	},
	
	open: function(){
		ElberosDialog.prototype.open.call(this);
		this.showCurrentImage();
	},
	
	push: function(src, title){
		this.images.push({
			src: src,
			title: title,
		});
	},
	
	setCurrentImage: function(src){
		this.pos = 0;
		
		for (var i=0; i<this.images.length; i++){
			if (this.images[i].src == src)
				this.pos = i;
		}
	},
	
	showCurrentImage: function(){
		this.pos = this.showImage(this.pos);
	},
	
	showNextImage: function(){
		this.pos = this.showImage(this.pos + 1);
	},
	
	showPrevImage: function(){
		this.pos = this.showImage(this.pos - 1);
	},
	
	showImage: function(pos){
		if (this.images.length == 0)
			return 0;
		
		pos = pos % this.images.length;
		pos = (pos + 2 * this.images.length) % this.images.length;
		
		var image = this.images[pos];
		this.$el.find('.elberos_dialog__image_preview').attr('src', '');
		this.$el.find('.elberos_dialog__image_preview').attr('src', image.src);
		
		if (image.title == undefined) this.$el.find('.elberos_dialog__image_title').html('');
		else this.$el.find('.elberos_dialog__image_title').html(image.title);
		
		return pos;
	},
	
});


$(document).on('click', '.gallery__item', function(e){
	
	var dialog = new ElberosImageDialog();
	var $gallery = $(this).parents('.gallery');
	
	var styles = $gallery.attr("data-gallery-style");
	if (styles)
	{
		dialog.styles = styles.split(" ").filter(function (s){ return s != "" });
	}
	
	$gallery.find('.gallery__item').each(function(){
		var src = $(this).attr('src');
		var href = $(this).attr('data-image-big');
		var title = $(this).attr('data-image-title');
		
		if ( $(this).hasClass('bx-clone') )
			return;
		
		if (href == undefined || href == null) href = src;
		
		dialog.push(href, title);
	});
	
	dialog.setCurrentImage( $(this).attr('data-image-big') );
	dialog.open();
});

function reloadAllCaptcha(){
	var href = '/api/captcha/create/?_=' + Math.random();
	$('img.elberos_captcha_image').each(function(){
		$(this).attr('src', href);
	});
}
$(document).on('click', 'img.elberos_captcha_image', function(){
	reloadAllCaptcha();
});

function scrollTop(top, duration, easing){
	if (duration == undefined) duration = 'slow';
	if (easing == undefined) easing = 'swing';
	if (top == undefined) top = 0;
	$('body,html').animate({'scrollTop': top}, duration, easing);
}

function scrollToElement(el, duration, easing){
	var top = $(el).offset().top;
	scrollTop(top, duration, easing);
}