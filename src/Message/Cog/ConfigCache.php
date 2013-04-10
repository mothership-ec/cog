<?php

namespace Message\Cog;

/**
* ConfigCache
*
* Stores parsed config files in the cache. This speeds up initialisation of
* the config by about 10x.
*
* TODO: Remove the raw apc_ calls and use a wrapper
*/
class ConfigCache extends Config
{
	public function __construct($path, $environment)
	{
		if(1) {
//		if(!\apc_fetch('config.values') || \apc_fetch('config.hash') !== $this->hashDirectory($path)) {
			parent::__construct($path, $environment);
			//apc_store('config.values', $this->_configs);
			//apc_store('config.hash', $this->hashDirectory($path));
		} else {
			$this->_configs = apc_fetch('config.values');
		}
	}

	/**
	 * @param $path
	 * @return string
	 */
	public function hashDirectory($path)
	{
		$hash      = '';
		$directory = new \RecursiveDirectoryIterator($path);
		$iterator  = new \RecursiveIteratorIterator($directory);

		foreach($iterator as $file) {
			if($file->getExtension() === 'yml') {
				$hash.= md5($file->getMTime());
			}
		}

		return md5($hash);
	}
}