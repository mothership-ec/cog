<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

class Datalist extends Field
{
	public function getFieldType()
	{
		return 'datalist';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'datalist', $this->getFieldOptions());
	}
}