<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FetchController extends AbstractController
{
    public function __construct() {
        $this->PINS = [];
        $this->boardId = $_ENV['BOARD_ID'];
        $this->url = $_ENV['FETCH_BASE_URL'];
        $this->count = 0;
        $this->maxLoopCount = 2; // false = no max 
    }

    private function error($error) {
        echo $error;
        die;
    }

    private function getResource($option) {
        $data = urlencode(json_encode(['options' => $option]));
        //var_dump($this->url.$data);
    
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
        
        if (curl_errno($ch)) return $this->error(curl_error($ch));
        
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
                $this->PINS[] = [
                    'id' => $item['id'],
                    'description' => $item['description'],
                    'url' => "https://www.pinterest.com/pin/".$item['id'],
                    'image' => $item['images']['orig']['url']
                ];
            }
    
        }
        
        $bookmarks = $json['resource']['options']['bookmarks'];
    
        if($this->maxLoopCount !== false && ++$this->count === $this->maxLoopCount) $this->done();

        if ($bookmarks[0] == '-end-') {
            $this->done();
        } else {
            $this->getFeed($bookmarks);
        }
    }

    private function done() {
        dd($this->PINS);
    }


    #[Route('/fetch', name: 'app_fetch')]
    public function index(): Response
    {
        $this->getFeed();

        return $this->render('fetch/index.html.twig', [
            'controller_name' => 'FetchController',
        ]);
    }
}
