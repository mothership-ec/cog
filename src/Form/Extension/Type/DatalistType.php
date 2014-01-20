<?php

namespace Message\Cog\Form\Extension\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatalistType extends \Symfony\Component\Form\AbstractType
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
		));
	}
}

