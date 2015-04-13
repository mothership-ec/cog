# Changelog

## 4.3.0

- `assets.yml` config file for handling asset generation
- Option to disable automatic asset generation on local versions (does not affect non-local versions as automatic asset generation is already disabled)
- Memory limit on asset generation is increased to `512M` if less
- Added `QueryCounabletInterface` to DB component for counting/listing the queries run in a single request
- Added `CachableInterface` to DB component for allowing database results to be cached
- Added `CacheInterface` to represent a cache for database results
- Added `CacheCollection` to DB component for holding different caching options
- Added `Adapter\MySQLi\MemoryCache` class to DB component for caching results in memory (identified via `getName()` method as `mysql_memory`)
- Added `NoneCache` class to represent caching disabled (identified via `getName()` method as `none`)
- Added `cache` option to `db.yml`, defaults to `mysql_memory`
- `Adapter\MySQLi\Connection` class implements `QueryCountableInterface`
- The static `$_queryList` variable on the `Query` object has been moved to the `Adapter\MySQLi\Connection` class, and the `getQueryList()` and `getQueryCount()` methods on the `Query` object have been deprecated, as they now exist on the connection
- `Adapter\MySQLi\Connection` class implements `CachableInterface`

## 4.2.0

- Improved validation on nested set helper
- Add `NestedSetException` class to DB component
- Deprecates useless and unfinished `toArray()` method on `NestedSetHelper` in DB component
- Fix issue where double slash would appear on full URLs made from slugs in the routing component

## 4.1.1

- Improved bcrypt security checks
- Fixed incorrect header type on changelog

## 4.1.0

- Ability to trim trailing zeros off prices in the Twig price function

## 4.0.1

- Amendments to random string generation
- Amendments to readme
- Added changelog

## 4.0.0

+ Initial open source version

