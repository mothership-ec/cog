# Cache component

A simple wrapper and interface around `TreasureChest`'s interfaces and classes.

Currently the only cache available is the filesystem cache. In the future we should check to see if APC is
enabled and use that instead.

## Usage

Create an instance of the cache object

	$cache = new Cache\Filesystem('/tmp'); // directory to store cache data in

The `$cache` object can then be manipulated like so:

	$cache->store('email', 'bob@example.org');
	$cache->store('age', 45);
	$cache->fetch('email'); // returns bob@example.org
	$cache->inc('age', 5); // returns 50
	$cache->dec('age', 10); // returns 40
	$cache->delete('email');
	$cache->fetch('email'); // returns boolean FALSE

The methods and their signatures generally match the functionality defined in the [apc_* set of functions](http://www.php.net/manual/en/ref.apc.php). 

## Methods

- `add` — Cache a new variable in the data store
- `clear` — Clears the entire cache
- `dec` — Decrease a stored number
- `delete` — Removes a stored variable from the cache
- `exists` — Checks if a key exists
- `fetch` — Fetch a stored variable from the cache
- `inc` — Increase a stored number
- `store` — Cache a variable in the data store
