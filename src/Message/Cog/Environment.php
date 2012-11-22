<?php

namespace Message\Cog;

/**
* Determines what environment Cog is running in.
*
* An environment is made up of 3 things
*  - A name
*  - A set of areas
*  - A context
*
* Environments define the 'place' where a Cog app is running. The environment 
* names are predefined and can't be changed. The allowed options are 
* currently: live, local, dev, staging and test.
*
* The environment's context is either 'web' if the app is being access via HTTP
* or 'console' if it's being run via the command line.
* 
* This class also determines what context the request is running in e.g web or
* console.
*
* @TODO: security protocol for live
*/
class Environment
{
	protected $_flagEnv  = 'COG_ENV';
	protected $_flagArea = 'COG_AREA';
	protected $_context;
	protected $_name;
	protected $_area;
	protected $_allowedAreas = array();
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
	
	public function __construct(array $areas, $environmentVar = null, $areaVar = null)
	{
		if(!count($areas)) {
			throw new \InvalidArgumentException('At least one area must be added to the environment.');
		}
		$this->_allowedAreas = $areas;

		if(!empty($environmentVar)) {
			$this->_flagEnv = $environmentVar;
		}

		if(!empty($areaVar)) {
			$this->_flagArea = $areaVar;
		}

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
	 * Searches the global environment and then the $_SERVER superglobals for a variable.
	 * Normally these have been set in a vhost config file or .profile file
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
		$this->set($this->getEnvironmentVar($this->_flagEnv) ?: $this->_allowedEnvironments[0]);
	}

	protected function _detectArea()
	{
		$this->setArea($this->getEnvironmentVar($this->_flagArea) ?: $this->_allowedAreas[0]);
	}

}