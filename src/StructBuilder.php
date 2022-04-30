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


class StructBuilder
{
	public $entity_name = "";
	public $action = "";
	public $fields = [];
	public $form_fields = [];
	public $table_fields = [];
	
	
	/**
	 * Get entity name
	 */
	public static function getEntityName()
	{
		return "";
	}
	
	
	
	/**
	 * Create instance
	 */
	public static function create($action, $init = null)
	{
		/* Create struct */
		$class_name = get_called_class();
		$struct = new $class_name();
		$struct->action = $action;
		$struct->entity_name = static::getEntityName();
		
		/* Init */
		$struct->init();
		
		/* Init */
		if ($init && is_callable($init))
		{
			$struct = $init($struct);
		}
		
		/* Apply filter */
		$struct = apply_filters("elberos_struct_builder", $struct);
		
		return $struct;
	}
	
	
	
	/**
	 * Init struct
	 */
	public function init()
	{
	}
	
	
	
	/**
	 * Set action
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}
	
	
	
	/**
	 * Get field
	 */
	public function getField($field_name)
	{
		return isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
	}
	
	
	
	/**
	 * Add field
	 */
	public function addField($field)
	{
		$api_name = $field['api_name'];
		$this->fields[$api_name] = $field;
		return $this;
	}
	
	
	
	/**
	 * Edit field
	 */
	public function editField($field_name, $arr)
	{
		if (isset($this->fields[$field_name]))
		{
			foreach ($arr as $key => $value)
			{
				$this->fields[$field_name][$key] = $value;
			}
		}
		return $this;
	}
	
	
	
	/**
	 * Remove field
	 */
	public function removeField($field_name)
	{
		unset( $this->fields[$field_name] );
		return $this;
	}
	
	
	
	/**
	 * Add table field
	 */
	public function addTableField($field_name, $field_name_before = "")
	{
		if (!in_array($field_name, $this->table_fields))
		{
			if ($field_name == "")
			{
				$this->table_fields[] = $field_name;
			}
			else
			{
				$pos = array_search($field_name_before, $this->table_fields);
				if ($pos !== false)
				{
					array_splice($this->table_fields, $pos + 1, 0, [$field_name]);
				}
			}
		}
		return $this;
	}
	
	
	
	/**
	 * Add table field
	 */
	public function addFormField($field_name, $field_name_before = "")
	{
		if (!in_array($field_name, $this->form_fields))
		{
			if ($field_name_before == "")
			{
				$this->form_fields[] = $field_name;
			}
			else
			{
				$pos = array_search($field_name_before, $this->form_fields);
				if ($pos !== false)
				{
					array_splice($this->form_fields, $pos + 1, 0, [$field_name]);
				}
			}
		}
		return $this;
	}
	
	
	
	/**
	 * Set table fields
	 */
	public function setTableFields($fields)
	{
		$this->table_fields = $fields;
		return $this;
	}
	
	
	
	/**
	 * Set table fields
	 */
	public function setFormFields($fields)
	{
		$this->form_fields = $fields;
		return $this;
	}
	
	
	
	/**
	 * Remove table field
	 */
	public function removeTableField($field_name)
	{
		$pos = array_search($field_name, $this->table_fields);
		if ($pos !== false) unset($this->table_fields[$pos]);
		return $this;
	}
	
	
	
	/**
	 * Remove form field
	 */
	public function removeFormField($field_name)
	{
		$pos = array_search($field_name, $this->form_fields);
		if ($pos !== false) unset($this->form_fields[$pos]);
		return $this;
	}
	
	
	
	/**
	 * Default fields
	 */
	public function getDefault()
	{
		$res = [];
		foreach ($this->fields as $field)
		{
			$api_name = isset($field["api_name"]) ? $field["api_name"] : "";
			$default = isset($field["default"]) ? $field["default"] : "";
			$virtual = isset($field["virtual"]) ? $field["virtual"] : false;
			if ($virtual) continue;
			$res[$api_name] = $default;
		}
		return $res;
	}
	
	
	
	/**
	 * Get value
	 */
	public function getValue($item, $field_name)
	{
		$action = $this->action;
		if (isset($this->fields[$field_name]))
		{
			$field = $this->fields[$field_name];
			$value = ($item != null) ? (isset($item[$field_name]) ? $item[$field_name] : "") : "";
			$default = isset($field["default"]) ? $field["default"] : "";
			if ($value === "") $value = $default;
			return $value;
		}
		return "";
	}
	
	
	
	/**
	 * Get column value
	 */
	public function getColumnValue($item, $field_name)
	{
		$field = $this->getField($field_name);
		$value = $this->getValue($item, $field_name);
		
		if ($field)
		{
			if (isset($field['column_value']))
			{
				return call_user_func_array($field['column_value'], [$this, $item]);
			}
			if (isset($field['type']) && ($field['type'] == 'select' || $field['type'] == 'select_input_value'))
			{
				$options = isset( $field['options'] ) ? $field['options'] : [];
				$option = \Elberos\find_item($options, "id", $value);
				if ($option)
				{
					$value = $option['value'];
				}
			}
		}
		
		return $value;
	}
	
	
	
