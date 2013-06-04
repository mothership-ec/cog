# Routing Component

A route maps a URL to a controller. Registering a route involves telling Cog that a url such as `/order/4214/refunds` should be handled by the `listRefunds()` method in  `Mothership\Core\Controller\Order` class.

## Anatomy

A route consists of 3 things:

- A URL
- A name
- A list of defaults
- A list of requirements

### URLs

A URL must start with a forward slash and not have any trailing slashes e.g `/blogs/list` or `/checkout/account/address`.

URLs can contain placeholders which act like parameters which can be passed to controllers and used in futher processing. Placeholders are defined using curly braces around a name.

The following are all examples of placeholders in URLs:

- `/order/{orderID}/return/{returnID}`
- `/blog/{postTitle}`
- `/order/view/{orderID}`
- `/blog_archive_{year}_{month}/tagged/{tagID}`

Taking the last URL as an example if you visited `/blog_archive_2012_10/tagged/cycling` you'd have three variables in your controller:

- `$year` => 2012
- `$month` => 10
- `$tagID` => cycling

By default placeholders are matched using a ungreedy regex which tries to match any character except for a forward slash. By default they are required so if you had a URL such as `/blog/{postTitle}`, visiting `/blog/` wouldnt cause the router to match anything. 

For more information on how to access placeholders in controllers, read the Controller component documentation.

### Names

Each route must have a unique name to identify it. The route names can be any arbritary string but we recommend using a name made up of the module and a suitable action. This ensures that if it needs to be referenced in other parts of the system the URL can be changed without breaking any links. 

An example of this could be a template tag which takes a route name and returns the URL for that route.

    <a href="{{ render_link('account.profile') }}">My account</a>
    
Becomes
    
    <a href="/account">My account</a>
    
In the future if the URL for the route was updated to `/my/account` we wouldnt have to update any templates.

### Requirements 

You can restrict the regular expression that placeholders are matched against using requirements. For example you might have a placeholder that is an ID and should only ever match against digits.

You can add requirements using the `setRequirement` method. It takes two arguments, the name of the placeholder and the regular expression you want to match against. In the following example, the `userID` placeholder is set to only match against digits.

	$router->add('user.view', '/view/user/{userID}', 'Message:Cog:ClassName#view')
		->setRequirement('userID', '\d+');
		
If all the placeholder requirements on a route are not met, then the route won't be matched. 

Some requirements have special meaning, this are prefixed with an underscore.

- `_scheme` - Sets the scheme that must be matched against e.g. `http` or `https`. Can be a regex like `http|https`. Defaults to any scheme.
- `_method` - The HTTP method that this route responds to. Can be a regex such as `GET|POST`. Defaults to any method.

### Defaults

By default all placeholders are required for a route to be matched. By setting a default for that placeholder, it becomes optional. If the placeholder is missing from the URL the default value will be set and passed in the controller.

Some defaults have special meaning, this are prefixed with an underscore.

- `_format` - The format responses can be returned in e.g. `xml` or `html`. Can be a regex such as `xml|http|json`. Defaults to any format.
- `_access` - Specifies if the route is internal or external.
- `_controller` - The controller and method which should be executed when this route is matched. Can be an absolute class name and path or a controller reference. e.g `Message\CMS\Controller\ClassName::viewMethod` or `Message:Cog:ClassName#view`.


## Registering routes

The `Router` class is responsible for taking a request and returning a route that matches it. Before this happens Route`s are added to the `CollectionManager` class.

`CollectionManager` allows you to create different groups of routes. Each group is represented using the `RouteCollection` class. `RouteCollection` is a decorator for Symfony's `RouteCollection` class.

`CollectionManager` uses an array access based method of setting up groups. If you 

	$manager = new CollectionManager(new ReferenceParser);

	// Add a route to the default collection
	$manager->add('user.view', '/view/user/{userID}', 'Message:Cog:ClassName#view');

	// Add a route to the cp collection.
	$manager['cp']->add('cp.stream', '/events/stream', 'Message:Cog:ClassName#view');

By default collections are mounted at '/', but you can choose to mount them with a different prefix using the `setPrefix()` method.

	$manager['blog']->add('blog.comments.view', '/comments', 'Message:Blog::Comments#view');
	$manager['blog']->setPrefix('/blog');

This will prefix all routes URLs in the `blog` collection with `/blog`. In the example above to access the `Message:Blog::Comments#view` controller you'd visit /blog/comments in your browser.
