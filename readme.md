# Cog

## What is Cog?

Cog is Message's private internal PHP5 framework. It's very powerful but also lightweight and helps us create large-scale web applications with ease and confidence.

## How do I set up a new Cog project?

@TODO write me

## What services does Cog define?

Cog defines the following services on the service container. Don't overwrite any of these in your application unless you want to replace the functionality of this service with your own class.

* `class.loader` This is the Composer autoloader class which is based on Symfony's autoloader.
* `http.request.master` This is the `Message\Cog\HTTP\Request` instance for the current master request.