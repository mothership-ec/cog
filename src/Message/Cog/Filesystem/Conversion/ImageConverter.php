<?php

namespace Message\Cog\Filesystem\Conversion;

use InvalidArgumentException;

use Knp\Snappy\Image;
use Message\Cog\Filesystem\File;

class ImageConverter extends AbstractConverter {

	/**
	 * {@inheritDoc}
	 */
	public function generate($path, $html)
	{
		$image = new Image;
		$image->setBinary($this->_getBinDir() . 'wkhtmltoimage-' . $this->_getBinaryType());

		foreach ($this->_options as $key => $value) {
			$image->setOption($key, $value);
		}

		$image->generateFromHTML($html, $path);

		return new File($path);
	}

}