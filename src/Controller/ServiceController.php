<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Services;
use App\Repository\CategoriesRepository;
use App\Repository\DisponibilitesRepository;
use App\Repository\ServicesRepository;
use App\Repository\TimeSlotsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

    #[Route('/service/{id}/detail', name: 'app_services_detail')]
    public function detail(ServicesRepository $servicerepo, CategoriesRepository $categoriesRepository,$id): Response
    {
        $data = [];

        $categorie = $categoriesRepository->find($id);
      
        
        return $this->render('service/detail.html.twig', [
            'categorie' => $categorie,
        ]);
    }
    
    #[Route('/service/{id}/reservation', name: 'app_services_reservation')]
    public function reservaion(DisponibilitesRepository $disponibilitesRepository, EntityManagerInterface $emi,$id,
    Request $request): Response
    {
        $lesdispos = $disponibilitesRepository->findBy(['statut'=>true], ['date' => 'ASC']);
      
      
        $data = [];
        $couleurs = [];

        //  $dates = $disponibilitesRepository->findAllDistinctDates();

          $today = new DateTimeImmutable('today');


          foreach ($lesdispos as $ladispos) {
             if (($ladispos->getDate()->format('Y-m-d')) < ($today->format('Y-m-d'))) {
             

              $ladispos->setStatut(false);

              $emi->persist($ladispos);
              $emi->flush();


             }
          }


        $search = $request->query->get('search');

          if ($search) {
            $convert = new DateTimeImmutable($search);
            $lesdisponibilites = $disponibilitesRepository->findBy(['statut'=>true,'date'=> $convert], ['date' => 'ASC']);

        $couleurs = ['yellow','orange','red','green'];

        $nbDisponibilites = count($lesdisponibilites);
        $nbCouleurs = count($couleurs);
        if ($nbCouleurs < $nbDisponibilites) {
            // Ajouter des couleurs pour correspondre au nombre de disponibilités
            for ($i = $nbCouleurs; $i < $nbDisponibilites; $i++) {
                $couleurs[] = $couleurs[$i % $nbCouleurs]; // Répéter les couleurs
            }
        }
          }else {
            
          


          $lesdisponibilites = $disponibilitesRepository->findBy(['statut'=>true], ['date' => 'ASC']);

        $couleurs = ['yellow','orange','red','green'];

        $nbDisponibilites = count($lesdisponibilites);
        $nbCouleurs = count($couleurs);
        if ($nbCouleurs < $nbDisponibilites) {
            // Ajouter des couleurs pour correspondre au nombre de disponibilités
            for ($i = $nbCouleurs; $i < $nbDisponibilites; $i++) {
                $couleurs[] = $couleurs[$i % $nbCouleurs]; // Répéter les couleurs
            }
        }
    }
        return $this->render('service/reservation.html.twig', [
         'couleurs'=>$couleurs,
        'jours'=>$lesdisponibilites,
        'idservices'=>$id,
           
        ]);
    }



    #[Route('/service/{idservice}/reservation/{idjour}', name: 'app_services_heurs')]
    public function Heure(TimeSlotsRepository $timeSlotsRepository , DisponibilitesRepository $disponibilitesRepository, EntityManagerInterface $emi,$idservice,
    Request $request,$idjour): Response
    {
        $jour = $disponibilitesRepository->find($idjour);
        
        $lesheurs = $timeSlotsRepository->findBy(['date'=>$jour,'statut'=>true]);
      
        
        $couleurs = ['yellow','orange','red','green'];

        $nbDisponibilites = count($lesheurs);
        $nbCouleurs = count($couleurs);
        if ($nbCouleurs < $nbDisponibilites) {
            // Ajouter des couleurs pour correspondre au nombre de disponibilités
            for ($i = $nbCouleurs; $i < $nbDisponibilites; $i++) {
                $couleurs[] = $couleurs[$i % $nbCouleurs]; // Répéter les couleurs
            }
        }
    
        return $this->render('service/heurs.html.twig', [
         'couleurs'=>$couleurs,
        'lesheurs'=>$lesheurs,
        'idservice'=>$idservice,
       // 'idjour'=>$idjour

           
        ]);
    }


    #[Route('/service/{idservice}/reserver/{idheur}', name: 'app_services_jour_heur',), IsGranted('ROLE_USER')]
    public function Jour_heur(TimeSlotsRepository $timeSlotsRepository , EntityManagerInterface $emi,$idservice,
    ServicesRepository $servicesRepository,$idheur): Response
    {
      //  $jour = $disponibilitesRepository->find($idjour);
        
        $heur = $timeSlotsRepository->find($idheur);

        $service = $servicesRepository->find($idservice);

        $user = $this->getUser();

        $reservation = new Reservation();

        $reservation->setUser($user)
                    ->setServices($service)
                    ->setCreateAt(new DateTimeImmutable())
                    ->setActif(true)
                    ->setEtat(true)
                    ->setTimeSlots($heur);

        $emi->persist($reservation);
        $emi->flush();

        $heur->setStatut(false);

        $emi->persist($heur);
        $emi->flush();

        return $this->redirectToRoute('app_reservation');
    }


    
}
