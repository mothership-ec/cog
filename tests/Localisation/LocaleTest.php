<?php

namespace Message\Cog\Test\Localisation;

use Message\Cog\Localisation\Locale;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
	public function testGettingId()
	{
		$locale = new Locale('en_US');

		$this->assertSame('en_US', $locale->getId());
	}

	public function testGettingFallback()
	{
		$locale = new Locale('en_US', 'en_GB');

		$this->assertSame('en_GB', $locale->getFallback());
	}
}