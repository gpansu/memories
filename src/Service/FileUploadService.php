<?php

// src/Service/FileUploadService.php
namespace App\Service;



use App\Entity\Account;
use App\Entity\Document;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    private $targetDirectory;

    public function __construct(String $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, Account $user, LoggerInterface $logger)
    {
        $extension = $file->guessExtension();
        $fileName = $user->getId()."_".md5(uniqid()).'.'.$extension;

        $newDocument = new Document();
        $newDocument->setAccount($user);
        $newDocument->setOriginalName($file->getClientOriginalName());
        $newDocument->setExtension($extension);
        $newDocument->setUploadDate(new \DateTime('now'));

        try {
            $file->move($this->getTargetDirectory($user), $fileName);
        } catch (FileException $e) {
            $logger->error($e);
        }
        $newDocument->setPath(realpath($this->getTargetDirectory($user)."/".$fileName));

        return $newDocument;
    }

    public function getTargetDirectory(Account $user)
    {
        $path = $this->targetDirectory.'/'.$user->getId();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }
}