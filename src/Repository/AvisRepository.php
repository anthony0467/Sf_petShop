<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Avis>
 *
 * @method Avis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avis[]    findAll()
 * @method Avis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function save(Avis $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Avis $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function actifAvisVendor($vendorId)
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->select('a')
            ->from(Avis::class, 'a')
            ->where('a.actif = 1')
            ->andWhere('a.Vendeur = :vendorId')
            ->andWhere('a.parent IS NULL')
            ->orderBy('a.dateAvis', 'ASC')
            ->setParameter('vendorId', $vendorId);

        return $query->getQuery()->getResult();
    }


    public function inactifAvis()
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->select('a')
            ->from(Avis::class, 'a')
            ->where('a.actif = 0')
            ->orderBy('a.dateAvis', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function actifAvis()
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->select('a')
            ->from(Avis::class, 'a')
            ->where('a.actif = 1')
            ->orderBy('a.dateAvis', 'ASC');

        return $query->getQuery()->getResult();
    }

    public function actifReplyParent($parentId)
    {
        $em = $this->getEntityManager();

        $query = $em->createQueryBuilder();
        $query->select('a')
            ->from(Avis::class, 'a')
            ->where('a.actif = 1')
            ->andWhere('a.Parent = :parentId')
            ->orderBy('a.dateAvis', 'ASC')
            ->setParameter('parentId', $parentId);

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return Avis[] Returns an array of Avis objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Avis
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
