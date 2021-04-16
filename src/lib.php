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


/**
 * Remove last slash
 */
function remove_last_slash($path)
{
	$sz = strlen($path);
    if ($sz == 0) return "";
	if ($path[$sz - 1] == "/") return substr($path, 0, -1);
	return $path;
}


/**
 * Make a string's first character uppercase
 * http://stackoverflow.com/questions/2517947/ucfirst-function-for-multibyte-character-encodings
 *
 * @param string $path The input string
 * @return string Returns the resulting string
 */
function mb_ucfirst($string, $encoding='utf-8')
{
    $strlen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strlen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}



/**
 * Returns information about a file path.
 * From http://php.net/manual/en/function.pathinfo.php#107461
 *
 * @param string $path The path to be parsed
 * @return array
 */
function mb_pathinfo($filepath)
{
    preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im',$filepath,$m);
    $ret['dirname']=isset($m[1])?$m[1]:'';
    $ret['basename']=isset($m[2])?$m[2]:'';
    $ret['extension']=isset($m[5])?$m[5]:'';
    $ret['filename']=isset($m[3])?$m[3]:'';;
    return $ret;
}



/**
 * Return extension
 */
function mb_extension($filepath)
{
    $ret = mb_pathinfo($filepath);
    return $ret['extension'];
}



/**
 * Make file dir
 *
 * @param string $path The path of the filename
 */
function make_dir_by_filename($file_path, $mode = 0755)
{
	$arr = mb_pathinfo($file_path);
	$dirname = $arr['dirname'];
	if (!file_exists($dirname)) 
	{
		mkdir ($dirname, $mode, true);
	}
}



/**
 * Convert to money
 */ 
function to_money($value, $decimals=2)
{
	return number_format($value, $decimals, ".", " ");
}



/**
 * Make index
 *
 * @param array $arr
 * @param string $field_name
 * @return array
 */
function make_index($arr, $field_name='id')
{
	$index = [];
	foreach ($arr as $key => &$val){
		if (!isset($val[$field_name]))
			continue;
		$index[ $val[$field_name] ] = $key;
	}
	return $index;
}



/**
 * Get row from index
 *
 * @param array $arr
 * @param string $field_name
 * @return array
 */
function index($arr, $index, $value, $default = null)
{
	if (!isset($index[$value]))
		return $default;

	$key = $index[$value];

	if (!isset($arr[$key]))
		return $default;

	return $arr[$key];
}


function str_split2($str, $split_length)
{
	$str = (string) $str;
	$pos = strlen($str) - $split_length;
	$res = [];
	
	while ($pos >= 0){
		$res[] = substr($str, $pos, $split_length);
		$pos -= $split_length;
	}
	
	if ($pos < 0){
		$res[] = substr($str, 0, $pos + $split_length);
	}
	$res = array_reverse($res);
	
	return $res;
}



/**
 * Split number
 *
 * @param $number
 * @param $split_length
 * @param $count
 * @param $cut_end
 * @return 
 */
function split_number($number, $split_length, $count=-1, $cut_end = true){
	
	$arr = str_split2($number, $split_length);
	$arr_len = count($arr);
	
	if ($count == -1)
	{
		for ($i=0; $i<$arr_len; $i++)
		{
			$arr[$i] = str_pad($arr[$i], $split_length, '0', STR_PAD_LEFT);
		}
		return $arr;
	}
	
	if (!$cut_end){
		$count = $count - 1;
	}
	
	$res = [];
	while ($count > 0)
	{
		
		$c = 0;
		if (count($arr) > 0)
			$c = array_pop($arr);
		
		$c = str_pad($c, $split_length, '0', STR_PAD_LEFT);
		$res[] = $c;
		$count --;
	}
	
	if (!$cut_end)
	{
		$res[] = implode("", $arr);
	}
	
	$res = array_reverse($res);
	
	return $res;
}


function create_date_from_timestamp($timestamp, $tz)
{
	$tz = $tz instanceof \DateTimeZone ? $tz : new \DateTimeZone($tz);
	$dt = new \DateTime();
	$dt->setTimestamp($timestamp);
	$dt->setTimezone($tz);
	return $dt;
}

