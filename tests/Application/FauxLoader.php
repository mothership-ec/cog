<?php

namespace Message\Cog\Test\Application;

use Message\Cog\Application\Loader;

class FauxLoader extends Loader
{
	public function _registerModules()
	{
		return array();
	}
}