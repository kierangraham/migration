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
		return $this->_model->db();
	}
	
	public function get_table($model)
	{
		// Gets the table object with the database
		$table = new Database_Table(NULL, $this->_database);
		
		// Set the name of the table
		$table->name = $model->table();
		
		// Get all the fields from the model
		$fields = $model->fields();
		
		// Loop through each field in the model
		foreach($fields as $field)
		{
			// We're only interested in fields within the database
			if($field->in_db)
			{
				// Get the field's base class.
				$class = get_class($field);
				
				// Switch through each class
				switch($class)
				{
					// We're dealing with an auto-increment int field						
					case 'Sprig_Field_Auto':
						$column = new Database_Column_Int;
						$column->is_auto_increment = TRUE;
						$column->datatype = array('int');
						break;
						
					// This is a boolean field
					case 'Sprig_Field_Boolean':
						$column = new Database_Column_Bool;
						$column->datatype = 'boolean';
						break;
						
					// Basic string fields (varchar)
					case 'Sprig_Field_Password':
					case 'Sprig_Field_Image':
					case 'Sprig_Field_Enum':
					case 'Sprig_Field_Country':
					case 'Sprig_Field_Email':
					case 'Sprig_Field_Char':
						$column = new Database_Column_String;
						$column->datatype = 'varchar';
						$column->parameters = array(isset($column->max_length) ? $column->max_length : 45);
						break;
						
					// Basic floating point field
					case 'Sprig_Field_Float':
						$column = new Database_Column_Float;
						$column->datatype = 'float';
						break;
						
					// Integer field
					case 'Sprig_Field_Integer':
						$column = new Database_Column_Int;
						$column->datatype = 'int';
						break;
						
					case 'Sprig_Field_Text':
						$column = new Database_Column_String;
						$column->parameters = 65535;
						$column->datatype = 'varchar';
						break;
				}
				
				// Set the other basic properties.
				$column->is_unique = $field->unique;
				$column->default = $field->default;
				$column->is_nullable = $field->null;
				$column->is_primary = $field->primary;
				$column->name = $field->column;
				
				// Add the column to the table.
				$table->add_column($column);
			}
		}
		
		// Return the table.
		return $table;
	}
}