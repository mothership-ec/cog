<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Services;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TaskGenerate
 *
 * Provides the task:generate command.
 * Generates a boilerplate task within a module.
 */
class TaskGenerate extends Command
{
	protected function configure()
	{
		$this
			->setName('task:generate')
			->setDescription('Generate a boilerplate task within a module.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');

		$module = $dialog->askAndValidate($output, '<question>Name of the module (e.g Fedex)</question>:', function($answer){
			if(!preg_match('/^[A-Za-z0-9\\\\_]*$/', $answer)) {
				throw new \InvalidArgumentException('Module names can only contain alphanumeric chars and \\.');
			}
			if(!Services::get('module.loader')->exists($answer)) {
				throw new \InvalidArgumentException('Module `'.$answer.'` does not exist.');
			}
			return $answer;
		});

		$classGuess = '';
		$name = $dialog->askAndValidate($output, '<question>Name of the task (e.g core:push_orders)</question>:', function($answer) use (&$classGuess) {
			if(!preg_match('/^([a-z0-9_]+?):([a-z][a-z0-9_:]*)$/', $answer, $matches)) {
				throw new \Exception('Task names must be in the format module:task_name');
			}
			foreach(explode('_', $matches[2]) as $part) {
				$classGuess.= ucfirst($part);
			}
			return $answer;
		});

		$class = $dialog->askAndValidate($output, '<question>Class name for the task (leave blank to use `'.$classGuess.'`)</question>:', function($answer){
			if(!preg_match('/^[A-Za-z0-9_]*$/', $answer)) {
				throw new \Exception('Class names can only contain alphanumeric chars and _');
			}
			return $answer;
		}, false, $classGuess);

		$desc = $dialog->askAndValidate($output, '<question>A short description explaining what it does</question>:', function($answer){
			$answer = trim($answer);
			if(empty($answer)) {
				throw new \Exception('A short description is required.');
			}
			return $answer;
		});

		$modulePath = Services::get('module.loader')->getPath($module);
		$taskPath   = $modulePath . 'Task/';
		$fileName   = $taskPath . $class . '.php';

		if(file_exists($fileName)) {
			$output->writeln('<error>File already exists in `' . $fileName . '`</error>');
			return;
		}

		if(!is_dir($taskPath)) {
			if(!is_writable($modulePath)) {
				$output->writeln('<error>Cannot write to `' . $modulePath . '`</error>');
				return;
			}
			mkdir($taskPath);
			chmod($taskPath, 0777);
		}

		$contents = "<?php

namespace ".$module."\Task;

use Message\Cog\Console\Task;

class ".$class." extends Task
{
	public function process()
	{
		return '<info>Successfully ran `".$name."`</info>';
	}
}";

		// Write to disk
		file_put_contents($fileName, $contents);

		// TODO: automatically register this task in the bootstrap. For
		// the moment we have to manually add it.
		$bootstrapCode = "\$tasks->add(new \\".$module."\\Task\\".$class."('".$name."'), '".addslashes($desc)."');";

		$formatter = $this->getHelperSet()->get('formatter');
		$output->writeln($formatter->formatBlock(array(
			'Success. The task has been generated.',
			'You now need to add the following line to the task bootstrap in `'.$module.'`:',
			'',
			'	<comment>'.$bootstrapCode.'</comment>',
			'',
		), 'info'));
	}
}
