<?php

namespace App\Command;

use App\Service\MessageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-360-webhook',
    description: 'Creates or updates the 360 webhook endpoint',
)]
class Make360WebhookCommand extends Command
{


    public function __construct(
        private MessageService $messageService,

    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'You webhook url - probably https://www.yourdomain.com/hook-endpoint')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {

            $url = $input->getArgument('url');

            if(!filter_var($url, FILTER_VALIDATE_URL)){
                $io->error('This is not a valid url!');
                return Command::FAILURE;
            }

            $this->messageService->makeWebhook($url);

            $hook = $this->messageService->getWebhook();

            $io->success('Current wehhook is: '.$hook->url);

            return Command::SUCCESS;
        }
        catch (\Exception $exception){

            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

    }
}
