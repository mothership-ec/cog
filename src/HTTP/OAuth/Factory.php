<?php

namespace Message\Cog\HTTP\OAuth;

class Factory
{
	public function build($publicKey, $secretKey, $url, $authType = null, $enableDebug = true)
	{
		$oauth = new OAuth($publicKey, $secretKey);
		$oauth->setUrl($url)
			->setEnableDebug($enableDebug)
		;

		if (null !== $authType) {
			$oauth->setAuthType($authType);
		}

		return $oauth;
	}
}