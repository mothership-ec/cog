<?php

namespace Message\Cog\Module;

use Message\Cog\Services;
use Message\Cog\Module\Bootstrap\EventsInterface;
use Message\Cog\Module\Bootstrap\RoutesInterface;
use Message\Cog\Module\Bootstrap\ServicesInterface;
use Message\Cog\Module\Bootstrap\TasksInterface;
use DirectoryIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Loads modules and their related files.
 *
 * @todo Make this class cachable for performance gains.
 */
class Loader
{
	protected $_eventDispatcher;

	protected $_modules;
	protected $_namespaces;

	/**
	 * Constructor.
	 *
	 * @param EventDispatcherInterface $router The event dispatcher to use for event firing
	 */
	public function __construct(EventDispatcherInterface $dispatcher)
	{
		$this->_eventDispatcher = $dispatcher;
	}

	public function run(array $modules, array $namespaces)
	{
		$this->_modules    = $modules;
		$this->_namespaces = $namespaces;
		$this->_validateModules();
		$this->_loadModules();
	}

	public function exists($name)
	{
		return in_array($name, $this->_modules);
	}

	public function getModules()
	{
		return $this->_modules;
	}

	public function getPath($moduleName)
	{
		if ('\\' == $moduleName[0]) {
			$moduleName = substr($moduleName, 1);
		}

		if (false !== $pos = strrpos($moduleName, '\\')) {
			$namespace  = substr($moduleName, 0, $pos);
			$className  = substr($moduleName, $pos + 1);
			foreach($this->_namespaces as $ns => $dirs) {
				if (0 !== strpos($namespace, $ns)) {
					continue;
				}
				foreach ($dirs as $dir) {
					$path = $dir . '/' . str_replace('\\', '/', $moduleName) . '/';
					if(is_dir($path)) {
						return $path;
					}
				}
			}
		}

		return false;
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
			// TODO: we should check for the presence of the module and fail nicely here
			$doc = Document::create($module);
			if (isset($doc->getInfo()->dependencies)) {
				$this->_checkDependencies($doc);
			}
		}
	}

	protected function _checkDependencies(Document $doc)
	{
		$dependencies = $doc->getInfo()->dependencies;
		if (is_array($dependencies) && !empty($dependencies)) {
			foreach ($dependencies as $dependency) {
				if (!in_array($dependency, $this->_modules)) {
					if ($doc->getInfo()->critical) {
						throw new Exception(
							'Critical module could not be loaded `' . $doc->getInfo()->name . '`, dependency not found: ' . $dependency,
							Exception::DEPENDENCY_NOT_FOUND
						);
					}
					else {
						// log warning quietly, tell developers
					}
				}
			}
		}
	}

	protected function _loadModules()
	{
		foreach ($this->_modules as $module) {
			$modulePath   = $this->getPath($module);
			$bootstrapDir =  $modulePath.'Bootstrap/';
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
			// TODO: Load /Bootstrap files in /test in the module folder after
			// these ones if in test mode
			// if (Services::get('env') === 'test') {
			// }
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