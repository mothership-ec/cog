<?php

namespace Message\Cog\Test\ValueObjects;

use Message\Cog\ValueObject\DateRange;
use DateTime;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException        \LogicException
	 * @expectedExceptionMessage at least one date must be provided
	 */
	public function testConstructNoDatesSuppliedException()
	{
		$dateRange = new DateRange;
	}

	public function testIsInRange()
	{
		// Test with passing a DateTime
		$dateRange = new DateRange(
			DateTime::createFromFormat('d/m/Y H:i:s', '01/05/2013 00:00:00'),
			DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 00:00:00')
		);

		$this->assertTrue($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '14/05/2013 00:00:00')));
		$this->assertFalse($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '16/05/2013 00:00:01')));

		$dateRange = new DateRange(
			DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:13:52'),
			DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 18:13:22')
		);

		$this->assertTrue($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 10:13:22')));
		$this->assertFalse($dateRange->isInRange(DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 09:13:51')));

		// Test with passing no DateTime (should default to now)
		$dateRange = new DateRange(
			new DateTime('-1 day'),
			new DateTime('+1 day')
		);

		$this->assertTrue($dateRange->isInRange());

		// Test with no start DateTime
		$dateRange = new DateRange(null, new DateTime('28 feb 2014 8:30:55pm'));

		$this->assertTrue($dateRange->isInRange(new DateTime('27 feb 2014 8:30:55pm')));
		$this->assertFalse($dateRange->isInRange(new DateTime('28 feb 2014 8:30:56pm')));

		// Test with no end DateTime
		$dateRange = new DateRange(new DateTime('28 feb 2014 8:30:55pm'), null);

		$this->assertFalse($dateRange->isInRange(new DateTime('27 feb 2014 8:30:55pm')));
		$this->assertTrue($dateRange->isInRange(new DateTime('28 feb 2014 8:30:56pm')));
	}

	public function testGetIntervalToStart()
	{
		// Test with no end date
		$from      = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate  = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');
		$dateRange = new DateRange($from, null);

		$this->assertEquals($testDate->diff($from), $dateRange->getIntervalToStart($testDate));

		$from      = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate  = DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11');
		$dateRange = new DateRange($from, null);

		$this->assertEquals($testDate->diff($from), $dateRange->getIntervalToStart($testDate));

		// Test with a start and end date & no test date (defaults to now)
		$from          = new DateTime('-2 days');
		$dateRange     = new DateRange($from, new DateTime('+1 day'));
		$testDateRange = new DateTime;

		$this->assertEquals($dateRange->getIntervalToStart(), $testDateRange->diff($from));
	}

	/**
	 * @expectedException        \LogicException
	 * @expectedExceptionMessage start date must be provided
	 */
	public function testGetIntervalToStartNoStartDateException() {
		$dateRange = new DateRange(null, new DateTime('-2 days'));
		$dateRange->getIntervalToStart(DateTime::createFromFormat('d/m/Y H:i:s', '13/06/2011 09:02:11'));
	}

	public function testGetIntervalToEnd()
	{
		// Test with no start date
		$to        = DateTime::createFromFormat('d/m/Y H:i:s', '14/06/2012 00:00:00');
		$testDate  = DateTime::createFromFormat('d/m/Y H:i:s', '12/04/2013 14:10:58');
		$dateRange = new DateRange(null, $to);

		$this->assertEquals($testDate->diff($to), $dateRange->getIntervalToEnd($testDate));

		$to        = DateTime::createFromFormat('d/m/Y H:i:s', '15/05/2013 01:01:01');
		$testDate  = DateTime::createFromFormat('d/m/Y H:i:s', '13/01/2011 09:02:11');
		$dateRange = new DateRange(null, $to);

		$this->assertEquals($testDate->diff($to), $dateRange->getIntervalToEnd($testDate));

		// Test with a start and end date & no test date (defaults to now)
		$to            = new DateTime('+1 day');
		$dateRange     = new DateRange(new DateTime('-2 days'), $to);
		$testDateRange = new DateTime;

		$this->assertEquals($dateRange->getIntervalToEnd(), $testDateRange->diff($to));
	}

	/**
	 * @expectedException        \LogicException
	 * @expectedExceptionMessage end date must be provided
	 */
	public function testGetIntervalToEndNoEndDateException() {
		$dateRange = new DateRange(new DateTime('-2 days'), null);
		$dateRange->getIntervalToEnd(new DateTime);
	}

	public function testGetStartAndGetEnd()
	{
		$end       = new DateTime('+3 weeks');
		$dateRange = new DateRange(null, $end);

		$this->assertNull($dateRange->getStart());
		$this->assertSame($end, $dateRange->getEnd());

		$start     = new DateTime('+1 year');
		$dateRange = new DateRange($start);

		$this->assertSame($start, $dateRange->getStart());
		$this->assertNull($dateRange->getEnd());
	}

	public function testToString()
	{
		$this->expectOutputString('2013-01-01T09:30:00+00:00 - 2014-02-28T20:30:55+00:00');

		$dateRange = new DateRange(new DateTime('1 jan 2013 9:30am'),  new DateTime('28 feb 2014 8:30:55pm'));

		echo $dateRange;
	}
}