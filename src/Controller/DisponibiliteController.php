<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Entity\Disponibilites;
use App\Entity\TimeSlots;
use App\Repository\DisponibiliteRepository;
use App\Repository\DisponibilitesRepository;
use App\Repository\TimeSlotsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/disponibilite')]
class DisponibiliteController extends AbstractController
{
    #[Route('/', name: 'app_disponibilite_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('disponibilite/index.html.twig', []);
    }
    #[Route('/list', name: 'app_disponibilite_list', methods: ['GET'])]
    public function list(DisponibilitesRepository $disponibilitesRepository, TimeSlotsRepository $timeSlotRepository,
     Request $request): Response
    {
         
        $search = $request->query->get('search');

        if ($search) {

           $convert = new DateTimeImmutable($search);
           
            $lesdisponibilites = $disponibilitesRepository->findBy(['date'=>$convert], ['id' => 'desc']);

       

            $data = [];
            foreach ($lesdisponibilites as $lesdisponibilite) {
                $date = $lesdisponibilite->getDate()->format('Y-m-d');
                if (!isset($data[$date])) {
                    $data[$date] = [
                        'jour' => $lesdisponibilite->getDate(),
                        'time' => [],
                    ];
                }
                
                $data[$date]['time'] = array_merge($data[$date]['time'], $lesdisponibilite->getTimeSlots()->toArray());
            }
        }else{
            $lesdisponibilites = $disponibilitesRepository->findBy([], ['id' => 'desc']);

       

            $data = [];
            foreach ($lesdisponibilites as $lesdisponibilite) {
                $date = $lesdisponibilite->getDate()->format('Y-m-d');
                if (!isset($data[$date])) {
                    $data[$date] = [
                        'jour' => $lesdisponibilite->getDate(),
                        'time' => [],
                    ];
                }
                
                $data[$date]['time'] = array_merge($data[$date]['time'], $lesdisponibilite->getTimeSlots()->toArray());
            }
    
        }
 
       
       

        return $this->render('disponibilite/show.html.twig', [
            'datas' => $data,
          
        ]);
    }

    #[Route('/new', name: 'app_disponibilite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $emi): Response
    {
        $data = json_decode($request->getContent(), true);

        try {
            // Create new Disponibilite entity
            $jour = new Disponibilites();
            $jour->setDate(new \DateTimeImmutable($data['date']));
            $emi->persist($jour);
            $emi->flush();
            //  http://localhost:8000/admin/disponibilite/new

            return new JsonResponse(['success' => true, 'id' => $jour->getId()]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    #[Route('/date/{id}/time_slots', methods: ['GET'])]
    public function getTimeSlots(Disponibilites $date, TimeSlotsRepository $timeSlotRepository): JsonResponse
    {
        $timeSlots = $timeSlotRepository->findBy(['date' => $date]);

        $data = [];
        foreach ($timeSlots as $slot) {
            $data[] = [
                'start_time' => $slot->getStartTime()->format('H:i'),
                'end_time' => $slot->getEndTime()->format('H:i'),
                'status' => $slot->isStatut(),
            ];
        }

        return new JsonResponse(['success' => true, 'time_slots' => $data]);
    }


    #[Route('/time_slots/new', methods: ['POST'])]
    public function addTimeSlot(Request $request, EntityManagerInterface $entityManager, DisponibilitesRepository $dateRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $date = $dateRepository->find($data['date_id']);

        if (!$date) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid date ID', 'DateID' => $data['date_id']]);
        }

        $timeSlot = new TimeSlots();
        $timeSlot->setDate($date);
        $timeSlot->setStartTime(new \DateTimeImmutable($data['start_time']));
        $timeSlot->setEndTime(new \DateTimeImmutable($data['end_time']));
        $timeSlot->setStatut($data['status'] ?? 'available');

        $entityManager->persist($timeSlot);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }


    #[Route('/time_slots/update/{id}', methods: ['PUT'])]
    public function updateTimeSlot($id, Request $request, TimeSlotsRepository $timeSlotRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $timeSlot = $timeSlotRepository->find($id);
        if (!$timeSlot) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid time slot ID'], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['start_time']) && isset($data['end_time'])) {
            $timeSlot->setStartTime(new \DateTimeImmutable($data['start_time']));
            $timeSlot->setEndTime(new \DateTimeImmutable($data['end_time']));
            $timeSlot->setStatut($data['status'] ?? 'available');

            $entityManager->persist($timeSlot);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'error' => 'Invalid data'], 400);
    }


    #[Route('/time_slots/delete/{id}', name: 'app_disponibilite_delete')]
    public function deleteTimeSlot($id, TimeSlotsRepository $timeSlotRepository, EntityManagerInterface $entityManager): Response
    {
        $timeSlot = $timeSlotRepository->find($id);

        $entityManager->remove($timeSlot);
        $entityManager->flush();

        return $this->redirectToRoute('app_disponibilite_list');
    }
}
