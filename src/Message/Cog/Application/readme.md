# Application Component

This component is aware of high-level information about the application and is responsible for loading the application.

## Cog Bootstraps

Each Cog module has bootstraps for event listeners; services; routes and tasks. Cog itself also has bootstraps for many of these, primarily event listeners and services.

The bootstraps for Cog live in the Application component under the `Bootstrap` namespace. A list the services defined by Cog can be found in [the main readme file](../readme.md).

## Environment

The `Environment` class is responsible for determining the environment in which the application is running in, and translates this into useful instructions for how the application should behave.

An environment is made up of a name and a context. The name can be one of the following:

* **local** Developers machine
* **test** When test suite is running
* **dev** A test site on a client server
* **staging** Deployed code ready to go live
* **live** Live public facing site

Bottled water
: $ 1.25
: $ 1.55 (Large)

## Application Loader

### Contexts