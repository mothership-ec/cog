<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;

class FauxRouter implements RouterInterface
{
	protected $_context;

	public function match($pathinfo)
	{
		return false;
	}

	public function generate($name, $parameters = array(), $absolute = false)
	{

	}

	public function setContext(RequestContext $context)
	{
		$this->_context = $context;
	}

	public function getContext()
	{
		return $this->_context;
	}

	public function getRouteCollection()
	{
		return array();
	}
}