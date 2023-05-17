<?php

namespace App\Service;

class WikimapiaService {
    public function __construct(
        private string $publicDirectory,
        private DataService $dataService
    ) {
        $this->catId = $_ENV['WIKIMAPIA_CAT_ID'];
        $this->url = $_ENV['WIKIMAPIA_BASE_URL'];
        $this->fetchUrl = $_ENV['WIKIMAPIA_FETCH_BASE_URL'];
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];
        
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
        die;
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
                dump($url);
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
}