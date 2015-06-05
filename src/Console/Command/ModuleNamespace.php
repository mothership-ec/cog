<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Status
 *
 * Provides the status command.
 */
class ModuleNamespace extends Command
{
	protected function configure()
	{
		$this
			->setName('module:namespace')
			->setDescription('Lists module namespace load directories.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$modules = $this->get('module.loader')->getModules();
		$locator = $this->get('module.locator');

		$table = $this->_getTable($output)
			->setHeaders(array('Name', 'Location'));

		foreach($modules as $module) {
			$table->addRow(array($module, $locator->getPath($module)));
		}

		$table->render($output);
	}
}