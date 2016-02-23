<?php

namespace Message\Cog\Form\Constraint;

use Symfony\Component\Validator;

/**
 * Class VimeoValidator
 * @package Message\Cog\Form\Constraint
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class VimeoValidator extends Validator\Constraints\UrlValidator
{
	const URL_MATCH         = 'vimeo.com';
	const CODE_LENGTH       = 15;
	const ERROR_MESSAGE     = '\'%value%\' is not a valid Vimeo URL';
	const VALUE_KEY         = '%value%';

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, Validator\Constraint $constraint)
	{
		if (null === $value || false === $value || '' === $value) {
			return;
		}

		parent::validate($value, $constraint);

		if (!$this->_isValid($value)) {
			$this->context->addViolation(self::ERROR_MESSAGE, [self::VALUE_KEY => $value]);
		}
	}

	/**
	 * Check if the value is a valid Vimeo URL
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	private function _isValid($url)
	{
		if (strpos($url, self::URL_MATCH) === false) {
			return false;
		}

		$parts = explode('/', $url);

		$code  = array_pop($parts);

		if (!is_numeric($code)) {
			return false;
		}

		if (strlen($code) > self::CODE_LENGTH) {
			return false;
		}
	}
}