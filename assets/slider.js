"use strict";

/*!
 *  Elberos Slider Plugin
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


/**
 * Constructor
 */ 
function ElberosSlider($el, params)
{
	this.$el = $el;
	this.$el.get(0).controller = this;
	
	this.events = {};
	this.count = 0;
	this.real_count = 0;
	this.current_pos = 0;
	this.slider_width = -1;
	this.slider_height = -1;
	this.autoplay = false;
	this.infinity = false;
	this.vertical = false;
	this.animate = true;
	this.speed_animate = 300;
	this.items_per_screen = 1;
	this.item_css_display = "inline-block";
	
	this.autoplay = true;
	this.autoplay_timeout = 10000;
	this.autoplay_timer = null;
	
	if (params != undefined)
	{
		for (var key in params){
			this[key] = params[key];
		}
	}
}

Object.assign( ElberosSlider.prototype, {
	
	/**
	 * Init form
	 */
	init: function(params)
	{
		// Subscribe event prev
		this.$el.find('.elberos_slider__arrow--left').click(bindCtx(this.movePrev, this));
		this.$el.find('.elberos_slider__arrow--top').click(bindCtx(this.movePrev, this));
		
		// Subscribe event next
		this.$el.find('.elberos_slider__arrow--right').click(bindCtx(this.moveNext, this));
		this.$el.find('.elberos_slider__arrow--bottom').click(bindCtx(this.moveNext, this));
		
		// Subscribe pointers
		this.$el.find('.elberos_slider__point').click(bindCtx(this.clickPoint, this));
		
		// Show sliders
		this.$el.find('.elberos_slider__item').css("display", this.item_css_display);
		
		// Calc slider count
		this.calcSliderCount();
		
		// Send event ready
		this.sendEvent('ready');
	},
	
	
	
	/**
	 * Calc slider count
	 */ 
	calcSliderCount: function()
	{
		if (this.slider_width == 'auto')
		{
			this.slider_width = this.$el.find('.elberos_slider__wrap').width() / this.items_per_screen;
			this.$el.find('.elberos_slider__item').width(this.slider_width);
		}
		
		if (this.slider_height == 'auto')
		{
			this.slider_height = this.$el.find('.elberos_slider__wrap').height() / this.items_per_screen;
			this.$el.find('.elberos_slider__item').height(this.slider_height);
		}
		
		this.real_count = 0;
		this.count = 0;
		this.$el.find('.elberos_slider__item').each
		(
			(function(obj){
				return function(){
					
					obj.real_count++;
					
					if ($(this).hasClass('nofake'))
						obj.count++;
					
					if (obj.slider_width == -1)
						obj.slider_width = $(this).outerWidth();
					
					if (obj.slider_height == -1)
						obj.slider_height = $(this).outerHeight();
				}
			})(this)
		);
		
		if (this.vertical){
			this.$el.find('.elberos_slider__items').height(this.slider_height * this.real_count);
		}
		else{
			this.$el.find('.elberos_slider__items').width(this.slider_width * this.real_count);
		}
		
	},
	
	
	
	/**
	 * Animate show slider
	 */
	showSlider: function(new_pos, old_pos, animate)
	{
		var $slider__items = this.$el.find('.elberos_slider__items');
		var width = this.slider_width;
		var height = this.slider_height;
		var count = this.count;
		
		var newTop = 0;
		var newLeft = 0;
		this.$el.find('.elberos_slider__item').each(function(){
			$(this).removeClass('current');
			var pos = $(this).attr('data-pos');
			if (pos == new_pos){
				$(this).addClass('current');
				var csspos = $(this).position();
				newLeft = -csspos.left;
				newTop = -csspos.top;
			}
		});
		this.$el.find('.elberos_slider__point').each(function(){
			$(this).removeClass('current');
			var pos = $(this).attr('data-pos');
			if (pos == new_pos){
				$(this).addClass('current');
			}
		});
		
		if (this.vertical){
			if (animate){
				this.stopAutoplay();
				$slider__items.animate(
					{ "top": newTop+"px" }, 
					this.speed_animate,
					false,
					bindCtx(this.endAnimate, this)
				);
			}
			else{
				$slider__items.css("top", newTop+"px");
			}
		}
		else{
			if (animate){
				this.stopAutoplay();
				$slider__items.animate(
					{ "left": newLeft+"px" }, 
					this.speed_animate,
					false,
					bindCtx(this.endAnimate, this)
				);			
			}
			else{
				$slider__items.css("left", newLeft+"px");
			}
		}
	},
	
	
	
	/**
	 * end slider animate
	 */
	endAnimate: function()
	{
		var new_pos = (this.current_pos % this.count + this.count*2) % this.count;
		if (new_pos != this.current_pos){
			this.showSlider(new_pos, this.current_pos, false);
			this.current_pos = new_pos;
		}
		
		this.startAutoplay();
	},
	
	
	
	/**
	 * Set slider position
	 */
	setPos: function(pos, animate)
	{
		if (this.count == 0)
			return;
		
		if (animate == undefined)
		{
			animate = this.animate;
		}
		
		var old_pos = this.current_pos;
		
		if (this.infinity && pos >= -this.count && pos <= this.count*2 - 1){			
			this.current_pos = pos;
			this.showSlider(this.current_pos, old_pos, animate);
		}
		else{
			var old_pos = this.current_pos;
			this.current_pos = (pos % this.count + this.count*2) % this.count;
			this.showSlider(this.current_pos, old_pos, animate);
		}
		
		//this.current_pos = (pos % this.count + this.count*2) % this.count;
		
		this.sendEvent('setPos', pos);
	},
	
	
	
	/**
	 * Move slider prev
	 */
	movePrev: function()
	{
		this.setPos(this.current_pos - 1, this.animate);
	},
	
	
	
	/**
	 * Move slider next
	 */
	moveNext: function()
	{
		this.setPos(this.current_pos + 1, this.animate);
	},
	
	
	
	/**
	 * Click pointer
	 */
	clickPoint: function(e)
	{
		var pos = e.target.getAttribute('data-pos');
		this.setPos(pos, this.animate);
	},
	
	
	
	/**
	 * Start autoplay
	 */
	startAutoplay: function()
	{
		if (this.autoplay_timer != null || this.autoplay == false)
			return;
		
		this.autoplay_timer = setInterval
		(
			(function(obj){
				return function(){
					obj.moveNext();
				}
			})(this),
			this.autoplay_timeout
		);
	},
	
	
	
	/**
	 * Stop autoplay
	 */
	stopAutoplay: function()
	{
		if (this.autoplay_timer == null)
			return;
		
		clearTimeout(this.autoplay_timer);
		this.autoplay_timer = null;
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