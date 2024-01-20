<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function getAll($filters = [], $isAdmin = false)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->leftJoin('a.themes', 't');

        if (isset($filters['theme'])) {
            $qb->andWhere('t.id = :themeId')
                ->setParameter('themeId', $filters['theme']->getId());
        }

        if (isset($filters['language']) && !$isAdmin) {
            $qb->leftJoin('a.language', 'l')
                ->andWhere('l.code = :language')
                ->setParameter('language', $filters['language']);
        }

        if ($isAdmin) {
            $qb->addOrderBy('CASE WHEN a.publishedAt IS NULL THEN 0 ELSE 1 END', 'ASC');
        } else {
            $qb->andWhere('t.id IS NOT NULL');
            $qb->andWhere('a.publishedAt IS NOT NULL');
        }

        $qb->addOrderBy('a.publishedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
