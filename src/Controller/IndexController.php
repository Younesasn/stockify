<?php

namespace App\Controller;

use App\Entity\Extension;
use App\Entity\Upload;
use App\Entity\User;
use App\Form\UploadType;
use App\Form\UserType;
use App\Repository\CategoryRepository;
use App\Repository\ExtensionRepository;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IndexController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {}

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }

    #[Route('/signup', name: 'signup')]
    public function signUp(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Vous êtes bien inscrit ! Connectez-vous pour accéder à votre espace.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('index/signup.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, UploadRepository $uploadRepository, ExtensionRepository $extensionRepository, CategoryRepository $categoryRepository): Response
    {
        $pictureCategory = $categoryRepository->findOneBy(['name' => 'Photos']);
        $pictures = $uploadRepository->findAllUploadWithCategory('Photos');

        $fileCategory = $categoryRepository->findOneBy(['name' => 'Fichiers']);
        $files = $uploadRepository->findAllUploadWithCategory('Fichiers');

        $videoCategory = $categoryRepository->findOneBy(['name' => 'Vidéos']);
        $videos = $uploadRepository->findAllUploadWithCategory('Vidéos');

        $audioCategory = $categoryRepository->findOneBy(['name' => 'Audios']);
        $audios = $uploadRepository->findAllUploadWithCategory('Audios');

        $uploads = $uploadRepository->findAll();

        $extension = new Extension();

        $file = new Upload();
        $form = $this->createForm(UploadType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('filename')->getData();

            if ($brochureFile) {
                $extensionFile = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_EXTENSION);
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extensionFile;

                try {
                    $brochureFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                $file->setExtension($extensionFile);
                $file->setSize(filesize($this->getParameter('uploads_directory') . '/' . $newFilename));
                $file->setOriginalFilename($brochureFile->getClientOriginalName());
                $file->setFilename($newFilename);
                $file->setDate(new \DateTime());

                $searchExtension = $extensionRepository->findOneByValue($extensionFile);

                if (empty($searchExtension)) {
                    // catégorie par défaut à implémenter
                    $extension->setValue($extensionFile);
                    $em->persist($extension);
                }

                $file->setCategory($searchExtension->getCategory());
                $em->persist($file);
                $em->flush();
            }

            $this->addFlash('success', $file->getOriginalFilename() . ' a bien été enregistée ! ' . $file->getFolder());
            return $this->redirectToRoute('dashboard', []);
        }

        return $this->render('index/dashboard.html.twig', [
            'form' => $form,
            'uploads' => $uploads,
            'files' => $files,
            'fileCategory' => $fileCategory,
            'pictures' => $pictures,
            'pictureCategory' => $pictureCategory,
            'videos' => $videos,
            'videoCategory' => $videoCategory,
            'audios' => $audios,
            'audioCategory' => $audioCategory,
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'delete')]
    public function delete(Upload $upload, EntityManagerInterface $em): Response
    {
        unlink('uploads/' . $upload->getFilename());
        $em->remove($upload);
        $em->flush();
        return $this->redirectToRoute('dashboard');
    }
}
