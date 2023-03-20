<?php

namespace App\Controller;

use App\Entity\Proposition;
use App\Repository\ArticlesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;


class PropositionController extends AbstractController
{
    #[Route('/proposition', name: 'app_proposition')]
    public function index(): Response
    {
        return $this->render('proposition/index.html.twig', [
            'controller_name' => 'PropositionController',
        ]);
    }

    #[Route('/addproposition', name: 'app_proposition_add')]
    public function indexproposition(Request $request, EntityManagerInterface $add ,SerializerInterface $serializer,UserRepository $userReposirory ,ArticlesRepository $articleRepository): Response
    {
      $data = json_decode($request->getContent() , true);

      $propo = new Proposition();
      $propo->setDateproposition(new \DateTime('now'));
      $propo->setMesssage($data['message']);
      $propo->setEtatproposition($data['etatproposition']);	
      $userquidemande = $userReposirory->find($data['userquidemande']);
      $propo->setUserquidemande($userquidemande);
      $userquirecois = $userReposirory->find($data['userquirecois']);
      $propo->setUserquirecois($userquirecois);
      $articledemande = $articleRepository->find($data['articledemande']);
      $propo->setArticledemande($articledemande);
      $articlequirecoistrue = $articleRepository->find($data['articlequirecoistrue']);
      $propo->setArticlequirecoistrue($articlequirecoistrue);
      $add->persist($propo);
      $add->flush();
     
      return new JsonResponse("OK", Response::HTTP_CREATED, [],true);


    
    }
}
