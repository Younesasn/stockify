<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ExtensionRepository;
use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(UploadRepository $uploadRepository, ExtensionRepository $extensionRepository, CategoryRepository $categoryRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        $pictures = $uploadRepository->findByUserWithCategory($user, 'Photos');
        $files = $uploadRepository->findByUserWithCategory($user, 'Fichiers');
        $videos = $uploadRepository->findByUserWithCategory($user, 'Vidéos');
        $audios = $uploadRepository->findByUserWithCategory($user, 'Audios');

        return $this->json([
            'User' => [
                'Nom' => $user->getFirstName(),
                'Prénom' => $user->getLastName(),
                'Email' => $user->getEmail(),
                'Abonnement' => $user->getSubscription()->getName()
            ],
            "Photos" => $pictures,
            "Fichiers" => $files,
            "Vidéos" => $videos,
            "Audios" => $audios,
        ], context: ['groups' => 'upload:read']);
    }
}
