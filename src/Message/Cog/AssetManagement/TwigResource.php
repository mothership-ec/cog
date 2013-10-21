<?php

namespace Message\Cog\AssetManagement;

use Assetic\Extension\Twig\TwigResource as AsseticTwigResource;

class TwigResource extends AsseticTwigResource {

	public function __toString()
	{
		// Ensure that the name property is cast as a string before returning.
		// This fixes the issue of PHP not bubbling down the __toString()
		// methods.
		// Oh and $name is a private variable so can not be accessed directly.
		// @see https://gist.github.com/lsjroberts/7084126
		return (string) $this->__toString();
	}

}