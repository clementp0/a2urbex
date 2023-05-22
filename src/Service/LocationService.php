<?php

namespace App\Service;

use App\Repository\TypeRepository;
use App\Repository\CountryRepository;
use Geocoder\Provider\Provider;
use Geocoder\Query\ReverseQuery;
use App\Entity\Country;
use Symfony\Component\Security\Core\Security;
use App\Repository\FriendRepository;
use App\Repository\LocationRepository;

class LocationService {
    public function __construct(
        TypeRepository $typeRepository, 
        CountryRepository $countryRepository, 
        Provider $googleMapsGeocoder,
        private Security $security,
        private FriendRepository $friendRepository,
        private LocationRepository $locationRepository
    ) {
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
            $items = $this->googleMapsGeocoder->reverseQuery(ReverseQuery::fromCoordinates($location->getLat(), $location->getLon()));
            
            $c = null;
            foreach($items->getIterator() as $item) {
                $c = $item->getCountry();
                if($c) break;
            }
            if(!$c) return;

            $country = $this->countryRepository->findOneBy(['code' => $c->getCode()]);
            if(!$country) {
                $country = new Country();
                $country->setCode($c->getCode())->setName($c->getName());
                $this->countryRepository->add($country);
            }

            $location->setCountry($country);
        }
    }

    public function addCountryDirect($location, $name) {
        $country = $this->countryRepository->findOneBy(['name' => $name]);
        if(!$country) {
            $country = new Country();
            $country->setName($name);
            $this->countryRepository->add($country);
        }

        $location->setCountry($country);
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

    public function findSearch($search, $submit = false) {    
        $user = $this->security->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_SUPERUSER', $user->getRoles(), true)) {
            if ($submit) {
                return $this->locationRepository->findWithSearch($search);
            } else {
                return $this->locationRepository->findByAll();
            }
        } else {
            $users = [$user->getId()];

            $f = $this->friendRepository->findFriendForSearch($user->getId());
            if($f) {
                foreach($f as $item) {
                    $users[] = $item['id'];
                }
            }

            if ($submit) {
                return $this->locationRepository->findWithSearchAndUsers($search, $users);
            } else {
                return $this->locationRepository->findByUsers($users);
            }
        }
    }

    public function convertCoord($str) {
        preg_match('#([0-9]+)°([0-9]+)\'([0-9]+(.[0-9])?)"([A-Z])#', $str, $matches);
        if(count($matches) === 6) {
            $pos = in_array($matches[5], ['N', 'E']) ? 1 : -1;
            return $pos*($matches[1]+$matches[2]/60+$matches[3]/3600);
        }
        return $str;
    }
}