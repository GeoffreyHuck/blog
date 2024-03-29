<?php
namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Theme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Theme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Theme[]    findAll()
 * @method Theme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Finds the themes for the menu.
     *
     * @param string $locale The locale.
     *
     * @return Theme[]
     */
    public function findForMenu(string $locale): array
    {
        $qb = $this->createQueryBuilder('t');

        $qb->select('t')
            ->innerJoin('t.language', 'l')
            ->where('l.code = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('t.position', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
