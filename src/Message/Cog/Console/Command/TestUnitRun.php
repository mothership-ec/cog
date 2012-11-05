<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Testing\SuiteNameParser;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs unit tests.
 *
 * By default, this will run all tests including vendor unit tests. Arguments
 * can be passed to only run certain test suites.
 */
class TestUnitRun extends Command
{
	/**
	 * Configures the command, setting the name, description and arguments.
	 */
	protected function configure()
	{
		$this
			->setName('test:unit:run')
			->setDescription('Runs unit tests.')
			->addArgument('suite_name', InputArgument::OPTIONAL, 'Only run a specific suite of tests.')
		;
	}

	/**
	 * Executes the command.
	 *
	 * @param  InputInterface  $input  The input interface
	 * @param  OutputInterface $output The output interface
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ms test:unit:run
		// ms test:unit:run mothership/wishlist
		// ms test:unit:run mothership
		// ms test:unit:run vendor/crane
		// ms test:unit:run vendor/symfony
		// ms test:unit:run vendor/symfony/eventdispatcher
		// ms test:unit:run bespoke/ipp
		//
		// TODO: change environmeny to "test mode"
		// for all tests we just need to run the PHPUnit command on the directory
		//
		// mothership/framework ones may need a special bootstrap.. but we can store this in XML somewhere
		// and tell the phpunit command to use that config file

		$suiteName = $input->getArgument('suite_name');

		if(!$suiteName) {
			try {
				$suite = SuiteNameParser::parse($suiteName);

				if (!$suite->exists()) {
					throw new \Exception(sprintf('Suite `%s` does not exist.', $suiteName));
				}
			}
			catch (\Exception $e) {
				$output->writeln('<error>' . $e->getMessage() . '</error>');
				return;
			}
		}
	}
}
