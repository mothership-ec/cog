<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Cog\Application\Environment;
use Message\Cog\Routing\RouteCollection;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['profiler'] = $serviceContainer->share(function() {
			return new \Message\Cog\Debug\Profiler(null, null, false);
		});

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
			return new \Message\Cog\Event\Dispatcher;
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
			return new \Message\Cog\Templating\DelegatingEngine(
				array(
					new \Message\Cog\Templating\PhpEngine(
						new \Message\Cog\Templating\ViewNameParser(
							$c,
							$c['reference_parser'],
							array(
								'twig',
								'php',
							)
						),
						new \Symfony\Component\Templating\Loader\FilesystemLoader(
							$c['app.loader']->getBaseDir()
						),
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
				(isset($c['http.request.master']) ? $c['http.request.master'] : null)
			);
		};

		$serviceContainer['response_builder'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Controller\ResponseBuilder(
				$c['templating']
			);
		});

		$serviceContainer['config'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\ConfigCache(
				$c['app.loader']->getBaseDir().'config/',
				$c['env']
			);
		});

		$serviceContainer['module.locator'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Module\Locator($c['class.loader']->getPrefixes());
		});

		$serviceContainer['module.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Module\Loader($c['module.locator'], $c['bootstrap.loader'], $c['event.dispatcher']);
		});

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
			return new \Message\Cog\Functions\Debug;
		});

		$serviceContainer['reference_parser'] = function($c) {
			return new \Message\Cog\ReferenceParser($c['module.locator'], $c['fns.utility']);
		};

		// Application Contexts
		$serviceContainer['app.context.web'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Web($c);
		});

		$serviceContainer['app.context.console'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Console($c);
		});
	}

}
