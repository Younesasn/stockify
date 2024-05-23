<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Extension;
use App\Entity\Subscription;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Filesystem\Filesystem;

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
            'storage' => 100000000
        ],
        [
            'name' => 'Premium',
            'storage' => 200000000
        ],
    ];
    
    public function load(ObjectManager $manager): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove('public/uploads');

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
        $user->setPassword('user');
        $user->setSubscription($sub);
        $user->setToken($faker->sha256());
        $user->setDirectoryName($user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid());
        $filesystem->mkdir('public/uploads/' . $user->getDirectoryName());
        $manager->persist($user);

        $admin = new User();
        $admin->setFirstName($faker->firstName);
        $admin->setLastName($faker->lastName);
        $admin->setEmail('admin@admin.com');
        $admin->setPassword('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSubscription($sub);
        $admin->setToken($faker->sha256());
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
