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


class StructBuilder
{
	public $action = "";
	public $item = null;
	public $form_name = "";
	public $fields = [];
	public $show_fields = [];
	
	
	
	/**
	 * Create instance
	 */
	public static function create($form_name, $action, $item)
	{
		/* Create struct */
		$struct = new self();
		$struct->setFormName($form_name);
		$struct->action = $action;
		$struct->item = $item;
		
		/* Apply filter */
		$struct = apply_filters("elberos_struct_builder", $struct);
		
		return $struct;
	}
	
	
	
	/**
	 * Set form name
	 */
	public function setFormName($form_name)
	{
		$this->form_name = $form_name;
		return $this;
	}
	
	
	
	/**
	 * Add field
	 */
	public function addField($field)
	{
		$api_name = $field['api_name'];
		$this->fields[$api_name] = $field;
		if (!in_array($api_name, $this->show_fields)) $this->show_fields[] = $api_name;
		return $this;
	}
	
	
	
	/**
	 * Remove field
	 */
	public function removeField($field_name)
	{
		unset( $this->fields[$field_name] );
	}
	
	
	
	/**
	 * Remove show field
	 */
	public function removeShowField($field_name)
	{
		$pos = array_search($field_name, $this->show_fields);
		if ($pos !== false) unset($this->show_fields[$pos]);
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
	public function getValue($field_name)
	{
		$action = $this->action;
		if (isset($this->fields[$field_name]))
		{
			$field = $this->fields[$field_name];
			$value = ($this->item != null) ? (isset($this->item[$field_name]) ? $this->item[$field_name] : "") : "";
			$default = isset($field["default"]) ? $field["default"] : "";
			if ($value === "") $value = $default;
			return $value;
		}
		return "";
	}
	
	
	
	/**
	 * Process item
	 */
	public function processItem()
	{
		$item = [];
		
		/* Get value */
		foreach ($this->fields as $field)
		{
			$api_name = $field["api_name"];
			
			/* Skip virtual */
			$virtual = isset($field["virtual"]) ? $field["virtual"] : false;
			if ($virtual) continue;
			
			$item[ $api_name ] = $this->getValue($api_name);
		}
		
		/* Process item */
		foreach ($this->fields as $field)
		{
			$api_name = $field["api_name"];
			$process_item = isset($field["process_item"]) ? $field["process_item"] : null;
			if ($process_item)
			{
				$item = $process_item($this, $item);
			}
		}
		
		return $item;
	}
	
	
	
	/**
	 * Render fields
	 */
	public function renderForm()
	{
		$item = $this->item;
		$action = $this->action;
		foreach ($this->show_fields as $api_name)
		{
			$field = isset($this->fields[$api_name]) ? $this->fields[$api_name] : null;
			if ($field == null) continue;
			
			$show = isset($field["show"]) ? $field["show"] : true;
			$show_add = isset($field["show_add"]) ? $field["show_add"] : true;
			$show_edit = isset($field["show_edit"]) ? $field["show_edit"] : true;
			$label = isset($field["label"]) ? $field["label"] : "";
			$type = isset($field["type"]) ? $field["type"] : "";
			
			if (!$show) continue;
			if (!$show_add and $action == "add") continue;
			if (!$show_edit and $action == "edit") continue;
			
			$value = isset($item[$api_name]) ? $item[$api_name] : "";
			$default = isset($field["default"]) ? $field["default"] : "";
			$options = isset($field["options"]) ? $field["options"] : [];
			$placeholder = isset($field["placeholder"]) ? $field["placeholder"] : "";
			$show_select_value = isset($field["show_select_value"]) ? $field["show_select_value"] : true;
			
			if ($value === "") $value = $default;
			
			$style_row = "";
			$php_style_res = [];
			$php_style = isset($field["php_style"]) ? $field["php_style"] : null;
			if (is_callable($php_style))
			{
				$php_style_res = $php_style($this, $field);
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
			<div class="web_form__row" data-name="<?= esc_attr($api_name) ?>" <?= $style_row ?>>
				
				<div class="web_form__label"><?= esc_html($label) ?></div>
				
				<?php if ($type == "input") { ?>
				<input type="text" class="web_form_input web_form_value web_form_input--text"
					placeholder="<?= esc_attr($placeholder) ?>"
					name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>" />
				<?php } ?>
				
				<?php if ($type == "password") { ?>
				<input type="password" class="web_form_input web_form_value web_form_input--text"
					placeholder="<?= esc_attr($placeholder) ?>"
					name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>" />
				<?php } ?>
				
				<?php if ($type == "textarea") { ?>
				<textarea type="text" class="web_form_input web_form_value" placeholder="<?= esc_attr($placeholder) ?>"
					name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" ><?= esc_html($value) ?></textarea>
				<?php } ?>
				
				<?php if ($type == "select") { ?>
				<select type="text" class="web_form_input web_form_value" placeholder="<?= esc_attr($placeholder) ?>"
					name="<?= esc_attr($api_name) ?>" data-name="<?= esc_attr($api_name) ?>" value="<?= esc_attr($value) ?>">
						
						<?php if ($show_select_value){ ?>
						<option>Выберите значение</option>
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
				<?php } ?>
				
				<div class="web_form_field_result" data-name="<?= esc_attr($api_name) ?>" data-default="&nbsp;">&nbsp;</div>
			</div>
			<?php
		}
	}
	
	
	
	/**
	 * Render js
	 */
	public function renderJS()
	{
		?>
		<script>
		function change_form_<?= $this->form_name ?>()
		{
			var $form = jQuery(".web_form_<?= $this->form_name ?>");
			<?php
			foreach ($this->show_fields as $api_name)
			{
				$field = isset($this->fields[$api_name]) ? $this->fields[$api_name] : null;
				if ($field == null) continue;
				
				if (isset($field["js_change"]) and is_callable($field["js_change"]))
				{
					echo $field["js_change"]($this) . "\n";
				}
			}
			?>
		}
		onJQueryLoaded(function(){
			change_form_<?= $this->form_name ?>();
			jQuery("document").on("change", ".web_form_<?= $this->form_name ?> .web_form_value", function(){
				change_form_<?= $this->form_name ?>();
			});
		});
		</script>
		<?php
	}
}