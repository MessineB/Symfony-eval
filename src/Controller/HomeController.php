<?php

namespace App\Controller;

use App\Entity\Hashtag;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\PostLike;
use App\Repository\HashtagRepository;
use App\Repository\PostRepository;
use App\Repository\PostLikeRepository;
use Doctrine\Persistence\ObjectManager;
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
    public function home(PostRepository $postRepository, ManagerRegistry $doctrine, Request $request, HashtagRepository $hashtagRepo, $page): Response
    {

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $post->setUser($user);
            $post->setStatus(["published"]);
            $post->setCreatedAt(new \DateTime());
            
            #On parse l'input contenu du formulaire pour ne récupérer que les #
            $content = $form->get('content')->getData();
            preg_match_all("/(#\w+)/u", $content, $result);

        
            foreach($result[0] as $value){
                
                $hashtag = new Hashtag();
                
                $oldHashtag = $hashtagRepo->findByContent($value);

                if($oldHashtag != null){

                    $countHashtag = $oldHashtag[0]->getCount();
                    $countHashtag += 1;
                    $oldHashtag[0]->setCount($countHashtag);

                }else{

                    $hashtag->setContent($value);
                    $hashtag->setCount(1);

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($hashtag);
                    $entityManager->flush();

                }
                
            }

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

        //Pour la pagination
        $nbrParPage = 3;
        $array = [];
        $array = $postRepository->findAll();
        $nbrPost = count($array);
        $nbrPage = ceil($nbrPost / $nbrParPage);

        return $this->render('home/index.html.twig', [
            'addPost' => $form->createView(),
            'posts' => $postRepository->findBy([],[],$nbrParPage,($page - 1) * $nbrParPage),
            'nbrPage' => $nbrPage,
            'page' => $page,
            'hashtags' => $hashtagRepo->findByCount()
        ]);
    }

    /**
     * Permet de like un post
     * 
     * @param Post $post
     * @param ObjectManager $manager
     * @param PostLikeRepository $likeRepo
     * @return Response
     */
    #[Route('/post/{id}/like', name: 'post_like', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    //permet de liker ou unliker un post
    public function like(Post $post, ManagerRegistry $doctrine , PostLikeRepository $likeRepo) : Response  
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        if ($post->isLikedByUser($user)) {
            $like = $likeRepo->findOneBy([
                'post' => $post,
                'user' => $user
            ]);
           
            $entityManager->remove($like);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }
        
        $like = new PostLike() ;
        $like->setPost($post)->setUser($user);
        $entityManager->persist($like);
        $entityManager->flush();

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
