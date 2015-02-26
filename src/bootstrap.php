<?php

ini_set('display_errors', 1);

use \MikeyMike\RfcDigestor\Command\Rfc;
use \MikeyMike\RfcDigestor\Command\Notify;
use \MikeyMike\RfcDigestor\Service\RfcService;
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

$app         = new Application('PHP RFC Digestor', '0.1.0');
$conf        = new Config(realpath(__DIR__ . '/../config.json'));
$storagePath = rtrim(realpath(sprintf('%s/%s', __DIR__, $conf->get('storagePath'))), '/');
$rfcService  = new RfcService($storagePath);

// Set config path for future commands
$conf->set('storagePath', $storagePath);

$app->addCommands(array(
    new Rfc\Digest($rfcService),
    new Rfc\Summary($rfcService),
    new Rfc\RfcList($rfcService),
//    new Notify\Rfc($rfcService),
//    new Notify\Summary($rfcService),
    new Notify\RfcList($conf, $rfcService),
));

return $app;
