# Event Component

This component provides event functionality, implementing the [Observer](http://en.wikipedia.org/wiki/Observer_pattern) pattern. It is just a wrapper around Symfony's [EventDispatcher component](http://symfony.com/doc/2.0/components/event_dispatcher/introduction.html).

## Events

An event is an instance of the `Event` class (or a subclass of it). This often needs no information attached to it, however it is more useful when you extend it and dependency inject information that you wish to pass to the event listeners.

## Event Names

Event names should adhere to the following rules:

* Must be lowercase
* Must use a period as a namespace separator
* Should, in most cases, be appended with a namespace for the component name or Cog module name (e.g. `cms.page.edit`, `commerce.order.new`, `http.request`)
* Should never be in past tense (e.g. `cms.page.edited`)

## Event Dispatcher

The `EventDispatcher` is responsible for dispatching events, and also storing all registered event listeners and subscribers.

## Dispatching

Dispatching events from the `EventDispatcher` is as easy as:

	$dispatcher->dispatch($eventName, $eventClass);

Classes that dispatch events should either be `Service\ContainerAware` or, preferrable, have the `EventDispatcher` dependency injected.

## Event Listeners

An event listener can be any callable. The first and only parameter passed to the callable is the event object. They are registered on the `EventDispatcher` as follows:

	$dispatcher->addListener($eventName, function($event) {
		// do something
	});
	
	$dispatcher->addListener($eventName, array('ClassName', 'methodName'));

### Event Subscribers

Event subscribers are classes that have event listener methods, and the class itself is aware of which events it's listening to. Event subscribers must implement `EventSubscriberInterface`.

The `getSubscribedEvents()` method should return an associative array where the keys are the event names, and the values are an array of method names & priorities. See [the Symfony documentation](http://symfony.com/doc/2.0/components/event_dispatcher/introduction.html#using-event-subscribers) for full details.

## Custom Event Classes

You can even allow registered event listeners to overwrite or change values set on the `Event`. Here's an example `Event` class that enables this:

	class MyEvent extends \Message\Cog\Event\Event
	{
		protected $_value;
		
		public function setValue($value)
		{
			$this->_value = $value;
		}
		
		public function getValue()
		{
			return $this->_value;
		}
	}

This event would likely be dispatched similarly to this:

	$event = new MyEvent;
	$value = 'my value';
	$event->setValue($value);
	$services['event.dispatcher']->dispatch('something.happened', $event);
	
	$newValue = $event->getValue();
	
And a listener can now change the value on the event, in turn changing what is defined as `$newValue` like so:

	$services['event.dispatcher']->addListener('something.happened', function($event) {
		$event->setValue('no, it's MY value!')
	});

## Testing

In the `Test` namespace, there is an implementation of the dispatcher, `FauxDispatcher` which is useful when writing unit tests. It can easily report whether certain events have been dispatched and whether certain listeners have been registered.