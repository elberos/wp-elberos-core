<?php

/*!
 *  Elberos Framework
 *
 *  (c) Copyright 2019-2021 "Ildar Bikmamatov" <support@elberos.org>
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

namespace Elberos;

use Elberos\Html;
use Elberos\RawString;



/**
 * Class Slider
 * 
 */ 
class Slider
{
	
	var $id = "";
	var $animate = true;
	var $points = true;
	var $content = [];
	var $subparams = [];
	var $infinity = true;
	var $vertical = false;
	var $arrows = true;
	var $speed_animate = 500;
	var $autoplay = true;
	var $autoplay_timeout = 10000;
	var $current_pos = 0;
	var $items_per_screen = 1;
	var $item_css_display = 'inline-block';
	var $width = 'auto';
	var $height = -1;
	var $js_init = true;
	
	
	/**
	 * Constructor
	 *
	 * @return array 
	 */
	public function __construct($params)
	{
		foreach ($params as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	
	
	/**
	 * Output load js and css
	 */
	public static function loadAssets($load_css = false)
	{
		$content = "\$load.subscribe(['jquery_loaded'])".
		".load([".
			($load_css ? "'/wp-content/plugins/wp-elberos-core/assets/slider.css', " : "") .
			"'/wp-content/plugins/wp-elberos-core/assets/slider.js', " .
		"])".
		".deliver('elberos_slider_loaded');";
		return $content;
	}
	
	
	
	/**
	 * Add item
	 */
	public function addItem($content)
	{
		$this->content[] = new RawString($content);
	}
	
	
	
	/**
	 * Render slider items
	 *
	 * @return Html
	 */
	public function renderItems()
	{
		$first = true;
		$component_content = [];
		
		// fake before
		if ($this->infinity)
		{
			$pos = - count($this->content);
			foreach ($this->content as $html)
			{
				$subparams = $this->subparams;
				$subparams['data-pos'] = $pos;
				if (!isset($subparams['class']))
				{
					$subparams['class'] = '';
				}
				$subparams['class'] .= ' fake';
				if ($first) $first = false; else $subparams['style'] = "display:none;";
				$component_content[] = Html::elem("div", "elberos_slider__item", $subparams, $html);
				$pos++;
			}
		}
		
		$pos = 0;
		foreach ($this->content as $html)
		{
			$subparams = $this->subparams;
			$subparams['data-pos'] = $pos;
			
			if (!isset($subparams['class']))
			{
				$subparams['class'] = '';
			}
			$subparams['class'] .= ' nofake';
			
			if ($pos == $this->current_pos)
			{
				$subparams['class'] .= ' current';
			}
			if ($first) $first = false; else $subparams['style'] = "display:none;";
			$component_content[] = Html::elem("div", "elberos_slider__item", $subparams, $html);
			$pos++;
		}
		
		
		// fake after
		if ($this->infinity)
		{
			foreach ($this->content as $html)
			{
				$subparams = $this->subparams;
				$subparams['data-pos'] = $pos;
				if (!isset($subparams['class']))
				{
					$subparams['class'] = '';
				}
				$subparams['class'] .= ' fake';
				if ($first) $first = false; else $subparams['style'] = "display:none;";
				$component_content[] = Html::elem("div", "elberos_slider__item", $subparams, $html);
				$pos++;
			}
		}
		
		return Html::elem("div", "elberos_slider__container", [], [
			Html::elem("div", "elberos_slider__wrap", [], [
				Html::elem("div", "elberos_slider__items", [], $component_content),
			])
		]);
	}
	
	
	
	/**
	 * Render slider items
	 *
	 * @return Html
	 */
	public function renderPoints()
	{
		if (!$this->points)
			return null;
		
		$pos = 0;
		$points = [];
		foreach ($this->content as $html)
		{
			$current = "";
			if ($pos == $this->current_pos) $current = " current";
			$points[] = Html::elem("div", "elberos_slider__point".$current, ['data-pos'=>$pos]);
			$pos++;
		}
		
		return Html::elem("div", "elberos_slider__points", [], $points);
	}
	
	
	
	/**
	 * Render javascript code
	 *
	 * @param string $js_var_name
	 */
	public function renderJs($js_var_name = null)
	{
		if (!$this->js_init)
			return null;
		
		if ($js_var_name == null)
			$js_var_name = $this->id . "_elem";
		
		$js_code = [];
		$js_code[] = new RawString("var " . $js_var_name . "_ = null;");
		$js_code[] = new RawString("\$load.subscribe('elberos_slider_loaded', function(){");
		$js_code[] = Html::level([
			new RawString($js_var_name . " = new ElberosSlider(\$(" . json_encode("#" . $this->id) . "));"),
			new RawString($js_var_name . ".vertical = " . json_encode($this->vertical) . ";"),
			new RawString($js_var_name . ".animate = " . json_encode($this->animate) . ";"),
			new RawString($js_var_name . ".speed_animate = " . json_encode($this->speed_animate) . ";"),
			new RawString($js_var_name . ".items_per_screen = " . json_encode($this->items_per_screen) . ";"),
			new RawString($js_var_name . ".item_css_display = " . json_encode($this->item_css_display) . ";"),
			new RawString($js_var_name . ".slider_width = " . json_encode($this->width) . ";"),
			new RawString($js_var_name . ".slider_height = " . json_encode($this->height) . ";"),
			new RawString($js_var_name . ".autoplay = " . json_encode($this->autoplay) . ";"),
			new RawString($js_var_name . ".autoplay_timeout = " . json_encode($this->autoplay_timeout) . ";"),
			new RawString($js_var_name . ".infinity = " . json_encode($this->infinity) . ";"),
			new RawString($js_var_name . ".init();"),
			new RawString($js_var_name . ".setPos(" . json_encode($this->current_pos) . ", false);"),
			$this->autoplay ? new RawString($js_var_name . ".startAutoplay();") : null,
		]);
		$js_code[] = new RawString("});");
		
		return Html::script($js_code);
	}
	
	
	
	/**
	 * Render Arrows
	 */
	public function renderArrows()
	{
		if (!$this->arrows)
			return null;
		
		$component_content = [];
		
		if ($this->vertical)
		{
			$component_content[] = Html::elem("div", "elberos_slider__arrow elberos_slider__arrow--top");
			$component_content[] = Html::elem("div", "elberos_slider__arrow elberos_slider__arrow--bottom");
		}
		else
		{
			$component_content[] = Html::elem("div", "elberos_slider__arrow elberos_slider__arrow--left");
			$component_content[] = Html::elem("div", "elberos_slider__arrow elberos_slider__arrow--right");
		}
		
		return $component_content;
	}
	
	
	
	/**
	 * Render component
	 */
	public function render($content=[])
	{
		$this->attrs['id'] = $this->id;
		
		$class = "slider";
		if ($this->vertical)
			$class .= " slider--vertical";
		else
			$class .= " slider--horizontal";
		
		$content = [];
		$content[] = $this->renderArrows();
		$content[] = $this->renderItems();
		$content[] = $this->renderPoints();
		$content[] = $this->renderJs();
		
		return Html::elem("div", $class, $this->attrs, $content);
	}
	
}
