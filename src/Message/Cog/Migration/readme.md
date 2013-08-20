# Migrations

By default, migrations should be stored in `cog://migrations/`.


## Commands

<!--
**Make a new migration class**

```
Usage:
 bin/cog migrate:make create_foo_table

Options:
 --create		Create a table
 --update		Update a table
```
-->

**Run migrations**

```
Usage:
 bin/cog migrate

Options:
 --path="…"		Run the migrations in this path
```

**Rollback the last migration**

```
Usage:
 bin/cog migrate:rollback
```

**Reset database to original state - rollback all migrations**

```
Usage:
 bin/cog migrate:reset
```

**Refresh database to latest state - reset all, then migrate all**

```
Usage:
 bin/cog migrate:refresh
```



## Migrations

Your migration should have a unique file and class name representing what action the migration takes.

The `down()` method should take the exact opposite action as the `up()` method to ensure when rolled back the database is reset to the state it was before the migration was run.

**Example Migration**

```php
<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class CreateUserTable extends Migration
{

	public function up()
	{
		$this->run('
			CREATE TABLE IF NOT EXISTS
				user (
					user_id INT NOT NULL AUTO_INCREMENT,
					email VARCHAR (255),
					password VARCHAR (255),
					PRIMARY KEY (user_id)
				)
		');
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				user
		');
	}

}
```

<!--
When you run the `migrate:make` command one of the follow stub classes will be created in `/migrations/###_foo.php` with a timestamp prefix for uniqueness.


**Blank**

`bin/cog migrate:make foo`


```
class ###Foo extends Migration {

	public function up()
	{
		$this->run('');
	}
	
	public function down()
	{
		$this->run('');
	}
	
}
```

**Create**

`bin/cog migrate:make create_foo_table --create`

```
class ###CreateFooTable extends Migration {

	public function up()
	{
		$this->run('
			CREATE TABLE foo (
				id INT(11),
				PRIMARY KEY (id)
			)
		');
		
	}
	
	public function down()
	{
		$this->run('DROP TABLE foo');
	}

}
```

**Update**

`bin/cog migrate:make update_foo_table --update`

```
class ###UpdateFooTable extends Migration {
	
	public function up()
	{
		$this->run('
			ALTER TABLE foo
		');
	}
	
	public function down()
	{
		$this->run('
			ALTER TABLE foo
		');
	}
	
}
```
-->


## Non Database Migrations

Migrations can be used for different situations, not just databases.


### Adapters

Create a new adapter as below:

```
Migration/
	Adapter/
		MyAdapter/
			Create.php
			Delete.php
			Loader.php
			Migration.php

```

`MyAdapter\Create` and `MyAdapter\Delete` handle logging migration operations in the database.

`MyAdapter\Migration` should have a `run($command)` method that takes the single `$command` value and runs the operation.

For example, you could write `FileCache\Migration` which takes `$command` as a string and stores it in a file, or `KVCache\Migration` which takes `$command` as an array and serializes it into a file.