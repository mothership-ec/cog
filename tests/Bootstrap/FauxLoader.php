<?php

namespace Message\Cog\Test\Bootstrap;

use Message\Cog\Bootstrap\LoaderInterface;
use Message\Cog\Bootstrap\BootstrapInterface;

class FauxLoader implements LoaderInterface
{
	public function addFromDirectory($path, $namespace)
	{

	}

	public function add(BootstrapInterface $bootstrap)
	{

	}

	public function load()
	{

	}

	public function clear()
	{

	}
}