<?php

namespace Message\Cog\Console;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;


/**
 * A wrapper around Symfony's Console\Application so that we can inject
 * our service container
 */
class Application extends BaseApplication implements ContainerAwareInterface
{
	const ENV_OPT_NAME = 'env';

	protected $_container;

	/**
	 * {inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	/**
	 * {inheritDoc}
	 */
	public function add(SymfonyCommand $command)
	{
		// Inject the service container if the command knows how to use it
		if ($command instanceof ContainerAwareInterface) {
			$command->setContainer($this->_container);
		}

		parent::add($command);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureIO(InputInterface $input, OutputInterface $output)
	{
		// Add a bold style for all commands (cant believe this isnt in Symfony by default!)
		$output->getFormatter()->setStyle('bold', new OutputFormatterStyle(null, null, array('bold')));
	}
}
