<?php

namespace Message\Cog\Form\Constraint;

use Symfony\Component\Validator;

/**
 * Class YoutubeValidator
 * @package Mothership\Site\Constraint
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Validator for Youtube constraint
 */
class YoutubeValidator extends Validator\Constraints\UrlValidator
{
	const URL_MATCH         = 'youtu';
	const CODE_LENGTH       = 11;
	const ERROR_MESSAGE     = '\'%value%\' is not a valid Vimeo URL';
	const VALUE_KEY         = '%value%';

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, Validator\Constraint $constraint)
	{
		if (empty($value)) {
			return true;
		}

		parent::validate($value, $constraint);

		if (!$this->_isValid($value)) {
			$this->context->addViolation('\'%value%\' is not a valid YouTube video URL', [
				'%value%' => $value,
			]);

			return false;
		}
	}

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	private function _isValid($url)
	{
		if (!strstr($url, self::URL_MATCH)) {
			return false;
		}

		// Check if short hand URL
		if (preg_match('/^https?:\/\/youtu.be\/[A-Za-z0-9\-_]{11}$/', $url)) {
			return true;
		}

		$urn = explode('/', $url);
		$urn = array_pop($urn);
		parse_str($urn, $parts);

		if (!array_key_exists('watch?v', $parts) || (strlen($parts['watch?v']) !== self::CODE_LENGTH)) {
			return false;
		}

		return true;
	}
}