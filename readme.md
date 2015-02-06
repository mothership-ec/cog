# Cog

## What is Cog?

Cog is Message's private internal PHP5 framework. It's very powerful but also lightweight and helps us create large-scale web applications with ease and confidence.

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
* `cog.load.success` Fired once Cog is ready to use (but before modules have been loaded). All of Cog's event handlers and services will be setup and registered by this point.

## Running tests

To execute all tests run `phpunit` in the root of the site.

You can run tests for individual components by running `phpunit --testsuite <componentname>`

e.g `phpunit --testsuite Routing` or `phpunit --testsuite Application`

## License

Mothership E-Commerce
Copyright (C) 2015 Jamie Freeman

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
