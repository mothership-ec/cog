<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Bootstrap\EventsInterface;

/**
 * Cog event listener bootstrap.
 *
 * Registers Cog event listeners when the application is loaded.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	/**
	 * Inject the service container.
	 *
	 * @param ContainerInterface $serviceContainer The service container
	 */
	public function setContainer(ContainerInterface $serviceContainer)
	{
		$this->_services = $serviceContainer;
	}

	/**
	 * Register the event listeners & subscribers to the given event dispatcher.
	 *
	 * @param object $eventDispatcher The event dispatcher
	 */
	public function registerEvents($eventDispatcher)
	{
		// Our HTTP Request listeners
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Request
		);

		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Response($this->_services['http.cookies'])
		);

		// Symfony's HTTP Response Listener
		$eventDispatcher->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\ResponseListener('utf-8')
		);

		// Symfony's HTTP Fragment Listener
		$eventDispatcher->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\FragmentListener(
				$this->_services['http.uri_signer']
			)
		);

		// Profiler
		$eventDispatcher->addSubscriber(
			new \Message\Cog\Debug\EventListener(
				$this->_services['profiler'],
				$this->_services['environment']
			)
		);

		// Filesystem
		$eventDispatcher->addSubscriber(
			new \Message\Cog\Filesystem\EventListener
		);


		// Routing
		$eventDispatcher->addSubscriber(new \Message\Cog\Routing\EventListener);

		// Controller
		$eventDispatcher->addSubscriber(new \Message\Cog\Controller\EventListener);

		// Asset Management
		$eventDispatcher->addSubscriber(new \Message\Cog\AssetManagement\EventListener);

		// Deploy
		$eventDispatcher->addSubscriber(new \Message\Cog\Deploy\EventListener);

		// Status check
		$appLoader = $this->_services['app.loader'];
		$eventDispatcher->addListener('console.status.check', function($event) use ($appLoader) {

			$event
				->header('PHP Environment')
					->report('PHP version', PHP_VERSION)
					->report('Default timezone', date_default_timezone_get())

					// Needed for running scheduled tasks
					->check('proc_open() exists', function() {
						return function_exists('proc_open');
					})

					// Needed for stream wrappers
					->check('eval() available', function() {
						return strtolower(ini_get('suhosin.executor.disable_eval')) !== 'Off';
					})

					// Needed for localisation / forms
					->check('intl extension loaded', function() {
						return extension_loaded('intl');
					})

				->header('Message\\Cog')
					->report('Installation directory', $appLoader->getBaseDir())
					->checkPath('Temp directory', 'cog://tmp/')
					->checkPath('Public directory', 'cog://public/')
					->checkPath('Log directory', 'cog://logs/')
			;

		}, 900);
	}
}