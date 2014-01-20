<?php

namespace Message\Cog\Console\Task\OutputHandler;


class Mail extends OutputHandler
{
	protected $_recipients;
	protected $_subject;
	protected $_body;

	/**
	 * {inheritDoc}
	 */
	public function getName()
	{
		return 'mail';
	}

	/**
	 * Set the recipients for the console mail output.
	 *
	 * @param string|array $recipients The recipients this should be delivered to.
	 */
	public function setRecipients($recipients)
	{
		$this->_recipients = $recipients;
	}

	/**
	 * Set the subject.
	 *
	 * @param string $subject The subject of the email to send.
	 */
	public function setSubject($subject)
	{
		$this->_subject = $subject;
	}

	/**
	 * Set the body.
	 *
	 * @param string $body The body to prepend to the output in the email.
	 */
	public function setBody($body)
	{
		$this->_body = $body;
	}

	/**
	 * {inheritDoc}
	 */
	public function process(array $args)
	{
		if(!$this->_output) {
			return;
		}

		// if a single string is provided turn it into the format we expect
		if(!is_array($this->_recipients)) {
			$this->_recipients = array($this->_recipients => '');
		}

		$content    = $args[0];
		$recipients = $this->_recipients;
		$subject    = $this->_subject ?: 'Output of '.$this->_task->getName();
		$body       = $this->_body  . $content;

		// Todo: use the email component when it's built
		mail(implode(', ', array_keys($this->_recipients)), $subject, $body);
	}
}