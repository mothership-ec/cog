<?php

namespace Message\Cog\Validator\Extension\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Message\Cog\Validator\Extension\EventListener\ValidationListener;
use Message\Cog\HTTP\Session;

/**
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidationMessageTypeExtension extends AbstractTypeExtension
{
    protected $_session;

    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->addEventSubscriber(new ValidationListener($this->_session));
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
        // set an 'errors_with_fields' variable that will be available when rendering this field
        $view->vars['errors_with_fields'] = $options['errors_with_fields'];
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
