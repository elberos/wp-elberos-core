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
	
	public $fields = [];
	
	
	public function addField($field)
	{
		$api_name = $field['api_name'];
		$this->fields[$api_name] = $field;
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
	 * Process item
	 */
	public function processItem($item)
	{
		foreach ($this->fields as $field)
		{
			$process_item = isset($field["process_item"]) ? $field["process_item"] : null;
			if ($process_item)
			{
				$item = $process_item($item);
			}
		}
		return $item;
	}
	
	
	
	/**
	 * Render fields
	 */
	public function renderForm($item, $action = "")
	{
		foreach ($this->fields as $field)
		{
			$show = isset($field["show"]) ? $field["show"] : true;
			$show_add = isset($field["show_add"]) ? $field["show_add"] : true;
			$show_edit = isset($field["show_edit"]) ? $field["show_edit"] : true;
			$api_name = isset($field["api_name"]) ? $field["api_name"] : "";
			$label = isset($field["label"]) ? $field["label"] : "";
			$type = isset($field["type"]) ? $field["type"] : "";
			
			if (!$show) continue;
			if (!$show_add and $action == "add") continue;
			if (!$show_edit and $action == "edit") continue;
			if ($api_name == "") continue;
			
			$value = isset($item[$api_name]) ? $item[$api_name] : "";
			
			$options = isset($field["options"]) ? $field["options"] : [];
			$placeholder = isset($field["placeholder"]) ? $field["placeholder"] : "";
			$show_select_value = isset($field["show_select_value"]) ? $field["show_select_value"] : true;
			
			?>
			<div class="web_form__row" data-name="<?= esc_attr($api_name) ?>">
				
				<div class="web_form__label"><?= esc_html($label) ?></div>
				
				<?php if ($type == "input") { ?>
				<input type="text" class="web_form_input web_form_value" placeholder="<?= esc_attr($placeholder) ?>"
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
}