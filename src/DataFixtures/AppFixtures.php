<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Extension;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const CATEGORIES = [
        [
            'name' => 'Photos',
            'icon' => 'fa-regular fa-images'
        ],
        [
            'name' => 'Vidéos',
            'icon' => 'fa-solid fa-film'
        ],
        [
            'name' => 'Fichiers',
            'icon' => 'fa-regular fa-file'
        ],
        [
            'name' => 'Audios',
            'icon' => 'fa-regular fa-circle-play'
        ],
        [
            'name' => 'Non-catégorisés',
            'icon' => 'fa-solid fa-question'
        ],
    ];

    public const EXTENSIONS = [
        'Photos' => [
            'jpeg',
            'jpg',
            'png',
            'gif',
            'apng',
            'avif',
            'svg',
            'webp'
        ],
        'Vidéos' => [
            'avi',
            'flv',
            'mov',
            'mp4'
        ],
        'Fichiers' => [
            'doc',
            'docx',
            'md',
            'odt',
            'pdf',
            'ppt',
            'pptx',
            'txt',
            'xls',
            'xlsx',
            'dll',
            'ini',
            'tmp',
            'css',
            'html',
            'js',
            'php',
            'sql',
            'xml',
            'yaml',
            'pages'
        ],
        'Audios' => [
            'flac',
            'mp3',
            'aac',
            'wma',
            'wav',
            'wave'
        ],
        'Non-catégorisés' => []
    ];

    public const SUBSCRIPTIONS = [
        [
            'name' => 'Free',
            'storage' => 1000000000
        ],
        [
            'name' => 'Premium',
            'storage' => 2000000000
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $filesystem = new Filesystem();
        $faker = \Faker\Factory::create();

        foreach (self::SUBSCRIPTIONS as $subscription) {
            $sub = new Subscription();
            $sub->setName($subscription['name']);
            $sub->setStorage($subscription['storage']);
            $manager->persist($sub);
        }

        $user = new User();
        $user->setFirstName($faker->firstName);
        $user->setLastName($faker->lastName);
        $user->setEmail('user@user.com');
        $user->setPassword($this->hasher->hashPassword($user, 'user'));
        $user->setRoles(['ROLE_USER']);
        $user->setSubscription($sub);
        $user->setDirectoryName($user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid());
        $filesystem->mkdir('public/uploads/' . $user->getDirectoryName());
        $manager->persist($user);

        $admin = new User();
        $admin->setFirstName($faker->firstName);
        $admin->setLastName($faker->lastName);
        $admin->setEmail('admin@admin.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSubscription($sub);
        $admin->setDirectoryName($admin->getFirstName() . '_' . $admin->getLastName() . '_' . uniqid());
        $filesystem->mkdir('public/uploads/' . $admin->getDirectoryName());
        $manager->persist($admin);

        foreach (self::CATEGORIES as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setIcon($categoryData['icon']);

            foreach (self::EXTENSIONS[$categoryData['name']] as $extension) {
                $ext = new Extension();
                $ext->setValue($extension);
                $ext->setCategory($category);
                $this->addReference('extension_' . $extension, $ext);
                $manager->persist($ext);
            }

            $manager->persist($category);
        }

        $manager->flush();
    }
}