function create_date_from_string($date, $format = 'Y-m-d H:i:s', $tz = 'UTC')
{
	$tz = $tz instanceof \DateTimeZone ? $tz : new \DateTimeZone($tz);
	$dt = \DateTime::createFromFormat($format, $date, $tz);
	return $dt;
}

function tz_date($timestamp = null, $format = 'Y-m-d H:i:s', $tz = 'UTC')
{
	if ($timestamp === null) $timestamp = time();
	$dt = \Elberos\create_date_from_timestamp($timestamp, $tz);
	return $dt->format($format);
}

function tz_timestamp($date, $format = 'Y-m-d H:i:s', $tz = 'UTC')
{
	$dt = \Elberos\create_date_from_string($date, $format, $tz);
	if ($dt) return $dt->getTimestamp();
	return 0;
}

function get_wp_timezone()
{
	$timezone_string = get_option( 'timezone_string' );
	if (!empty($timezone_string)) return $timezone_string;
	$offset = get_option( 'gmt_offset' );
	$hours = (int)$offset;
	$minutes = abs(($offset - (int)$offset) * 60);
	$offset = sprintf('%+03d:%02d', $hours, $minutes);
	return "GMT" . $offset;
}

function wp_create_date_from_string($date)
{
	$tz = new \DateTimeZone( get_wp_timezone() );
	$dt = \Elberos\create_date_from_string($date, 'Y-m-d H:i:s', $tz);
	return $dt;
}

function wp_date_to_timestamp($date)
{
	$tz = new \DateTimeZone( get_wp_timezone() );
	$dt = \Elberos\create_date_from_string($date, 'Y-m-d H:i:s', $tz);
	if ($dt) return $dt->getTimestamp();
	return 0;
}

function wp_from_gmtime($date, $format = 'Y-m-d H:i:s', $tz = 'UTC')
{
	$dt = \Elberos\create_date_from_string($date, 'Y-m-d H:i:s', $tz);
	if ($dt)
	{
		$dt->setTimezone( new \DateTimeZone( get_wp_timezone() ) );
		return $dt->format($format);
	}
	return "";
}

function wp_langs()
{
	$res = [];
	if ( defined( 'POLYLANG_VERSION' ) )
	{
		$links = PLL()->links;
		$langs = $links->model->get_languages_list( array( 'hide_empty' => 1 ));
		foreach ($langs as $lang)
		{
			$res[] =
			[
				"name" => $lang->name,
				"locale" => $lang->locale,
				"code" => $lang->slug,
				"slug" => $lang->slug,
				"item" => $lang,
			];
		}
	}
	return $res;
}

function wp_get_default_lang()
{
	$default_lang = "ru";
	if ( defined( "POLYLANG_VERSION" ) )
	{
		$default_lang = PLL()->options['default_lang'];
	}
	$default_lang = apply_filters("elberos_default_lang", $default_lang);
	return $default_lang;
}

function wp_hide_default_lang()
{
	$res = false;
	if ( defined( "POLYLANG_VERSION" ) )
	{
		$res = PLL()->options['hide_default'];
	}
	return $res;
}

function wp_get_alias($text, $alias = "")
{
	$default_lang = \Elberos\wp_get_default_lang();
	$text_en = isset($text["en"]) ? $text["en"] : "";
	$text_ru = isset($text["ru"]) ? $text["ru"] : "";
	$text_default = isset($text[$default_lang]) ? $text[$default_lang] : "";
	if ($alias == "")
	{
		if ($text_en) $alias = sanitize_title($text_en);
		else if ($text_ru) $alias = sanitize_title($text_ru);
		else if ($text_default) $alias = sanitize_title($text_default);
	}
	return $alias;
}

function is_langs()
{
	$langs = \Elberos\wp_langs();
	return $langs != null && count($langs) > 0;
}

/**
 * Encode base64 url
 */
