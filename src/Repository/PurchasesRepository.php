<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Purchases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchases>
 */
class PurchasesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchases::class);
    }

    //    /**
    //     * @return Purchases[] Returns an array of Purchases objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Purchases
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByCommande(Commande $commande)
    {
        return $this->createQueryBuilder('p')
            ->join('p.commande', 'c') // Assurez-vous que "commandes" est le nom de la propriété dans votre entité Produit qui fait référence à Commande
            ->where('c.id = :commande_id')
            ->setParameter('commande_id', $commande->getId())
            ->getQuery()
            ->getResult();
    }
}
