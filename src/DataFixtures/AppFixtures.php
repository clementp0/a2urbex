<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\Country;
use App\Entity\Category;
use App\Entity\CategoryOption;

class AppFixtures extends Fixture {
    public function load(ObjectManager $manager): void {
        $this->addCategory($manager);
        $this->addCountries($manager);
    }

    private function addCategory($manager) {
        $categories = [
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

        foreach($categories as $name => $options) {
            $t = new Category();
            $t->setName($name);
            $manager->persist($t);

            foreach($options as $option) {
                $o = new CategoryOption();
                $o->setName($option);
                $o->setCategory($t);
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
            'serbia',
            'denmark',
            'slovenia',
            'slovakia',
            'portugal',
            'scotland',
            'croatia',
            'latvia',
            'czechia',
            'netherlands',
            'switzerland',
            'austria',
            'wales',
            'luxembourg',
            'hungary',
            'japan',
            'finland',
            'ukraine',
            'russia',
            'united kingdom',
            'estonia',
            'morocco',
            'georgia',
            'australia'
        ];

        foreach($countries as $country) {
            $c = new Country();
            $c->setName($country);
            $manager->persist($c);
        }

        $manager->flush();
    }
}