	/**
	 * Get select value
	 */
	public function getSelectOption($field_name, $value)
	{
		$field = $this->getField($field_name);
		$options = isset( $field['options'] ) ? $field['options'] : [];
		$option = \Elberos\find_item($options, "id", $value);
		if ($option)
		{
			return $option;
		}
		return null;
	}
	
	
	
	/**
	 * Update data
	 */
	public function update($item, $data)
	{
		foreach ($this->form_fields as $field_name)
		{
			$field = isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
			if (!$field) continue;
			if (!isset($data[$field_name])) continue;
			$virtual = isset($field["virtual"]) ? $field["virtual"] : false;
			if ($virtual) continue;
			$item[$field_name] = $data[$field_name];
		}
		return $item;
	}
	
	
	
	/**
	 * Process item
	 */
	public function processItem($item)
	{
		$res = [];
		
		/* Get value */
		foreach ($this->form_fields as $field_name)
		{
			$field = isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
			if (!$field) continue;
			
			/* Skip virtual */
			$virtual = isset($field["virtual"]) ? $field["virtual"] : false;
			if ($virtual) continue;
			
			$res[ $field_name ] = $this->getValue($item, $field_name);
		}
		
		/* Process item */
		foreach ($this->form_fields as $field_name)
		{
			$field = isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
			if (!$field) continue;
			
			/* Skip virtual */
			$virtual = isset($field["virtual"]) ? $field["virtual"] : false;
			if ($virtual) continue;
			
			/* Process item */
			if (isset($field["process_item"]))
			{
				$res = call_user_func_array($field["process_item"], [$this, $item, $res]);
			}
			else
			{
				$res = $this->processItemField($field, $item, $res);
			}
		}
		
		$params =
		[
			"res" => $res,
			"item" => $item,
			"struct" => $this,
		];
		
		$params = apply_filters("elberos_struct_process_item", $params);
		$res = $params["res"];
		
		return $res;
	}
	
	
	
	/**
	 * Render fields
	 */
	public function renderForm($item = [], $action = "")
	{
		foreach ($this->form_fields as $api_name)
		{
			$field = isset($this->fields[$api_name]) ? $this->fields[$api_name] : null;
			if ($field == null) continue;
			
			$form_show = isset($field["form_show"]) ? $field["form_show"] : true;
			$form_show_add = isset($field["form_show_add"]) ? $field["form_show_add"] : true;
			$form_show_edit = isset($field["form_show_edit"]) ? $field["form_show_edit"] : true;
			$label = isset($field["label"]) ? $field["label"] : "";
			
			if (!$form_show) continue;
			if (!$form_show_add and $action == "add") continue;
			if (!$form_show_edit and $action == "edit") continue;
			
			/* Row style */
			$style_row = "";
			if (isset($field["php_style"]))
			{
				$php_style_res = call_user_func_array($field["php_style"], [$this, $field, $item]);
			}
			else
			{
				$php_style_res = $this->phpFormStyleField($field, $item);
			}
			
			if (isset($php_style_res["row"]))
			{
				$row = array_map
				(
					function ($k, $v)
					{
						return $k . ": " . esc_attr($v);
					},
					array_keys($php_style_res["row"]),
					$php_style_res["row"]
				);
				$style_row = "style='" . implode(";", $row) . "'";
			}
			
			?>
			<div class="web_form_row" data-name="<?= esc_attr($api_name) ?>" <?= $style_row ?>>
				
				<div class="web_form_label"><?= esc_html($label) ?></div>
				
				<?php $this->renderFormField($field, $item); ?>
				
				<div class="web_form_field_result" data-name="<?= esc_attr($api_name) ?>" data-default="&nbsp;">&nbsp;</div>
			</div>
			<?php
		}
	}
	
	
	
