<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Absence;
use App\Domain\Exception\PersonneNotFoundException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
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
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @inheritdoc
     */
    public function __construct(ManagerRegistry $registry, AbsenceRepositoryInterface $absenceRepository)
    {
        parent::__construct($registry, Personne::class);

        $this->absenceRepository = $absenceRepository;
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
        if (!$personne instanceof Personne) {
            throw new PersonneNotFoundException('Personne '.$email." n'est pas trouvée");
        }

        $personneRepositoryReflProp = (new \ReflectionClass(Personne::class))->getProperty('personneRepository');
        $personneRepositoryReflProp->setAccessible(true);
        $personneRepositoryReflProp->setValue($personne, $this);

        $personneRepositoryReflProp = (new \ReflectionClass(Personne::class))->getProperty('absenceRepository');
        $personneRepositoryReflProp->setAccessible(true);
        $personneRepositoryReflProp->setValue($personne, $this->absenceRepository);

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
}
