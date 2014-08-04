<?php

namespace Message\Cog\Localisation;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

use Symfony\Component\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator implements ContainerAwareInterface
{
	protected $_container;
	protected $_cacheEnabled = false;

	/**
	 * @{inheritDoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	/**
	 * Enable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function enableCaching()
	{
		$this->_cacheEnabled = true;
	}

	/**
	 * Disable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function disableCaching()
	{
		$this->_cacheEnabled = false;
	}

	/**
	 * @{inheritDoc}
	 */
	public function loadCatalogue($locale)
	{
		$cacheKey = sprintf('cog.localisation.translations1.%s', $locale);

		if (false === $this->_cacheEnabled or
			false === $this->catalogues = $this->_container['cache']->fetch($cacheKey)
		) {
			$parser = $this->_container['reference_parser'];

			// Load translation files from modules
			foreach ($this->_container['module.loader']->getModules() as $moduleName) {
				$moduleName = str_replace('\\', $parser::SEPARATOR, $moduleName);
				$dir        = 'cog://@' . $moduleName . $parser::MODULE_SEPARATOR . 'translations';

				if (file_exists($dir)) {
					foreach ($this->_container['filesystem.finder']->in($dir) as $file) {
						$this->addResource('yml', $file->getPathname(), $file->getFilenameWithoutExtension());
					}
				}
			}

			// Load application translation files
			$dir = $this->_container['app.loader']->getBaseDir().'translations';
			if (file_exists($dir)){	
				foreach ($this->_container['filesystem.finder']->in($dir) as $file) {
					$this->addResource('yml', $file->getPathname(), $file->getFilenameWithoutExtension());
				}
			}

			parent::loadCatalogue($locale);

			$this->_container['cache']->store($cacheKey, $this->catalogues);
		}
	}

}