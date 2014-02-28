<?php

namespace Message\Cog\Validator\Extension;

use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Validator\ValidatorInterface;
use Message\Cog\HTTP\Session;

/**
 * Extension adding Type\ValidationMessageTypeExtension
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ValidationMessageExtension extends AbstractExtension
{

    protected $_session;

    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    protected function loadTypeExtensions()
    {
        return array(
            new Type\ValidationMessageTypeExtension($this->_session),
        );
    }
}
