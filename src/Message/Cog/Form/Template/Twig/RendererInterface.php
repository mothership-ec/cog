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

use Symfony\Component\Form\FormRendererInterface;

/**
 * Taken from Symfony
 *
 * @see https://github.com/symfony/TwigBridge/blob/master/Form/TwigRendererEngineInterface.php
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
interface RendererInterface extends FormRendererInterface
{
    /**
     * Sets Twig's environment.
     *
     * @param \Twig_Environment $environment
     */
    public function setEnvironment(\Twig_Environment $environment);
}
