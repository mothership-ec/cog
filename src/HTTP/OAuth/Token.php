<?php

namespace Message\Cog\HTTP\OAuth;

class Token
{
	private $_type;

	private $_token;

	private $_secret;

	private $_validTypes = [
		'request',
		'access',
	];

	/**
	 * @param $type
	 * @param array $token
	 */
	public function __construct($type, array $token = null)
	{
		$this->setType($type);

		if (null !== $token) {
			$this->build($token);
		}
	}

	/**
	 * @param array $token
	 * @throws \LogicException
	 *
	 * @return bool
	 */
	public function build(array $token)
	{
		if (array_key_exists(ResponseKeys::TOKEN, $token) && array_key_exists(ResponseKeys::TOKEN_SECRET, $token)) {
			$this->setToken($token[ResponseKeys::TOKEN]);
			$this->setSecret($token[ResponseKeys::TOKEN_SECRET]);

			return true;
		}

		throw new \LogicException(
			'Token is not valid, must contain a key of `' . ResponseKeys::TOKEN . '` and `' . ResponseKeys::TOKEN_SECRET . '`'
		);
	}

	/**
	 * @param mixed $token
	 * @throws \InvalidArgumentException
	 *
	 * @return Token         return $this for chainability
	 */
	public function setToken($token)
	{
		if (!is_string($token)) {
			throw new \InvalidArgumentException('Token must be a string, ' . gettype($token) . ' given');
		}

		$this->_token = $token;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getToken()
	{
		return $this->_token;
	}

	/**
	 * @param mixed $secret
	 * @throws \InvalidArgumentException
	 *
	 * @return Token         return $this for chainability
	 */
	public function setSecret($secret)
	{
		if (!is_string($secret)) {
			throw new \InvalidArgumentException('Secret must be a string, ' . gettype($secret) . ' given');
		}

		$this->_secret = $secret;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSecret()
	{
		return $this->_secret;
	}

	/**
	 * @param mixed $type
	 * @throws \InvalidArgumentException
	 *
	 * @return Token         return $this for chainability
	 */
	public function setType($type)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('Type must be a string, ' . gettype($type) . ' given');
		}
		if (!in_array($type, $this->_validTypes)) {
			throw new \InvalidArgumentException('`' . $type . '` is not a valid token type');
		}

		$this->_type = $type;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->_type;
	}
}