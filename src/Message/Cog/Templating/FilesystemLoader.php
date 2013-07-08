<?php

namespace Message\Cog\Templating;

use Symfony\Component\Templating\Loader\FilesystemLoader as SymfonyLoader;

/**
 * Class FilesystemLoader
 * @package Message\Cog\Templating
 *
 * Extension of Symfony's FilesystemLoader class to allow new pattern directories to be added
 * from other modules
 */
class FilesystemLoader extends SymfonyLoader
{
	public function addTemplatePathPattern($templatePathPattern)
	{
		$this->templatePathPatterns[] = $templatePathPattern;

		return $this;
	}

	public function addTemplatePathPatterns(array $templatePathPatterns)
	{
		$this->templatePathPatterns = array_merge($this->templatePathPatterns, $templatePathPatterns);
	}

	public function getTemplatePathPatterns()
	{
		return $this->templatePathPatterns;
	}
}