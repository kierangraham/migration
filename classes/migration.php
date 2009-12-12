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
abstract class Migration {
	
	/**
	 * Creates a new migration manager for a model.
	 *
	 * @param   object	The model object.
	 * @return  Migration	The appropriate migration driver.
	 */
	public static function factory($model, $type = NULL)
	{
		// If the user has not specified a type, we'll take a guess.
		if($type === NULL)
		{
			$type = get_parent_class($model);
		}
		
		// Get the migration driver
		$class = 'Migration_'.ucfirst($type);
		
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
	
	// The table object generated from the model
	protected $_table;
	
	/**
	 * Creates the new migration object with the specified model.
	 *
	 * @param   object	The model object.
	 */
	protected function __construct($model)
	{
		// Sets the model.
		$this->_model = $model;
		
		// Set the table object generated from the model.
		$this->_table = $this->get_table($model);
	}
	
	/**
	 * Drops the model's table from the database.
	 *
	 * @return	void
	 */
	public function remove()
	{
		// Drop the table
		DB::drop('table', $this->_table->name)
			->execute($this->_table->database);
			
		// And return the current object for chaining
		return $this;
	}
	
	/**
	 * Syncs the model with the database.
	 *
	 * @return  void
	 */
	public function sync($force = FALSE)
	{
		// Get the model's active database
		$db = $this->get_database();
		
		// Get a list of tables with the same name as the model
		$table = Database_Table::instance($this->_table->name, $db);
		
		// We have a hit, time to update it where necessary.
		if ($table)
		{
			// Get a list of columns from the database table
			$columns = $table->columns();
			
			// Loop through each column within the model
			foreach($this->_table->columns() as $name => $column)
			{
				// Check if the column exists in the table
				if(isset($columns[$column->name]))
				{
					if($force === TRUE)
					{
						// If it does, then we alter it
						DB::alter($table->name)
							->modify($column->compile())
							->execute($table->database);
					}
				}
				else
				{
					// Otherwise we create it
					$table->add_column($column);
				}
				
				// We have processed the column and it exists in the model.
				unset($columns[$column->name]);
			}
			
			// Do not delete columns if the operaction isnt forced.
			if($force === TRUE)
			{	
				// Loop through anything we have left
				foreach($columns as $name => $column)
				{
					// Drop any redundant columns
					$column->drop();
				}
			}
		}
		else
		{
			// There was no existing table, so just create it
			$this->_table->create();
		}
		
		// And return the current object for chaining
		return $this;
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