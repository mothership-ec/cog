<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Exception\IOException;

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
		$basePath = 'cog://';
		$cogulePath =  'public/cogules/';

		$filePath = $basePath . $cogulePath;
		$resourcesDir = 'resources/public/';

		// Get List of Loaded Modules
		$modules = $this->get('module.loader')->getModules();

		// Service to locate module paths
		$moduleLocator = $this->get('module.locator');

		// Determine whether to use symlinks to map resources.
		$useSymlinks = $input->getOption(self::USE_SYMLINKS);

		$output->writeln('<info>Moving public assets for ' . count($modules) . ' modules.</info>');

		foreach($modules as $module) {
			$moduleName = str_replace("\\", ':', $module);

			// Directory locations
			$originDir = $moduleLocator->getPath($module, false) . $resourcesDir;
			$targetDir = $filePath . $moduleName;

			// Move on to next module if there are no public resources.
			if(!$fileSystem->exists($originDir)) {
				$output->writeln("<comment>No resources for {$module}</comment>");
				continue;
			}

			// Either Symlink the directory, or copy the files across.
			if($useSymlinks) {
				// Path needs to be relative in order to symlink.
				$symlinkDir = $cogulePath . $moduleName;

				$fileSystem->symlink($originDir, $symlinkDir);
			} else {
				$fileSystem->mirror($originDir, $targetDir);
			}

			$output->writeln("<info>Copying {$originDir} to {$targetDir}</info>");
		}
	}
}
