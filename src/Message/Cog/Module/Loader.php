<?php

namespace Message\Cog\Module;

use DirectoryIterator;

use Message\Cog\Services;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Module\Bootstrap\EventsInterface;
use Message\Cog\Module\Bootstrap\RoutesInterface;
use Message\Cog\Module\Bootstrap\ServicesInterface;
use Message\Cog\Module\Bootstrap\TasksInterface;

/**
 * Loads Cog modules and their related files.
 *
 * @todo Make this class cachable for performance gains.
 */
class Loader
{
	protected $_locator;
	protected $_eventDispatcher;

	protected $_modules;

	/**
	 * Constructor.
	 *
	 * @param LocatorInterface    $locator    The module locator
	 * @param DispatcherInterface $dispatcher The event dispatcher to use for event firing
	 */
	public function __construct($locator, DispatcherInterface $dispatcher)
	{
		$this->_locator         = $locator;
		$this->_eventDispatcher = $dispatcher;
	}

	public function run(array $modules)
	{
		$this->_modules = $modules;
		$this->_validateModules();
		$this->_loadModules();
	}

	/**
	 * Check if a given module was requested to be loaded within this
	 * application.
	 *
	 * @param  string $name Module name to check for
	 * @return boolean      Result of the check
	 */
	public function exists($name)
	{
		return in_array($name, $this->_modules);
	}

	/**
	 * Get list of modules that this application has requested are loaded.
	 *
	 * @return array Array of modules
	 */
	public function getModules()
	{
		return $this->_modules;
	}

	protected function _validateModules()
	{
		if (empty($this->_modules)) {
			throw new Exception(
				'No modules found',
				Exception::NO_MODULES_FOUND
			);
		}

		foreach ($this->_modules as $module) {
			if (!file_exists($this->_locator->getPath($module))) {
				throw new Exception(
					sprintf('Module could not be found: `%s`', $module),
					Exception::MODULE_NOT_FOUND
				);
			}
		}
	}

	protected function _loadModules()
	{
		foreach ($this->_modules as $module) {
			$modulePath   = $this->_locator->getPath($module);
			$bootstrapDir = $modulePath . 'Bootstrap/';
			// IF THERE IS A BOOTSTRAP DIRECTORY
			if (is_dir($bootstrapDir)) {
				// RUN ALL BOOTSTRAPS
				$dir = new DirectoryIterator($bootstrapDir);
				foreach ($dir as $file) {
					// SKIP NON-PHP FILES
					if ($file->getExtension() !== 'php') {
						continue;
					}
					// BUILD BOOTSTRAP CLASS NAME
					$bootstrapClassName = '\\' . $module . '\\Bootstrap\\' . $file->getBasename('.php');
					// LOAD BOOTSTRAP FILE
					$this->_loadBootstrap(new $bootstrapClassName);
				}
			}
			$this->_eventDispatcher->dispatch(
				sprintf(
					Event::MODULE_LOADED,
					strtolower(str_replace('\\', '.', $module))
				),
				new Event
			);
		}
	}

	protected function _loadBootstrap($bootstrap)
	{
		// IF THIS IS A ROUTES BOOTSTRAP, REGISTER ROUTES
		if ($bootstrap instanceof ServicesInterface) {
			$bootstrap->registerServices(Services::instance());
		}
		// IF THIS IS AN EVENTS BOOTSTRAP, REGISTER EVENTS
		if ($bootstrap instanceof EventsInterface) {
			$bootstrap->registerEvents(Services::get('event.dispatcher'));
		}
		// IF THIS IS A ROUTES BOOTSTRAP, REGISTER ROUTES
		if ($bootstrap instanceof RoutesInterface) {
			$bootstrap->registerRoutes(Services::get('router'));
		}
		// IF THIS IS A TASKS BOOTSTRAP AND WE'RE IN THE CONSOLE, REGISTER TASKS
		if (Services::get('environment')->context() == 'console' && $bootstrap instanceof TasksInterface) {
			$bootstrap->registerTasks(Services::get('task.collection'));
		}
	}

}