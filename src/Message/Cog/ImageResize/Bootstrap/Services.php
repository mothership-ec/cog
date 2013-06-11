<?php

namespace Message\Cog\ImageResize\Bootstrap;

use Message\Cog\ImageResize\Resize\TwigExtension;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	const ROUTE_NAME = 'imageresize.cache';
	const SALT       = 'rCWj^/P8HXMKAru6xX4;(YyP7HTvZutzzEAqoxM9M&Ip+K{{Lot 7g*ZiGR@g>-@';

	public function registerServices($container)
	{
		$container['imagine'] = function($c) {
			if(extension_loaded('imagick')) {
				return new \Imagine\Imagick\Imagine();
			}

			if(extension_loaded('gmagick')) {
				return new \Imagine\Gmagick\Imagine(); 
			}

			if(extension_loaded('gd')) {
				return new \Imagine\Gd\Imagine(); 
			}

			throw new \Exception('No image processing libraries available for Imagine.');
		};

		$container['image.resize'] = $container->share(function($c) {
			$resize = new \Message\Cog\ImageResize\Resize(
				$c['imagine'],
				$c['routing.generator'],
				Services::ROUTE_NAME,
				Services::SALT
			);
			$resize->setDefaultQuality(90);

			return $resize;
		});
	}
}