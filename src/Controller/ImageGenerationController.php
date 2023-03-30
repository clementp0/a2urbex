<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\LocationService;

class ImageGenerationController extends AbstractController
{

    public function __construct(LocationRepository $locationRepository, LocationService $locationService)
    {
        $this->locationRepository = $locationRepository;
        $this->locationService = $locationService;
        $this->imgPath = $_ENV['IMG_LOCATION_PATH'];
        $this->source = '';
    }


    #[Route('/generation', name: 'app_image_generation')]
    public function index(): Response
    {


        $path = $_ENV['PROJECT_PATH'];
        $locations = $this->locationRepository->findAll();
        $publicDir = $this->getParameter('public_directory');

        $imgName = $this->locationService->generateImgUid() . '.' . $ext;
        file_put_contents($publicDir . $this->imgPath . $imgName, $file);

        
        foreach ($locations as $location) {
            if ($location->getImage() == "") {

                $output = shell_exec('cd && cd stable_diffusion.openvino && python demo.py --prompt "' . $locationName . '" --output "' . $locationId . '.png" && mv ' . $locationId . '.png caca/' . $locationId . '.png ');
                echo "<pre>$output</pre>";


                $location->setImage($this->imgPath . $imgName);

            }
        }


        // foreach($locations as $location) {
        //     if ($location->getImage() == "") {
        //         $locationId = $location->getId();
        //         $locationName = $location->getName();

        //         $output = shell_exec('cd && cd stable_diffusion.openvino && python demo.py --prompt "' . $locationName . '" --output "' . $locationId . '.png" && mv ' . $locationId . '.png caca/' . $locationId . '.png ');
        //         echo "<pre>$output</pre>";

        //    }
        // }



    }
}