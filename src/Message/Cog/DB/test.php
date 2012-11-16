<?php

require 'Exception.php';
require 'Query.php';
require 'ResultIterator.php';
require 'ResultArrayAccess.php';
require 'Adapter/ConnectionInterface.php';
require 'Adapter/ResultInterface.php';
require 'Result.php';

require 'Transaction.php';

require 'Adapter/MySQLi/Connection.php';
require 'Adapter/MySQLi/Result.php';

require 'Adapter/Faux/Connection.php';
require 'Adapter/Faux/Result.php';

$connection = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
	'host'     => '127.0.0.1',
	'user'     => 'root',
	'password' => 'cheese',
	'db'	   => 'classicmodels',
));

$query = new \Message\Cog\DB\Query($connection);

$result = $query->run("SELECT * FROM products");


$connection = new \Message\Cog\DB\Adapter\Faux\Connection;
$connection->setResult(array(
	array(
		'forename' => 'James',
		'surname' => 'Moss',
		'age'	=> 24,
	),
	array(
		'forename' => 'Joe',
		'surname' => 'Holdcroft',
		'age'	=> 20,
	),
	array(
		'forename' => 'Danny',
		'surname' => 'Hannah',
		'age'	=> 25,
	),		
));

$query->setConnection($connection);

$result = $query->run("SELECT * FROM products");

var_dump(count($result));

foreach($result as $row) {
	var_dump($row);
}

var_dump($result[2]);

var_dump($result[5]);