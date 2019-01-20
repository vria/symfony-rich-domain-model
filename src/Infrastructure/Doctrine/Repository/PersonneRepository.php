<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneRepository extends ServiceEntityRepository implements PersonneRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
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
    public function get(string $email): Personne
    {
        $personne = $this->find($email);
        $personneRepositoryReflProp = (new \ReflectionClass(Personne::class))->getProperty('personneRepository');
        $personneRepositoryReflProp->setAccessible(true);
        $personneRepositoryReflProp->setValue($personne, $this);

        return $personne;
    }

    /**
     * @inheritdoc
     */
    public function save(Personne $personne): void
    {
        $this->_em->persist($personne);
        $this->_em->flush($personne);
    }

    /**
     * @inheritdoc
     */
    public function emailAlreadyExist(string $email): bool
    {
        try {
            return $this->createQueryBuilder('p')
                    ->select('COUNT(p)')
                    ->andWhere('p.email = :email')
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
        } catch (NoResultException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function absenceAlreadyExist(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin): bool
    {
        try {
            return $this->createQueryBuilder('p')
                    ->select('COUNT(a)')
                    ->innerJoin('p.absences', 'a')
                    ->andWhere('p = :personne')
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
}
