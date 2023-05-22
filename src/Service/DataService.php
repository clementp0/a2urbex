<?php

namespace App\Service;

class DataService {
    public function fetchUrl($url, $encoding = false) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);   
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);         
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        if($encoding === true) curl_setopt($ch, CURLOPT_ENCODING, '');
        
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        } else {
            return curl_exec($ch);
        }
    }

    public function writeJson($file, $data) {
        $string = json_encode($data, JSON_PRETTY_PRINT);
        $this->writeFile($file, $string);
    }

    public function writeFile($file, $data, $append = false) {
        $fp = fopen($file, $append ? 'a' : 'w');
        fwrite($fp, $data);
        fclose($fp);
    }

    public function verifyFolder($folder, $create = false) {
        if(!$create) {
            return file_exists($folder);
        } else {
            if(file_exists($folder)) return;
            mkdir($folder, 0777, true);
        }
    }

    public function initFile($file) {
        $this->writeFile($file, '');
    }

    public function getFile($file) {
        return file_get_contents($file);
    }
    public function getJson($file) {
        $data = $this->getFile($file);
        return json_decode($data, true);
    }
}