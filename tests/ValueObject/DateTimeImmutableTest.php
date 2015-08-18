<?php

namespace Message\Cog\Test\ValueObject;

use Message\Cog\ValueObject\DateTimeImmutable;

use DateTime;
use DateTimeZone;
use DateInterval;

class DateTimeImmutableTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateFromFormat()
	{
		$this->assertFalse(DateTimeImmutable::createFromFormat('d/m/y/', 'not even a date!'));

		$date = DateTimeImmutable::createFromFormat('d/m/y h:ia', '3/4/92 1:40pm', new DateTimeZone('GMT'));

		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $date);
		$this->assertSame(702308400, $date->getTimestamp());
	}

	public function testModify()
	{
		$date = new DateTimeImmutable('01/01/2000 9:00am');
		$newDate = $date->modify('+2 days');

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->getTimestamp(), $newDate->getTimestamp());
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testAdd()
	{
		$date = new DateTimeImmutable('04/07/2012 4:00pm');
		$newDate = $date->add(new DateInterval('P1MT2H5M'));

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->getTimestamp(), $newDate->getTimestamp());
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testSub()
	{
		$date = new DateTimeImmutable('25 December 2013');
		$newDate = $date->sub(new DateInterval('P1D'));

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->getTimestamp(), $newDate->getTimestamp());
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testSetTime()
	{
		$date = new DateTimeImmutable('21 March 2011 4:50am');
		$newDate = $date->setTime(9, 30);

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->format('d/m/y h:ia'), $newDate->format('d/m/y h:ia'));
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testSetDate()
	{
		$date = new DateTimeImmutable('6 August 2013');
		$newDate = $date->setDate(2012, 4, 3);

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->format('d/m/y'), $newDate->format('d/m/y'));
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testSetISODate()
	{
		$date = new DateTimeImmutable('5 July 1984');
		$newDate = $date->setISODate(1990, 13, 2);

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->format('d/m/y'), $newDate->format('d/m/y'));
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}

	public function testSetTimestamp()
	{
		$date = new DateTimeImmutable('@' . time());
		$newDate = $date->setTimestamp(strtotime('-4 days'));

		$this->assertNotSame($date, $newDate);
		$this->assertNotEquals($date->getTimestamp(), $newDate->getTimestamp());
		$this->assertInstanceOf('Message\Cog\ValueObject\DateTimeImmutable', $newDate);
	}
}