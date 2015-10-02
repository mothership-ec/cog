<?php

namespace Message\Cog\AssetManagement;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;

/**
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class CssCoguleRewriteFilter extends BaseCssFilter
{

	public function filterLoad(AssetInterface $asset)
	{

	}

	public function filterDump(AssetInterface $asset)
	{
		$sourceBase = $asset->getSourceRoot();
		$sourcePath = $asset->getSourcePath();
		$targetPath = $asset->getTargetPath();

		if (null === $sourcePath || null === $targetPath || $sourcePath == $targetPath) {
			return;
		}

		$cogNamespace = $asset->cogNamespace;

		$content = $this->filterReferences($asset->getContent(), function($matches) use ($cogNamespace) {
			if (false !== strpos($matches['url'], '://') ||
				0 === strpos($matches['url'], '//') ||
				0 === strpos($matches['url'], 'data:') ||
				isset($matches['url'][0]) && '/' == $matches['url'][0]) {
				// absolute or protocol-relative or data uri
				return $matches[0];
			}

			$url = $matches['url'];
			while (0 === strpos($url, '../')) {
				$url = substr($url, 3);
			}

			$url = '../../cogules/' . $cogNamespace . '/' . $url;

			return str_replace($matches['url'], $url, $matches[0]);
		});

		$asset->setContent($content);
	}

}