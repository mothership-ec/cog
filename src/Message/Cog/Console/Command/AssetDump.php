<?php

	namespace Message\Cog\Console\Command;

	use Message\Cog\Console\Command;

	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	/**
	 * AssetDump
	 *
	 * Provides the asset:dump command.
	 * Move module assets to public folder.
	 */
	class AssetDump extends Command
	{
		protected function configure()
		{
			$this
				->setName('asset:dump')
				->setDescription('Move module assets to public folder.')
			;
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{
			$modules = $this->get('module.loader')->getModules();

			$output->writeln('<info>Found ' . count($modules) . ' registered modules.</info>');

			$table = $this->getHelperSet()->get('table')
				->setHeaders(array('Name'));

			foreach($modules as $module) {
				$table->addRow(array($module));
			}

			$table->render($output);
		}
	}
