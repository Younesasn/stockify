<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Extension;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ExtensionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class IndexController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(FileUploader $fileUploader, Request $request, EntityManagerInterface $em, UploadRepository $uploadRepository, ExtensionRepository $extensionRepository, CategoryRepository $categoryRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        // Récupérations des Category & des Upload par Category du User
        $pictureCategory = $categoryRepository->findOneByName('Photos');
        $pictures = $uploadRepository->findByUserWithCategory($user, 'Photos');

        $fileCategory = $categoryRepository->findOneByName('Fichiers');
        $files = $uploadRepository->findByUserWithCategory($user, 'Fichiers');

        $videoCategory = $categoryRepository->findOneByName('Vidéos');
        $videos = $uploadRepository->findByUserWithCategory($user, 'Vidéos');

        $audioCategory = $categoryRepository->findOneByName('Audios');
        $audios = $uploadRepository->findByUserWithCategory($user, 'Audios');

        $otherCategory = $categoryRepository->findOneByName('Non-catégorisés');
        $others = $uploadRepository->findByUserWithCategory($user, 'Non-catégorisés');

        // Addition de toutes les tailles d'Upload du User + produit en croix
        $size = $uploadRepository->findSizeAllFiles($user);
        $userStorage = $user->getSubscription()->getStorage();
        $midStorage = $userStorage / 2;
        $pourcent = $size * 100 / $userStorage;

        // Déclaration des Entity
        $file = new Upload();
        $extension = new Extension();
        $form = $this->createForm(UploadType::class, $file);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('filename')->getData();

            if ($brochureFile) {

                try {
                    $data = $fileUploader->upload($user, $brochureFile);
                } catch(FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('dashboard', []);
                }

                [
                    'newFilename' => $newFilename,
                    'dirname' => $dirname,
                    'extension' => $extensionFile
                ] = $data;

                $file->setUser($user);
                $file->setExtension($extensionFile);
                $file->setSize(filesize('uploads/' . $dirname . '/' . $newFilename));
                $file->setOriginalFilename($brochureFile->getClientOriginalName());
                $file->setFilename($newFilename);
                $file->setDate(new \DateTime());

                $searchExtension = $extensionRepository->findOneByValue($extensionFile);
                
                // si l'extension n'existe pas en BDD
                if (empty($searchExtension)) {
                    // catégorie par défaut
                    $extension->setCategory($categoryRepository->findOneByName('Non-catégorisés'));
                    $extension->setValue($extensionFile);
                    $em->persist($extension);
                    $searchExtension = $extension;
                }

                $file->setCategory($searchExtension->getCategory());
                $em->persist($file);
                $em->flush();
            }

            $this->addFlash('success', $file->getOriginalFilename() . ' a bien été enregistée ! ');
            return $this->redirectToRoute('dashboard', []);
        }
        
        return $this->render('index/dashboard.html.twig', [
            'form' => $form,
            'files' => $files,
            'fileCategory' => $fileCategory,
            'pictures' => $pictures,
            'pictureCategory' => $pictureCategory,
            'videos' => $videos,
            'videoCategory' => $videoCategory,
            'audios' => $audios,
            'audioCategory' => $audioCategory,
            'others' => $others,
            'otherCategory' => $otherCategory,
            'size' => $size,
            'storage' => $userStorage,
            'pourcent' => $pourcent,
            'midStorage' => $midStorage
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'delete')]
    public function delete(Upload $upload, EntityManagerInterface $em): Response
    {
        $em->remove($upload);
        $em->flush();
        $this->addFlash('success', $upload->getOriginalFilename() . ' a bien été supprimé !');
        return $this->redirectToRoute('dashboard');
    }
}
