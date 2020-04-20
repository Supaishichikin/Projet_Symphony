<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function search( $filter = '')
    {
        $builder = $this->createQueryBuilder('u');
        $builder->orderBy('u.pseudo', 'ASC');

        if($filter != '') {
            $builder
                ->andWhere('u.pseudo LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%')
                ->setMaxResults(10);
        }



        $query = $builder->getQuery();

        return $query->getResult();
    }
}
