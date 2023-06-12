<?php

namespace App\Service;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;

class HashService {
    public function __construct(
        private HashidsServiceInterface $hashidsService
    ) {}

    public function encode($id, $type = null) {
        $hash = $this->getTypeHash($type);
        return $this->hashidsService->encode($hash.$id);
    }
    public function decode($value, $type = null) {
        $hash = $this->getTypeHash($type);
        $decoded = $this->hashidsService->decode($value);
        $string = is_array($decoded) ? $decoded[0] : $decoded;

        return (int)str_replace($hash, '', $string);
    }
    private function getTypeHash($type) {
        switch ($type) {
            case 'location': return $_ENV['HASH_KEY_LOCATION'];
            case 'favorite': return $_ENV['HASH_KEY_FAVORITE'];
            case 'user': return $_ENV['HASH_KEY_USER'];
            default: return $_ENV['HASH_KEY_DEFAULT'];
        }
    }

    public function encodeLoc($id) {
        return $this->encode($id, 'location');
    }
    public function encodeFav($id) {
        return $this->encode($id, 'favorite');
    }
    public function encodeUsr($id) {
        return $this->encode($id, 'user');
    }
    public function decodeLoc($id) {
        return $this->decode($id, 'location');
    }
    public function decodeFav($id) {
        return $this->decode($id, 'favorite');
    }
    public function decodeUsr($id) {
        return $this->decode($id, 'user');
    }
}