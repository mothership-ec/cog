<?php

namespace Message\Cog\Form\Extension\Type;

use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateType extends \Symfony\Component\Form\Extension\Core\Type\DateType
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

