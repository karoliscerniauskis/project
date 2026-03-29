<?php

declare(strict_types=1);

namespace App\Shared\UI\Console;

use App\Shared\Application\Outbox\OutboxProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:outbox:process',
    description: 'Processes pending outbox messages.',
)]
final class ProcessOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxProcessor $outboxProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outboxProcessor->processPending();
        $output->writeln('Outbox processed.');

        return Command::SUCCESS;
    }
}
