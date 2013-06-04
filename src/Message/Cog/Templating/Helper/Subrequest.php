<?php

namespace Message\Cog\Templating\Helper;

use Message\Cog\HTTP\Dispatcher;

use Symfony\Component\Templating\Helper\Helper;

/**
 * Templating helper for executing subrequests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Subrequest extends Helper
{
	protected $_dispatcher;

	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function forward($routeName, array $attributes = array(), array $query = array())
	{
		$response = $this->_dispatcher->forward($routeName, $attributes, $query);

        $response->sendContent();
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return string The canonical name
	 */
	public function getName()
	{
		return 'subrequest';
	}
}
