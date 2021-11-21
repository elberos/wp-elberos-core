<?php
/* 
Plugin Name: Login LockDown
Plugin URI: http://www.bad-neighborhood.com/
Version: v1.8.1
Author: Michael VanDeMar
Description: Adds some extra security to WordPress by restricting the rate at which failed logins can be re-attempted from a given IP range. Distributed through <a href="http://www.bad-neighborhood.com/" target="_blank">Bad Neighborhood</a>.
*/

/*
== Change Log ==
*
* ver. 1.8.1 30-Sep-2019
* - adding missing ./languages folder
*
* ver. 1.8 30-Sep-2019
* - fixed issues with internationalization, added .pot file
* - changed the credit link to default to not showing
*
* ver. 1.7.1 13-Sep-2016
* - fixed bug causing all ipv6 addresses to get locked out if 1 was
* - added in WordPress MultiSite functionality
* - fixed bug where subnets could be overly matched, causing more IPs to be blocked than intended
* - moved the report for locked out IP addresses to its own tab
*
* ver. 1.6.1 8-Mar-2014
* - fixed html glitch preventing options from being saved
*
* ver. 1.6 7-Mar-2014
* - cleaned up deprecated functions
* - fixed bug with invalid property on a non-object when locking out invalid usernames
* - fixed utilization of $wpdb->prepare
* - added more descriptive help text to each of the options
* - added the ability to remove the "Login form protected by Login LockDown." message from within the dashboard
*
* ver. 1.5 17-Sep-2009
* - implemented wp_nonce security in the options and lockdown release forms in the admin screen
* - fixed a security hole with an improperly escaped SQL query
* - encoded certain outputs in the admin panel using esc_attr() to prevent XSS attacks
* - fixed an issue with the 'Lockout Invalid Usernames' option not functioning as intended
*
* ver. 1.4 29-Aug-2009
* - removed erroneous error affecting WP 2.8+
* - fixed activation error caused by customizing the location of the wp-content folder
* - added in the option to mask which specific login error (invalid username or invalid password) was generated
* - added in the option to lock out failed login attempts even if the username doesn't exist
*
* ver. 1.3 23-Feb-2009
* - adjusted positioning of plugin byline
* - allowed for dynamic location of plugin files
*
* ver. 1.2 15-Jun-2008
* - now compatible with WordPress 2.5 and up only
*
* ver. 1.1 01-Sep-2007
* - revised time query to MySQL 4.0 compatability
*
* ver. 1.0 29-Aug-2007
* - released
*/

/*
== Installation ==

1. Extract the zip file into your plugins directory into its own folder.
2. Activate the plugin in the Plugin options.
3. Customize the settings from the Options panel, if desired.

*/

