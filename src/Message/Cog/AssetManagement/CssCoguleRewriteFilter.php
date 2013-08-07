<?php namespace Message\Cog\AssetManagement;

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
		de($asset);
		$sourceBase = $asset->getSourceRoot();
        $sourcePath = $asset->getSourcePath();
        $targetPath = $asset->getTargetPath();

        de($sourceBase, $sourcePath, $targetPath);

        if (null === $sourcePath || null === $targetPath || $sourcePath == $targetPath) {
            return;
        }

        $content = $this->filterReferences($asset->getContent(), function($matches) {
        	if (false !== strpos($matches['url'], '://') || 0 === strpos($matches['url'], '//') || 0 === strpos($matches['url'], 'data:')) {
                // absolute or protocol-relative or data uri
                return $matches[0];
            }

            if (isset($matches['url'][0]) && '/' == $matches['url'][0]) {
                // root relative
                return str_replace($matches['url'], $host.$matches['url'], $matches[0]);
            }

            d($matches);

        	// return str_replace($matches['url'], )
        });
	}

}