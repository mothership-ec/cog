<?php

namespace Message\Cog\Functions;

use LogicException;

class Utility
{
	protected $_moduleLoader;

	public function __construct($moduleLoader)
	{
		$this->_moduleLoader = $moduleLoader;
	}

	/**
	 * Trace the current function call back to the module that called it.
	 *
	 * This calls `debug_backtrace` and loops through each of the elements,
	 * checking the class name until it finds an entry in the list of loaded
	 * modules.
	 *
	 * @return string         Module name found in the backtrace
	 *
	 * @throws LogicException If stack trace could not be traced back to a module
	 */
	public function traceCallingModuleName()
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		foreach ($backtrace as $call) {
			foreach ($this->_moduleLoader->getModules() as $moduleName) {
				if (isset($call['class'])
				 && $moduleName === substr($call['class'], 0, strlen($moduleName))) {
					return $moduleName;
				}
			}
		}

		throw new LogicException('Stack trace could not be traced back to a module');
	}
}