# Console component

The console component is split into two areas Tasks and Commands. This document mostly deals with Tasks as they are primarily what developers will be dealing with. For more information on the difference between a task and a command see the advanced section at the end of this document.

## What is a Task?

Tasks in Cog are units of work which run independently from a page request.  This might be via a cronjob at regular intervals, as part of a queue or message system, by a post-receive hook after a deploy, or just manually by a developer. In the past we managed these using adhoc scripts in `/utility_scripts` but the Task system provides a structured platform with helpful methods.

Examples tasks could be:

- Regenerating some cached content.
- Requesting an resource or time intensive external API.
- Processing items in a queue.
- Emailing the output of a report to a client
- Porting items from a CSV file into the database

## Running tasks

Tasks are run via Cog's console using the `task:run` command followed by the task name like so:

    $ bin/cog task:run cms:clean_tmp_data

## File structure

Tasks can sit anywhere within modules but for consistency we recommend keeping them in a `/Task` directory within a module e.g `Message/CMS/Task/SendOrderData.php`. The file's name must match the PHP class that is contained within it as per the PSR-0 spec.

## Class structure

Here's the bare minimum needed for a task that would be part of the `Cog\Core` module:

	namespace Message\CMS\Task;

	use Message\Cog\Console\Task\Task;

	class SendOrderData extends Task
	{
		public function process()
		{
			return 'The task has run';
		}
	}

Tasks **must** extend `Message\Cog\Console\Task`, they must also implement a `process()` method. This is what is called when the task is run. Whatever `process()` returns is output back to the user on the command line.

## Registering a task

Registering a task involves telling Cog that a task name like `core:send_order_data` maps to the PHP class `Message\CMS\Task\SendOrderData`. This process is done in the `registerTasks()` method of the module's bootstrap.

Here's an example for `Cog\Core`:

	namespace Message\CMS\Bootstrap;

	use Cog\Framework\Module\Bootstrap\TasksInterface,
		Message\CMS\Task;

	class Tasks implements TasksInterface
	{
		public function registerTasks($tasks)
		{
			$tasks->add(new Task\MakeTea('cms:make_tea'), 'Makes the tea for the whole team');
			$tasks->add(new Task\TwitterCache('cms:twitter_cache'), 'Download and cache a twitter stream');
			$tasks->add(new Task\CleanupTmpData('cms:cleanup_tmp_data'), 'Clears temporary data from the DB');
			$tasks->add(new Task\SendOrderData('cms:send_order_data'), 'Sends order data to 3rd parties');
		}
	}

The `$tasks` parameter which is pass into the `registerTasks()` method is an instance of `TaskCollection`. The `add()` method takes two parameters:

- First is an instance of your task class, in our example this is `Message\CMS\Task\SendOrderData`; the constructor has a single parameter which is the name of the task you want to use in the console when running the task e.g `core:send_order_data`.

- The second parameter is a description of what the task does. This is required and an `InvalidArgumentException` will be thrown if you omit it. Keep your descriptions accurate but concise; they will be useful in 12 months when you've written a custom task for a client and you've forgotten what it does.

## Putting it all together

Once you've created your task class and registered it in the bootstrap you should be able to run it by typing the following in your terminal:

    $ bin/cog task:run cms:clean_tmp_data

