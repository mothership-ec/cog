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

	protected $_container;
	protected $_request;
	protected $_user;

	public function __construct(ContainerInterface $container)
	{
		$this->_container = $container;
		$this->_request = $this->_container['request'];
		$this->_user = $this->_container['user.current'];

		$this->addExtension(new CoreExtension)
			->addExtension(new CsrfExtension(
				new DefaultCsrfProvider($this->_getSecret())
			))
		;
	}

	/**
	 * Generate secret key to parse into Symfony's CSRF provider, where it will be hashed with the session and
	 * form
	 *
	 * @return string
	 */
	protected function _getSecret()
	{
		$parts = array(
			$this->_request->headers->get('host'),
			$this->_user->email,
			$this->_user->id,
			$this->_user->lastLoginAt,
		);

		return serialize($parts);
	}
}