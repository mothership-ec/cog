<?php

namespace Message\Cog\Field;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DynamicFormType extends AbstractType
{
	protected $_fields = [];

	public function getName()
	{
		return 'dynamic';
	}

	public function add($child, $type = null, array $options = [])
	{
		$this->_fields[] = [
			'child' => $child,
			'type' => $type,
			'options' => $options,
		];

		return $this;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		foreach ($this->_fields as $field) {
			$builder->add($field['child'], $field['type'], $field['options']);
		}
	}
}