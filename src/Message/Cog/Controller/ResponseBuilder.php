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
	 * @throws NotAcceptableHttpException If view could not be rendered or generated
	 *
	 * @todo When rendering the view, find out the type of the view rendered and
	 *       set the content type as appropriate.
	 */
	public function render($reference, array $params = array())
	{
		// Convert any shorthand parameters to what they should be
		foreach ($params as $key => $val) {
			if ($val instanceof FormHandler) {
				$params[$key] = $val->getForm()->createView();
			}
		}

		try {
			return Response::create($this->_engine->render($reference, $params));
		}
		catch (\Exception $e) {
			// See if we can automatically generate a response
			if ($generatedResponse = $this->_generateResponse($params)) {
				return $generatedResponse;
			}
			// If not, throw an exception
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

	/**
	 * Looks at the allowed content types and checks whether any of these can
	 * be automatically generated, and returns the automatically generated
	 * response if so.
	 *
	 * @param  array $params  The parameters to use when generating the response
	 *
	 * @return Response|false The generated response result, or false if not generated
	 */
	protected function _generateResponse(array $params = array())
	{
		// REFACTOR: One day this could use Engine classes to auto-generate, but
		// for now it's so simple it's kinda pointless.
		foreach ($this->_request->getAllowedContentTypes() as $mimeType) {
			$format = $this->_request->getFormat($mimeType);

			switch ($format) {
				case 'json':
					return Response::create(json_encode($params), 200, array('Content-Type' => $mimeType));
					break;
			}
		}

		return false;
	}
}