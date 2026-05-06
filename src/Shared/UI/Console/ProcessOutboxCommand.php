<?php

declare(strict_types=1);

namespace App\Shared\UI\Console;

use App\Shared\Application\Outbox\OutboxProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(
    name: 'app:outbox:process',
    description: 'Processes pending outbox messages.',
)]
final class ProcessOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxProcessor $outboxProcessor,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'watch',
                null,
                InputOption::VALUE_NONE,
                'Continuously process pending outbox messages.',
            )
            ->addOption(
                'interval',
                null,
                InputOption::VALUE_REQUIRED,
                'Interval in seconds between outbox processing runs.',
                5,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            try {
                $this->outboxProcessor->processPending();
                $output->writeln('Outbox processed.');
                $this->logger->info('Outbox processed successfully.');
            } catch (Throwable $exception) {
                $output->writeln(sprintf(
                    '<error>Outbox processing failed: %s</error>',
                    $exception->getMessage(),
                ));

                $this->logger->error('Outbox processing failed.', [
                    'exception' => $exception,
                ]);

                if (!$input->getOption('watch')) {
                    return Command::FAILURE;
                }
            }

            if (!$input->getOption('watch')) {
                break;
            }

            sleep($this->getInterval($input));
        }

        return Command::SUCCESS;
    }

    private function getInterval(InputInterface $input): int
    {
        $interval = $input->getOption('interval');

        if (is_int($interval)) {
            return max(1, $interval);
        }

        if (is_string($interval) && ctype_digit($interval)) {
            return max(1, (int) $interval);
        }

        return 5;
    }
}
