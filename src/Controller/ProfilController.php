<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\AnnonceRepository;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Vérifie que l'utilisateur est connecté
    public function index(AnnonceRepository $annonceRepository): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Récupère les annonces de l'utilisateur connecté
        $annonces = $annonceRepository->findBy(['user' => $user]);

        return $this->render('profil/profil.html.twig', [
            'user' => $user,
            'annonces' => $annonces, // Passe les annonces à la vue
        ]);
    }
}
