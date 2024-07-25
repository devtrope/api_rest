<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request, 
    JWTTokenManagerInterface $JWTManager, 
    UserPasswordHasherInterface $passwordHasher, 
    EntityManagerInterface $entityManager): JsonResponse
    {
        $credentials = json_decode($request->getContent());

        if (! isset($credentials->email) || ! isset($credentials->password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $email = $credentials->email;
        $password = $credentials->password;

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (! $user || ! $passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        return $this->json(['token' => $JWTManager->create($user)]);
    }
}
