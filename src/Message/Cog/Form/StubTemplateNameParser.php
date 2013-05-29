<?php

namespace Message\Cog\Form;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReference;

/**
 * Template name parser
 * @see Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTemplateNameParser
 *
 * @author Nicolas Clavaud <nclavaud@gmail.com>
 * http://n.clavaud.free.fr/blog/index.php?article31/symfony2-standalone-form-component-tutorial
 *
 * Needed to load the templates used for rendering form items.
 */

class StubTemplateNameParser implements TemplateNameParserInterface
{
	private $root;

	private $rootTheme;

	public function __construct($root, $rootTheme)
	{
		$this->root = $root;
		$this->rootTheme = $rootTheme;
	}

	public function parse($name)
	{

		list($bundle, $controller, $template) = explode(':', $name);

		if ($template[0] == '_') {
			$path = $this->rootTheme.'/Custom/'.$template;
		} elseif ($bundle === 'TestBundle') {
			$path = $this->rootTheme.'/'.$controller.'/'.$template;
		} else {
			$path = $this->root.'/'.$controller.'/'.$template;
		}

		return new TemplateReference($path, 'php');

	}
}