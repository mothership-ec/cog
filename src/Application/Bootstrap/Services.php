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
	 * @param object $services The service container
	 */
	public function registerServices($services)
	{
		$services['currency_formatter'] = $services->factory(function($c) {
			return new \NumberFormatter($c['locale']->getID(), \NumberFormatter::CURRENCY);
		});

		$services['profiler'] = function($s) {
			return new Cog\Debug\Profiler(null, function() use ($s) {
				return $s['db.connection']->getQueryCount();
			}, false);
		};

		$services['db.connection'] = function($s) {
			$connection = new Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> $s['cfg']->db->hostname,
				'user'		=> $s['cfg']->db->user,
				'password' 	=> $s['cfg']->db->pass,
				'db'		=> $s['cfg']->db->name,
				'charset'	=> $s['cfg']->db->charset,
			));

			$cache = (isset($s['cfg']->db->cache)) ?
				$s['db.cache.collection']->get($s['cfg']->db->cache) :
				$s['db.cache.collection']->get('mysql_memory')
			;

			$connection->setCache($cache);

			return $connection;
		};

		$services['db.query.parser'] = function($s) {
			return new Cog\DB\QueryParser($s['db.connection']);
		};

		$services['db.query'] = $services->factory(function($s) {
			return new Cog\DB\Query($s['db.connection'], $s['db.query.parser']);
		});

		$services['db.query.builder'] = $services->factory(function($s) {
			return new Cog\DB\QueryBuilder($s['db.connection'], $s['db.query.parser']);
		});


		$services['db.query.builder.factory'] = $services->factory(function($c) {
			return new Cog\DB\QueryBuilderFactory($c['db.connection'], $c['db.query.parser']);
		});

		// shortcut for easier access
		$services['db'] = $services->raw('db.query');

		$services['db.transaction'] = $services->factory(function($c) {
			return new Cog\DB\Transaction($c['db.connection'], $c['db.query.parser'], $c['event.dispatcher']);
		});

		$services['db.nested_set_helper'] = $services->factory(function($s) {
			return new Cog\DB\NestedSetHelper($s['db.query'], $s['db.transaction']);
		});

		$services['db.cache.collection'] = function($c) {
			return new Cog\DB\Adapter\CacheCollection([
				$c['db.cache.none'],
				$c['db.cache.mysql.memory'],
			]);
		};

		$services['db.cache.none'] = function($c) {
			return new Cog\DB\Adapter\NoneCache;
		};

		$services['db.cache.mysql.memory'] = function($c) {
			return new Cog\DB\Adapter\MySQLi\MemoryCache;
		};

		$services['event'] = $services->factory(function() {
			return new Cog\Event\Event;
		});

		$services['event.dispatcher'] = function($c) {
			return new Cog\Event\Dispatcher($c);
		};

		$services['routes'] = function($c) {
			return new Cog\Routing\CollectionManager($c['reference_parser']);
		};

		$services['routing.matcher'] = $services->factory(function($c) {
			return new Cog\Routing\UrlMatcher($c['routes.compiled'], $c['http.request.context']);
		});

		$services['routing.generator'] = $services->factory(function($c) {
			$generator = new Cog\Routing\UrlGenerator($c['routes.compiled'], $c['http.request.context']);
			$generator->setCsrfSecrets($c['http.session'], $c['cfg']->app->csrfSecret);

			return $generator;
		});

		// Service for the templating delegation engine
		$services['templating'] = $services->factory(function($c) {
			return new Cog\Templating\DelegatingEngine(
				array(
					// Twig templating engine
					$c['templating.engine.twig'],
					$c['templating.engine.php'],
				)
			);
		});

		$services['templating.formats'] = $services->factory(function($c) {
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
		});

		$services['templating.view_name_parser'] = $services->factory(function($c) {
			$parser = new Cog\Templating\ViewNameParser(
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
		});

		$services['templating.actions_helper'] = $services->factory(function($c) {
			return new Cog\Templating\Helper\Actions(
				$c['http.fragment_handler'],
				$c['reference_parser']
			);
		});

		$services['templating.twig.loader'] = $services->factory(function($c) {
			return new Cog\Templating\TwigFilesystemLoader(array(
				'/'
			), $c['templating.view_name_parser']);
		});

		$services['templating.twig.environment'] = $services->factory(function($c) {
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

						// if txt, don't escape
						if ($format === 'txt') {
							return false;
						}

						// If the format is html, css or js, set that as the autoescape strategy
						if (in_array($format, array('js', 'css'))) {
							return $format;
						}

						// Default html
						return 'html';
					}
				)
			);

			$assetFactory = $c['asset.factory'];
			$assetManager = $c['asset.manager'];
			$assetManager->setLoader('twig', new \Assetic\Extension\Twig\TwigFormulaLoader($twigEnvironment));
			$assetFactory->setAssetManager($c['asset.manager']);

			$twigEnvironment->addExtension(new Cog\Templating\Twig\Extension\HttpKernel($c['templating.actions_helper']));
			$twigEnvironment->addExtension(new Cog\Templating\Twig\Extension\Routing($c['routing.generator']));
			$twigEnvironment->addExtension(new Cog\Templating\Twig\Extension\Translation($c['translator']));
			$twigEnvironment->addExtension(new Cog\Templating\Twig\Extension\PriceTwigExtension());
			$twigEnvironment->addExtension(new Cog\Module\Templating\Twig\Extension\ModuleExists($c['module.loader']));
			$twigEnvironment->addExtension($c['form.twig_form_extension']);
			$twigEnvironment->addExtension(new \Assetic\Extension\Twig\AsseticExtension($assetFactory));
			if ('live' !== $c['env']) {
				$twigEnvironment->addExtension(new \Twig_Extension_Debug);
			}
			$twigEnvironment->addGlobal('app', $c['templating.globals']);

			return $twigEnvironment;
		});

		$services['templating.engine.php'] = $services->factory(function($c) {
			$engine = new Cog\Templating\PhpEngine(
				$c['templating.view_name_parser'],
				$c['templating.filesystem.loader'],
				array(
					new \Symfony\Component\Templating\Helper\SlotsHelper,
					$c['templating.actions_helper'],
					new Cog\Templating\Helper\Routing($c['routing.generator']),
					new Cog\Templating\Helper\Translation($c['translator']),
				)
			);

			$engine->addGlobal('app', $c['templating.globals']);

			return $engine;
		});

		$services['templating.filesystem.loader'] = $services->factory(function($c) {
			return new \Symfony\Component\Templating\Loader\FilesystemLoader(
				array(
					$c['app.loader']->getBaseDir(),
					'cog://Message:Cog::Form:View:Php',
				)
			);
		});

		$services['templating.engine.twig'] = $services->factory(function($c) {
			return new Cog\Templating\TwigEngine(
				$c['templating.twig.environment'],
				$c['templating.view_name_parser']
			);
		});

		$services['templating.globals'] = function($c) {
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
		};

		$services['http.cache.esi'] = function($c) {
			return new \Symfony\Component\HttpKernel\HttpCache\Esi;
		};

		$services['http.kernel'] = $services->factory(function($c) {
			return new Cog\HTTP\Kernel(
				$c['event.dispatcher'],
				new \Symfony\Component\HttpKernel\Controller\ControllerResolver
			);
		});

		$services['http.session'] = function($c) {
			$namespace = isset($c['cfg']->app->sessionNamespace) ? $c['cfg']->app->sessionNamespace : 'cog';
			$storage   = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(
				array(),
				null,
				new \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag(
					sprintf('__%s_meta', $namespace)
				)
			);

			// Use an array as the session storage when running unit tests or from the command line
			if ('test' === $c['env']
             || 'cli' === php_sapi_name()) {
				$storage = new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
			}

			return new Cog\HTTP\Session(
				$storage,
				new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag(
					sprintf('__%s_attributes', $namespace)
				),
				new \Symfony\Component\HttpFoundation\Session\Flash\FlashBag(
					sprintf('__%s_flashes', $namespace)
				)
			);
		};

		$services['http.cookies'] = function() {
			return new Cog\HTTP\CookieCollection;
		};

		$services['http.fragment_handler'] = function($c) {
			$inlineRenderer = new \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer($c['http.kernel']);

			return new \Symfony\Component\HttpKernel\Fragment\FragmentHandler(array(
				new \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer($c['http.cache.esi'], $inlineRenderer),
				$inlineRenderer
			), ('local' === $c['env']));
		};

		$services['http.uri_signer'] = function() {
			return new \Symfony\Component\HttpKernel\UriSigner(time());
		};

		$services['response_builder'] = function($c) {
			return new Cog\Controller\ResponseBuilder(
				$c['templating']
			);
		};

		$services['config.loader'] = function($c) {
			if ('local' === $c['env']) {
				// When running locally, don't use the cache loader
				return new Cog\Config\Loader(
					$c['app.loader']->getBaseDir() . 'config/',
					$c['environment']
				);
			}
			else {
				return new Cog\Config\LoaderCache(
					$c['app.loader']->getBaseDir() . 'config/',
					$c['environment'],
					$c['cache']
				);
			}
		};

		$services['cfg'] = function($c) {
			return new Cog\Config\Registry($c['config.loader']);
		};

		$services['module.locator'] = function($c) {
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

			return new Cog\Module\Locator($prefixes);
		};

		$services['module.loader'] = function($c) {
			return new Cog\Module\Loader(
				$c['module.locator'],
				$c['bootstrap.loader'],
				$c['event.dispatcher'],
				$c['log.errors']
			);
		};

		$services['task.collection'] = function($c) {
			return new Cog\Console\Task\Collection;
		};

		// Functions
		$services['fns.text'] = function() {
			return new Cog\Functions\Text;
		};
		$services['fns.utility'] = function($c) {
			return new Cog\Functions\Utility($c['module.loader']);
		};
		$services['fns.debug'] = function($c) {
			return new Cog\Functions\Debug;
		};

		$services['reference_parser'] = function($c) {
			return new Cog\Module\ReferenceParser($c['module.locator'], $c['fns.utility']);
		};

		// Filesystem
		$services['filesystem.stream_wrapper_manager'] = function($c) {
			return new Cog\Filesystem\StreamWrapperManager;
		};

		$services['filesystem.stream_wrapper'] = $services->factory(function($c) {
			$wrapper = new Cog\Filesystem\StreamWrapper;
			$wrapper->setReferenceParser($c['reference_parser']);
			$wrapper->setMapping($c['filesystem.stream_wrapper_mapping']);

			return $wrapper;
		});

		$services['filesystem.stream_wrapper_mapping'] = function($c) {
			$baseDir = $c['app.loader']->getBaseDir();
			$mapping = array(
				// Maps cog://tmp/* to /tmp/* (in the installation)
				"/^\/tmp(\/.*)?$/us"      => $baseDir.'tmp$1',
				"/^\/logs\/(.*)/us"       => $baseDir.'logs/$1',
				"/^\/logs/us"             => $baseDir.'logs/',
				"/^\/public(\/.*)?$/us"   => $baseDir.'public$1',
				"/^\/data(\/.*)?$/us"     => $baseDir.'data$1',
				"/^\/view\/(.*)/us"       => $baseDir.'view/$1',
				"/^\/view/us"             => $baseDir.'view/',
				"/^\/migrations\/(.*)/us" => $baseDir.'migrations/$1',
				"/^\/migrations/us"       => $baseDir.'migrations/',
				"/^\/certs(\/.*)?$/us"    => $baseDir.'certs$1',
			);

			return $mapping;
		};

		$services['filesystem'] = $services->factory(function($c) {
			return new Cog\Filesystem\Filesystem;
		});

		$services['filesystem.finder'] = $services->factory(function($c) {
			return new Cog\Filesystem\Finder;
		});

		$services['filesystem.conversion.pdf'] = $services->factory(function($c) {
			return new Cog\Filesystem\Conversion\PDFConverter($c);
		});

		$services['filesystem.conversion.image'] = $services->factory(function($c) {
			return new Cog\Filesystem\Conversion\ImageConverter($c);
		});

		// Fields
		$services['field.factory'] = $services->factory(function($c) {
			return new \Message\Cog\Field\Factory(/*$c['field.collection']*/);
		});

		$services['field.form'] = $services->factory(function($c) {
			return new \Message\Cog\Field\Form($c['form.factory']);
		});

		$services['field.collection'] = function($c) {
			return new \Message\Cog\Field\Collection(array(
				new \Message\Cog\Field\Type\Boolean,
				new \Message\Cog\Field\Type\Checkbox,
				new \Message\Cog\Field\Type\Choice,
				new \Message\Cog\Field\Type\Datalist,
				new \Message\Cog\Field\Type\Date,
				new \Message\Cog\Field\Type\Datetime,
				new \Message\Cog\Field\Type\Html,
				new \Message\Cog\Field\Type\Integer,
				new \Message\Cog\Field\Type\MultiChoice,
				new \Message\Cog\Field\Type\Richtext($c['markdown.parser']),
				new \Message\Cog\Field\Type\Text,
				new \Message\Cog\Field\Type\Hidden,
			));
		};

		$services['field.content.builder'] = $services->factory(function($c) {
			return new \Message\Cog\Field\ContentBuilder;
		});

		$services['markdown.parser'] = $services->factory(function() {
			return new \dflydev\markdown\MarkdownParser;
		});

		// Application Contexts
		$services['app.context.web'] = function($c) {
			return new Cog\Application\Context\Web($c);
		};

		$services['app.context.console'] = function($c) {
			return new Cog\Application\Context\Console($c);
		};

		// Forms
		/**
		 * @deprecated Use symfony form directly instead (form.factory and form.builder).
		 */
		$services['form'] = $services->factory(function($c) {
			return new \Message\Cog\Form\Handler($c);
		});

		/**
		 * @deprecated Use symfony form directly instead (form.factory and form.builder).
		 */
		$services['form.handler'] = $services->factory(function($c) {
			return new \Message\Cog\Form\Handler($c);
		});

		$services['form.builder'] = $services->factory(function($c) {
			return $c['form.factory']->createBuilder();
		});

		$services['form.factory'] = function($c) {
			return $c['form.factory.builder']->getFormFactory();
		};

		$services['form.factory.builder'] = function($c) {
			return new \Message\Cog\Form\Factory\Builder($c['form.extensions']);
		};

		$services['form.extensions'] = function($c) {
			return [
				new \Message\Cog\Form\Extension\Core\CoreExtension($c['http.session'], $c['cfg'], $c['translator']),
				new \Symfony\Component\Form\Extension\Core\CoreExtension,
				new \Symfony\Component\Form\Extension\Csrf\CsrfExtension(
					new \Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider($c['http.session'], $c['form.csrf_secret'])
				),
				new \Symfony\Component\Form\Extension\Validator\ValidatorExtension($c['symfony.validator']),
				new \Message\Cog\Form\Extension\Validator\ValidatorExtension($c['http.session'], $c['translator']),
			];
		};

		$services['form.csrf_secret'] = $services->factory(function($c) {
			$parts = array(
				$c['cfg']->app->csrfSecret,							// Global CSRF secret key
				$c['http.request.master']->headers->get('host'),	// HTTP host
				$c['environment'],									// Application environment
				$c['http.request.master']->getClientIp(),			// User's IP address
//				$c['http.session']->getId(),						// Session ID
			);

			return serialize($parts);
		});

		$services['form.helper.php'] = $services->factory(function($c) {
			$engine = $c['templating.engine.php'];

			$formHelper = new Cog\Form\Template\Helper(
				new \Symfony\Component\Form\FormRenderer(
					new \Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine(
						$engine,
						$c['form.templates.php']
					),
					null
				)
			);

			return $formHelper;
		});

		$services['form.helper.twig'] = function($c) {
			$formHelper = new Cog\Form\Template\Helper(
				$c['form.renderer.twig']
			);

			return $formHelper;
		};

		$services['form.renderer.twig'] = $services->factory(function($c) {
			return new \Symfony\Bridge\Twig\Form\TwigRenderer(
				new \Symfony\Bridge\Twig\Form\TwigRendererEngine(
					$c['form.templates.twig']
				)
			);
		});

		$services['form.renderer.engine.twig'] = $services->factory(function($c) {
			return new \Symfony\Bridge\Twig\Form\TwigRendererEngine($c['form.templates.twig']);
		});

		$services['form.templates.twig'] = function($c) {
			return array(
				'Message:Cog::form:twig:form_div_layout',
			);
		};

		$services['form.templates.php'] = function($c) {
			return array(
				'Message:Cog::form:php',
			);
		};

		$services['form.twig_form_extension'] = $services->factory(function($c) {
			return new \Symfony\Bridge\Twig\Extension\FormExtension($c['form.renderer.twig']);
		});

		// Validator
		/**
		 * @deprecated Use symfony's validation component (symfony.validator) instead.
		 */
		$services['validator'] = $services->factory(function($c) {
			return new Cog\Validation\Validator(
				new Cog\Validation\Loader(
					new Cog\Validation\Messages,
					array(
						new Cog\Validation\Rule\Type,
						new Cog\Validation\Rule\Date,
						new Cog\Validation\Rule\Number,
						new Cog\Validation\Rule\Iterable,
						new Cog\Validation\Rule\Text,
						new Cog\Validation\Rule\Other,
						new Cog\Validation\Filter\Text,
						new Cog\Validation\Filter\Type,
						new Cog\Validation\Filter\Other,
					)
				)
			);
		});

		$services['symfony.validator'] = function($c) {
			if (isset($c['translator'])) {
				$language = \Locale::getPrimaryLanguage($c['locale']->getId());
				$c['translator']->addResource(
					'xliff',
					'cog://vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators.'.$language.'.xlf',
					'en',
					'validators'
				);
			}

			return new \Symfony\Component\Validator\Validator(
				new \Symfony\Component\Validator\Mapping\ClassMetadataFactory(
					new \Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader()
				),
				$c['symfony.validator.validator_factory'],
				isset($c['translator']) ? $c['translator'] : new \Symfony\Component\Validator\DefaultTranslator()
			);
		};

		$services['symfony.validator.validator_factory'] = function($c) {
			// @todo write our own one, to define how to load validators for a given constraint
			return new \Symfony\Component\Validator\ConstraintValidatorFactory();
		};

		$services['security.salt'] = function() {
			return new Cog\Security\StringGenerator;
		};

		$services['security.string-generator'] = function() {
			return new Cog\Security\StringGenerator;
		};

		$services['security.hash'] = function($c) {
			return new Cog\Security\Hash\Bcrypt($c['security.string-generator']);
		};

		// Hardcode to en_GB for the moment. In the future this can be determined
		// from properties on the route or the session object
		$services['locale'] = function($c) {
			return new Cog\Localisation\Locale('en_GB');
		};

		$services['translator'] = function ($c) {
			$selector = new Cog\Localisation\MessageSelector;
			$id       = $c['locale']->getId();

			$translator = new Cog\Localisation\Translator($id, $selector);
			$translator->setFallbackLocale($c['locale']->getFallback());
			$translator->setContainer($c);

			$yml = new Cog\Localisation\YamlFileLoader(
				new \Symfony\Component\Yaml\Parser
			);

			if ('local' !== $c['env']) {
				$translator->enableCaching();
			}

			$translator->addLoader('yml', $yml);
			$translator->addLoader('xliff', new \Symfony\Component\Translation\Loader\XliffFileLoader);

			$translator->loadCatalogue($id);

			return $translator;
		};

		$services['asset.manager'] = function($c) {
			$manager = new \Assetic\Factory\LazyAssetManager($c['asset.factory']);

			if (!$c['asset.factory']->getAssetManager()) {
				$c['asset.factory']->setAssetManager($manager);
			}

			return $manager;
		};

		$services['asset.filters'] = function($c) {
			$manager = new \Assetic\FilterManager;

			$manager->set('csscogulerewrite', new Cog\AssetManagement\CssCoguleRewriteFilter);

			$manager->set('cssmin', new \Assetic\Filter\CssMinFilter);
			$manager->set('jsmin', new \Assetic\Filter\JSMinFilter);

			return $manager;
		};

		$services['asset.factory'] = function($c) {
			$factory = new Cog\AssetManagement\Factory('cog://public/');

			$factory->setReferenceParser($c['reference_parser']);
			$factory->setFilterManager($c['asset.filters']);

			if ('local' !== $c['env']) {
				$factory->enableCacheBusting();
			}

			return $factory;
		};

		$services['asset.writer'] = function($c) {
			return new \Assetic\AssetWriter('cog://public');
		};

		$services['log.errors'] = function($c) {
			$logger = new \Monolog\Logger('errors');

			// Set up handler for logging to file (as default)
			$logger->pushHandler(
				new Cog\Logging\TouchingStreamHandler('cog://logs/error.log')
			);

			return $logger;
		};

		$services['log.console'] = function($c) {
			$logger = new \Monolog\Logger('console');

			// Set up handler for logging to file (as default)
			$logger->pushHandler(
				new Cog\Logging\TouchingStreamHandler('cog://logs/console.log')
			);

			return $logger;
		};

		$services['whoops'] = function($c) {
			$run = new \Whoops\Run;
			$run->allowQuit(false);
			$run->pushHandler($c['whoops.page_handler']);

			return $run;
		};

		$services['whoops.page_handler'] = function($c) {
			return new \Message\Cog\Debug\Whoops\SimpleHandler;
		};

		$services['migration.mysql'] = function($c) {
			return new Cog\Migration\Migrator(
				$c['migration.mysql.loader'],
				$c['migration.mysql.create'],
				$c['migration.mysql.delete']
			);
		};

		// Shortcut to mysql migration adapter
		$services['migrator'] = function($c) {
			return $c['migration.mysql'];
		};

		// Preserved for backwards compatibility. Use more accurately named `migrator` instead.
		$services['migration'] = function($c) {
			return $c['migration.mysql'];
		};

		$services['migration.mysql.loader'] = function($c) {
			return new Cog\Migration\Adapter\MySQL\Loader(
				$c['db'],
				$c['filesystem.finder'],
				$c['filesystem'],
				$c['reference_parser']
			);
		};

		$services['migration.mysql.create'] = function($c) {
			return new Cog\Migration\Adapter\MySQL\Create($c['db']);
		};

		$services['migration.mysql.delete'] = function($c) {
			return new Cog\Migration\Adapter\MySQL\Delete($c['db']);
		};

		$services['helper.prorate'] = $services->factory(function() {
			return new Cog\Helper\ProrateHelper;
		});

		$services['helper.date'] = $services->factory(function() {
			return new Cog\Helper\DateHelper;
		});

		$services['mail.transport'] = function($c) {
			return new Cog\Mail\Transport\Mail();
		};

		$services['mail.dispatcher'] = function($c) {
			$swift = new \Swift_Mailer($c['mail.transport']);
			$dispatcher = new Cog\Mail\Mailer($swift);

			$dispatcher->setWhitelistFallback($c['cfg']->email->fallbackEmail);
			$dispatcher->addToWhitelist($c['cfg']->email->whitelist);

			// Only enable whitelist filtering on non-live environments
			if ($c['env'] !== 'live') {
				$dispatcher->enableToFiltering();
			}

			return $dispatcher;
		};

		$services['mail.message'] = $services->factory(function($c) {
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
			$message = new Cog\Mail\Message($engine, $c['templating.view_name_parser']);

			// Now replace the old templating formats
			$c['templating.formats'] = $origFormats;

			// Set default from address
			$message->setFrom($c['cfg']->app->defaultEmailFrom->email, $c['cfg']->app->defaultEmailFrom->name);

			return $message;
		});

		$services['country.list'] = $services->factory(function($c) {
			return new Cog\Location\CountryList;
		});

		$services['country.event'] = $services->factory(function($c) {
			return new Cog\Location\CountryEvent($c['country.list']);
		});

		$services['state.list'] = $services->factory(function($c) {
			return new Cog\Location\StateList;
		});

		$services['title.list'] = $services->factory(function($c) {
			return array(
				'Mr'     => 'Mr',
				'Mrs'    => 'Mrs',
				'Miss'   => 'Miss',
				'Ms'     => 'Ms',
				'Doctor' => 'Doctor',
			);
		});

		$services['pagination'] = $services->factory(function($c) {
			return new Cog\Pagination\Pagination($c['pagination.adapter.sql']);
		});

		$services['pagination.adapter.sql'] = $services->factory(function($c) {
			return new Cog\Pagination\Adapter\SQLAdapter($c['db.query']);
		});

		$services['pagination.adapter.array'] = $services->factory(function($c) {
			return new Cog\Pagination\Adapter\ArrayAdapter();
		});

		$services['serializer.array_to_xml'] = $services->factory(function($c) {
			return new Cog\Serialization\ArrayToXml();
		});

		$services['filter.form_factory'] = function ($c) {
			return new Cog\Filter\FormFactory;
		};

		$services['filter.data_binder'] = function ($c) {
			return new Cog\Filter\DataBinder;
		};
	}
}
