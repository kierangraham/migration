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
	
	/**
	 * The sprig model object.
	 * 
	 * @var	Sprig
	 */
	protected $_model;
	
	protected function _get_model($model)
	{
		if (is_string($model))
		{
			return Sprig::factory($model);
		}
		elseif ($model instanceof Sprig)
		{
			return $model;
		}
		else
		{
			throw new Kohana_Exception('Invalid model :model given to sprig driver.', array(
				':model'	=> (string) $model
			));
		}
	}
	
	protected function _get_tables()
	{
		$tables = array();
		
		$table = new Database_Table($this->_db);
		$table->name = $this->_model->table();
		
		$model_pks = is_array($this->_model->pk()) ? $this->_model->pk() : array($this->_model->pk());
		
		foreach ($this->_model->fields() as $field)
		{
			if ($field->in_db)
			{
				if ($field->unique)
				{
					$table->add_constraint(
						new Database_Constraint_Unique($field->column)
					);
				}
				
				$column = current($this->_get_columns($field, $table));
				
				$table->add_column($column);
			}
			else
			{
				if ($field instanceof Sprig_Field_ManyToMany)
				{
					$pivot = new Database_Table($this->_db);
					$pivot->name = $field->through;
						
					$pivot->add_column($this->_get_columns(
						new Sprig_Field_BelongsTo(array(
							'model'	=> $field->model
					)), $pivot ));
					
					$pivot->add_column($this->_get_columns(
						new Sprig_Field_BelongsTo(array(
							'model'	=> inflector::singular($this->_model->table())
					)), $pivot ));
					
					$pivot->add_constraint(new Database_Constraint_Primary(
						array_keys($pivot->columns()), $pivot->name
					));
						
					$tables[] = $pivot;
				}
			}
		}
		
		$table->add_constraint(
			new Database_Constraint_Primary($model_pks, $table->name)
		);
		
		$tables[] = $table;
		
		return $tables;
	}
	
	protected function _get_database()
	{
		return Database::instance($this->_model->db());
	}
	
	/**
	 * Gets the database columns associated with the field.
	 * 
	 * @param	Sprig_Field	The sprig field.
	 * @param	Database_Table	The parent database table.
	 * @return	array
	 */
	private function _get_columns(Sprig_Field $field, Database_Table $table)
	{
		if ($field instanceof Sprig_Field_BelongsTo)
		{
			$references = $this->_get_model($field->model);
			
			$pks = is_array($references->pk()) ? $references->pk() : array($references->pk());
			
			$columns = array();
			
			foreach($pks as $pk)
			{
				$foreign_field = $references->field($pk);
				
				$column = current($this->_get_columns($foreign_field, $table));
				
				$column->name = Inflector::singular($references->table()).'_'.$pk;
				
				if ($column instanceof Database_Column_Int)
				{
					$column->is_auto_increment = FALSE;
				}
				
				$columns[] = $column;
			}
			
			return $columns;
		}
		elseif ($field instanceof Sprig_Field_Char)
		{
			if ($field instanceof Sprig_Field_Text)
			{
				$column = Database_Column::factory($table, 'blob', $field->column);
			}
			else
			{
				$column = Database_Column::factory($table, 'varchar', $field->column);
				$column->parameters = isset($column->max_length) ? $column->max_length : 45;
			}
		}
		elseif ($field instanceof Sprig_Field_Integer)
		{
			$column = Database_Column::factory($table, 'int', $field->column);
			
			$column->is_auto_increment = $field instanceof Sprig_Field_Auto;
		}
		elseif ($field instanceof Sprig_Field_Boolean)
		{
			$column = Database_Column::factory($table, 'bool', $field->column);
		}
		
		$column->default = $field->default;
		$column->is_nullable = $field->null;
		$column->name = $field->column;
		
		return array($column);
	}

} // End Migration_Sprig