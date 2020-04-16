<?php

namespace App\Repository;

use App\Entity\Achievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Achievement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Achievement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Achievement[]    findAll()
 * @method Achievement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achievement::class);
    }

    public function search(array $filters = [])
    {
        $builder = $this->createQueryBuilder('a');

        $builder->orderBy('a.name', 'ASC');

        if(!empty($filters['category'])){
            $builder
                ->andWhere('a.category = :category')
                ->setParameter('category', $filters['category'])
            ;
        }

        $query = $builder->getQuery();

        return $query->getResult();
    }
}
