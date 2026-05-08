<?php

declare(strict_types=1);

namespace App\Voucher\UI\Console;

use App\Voucher\Application\Reminder\VoucherReminderProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:voucher-reminders:process',
    description: 'Processes voucher claim and expiration reminders.',
)]
final class ProcessVoucherRemindersCommand extends Command
{
    public function __construct(
        private readonly VoucherReminderProcessor $voucherReminderProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sent = $this->voucherReminderProcessor->process();
        $output->writeln(sprintf('Voucher reminders processed. Sent: %d', $sent));

        return Command::SUCCESS;
    }
}
