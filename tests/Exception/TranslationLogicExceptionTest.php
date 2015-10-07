<?php

namespace Message\Cog\Test\Event;

use Message\Cog\Exception\TranslationLogicException;

class TranslationLogicExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetTranslationFromConstruct()
	{
		$translation = 'translation';
		$exception = new TranslationLogicException('Message', $translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testGetTranslationFromSetTranslation()
	{
		$translation = 'translation';
		$exception = new TranslationLogicException('Message');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testGetTranslationDefaultToMessage()
	{
		$message = 'Message';
		$exception = new TranslationLogicException($message);
		$this->assertSame($message, $exception->getTranslation());
	}

	public function testSetTranslationOverrideConstruct()
	{
		$translation = 'override';
		$exception = new TranslationLogicException('Message', 'original');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testSetTranslationOverrideSetTranslation()
	{
		$translation = 'override';
		$exception = new TranslationLogicException('Message');
		$exception->setTranslation('original');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetTranslationInvalidTypeFromConstruct()
	{
			new TranslationLogicException('Message', []);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetTranslationInvalidTypeFromSetTranslation()
	{
		$exception = new TranslationLogicException('Message');
		$exception->setTranslation([]);
	}
}