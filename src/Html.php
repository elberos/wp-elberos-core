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


/* Check if Wordpress */
if (!defined('ABSPATH')) exit;

use Elberos\RawString;


/* Class Html */
class Html
{
	
	/**
	 * Escape string
	 *
	 * @param string $str string
	 * @return string escaped string
	 */ 
	static function escape($str)
	{
		$str = (string)$str;
		return @htmlspecialchars($str, ENT_COMPAT | ENT_HTML401, 'UTF-8');
	} 
	
	
	
	/**
	 * Output string
	 *
	 * @param string $str string
	 * @return string escaped string
	 */ 
	static function s($content)
	{
		if ($content == null) return "";
		$s = "";
		if (gettype($content) == 'array')
		{
			foreach ($content as $item)
			{
				$s .= static::s($item);
			}
		}
		else if ($content instanceof RawString)
		{
			$s .= (string)$content->s;
		}
		else
		{
			$s .= static::escape((string)$content);
		}
		return $s;
	} 
	
	
	
	/**
	 * Convert attrs to string
	 */
	static function attrs($attrs)
	{
		$arr = [];
		foreach ($attrs as $key => $value)
		{
			if ($value === "") continue;
			$arr[] = $key . "='" . esc_attr($value) . "'";
		}
		return implode(" ", $arr);
	}
	
	
	
	/**
	 * Output elem
	 */
	static function elem($tag_name, $class_name, $attrs = [], $content = null)
	{
		$s = "";
		
		if (!isset($attrs['class'])) $attrs['class'] = "";
		$attrs['class'] = $class_name . (($attrs['class'] != "") ? (" " . trim($attrs['class'])) : "");
		
		//var_dump($attrs);
		$attrs = static::attrs($attrs);
		if ($attrs != "") $attrs = " " . $attrs;
		
		$s .= "<" . $tag_name . $attrs . ">";
		$s .= static::s($content);
		$s .= "</$tag_name>";
		
		return new RawString($s);
	}
	
	
	
	/**
	 * Level
	 */
	static function level($items)
	{
		return $items;
	}
	
	
	
	/**
	 * Level
	 */
	static function script($js_code)
	{
		$s = "";
		$s .= "<script type='text/javascript'>";
		$s .= static::s($js_code);
		$s .= "</script>";
		return new RawString($s);
	}
}
