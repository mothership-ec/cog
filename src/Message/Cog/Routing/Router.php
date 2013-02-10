<?php

namespace Message\Cog\Routing;

use Message\Cog\ReferenceParserInterface;
use Message\Cog\Cache;

/**
* A wrapper around Symfony's Routing component making it easier to use.
*
* TODO: Document our custom Route class here with a usage example
*
* @link http://symfony.com/doc/current/components/routing.html
* @link http://symfony.com/doc/current/book/routing.html
*/
class Router implements RouterInterface
{
	protected $_referenceParser;
	protected $_matcher;
	protected $_generator;
	protected $_context;
	protected $_collection;
	protected $_options;
	protected $_cache;

	/**
	 * Constructor.
	 *
	 * @param ReferenceParserInterface $referenceParser Engine to parse references
	 * @param array		               $options         An array of options
	 * @param RequestContext           $context         The context
	 */
	public function __construct(ReferenceParserInterface $referenceParser, array $options = array(), RequestContext $context = null)
	{
		$this->_referenceParser = $referenceParser;
		$this->_collection = new RouteCollection;
		$this->_context = null === $context ? new RequestContext() : $context;
		$this->setOptions($options);
	}

	/**
	 * Add a route to the router.
	 *
	 * @param string $name       A valid route name
	 * @param string $url        A route URL
	 * @param string $controller The controller/method to execute upon a successful match
	 * 
	 * @return Route The newly added route
	 */
	public function add($name, $url, $controller)
	{
		$reference = $this->_referenceParser->parse($controller);
		$defaults  = array(
			'_controller' => $reference->getSymfonyLogicalControllerName()
		);
		$route     = new Route($url, $defaults);
		$this->getRouteCollection()->add($name, $route);

		return $route;
	}

