<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Commande>
 *
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function save(Commande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Commande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function commandeVendeur(User $vendeur)
    {

        $em = $this->getEntityManager();


        $query = $em->createQueryBuilder();
        $query->select('c,p,u') // Sélectionnez les commandes (c), les produits (p), et les utilisateurs (u)
            ->from(Commande::class, 'c') // Spécifiez l'entité principale Commande et donnez-lui l'alias 'c'
            ->leftJoin('c.produit', 'p') // Effectuez une jointure avec l'entité Produit associée
            ->leftJoin('p.user', 'u') // Effectuez une jointure avec l'entité User associée au Produit
            ->where('p.isSelling = 1')
            ->andWhere('u = :vendeur') // Utilisez le paramètre nommé :vendeur
            ->orderBy('c.dateCommande', 'DESC')
            ->setParameter('vendeur', $vendeur);

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return Commande[] Returns an array of Commande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
