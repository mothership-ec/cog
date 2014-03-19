<?php

namespace Message\Cog\Form\Extension\Core;

use Symfony\Component\Form\AbstractExtension;

class CoreExtension extends AbstractExtension
{
	protected function loadTypes()
	{
		return [
			new Type\DatalistType,
			new Type\EntityType,
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
