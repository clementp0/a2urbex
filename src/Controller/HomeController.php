<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Location;
use App\Entity\Country;
use App\Entity\Category;

class HomeController extends AppController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();

        //Location_count
        $repoLocation = $em->getRepository(Location::class);
        $pins_count = $repoLocation->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.pending = 0 OR a.pending IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        //Country_count
        $repoCountry = $em->getRepository(Country::class);
        $country_count = $repoCountry->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        //Category_count
        $repoCategory = $em->getRepository(Category::class);
        $category_count = $repoCategory->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        //Return data 
        return $this->render('home/index.html.twig', [
            'pins' => $pins_count,
            'country' => $country_count,
            'category' => $category_count
        ]);
    }

    #[Route('/links', name: 'app_links')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}
