<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/accueil/{page?1}', name: 'app_home', methods: ['POST','GET'])]
    #[IsGranted("ROLE_USER")]
    public function home(PostRepository $postRepository, ManagerRegistry $doctrine, Request $request, $page): Response
    {

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $post->setUser($user);
            $post->setStatus(["published"]);
            $post->setCreatedAt(new \DateTime());

            #Traitement des images
            if ($picture = $form->get("picture")->getData()) {
                $namePicture = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $namePicture = str_replace(" ", "_", $namePicture);
                $namePicture .= uniqid() . "." . $picture->guessExtension();
                $picture->move($this->getParameter("images"), $namePicture);
                
                $post->setPicture($namePicture);
            } 

            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Votre post a été créé avec succès !');
        }

        /*  if ($form->isSubmitted() && $form->getErrors()) {
            $this->addFlash('warning', 'Vérifiez d\'avoir remplis tous les champs requis');
        } */

        //pour la pagination
        $nbrParPage = 3;
        $array = [];
        $array = $postRepository->findAll();
        $nbrPost = count($array);
        $nbrPage = ceil($nbrPost / $nbrParPage);

        return $this->render('home/index.html.twig', [
            'addPost' => $form->createView(),
            'posts' => $postRepository->findBy([],[],$nbrParPage,($page - 1) * $nbrParPage),
            'nbrPage' => $nbrPage,
            'page' => $page
        ]);
    }
}
