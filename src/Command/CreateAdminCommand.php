<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Create an administrator account.')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Administrator email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = strtolower(trim((string) $input->getArgument('email')));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $io->error('Enter a valid email address.');

            return Command::INVALID;
        }

        if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]) instanceof User) {
            $io->error('An account with that email already exists.');

            return Command::FAILURE;
        }

        do {
            $password = (string) $io->askHidden('Administrator password (at least 12 characters)');
        } while (strlen($password) < 12);

        $user = new User($email, '', ['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Administrator account created for %s.', $email));

        return Command::SUCCESS;
    }
}
