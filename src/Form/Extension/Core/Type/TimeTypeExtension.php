<?php

namespace Message\Cog\Form\Extension\Core\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractTypeExtension;

/**
 * Extension setting the default widget for time fields to 'single_text' (HTML5 time field)
 */
class TimeTypeExtension extends AbstractTypeExtension
{
	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'widget' => 'single_text'
		]);
	}

	public function getExtendedType()
	{
		return 'time';
	}
}

