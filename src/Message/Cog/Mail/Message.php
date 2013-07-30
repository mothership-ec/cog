<?php

namespace Message\Cog\Mail;

class Message extends  \Swift_Message
{
	private $view = '';
	protected $_engine;

	public function __construct($engine)
	{
		$this->_engine = $engine;
		parent::__construct();
	}

	public function setEngine($engine)
	{
		$this->_engine = $engine;
	}

	public function setView($view, $params = array())
	{
		$this->view = $view;

		// Parse the view and get result as a string.
		$html = $this->_engine->render($view, $params);

		// Set the body of the email.
		$this->setBody($html, 'text/html');

		return $this;
	}

	public function getView()
	{
		return $this->view;
	}
}