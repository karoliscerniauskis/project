<?php

declare(strict_types=1);

namespace App\Auth\UI\Console;

use App\Auth\Application\EmailBreach\EmailBreachCheckProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:email-breach-checks:process',
    description: 'Checks opted-in user emails against breach data.',
)]
final class ProcessEmailBreachChecksCommand extends Command
{
    public function __construct(
        private readonly EmailBreachCheckProcessor $emailBreachCheckProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $checked = $this->emailBreachCheckProcessor->process();
        $output->writeln(sprintf('Email breach checks processed: %d', $checked));

        return Command::SUCCESS;
    }
}
