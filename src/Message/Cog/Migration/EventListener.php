<?php

namespace Message\Cog\AssetManagement;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

use Message\Cog\Deploy\Event\Event as DeployEvent;

/**
 * Event listener for migrations.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'cog.deploy.before.create_symlink' => array(
				array('runMigrations')
			)
		);
	}

	/**
	 * Run the migrations on deploy.
	 *
	 * @param DeployEvent $event
	 */
	public function runMigrations(DeployEvent $event)
	{
		$event->executeCommand('migrate:run');
	}
}