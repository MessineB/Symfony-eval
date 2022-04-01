<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'app_registration')]
    public function index(Request $request, UserPasswordHasherInterface $passwordEncoder , ManagerRegistry $doctrine): Response
    {   
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        //Si le formulaire est soumis et qu'il est valide: 
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setRoles(["ROLE_USER"]); /* Attribut par défaut le rôle User aux nouveaux inscrits */

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            #Notification
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Veuillez vous authentifier');
            return $this->redirectToRoute('app_login');
        }
        //Si le formulaire est soumis et qu'il y a des erreurs:
        if ($form->isSubmitted() && $form->getErrors()) {
            $this->addFlash('warning', 'Vérifiez d\'avoir remplis tous les champs requis et d\'avoir accepté les conditions d\'utilisation de nos services');
        }
        return $this->render('registration/index.html.twig', [
            'RegistrationForm' => $form->createView() ,
        ]);
    }
}
