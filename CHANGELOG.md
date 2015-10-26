# Changelog

## 4.14.0

- Mothership now bypasses minification of assets when developing locally by default. Since the assets currently get generated on every request in order to ensure that changes made to CSS and JS files can be seen right away, load times were very slow as the generation process also included minification
- Added `AssetManagement\NullFilter` class which implements `\Assetic\Filter\FilterInterface`, but does nothing to amend source files
- Added `asset.filter.minify.css` service which returns instance of `\Assetic\Filter\CssMinFilter`
- Added `asset.filter.minify.js` service which returns instance of `\Assetic\Filter\JSMinFilter`
- Added `asset.filter.null` service which returns instance of `AssetManagement\NullFilter`
- Added `asset.yml` `local-minify` config option. Setting this to true will enable minification on local versions of Mothership. Set to false by default.
- `asset.yml` `auto-generate` option set to true by default
- Tasks will re-throw any caught exceptions

## 4.13.0

- Added ability to assign translations to exceptions for rendering error messages to the user while still being able to keep a log of the error message
- Added `Exception` component
- Added `Exception\TranslationExceptionInterface` interface which ensures that exceptions that allow for translation strings to be added have the same constructor and methods for adding translation details
- Added `Exception\TranslationExceptionTrait` trait which has methods declared in `Exception\TranslationExceptionInterface` for shared functionality between translation exceptions
- Added `Exception\TranslationLogicException` exception class which extends `\LogicException` and uses methods declared in `Exception\TranslationExceptionTrait`
- Added `Exception\TranslationRuntimeException` exception class which extends `\RuntimeException` and uses methods declared in `Exception\TranslationExceptionTrait`
- Added `Filter\CallbackFilter` class for applying filters with custom filtering capability without the need to create a whole new class
- `Controller::addFlash()` method now filters out duplicate flash messages

## 4.12.0

- Added `Field\Content` class, adapted from `Message\Mothership\Page\Content` in the CMS module, to allow content to be more consistent across modules
- Added `Field\ContentBuilder` class for joining form data onto a `Content` object
- Added `Field\ContentInterface` interface representing content classes, to be used with the `Field\ContentBuilder` class
- Added `Field\Type\Hidden` for adding hidden form fields to content
- Added `HTTP\ResponseInterface`, which contains all the methods of `Symfony\Component\HttpFoundation\Response` class, as a means of ensuring that we can type hint all Cog response classes
- Added `Debug\Whoops\SimpleHandler` class which rethrows any exceptions an lets the developer's PHP handler deal with the error
- Added `field.content.builder` service which returns an instance of `Field\ContentBuilder`
- Added `Field\Group::get()` method for returning a field in a group, `__get()` now calls this method
- Added `Field\Group::exists()` method for checking if a field exists, `__isset()` now calls this method
- `HTTP\Response` implements `HTTP\ResponseInterface`
- `HTTP\RedirectResponse` implements `HTTP\ResponseInterface`
- `HTTP\StreamedResponse` implements `HTTP\ResponseInterface`
- Disabled `Whoops` error handler as it made error messages harder to interpret than just using xDebug
- No longer throws `RuntimeException` if a module has no `Bootstrap` directory. Will only throw it if the given path is not a directory, it is not readable, or it is not executable
- Resolved issue where referencing a file, such as an image, absolutely from a CSS file would cause an undefined variable error
- Resolved issue where tasks would print output twice
- Removed broken `http.oath.factory` service
- Removed broken `rest.xml_request_dispatcher` service
- Removed broken `http.rest.request_dispatcher_collection` service
- Deprecated `Form\Handler` class
- Deprecated `Validation` component
- Deprecated `Console\Command\ServiceList` command class and removed it from command list

## 4.11.0

- Increased reliability and refactor for `Security\StringGenerator` class. String generation methods now pass a closure to a private `_generateStringFromCallback()` method that use the closure as the algorithm for creating the string. A random string will be generated, and then any disallowed characters will be stripped out, and if the string no longer matches the given length, a new string will be generated, appended, and the process will repeat. If the string exceeds the given length, it will be trimmed down to size and returned.
- Added `Security\StringGenerator::setTenacity()` method for setting the number of attempts generate methods should make to create a string that matches the given requirements. Defaults to 1000.
- Added `Security\StringGenerator::allowChars()` method for setting which characters should be allowed to appear in the randomly generated string
- Added `Security\StringGenerator::disallowChars()` method for setting which characters should not be allowed to appear in the randomly generated string
- Added `Security\Exception\GenerateStringException` exception class to be thrown when a string cannot be generated by the `Security\StringGenerator` class
- `Security\StringGenerator::generate()` attempts to create random string from `/dev/arandom` before attempting to create string from `/dev/urandom`
- Deprecated `Security\StringGenerator::setPattern()` method and `Security\StringGenerator::$_pattern` property. It is too inefficient and difficult to generate a string matching a regular expression, and on top of that the methods that exist only ever generate strings with alphanumeric characters, dots or slashes, and there is a set length for the string. This means that it is possible to set a regex pattern that it would be impossible to create a string with, for instance if the length had been set to `10`, but the regex pattern had been set to `/^[A-Z]{11}$/`, these two configurations would contradict each other.
- Overhauled unit tests for `Security\StringGenerator` to test new features, as well as running tests 200 times each to ensure that the tests are reliable and are not simply passing by chance.

