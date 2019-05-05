<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Absence;
use App\Domain\Exception\AbsenceNotFoundException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 *
 * @internal
 *   Une répositoire qui peut être utilisée seulement dans @see Personne aggregate
 */
class AbsenceRepository extends ServiceEntityRepository implements AbsenceRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Absence $absence): void
    {
        $this->_em->persist($absence);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function absenceDeposeDansPeriode(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin, $exclude = null): bool
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.personne = :personne')
            ->andWhere('a.debut <= :fin')
            ->andWhere(':debut <= a.fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('personne', $personne)
        ;

        if ($exclude) {
            $qb->andWhere('a != :exclude')
                ->setParameter('exclude', $exclude)
            ;
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function absenceDeposePourDate(Personne $personne, \DateTimeImmutable $date, array $types)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->andWhere('a.personne = :personne')
            ->andWhere('a.debut <= :date')
            ->andWhere(':date <= a.fin')
            ->andWhere('a.type in (:types)')
            ->setParameter('personne', $personne)
            ->setParameter('debut', $date)
            ->setParameter('types', $types)
        ;

        return (int) ($qb->getQuery()->getSingleScalarResult()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsence(Personne $personne, $id)
    {
        try {
            return $this->createQuerybuilder('a')
                ->andWhere('a.id = :id')
                ->andWhere('a.personne = :personne')
                ->setParameter('id', $id)
                ->setParameter('personne', $personne)
                ->getQuery()
                ->getSingleResult()
            ;
        } catch (NoResultException $e) {
            throw new AbsenceNotFoundException('Absence n\'est pas trouvée');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsences(Personne $personne, \DateTimeImmutable $startPeriod, \DateTimeImmutable $endPeriod)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.personne', 'p')
            ->andWhere('a.personne = :personne')
            ->andWhere('a.debut <= :end_period')
            ->andWhere('a.fin >= :start_period')
            ->setParameter('start_period', $startPeriod)
            ->setParameter('end_period', $endPeriod)
            ->setParameter('personne', $personne)
            ->orderBy('a.debut', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function annuler(Absence $absence)
    {
        $this->_em->remove($absence);
        $this->_em->flush();
    }
}
