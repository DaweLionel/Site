<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation'), IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(['user'=>$this->getUser(),'actif'=>true,'etat'=>true]);
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    #[Route('/reservation/{id}/annuler', name: 'app_reservation_annuler'), IsGranted('ROLE_USER')]
    public function Annuler(ReservationRepository $reservationRepository, $id, 
    TimeSlotsRepository $timeSlotsRepository, EntityManagerInterface $emi): Response
    {
        $annulerReservation = $reservationRepository->find($id);

        $idcrenaux =  $annulerReservation->getTimeSlots();

        $crenaux = $timeSlotsRepository->find($idcrenaux);

        $crenaux->setStatut(true);

        $emi->persist($crenaux);
        $emi->flush();


        $annulerReservation->setEtat(false);

        $emi->persist($annulerReservation);
        $emi->flush();

       
        return $this->redirectToRoute('app_reservation');
    }
}
