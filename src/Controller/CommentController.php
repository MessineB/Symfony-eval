<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
    /**
     * Permet de like un post
     * 
     * @param Comment $comment
     * @param ObjectManager $manager
     * @param LikeCommentRepository $likeRepo
     * @return Response
     */
    #[Route('/comment/{id}', name: 'comment_like', methods: ['GET'])]
    public function like(Comment $comment, ManagerRegistry $manager, LikeCommentRepository $likeRepo, Request $request): Response
    {
        $user = $this->getUser();
        $entityManager = $manager->getManager();
        if (!$user)  return $this->redirectToRoute('main_page', [], Response::HTTP_SEE_OTHER);

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


        $like = new LikeComment();
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

