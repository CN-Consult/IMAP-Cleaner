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


use ImapCleaner\DefaultParameterAdder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists the mailboxes in an IMAP account.
 * This is useful to get the names and paths of the Mailboxes that exist in a specified account
 */
class ListMailBoxesCommand extends BaseImapCommand
{

	protected static $defaultName = "mailbox:list";

	function configure()
	{
		$this->setDescription("Lists all mailboxes");
		parent::configure();
		$this->addOption("noCounts",null,InputOption::VALUE_NONE,"Skip counting the number of mails per mailbox");
	}

	function execute(InputInterface $_input, OutputInterface $_output)
	{
		$doCounts = true;

		$email=$_input->getArgument("email");
		$server = $_input->getOption("server");
		$port = $_input->getOption("port");
		$password = $_input->getOption("password");
		if ($_input->getOption("noCounts")) $doCounts = false;


		$mailboxIdentifier = "{" . $server . ":" . $port . "/imap/ssl}";
		$_output->write("Logging in... ");
		$imap = imap_open($mailboxIdentifier, $email, $password);
		if ($imap)
		{
			$_output->writeln("Ok");

			$_output->write("Fetching mailboxes ... ");
			$mailboxes=imap_getmailboxes($imap,$mailboxIdentifier,"*");
			$_output->writeln("Ok");
			$_output->writeln("These are your mailboxes:");
			foreach ($mailboxes as $mailbox)
			{
				//var_dump($mailbox);
				if (isset($mailbox->name))
				{
					$numEmails = "";
					if ($doCounts)
					{ //we should count, so use imap_reopen and num_msg to count the mails in the box
						$numEmails = " (Unknown)";
						if (imap_reopen($imap,$mailbox->name))
						{
							try {
								$numEmails = " (" . imap_num_msg($imap) . ")";
							}
							catch (Exception $e)
							{

							}
						}
					}
					$name = str_replace($mailboxIdentifier,"",$mailbox->name);
					$_output->writeln(" - $name".$numEmails);
				}
			}

			return 0;

		}
		else $_output->writeln("Failed connecting to $server:$port!");
		return 1;
	}
}
