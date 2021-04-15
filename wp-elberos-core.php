<?php
/**
 * Plugin Name: Elberos Core
 * Description: Core plugin for WordPress
 * Version:     0.1.0
 * Author:      Elberos team <support@elberos.org>
 * License:     Apache License 2.0
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


if ( !class_exists( 'Elberos_Plugin' ) ) 
{

class Elberos_Plugin
{
	
	/**
	 * Init Plugin
	 */
	public static function init()
	{
		add_action
		(
			'admin_init', 
			function()
			{
				require_once __DIR__ . "/delivery/admin-delivery-log.php";
				require_once __DIR__ . "/delivery/admin-mail-settings.php";
				require_once __DIR__ . "/forms/admin-forms-settings.php";
				require_once __DIR__ . "/forms/admin-forms-data.php";
			}
		);
		add_action('admin_menu', 'Elberos_Plugin::register_admin_menu');
		add_action('send_headers', 'Elberos_Plugin::send_headers');
		
		/* Disable Rank Math Seo Output */
		add_action
		(
			'plugins_loaded',
			function ()
			{
				if (class_exists(RankMath::class))
				{
					// Remove RankMath SEO Frontend Block
					$rank_math = RankMath::get();
					remove_action( 'plugins_loaded', [ $rank_math, 'init_frontend' ], 15 );
				}
			},
			0
		);
		
		// Add Cron
		add_filter( 'cron_schedules', 'Elberos_Plugin::cron_schedules' );
		if ( !wp_next_scheduled( 'elberos_forms_cron_send_mail' ) )
		{
			wp_schedule_event( time() + 60, 'elberos_forms_two_minute', 'elberos_forms_cron_send_mail' );
		}
		add_action( 'elberos_forms_cron_send_mail', 'Elberos\MailSender::cron_send_mail' );
		
		/* Remove plugin updates */
		add_filter( 'site_transient_update_plugins', 'Elberos_Plugin::filter_plugin_updates' );
	}	
	
	
	
	/**
	 * Remove plugin updates
	 */
	public static function filter_plugin_updates($value)
	{
		$name = plugin_basename(__FILE__);
		if (isset($value->response[$name]))
		{
			unset($value->response[$name]);
		}
		return $value;
	}
	
	
	
	/**
	 * Cron schedules
	 */
	public static function cron_schedules()
	{
		$schedules['elberos_forms_two_minute'] = array
		(
			'interval' => 120, // Каждые 2 минуты
			'display'  => __( 'Once Two Minute', 'elberos-forms' ),
		);
		return $schedules;
	}
	
	
	
	/**
	 * Register Admin Menu
	 */
	public static function register_admin_menu()
	{
		add_menu_page(
			'Формы', 'Формы',
			'manage_options', 'elberos-forms',
			function ()
			{
				\Elberos\Forms\Settings::show();
			},
			null,
			35
		);
		
		add_submenu_page(
			'elberos-forms',
			'Данные форм', 'Данные форм',
			'manage_options', 'elberos-forms-data',
			function()
			{
				\Elberos\Forms\Data::show();
			}
		);
		
		add_submenu_page(
			'options-general.php',
			'Настройки почты', 'Настройки почты',
			'manage_options', 'elberos-mail-settings',
			function()
			{
				\Elberos\Delivery\MailSettings::show();
			}
		);
		
		add_submenu_page(
			'options-general.php',
			'Лог доставки сообщений', 'Лог доставки сообщений',
			'manage_options', 'elberos-delivery-log',
			function()
			{
				\Elberos\Delivery\Log::show();
			}
		);
	}
	
	
	
	/**
	 * Send headers
	 */
	public static function send_headers()
	{
		// headers
		$utm_source = isset($_GET['utm_source']) ? $_GET['utm_source'] : null;
		$utm_medium = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : null;
		$utm_campaign = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : null;
		$utm_content = isset($_GET['utm_content']) ? $_GET['utm_content'] : null;
		$utm_term = isset($_GET['utm_term']) ? $_GET['utm_term'] : null;
		$utm_place = isset($_GET['utm_place']) ? $_GET['utm_place'] : null;
		$utm_pos = isset($_GET['utm_pos']) ? $_GET['utm_pos'] : null;
		
		if (
			$utm_source != null ||
			$utm_medium != null ||
			$utm_campaign != null ||
			$utm_content != null ||
			$utm_term != null
		)
		{
			$utm = [
				's' => $utm_source,
				'm' => $utm_medium,
				'cmp' => $utm_campaign,
				'cnt' => $utm_content,
				't' => $utm_term,
				'pl' => $utm_place,
				'ps' => $utm_pos,
			];
			
			setcookie( "f_utm", json_encode($utm), time() + 7*24*60*60, "/" );
		}
	}
	
}

require_once __DIR__ . "/src/lib.php";
require_once __DIR__ . "/src/Html.php";
require_once __DIR__ . "/src/RawString.php";
require_once __DIR__ . "/src/Site.php";
require_once __DIR__ . "/src/Slider.php";
require_once __DIR__ . "/src/Image.php";
require_once __DIR__ . "/src/Update.php";
require_once __DIR__ . "/src/Dialog.php";
require_once __DIR__ . "/delivery/mail-sender.php";
require_once __DIR__ . "/forms/forms.php";
require_once __DIR__ . "/forms/forms-api.php";
require_once __DIR__ . "/forms/forms-helper.php";
require_once __DIR__ . "/vendor/autoload.php";

Elberos_Plugin::init();
\Elberos\Forms\Api::init();

}
