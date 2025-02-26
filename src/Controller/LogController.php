<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends AbstractController
{
    #[Route('/mod-admin/logs', name: 'admin_logs')]
    public function index(Request $request, LogRepository $logRepository): Response
    {
        // Récupérer les filtres depuis la requête
        $actionFilter = $request->query->get('action');
        $sortOrder = $request->query->get('sort_order', 'desc');  // Tri par défaut : 'desc'
        $sortBy = $request->query->get('sort_by', 'timestamp');  // Tri par défaut : 'timestamp'

        // Construire le critère de filtrage
        $criteria = [];
        if ($actionFilter) {
            $criteria['action'] = $actionFilter;
        }

        // Récupérer les logs filtrés et triés
        $logs = $logRepository->findBy($criteria, [$sortBy => $sortOrder]);

        return $this->render('administrateur/logs.html.twig', [
            'logs' => $logs,
            'actionFilter' => $actionFilter,
            'sort_order' => $sortOrder,
            'sort_by' => $sortBy
        ]);
    }
}
