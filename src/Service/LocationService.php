<?php

namespace App\Service;

use App\Repository\TypeRepository;
use App\Repository\CountryRepository;
use Geocoder\Provider\Provider;
use Geocoder\Query\ReverseQuery;
use App\Entity\Country;

class LocationService {
    public function __construct(TypeRepository $typeRepository, CountryRepository $countryRepository, Provider $googleMapsGeocoder) {
        $this->googleMapsGeocoder = $googleMapsGeocoder;
        $this->countryRepository = $countryRepository;

        $this->typeOptions = [];
        foreach($typeRepository->findAll() as $type) {
            $typeOptions = $type->getTypeOptions();
            foreach($typeOptions as $item) {
                $this->typeOptions[] = $item;
            }
        }
    }

    public function addCountry($location) {
        if($location->getLat() && $location->getLon()) {
            $item = $this->googleMapsGeocoder->reverseQuery(ReverseQuery::fromCoordinates($location->getLat(), $location->getLon()));
            $c = $item->first()->getCountry();

            if($c) {
                $country = $this->countryRepository->findOneBy(['code' => $c->getCode()]);
                if(!$country) {
                    $country = new Country();
                    $country->setCode($c->getCode())->setName($c->getName());
                    $this->countryRepository->add($country);
                }
    
                $location->setCountry($country);
            }
        }
    }

    public function addType($location) {
        $name = $location->getName();
        foreach($this->typeOptions as $typeOption) {
            if(strpos(strtolower($name), $typeOption->getName()) !== false) {
                $location->setType($typeOption->getType());
                break;
            }
        }
    }

    public function generateImgUid() {
        $k = $_ENV['HASH_KEY'];
        $kp = substr($k, rand(0, strlen($k) - 2), 2);
        return $kp.uniqid();
    }
}