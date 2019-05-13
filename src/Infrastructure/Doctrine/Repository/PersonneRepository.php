<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Exception\PersonneNonTrouveeException;
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
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllInfo(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.email, p.nom')
            ->orderBy('p.email', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * Implementation de @see PersonneRepositoryInterface::get().
     * {@inheritdoc}
     */
    public function get(string $email): Personne
    {
        $personne = $this->find($email);
        if (!$personne instanceof Personne) {
            throw new PersonneNonTrouveeException('Personne '.$email." n'est pas trouvée");
        }

        return $personne;
    }

    /**
     * Implementation de @see PersonneRepositoryInterface::save().
     * {@inheritdoc}
     */
    public function save(Personne $personne): void
    {
        $this->_em->persist($personne);
        $this->_em->flush();
    }

    /**
     * Implementation de @see PersonneRepositoryInterface::emailAlreadyExist().
     * {@inheritdoc}
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
