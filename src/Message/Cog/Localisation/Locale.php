<?php

namespace Message\Cog\Localisation;


/**
 * Locale class
 *
 * Represents a locale in Cog
 */
class Locale
{
	protected $_id;
	protected $_fallback;

	/**
	 * Constructor
	 *
	 * @param string $id       A valid IETF language tag which represents this locale.
	 * @param string $fallback A locale to fall back to if content isnt available in the primary locale.
	 */
	public function __construct($id, $fallback = null)
	{
		$this->_id       = $id;
		$this->_fallback = $fallback;
	}

	/**
	 * Get the IETF language tag for this locale
	 *
	 * @return string A IETF language tag
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Get the fallback locale
	 *
	 * @return string A IETF language tag for the fallback locale
	 */
	public function getFallback()
	{
		return $this->_fallback;
	}
}