/*
/--------------------------------------------------------------------\
|                                                                    |
| License: GNU GENERAL PUBLIC LICENSE Version 2                      |
|                                                                    |
| Login LockDown - added security measures to WordPress intended to  |
| inhibit or reduce brute force password discovery.                  |
| Copyright (C) 2007 - 2014, Michael VanDeMar,                       |
| http://www.bad-neighborhood.com                                    |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/

if ( ! defined( 'ABSPATH' ) )
{
	exit; // Exit if accessed directly.
}

$loginlockdown_db_version = "1.0";
$loginlockdownOptions = get_loginlockdownOptions();

function loginLockdown_install() {
	global $wpdb;
	global $loginlockdown_db_version;
	$table_name = $wpdb->base_prefix . "login_fails";

	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		$sql = "CREATE TABLE " . $table_name . " (
			`login_attempt_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_name` varchar(255) NOT NULL,
			`login_attempt_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`login_attempt_IP` varchar(100) NOT NULL default '',
			PRIMARY KEY  (`login_attempt_ID`),
			KEY `login_attempt_date` (`login_attempt_date`),
			KEY `login_attempt_IP` (`login_attempt_IP`)
			);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	$table_name = $wpdb->base_prefix . "lockdowns";

	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		$sql = "CREATE TABLE " . $table_name . " (
			`lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_name` varchar(255) NOT NULL,
			`lockdown_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`release_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`lockdown_IP` varchar(100) NOT NULL default '',
			PRIMARY KEY  (`lockdown_ID`),
			KEY `lockdown_date` (`lockdown_date`),
			KEY `release_date` (`release_date`),
			KEY `lockdown_IP` (`lockdown_IP`)
			);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	add_option("loginlockdown_db_version", "1.0", "", "no");
	// added in 1.6, cleanup from previously improperly set db versions
	delete_option( "loginlockdown_db1_version" );
	delete_option( "loginlockdown_db2_version" );
}

function countFails($username = "") {
	global $wpdb;
	global $loginlockdownOptions;
	$table_name = $wpdb->base_prefix . "login_fails";
	$subnet = calc_subnet($_SERVER['REMOTE_ADDR']);

	$numFailsquery = "SELECT COUNT(login_attempt_ID) FROM $table_name " . 
					"WHERE login_attempt_date + INTERVAL " .
					$loginlockdownOptions['retries_within'] . " MINUTE > now() AND " . 
					"login_attempt_IP LIKE '%s'";
	$numFailsquery = $wpdb->prepare( $numFailsquery, $subnet[1]  . "%");

	$numFails = $wpdb->get_var($numFailsquery);
	return $numFails;
}

function incrementFails($username = "") {
	global $wpdb;
	global $loginlockdownOptions;
	$table_name = $wpdb->base_prefix . "login_fails";
	$subnet = calc_subnet($_SERVER['REMOTE_ADDR']);
	
	$username = sanitize_user($username);
	$insert = "INSERT INTO " . $table_name . " (user_name, login_attempt_date, login_attempt_IP) " .
			"VALUES (%s, now(), '%s')";
	$insert = $wpdb->prepare( $insert, $username, $subnet[0] );
	$results = $wpdb->query($insert);
}

function lockDown($username = "") {
	global $wpdb;
	global $loginlockdownOptions;
	$table_name = $wpdb->base_prefix . "lockdowns";
	$subnet = calc_subnet($_SERVER['REMOTE_ADDR']);

	$username = sanitize_user($username);
	$insert = "INSERT INTO " . $table_name . " (user_name, lockdown_date, release_date, lockdown_IP) " .
			"VALUES (%s, now(), date_add(now(), INTERVAL " .
			$loginlockdownOptions['lockout_length'] . " MINUTE), '%s')";
	$insert = $wpdb->prepare( $insert, $username, $subnet[0] );
	$results = $wpdb->query($insert);
}

function isLockedDown() {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "lockdowns";
	$subnet = calc_subnet($_SERVER['REMOTE_ADDR']);

	$stillLockedquery = "SELECT user_name FROM $table_name " . 
					"WHERE release_date > now() AND " . 
					"lockdown_IP LIKE %s";
	$stillLockedquery = $wpdb->prepare($stillLockedquery, $subnet[1] . "%");
	//var_dump($stillLockedquery);
	$stillLocked = $wpdb->get_var($stillLockedquery);

	return $stillLocked;
}

function listLockedDown() {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "lockdowns";

	$listLocked = $wpdb->get_results(
		"SELECT lockdown_ID, floor((UNIX_TIMESTAMP(release_date)-UNIX_TIMESTAMP(now()))/60) AS minutes_left, ".
		"lockdown_IP FROM $table_name WHERE release_date > now()", ARRAY_A
	);

	return $listLocked;
}

function get_loginlockdownOptions() {
	$loginlockdownAdminOptions = array(
		'max_login_retries' => 5,
		'retries_within' => 10,
		'lockout_length' => 60,
		'lockout_invalid_usernames' => 'yes',
		'mask_login_errors' => 'yes',
		'show_credit_link' => 'no',
		'login_with_email' => 'yes',
	);
	$loginlockdownOptions = get_option("loginlockdownAdminOptions");
	if ( !empty($loginlockdownOptions) ) {
		foreach ( $loginlockdownOptions as $key => $option ) {
			$loginlockdownAdminOptions[$key] = $option;
		}
	}
	update_option("loginlockdownAdminOptions", $loginlockdownAdminOptions);
	return $loginlockdownAdminOptions;
}

function calc_subnet($ip) {
	$subnet[0] = $ip;
	if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
		$ip = expandipv6($ip);
		preg_match("/^([0-9abcdef]{1,4}:){4}/", $ip, $matches);
		$subnet[0] = $ip;
		$subnet[1] = $matches[0];
	} else {
		$subnet[1] = substr ($ip, 0 , strrpos ( $ip, "." ) + 1);
	}
	return $subnet;
}

function expandipv6($ip){
	$hex = unpack("H*hex", inet_pton($ip));         
	$ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);

	return $ip;
}


function print_loginlockdownAdminPage() {
	global $wpdb;
	$table_name = $wpdb->base_prefix . "lockdowns";
	$table_name_lockdowns = $wpdb->base_prefix . "lockdowns";
	$table_name_login_fails = $wpdb->base_prefix . "login_fails";
	$loginlockdownAdminOptions = get_loginlockdownOptions();

	if (isset($_POST['update_loginlockdownSettings'])) {

		//wp_nonce check
		check_admin_referer('login-lockdown_update-options');

		if (isset($_POST['ll_max_login_retries'])) {
			$loginlockdownAdminOptions['max_login_retries'] = $_POST['ll_max_login_retries'];
		}
		if (isset($_POST['ll_retries_within'])) {
			$loginlockdownAdminOptions['retries_within'] = $_POST['ll_retries_within'];
		}
		if (isset($_POST['ll_lockout_length'])) {
			$loginlockdownAdminOptions['lockout_length'] = $_POST['ll_lockout_length'];
		}
		if (isset($_POST['ll_lockout_invalid_usernames'])) {
			$loginlockdownAdminOptions['lockout_invalid_usernames'] = $_POST['ll_lockout_invalid_usernames'];
		}
		if (isset($_POST['ll_mask_login_errors'])) {
			$loginlockdownAdminOptions['mask_login_errors'] = $_POST['ll_mask_login_errors'];
		}
		if (isset($_POST['ll_show_credit_link'])) {
			$loginlockdownAdminOptions['show_credit_link'] = $_POST['ll_show_credit_link'];
		}
		if (isset($_POST['ll_login_with_email'])) {
			$loginlockdownAdminOptions['login_with_email'] = $_POST['ll_login_with_email'];
		}
		update_option("loginlockdownAdminOptions", $loginlockdownAdminOptions);
		?>
<div class="updated"><p><strong><?php _e("Settings Updated.", "loginlockdown");?></strong></p></div>
		<?php
	}
	if (isset($_POST['release_lockdowns'])) {

		//wp_nonce check
		check_admin_referer('login-lockdown_release-lockdowns');
		
		if (isset($_POST['releaseme'])) {
			$released = $_POST['releaseme'];
			foreach ( $released as $release_id )
			{
				$sql = "SELECT lockdown_IP from $table_name_lockdowns where lockdown_ID = '%d';";
				$sql = $wpdb->prepare($sql, $release_id);
				$row = $wpdb->get_row($sql, ARRAY_A);
				if ($row)
				{
					$lockdown_IP = $row["lockdown_IP"];
					
					$sql = "DELETE FROM $table_name_lockdowns " .
							"WHERE lockdown_ID = '%d'";
					$sql = $wpdb->prepare($sql, $release_id);
					$results = $wpdb->query($sql);
					
					$sql = "DELETE FROM $table_name_login_fails " .
								"WHERE login_attempt_IP = %s";
					$sql = $wpdb->prepare($sql, $lockdown_IP);
					$results = $wpdb->query($sql);
				}
			}
		}
		update_option("loginlockdownAdminOptions", $loginlockdownAdminOptions);
		?>
<div class="updated"><p><strong><?php _e("Lockdowns Released.", "loginlockdown");?></strong></p></div>
		<?php
	}
	$dalist = listLockedDown();
?>
<div class="wrap">
<?php
	
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';

?>
<h2><?php _e('Login LockDown Options', 'loginlockdown') ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="?page=loginlockdown.php&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'loginlockdown') ?></a>
		<a href="?page=loginlockdown.php&tab=activity" class="nav-tab <?php echo $active_tab == 'activity' ? 'nav-tab-active' : ''; ?>"><?php _e('Activity', 'loginlockdown') ?> (<?php echo count($dalist); ?>)</a>
	</h2>
<?php if ( $active_tab == 'settings' ) { ?>
<form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
<?php
if ( function_exists('wp_nonce_field') )
	wp_nonce_field('login-lockdown_update-options');
?>

<h3><?php _e('Max Login Retries', 'loginlockdown') ?></h3>
<p><?php _e('Number of failed login attempts within the "Retry Time Period Restriction" (defined below) needed to trigger a LockDown.', 'loginlockdown') ?></p>
<p><input type="text" name="ll_max_login_retries" size="8" value="<?php echo esc_attr($loginlockdownAdminOptions['max_login_retries']); ?>"></p>
<h3><?php _e('Retry Time Period Restriction (minutes)', 'loginlockdown') ?></h3>
<p><?php _e('Amount of time that determines the rate at which failed login attempts are allowed before a LockDown occurs.', 'loginlockdown') ?></p>
<p><input type="text" name="ll_retries_within" size="8" value="<?php echo esc_attr($loginlockdownAdminOptions['retries_within']); ?>"></p>
<h3><?php _e('Lockout Length (minutes)', 'loginlockdown') ?></h3>
<p><?php _e('How long a particular IP block will be locked out for once a LockDown has been triggered.', 'loginlockdown') ?></p>
<p><input type="text" name="ll_lockout_length" size="8" value="<?php echo esc_attr($loginlockdownAdminOptions['lockout_length']); ?>"></p>
<h3><?php _e('Login with email?', 'loginlockdown') ?></h3>
<p><input type="radio" name="ll_login_with_email" value="yes" <?php if( $loginlockdownAdminOptions['login_with_email'] == "yes" ) echo "checked"; ?>>&nbsp;<?php _e('Yes', 'loginlockdown') ?>&nbsp;&nbsp;&nbsp;<input type="radio" name="ll_login_with_email" value="no" <?php if( $loginlockdownAdminOptions['login_with_email'] == "no" ) echo "checked"; ?>>&nbsp;<?php _e('No', 'loginlockdown') ?></p>
<h3><?php _e('Lockout Invalid Usernames?', 'loginlockdown') ?></h3>
<p><?php _e('By default Login LockDown will not trigger if an attempt is made to log in using a username that does not exist. You can override this behavior here.', 'loginlockdown') ?></p>
<p><input type="radio" name="ll_lockout_invalid_usernames" value="yes" <?php if( $loginlockdownAdminOptions['lockout_invalid_usernames'] == "yes" ) echo "checked"; ?>>&nbsp;<?php _e('Yes', 'loginlockdown') ?>&nbsp;&nbsp;&nbsp;<input type="radio" name="ll_lockout_invalid_usernames" value="no" <?php if( $loginlockdownAdminOptions['lockout_invalid_usernames'] == "no" ) echo "checked"; ?>>&nbsp;<?php _e('No', 'loginlockdown') ?></p>
<h3><?php _e('Mask Login Errors?', 'loginlockdown') ?></h3>
<p><?php _e('WordPress will normally display distinct messages to the user depending on whether they try and log in with an invalid username, or with a valid username but the incorrect password. Toggling this option will hide why the login failed.', 'loginlockdown') ?></p>
<p><input type="radio" name="ll_mask_login_errors" value="yes" <?php if( $loginlockdownAdminOptions['mask_login_errors'] == "yes" ) echo "checked"; ?>>&nbsp;<?php _e('Yes', 'loginlockdown') ?>&nbsp;&nbsp;&nbsp;<input type="radio" name="ll_mask_login_errors" value="no" <?php if( $loginlockdownAdminOptions['mask_login_errors'] == "no" ) echo "checked"; ?>>&nbsp;<?php _e('No', 'loginlockdown') ?></p>
<div class="submit">
<input type="submit" class="button button-primary" name="update_loginlockdownSettings" value="<?php _e('Update Settings', 'loginlockdown') ?>" /></div>
</form>
<?php } else { ?>
<form method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
<?php
if ( function_exists('wp_nonce_field') )
	wp_nonce_field('login-lockdown_release-lockdowns');
?>
<h3><?php 
if( count($dalist) == 1 ) {
	printf( esc_html__( 'There is currently %d locked out IP address.', 'loginlockdown' ), count($dalist) ); 

} else {
	printf( esc_html__( 'There are currently %d locked out IP addresses.', 'loginlockdown' ), count($dalist) ); 
} ?></h3>

<?php
	$num_lockedout = count($dalist);
	if( 0 == $num_lockedout ) {
		echo "<p>No IP blocks currently locked out.</p>";
	} else {
		foreach ( $dalist as $key => $option ) {
			?>
<li><input type="checkbox" name="releaseme[]" value="<?php echo esc_attr($option['lockdown_ID']); ?>"> <?php echo esc_attr($option['lockdown_IP']); ?> (<?php echo esc_attr($option['minutes_left']); ?> <?php _e('minutes left', 'loginlockdown') ?>)</li>
			<?php
		}
	}
?>
<div class="submit">
<input type="submit" class="button button-primary" name="release_lockdowns" value="<?php _e('Release Selected', 'loginlockdown') ?>" /></div>
</form>
<?php } ?>
</div>
<?php
}//End function print_loginlockdownAdminPage()

function loginlockdown_ap() {
	if ( function_exists('add_options_page') ) {
		add_options_page('Login LockDown', 'Login LockDown', 'manage_options', basename(__FILE__), 'print_loginlockdownAdminPage');
	}
}

//Actions and Filters   
if ( isset($loginlockdown_db_version) ) {
	//Actions
	add_action('admin_menu', 'loginlockdown_ap');
	if(!defined('WP_PLUGIN_DIR')){
		define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');
	}
	$activatestr = str_replace(WP_PLUGIN_DIR . "/", "activate_", __FILE__);
	add_action($activatestr, 'loginLockdown_install');

	// authenticate filter
	$login_with_email = $loginlockdownOptions['login_with_email'];
	if ( $login_with_email == 'no')
	{
		remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);
	}
	add_filter('authenticate', 'll_wp_authenticate_username_password', 99999, 3);
	add_filter('lostpassword_errors', 'll_lostpassword_errors', 99999, 2);
	function ll_wp_authenticate_username_password($user, $username, $password)
	{
		global $wpdb, $error;
		global $loginlockdownOptions;

		$username = sanitize_user($username);
		$password = trim($password);
		
		if ( "" != isLockedDown() )
		{
			return new WP_Error('authentication_failed', __("<strong>ERROR</strong>: We're sorry, but this IP range has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'loginlockdown'));
		}
		
		// Check login
		$login_with_email = $loginlockdownOptions['login_with_email'];
		if ( $login_with_email == 'no' && is_a($user_data, 'WP_User') )
		{
			if ($username != $user_data->user_login)
			{
				$user = null;
			}
		}
		
		// if auth
		if ( is_a($user, 'WP_User') )
		{
			return $user;
		}
		
		$ignore_codes = array('empty_username', 'empty_password');
		$max_login_retries = $loginlockdownOptions['max_login_retries'];
		$count_fails = countFails($username);
		$attempts_left = $max_login_retries - $count_fails - 1;
		
		if ($user == null or (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes)))
		{
			if ( $attempts_left >= 0 )
			{
				incrementFails($username);
			}
			else
			{
				$attempts_left = 0;
			}
			if ( $attempts_left == 0 )
			{
				lockDown($username);
				return new WP_Error('authentication_failed', __("<strong>ERROR</strong>: We're sorry, but this IP range has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'loginlockdown'));
			}
			if ( 'yes' == $loginlockdownOptions['mask_login_errors'] )
			{
				return new WP_Error('authentication_failed', sprintf(__('<strong>ERROR</strong>: Invalid username or incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>? %d attempts left', 'loginlockdown'), site_url('wp-login.php?action=lostpassword', 'login'), $attempts_left));
			}
		}
		
		return $user;
	}
	
	function ll_lostpassword_errors($errors, $user_data)
	{
		global $loginlockdownOptions;

		$username = isset($_POST['user_login']) ? $_POST['user_login'] : "";
		$username = sanitize_user($username);
		
		if ( "" != isLockedDown() )
		{
			return new WP_Error('authentication_failed', __("<strong>ERROR</strong>: We're sorry, but this IP range has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'loginlockdown'));
		}
		
		// Check login
		$login_with_email = $loginlockdownOptions['login_with_email'];
		if ( $login_with_email == 'no' && is_a($user_data, 'WP_User') )
		{
			if ($username != $user_data->user_login)
			{
				$user_data = null;
			}
		}
		
		if ( !is_a($user_data, 'WP_User') )
		{
			$max_login_retries = $loginlockdownOptions['max_login_retries'];
			$count_fails = countFails($username);
			$attempts_left = $max_login_retries - $count_fails - 1;
		
			incrementFails($username);
			
			if ( $attempts_left == 0 )
			{
				lockDown($username);
				return new WP_Error('authentication_failed', __("<strong>ERROR</strong>: We're sorry, but this IP range has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'loginlockdown'));
			}
			if ( 'yes' == $loginlockdownOptions['mask_login_errors'] )
			{
				return new WP_Error('authentication_failed', sprintf(__('<strong>ERROR</strong>: Invalid username. %d attempts left', 'loginlockdown'), $attempts_left));
			}
		}
		
		return $errors;
	}
	
	if ( !function_exists('wp_authenticate') )
	{
		/**
		 * Authenticate a user, confirming the login credentials are valid.
		 *
		 * @since 2.5.0
		 * @since 4.5.0 `$username` now accepts an email address.
		 *
		 * @param string $username User's username or email address.
		 * @param string $password User's password.
		 * @return WP_User|WP_Error WP_User object if the credentials are valid,
		 *                          otherwise WP_Error.
		 */
		function wp_authenticate( $username, $password ) {
			$username = sanitize_user( $username );
			$password = trim( $password );
			
			if ( "" != isLockedDown() )
			{
				return new WP_Error('authentication_failed', __("<strong>ERROR</strong>: We're sorry, but this IP range has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'loginlockdown'));
			}
			
			/**
			 * Filters whether a set of user login credentials are valid.
			 *
			 * A WP_User object is returned if the credentials authenticate a user.
			 * WP_Error or null otherwise.
			 *
			 * @since 2.8.0
			 * @since 4.5.0 `$username` now accepts an email address.
			 *
			 * @param null|WP_User|WP_Error $user     WP_User if the user is authenticated.
			 *                                        WP_Error or null otherwise.
			 * @param string                $username Username or email address.
			 * @param string                $password User password
			 */
			$user = apply_filters( 'authenticate', null, $username, $password );

			if ( null == $user ) {
				// TODO: What should the error message be? (Or would these even happen?)
				// Only needed if all authentication handlers fail to return anything.
				$user = new WP_Error( 'authentication_failed', __( '<strong>Error</strong>: Invalid username, email address or incorrect password.' ) );
			}

			$ignore_codes = array( 'empty_username', 'empty_password' );

			if ( is_wp_error( $user ) && ! in_array( $user->get_error_code(), $ignore_codes, true ) ) {
				$error = $user;

				/**
				 * Fires after a user login has failed.
				 *
				 * @since 2.5.0
				 * @since 4.5.0 The value of `$username` can now be an email address.
				 * @since 5.4.0 The `$error` parameter was added.
				 *
				 * @param string   $username Username or email address.
				 * @param WP_Error $error    A WP_Error object with the authentication failure details.
				 */
				do_action( 'wp_login_failed', $username, $error );
			}

			return $user;
		}
	}
	
	// Cron
	if ( !wp_next_scheduled( 'loginlockdown_release_event' ) )
	{
		wp_schedule_event( time() + 60, 'hourly', 'loginlockdown_release_event' );
	}
	add_action( 'loginlockdown_release_event', 'loginlockdown_release' );
	
	function loginlockdown_release()
	{
		global $wpdb;
		$table_name_lockdowns = $wpdb->base_prefix . "lockdowns";
		$table_name_login_fails = $wpdb->base_prefix . "login_fails";
		$loginlockdownAdminOptions = get_loginlockdownOptions();
		
		// Log 3 month's records
		$release_date = gmdate('Y-m-d H:i:s', time() - 90*24*3600);
		
		// Delete lockdowns
		$sql = "DELETE FROM $table_name_lockdowns " .
				"WHERE release_date < %s";
		$sql = $wpdb->prepare($sql, $release_date);
		$results = $wpdb->query($sql);
		
		// Delete login_fail
		$sql = "DELETE FROM $table_name_login_fails " .
					"WHERE login_attempt_date < %s";
		$sql = $wpdb->prepare($sql, $release_date);
		$results = $wpdb->query($sql);
	}
	
	
	// multisite network-wide activation
	register_activation_hook( __FILE__, 'loginlockdown_multisite_activate' );
	function loginlockdown_multisite_activate($networkwide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					loginLockdown_install();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
	}

	// multisite new site activation
	add_action( 'wpmu_new_blog', 'loginlockdown_multisite_newsite', 10, 6);
	function loginlockdown_multisite_newsite($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;

		if (is_plugin_active_for_network('loginlockdown/loginlockdown.php')) {
			$old_blog = $wpdb->blogid;
			switch_to_blog($blog_id);
			loginLockdown_install();
			switch_to_blog($old_blog);
		}
	}

	// multisite old sites check

	add_action('admin_init','loginlockdown_multisite_legacy');
	function loginlockdown_multisite_legacy() {
		$loginlockdownMSRunOnce = get_option("loginlockdownmsrunonce");
		if ( empty($loginlockdownMSRunOnce) ) {
			global $wpdb;

			if (function_exists('is_multisite') && is_multisite()) {

				$old_blog = $wpdb->blogid;

				// Get all blog ids
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {

					// check if already exists
					$bed_check = $wpdb->query("SHOW TABLES LIKE '{$wpdb->base_prefix}{$blog_id}_login_fails'");
					if (!$bed_check) {

						switch_to_blog($blog_id);
						loginLockdown_install();

					}
				}
				switch_to_blog($old_blog);
			}
			add_option("loginlockdownmsrunonce", "done", "", "no");
			return;
		}
	}
}

add_action('plugins_loaded', 'loginlockdown_init', 10); 

function loginlockdown_init() {
	load_plugin_textdomain( 'loginlockdown', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}