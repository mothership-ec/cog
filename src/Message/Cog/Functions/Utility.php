<?php

namespace Mothership\Framework\Functions;

class Utility
{
	protected $_moduleLoader;

	public function __construct($moduleLoader)
	{
		$this->_moduleLoader = $moduleLoader;
	}

	public function traceCallingModuleName()
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($backtrace as $call) {
			// Turn a class name like Mothership\Core\Model\Order into Mothership\Core
			$namespaces = explode('\\', $call['class']);
			$moduleName = implode('\\', array_slice($namespaces, 0, 2));
			
			if(in_array($moduleName, $this->_moduleLoader->getModules())) {
				return $moduleName;
			}
		}

		throw new \LogicException('Stack trace could not be traced back to a module');
	}
}