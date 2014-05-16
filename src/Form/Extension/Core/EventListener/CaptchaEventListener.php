<?php

namespace Message\Cog\Form\Extension\Core\EventListener;

use Symfony\Component\Form;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\Session;
use Message\Cog\Form\Extension\Core\Type\CaptchaType;

class CaptchaEventListener implements SubscriberInterface
{
	protected $_session;

	public function __construct(Session $session)
	{
		$this->_session = $session;
	}

	public static function getSubscribedEvents()
	{
		return array(
			Form\FormEvents::POST_SUBMIT => ['postSubmit', -900],
		);
	}

	public function postSubmit(Form\FormEvent $event)
	{
		if (!$this->_isCorrect($event->getData())) {
			$event->getForm()->addError(
				new Form\FormError('Captcha answer is incorrect')
			);
		}

		$this->_session->remove(CaptchaType::SESSION_NAME);
	}

	private function _isCorrect($data)
	{
		$answer  = md5(strtolower($data));
		$captcha = $this->_session->get(CaptchaType::SESSION_NAME);

		return in_array($answer, $captcha['answer']);
	}
}