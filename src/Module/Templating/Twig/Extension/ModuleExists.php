<?php

namespace Message\Cog\Module\Templating\Twig\Extension;

use Message\Cog\Module\Loader;

/**
 * Class ModuleExists
 * @package Message\Cog\Module\Templating\Twig\Extension
 */
class ModuleExists extends \Twig_Extension
{
	/**
	 * @var Loader
	 */
    protected $_moduleLoader;

    public function __construct(Loader $loader)
    {
        $this->_moduleLoader = $loader;
    }

	/**
	 * {@inheritDoc}
	 */
    public function getFunctions()
    {
		// Original function was `moduleExists`, but `module_exists` added for consistency with Twig function
		// naming conventions
        return [
            'moduleExists' => new \Twig_Function_Method($this, 'checkModuleExists'),
            'module_exists' => new \Twig_Function_Method($this, 'checkModuleExists'),
        ];
    }

	/**
	 * Check to see if a module exists. This accepts namespaces delimited by either a backslash (\) or a colon (:)
	 *
	 * @param $value
	 *
	 * @return bool
	 */
    public function checkModuleExists($value)
    {
		$value = str_replace(':', '\\', $value);

        return $this->_moduleLoader->exists($value);
    }

	/**
	 * {@inheritDoc}
	 */
    public function getName()
    {
        return 'modulexists';
    }
}