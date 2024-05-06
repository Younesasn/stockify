<?php

namespace App\Controller;

use App\Entity\Extension;
use App\Entity\Folder;
use App\Entity\Upload;
use App\Entity\User;
use App\Form\UploadType;
use App\Form\UserType;
use App\Repository\CategoryRepository;
use App\Repository\ExtensionRepository;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IndexController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // dd($this->getUser());

        return $this->render('index/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/signup', name: 'signup')]
    public function signUp(Request $request, EntityManagerInterface $em): Response
    {
        $user = new User();
        $filesystem = new Filesystem();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
            $user->setDirectoryName($user->getFirstName() . '_' . $user->getLastName() . '_' . uniqid());
            $filesystem->mkdir($this->getParameter('uploads_directory') . '/' . $user->getDirectoryName());
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Vous êtes bien inscrit !');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('index/signup.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request, SluggerInterface $slugger, EntityManagerInterface $em, UploadRepository $uploadRepository, ExtensionRepository $extensionRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupérations de la Category & des Upload du User
        $pictureCategory = $categoryRepository->findOneBy(['name' => 'Photos']);
        $pictures = $uploadRepository->findByUserWithCategory($this->getUser(), 'Photos');

        $fileCategory = $categoryRepository->findOneBy(['name' => 'Fichiers']);
        $files = $uploadRepository->findByUserWithCategory($this->getUser(), 'Fichiers');

        $videoCategory = $categoryRepository->findOneBy(['name' => 'Vidéos']);
        $videos = $uploadRepository->findByUserWithCategory($this->getUser(), 'Vidéos');

        $audioCategory = $categoryRepository->findOneBy(['name' => 'Audios']);
        $audios = $uploadRepository->findByUserWithCategory($this->getUser(), 'Audios');

        // Déclaration des Entity
        $extension = new Extension();
        $file = new Upload();
        $folder = new Folder();
        $filesystem = new Filesystem();
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

                    $dirname = $this->getUser()->getDirectoryName();
                    $brochureFile->move(
                        $this->getParameter('uploads_directory') . '/' . $dirname,
                        $newFilename
                    );

                    $folder->setName($dirname);
                    $folder->setDate(new \DateTime());
                    $file->setFolder($folder);
                    $em->persist($folder);

                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }

                $file->setUser($this->getUser());
                $file->setExtension($extensionFile);
                $file->setSize(filesize('uploads/' . $dirname . '/' . $newFilename));
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

            $this->addFlash('success', $file->getOriginalFilename() . ' a bien été enregistée ! ');
            return $this->redirectToRoute('dashboard', []);
        }

        return $this->render('index/dashboard.html.twig', [
            'user' => $this->getUser(),
            'form' => $form,
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
        unlink('uploads/' . $this->getUser()->getDirectoryName() . '/' . $upload->getFilename());
        $em->remove($upload);
        $em->flush();
        return $this->redirectToRoute('dashboard');
    }
}
