<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Location;
use App\Service\LocationService;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class FetchController extends AppController
{
    public function __construct(LocationRepository $locationRepository, LocationService $locationService) {
        $this->locationRepository = $locationRepository;
        $this->locationService = $locationService;

        $this->boardId = $_ENV['BOARD_ID'];
        $this->url = $_ENV['FETCH_BASE_URL'];
        $this->pinUrl = $_ENV['PIN_COUNT_URL'];
        $this->pinBaseUrl = $_ENV['PIN_BASE_URL'];
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];

        $this->count = 0;
        $this->maxLoopCount = false; // false = no max 
        $this->newPinCount = 0;

        $this->pinCount = 0;
        $this->pinCounter = 0;
        $this->newPins = '';
        $this->finished = '';
        $this->error = 'Without Error(s)';

        $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $this->pinUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);   
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);         
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            
            $response= curl_exec($ch);
            $pin_total = 0;
                if (preg_match('/"pin_count":(\d+)/', $response, $matches)) {
            $pin_total = intval($matches[1]);
            }

        $this->pinTotal = $pin_total;
    }
    
    #[Route('/fetch', name: 'app_fetch')]
    public function index(): Response {
        $file = 'pin.json';
        file_put_contents($file, '');
        $this->verifyImgFolder();
        $this->getFeed();
        return $this->redirect('admin');
    }

    #[Route('/check', name: 'app_check_count')]
    public function check(): Response {
        
        $existing = $this->locationRepository->findBySource('Pinterest');
        $existing_count = count($existing);
        $remaining = $this->pinTotal - $existing_count;
        $file = 'count.json';
        file_put_contents($file, $remaining);

        return $this->redirect('admin');
    }

    #[Route('/update', name: 'app_update')]
    public function update(): Response {
        $locations = $this->locationRepository->findAll();

        foreach($locations as $location) {
            if(!$location->getCountry()) $this->locationService->addCountry($location);
            if(!$location->getType()) $this->locationService->addType($location);
            $this->locationRepository->add($location);
        }

        $exportDate = './assets/update.json';
        $jsonData = [
            "last_updated" => date("d/m/Y H:i", time()),
        ];
        $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
        $fp = fopen($exportDate, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
    
        return $this->redirect('admin');
    }

    #[Route('/patch', name: 'app_patch')]
    public function patch(): Response {
        $dup = $this->locationRepository->findPidDuplicate();

        foreach($dup as $row) {
            $items = explode(',', $row['ids']);
            unset($items[0]);

            foreach($items as $item) {
                $loc = $this->locationRepository->find($item);
                $this->locationRepository->remove($loc);
            }
        }

        return $this->redirect('admin');
    }

    private function verifyImgFolder() {
        $publicDir = $this->getParameter('public_directory');

        if(file_exists($publicDir.$this->imgPath)) return;
        mkdir($publicDir.$this->imgPath, 0777, true);
    }

    private function getResource($option) {
        $data = urlencode(json_encode(['options' => $option]));
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->url.$data);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);   
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);         
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return $this->error(curl_error($ch));
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code == intval(200)) {
            $json = json_decode($response, true);
            $this->parseFeed($json);
        } else {
            $this->error("Ressource introuvable : " . $http_code);
        }
    }

    private function getFeed($bookmarks = false) {
        if($bookmarks === false) {
            $this->getResource(['board_id' => $this->boardId, 'page_size' => '25']);
        } else {
            $this->getResource(['board_id' => $this->boardId, 'page_size' => '25', 'bookmarks' => $bookmarks]);
        }
    }

    private function parseFeed($json) {
        $data = $json['resource_response']['data'];;
        foreach($data as $item) {
            if (isset($item['type']) && $item['type'] == 'pin') {
                $this->savePin($item);
            }
        }
        
        $bookmarks = $json['resource']['options']['bookmarks'];
        if($this->maxLoopCount !== false && ++$this->count === $this->maxLoopCount) {
            $this->done();
        }elseif ($bookmarks[0] == '-end-') {
            $this->done();
        } else {
            $this->getFeed($bookmarks);
        }
    }


    private function savePin($item) {      
        $publicDir = $this->getParameter('public_directory');

        $exist = $this->locationRepository->findByPid($item['id']) !== null;
        if(!$exist) {
            $location = new Location();

            $imgUrl = $item['images']['orig']['url'];
            $imgName = $this->locationService->generateImgUid().'.'.pathinfo($imgUrl)['extension'];
            copy($imgUrl, $publicDir.$this->imgPath.$imgName);
            
            preg_match('#(.*".{1}) (.*".{1}) (.*)#', $item['description'], $matches);
            if(isset($matches[1])) $location->setLat((float)$this->convertCoord($matches[1]));
            if(isset($matches[2])) $location->setLon((float)$this->convertCoord($matches[2]));
            if(isset($matches[3])) $location->setName(substr(str_replace('"', "''", $matches[3]), 0, 250));

            $location
                ->setSource('Pinterest')
                ->setPid((int)$item['id'])
                ->setUrl($this->pinBaseUrl.$item['id'])
                ->setImageDirect($this->imgPath.$imgName)
                ->setDescription(substr($item['description'], 0, 250));
            
            $this->locationService->addType($location);
            $this->locationService->addCountry($location);
            
            
            $this->locationRepository->add($location);
            $this->newPinCount++;

        $this->pinCount++;

    }

    $this->pinCounter++;
    $percentage = ($this->pinCounter / $this->pinTotal) * 100;
    $filename = 'pin.json';
    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $filename;
    $file = fopen($filePath, 'a');
    fwrite($file, PHP_EOL . $percentage );
    fclose($file);
}

    private function convertCoord($str) {
        preg_match('#([0-9]+)°([0-9]+)\'([0-9]+.[0-9])"([A-Z])#', $str, $matches);
        if(count($matches) === 5) {
            $pos = in_array($matches[4], ['N', 'E']) ? 1 : -1;
            return $pos*($matches[1]+$matches[2]/60+$matches[3]/3600);
        }
        return $str;
    }

    private function error($error) {
        $this->error = $error;
    }

    private function done() {
        $this->finished = 'Success';
        $this->newPins = $this->newPinCount;

        // Write finished data 
        $exportDate = './assets/export.json';
        $jsonData = [
            "last_fetched" => date("d/m/Y H:i", time()),
            "board" => $this->boardId,
            "finished" => $this->finished,
            "error" => $this->error,
            "total" => $this->pinCount,
            "newpins" => $this->newPins,
            "token" => rand() . "\n"
        ];
        $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT);
        $fp = fopen($exportDate, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
    }
}