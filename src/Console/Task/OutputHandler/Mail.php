<?php

namespace Message\Cog\Console\Task\OutputHandler;

use Message\Cog\Mail\MailableInterface;
use Message\Cog\Mail\Mailer;

class Mail extends OutputHandler
{
	protected $_message;
	protected $_dispatch;

	public function __construct(MailableInterface $message, Mailer $dispatch)
	{
		$this->_message = $message;
	}

	/**
	 * {inheritDoc}
	 */
	public function getName()
	{
		return 'mail';
	}

	/**
	 * Get the message instance.
	 *
	 * @return MailableInterface
	 */
	public function getMessage()
	{
		return $this->_message;
	}

	/**
	 * {inheritDoc}
	 */
	public function process(array $args)
	{
		if(!$this->_output) {
			return;
		}

		// Get the first argument as the output
		$content = array_shift($args);

		// Set the subject to a default if not already set
		if ("" == $this->_message->getSubject()) {
			$this->_message->setSubject("Output of " . $this->_task->getName());
		}

		// Append the task output to any existing body, and then any remaining
		// arguments
		$this->_message->setBody($this->_message->getBody()
			. "\n\n" . $content
			. "\n\n" . var_export($args, true)
		);

		// Dispatch the message
		$this->_dispatcher->send($this->_message);
	}
}