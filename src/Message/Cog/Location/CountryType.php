<?php

namespace Message\Cog\Location;

use Message\Cog\Location\CountryList;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\CountryType as SymfonyCountryType;

class CountryType extends SymfonyCountryType {

	public function __construct()
	{
		$this->_countryList = new CountryList;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if ($options['only_eu']) {

		}
	}

	 /**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choice_list' => $this->_countryList,
			'only_eu' => false
		));
	}

}