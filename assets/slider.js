"use strict";

/*!
 *  Elberos Slider Plugin
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
	this.animate_process = false;
	this.swipe = true;
	this.swipe_x = 0;
	this.swipe_y = 0;
	this.swipe_start_x = 0;
	this.swipe_start_y = 0;
	this.swipe_shift_x = 0;
	this.swipe_shift_y = 0;
	this.swipe_process = false;
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
		
		// If swipe
		if (this.swipe)
		{
			var el = this.$el.get(0);
			el.addEventListener('mousedown', bindCtx(this.mouseDown, this));
			
			var body = document.getElementsByTagName("body")[0];
			body.addEventListener('mousemove', bindCtx(this.mouseMove, this));
			body.addEventListener('mouseup', bindCtx(this.mouseUp, this));
			
			el.addEventListener("touchstart", bindCtx(this.mouseDown, this), false);
			el.addEventListener("touchend", bindCtx(this.mouseUp, this), false);
			el.addEventListener("touchcancel", bindCtx(this.mouseCancel, this), false);
			el.addEventListener("touchmove", bindCtx(this.mouseMove, this), false);
		}
		
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
				return function()
				{
					
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
		
		if (this.vertical)
		{
			this.$el.find('.elberos_slider__items').height(this.slider_height * this.real_count);
		}
		else
		{
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
			if (pos == new_pos)
			{
				$(this).addClass('current');
				var csspos = $(this).position();
				newLeft = -csspos.left;
				newTop = -csspos.top;
			}
		});
		this.$el.find('.elberos_slider__point').each(function(){
			$(this).removeClass('current');
			var pos = $(this).attr('data-pos');
			if (pos == new_pos)
			{
				$(this).addClass('current');
			}
		});
		
		if (this.vertical)
		{
			if (animate)
			{
				this.stopAutoplay();
				this.animate_process = true;
				$slider__items.animate
				(
					{ "top": newTop+"px" },
					this.speed_animate,
					false,
					bindCtx(this.endAnimate, this)
				);
			}
			else
			{
				$slider__items.css("top", newTop+"px");
			}
		}
		else
		{
			if (animate)
			{
				this.stopAutoplay();
				this.animate_process = true;
				$slider__items.animate
				(
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
		if (new_pos != this.current_pos)
		{
			this.showSlider(new_pos, this.current_pos, false);
			this.current_pos = Number(new_pos);
		}
		
		this.animate_process = false;
		this.startAutoplay();
	},
	
	
	
	/**
	 * Set slider position
	 */
	setPos: function(pos, animate)
	{
		pos = Number(pos);
		
		if (this.count == 0)
			return;
		
		if (animate == undefined)
		{
			animate = this.animate;
		}
		
		var old_pos = Number(this.current_pos);
		
		if (this.infinity && pos >= -this.count && pos <= this.count*2 - 1)
		{
			this.current_pos = pos;
			this.showSlider(this.current_pos, old_pos, animate);
		}
		else
		{
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
	 * Mouse down
	 */
	mouseDown: function(e)
	{
		var find_item = $(e.target).parents('.elberos_slider__item').get(0);
		if (find_item == undefined) return;
		if (this.animate_process) return;
		if (this.swipe)
		{
			var $slider__items = this.$el.find('.elberos_slider__items');
			var slider_top = Number($slider__items.css('top').replace('px', ''));
			var slider_left = Number($slider__items.css('left').replace('px', ''));
			this.swipe_start_x = slider_left;
			this.swipe_start_y = slider_top;
			
			if (TouchEvent != undefined && e instanceof TouchEvent)
			{
				this.swipe_x = e.changedTouches[0].pageX;
				this.swipe_y = e.changedTouches[0].pageY;
			}
			else
			{
				this.swipe_x = e.pageX;
				this.swipe_y = e.pageY;
			}
			
			this.swipe_process = true;
			this.stopAutoplay();
		}
	},
	
	
	
	/**
	 * Mouse move
	 */
	mouseMove: function(e)
	{
		if (this.swipe_process)
		{
			var $slider__items = this.$el.find('.elberos_slider__items');
			var slider_top = $slider__items.get(0).offsetTop;
			var slider_left = $slider__items.get(0).offsetLeft;
			var pageX = 0;
			var pageY = 0;
			
			if (TouchEvent != undefined && e instanceof TouchEvent)
			{
				pageX = e.changedTouches[0].pageX;
				pageY = e.changedTouches[0].pageY;
			}
			else
			{
				pageX = e.pageX;
				pageY = e.pageY;
			}
			
			var shift_x = pageX - this.swipe_x;
			var shift_y = pageY - this.swipe_y;
			this.swipe_shift_x = shift_x;
			this.swipe_shift_y = shift_y;
			
			if (this.vertical)
			{
				$slider__items.css("top", (this.swipe_start_y + shift_y) + "px");
			}
			else
			{
				$slider__items.css("left", (this.swipe_start_x + shift_x) + "px");
			}
		}
	},
	
	
	
	/**
	 * Mouse up
	 */
	mouseUp: function(e)
	{
		if (this.swipe_process)
		{
			this.swipe_process = false;
			
			var shift = 150;
			if (TouchEvent != undefined && e instanceof TouchEvent)
			{
				shift = 100;
			}
			
			if (this.vertical)
			{
				if (this.swipe_shift_y < -shift || this.swipe_shift_y > shift)
				{
					this.makeSwipe(this.animate);
				}
				else
				{
					this.cancelSwipe(this.animate);
				}
			}
			else
			{
				if (this.swipe_shift_x < -shift || this.swipe_shift_x > shift)
				{
					this.makeSwipe(this.animate);
				}
				else
				{
					this.cancelSwipe(this.animate);
				}
			}
			
			this.startAutoplay();
		}
	},
	
	
	
	/**
	 * Mouse cancel
	 */
	mouseCancel: function ()
	{
		if (this.swipe_process)
		{
			this.cancelSwipe(this.animate);
		}
	},
	
	
	
	/**
	 * Make swipe
	 */
	makeSwipe: function()
	{
		var $slider__items = this.$el.find('.elberos_slider__items');
		var next_pos = 0;
		
		if (this.vertical)
		{
			if (this.swipe_shift_y > 0)
			{
				next_pos = this.current_pos - 1;
			}
			else if (this.swipe_shift_y < 0)
			{
				next_pos = this.current_pos + 1;
			}
			else
			{
				return;
			}
		}
		else
		{
			if (this.swipe_shift_x > 0)
			{
				next_pos = this.current_pos - 1;
			}
			else if (this.swipe_shift_x < 0)
			{
				next_pos = this.current_pos + 1;
			}
			else
			{
				return;
			}
		}
		
		if (!this.infinity)
		{
			if (next_pos < 0)
			{
				this.cancelSwipe(this.animate);
				return;
			}
			if (next_pos >= this.count)
			{
				this.cancelSwipe(this.animate);
				return;
			}
		}
		
		this.setPos(next_pos, this.animate);
	},
	
	
	
	/**
	 * Cancel swipe
	 */
	cancelSwipe: function(animate)
	{
		var $slider__items = this.$el.find('.elberos_slider__items');
		var newLeft = this.swipe_start_x;
		var newTop = this.swipe_start_y;
		
		if (this.vertical)
		{
			if (animate)
			{
				this.stopAutoplay();
				$slider__items.animate
				(
					{ "top": newTop+"px" },
					this.speed_animate,
					false,
					bindCtx(this.endAnimate, this)
				);
			}
			else
			{
				$slider__items.css("top", newTop+"px");
			}
		}
		else
		{
			if (animate)
			{
				this.stopAutoplay();
				$slider__items.animate
				(
					{ "left": newLeft+"px" },
					this.speed_animate,
					false,
					bindCtx(this.endAnimate, this)
				);
			}
			else
			{
				$slider__items.css("left", newLeft+"px");
			}
		}
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
				return function()
				{
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