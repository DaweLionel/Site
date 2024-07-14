<?php

namespace App\Controller;

use App\Entity\Services;
use App\Entity\Categories;
use App\Repository\ServicesRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/service', name: 'app_adminservices')]
    public function services(ServicesRepository $servicerepo): Response
    {
        $services = $servicerepo->findBy(['actif'=>true],['id'=>'desc']);
        return $this->render('admin/service/index.html.twig', [
            'services' => $services,
        ]);
    }
    #[Route('/form_service', name: 'app_form_services')]
    public function form_service(CategoriesRepository $caterep): Response
    {
        $categories = $caterep->findBy(['actif'=>true]);
        return $this->render('admin/service/form_service.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/form_recup_service/{id}', name: 'app_recup_services')]
    public function form_update($id,ServicesRepository $servicerepo,CategoriesRepository $caterep): Response
    {
        $service = $servicerepo->find($id);
        $categories = $caterep->findBy(['actif'=>true]);
        return $this->render('admin/service/form_updateservice.html.twig', [
            'service' => $service,
            'categories' => $categories,
        ]);
    }


    #[Route('/addservice', name: 'app_add_services')]
    public function add_service(Request $request,EntityManagerInterface $emi,CategoriesRepository $caterep): Response
    {
        $data = $request->request->All();
        $categorie = $caterep->FindOneBy(['id'=>$data['category']]);
        $service = new Services();
        $service->setNom($data['nom'])
                ->setPrix($data['prix'])
                ->setCategories($categorie)
                ->setActif(true);
        $emi->persist($service);
        $emi->flush();
        $this->addFlash(
           'success',
           'Service enregistré!'
        );
        return $this->redirectToRoute('app_form_services');
    }

    #[Route('/update_service/{id}', name: 'app_update_services')]
    public function update_service($id,Request $request,EntityManagerInterface $emi,ServicesRepository $servicerepo,CategoriesRepository $caterep): Response
    {
        $data = $request->request->All();
        $categorie = $caterep->FindOneBy(['id'=>$data['category']]);
        $service = $servicerepo->find($id);
        $service->setNom($data['nom'])
                ->setPrix($data['prix'])
                ->setCategories($categorie)
                ->setActif(true);
        $emi->persist($service);
        $emi->flush();
        $this->addFlash(
           'success',
           'Service modifié!'
        );
        return $this->redirectToRoute('app_adminservices');
    }

    #[Route('/delete_service/{id}', name: 'app_delete_service')]
    public function delete_service($id,Request $request,EntityManagerInterface $emi,ServicesRepository $servicerepo): Response
    {

        $service = $servicerepo->find($id);
        $service->setActif(false);
        $emi->persist($service);
        $emi->flush();
        $this->addFlash(
           'success',
           'Service supprimé!'
        );
        return $this->redirectToRoute('app_adminservices');
    }


// Administration des categories
    #[Route('/categorie', name: 'app_admincategories')]
    public function categories(categoriesRepository $categorierepo): Response
    {
        $categories = $categorierepo->findBy(['actif'=>true]);
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/form_categorie', name: 'app_form_categories')]
    public function form_categorie(CategoriesRepository $caterep): Response
    {
        $categories = $caterep->findBy(['actif'=>true]);
        return $this->render('admin/categories/form_categorie.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/form_recup_categorie/{id}', name: 'app_recup_categories')]
    public function form_update_categorie($id,categoriesRepository $categorierepo,CategoriesRepository $caterep): Response
    {
        $categorie = $categorierepo->find($id);
        $categories = $caterep->findBy(['actif'=>true]);
        return $this->render('admin/categories/form_updatecategorie.html.twig', [
            'categorie' => $categorie,
            'categories' => $categories,
        ]);
    }


    #[Route('/addcategorie', name: 'app_add_categories')]
    public function add_categorie(Request $request,EntityManagerInterface $emi,CategoriesRepository $caterep): Response
    {
        $data = $request->request->All();
        $file =$request->files->get('image');
        if(isset($file)){
            if ($file->guessExtension() === 'jpg' || $file->guessExtension() === 'jpeg' || $file->guessExtension() === 'png' || $file->guessExtension() === 'gif') {
                $newimage = uniqid().'.'.$file->guessExtension();
    
                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        'image/categories',
                        $newimage
                    );
                  
                } catch (FileException $e) {
                    $message = 'Une erreur est survenue lors de l\'enregistrement de l\'image.';
                  
                }
            }
        }
        $categorie = new Categories();
        $categorie->setNom($data['nom'])
                ->setDescription($data['description'])
                ->setActif(true);
        if(isset($newimage)){
            $categorie->SetImage($newimage);
        }
        $emi->persist($categorie);
        $emi->flush();
        $this->addFlash(
           'success',
           'categorie enregistré!'
        );
        return $this->redirectToRoute('app_admincategories');
    }

    #[Route('/update_categorie/{id}', name: 'app_update_categories')]
    public function update_categorie($id,Request $request,EntityManagerInterface $emi,categoriesRepository $categorierepo,CategoriesRepository $caterep): Response
    {
        $data = $request->request->All();
        $file =$request->files->get('image');
        if(isset($file)){
            if ($file->guessExtension() === 'jpg' || $file->guessExtension() === 'jpeg' || $file->guessExtension() === 'png' || $file->guessExtension() === 'gif') {
                $newimage = uniqid().'.'.$file->guessExtension();
    
                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        'image/categories',
                        $newimage
                    );
                  
                } catch (FileException $e) {
                    $message = 'Une erreur est survenue lors de l\'enregistrement de l\'image.';
                  
                }
            }
        }
        $categorie = $categorierepo->find($id);
        $categorie->setNom($data['nom'])
                ->setDescription($data['description'])
                ->setActif(true);
        if(isset($newimage)){
            $categorie->SetImage($newimage);
        }
        $emi->persist($categorie);
        $emi->flush();
        $this->addFlash(
           'success',
           'categorie modifié!'
        );
        return $this->redirectToRoute('app_admincategories');
    }

    #[Route('/delete_categorie/{id}', name: 'app_delete_categorie')]
    public function delete_categorie($id,Request $request,EntityManagerInterface $emi,categoriesRepository $categorierepo): Response
    {

        $categorie = $categorierepo->find($id);
        $categorie->setActif(false);
        $emi->persist($categorie);
        $emi->flush();
        $this->addFlash(
           'success',
           'categorie supprimé!'
        );
        return $this->redirectToRoute('app_admincategories');
    }























}
