<?php

namespace Message\Cog\Form\Template;

use Symfony\Component\Form\Extension\Templating\TemplatingExtension as SymfonyTemplating;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Message\Cog\Form\Helper;
use Message\Cog\Form\Renderer;
use Message\Cog\Templating\PhpEngine;

class Templating extends SymfonyTemplating
{
	public function __construct(PhpEngine $engine, CsrfProviderInterface $csrfProvider = null, array $defaultThemes = array())
	{
		$engine->addHelpers(array(
			new Helper(new Renderer(new TemplatingRendererEngine($engine, $defaultThemes), $csrfProvider))
		));
	}
}