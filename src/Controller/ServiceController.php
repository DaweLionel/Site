<?php

namespace App\Controller;

use App\Entity\Services;
use App\Repository\CategoriesRepository;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_services')]
    public function index(ServicesRepository $servicerepo, CategoriesRepository $categoriesRepository): Response
    {

        $categories = $categoriesRepository->findBy(['actif'=>true]);
      
        
        return $this->render('service/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    
}
