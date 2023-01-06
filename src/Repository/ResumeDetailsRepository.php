<?php

namespace App\Repository;

use App\Entity\ResumeDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResumeDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResumeDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResumeDetails[]    findAll()
 * @method ResumeDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResumeDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResumeDetails::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ResumeDetails $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(ResumeDetails $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ResumeDetails[] Returns an array of ResumeDetails objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResumeDetails
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
