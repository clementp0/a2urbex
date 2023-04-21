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

    public function findPidDuplicate() {
        return $this->createQueryBuilder('l')
            ->select('GROUP_CONCAT(l.id) AS ids')
            ->andWhere('l.pid IS NOT NULL')
            ->groupby('l.pid')
            ->having('COUNT(l.id) > 1')
            ->getQuery()
            ->getResult();
        ;
    }

    public function findByPid($pid) {
        return $this->createQueryBuilder('l')
            ->andWhere('l.pid = :pid')
            ->setParameter('pid', $pid)
            ->getQuery()
            ->getOneOrNullResult();
    }


    private function getBaseSearch(Search $search) {
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

        if (!empty($search->source)) {
            $sources = [];
            $other = false;
            foreach($search->source as $item) {
                if($item === '0') $other = true;
                else $sources[] = $item;
            }

            $query = $query
                ->andWhere('l.source IN (:sources)'.($other ? ' OR l.source IS NULL' : ''))
                ->setParameter('sources', $sources)
            ;
        }

        return $query;
    }

    public function findWithSearch(Search $search) {
        return $this->getBaseSearch($search)->getQuery()->getResult();
    }

    public function findWithSearchAndUsers(Search $search, $users) {
        $q = $this->getBaseSearch($search);
        $q->andWhere('l.user IN ('.implode(', ', $users).')');

        return $q->getQuery()->getResult();
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
            ->andWhere('l.source = :source')
            ->setParameter('source', $source)
            ->getQuery()
            ->execute();
        ;
    }

    public function findByUser(){
        $user = $this->security->getUser();
        return $this->getBaseQuery()
            ->andWhere('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
        ;
    }

    public function findByUsers($users) {
        return $this->getBaseQuery()
            ->andWhere('l.user IN ('.implode(', ', $users).')')
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
                    ->groupBy('l.id')
                    ->orderBy('l.id', 'DESC');
            }
            else{
                $qb
                    ->leftJoin('l.favorites', 'f')
                    ->groupBy('l.id');
            }

        return  $qb;
    }

    public function findAllSource() {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT(l.source)')
            ->getQuery()
            ->execute()    
        ;
    }

    public function findAllNoCountry() {
        return $this->createQueryBuilder('l')
            ->andWhere('l.country IS NULL')
            ->getQuery()
            ->execute()
        ;
    }
}

