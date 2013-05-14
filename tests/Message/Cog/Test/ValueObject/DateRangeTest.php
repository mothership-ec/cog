<?php

namespace Message\Cog\Test\ValueObjects;

use Message\Cog\ValueObject\DateRange;
use DateTime;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{

	public function testIsInRange()
	{
		// Set the to time
		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 00:00:00');
		$from = DateTime::createFromFormat('d/m/Y H:i:s', '01/05/2013 00:00:00');
		$dateRange = new DateRange($from, $to);

		// Set test datetime as today
		// Check that today is before tomorrow
		$this->assertTrue($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '14/05/2013 00:00:00')));

		// Set date to more than the to date
		// Should return false
		$this->assertFalse($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '16/05/2013 00:00:01')));

		// Reset the to and from
		$to = new DateTime;
		$to->setTimestamp(strtotime('+1 day'));
		$from = new DateTime;
		$from->setTimestamp(strtotime('-1 day'));
		$dateRange = new DateRange($from, $to);

		// Pass through null, should use current datetime
		$this->assertTrue($dateRange->isInRange());

		// Check for differnet times
		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:13:52');
		$from = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 18:13:22');
		$dateRange = new DateRange($from, $to);

		// Check that the different times work as expected
		// Should return true
		$dateRange->assertTrue($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 10:13:22')));

		// Should return false
		$dateRange->assertFalse($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:59:99')));

	}

	public function testGetIntervalToStart()
	{
		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');
		$dateRange = new DateRange(null, $to);

		// Set the result
		$result = $dateRange->getIntervalToStart($testDate);

		// Check that a DateInterval Object is returned
		$this->assertTrue($result instanceof \DateInterval);

		// Check that the difference between the results is what it should be
		// Check it is positive result
		$this->assertEquals(0,$result->invert);
		
		// Check date
		$this->assertEquals(0, $result->y);
		$this->assertEquals(1, $result->m);
		$this->assertEquals(3, $result->d);

		// Check times
		$this->assertEquals(13, $result->h);
		$this->assertEquals(9, $result->i);
		$this->assertEquals(57, $result->s);

		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11');
		$dateRange = new DateRange(null, $to);

		// Set the result
		$result = $dateRange->getIntervalToStart($testDate);

		// Check that a DateInterval Object is returned
		$this->assertTrue($result instanceof \DateInterval);

		// Check that the difference between the results is what it should be
		// Check that it is now a negative result
		$this->assertEquals(1,$result->invert);
		
		// Check date
		$this->assertEquals(2, $result->y);
		$this->assertEquals(1, $result->m);
		$this->assertEquals(2, $result->d);

		// Check times
		$this->assertEquals(8, $result->h);
		$this->assertEquals(1, $result->i);
		$this->assertEquals(10, $result->s);

	}

	public function testGetIntervalToEnd()
	{
		$from = DateTime::createFromFormat('d/m/Y H:i:s', '14/06/2012 00:00:00');
		$dateRange = new DateRange($from, null);

		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');

		// Set the result
		$result = $dateRange->getIntervalToEnd($testDate);

		// Check that a DateInterval Object is returned
		$this->assertTrue($result instanceof \DateInterval);

		// Check that the difference between the results is what it should be
		// Check it is positive result
		$this->assertEquals(0,$result->invert);

		// Check that the difference between the results is what it should be
		// Check date
		$this->assertEquals(1, $result->y);
		$this->assertEquals(2, $result->m);
		$this->assertEquals(2, $result->d);

		// Check times
		$this->assertEquals(14, $result->h);
		$this->assertEquals(10, $result->i);
		$this->assertEquals(58, $result->s);

		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/01/2011 09:02:11');
		$dateRange = new DateRange(null, $to);

		// Set the result
		$result = $dateRange->getIntervalToEnd($testDate);

		// Check that a DateInterval Object is returned
		$this->assertTrue($result instanceof \DateInterval);

		// Check that the difference between the results is what it should be
		// Check that it is now a negative result
		$this->assertEquals(1,$result->invert);
		
		// Check date
		$this->assertEquals(2, $result->y);
		$this->assertEquals(4, $result->m);
		$this->assertEquals(2, $result->d);

		// Check times
		$this->assertEquals(8, $result->h);
		$this->assertEquals(1, $result->i);
		$this->assertEquals(10, $result->s);
	}
}