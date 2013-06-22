<?php

namespace Message\Cog\AssetManagement;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

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
		return array(KernelEvents::RESPONSE => array(
			array('generateAssets'),
		));
	}

	/**
	 * Generate the assets defined in the Twig view.
	 *
	 * This only fires for the master request.
	 *
	 * @param FilterResponseEvent $response The event
	 *
	 * @todo Make this only happen when in the local environment
	 */
	public function generateAssets(FilterResponseEvent $response)
	{
		if (HttpKernelInterface::MASTER_REQUEST !== $response->getRequestType()) {
			return;
		}

		foreach ($this->_services['templating.twig.loader']->parsedPaths as $template) {
			$this->_services['asset.manager']->addResource(new TwigResource(
				new \Twig_Loader_Filesystem('/'),
				$template
			), 'twig');
		}

		$this->_services['asset.writer']->writeManagerAssets($this->_services['asset.manager']);
	}
}