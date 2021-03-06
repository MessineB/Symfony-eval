<?php

namespace App\Controller;

use App\Repository\HashtagRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search', methods:['GET', 'POST'])]
    public function index(Request $rq, PostRepository $pr, HashtagRepository $hashtagRepo): Response
    {

        $word = $rq->get("search");

        $posts= $pr->search($word);

        return $this->render('search/index.html.twig', [
            'posts' => $posts,
            'hashtags' => $hashtagRepo->findByCount()
        ]);
    }
}