	/**
	 * Render js
	 */
	public function renderFormField($field, $item)
	{
		$readonly = "";
		$api_name = $field["api_name"];
		$type = isset($field["type"]) ? $field["type"] : "";
		$value = isset($item[$api_name]) ? $item[$api_name] : "";
		$default = isset($field["default"]) ? $field["default"] : "";
		$options = isset($field["options"]) ? $field["options"] : [];
		$params = isset($field["params"]) ? $field["params"] : [];
		$placeholder = isset($field["placeholder"]) ? $field["placeholder"] : "";
		$show_select_value = isset($field["show_select_value"]) ? $field["show_select_value"] : true;
		
		if ($value === "") $value = $default;
		
		if (isset($field["readonly"]) and $field["readonly"])
		{
			$readonly = "readonly='readonly'";
		}
		
		$form_render = isset($field["form_render"]) ? $field["form_render"] : null;
		$min_height = isset($params["min-height"]) ? $params["min-height"] : "200px";
		$field_id = "value_" . $api_name . "_" . mt_rand(0, 999999);
		
		if ($form_render) { call_user_func_array($form_render, [$this, $field, $item]);
		?>
		
		<?php } else if ($type == "input") { ?>
		<input id="<?= $field_id ?>" type="text" class="web_form_input web_form_value web_form_input--text"
			placeholder="<?= esc_attr($placeholder) ?>" <?= $readonly ?>
			name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>" />
			
		<?php } else if ($type == "password") { ?>
		<input id="<?= $field_id ?>" type="password" class="web_form_input web_form_value web_form_input--text"
			placeholder="<?= esc_attr($placeholder) ?>" <?= $readonly ?>
			name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>" />
		
		<?php } else if ($type == "textarea") { ?>
		<textarea id="<?= $field_id ?>" type="text" class="web_form_input web_form_value web_form_input--textarea"
			style="min-height: <?= $min_height ?>;"
			placeholder="<?= esc_attr($placeholder) ?>" name="<?= esc_attr($api_name) ?>"
			data-name="<?= esc_attr($api_name) ?>" <?= $readonly ?> ></textarea>
		<script>
			<?= $field_id ?>.value = <?= json_encode($value) ?>;
		</script>
		
		<?php } else if ($type == "ckeditor") { ?>
		<textarea id="<?= $field_id ?>" type="text" class="ckeditor-small" style="min-height: <?= $min_height ?>;"
			placeholder="<?= esc_attr($placeholder) ?>" name="<?= esc_attr($api_name) ?>"
			data-name="<?= esc_attr($api_name) ?>" <?= $readonly ?> ></textarea>
		<script>
			<?= $field_id ?>.value = <?= json_encode($value) ?>;
		</script>
		
		<?php } else if ($type == "select") { ?>
		<select id="<?= $field_id ?>" type="text" class="web_form_input web_form_value web_form_input--select"
			placeholder="<?= esc_attr($placeholder) ?>"
			name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>"
			<?= $readonly ?>>
				
				<?php if ($show_select_value){ ?>
				<option value="">Выберите значение</option>
				<?php } ?>
				
				<?php foreach ($options as $option){
					$selected = "";
					if ($value == $option['id']) $selected = "selected";
				?>
				<option <?= $selected ?> value="<?= esc_attr($option['id']) ?>">
					<?= esc_html($option['value']) ?>
				</option>
				<?php } ?>
				
		</select>
		
		<?php } else if ($type == "select_input_value") { ?>
		
		<?php $value_text = $this->getColumnValue($item, $api_name); ?>
		
		<input id="<?= $field_id ?>" type="text" class="web_form_input web_form_value web_form_input--text"
			placeholder="<?= esc_attr($placeholder) ?>" <?= $readonly ?>
			name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value_text) ?>" />
		
		<?php } else if ($type == "captcha") { ?>
		<div class="web_form_captcha">
			<span class="web_form_captcha_item web_form_captcha_item_img">
				<img class="elberos_captcha_image" src="/api/captcha/create/?_=<?= time() ?>">
			</span>
			<span class="web_form_captcha_item web_form_captcha_item_text">
				
				<input type="text" class="web_form_input web_form_value web_form_input--text"
					placeholder="<?= esc_attr($placeholder) ?>" <?= $readonly ?>
					name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>"
					value="<?= esc_attr($value) ?>" />
			</span>
		</div>
		
		<?php
		}
		else
		{
			do_action('elberos_struct_builder_render_form_field', $this, $field, $item);
		}
	}
	
	
	
	/**
	 * Render js
	 */
	public function renderJS($item = [])
	{
		?>
		<script>
		function change_form_<?= $this->entity_name ?>()
		{
			var $form = jQuery(".web_form_<?= $this->entity_name ?>");
			<?php
			foreach ($this->form_fields as $api_name)
			{
				$field = isset($this->fields[$api_name]) ? $this->fields[$api_name] : null;
				if ($field == null) continue;
				
				/* js change */
				if (isset($field["js_change"]))
				{
					echo call_user_func_array($field["js_change"], [$this, $field, $item]) . "\n";
				}
				else
				{
					echo $this->jsFormChange($field, $item);
				}
			}
			?>
		}
		<?php if (!is_admin()){ echo "onJQueryLoaded(function(){\n"; } ?>
			change_form_<?= $this->entity_name ?>();
			jQuery(".web_form_<?= $this->entity_name ?> .web_form_value").change(function(){
				change_form_<?= $this->entity_name ?>();
			});
		<?php if (!is_admin()){ echo "});"; } ?>
		</script>
		<?php
	}
	
	
	
	/**
	 * Process item field
	 */
	public function processItemField($field, $item, $res)
	{
		return $res;
	}
	
	
	
	/**
	 * PHP Style
	 */
	public function phpFormStyleField($field, $item)
	{
		return [];
	}
	
	
	
	/**
	 * JS script
	 */
	public function jsFormChange($field, $item)
	{
		return "";
	}
}