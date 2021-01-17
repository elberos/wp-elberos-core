"use strict";

/*!
 *  Elberos Dialog
 *  URL: https://github.com/elberos/wp-elberos-core 
 *
 *  (c) Copyright 2020 "Ildar Bikmamatov" <support@elberos.org>
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
}


Object.assign( ElberosDialog.prototype, {
	
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
		$('body').append(this.$shadow);
		$('body').append($box);
		this.setElem($box);
		
		// Scroll lock
		$('body').addClass('scroll-lock');
		this.sendEvent('open');
	},
	
	getDialogBox: function()
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
		return '<button class="button elberos_dialog__button_close"><div></div></button>';
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
		
		if (!isset(this.events[event]))
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
		if (!isset(data)) data = null;
		
		var res = null;
		if (isset(this.events[event]))
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


$(document).on('click', '.elberos_dialog__box .elberos_dialog__box_table', function(e){
	
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
