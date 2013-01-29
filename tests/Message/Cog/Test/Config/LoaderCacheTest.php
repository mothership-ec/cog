<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\LoaderCache;
use Message\Cog\Config\Group;

use Message\Cog\Test\Cache\Adapter\Faux as FauxCache;
use Message\Cog\Test\Application\FauxEnvironment;

use stdClass;

class LoaderCacheTest extends \PHPUnit_Framework_TestCase
{
	static public function getInvalidCaches()
	{
		return array(
			array('string'),
			array(null),
			array(1),
			array(1.2),
			array(new stdClass),
			array(true),
			array(false),
			array(array(
				'key1' => new Group,
				'i have no key!',
				'key2' => new Group,
			)),
			array(array(
				'key1' => new Group,
				'key2' => new stdClass,
				'key3' => new Group,
			)),
		);
	}

	public function testLoadingFromCache()
	{
		$dir      = realpath(__DIR__) . '/fixtures';
		$cache    = new \Message\Cog\Cache\Instance(new FauxCache);
		$env      = new FauxEnvironment;
		$loader   = new LoaderCache($dir, $env, $cache);
		$registry = new NonLoadingRegistry($loader);

		$cache->store($loader->getCacheKey(), RegistryTest::getConfigFixtures());

		$loader->load($registry);

		$this->assertEquals($cache->fetch($loader->getCacheKey()), $registry->getAll());
	}

	/**
	 * @dataProvider getInvalidCaches
	 *
	 * @todo Fix this test. For some reason, even though the caches are
	 *       successfully deleted within LoaderCache::loadFromCache, they
	 *       are still there within this test. Makes no sense as the cache
	 *       instance should be automatically updated... very odd
	 */
	public function testInvalidCacheGetsCleared($cached)
	{
		$dir      = realpath(__DIR__) . '/fixtures';
		$cache    = new \Message\Cog\Cache\Instance(new FauxCache);
		$env      = new FauxEnvironment;
		$loader   = new LoaderCache($dir, $env, $cache);
		$registry = new NonLoadingRegistry($loader);

		$cache->store($loader->getCacheKey(), $cached);

		$loader->load($registry);

		$this->assertFalse($cache->exists($loader->getCacheKey()));
	}

	public function testSavingToCache()
	{
		$dir      = realpath(__DIR__) . '/fixtures';
		$cache    = new \Message\Cog\Cache\Instance(new FauxCache);
		$env      = new FauxEnvironment;
		$loader   = new LoaderCache($dir, $env, $cache);
		$registry = new NonLoadingRegistry($loader);
		$expected = include 'expected_groups.php';

		$env->set('live');
		$env->setInstallation('server6');

		$loader->load($registry);

		$this->assertEquals($cache->fetch($loader->getCacheKey()), $expected);
	}
}