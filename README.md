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

### Interfaced
If you've used the migratable interface, then your model will not need a driver to extract the database modelling information out of it.

To use this, simply don't specify a type at all. 	`Migration::factory('my_model');`

### Quick Start
The migration module is meant to be an easy way to sync models with database schemas, below is a quick start guide of how to use it. I will be using sprig in my example.

	$migration = Migration::factory('user', 'sprig');

Will create a new instance of a migration object using the sprig driver.

	$migration->sync();

The above code will sync the model with the database schema, creating the table if it doesnt exist, otherwise adding, modifying and dropping columns where appropriate.

	$migration->remove();

This will drop all tables associated with the model.

	$migration->rebuild();

This will remove the table if it exists then create it again. As the migration module currently doesnt support constraints this is a useful method for adding new constraints to the table schema.

Note its simply an alias for:

	$migration->remove()->sync();

As all methods are chainable.

## Creating Drivers

Creating drivers for the migration manager is easy. However you must fit a certain criteria to allow your model engine to be effective.
 
The migration module also has added support for migratable models that manage migrations on an individual basis using the migratable interface. This is useful if you want to hardcode table objects processed by the migration module. Obviously this would require you to do it for every model you want to use it on

### The Interface Method
Below is a basic example of how the migratable interface would be setup using an ORM model. Obviously the ORM isn't designed for database modelling, and so a driver cannot be created for it. The interface method can be used instead.

	class Model_MyModel extends ORM implements Model_Migratable 	{
		
		public function get_database() { }

		public function get_tables() { }
		
	}

#### Abstract Methods
These are methods you will need to have working in your model.

`get_database()` This method will return a Database object of the database used by the model.

`get_tables()` Returns an array of tables modelled by the model, if the table is part of a ManyToMany relationship then it would need to return both the pivot table and itself.

### The Driver Method

#### Requirements

Below is a list of requirements, if you understand the migration process, these will seem trivial. Essencially your model must model the database rather than the other way round. Which is why ORM would never work for this process.

* Your model must not involve retrieving schema information from the database.
* Your model must store records of primary / composite keys.
* Your model must be able to provide details of every field contained within it.

#### Template Driver Class

Your driver must extend the migration class, containing some abstract methods that you have to implament within your driver. Below is a basic setup of a class which would be located in `migration/driver.php`

	class Migration_Driver extends Migration {
		
		protected function _get_model($name) { }
		protected function _get_database() { }
		protected function _get_tables() { }

	} // End Migration_Driver

##### Abstract Methods

These are abstract methods defined in the migration class which you must extend in your driver.

* `_get_model($model)` This method returns the model object from a given identifier or name. It would be wise to check if the model parameter is already given as an object.

* `_get_database()` This method is important for extracting the database associated with the model. If your model doesnt support this, then just return `Database::instance();`.

* `_get_tables()` This is the main method, which involves converting your model object into an array of Database_Table objects. For further information on the Database_Table API, see the DBForge documentation. Also see the sprig driver for an example.
