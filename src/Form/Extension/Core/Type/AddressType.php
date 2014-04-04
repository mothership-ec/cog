<?php

namespace Message\Cog\Form\Extension\Core\Type;

use Symfony\Component\Form;
use Message\Cog\Location\Address\Address;
use Symfony\Component\Validator\Constraints;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Discount\Discount\Discount;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AddressType extends Form\AbstractType
{
	protected $_stateList;
	protected $_countryList;

	public function __construct($stateList, $countryList)
	{
		$this->_stateList   = $stateList;
		$this->_countryList = $countryList;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$type = $options['address_type'];

		$builder->add('address_line_1','text', [
			'property_path' => 'lines[1]',
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('address_line_2','text', [
			'property_path' => 'lines[2]',
		]);
		$builder->add('address_line_3','text', [
			'property_path' => 'lines[3]',
		]);
		$builder->add('address_line_4','text', [
			'property_path' => 'lines[4]',
		]);
		$builder->add('town','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('postcode','text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('telephone', 'text', [
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);
		$builder->add('stateID','choice', array(
			'label'   => 'State',
			'choices' => $this->_services['state.list']->all(),
			'empty_value' => 'Please select...',
			'attr'          => array(
				'data-state-filter-country-selector' => "#" . $type . "_countryID"
			),
		));

		$event = $this->_services['country.event'];

		$builder->add('countryID', 'choice', [
			'label'       => 'Country',
			'choices'     => $this->_services['event.dispatcher']->dispatch('country.'.$type, $event)->getCountries(),
			'empty_value' => 'Please select...',
			'constraints' => new Constraints\NotBlank([
				'groups' => [$type, 'all'],
			]),
		]);

		$builder->addEventListener(Form\FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
	}

	public function onPostSubmit(Form\FormEvent $event)
	{
		$form = $event->getForm();
		$this->validateState($form);
		$this->filter($form);
	}

	public function validateState(Form\FormInterface $form)
	{
		$states = $this->_services['state.list']->all();
		$address = $form->getData();

		if (isset($states[$address->countryID]) and (empty($address->stateID) or ! isset($states[$address->countryID][$address->stateID]))) {
			$form->get('stateID')->addError(new Form\FormError(sprintf('This value is required for %s addresses.',
				$this->_services['country.list']->getByID($address->countryID)
			)));
		}
	}

	public function filter(Form\FormInterface $form)
	{
		$type = $form->getConfig()->getOption('address_type');
		$address = $form->getData();

		$address->type    = $type;
		$address->state   = $this->_services['state.list']->getByID($address->countryID, $address->stateID);
		$address->country = $this->_services['country.list']->getByID($address->countryID);
	}

	/**
	 * @todo  Add state & country list as options in here and default to the
	 *        ones passed into the constructor.
	 *
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['address_type']);

		$resolver->setDefaults([
		   'data_class'   => 'Message\\Cog\\Location\\Address\\Address',
		]);
	}

	public function getName()
	{
		return 'address';
	}
}