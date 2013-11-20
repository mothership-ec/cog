<?php

namespace Message\Cog\Controller;

use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;
use Message\Cog\HTTP\RequestAwareInterface;
use Message\Cog\Templating\EngineInterface;
use Message\Cog\Form\Handler as FormHandler;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * This class uses the given templating engine to render a view and turn it into
 * a HTTP Response object. It also determines which format to return based on
 * what was requested, what is available and what can be automatically generated.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ResponseBuilder implements RequestAwareInterface
{
	protected $_engine;
	protected $_request;

	/**
	 * Constructor.
	 *
	 * @param EngineInterface $engine Template rendering engine
	 */
	public function __construct(EngineInterface $engine)
	{
		$this->_engine = $engine;
	}

	/**
	 * Sets the Request for which this Response is being built for.
	 *
	 * @param Request $request The Request instance
	 */
	public function setRequest(Request $request)
	{
		$this->_request = $request;

		return $this;
	}

	/**
	 * Render a view using the given templating engine. The templating engine is
	 * determined by the file extension passed in the view name $view.
	 *
	 * @param  string $reference The reference for the view to render
	 * @param  array  $params    The parameters to use when rendering the view
	 *
	 * @return Response          The rendered result as a Response instance
	 *
	 * @throws NotAcceptableHttpException If view could not be rendered
	 *
	 * @todo When rendering the view, find out the type of the view rendered and
	 *       set the content type as appropriate.
	 */
	public function render($reference, array $params = array(), Response $response = null)
	{
		if (!$response) {
			$response = new Response;
		}

		// Convert any shorthand parameters to what they should be
		foreach ($params as $key => $val) {
			if ($val instanceof FormHandler) {
				$params[$key] = $val->getForm()->createView();
			}
		}

		try {
			$response->setContent($this->_engine->render($reference, $params));

			return $response;
		}
		catch (\Exception $e) {
			throw new NotAcceptableHttpException(
				sprintf(
					'Exception thrown while rendering view `%s`: `%s`',
					$reference,
					$e->getMessage()
				),
				$e
			);
		}
	}
}