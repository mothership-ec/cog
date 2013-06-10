<?php

namespace Message\Cog\Localisation;


class Locale
{
	protected $_id;
	protected $_fallback;

	public function __construct($id, $fallback = null)
	{
		$this->_id = $id;
	}

	public function getId()
	{
		return $this->_id;
	}

	public function getFallback()
	{
		return $this->_fallback;
	}
}