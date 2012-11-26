<?php

namespace Message\Cog\Routing;

class UrlMatcher extends \Symfony\Component\Routing\Matcher\RedirectableUrlMatcher
{
	public function redirect($path, $route, $scheme = null)
	{
		// TODO: Use the response class when it's built
		header('Location: '.$path);
		exit;
	}
}