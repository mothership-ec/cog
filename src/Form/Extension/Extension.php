<?php

namespace Message\Cog\Form\Extension;

use \Symfony\Component\Form\AbstractExtension;
use \Symfony\Component\PropertyAccess\PropertyAccess;


class Extension extends AbstractExtension
{
	protected function loadTypes()
	{
		return array(
			new Type\DateType(),
			new Type\TimeType(),
			new Type\DatalistType(),
//			new Type\LinkedChoice(),  @todo needs an array as its param, maybe look into refactoring this thing
		);
	}
}
