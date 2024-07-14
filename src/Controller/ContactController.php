<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    #[Route('/', name: 'app_contact', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('contact/index.html.twig', [
           
        ]);
    }

    #[Route('/new-save', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        

        if ($request->isMethod('post')) {

            $data = $request->request->all();

        

        $contact = new Contact();
      
        $contact->setName($data['name'])
                ->setEmail($data['email'])
                ->setSubject($data['subject'])
                ->setMessage($data['message'])
                ->setLecture(false);

            $entityManager->persist($contact);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_contact', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/delete/{id}', name: 'app_contact_delete')]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
       
            $entityManager->remove($contact);
            $entityManager->flush();
        

        return $this->redirectToRoute('app_contact', [], Response::HTTP_SEE_OTHER);
    }
}
