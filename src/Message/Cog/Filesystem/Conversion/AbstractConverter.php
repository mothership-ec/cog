<?php

namespace Message\Cog\Filesystem\Conversion;

use Exception;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Converts a HTML view to a stored file.
 *
 * @usage
 *     $converter = $this->get('filesystem.conversion.pdf');
 *     $converter->setView('::my-view', ['foo' => 'bar']);
 *     $file = $converter->save('/path/to/file');
 */
abstract class AbstractConverter implements ContainerAwareInterface {

	protected $_html;
	protected $_options = array();

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function setView($view, $params = array())
	{
		$this->_html = $this->_container['response_builder']
			->setRequest($this->_container['request'])
			->render($view, $params)
			->getContent();

		return $this;
	}

	public function setUrl($url)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$this->_html = curl_exec($ch);
		curl_close($ch);

		return $this;
	}

	public function save($path)
	{
		$file = $this->generate($path, $this->_html);

		return $file;
	}

	public function setOptions($options)
	{
		$this->_options = $options;
	}

	abstract public function generate($path, $html);


	protected function _getBinDir()
	{
		return $this->_container['app.loader']->getBaseDir() . 'bin/';
	}

	protected function _getBinaryType()
	{
		if ("Darwin" === PHP_OS) {
			return "osx";
		}
		elseif ("Linux" == PHP_OS) {
			return (8 === PHP_INT_SIZE) ? "amd64" : "i1386";
		}

		throw new Exception("Could not determine binary type");
	}
}