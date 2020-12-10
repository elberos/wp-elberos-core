<?php

/*!
 *  Elberos User Cabinet
 *
 *  (c) Copyright 2019-2020 "Ildar Bikmamatov" <support@elberos.org>
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


namespace Elberos\Delivery;


if ( !class_exists( Log::class ) ) 
{

class Log
{
	public static function show()
	{
		$table = new Log_Table();
		$table->display();		
	}
}


class Log_Table extends \WP_List_Table 
{
	
	function __construct()
	{
		global $status, $page;

		parent::__construct(array(
			'singular' => 'elberos-core',
			'plural' => 'elberos-core',
		));
	}
	
	function get_table_name()
	{
		global $wpdb;
		return $wpdb->prefix . 'elberos_delivery';
	}
	
	// Вывод значений по умолчанию
	function get_default()
	{
		return array(
			'id' => 0,
			'worker' => '',
			'plan' => '',
			'status' => '',
			'dest' => '',
			'title' => '',
			'gmtime_plan' => '',
			'gmtime_send' => '',
			'error' => '',
		);
	}
		
	// Колонки таблицы
	function get_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />', 
			'worker' => __('Worker', 'elberos-core'),
			'plan' => __('План', 'elberos-core'),
			'status' => __('Статус', 'elberos-core'),
			'dest' => __('Dest', 'elberos-core'),
			'title' => __('Title', 'elberos-core'),
			'date' => __('Дата', 'elberos-core'),
			'error' => __('Ошибка', 'elberos-core'),
			'buttons' => __('', 'elberos-core'),
		);
		return $columns;
	}
	
	// Сортируемые колонки
	function get_sortable_columns()
	{
		$sortable_columns = array(
		);
		return $sortable_columns;
	}
	
	// Действия
	function get_bulk_actions()
	{
		return [];
	}
	
	// Вывод каждой ячейки таблицы
	function column_default($item, $column_name)
	{
		return isset($item[$column_name]) ? $item[$column_name] : '';
	}
	
	// Заполнение колонки cb
	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']
		);
	}
	
	// Status
	function column_status($item)
	{
		if ($item["status"] == 0) return "Запланировано";
		if ($item["status"] == 1) return "Отправлено";
		if ($item["status"] == 2) return "В процессе";
		if ($item["status"] == -1) return "Ошибка";
		return "";
	}
	
	// Dest
	function column_dest($item)
	{
		$dest = json_decode($item["dest"]);
		if (gettype($dest) == 'array')
		{
			return implode(", ", $dest);
		}
		return "";
	}
	
	// Date add
	function column_gmtime_add($item)
	{
		return \Elberos\wp_from_gmtime($item['gmtime_add']);
	}
	
	// Date plan
	function column_gmtime_plan($item)
	{
		return \Elberos\wp_from_gmtime($item['gmtime_plan']);
	}
	
	// Date send
	function column_gmtime_send($item)
	{
		return \Elberos\wp_from_gmtime($item['gmtime_send']);
	}
	
	// Date
	function column_date($item)
	{
		if ($item['status'] == 1) return \Elberos\wp_from_gmtime($item['gmtime_send']);
		return \Elberos\wp_from_gmtime($item['gmtime_plan']);
	}
	
	// Колонка name
	function column_buttons($item)
	{
		$actions = array(
			'view' => sprintf(
				'<a href="?page=elberos-delivery-log&action=view&id=%s">%s</a>',
				$item['id'], 
				__('Просмотр', 'elberos-core')
			),
		);
		return $this->row_actions($actions, true);
	}
	
	// Создает элементы таблицы
	function prepare_items()
	{
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$per_page = 10; 

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
	   
		$this->process_bulk_action();

		$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");
		$paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
		$orderby = "gmtime_add";
		$order = "asc";
		
		$sql = $wpdb->prepare
		(
			"SELECT t.* FROM $table_name as t
			ORDER BY $orderby $order LIMIT %d OFFSET %d",
			$per_page, $paged * $per_page
		);
		$this->items = $wpdb->get_results($sql, ARRAY_A);

		$this->set_pagination_args(array(
			'total_items' => $total_items, 
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page) 
		));
	}
	
	
	function process_bulk_action()
	{
	}
	
	// Валидация значений
	function item_validate($item)
	{
		return false;
	}
	
	function process_item($item)
	{
		return $item;
	}
	
	function after_process_item($action, $success_save, $item)
	{
		global $wpdb;
		
		$item_id = $item['id'];
		if ($success_save)
		{
		}
	}
	
	function css()
	{
	}
	
	function display_view()
	{
		global $wpdb;
		
		$message = "";
		$notice = "";
		$item = $this->get_default();
		$item_id = (int) (isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
		$table_name = $this->get_table_name();
		
		if ($item_id > 0)
		{
			$item = $wpdb->get_row
			(
				$wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $item_id), ARRAY_A
			);
		}
		
		?>
		
		<div class="wrap">
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<h1>Статус отправки</h1>
			
			<a type="button" class='button-primary' href='?page=elberos-delivery-log'> Back </a>
			
			<!-- Date -->
			<p>
				<label for="title"><b><?php _e('Дата', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html( $this->column_date($item) ) ?>
			</p>
			
			<!-- Status -->
			<p>
				<label for="title"><b><?php _e('Status', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html( $this->column_status($item) ) ?>
			</p>
			
			<!-- Dest -->
			<p>
				<label for="title"><b><?php _e('Dest', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html( $this->column_dest($item) ) ?>
			</p>
			
			<!-- Worker -->
			<p>
				<label for="plan"><b><?php _e('Worker', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html($item['worker']) ?>
			</p>
			
			<!-- Plan -->
			<p>
				<label for="plan"><b><?php _e('Plan', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html($item['plan']) ?>
			</p>
			
			<!-- Title -->
			<p>
				<label for="title"><b><?php _e('Title', 'elberos-core')?>:</b></label>
			<br>
				<?= esc_html($item['title']) ?>
			</p>
			
			<!-- Message -->
			<p>
				<label for="message"><b><?php _e('Message', 'elberos-core')?>:</b></label>
			<br>
				<textarea readonly style='width: 100%; height: 300px;'><?= esc_html($item['message']) ?></textarea>
			</p>
			
			<a type="button" class='button-primary' href='?page=elberos-delivery-log'> Back </a>
			
		</div>
		
		<?php
	}
	
	
	function display_table()
	{
		$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : "";
		
		$this->prepare_items();
		$message = "";
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php echo get_admin_page_title() ?>
			</h1>
			<hr class="wp-header-end">
			
			<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
			<?php echo $message; ?>
			
			<?php
			// выводим таблицу на экран где нужно
			echo '<form action="" method="POST">';
			parent::display();
			echo '</form>';
			?>

		</div>
		<?php
	}
	
	function display()
	{
		$action = $this->current_action();
		$this->css();
		if ($action == 'view')
		{
			$this->display_view();
		}
		else
		{
			$this->display_table();
		}
	}
	
}

}