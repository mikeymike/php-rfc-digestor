<?php

ini_set('display_errors', 1);

use \MikeyMike\RfcDigestor\RfcBuilder;
use \MikeyMike\RfcDigestor\Command\Rfc;
use \MikeyMike\RfcDigestor\Command\Notify;
use \MikeyMike\RfcDigestor\Service\RfcService;
use \MikeyMike\RfcDigestor\Service\DiffService;
use \MikeyMike\RfcDigestor\Command\Test;
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

$app  = new Application('PHP RFC Digestor', '0.1.0');

// Get config files
// Likely not platform agnostic
$userConfigFile    = sprintf('%s/.rfcdigester.json', realpath($_ENV['HOME']));
$defaultConfigFile = sprintf('%s/../config.json', realpath(__DIR__));

// Load configs and get storage path
$conf        = new Config([$defaultConfigFile, $userConfigFile]);
$storagePath = realpath(sprintf('%s/%s', __DIR__, $conf->get('storagePath')));

// Set config path for future commands
$conf->set('storagePath', $storagePath);

// Build dependencies
$rfcBuilder  = new RfcBuilder($storagePath);
$rfcService  = new RfcService($rfcBuilder);
$diffService = new DiffService();

$transport = new Swift_SmtpTransport();
$transport->setHost($conf->get('smtp.host'));
$transport->setPort($conf->get('smtp.port'));
$transport->setUsername($conf->get('smtp.username'));
$transport->setPassword($conf->get('smtp.password'));
$transport->setEncryption($conf->get('smtp.security'));
$mailer = new Swift_Mailer($transport);

$app->addCommands(array(
    new Rfc\Digest($rfcService),
    new Rfc\Summary($rfcService),
    new Rfc\RfcList($rfcService),
//    new Notify\Rfc($rfcService),
//    new Notify\Summary($rfcService),
    new Notify\RfcList($conf, $rfcService, $diffService, $mailer),
    new Test\Email($conf, $mailer)
));

return $app;
