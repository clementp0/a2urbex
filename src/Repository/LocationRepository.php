<?php

namespace App\Repository;
use App\Entity\Location;
use App\Class\Search;
use App\Entity\Country;
use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
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

    /**
     * @return Location[]
     */
    
    public function findWithSearch(Search $search)
        {
        $query = $this
            ->createQueryBuilder('l');


            if (!empty($search->country && $search->type )){
                $query = $query
                    // ->join('l.country', 'c')
                    // ->join('l.type', 't')
                    ->andWhere('c.id IN (:country)')
                    ->andWhere('t.id IN (:type)')
                    ->setParameters(array(
                        'type' => $search->type,
                        'country'=> $search->country
                    ));
            }

            if (!empty($search->country)){
                $query = $query
                    ->select('c' , 'l')
                    ->join( 'l.country', 'c')
                    ->andWhere('c.id IN (:country)')
                    ->setParameter('country', $search->country);
            }

            if (!empty($search->string)){
                $query = $query
                    ->andWhere('l.description LIKE :string')
                    ->setParameter('string', "%$search->string%");
            }

            if (!empty($search->type)){
                $query = $query
                    ->select('t' , 'l')
                    ->join( 'l.type', 't')
                    ->andWhere('t.id IN (:type)')
                    ->setParameter('type', $search->type);
            }
            // echo($query);
            return $query->getQuery()->getResult();
    }




    // /**
    //  * @return Location[] Returns an array of Location objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Location
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
