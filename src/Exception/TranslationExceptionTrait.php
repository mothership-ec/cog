<?php

namespace Message\Cog\Exception;

/**
 * Class TranslationExceptionTrait
 * @package Message\Cog\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Trait for duplicating translation functionality across translation exceptions
 */
trait TranslationExceptionTrait
{
	/**
	 * @var string
	 */
	private $_translation;

	/**
	 * @var
	 */
	private $_params = [];

	/**
	 * @see TranslationExceptionInterface::setTranslation()
	 * {@inheritDoc}
	 */
	public function setTranslation($translation)
	{
		if (!is_string($translation)) {
			throw new \InvalidArgumentException('Translation must be a string!');
		}

		$this->_translation = $translation;
	}

	/**
	 * @see TranslationExceptionInterface::getTranslation()
	 * {@inheritDoc}
	 */
	public function getTranslation()
	{
		return $this->_translation ?: $this->getMessage();
	}

	public function setParams(array $params)
	{
		$this->_params = $params;
	}

	public function getParams()
	{
		return $this->_params;
	}
}