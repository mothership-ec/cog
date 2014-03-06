<?php

namespace Message\Cog\Validator\Extension\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

/**
 * Determines field's required-option, using constraints
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class RequiredTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['constraints']);

        $required = function (Options $options) {
            if($constraints = $options['constraints']) {
                if(is_array($constraints)) {
                    foreach($constraints as $constraint) {
                        if($constraint instanceof Constraints\NotBlank) {
                            return true;
                        }
                    }
                } else {
                    if($constraints instanceof Constraints\NotBlank) {
                        return true;
                    }
                }
            }

            return false;
        };

        $resolver->setDefaults([
            'constraints' => array(),
            'required'    => $required,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
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
