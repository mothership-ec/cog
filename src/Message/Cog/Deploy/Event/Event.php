<?php

namespace Message\Cog\Deploy\Event;

use Message\Cog\Event\Event as BaseEvent;
use Symfony\Component\Console\Output\OutputInterface;

class Event extends BaseEvent {

	protected $_output;

	public function setOutput(OutputInterface $output)
	{
		$this->_output = $output;
	}

	public function writeln($line)
	{
		$this->_output->writeln($line);
	}

}