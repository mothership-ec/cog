<?php

namespace Message\Cog\Exception;

/**
 * Interface TranslationExceptionInterface
 * @package Message\Cog\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface for exceptions that can take a translation string as a second parameter, for use in
 * user-facing error messages. To access the translation string, call `getTranslation()`
 */
interface TranslationExceptionInterface
{
	/**
	 * Identical to \Exception constructor, except the second parameter is the translation string. The
	 * remaining constructor arguments ($code and $previous) are moved along to third and forth.
	 *
	 * @param string $message
	 * @param null $translation
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($message = "", $translation = null, $code = 0, \Exception $previous = null);

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