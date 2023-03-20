<?php

namespace App\Controller;
use App\Entity\User;

use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_user')]
    public function index(UserRepository $userRepository,SerializerInterface $serializer): JsonResponse
    {
       $user = $userRepository->findAll();

       $userJson = $serializer->serialize($user,'json');
       return new JsonResponse($userJson,200,[],true);
    }

       #[Route('/user/create',name:'addUser',methods:['POST'])]
    public function addUser(Request $request, EntityManagerInterface $add ,SerializerInterface $serializer,UserPasswordHasherInterface $passwordHasher  ):Response  
    {

        //die("ijiji");

         $data = json_decode($request->getContent() , true);
          $user = new User();
          $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['nameplaintextPassword']
         );
  

         $user->setName($data['name']);
         $user->setUsername($data['username']);
         $user->setPhoneNumber($data['phone_number']);
         $user->setEmail($data['email']);
         // $user->setStatut($data[false]);
         $user->setPassword($hashedPassword);
         //$user = $serializer->deserialize($request->getContent(), User::class,'json');
         $add->persist($user);
         $add->flush();
        
        // $jsonUser = $serializer->serialize($user,'json');
         return new JsonResponse("OK", Response::HTTP_CREATED, [],true);

        //$response = new Response(json_encode($user));
        // $response
        //     ->headers
        //     ->set('Content-Type', 'application/json');
        // return $response;

    }
}
