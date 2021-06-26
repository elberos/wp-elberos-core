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


class Table extends \Elberos_WP_List_Table
{
	var $struct;
	var $form_item;
	var $form_item_id;
	var $form_notice;
	var $form_message;
	var $context = [];
	
	
	/**
	 * Construct
	 */
	function __construct()
	{
		global $status, $page;

		parent::__construct(array(
			'singular' => $this->get_page_name(),
			'plural' => $this->get_page_name(),
		));
		
		$this->context["site_table"] = $this;
	}
	
	
	
	/**
	 * Create struct
	 */
	static function createStruct()
	{
		return null;
	}
	
	
	
	/**
	 * Init struct
	 */
	function initStruct()
	{
		$this->struct = static::createStruct();
		do_action("elberos_wp_list_table_struct", [$this]);
	}
	
	
	
	/**
	 * Table name
	 */
	function get_table_name()
	{
		return "";
	}
	
	
	
	/**
	 * Page name
	 */
	function get_page_name()
	{
		return "";
	}
	
	
	
	/**
	 * Returns per page
	 */
	function per_page()
	{
		return 20;
	}
	
	
	
	/**
	 * Returns struct
	 */
	function getStruct($item)
	{
		return null;
	}
	
	
	
	/**
	 * Returns columns
	 */
	function get_columns()
	{
		$columns = [];
		if ($this->struct != null)
		{
			$table_fields = $this->struct->table_fields;
			$columns['cb'] = '<input type="checkbox" />';
			foreach ($table_fields as $field_name)
			{
				$field = $this->struct->getField($field_name);
				if ($field)
				{
					$columns[$field_name] = isset($field['label']) ? $field['label'] : '';
				}
			}
			$columns['buttons'] = '';
		}
		return $columns;
	}
	
	
	
	/**
	 * Get default
	 */
	function get_default()
	{
		$res = $this->struct->getDefault();
		return $res;
	}
	
	
	
	/**
	 * Get sortable items
	 */
	function get_sortable_columns()
	{
		return [];
	}
	
	
	
	/**
	 * Column value
	 */
	function column_default($item, $column_name)
	{
		return $this->struct->getColumnValue($item, $column_name);
	}
	
	
	
