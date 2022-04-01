<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/poster-un-statut', name: 'app_post', methods: ['POST','GET'])]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $post->setUser($user);
            $post->setStatus(["published"]);
            $post->setCreatedAt(new \DateTime());
            if ($picture = $form->get("picture")->getData()) {
                $namePicture = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $namePicture = str_replace(" ", "", $namePicture);
                $namePicture .= uniqid() . "." . $picture->guessExtension();
                $picture->move($this->getParameter("images"), $namePicture);
                $post->setPicture($namePicture);
            } 
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Votre post a été créé avec succès !');
        }

        if ($form->isSubmitted() && $form->getErrors()) {
            $this->addFlash('warning', 'Vérifiez d\'avoir remplis tous les champs requis');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
