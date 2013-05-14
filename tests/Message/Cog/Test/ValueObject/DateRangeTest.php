<?php

namespace Message\Cog\Test\ValueObjects;

use Message\Cog\ValueObject\DateRange;
use DateTime;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{

	public function testIsInRange()
	{
	
		$to = new DateTime;
		
		// Set the to time
		// set to tomorrow
		$to->setDate(2013, 05, 15);

		$from = new DateTime;
		
		// Set the from date
		$from->setDate(2013, 01, 01);

		$dateRange = new DateRange($from, $to);
		
		// Set test datetime as now
		$testDateRange = new DateTime;
		$testDateRange->setDate(2013, 05, 14);

		// Check that today is before tomorrow
		$this->assertTrue($dateRange->isInRange($testDateRange));
		
		// Set date to more than the to date
		$testDateRange->setDate(2013, 05, 16);
		
		// Should return false
		$this->assertFalse($dateRange->isInRange($testDateRange));
		
		
	}
	
	public function testGetIntervalToEnd()
	{
		
	}

	public function getIntervalToStart()
	{
		
	}
}