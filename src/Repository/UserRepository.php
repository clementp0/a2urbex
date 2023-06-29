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
use App\Repository\FavoriteRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $registry, 
        private Security $security, 
        private FriendRepository $friendRepository,
        private FavoriteRepository $favoriteRepository
    )
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

    private function getSearchQuery($search, $userId = null) {
        $q = $this->createQueryBuilder('u')
            ->select('u.id, u.firstname, SUBSTRING(u.lastname, 1, 1) lastname, CONCAT(u.firstname, \'#\', u.id) username')
            ->orderBy('u.firstname', 'ASC')
            ->andWhere('CONCAT(u.firstname, \'#\', u.id) LIKE :search')
            ->setParameter('search', '%'.$search.'%')
        ;

        if($userId) $q->andWhere('u.id != '. $userId);

        return $q;
    }


    public function findForSearchFriend($search, $userId) {
        $q = $this->getSearchQuery($search, $userId);

        $f = $this->friendRepository->findFriendForSearch($userId, true);
        if($f) {
            $friends = array_map(function($item) {
                return $item['id'];
            }, $f);
            
            $q->andWhere('u.id NOT IN ('.implode(', ', $friends).')');
        }

        return $q->setMaxResults(10)->getQuery()->getResult();
    }

    public function findForSearchFav($search, $favId) {
        $q = $this->getSearchQuery($search);

        $u = $this->favoriteRepository->find($favId)->getUsers();
        if($u) {
            $users = [];
            foreach($u as $item) $users[] = $item->getId();

            $q->andWhere('u.id NOT IN ('.implode(', ', $users).')');
        }

        return $q->setMaxResults(10)->getQuery()->getResult();
    }

    public function findUsers($user = null, $friends = false) {
        $qb = $this->createQueryBuilder('u')->orderBy('u.lastActiveAt', 'DESC');

        if($user && $friends) {
            $users = [$user->getId()];
            $f = $this->friendRepository->findFriendForSearch($user->getId());
            if($f) foreach($f as $item) $users[] = $item['id'];

            $qb->andWhere('u.id IN ('.implode(', ', $users).')');
        }

        return $qb->getQuery()->getResult();
    }
}
