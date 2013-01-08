<?php

namespace Message\Cog\HTTP;

use InvalidArgumentException;

/**
 * RedirectResponse represents a HTTP redirect response.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RedirectResponse extends Response
{
	protected $_targetUrl;

	/**
	 * Constructor.
	 *
	 * Sets the Response up and ensures it conforms to HTTP redirection rules.
	 *
	 * @param string  $url     The URL to redirect to
	 * @param integer $status  The HTTP status code to redirect with (defaults to 302)
	 * @param array   $headers Additional headers to send (there's no need to
	 *                         set the Location header in here)
	 *
	 * @throws InvalidArgumentException If the HTTP status code passed is not a redirect code
	 */
	public function __construct($url, $status = 302, array $headers = array())
	{
		parent::__construct('', $status, $headers);

		$this->setTargetUrl($url);

		if (!$this->isRedirect()) {
			throw new InvalidArgumentException(sprintf('HTTP status code `%s` is not a redirect', $status));
		}
	}

	/**
	 * Get the redirection target URL.
	 *
	 * @return string The URL to redirect to
	 */
	public function getTargetUrl()
	{
		return $this->_targetUrl;
	}

	/**
	 * Set the target for the redirection on this response.
	 *
	 * @todo Once the Validate component is ready, use it to validate the URL
	 *       here and throw an exception if it is not valid.
	 * @todo It'd be nice to add the scheme if there isn't one in the url passed.
	 *
	 * @param  string $url      The URL to redirect to
	 *
	 * @return RedirectResponse Returns $this for chainability
	 *
	 * @throws InvalidArgumentException If the URL is passed empty
	 */
	public function setTargetUrl($url)
	{
		if (empty($url)) {
			throw new InvalidArgumentException('Cannot redirect to a blank URL');
		}

		$this->_targetUrl = $url;

		$this->setContent($this->_getHTMLContent());
		$this->headers->set('Location', $this->_targetUrl);

		return $this;
	}

	/**
	 * Get the HTML contents for a redirect response.
	 *
	 * This includes both a meta "refresh" tag and a Javascript redirect incase
	 * the client's browser does not support HTTP redirection.
	 *
	 * @return string The HTML contents for the redirect
	 */
	protected function _getHTMLContent()
	{
		$html = '
<!DOCTYPE html>
<html>
	<head>
		<meta charset="%2$s">
		<meta http-equiv="refresh" content="1;url=%1$s">
		<title>Redirecting to %1$s</title>
		<script>
			parent.location = "%1$s";
		</script>
	</head>
	<body>
		Redirecting to <a href="%1$s">%1$s</a>&hellip;. Please <a href="%1$s">click here if you are not redirected</a>.
	</body>
</html>';

		return sprintf(
			$html,
			htmlspecialchars($this->_targetUrl, ENT_QUOTES, $this->getCharset()),
			$this->getCharset()
		);
	}
}