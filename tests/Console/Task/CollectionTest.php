<?php

namespace Message\Cog\Test\Console\Task;

use Message\Cog\Console\Task\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testAddingTask()
	{
		$task = new FauxTask('faux');
		
		$collection = new Collection;
		$collection->add($task, 'A short description');

		$this->assertSame(array('faux' => array(
			'A short description',
			$task,
		)), $collection->all());
	}

	public function testGettingTask()
	{
		$task = new FauxTask('faux');
		
		$collection = new Collection;
		$collection->add($task, 'A short description');

		$this->assertSame(array('A short description', $task), $collection->get('faux'));
	}
}