<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Message\Cog\Form\Template\Twig;

use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * Taken from Symfony
 *
 * @see https://github.com/symfony/TwigBridge/blob/master/Form/TwigRenderer.php
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Renderer extends FormRenderer implements RendererInterface
{
    /**
     * @var RendererEngineInterface
     */
    private $engine;

    public function __construct(RendererEngineInterface $engine, CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($engine, $csrfProvider);

        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment(\Twig_Environment $environment)
    {
        $this->engine->setEnvironment($environment);
    }
}
