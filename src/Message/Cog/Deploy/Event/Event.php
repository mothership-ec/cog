<?php

namespace Message\Cog\Deploy\Event;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent {

	protected $_commands = array();

	public function addCommand($command)
	{
		$this->_commands[] = $command;
	}

	public function getCommands()
	{
		return $this->_commands;
	}

}