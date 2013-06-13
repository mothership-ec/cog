<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EventList
 *
 * Provides the task:list command.
 * List all registered event listeners
 */
class EventList extends Command
{
	protected function configure()
	{
		$this
			->setName('event:list')
			->setDescription('List all registered event listeners.')
			->addArgument('search_term', InputArgument::OPTIONAL, 'Only return events that contain this term.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$term      = $input->getArgument('search_term');
		$listeners = $this->get('event.dispatcher')->getListeners();
		$events    = array();

		foreach($listeners as $eventName => $handlers) {
			foreach($handlers as $order => $listener) {
				if(is_array($listener)) {
					list($object, $methodName) = $listener;
					$priority = 0;
					$object   = get_class($object);

					// get the priority
					$objectEvents = $object::getSubscribedEvents();
					$eventMethods = $objectEvents[$eventName];

					if(is_array($eventMethods)) {
						foreach($eventMethods as $eventMethod) {
							if($eventMethod[0] == $methodName) {
								$priority = isset($eventMethod[1]) ? $eventMethod[1] : 0;
							}
						}
					}	
				} else {
					$reflection = new \ReflectionFunction($listener);
					$file        = new \Message\Cog\Filesystem\File($reflection->getFileName());


					$object     = $reflection->getNamespaceName().'\\'.$file->getFilenameWithoutExtension();
					$methodName = 'L:'.$reflection->getStartLine() . ' - L:'.$reflection->getEndLine();
					$priority   = '';
					
					var_dump($reflection->getFileName());
				}

				
				$events[] = array(
					$object,
					$methodName,
					$eventName,
					$priority,
					$order,
				);
			}
		}

		// Sort events by event name, then by order of execution.
		uasort($events, function($a, $b){
			if ($a[2] == $b[2]) {
				if ($a[4] == $b[4]) {
					return 0;
				}
				return ($a[4] < $b[4]) ? -1 : 1;
			}

			return ($a[2] < $b[2]) ? -1 : 1;
		});

		$output->writeln('<info>Found ' . count($events) . ' registered event listeners.</info>');
		$table = $this->getHelperSet()->get('table')
			->setHeaders(array('Class', 'Method', 'Event', 'Priority'));

		foreach($events as $event) {
			$table->addRow(array($event[0], $event[1], $event[2], $event[3]));
		}
		
		$table->render($output);
	}
}
