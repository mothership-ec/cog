# Changelog

## 4.4.0

- Added `Filter` component
- `FilterInterface`, representing a filter, added
- `AbstractFilter` implementing `FilterInterface` covers default functionality for a filter
- `FilterCollection` class for storing filters
- `FilterForm` class for representing filters as a form for user interaction
- `Filter\FormFactory` class for building instances of `FilterForm` from a `FilterCollection`
- `Filter\DataBinder` class for assigning an array of form data to filters in a collection
- `Filter\Exception\NoFiltersException` to be thrown when attempting to build a form from an empty `FilterCollection`
- `Filter\Exception\NoValueSetException` to be thrown when attempting to use a filter with no value
- Unit tests for entire `Filter` component
- New `q` option for `DB\QueryParser`, which converts instances of `DB\QueryBuilderInterface` into a query string for subqueries

## 4.3.3

- Fix invalid date format on `__toString()` method on `Datetime` field
- Fix invalid date format on `__toString()` method on `Date` field

## 4.3.2

- Revert change to `Pagination\Adapter\SQLAdapter` made in `4.3.0` as it appears it's a bit flaky as to when it does and doesn't work, will need further investigation (see <a href="https://github.com/mothership-ec/cog/issues/435">this issue</a>)

## 4.3.1

- Change `Datetime` field to convert values to instance of `DateTimeImmutable` if not already a `\DateTime` on `setValue()`, rather than on the way out via `getValue()`. Will throw `\LogicException` if date string is not valid
- `Date` field extends `Datetime`
- `__toString()` method of `Datetime` field converts `DateTimeImmutable` to date string with format of 'G:i:s d m Y'
- `__toString()` method of `Date` field converts `DateTimeImmutable` to date string with format of 'd m Y'

## 4.3.0

- `asset.yml` config file for handling asset generation
- Option to disable automatic asset generation on local versions (does not affect non-local versions as automatic asset generation is already disabled)
- Memory limit on asset generation is increased to `512M` if less
- Added `QueryCountableInterface` to DB component for counting/listing the queries run in a single request
- Added `CachableInterface` to DB component for allowing database results to be cached
- Added `CacheInterface` to represent a cache for database results
- Added `CacheCollection` to DB component for holding different caching options
- Added `Adapter\MySQLi\MemoryCache` class to DB component for caching results in memory (identified via `getName()` method as `mysql_memory`)
- Added `NoneCache` class to represent caching disabled (identified via `getName()` method as `none`)
- Added `cache` option to `db.yml`, defaults to `mysql_memory`
- `Adapter\MySQLi\Connection` class implements `QueryCountableInterface`
- The static `$_queryList` variable on the `Query` object has been moved to the `Adapter\MySQLi\Connection` class, and the `getQueryList()` and `getQueryCount()` methods on the `Query` object have been deprecated, as they now exist on the connection
- `Adapter\MySQLi\Connection` class implements `CachableInterface`
- Removed broken `Pagination\Adapter\DBResultAdapter` class and its `pagination.adapter.dbresult` service
- Fixed issue on `Pagination\Adapter\SQLAdapter` class where the `count` would always be set to 1

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

