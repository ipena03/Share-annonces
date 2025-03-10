<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FichierRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;


use App\Entity\User; // Importer la classe User si nécessaire
use Doctrine\ORM\EntityManagerInterface; // Pour obtenir l'Entity Manager
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // Pour gérer les accès
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Pour gérer les exceptions HTTP

final class FichierController extends AbstractController
{
    #[Route('/liste-fichier', name: 'app_liste-fichier')]
    #[IsGranted('ROLE_USER')]
    public function index(FichierRepository $fichierRepository): Response
    {
        // Si l'utilisateur n'est pas un modérateur, redirigez-le vers ses propres fichiers
        if (!$this->isGranted('ROLE_MOD')) {
            return $this->redirectToRoute('app_fichier_utilisateur');
        }
        
        // Sinon, montrez tous les fichiers (pour les modérateurs)
        $fichiers = $fichierRepository->findAll();
        return $this->render('fichier/liste-fichier.html.twig', [
            'fichiers' => $fichiers,
            'is_admin' => true
        ]);
    }
    
    
    
    #[Route('/fichier/utilisateur', name: 'app_fichier_utilisateur')]
    #[IsGranted('ROLE_USER')]
    public function fichiersUtilisateur(FichierRepository $fichierRepository): Response
    {
        $user = $this->getUser();
        $fichiers = $fichierRepository->findByUser($user->getId());

        return $this->render('fichier/liste-fichier.html.twig', [
            'fichiers' => $fichiers
        ]);
    }

    #[Route('/fichier/télécharger/{id}', name: 'app_download')]
    public function download(int $id, FichierRepository $fichierRepository): Response
    {
        $fichier = $fichierRepository->find($id);
        
        if (!$fichier) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }
        
        // Vérifier que l'utilisateur est le propriétaire du fichier
        $this->denyAccessUnlessGranted('FILE_DOWNLOAD', $fichier);
        
        $filePath = $this->getParameter('upload_directory') . '/' . $fichier->getRouteFichier();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }
        
        return new BinaryFileResponse($filePath);
    }


#[Route('/fichier/supprimer/{id}', name: 'app_delete_fichier', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function delete(int $id, FichierRepository $fichierRepository, Request $request): Response
{
    $csrfToken = $request->request->get('_token');
    
    if (!$this->isCsrfTokenValid('delete_fichier_' . $id, $csrfToken)) {
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('app_fichier_utilisateur');
    }
    
    $fichier = $fichierRepository->find($id);
    
    if (!$fichier) {
        throw $this->createNotFoundException('Fichier introuvable.');
    }
    
    // Vérifier que l'utilisateur est le propriétaire du fichier
    $this->denyAccessUnlessGranted('FILE_DELETE', $fichier);
    
    $entityManager = $this->getDoctrine()->getManager();
    // Suppression du fichier physique
    $filePath = $this->getParameter('upload_directory') . '/' . $fichier->getRouteFichier();
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    $entityManager->remove($fichier);
    $entityManager->flush();
    
    $this->addFlash('success', 'Fichier supprimé avec succès.');
    return $this->redirectToRoute('app_fichier_utilisateur');
}

}
