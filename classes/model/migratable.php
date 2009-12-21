<?php defined('SYSPATH') OR die('No direct access allowed.');
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
	
	public function get_columns();
	
} // End Model_Migratable