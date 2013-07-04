<?php

namespace Message\Cog\Form\Factory;

use Symfony\Component\Form\FormFactoryBuilder as SymfonyBuilder;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
//use Symfony\Component\Form\FormTypeGuesserChain;
use Message\Cog\Service\Container;
use Message\Cog\Service\ContainerInterface;

/**
 * Class FormFactoryBuilder
 * @package Message\Cog\Form
 *
 * Extends Symfony\Component\Form\FormFactoryBuilder
 * Adds CoreExtension by default so the Symfony\Component\Form\Forms class is no longer necessary,
 * also sets up Csrf and Templating extensions
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Builder extends SymfonyBuilder
{
	public function __construct(array $extensions)
	{
		$this->addExtensions($extensions);
	}
}