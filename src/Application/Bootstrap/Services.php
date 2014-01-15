<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog;

use Message\Cog\Bootstrap\ServicesInterface;
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
		$serviceContainer['profiler'] = $serviceContainer->share(function($s) {
			return new \Message\Cog\Debug\Profiler(null, function() use ($s) {
				return $s['db']->getQueryCount();
			}, false);
		});

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
			$generator->setCsrfSecrets($c['http.session'], $c['cfg']->app->csrfSecret);

			return $generator;
		};

		// Service for the templating delegation engine
		$serviceContainer['templating'] = function($c) {
			return new \Message\Cog\Templating\DelegatingEngine(
				array(
					// Twig templating engine
					$c['templating.engine.twig'],
					$c['templating.engine.php'],
				)
			);
		};

		$serviceContainer['templating.formats'] = function($c) {
			$formats = array();

			// If there is a request available
			if (isset($c['request'])) {
				// Get available content types for request.
				$request = $c['request'];

				$contentTypes = $request->getAllowedContentTypes();

				foreach($contentTypes as $key => $mimeType) {
					$formats[$key] = $request->getFormat($mimeType);
				}

				// Remove duplicate formats caused by html / xhtml etc.
				$formats = array_unique($formats);

				// If there is a request and the formats are empty then fill
				// with a default html format.
				// @see https://github.com/messagedigital/cog/issues/214
				if (empty($formats) or (1 === count($formats) and null === $formats[0])) {
					$formats = array('html');
				}
			}

			return $formats;
		};

		$serviceContainer['templating.view_name_parser'] = function($c) {
			$parser = new \Message\Cog\Templating\ViewNameParser(
				$c['reference_parser'],
				$c['filesystem.finder'],
				array(
					'twig',
					'php',
				),
				$c['templating.formats']
			);

			$parser->addDefaultDirectory($c['app.loader']->getBaseDir() . 'view/');

			return $parser;
		};

		$serviceContainer['templating.actions_helper'] = function($c) {
			return new \Message\Cog\Templating\Helper\Actions(
				$c['http.fragment_handler'],
				$c['reference_parser']
			);
		};

		$serviceContainer['templating.twig.loader'] = function($c) {
			return new \Message\Cog\Templating\TwigFilesystemLoader(array(
				'/'
			), $c['templating.view_name_parser']);
		};

		$serviceContainer['templating.twig.environment'] = function($c) {
			$twigEnvironment = new \Twig_Environment(
				$c['templating.twig.loader'],
				array(
					'cache'       => 'cog://tmp',
					'auto_reload' => true,
					'debug'       => 'live' !== $c['env'],
					'autoescape'  => function($name) {
						// Trim off the .twig file extension
						if ('.twig' === substr($name, -5)) {
							$name = substr($name, 0, -5);
						}

						// Get the actual file extension (format)
						$format = substr($name, strrpos($name, '.') + 1);

						// If the format is html, css or js, set that as the autoescape strategy
						if (in_array($format, array('html', 'js', 'css'))) {
							return $format;
						}

						// Otherwise, turn off autoescaping (for example, .txt files for plaintext emails)
						return false;
					}
				)
			);

			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\HttpKernel($c['templating.actions_helper']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\Routing($c['routing.generator']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\Translation($c['translator']));
			$twigEnvironment->addExtension(new \Message\Cog\Templating\Twig\Extension\PriceTwigExtension());
			$twigEnvironment->addExtension(new \Message\Cog\Module\Templating\Twig\Extension\ModuleExists($c['module.loader']));
			$twigEnvironment->addExtension($c['form.twig_form_extension']);
			$twigEnvironment->addExtension(new \Assetic\Extension\Twig\AsseticExtension($c['asset.factory']));
			if ('live' !== $c['env']) {
				$twigEnvironment->addExtension(new \Twig_Extension_Debug);
			}
			$twigEnvironment->addGlobal('app', $c['templating.globals']);

			return $twigEnvironment;
		};

		$serviceContainer['templating.engine.php'] = function($c) {
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
		};

		$serviceContainer['templating.filesystem.loader'] = function($c) {
			return new \Symfony\Component\Templating\Loader\FilesystemLoader(
				array(
					$c['app.loader']->getBaseDir(),
					'cog://Message:Cog::Form:View:Php',
				)
			);
		};

		$serviceContainer['templating.engine.twig'] = function($c) {
			return new \Message\Cog\Templating\TwigEngine(
				$c['templating.twig.environment'],
				$c['templating.view_name_parser']
			);
		};

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

			$globals->set('request', function($services) {
				if (isset($services['request'])) {
					return $services['request'];
				}
			});

			return $globals;
		});

		$serviceContainer['http.cache.esi'] = $serviceContainer->share(function($c) {
			return new \Symfony\Component\HttpKernel\HttpCache\Esi;
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
			$inlineRenderer = new \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer($c['http.kernel']);

			return new \Symfony\Component\HttpKernel\Fragment\FragmentHandler(array(
				new \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer($c['http.cache.esi'], $inlineRenderer),
				$inlineRenderer
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
			$classLoader = $c['class.loader'];
			$prefixes    = array();

			// Get PSR-0 prefixes and append the namespace directories
			foreach ($classLoader->getPrefixes() as $prefix => $dirs) {
				$prefix      = trim($prefix, '\\');
				$prefixAsDir = str_replace('\\', DIRECTORY_SEPARATOR, $prefix);

				foreach ($dirs as $key => $dir) {
					$dirs[$key] = $dir .= DIRECTORY_SEPARATOR . $prefixAsDir;
				}

				$prefixes[rtrim($prefix)] = $dirs;
			}

			// If the Composer autoloader supports PSR-4, grab those too
			if (method_exists($classLoader, 'getPrefixesPsr4')) {
				foreach ($classLoader->getPrefixesPsr4() as $prefix => $dirs) {
					$prefixes[trim($prefix, '\\')] = $dirs;
				}
			}

			return new \Message\Cog\Module\Locator($prefixes);
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
				"/^\/tmp\/(.*)/us"        => $baseDir.'tmp/$1',
				"/^\/logs\/(.*)/us"       => $baseDir.'logs/$1',
				"/^\/logs/us"             => $baseDir.'logs/',
				"/^\/public\/(.*)/us"     => $baseDir.'public/$1',
				"/^\/data\/(.*)/us"       => $baseDir.'data/$1',
				"/^\/view\/(.*)/us"       => $baseDir.'view/$1',
				"/^\/view/us"             => $baseDir.'view/',
				"/^\/migrations\/(.*)/us" => $baseDir.'migrations/$1',
				"/^\/migrations/us"       => $baseDir.'migrations/',
			);

			return $mapping;
		};

		$serviceContainer['filesystem'] = function($c) {
			return new \Message\Cog\Filesystem\Filesystem;
		};

		$serviceContainer['filesystem.finder'] = function($c) {
			return new \Message\Cog\Filesystem\Finder;
		};

		$serviceContainer['filesystem.conversion.pdf'] = function($c) {
			return new \Message\Cog\Filesystem\Conversion\PDFConverter($c);
		};

		$serviceContainer['filesystem.conversion.image'] = function($c) {
			return new \Message\Cog\Filesystem\Conversion\ImageConverter($c);
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
				new \Message\Cog\Form\Extension\Extension,
				new \Symfony\Component\Form\Extension\Core\CoreExtension,
				new \Symfony\Component\Form\Extension\Csrf\CsrfExtension(
					new \Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider($c['http.session'], $c['form.csrf_secret'])
				),
			);
		};

		$serviceContainer['form.csrf_secret'] = function($c) {
			$parts = array(
				$c['cfg']->app->csrfSecret,							// Global CSRF secret key
				$c['http.request.master']->headers->get('host'),	// HTTP host
				$c['environment'],									// Application environment
				$c['http.request.master']->getClientIp(),			// User's IP address
//				$c['http.session']->getId(),						// Session ID
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
				'Message:Cog::form:twig:form_div_layout',
			);
		};

		$serviceContainer['form.templates.php'] = function($c) {
			return array(
				'Message:Cog::form:php',
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
						new \Message\Cog\Validation\Rule\Type,
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
			return new \Message\Cog\Security\StringGenerator;
		});

		$serviceContainer['security.string-generator'] = $serviceContainer->share(function() {
			return new \Message\Cog\Security\StringGenerator;
		});

		$serviceContainer['security.hash'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Security\Hash\Bcrypt($c['security.string-generator']);
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
			$translator->setContainer($c);

			$yml = new \Message\Cog\Localisation\YamlFileLoader(
				new \Symfony\Component\Yaml\Parser
			);

			if ('local' !== $c['env']) {
				$translator->enableCaching();
			}

			$translator->addLoader('yml', $yml);

			$translator->loadCatalogue($id);


			return $translator;
		});

		$serviceContainer['asset.manager'] = $serviceContainer->share(function($c) {
			$manager = new \Assetic\Factory\LazyAssetManager($c['asset.factory'], array(
				'twig' => new \Assetic\Extension\Twig\TwigFormulaLoader($c['templating.twig.environment']),
			));

			$c['asset.factory']->setAssetManager($manager);

			return $manager;
		});

		$serviceContainer['asset.filters'] = $serviceContainer->share(function($c) {
			$manager = new \Assetic\FilterManager;

			$manager->set('csscogulerewrite', new \Message\Cog\AssetManagement\CssCoguleRewriteFilter);

			$manager->set('cssmin', new \Assetic\Filter\CssMinFilter);
			$manager->set('jsmin', new \Assetic\Filter\JSMinFilter);

			return $manager;
		});

		$serviceContainer['asset.factory'] = $serviceContainer->share(function($c) {
			$factory = new \Message\Cog\AssetManagement\Factory('cog://public/');

			$factory->setReferenceParser($c['reference_parser']);
			$factory->setFilterManager($c['asset.filters']);

			if (! $c['environment']->isLocal()) {
				$factory->enableCacheBusting();
			}

			return $factory;
		});

		$serviceContainer['asset.writer'] = $serviceContainer->share(function($c) {
			return new \Assetic\AssetWriter('cog://public');
		});

		$serviceContainer['log.errors'] = $serviceContainer->share(function($c) {
			$logger = new \Monolog\Logger('errors');

			// Set up handler for logging to file (as default)
			$logger->pushHandler(
				new \Message\Cog\Logging\TouchingStreamHandler('cog://logs/error.log')
			);

			return $logger;
		});

		$serviceContainer['whoops'] = $serviceContainer->share(function($c) {
			$run = new \Whoops\Run;
			$run->allowQuit(false);
			$run->pushHandler($c['whoops.page_handler']);

			return $run;
		});

		$serviceContainer['whoops.page_handler'] = $serviceContainer->share(function($c) {
			return new \Whoops\Handler\PrettyPageHandler;
		});

		$serviceContainer['migration.mysql'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Migration\Migrator(
				$c['migration.mysql.loader'],
				$c['migration.mysql.create'],
				$c['migration.mysql.delete']
			);
		});

		// Shortcut to mysql migration adapter
		$serviceContainer['migration'] = $serviceContainer->share(function($c) {
			return $c['migration.mysql'];
		});

		$serviceContainer['migration.mysql.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Migration\Adapter\MySQL\Loader(
				$c['db'],
				$c['filesystem.finder'],
				$c['filesystem'],
				$c['reference_parser']
			);
		});

		$serviceContainer['migration.mysql.create'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Migration\Adapter\MySQL\Create($c['db']);
		});

		$serviceContainer['migration.mysql.delete'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Migration\Adapter\MySQL\Delete($c['db']);
		});

		$serviceContainer['migration.collection'] = function($c) {
			return new \Message\Cog\Migration\Collection\Collection();
		};

		$serviceContainer['helper.prorate'] = function() {
			return new \Message\Cog\Helper\ProrateHelper;
		};

		$serviceContainer['helper.date'] = function() {
			return new \Message\Cog\Helper\DateHelper;
		};

		$serviceContainer['mail.transport'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Mail\Transport\Mail();
		});

		$serviceContainer['mail.dispatcher'] = $serviceContainer->share(function($c) {

			$swift = new \Swift_Mailer($c['mail.transport']);
			$dispatcher = new \Message\Cog\Mail\Mailer($swift);

			$dispatcher->setWhitelistFallback('dev@message.co.uk');
			$dispatcher->addToWhitelist('/.+@message\.co\.uk/');
			$dispatcher->addToWhitelist('/.+@message\.uk\.com/');

			// Only enable whitelist filtering on non-live environments
			if ($c['env']!== 'live') {
				$dispatcher->enableToFiltering();
			}

			return $dispatcher;
		});

		$serviceContainer['mail.message'] = function($c) {
			// This is all a bit hacky, but the only easy way I can think of
			// First, change the formats allowed in templating for views
			$origFormats = $c->raw('templating.formats');
			$c['templating.formats'] = array(
				'html',
				'txt',
			);

			// Now get a new instance of the templating engine (which will now be using these formats)
			$engine = $c['templating'];

			// Get an instance of Message
			$message = new \Message\Cog\Mail\Message($engine, $c['templating.view_name_parser']);

			// Now replace the old templating formats
			$c['templating.formats'] = $origFormats;

			// Set default from address
			$message->setFrom($c['cfg']->app->defaultEmailFrom->email, $c['cfg']->app->defaultEmailFrom->name);

			return $message;
		};

		$serviceContainer['country.list'] = function($c) {
			return new \Message\Cog\Location\CountryList;
		};

		$serviceContainer['country.event'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\Location\CountryEvent($c['country.list']);
		});

		$serviceContainer['state.list'] = function($c) {
			return new \Message\Cog\Location\StateList;
		};

		$serviceContainer['title.list'] = function($c) {
			return array(
				'Mr' => 'Mr',
				'Mrs' => 'Mrs',
				'Miss' => 'Miss',
				'Ms' => 'Ms',
				'Doctor' => 'Doctor'
			);
		};

		$serviceContainer['pagination'] = function($c) {
			return new \Message\Cog\Pagination\Pagination($c['pagination.adapter.sql']);
		};

		$serviceContainer['pagination.adapter.dbresult'] = function($c) {
			return new \Message\Cog\Pagination\Adapter\DBResultAdapter();
		};

		$serviceContainer['pagination.adapter.sql'] = function($c) {
			return new \Message\Cog\Pagination\Adapter\SQLAdapter($c['db.query']);
		};

		$serviceContainer['pagination.adapter.array'] = function($c) {
			return new \Message\Cog\Pagination\Adapter\ArrayAdapter();
		};
	}
}