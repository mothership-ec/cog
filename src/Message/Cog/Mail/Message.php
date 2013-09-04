<?php

namespace Message\Cog\Mail;

class Message extends  \Swift_Message
{
	protected $_view = '';
	protected $_templateContentTypes = array(
			'html' => 'text/html',
			'txt' => 'text/plain'
		);

	protected $_engine;
	protected $_parser;

	public function __construct($engine, $parser)
	{
		$this->_engine = $engine;
		$this->_parser = $parser;

		parent::__construct();
	}

	/**
	 * Set which template engine to use.
	 *
	 * @param $engine
	 */
	public function setEngine($engine)
	{
		$this->_engine = $engine;
	}

	/**
	 * Set which View should be used for the body of the email.
	 *
	 * Example: UniformWares:CMS::Mail:order_dispatched
	 *
	 * You can create both .html and .txt versions
	 *
	 * @param $view
	 * @param array $params
	 *
	 * @return $this
	 */
	public function setView($view, $params = array())
	{
		$this->_view = $view;

		// Get list of templates to render
		$templates = $this->_parser->parse($view, $batch = true);

		// Get the format for each template, render it and add it to
		foreach($templates as $format => $template) {
			$contentType = $this->getTemplateContentType($format);

			// Render the template as a string.
			$body = $this->_engine->render($template, $params);

			// Only set the body once.
			if(!$this->getBody()) {
				$this->setBody($body, $contentType);
			}
			else {
				// Add alternative body.
				$this->addPart($body, $contentType);
			}
		}

		return $this;
	}

	public function getView()
	{
		return $this->_view;
	}

	/**
	 * Determines what content type to set based on the template format.
	 *
	 * @param $format
	 *
	 * @return string
	 */
	public function getTemplateContentType($format)
	{
		return (isset($this->_templateContentTypes[$format])) ? $this->_templateContentTypes[$format] : '';
	}
}