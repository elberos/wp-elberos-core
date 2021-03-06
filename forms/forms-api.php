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


namespace Elberos\Forms;


if ( !class_exists( Api::class ) ) 
{

class Api
{
	
	/**
	 * Init api
	 */
	public static function init()
	{
		add_action('rest_api_init', '\\Elberos\\Forms\\Api::register_api');
	}
	
	
	
	/**
	 * Register API
	 */
	public static function register_api()
	{
		register_rest_route
		(
			'elberos_forms',
			'submit_form',
			array(
				'methods' => 'POST',
				'callback' => function ($arr){ return static::submit_form($arr); },
			)
		);
	}
	
	
	
	/**
	 * Get field by name
	 */
	public static function getFieldByName($fields, $field_name)
	{
		foreach ($fields as $field)
		{
			if ($field['name'] == $field_name)
			{
				return $field;
			}
		}
		return null;
	}
	
	
	
	/**
	 * Api submit form
	 */
	public static function submit_form($params)
	{
		global $wpdb;
		
		$table_forms_name = $wpdb->prefix . 'elberos_forms';
		$table_forms_data_name = $wpdb->prefix . 'elberos_forms_data';
		$form_api_name = isset($_POST["form_api_name"]) ? $_POST["form_api_name"] : "";
		$form_title = isset($_POST["form_title"]) ? $_POST["form_title"] : "";
		$forms_wp_nonce = isset($_POST["_wpnonce"]) ? $_POST["_wpnonce"] : "";
		$wp_nonce_res = (int)wp_verify_nonce($forms_wp_nonce, 'wp_rest');
		
		/* Check wp nonce */
		if ($wp_nonce_res == 0)
		{
			return 
			[
				"success" => false,
				"message" => __("Ошибка формы. Перезагрузите страницу.", "elberos-forms"),
				"fields" => [],
				"code" => -1,
			];
		}
		
		/* Find form */
		$forms = $wpdb->get_results
		(	
			$wpdb->prepare
			(
				"select * from $table_forms_name where api_name=%s", $form_api_name
			),
			ARRAY_A,
			0
		);
		$form = isset($forms[0]) ? $forms[0] : null;
		if ($form == null)
		{
			return 
			[
				"success" => false,
				"message" => "Форма не найдена",
				"fields" => [],
				"code" => -1,
			];
		}
		
		$form_id = $form['id'];
		$form_settings = @json_decode($form['settings'], true);
		$form_settings_fields = isset($form_settings['fields']) ? $form_settings['fields'] : [];
		$form_data = [];
		$data = isset($_POST["data"]) ? $_POST["data"] : [];
		$utm = isset($_POST["utm"]) ? $_POST["utm"] : [];
		
		/* Validate fields */
		$fields = [];
		foreach ($data as $key => $value)
		{
			$field = static::getFieldByName($form_settings_fields, $key);
			if ($field == null)
			{
				continue;
			}
			
			$title = isset($field['title']) ? $field['title'] : "";
			$required = isset($field['required']) ? $field['required'] : false;
			if ($value == "" && $required)
			{
				$fields[$key][] = __("Пустое поле '" . $title . "'", "elberos-forms");
			}
			
			$form_data[$key] = $value;
		}
		
		/* Add missing fields */
		foreach ($form_settings_fields as $field)
		{
			$title = isset($field['title']) ? $field['title'] : "";
			$key = isset($field['name']) ? $field['name'] : "";
			if ($key == null)
			{
				continue;
			}
			if (isset($data[$key]))
			{
				continue;
			}
			$required = isset($field['required']) ? $field['required'] : false;
			if ($required)
			{
				$fields[$key][] = __("Пустое поле '" . $title . "'", "elberos-forms");
			}
			
			$form_data[$key] = "";
		}
		
		/* If validate fields error */
		if (count ($fields) > 0)
		{
			return 
			[
				"success" => false,
				"message" => __("Ошибка. Проверьте корректность введенных данных", "elberos-forms"),
				"fields" => $fields,
				"code" => -2,
			];
		}
		
		/* Add UTM */
		$f_utm = isset($_COOKIE['f_utm']) ? $_COOKIE['f_utm'] : null;
		if ($f_utm) $f_utm = @json_decode( stripslashes($f_utm), true);
		if ($f_utm)
		{
			$utm['utm_source'] = isset($f_utm['s']) ? $f_utm['s'] : null;
			$utm['utm_medium'] = isset($f_utm['m']) ? $f_utm['m'] : null;
			$utm['utm_campaign'] = isset($f_utm['cmp']) ? $f_utm['cmp'] : null;
			$utm['utm_content'] = isset($f_utm['cnt']) ? $f_utm['cnt'] : null;
			$utm['utm_term'] = isset($f_utm['t']) ? $f_utm['t'] : null;
		}
		
		/* Insert data */
		$data_s = json_encode($form_data);
		$utm_s = json_encode($utm);
		$gmtime_add = gmdate('Y-m-d H:i:s');
		
		/* Check if spam */
		$ip = \Elberos\get_client_ip();
		$spam = static::checkSpam($ip);
		
		$q = $wpdb->prepare
		(
			"INSERT INTO $table_forms_data_name
				(
					form_id, form_title, data, utm, gmtime_add, spam
				) 
				VALUES( %d, %s, %s, %s, %s, %d )",
			[
				$form_id, $form_title, $data_s, $utm_s, $gmtime_add, $spam
			]
		);
		$wpdb->query($q);
		
		return
		[
			"success" => true,
			"message" => "Ok",
			"fields" => [],
			"code" => 1,
		];
	}
	
	
	/**
	 * Check spam by ip
	 */
	static function checkSpam($ip)
	{
		global $wpdb;
		
		$time = time();
		$spam_result = 0;
		
		$table_name = $wpdb->prefix . 'elberos_forms_ip';
		$sql = $wpdb->prepare
		(
			"SELECT * FROM $table_name WHERE ip=%s", $ip
		);
		$row = $wpdb->get_row($sql, ARRAY_A);
		if ($row == null)
		{
			$q = $wpdb->prepare
			(
				"INSERT INTO $table_name
					(
						ip, count, last
					) 
					VALUES( %s, %d, %d )",
				[
					$ip, 1, $time
				]
			);
			$wpdb->query($q);
		}
		
		else
		{
			$count = $row["count"];
			if ($row["last"] + 15*60 > $time)
			{
				if ($count >= 3)
				{
					$spam_result = 1;
					$count--;
				}
			}
			else
			{
				$count = 0;
			}
			
			$wpdb->query
			(
				$wpdb->prepare
				(
					"UPDATE $table_name SET
						count=%d,
						last=%d
					WHERE ip = %s",
					[
						$count + 1,
						$time,
						$ip
					]
				)
			);
		}
		
		
		return $spam_result;
	}
	
}

}