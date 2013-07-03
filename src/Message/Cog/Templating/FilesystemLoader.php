<?php

namespace Message\Cog\Templating;

use Symfony\Component\Templating\Loader\FilesystemLoader as SymfonyLoader;

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