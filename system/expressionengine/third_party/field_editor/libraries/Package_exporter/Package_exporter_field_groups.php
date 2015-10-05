<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package		Field Editor
 * @author		Vector Media Group
 * @copyright	Copyright (c) 2015, Vector Media Group
 */

class Package_exporter_field_groups extends Package_exporter_driver
{
	public static $master_node = 'field_group';
	
	public static $sub_nodes = array(
		'field' => NULL,
	);
	
	public static function export($group_ids)
	{
		$EE =& get_instance();
		
		$field_groups = array();
		
		if ($group_ids)
		{
			$field_group_query = $EE->db->where_in('group_id', $group_ids)
						    ->get('field_groups');

			$field_group_rows = $field_group_query->result_array();

			if ($field_group_query->num_rows() > 0)
			{
				foreach ($field_group_rows as $field_group_row)
				{
					$field_group_id = $field_group_row['group_id'];
					
					unset($field_group_row['group_id'], $field_group_row['site_id']);
	
					$field_query = $EE->db->where('group_id', $field_group_id)
							      ->order_by('field_order')
							      ->get('channel_fields');
					
					foreach ($field_query->result_array() as $field_row)
					{
						$field_id = $field_row['field_id'];
	
						unset($field_row['field_id'], $field_row['group_id'], $field_row['site_id']);
						
						if (method_exists(__CLASS__, 'export_'.$field_row['field_type']))
						{
							$field_row[$field_row['field_type']] = base64_encode(serialize(call_user_func(array(__CLASS__, 'export_'.$field_row['field_type']), $field_id, $field_row)));
						}

						$field_row['column_type'] = self::get_column_type('channel_data', 'field_id_'.$field_id);
	
						$field_group_row['field'][] = $field_row;
					}
					
					$field_groups[] = $field_group_row;
				}
			}
		}
		
		return $field_groups;
	}

	/**
	 * Get a column type definition for the specified column
	 * @param  string $table
	 * @param  string $column
	 * @return string
	 */
	private static function get_column_type($table, $column)
	{
		$EE =& get_instance();

		$table = $EE->db->dbprefix($table);

		$column = $EE->db->escape($column);

		$query = $EE->db->query("SHOW COLUMNS FROM `$table` WHERE Field = $column");

		$column_type = $query->row('Type') ? $query->row('Type') : 'TEXT';

		if ($query->row('Null') === 'NO')
		{
			$column_type .= ' NOT NULL';
		}

		if ($query->row('Default') !== NULL && $query->row('Default') !== 'NULL')
		{
			$column_type .= ' DEFAULT '.$EE->db->escape($query->row('Default'));
		}

		if ($query->row('Extra') === 'auto_increment')
		{
			$column_type .= ' AUTO_INCREMENT';
		}

		$query->free_result();

		return $column_type;
	}
	
	public static function export_matrix($field_id, $field_row)
	{
		$EE =& get_instance();
		
		$query = $EE->db->where('field_id', $field_id)
				->order_by('col_order', 'asc')
				->get('matrix_cols');
		
		if ($query->num_rows() === 0)
		{
			return array();
		}
		
		$result = $query->result_array();

		$query->free_result();

		foreach ($result as &$row)
		{
			$row['column_type'] = self::get_column_type('matrix_data', 'col_id_'.$row['col_id']);
		}

		return $result;
	}
        public static function export_grid($field_id)
	{
		$EE =& get_instance();
		
		$query = $EE->db->where('field_id', $field_id)
				->order_by('col_order', 'asc')
				->get('grid_columns');
		
		if ($query->num_rows() === 0)
		{
			return array();
		}
		
		$result = $query->result_array();

		$query->free_result();

		foreach ($result as &$row)
		{
			$row['column_type'] = self::get_column_type('channel_grid_field_'.$row['field_id'], 'col_id_'.$row['col_id']);
		}

		return $result;
	}
}