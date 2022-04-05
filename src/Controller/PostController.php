<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\CommentLike;
use App\Repository\CommentLikeRepository;
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
    /**
     * Permet de like un comment
     * 
     * @param Comment $comment
     * @param ObjectManager $manager
     * @param LikeCommentRepository $likeRepo
     * @return Response
     */
    #[Route('/comment/{id}', name: 'comment_like', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function like(Comment $comment, ManagerRegistry $manager, CommentLikeRepository $likeRepo, Request $request): Response
    {
        $user = $this->getUser();
        $entityManager = $manager->getManager();

        if ($comment->isLikedByUser($user)) {
            $like = $likeRepo->findOneBy([
                'comment' => $comment,
                'user' => $user
            ]);

            $entityManager->remove($like);
            $entityManager->flush();

            return $this->redirectToRoute(
                'show_post',
                ['id' => $comment->getPost()->getId()],
                Response::HTTP_SEE_OTHER
            );
        }


        $like = new CommentLike();
        $like->setComment($comment)
            ->setUser($user);
        $entityManager->persist($like);
        $entityManager->flush();

        return $this->redirectToRoute(
            'show_post',
            ['id' => $comment->getPost()->getId()],
            Response::HTTP_SEE_OTHER
        );
    }
}

