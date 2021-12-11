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
 * Returns image url
 */
function get_image_url($post_id, $size)
{
	$img = wp_get_attachment_image_src($post_id, $size);
	if ($img)
	{
		$post = get_post( $post_id );
		$url = $img[0] . "?_=" . strtotime($post->post_modified_gmt);
		return $url;
	}
	return "";
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
	$cookie_file = null;
	
	if ($params != null)
	{
		if (isset($params['post'])) $post = $params['post'];
		if (isset($params['headers'])) $post = $params['headers'];
		if (isset($params['user_agent'])) $post = $params['user_agent'];
		if (isset($params['cookie_file'])) $post = $params['cookie_file'];
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
function twig_render($twig, $template, $context)
{
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
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS ${distinct} ${fields} FROM ${table_name} as t ${join} ${where} ${order_by}";
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
	$res = [];
	
	if ( preg_match_all( '/(<h([1-6]{1})[^>]*>)(.*)<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER ) )
	{
		foreach ($matches as $arr)
		{
			$h_name = $arr[1];
			$h_title = trim($arr[3]);
			
			if ($h_name == "<h1>") $h_name = "h1";
			else if ($h_name == "<h2>") $h_name = "h2";
			else if ($h_name == "<h3>") $h_name = "h3";
			else if ($h_name == "<h4>") $h_name = "h4";
			else if ($h_name == "<h5>") $h_name = "h5";
			else if ($h_name == "<h6>") $h_name = "h6";
			
			if (in_array($h_name, ["h1","h2","h3","h4","h5","h6"]))
			{
				$res[] =
				[
					"name" => $h_name,
					"title" => $h_title,
				];
			}
		}
	}
	
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