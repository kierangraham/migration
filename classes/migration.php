<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database migration manager.
 *
 * @package		Migraion
 * @author		Oliver Morgan
 * @uses		Kohana 3.0 Database
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Migration {
	
	/**
	 * Creates a new migration manager for a model.
	 *
	 * @param   object	The model object.
	 * @return  Migration	The appropriate migration driver.
	 */
	public static function factory($model)
	{
		// Get the migration driver
		$class = 'Migration_'.ucfirst(get_parent_class($model));
		
		// Check if the class exists
		if(class_exists($class))
		{
			// Return a new instance of the driver interface
			return new $class($model);
		}
		else
		{
			// The driver could not be found, throw an error
			throw new Kohana_Exception('Model mdl is not supported.', 
				array('mdl' => $class));
		}
	}
	
	// The model we're working with
	protected $_model;
	
	/**
	 * Creates the new migration object with the specified model.
	 *
	 * @param   object	The model object.
	 */
	protected function __construct($model)
	{
		// Sets the model.
		$this->_model = $model;	
	}
	
	/**
	 * Creates a new migration manager for a model.
	 *
	 * @param   object	The model object.
	 * @return  Migration	The appropriate migration driver.
	 */
	public function remove()
	{
		// Get the table name
		$name = $this->get_table()->name;
		
		// Get any associated tables from the database
		$table =  $db->get_tables(TRUE, $name);
		
		// If it exists drop it
		if (is_object($table))
		{
			$table->drop();
			
			// It was dropped
			return TRUE;
		}
		
		// No table was found with that name
		return FALSE;
	}
	
	/**
	 * Syncs the model with the database.
	 *
	 * @return  void
	 */
	public function sync()
	{
		// Get the model's active database
		$db = $this->get_database();
		
		// Start the transaction
		$db->query(NULL, 'BEGIN');
		
		try
		{
			// Get a table object from the model
			$table = $this->get_table();
			
			// Get a list of tables with the same name as the model
			$tables = $db->get_tables(TRUE, $table->name);
			
			// We have a hit, time to update it where necessary.
			if (is_object($tables))
			{
				// Array of columns that do not exist in the model
				$columns = $table->columns(TRUE);
				
				// Loop through each column within the model
				foreach($table->columns(TRUE) as $name => $column)
				{
					// Remove the column as it exists in the model
					unset($columns[$column->name]);
					
					// Check if the column exists in the table
					if(count($tables->columns(TRUE, $name)) == 1)
					{
						// If it does, then we alter it
						DB::alter($tables)
							->modify($column, $name)
							->execute();
					}
					else
					{
						// Otherwise we create it
						$tables->add_column($column);
					}
				}
				
				foreach($columns as $column)
				{
					// Drop any redundant columns
					DB::drop($column)
						->execute();
				}
			}
			else
			{
				// There was no existing table, so just create it
				$table->create();
			}
		}
		catch( Exception $e)
		{
			// If an error occurs then rollback the transaction
			$db->query(NULL, 'ROLLBACK');
			
			// And throw the error to be caught elsewhere
			throw $e;
		}
		
		// Everything completed according to plan, commit the transaction
		$db->query(NULL, 'COMMIT');
	}
	
	/**
	 * Gets the database used by the model.
	 *
	 * @return  Database	The database object.
	 */
	abstract public function get_database();
	
	/**
	 * Generates a normalised table object from the model.
	 * 
	 * @param	object	The model to generate a table object from.
	 * @return  Database_Table	The table object.
	 */
	abstract public function get_table($model);
	
} // END Migration