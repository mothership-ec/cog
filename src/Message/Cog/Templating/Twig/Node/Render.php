<?php

namespace Message\Cog\Templating\Twig\Node;

/**
 * Represents a render node.
 *
 * @link https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/TwigBundle/Node/RenderNode.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Render extends \Twig_Node
{
	public function __construct(\Twig_Node_Expression $expr, \Twig_Node_Expression $options, $lineno, $tag = null)
	{
		parent::__construct(array('expr' => $expr, 'options' => $options), array(), $lineno, $tag);
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
			->write("echo \$this->env->getExtension('actions')->renderUri(")
			->subcompile($this->getNode('expr'))
			->raw(', ')
			->subcompile($this->getNode('options'))
			->raw(");\n")
		;
	}
}