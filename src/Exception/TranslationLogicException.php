<?php

namespace Message\Cog\Exception;

/**
 * Class TranslationLogicException
 * @package Message\Cog\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Logic exception to allow for translation strings to be given to an exception for user-facing error
 * messages.
 *
 * @see TranslationExceptionInterface
 */
class TranslationLogicException extends \LogicException implements TranslationExceptionInterface
{
	use TranslationExceptionTrait;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(
		$message = "",
		$translation = null,
		$code = 0,
		\Exception $previous = null
	)
	{
		if (null !== $translation) {
			$this->setTranslation($translation);
		}

		parent::__construct($message, $code, $previous);
	}
}