<?php

namespace App\Domain\Repository;

use App\Domain\AbsenceImmutable;
use App\Domain\Personne;

/**
 * Le repository pour l'entité @see Personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
interface AbsenceRepositoryInterface
{
    /**
     * Vérifier s'il existe déjà une absence qui chevauche les dates fournies.
     *
     * @param Personne $personne
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @return bool
     */
    public function absenceAlreadyExist(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin): bool;

    /**
     * @param Personne $personne
     * @param \DateTimeImmutable $startPeriod
     * @param \DateTimeImmutable $endPeriod
     *
     * @return AbsenceImmutable[]
     */
    public function getImmutableAbsences(Personne $personne, \DateTimeImmutable $startPeriod, \DateTimeImmutable $endPeriod);
}
