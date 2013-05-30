# Application Component

This component is aware of high-level information about the application and is responsible for loading the application.

## Cog Bootstraps

Each Cog module has bootstraps for event listeners; services; routes and tasks. Cog itself also has bootstraps for many of these, primarily event listeners and services.

The bootstraps for Cog live in the Application component under the `Bootstrap` namespace. A list the services defined by Cog can be found in the main Cog readme file.

## Environment

The `Environment` class is responsible for determining the environment in which the application is running in, and translates this into useful instructions for how the application should behave.

An environment is made up of a name, an installation name (optionally) and a context.

### Environment names

* **local** Developers machine
* **test** When test suite is running
* **dev** A test site on a client server
* **staging** Deployed code ready to go live
* **live** Live public facing site

### Environment contexts

* **web** When PHP is running on a web server (in fcgi or as mod_php)
* **console** When PHP is running from the command line

### Setting the environment

#### Web context

In the web context, the environment name is determined from value of the `COG_ENV` environment flag. This can be set in the virtual host definition in either Nginx or Apache.

#### Console context

In the console context, the environment name is determined from the command-line option `--env=`, or `-e` in short form.

For example, running the following Cog commands will instruct Cog to execute in the staging environment:

	$ bin/cog services:list --env=staging
	$ bin/cog services:list -e staging

#### Setting an Installation Name

When setting the environment name as detailed above, an optional installation name can be appended. This can be any alphanumeric string prepended by a hyphen.

The installation name is simply a unique identifier for the specific installation of the application. It's very helpful if you need to idenfity a specific front-end server, a specific dev site or even a specific local development machine.

##### Examples

* `dev-dev6`
* `dev-new`
* `live-server1`
* `live-server2`
* `local-joe`
* `local-danny`

## Application Loader

The application loader `Loader` is of course responsible for loading the Cog application.

There are five steps to loading a Cog application, each of which are public methods that can be called individually on the `Loader`. Or, the `run()` method runs all five steps in sequence and returns the result of the final step.

### The five steps of application loading

#### Initialise

The `initialise()` step sets any required PHP settings; includes the Composer autoloader; defaults the service container to use if it was not already defined by calling `setServiceContainer($container)` and then adds a service, `class.loader` for the Composer autoloader.

#### Load Cog

The `loadCog()` step adds a service for the application loader itself and the bootstrap loader. Then it loads the bootstraps found in the `Message\Cog\Application\Bootstrap` namespace.

#### Set Context

The `setContext()` method finds the context from the `Environment` and instantiates the context loader class defined on the service container with the name "app.context.{contextName}" where {contextName} is one of the two valid context names.

#### Load Modules

The `loadModules()` step loads the modules defined by `_registerModules()` on the application-specific loader by passing them to the `module.loader` service's `run()` method. This should be an implementation of `Message\Cog\Module\LoaderInterface`.

#### Execute

The final step, `execute()` calls and returns the value of the `run()` method on the context class that was set by `setContext()`.

For web requests, this dispatches the request and renders the response. For console requests, the command is sent to the Command component.

Finally, the `terminate` event is dispatched as the last thing that happens in the request execution.

### Extending the application loader

Every Cog app will have to have it's own loader class that extends `Message\Cog\Application\Loader`. This file is created by the `$ bin/cog install` command.

The primary function of the application-specific loader is to define the list of modules to load, in order.

Here's an example of an application-specific loader for an application called "Pongatron".

	<?php
	
	namespace Pongatron;
	
	class App extends \Message\Cog\Application\Loader
	{
		protected function _registerModules()
		{
			return array(
				'Message\Raven',
				'Message\UserSkeleton',
				'Pongatron\User',
				'Pongatron\Game',
				'Pongatron\Reporting',
			);
		}
	}