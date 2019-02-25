<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Absence;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 *
 * @internal Used only by model classes in
 * @see Personne aggregate
 */
class AbsenceRepository extends ServiceEntityRepository implements AbsenceRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * @inheritdoc
     */
    public function absenceAlreadyExist(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin): bool
    {
        try {
            return $this->createQueryBuilder('a')
                    ->select('COUNT(a)')
                    ->andWhere('a.personne = :personne')
                    ->andWhere('a.debut <= :fin')
                    ->andWhere('a.fin >= :debut')
                    ->setParameter('debut', $debut)
                    ->setParameter('fin', $fin)
                    ->setParameter('personne', $personne)
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
        } catch (NoResultException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getImmutableAbsences(Personne $personne, \DateTimeImmutable $startPeriod, \DateTimeImmutable $endPeriod)
    {
        $absences = $this->createQueryBuilder('a')
            ->andWhere('a.personne = :personne')
            ->andWhere('a.debut <= :end_period')
            ->andWhere('a.fin >= :start_period')
            ->setParameter('start_period', $startPeriod)
            ->setParameter('end_period', $endPeriod)
            ->setParameter('personne', $personne)
            ->orderBy('a.debut')
            ->getQuery()
            ->getResult();

        return array_map(function(Absence $absence) {
            return $absence->getAbsenceImmutable();
        }, $absences);
    }
}
