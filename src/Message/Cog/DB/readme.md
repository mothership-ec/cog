# DB component

## Usage

### Create a connection

	// Create a connection
	$connection = new \Message\Cog\DB\MySQL\Connection(array(
		'host'	=> '127.0.0.1',
		'user'	=> 'root',
		'password' => 'cheese',
		'db'	=> 'test',
	));
	
### Run a query
	
	$query = new Query($connection);
	
	$result = $query->run("SELECT iso_code, name, population, gdp FROM countries");
	
### Work with the results

Calling `Query::run` returns an instance of the `Result` class. This class is flexible in that it's a normal object and has accessor methods to make getting at the query result data easier but it also acts as an array

	foreach($result as $row) {
		var_dump($row->iso_code, $row->name, $row->population, $row->gdp);
	}
	
	var_dump($result->value());
	
### Transactions