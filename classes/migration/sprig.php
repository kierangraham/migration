<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Sprig migration driver.
 *
 * @package		Migration
 * @author		Oliver Morgan
 * @uses		DBForge
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Migration_Sprig extends Migration {
	
	protected function _get_model($name)
	{
		// If the name is given as an object, this may not be necessary
		if (is_object($name))
		{
			// Check first if the model is valid
			if ( ! $name instanceof Sprig)
			{
				// If not throw an error
				throw new Kohana_Exception('Model :mdl is not a valid Sprig model.', array(
					':mdl'	=> (string) $name
				));
			}
			
			// If it is, just return it as is
			return $name;
		}
		
		// Otherwise just let sprig deal with it
		return Sprig::factory($name);
	}
	
	protected function _get_database()
	{
		// Returns the database of the model
		return Database::instance($this->_model->db());
	}
	
	public function get_table($model)
	{
		// Get the database object
		$db = $this->_get_database();
		
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
					$indexes[$field->column] = $field;
				}
				
				// Check if the field implaments the migratable interface
				if ($field instanceof Model_Migratable)
				{
					// If so, it's going to generate a column itself
					$column = $field->get_column();	
				}
				
				// Check if we're dealing with a character based field
				elseif ($field instanceof Sprig_Field_Char)
				{
					// Check if our character based field is a text field
					if($field instanceof Sprig_Field_Text)
					{
						// If so, we'll give it a blob datatype to be platform independent
						$column = Database_Column::factory($table, 'blob', $field->column);
					}
					else
					{
						// Otherwise we'll just give a varchar witha  default length of 45
						$column = Database_Column::factory($table, 'varchar', $field->column);
						$column->parameters = isset($column->max_length) ? $column->max_length : 45;
					}
				}
				
				// Check if we're dealing with an integer
				elseif ($field instanceof Sprig_Field_Integer)
				{
					// If so, give it the standard int datatype
					$column = Database_Column::factory($table, 'int', $field->column);
					
					// Set the auto_increment value
					$column->is_auto_increment = $field instanceof Sprig_Field_Auto;
				}
				
				// Check if we're dealing with a boolean field
				elseif ($field instanceof Sprig_Field_Boolean)
				{
					// If so, just use the standard bool value
					$column = Database_Column::factory($table, 'bool', $field->column);
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
		
		// Loop through each key index
		foreach($indexes as $name => $field)
		{
			// If the field isnt already a primary key
			if ( ! $field->primary)
			{
				// Add it as a unique constraint
				$table->add_constraint(new Database_Constraint_Unique($name));
			}
		}
		
		// Return the table.
		return $table;
	}
}