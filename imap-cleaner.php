#!/usr/bin/env php
<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 * @license MIT
 */


require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application("IMAP-Cleaner","1.0.1");

$application->add(new \ImapCleaner\Commands\ListMailBoxesCommand());
$application->add(new \ImapCleaner\Commands\CleanupMailboxCommand());

$application->run();