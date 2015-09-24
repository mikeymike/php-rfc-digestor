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
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use MikeyMike\RfcDigestor\Entity\SlackSubscriber;
use MikeyMike\RfcDigestor\Entity\Subscriber;
use MikeyMike\RfcDigestor\Notifier\SlackRfcNotifier;
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

$app['email-form'] = $app['form.factory']
    ->createNamedBuilder('email', 'form')
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

$app['slack-form'] = $app['form.factory']
    ->createNamedBuilder('slack', 'form')
    ->add('email', 'text', [
        'attr' => ['placeholder' => 'Your email'],
        'constraints' => new Assert\Email
    ])
    ->add('token', 'text', [
        'attr' => ['placeholder' => 'Your slack token'],
        'constraints' => new Assert\NotBlank
    ])
    ->add('channel', 'text', [
        'attr' => ['placeholder' => 'The slack channel to post to'],
        'constraints' => new Assert\NotBlank
    ])
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

$app->match('/', function (Request $request) use ($app) {
    $emailForm  = $app['email-form'];
    $slackForm  = $app['slack-form'];
    if ('POST' === $request->getMethod()) {

        if ($request->request->has('email')) {
            $emailForm->handleRequest($request);

            if ($emailForm->isValid()) {
                $email = $emailForm->getData()['email'];
                $subscriber = new Subscriber;
                $subscriber->setEmail($email);
                $subscriber->setUnsubscribeToken(bin2hex(openssl_random_pseudo_bytes(16)));
                $em = $app['orm.em'];
                $em->persist($subscriber);
                $em->flush();
                $app['session']->getFlashBag()->add('message', sprintf('You successfully subscribed with %s', $email));
                return $app->redirect('/');
            }
        }

        if ($request->request->has('slack')) {
            $slackForm->handleRequest($request);

            if ($slackForm->isValid()) {
                $email      = $slackForm->getData()['email'];
                $token      = $slackForm->getData()['token'];
                $channel    = $slackForm->getData()['channel'];
                $subscriber = new SlackSubscriber;
                $subscriber->setEmail($email);
                $subscriber->setToken($token);
                $subscriber->setChannel($channel);
                $em = $app['orm.em'];
                $em->persist($subscriber);
                $em->flush();
                $app['session']->getFlashBag()->add(
                    'message',
                    sprintf('You successfully subscribed to slack updates on channel %s', $channel)
                );
                return $app->redirect('/');
            }
        }
    }

    $numEmailSubscribers = (int) $app['orm.em']->createQueryBuilder()
        ->select('COUNT(s.id)')
        ->from('MikeyMike\RfcDigestor\Entity\Subscriber', 's')
        ->getQuery()
        ->getSingleScalarResult();

    $numSlackSubscribers = (int) $app['orm.em']->createQueryBuilder()
        ->select('COUNT(s.id)')
        ->from('MikeyMike\RfcDigestor\Entity\SlackSubscriber', 's')
        ->getQuery()
        ->getSingleScalarResult();

    return $app['twig']->render('index.twig',
        [
            'email_form' => $emailForm->createView(),
            'slack_form' => $slackForm->createView(),
            'email_subscribers' => $numEmailSubscribers,
            'slack_subscribers' => $numSlackSubscribers
        ]
    );

})->method('GET|POST');

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

$app['slack.api'] = function ($app) {
    $interactor = new CurlInteractor;
    $interactor->setResponseFactory(new SlackResponseFactory);
    return new Commander(null, $interactor);
};

$app['notifier.rfc.notifiers'] = function ($app) {
    return [
        new SlackRfcNotifier(
            $app['orm.em']->getRepository('MikeyMike\RfcDigestor\Entity\SlackSubscriber'),
            $app['slack.api']
        ),
        new \MikeyMike\RfcDigestor\Notifier\EmailRfcNotifier(
            $app['orm.em']->getRepository('MikeyMike\RfcDigestor\Entity\Subscriber'),
            $app['swift'],
            $app['twig'],
            $app['config']
        )
    ];
};

$app['notifier.rfc'] = function ($app) {
    return new \MikeyMike\RfcDigestor\RfcNotifier(
        $app['config'],
        $app['rfc.service'],
        $app['diff.service'],
        $app['notifier.rfc.notifiers']
    );
};

$app['cli'] = new Application('PHP RFC Digestor', '0.1.0');
$app['cli']->addCommands(array(
    new Rfc\Digest($app['rfc.service']),
    new Rfc\Summary($app['rfc.service']),
    new Rfc\RfcList($app['rfc.service']),
    new Notify\Rfc($app['notifier.rfc']),
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