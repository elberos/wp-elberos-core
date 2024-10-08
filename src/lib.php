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


/**
 * Return site option
 */
function get_option($key, $value = "")
{
	if ( ! is_multisite() )
	{
		return \get_option($key, $value);
	}
	return \get_network_option(1, $key, $value);
}



/**
 * Save site option
 */
function save_option($key, $value)
{
	if ( ! is_multisite() )
	{
		if (!add_option($key, $value, "", "no"))
		{
			\update_option($key, $value);
		}
	}
	else
	{
		if (!add_network_option(1, $key, $value, "", "no"))
		{
			\update_network_option(1, $key, $value);
		}
	}
}


/**
 * Get url parameters
 */
function url_get($key, $value = "")
{
	return isset($_GET[$key]) ? $_GET[$key] : $value;
}


/**
 * Add get parametr
 */
function url_get_add($url, $key, $value = "")
{
	$url_parts = parse_url($url);
	$get_args = [];
	
	if (isset($url_parts['query']))
	{
		parse_str($url_parts['query'], $get_args);
	}
	
	$get_args[$key] = $value;
	$url_parts['query'] = http_build_query($get_args);
	
	$new_url = "";
	if (isset($url_parts["scheme"])) $new_url .= $url_parts["scheme"] . "://";
	if (isset($url_parts["host"])) $new_url .= $url_parts["host"];
	if (isset($url_parts["path"])) $new_url .= $url_parts["path"];
	if (isset($url_parts["query"])) $new_url .= "?" . $url_parts["query"];
	
	return $new_url;
}


/**
 * Returns site url
 */
function get_url($url)
{
	return \site_url($url);
}



/**
 * Returns site name
 */
function get_site_name()
{
	if ( ! is_multisite() )
	{
		return get_option( 'blogname' );
	}
	return get_blog_option( 1, 'blogname' );
}



/**
 * Is get cheched
 */
function is_get_checked($key, $value, $default = false)
{
	$get_value = isset($_GET[$key]) ? $_GET[$key] : "";
	if ($get_value === $value) return "checked='checked'";
	if ($get_value == "" and $default) return "checked='checked'";
	return "";
}



/**
 * Is get selected
 */
function is_get_selected($key, $value, $default = false)
{
	$get_value = isset($_GET[$key]) ? $_GET[$key] : "";
	if ($get_value === $value) return "selected='selected'";
	if ($get_value == "" and $default) return "selected='selected'";
	return "";
}



/**
 * Is value cheched
 */
function is_value_checked($key, $value, $default = false)
{
	if ($key === $value) return "checked='checked'";
	if ($key == "" and $default) return "checked='checked'";
	return "";
}



/**
 * Is value selected
 */
function is_value_selected($key, $value, $default = false)
{
	if ($key === $value) return "selected='selected'";
	if ($key == "" and $default) return "selected='selected'";
	return "";
}



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
 * Trim UTF-8 string
 */
