<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Interfaced migration driver
 *
 * @package		Migration
 * @author		Oliver Morgan
 * @uses		DBForge
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
class Migration_Interface extends Migration {
	
	protected function _model($model)
	{
		// If the model is given as a string, instantiate it.
		if (is_string($model))
		{
			$model = Model::factory($model);
		}
		
		// If the model does not implement the migratable interface, this driver can't control it.
		if ( ! $model instanceof Model_Migratable)
		{
			// Throw an error if it doesnt.
			throw new Kohana_Exception('Model :mdl does not implement the Model_Migratable interface', array(
				':mdl' => (string)$model
			));
		}
		
		// Return the model object
		return $model;
	}
	
	protected function _db()
	{
		// Returns the interfaced object's database
		return $this->_model->db();
	}
	
	protected function _tables()
	{
		//
		return $this->_model->migration_tables();
	}
	
} // End Migration_Interface