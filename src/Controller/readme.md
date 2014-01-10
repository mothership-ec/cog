# Controller Component

This component deals with accessing and invoking controllers and includes a base controller that the application can use as desired to speed up controller development.

## Controllers

### What is a controller?

A controller is one of the following:

- A publicly accessible method on a class
- A standalone function
- An anonymous function

However, it is standard within Cog to primarily use a method on a class. Typically, that class will extend the "base controller class", `Message\Cog\Controller\Controller`. The base controller class does not **have** to be used, but it is recommended as it provides helpful methods and access to frequently used utilities and components, such as the service container.

It is standard practice for a module, or a particular section of a module to have a controller class that extends the base controller class, with a method for each controller.

### How controllers work

A controller method or function should always return an instance of `Message\Cog\HTTP\Response`. This can be built manually, or preferably via the `ResponseBuilder` class (detailed later in this document).

The base controller provides a handy method for using the response builder for rendering views, `render()`.

A controller on a controller class that extends the base controller class will typically look like this:

	namespace MyApp\Core\Controller;
	
	class Competitions extends \Message\Cog\Controller\Controller
	{
		public function latest()
		{
			// Run some code to find the latest competition and assign it to $competition
			
			return $this->render(
				'::latest', // Reference for the view to render
				array(
					'competition' => $competition,
					'customParam' => 'testing',
				)
			);
		}
	}

This will return a `HTTP\Response` instance based on the master request for the rendered view "latest" in the `MyApp\Core` module.

The parameters `competition` and `customParam` are passed to the view and are accessed by their names. See the readme for the Templating component for more information.

## Controller Resolver

### What does it do?

The `ControllerResolver` is responsible for invoking a controller for a given HTTP request. It also cleverly maps all parameters found in the request to arguments in the controller method. Consider the following example controller class & method:

	namespace MyApp\PasswordReset;
	
	class Controller extends \Message\Cog\Controller\Controller
	{
		public function request($userID, $attemptNumber = 1);
		public function reset($userID, $hash);
	}

And the following route defined in the `MyApp\PasswordReset` route bootstrap:

	$router->add('password_reset.request', '/user/password/request/{userID}/{attemptNumber}', '::Controller#request')
		->setOptional('attemptNumber');
		
	$router->add('password_reset.reset', '/user/password/reset/{userID}/{hash}', '::Controller#reset');		

The `ControllerResolver` will automatically match the route parameters to the controller arguments by name. An exception will be thrown if a non-optional argument is not included in the request.

For more information about how the `ControllerResolver` works, [read this section of the Symfony book](http://symfony.com/doc/2.0/book/controller.html#route-parameters-as-controller-arguments).

## Response Builder

The `ResponseBuilder` class is essentially the glue between a controller and a rendered view. It takes a reference for a view, some parameters and the HTTP request and uses them to render the view and return an instance of `HTTP\Response`.

It also includes some basic functionality to automatically generate responses based on the request format type. This is will only happen if the route applies to requests for this format.

For example, if you set up the following route:

	$router->add('test.myroute', '/my/page/{thing}', '::Controller:MyController#view')
		->setFormat('ANY');

With the following controller:

	public function view($thing)
	{
		return $this->render('::MyView', array(
			'myParam' => 'hello',
			'another' => array(1, 2, 3),
			'thing'   => $thing,
		));
	}

Where the `MyView` view does not exist in JSON (i.e. it only exists as `MyView.html.php`).

If the user requests the route as JSON using the `Accept` HTTP header, then the response builder will automatically build JSON for the parameters array and return is as a `HTTP\Response` instance. The JSON would look like this (assuming the "thing" parameter was passed as "test"):

	{"myParam":"hello","another":[1,2,3],"thing":"test"}