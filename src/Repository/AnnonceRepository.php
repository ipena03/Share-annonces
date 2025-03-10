<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Annonce>
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

    /**
     * Méthode pour filtrer les annonces par critères
     * 
     * @param array $criteria
     * @return Annonce[]
     */
    public function findByCriteriaAndSort(array $criteria, string $sortBy, string $sortOrder)
    {
        $qb = $this->createQueryBuilder('a');
    
        // Appliquer les filtres selon les critères
        if (!empty($criteria['type'])) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $criteria['type']);
        }
    
        // Appliquer le tri par date ou prix
        if ($sortBy == 'prix') {
            $qb->orderBy('a.prix', $sortOrder);
        } else {  // Tri par date (par défaut)
            $qb->orderBy('a.dateEnvoi', $sortOrder);
        }
    
        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les annonces les mieux notées
     * 
     * @param int $limit Nombre d'annonces à récupérer
     * @return Annonce[]
     */
    public function findTopRated(int $limit = 3): array
    {
        // Cette requête DQL va récupérer les annonces avec leur note moyenne
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            'SELECT a, AVG(r.rating) as avgRating
            FROM App\Entity\Annonce a
            JOIN a.ratings r
            GROUP BY a.id
            HAVING COUNT(r.id) > 0
            ORDER BY avgRating DESC'
        )->setMaxResults($limit);
        
        $results = $query->getResult();
        
        // Extraire juste les objets Annonce du résultat
        $annonces = [];
        foreach ($results as $result) {
            $annonces[] = $result[0]; // L'entité Annonce est à l'index 0
        }
        
        return $annonces;
    }
}