function mb_trim($name)
{
	if ($name == null) return "";
	$name = preg_replace('/^[\x00-\x1F\x7F\s]+/u', '', $name);
	$name = preg_replace('/[\x00-\x1F\x7F\s]+$/u', '', $name); 
	return $name;
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
function formatMoney($value, $decimals=2)
{
	return number_format($value, $decimals, ".", " ");
}



/**
 * Convert to number
 */ 
function to_number($value)
{
	return preg_replace('/^[0-9]/', '', $value);
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



/**
 * Find item by field_name
 *
 * @param array $arr
 * @param string $field_name
 * @param string $value
 * @return row
 */
function find_key($arr, $field_name, $value)
{
	if (gettype($arr) == 'array')
	{
		foreach ($arr as $key => $row)
		{
			if (!isset($row[$field_name]))
				continue;
			if ($row[$field_name] == $value)
				return $key;
		}
	}
	return -1;
}



/**
 * Find item by field_name
 *
 * @param array $arr
 * @param string $field_name
 * @param string $value
 * @return row
 */
function find_item($arr, $field_name, $value)
{
	if (gettype($arr) == 'array')
	{
		foreach ($arr as $row){
			if (!isset($row[$field_name]))
				continue;
			if ($row[$field_name] == $value)
				return $row;
		}
	}
	return null;
}



/**
 * Find items by field_name
 *
 * @param array $arr
 * @param string $field_name
 * @param string $value
 * @return row
 */
function find_items($arr, $field_name, $value)
{
	$row = [];
	foreach ($arr as $val){
		if (!isset($val[$field_name]))
			continue;
		if ($val[$field_name] == $value)
			$row[] = $val;
	}
	return $row;
}



/**
 * Get $arr[$key] if exists or return default value
 *
 * @param array $arr 
 * @param string|array $key 
 * @param var $default Default value
 * @return var
 */
function attr($arr, $key_arr, $default = null){
	if ($arr == null) return $default;
	
	if (gettype($key_arr) != 'array')
		$key_arr = explode(".", $key_arr);
	
	$sz = count($key_arr);
	$res = &$arr;
	
	for ($i=0; $i<$sz; $i++){
		
		$key = $key_arr[$i];
		if (!isset($res[$key]))
			return $default;
		
		$res = &$res[$key];
	}
	
	return $res;
}



/**
 * Set $arr[$key] if exists or return default value
 *
 * @param array $arr 
 * @param string|array $key 
 * @param var $default Default value
 * @return var
 */
function push(&$arr, $key_arr, $val)
{
	if (gettype($key_arr) != 'array')
		$key_arr = explode(".", $key_arr);
	
	$sz = count($key_arr);
	$res = &$arr;
	
	for ($i=0; $i<$sz; $i++)
	{
		
		$key = $key_arr[$i];
		if ($i == $sz - 1)
		{
			$res[$key] = $val;
			break;
		}
		
		if (!isset($res[$key]))
			$res[$key] = [];
		
		$res = &$res[$key];
	}
	
	return $res;
}



/**
 * Set $arr[$key] if exists or return default value
 *
 * @param array $arr 
 * @param string|array $key 
 * @param var $default Default value
 * @return var
 */
function add(&$arr, $key_arr, $val)
{
	if (gettype($key_arr) != 'array')
		$key_arr = explode(".", $key_arr);
	
	$sz = count($key_arr);
	$res = &$arr;
	
	for ($i=0; $i<$sz; $i++)
	{
		$key = $key_arr[$i];
		if ($i == $sz - 1)
		{
			$res[$key][] = $val;
			break;
		}
		
		if (!isset($res[$key]))
			$res[$key] = [];
		
		$res = &$res[$key];
	}
	
	return $res;
}



/**
 * Append new value to array
 */
function append($arr, $val)
{
	$arr[] = $val;
	return $arr;
}



/**
 * Returns index of
 */
function index_of($arr, $value)
{
	$index = array_search($value, $arr);
	if ($index === false) return -1;
	return $index;
}



/**
 * Contains fields
 *
 * @param array $item
 * @param array $fields
 * @return bool
 */
function containsFields($item, $fields)
{
	if (!is_array($fields)) return false;
	foreach ($fields as $key => $value)
	{
		if (!isset($item[$key])) return false;
		if ($item[$key] != $value) return false;
	}
	return true;
}



/**
 * Equal arr
 *
 * @param array $item
 * @param array $fields
 * @return bool
 */
function equalArr($item1, $item2)
{
	return containsFields($item1, $item2) && containsFields($item2, $item1);
}



/**
 * Split
 */
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

function dbtime($time = -1)
{
	if ($time == -1) $time = time();
	return gmdate('Y-m-d H:i:s', $time);
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
	$timezone_string = \get_option( 'timezone_string' );
	if (!empty($timezone_string)) return $timezone_string;
	$offset = (double)\get_option( 'gmt_offset' );
	$hours = (int)$offset;
	$minutes = abs(($offset - (int)$offset) * 60);
	$gmt_offset = sprintf('%+03d:%02d', $hours, $minutes);
	return "GMT" . $gmt_offset;
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
		if (defined("WP_TZ")) $tz = WP_TZ;
		else
		{
			$tz = get_wp_timezone();
			if (!$tz) $tz = "UTC";
		}
		$tz = new \DateTimeZone( $tz );
		$dt->setTimezone( $tz );
		return $dt->format($format);
	}
	return "";
}

function gm_to_datetime($date, $tz = 'UTC', $format = 'Y-m-d H:i:s')
{
	$dt = \Elberos\create_date_from_string($date, 'Y-m-d H:i:s', 'UTC');
	if ($dt)
	{
		$dt->setTimezone( new \DateTimeZone( $tz ) );
		return $dt->format($format);
	}
	return "";
}

function wp_langs()
{
	$res = [];
	if ( defined( 'POLYLANG_VERSION' ) && function_exists("\\PLL") )
	{
		$links = \PLL()->links;
		if ($links)
		{
			$langs = $links->model->get_languages_list();
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
	}
	return $res;
}

function wp_get_default_lang()
{
	$default_lang = "ru";
	if ( defined( "POLYLANG_VERSION" ) && function_exists("\\PLL") )
	{
		$default_lang = \PLL()->options['default_lang'];
	}
	$default_lang = apply_filters("elberos_default_lang", $default_lang);
	return $default_lang;
}

function wp_hide_default_lang()
{
	$res = false;
	if ( defined( "POLYLANG_VERSION" ) && function_exists("\\PLL") )
	{
		$res = \PLL()->options['hide_default'];
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
	if (!empty($_SERVER['HTTP_X_REAL_IP']))
	{
		return $_SERVER['HTTP_X_REAL_IP'];
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
 * CIDR Match
 */
function cidr_match ($IP, $CIDR)
{
	list ($net, $mask) = explode ("/", $CIDR);

	$ip_net = ip2long ($net);
	$ip_mask = ~((1 << (32 - $mask)) - 1);

	$ip_ip = ip2long ($IP);

	$ip_ip_net = $ip_ip & $ip_mask;

	return ($ip_ip_net == $ip_net);
}


/**
 * Create api
 */
function create_nonce()
{
	$ip = get_client_ip();
	return md5($ip . NONCE_KEY);
}


/**
 * Check api 
 */
function check_nonce($text1)
{
	$ip = get_client_ip();
	$text2 = md5($ip . NONCE_KEY);
	return $text1 == $text2;
}


/**
 * Check wp nonce
 */
function check_wp_nonce($nonce_action)
{
	/* Check nonce */
	$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : false;
	if ($nonce == false)
	{
		return false;
	}
	if (!wp_verify_nonce($nonce, $nonce_action))
	{
		return false;
	}
	return true;
}


/**
 * Returns image url
 */
function get_image_url($post_id, $size = 'thumbnail')
{
	$img = wp_get_attachment_image_src($post_id, $size);
	//var_dump($post_id);
	//var_dump($img);
	if ($img)
	{
		$post = get_post( $post_id );
		$url = $img[0] . "?_=" . strtotime($post->post_modified_gmt);
		return $url;
	}
	return "";
}


/**
 * Returns images urls
 */
function get_images_url($images_id)
{
	global $wpdb;
	
	/* Get uploads dir */
	$uploads = wp_get_upload_dir();
	$baseurl = $uploads["baseurl"];
	
	/* Get meta */
	$wp_posts = $wpdb->prefix . "posts";
	$wp_postmeta = $wpdb->prefix . "postmeta";
	$sql = $wpdb->prepare
	(
		"SELECT postmeta.meta_value, post.ID as id, post.post_modified_gmt " .
		"FROM " . $wp_postmeta . " as postmeta " .
		"INNER JOIN " . $wp_posts . " as post on (post.ID = postmeta.post_id) " .
		"WHERE postmeta.meta_key='_wp_attachment_metadata' AND " .
		"postmeta.post_id in (" . implode(",", array_fill(0, count($images_id), "%d")) . ")",
		$images_id
	);
	$posts_meta = $wpdb->get_results($sql, ARRAY_A);
	
	/* Get result */
	$result = [];
	foreach ($posts_meta as $item)
	{
		$item["meta_value"] = @unserialize($item["meta_value"]);
		if ($item["meta_value"])
		{
			$file = $item["meta_value"]["file"];
			$file_dir = dirname($file);
			$item["meta_value"]["url"] =
				$baseurl . "/" . $item["meta_value"]["file"] .
				"?_=" . strtotime($item["post_modified_gmt"])
			;
			
			foreach ($item["meta_value"]["sizes"] as $key => $size)
			{
				$item["meta_value"]["sizes"][$key]["url"] =
					$baseurl . "/" . $file_dir . "/" .
					$size["file"] . "?_=" . strtotime($item["post_modified_gmt"])
				;
			}
		}
		
		$result[$item["id"]] = $item;
	}
	
	return $result;
}


/**
 * Send curl
 */
function curl($url, $params = null)
{
	$post = null;
	$headers = null;
	$curl_version = curl_version();
	$curl_version_text = ($curl_version != false && isset($curl_version['version'])) ?
		$curl_version['version'] : "0";
	$user_agent = "curl-client/" . $curl_version_text;
	$cookie_file = null;
	
	if ($params != null)
	{
		if (isset($params['post'])) $post = $params['post'];
		if (isset($params['headers'])) $headers = $params['headers'];
		if (isset($params['user_agent'])) $user_agent = $params['user_agent'];
		if (isset($params['cookie_file'])) $cookie_file = $params['cookie_file'];
	}
	
	# Сохраняем дескриптор сеанса cURL
	$curl = curl_init();
	
	# Устанавливаем необходимые опции для сеанса cURL
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	if ($cookie_file)
	{
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file);
	}
	
	if (isset($params['verify_ssl']) && $params['verify_ssl'] == false)
	{
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	}
	
	if (isset($params['http_auth']))
	{
		$username = isset($params['http_auth']['username']) ? $params['http_auth']['username'] : '';
		$password = isset($params['http_auth']['password']) ? $params['http_auth']['password'] : '';
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
	}
	
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
	
	$curl_errno = 0;
	$curl_errstr = '';
	if ($code == 0)
	{
		$curl_errno = curl_errno($curl);
		$curl_errstr = curl_error($curl);
	}
	
	# Завершаем сеанс cURL
	curl_close($curl);
	
	return [
		"code" => $code,
		"out" => $out,
		"errno" => $curl_errno,
		"errstr" => $curl_errstr,
	];
}


global $elberos_twig;
$elberos_twig = null;


/**
 * Create twig
 */
function create_twig()
{
	global $elberos_twig;
	
	/* Restore twig */
	if ($elberos_twig != null)
	{
		return $elberos_twig;
	}
	
	$twig_opt = array
	(
		'autoescape'=>true,
		'charset'=>'utf-8',
		'optimizations'=>-1,
	);
	
	/* Twig cache */
	$twig_cache = true;
	if (defined("TWIG_CACHE"))
	{
		$twig_cache = TWIG_CACHE;
	}
	
	/* Enable cache */
	if ($twig_cache)
	{
		$twig_opt['cache'] = ABSPATH . 'wp-content/cache/twig';
		$twig_opt['auto_reload'] = true;
	}
	
	/* Create twig loader */
	$twig_loader = new \Twig\Loader\FilesystemLoader();
	$twig_loader->addPath(get_template_directory() . '/templates');
	do_action('elberos_twig_loader', [$twig_loader]);
	
	/* Create twig instance */
	$twig = new \Twig\Environment
	(
		$twig_loader,
		$twig_opt
	);
	
	/* Set strategy */
	$twig->getExtension(\Twig\Extension\EscaperExtension::class)->setDefaultStrategy('html');
	
	/* Do action */
	do_action('elberos_twig', [$twig]);
	
	/* Save twig */
	$elberos_twig = $twig;
	
	return $twig;
}



/**
 * Twig render
 */
function twig_render($twig, $template, $context = [])
{
	/* set this to context */
	$context["this"] = $context;
	
	if (gettype($template) == 'array')
	{
		foreach ($template as $t)
		{
			try
			{
				$res = $twig->render($t, $context);
				return $res;
			}
			catch (\Twig\Error\LoaderError $err)
			{
			}
		}
	}
	else
	{
		return $twig->render($template, $context);
	}
	return "";
}



/**
 * Add email
 */
function add_email($plan, $email_to, $title, $message, $params = [])
{
	\Elberos\MailSender::addMail($plan, $email_to, $title, $message, $params);
}



/**
 * Send email
 */
function send_email($plan, $email_to, $template, $context, $params = [])
{
	$twig = \Elberos\create_twig();
	
	if (!isset($context['site_name'])) $context['site_name'] = \Elberos\get_site_name();
	$title = isset($context['title']) ? $context['title'] : '';
	$message = \Elberos\twig_render($twig, $template, $context);
	
	\Elberos\MailSender::addMail($plan, $email_to, $title, $message, $params);
}


/**
 * Is alfa num
 */
function is_alfa_num($ch)
{
	if ($ch == " ") return false;
	if ($ch == "_") return true;
	$code = mb_ord($ch);
	if ($code >= 97 and $code <= 122) return true;
	if ($code >= 65 and $code <= 90) return true;
	if ($code >= 48 and $code <= 57) return true;
	return false;
}


/**
 * Get arguments
 */
function wpdb_query_args($sql, $args, &$sql_arr)
{
	while (true)
	{
		$pos = 0;
		$sz = mb_strlen($sql);
		
		while ($pos < $sz and mb_substr($sql, $pos, 1) != ":") $pos++;
		if ($pos >= $sz)
		{
			break;
		}
		
		$pos2 = $pos + 1;
		while ($pos2 < $sz and is_alfa_num(mb_substr($sql, $pos2, 1))) $pos2++;
		
		$row_name = substr($sql, $pos + 1, $pos2 - $pos - 1);
		$sql = substr($sql, 0, $pos) . "%s" . substr($sql, $pos2);
		
		$sql_arr[] = isset($args[$row_name]) ? $args[$row_name] : "";
	}
	return $sql;
}



/**
 * Prepare query
 */
function wpdb_prepare($sql, $args)
{
	global $wpdb;
	$sql_arr = [];
	$sql = wpdb_query_args($sql, $args, $sql_arr);
	if (count($sql_arr) > 0)
	{
		$sql = $wpdb->prepare($sql, $sql_arr);
	}
	
	/* Table prefix */
	$sql = str_replace("\${prefix}", $wpdb->prefix, $sql);
	$sql = str_replace("\${base_prefix}", $wpdb->base_prefix, $sql);
	
	return $sql;
}



/**
 * wpdb Query
 */
function wpdb_query($params)
{
	global $wpdb;
	
	$sql_arr = [];
	$distinct = (isset($params["distinct"]) && $params["distinct"]) ? "DISTINCT" : "";
	$table_name = isset($params["table_name"]) ? $params["table_name"] : "";
	$fields = isset($params["fields"]) ? $params["fields"] : "t.*";
	$join = isset($params["join"]) ? $params["join"] : "";
	$per_page = isset($params["per_page"]) ? $params["per_page"] : 10;
	$order_by = isset($params["order_by"]) ? $params["order_by"] : "id desc";
	$log = isset($params["log"]) ? $params["log"] : false;
	
	$page = 0;
	if (isset($params["page"])) $page = $params["page"];
	
	$args = isset($params["args"]) ? $params["args"] : [];
	$where = isset($params["where"]) ? $params["where"] : "";
	if ($where != "") $where = "where " . $where;
	
	/* Table prefix */
	$table_name = str_replace("\${prefix}", $wpdb->prefix, $table_name);
	$table_name = str_replace("\${base_prefix}", $wpdb->base_prefix, $table_name);
		
	/* Order by */
	if ($order_by) $order_by = "ORDER BY " . $order_by;
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS ${distinct} ${fields} " .
		"FROM ${table_name} as t ${join} ${where} ${order_by}";
	$sql = wpdb_query_args($sql, $args, $sql_arr);
	
	$limit = "";
	if ($per_page > 0)
	{
		$sql_arr[] = $per_page;
		$sql_arr[] = $page * $per_page;
		$sql .= " LIMIT %d OFFSET %d";
	}
	
	/* Query */
	$sql = $wpdb->prepare($sql, $sql_arr);
	
	if ($log)
	{
		echo $sql . "\n";
	}
	
	$items = $wpdb->get_results($sql, ARRAY_A);
	$count = $wpdb->get_var('SELECT FOUND_ROWS()');
	if ($per_page > 0) $pages = ceil($count / $per_page);
	else $pages = 0;
	
	return [$items, $count, $pages, $page];
}



/**
 * wpdb Get by id
 */
function wpdb_get_by_id($table_name, $id)
{
	global $wpdb;
	$sql = \Elberos\wpdb_prepare
	(
		"select * from $table_name " .
		"where id = :id limit 1",
		[
			'id' => $id,
		]
	);
	$item = $wpdb->get_row($sql, ARRAY_A);
	return $item;
}



/**
 * Insert
 **/
function wpdb_insert($table_name, $insert)
{
}



/**
 * Update
 **/
function wpdb_update($table_name, $update, $where)
{
	global $wpdb;
	
	$args = [];
	
	/* Build update */
	$update_arr = [];
	$update_keys = array_keys($update);
	foreach ($update_keys as $key)
	{
		$update_arr[] = "`" . $key . "` = :_update_" . $key;
		$args["_update_" . $key] = $update[$key];
	}
	$update_str = implode(", ", $update_arr);
	
	/* Build where */
	$where_arr = [];
	$where_keys = array_keys($where);
	foreach ($where_keys as $key)
	{
		$where_arr[] = "`" . $key . "` = :_where_" . $key;
		$args["_where_" . $key] = $where[$key];
	}
	$where_str = implode(" and ", $where_arr);
	
	$sql = \Elberos\wpdb_prepare
	(
		"update $table_name set $update_str where $where_str",
		$args
	);
	$wpdb->query($sql);
}



/**
 * Insert or update
 **/
function wpdb_insert_or_update($table_name, $search, $insert, $update = null)
{
	global $wpdb;
	
	if ($update == null) $update = $insert;
	
	$keys = array_keys($search);
	$where = array_map
	(
		function ($item)
		{
			return "`" . $item . "` = :" . $item;
		},
		$keys
	);
	$where_str = implode(" and ", $where);
	
	/* Find item */
	$sql = \Elberos\wpdb_prepare
	(
		"select * from $table_name where $where_str limit 1",
		$search
	);
	$item = $wpdb->get_row($sql, ARRAY_A);
	$item_id = 0;
	
	/* Insert item */
	if ($item == null)
	{
		$wpdb->insert($table_name, $insert);
		$item_id = $wpdb->insert_id;
	}
	
	/* Update item */
	else
	{
		$wpdb->update
		(
			$table_name,
			$update,
			[
				"id" => $item["id"],
			]
		);
		$item_id = $item["id"];
		/*
		$keys = array_keys($update);
		$update_arr = array_map
		(
			function ($item)
			{
				return "`" . $item . "` = :" . $item;
			},
			$keys
		);
		$update_str = implode(", ", $update_arr);
		$update["id"] = $item["id"];
		
		$sql = \Elberos\wpdb_prepare
		(
			"update $table_name set $update_str where id = :id",
			$update
		);
		$wpdb->query($sql);
		$item_id = $item["id"];*/
	}
	
	/* Find item by id */
	$sql = \Elberos\wpdb_prepare
	(
		"select * from $table_name where id=:id limit 1",
		[
			"id" => $item_id,
		]
	);
	$item = $wpdb->get_row($sql, ARRAY_A);
	
	return $item;
}



/**
 * Upload bits
 */
function wp_upload_bits_ext($name, $sha1, $bits, $time = null)
{
	if ( empty( $name ) )
	{
		return array( 'error' => __( 'Empty filename' ) );
	}

	$wp_filetype = wp_check_filetype( $name );
	if ( ! $wp_filetype['ext'] && ! current_user_can( 'unfiltered_upload' ) )
	{
		return array( 'error' => __( 'Sorry, you are not allowed to upload this file type.' ) );
	}

	$upload = wp_upload_dir( $time );

	if ( false !== $upload['error'] )
	{
		return $upload;
	}
	
	$upload_bits_error = apply_filters
	(
		'wp_upload_bits',
		array(
			'name' => $name,
			'bits' => $bits,
			'time' => $time,
		)
	);
	if ( ! is_array( $upload_bits_error ) )
	{
		$upload['error'] = $upload_bits_error;
		return $upload;
	}

	$filename = wp_unique_filename( $upload['path'], $name );
	
	/* File name to subfolder */
	$sha1_2 = substr($sha1, 0, 2);
	if (strlen($sha1_2) < 2) $sha1_2 = "aa";
	$filename = $sha1_2 . "/" . $filename;
	
	/* Make dir */
	$new_file = $upload['path'] . "/$filename";
	if ( ! wp_mkdir_p( dirname( $new_file ) ) )
	{
		if ( 0 === strpos( $upload['basedir'], ABSPATH ) )
		{
			$error_path = str_replace( ABSPATH, '', $upload['basedir'] ) . $upload['subdir'];
		}
		else
		{
			$error_path = wp_basename( $upload['basedir'] ) . $upload['subdir'];
		}

		$message = sprintf
		(
			/* translators: %s: Directory path. */
			__( 'Unable to create directory %s. Is its parent directory writable by the server?' ),
			$error_path
		);
		return array( 'error' => $message );
	}

	$ifp = @fopen( $new_file, 'wb' );
	if ( ! $ifp )
	{
		return array
		(
			/* translators: %s: File name. */
			'error' => sprintf( __( 'Could not write file %s' ), $new_file ),
		);
	}

	fwrite( $ifp, $bits );
	fclose( $ifp );
	clearstatcache();

	// Set correct file permissions.
	$stat  = @stat( dirname( $new_file ) );
	$perms = $stat['mode'] & 0007777;
	$perms = $perms & 0000666;
	chmod( $new_file, $perms );
	clearstatcache();

	// Compute the URL.
	$url = $upload['url'] . "/$filename";

	if ( is_multisite() )
	{
		clean_dirsize_cache( $new_file );
	}

	/** This filter is documented in wp-admin/includes/file.php */
	return apply_filters
	(
		'wp_handle_upload',
		array(
			'file'  => $new_file,
			'url'   => $url,
			'type'  => $wp_filetype['type'],
			'error' => false,
		),
		'sideload'
	);
}

 

/**
 * Upload file
 */
function upload_file($image_path_full, $params = [])
{
	global $wpdb;
	
	$is_file = is_file($image_path_full);
	if (!$is_file)
	{
		return -1;
	}
	
	/* File title */
	$new_file_name = basename($image_path_full);
	$post_name = $new_file_name;
	if (isset($params['title']))
	{
		$post_name = $params['title'];
	}
	
	/* Get sha1 */
	$sha1 = isset($params["sha1"]) ? $params["sha1"] : "";
	if (!$sha1)
	{
		$sha1 = sha1_file($image_path_full);
	}
	
	/* Find image by sha1 */
	$sql = \Elberos\wpdb_prepare
	(
		"select * from " . $wpdb->base_prefix . "postmeta " .
		"where meta_key='file_sha1' and meta_value=:meta_value limit 1",
		[
			"meta_value" => $sha1,
		]
	);
	$row = $wpdb->get_row($sql, ARRAY_A);
	if ($row)
	{
		if (isset($params['title']))
		{
			$post =
			[
				'ID' => $row["post_id"],
				'post_title' => $post_name,
				'post_content' => $post_name,
				'post_name' => $post_name,
			];
			wp_update_post($post);
		}
		return $row["post_id"];
	}
	
	/* Upload file */
	$file_content = file_get_contents($image_path_full);
	$wp_filetype = @wp_check_filetype($new_file_name, null );
	$upload = @wp_upload_bits_ext( $new_file_name, $sha1, $file_content );
	
	if ( !$upload['error'] )
	{
		$file_url = $upload['url'];
		
		$attachment = array
		(
			'post_date' => date('Y-m-d H:i:s'),
			'post_date_gmt' => gmdate('Y-m-d H:i:s'),
			'post_title' => $post_name,
			'post_status' => 'inherit',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_name' => $post_name,
			'post_modified' => date('Y-m-d H:i:s'),
			'post_modified_gmt' => gmdate('Y-m-d H:i:s'),
			'post_type' => 'attachment',
			'guid' => $file_url,
			'post_mime_type' => $wp_filetype['type'],
			'post_excerpt' => '',
			'post_content' => $post_name
		);
		
		$photo_id = @wp_insert_attachment( $attachment, $upload['file'] );
		@update_post_meta( $photo_id, 'file_sha1', $sha1 );
		
		require_once ABSPATH . 'wp-admin/includes/image.php';
		
		/* Update metadata */
		@update_attached_file( $photo_id, $upload['file'] );
		@wp_update_attachment_metadata
		(
			$photo_id, @wp_generate_attachment_metadata( $photo_id, $upload['file'] )
		);
		
		return $photo_id;
	}

	return -1;
}


/**
 * Update term id
 */
function update_term_id($post_id, $term_id)
{
	global $wpdb;
	
	if ($post_id <= 0) return;
	if ($term_id <= 0) return;
	
	/* Insert term id */
	$sql = \Elberos\wpdb_prepare
	(
		"select * from " . $wpdb->prefix . "term_relationships " .
		"where object_id=:object_id and term_taxonomy_id=:term_taxonomy_id limit 1",
		[
			"object_id" => $post_id,
			"term_taxonomy_id" => $term_id,
		]
	);
	$item = $wpdb->get_row($sql, ARRAY_A);
	if (!$item)
	{
		$wpdb->insert
		(
			$wpdb->prefix . "term_relationships",
			[
				"object_id" => $post_id,
				"term_taxonomy_id" => $term_id,
				"term_order" => 0,
			]
		);
	}
}


/**
 * Check captch
 */
function captcha_validation($value)
{
	$jwt_text = isset($_COOKIE['elberos_captcha']) ? $_COOKIE['elberos_captcha'] : "";
	
	$jwt_data = decode_jwt($jwt_text, NONCE_KEY);
	if ($jwt_data == null)
	{
		return false;
	}
	
	$cookie_text1 = isset($jwt_data["d"]) ? $jwt_data["d"] : "";
	$cookie_text2 = md5($value . NONCE_SALT);
	
	$jwt_time = (int)(isset($jwt_data["t"]) ? $jwt_data["t"] : 0);
	if ($jwt_time + 60*60 < time())
	{
		return false;
	}
	
	return $cookie_text1 == $cookie_text2;
}



/**
 * Generate captcha
 */
function create_c4wp()
{
	require_once __DIR__ . "/class-c4wp-create-image-captcha.php";
	$captcha = new \Elberos_C4WP_Create_Image_Captcha
	([
		"c4wp_key" => "elberos_captcha",
		"c4wp_image_width" => 200,
		"c4wp_image_height" => 60,
		"c4wp_fonts" => dirname(__DIR__) . "/assets/fonts/Roboto-Regular.ttf",
		"c4wp_char_on_image" => 6,
		"c4wp_possible_letters" => "qwertyuiopasdfghjklzxcvbnm",
		"c4wp_background_color" => "52e9eb",
		"c4wp_noice_color" => "a5524a",
		"c4wp_text_color" => "000000",
		"c4wp_random_dots" => 50,
		"c4wp_random_lines" => 4,
	]);
	return $captcha;
}



/**
 * Generate captcha
 */
function generate_captcha()
{
	$captcha = create_c4wp();
	$captcha->createCaptcha();
}



/**
 * Flush captcha
 */
function flush_captcha()
{
	$captcha = create_c4wp();
	$captcha->flushCaptcha();
}



/**
 * Create JWT
 */
function create_jwt($data, $jwt_key)
{
	$data_json = json_encode($data);
	$data_b64 = \Elberos\base64_encode_url($data_json);
	$head_b64 = \Elberos\base64_encode_url(json_encode(['alg'=>'HS512','typ'=>'JWT']));
	
	/* Sign */
	$text = $head_b64 . '.' . $data_b64;
	$out = hash_hmac('SHA512', $text, $jwt_key, true);
	$out = \Elberos\base64_encode_url($out);
	
	return $text . '.' . $out;
}



/**
 * Decode JWT
 */
function decode_jwt($text, $jwt_key)
{
	$arr = explode(".", $text);
	if (count($arr) != 3) return null;
	
	$head_b64 = $arr[0];
	$data_b64 = $arr[1];
	$sign_b64 = $arr[2];
	$data_json = @\Elberos\base64_decode_url($data_b64);
	$data = @json_decode($data_json, true);
	if ($data == null) return null;
	
	/* Validate sign */
	$text = $head_b64 . '.' . $data_b64;
	$hash = hash_hmac('SHA512', $text, $jwt_key, true);
	$hash = \Elberos\base64_encode_url($hash);
	$verify = hash_equals($sign_b64, $hash);
	if (!$verify) return null;
	
	return $data;
}


/**
 * Generate uuid
 */
function uid()
{
	$bytes = bin2hex(random_bytes(16));
	return substr($bytes, 0, 8) . "-" .
		substr($bytes, 8, 4) . "-" .
		substr($bytes, 12, 4) . "-" .
		substr($bytes, 16, 4) . "-" .
		substr($bytes, 20);
}


/**
 * Returns toc
 */
function get_toc($content)
{
	$res = [
		"headers" => [],
		"content" => $content,
	];
	
	if ( preg_match_all( '/(<h([1-6]{1})[^>]*>)(.*)<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER ) )
	{
		foreach ($matches as $arr)
		{
			$h_name = $arr[1];
			$h_title = trim($arr[3]);
			$h_id = sanitize_title($h_title);
			
			if ($h_name == "<h1>") $h_name = "h1";
			else if ($h_name == "<h2>") $h_name = "h2";
			else if ($h_name == "<h3>") $h_name = "h3";
			else if ($h_name == "<h4>") $h_name = "h4";
			else if ($h_name == "<h5>") $h_name = "h5";
			else if ($h_name == "<h6>") $h_name = "h6";
			
			if (in_array($h_name, ["h1","h2","h3","h4","h5","h6"]))
			{
				$replace = "<" . $h_name . " id=\"" . esc_attr($h_id) . "\">" .
					$h_title .
				"</" . $h_name . ">";
				
				$res["headers"][] =
				[
					"find" => $arr[0],
					"replace" => $replace,
					"name" => $h_name,
					"title" => $h_title,
					"id" => $h_id,
				];
			}
		}
	}
	
	foreach ($res["headers"] as $h)
	{
		$content = mb_eregi_replace( $h["find"], $h["replace"], $content );
	}
	$res["content"] = $content;
	
	return $res;
}


/**
 * Returns posts
 */
function get_posts($args)
{
	$query = new \WP_Query;
	$items = $query->query($args);
	$paged = $query->query_vars["paged"];
	if ($paged <= 0) $paged = 1;
	return
	[
		"query" => $query,
		"items" => $items,
		"total" => $query->found_posts,
		"posts_per_page" => $query->query["posts_per_page"],
		"paged" => $paged,
		"pages" => $query->max_num_pages,
	];
}


/**
 * Title split
 */
function title_cut_words($s, $len, $end = "")
{
	$res = "";
	$res_len = 0;
	$is_cut = false;
	$s = trim($s);
	$words = explode(' ', $s);
	$words = array_filter($words, function($item){ return trim($item) != ""; });
	foreach ($words as $word)
	{
		$res_len += mb_strlen($word);
		if ($res_len > $len)
		{
			$is_cut = true;
			break;
		}
		$res .= " " . $word;
	}
	
	if ($is_cut)
	{
		$res .= $end;
	}
	
	return $res;
}