<?php

namespace Message\Cog;

/**
* Determines what environment Cog is running in.
*
* An environment is made up of two things
*  - A name
*  - A context
*
* Environments define the 'place' where a Cog app is running. The environment 
* names are predefined and can't be changed. The allowed options are 
* currently: live, local, dev, staging and test.
* 
* This class also determines what context the request is running in: 'web' if 
* the app is being access via HTTP or 'console' if it's being run via the 
* command line.
* 
* @TODO: security protocol for live
*/
class Environment
{
	protected $_flagEnv  = 'COG_ENV';
	protected $_context;
	protected $_name;
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
	
	public function __construct($environmentVar = null)
	{
		if(!empty($environmentVar)) {
			$this->_flagEnv = $environmentVar;
		}

		$this->_detectContext();
		$this->_detectEnvironment();
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

	/**
	 * Manually set the context of the environment. This method should very
	 * rarely need to be used, most likely only for testing purposes.
	 * 
	 * @param string $context A valid context name.
	 */
	public function setContext($context)
	{
		$this->_validate('context', $context, $this->_allowedContexts);
		$this->_context = $context;
	}

	/**
	 * Searches the global environment and then the $_SERVER superglobals for a variable.
	 * Normally these have been set in a vhost config file or .profile file. It 
	 * falls back to the $_SERVER superglobal as $_ENV cant be populated when php
	 * is running via PHP-FPM.
	 *
	 * @param  string $varName Name of the variable to search for.
	 * @return mixed Value of the found variable (as a string) or false if it doesnt exist
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
}