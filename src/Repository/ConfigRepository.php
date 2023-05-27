<?php

namespace App\Repository;

use App\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Config>
 *
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    public function save(Config $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Config $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function get($type, $name = null) {
        if($name === null) {
            return $this->findBy(['type' => $type]);
        } else {
            return $this->findOneBy(['name' => $name, 'type' => $type]);
        }
    }

    public function set($type, $name, $value) {
        $config = $this->get($type, $name);
        if(!$config) {
            $config = new Config();
            $config->setName($name)->setType($type);
        }
        $config->setValue($value);

        $this->save($config, true);
    }
    public function setArray($type, $items) {
        foreach($items as $name => $value) {
            $this->set($type, $name, $value);
        }
    }
}
