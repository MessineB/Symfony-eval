<?php

namespace App\Controller;

use App\Entity\Comment;
use DateTime;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
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

    /* Voir un seul post */
    #[Route('/voir-un-post/{id}', name: 'show_post', methods: ['GET', 'POST'])]
    public function showOnePost(Post $post, ManagerRegistry $doctrine, Request $request): Response
    {
        /* Récupération des commentaire d'un post */
        $comments = $post->getComments();

        /* Ajout du formulaire */
        $newComment = new Comment();
        $form = $this->createForm(CommentType::class, $newComment);
        $form->handleRequest($request);

        /* Traitement du formulaire d'ajout de commentaire */
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $newComment->setUser($user);
            $newComment->setPost($post);
            $newComment->setStatus(["published"]);
            $newComment->setCreatedAt(new \DateTime());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($newComment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire a été créé avec succès !');
        }

        return $this->render('post/one-post.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'comments'=> $comments
        ]);
    }
}