You should then see the following output (which is what gets returned from `SendOrderData`'s `process()` method):

    $ The task has run

You can see a list of all registered tasks by running:

    $ bin/cog task:list

If you can't see your task in the list then it's likely that you havent registered it properly.

## Scheduling tasks

The task system comes with a way of schduling tasks to run at certain, repeated points in time; the same as a cronjob but without the hassle of having to edit a crontab file. To do this you'll need to add a protected `configure()` method to your task class like so:

    namespace Message\CMS\Task;

	use Message\Cog\Console\Task;

	class SendOrderData extends Task
	{
	    protected function configure()
	    {
	        // Run every 5 minutes
	        $this->schedule('*/5 * * * *');
	    }

		public function process()
		{
			return 'The task has run';
		}
	}

The `configure()` method is automatically called when you register a task and allows you to give extra information about your task to the task runner.

In this case we call `$this->schedule('*/5 * * * *')` which tells the task runner to run this task every 5 minutes. That's all you need to do to get your task running at scheduled times. The `schedule()` method takes up to two parameters:

- The first must be a cron expression in a string which indicates how often to run the task. If you provide an invalid expression an exception will be thrown at registration time.
- Optionally you can provide a second parameter to indicate which environments this task will be scheduled to run in. For example if you only wanted a task to run on live and dev but not while you were developing locally you'd call `$this->schedule('*/5 * * * *', array('dev', 'live'))`. If you only want to schedule the task to run in a single environment you can provide a string rather than an array e.g `$this->schedule('*/5 * * * *', 'live')`.

**Always** leave a comment above the call to `schedule()` to inform other developers when the task is supposed to run e.g `3am on the 1st of every other month` as sometimes decipering cron expressions can be tricky.

## Automatically generating tasks

The console has a feature which lets you automatically generate a task and the associated PHP files based on a few basic questions. You can run this command by calling:

    $ bin/cog task:generate

## Working with the output of tasks

The task system has some built-in output handlers which let you do the following with a simple method call:

- Print the output of a task to the screen.
- Save the output of a task to a file
- Email the output of a task to a recipient.

### Printing to the screen

By default everything returned from your task will be printed to the screen. To prevent this from happening call `$this->output('print')->disable()` from within your task's `process()` method.

### Saving to file

To save the task's output to a file you should enable it with `$this->output('log')->enable()`.

This uses the `log.console` service which by default has a single stream handler to `cog://logs/console.log`. To change this you can either extend / overwrite the `log.console` service or access the logger using `$this->output('log')->getLogger()`.

### Emailing recipients

Lastly it's possible to email recipients the output of your task using `$this->output('mail')->enable()`.

The mail handler has an instance of `mail.message` that can be accessed with `$this->output('mail')->getMessage()`.

By default the recipient is set to `$services['cfg']->app->defaultContactEmail` and the sender is `$services['cfg']->app->defaultEmailFrom->email`.

### Example

    namespace Message\CMS\Task;

	use Message\Cog\Console\Task as BaseTask;

	class SendOrderData extends BaseTask
	{
	    protected function configure()
	    {
	        // Run every 5 minutes
	        $this->schedule('*/5 * * * *');
	    }

		public function process()
		{
		    // Email output to client
	        $this->output('mail')->enable()
	        $this->output('mail')->getMessage()
	            ->setTo('info@shirts.com')
	            ->setSubject('Order data');

	        // Append to log file
	        $this->output('log')->enable();
	        $this->output('log')->getLogger()
	            ->pushHandler($someHandler);

	        // Don't display output in console
	        $this->output('print')->disable();

			return 'The task has run';
		}
	}

## Styling output

You can use a simple tagging feature similar to HTML to add colour to the output of your tasks like so:

    namespace Message\CMS\Task;

	use Message\Cog\Console\Task;

	class SendOrderData extends Task
	{
		public function process()
		{
			// green text
            $output.= '<info>foo</info>';

            // yellow text
            $output.= '<comment>foo</comment>';

            // black text on a cyan background
            $output.= '<question>foo</question>';

            // white text on a red background
            $output.= '<error>foo</error>';

            // Make text bold
            $output.= '<bold>foo</bold>';

            // Tags can be combined - Make text bold and yellow
            $output.= '<question><bold>foo</bold></question>';

            return $output;
		}
	}

It's possible to add your own styles, this covered in the advanced section at the end of this guide.

## Streaming output

By default when you `echo` or `return` output within a task the output is buffered and not displayed to the user until the after the `process()` method has finished executing.

If you want to display progress to a user as it happens use the `write()` and `writeln()` methods like so:

    namespace Message\CMS\Task;

	use Message\Cog\Console\Task\Task;

	class SendOrderData extends Task
	{
		public function process()
		{
			$this->write('Downloading a big file…')
		    sleep(5);
		    $this->writeln('Done');
		}
	}

## Advanced

### Internals

Really a task is just an subclass of `Symfony\Component\Console\Command\Command`. It's `execute()` method has been proxied to `process()` so that it's signature can be simplified  for ease of use.

The `$input` and `$output` parameters found in a `execute()` method call have been added as protected properties at `$this->_input` and `$this->output` accordingly.  This means you can do things like adding new style formatters and user dialogs if you so wish from within your task.

The task itself runs in a special sandboxed instance of `Symfony\Component\Console\Application` so that help commands and the parent command can't be accessed or modified. This also allows it to capture any output so that it can be printed, emailed or logged.

### Task scheduling

A cronjob should run every minute which invokes the `task:run_scheduled` command that, in turn, finds tasks which have a valid cron expression which is due to run. It then launches seperate processes for each of the tasks so that they can run asynchronously of each other.

The line in the cronjob might look like this:

	*	*	*	*	*	/var/www/example.org/bin/cog --env=live task:run_scheduled

### Commands vs Tasks

A command is a top level part of the console, normally registered by something in `Message\Cog`. Examples of commands are `task:list`, `task:generate`, `task:run`, `module:list`.

A task is more specific piece of work usually related to a business-rule within a module. e.g `cms:order_post_process`, `ecommerce:send_oia_orders`, `cms:twitter_cache`. Tasks can only be run by the `task:run` command. By design it's not possible to do:

    $ bin/cog cms:order_post_process

You'll just get a command not found error. It's possible for modules to register commands as well as tasks but this is generally discouraged.