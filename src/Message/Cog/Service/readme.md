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

### Container Aware

Generally speaking, accessing the container directly within other code should be avoided. If a class needs access to a service, it should ideally be dependency injected using the `ContainerAwareInterface` interface.

As such, the only place in which the service container can be referenced directly (statically using `instance()`) is when passing the container into `setContainer()` on a class that implements `ContainerAwareInterface` when you don't already have access to an instance of the service container.

In this instance, the container should be accessed using the following rules:

* The fully-qualified class name for the service container should be added as a `use` statement to the top of the file.
* The service container should be assigned to the property name `_services`.