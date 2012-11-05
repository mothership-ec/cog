<?php

namespace Message\Cog\Module;

class Event extends \Message\Cog\Event\Event
{
	const MODULE_LOADED      = 'module.%s.load.success';
	const MODULE_LOAD_FAILED = 'module.%s.load.failure';
}