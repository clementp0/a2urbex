<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\Country;

class AppFixtures extends Fixture {
    public function load(ObjectManager $manager): void {
        $this->addTypes($manager);
        $this->addCountries($manager);
    }

    private function addTypes($manager) {
        $types = [
            'castle' => ['château', 'chateau', 'pałac', 'schloss', 'castle'],
            'hostel' => ['hotel', 'hôtel', 'hostel', 'resort'],
            'cinema' => ['cinéma', 'cinema'],
            'train' => ['train', 'gare'],
            'hospital' => ['hospital', 'hôpital', 'hopital'],
            'house' => ['manoir', 'maison', 'casa', 'villa', 'house', 'haus'],
            'factory' => ['industrial', 'usine', 'factory'],
            'building' => ['building', 'construction'],
            'restaurant' => ['restaurant'],
        ];

        foreach($types as $name => $options) {
            $t = new Type();
            $t->setName($name);
            $manager->persist($t);

            foreach($options as $option) {
                $o = new TypeOption();
                $o->setName($option);
                $o->setType($t);
                $manager->persist($o);
            }
        }

        $manager->flush();
    }

    private function addCountries($manager) {
        $countries = [
            'france',
            'england',
            'poland',
            'ireland',
            'spain',
            'italy',
            'germany',
            'belgium',
            'serbia'
        ];

        foreach($countries as $country) {
            $c = new Country();
            $c->setName($country);
            $manager->persist($c);
        }

        $manager->flush();
    }
}
