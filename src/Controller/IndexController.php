<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
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
    public function dashboard(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $file = new Upload();
        $form = $this->createForm(UploadType::class, $file);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('filename')->getData();

            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $file->setExtension(pathinfo($brochureFile->getClientOriginalName(), PATHINFO_EXTENSION));
                $file->setSize(filesize($this->getParameter('uploads_directory') . '/' . $newFilename));
                $file->setOriginalFilename($brochureFile->getClientOriginalName());
                $file->setFilename($newFilename);
            }

            $file->setDate(new \DateTime());
            $em->persist($file);
            $em->flush();

            $this->addFlash('success', $file->getOriginalFilename() . ' a bien été enregistée dans ' . $file->getFolder());
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('index/dashboard.html.twig', [
            'form'=> $form
        ]);
    }
}
