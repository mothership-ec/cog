<?php

namespace Message\Cog\ImageResize\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	const ROUTE_NAME = 'imageresize.cache';
	const SALT       = 'rCWj^/P8HXMKAru6xX4;(YyP7HTvZutzzEAqoxM9M&Ip+K{{Lot 7g*ZiGR@g>-@';

	public function registerServices($container)
	{
		$serviceContainer['imagine'] = function($c) {
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

		$serviceContainer['image.resize'] = $serviceContainer->share(function($c) {
			return new \Message\Cog\ImageResize\Resize(
				$c['imagine'],
				$c['routing.generator'],
				self::ROUTE_NAME,
				self::SALT
			);
		});
	}
}