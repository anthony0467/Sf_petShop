<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
