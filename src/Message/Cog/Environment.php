<?php

namespace Message\Cog;

/**
* Determines what environment Cog is running in.
*
* Examples of environments could be live, local, dev, staging, test, etc.
* This class also determines what context the request is running in e.g web or
* console.
*
* @TODO: write unit tests for this class
* @TODO: security protocol for live
* @TODO: pull out environment context name and default environment as constants?
*/
class Environment
{
	const ENV_NAME  = 'MOTHERSHIP_ENV';
	const AREA_NAME = 'MOTHERSHIP_AREA';

	protected $_context;
	protected $_name;
	protected $_area;

	protected $_allowedEnvironments = array(
		'local', 	// Developers machine
		'test', 	// When test suite is running
		'dev',		// A test site on a client server
		'staging',	// Deployed code ready to go live
		'live',		// Live public facing site
	);
	protected $_allowedContexts = array(
		'web',		// Running in fcgi or as mod_php
		'console',	// Run from the command line.
	);
	protected $_allowedAreas = array(
		'www',
		'epos',
		'admin',
		'checkout',
	);

	public function __construct()
	{
		$this->_detectContext();
		$this->_detectEnvironment();
		$this->_detectArea();
	}

	public function get()
	{
		return $this->_name;
	}

	public function set($name)
	{
		$this->_validate('environment', $name, $this->_allowedEnvironments);
		$this->_name = $name;
	}

	public function isLocal()
	{
		return $this->get() === 'local';
	}

	public function context()
	{
		return $this->_context;
	}

	public function setContext($context)
	{
		$this->_validate('context', $context, $this->_allowedContexts);
		$this->_context = $context;
	}

	public function area()
	{
		return $this->_area;
	}

	public function setArea($area)
	{
		$this->_validate('area', $area, $this->_allowedAreas);
		$this->_area = $area;
	}

	public function getAllowedAreas()
	{
		return $this->_allowedAreas;
	}

	/**
	 * Searches the $_ENV and then the $_SERVER superglobals for a variable.
	 * Normally these have been set in a vhost config file.
	 *
	 * @param  string $varName Name of the variable to search for.
	 * @return mixed Value of the found variable or false if it doesnt exist
	 */
	public function getEnvironmentVar($varName)
	{
		$definedVar = getenv($varName);
		if($definedVar === false && isset($_SERVER[$varName])) {
			$definedVar = $_SERVER[$varName];
		}

		return $definedVar;
	}

	protected function _validate($type, $value, array $allowed)
	{
		if(!in_array($value, $allowed)) {
			$msg = '`%s` is not a valid %s. Allowed values: `%s`';
			throw new \InvalidArgumentException(
				sprintf($msg, $value, $type, implode('`, `', $allowed))
			);
		}

		return true;
	}

	protected function _detectContext()
	{
		$this->setContext(php_sapi_name() == 'cli' ? 'console' : 'web');
	}

	protected function _detectEnvironment()
	{
		$this->set($this->getEnvironmentVar(self::ENV_NAME) ?: $this->_allowedEnvironments[0]);
	}

	protected function _detectArea()
	{
		$this->setArea($this->getEnvironmentVar(self::AREA_NAME) ?: $this->_allowedAreas[0]);
	}

}