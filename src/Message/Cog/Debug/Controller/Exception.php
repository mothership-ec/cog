<?php

namespace Message\Cog\Debug\Controller;

use Message\Cog\Controller\Controller;

use Message\Cog\Filesystem\File;
use Message\Cog\HTTP\Response;

class Exception extends Controller
{
	public function viewException($exception)
	{
		return $this->render('Message:Cog::Debug:exception', array(
			'exception'   => $exception,
			'statusTexts' => Response::$statusTexts,
		));
	}

	public function filePeek($file, $line, $offset = 3)
	{
		$file      = new File($file);
		$lines     = array();
		$startLine = $line - $offset;

		if ($file->isReadable()) {
			$file = $file->openFile();
			$file->seek($startLine);

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

		return $this->render('Message:Cog::Debug:file_peek', array(
			'lines'     => $lines,
			'startLine' => $startLine + 1,
			'highlight' => $line,
		));
	}
}