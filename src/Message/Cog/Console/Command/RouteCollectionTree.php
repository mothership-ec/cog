<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Functions\Iterable;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RouteCollectionTree
 *
 * Provides the module:generate command.
 * Lists all loaded modules in the system.
 */
class RouteCollectionTree extends Command
{
	protected function configure()
	{
		$this
			->setName('route:collection:tree')
			->setDescription('Lists all loaded route collections in the system.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$collections = $this->get('routes');

		$parents = array();
		foreach ($collections as $collectionName => $collection) {
			$parents[$collectionName] = $collection->getParent();
		}

		$tree = Iterable::toTree($parents);

		$output->writeln('<info>Found '.count($parents).' registered route collections.</info>');
		$output->writeln('');
		$output->writeln('<bold>ROOT</bold>');

		$this->_printTree($output, $tree);

		$output->writeln('');
	}

	public function _printTree($output, $tree, $depth = 0)
	{
		$i = 0;
		$count = count($tree);

		foreach($tree as $key => $node) {
			$i++;
			$output->writeln(str_repeat("  ", $depth+1) . ($i == $count ? '└' : '├') . ' ' . $key);
			$this->_printTree($output, $node, $depth+1);
		}
	}
}
