<?php

namespace App\Repository;

use App\Entity\Upload;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Upload>
 *
 * @method Upload|null find($id, $lockMode = null, $lockVersion = null)
 * @method Upload|null findOneBy(array $criteria, array $orderBy = null)
 * @method Upload[]    findAll()
 * @method Upload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Upload::class);
    }

    /**
     * @return Upload[] Returns an array of Upload objects
     */
    public function findByUserWithCategory(User $user, string $category): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.category', 'c')
            ->andWhere('c.name = :category')
            ->andWhere('u.user = :user')
            ->setParameter('category', $category)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSizeAllFiles(User $user): int|null
    {
        return $this->getEntityManager()->createQuery('SELECT SUM(u.size) FROM ' . Upload::class . ' u WHERE u.user = :user')
                    ->setParameter('user', $user)
                    ->getSingleScalarResult()
                    ;
    }

    //    public function findOneBySomeField($value): ?Upload
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
