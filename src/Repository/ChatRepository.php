<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Chat>
 *
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function save(Chat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Chat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBy2User($user1, $user2) {
        return $this->createQueryBuilder('c')
            ->join('c.chatUsers', 'cu1')
            ->join('c.chatUsers', 'cu2')
            ->andWhere('cu1.user = :user1')
            ->andWhere('cu2.user = :user2')
            ->andWhere('c.multi IS NULL OR c.multi = false')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByUser($user) {
        return $this->createQueryBuilder('c')
            ->join('c.chatUsers', 'cu')
            ->join('cu.user', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUsers($chat) {
        return $this->getEntityManager()->createQueryBuilder()->from(User::class, 'u')
            ->select('u')
            ->join('u.chatUsers', 'cu')
            ->join('cu.chat', 'c')
            ->where('c = :chat')
            ->setParameter('chat', $chat)
            ->getQuery()
            ->getResult()
        ;
    }

    public function containUser($chat, $user) {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(cu)')
            ->join('c.chatUsers', 'cu')
            ->andWhere('c = :chat')
            ->andWhere('cu.user = :user')
            ->setParameter('chat', $chat)
            ->setParameter('user', $user);

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result > 0;
    }
}
