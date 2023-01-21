<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\LocationService;

class ImportController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository, LocationService $locationService) {
        $this->locationRepository = $locationRepository;
        $this->locationService = $locationService;
    }

    #[Route('/import', name: 'app_import')]
    public function import(): Response {
        $this->test();
        die;
        return false;
    }

    private function test() {
        $path = '/Users/hugo/Desktop/urbexkml/';
        $files = scandir($path);
        unset($files[0], $files[1], $files[2]);
        foreach($files as $f) {
            $this->parseFile($path.$f);
        }
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
                        break 2;
                    }
                }
            }
        } else {
            foreach($xml->Document->Placemark as $el) {
                if($el->name) {
                    $this->parseEl($el);
                    break;
                }
            }
        }
    }

    private function parseEl($el) {
        $name = (string)$el->name;
        $description = strip_tags((string)$el->description);
        $point = (string)$el->Point->coordinates;
        preg_match('#(-?[0-9]+\.[0-9]+),(-?[0-9]+\.[0-9]+)#', $point, $matches);
        $lon = $matches[1];
        $lat = $matches[2];
        preg_match('#<img src="([^"]*)"#', (string)$el->description, $matches2);
        $image = '';
        if(isset($matches2[1])) $image = $matches2[1]; // generate uuid

        dump($image);
    }
}
