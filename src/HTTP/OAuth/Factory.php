<?php

namespace Message\Cog\HTTP\OAuth;

class Factory
{
	public function build($publicKey, $secretKey, $requestUrl, $accessUrl, $callbackUrl = null, $authType = null, $enableDebug = true)
	{
		$oauth = new OAuth($publicKey, $secretKey);
		$oauth->setRequestUrl($requestUrl)
			->setAccessUrl($accessUrl)
			->setEnableDebug($enableDebug)
		;

		if (null !== $callbackUrl) {
			$oauth->setCallbackUrl($callbackUrl);
		}

		if (null !== $authType) {
			$oauth->setAuthType($authType);
		}

		return $oauth;
	}
}