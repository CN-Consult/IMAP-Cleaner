<?php
/**
 *
 * @file
 * @version 2.11
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 * @license MIT
 */


namespace ImapCleaner\Commands;


use DateTime;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Cleans up old mails from the given Mailbox.
 */
class CleanupMailboxCommand extends BaseImapCommand
{
	protected static $defaultName = "mailbox:cleanup";

	function configure()
	{
		$this->setDescription("Cleans up old mails from the specified mailbox");
		parent::configure();
		$this->addArgument("mailbox",InputArgument::REQUIRED,"The name of the mailbox that should be cleaned up. The name should match with what mailbox:list outputs.");
		$this->addOption("age","a",InputOption::VALUE_REQUIRED,"The minimum age of mails that should be deleted.","1 year");
	}

	function execute(InputInterface $_input, OutputInterface $_output)
	{

		$email=$_input->getArgument("email");
		$server = $_input->getOption("server");
		$port = $_input->getOption("port");
		$password = $_input->getOption("password");
		$mailbox = $_input->getArgument("mailbox");


		$mailboxIdentifier = "{" . $server . ":" . $port . "/imap/ssl}$mailbox";
		$_output->write("Logging in... ");
		$imap = imap_open($mailboxIdentifier, $email, $password);
		if ($imap)
		{
			$_output->writeln("<info>Ok</info>");
			$now = time();
			$deleteBeforeDate = strtotime("- ".$_input->getOption("age"),$now);
			if ($deleteBeforeDate<$now)
			{
				$totalCount = imap_num_msg($imap);
				$oldMessages = imap_search($imap, "BEFORE ". date("d-M-Y",$deleteBeforeDate));
				if (is_array($oldMessages)) {
					$numDeleteMessages = count($oldMessages);
					if ($_output->isVerbose()) $_output->writeln("Found <info>$numDeleteMessages</info> old Messages in mailbox <info>$mailbox</info>!");

					$helper = $this->getHelper('question');
					$confirmation = new ConfirmationQuestion("Do you want to continue and delete <info>" . count($oldMessages) . "</info> of <info>$totalCount</info> mails in <info>$mailbox</info> that are older than <info>" . date("Y-m-d", $deleteBeforeDate) . "</info>?\n<fg=red>This action is destructive and cannot be undone!</fg=red> Are you sure? ");
					if ($helper->ask($_input, $_output, $confirmation)) {
						$_output->writeln("Deleting!");
						$progressBar = new ProgressBar($_output, $numDeleteMessages);
						$progressBar->setFormat("very_verbose");
						$numDeletedMessages = 0;
						$numDeletedSinceLastExpunge = 0;
						$batchSize = 100; //how many messages to delete at once

						$numDeletes = floor($numDeleteMessages / $batchSize);
						$leftOver = $numDeleteMessages % $batchSize;
						for ($i = 0; $i < $numDeletes; $i++) {
							$inMsgNo = $i * $batchSize + 1;
							$range = "$inMsgNo:" . ($inMsgNo + $batchSize - 1);
							imap_delete($imap, $range);
							$progressBar->advance($batchSize);
							$numDeletedMessages += $batchSize;
							$numDeletedSinceLastExpunge += $batchSize;
							if ($numDeletedSinceLastExpunge >1000)
							{
								$_output->write(" Expunging mails...");
								imap_expunge($imap);
								$numDeletedSinceLastExpunge=0;
							}
						}
						$inMsgNo = $i * $batchSize + 1;
						$range = "$inMsgNo:" . ($inMsgNo + $leftOver - 1);
						imap_delete($imap, $range);
						$numDeletedMessages += $leftOver;

						$progressBar->finish();
						$_output->write("\nExpunging marked mails... ");
						imap_expunge($imap); //expunge to really delete all marked mails
						$_output->writeln("<info>Ok</info>");
						imap_close($imap);
						$_output->writeln("Finished! Deleted <fg=red>$numDeletedMessages</fg=red> old Messages!");

						return 0;
					} else $_output->writeln("Ok, not doing anything!");
					return 0;
				}
				else $_output->writeln("Could not find any messages that are older than <info>" . date("Y-m-d", $deleteBeforeDate) . "</info>");
			}
			else $_output->writeln("Refusing to delete all messages in folder!");

		}
		else $_output->writeln("Failed connecting to $server:$port!");
		return 1;
	}
}
