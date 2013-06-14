<?php

namespace Message\Cog\Form;

use Message\Cog\Event\SubscriberInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Class EventListener
 * @package Message\Cog\Form
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * @todo can probably be deleted as form helper is registered in form wrapper
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(
			'modules.load.success' => array(
				array('setupFormHelper'),
			)
		);
	}

	public function setupFormHelper()
	{
//		$this->_services['templating.php.engine']->addHelpers(array(
//			$this->_services['form.helper.php'],
//			$this->_services['form.helper.twig']
//		));

	}
}