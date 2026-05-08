<?php

declare(strict_types=1);

namespace App\Voucher\UI\Console;

use App\Voucher\Application\ScheduledSend\ScheduledVoucherSender;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vouchers:send-scheduled',
    description: 'Sends vouchers scheduled for delayed delivery.',
)]
final class SendScheduledVouchersCommand extends Command
{
    public function __construct(
        private readonly ScheduledVoucherSender $scheduledVoucherSender,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sent = $this->scheduledVoucherSender->process();

        $output->writeln(sprintf('Scheduled vouchers sent: %d', $sent));

        return Command::SUCCESS;
    }
}
