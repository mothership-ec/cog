<?php

namespace Message\Cog\Form\Template;

use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;

class SimpleTemplateNameParser implements TemplateNameParserInterface
{
	private $root;

	public function __construct($type)
	{
		$this->root = realpath(__DIR__ . '/../Views/' . ucfirst($type));
	}

	public function parse($name)
	{
		if (false !== strpos($name, ':')) {
			$path = str_replace(':', '/', $name);
		} else {
			$path = $this->root . '/' . $name;
		}

		return new TemplateReference($path, 'php');
	}
}