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
		$html = $this->_container['response_builder']
			->setRequest($this->_container['request'])
			->render($view, $params)
			->getContent();

		$this->_html = $this->_extractAssets($html);

		return $this;
	}

	public function setUrl($url)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$html = curl_exec($ch);
		curl_close($ch);

		$this->_html = $this->_extractAssets($html);

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

	protected function _extractAssets($html)
	{
		// http://regex101.com/r/mN7iX7
		preg_match_all("/(<(link|img)[^>]+(href|src)=[\"|']([^\"]+)[\"|'][^>]*\/?>)/", $html, $matches);

		$replaces = array();

		foreach ($matches[4] as $i => $href) {
			$path = $this->_container['app.loader']->getBaseDir() . 'public' . $href;

			$ext = pathinfo($path, PATHINFO_EXTENSION);

			// CSS
			if ('css' === $ext) {
				if (isset($replaces[$matches[0][$i]])) {
					continue;
				}

				$contents = file_get_contents($path);
				$newTag = '<style>' . $contents . '</style>';

				$replaces[$matches[0][$i]] = $newTag;
			}

			// Images
			else {
				if (isset($replaces[$href])) {
					continue;
				}

				$contents = file_get_contents($path);
				$newPath = 'data:image/' . $ext . ';base64,' . base64_encode($contents);

				$replaces[$href] = $newPath;
			}
		}

		$html = str_replace(array_keys($replaces), array_values($replaces), $html);

		return $html;
	}
}