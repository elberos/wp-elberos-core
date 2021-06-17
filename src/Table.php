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
	}
	
	
	
	/**
	 * Init struct
	 */
	function initStruct()
	{
		$this->struct = $this->createStruct();
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
		return 10;
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
		$field = $this->struct->getField($column_name);
		if ($field and isset($field['column_value']))
		{
			return call_user_func_array($field['column_value'], [$this->struct, $item]);
		}
		return esc_html( isset($item[$column_name]) ? $item[$column_name] : '' );
	}
	
	
	
	/**
	 * Column buttons
	 */
	function column_buttons($item)
	{
		$page_name = $this->get_page_name();
		
		$actions = array
		(
			'edit' => sprintf(
				'<a href="?page=' . $page_name . '&action=edit&id=%s">%s</a>',
				$item['id'], 
				__('Edit', 'elberos-core')
			),
			/*
			'delete' => sprintf(
				'<a href="?page=' . $page_name . '&action=show_delete&id=%s">%s</a>',
				$item['id'],
				__('Delete', 'elberos-core')
			),*/
		);
		
		return $this->row_actions($actions, true);
	}
	
	
	
	/**
	 * Process bulk action
	 */
	function process_bulk_action()
	{
		global $wpdb;
		$table_name = $this->get_table_name();

		if ($this->current_action() == 'trash')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("update $table_name set is_deleted=1 WHERE id IN($ids)");
			}
		}
		
		if ($this->current_action() == 'notrash')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("update $table_name set is_deleted=0 WHERE id IN($ids)");
			}
		}
		
		if ($this->current_action() == 'delete')
		{
			$ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
			}
		}
	}
	
	
	
	/**
	 * Process item
	 */
	function process_item($item)
	{
		$item = $this->struct->processItem($item);
		return $item;
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
	 * CSS
	 */
	function css()
	{
	}
	
	
	
	/**
	 * Display table
	 */
	function display_table()
	{
		$this->initStruct();
		
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->process_bulk_action();
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
	 * Display add or edit
	 */
	function display_add_or_edit()
	{
	}
	
	
	
	/**
	 * Display
	 */
	function display()
	{
		$action = $this->current_action();
		$this->css();
		if ($action == 'add' or $action == 'edit')
		{
			$this->display_add_or_edit();
		}
		else
		{
			$this->display_table();
		}
	}
}