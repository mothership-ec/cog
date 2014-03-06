<?php

namespace Message\Cog\Form\Extension\Core\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class DatalistType extends AbstractType
{
	protected $_choices;

	public function getName()
	{
		return 'datalist';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		if (!array_key_exists('choices', $options) || !is_array($options['choices'])) {
			throw new \Exception('"choices" must be set and be an array');
		}

		$view->vars = array_replace($view->vars, array(
			'choices'	=> $options['choices'],
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices'	=> array(),
			'compound'	=> false,
		));
	}
}

