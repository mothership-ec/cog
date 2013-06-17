<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Message\Cog\Templating\Twig\Extension;

use Message\Cog\Form\Twig\Theme\TokenParser;
use Message\Cog\Form\Template\Twig\RendererInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Twig extends Twig with form capabilities.
 *
 * Copy of Symfony Twig form extension
 *
 * @see https://github.com/symfony/TwigBridge/blob/master/Extension/FormExtension.php
 *
 * @author Thomas Marchant
 */
class Form extends \Twig_Extension
{
    /**
     * This property is public so that it can be accessed directly from compiled
     * templates without having to call a getter, which slightly decreases performance.
     *
     * @var RendererInterface
     */
    public $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->renderer->setEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            // {% form_theme form "SomeBundle::widgets.twig" %}
            new TokenParser(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'form_enctype' => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'form_widget'  => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'form_errors'  => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'form_label'   => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'form_row'     => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'form_rest'    => new \Twig_Function_Node('Message\Cog\Form\Twig\Theme\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'csrf_token'   => new \Twig_Function_Method($this, 'renderer->renderCsrfToken'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'humanize' => new \Twig_Filter_Method($this, 'renderer->humanize'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            'selectedchoice' => new \Twig_Test_Method($this, 'isSelectedChoice'),
        );
    }

    /**
     * Returns whether a choice is selected for a given form value.
     *
     * Unfortunately Twig does not support an efficient way to execute the
     * "is_selected" closure passed to the template by ChoiceType. It is faster
     * to implement the logic here (around 65ms for a specific form).
     *
     * Directly implementing the logic here is also faster than doing so in
     * ChoiceView (around 30ms).
     *
     * The worst option tested so far is to implement the logic in ChoiceView
     * and access the ChoiceView method directly in the template. Doing so is
     * around 220ms slower than doing the method call here in the filter. Twig
     * seems to be much more efficient at executing filters than at executing
     * methods of an object.
     *
     * @param ChoiceView   $choice        The choice to check.
     * @param string|array $selectedValue The selected value to compare.
     *
     * @return Boolean Whether the choice is selected.
     *
     * @see ChoiceView::isSelected()
     */
    public function isSelectedChoice(ChoiceView $choice, $selectedValue)
    {
        if (is_array($selectedValue)) {
            return false !== array_search($choice->value, $selectedValue, true);
        }

        return $choice->value === $selectedValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'form';
    }
}
