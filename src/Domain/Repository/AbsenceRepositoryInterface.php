<?php

namespace App\Domain\Repository;

use App\Domain\Absence;
use App\Domain\Exception\AbsenceNotFoundException;
use App\Domain\Personne;

/**
 * Le repository pour l'entité @see Absence.
 *
 * @internal
 *   Une répositoire qui peut être utilisée seulement dans @see Personne aggregate
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
interface AbsenceRepositoryInterface
{
    /**
     * Sauvegarder une absence.
     *
     * @param Absence $absence
     */
    public function save(Absence $absence): void;

    /**
     * Vérifier s'il existe déjà une absence qui chevauche les dates fournies.
     *
     * @param Personne           $personne
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     * @param $exclude = null
     *
     * @return bool
     */
    public function absenceDeposeDansPeriode(Personne $personne, \DateTimeImmutable $debut, \DateTimeImmutable $fin, $exclude = null): bool;

    /**
     * @param Personne           $personne
     * @param \DateTimeImmutable $date
     * @param array              $types
     *
     * @return bool
     */
    public function absenceDeposePourDate(Personne $personne, \DateTimeImmutable $date, array $types);

    /**
     * @param Personne $personne
     * @param $id
     *
     * @throws AbsenceNotFoundException
     *
     * @return Absence
     */
    public function getAbsence(Personne $personne, $id);

    /**
     * @param Personne           $personne
     * @param \DateTimeImmutable $startPeriod
     * @param \DateTimeImmutable $endPeriod
     *
     * @return Absence[]
     */
    public function getAbsences(Personne $personne, \DateTimeImmutable $startPeriod, \DateTimeImmutable $endPeriod);

    /**
     * @param Absence $absence
     *
     * @return void
     */
    public function annuler(Absence $absence);
}
