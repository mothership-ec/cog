<?php

namespace Message\Cog\Config;

use Composer\Script\PackageEvent;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;

/**
 * Configuration fixture manager.
 *
 * Handles the relocation and updating of configuration files when Cog modules
 * are installed or updated. These methods are Composer scripts, that are set up
 * to be used in the application's `composer.json` file.
 *
 * Currently, the updating mechanism simply warns the developer of any changes
 * to the configuration fixtures in the new Cog module version. In future, we'd
 * like to do something more automated and less susceptible to human error.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FixtureManager
{
	const CONFIG_FIXTURE_PATH = 'Fixtures/Config/';

	public function postInstall(PackageEvent $event)
	{
		$package = $event->getOperation()->getPackage();

		if (!$this->isPackageCogModule($package)) {
			return false;
		}

		$fixtureDir = $package->getTargetDir() . self::CONFIG_FIXTURE_PATH;

		if ($this->validateFixtureDirectory($fixtureDir, $event->getIO())) {
			$dir = new DirectoryIterator($fixtureDir);

			foreach ($dir as $file) {
				if ('yml' !== $file->getExtension()) {
					continue;
				}

				// check if file readable?
				//
				// move to config directory
				//
				// ask for confirmation to move them?
				//
				// clear the config cache! ... can we even do this reliably from here?
			}
		}
	}

	/**
	 * Check if a given Composer package is a Cog module.
	 *
	 * Cog module packages are identified by a name prefixed with 'cog-',
	 * regardless of the vendor name.
	 *
	 * @param  PackageInterface $package The Composer package to check
	 * @return boolean                   True if the package is a Cog module
	 */
	public function isPackageCogModule(PackageInterface $package)
	{
		list($vendor, $name) = explode('/', $package->getName());

		return 'cog-' === substr($name, 0, 4);
	}

	public function validateFixtureDirectory($fixtureDir, IOInterface $io)
	{
		if (file_exists($fixtureDir)) {
			if (!is_readable(($fixtureDir))) {
				$io->write(sprintf(
					'WARNING: Configuration fixture directory `%s` is not readable so configuration fixtures could not be moved to the application config directory.',
					$fixtureDir
				), true);

				return false;
			}
		}

		return true;
	}
}