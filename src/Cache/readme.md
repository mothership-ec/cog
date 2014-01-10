# Cache Component

A simple wrapper around the `TreasureChest` package, providing caching functionality.

## Usage

Create an instance of the cache `Instance` object and pass in the adapter you wish to use:

	$cache = new Message\Cog\Cache\Instance(
		new Message\Cog\Cache\Adapter\APC
	);

The `$cache` object can then be manipulated like so:

	$cache->store('email', 'bob@example.org');
	$cache->store('age', 45);
	$cache->fetch('email'); // returns bob@example.org
	$cache->inc('age', 5); // returns 50
	$cache->dec('age', 10); // returns 40
	$cache->delete('email');
	$cache->fetch('email'); // returns boolean FALSE

The methods and their signatures generally match the functionality defined in the [apc_* set of functions](http://www.php.net/manual/en/ref.apc.php).

### Methods

* `add` — Cache a new variable in the data store
* `clear` — Clears the entire cache
* `dec` — Decrease a stored number
* `delete` — Removes a stored variable from the cache
* `exists` — Checks if a key exists
* `fetch` — Fetch a stored variable from the cache
* `inc` — Increase a stored number
* `store` — Cache a variable in the data store

## The cache store service

The service definition for the cache is set to use APC if it is available, otherwise the filesystem cache is used. A global prefix of the application name; the environment name and the installation name (if set) is used, so that caches are unique to the installation.

	$serviceContainer['cache'] = $serviceContainer->share(function($s) {
		$adapterClass = (extension_loaded('apc') && ini_get('apc.enabled')) ? 'APC' : 'Filesystem';
		$adapterClass = '\Message\Cog\Cache\Adapter\\' . $adapterClass;
		$cache        = new \Message\Cog\Cache\Instance(
			new $adapterClass
		);
		$cache->setPrefix(implode('.', array(
			$s['app.loader']->appName,
			$s['environment']->get(),
			$s['environment']->installation(),
		)));

		return $cache;
	});

### Creating a custom cache store service

If you need to use a cache store with different settings than the `cache` service definition defined in Cog, you can simply add more service definitions with your preferred settings.

For example, you may wish to create a shared cache between all installations of this application on this server that only uses APC:

	$serviceContainer['cache.shared'] = $serviceContainer->share(function($s) {
		$cache = new \Message\Cog\Cache\Instance(
			new \Message\Cog\Cache\Adapter\APC
		);
		$cache->setPrefix($s['app.loader']->appName);

		return $cache;
	});