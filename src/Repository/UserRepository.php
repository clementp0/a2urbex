<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\FriendRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, private Security $security, private FriendRepository $friendRepository)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
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
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findAllButCurrent() {
        $user = $this->security->getUser();

        return $this->createQueryBuilder('u')
            ->orderBy('u.lastname', 'ASC')
            ->andWhere('u.id != '.($user ? $user->getId() : ''))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForSearch($search, $userId, $excludeFriends = false) {
        $q = $this->createQueryBuilder('u')
            ->select('u.id, u.firstname, SUBSTRING(u.lastname, 1, 1) lastname, CONCAT(u.firstname, \'#\', u.id) username')
            ->orderBy('u.firstname', 'ASC')
            ->andWhere('u.id != '. $userId)
            ->andWhere('CONCAT(u.firstname, \'#\', u.id) LIKE :search')
            ->setParameter('search', '%'.$search.'%')
        ;

        
        if($excludeFriends) {
            $friends = array_map(function($item) {
                return $item['id'];
            }, $this->friendRepository->findFriendForSearch($userId));

            $q->andWhere('u.id NOT IN ('.implode(', ', $friends).')');
        }

        return $q->setMaxResults(10)->getQuery()->getResult();
    }
}
