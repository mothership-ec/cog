<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AssetDump
 *
 * Provides the asset:dump command.
 * Move module assets to public folder.
 */
class Mailer extends Command
{
	const USE_SYMLINKS = 'symlink';

	protected function configure()
	{
		$this
			->setName('mailer')
			->setDescription('Test Mailer.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$mail = $this->get('mail.message');

		$mail->setTo('joe@message.co.uk');
		$mail->setFrom('test@message.co.uk');
		$mail->setView('UniformWares:CMS::PageType:home');

		print_r($mail);
	}
}
