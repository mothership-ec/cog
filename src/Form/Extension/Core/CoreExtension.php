<?php

namespace Message\Cog\Form\Extension\Core;

use Symfony\Component\Form\AbstractExtension;

class CoreExtension extends AbstractExtension
{
	public function __construct(Container $services)
	{
		$this->_services = $services;
	}

	protected function loadTypes()
	{
		return [
			new Type\DatalistType,
			new Type\EntityType,
			new Type\AddressType(
				$this->_services['country.list'],
				$this->_services['state.list']
			);
		];
	}

	protected function loadTypeExtensions()
	{
		return [
			new Type\DateTypeExtension,
			new Type\TimeTypeExtension,
		];
	}
}
