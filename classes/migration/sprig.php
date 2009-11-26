<?php defined('SYSPATH') OR die('No direct access allowed.');

class Migration_Sprig extends Migration {
	
	public function get_database()
	{
		return $this->_model->db();
	}
	
	public function get_table($model)
	{
		$table = new Database_Table($this->_database);
		$table->name = $model->table();
		
		$fields = $model->fields();
		
		foreach($fields as $field)
		{
			if($field->in_db)
			{
				$class = get_parent_class($field);
				$class = $class == 'Sprig_Field' ? get_class($field) : $class;
				
				switch($class)
				{
					case 'Sprig_Field_Char':
						$column = new Database_Column_String;
						$column->datatype = array('char',
							$column->max_length
						);
						
					case 'Sprig_Field_Integer':
						$column = new Database_Column_Int;
						$column->datatype = array('int');
						
					case 'Sprig_Field_Boolean':
						$column = new Database_Column_Bool;
						$column->datatype = array('bool');
						
					case 'Sprig_Field_Float':
						$column = new Database_Column_Float;
						$column->datatype = array('float');
				}
				
				$column->is_unique = $field->unique;
				$column->default = $field->default;
				$column->is_nullable = $field->null;
				$column->is_primary = $field->primary;
				$column->name = $field->column;
				
				$table->add_column($column);
			}
		}
		
		return $table;
	}
}