<?php

namespace Message\Cog\Validator\Extension;

use Symfony\Component\Form\AbstractExtension;
use Message\Cog\HTTP\Session;
use Message\Cog\Localisation\Translator;

/**
 * Extension adding Type\ValidationMessageTypeExtension
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidatorExtension extends AbstractExtension
{

    protected $_session;
    protected $_translator;

    public function __construct(Session $session, Translator $translator)
    {
        $this->_session    = $session;
        $this->_translator = $translator;
    }

    protected function loadTypeExtensions()
    {
        return array(
            new Type\ValidationMessageTypeExtension($this->_session, $this->_translator),
            new Type\RequiredTypeExtension,
        );
    }
}
