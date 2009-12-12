<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Migratable model interface.
 *
 * @package		Migration
 * @author		Oliver Morgan
 * @uses		DBForge
 * @copyright	(c) 2009 Oliver Morgan
 * @license		MIT
 */
interface Model_Migratable {
	
	/**
	 * Returns a column object for the field.
	 * 
	 * @return	Database_Column	The column object.
	 */
	public function get_column();
	
}