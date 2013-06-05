<?php

namespace Message\Cog\Localisation;


class Locale
{
	protected $_id;

	public function __construct($id)
	{
		$this->_id = $id;
	}

	public function getId()
	{
		return $this->_id;
	}
}