<?php

namespace Message\Cog\Test\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Foo1Command extends Command
{
	protected $_input;
	protected $_output;

	protected function configure()
	{
		$this
			->setName('foo:bar1')
			->setDescription('The foo:bar1 command')
			->setAliases(array('afoobar1'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->_input  = $input;
		$this->_output = $output;
	}
}