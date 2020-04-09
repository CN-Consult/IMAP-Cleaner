<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 * @license MIT
 */


namespace ImapCleaner\Commands;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Base Imap command that adds all imap-specific options / arguments that are necessary for all commands.
 * It also automatically completes missing options interactively.
 * It adds the following options:
 * - server
 * -
 * It also adds the following argument:
 * - email
 *
 * To use it simply inherit from this command, implement configure as usual to add you description and additional
 * options/arguments. Simply don't forget to call parent::configure() so that the parent options arguments are added.
 * Then you can implement your execute() and use all available options.
 */
class BaseImapCommand extends \Symfony\Component\Console\Command\Command
{
	function configure()
	{
		parent::configure();
		$this->addOption("server","s",InputOption::VALUE_REQUIRED,"The server to connect to");
		$this->addOption("port","p",InputOption::VALUE_REQUIRED,"The port to connect to",993);
		$this->addOption("password",null,InputOption::VALUE_REQUIRED,"The password to use for authentication");

		$this->addArgument("email",InputArgument::REQUIRED);
	}

	function interact(InputInterface $_input, OutputInterface $_output)
	{
		$requiredOptions = ["server","password"];
		$requiredArguments = ["email"];
		$helper = $this->getHelper('question');


		foreach ($requiredArguments as $requiredArgument)
		{
			$value = $_input->getArgument($requiredArgument);
			if (!$value)
			{
				$question = new Question("Please enter the value <info>$requiredArgument</info>:", false);

				$askedValue=$helper->ask($_input, $_output, $question);
				if (!$askedValue)
				{
					throw new \Exception("Cannot continue without $requiredArgument, aborting!");
				}
				else $_input->setArgument($requiredArgument,$askedValue);
			}
		}

		foreach ($requiredOptions as $requiredOption)
		{
			$value = $_input->getOption($requiredOption);
			if (!$value)
			{
				$question = new Question("Please enter the value <info>$requiredOption</info>:", false);
				if ($requiredOption=="password") $question->setHidden(true);

				$askedValue=$helper->ask($_input, $_output, $question);
				if (!$askedValue)
				{
					throw new \Exception("Cannot continue without $requiredOption, aborting!");
				}
				else $_input->setOption($requiredOption,$askedValue);
			}
		}
	}
}