	/**
	 * Sets the cache class to use to store compiled routes to improve
	 * performance. If no cache is specified then routes are never cached.
	 *
	 * @param \TreasureChest\CacheInterface  A cache object to store routes in.
	 */
	public function setCache(\TreasureChest\CacheInterface $cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * Sets options.
	 *
	 * Available options:
	 *
	 *   * cache_dir:	 The cache directory (or null to disable caching)
	 *   * debug:		 Whether to enable debugging or not (false by default)
	 *   * resource_type: Type hint for the main resource (optional)
	 *
	 * @param array $options An array of options
	 *
	 * @throws \InvalidArgumentException When unsupported option is provided
	 */
	public function setOptions(array $options)
	{
		$this->_options = array(
			'cache'                  => null,
			'cache_key'              => null,
			'debug'                  => false,
			'generator_class'        => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
			'generator_base_class'   => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
			'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
			'generator_cache_class'  => 'ProjectUrlGenerator',
			'matcher_class'          => 'Message\\Cog\\Routing\\UrlMatcher',
			'matcher_base_class'     => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
			'matcher_dumper_class'   => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
			'matcher_cache_class'    => 'ProjectUrlMatcher',
			'resource_type'          => null,
		);

		// check option names and live merge, if errors are encountered Exception will be thrown
		$invalid = array();
		foreach ($options as $key => $value) {
			if (array_key_exists($key, $this->_options)) {
				$this->_options[$key] = $value;
			} else {
				$invalid[] = $key;
			}
		}

		if (count($invalid)) {
			throw new \InvalidArgumentException(sprintf('The Router does not support the following options: "%s".', implode('\', \'', $invalid)));
		}
	}

	/**
	 * Sets an option.
	 *
	 * @param string $key   The key
	 * @param mixed  $value The value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setOption($key, $value)
	{
		if (!array_key_exists($key, $this->_options)) {
			throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
		}

		$this->_options[$key] = $value;
	}

	/**
	 * Gets an option value.
	 *
	 * @param string $key The key
	 *
	 * @return mixed The value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getOption($key)
	{
		if (!array_key_exists($key, $this->_options)) {
			throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
		}

		return $this->_options[$key];
	}

	/**
	 * Gets the RouteCollection instance associated with this Router.
	 *
	 * @return RouteCollection A RouteCollection instance
	 */
	public function getRouteCollection()
	{
		return $this->_collection;
	}

	/**
	 * Sets the RouteCollection instance associated with this Router.
	 *
	 * @param RouteCollection $collection The RouteCollection to use.
	 */
	public function setRouteCollection(RouteCollection $collection)
	{
		$this->_collection = $collection;
	}

	/**
	 * Sets the request context.
	 *
	 * This hints for the base Symfony `RequestContext` class because this is
	 * what the Symfony interface requires. Our `RequestContext` extends this
	 * anyway.
	 *
	 * @param RequestContext $context The context
	 */
	public function setContext(\Symfony\Component\Routing\RequestContext $context)
	{
		$this->_context = $context;

		$this->getMatcher()->setContext($context);
		$this->getGenerator()->setContext($context);
	}

	/**
	 * Gets the request context.
	 *
	 * @return RequestContext The context
	 */
	public function getContext()
	{
		return $this->_context;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate($name, $parameters = array(), $absolute = false)
	{
		return $this->getGenerator()->generate($name, $parameters, $absolute);
	}

	/**
	 * {@inheritdoc}
	 */
	public function match($pathinfo)
	{
		return $this->getMatcher()->match($pathinfo);
	}

	/**
	 * Gets the UrlMatcher instance associated with this Router.
	 *
	 * @return UrlMatcherInterface A UrlMatcherInterface instance
	 */
	public function getMatcher()
	{
		if (null !== $this->_matcher) {
			return $this->_matcher;
		}

		// TODO: Remove this line and re-instate cached matchers that dont rely on ConfigCache

		if(null === $this->_options['cache'] || null === $this->_options['cache_key']) {
			$matcher = new $this->_options['matcher_class']($this->getRouteCollection(), $this->_context);
			return $this->_matcher = $matcher;
		}

		if($matcher = $this->_options['cache']->fetch($this->_options['cache_key'])) {
			//return $matcher;
		}

		$dumper = new $this->_options['matcher_dumper_class']($this->getRouteCollection());

		$options = array(
			'class'	  => $this->_options['matcher_cache_class'],
			'base_class' => $this->_options['matcher_base_class'],
		);

		dump($dumper->dump($options));


		$matcher = new $this->_options['matcher_class']($this->getRouteCollection(), $this->_context);

		$this->_options['cache']->store($this->_options['cache_key'], $matcher);

		return $matcher;



		/*

		if (null === $this->_options['cache_dir'] || null === $this->_options['matcher_cache_class']) {
			return $this->_matcher = new $this->_options['matcher_class']($this->getRouteCollection(), $this->_context);
		}


		$class = $this->_options['matcher_cache_class'];
		$cache = new ConfigCache($this->_options['cache_dir'].'/'.$class.'.php', $this->_options['debug']);


		if (!$cache->isFresh($class)) {

		}

		require_once $cache;

		return $this->_matcher = new $class($this->_context);
		*/
	}

	/**
	 * Gets the UrlGenerator instance associated with this Router.
	 *
	 * @return UrlGeneratorInterface A UrlGeneratorInterface instance
	 */
	public function getGenerator()
	{
		if (null !== $this->_generator) {
			return $this->_generator;
		}

		// TODO: Remove this line and re-instate cached generators that dont rely on ConfigCache
		return $this->_generator = new $this->_options['generator_class']($this->getRouteCollection(), $this->_context);

		/*
		if (null === $this->_options['cache_dir'] || null === $this->_options['generator_cache_class']) {
			return $this->_generator = new $this->_options['generator_class']($this->getRouteCollection(), $this->_context);
		}

		$class = $this->_options['generator_cache_class'];
		$cache = new ConfigCache($this->_options['cache_dir'].'/'.$class.'.php', $this->_options['debug']);
		if (!$cache->isFresh($class)) {
			$dumper = new $this->_options['generator_dumper_class']($this->getRouteCollection());

			$options = array(
				'class'	  => $class,
				'base_class' => $this->_options['generator_base_class'],
			);

			$cache->write($dumper->dump($options), $this->getRouteCollection()->getResources());
		}

		require_once $cache;

		return $this->_generator = new $class($this->_context);
		*/
	}
}
