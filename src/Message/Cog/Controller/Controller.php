<?php

namespace Message\Cog\Controller;

use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;
use Message\Cog\HTTP\RedirectResponse;
use Message\Cog\HTTP\RequestAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use LogicException;

/**
 * Base controller class, providing helpers and common features used by
 * controllers.
 *
 * Controller classes don't have to extend this class, but they will often find
 * it useful to.
 *
 * @todo Add a helper method for adding feedback once the Feedback component is
 *       built.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Controller implements ContainerAwareInterface, RequestAwareInterface
{
	protected $_services;
	protected $_request;

	/**
	 * Get a service directly from the container by name.
	 *
	 * @param  string $serviceName The service name
	 *
	 * @return mixed               The requested service
	 */
	public function get($serviceName)
	{
		return $this->_services[$serviceName];
	}

	/**
	 * Add a flash message to the session.
	 *
	 * @param string $type    The type of message
	 * @param string $message The message to add
	 */
	public function addFlash($type, $message)
	{
		return $this->get('http.session')->getFlashBag()->add($type, $message);
	}

	/**
	 * Run a string through the translation engine.
	 *
	 * @see Message\Cog\Localisation\Translator::trans
	 *
	 * @param  string      $message Message or message ID
	 * @param  array       $params  Array of parameters
	 * @param  string|null $domain  Message domain
	 * @param  string|null $locale  Override of locale to use
	 *
	 * @return string               Translated string
	 */
	public function trans($message, array $params = array(), $domain = null, $locale = null)
	{
		return $this->get('translator')->trans($message, $params, $domain, $locale);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param ContainerInterface $container The service container instance
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	/**
	 * {@inheritdoc}
	 *
	 * This is used by `ResponseBuilder` to help determine the response format
	 * type.
	 *
	 * @param Request $request The request to set
	 */
	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}

	/**
	 * Generate a URL from a route name.
	 *
	 * @see \Message\Cog\Routing\UrlGenerator::generate()
	 *
	 * @param string         $routeName     Name of the route to use
	 * @param array          $params        Parameters to use in the route
	 * @param boolean|string $referenceType The type of reference (one of the
	 *                                      constants in UrlGeneratorInterface)
	 *
	 * @return string            The generated URL
	 */
	public function generateUrl($routeName, $params = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->_services['routing.generator']->generate($routeName, $params, $absolute);
	}

	/**
	 * Returns a RedirectResponse instance for redirection to the given URL.
	 *
	 * @param  string $url 	  URL to redirect to
	 * @param  int    $status HTTP status code to use when redirecting
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302)
	{
		return new RedirectResponse($url, $status);
	}

	/**
	 * Returns a RedirectResponse instance for redirection to the given route name.
	 *
	 * @param  string $routeName Name of the route to redirect to
	 * @param  array  $params    Parameters to use in the route
	 * @param  int    $status    HTTP status code to use when redirecting
	 *
	 * @return RedirectResponse
	 */
	public function redirectToRoute($routeName, $params = array(), $status = 302)
	{
		return $this->redirect($this->generateUrl($routeName, $params), $status);
	}

	/**
	 * Returns a RedirectResponse instance for redirection to the request
	 * referer.
	 *
	 * @param  int $status HTTP status code to use when redirecting
	 *
	 * @return RedirectResponse
	 */
	public function redirectToReferer($status = 302)
	{
		return $this->redirect($this->get('request')->headers->get('referer'), $status);
	}

	/**
	 * Triggers a sub-request for the given controller reference and returns a
	 * filtered Response instance.
	 *
	 * @param  string $reference  The reference for the controller
	 * @param  array  $attributes Request attributes
	 * @param  array  $query      Optional query (GET) parameters
	 * @param  bool   $catch      True to catch exceptions thrown in the sub-request
	 *
	 * @return Response           The filtered Response instance
	 */
	public function forward($reference, array $attributes = array(), array $query = array(), $catch = true)
	{
		// Copy the route format from the current request
		if (!array_key_exists('_format', $attributes)) {
			$attributes['_format'] = $this->_services['request']->attributes->get('_format');
		}
		// Set the Symfony controller reference
		$attributes['_controller'] = $this->_services['reference_parser']->parse($reference)->getSymfonyLogicalControllerName();

		$kernel  = $this->_services['http.kernel'];
		$request = $this->_services['request']->duplicate($query, null, $attributes);

		// Execute the sub-request
		return $kernel->handle($request, $kernel::SUB_REQUEST, $catch);
	}

	/**
	 * Render a view and return the rendered contents as a HTTP Response
	 * instance.
	 *
	 * @see ResponseBuilder::render
	 *
	 * @param  string $reference The reference for the view
	 * @param  array  $params    Optional parameters to pass to the view
	 *
	 * @return Response          The rendered view as a HTTP Response instance
	 *
	 * @throws LogicException    If the Request is not set on this class
	 */
	public function render($reference, array $params = array(), Response $response = null)
	{
		if (!($this->_request instanceof Request)) {
			throw new LogicException('Request must be set on the controller to render a view.');
		}

		return $this->_services['response_builder']
			->setRequest($this->_request)
			->render($reference, $params, $response);
	}

	/**
	 * Returns a `NotFoundHttpException`.
	 *
	 * This will result in a 404 response code. Usage example:
	 *
	 * <code>
	 *     throw $this->createNotFoundException('Page not found!');
	 * </code>
	 *
	 * @param string     $message  The exception message
	 * @param \Exception $previous The previous exception
	 * @param int        $code     The exception code
	 *
	 * @return NotFoundHttpException
	 */
	public function createNotFoundException($message = 'Not Found', \Exception $previous = null, $code = 0)
	{
	    return new NotFoundHttpException($message, $previous);
	}

	/**
	 * Returns a `AccessDeniedHttpException`.
	 *
	 * This will result in a 403 response code. Usage example:
	 *
	 * <code>
	 *     throw $this->createAccessDeniedException('Page not found!');
	 * </code>
	 *
	 * @param string     $message  The exception message
	 * @param \Exception $previous The previous exception
	 * @param int        $code     The exception code
	 *
	 * @return AccessDeniedHttpException
	 */
	public function createAccessDeniedException($message = 'Access Denied', \Exception $previous = null, $code = 0)
	{
	    return new AccessDeniedHttpException($message, $previous);
	}
}