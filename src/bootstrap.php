<?php

ini_set('display_errors', 1);

use \MikeyMike\RfcDigestor\Command;
use MikeyMike\RfcDigestor\Command\ActiveRfcVoteSummary;
use \MikeyMike\RfcDigestor\Command\Digest;
use \MikeyMike\RfcDigestor\Service\RfcBuilder;
use \Symfony\Component\DomCrawler\Crawler;
use \Symfony\Component\Console\Application;
use \Noodlehaus\Config;

switch (true) {
    case (file_exists(__DIR__ . '/../vendor/autoload.php')):
        // Installed standalone
        require __DIR__ . '/../vendor/autoload.php';
        break;
    case (file_exists(__DIR__ . '/../../../autoload.php')):
        // Installed as a Composer dependency
        require __DIR__ . '/../../../autoload.php';
        break;
    case (file_exists('vendor/autoload.php')):
        // As a Composer dependency, relative to CWD
        require 'vendor/autoload.php';
        break;
    default:
        throw new RuntimeException('Unable to locate Composer autoloader; please run "composer install".');
}

$app        = new Application('PHP RFC Digestor', '0.1.0');
$conf       = new Config(realpath(__DIR__ . '/../config.json'));
$rfcBuilder = new RfcBuilder(realpath(sprintf('%s/%s', __DIR__, $conf->get('storagePath'))));
$storagePath = realpath(sprintf('%s/%s', __DIR__, $conf->get('storagePath')));
$app->addCommands(array(
    new Digest($conf, $rfcBuilder),
    new Digest\Votes($conf, $rfcBuilder),
    new Digest\Details($conf, $rfcBuilder),
    new Digest\ChangeLog($conf, $rfcBuilder),
    new Command\Notify(),
    new Command\RfcList(),
    new ActiveRfcVoteSummary($rfcBuilder)
));

return $app;