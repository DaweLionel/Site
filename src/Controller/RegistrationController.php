<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_inscription')]
    public function index(): Response
    {
        return $this->render('registration/inscription_form.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        
    
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('registration/index.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
    
        return new Response('ok');
    }

    #[Route('/save_user', name: 'app_save_user')]
    public function inscrip(UserRepository $userep,Request $request,UserPasswordHasherInterface $userhash,EntityManagerInterface $em): Response
    {
        $data = $request->request->All();
        
        $verif = $userep->findOneBy(['email' =>$data['email']]);
        //dd($data);
        if ($verif == null){
            $user = new User;
            $user->setNom($data['nom'])
                 ->setPrenom($data['prenom'])
                 ->setTelephone($data['telephone'])
                 ->setEmail($data['email'])
                 ->setPassword($userhash->hashPassword(
                    $user,
                    $data['password']
            ))
                 ->setRoles(['ROLE_USER'])
                 ->setActif(true);
             $em->persist($user);
             $em->flush();
             $this->addFlash('success','inscription reussie');
            return $this->redirectToRoute('app_login');
        }
        else{
            $this->addFlash('danger','un utilisateur avec cet email existe déjà');
            return $this->redirectToRoute('app_inscription');
        }

    }
}
