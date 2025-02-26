<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FichierRepository;

final class FichierController extends AbstractController
{
    #[Route('/liste-fichier', name: 'app_liste-fichier')]
    public function index(FichierRepository $fichierRepository): Response
    {
        $fichiers = $fichierRepository->findAll();
        return $this->render('fichier/liste-fichier.html.twig', [
            'fichiers' => $fichiers
        ]);
    }

    // Route pour télécharger un fichier
    #[Route('/fichier/télécharger/{filename}', name: 'app_download')]
    public function download(string $filename): Response
    {
        $uploadDirectory = $this->getParameter('upload_directory');
        $filePath = $uploadDirectory . '/' . $filename;

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }

        // Créer une réponse pour le téléchargement
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition('attachment', $filename);

        return $response;
    }
}
