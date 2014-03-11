<?php

namespace Message\Cog\Form\Extension\Validator\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Determines field's required-option, using constraints
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class FormTypeRequiredExtension extends AbstractTypeExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['constraints']);

		$required = function (Options $options) {
			$constraints = $options['constraints'];
			if (is_array($constraints)) {
				foreach ($constraints as $constraint) {
					if ($constraint instanceof Constraints\NotBlank) {
						return true;
					}
				}
			} else {
				if ($constraints instanceof Constraints\NotBlank) {
					return true;
				}
			}

			return false;
		};

		$resolver->setDefaults([
			'constraints' => array(),
			'required'	=> $required,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function finishView(FormView $view, FormInterface $form, array $options)
	{
		// override required option in view
		$view->vars['required'] = $options['required'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExtendedType()
	{
		return 'form';
	}
}
