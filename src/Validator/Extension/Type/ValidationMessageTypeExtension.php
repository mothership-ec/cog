<?php

namespace Message\Cog\Validator\Extension\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Message\Cog\Validator\Extension\EventListener\ValidationMessageListener;
use Message\Cog\HTTP\Session;
use Message\Cog\Localisation\Translator;

/**
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidationMessageTypeExtension extends AbstractTypeExtension
{
    protected $_session;
    protected $_translator;

    public function __construct(Session $session, Translator $translator)
    {
        $this->_session    = $session;
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
        $resolver->setRequired(['errors_with_fields']);
        $resolver->setDefaults([
            'errors_with_fields' => false,
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
        $view->vars['errors_with_fields'] = $form->getRoot()->getConfig()->getOption('errors_with_fields');
    }


    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
