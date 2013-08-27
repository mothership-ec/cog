<?php

namespace Message\Cog\Deploy;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Deploy\Event\Event as DeployEvent;

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
			'cog.deploy.before.create_symlink' => array(
				'beforeCreateSymlink'
			)
		);
	}

	/**
	 * Migrate the database before creating the symlink.
	 * 
	 * @param  DeployEvent $event
	 * @return void
	 */
	public function beforeCreateSymlink(DeployEvent $event)
	{
		$event->writeln('bin/cog migrate:run');
	}
}