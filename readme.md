# Cog

## What is Cog?

Cog is Message's private internal PHP5 framework. It's very powerful but also lightweight and helps us create large-scale web applications with ease and confidence.

## What are the rules of Cog?

* Thou shalt never call a class/method statically unless:
	* Thy class is a function class in the `Message\Cog\Functions` namespace
	* Thy class is an instance of `Message\Cog\Service\Container`

## How do I set up a new Cog project?

@TODO write me

## What services does Cog define?

Cog defines the following services on the service container. Don't overwrite any of these in your application unless you want to replace the functionality of this service with your own class.

* `class.loader` This is the Composer autoloader class which is based on Symfony's autoloader.
* `http.request.master` This is the `Message\Cog\HTTP\Request` instance for the current master request.
* `bootstrap.loader` This is an instance of `Message\Cog\Bootstrap\Loader`.
* `app.loader` This is the instance of the application loader class, which will be the installation's sublass of `Message\Cog\Application\Loader`.
* `module.loader` This is an instance of `Message\Cog\Module\Loader`.

## What global events does Cog fire?

* `terminate` This is the very last thing that happens for any Cog request. Use this event for stopping debug timers; garbage collection; data logging; etc.