<?php

namespace Message\Cog\Form\Constraint;

use Symfony\Component\Validator;

class VimeoValidator extends Validator\Constraints\UrlValidator
{
	const URL_MATCH         = 'vimeo.com';
	const CODE_LENGTH       = 15;
	const ERROR_MESSAGE     = '\'%value%\' is not a valid Vimeo URL';
	const VALUE_KEY         = '%value%';

	public function validate($value, Validator\Constraint $constraint)
	{
		if (empty($value)) {
			return true;
		}

		if (strpos($value, self::URL_MATCH) === false) {
			$this->context->addViolation(self::ERROR_MESSAGE, [self::VALUE_KEY => $value]);
			return false;
		}

		$parts = explode('/', $value);

		$code  = array_pop($parts);

		if (strlen($code) > self::CODE_LENGTH) {
			$this->context->addViolation(self::ERROR_MESSAGE, [self::VALUE_KEY => $value]);
		}
	}
}