<?php

namespace Message\Cog\Bootstrap;

use Message\Cog\Module\Bootstrap\ServicesInterface;
use Message\Cog\Environment;

use Symfony\Component\Routing\RouteCollection;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		// Composer auto loader
		$this->_services['class.loader'] = function() {
			return \ComposerAutoloaderInit::getLoader();
		};

		$env = new Environment;
		$serviceContainer['environment'] = function() use ($env) {
			return $env;
		};
		$serviceContainer['env'] = function($c) {
			return $c['environment']->get();
		};

		$serviceContainer['event'] = function() {
			return new \Message\Cog\Event\Event;
		};

		$serviceContainer['event.dispatcher'] = $serviceContainer->share(function() {
			return new \Symfony\Component\EventDispatcher\EventDispatcher;
		});

		$serviceContainer['router'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Routing\Router(
				$c['reference_parser'],
				array(
					'cache_key' => 'router',
				)
			);
		});

		$serviceContainer['controller.resolver'] = $serviceContainer->share(function() {
			return new \Message\Cog\Controller\ControllerResolver;
		});

		$serviceContainer['templating'] = $serviceContainer->share(function($c) {
			return new \Symfony\Component\Templating\DelegatingEngine(
				array(
					new \Symfony\Component\Templating\PhpEngine(
						new \Message\Cog\Templating\ViewNameParser(
							$c,
							$c['reference_parser'],
							array(
								'twig',
								'php',
							)
						),
						new \Symfony\Component\Templating\Loader\FilesystemLoader(SYSTEM_PATH . 'library/%name%'),
						array(
							new \Symfony\Component\Templating\Helper\SlotsHelper
						)
					),
				)
			);
		});

		$serviceContainer['http.dispatcher'] = function($c) {
			return new \Message\Cog\HTTP\Dispatcher(
				$c['event.dispatcher'],
				$c['controller.resolver'],
				$c['http.request.master']
			);
		};

		$serviceContainer['response_builder'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Controller\ResponseBuilder(
				$c['templating']
			);
		});

		$serviceContainer['config'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\ConfigCache(ROOT_PATH.'config/', $c['env']);
		});

		$serviceContainer['module.locator'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Module\Locator($c['class.loader']->getPrefixes());
		});

		$serviceContainer['module.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Module\Loader($c['module.locator'], $c['event.dispatcher']);
		});

		$serviceContainer['password'] = $serviceContainer->share(function($c) {
			return \Message\Cog\Hash::create($c['config']->security->passwordAlgorithm);
		});

		foreach ($serviceContainer['environment']->getAllowedAreas() as $area) {
			$serviceContainer['routes.' . $area] = $serviceContainer->share(function($c) {
				return new RouteCollection;
			});
		}

		$serviceContainer['task.collection'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Console\TaskCollection;
		});

		// Functions
		$serviceContainer['fns.text'] = $serviceContainer->share(function() {
			return new \Message\Cog\Functions\Text;
		});
		$serviceContainer['fns.utility'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Functions\Utility($c['module.loader']);
		});
		$serviceContainer['fns.debug'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Functions\Utility($c['module.loader']);
		});

		$serviceContainer['reference_parser'] = function($c) {
			return new \Message\Cog\ReferenceParser($c['module.locator'], $c['fns.utility']);
		};
	}

}
