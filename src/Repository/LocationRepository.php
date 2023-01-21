<?php

namespace App\Repository;

use App\Entity\Location;
use App\Class\Search;
use App\Entity\Country;
use App\Entity\Type;

use App\Entity\User;
use App\Entity\Favorite;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Security\Core\Security;

/**
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Location::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Location $entity, bool $flush = true): void
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
    public function remove(Location $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByPid($pid) {
        return $this->createQueryBuilder('l')
            ->andWhere('l.pid = :pid')
            ->setParameter('pid', $pid)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findWithSearch(Search $search) {
        $query = $this->getBaseQuery();

        if (!empty($search->country)){
            $query = $query
            ->join( 'l.country', 'c')
            ->andWhere('c.id IN (:country)')
            ->setParameter('country', $search->country)
            ->addSelect('c');
        }

        if (!empty($search->type)){
            $query = $query
            ->join( 'l.type', 't')
            ->andWhere('t.id IN (:type)')
            ->setParameter('type', $search->type)
            ->addSelect('t');
        }

        if (!empty($search->string)){
            $query = $query
                ->andWhere('l.name LIKE :string')
                ->setParameter('string', "%$search->string%");
        }
        
        return $query->getQuery()->getResult();
    }

    public function findByAll() {
        return $this->getBaseQuery()->getQuery()->getResult();
    }

    public function findByIdFav($idFav) {
        return $this->getBaseQuery()
            ->andWhere('f.id = '.$idFav)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findById($id) {
        return $this->getBaseQuery()
            ->andWhere('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        ;
    }

    public function findBySource($source){
        return $this->getBaseQuery()
            ->andwhere('l.Source = :source')
            ->setParameter('source', $source)
            ->getQuery()
            ->execute();
        ;
    }

    private function getBaseQuery() {
        $user = $this->security->getUser();
        
        $qb = $this->createQueryBuilder('l')
            ->select('l loc')
            ->orderBy('l.id', 'ASC');
        
            if($user) {
            
                $qb
                    ->leftJoin('l.favorites', 'f')
                    ->leftJoin('f.users', 'u')
                    ->addSelect('GROUP_CONCAT(CASE WHEN u.id = :uid THEN f.id ELSE :null END) fids')
                    ->setParameter('null', NULL)
                    ->setParameter('uid', $user->getId())
                    ->groupBy('l.id');
            }
            else{
                $qb
                    ->leftJoin('l.favorites', 'f')
                    ->groupBy('l.id');
            }

        return  $qb;
    }
}

