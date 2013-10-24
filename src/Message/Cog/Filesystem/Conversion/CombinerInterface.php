<?php

namespace Message\Cog\Filesystem\Conversion;

interface CombinerInterface {

	/**
	 * Combines multiple documents into one document.
	 *
	 * @param  ... \Message\Cog\Filesystem\File
	 * @return \Message\Cog\Filesystem\File The combined file.
	 */
	public function combine();

}