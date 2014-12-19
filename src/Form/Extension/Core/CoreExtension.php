<?php

namespace Message\Cog\Form\Extension\Core;

use Message\Cog\HTTP\Session;
use Message\Cog\Config\Registry;
use Message\Cog\Localisation\Translator;

use Symfony\Component\Form\AbstractExtension;

class CoreExtension extends AbstractExtension
{
	protected $_session;
	protected $_cfg;
	protected $_trans;

	public function __construct(Session $session, Registry $cfg, Translator $trans)
	{
		$this->_session = $session;
		$this->_cfg     = $cfg;
		$this->_trans   = $trans;
	}

	protected function loadTypes()
	{
		return [
			new Type\DatalistType,
			new Type\EntityType,
			new Type\CaptchaType($this->_cfg->captcha->textcaptchaKey, $this->_session, $this->_trans)
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
