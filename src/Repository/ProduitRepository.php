<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function annonceInactif()
{
    
    $em = $this->getEntityManager();
 

    $query = $em->createQueryBuilder();
    $query->select('p')
        ->from(Produit::class, 'p')
        ->where('p.etat = 0')
        ->orderBy('p.dateCreationProduit', 'DESC');

    return $query->getQuery()->getResult();
}

public function annonceActif()
{
    
    $em = $this->getEntityManager();
 

    $query = $em->createQueryBuilder();
    $query->select('p')
        ->from(Produit::class, 'p')
        ->where('p.etat = 1')
        ->orderBy('p.dateCreationProduit', 'DESC');

    return $query->getQuery()->getResult();
}

public function search($mots= null, $categorie = null){ // recherche les produits
    $query = $this->createQueryBuilder('p');
    $query->where('p.etat = 1');

    if($mots != null){
        $query->andWhere('MATCH_AGAINST(p.nomProduit, p.description) AGAINST (:mots boolean)>0')->setParameter('mots', $mots);
    }
    if($categorie != null){
        $query->leftJoin('p.categorie', 'c')
        ->andWhere('c.id = :id')
        ->setParameter('id', $categorie);
    }

    return $query->getQuery()->getResult();


}



//    /**
//     * @return Produit[] Returns an array of Produit objects
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

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
