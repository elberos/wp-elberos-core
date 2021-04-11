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


class Update
{
	
	/**
	 * Update db field
	 */
	static function update_field($field, $image_name, $path)
	{
		$field[$image_name] =
		[
			"path" => $path,
			"inc" => time(),
		];
		return $field;
	}
	
	
	
	/**
	 * Move uploaded image
	 */
	static function move_uploaded_image($abs_path, $tmp_name, $upload_orig_path)
	{
		$abs_upload_path = $abs_path . $upload_orig_path;
		make_dir_by_filename($abs_upload_path);
		if (move_uploaded_file($tmp_name, $abs_upload_path))
		{
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * Resize image
	 */
	static function resize_image($abs_path, $orig_path, $resize_path, $opt)
	{
		$width = $opt['width'];
		$height = $opt['height'];
		$kind = $opt['kind'];
		
		$abs_orig_path = $abs_path . $orig_path;
		$abs_resize_path = $abs_path . $resize_path;
		
		$img = new Image();
		$img->open($abs_orig_path);
		if ($img->isLoaded())
		{
			if ($kind == 'scaleTo') $img->scaleTo($width, $height);
			else if ($kind == 'contain' || $kind == 'cover')
			{
				$pos_x = isset($opt['pos_x']) ? $opt['pos_x'] : 'center';
				$pos_y = isset($opt['pos_y']) ? $opt['pos_y'] : 'center';
				$scale = isset($opt['scale']) ? $opt['scale'] : true;
				if ($kind == 'contain') $img->resizeContain($width, $height, $pos_x, $pos_y, $scale);
				else if ($kind == 'cover') $img->resizeCover($width, $height, $pos_x, $pos_y, $scale);
			}
			$img->save($abs_resize_path);
			$img->destroy();
			unset($img);
		}
		
	}
	
	
	
	/**
	 * Upload image
	 */
	static function wp_upload_image($folder, $item, $field_name, $opt = [])
	{
		global $wpdb;
		
		/* Remove image */
		if (isset($_POST['remove_image']) && $_POST['remove_image'] == $field_name)
		{
			$item[$field_name] = "";
			return $item;
		}
		
		if (!isset($item['id'])) return $item;
		if (!isset($_FILES[$field_name])) return $item;
		
		$file_name = $_FILES[$field_name]['name'];
		$tmp_name = $_FILES[$field_name]['tmp_name'];
		if ($tmp_name == "") return $item;
		if ($file_name == "") return $item;
		
		$ext = mb_extension($file_name);
		$field = @json_decode( isset($item[$field_name]) ? $item[$field_name] : "", true );
		$id = $item['id'];
		
		/* Get path */
		$arr = split_number($id, 3, 2);
		$abs_path = remove_last_slash(ABSPATH);
		$upload_dir = "/wp-content/uploads";
		$upload_prefix = $upload_dir . "/" . $folder . "/" . implode("/", $arr);
		
		/* Save orig image */
		$upload_orig_path = $upload_prefix . "-" . $field_name . "-orig." . $ext;
		$field = static::update_field($field, "orig", $upload_orig_path);
		static::move_uploaded_image($abs_path, $tmp_name, $upload_orig_path);
		
		/* Save Thumbnail */
		$upload_file_path = $upload_prefix . "-" . $field_name . "-thumb." . $ext;
		$field = static::update_field($field, "thumb", $upload_file_path);
		static::resize_image
		(
			$abs_path,
			$upload_orig_path,
			$upload_file_path,
			[
				"width" => 200,
				"height" => 200,
				"kind" => "scaleTo",
			]
		);
		
		if ($opt != null && isset($opt['images']))
		{
			foreach ($opt['images'] as $image)
			{
				$image_name = $image['name'];
				$upload_path = $upload_prefix . "-" . $field_name . "-" . $image_name . "." . $ext;
				$field = static::update_field($field, $image_name, $upload_path);
				static::resize_image
				(
					$abs_path,
					$upload_orig_path,
					$upload_path,
					$image
				);
			}
		}
		
		$item[$field_name] = json_encode($field);
		return $item;
	}
	
	
	
	/**
	 * Get Image
	 */
	static function get_image($item, $field_name, $type)
	{
		$field = @json_decode( isset($item[$field_name]) ? $item[$field_name] : "", true );
		if ($field == null) return "";
		
		$image = isset($field[$type]) ? $field[$type] : null;
		if ($image == null) return "";
		
		$path = isset($image['path']) ? $image['path'] : "";
		if ($path == "") return "";
		
		$inc = isset($image['inc']) ? $image['inc'] : "1";
		return $path . "?_=" . $inc;
	}
	
	
	
	/**
	 * Intersect
	 */
	static function intersect($item, $arr)
	{
		$res = [];
		foreach ($item as $key => $value)
		{
			if (in_array($key, $arr))
			{
				$res[$key] = $value;
			}
		}
		return $res;
	}
	
	
	
	/**
	 * Save or update
	 */
	static function wp_save_or_update($obj, $nonce_action)
	{
		global $wpdb;
		
		$action = $obj->current_action();
		$default = $obj->get_default();
		$table_name = $obj->get_table_name();
		$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : false;
		$action = $obj->current_action();
		$item = $default;
		$item_id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
		
		if ($item_id > 0)
		{
			$item = $wpdb->get_row
			(
				$wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $item_id), ARRAY_A
			);
		}
		
		$action = "";
		$message = "";
		$notice = "";
		
		if (!$item)
		{
			$notice = __('Элемент не найден', 'template');
			return
			[
				"item" => $item,
				"message" => $message,
				"notice" => $notice,
			];
		}
		
		if ($nonce == false)
		{
			return
			[
				"item" => $item,
				"message" => $message,
				"notice" => $notice,
			];
		}
		
		if (!wp_verify_nonce($nonce, $nonce_action))
		{
			$notice = __('Неверный токен', 'template');
			return
			[
				"item" => $item,
				"message" => $message,
				"notice" => $notice,
			];
		}
		
		$post = stripslashes_deep($_POST);
		$item = shortcode_atts($default, $item);
		$item = shortcode_atts($item, $post);
		
		// Check item
		if (method_exists($obj, 'item_validate'))
		{
			$item_valid = $obj->item_validate($item);
			if ($item_valid !== true)
			{
				return
				[
					"item" => $item,
					"message" => $message,
					"notice" => $item_valid,
				];
			}
		}
		
		// Process item
		$process_item = $item;
		if (method_exists($obj, 'process_item'))
		{
			$process_item = $obj->process_item($item);
			foreach ($process_item as $key => $value) $item[$key] = $value;
		}
		
		$success_save = false;
		
		/* Create */
		if ($item_id == 0)
		{
			$result = $wpdb->insert($table_name, $process_item);
			$item['id'] = $wpdb->insert_id;
			$item_id = $wpdb->insert_id;
			
			if ($result)
			{
				$success_save = true;
				if (method_exists($obj, 'upload_images'))
				{
					$item = $obj->upload_images($item);
					//$diff = static::diff($item, $new_item);
					if ($item)
					{
						$result = $wpdb->update($table_name, $item, array('id' => $item_id));
					}
				}
			}
			
			$action = "create";
		}
		
		/* Update */
		else
		{
			if (method_exists($obj, 'upload_images'))
			{
				$item = $obj->upload_images($item);
			}
			$result = $wpdb->update($table_name, $process_item, array('id' => $item_id));
			$success_save = true;
			$action = "update";
		}
		
		/* Message */
		if ($success_save)
		{
			$message = __('Успешно обновлено', 'template');
		}
		else
		{
			$notice = __('Ошибка при обновлении элемента', 'template');
		}
		
		/* After process item */
		if (method_exists($obj, 'after_process_item'))
		{
			$obj->after_process_item($action, $success_save, $item);
		}
		
		return
		[
			"item" => $item,
			"message" => $message,
			"notice" => $notice,
		];
	}
}