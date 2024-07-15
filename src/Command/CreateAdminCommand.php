<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user in the database',
)]
class CreateAdminCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private SubscriptionRepository $subscriptionRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $faker = Factory::create('fr_FR');
        $filesystem = new Filesystem();
        
        $sub = $this->subscriptionRepository->findOneByName('Premium');
        
        $user = new User();
        $user
            ->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setEmail($email)
            ->setPassword($password)
            ->setSubscription($sub)
            ->setRoles(['ROLE_ADMIN']);
            $user->setToken($faker->sha256());
            $user->setDirectoryName($user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid());
            $filesystem->mkdir('public/uploads/' . $user->getDirectoryName());

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Admin user successfully created');

        return Command::SUCCESS;
    }
}
