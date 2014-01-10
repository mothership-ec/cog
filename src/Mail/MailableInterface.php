<?php

namespace Message\Cog\Mail;

interface MailableInterface {

	/**
	 * Get the dispatchable message.
	 *
	 * @return Message
	 */
	public function getMessage();

}