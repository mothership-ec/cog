<?php

namespace Message\Cog\Debug\Controller;

use Message\Cog\Controller\Controller;

use Message\Cog\Filesystem\File;

class Exception extends Controller
{
	public function viewException($exception)
	{
		return $this->render('Message:Cog:Debug::exception', array(
			'exception' => $exception,
		));
	}

	public function filePeek($file, $line, $offset = 2)
	{
		$file  = new File($file);
		$lines = array();

		if ($file->isReadable()) {
			$file = $file->openFile();
			$file->seek($line - $offset);

			// Start a loop for the number of lines to fetch
			for ($i = 0; $i <= (($offset * 2) + 1); $i++) {
				// Break the loop if we're at EOF
				if (!$file->valid()) {
					break;
				}
				$lines[$file->key()] = $file->current();
				$file->next();
			}
		}

		return $this->render('Message:Cog:Debug::file_peek', array(
			'lines' => $lines,
		));
	}
}