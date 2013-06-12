<?php

namespace Message\Cog\Console;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;



class Application extends BaseApplication implements ContainerAwareInterface
{
	protected $_container;

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function add(Command $command)
	{
		if($command instanceof ContainerAwareInterface) {
			$command->setContainer($this->_container);
		}

		parent::add($command);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureIO(InputInterface $input, OutputInterface $output)
	{
		// Add a bold style (cant believe this isnt in Symfony by default!)
		$output->getFormatter()->setStyle('bold', new OutputFormatterStyle(null, null, array('bold')));
	}
}
