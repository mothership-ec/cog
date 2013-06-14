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

	public function enable($recipients, $subject = '', $body = '')
	{
		$this->_recipients = $recipients;
		$this->_subject    = $subject;
		$this->_body       = $body;

		parent::enable();
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