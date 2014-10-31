<?php

namespace Message\Cog\HTTP\OAuth;

class OAuth extends \OAuth
{
	const DEFAULT_AUTH_TYPE = OAUTH_AUTH_TYPE_URI;

	/**
	 * @var string
	 */
	private $_requestUrl;

	private $_accessUrl;

	private $_callbackUrl;

	/**
	 * @var bool
	 */
	private $_enableDebug = true;

	/**
	 * @var bool
	 */
	private $_authTypeSet = false;

	/**
	 * @param string $requestUrl
	 * @throws \InvalidArgumentException
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setRequestUrl($requestUrl)
	{
		if (!filter_var($requestUrl, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException('Not a valid URL!');
		}

		$this->_requestUrl = $requestUrl;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRequestUrl()
	{
		return $this->_requestUrl;
	}

	/**
	 * @param string $accessUrl
	 * @throws \InvalidArgumentException
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setAccessUrl($accessUrl)
	{
		if (!filter_var($accessUrl, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException('Not a valid URL!');
		}

		$this->_accessUrl = $accessUrl;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAccessUrl()
	{
		return $this->_accessUrl;
	}

	/**
	 * @param string $callbackUrl
	 * @throws \InvalidArgumentException
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setCallbackUrl($callbackUrl)
	{
		if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException('Not a valid URL!');
		}

		$this->_callbackUrl = $callbackUrl;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCallbackUrl()
	{
		return $this->_callbackUrl;
	}

	/**
	 * @param int $authType
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setAuthType($authType)
	{
		$this->_authTypeSet = true;

		parent::setAuthType($authType);
	}

	/**
	 * @param boolean $enableDebug
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setEnableDebug($enableDebug)
	{
		$this->_enableDebug = $enableDebug;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnableDebug()
	{
		return $this->_enableDebug;
	}

	public function getToken()
	{
		try {
			if (null === $this->_requestUrl) {
				throw new \LogicException('No request URL set');
			}

			if (!$this->_authTypeSet) {
				$this->setAuthType(self::DEFAULT_AUTH_TYPE);
			}

			if ($this->_enableDebug) {
				$this->enableDebug();
			}

			$requestTokenData = $this->getRequestToken($this->_requestUrl, $this->_callbackUrl);

			if (empty($requestTokenData) || (!array_key_exists(ResponseKeys::TOKEN, $requestTokenData) || !array_key_exists(ResponseKeys::TOKEN_SECRET, $requestTokenData))) {
				throw new \RuntimeException('Could not receive request token');
			}

			$requestToken  = $requestTokenData[ResponseKeys::TOKEN];
			$requestSecret = $requestTokenData[ResponseKeys::TOKEN_SECRET];

			$this->setToken($requestToken, $requestSecret);

			return $this->getAccessToken($this->_accessUrl);
		}
		catch (\OAuthException $e) {
			var_dump($e);
		}
	}
}