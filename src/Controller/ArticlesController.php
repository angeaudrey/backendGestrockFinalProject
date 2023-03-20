<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Articles;
use App\Service\FileUploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\ArticlesRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
 use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\PathPackage;

use Symfony\Component\HttpFoundation\RequestStack;

class ArticlesController extends AbstractController
{

    private $doctrine;
    private $serializer;
private $requestStack;
    public function __construct(
        ManagerRegistry $doctrine,SerializerInterface $serializer,RequestStack $requestStack
        )
    {
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;


    }
   
    #[Route('/articles', name: 'app_articles')]
    public function index(): Response
    {
        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
        ]);
    }

    #[Route('/articles/liste', name: 'app_articles_liste', methods: ['GET'])]
    public function indexarticle(ArticlesRepository $articleRepository): Response
    {
           $articlelsite= $articleRepository->findAll();
  
 
           $request = $this->requestStack->getCurrentRequest();
           $host = $request->getSchemeAndHttpHost(); // ex: http://localhost:8000
           $basePath = $request->getBasePath(); // 
 
        $retour = array();
        foreach($articlelsite as $cat){
            $retour[] = array(
                'id' => $cat->getId(),
                'designation' => $cat->getDesignation(),
                'description' => $cat->getDescription(),
                'photo' =>  $host.'/uploads/img/'.$cat->getPhoto(),
                //'statut' => $cat->getStatut(),
                'datecreation' => $cat->getDatecreation(),
                'montantestimation' => $cat->getMontantestimation(),
                
               
            );
        }
        $response = [
            'code' => 200,
            'error' => false,
            'data' => $retour,
        ];
        
         return new Response($this->serializer->serialize($retour, "json"));
 
    }

    #[Route('/api/articles/add', name: 'app_articles_add', methods: ['POST'])]
    public function addarticle(Request $request, EntityManagerInterface $add ,SerializerInterface $serializer,UserPasswordHasherInterface $passwordHasher ,UserRepository $userRepository,CategoryRepository $categoryRepository,FileUploader $fileUploader): Response
    {
        $data = json_decode($request->getContent(), true ) ?? $_POST;
 
 
        $articles = new Articles();
        $articles->setDesignation($data['designation']);
        $articles->setDescription($data['description']);
       // $articles->setPhoto($data['photo']);
        $fileName2 = $this->upload_files('img', $request->files->get('image'), $fileUploader);
        $articles->setPhoto($fileName2);
        $articles->setMontantestimation($data['montantestimation']);
        $articles->setStatut($data['statut']);
        $articles->setDatecreation(new \DateTime());
        $userId = $this->getUser()->getId();

        $user = $userRepository->find($userId);

        $categorie = $categoryRepository->find($data['idcategorie']);
        $articles->setUser($user);
        $articles->setCategorie($categorie);
        $add->persist($articles);
        $add->flush();
        $response = [
            'code' => 200,
            'error' => false,
            'data' => "success",
        ];
        
         return new Response($this->serializer->serialize($response, "json"));
 
    }


    #[Route('/api/detailarticle/{id}',name:'article_detail',methods:['GET'])]
    public function detailarticle(Request $request,ArticlesRepository $articleRepository,$id) 
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $request->getSchemeAndHttpHost(); // ex: http://localhost:8000
        $basePath = $request->getBasePath(); // 
        $articlesingle =  $articleRepository->findby(['id' => $id]);

        $retour = array(); 
                $retour[] = array(
            'id' => $articlesingle[0]->getId(),
            'designation' => $articlesingle[0]->getDesignation(),
            'description' => $articlesingle[0]->getDescription(),
            'photo' =>  $host.'/uploads/img/'.$articlesingle[0]->getPhoto(),
            //'statut' => $cat->getStatut(),
            'datecreation' => $articlesingle[0]->getDatecreation(),
            'montantestimation' => $articlesingle[0]->getMontantestimation(),
            
           
        );
            

        
         
        $response = [
            'code' => 200,
            'error' => false,
            'data' => $retour,
        ];
        

        
        return new Response($this->serializer->serialize($retour, "json"));

        
    }
    #[Route('/articles/rechercher', name: 'app_articles_recherche', methods: ['GET'])]
    public function searcharticle(Request $request, EntityManagerInterface $add ,SerializerInterface $serializer,UserPasswordHasherInterface $passwordHasher ,UserRepository $userRepository,CategoryRepository $categoryRepository,FileUploader $fileUploader,ArticlesRepository $articleRepository): Response
    {
        $query = $request->query->get('q');
        $query1 = $request->query->get('q1');

        $request = $this->requestStack->getCurrentRequest();
        $host = $request->getSchemeAndHttpHost(); // ex: http://localhost:8000
        $basePath = $request->getBasePath(); // 
       // die();
        $articlelsite= $articleRepository->search($query, intval($query1));
  
 
       // dump($articlelsite);
        
       $retour = array();
       foreach($articlelsite as $cat){
           $retour[] = array(
               'id' => $cat->getId(),
               'designation' => $cat->getDesignation(),
               'description' => $cat->getDescription(),
               'photo' =>  $host.'/uploads/img/'.$cat->getPhoto(),
               //'statut' => $cat->getStatut(),
               'datecreation' => $cat->getDatecreation(),
               'montantestimation' => $cat->getMontantestimation(),
               
              
           );
       }
       $response = [
           'code' => 200,
           'error' => false,
           'data' => $retour,
       ];
       
        return new Response($this->serializer->serialize($retour, "json"));
    }

    public function upload_files($folder, $file, FileUploader $fileUploader) {
        $ds = DIRECTORY_SEPARATOR;
        $uploadDir = $this->getParameter('kernel.project_dir')
            . $ds."public".$ds."uploads".$ds.$folder.$ds;
        $fileName = '';
        if ($file) {
            $fileUploader->setTargetDirectory($uploadDir);
            $fileName = $fileUploader->upload($file);
        }
        return $fileName;
    }
}