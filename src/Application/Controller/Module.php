<?php

namespace Message\Cog\Application\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Message\Cog\Controller\Controller;
use Message\Cog\HTTP\Response;
use Message\Cog\Console\Command\AssetDump;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Sam Trangmar-Keates
 *
 * Router for cogules. This maps paths given as 'cogules/Cog:Module/path/to/resource.jpg' to the
 * file they are stored as in the FileSystem.
 *
 * It also checks 'cogules/Cog:Module/path/to/resource.jpg' for BC as this is how they were previously
 * stored.
 */
class Module extends Controller
{
	public function getFile($fileRef)
	{
		$givenPath = 'cog://public/cogules/' . $fileRef;
		$bangPath  = 'cog://public/cogules' . str_replace(':', AssetDump::MODULE_SEPARATOR, $fileRef);

		try {
			$file = new File($bangPath);
		} catch (FileNotFoundException $e) {
			try {
				$file = new File($givenPath);
			} catch (FileNotFoundException $e) {
				return new Response('File not found', 404);
			}
		}

		return new Response(readfile($file), 200, ['Content-Type' => $file->getMimeType() ?: 'text/plain']);
	}
}