<?php

namespace Message\Cog\Filesystem\Conversion;

use InvalidArgumentException;

use Knp\Snappy\Image;
use Message\Cog\Filesystem\File;

class ImageConverter extends Converter {

	public function generate($path, $html)
	{
		$image = new Image;
		$image->setBinary('/path/to/vendor/bin/wkhtmltoimage');

		$image->generateFromHTML($html, $path);

		return new File($path);
	}

}