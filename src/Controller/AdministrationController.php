<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class AdministrationController extends AbstractController
{
    #[Route('/mod-liste-users', name: 'app_liste-users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('administrateur/liste-user.html.twig', [
            'users' => $users
        ]);
    }
}
