<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Location;
use App\Repository\LocationRepository;

class FetchController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;

        $this->boardId = $_ENV['BOARD_ID'];
        $this->url = $_ENV['FETCH_BASE_URL'];
        $this->pinBaseUrl = $_ENV['PIN_BASE_URL'];

        $this->count = 0;
        $this->maxLoopCount = 1; // false = no max 
        $this->newPinCount = 0;

        $this->pinCount = 0;
        $this->newPins = '';
        $this->finished = '';
        $this->error = 'Without Error(s)';
    }
    
    #[Route('/fetch', name: 'app_fetch')]
    public function index(): Response
    {
        $this->getFeed();
    
        return $this->redirect('admin');
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
        $data = $json['resource_response']['data'];
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
        $exist = $this->locationRepository->findByPid($item['id']) !== null;
        if(!$exist) {
            $location = new Location();
            
            preg_match('#(.*".{1}) (.*".{1}) (.*)#', $item['description'], $matches);
            if(isset($matches[1])) $location->setLon($this->convertCoord($matches[1]));
            if(isset($matches[2])) $location->setLat($this->convertCoord($matches[2]));
            if(isset($matches[3])) $location->setName($matches[3]);

            $location
                ->setPid((int)$item['id'])
                ->setDescription($item['description'])
                ->setUrl($this->pinBaseUrl.$item['id'])
                ->setImage($item['images']['orig']['url'])
            ;
            
            $this->locationRepository->add($location);
            $this->newPinCount++;
        }


        $this->pinCount++;
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
        $export_date = './assets/export.json';
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
        $fp = fopen($export_date, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
    }
}