# Routing Component

A route maps a URL to a controller. Registering a route involves telling Cog that a url such as `/order/4214/refunds` should be handled by the `listRefunds()` method in  `Mothership\Core\Controller\Order` class.

## Anatomy

A route consists of 3 things:

- A URL
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


## An example route bootstrap



