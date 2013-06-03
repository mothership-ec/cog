<?php

namespace Message\Cog\Form\Template;

use Symfony\Component\Form\Extension\Templating\TemplatingExtension as SymfonyTemplating;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Form\FormRenderer;
use Message\Cog\Form\Helper;
use Message\Cog\Templating\PhpEngine;

class Templating extends SymfonyTemplating
{
	public function __construct(PhpEngine $engine, CsrfProviderInterface $csrfProvider = null, array $defaultThemes = array())
	{
		$engine->addHelpers(
			array(
				new Helper(
					new FormRenderer(
						new TemplatingRendererEngine($engine, $defaultThemes),
						$csrfProvider
					)
				)
			)
		);
	}
}