<?php

namespace Message\Cog\Form\Extension\Type;

use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TimeType extends \Symfony\Component\Form\Extension\Core\Type\TimeType
{
	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);

		$resolver->setDefaults(array('widget' => 'single_text'));
	}
}

