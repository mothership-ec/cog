<?php

namespace Message\Cog\Mail;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

abstract class Factory implements ContainerAwareInterface {

	protected $_container;
	protected $_built = false;

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->_message = $this->get('mail.message');
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function get($service)
	{
		return $this->_container[$service];
	}

	abstract public function build()
	{
		$this->_built = true;
	}

	public function send()
	{
		try {
			if (!$this->_built) {
				throw new \LogicException('Message must be built first!');
			}

			return $this->get('mail.dispatcher')->send($this->_message);
		}
		catch (\Exception $e) {
			$this->get('log.errors')->error('Message:Cog:Mail:Factory', array(
				'exception' => $e,
			));

			return false;
		}
	}

}