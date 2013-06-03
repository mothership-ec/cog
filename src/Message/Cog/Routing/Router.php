<?php

namespace Message\Cog\Routing;

use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
* A wrapper around Symfony's Routing component making it easier to use.
*
* TODO: Add a caching layer in getMatcher and getGenerator
*
* @link http://symfony.com/doc/current/components/routing.html
* @link http://symfony.com/doc/current/book/routing.html
*/
class Router implements RouterInterface
{
	protected $_matcher;
	protected $_generator;
	protected $_context;
	protected $_collection;
	protected $_options;

	/**
	 * Constructor.
	 *
	 * @param array		               $options         An array of options
	 * @param RequestContext           $context         The context
	 */
	public function __construct(array $options = array(), RequestContext $context = null)
	{
		$this->_context = null === $context ? new RequestContext() : $context;
		$this->setOptions($options);
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
	public function setRouteCollection(SymfonyRouteCollection $collection)
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

		// TODO: Remove this line and re-instate cached matchers that dont rely on Symfony's ConfigCache
		$matcher = new $this->_options['matcher_class']($this->getRouteCollection(), $this->_context);

		return $this->_matcher = $matcher;
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

		// TODO: Remove this line and re-instate cached generators that dont rely on Symfony's ConfigCache
		return $this->_generator = new $this->_options['generator_class']($this->getRouteCollection(), $this->_context);
	}
}
