<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    // Exemple de méthode personnalisée pour récupérer un favori d'un utilisateur et d'une annonce
    public function findByUserAndAnnonce($user, $annonce)
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.annonce = :annonce')
            ->setParameter('user', $user)
            ->setParameter('annonce', $annonce)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
