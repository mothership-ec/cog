<?php

namespace Message\Cog\Config;

use Composer\Script\PackageEvent;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;

use DirectoryIterator;

/**
 * Configuration fixture manager.
 *
 * Handles the relocation and updating of configuration files when Cog modules
 * are installed or updated. These methods are Composer scripts, that are set up
 * to be used in the application's `composer.json` file.
 *
 * @todo Currently, the updating mechanism simply warns the developer of any
 *       changes to the configuration fixtures in the new Cog module version. In
 *       future, we'd like to do something more automated and less susceptible
 *       to human error.
 *
 * @todo We could also hook into the package uninstall events and let the
 *       developer know that config files can be deleted where they are defined
 *       in an uninstalled module (or even delete them automatically!)
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FixtureManager
{
	const CONFIG_FIXTURE_PATH = 'Fixtures/Config/';

	/**
	 * Moves configuration fixtures defined in a just-installed Cog module
	 * package to the application's config directory.
	 *
	 * @param  PackageEvent $event The post-install package event
	 * @return boolean             True if at least one fixture was moved
	 */
	static public function postInstall(PackageEvent $event)
	{
		if (!self::isPackageCogModule($event->getOperation()->getPackage())) {
			return false;
		}

		$workingDir = self::getWorkingDir();
		$fixtureDir = self::getConfigFixtureDir($event);

		try {
			$fixtures = self::getFixtures($fixtureDir);

			if (!$fixtures) {
				return false;
			}

			foreach ($fixtures as $fixture) {
				if (copy($fixtureDir . $fixture, $workingDir . 'config/' . $fixture)) {
					$message = '<info>Moved package `%s` config fixture `%s` to application config directory.</info>';
				}
				else {
					$message = '<error>Moving package `%s` config fixture `%s` to application config directory FAILED - please move manually.</error>';
				}

				$event->getIO()->write(sprintf(
					$message,
					$event->getOperation()->getPackage()->getPrettyName(),
					$fixture
				));
			}

			return true;
		}
		catch (Exception $e) {
			$event->getIO()->write('<error>' . $e->getMessage() . '</error>');

			return false;
		}
	}

	/**
	 * Get the full path to the current working directory, with a trailing slash.
	 *
	 * As far as I can work out, this is the only reliable way to do this at
	 * present and Composer's API has no methods to retrieve this information.
	 *
	 * If a more solid approach becomes available, this method should be updated.
	 *
	 * @return string Full path to the current working directory
	 */
	static public function getWorkingDir()
	{
		return realpath(null) . '/';
	}

	/**
	 * Get the full path to the config fixture directory for a given package
	 * event (and therefore a given package).
	 *
	 * @param  PackageEvent $event The package event to find the directory for
	 * @return string              Full path to the package's config fixture dir
	 */
	static public function getConfigFixtureDir(PackageEvent $event)
	{
		return implode('/', array(
			realpath($event->getComposer()->getConfig()->get('vendor-dir')),
			$event->getPackage()->getPrettyName(),
			$event->getPackage()->getTargetDir(),
			self::CONFIG_FIXTURE_PATH
		));
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
	static public function isPackageCogModule(PackageInterface $package)
	{
		list($vendor, $name) = explode('/', $package->getName());

		return 'cog-' === substr($name, 0, 4);
	}

	/**
	 * Get all configuration fixtures defined in a given directory.
	 *
	 * Configuration fixtures must have a file extension of '.yml'.
	 *
	 * @param  string $fixtureDir Path to the directory to look in
	 *
	 * @return false|array        False if the directory does not exist,
	 *                            otherwise an array of the fixture filenames
	 *
	 * @throws Exception          If the directory is not readable
	 * @throws Exception          If any fixture files are not readable
	 */
	static public function getFixtures($fixtureDir)
	{
		if (!file_exists($fixtureDir)) {
			return false;
		}

		if (!is_readable(($fixtureDir))) {
			throw new Exception(sprintf(
				'WARNING: Configuration fixture directory `%s` is not readable so configuration fixtures could not be moved to the application config directory.',
				$fixtureDir
			));
		}

		$dir      = new DirectoryIterator($fixtureDir);
		$fixtures = array();

		foreach ($dir as $file) {
			if ('yml' !== $file->getExtension()) {
				continue;
			}

			if (!$file->isReadable()) {
				throw new Exception(sprintf(
					'WARNING: Configuration fixture `%s` is not readable so could not be moved to the application config directory.',
					$file->getBasename()
				));
			}

			$fixtures[] = $file->getBasename();
		}

		return $fixtures;
	}
}