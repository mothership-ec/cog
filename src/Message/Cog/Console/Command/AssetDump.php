<?php

	namespace Message\Cog\Console\Command;

	use Message\Cog\Console\Command;
	use Message\Cog\Filesystem\File;

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
		const USE_SYMLINKS = 'symlink';

		protected function configure()
		{
			$this
				->setName('asset:dump')
				->setDescription('Move module assets to public folder.')
			;

			// Whether to use symlinks to copy assets across
			$this->addOption('--' . self::USE_SYMLINKS);
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{
			// Service to work with files
			$fileSystem = $this->get('filesystem');

			// Base Location for Public Assets
			$filePath = 'cog://public/cogules/';
			$resourcesDir = 'resources/public/';

			// Get List of Loaded Modules
			$modules = $this->get('module.loader')->getModules();

			// Service to locate module paths
			$moduleLocator = $this->get('module.locator');

			$output->writeln('<info>Moving public assets for ' . count($modules) . ' modules.</info>');

			$useSymlinks = $input->getOption('symlink');

			foreach($modules as $module) {
				$moduleName = str_replace("\\", ':', $module);

				// Directory locations
				$originDir = $moduleLocator->getPath($module) . $resourcesDir;
				$targetDir = $filePath . $moduleName;

				// Move on to next module if there are no public resources.
				if(!$fileSystem->exists($originDir))
				{
					$output->writeln("No resources for {$module}");
					continue;
				}

				$output->writeln("Copying {$originDir} to {$targetDir}");
				$output->writeln('');

				/*
				 * Copy files across to public folder
				 *
				 * Either create a symlink, or copy files across.
				 */
				if($useSymlinks)
				{
					$fileSystem->symlink($originDir, $targetDir);
				}
				else
				{
					$fileSystem->mirror($originDir, $targetDir);
				}
			}
		}
	}
