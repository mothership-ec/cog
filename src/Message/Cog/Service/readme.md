# Service Component

This component is responsible for keeping track of service definitions to aid dependency injection and make unit testing easier.

## Container

The service container should always extend the `Message\Cog\Service\ContainerInterface` interface.

The default service container, `Message\Cog\Service\Container`, is an extension of [Pimple](http://pimple.sensiolabs.org/) (package name `pimple/pimple`). We have added a few methods to make it easier to use:

* `instance()` Singleton accessor.
* `get($id)` Static method to get a service.
* `getAll()` Returns a list of all defined services.

## ContainerAware

**TODO: write me when this bit is done**