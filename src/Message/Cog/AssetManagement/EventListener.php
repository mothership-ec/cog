<?php

namespace Message\Cog\AssetManagement;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

use Message\Cog\Deploy\Event\Event as DeployEvent;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Assetic\Extension\Twig\TwigResource;

/**
 * Event listener for the asset management system.
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
			KernelEvents::RESPONSE => array(
				array('generateAssetsOnRequest'),
			),
			'cog.deploy.after.update_code' => array(
				array('generateAssetsOnDeploy'),
			)
		);
	}

	/**
	 * Generate the assets defined in the Twig view. This is only used when
	 * working locally.
	 *
	 * This only fires for the master request.
	 *
	 * @param FilterResponseEvent $response The event
	 */
	public function generateAssetsOnRequest(FilterResponseEvent $response)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $response->getRequestType()) {
			return;
		}

		if ('local' !== $this->_services['env']) {
			return;
		}

		$twigLoader = $this->_services['templating.twig.loader'];

		foreach ($twigLoader::$parsedPaths as $template) {
			$this->_services['asset.manager']->addResource(new TwigResource(
				new \Twig_Loader_Filesystem('/'),
				$template
			), 'twig');
		}

		$this->_services['asset.writer']->writeManagerAssets($this->_services['asset.manager']);
	}

	/**
	 * Dump and generate the assets on deploy.
	 *
	 * @param  DeployEvent $event
	 */
	public function generateAssetsOnDeploy(DeployEvent $event)
	{
		$event->executeCommand('asset:dump');
		$event->executeCommand('asset:generate');
	}
}