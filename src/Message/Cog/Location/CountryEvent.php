<?php

namespace Message\Cog\Location;

use Message\Cog\Event\Event;

/**
 * Event for country filtering.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class CountryEvent extends Event
{
	protected $_countries;

	/**
	 * Constructor.
	 *
	 * @param array $countries The countries for this event.
	 */
	public function __construct(array $countries)
	{
		$this->setCountries($countries);
	}

	/**
	 * Get the countries relating to this event.
	 *
	 * @return array
	 */
	public function getCountries()
	{
		return $this->_countries;
	}

	/**
	 * Set the countries relating to this event.
	 *
	 * @param array $countries
	 */
	public function setCountries(array $countries)
	{
		$this->_countries = $countries;
	}
}