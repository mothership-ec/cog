<?php

namespace Message\Cog\Mailer\Engines\SwiftMailer;

use Message\Cog\Mailer\MailerInterface;

class SwiftMailer implements MailerInterface  {

	protected $_mailer;

	public function __construct($mailer)
	{
		$this->_mailer = $mailer;
	}

	public function setFrom($addresses, $name = null)
	{
		return $this->_mailer->setFrom($addresses, $name);
	}

	public function setTo($addresses, $name = null)
	{
		return $this->_mailer->setTo($addresses, $name);
	}

	public function setCc($addresses, $name = null)
	{
		return $this->_mailer->setCc($addresses, $name);
	}

	public function setBcc($addresses, $name = null)
	{
		return $this->_mailer->setBcc($addresses, $name);
	}
}