	/* Заполнение колонки cb */
	function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }
	
	
	
	/**
	 * Column buttons
	 */
	function column_buttons($item)
	{
		$page_name = $this->get_page_name();
		return sprintf
		(
			'<a href="?page=' . $page_name . '&action=edit&id=%s">%s</a>',
			$item['id'], 
			__('Редактировать', 'elberos-core')
		);
	}
	
	
	
	/**
	 * Process bulk action
	 */
	function process_bulk_action()
	{
		global $wpdb;
		
		$action = $this->current_action();
		$table_name = $this->get_table_name();
		
		if ($action == 'trash')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("update $table_name set is_deleted=1 WHERE id IN($ids)");
			}
		}
		
		if ($action == 'notrash')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("update $table_name set is_deleted=0 WHERE id IN($ids)");
			}
		}
		
		if ($action == 'delete')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
			}
		}
		
		if (in_array($action, ['add', 'edit']))
		{
			$this->do_get_item();
			$this->do_save_or_update();
		}
	}
	
	
	
	/**
	 * Item validate
	 */
	function item_validate($item)
	{
		return "";
	}
	
	
	
	/**
	 * Process item
	 */
	function process_item($item, $old_item)
	{
		return $item;
	}
	
	
	
	/**
	 * Process item before
	 */
	function process_item_before($item, $old_item, $action)
	{
	}
	
	
	
	/**
	 * Process item after
	 */
	function process_item_after($item, $old_item, $action, $success)
	{
	}
	
	
	
	/**
	 * Get item
	 */
	function do_get_item()
	{
		global $wpdb;
		
		$table_name = $this->get_table_name();
		$this->form_item_id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
		$this->form_item = null;
		
		/* Create */
		if ($this->form_item_id == 0)
		{		
			$this->form_item = $this->struct->getDefault();
			$this->form_item['id'] = 0;
			$this->form_item_id = 0;
		}
		
		/* Update */
		else
		{
			if ($this->form_item_id > 0)
			{
				$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d limit 1", $this->form_item_id);
				$this->form_item = $wpdb->get_row($sql, ARRAY_A);
			}
		}
	}
	
	
	
	/**
	 * Do save or update
	 */
	function do_save_or_update()
	{
		global $wpdb;
		
		$table_name = $this->get_table_name();
		
		if ($this->form_item == null)
		{
			$this->form_notice = __('Элемент не найден', 'elberos-core');
			return;
		}
		
		/* Check nonce */
		$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : false;
		$nonce_action = basename(__FILE__);
		if ($nonce == false)
		{
			return;
		}
		if (!wp_verify_nonce($nonce, $nonce_action))
		{
			$this->form_notice = __('Неверный токен', 'elberos-core');
			return;
		}
		
		$old_item = $this->form_item;
		$item_id = $this->form_item_id;
		
		/* Item validation */
		$notice = $this->item_validate($old_item);
		if ($notice)
		{
			$this->form_notice = $notice;
			return;
		}
		
		/* Process item */
		$process_item = $this->struct->update($old_item, $_POST);
		$process_item = $this->struct->processItem($process_item);
		$process_item = $this->process_item($process_item, $old_item);
		
		$action = "";
		
		/* Create */
		if ($item_id == 0)
		{
			$action = "create";
			
			/* Before */
			$this->process_item_before($process_item, $old_item, 'create');
			do_action("elberos_wp_list_table_process_item_before", [$this, $process_item, $old_item, 'create']);
			
			/* Request */
			$result = $wpdb->insert($table_name, $process_item);
			$item_id = $wpdb->insert_id;
			
			$process_item['id'] = $item_id;
			$this->form_item_id = $item_id;
			$success = false;
			
			/* Result */
			if ($wpdb->last_error != "")
			{
				$this->form_notice = __('Ошибка базы данных', 'elberos-core');
			}
			else
			{
				$success = true;
				$this->form_message = __('Успешно обновлено', 'elberos-core');
			}
		}
		
		/* Update */
		else
		{
			$action = "update";
			
			/* Before */
			$this->process_item_before($process_item, $old_item, 'update');
			do_action("elberos_wp_list_table_process_item_before", [$this, $process_item, $old_item, 'update']);
			
			/* Request */
			$result = $wpdb->update($table_name, $process_item, array('id' => $item_id));
			$success = false;
			
			/* Result */
			if ($wpdb->last_error != "")
			{
				$this->form_notice = __('Ошибка базы данных', 'elberos-core');
			}
			else
			{
				$success = true;
				$this->form_message = __('Успешно обновлено', 'elberos-core');
			}
		}
		
		/* Get new value */
		if ($item_id > 0)
		{
			$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d limit 1", $item_id);
			$this->form_item = $wpdb->get_row($sql, ARRAY_A);
		}
		
		/* After */
		$this->process_item_after($process_item, $old_item, 'create', $success);
		do_action("elberos_wp_list_table_process_item_after", [$this, $process_item, $old_item, $action, $success]);
	}
	
	
	
	/**
	 * Prepare table items
	 */
	function prepare_table_items()
	{
	}
	
	
	
	/**
	 * Display table sub
	 */
	function display_table_sub()
	{
		$page_name = $this->get_page_name();
		$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : "";
		?>
		<ul class="subsubsub">
			<li>
				<a href="admin.php?page=<?= $page_name ?>"
					class="<?= ($is_deleted != "true" ? "current" : "")?>"  >Все</a> |
			</li>
			<li>
				<a href="admin.php?page=<?= $page_name ?>&is_deleted=true"
					class="<?= ($is_deleted == "true" ? "current" : "")?>" >Корзина</a>
			</li>
		</ul>
		<?php
	}
	
	
	
	/**
	 * Display form sub
	 */
	function display_form_sub()
	{
		$page_name = $this->get_page_name();
		?>
		<br/>
		<a type="button" class='button-primary' href='?page=<?= $page_name ?>'> Back </a>
		<br/>
		<?php
	}
	
	
	
	/**
	 * CSS
	 */
	function display_css()
	{
		?>
		<style>
		.add_or_edit_form{
			margin-top: 20px;
		}
		.add_or_edit_form .web_form__label{
			margin-bottom: 5px;
		}
		.add_or_edit_form .web_form_input{
			width: 100%;
			max-width: 100%;
		}
		</style>
		<?php
	}
	
	
	
	/**
	 * Display table
	 */
	function display_table()
	{
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->prepare_table_items();
		$page_name = $this->get_page_name();
		
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php echo get_admin_page_title() ?>
			</h1>
			<a href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=' . $page_name . '&action=add');?>"
				class="page-title-action"
			>
				<?php _e('Add new', 'elberos-core')?>
			</a>
			<hr class="wp-header-end">
			
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			
			<?php $this->display_table_sub(); ?>
			
			<?php
			echo '<form action="" method="POST">';
			parent::display_table();
			echo '</form>';
			?>

		</div>
		<?php
	}
	
	
	
	/**
	 * Returns form title
	 */
	function get_form_title($item)
	{
		return _e($item['id'] > 0 ? 'Редактировать' : 'Добавить', 'elberos-core');
	}
	
	
	
	/**
	 * Display add or edit
	 */
	function display_add_or_edit()
	{
		$this->display_form_sub();
		
		$item = $this->form_item;
		$item_id = $this->form_item_id;
		$notice = $this->form_notice;
		$message = $this->form_message;
		$page_name = $this->get_page_name();
		
		/* Item not found */
		if ($item == null)
		{
			if (empty($notice))
			{
				$notice = __('Элемент не найден', 'elberos-core');
			}
			?><div class="wrap"><div id="notice" class="error"><p><?php echo $notice ?></p></div></div><?php
			return;
		}
		
		?>
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1><?php _e( $this->get_form_title($item) ); ?></h1>
			
			<?php if (!empty($notice)): ?>
				<div id="notice" class="error"><p><?php echo $notice ?></p></div>
			<?php endif;?>
			<?php if (!empty($message)): ?>
				<div id="message" class="updated"><p><?php echo $message ?></p></div>
			<?php endif;?>
			
			<form id="elberos_form" method="POST">
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
				<input type="hidden" name="id" value="<?php echo $item_id ?>"/>
				<div class="add_or_edit_form" style="width: 60%">
					<? $this->display_form($item) ?>
					<? $this->display_form_buttons($item) ?>
				</div>
			</form>
		</div>
		
		<?php
	}
	
	
	
	/**
	 * Display form
	 */
	function display_form($item)
	{
		echo $this->struct->renderForm($item, $item['id'] > 0 ? "edit" : "add");
		echo $this->struct->renderJS($item, $item['id'] > 0 ? "edit" : "add");
	}
	
	
	
	/**
	 * Display form buttons
	 */
	function display_form_buttons($item)
	{
		?>
		<center>
			<input type="submit" class="button-primary" value="<?= esc_attr('Сохранить', 'elberos-core')?>" >
		</center>
		<?php
	}
	
	
	
	/**
	 * Init
	 */
	function display_init()
	{
		$this->initStruct();
		$this->process_bulk_action();
	}
	
	
	
	/**
	 * Display action
	 */
	function display_action()
	{
		$action = $this->current_action();
		if ($action == 'add' or $action == 'edit')
		{
			$this->display_add_or_edit();
		}
		else
		{
			$this->display_table();
		}
	}
	
	
	
	/**
	 * Display
	 */
	function display()
	{
		$this->display_css();
		$this->display_init();
		$this->display_action();
	}
	
	
	
	/**
	 * Render twig
	 */
	function render($template)
	{
		$twig = \Elberos\create_twig();
		echo \Elberos\twig_render($twig, $template, $this->context);
	}
}