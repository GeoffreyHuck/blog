<?php

namespace App\Repository;

use App\Entity\Comment;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Gets for an url.
     *
     * @param string $url The url.
     *
     * @return Comment[]
     */
    public function getForUrl(string $url): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, cc')
            ->leftJoin('c.children', 'cc')
            ->andWhere('c.url = :url')
            ->andWhere('c.status = :statusNew OR c.status = :statusNotified')
            ->setParameter('url', $url)
            ->setParameter('statusNew', Comment::STATUS_NEW)
            ->setParameter('statusNotified', Comment::STATUS_NOTIFIED)
            ->orderBy('c.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets for a reply.
     *
     * @param string $url The url.
     * @param string $reply The reply.
     *
     * @return Comment|null
     */
    public function getForReply(string $url, string $reply): ?Comment
    {
        // reply should be in the form : "AUTHOR NAME on DD/MM/YYYY HH:II:SS"
        $matches = [];
        if (preg_match('#^(.+) on (\d\d/\d\d/\d\d\d\d \d\d:\d\d:\d\d)$#', $reply, $matches)) {
            try {
                $qb = $this->createQueryBuilder('c')
                    ->andWhere('c.url = :url')
                    ->andWhere('c.author = :author')
                    ->andWhere('c.createdAt = :createdAt')
                    ->setParameter('url', $url)
                    ->setParameter('author', $matches[1])
                    ->setParameter('createdAt', DateTime::createFromFormat('d/m/Y H:i:s', $matches[2]))
                    ->setMaxResults(1);

                return $qb->getQuery()->getOneOrNullResult();
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }
}
