<?php

namespace Message\Cog\Module\Templating\Twig\Extension;

use Message\Cog\Module\Loader;

class ModuleExists extends \Twig_Extension
{
    protected $_moduleLoader;

    public function __construct(Loader $loader)
    {
        $this->_moduleLoader = $loader;
    }
    public function getFunctions()
    {
        return array(
            'moduleExists' => new \Twig_Function_Method($this, 'checkModuleExists'),
        );
    }

    public function checkModuleExists($value)
    {
        return $this->_moduleLoader->exists($value);
    }

    public function getName()
    {
        return 'modulexists';
    }
}