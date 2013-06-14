<?php

namespace Message\Cog\Form\Factory;

use Symfony\Component\Form\FormFactoryBuilder as SymfonyBuilder;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Message\Cog\Form\Registry;
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
	public function __construct(ContainerInterface $container)
	{
		$this->addExtension(new CoreExtension)
			->addExtension(new \Message\Cog\Form\Csrf\Csrf(
					new \Message\Cog\Form\Csrf\Provider($this->_getCsrfSecret()))
			)
		;
	}

	/**
	 * Get csrf secret key
	 *
	 * @todo generate csrf key depending on environmental variables such as user etc.
	 *
	 * @return string
	 */
	protected function _getCsrfSecret()
	{
		return 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr';
	}

}