<?php

namespace App\Repository;

use App\Entity\Fichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @extends ServiceEntityRepository<Fichier>
 */
class FichierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichier::class);
    }

    /**
     * Recherche les fichiers envoyés par un utilisateur donné
     * 
     * @param int $userId
     * @return Fichier[]
     */
// Dans src/Repository/FichierRepository.php
public function findByUser(int $userId)
{
    return $this->createQueryBuilder('f')
        ->andWhere('f.user = :userId')
        ->setParameter('userId', $userId)
        ->orderBy('f.id', 'DESC')
        ->getQuery()
        ->getResult();
}
    /**
     * Trouver un fichier par son nom exact
     * 
     * @param string $nom
     * @return Fichier|null
     * @throws NonUniqueResultException
     */
    public function findOneByNom(string $nom): ?Fichier
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter le nombre total de fichiers
     * 
     * @return int
     */
    public function countAllFichiers(): int
    {
        try {
            return (int) $this->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }
}
