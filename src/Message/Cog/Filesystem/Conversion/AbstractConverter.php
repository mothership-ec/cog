<?php

namespace Message\Cog\Filesystem\Conversion;

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
		$this->_html = $this->_getHtml($view, $params);

		return $this;
	}

	public function save($path)
	{
		$file = $this->generate($path, $this->_html);

		return $file;
	}

	abstract public function generate($path, $html);

	/**
	 * Get the rendered html for a view.
	 *
	 * @param  string $view
	 * @param  array  $params
	 * @return string Rendered html output
	 */
	protected function _getHtml($view, $params)
	{
		return $this->_container['response_builder']
			->setRequest($this->_container['request'])
			->render($view, $params)
			->getContent();
	}

}