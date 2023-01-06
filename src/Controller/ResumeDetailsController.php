<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class ResumeDetailsController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    

    /**
     * @Route("/resume/fr", name="french", methods={"GET"})
     */
    
    public function french( SerializerInterface $serializer): Response
    {
        $id=1;
        $resume_fr = $this->entityManager->getRepository(Category::class)->find($id);
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize($resume_fr, 'json', ['ignored_attributes' => ['owner'], 'circular_reference_handler' => function ($object) {return $object->getId(); }
        ]);

        if ($resume_fr) {
            return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);

        }
        else{
            return $this->json(["error" => "No Data"]);
        }

    }

    /**
     * @Route("/resume/en", name="english", methods={"GET"})
     */
    
    public function english( SerializerInterface $serializer): Response
    {
        $id=2;
        $resume_en = $this->entityManager->getRepository(Category::class)->find($id);
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize($resume_en, 'json', ['ignored_attributes' => ['owner'], 'circular_reference_handler' => function ($object) {return $object->getId(); }
        ]);

        if ($resume_en) {
            return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);

        }
        else{
            return $this->json(["error" => "No Data"]);
        }

    }

    /**
     * @Route("/resume/pl", name="polish", methods={"GET"})
     */
    
    public function polish( SerializerInterface $serializer): Response
    {
        $id=3;
        $resume_pl = $this->entityManager->getRepository(Category::class)->find($id);
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize($resume_pl, 'json', ['ignored_attributes' => ['owner'], 'circular_reference_handler' => function ($object) {return $object->getId(); }
        ]);

        if ($resume_pl) {
            return new Response($jsonObject, 200, ['Content-Type' => 'application/json']);

        }
        else{
            return $this->json(["error" => "No Data"]);
        }

    }

   
}