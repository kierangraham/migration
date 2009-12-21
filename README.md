# Migration Module for Kohana 3.0

## Installation

### Requirements

* Kohana 3.x
* Model Migration Driver (Ships with sprig, http://www.github.com/shadowhand/sprig)
* DBForge Module (http://www.github.com/ollym/dbforge)

### Instructions

* Add the module to your list of modules in your bootsrap.
* Happy coding!

## User Guide

### Drivers

#### Sprig
I have included a native sprig driver that allows you to sync/remove sprig models from the database.

To use the sprig driver, simply specify the type as 'sprig' and register the module as a sprig model object.

### Quick Start
The migration module is meant to be an easy way to sync models with database schemas, below is a quick start guide of how to use it. I will be using sprig in my example.

	$migration = Migration::factory(Sprig::factory('user'), 'sprig');

Will create a new instance of a migration object using the sprig driver.

	$migration->sync();

That code will sync the model, without forcing column alterations. That means that if the column exists in the database already, the migration manager will not attempt to modify / update it. Due to the limitations in introspection techniques, it's impossible to compare columns from the database directly with models to determine if they need updating or not. Modifying a column will not cause you to loose data.

	$migration->sync(TRUE);

In contrast to the code above, this will sync the model, forcing updates to columns that already exist in the database.

	$migration->remove();

This will drop the table associated with the model. This may be removed in versions to come as its simply an alias of the `DB::Drop()` method in DBForge.

### Advanced Methods

Certainly in development its useful to have your models kept in sync with the database the whole time. Allowing you to control your database's schema using models. To do this, add the following bit of code to your boostrap after all the modules are loaded.

	// Find every model in your application
	foreach(Kohana::list_files('classes/model') as $uri => $path)
	{
		// Extract the class name from the relative path
		$class = ucfirst(str_replace(array('classes/model', DIRECTORY_SEPARATOR, EXT), '', $uri));
	
		// Use the migration module to sync model
		Migration::factory(Sprig::factory($class), 'sprig')->sync(TRUE);
	}

This assumes that all your models use sprig as your model engine driver. For other drivers, simply change the `Sprig::factory()` and `'sprig'` parameters.

## Creating Drivers

Creating drivers for the migration manager is easy. However you must fit a certain criteria to allow your model engine to be effective.

### Requirements

Below is a list of requirements, if you understand the migration process, these will seem trivial. Essencially your model must model the database rather than the other way round. Which is why ORM would never work for this process.

* Your model must not involve retrieving schema information from the database.
* Your model must store records of primary / composite keys.
* Your model must be able to provide details of every field contained within it.

### Template Class

Your driver must extend the migration class, containing some abstract methods that you have to implament within your driver. Below is a basic setup of a class which would be located in `migration/driver.php`

	class Migration_Driver extends Migration {
		
		protected function _get_model($name) { }
		protected function _get_database() { }
		public function get_table($model) { }

	} // END Migration_Driver

### Abstract Methods

These are abstract methods defined in the migration class which you must extend in your driver.

* `_get_model($name` This method returns the model object from a given identifier or name.

* `_get_database()` This method is important for extracting the database associated with the model. If your model doesnt support this, then just return `Database::instance();`.

* `get_table($model)` This is the main method, which involves converting your model object into a Database_Table object. For further information on the Database_Table API, see the DBForge documentation. Also see the sprig driver for an example.
