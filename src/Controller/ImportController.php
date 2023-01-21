<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\UploadRepository;
use App\Service\LocationService;

class ImportController extends AppController
{
    public function __construct(LocationRepository $locationRepository, LocationService $locationService) {
        $this->locationRepository = $locationRepository;
        $this->locationService = $locationService;
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];
        $this->source = '';
    }

    #[Route('/import/{id}', name: 'app_import')]
    public function import($id, UploadRepository $uploadRepository): Response {
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
        
        preg_match('#(-?[0-9]+\.[0-9]+),(-?[0-9]+\.[0-9]+)#', (string)$el->Point->coordinates, $matches);
        preg_match('#<img src="([^"]*)"#', (string)$el->description, $matches2);

        $name = str_replace("\n", ' ', (string)$el->name);
        $name = str_replace('"', "'", $name);

        $location = new Location();
        $location
            ->setName($name)
            ->setDescription(strip_tags((string)$el->description))
            ->setLon($matches[1])
            ->setLat($matches[2])
            ->setSource($this->source)
        ;

        if(isset($matches2[1])) {
            $file = file_get_contents($matches2[1]);
            $mimeType = finfo_buffer(finfo_open(), $file, FILEINFO_MIME_TYPE);
            $ext = explode('/', $mimeType)[1];
            $imgName = $this->locationService->generateImgUid().'.'.$ext;
            file_put_contents($publicDir.$this->imgPath.$imgName, $file);

            $location->setImage($this->imgPath.$imgName);
        }

        $this->locationService->addType($location);
        $this->locationService->addCountry($location);

        $this->locationRepository->add($location);
    }
}