function base64_encode_url($s)
{
	$s = base64_encode($s);
	$s = str_replace('+', '-', $s);
	$s = str_replace('/', '_', $s);
	$s = str_replace('=', '', $s);
	return $s;
}


/**
 * Decode base64 url
 */
function base64_decode_url($s)
{
	$c = 4 - strlen($s) % 4;
	if ($c < 4 && $c > 0) $s .= str_repeat('=', $c);
	$s = str_replace('-', '+', $s);
	$s = str_replace('_', '/', $s);
	return base64_decode($s);
}


/**
 * Returns ip
 */
function get_client_ip()
{
	return $_SERVER['REMOTE_ADDR'];
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		return $_SERVER['REMOTE_ADDR'];
	}
	return "0";
}


/**
 * Update meta array
 */
function update_post_meta_arr($post_id, $meta_key, $arr, $item_key_id = "")
{
	if (gettype($arr) != "array") return;
	
	global $wpdb;
	
	$table = $wpdb->prefix . "postmeta";
	$sql = $wpdb->prepare
	(
		"SELECT t.* FROM {$table} as t
		WHERE t.post_id = %d and t.meta_key = %s", $post_id, $meta_key
	);
	$items = $wpdb->get_results($sql, ARRAY_A);
	
	// Extract json from meta_value
	if ($item_key_id != "")
	{
		$items = array_map
		(
			function($item)
			{
				$item['meta_value'] = @unserialize($item['meta_value']);
				return $item;
			},
			$items
		);
	}
	
	// Add meta_value to arr
	$arr = array_map
	(
		function($item)
		{
			return [ 'meta_value' => $item ];
		},
		$arr
	);
	
	$find_item = function($items, $value, $item_key_id)
	{
		foreach ($items as $c)
		{
			if ($item_key_id == "")
			{
				if ($c['meta_value'] == $value['meta_value'])
				{
					return $c;
				}
			}
			else
			{
				if (isset($c['meta_value'][$item_key_id]) && isset($value['meta_value'][$item_key_id]))
				{
					if ($c['meta_value'][$item_key_id] == $value['meta_value'][$item_key_id])
					{
						return $c;
					}
				}
			}
		}
		return null;
	};
	
	/* Add */
	foreach ($arr as $arr_item)
	{
		$find = $find_item($items, $arr_item, $item_key_id);
		if (!$find)
		{
			$meta_value_text = $arr_item['meta_value'];
			if (gettype($meta_value_text) == "array")
			{
				$meta_value_text = serialize($meta_value_text);
			}
			$wpdb->insert($table, ['post_id' => $post_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value_text]);
		}
	}
	
	/* Delete */
	foreach ($items as $c)
	{
		$find = $find_item($arr, $c, $item_key_id);
		if (!$find)
		{
			$wpdb->delete($table, ['meta_id' => $c['meta_id']]);
		}
	}
	
}

/**
 * Send curl
 */
function curl($url, $post = null, $headers = null, $params = null)
{
	$post = null;
	$headers = null;
	$curl_version = curl_version();
	$curl_version_text = ($curl_version != false && isset($curl_version['version'])) ? $curl_version['version'] : "0";
	$user_agent = "curl-client/" . $curl_version_text;
	
	if ($params != null)
	{
		if (isset($params['post'])) $post = $params['post'];
		if (isset($params['headers'])) $post = $params['headers'];
		if (isset($params['user_agent'])) $post = $params['user_agent'];
	}
	
	# Сохраняем дескриптор сеанса cURL
	$curl = curl_init();
	
	# Устанавливаем необходимые опции для сеанса cURL
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file); # PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_file); # PHP>5.3.6 dirname(__FILE__) -> __DIR__
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	
	if ($post !== null)
	{
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
	}
	else
	{
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	}
	
	if ($headers != null && count($headers) > 0)
	{
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}
	
	# Инициируем запрос к API и сохраняем ответ в переменную
	$out = curl_exec($curl);
	
	# Получим HTTP-код ответа сервера
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
	# Завершаем сеанс cURL
	curl_close($curl);
	
	return [$code, $out];
}