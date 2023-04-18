<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friend>
 *
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    public function save(Friend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Friend $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPending($user) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.friend = :user')
            ->setParameter('user', $user)
            ->andWhere('f.pending = true')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findWaiting($user) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.pending = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFriends($user) {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->andWhere('f.pending = false')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFriendForSearch($user, $bypass = false) {
        $q = $this->createQueryBuilder('f')
            ->leftJoin('f.friend', 'ff')
            ->select('ff.id')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
        ;

        if(!$bypass) $q->andWhere('f.pending = 0');

        return $q->getQuery()->getResult();
    }
}
