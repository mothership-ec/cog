<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\CallbackFilter;
use Message\Cog\DB\QueryBuilderInterface;
use Mockery as m;

/**
 * Class FilterCollectionTest
 * @package Message\Cog\Test\Filter
 *
 * @author Sam Trangmar-Keates <sam@mothership.ec>
 */
class CallbackFilterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test apply callback is run
	 */
	public function testCallsFilterCallback()
	{
		$self = $this;
		$run = false;

		$filter = new CallbackFilter('test', 'Test', function($qb) use ($self, &$run) {
			$this->assertTrue($qb instanceof QueryBuilderInterface);
			$run = true;
		});
		

		$filter->apply(m::mock('\\Message\\Cog\\DB\\QueryBuilderInterface'));

		$this->assertTrue($run);
	}

	/**
	 * Test getForm() callback is run
	 */
	public function testCallsFormCallback()
	{
		$self = $this;
		$run = false;

		$filter = new CallbackFilter('test', 'Test', function($qb){}, function() use ($self, &$run){
			$run = true;

			return 'choice';
		});
		

		$form = $filter->getForm();

		$this->assertTrue($run);
		$this->assertEquals('choice', $form);
	}
}