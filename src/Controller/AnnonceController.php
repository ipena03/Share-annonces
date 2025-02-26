<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Annonce;
use App\Repository\AnnonceRepository;
use App\Form\ModifierAnnonceType;
use App\Form\SupprimerAnnonceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AnnonceController extends AbstractController
{
    #[Route('/liste-annonces', name: 'app_liste-annonces')]
    public function index(Request $request, AnnonceRepository $annonceRepository): Response
    {
        // Paramètres de filtrage
        $criteria = [];
    
        // Filtrer par type : location ou prestations
        if ($type = $request->query->get('type')) {
            if (in_array($type, ['location', 'prestation de service'])) {
                $criteria['type'] = $type;
            }
        }
    
        // Paramètres de tri
        $sortBy = $request->query->get('sort_by', 'date');
        $sortOrder = $request->query->get('sort_order', 'asc');
    
        // Recherche des annonces avec les critères et le tri
        $annonces = $annonceRepository->findByCriteriaAndSort($criteria, $sortBy, $sortOrder);
    
        return $this->render('annonce/liste-annonces.html.twig', [
            'annonces' => $annonces,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'type' => $type,
        ]);
    }

    #[Route('/modifier-annonces/{id}', name: 'app_modifier_annonces')]
    public function modifierCategorie(Request $request, Annonce $annonce, EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
    
        // Vérifie si l'annonce appartient à l'utilisateur connecté
        if ($annonce->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette annonce.');
        }
    
        $form = $this->createForm(ModifierAnnonceType::class, $annonce);
    
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($annonce);
                $em->flush();
                $this->addFlash('notice', 'Annonce modifiée');
    
                // Log modification
                $this->logAction('Annonce modifiée', $user->getUsername(), 'Annonce modifiée : ' . $annonce->getTitle());
    
                return $this->redirectToRoute('app_profile');
            }
        }
    
        return $this->render('annonce/modifier-annonces.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer-annonces/{id}', name: 'app_supprimer_annonces')]
    public function supprimerCategorie(Request $request, Annonce $annonce, EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
    
        // Vérifie si l'annonce appartient à l'utilisateur connecté
        if ($annonce->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette annonce.');
        }
    
        // Suppression de l'annonce
        if ($annonce != null) {
            $em->remove($annonce);
            $em->flush();
            $this->addFlash('notice', 'Annonce supprimée');

            // Log suppression
            $this->logAction('Annonce supprimée', $user->getUsername(), 'Annonce supprimée : ' . $annonce->getTitle());
        }
    
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/details-annonce/{id}', name: 'app_details_annonce')]
    public function detailsAnnonce(Annonce $annonce): Response
    {
        $user = $annonce->getUser(); // Récupère l'utilisateur associé à l'annonce
    
        return $this->render('annonce/details-annonce.html.twig', [
            'annonce' => $annonce,
            'user' => $user,
        ]);
    }

    // Méthode pour ajouter un log dans la base de données
    private function logAction(string $action, string $username, string $details): void
    {
        $em = $this->getDoctrine()->getManager();
        $log = new Log();
        $log->setAction($action);
        $log->setUsername($username);
        $log->setTimestamp(new \DateTime());
        $log->setDetails($details);

        // Enregistrement dans la base de données
        $em->persist($log);
        $em->flush();
    }
}
