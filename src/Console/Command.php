<?php

namespace Message\Cog\Console;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Our wrapper around Symfony's Console component.
 */
class Command extends BaseCommand implements ContainerAwareInterface
{
	protected $_services;

	/**
	 * {inheritDoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Gets a service from the container
	 *
	 * @param  string $name The name of the service to retrieve
	 *
	 * @return mixed       The service (if it exists)
	 */
	public function get($name)
	{
		return $this->_services[$name];
	}

	/**
	 * Create a Table output instance
	 *
	 * @param OutputInterface $output
	 * @return Table
	 */
	protected function _getTable(OutputInterface $output)
	{
		return new Table($output);
	}
}