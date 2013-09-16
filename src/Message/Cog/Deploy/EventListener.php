<?php

namespace Message\Cog\Deploy;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Deploy\Event\Event as DeployEvent;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * Deploy event listener.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(

		);
	}
}