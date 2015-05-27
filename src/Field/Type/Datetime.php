<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\ValueObject\DateTimeImmutable;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a single date & time.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Datetime extends Field
{
	public function __toString()
	{
		return ($this->_value instanceof \DateTime) ? $this->_value->format('d-m-Y G:i:s') : (string) $this->_value;
	}

	public function getFieldType()
	{
		return 'datetime';
	}

	public function setValue($value)
	{
		if (is_scalar($value) && (string) $value === '') {
			$value = null;
		}

		if (null !== $value && !$value instanceof \DateTime) {
			if (!is_scalar($value)) {
				throw new \InvalidArgumentException('Value must be a scalar type or a DateTime');
			}

			try {
				$value = $this->_isTimestamp($value) ? new DateTimeImmutable(date('c', $value)) : new DateTimeImmutable($value);
			} catch (\Exception $e) {
				throw new \LogicException('Could not create DateTime from `' . $value . '`');
			}
		}

		parent::setValue($value);
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'datetime', $this->getFieldOptions());
	}

	/**
	 * @see https://gist.github.com/sepehr/6351385
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	private function _isTimestamp($value)
	{
		$check = (is_int($value) OR is_float($value))
			? $value
			: (string) (int) $value;

		return  ($check === $value)
			&& ( (int) $value <=  PHP_INT_MAX)
			&& ( (int) $value >= ~PHP_INT_MAX);
	}
}