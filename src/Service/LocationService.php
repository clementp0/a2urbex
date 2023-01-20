<?php

namespace App\Service;

use App\Repository\TypeRepository;
use App\Repository\CountryRepository;

class LocationService {
    public function __construct(TypeRepository $typeRepository, CountryRepository $countryRepository) {
        $this->countries = $countryRepository->findAll();

        $this->typeOptions = [];
        foreach($typeRepository->findAll() as $type) {
            $typeOptions = $type->getTypeOptions();
            foreach($typeOptions as $item) {
                $this->typeOptions[] = $item;
            }
        }
    }

    public function addCountry($location) {
        $name = $location->getName();
        foreach($this->countries as $country) {
            if(strpos(strtolower($name), $country->getName()) !== false) {
                $location->setCountry($country);
                break;
            }
        }
    }

    private function addType($location) {
        $name = $location->getName();
        foreach($this->typeOptions as $typeOption) {
            if(strpos(strtolower($name), $typeOption->getName()) !== false) {
                $location->setType($typeOption->getType());
                break;
            }
        }
    }


}