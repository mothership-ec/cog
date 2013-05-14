<?php

namespace Message\Cog\Test\ValueObject;

use Message\Cog\ValueObject\Authorship;

use DateTime;

class AuthorshipTest extends \PHPUnit_Framework_TestCase
{
	public function testCreating()
	{
		$authorship = new Authorship;
		$timestamp  = new DateTime('10 minutes ago');
		$author     = 5;

		$this->assertNull($authorship->createdAt());
		$this->assertNull($authorship->createdBy());

		$this->assertEquals($authorship, $authorship->create($timestamp, $author));

		$this->assertEquals($timestamp, $authorship->createdAt());
		$this->assertEquals($author, $authorship->createdBy());

		return $authorship;
	}

	/**
	 * @depends           testCreating
	 */
	public function testCreatingTwiceThrowsException($authorship)
	{
		$createdAt = $authorship->createdAt();
		$createdBy = $authorship->createdBy();

		try {
			$authorship->create(new DateTime('now'), 65);
		}
		catch (\LogicException $e) {
			$this->assertEquals($createdAt, $authorship->createdAt());
			$this->assertEquals($createdBy, $authorship->createdBy());

			return;
		}

		$this->fail('No exception was thrown when trying to set create metadata twice');
	}

	public function testUpdating()
	{
		$authorship = new Authorship;
		$timestamp  = new DateTime('1 day ago');
		$author     = 'Joe Holdcroft';

		$this->assertNull($authorship->updatedAt());
		$this->assertNull($authorship->updatedBy());

		$this->assertEquals($authorship, $authorship->update($timestamp, $author));

		$this->assertEquals($timestamp, $authorship->updatedAt());
		$this->assertEquals($author, $authorship->updatedBy());

		return $authorship;
	}

	/**
	 * @depends testUpdating
	 */
	public function testUpdatingMultipleTimes($authorship)
	{
		$timestamp = new DateTime('+2 minutes');
		$author    = 'Danny Hannah';

		$authorship->update($timestamp, $author);

		$this->assertEquals($timestamp, $authorship->updatedAt());
		$this->assertEquals($author, $authorship->updatedBy());
	}

	public function testDeleting()
	{
		$authorship = new Authorship;
		$timestamp  = new DateTime('-639 minutes');
		$author     = new \stdClass;

		$this->assertNull($authorship->deletedAt());
		$this->assertNull($authorship->deletedBy());

		$this->assertEquals($authorship, $authorship->delete($timestamp, $author));

		$this->assertEquals($timestamp, $authorship->deletedAt());
		$this->assertEquals($author, $authorship->deletedBy());

		return $authorship;
	}

	/**
	 * @depends testDeleting
	 */
	public function testDeletingTwiceThrowsException($authorship)
	{
		$deletedAt = $authorship->deletedAt();
		$deletedBy = $authorship->deletedBy();

		try {
			$authorship->delete(new DateTime('now'), 'Test McTester');
		}
		catch (\LogicException $e) {
			$this->assertEquals($deletedAt, $authorship->deletedAt());
			$this->assertEquals($deletedBy, $authorship->deletedBy());

			return;
		}

		$this->fail('No exception was thrown when trying to set delete metadata twice');
	}

	/**
	 * @depends testDeleting
	 */
	public function testRestoring($authorship)
	{
		$this->assertEquals($authorship, $authorship->restore());

		$this->assertNull($authorship->deletedAt());
		$this->assertNull($authorship->deletedBy());
	}

	/**
	 * @expectedException        \LogicException
	 * @expectedExceptionMessage has not been deleted
	 */
	public function testRestoringWhenNotDeleted()
	{
		$authorship = new Authorship;

		$authorship->restore();
	}

	public function testSettingNullTimestampDefaultsToNow()
	{
		$authorship = new Authorship;
		$time       = time();

		$authorship
			->create(null, 5)
			->update(null, 10)
			->delete(null, 'Joe');

		$this->assertEquals($time, $authorship->createdAt()->getTimestamp(), '', 5);
		$this->assertEquals($time, $authorship->updatedAt()->getTimestamp(), '', 5);
		$this->assertEquals($time, $authorship->deletedAt()->getTimestamp(), '', 5);
	}

	public function testToStringCreated()
	{
		$this->expectOutputString('Created by Joe on 3 April 2013 at 9:30am');

		$authorship = new Authorship;

		$authorship->create(new DateTime('3 April 2013 09:30'), 'Joe');

		echo $authorship;
	}

	public function testToStringCreatedUpdated()
	{
		$this->expectOutputString(
			'Created by Jimbo on 24 January 1991 at 11:55pm' . "\n" .
			'Last updated by James on 13 May 2013 at 1:45pm'
		);

		$authorship = new Authorship;

		$authorship
			->create(new DateTime('24 January 1991 23:55'), 'Jimbo')
			->update(new DateTime('13 May 2013 13:45'), 'James');

		echo $authorship;
	}

	public function testToStringCreatedUpdatedDeleted()
	{
		$this->expectOutputString(
			'Created by Tester on 17 February 2000 at 6:00pm' . "\n" .
			'Last updated by Someone else on 26 March 2005 at 4:00pm' . "\n" .
			'Deleted by Danny on 4 September 2014 at 4:00am'
		);

		$authorship = new Authorship;

		$authorship
			->create(new DateTime('17 February 2000 18:00'), 'Tester')
			->update(new DateTime('26 March 2005 16:00'), 'Someone else')
			->delete(new DateTime('4 September 2014 04:00'), 'Danny');

		echo $authorship;
	}

	public function testToStringUpdated()
	{
		$this->expectOutputString('Last updated by Jamie on 1 January 2010 at 12:30pm');

		$authorship = new Authorship;

		$authorship->update(new DateTime('1 January 2010 12:30'), 'Jamie');

		echo $authorship;
	}
}