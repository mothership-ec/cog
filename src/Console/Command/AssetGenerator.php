<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Templating\TemplateReference;

use Message\Cog\AssetManagement\TwigResource;
use Message\Cog\Module\ReferenceParser;

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
		$memory = ini_get('memory_limit');

		switch (substr($memory, -1)) {
			case 'M':
			case 'm':
				$memory = (int) $memory * 1048576;
				break;
			case 'K':
			case 'k':
				$memory = (int) $memory * 1024;
				break;
			case 'G':
			case 'g':
				$memory = (int) $memory * 1073741824;
				break;
			default:
				$memory = (int) $memory;
				break;
		}

		if ($memory < 536870912) {
			$output->writeln('<info>Increasing memory limit to 512M</info>');
			$iniSet = ini_set('memory_limit', 536870912);
		}

		// Call the twig environment. This seems bizarre but is important,
		// otherwise the AssetManager won't know how to load Twig files. It is
		// the only way we could get around a crazy dependency loop for the time being.
		$this->get('templating.twig.environment');

		$assetManager = $this->get('asset.manager');
		$fileSystem   = $this->get('filesystem.finder');
		$twigLoader   = $this->get('templating.twig.loader');

		// Get list of loaded codules
		$modules = $this->get('module.loader')->getModules();

		// Service to locate module paths
		$moduleLocator = $this->get('module.locator');

		$output->writeln('<info>Generating public assets for ' . count($modules) . ' modules.</info>');

		// Compile assets for all cogules
		foreach ($modules as $module) {
			$moduleName = str_replace("\\", ReferenceParser::SEPARATOR, $module);

			// Path for cogules' view directory
			$originDir = $moduleLocator->getPath($module, false) . 'resources/view';

			// If there are no views for a module, no need to check for templates
			if (!file_exists($originDir)) {
				$output->writeln("<comment>No views found for module {$moduleName}</comment>");

				continue;
			}

			foreach ($fileSystem->in($originDir) as $file) {
				// Check that the file is one that we need to combine.
				if (!in_array($file->getExtension(), $this->_fileExtensions)) {
					continue;
				}

				$assetManager->addResource(new TwigResource(
					$twigLoader,
					new TemplateReference($file->getPathname(), $file->getExtension())
				), 'twig');
			}
		}

		// Compile assets for view overrides
		if (file_exists('cog://view/')) {
			foreach ($fileSystem->in('cog://view/') as $file) {
				// Check that the file is one that we need to combine.
				if (!in_array($file->getExtension(), $this->_fileExtensions)) {
					continue;
				}

				$this->_services['asset.manager']->addResource(new TwigResource(
					$twigLoader,
					new TemplateReference($file->getRealPath(), $file->getExtension())
				), 'twig');
			}
		}

		// Compile the assets
		$this->_services['asset.writer']->writeManagerAssets($this->_services['asset.manager']);

		$output->writeln("<info>Compiled assets for all views</info>");

		if (!empty($iniSet)) {
			$output->writeln('<info>Resetting memory limit to ' . $iniSet . '</info>');
			ini_set('memory_limit', $iniSet);
		}
	}
}
