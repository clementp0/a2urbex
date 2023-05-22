<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\PinterestService;
use App\Service\WikimapiaService;
use App\Service\LocationService;
use App\Service\DataService;
use App\Repository\LocationRepository;

class FetchController extends AppController
{
    public function __construct(
        private string $dataDirectory,
        private LocationRepository $locationRepository,
        private LocationService $locationService,
        private PinterestService $pinterestService,
        private WikimapiaService $wikimapiaService,
        private DataService $dataService
    ) {}
    
    #[Route('/fetch/pinterest', name: 'app_fetch_pinterest')]
    public function fetchPinterest(): Response {
        $this->pinterestService->fetch();
        return $this->redirect('admin');
    }

    #[Route('/fetch/wikimapia', name: 'app_fetch_wikimapia')]
    public function fetchWikimapia(): Response {
        $this->wikimapiaService->fetch();
        return $this->redirect('admin');
    }

    #[Route('/fetch/wikimapia/pending', name: 'app_fetch_wikimapia')]
    public function fetchWikimapiaPending(): Response {
        $this->wikimapiaService->fetchInfo();
        return $this->redirect('admin');
    }

    #[Route('/check', name: 'app_check_count')]
    public function check(): Response { 

        $existing = $this->locationRepository->findBySource('Pinterest');
        $existing_count = count($existing);
        $remaining = $this->pinterestService->getPinTotal(true) - $existing_count;
        $this->dataService->writeFile($this->dataDirectory.'count.txt', $remaining);

        return $this->redirect('admin');
    }

    #[Route('/update', name: 'app_update')]
    public function update(): Response {
        $locations = $this->locationRepository->findAll();

        foreach($locations as $location) {
            if(!$location->getCountry()) $this->locationService->addCountry($location);
            if(!$location->getType()) $this->locationService->addType($location);
            $this->locationRepository->add($location);
        }

        $data = ["last_updated" => date("d/m/Y H:i", time())];
        $this->DataService->writeJson($this->dataDirectory.'update.json', $data);
        return $this->redirect('admin');
    }

    #[Route('/patch', name: 'app_patch')]
    public function patch(): Response {
        $dup = $this->locationRepository->findPidDuplicate();

        foreach($dup as $row) {
            $items = explode(',', $row['ids']);
            unset($items[0]);

            foreach($items as $item) {
                $loc = $this->locationRepository->find($item);
                $this->locationRepository->remove($loc);
            }
        }

        return $this->redirect('admin');
    }
}