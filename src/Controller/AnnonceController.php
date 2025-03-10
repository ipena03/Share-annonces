<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Annonce;
use App\Entity\Rating;  // Ajoute l'entité Rating
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
    private EntityManagerInterface $entityManager;

    // Injection de l'EntityManagerInterface via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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

    #[Route('/usermodifier-annonces/{id}', name: 'app_modifier_annonces')]
    public function modifierCategorie(Request $request, Annonce $annonce): Response
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
                $this->entityManager->persist($annonce);
                $this->entityManager->flush();
                $this->addFlash('notice', 'Annonce modifiée');

                // Log modification
                $this->logAction('modification_annonce', $user->getUserIdentifier(), 'Annonce modifiée : ' . $annonce->getNom());

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('annonce/modifier-annonces.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer-annonces/{id}', name: 'app_supprimer_annonces')]
    public function supprimerCategorie(Request $request, Annonce $annonce): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Vérifie si l'annonce appartient à l'utilisateur connecté
        if ($annonce->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette annonce.');
        }

        // Suppression de l'annonce
        if ($annonce != null) {
            $this->entityManager->remove($annonce);
            $this->entityManager->flush();
            $this->addFlash('notice', 'Annonce supprimée');

            // Log suppression - Utiliser getUserIdentifier() au lieu de getUsername()
            $this->logAction('suppression_annonce', $user->getUserIdentifier(), 'Annonce supprimée : ' . $annonce->getNom());
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/details-annonce/{id}', name: 'app_details_annonce')]
    public function detailsAnnonce(Annonce $annonce, Request $request): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
        
        // Vérifie si l'utilisateur a déjà évalué cette annonce
        $existingRating = $this->entityManager->getRepository(Rating::class)
            ->findOneBy(['announcement' => $annonce, 'user' => $user]);
    
        // Si une évaluation existe, on peut l'afficher et l'éditer
        if ($existingRating) {
            $ratingValue = $existingRating->getRating();
        } else {
            $ratingValue = null; // Si aucune évaluation, il n'y a pas de valeur de note
        }
    
        // Si l'utilisateur soumet une évaluation
        if ($request->isMethod('POST')) {
            $ratingValue = (int) $request->request->get('rating'); // Récupère la valeur de la note
            if ($ratingValue) {
                if ($existingRating) {
                    // Met à jour l'évaluation si elle existe déjà
                    $existingRating->setRating($ratingValue);
                } else {
                    // Sinon crée une nouvelle évaluation
                    $rating = new Rating();
                    $rating->setRating($ratingValue);
                    $rating->setAnnouncement($annonce);
                    $rating->setUser($user);  // Il faut ajouter un champ user dans l'entité Rating si ce n'est pas déjà fait.
                    $this->entityManager->persist($rating);
                }
    
                // Sauvegarde dans la base de données
                $this->entityManager->flush();
    
                $this->addFlash('notice', 'Votre évaluation a été mise à jour avec succès!');
            }
        }
    
        // Calcul de la moyenne des évaluations
        $ratings = $annonce->getRatings(); // Récupère toutes les évaluations de l'annonce
        $totalRatings = count($ratings);
        $averageRating = $totalRatings > 0 ? array_sum(array_map(fn($rating) => $rating->getRating(), $ratings->toArray())) / $totalRatings : 0;
    
        return $this->render('annonce/details-annonce.html.twig', [
            'annonce' => $annonce,
            'user' => $user,
            'rating' => $ratingValue,
            'averageRating' => $averageRating,
        ]);
    }

    #[Route('/supprimer-rating/{id}', name: 'app_supprimer_rating')]
    public function supprimerRating(Rating $rating): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté
    
        // Vérifie si l'évaluation appartient à l'utilisateur connecté
        if ($rating->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette évaluation.');
        }
    
        // Suppression de l'évaluation
        $this->entityManager->remove($rating);
        $this->entityManager->flush();
        $this->addFlash('notice', 'Évaluation supprimée');
    
        // Log suppression de l'évaluation
        $this->logAction('suppression_rating', $user->getUserIdentifier(), 'Évaluation supprimée pour l\'annonce : ' . $rating->getAnnouncement()->getNom());
    
        // Redirige vers la page des détails de l'annonce après la suppression de l'évaluation
        return $this->redirectToRoute('app_details_annonce', ['id' => $rating->getAnnouncement()->getId()]);
    }

    #[Route('/toggle-favorite/{annonceId}', name: 'app_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(int $annonceId): JsonResponse
    {
        return $this->json(['message' => 'Route atteinte', 'annonceId' => $annonceId]);
    }

    /**
     * Méthode pour enregistrer les actions de l'utilisateur
     *
     * @param string $action
     * @param string $username
     * @param string|null $details
     */
    public function logAction(string $action, string $username, string $details = null): void
    {
        // Création d'un nouvel enregistrement de log
        $log = new Log();
        $log->setAction($action);
        $log->setUsername($username);
        $log->setTimestamp(new \DateTime());  // Enregistrer le timestamp actuel
        $log->setDetails($details);

        // Sauvegarde dans la base de données
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