## 4.10.0

- Added forth parameter to `DB\QueryBuilder::join()` that takes a boolean to determine whether to use an `ON` clause (if set to `true`) or a `USING` clause (if set to `false`). Defaults to `true`.
- Added forth parameter to `DB\QueryBuilder::leftJoin()` that takes a boolean to determine whether to use an `ON` clause (if set to `true`) or a `USING` clause (if set to `false`). Defaults to `true`.
- Added `DB\QueryBuilder::joinOn()` method for doing a join with the `ON` clause
- Added `DB\QueryBuilder::joinUsing()` method for doing a join with the `USING` clause
- Added `DB\QueryBuilder::leftJoinOn()` method for doing a left join with the `ON` clause
- Added `DB\QueryBuilder::leftJoinUsing()` method for doing a left join with the `USING` clause
- `Controller::addFlash()` method now automatically translates strings if possible
- Added unit tests for new `DB\QueryBuilder` methods
- Added unit tests for `Field` component (imported from CMS, where fields used to be)

## 4.9.1

- Resolve issue where `DB\QueryBuilder` would create joins and left joins separately, and therefore ignoring the order in which they were added
- Added unit tests to ensure that join and left join order is maintained in `DB\QueryBuilder`

## 4.9.0

- Added `addParams()` method to `QueryBuilder`, allowing developers to add extra parameters for parsing ad hoc, i.e. not just in where statements
- `QueryBuilder` parses queries when `getQueryString()` is called
- Added `run()` method to `QueryBuilder` which calls `run()` on the `Query` object itself
- Deprecated `QueryBuilderInterface` as it was too restrictive, just use `QueryBuilder` instead
- Added protected `_getConnection()` method to `QueryParser` instead of calling `$this->_connection` directly
- Amended `QueryBuilder` unit tests to accommodate the `QueryParser::parse()` call in `getQueryString()`
- Disabled email notifications on Travis

## 4.8.0

- Removed broken and deprecated unit tests - now all existing unit tests pass
- Implement Travis continuous integration
- `Slug::sanitize()` method comes with some character default replacements not always covered by `iconv()` function
- `Result::bindTo()` can accept a `ValueObject\Collection` as its forth parameter to load objects from a cache. Use the fifth parameter to set the key of the collection
- Unit test for cache on `Result::bindTo()` method
- PHPUnit no longer uses deprecated strict mode

## 4.7.1

- Resolve issue where `LinkedChoice` form field would reset the keys of the choices array if they were numeric values

## 4.7.0

- Resolve issue on `DB\QueryBuilder` where `groupBy()` and `orderBy()` would break if you gave it an array
- Added unit tests for giving `groupBy()` an array, and for giving it an array twice
- Added unit tests for giving `orderBy()` an array, and for giving it an array twice
- Allow the sort on `Collection` class to be set to null via `setSort()`, which disables the sorting
- Added unit tests for setting the sort to null on `Collection` class

## 4.6.0

- Migrations are sorted by timestamp and name before being run with the `migrate:run` command
- Feedback provided when a migration has already been run
- Migration names are validated before being run, a `\LogicException` is thrown if invalid
- Added `runFromReferences()` method to `Migrator` class for looping through an array of references and sorting migrations before running them
- Renamed `migration` service to `migrator` (`migration` is still available for backwards compatibility)

## 4.5.1

- Resolve issue where classes that extend ValueObject\Collection and override the constructor break as a result of there being no sort setting

## 4.5.0

- Added `email.yml` config for setting a default email address to send emails to when site is not in 'live' mode. Also allows developers add set email addresses or partial email addresses to a whitelist, which determines which email addresses can be sent emails as if the site were live when in dev mode.
- Removed references to `Message` in email whitelists and readme
- Added `module_exists` Twig function to alias `moduleExists`
- `moduleExists` and `module_exists` Twig functions allow namespaces to be delimited by backslashes or colons
- Resolved issue in `ValueObject\Collection` class no longer error when trying to create the exception message if the key is a closure
- `ValueObject\Collection` classes no longer sort unnecessarily, they will now sort as data leaves rather than on the way in
- Updated `symfony/console` dependency to 2.7 minimum

## 4.4.3

- Fix issue where integer field would break if value was empty
- Lock `symfony\options-resolver` to 2.6 as it was throwing a deprecated error in later versions

## 4.4.2

- Include `Zend\Escaper` library in `composer.json` file to prevent `d()` and `de()` functions breaking when xDebug is not installed

## 4.4.1

- Fix issue where falsy values such as empty strings were being converted to current datetimes when passed into the `Date` and `Datetime` fields

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

