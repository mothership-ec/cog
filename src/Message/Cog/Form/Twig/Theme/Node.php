<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Message\Cog\Form\Twig\Theme;

/**
 * Taken from Symfony's FormThemeNode
 *
 * @see https://github.com/symfony/TwigBridge/blob/master/Node/FormThemeNode.php
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Node extends \Twig_Node
{
	public function __construct(\Twig_NodeInterface $form, \Twig_NodeInterface $resources, $lineno, $tag = null)
	{
		parent::__construct(array('form' => $form, 'resources' => $resources), array(), $lineno, $tag);
	}

	/**
	 * Compiles the node to PHP.
	 *
	 * @param \Twig_Compiler $compiler A Twig_Compiler instance
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write('$this->env->getExtension(\'form\')->renderer->setTheme(')
			->subcompile($this->getNode('form'))
			->raw(', ')
			->subcompile($this->getNode('resources'))
			->raw(");\n");
		;
	}
}
