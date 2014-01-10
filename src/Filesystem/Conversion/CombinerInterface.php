<?php

namespace Message\Cog\Filesystem\Conversion;

/**
 * Combines multiple files into one.
 *
 * @usage
 *     $converter = $this->get('filesystem.conversion.pdf');
 *     $file = $converter->combine('combined.pdf', array(
 *         $converter->setView('::view-1')->save('view-1.pdf'),
 *         $converter->setView('::view-2')->save('view-2.pdf'),
 *         $converter->setView('::view-3')->save('view-3.pdf'),
 *     ));
 */
interface CombinerInterface {

	/**
	 * Combines multiple documents into one document.
	 *
	 * @param  string    $path Path to save the combined file.
	 * @param  ...[File] $files
	 * @return \Message\Cog\Filesystem\File The combined file.
	 */
	public function combine($path, array $files = null);

}