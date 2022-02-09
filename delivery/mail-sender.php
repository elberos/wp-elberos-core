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

use Elberos\Forms\FormsHelper;


if ( !class_exists( MailSender::class ) ) 
{
	
	class MailSender
	{
		
		/**
		 * Queue mail delivery
		 */
		public static function addMail($plan, $email_to, $title, $message, $params = [])
		{
			global $wpdb;
			$table_forms_delivery = $wpdb->base_prefix . 'elberos_delivery';
			
			$uuid = isset($params['uuid']) ? $params['uuid'] : wp_generate_uuid4();
			$gmtime_add = gmdate('Y-m-d H:i:s');
			$gmtime_plan = isset($params['gmtime_plan']) ? $params['gmtime_plan'] : $gmtime_add;
			
			// If array
			if (gettype($email_to) == 'array') $email_to = implode(", ", $email_to);
			
			// Add email to Queue
			$q = $wpdb->prepare
			(
				"INSERT INTO $table_forms_delivery
					(
						worker, plan, dest, title, message, gmtime_add, gmtime_plan, uuid
					) 
					VALUES( %s, %s, %s, %s, %s, %s, %s, %s )",
				[
					'email', $plan, $email_to, $title, $message, $gmtime_add, $gmtime_plan, $uuid
				]
			);
			$wpdb->query($q);
		}
		
		
		
		/**
		 * Get mail plan
		 */
		public static function getPlan($plan)
		{
			global $wpdb;
			$table_clients = $wpdb->base_prefix . 'elberos_mail_settings';
			$sql = $wpdb->prepare
			(
				"SELECT * FROM $table_clients WHERE plan=%s and enable=1 and is_deleted=0", $plan
			);
			return $wpdb->get_row($sql, ARRAY_A);
		}
		
		
		
		/**
		 * Send mail
		 */
		public static function sendMail($plan, $email_to, $title, $message, $params = [])
		{
			/* Enable Swift Mailer */
			wp_swiftmailer_load();
			
			/* Check mail settings */
			$row = static::getPlan($plan);
			if (!$row) $row = static::getPlan("default");
			if (!$row)
			{
				return [-2, 'Mail is Disable'];
			}
			
			$uuid = isset($params['uuid']) ? $params['uuid'] : wp_generate_uuid4();
			$enable = $row['enable'];
			$host = $row['host'];
			$port = $row['port'];
			$login = $row['login'];
			$password = $row['password'];
			$ssl_enable = $row['ssl_enable'];
			
			if ($enable != "1")
			{
				return [-2, 'Mail is Disable'];
			}
			if ($email_to == null)
			{
				return [-3, 'email_to is empty'];
			}
			if (gettype($email_to) == 'array' and count($email_to) == 0)
			{
				return [-3, 'email_to is empty'];
			}
			
			// Create message
			$message = \Swift_Message::newInstance()
				->setFrom($login)
				->setContentType('text/html')
				->setCharset('utf-8')
				->setSubject("=?utf-8?b?" . base64_encode($title) . "?=")
				->setBody($message)
				->setMaxLineLength(998)
			;

			// Add User Agent
			$headers = $message->getHeaders();
			$headers->addTextHeader('User-Agent', 'PHP Swiftmail');
			
			// Add email to
			if (gettype($email_to) != 'array')
			{
				$email_to = str_replace(";", ",", $email_to);
				$email_to = explode(",", $email_to);
			}
			$email_to = array_map( function($item){ return trim($item); }, $email_to );
			$message->setTo($email_to);
			
			// Add uuid
			//$message->setId($uuid);
			
			// Create transport
			$transport = \Swift_SmtpTransport::newInstance($host, $port);
			
			// Authentification
			$transport->setUsername($login)->setPassword($password);
			
			// Set ssl
			if ($ssl_enable) $transport->setEncryption('ssl');
			
			$error_code = 0;
			$error_message = "";
			try
			{
				$mailer = \Swift_Mailer::newInstance($transport);
				$result = $mailer->send($message);
				$error_code = 1;
				$error_message = "Ok";
			}
			catch (\Exception $e)
			{
				$error_code = -1;
				$error_message = $e->getMessage();
			}
			
			// Close connection
			$transport->stop();
			
			// Return
			return [$error_code, $error_message];
		}
		
		
		
		/**
		 * Returns forms mail
		 */
		public static function getFormsMail($item)
		{
			$site_name = get_bloginfo("", "name");
			$form_id = $item['form_id'];
			$item_title = $item['form_title'];
			$form_position = $item['form_position'];
			$form_title = FormsHelper::get_form_title($form_id);
			$title = ($item_title != "" ? $item_title : $form_title) . " с сайта " .
				$site_name . " номер " . $item["id"];
			$email_to = FormsHelper::get_form_email_to($form_id);
			
			$form_data_res = []; $form_data_utm = [];
			$form_data = @json_decode($item['data'], true);
			$form_utm = @json_decode($item['utm'], true);
			
			// elberos_form_get_data
			$res = apply_filters
			(
				'elberos_form_get_data',
				[
					'item' => $item,
					'data' => $form_data,
					'name' => 'display_item',
				]
			);
			$form_data = $res['data'];
			
			foreach ($form_data as $key => $value)
			{
				if ($value == "") continue;
				$key_title = FormsHelper::get_field_title($item['form_id'], $key);
				if ($key_title == "") continue;
				$form_data_res[] = [
					'key'=>$key,
					'title'=>$key_title,
					'value'=>$value,
				];
			}
			foreach ($form_utm as $key => $value)
			{
				if ($value == "") continue;
				$key_title = FormsHelper::decode_utm_key($key);
				if ($key_title == "") continue;
				$form_data_res[] = [
					'key'=>$key,
					'title'=>$key_title,
					'value'=>$value,
				];
			}
			if ($form_position)
			{
				array_unshift(
					$form_data_res,
					[
						'key'=>"",
						'title'=>"Расположение формы",
						'value'=>$form_position,
					]
				);
			}
			if ($item_title != "")
			{
				array_unshift(
					$form_data_res,
					[
						'key'=>"",
						'title'=>"Название",
						'value'=>$item_title,
					]
				);
			}
			array_unshift(
				$form_data_res,
				[
					'key'=>"",
					'title'=>"Форма",
					'value'=>$form_title,
				]
			);
			array_unshift(
				$form_data_res,
				[
					'key'=>"",
					'title'=>"Сайт",
					'value'=>$site_name,
				]
			);
			
			$res_data = array_map
			(
				function($item)
				{
					return "
						<tr class='forms_data_item'>
							<td class='forms_data_item_key' style='padding: 2px; text-align: right;'>".
								esc_html($item['title']).":</td>
							<td class='forms_data_item_value' style='padding: 2px; text-align: left;'>".
								esc_html($item['value'])."</td>
						</tr>
					";
				},
				$form_data_res
			);
			
			ob_start();
			?>
			<html>
			<head>
			<title><?php echo $title; ?></title>
			</head>
			<body>
			<div style="font-family:verdana;font-size:16px">
			<table class="forms_data_display_item">
				<?php echo implode("", $res_data); ?>
			</table>
			</div>
			</body>
			</html>
			<?php
			$message = ob_get_contents();
			ob_end_clean();
			
			return [$title, $message, $email_to];
		}
		
		
		
		/**
		 * Cron send mail
		 */
		public static function cron_send_mail()
		{
			global $wpdb;
			
			// Load forms settings
			FormsHelper::load_forms_settings();
			
			// Load forms items
			$table_name = $wpdb->base_prefix . 'elberos_forms_data';
			$forms_settings_table_name = $wpdb->base_prefix . 'elberos_forms';
			
			$sql = "SELECT t.*,
				forms.name as form_name,
				forms.api_name as form_api_name
			FROM $table_name as t
			INNER JOIN $forms_settings_table_name as forms on (forms.id = t.form_id)
			WHERE send_email_code=0 LIMIT 5";
			$items = $wpdb->get_results($sql, ARRAY_A);
			
			// Send forms email
			foreach ($items as $item)
			{
				if ($item["spam"] == 0)
				{
					list ($title, $message, $email_to) = static::getFormsMail($item);
					\Elberos\add_email("forms", $email_to, $title, $message);
				}
				$sql = $wpdb->prepare
				(
					"UPDATE $table_name SET
						send_email_uuid=%s,
						send_email_code=%d,
						send_email_error=%s
					WHERE id = %d",
					[ "", 1, "Ok", $item['id'] ]
				);
				$wpdb->query( $sql );
			}
			
			// Send delivery email
			$table_name_delivery = $wpdb->base_prefix . 'elberos_delivery';
			$sql = "SELECT t.* FROM `$table_name_delivery` as t WHERE status=0 LIMIT 5";
			$items = $wpdb->get_results( $sql, ARRAY_A );
			//var_dump( $sql );
			
			// Send forms email
			foreach ($items as $item)
			{
				$send_email_code = -1;
				$send_email_error = "Unknown error";
				list ($send_email_code, $send_email_error) =
					static::sendMail
					(
						$item['plan'],
						$item['dest'],
						$item['title'],
						$item['message'],
						[
							'uuid'=>$item['uuid']
						]
					);
				
				$status = -1;
				if ($send_email_code == 1) $status = 1;
				$gmtime_send = gmdate('Y-m-d H:i:s', time());
				
				$sql = $wpdb->prepare
				(
					"UPDATE `$table_name_delivery` SET
						status=%d,
						send_email_code=%d,
						send_email_error=%s,
						gmtime_send=%s
					WHERE id = %d",
					[
						$status,
						$send_email_code,
						$send_email_error,
						$gmtime_send,
						$item['id'],
					]
				);
				//var_dump( $sql );
				$wpdb->query( $sql );
				
				flush();
				sleep( mt_rand(1,5) );
			}
		}
		
	}
	
	
}