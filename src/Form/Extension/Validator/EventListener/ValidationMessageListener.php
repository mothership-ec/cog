<?php

namespace Message\Cog\Form\Extension\Validator\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\Session;

use Message\Cog\Localisation\Translator;

use Message\Cog\Form\Extension\Validator\Type\FormTypeErrorsWithFieldsExtension as TypeExtension;


/**
 * Event Subscriber that's called in the end of the submission process.
 * If the option 'errors_with_fields' is false, it collects all form-errors and
 * adds them as flash messages.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidationMessageListener implements SubscriberInterface
{
	protected $_errors = array();
	protected $_session;
	protected $_translator;

	public function __construct(Session $session, Translator $translator)
	{
		$this->_session	= $session;
		$this->_translator = $translator;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			FormEvents::POST_SUBMIT => ['postSubmit', -900],
		);
	}

	public function postSubmit(FormEvent $event)
	{
		$form = $event->getForm();
		$data = $event->getData();

		if($form->isRoot() && !$form->getConfig()->getOption(TypeExtension::ERRORS_WITH_FIELDS_OPTION)) {
			$this->_getErrors($form);

			foreach($this->_errors as $fieldErrors) {
				foreach($fieldErrors as $error) {
					$this->_session->getFlashBag()->add('error', $error);
				}
			}
		}
	}

	/**
	 * Gets all the errors in the current $field and adds them to $_errors, to
	 * get all errors in the form and its children recursively.
	 * Also adds display name to error-message.
	 *
	 * @param  FormInterface $field Field to get Errors from
	 */
	protected function _getErrors(FormInterface $field)
	{
		if($field->getConfig()->getOption('label')) {
			$label = $this->_translator->trans($field->getConfig()->getOption('label'));
		} else {
			$label = ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $field->getName()))));
		}

		$fieldErrors = array();

		foreach($field->getErrors() as $error) {
			// don't show label if error is on the whole form
			$fieldErrors[] = $field->isRoot() ? $error->getMessage() : $label . ': ' . $error->getMessage();
		}

		$this->_errors[] = $fieldErrors;
		foreach($field->all() as $child) {
			$this->_getErrors($child);
		}
	}
}
