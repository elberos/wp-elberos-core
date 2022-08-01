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


class RobotsTXT
{
	
	/**
	 * Show
	 */
	static function show()
	{
		$form_notice = "";
		$content = get_blog_option(null, "robots.txt");
		if (isset($_POST["content"]))
		{
			if (\Elberos\check_wp_nonce( basename(__FILE__) ))
			{
				$content = $_POST["content"];
				update_blog_option(null, "robots.txt", $content);
				$message = __('Успешно обновлено', 'elberos-core');
			}
			else
			{
				$form_notice = __('Неверный токен', 'elberos-core');
			}
		}
		
		?>
		<div class="wrap">
			<h1>Robots.txt</h1>
			
			<?php if (!empty($form_notice)): ?>
				<div id="notice" class="error"><p><?php echo $form_notice ?></p></div>
			<?php endif;?>
			<?php if (!empty($message)): ?>
				<div id="message" class="updated"><p><?php echo $message ?></p></div>
			<?php endif;?>
			
			<form id="form" method="POST">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( basename(__FILE__) )?>" />
				<p>
					<label for="content">Content:</label>
				<br>
					<textarea id="content" name="content" type="text" style="width: 60%; min-height: 300px;"><?= esc_html($content) ?></textarea>
				</p>
				<input type="submit" class="button-primary" value="Save">
			</form>
		</div>
		
		<?php
	}
	
	
	
}