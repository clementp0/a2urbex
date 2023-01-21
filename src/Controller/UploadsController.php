<?php

namespace App\Controller;

use App\Entity\Uploads;
use App\Form\UploadsType;
use App\Repository\UploadsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadsController extends AbstractController
{

    #[Route('/upload', name: 'upload', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger, UploadsRepository $uploadsRepository)
    {
        $uploads = new Uploads();
        $form = $this->createForm(UploadsType::class, $uploads);
        $form->handleRequest($request);
        $status = 'Waiting for data...';
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $Filename */
            $Filename = $form->get('uploads')->getData();
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
                $this->uploadsRepository = $uploadsRepository;
                $uploads->setFilename($newFilename);
                $uploads->setName($safeFilename);
                $uploads->setDate(new \DateTime());
                $this->uploadsRepository->save($uploads, true);
                $status = 'Uploaded successfully';
            }
            return $this->renderForm('uploads/index.html.twig', [
                'form' => $form,
                'status' => $status,
            ]);
        }
        return $this->renderForm('uploads/index.html.twig', [
            'form' => $form,
            'status' => $status,
        ]);
    }
}