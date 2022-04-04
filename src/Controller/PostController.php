<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{

    /* Voir un seul post */
    #[Route('/voir-un-post/{id}', name: 'show_post', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
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

        /* Voir un seul post */
        #[Route('/tous-les-posts', name: 'admin_post', methods: ['GET', 'POST'])]
        #[IsGranted("ROLE_ADMIN")]
        public function postAdmin(): Response
        {
    
            return $this->render('post/admin-post.html.twig', [
            ]);
    
        }
}
