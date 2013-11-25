<?php

namespace Message\Cog\Localisation;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * YAML translation file loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class YamlFileLoader extends ArrayLoader implements LoaderInterface
{
	protected $_yamlParser;
	protected $_cache;

	protected $_cacheEnabled = false;

	/**
	 * Constructor.
	 *
	 * @param YamlParser $yamlParser YAML parser
	 */
	public function __construct(YamlParser $yamlParser, $cache)
	{
		$this->_yamlParser = $yamlParser;
		$this->_cache = $cache;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($resource, $locale, $domain = 'messages')
	{
		if (!stream_is_local($resource)) {
			throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
		}

		if (!file_exists($resource)) {
			throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
		}

		$cacheKey = sprintf('cog.localisation.translations.%s', md5($resource . $locale . $domain));

		if (false === $this->_cacheEnabled or (false === $messages = $this->_cache->fetch($cacheKey))) {
			try {
				$messages = $this->_yamlParser->parse(file_get_contents($resource));
			} catch (ParseException $e) {
				throw new InvalidResourceException('Error parsing YAML.', 0, $e);
			}

			$this->_cache->store($cacheKey, $messages);
		}

		// empty file
		if (empty($messages)) {
			$messages = array();
		}

		// not an array
		if (!is_array($messages)) {
			throw new InvalidResourceException(sprintf('The file "%s" must contain a YAML array.', $resource));
		}

		$catalogue = parent::load($messages, $locale, $domain);

		return $catalogue;
	}

	/**
	 * Enable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function enableCaching()
	{
		$this->_cacheEnabled = true;
	}

	/**
	 * Disable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function disableCaching()
	{
		$this->_cacheEnabled = false;
	}
}