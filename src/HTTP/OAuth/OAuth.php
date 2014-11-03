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
//		if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
//			throw new \InvalidArgumentException('Not a valid URL!');
//		}

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

	public function getTokens()
	{
		if (null === $this->_requestUrl) {
			throw new \LogicException('No request URL set');
		}

		if (!$this->_authTypeSet) {
			$this->setAuthType(self::DEFAULT_AUTH_TYPE);
		}

		if ($this->_enableDebug) {
			$this->enableDebug();
		}

		try {
			$requestToken = $this->getRequestToken($this->_requestUrl, $this->_callbackUrl ? : 'oob');
		} catch (\Exception $e) {
			throw new Exception\RequestTokenException($e->getMessage() . ': ' . urldecode($this->getLastResponse()), $e->getCode());
		}

		try {
			$this->setToken($requestToken[ResponseKeys::TOKEN], $requestToken[ResponseKeys::TOKEN_SECRET]);
			$accessToken = $this->getAccessToken($this->_accessUrl);
		} catch (\Exception $e) {
			throw new Exception\AccessTokenException($e->getMessage() . ': ' . urldecode($this->getLastResponse()), $e->getCode());
		}

		return new TokenCollection([
			new Token('request', $requestToken),
			new Token('access', $accessToken),
		]);
	}
}