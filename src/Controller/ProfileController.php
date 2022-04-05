<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    public function index(PostRepository $postrepo,): Response
    { 
        $user = $this->getUser();
        $posts = $postrepo->findByUserid($user);
        $nbrPost = count($posts);
        return $this->render('profile/index.html.twig',  [
            'posts' => $posts,
            'nbrPost' => $nbrPost,
        ]);
    }
}
