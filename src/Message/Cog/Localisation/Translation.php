<?php

namespace Message\Cog\Localisation;


class Translation extends SymfonyTranslation
{
	/**
	 * Calculates the md5 checksum of a file.
	 *
	 * @return string 	the file's md5 checksum.
	 */
	public function getChecksum()
	{
		return md5_file($this->getRealPath());
	}
}