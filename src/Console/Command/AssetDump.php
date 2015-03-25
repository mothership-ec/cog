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
		$cogulePath =  'public/cogules/';

		// Location for module resources.
		$resourcesDir = 'resources/public/';

		// Get List of Loaded Modules
		$modules = $this->get('module.loader')->getModules();

		// Service to locate module paths
		$moduleLocator = $this->get('module.locator');

		// Determine whether to use symlinks to map resources.
		$useSymlinks = $input->getOption(self::USE_SYMLINKS);

		$output->writeln('<info>Moving public assets for ' . count($modules) . ' modules.</info>');

		$oldUmask = umask(0);
		foreach($modules as $module) {
			$moduleName = str_replace("\\", ':', $module);

			// Directory locations
			$originDir = $moduleLocator->getPath($module, false) . $resourcesDir;
			$targetDir = $this->get('app.loader')->getBaseDir() . '/' . $cogulePath . $moduleName;

			// Move on to next module if there are no public resources.
			if(!$fileSystem->exists($originDir)) {
				$output->writeln("<comment>No assets for {$module}.</comment>");
				continue;
			}

			// Remove existing assets if we want the target to be a symlink and it isn't
			// and vice versa.
			if(file_exists($targetDir)) {
				if($useSymlinks && !is_link($targetDir)) {
					$output->writeln("<info>Removing non symbolic assets for {$module}.</info>");
					$fileSystem->remove($targetDir);
				}
				elseif(!$useSymlinks && is_link($targetDir)) {
					$output->writeln("<info>Removing symbolic assets for {$module}.</info>");
					unlink($targetDir);
				}
			}

			$output->writeln("<info>Copying {$originDir} to {$targetDir}.</info>");

			// Either Symlink the directory, or copy the files across.
			if($useSymlinks) {
				$fileSystem->symlink($originDir, $targetDir);
			} else {
				$fileSystem->mirror($originDir, $targetDir);
			}
		}
		umask($oldUmask);

		$output->writeln("<info>Finished dumping module assets.</info>");
	}
}
