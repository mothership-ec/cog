<?php

namespace Message\Cog\Form\Extension\Validator\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Message\Cog\Form\Extension\Validator\EventListener\ValidationMessageListener;
use Message\Cog\HTTP\Session;
use Message\Cog\Localisation\Translator;

/**
 * Extension adding 'errors_with_fields' variable to options.
 * Adds an event listener which adds flashes if 'errors_with_fields' is false.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class FormTypeErrorsWithFieldsExtension extends AbstractTypeExtension
{
	const ERRORS_WITH_FIELDS_OPTION = 'errors_with_fields';

	protected $_session;
	protected $_translator;

	public function __construct(Session $session, Translator $translator)
	{
		$this->_session	= $session;
		$this->_translator = $translator;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->addEventSubscriber(new ValidationMessageListener($this->_session, $this->_translator));
	}

	/**
	 * Adds the 'errors_with_fields'-option and sets a default for it.
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([self::ERRORS_WITH_FIELDS_OPTION]);
		$resolver->setDefaults([
			self::ERRORS_WITH_FIELDS_OPTION => false,
		]);
	}

	/**
	 * Pass the 'errors_with_fields'-value to the view
	 *
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		// set an 'errors_with_fields' variable for the view to the root's option
		$view->vars[self::ERRORS_WITH_FIELDS_OPTION] = $form->getRoot()->getConfig()->getOption(self::ERRORS_WITH_FIELDS_OPTION);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getExtendedType()
	{
		return 'form';
	}
}
