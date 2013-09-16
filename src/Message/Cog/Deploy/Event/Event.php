<?php

namespace Message\Cog\Deploy\Event;

use Message\Cog\Event\Event as BaseEvent;
use Message\Cog\Console\Command;
use Message\Cog\Console\CommandCollection;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;

class Event extends BaseEvent {

	protected $_output;
	protected $_commandCollection;

	public function setOutput(OutputInterface $output)
	{
		$this->_output = $output;
	}

	public function setCommandCollection(CommandCollection $collection)
	{
		$this->_commandCollection = $collection;
	}

	public function writeln($line)
	{
		$this->_output->writeln($line);
	}

	public function executeCommand($command, InputInterface $input = null)
	{
		if (null === $input) {
			$input = new ArrayInput(array(), new InputDefinition());
		}

		$result = `bin/cog $command`;
		// $this->_commandCollection->get($command)->run($input, $this->_output);
	}

}