<?php

namespace Message\Cog\Deploy\Event;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent {

	protected $_lines = array();

	public function addLine($line)
	{
		$this->_lines[] = $line;
	}

	public function getLines()
	{
		return $this->_lines;
	}

}