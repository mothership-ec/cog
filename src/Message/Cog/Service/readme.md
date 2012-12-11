# Service Component

This component is responsible for keeping track of service definitions to aid dependency injection and make unit testing easier.

## Container

The service container should always extend the `Message\Cog\Service\ContainerInterface` interface.

The default service container, `Message\Cog\Service\Container`, is an extension of [Pimple](http://pimple.sensiolabs.org/) (package name `pimple/pimple`). We have added a few methods to make it easier to use:

* `instance()` Singleton accessor.
* `get($id)` Static method to get a service.
* `getAll()` Returns a list of all defined services.

### Hinting the container

When type hinting for the service container, always use the interface `Message\Cog\Service\ContainerInterface` instead of the container class itself. This allows for easy replacement of the container in future.

### Accessing the container

Generally speaking, accessing the container directly within other code should be avoided. If a class needs access to a service, it should ideally be dependency injected.

The only circumstance where it is acceptable to access the service container directly is if the only way to achieve what you need is by accessing the container directly. An example of this is in `Message\Cog\Templating\ViewNameParser` where the `request` service the current request) is accessed directly because it is the only way to get the current request from elsewhere in the system.

In these cases, the container should be accessed using the following rules:

* Inject the container in `__construct`, hinting `Message\Cog\Service\ContainerInterface`.
* If the above if not possible or practical:
	* Set a protected property `_services` on the object.
	* Assign the value of `::instance` on the container to the `_services` property.
	* Include the container in a `use` statement at the top of the file, so a codebase search can find all instances of statically calling the container.