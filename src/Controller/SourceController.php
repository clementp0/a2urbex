<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Repository\UploadRepository;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\LocationService;


class SourceController extends AppController
{
    public function __construct(LocationRepository $locationRepository, LocationService $locationService) {
        $this->locationRepository = $locationRepository;
        $this->locationService = $locationService;
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];
        $this->source = '';
    }

    #[Route('/source/upload', name: 'source_upload', methods: ['GET', 'POST'])]
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
                } catch (FileException $e) {}

                $this->uploadRepository = $uploadRepository;
                $upload->setFilename($newFilename);
                $upload->setName($safeFilename);
                $upload->setDate(new \DateTime());
                $this->uploadRepository->save($upload, true);
                $status = 'Uploaded successfully';
            }
            
        }
        return $this->renderForm('source/index.html.twig', [
            'form' => $form,
            'status' => $status,
        ]);
    }

    #[Route('/source/{id}/delete', name: 'source_delete', methods: ['GET'])]
    public function delete($id, ManagerRegistry $doctrine, Request $request, UploadRepository $uploadRepository): Response
    {
        $upload = $uploadRepository->find($id);
        $publicDir = $this->getParameter('public_directory');

        if($upload && $upload->getName()) {
            $removeSources = $this->locationRepository->findBySource($upload->getName());
            $entityManager = $doctrine->getManager();
            foreach ($removeSources as $removeSource) {
                $entityManager->remove($removeSource['loc']);

                $image = $removeSource['loc']->getImage();
                if(strlen($image) > 4 && file_exists($publicDir.$image)) {
                    unlink($publicDir.$image);
                }
            }

            $entityManager->flush();
        }
        return $this->redirect('/admin');
    }

    #[Route('/source/{id}/run', name: 'source_run')]
    public function run($id, UploadRepository $uploadRepository): Response {
        $upload = $uploadRepository->find($id);
        $uploadsDir = $this->getParameter('uploads_directory');
        $this->source = $upload->getName();
        $upload->setDone(1);
        
        $this->parseFile($uploadsDir.$upload->getFilename());

        return $this->redirect('/admin');
    }

    private function parseFile($f) {
        $file = file_get_contents($f);
        $xml = simplexml_load_string($file, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);;

        $name = (string)$xml->Document->name;
        if(!$name) $name = pathinfo($f)['filename'];

        if(isset($xml->Document->Folder)) {
            foreach($xml->Document->Folder as $item) {
                foreach($item as $el) {
                    if($el->name) {
                        $this->parseEl($el);
                    }
                }
            }
        } else {
            foreach($xml->Document->Placemark as $el) {
                if($el->name) {
                    $this->parseEl($el);
                }
            }
        }
    }

    private function parseEl($el) {
        $publicDir = $this->getParameter('public_directory');
        
        preg_match('#(-?[0-9]+(\.[0-9]+)?),(-?[0-9]+(\.[0-9]+)?)#', (string)$el->Point->coordinates, $matches);
        preg_match('#<img src="([^"]*)"#', (string)$el->description, $matches2);

        $name = str_replace("\n", ' ', (string)$el->name);
        $name = str_replace("\t", '', (string)$name);
        $name = str_replace('"', "'", $name);

        if(empty($matches)) return false;

        $location = new Location();
        $location
            ->setName($name)
            ->setDescription(strip_tags((string)$el->description))
            ->setLon($matches[1])
            ->setLat($matches[3])
            ->setSource($this->source)
        ;

        if(isset($matches2[1])) {
            $file = @file_get_contents($matches2[1]);

            if($file !== false) {
                $mimeType = finfo_buffer(finfo_open(), $file, FILEINFO_MIME_TYPE);
                $ext = explode('/', $mimeType)[1];
                $imgName = $this->locationService->generateImgUid().'.'.$ext;
                file_put_contents($publicDir.$this->imgPath.$imgName, $file);
    
                $location->setImage($this->imgPath.$imgName);
            }
        }

        $this->locationService->addType($location);
        $this->locationService->addCountry($location);

        $this->locationRepository->add($location);
    }
}
