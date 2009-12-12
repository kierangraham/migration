<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Sprig migration driver.
 *
 * @package		Migraion
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Migration_Sprig extends Migration {
	
	public function get_database()
	{
		// Returns the database of the model
		return Database::instance($this->_model->db());
	}
	
	public function get_table($model)
	{
		// Get the database object
		$db = $this->get_database();
		
		// Gets the table object with the database
		$table = new Database_Table();
		
		// Set the name of the table
		$table->name = $model->table();
		
		// Get all the fields from the model
		$fields = $model->fields();
		
		// Unique keys
		$indexes = array();
		
		// Loop through each field in the model
		foreach($fields as $field)
		{
			// We're only interested in fields within the database
			if($field->in_db)
			{
				// If the field is unique, add it to the index
				if($field->unique)
				{
					$indexes[$field->column] = $field->column;
				}
				
				// Get the field's base class.
				$class = get_class($field);
				
				// Switch through each class
				switch($class)
				{
					// We're dealing with an int field						
					case 'Sprig_Field_Auto':
					case 'Sprig_Field_Integer':
					{
						$column = Database_Column::factory($table, 'int', $field->column);
						$column->is_auto_increment = TRUE;
						break;
					}
						
					// This is a boolean field
					case 'Sprig_Field_Boolean':
					{
						$column = Database_Column::factory($table, 'bool', $field->column);
						break;
					}
					
					case 'Sprig_Field_Timestamp':
					{
						$column = Database_Column::factory($table, 'timestamp', $field->column);
						break;	
					}
						
					// Basic string fields (varchar)
					case 'Sprig_Field_Password':
					case 'Sprig_Field_Image':
					case 'Sprig_Field_Enum':
					case 'Sprig_Field_Country':
					case 'Sprig_Field_Email':
					case 'Sprig_Field_Char':
					{
						$column = Database_Column::factory($table, 'varchar', $field->column);
						$column->parameters = isset($column->max_length) ? $column->max_length : 45;
						break;
					}					
						
					case 'Sprig_Field_Text':
					{
						$column = Database_Column::factory($table, 'blob', $field->column);
						break;
					}
					
				}
				
				// Set the other basic properties.
				$column->default = $field->default;
				$column->is_nullable = (bool) $field->null;
				$column->name = $field->column;
				
				// Add the column to the table.
				$table->add_column($column);
			}
		}
		
		// Get the primary keys
		$keys = $model->pk();
		
		// If there is just one key, still add it to an array
		if ( ! is_array($keys))
		{
			$keys = array($keys);
		}
		
		// Add the primary keys
		$table->add_constraint(new Database_Constraint_Primary($keys));
		
		// Return the table.
		return $table;
	}
}