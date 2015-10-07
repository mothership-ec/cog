<?php

namespace Message\Cog\Test\Event;

use Message\Cog\Exception\TranslationRuntimeException;

class TranslationRuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetTranslationFromConstruct()
	{
		$translation = 'translation';
		$exception = new TranslationRuntimeException('Message', $translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testGetTranslationFromSetTranslation()
	{
		$translation = 'translation';
		$exception = new TranslationRuntimeException('Message');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testGetTranslationDefaultToMessage()
	{
		$message = 'Message';
		$exception = new TranslationRuntimeException($message);
		$this->assertSame($message, $exception->getTranslation());
	}

	public function testSetTranslationOverrideConstruct()
	{
		$translation = 'override';
		$exception = new TranslationRuntimeException('Message', 'original');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	public function testSetTranslationOverrideSetTranslation()
	{
		$translation = 'override';
		$exception = new TranslationRuntimeException('Message');
		$exception->setTranslation('original');
		$exception->setTranslation($translation);
		$this->assertSame($translation, $exception->getTranslation());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetTranslationInvalidTypeFromConstruct()
	{
		new TranslationRuntimeException('Message', []);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetTranslationInvalidTypeFromSetTranslation()
	{
		$translation = null;
		$exception = new TranslationRuntimeException('Message');
		$exception->setTranslation([]);
	}

	/**
	 * @expectedException \Message\Cog\Exception\TranslationRuntimeException
	 */
	public function testExceptionThrowable()
	{
		throw new TranslationRuntimeException;
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testExceptionIsRuntimeException()
	{
		throw new TranslationRuntimeException;
	}
}