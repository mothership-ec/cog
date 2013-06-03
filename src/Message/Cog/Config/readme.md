# Config Component

This component is responsible for everything to do with application configurations. Loading them; parsing them; providing them and so on.

## Configuration Storage

In a Cog application, configuration files are stored in the root `/config` directory. Configuration files are in the YAML format with the extension `.yml`.

### Environment-specific Configuration

Inside the root `/config` directory, each environment can have its own directory. Configuration files in these environment directories are "laid over" the base configuration file when the application is running under that environment.

For example, consider the following `/config` directory:

	config
		dev
			raven.yml
		raven.yml

The contents of `/config/raven.yml` is as follows:

	enabled: true
	dsn: udp://auth:details@server.local:3031/projectID
	error-handler:
	   enabled: true
	   call-existing-handler: true
	   error-types: E_ALL
	exception-handler:
	   enabled: true
	   call-existing-handler: true

And the contents of `/config/dev/raven.yml` is as follows:

	enabled: false

When the application is running in the `dev` environment, all of the configuration variables from the base `/config/raven.yml` file are used, except for `enabled`, which is overwritten by `/config/dev/raven.yml` to be `false`.

These environment overwrites work at an individual configuration line level, so `/config/dev/raven.yml` was changed to:

	error-handler:
	   call-existing-handler: false

Then only the `error-handler/call-existing-handler` configuration variable will be overwritten, and all other configuration variables, including those within `error-handler` will be pulled from the base `/config/raven.yml` file.

### Installation-specific Configuration

There is a third layer of configuration stacking available, at installation level. This is defined by the installation name. If no installation name is set, this layer is not applied.

This layer is applied in exactly the same way as the environment-specific configuration layer, at an individual configuration variable layer.

Consider a development installation on a developer's computer with the following environment line:

	fastcgi_param COG_ENV local-joe;

When the configurations are loaded for this installation, the following would happen for each configuration group:

* The base `/config/[groupName].yml` file is loaded
* The environment-specific `/config/local/[groupName].yml` file is loaded and overlayed
* The installation-specific `/config/local/joe/[groupName].yml` file is loaded and overlayed

For example, you would most likely want the `joe` installation to connect to a different database, but to pick up all of the standard `local` configurations.

So `/config/db.yml` might look like:

	host: db.testserver.com
	username: test
	password: test123
	database: appname_test

And `/config/local/db.yml` might look like this:

	host: server.local
	database: appname_dev

And `/config/local/joe/db.yml` might look like this:

	username: joe
	password: myPassword

The compiled configuration for this installation (environment name `local` and installation name `joe`) would be:

	host: server.local
	username: joe
	password: myPassword
	database: appname_dev

## Accessing Configurations

Configuration variables are accessed via the `Registry`, which is stored as a shared service with the identifier `cfg`. The `Registry` allows configuration group access via both array and object notation:

	$services['cfg']->raven->enabled;
	$services['cfg']['merchant']->address->postcode;

## Loading Configurations

Configurations are lazy loaded, so all configurations are loaded only the first time that something in the application requests a configuration variable from the `Registry`. At this point, the `Registry` passes itself to the `Loader`, which in turn loads all of the configuration groups before adding them to the `Registry`.

### Compilation

The `Compiler` class is responsible for taking the configuration files for one group (e.g. the base file, and any environment/installation overrides) and compiling it into properties on an instance of `Group`.

It's instantiated by the `Loader` and is responsible for turning the YAML into native PHP data and "overlaying" the appropriate environment-specific configuration variables.

### Caching

`LoaderCache` is a subclass of `Loader` which should be used in production for performance reasons. It is responsible for storing the compiled configuration groups in the cache defined as the service `cache` (by default this is APC, if it's available, otherwise filesystem) and retrieving the configurations from the cache if they have already been cached.

Unfortunately there is no way for the cache to automatically invalidate itself when any configuration file is changed. This would require scanning the filesystem which would pretty much render the caching obselete. This means that if you are using `LoaderCache`, **you need to manually invalidate the cache key `config` when any configuration files are changed** in order for the changes to take effect. This should ideally be built into the application's automated deployment process.

## Module Configurations

If a Cog module requires configuration sets, example configuration file(s) should be included under `fixtures` within the module.

Consider the `cog-raven` module:

	fixtures
		config
			raven.yml
	src
		Message
			Raven
				Bootstrap
					Bootstrap.php

The file `fixtures/config/raven.yml` should be an example for the configuration file, used as a default when the module is first installed. For example:

	enabled: false
	dsn: udp://auth:details@server.local:3031/projectID
	error-handler:
	   enabled: true
	   call-existing-handler: true
	   error-types: E_ALL
	exception-handler:
	   enabled: true
	   call-existing-handler: true

It's important that no real or dangerous values are used in these files, as they are copied to the application's `config` directory upon module installation.

### Composer Scripts

We use Composer's [Scripts](http://getcomposer.org/doc/articles/scripts.md) feature to automatically run some commands when a package is installed or updated).

This will be configured in the application's `composer.json` file (see also Cog Skeleton packages) as follows:

	"scripts": {
        "post-package-install": [
            "Message\\Cog\\Config\\FixtureManager::postInstall"
        ],
        "pre-package-update": [
        	"Message\\Cog\\Config\\FixtureManager::preUpdate"
        ],
        "post-package-update": [
        	"Message\\Cog\\Config\\FixtureManager::postUpdate"
        ]
    }

Composer automatically calls these at the appropriate times when running `composer install` or `composer update`.

#### Installing

The package installation script first checks that the installed package is a Cog module, by checking the package name begins with `cog-`. Such as `message/cog-cms`, `message/cog-raven` and so on.

If it is a Cog module, it checks for any `.yml` files in `/fixtures/config/` in the package's target directory. If it finds any, it moves them to the application's `/config` directory and feeds back to the developer on the CLI.

#### Updating

The package updating script also checks that the updated package is a Cog module in the same way. If it is, it detects changes between the previous version and the newly installed version of the Cog module's configuration fixtures, if it has any, by checking the MD5 checksums of all configuration fixtures before and after update.

If any of the configuration fixture files are different in the new version, a warning is displated to the developer on the CLI that they should manually check certain configuration fixture files within the Cog module to see if they need to carry across any of the changes to the application's `/config` directory.

## Usage

Here's an example showing how you might access some configuration variables on the group `analytics`.

	public function getAnalyticsTrackingCode()
	{
		if (!$serviceContainer['cfg']->analytics->enabled) {
			return $serviceContainer['cfg']->analytics->trackingCode;
		}
	}

	public function isPageIgnored($page)
	{
		foreach ($serviceContainer['cfg']->analytics->ignoredPages as $ignoredPage) {
			if ($ignoredPage === $page) {
				return true;
			}
		}

		return false;
	}