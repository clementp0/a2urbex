<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Service\LocationService;

class ImageGenerationController extends AppController
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

        $locations = $this->locationRepository->findAll();

        function post_data_to_url($url, $data)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }


        foreach ($locations as $location) {

            $s_path = $_ENV['STABLE_PATH'];
            $s_port = $_ENV['STABLE_PORT'];
            $s_url = $_ENV['STABLE_URL'];
            $s_date = date('Y-m-d');

            if ($location->getImage() == "") {

                shell_exec('cd && cd ' . $s_path .'outputs/txt2img-images/' . $s_date . '/ && mkdir render_a2urbex');
                $url = $s_url . ':' . $s_port . '/sdapi/v1/txt2img';
                $locationName = $location->getName() . " urbex";
                $imgName = $this->locationService->generateImgUid() . '.png';
                $data = array(
                    "enable_hr" => false,
                    "denoising_strength" => 0,
                    "firstphase_width" => 0,
                    "firstphase_height" => 0,
                    "hr_scale" => 2,
                    "hr_second_pass_steps" => 0,
                    "hr_resize_x" => 0,
                    "hr_resize_y" => 0,
                    "prompt" => "$locationName",
                    "seed" => -1,
                    "subseed" => -1,
                    "subseed_strength" => 0,
                    "seed_resize_from_h" => -1,
                    "seed_resize_from_w" => -1,
                    "sampler_name" => "Euler a",
                    "batch_size" => 1,
                    "n_iter" => 1,
                    "steps" => 50,
                    "cfg_scale" => 7,
                    "width" => 512,
                    "height" => 512,
                    "restore_faces" => false,
                    "tiling" => false,
                    "do_not_save_samples" => false,
                    "do_not_save_grid" => false,
                    "negative_prompt" => "string",
                    "eta" => 0,
                    "s_churn" => 0,
                    "s_tmax" => 0,
                    "s_tmin" => 0,
                    "s_noise" => 1,
                    "override_settings" => array(),
                    "override_settings_restore_afterwards" => true,
                    "script_args" => array(),
                    "sampler_index" => "Euler a",
                    "send_images" => true,
                    "save_images" => true
                );

                $response = post_data_to_url($url, $data);
                shell_exec('cd && mv ' . $s_path .'outputs/txt2img-images/' . $s_date  . '/*.png ' . $s_path . 'outputs/txt2img-images/' . $s_date . '/render_a2urbex/' . $imgName);
                $location->setImage($this->imgPath . $imgName);
                $location->setAi(true);
                $this->locationRepository->add($location);

            } else {

            }
        }

        return $this->redirect('admin');

    }
}