<?php

namespace App\Service;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\LocationService;
use App\Service\DataService;
use Symfony\Component\DomCrawler\Crawler;

class WikimapiaService {
    public function __construct(
        private string $publicDirectory,
        private DataService $dataService,
        private LocationRepository $locationRepository,
        private LocationService $locationService
    ) {
        $this->catId = $_ENV['WIKIMAPIA_CAT_ID'];
        $this->url = $_ENV['WIKIMAPIA_BASE_URL'];
        $this->fetchUrl = $_ENV['WIKIMAPIA_FETCH_BASE_URL'];
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];
        $this->source = 'Wikimapia';
        
        $this->zoom = (int)$_ENV['WIKIMAPIA_ZOOM'];
        $this->fetchSize = pow(2, $this->zoom - 2);
        $this->hash = 0;
        $this->factor = 0;
        $this->catUrl = '000/000/000';
    }
    public function fetch() {
        $this->dataService->verifyFolder($this->publicDirectory.$this->imgPath, true);
        $this->hash = $this->getHash();
        $this->catUrl = $this->getCatUrl();
        $this->factor = $this->getFactor();
        $this->fetchBase();
        $this->fetchInfo();
    }

    private function getHash() {
        $rand = (float)rand() / (float)getrandmax();
        return (int)round($rand * 1e7);
    }

    private function getFactor() {
        return (int)(log(1024) / log(2));
    }

    private function splitForUrl($str) {
        $out = '';

        for ($i = 0; $i < strlen($str); $i++) { 
            if($i !== 0 && $i % 3 === 0) $out .= '/';
            $out .= $str[$i];
        }
        return $out;
    }

    private function getCatUrl() {
        $str = str_pad($this->catId, 9, '0', STR_PAD_LEFT);
        return $this->splitForUrl($str);
    }

    private function fetchBase() {
        for ($x = 0; $x < $this->fetchSize; $x++) { 
            for ($y = 0; $y < $this->fetchSize; $y++) { 
                $url = $this->generateTileUrl($x, $y, $this->zoom);
                try {
                    $response = $this->dataService->fetchurl($url, true);
                } catch(\Exception $e) {
                    dd($e->getMessage());
                }

                $rows = explode("\n", $response);
                $rows = array_slice($rows, 4);
                foreach($rows as $row) $this->savePinBase($row);
            }
        }
    }

    private function generateTileUrl($x, $y, $zoom) {
        $quadKey = $this->generateQuadKey($x, $y, $zoom, $this->factor);
        $splitQuadKey = $this->splitForUrl($quadKey);
        return $this->fetchUrl . $this->catUrl . '/' . $splitQuadKey . '.xy' . '?'.$this->hash;
    }

    private function generateQuadKey($x, $y, $zoom, $factor) {
        $o = [[-2, 1],[0, 2],[2, 3]][$factor - 8];
        $n = '0';
        
        $x = (int)round($x);
        $y = (int)round((1 << $zoom - $o[0]) - $y - 1);
        $zoom -= $o[1];
        
        while($zoom >= 0) {
            $s = 1 << $zoom;
            $n .= (($x & $s) > 0 ? 1 : 0) + (($y & $s) > 0 ? 2 : 0);
            $zoom--;
        }

        return $n;
    }

    private function savePinBase($row) {
        $arr = explode('|', $row);
        if(count($arr) < 2) return;
        
        $exist = $this->locationRepository->findOneBy(['pid' => $arr[0], 'source' => $this->source]) !== null;
        if(!$exist) {
            $location = new Location();
            $location
                ->setPid($arr[0])
                ->setUrl($this->url.$arr[0])
                ->setSource($this->source)
                ->setPending(true)
            ;

            $this->locationRepository->add($location);
        }
    }

    public function fetchInfo() {
        $items = $this->locationRepository->findBy(['source' => $this->source, 'pending' => true]);
        foreach($items as $item) {
            $this->savePin($item);
        }
    }
    
    private function savePin($item) {
        try {
            $response = $this->dataService->fetchUrl($item->getUrl());
        } catch(\Exception $e) {
            dd($e->getMessage());
        }
        $response = preg_replace('#<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>#', '', $response);

        if(strpos($response, 'This place was deleted')) return;

        $crawler = new Crawler();
        $crawler->addHtmlContent($response);
        
        $coordinates = $crawler->filter('#comments')->previousAll()->text();
        $coordinatesSplit = explode(' ', $coordinates);
        if(count($coordinatesSplit) !== 5) return;

        $item
            ->setPending(false)
            ->setLat((float)$this->locationService->convertCoord($coordinatesSplit[2]))
            ->setLon((float)$this->locationService->convertCoord($coordinatesSplit[4]))
        ;
        
        $nameElement = $crawler->filter('h1');
        $item->setName($nameElement->count() > 0 ? $nameElement->text() : 'unknown');

        $descriptionElement = $crawler->filter('#place-description');
        if($descriptionElement->count() > 0) $item->setDescription($descriptionElement->text());

        $imageElement = $crawler->filter('#place-photos a');
        if($imageElement->count() > 0) $item->setImageDirect($imageElement->attr('href'));
    
        $country = $crawler->filter('#placeinfo-locationtree a')->text();
        $this->locationService->addCountryDirect($item, $country);

        $this->locationRepository->add($item);
    }
}
