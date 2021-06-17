<?php
/**
 * Plugin Name: Elberos Core
 * Description: Core plugin for WordPress
 * Version:     0.2.0
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
		add_action('elberos_twig', 'Elberos_Plugin::elberos_twig');
		
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
		if ( !wp_next_scheduled( 'elberos_cron_send_mail' ) )
		{
			wp_schedule_event( time() + 60, 'elberos_two_minute', 'elberos_cron_send_mail' );
		}
		add_action( 'elberos_cron_send_mail', 'Elberos\MailSender::cron_send_mail' );
		
		/* Remove plugin updates */
		add_filter( 'site_transient_update_plugins', 'Elberos_Plugin::filter_plugin_updates' );
		
		/* UTM form filter */
		add_filter( 'elberos_form_utm', 'Elberos_Plugin::elberos_form_utm' );
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
	public static function cron_schedules($schedules)
	{
		$schedules['elberos_two_minute'] = array
		(
			'interval' => 120, // Каждые 2 минуты
			'display'  => __( 'Once Two Minute', 'elberos-core' ),
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
				\Elberos\Forms\Data::show();
			},
			'/wp-content/plugins/wp-elberos-core/images/form.png',
			35
		);
		
		add_submenu_page(
			'elberos-forms',
			'Настройки форм', 'Настройки форм',
			'manage_options', 'elberos-forms-settings',
			function()
			{
				\Elberos\Forms\Settings::show();
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
	
	
	
	/**
	 * Elberos form utm
	 */
	public static function elberos_form_utm($utm)
	{
		$f_utm = isset($_COOKIE['f_utm']) ? $_COOKIE['f_utm'] : null;
		if ($f_utm) $f_utm = @json_decode( stripslashes($f_utm), true );
		if ($f_utm)
		{
			$utm['utm_source'] = isset($f_utm['s']) ? $f_utm['s'] : null;
			$utm['utm_medium'] = isset($f_utm['m']) ? $f_utm['m'] : null;
			$utm['utm_campaign'] = isset($f_utm['cmp']) ? $f_utm['cmp'] : null;
			$utm['utm_content'] = isset($f_utm['cnt']) ? $f_utm['cnt'] : null;
			$utm['utm_term'] = isset($f_utm['t']) ? $f_utm['t'] : null;
		}
		return $utm;
	}
	
	
	
	/**
	 * Twig
	 */
	public static function elberos_twig($twig)
	{
		$twig->getLoader()->addPath(__DIR__ . "/templates", "core");
	}
	
}

require_once __DIR__ . "/src/class-wp-list-table.php";
require_once __DIR__ . "/src/lib.php";
require_once __DIR__ . "/src/Html.php";
require_once __DIR__ . "/src/RawString.php";
require_once __DIR__ . "/src/Site.php";
require_once __DIR__ . "/src/Slider.php";
require_once __DIR__ . "/src/StructBuilder.php";
require_once __DIR__ . "/src/Image.php";
require_once __DIR__ . "/src/Table.php";
require_once __DIR__ . "/src/Update.php";
require_once __DIR__ . "/src/Dialog.php";
require_once __DIR__ . "/delivery/mail-sender.php";
require_once __DIR__ . "/forms/forms.php";
require_once __DIR__ . "/forms/forms-api.php";
require_once __DIR__ . "/forms/forms-helper.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/loginlockdown/loginlockdown.php";

Elberos_Plugin::init();
\Elberos\Forms\Api::init();

function wp_swiftmailer_load()
{
	if (!class_exists(Swift_Message::class))
	{
		require_once __DIR__ . "/vendor/autoload.php";
	}
}

}
