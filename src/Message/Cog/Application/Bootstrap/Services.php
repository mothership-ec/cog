<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Cog\Application\Environment;
use Message\Cog\Routing\RouteCollection;
use Message\Cog\DB;

/**
 * Cog services bootstrap.
 *
 * Registers Cog service definitions when the application is loaded.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Services implements ServicesInterface
{
	/**
	 * Register the services to the given service container.
	 *
	 * @param object $serviceContainer The service container
	 */
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

		$serviceContainer['db.connection'] = $serviceContainer->share(function($s) {
			return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> $s['cfg']->db->hostname,
				'user'		=> $s['cfg']->db->user,
				'password' 	=> $s['cfg']->db->pass,
				'db'		=> $s['cfg']->db->name,
				'charset'	=> $s['cfg']->db->charset,
			));
		});

		$serviceContainer['db.query'] = function($s) {
			return new \Message\Cog\DB\Query($s['db.connection']);
		};

		// shortcut for easier access
		$serviceContainer['db'] = function($s) {
			return $s['db.query'];
		};

		$serviceContainer['db.transaction'] = function($s) {
			return new \Message\Cog\DB\Transaction($s['db.connection']);
		};

		$serviceContainer['db.nested_set_helper'] = function($s) {
			return new \Message\Cog\DB\NestedSetHelper($s['db.query'], $s['db.transaction']);
		};

		$serviceContainer['cache'] = $serviceContainer->share(function($s) {
			$adapterClass = (extension_loaded('apc') && ini_get('apc.enabled')) ? 'APC' : 'Filesystem';
			$adapterClass = '\Message\Cog\Cache\Adapter\\' . $adapterClass;
			$cache        = new \Message\Cog\Cache\Instance(
				new $adapterClass
			);
			$cache->setPrefix(implode('.', array(
				$s['app.loader']->getAppName(),
				$s['environment']->get(),
				$s['environment']->installation(),
			)));

			return $cache;
		});

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
							new \Symfony\Component\Templating\Helper\SlotsHelper,
							$c['form.helper.php']
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

		$serviceContainer['http.session'] = $serviceContainer->share(function() {
			return new \Symfony\Component\HttpFoundation\Session\Session;
		});

		$serviceContainer['http.cookies'] = $serviceContainer->share(function() {
			return new \Message\Cog\HTTP\CookieCollection;
		});

		$serviceContainer['response_builder'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Controller\ResponseBuilder(
				$c['templating']
			);
		});

		$serviceContainer['config.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Config\LoaderCache(
				$c['app.loader']->getBaseDir() . 'config/',
				$c['environment'],
				$c['cache']
			);
		});

		$serviceContainer['cfg'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Config\Registry($c['config.loader']);
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

		$serviceContainer['reference_parser'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\ReferenceParser($c['module.locator'], $c['fns.utility']);
		});

		// Application Contexts
		$serviceContainer['app.context.web'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Web($c);
		});

		$serviceContainer['app.context.console'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Console($c);
		});


		// Forms
//		$serviceContainer['form.provider'] = function($c) {
//			return new \Message\Cog\Form\ServiceProvider;
//		};

//		$serviceContainer['form.data'] = function($c) {
//			return new \Message\Cog\Form\Data;
//		};

		$serviceContainer['form.builder.php'] = function($c) {
			return $c['form.factory.php']->createBuilder();
		};

		$serviceContainer['form.builder.twig'] = function($c) {
			return $c['form.factory.twig']->createBuilder();
		};

		$serviceContainer['form.factory.php'] = function($c) {
			$builder = new \Message\Cog\Form\Factory\Builder($c, 'php');
			return $builder->getFormFactory();
		};

		$serviceContainer['form.factory.twig'] = function($c) {
			$builder = new \Message\Cog\Form\Factory\Builder($c, 'twig');
			return $builder->getFormFactory();
		};

		$serviceContainer['form.handler'] = function($c) {
			return new \Message\Cog\Form\Handler($c);
		};

		$serviceContainer['form.helper.php'] = function($c) {
			$engine = $c['form.engine.php'];

			$formHelper = new \Message\Cog\Form\Helper(
				new \Symfony\Component\Form\FormRenderer(
					new \Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine($engine)
				)
			);

			return $formHelper;

		};

		// @todo add form.helper.twig

		$serviceContainer['form.engine.php'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Templating\PhpEngine(
				new \Message\Cog\Form\Template\SimpleTemplateNameParser('php'),
				new \Symfony\Component\Templating\Loader\FilesystemLoader(array())
			);
		});

		// @todo add form.engine.twig

		// Validator
		$serviceContainer['validator'] = function($c) {
			return new \Message\Cog\Validation\Validator(
				new \Message\Cog\Validation\Loader(
					new \Message\Cog\Validation\Messages,
					array(
						new \Message\Cog\Validation\Rule\Date,
						new \Message\Cog\Validation\Rule\Number,
//						new \Message\Cog\Validation\Rule\Iterable, - not working yet
						new \Message\Cog\Validation\Rule\Text,
						new \Message\Cog\Validation\Rule\Other,
						new \Message\Cog\Validation\Filter\Text,
						new \Message\Cog\Validation\Filter\Type,
						new \Message\Cog\Validation\Filter\Other,
					)
				)
			);
		};

	}
}