<?php

namespace App\Controller;

use App\Entity\Extension;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Repository\CategoryRepository;
use App\Repository\ExtensionRepository;
use App\Repository\UploadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
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
