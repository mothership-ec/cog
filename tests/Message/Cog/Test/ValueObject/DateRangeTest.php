<?php

namespace Message\Cog\Test\ValueObjects;

use Message\Cog\ValueObject\DateRange;
use DateTime;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{

	/**
     * @expectedException LogicException
     */
	public function testConstructNoDatesSuppliedException()
	{
		$dateRange = new DateRange;
	}

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
		$this->assertTrue($dateRange->isInRange(null));

		// Check for differnet times
		$from = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:13:52');
		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 18:13:22');
		$dateRange = new DateRange($from, $to);

		// Check that the different times work as expected
		// Should return true
		$this->assertTrue($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 10:13:22')));

		// Should return false
		$this->assertFalse($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:13:51')));


		// Check with only one paramater
		$to = new DateTime('28 feb 2014 8:30:55pm');
		$dateRange = new DateRange(null, $to);
		$testDate = new DateTime('27 feb 2014 8:30:55pm');

		$this->assertTrue($dateRange->isInRange($testDate));

		$testDate = new DateTime('28 feb 2014 8:30:56pm');
		$this->assertFalse($dateRange->isInRange($testDate));

		// Check with only one paramater
		$from = new DateTime('28 feb 2014 8:30:55pm');
		$dateRange = new DateRange($from, null);
		$testDate = new DateTime('27 feb 2014 8:30:55pm');

		$this->assertFalse($dateRange->isInRange($testDate));

		$testDate = new DateTime('28 feb 2014 8:30:56pm');
		$this->assertTrue($dateRange->isInRange($testDate));

	}

	public function testGetIntervalToStart()
	{
		$from = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');
		$dateRange = new DateRange($from, null);

		$testResult = $testDate->diff($from);

		// Set the result
		$result = $dateRange->getIntervalToStart($testDate);

		$this->assertEquals($testResult, $result);


		$from = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11');
		$dateRange = new DateRange($from, null);

		$testResult = $testDate->diff($from);

		// Set the result
		$result = $dateRange->getIntervalToStart($testDate);

		$this->assertEquals($testResult, $result);

		$from = new DateTime('-2 days');
		$to = new DateTime('+1 day');
		$dateRange = new DateRange($from, $to);
		$testDateRange = new DateTime;

		$this->assertEquals($dateRange->getIntervalToStart(), $testDateRange->diff($from));

		$this->setExpectedException('LogicException', 'A end date must be provided');

        $to = $from;
        $from = null;
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11');

        $dateRange = new DateRange($from, $to);
        $dateRange->getIntervalToStart($testDate);

	}

	public function testGetIntervalToEnd()
	{
		$to = DateTime::createFromFormat('d/m/Y H:i:s', '14/06/2012 00:00:00');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');
		$dateRange = new DateRange(null, $to);

		$testResult = $testDate->diff($to);

		// Set the result
		$result = $dateRange->getIntervalToEnd($testDate);

		$this->assertEquals($testResult, $result);


		$to = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/01/2011 09:02:11');
		$dateRange = new DateRange(null, $to);

		$testResult = $testDate->diff($to);

		// Set the result
		$result = $dateRange->getIntervalToEnd($testDate);

		$this->assertEquals($testResult, $result);

		$from = new DateTime('-2 days');
		$to = new DateTime('+1 day');
		$dateRange = new DateRange($from, $to);
		$testDateRange = new DateTime;

		$this->assertEquals($dateRange->getIntervalToEnd(), $testDateRange->diff($to));

        $this->setExpectedException('LogicException', 'A from date must be provided');

		$from = new DateTime('-2 days');
		$to = null;
		$testDate = DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11');

        $dateRange = new DateRange($from, $to);
        $dateRange->getIntervalToEnd($testDate);

	}

	public function testToString()
	{
		$from = new DateTime('1 jan 2013 9:30am');
		$to = new DateTime('28 feb 2014 8:30:55pm');
		$dateRange = new DateRange($from, $to);
		$this->expectOutputString('2013-01-01T09:30:00+00:00 - 2014-02-28T20:30:55+00:00');
		echo $dateRange;
	}


}