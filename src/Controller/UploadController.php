<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Repository\UploadRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadController extends AppController
{

    #[Route('/upload', name: 'upload', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger, UploadRepository $uploadRepository)
    {
        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);
        $status = 'Waiting for data...';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $Filename */
            $Filename = $form->get('upload')->getData();
            if ($Filename) {
                $originalFilename = pathinfo($Filename->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$Filename->guessExtension();
                
                try {
                    $Filename->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $this->uploadRepository = $uploadRepository;
                $upload->setFilename($newFilename);
                $upload->setName($safeFilename);
                $upload->setDate(new \DateTime());
                $this->uploadRepository->save($upload, true);
                $status = 'Uploaded successfully';
            }
            return $this->renderForm('upload/index.html.twig', [
                'form' => $form,
                'status' => $status,
            ]);
        }
        return $this->renderForm('upload/index.html.twig', [
            'form' => $form,
            'status' => $status,
        ]);
    }
}