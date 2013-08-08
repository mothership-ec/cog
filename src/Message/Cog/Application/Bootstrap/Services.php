<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog;

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
 * @author James Moss <james@message.co.uk>
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
			$adapterClass = '\\Message\\Cog\\Cache\\Adapter\\' . $adapterClass;
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

		$serviceContainer['event.dispatcher'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Event\Dispatcher($c);
		});

		$serviceContainer['routes'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Routing\CollectionManager($c['reference_parser']);
		});

		$serviceContainer['routing.matcher'] = function($c) {
			return new \Message\Cog\Routing\UrlMatcher($c['routes.compiled'], $c['http.request.context']);
		};

		$serviceContainer['routing.generator'] = function($c) {

			$generator = new \Message\Cog\Routing\UrlGenerator($c['routes.compiled'], $c['http.request.context']);
			$generator->setCsrfSecrets($c['http.session'], $c['routing.csrf_secret']);

			return $generator;
		};

		// @todo - Get this out of the config  rather than hardcoding it and change it for every site
		$serviceContainer['routing.csrf_secret'] = function($c) {
			return 'THIS IS A SECRET DO NOT SHARE IT AROUND';
		};

		// Service for the templating delegation engine
		$serviceContainer['templating'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Templating\DelegatingEngine(
				array(
					// Twig templating engine
					$c['templating.engine.twig'],
					$c['templating.engine.php'],
				)
			);
		});

		$serviceContainer['templating.view_name_parser'] = $serviceContainer->share(function($c) {
			// Get available content types for request.
			$request = $c['request'];
			$formats = array();

			$contentTypes = $request->getAllowedContentTypes();

			foreach($contentTypes as $key => $mimeType) {
				$formats[$key] = $request->getFormat($mimeType);
			}

			return new \Message\Cog\Templating\ViewNameParser(
				$c,
				$c['reference_parser'],
				array(
					'twig',
					'php',
				),
				$formats
			);
		});

		$serviceContainer['templating.actions_helper'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Templating\Helper\Actions(
				$c['http.fragment_handler'],
				$c['reference_parser']
			);
		});

		$serviceContainer['templating.twig.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Templating\TwigFilesystemLoader('/', $c['templating.view_name_parser']);
		});

		$serviceContainer['templating.twig.environment'] = $serviceContainer->share(function($c) {
			$twigEnvironment = new \Twig_Environment(
				$c['templating.twig.loader'],
				array(
					'cache'       => 'cog://tmp',
					'auto_reload' => true,
					'debug'       => 'live' !== $c['env'],

				)
			);

			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\HttpKernel($c['templating.actions_helper']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\Routing($c['routing.generator']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\Translation($c['translator']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\PriceTwigExtension());
			$twigEnvironment->addExtension($c['form.twig_form_extension']);
			$twigEnvironment->addExtension(new \Assetic\Extension\Twig\AsseticExtension($c['asset.factory']));
			if ('live' !== $c['env']) {
				$twigEnvironment->addExtension(new \Twig_Extension_Debug);
			}
			$twigEnvironment->addGlobal('app', $c['templating.globals']);

			return $twigEnvironment;
		});

		$serviceContainer['templating.engine.php'] = $serviceContainer->share(function($c) {
			$engine = new \Message\Cog\Templating\PhpEngine(
				$c['templating.view_name_parser'],
				$c['templating.filesystem.loader'],
				array(
					new \Symfony\Component\Templating\Helper\SlotsHelper,
					$c['templating.actions_helper'],
					new \Message\Cog\Templating\Helper\Routing($c['routing.generator']),
					new \Message\Cog\Templating\Helper\Translation($c['translator']),
				)
			);

			$engine->addGlobal('app', $c['templating.globals']);

			return $engine;
		});

		$serviceContainer['templating.filesystem.loader'] = $serviceContainer->share(function($c) {
			return new \Symfony\Component\Templating\Loader\FilesystemLoader(
				array(
					$c['app.loader']->getBaseDir(),
					'cog://Message:Cog::Form:View:Php',
				)
			);
		});

		$serviceContainer['templating.engine.twig'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Templating\TwigEngine(
				$c['templating.twig.environment'],
				$c['templating.view_name_parser']
			);
		});

		$serviceContainer['templating.globals'] = $serviceContainer->share(function($c) {
			$globals = new Cog\Templating\GlobalVariables($c);

			$globals->set('session', function($services) {
				return $services['http.session'];
			});

			$globals->set('cfg', function($services) {
				return $services['cfg'];
			});

			$globals->set('environment', function($services) {
				return $services['environment'];
			});

			return $globals;
		});

		$serviceContainer['http.kernel'] = function($c) {
			return new \Message\Cog\HTTP\Kernel(
				$c['event.dispatcher'],
				new \Symfony\Component\HttpKernel\Controller\ControllerResolver
			);
		};

		$serviceContainer['http.session'] = $serviceContainer->share(function($c) {
			$storage = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

			// Use an array as the session storage when running unit tests
			if ('test' === $c['env']) {
				$storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
			}

			return new \Message\Cog\HTTP\Session(
				$storage,
				null,
				new \Symfony\Component\HttpFoundation\Session\Flash\FlashBag('__cog_flashes')
			);
		});

		$serviceContainer['http.cookies'] = $serviceContainer->share(function() {
			return new \Message\Cog\HTTP\CookieCollection;
		});

		$serviceContainer['http.fragment_handler'] = $serviceContainer->share(function($c) {
			return new \Symfony\Component\HttpKernel\Fragment\FragmentHandler(array(
				new \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer($c['http.kernel'])
			), ('local' === $c['env']));
		});

		$serviceContainer['http.uri_signer'] = $serviceContainer->share(function() {
			return new \Symfony\Component\HttpKernel\UriSigner(time());
		});

		$serviceContainer['response_builder'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Controller\ResponseBuilder(
				$c['templating']
			);
		});

		$serviceContainer['config.loader'] = $serviceContainer->share(function($c) {
			if ('local' === $c['env']) {
				// When running locally, don't use the cache loader
				return new \Message\Cog\Config\Loader(
					$c['app.loader']->getBaseDir() . 'config/',
					$c['environment']
				);
			}
			else {
				return new \Message\Cog\Config\LoaderCache(
					$c['app.loader']->getBaseDir() . 'config/',
					$c['environment'],
					$c['cache']
				);
			}
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
			return new \Message\Cog\Console\Task\Collection;
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
			return new \Message\Cog\Module\ReferenceParser($c['module.locator'], $c['fns.utility']);
		});

		// Filesystem
		$serviceContainer['filesystem.stream_wrapper_manager'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Filesystem\StreamWrapperManager;
		});

		$serviceContainer['filesystem.stream_wrapper'] = function($c) {
			$wrapper = new \Message\Cog\Filesystem\StreamWrapper;
			$wrapper->setReferenceParser($c['reference_parser']);
			$wrapper->setMapping($c['filesystem.stream_wrapper_mapping']);

			return $wrapper;
		};

		$serviceContainer['filesystem.stream_wrapper_mapping'] = function($c) {
			$baseDir = $c['app.loader']->getBaseDir();
			$mapping = array(
				// Maps cog://tmp/* to /tmp/* (in the installation)
				"/^\/tmp\/(.*)/us"    => $baseDir.'tmp/$1',
				"/^\/logs\/(.*)/us"   => $baseDir.'logs/$1',
				"/^\/public\/(.*)/us" => $baseDir.'public/$1',
				"/^\/data\/(.*)/us"   => $baseDir.'data/$1',
			);

			return $mapping;
		};

		$serviceContainer['filesystem'] = function($c) {
			return new \Message\Cog\Filesystem\Filesystem;
		};

		$serviceContainer['filesystem.finder'] = function($c) {
			return new \Message\Cog\Filesystem\Finder;
		};

		// Application Contexts
		$serviceContainer['app.context.web'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Web($c);
		});

		$serviceContainer['app.context.console'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Application\Context\Console($c);
		});


		// Forms
		$serviceContainer['form'] = function($c) {
			return new \Message\Cog\Form\Handler($c);
		};

		$serviceContainer['form.handler'] = function($c) {
			return new \Message\Cog\Form\Handler($c);
		};

		$serviceContainer['form.builder'] = $serviceContainer->share(function($c) {
			return $c['form.factory']->getFormFactory()->createBuilder();
		});

		$serviceContainer['form.factory'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Form\Factory\Builder($c['form.extensions']);
		});

		$serviceContainer['form.extensions'] = function($c) {
			return array(
				new \Symfony\Component\Form\Extension\Core\CoreExtension,
				new \Symfony\Component\Form\Extension\Csrf\CsrfExtension(
					new \Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider($c['form.csrf_secret'])
				),
			);
		};

		$serviceContainer['form.csrf_secret'] = function($c) {
			$parts = array(
				$c['request']->headers->get('host'),
				$c['user.current']->email,
				$c['user.current']->id,
				$c['user.current']->lastLoginAt,
				$c['environment'],
			);

			return serialize($parts);
		};

		$serviceContainer['form.helper.php'] = function($c) {
			$engine = $c['templating.engine.php'];

			$formHelper = new \Message\Cog\Form\Template\Helper(
				new \Symfony\Component\Form\FormRenderer(
					new \Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine(
						$engine,
						$c['form.templates.php']
					),
					null
				)
			);

			return $formHelper;

		};

		$serviceContainer['form.helper.twig'] = $serviceContainer->share(function($c) {
			$formHelper = new \Message\Cog\Form\Template\Helper(
				$c['form.renderer.twig']
			);

			return $formHelper;
		});

		$serviceContainer['form.renderer.twig'] = function($c) {
			return new \Symfony\Bridge\Twig\Form\TwigRenderer(
				new \Symfony\Bridge\Twig\Form\TwigRendererEngine(
					$c['form.templates.twig']
				)
			);
		};

		$serviceContainer['form.renderer.engine.twig'] = function($c) {
			return new \Symfony\Bridge\Twig\Form\TwigRendererEngine($c['form.templates.twig']);
		};

		$serviceContainer['form.templates.twig'] = function($c) {
			return array(
				'Message:Cog:Form::Twig:form_div_layout',
			);
		};

		$serviceContainer['form.templates.php'] = function($c) {
			return array(
				'Message:Cog:Form::Php',
			);
		};

		$serviceContainer['form.twig_form_extension'] = function($c) {
			return new \Symfony\Bridge\Twig\Extension\FormExtension($c['form.renderer.twig']);
		};

		// Validator
		$serviceContainer['validator'] = function($c) {
			return new \Message\Cog\Validation\Validator(
				new \Message\Cog\Validation\Loader(
					new \Message\Cog\Validation\Messages,
					array(
						new \Message\Cog\Validation\Rule\Date,
						new \Message\Cog\Validation\Rule\Number,
						new \Message\Cog\Validation\Rule\Iterable,
						new \Message\Cog\Validation\Rule\Text,
						new \Message\Cog\Validation\Rule\Other,
						new \Message\Cog\Validation\Filter\Text,
						new \Message\Cog\Validation\Filter\Type,
						new \Message\Cog\Validation\Filter\Other,
					)
				)
			);
		};

		$serviceContainer['security.salt'] = $serviceContainer->share(function() {
			return new \Message\Cog\Security\Salt;
		});

		$serviceContainer['security.hash'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Security\Hash\Bcrypt($c['security.salt']);
		});


		// Hardcode to en_GB for the moment. In the future this can be determined
		// from properties on the route or the session object
		$serviceContainer['locale'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Localisation\Locale('en_GB');
		});

		$serviceContainer['translator'] = $serviceContainer->share(function ($c) {
			$selector = new \Message\Cog\Localisation\MessageSelector;
			$id       = $c['locale']->getId();

			$translator = new \Message\Cog\Localisation\Translator($id, $selector);
			$translator->setFallbackLocale($c['locale']->getFallback());

			$translator->addLoader('yml', new \Message\Cog\Localisation\YamlFileLoader(
				new \Symfony\Component\Yaml\Parser
			));

			// Load translation files from modules
			foreach ($c['module.loader']->getModules() as $moduleName) {
				$moduleName = str_replace('\\', $c['reference_parser']::SEPARATOR, $moduleName);
				$dir        = 'cog://@' . $moduleName . $c['reference_parser']::MODULE_SEPARATOR . 'translations';

				if (file_exists($dir)) {
					foreach ($c['filesystem.finder']->in($dir) as $file) {
						$translator->addResource('yml', $file->getPathname(), $file->getFilenameWithoutExtension());
					}
				}
			}

			// Load application translation files
			$dir = $c['app.loader']->getBaseDir().'translations';
			foreach ($c['filesystem.finder']->in($dir) as $file) {
				$translator->addResource('yml', $file->getPathname(), $file->getFilenameWithoutExtension());
			}

			return $translator;
		});

		$serviceContainer['asset.manager'] = $serviceContainer->share(function($c) {
			$manager = new \Assetic\Factory\LazyAssetManager($c['asset.factory'], array(
				'twig' => new \Assetic\Extension\Twig\TwigFormulaLoader($c['templating.twig.environment']),
			));

			$c['asset.factory']->setAssetManager($manager);

			return $manager;
		});

		$serviceContainer['asset.factory'] = $serviceContainer->share(function($c) {
			$factory = new \Message\Cog\AssetManagement\Factory($c['app.loader']->getBaseDir());

			$factory->setReferenceParser($c['reference_parser']);

			return $factory;
		});

		$serviceContainer['asset.writer'] = $serviceContainer->share(function($c) {
			return new \Assetic\AssetWriter('cog://public');
		});
	}
}