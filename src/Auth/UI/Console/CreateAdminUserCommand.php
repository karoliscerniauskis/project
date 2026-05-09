<?php

declare(strict_types=1);

namespace App\Auth\UI\Console;

use App\Auth\Domain\Security\UserPasswordHasher;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Creates an administrator user.',
)]
final class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasher $passwordHasher,
        private readonly UuidCreator $uuidCreator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email.')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Admin password.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email must be valid.');
        }

        $password = $input->getOption('password');

        if (!is_string($password)) {
            $helper = $this->getHelper('question');
            $question = new Question('Admin password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $password = $helper->ask($input, $output, $question);
        }

        if (!is_string($password) || $password === '') {
            throw new InvalidArgumentException('Password is required.');
        }

        $existingUser = $this->entityManager
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser instanceof UserRecord) {
            $output->writeln(sprintf('<error>User "%s" already exists.</error>', $email));

            return Command::FAILURE;
        }

        $user = new UserRecord(
            $this->uuidCreator->create(),
            $email,
            null,
            $this->passwordHasher->hashPassword($password),
            ['ROLE_ADMIN', 'ROLE_USER'],
            null,
            new DateTimeImmutable(),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Admin user "%s" created successfully.</info>', $email));

        return Command::SUCCESS;
    }
}
