<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategoriesRepository $cateRepo): Response
    {
        $categories = $cateRepo->findBy(['actif'=>true]);
        return $this->render('home/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    
}
