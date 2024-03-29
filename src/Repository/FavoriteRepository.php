<?php

namespace App\Repository;

use App\Entity\Favorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Favorite>
 *
 * @method Favorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Favorite[]    findAll()
 * @method Favorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Favorite::class);
    }

    public function save(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Favorite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function isOpen(){
        return $this->createQueryBuilder('f')
            ->select('f fav', 'COUNT(l) AS count')
            ->leftJoin('f.locations', 'l')
            ->leftJoin('f.users', 'u')
            ->groupBy('f.id')
            ->andWhere('f.share', 1)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByDefault() {
        return $this->getBaseQuery()
            ->getQuery()
            ->getResult()
        ;
    }


    public function findByEnabled() {
        return $this->getBaseQuery()
            ->andWhere('(f.disabled IS NULL OR f.disabled = 0)')
            ->getQuery()
            ->getResult()
        ;
    }

    private function getBaseQuery() {
        $userId = $this->security->getUser()->getId();

        return $this->createQueryBuilder('f')
            ->select('f fav', 'COUNT(l) AS count')
            ->leftJoin('f.locations', 'l')
            ->leftJoin('f.users', 'u')
            ->groupBy('f.id')
            ->andWhere('u.id = '.$userId)
        ;
    }
}
