<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

class Datalist extends Field
{
	public function getFieldType()
	{
		return 'datalist';
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'datalist', $this->getLabel(), $this->getFieldOptions());
	}
}