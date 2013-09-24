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

	protected $_whitelist = array();
	protected $_whitelistFallback;

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

	/**
	 * Get the current view used for the email
	 *
	 * @return string
	 */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * Determines what content type to set based on the template format.
	 *
	 * This is to match the file extension with the required content type for the email
	 * as defined in $_templateContentTypes.
	 *
	 *
	 * @param $format
	 *
	 * @return string
	 */
	public function getTemplateContentType($format)
	{
		return (isset($this->_templateContentTypes[$format])) ? $this->_templateContentTypes[$format] : '';
	}

	/**
	 * Restrict 'to' addresses to only those that are whitelisted
	 */
	public function addTo($address, $name = null)
	{
		// If there are any whitelist tests
		if (count($this->_whitelist) > 0) {
			$matched = false;

			// Check against each whitelist regex test
			foreach ($this->_whitelist as $regex) {
				$matched = preg_match($regex, $address);
				if ($matched) break;
			}

			// If the address did not match any of the tests, replace it with
			// the fallback address
			if (false === $matched) {
				$address = $this->_whitelistFallback;
			}
		}

		parent::addTo($address, $name);
	}

	/**
	 * Set the fallback email address that is used when an address does not
	 * match any whitelist options.
	 *
	 * @param string $address
	 */
	public function setWhitelistFallback($address)
	{
		$this->_whitelistFallback = $address;
	}

	/**
	 * Get the whitelist
	 *
	 * @return array
	 */
	public function getWhitelist()
	{
		return $this->_whitelist;
	}

	/**
	 * Set the whitelist
	 *
	 * @param array $walterWhite
	 */
	public function setWhitelist($walterWhite)
	{
		$this->_whitelist = $walterWhite;
	}

	/**
	 * Add a regex test to the whitelist
	 *
	 * @param string $regex
	 */
	public function addToWhitelist($regex)
	{
		$regex = is_array($regex) ? $regex : array($regex);

		$this->_whitelist = array_merge($this->_whitelist, $regex);
	}
}