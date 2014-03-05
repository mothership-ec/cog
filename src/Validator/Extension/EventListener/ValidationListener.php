<?php

namespace Message\Cog\Validator\Extension\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\Session;

use Message\Cog\Localisation\Translator;


/**
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidationListener implements SubscriberInterface
{
    protected $_errors = array();
    protected $_session;
    protected $_translator;

    public function __construct(Session $session, Translator $translator)
    {
        $this->_session    = $session;
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

        if($form->isRoot() && !$form->getConfig()->getOption('errors_with_fields')) {
            $this->_getErrors($form);

            foreach($this->_errors as $fieldErrors) {
                foreach($fieldErrors as $error) {
                    $this->_session->getFlashBag()->add('error', $error);
                }
            }
        }

        $event->setData($data);
    }

    protected function _getErrors(FormInterface $field)
    {
        $label = $this->_translator->trans($field->getConfig()->getOption('label')) ?: ucfirst($field->getName());
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
