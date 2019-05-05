<?php

namespace App\Domain;

use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceCompteur
{
    /**
     * Absence gèrée par compteur ?
     *
     * Lors d'un dépôt d'absence on vérifie que la personne possede un nombre
     * de jours restants suffisants @see AbsenceCompteur::$joursDisponibles.
     * Si les jours disponible sont insuffisants, l'absence ne peut pas être
     * déposée.
     */
    const TYPES_COMPTEUR = [
        AbsenceType::CONGES_PAYES,
        AbsenceType::TELETRAVAIL,
    ];

    /**
     * Compteur doit être incrémenté dès qu'une personne ait travaillé un
     * certain nombre de jours.
     */
    const PERIODE_INCREMENTER_COMPTEURS = [
        AbsenceType::CONGES_PAYES => 10,
        AbsenceType::TELETRAVAIL => 5,
    ];

    /**
     * Absences de ces types sont considerés comme des jours non travaillé.
     */
    const TYPES_ABSENT = [
        AbsenceType::MALADIE,
        AbsenceType::CONGES_PAYES,
    ];

    /**
     * Identité d'un compteur autogénérée.
     *
     * @var int
     */
    private $id;

    /**
     * @var Personne
     */
    private $personne;

    /**
     * @var AbsenceType
     *
     * @see AbsenceType::COMPTEUR
     */
    private $type;

    /**
     * @var int
     */
    private $joursDisponibles = 0;

    /**
     * @var int
     */
    private $joursTravailles = 0;

    /**
     * @param Personne $personne
     * @param int      $type
     */
    public function __construct(Personne $personne, int $type)
    {
        $this->personne = $personne;
        $this->type = new AbsenceType($type);
    }

    /**
     * @return AbsenceType
     */
    public function getType(): AbsenceType
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getJoursDisponibles(): int
    {
        return $this->joursDisponibles;
    }

    /**
     * @return int
     */
    public function getJoursTravailles(): int
    {
        return $this->joursTravailles;
    }

    /**
     * Incrementer le nombre des jours travaillés.
     */
    public function incrementerJoursTravailles()
    {
        ++$this->joursTravailles;
        if ($this->joursTravailles >= self::PERIODE_INCREMENTER_COMPTEURS[$this->type->getType()]) {
            $this->joursTravailles = 0;
            ++$this->joursDisponibles;
        }
    }

    /**
     * @param int $jours
     *
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function deposerAbsence(int $jours)
    {
        if ($this->joursDisponibles < $jours) {
            throw new AbsenceJoursDisponiblesInsuffisantsException('Il ne vous reste plus de jours disponibles pour ce type d\'absence');
        }

        $this->joursDisponibles -= $jours;
    }

    /**
     * @param int $jours
     */
    public function annulerAbsence(int $jours)
    {
        $this->joursDisponibles += $jours;
    }
}
