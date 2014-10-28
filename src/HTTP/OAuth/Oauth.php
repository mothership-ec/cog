<?php

namespace Message\Cog\HTTP\OAuth;

class OAuth extends \OAuth
{
	const DEFAULT_AUTH_TYPE = OAUTH_AUTH_TYPE_URI;

	/**
	 * @var string
	 */
	private $_url;

	/**
	 * @var bool
	 */
	private $_enableDebug = true;

	/**
	 * @var bool
	 */
	private $_authTypeSet = false;

	/**
	 * @param string $url
	 *
	 * @return OAuth         return $this for chainability
	 */
	public function setUrl($url)
	{
		$this->_url = $url;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->_url;
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

	public function getRequestToken($url = null)
	{
		if (null === $url && null === $this->_url) {
			throw new \LogicException('No URL set!');
		}

		$url = ($url) ?: $this->_url;

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException('Not a valid URL!');
		}

		if (!$this->_authTypeSet) {
			$this->setAuthType(self::DEFAULT_AUTH_TYPE);
		}

		if ($this->_enableDebug) {
			$this->enableDebug();
		}

		return parent::getRequestToken($url);
	}
}