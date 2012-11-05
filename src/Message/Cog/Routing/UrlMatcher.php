<?php

namespace Message\Cog\Routing;

use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;

class UrlMatcher extends RedirectableUrlMatcher
{
	public function redirect($path, $route, $scheme = null)
	{
		// TODO: Use the response class when it's built
		header('Location: '.$path);
		exit;
	}
}