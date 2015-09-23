<?php

ini_set('display_errors', 1);

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

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use MikeyMike\RfcDigestor\Entity\Subscriber;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use \MikeyMike\RfcDigestor\RfcBuilder;
use \MikeyMike\RfcDigestor\Command\Rfc;
use \MikeyMike\RfcDigestor\Command\Notify;
use \MikeyMike\RfcDigestor\Service\RfcService;
use \MikeyMike\RfcDigestor\Service\DiffService;
use \MikeyMike\RfcDigestor\Command\Test;
use \Symfony\Component\Console\Application;
use \Noodlehaus\Config;
use Openbuildings\Swiftmailer\CssInlinerPlugin;

$app = new Silex\Application();
$app['debug'] = true;
$app['config'] = function ($app) {
    // Get config files
    // Likely not platform agnostic
    $configs = [
        sprintf('%s/../config.json', realpath(__DIR__))
    ];

    // Unix or Windows home path
    $homePath = strtolower(substr(PHP_OS, 0, 3)) === 'win'
        ? getenv('USERPROFILE')
        : getenv('HOME');

    $userConfigFile = sprintf('%s/.rfcdigestor.json', $homePath);
    if (file_exists($userConfigFile)) {
        $configs[] = $userConfigFile;
    }
    $config = new Config($configs);

    // Load configs and get storage path
    $storagePath  = realpath(sprintf('%s/%s', __DIR__, $config->get('storagePath')));
    $templatePath = realpath(sprintf('%s/%s', __DIR__, $config->get('templatePath')));

    // Set config paths for future commands
    $config->set('storagePath', $storagePath);
    $config->set('templatePath', $templatePath);
    return $config;
};

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $app['config']->get('templatePath'),
));

$app['subscribe-form'] = $app['form.factory']
    ->createBuilder('form')
    ->add('email', 'text', [
        'attr' => ['placeholder' => 'Your email'],
        'constraints' => [
            new Assert\Email,
            new Assert\Callback(function ($email, ExecutionContextInterface $context) use ($app) {
                $repo = $app['orm.em']->getRepository('MikeyMike\RfcDigestor\Entity\Subscriber');
                if ($repo->findBy(['email' => $email])) {
                    $context->addViolation("Email already used");
                }
            })
        ]
    ])
    ->add('subscribe', 'submit', ['attr' => ['class' => 'btn-default']])
    ->getForm();

$app->register(new DoctrineServiceProvider, array(
    "db.options" => array(
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/../data/app.db'
    ),
));

$app->register(new DoctrineOrmServiceProvider, [
    "orm.proxies_dir" => "/path/to/proxies",
    "orm.em.options" => [
        "mappings" => [
            // Using actual filesystem paths
            [
                "type" => "annotation",
                "namespace" => 'MikeyMike\RfcDigestor\Entity',
                "path" => __DIR__ . "/../src/Entity",
            ],
        ],
    ],
]);

$app->get('/', function () use ($app) {
    $form = $app['subscribe-form'];
    return $app['twig']->render('index.twig',
        ['form' => $form->createView()]
    );
});

$app->post('/', function (Request $request) use ($app) {
    $form = $form = $app['subscribe-form'];
    $form->handleRequest($request);

    if ($form->isValid()) {
        $email = $form->getData()['email'];
        $subscriber = new Subscriber;
        $subscriber->setEmail($email);
        $subscriber->setUnsubscribeToken(bin2hex(openssl_random_pseudo_bytes(16)));
        $em = $app['orm.em'];
        $em->persist($subscriber);
        $em->flush();
        $app['session']->getFlashBag()->add('message', sprintf('You successfully subscribed with %s', $email));
        return $app->redirect('/');
    }

    return $app['twig']->render('index.twig',
        ['form' => $form->createView()]
    );
});

$app->get('/unsubscribe/{token}', function ($token)  use ($app) {
    $repo = $app['orm.em']->getRepository('MikeyMike\RfcDigestor\Entity\Subscriber');

    if ($subscriber = $repo->findOneBy(['unsubscribeToken' => $token])) {
        $em = $app['orm.em'];
        $em->remove($subscriber);
        $em->flush();
        $app['session']->getFlashBag()->add('message', sprintf('You successfully unsubscribed!'));
    }

    return $app->redirect('/');
});

$app['swift'] = function ($app) {
    $conf = $app['config'];
    $transport = new Swift_SmtpTransport();
    $transport->setHost($conf->get('smtp.host'));
    $transport->setPort($conf->get('smtp.port'));
    $transport->setUsername($conf->get('smtp.username'));
    $transport->setPassword($conf->get('smtp.password'));
    $transport->setEncryption($conf->get('smtp.security'));
    $mailer = new Swift_Mailer($transport);
    $mailer->registerPLugin(new CssInlinerPlugin());
    return $mailer;
};

$app['rfc.builder'] = function ($app) {
    return new RfcBuilder($app['config']->get('storagePath'));
};

$app['rfc.service'] = function ($app) {
    return new RfcService($app['rfc.builder'], $app['config']->get('rfcUrl'));
};

$app['diff.service'] = function ($app) {
    return new DiffService;
};

$app['cli'] = new Application('PHP RFC Digestor', '0.1.0');
$app['cli']->addCommands(array(
    new Rfc\Digest($app['rfc.service']),
    new Rfc\Summary($app['rfc.service']),
    new Rfc\RfcList($app['rfc.service']),
    new Notify\Rfc(
        $app['config'],
        $app['rfc.service'],
        $app['diff.service'],
        $app['swift'],
        $app['twig'],
        $app['orm.em']->getRepository('MikeyMike\RfcDigestor\Entity\Subscriber')),
    new Notify\Voting($app['config'], $app['rfc.service']),
    new Notify\RfcList(
        $app['config'],
        $app['rfc.service'],
        $app['diff.service'],
        $app['swift'],
        $app['twig']
    ),
    new Test\Email($app['config'], $app['swift'], $app['twig'])
));

return $app;