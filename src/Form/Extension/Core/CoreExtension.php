<?php

namespace Message\Cog\Form\Extension\Core;

use Message\Cog\HTTP\Session;
use Message\Cog\Config\Registry;

use Symfony\Component\Form\AbstractExtension;

class CoreExtension extends AbstractExtension
{
	protected $_session;
	protected $_cfg;

	public function __construct(Session $session, Registry $cfg)
	{
		$this->_session = $session;
		$this->_cfg     = $cfg;
	}

	protected function loadTypes()
	{
		return [
			new Type\DatalistType,
			new Type\EntityType,
			new Type\CaptchaType($this->_cfg->captcha->apiKey, $this->_session)
		];
	}

	protected function loadTypeExtensions()
	{
		return [
			new Type\DateTypeExtension,
			new Type\TimeTypeExtension,
		];
	}
}
