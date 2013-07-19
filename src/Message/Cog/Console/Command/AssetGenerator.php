<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Assetic\Extension\Twig\TwigResource;

/**
 * AssetGenerator
 *
 * Provides the asset:generate command.
 * Move module assets to public folder.
 */
class AssetGenerator extends Command
{
	protected $_fileExtensions = array();

	protected function configure()
	{
		$this
			->setName('asset:generate')
			->setDescription('Compile assets for modules.')
		;

		// Set file extension used for templates
		$this->_fileExtensions = array('twig');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Service to work with files.
		$fileSystem = $this->get('filesystem.finder');

		// Base location for Views.
		$viewDir = 'View';

		// Get list of loaded Modules.
		$modules = $this->get('module.loader')->getModules();

		// Service to locate module paths.
		$moduleLocator = $this->get('module.locator');

		$output->writeln('<info>Generating public assets for ' . count($modules) . ' modules.</info>');

		foreach($modules as $module) {
			$moduleName = str_replace("\\", ':', $module);

			// Path for Modules' View directory.
			$originDir = $moduleLocator->getPath($module) . $viewDir;

			// If there are no Views for a module, no need to check for templates.
			if(!file_exists($originDir)) {
				$output->writeln("<comment>No assets for {$moduleName}</comment>");
				continue;
			}

			foreach ($fileSystem->in($originDir) as $file) {

				// Check that the file is one that we need to combine.
				if(!in_array($file->getExtension(), $this->_fileExtensions)) {
					continue;
				}

				$this->_services['asset.manager']->addResource(new TwigResource(
					new \Twig_Loader_Filesystem('/'),
					$file->getPathname()
				), 'twig');
			}

			$this->_services['asset.writer']->writeManagerAssets($this->_services['asset.manager']);

			$output->writeln("<info>Compiled assets for {$moduleName}</info>");
		}
	}
}
