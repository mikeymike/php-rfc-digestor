<?php


namespace MikeyMike\RfcDigestor\Command\Notify;

use Doctrine\Common\Persistence\ObjectRepository;
use MikeyMike\RfcDigestor\RfcNotifier;
use MikeyMike\RfcDigestor\Service\SlackService;
use Noodlehaus\Config;
use MikeyMike\RfcDigestor\Service\DiffService;
use MikeyMike\RfcDigestor\Service\RfcService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Rfc
 *
 * @package MikeyMike\RfcDigestor\Command
 * @author  Michael Woodward <mikeymike.mw@gmail.com>
 */
class Rfc extends Command
{
    /**
     * @var RfcNotifier
     */
    private $rfcNotifier;

    /**
     * @param RfcNotifier $rfcNotifier
     */
    public function __construct(RfcNotifier $rfcNotifier)
    {
        $this->rfcNotifier = $rfcNotifier;
        parent::__construct();
    }

    /**
     * Configure Command
     */
    public function configure()
    {
        $this
            ->setName('notify:rfc')
            ->setDescription('Get notifications of RFC changes')
            ->addArgument('rfc', InputArgument::REQUIRED, 'RFC Code e.g. scalar_type_hints');
    }

    /**
     * Execute Command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (posix_isatty(STDOUT)) {
            $output->writeln('<info>щ(ºДºщ) This command is pointless when not run on a cron</info>');
        }

        $rfcCode = $input->getArgument('rfc');

        try {
            $this->rfcNotifier->notify($rfcCode);
        } catch (\RuntimeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }
    }
}
