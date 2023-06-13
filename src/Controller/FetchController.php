<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\PinterestService;
use App\Service\WikimapiaService;
use App\Service\LocationService;
use App\Repository\LocationRepository;
use App\Repository\ConfigRepository;

class FetchController extends AppController
{
    public function __construct(
        private LocationRepository $locationRepository,
        private LocationService $locationService,
        private PinterestService $pinterestService,
        private WikimapiaService $wikimapiaService,
        private ConfigRepository $configRepository
    ) {}

    #[Route('/fetch/lock/reset', name: 'app_fetch_lock_reset')]
    public function fetchLockReset(): Response {
        $this->configRepository->set('pinterest', 'fetch_lock', '0');
        return $this->redirect('/admin');
    }
    
    #[Route('/fetch/pinterest', name: 'app_fetch_pinterest')]
    public function fetchPinterest(): Response {
        $this->pinterestService->fetch();
        return $this->redirect('/admin');
    }

    #[Route('/fetch/pinterest/async', name: 'app_fetch_pinterest_async')]
    public function fetchPinterestAsync($rootDirectory): Response {
        $lock = (bool)$this->configRepository->get('pinterest', 'fetch_lock');
        if($lock === false) {
            $command = 'pinterest:fetch';
            $commandToExecute = sprintf('php %s/bin/console %s > /dev/null 2>&1 &', $rootDirectory, $command);
            exec($commandToExecute);
        }

        return new Response(json_encode(['lock' => $lock]));
    }

    #[Route('/fetch/wikimapia', name: 'app_fetch_wikimapia')]
    public function fetchWikimapia(): Response {
        $this->wikimapiaService->fetch();
        return $this->redirect('admin');
    }

    #[Route('/fetch/wikimapia/pending', name: 'app_fetch_wikimapia_pending')]
    public function fetchWikimapiaPending(): Response {
        $this->wikimapiaService->fetchInfo();
        return $this->redirect('admin');
    }

    #[Route('/check', name: 'app_check_count')]
    public function check(): Response { 

        $existing = $this->locationRepository->findBySource('Pinterest');
        $existing_count = count($existing);
        $remaining = $this->pinterestService->getPinTotal(true) - $existing_count;
        $this->configRepository->set('pinterest', 'pin_to_import', $remaining);

        return $this->redirect('admin');
    }

    #[Route('/update', name: 'app_update')]
    public function update(): Response {
        $locations = $this->locationRepository->findAll();
        
        foreach($locations as $location) {
            if(!$location->getCountry()) $this->locationService->addCountry($location);
            if(!$location->getCategory()) $this->locationService->addCategory($location);
            $this->locationRepository->add($location);
        }
        
        $this->configRepository->set('pinterest', 'update_date', date("d/m/Y H:i", time()));
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