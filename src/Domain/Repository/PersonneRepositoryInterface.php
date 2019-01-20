<?php

namespace App\Domain\Repository;

use App\Domain\Personne;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
interface PersonneRepositoryInterface
{
    /**
     * @return Personne[]
     */
    public function getAll();

    /**
     * @param string $email
     *
     * @return Personne
     */
    public function get(string $email);

    /**
     * @param Personne $personne
     *
     * @return Personne
     */
    public function save(Personne $personne);

    /**
     * @param string $email
     *
     * @return bool
     */
    public function emailAlreadyExist(string $email);

    /**
     * @param Personne $personne
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @return bool
     */
    public function absenceAlreadyExist(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin);
}
