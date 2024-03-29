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
	 * Return true if is_deleted enabled
	 */
	function is_enable_deleted_filter()
	{
		return true;
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
		$value = $this->struct->getColumnValue($item, $column_name);
		if ($value instanceof RawString) return $value;
		return esc_html( $value );
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
	 * Get form id
	 */
	function get_form_id($default = 0)
	{
		$id_name = $this->get_form_id_name();
		return (isset($_REQUEST[ $id_name ]) ? $_REQUEST[ $id_name ] : $default);
	}
	
	
	
	/**
	 * Get bulk id
	 */
	function get_bulk_id($default = [])
	{
		$id_name = $this->get_form_id_name();
		return (isset($_REQUEST[ $id_name ]) ? $_REQUEST[ $id_name ] : $default);
	}
	
	
	
	/**
	 * Get form id name
	 */
	function get_form_id_name()
	{
		return "id";
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
			$ids = $this->get_bulk_id();
			if (!empty($ids) && gettype($ids) == 'array')
			{
				$sql = $wpdb->prepare
				(
					"update " . $table_name . " set is_deleted=1 WHERE id IN(" .
						implode(",", array_fill(0, count($ids), "%d")) . ")",
					$ids
				);
				$wpdb->query($sql);
			}
		}
		
		if ($action == 'notrash')
		{
			$ids = $this->get_bulk_id();
			if (!empty($ids) && gettype($ids) == 'array')
			{
				$sql = $wpdb->prepare
				(
					"update " . $table_name . " set is_deleted=0 WHERE id IN(" .
						implode(",", array_fill(0, count($ids), "%d")) . ")",
					$ids
				);
				$wpdb->query($sql);
			}
		}
		
		if ($action == 'delete')
		{
			$ids = $this->get_bulk_id();
			if (!empty($ids) && gettype($ids) == 'array')
			{
				$sql = $wpdb->prepare
				(
					"DELETE FROM " . $table_name . " WHERE is_deleted=1 AND id IN(" .
						implode(",", array_fill(0, count($ids), "%d")) . ")",
					$ids
				);
				$wpdb->query($sql);
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
	 * Returns true if show filter
	 */
	function is_show_filter()
	{
		//var_dump( "elberos_table_is_show_filter_" . get_called_class() );
		list($_,$result) = apply_filters("elberos_table_is_show_filter_" . get_called_class(), [$this,false]);
		return $result;
	}
	
	
	
	/**
	 * Returns filter elements
	 */
	function get_filter()
	{
		return [];
	}
	
	
	
	/**
	 * Show filter item
	 */
	function show_filter_item($item_name)
	{
		do_action("elberos_table_show_filter_item_" . get_called_class(), [$this, $item_name]);
	}
	
	
	
	/**
	 * Show filter
	 */
	function show_filter()
	{
		$filter = $this->get_filter();
		
		list($_,$filter) = apply_filters("elberos_table_get_filter_" . get_called_class(), [$this, $filter]);
		
		foreach ($filter as $item_name)
		{
			$this->show_filter_item($item_name);
		}
		
		do_action("elberos_table_show_filter_" . get_called_class(), $this);
	}
	
	
	
	/**
	 * Show filter wrap
	 */
	function show_filter_wrap()
	{
		?>
		<div style="clear: both;"></div>
		<div class="extra_tablenav_filter">
			<div class="extra_tablenav_filter_items">
				<?php $this->extra_tablenav_before() ?>
				<?php $this->show_filter() ?>
				<?php $this->extra_tablenav_after() ?>
			</div>
			<div class="extra_tablenav_filter_search">
				<input type="button" class="button dosearch" value="Поиск">
			</div>
		</div>
		<div style="clear: both;"></div>
		<?php
	}
	
	
	
	/**
	 * JQ filter sub
	 */
	function jq_filter_sub()
	{
		return
		[
			"is_deleted",
			"order",
			"orderby",
		];
	}
	
	
	
	// Фильтр
	function extra_tablenav( $which )
	{
		if ( $which == "top" && $this->is_show_filter())
		{
			?>
			<?php $this->show_filter_wrap() ?>
			<?php $jq_filter_sub = $this->jq_filter_sub(); ?>
			<script>
			function extra_tablenav_filter_dosearch()
			{
				var filter = [];
				<?php
					foreach ($jq_filter_sub as $sub_name)
					{
						$sub_value = isset($_GET[$sub_name]) ? $_GET[$sub_name] : "";
						if ($sub_value != "")
						{
							echo "filter.push('" . $sub_name . "=" . esc_attr($sub_value) . "');\n";
						}
						
					}
				?>
				jQuery(".extra_tablenav_filter .web_form_value").each(function(){
					var name = jQuery(this).attr("name");
					var value = jQuery(this).val();
					if (value != "") filter.push(name + "=" + encodeURIComponent(value));
				});
				filter = filter.join("&");
				if (filter != "") filter = "&" + filter;
				document.location.href = 'admin.php?page=<?= esc_attr($this->get_page_name()) ?>'+filter;
			}
			jQuery(".extra_tablenav_filter .dosearch").click(function(){
				extra_tablenav_filter_dosearch();
			});
			jQuery(".extra_tablenav_filter .web_form_value").keydown(function(e){
				if (e.keyCode == 13)
				{
					e.preventDefault();
					extra_tablenav_filter_dosearch();
					return false;
				}
			});
			</script>
			<?php
		}
	}
	
	
	
	/**
	 * Process items params
	 */
	function prepare_table_items_filter($params)
	{
		/* Is deleted */
		if ($this->is_enable_deleted_filter())
		{
			if (isset($_GET["is_deleted"]) && ($_GET["is_deleted"] == "true" || $_GET["is_deleted"] == "1"))
			{
				$params["where"][] = "t.is_deleted=1";
			}
			else
			{
				$params["where"][] = "t.is_deleted=0";
			}
		}
		
		list($_,$params) = apply_filters("elberos_table_prepare_items_params_" . get_called_class(), [$this,$params]);
		
		return $params;
	}
	
	
	
	/**
	 * Process item before
	 */
	function process_item_before(&$item, $old_item, $action)
	{
	}
	
	
	
	/**
	 * Process item after
	 */
	function process_item_after($item, $old_item, $action, $success)
	{
	}
	
	
	
	/**
	 * Process item end
	 */
	function process_item_end($old_item, $action, $success)
	{
	}
	
	
	
	/**
	 * Prepare table items
	 */
	function prepare_table_items()
	{
		$params =
		[
			"fields" => [ "t.*" ],
			"where" => [],
			"args" => [],
			"order_by" => "id desc",
			"join" => [],
			"page" => isset($_GET['paged']) ? max(0, intval($_GET['paged']) - 1) : 0,
			"per_page" => $this->per_page(),
		];
		
		$params = $this->prepare_table_items_filter($params);
		
		list($items, $total_items, $pages, $page) = \Elberos\wpdb_query
		([
			"table_name" => $this->get_table_name(),
			"fields" => implode(",", $params['fields']),
			"where" => implode(" and ", $params['where']),
			"join" => implode(" ", $params['join']),
			"args" => $params['args'],
			"order_by" => $params['order_by'],
			"page" => $params['page'],
			"per_page" => $params['per_page'],
		]);
		
		$this->items = $items;
		$this->set_pagination_args(array(
			'total_items' => $total_items, 
			'per_page' => $params['per_page'],
			'total_pages' => ceil($total_items / $params['per_page']) 
		));
	}
	
	
	
	/**
	 * Get item
	 */
	function do_get_item()
	{
		global $wpdb;
		
		$this->form_item_id = $this->get_form_id();
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
				$this->form_item = $this->do_get_item_query($this->form_item_id);
			}
		}
	}
	
	
	
	/**
	 * Get item query
	 */
	function do_get_item_query($item_id)
	{
		global $wpdb;
		$table_name = $this->get_table_name();
		$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d limit 1", $item_id);
		return $wpdb->get_row($sql, ARRAY_A);
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
		$nonce_action = static::class;
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
		$process_item = $this->struct->update($old_item, stripslashes_deep($_POST));
		$process_item = $this->struct->processItem($process_item);
		$process_item = $this->process_item($process_item, $old_item);
		
		$action = "";
		
		/* Create */
		if ($item_id == 0)
		{
			$action = "create";
			
			/* Before */
			$res = apply_filters("elberos_wp_list_table_process_item_before", [
				"this" => $this,
				"process_item" => $process_item,
				"old_item" => $old_item,
				"action" => 'create',
			]);
			$process_item = $res["process_item"];
			
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
			$res = apply_filters("elberos_wp_list_table_process_item_before", [
				"this" => $this,
				"process_item" => $process_item,
				"old_item" => $old_item,
				"action" => 'update'
			]);
			$process_item = $res["process_item"];
			
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
		
		/* After */
		$this->process_item_after($process_item, $old_item, $action, $success);
		$res = apply_filters("elberos_wp_list_table_process_item_after", [
			"this" => $this,
			"process_item" => $process_item,
			"old_item" => $old_item,
			"action" => $action,
			"success" => $success
		]);
		
		/* Get new value */
		if ($item_id > 0)
		{
			$this->form_item = $this->do_get_item_query($item_id);
		}
		
		/* End */
		$this->process_item_end($old_item, $action, $success);
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
			width: 60%;
			margin-top: 20px;
		}
		.add_or_edit_form_buttons{
			width: 60%;
			text-align: center;
		}
		.tablenav{
			height: auto !important;
		}
		.tablenav .table_filter select, .tablenav .table_filter input{
			vertical-align: middle;
			max-width: 150px;
		}
		.extra_tablenav_filter_items{
			display: flex;
			clear: both;
			padding-top: 10px;
			padding-bottom: 10px;
		}
		.extra_tablenav_filter select, .extra_tablenav_filter input{
			display: inline-block;
			vertical-align: top;
			max-width: 150px;
		}
		.extra_tablenav_filter .web_form_value{
			display: block;
			margin: 1px;
		}
		.elberos_form .web_form__label{
			margin-bottom: 5px;
		}
		.elberos_form .web_form_input{
			width: 100%;
			max-width: 100%;
		}
		</style>
		<script>
		var $ = jQuery;
		</script>
		<?php
	}
	
	
	
	/**
	 * Returns table title
	 */
	function get_table_title()
	{
		return get_admin_page_title();
	}
	
	
	
	/**
	 * Display table add button
	 */
	function display_table_add_button()
	{
		$page_name = $this->get_page_name();
		?>
		<a href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=' . $page_name . '&action=add');?>"
			class="page-title-action"
		>
			<?php _e('Add new', 'elberos-core')?>
		</a>
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
				<?= $this->get_table_title() ?>
			</h1>
			<?php $this->display_table_add_button() ?>
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
			
			<form id="elberos_form" class="elberos_form <?= esc_attr("web_form_" . $this->struct->entity_name) ?>"
				method="POST"
			>
				<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(static::class)?>"/>
				<?php $this->display_form_content() ?>
				<?php $this->display_form_buttons() ?>
			</form>
		</div>
		
		<?php
	}
	
	
	
	/**
	 * Display form content
	 */
	function display_form_content()
	{
		if ($this->form_item == null)
		{
			return;
		}
		?>
		<div class="add_or_edit_form">
			<?php $this->display_form() ?>
		</div>
		<?php
	}
	
	
	
	/**
	 * Display form
	 */
	function display_form()
	{
		if ($this->form_item == null)
		{
			return;
		}
		$this->display_form_id();
		echo $this->struct->renderForm($this->form_item, $this->form_item['id'] > 0 ? "edit" : "add");
		echo $this->struct->renderJS($this->form_item, $this->form_item['id'] > 0 ? "edit" : "add");
	}
	
	
	
	function display_form_id()
	{
		$id = $this->form_item_id;
		$id_name = $this->get_form_id_name();
		?>
		<input type='hidden' name='<?= esc_attr($id_name) ?>' value='<?= $id ?>' />
		<?php
	}
	
	
	
	/**
	 * Display form buttons
	 */
	function display_form_buttons()
	{
		if ($this->form_item == null)
		{
			return;
		}
		?>
		<div class="add_or_edit_form_buttons">
			<input type="submit" class="button-primary" value="<?= esc_attr('Сохранить', 'elberos-core')?>" >
		</div>
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
		do_action("elberos_table_display_css_after_" . static::class, [$this]);
		
		$this->display_init();
		do_action("elberos_table_init_after_" . static::class, [$this]);
		
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