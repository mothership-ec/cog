<?php

namespace Message\Cog\Mailer;

/**
 * Interface for email engines
 *
 * @author Chad Tomkiss <chadtomkiss@hotmail.co.uk>
 */
interface MailerInterface
{
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
	 */
	public function setFrom($addresses, $name = null);

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
	 */
	public function setTo($addresses, $name = null);

	/**
	 * Set the Cc address(es).
	 *
	 * Recipients set in this field will receive a 'carbon-copy' of this message.
	 *
	 * This method has the same synopsis as {@link setFrom()} and {@link setTo()}.
	 *
	 * @param mixed  $addresses
	 * @param string $name      optional
	 */
	public function setCc($addresses, $name = null);

	/**
     * Set the Bcc addresses of this message.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param mixed  $addresses
     * @param string $name      optional
     */
	public function setBcc($addresses, $name = null);
}