<?php

namespace Message\Cog\Exception;

/**
 * Interface TranslationExceptionInterface
 * @package Message\Cog\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface for exceptions that can take a translation string as a second parameter, for use in
 * user-facing error messages. It takes a third parameter for translation parameters. The `code`
 * and `previous` parameters have been moved to forth and fifth respectively.
 */
interface TranslationExceptionInterface
{
	/**
	 * Identical to \Exception constructor, except the second parameter is the translation string. The
	 * remaining constructor arguments ($code and $previous) are moved along to third and forth.
	 *
	 * @param string $message
	 * @param null $translation
	 * @param array $params
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct(
		$message = "",
		$translation = null,
		$params = [],
		$code = 0,
		\Exception $previous = null
	);

	/**
	 * Set the translation string for the exception.
	 *
	 * @param string $translation
	 */
	public function setTranslation($translation);

	/**
	 * Get the translation string for the exception
	 *
	 * @return string
	 */
	public function getTranslation();
}