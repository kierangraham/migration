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
	
	protected function _get_model($model)
	{
		return $model;
	}
	
	protected function _get_database()
	{
		$this->_model->get_database();
	}
	
	protected function _get_tables()
	{
		$this->_model->get_tables();
	}
	
} // End Migration_Interface