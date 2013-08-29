<?php

namespace Message\Cog\Config;

use Composer\Composer;
use Composer\Script\PackageEvent;
use Composer\Package\BasePackage;

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
 * @todo We should also detect NEW config fixture files in `postUpdate()` and
 *       move them to the application config directory just like in `postInstall()`
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FixtureManager
{
	const CONFIG_FIXTURE_PATH = 'resources/fixtures/config/';

	static protected $_updatedFixtures = array();

	/**
	 * Moves configuration fixtures defined in a just-installed Cog module
	 * package to the application's config directory.
	 *
	 * @param  PackageEvent $event The post-install package event
	 *
	 * @return void|false          Returns false if the method needn't run
	 */
	static public function postInstall(PackageEvent $event)
	{
		$package = $event->getOperation()->getPackage();

		if (!static::isPackageCogModule($package)) {
			return false;
		}

		$workingDir = static::getWorkingDir();
		$fixtureDir = static::getConfigFixtureDir($event->getComposer(), $package);

		try {
			$fixtures = static::getFixtures($fixtureDir);

			if (!$fixtures) {
				return false;
			}

			foreach ($fixtures as $fixture) {
				$file = $workingDir . 'config/' . $fixture;
				$packageName = $event->getOperation()->getPackage()->getPrettyName();

				if(file_exists($file)) {
					$event->getIO()->write("<warning>Config Fixture already exists for {$packageName}</warning>");
					continue;
				}

				copy($fixtureDir . $fixture, $file);

				$event->getIO()->write(sprintf(
					'<info>Moved package `%s` config fixture `%s` to application config directory.</info>',
					$packageName,
					$fixture
				));
			}
		}
		catch (Exception $e) {
			$event->getIO()->write('<error>' . $e->getMessage() . '</error>');
		}
	}

	/**
	 * Stores the MD5 checksums for all config fixtures in the given package
	 * before the package is updated.
	 *
	 * This is then used for a comparison to the MD5 checksums of the new config
	 * fixtures in `postUpdate()`.
	 *
	 * @param  PackageEvent $event The pre-update package event
	 *
	 * @return void|false          Returns false if the method needn't run
	 */
	static public function preUpdate(PackageEvent $event)
	{
		if (!static::isPackageCogModule($event->getOperation()->getInitialPackage())) {
			return false;
		}

		$package    = $event->getOperation()->getInitialPackage();
		$fixtureDir = static::getConfigFixtureDir($event->getComposer(), $package);

		try {
			$fixtures = static::getFixtures($fixtureDir);

			if (!$fixtures) {
				return false;
			}

			static::$_updatedFixtures[$package->getPrettyName()] = array();

			foreach ($fixtures as $fixture) {
				static::$_updatedFixtures[$package->getPrettyName()][$fixture] = md5_file($fixtureDir . $fixture);
			}
		}
		catch (Exception $e) {
			$event->getIO()->write('<error>' . $e->getMessage() . '</error>');
		}
	}

	/**
	 * Detects changes to config fixtures in the newly updated version of a
	 * given package.
	 *
	 * The user is warned if a difference is detected, as they should manually
	 * check to see what has changed.
	 *
	 * @param  PackageEvent $event The post-update package event
	 *
	 * @return void|false          Returns false if the method needn't run
	 */
	static public function postUpdate(PackageEvent $event)
	{
		if (!static::isPackageCogModule($event->getOperation()->getInitialPackage())) {
			return false;
		}

		$package    = $event->getOperation()->getInitialPackage();
		$fixtureDir = static::getConfigFixtureDir($event->getComposer(), $package);

		try {
			$fixtures = static::getFixtures($fixtureDir);

			if (!$fixtures) {
				return false;
			}

			foreach ($fixtures as $fixture) {
				$checksum = md5_file($fixtureDir . $fixture);

				if (isset(static::$_updatedFixtures[$package->getPrettyName()][$fixture])
				 && $checksum !== static::$_updatedFixtures[$package->getPrettyName()][$fixture]) {
					$event->getIO()->write(sprintf(
						'<warning>Package `%s` config fixture `%s` has changed: please review manually.</warning>',
						$package->getPrettyName(),
						$fixture
					));
				}
			}
		}
		catch (Exception $e) {
			$event->getIO()->write('<error>' . $e->getMessage() . '</error>');
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
	 * Get the full path to the config fixture directory for a given package.
	 *
	 * @param  Composer    $composer The current Composer instance
	 * @param  BasePackage $package  The package to get the directory for
	 *
	 * @return string                Full path to the package's config fixture dir
	 */
	static public function getConfigFixtureDir(Composer $composer, BasePackage $package)
	{
		return implode('/', array(
			realpath($composer->getConfig()->get('vendor-dir')),
			$package->getPrettyName(),
			$package->getTargetDir(),
			static::CONFIG_FIXTURE_PATH
		));
	}

	/**
	 * Check if a given Composer package is a Cog module.
	 *
	 * Cog module packages are identified by a name prefixed with 'cog-',
	 * regardless of the vendor name.
	 *
	 * @param  BasePackage $package The Composer package to check
	 *
	 * @return boolean              True if the package is a Cog module
	 */
	static public function isPackageCogModule(BasePackage $package)
	{
		list($vendor, $name) = explode('/', $package->getPrettyName());

		return 'cog-' === substr($name, 0, 4);
	}

	/**
	 * Get all configuration fixtures defined in a given directory.
	 *
	 * Configuration fixtures must have a file extension of '.yml'.
	 *
	 * @param  string $fixtureDir Path to the directory to look in
	 *
	 * @return false|array        False if the directory does not exist or is
	 *                            empty, otherwise an array of the fixture filenames
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

		return $fixtures ?: false;
	}
}