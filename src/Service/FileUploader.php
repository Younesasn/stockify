<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    public function __construct(private string $targetDirectory, private SluggerInterface $slugger)
    {
    }

    public function upload(User $user, UploadedFile $file): array
    {
        $extensionFile = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extensionFile;

        $dirname = $user->getDirectoryName();

        if(filesize($file) > 20000000) {
            throw new FileException('Votre fichier est trop lourd');
        }

        try {
            $file->move(
                $this->targetDirectory . '/' . $dirname,
                $newFilename
            );
        } catch (FileException $e) {
            throw new FileException('Une erreur est survenue lors de l\'upload de votre fichier');
        }

        $data = ['newFilename' => $newFilename, 'dirname' => $dirname, 'extension' => $extensionFile];

        return $data;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}