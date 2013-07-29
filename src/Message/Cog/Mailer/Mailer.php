<?php

namespace Message\Cog\Mailer;

use Message\Cog\Mailer\MailerEngineInterface;

class Mailer implements MailerInterface {

	protected $_engine;

	public function __construct($engine)
	{
		$this->_engine = $engine;
	}

	/**
	 * Set the from address of this message
	 *
	 * If the message is from multiple people an array should be used.
	 *
	 * If $name is passed and the first parameter is a string, this name will be
	 * associated with the address.
	 *
	 * @param $addresses
	 * @param null $name
	 *
	 * @return mixed
	 */
	public function setFrom($addresses, $name = NULL)
	{
		return $this->_engine->setFrom($addresses, $name);
	}

	/**
	 * Set the to addresses of this message.
	 *
	 * If multiple recipients will receive the message an array should be used.
	 *
	 * If $name is passed and the first parameter is a string, this name will be
	 * associated with the address.
	 *
	 * @param $addresses
	 * @param null $name
	 *
	 * @return mixed
	 */
	public function setTo($addresses, $name = NULL)
	{
		return $this->_engine->setTo($addresses, $name);
	}

	/**
	 * Set the Cc address(es).
	 *
	 * Recipients set in this field will receive a 'carbon-copy' of this message.
	 *
	 * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
	 *
	 * @param mixed $addresses
	 * @param string $name      optional
	 *
	 * @return mixed
	 */
	public function setCc($addresses, $name = NULL)
	{
		return $this->_engine->setCc($addresses, $name);
	}

	/**
     * Set the Bcc addresses of this message.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     *
     * @return mixed
     */
	public function setBcc($addresses, $name = NULL)
	{
		return $this->_engine->setBcc($addresses, $name);
	}
}