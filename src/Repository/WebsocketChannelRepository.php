<?php

namespace App\Repository;

use App\Entity\WebsocketChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsocketChannel>
 *
 * @method WebsocketChannel|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebsocketChannel|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebsocketChannel[]    findAll()
 * @method WebsocketChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebsocketChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsocketChannel::class);
    }

    public function save(WebsocketChannel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WebsocketChannel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
