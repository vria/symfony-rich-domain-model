<?php

namespace App\Domain;
use App\Domain\Exception\AbsenceInvalidDatesException;
use App\Domain\Exception\AbsenceTypeInvalidException;

/**
 * Une absence déposée.
 * C'est une entité qui est cachée dans un agrégat dont la racine est @see Personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class Absence
{
    /**
     * Identité d'une absence qui est autogénérée.
     *
     * @var int
     */
    private $id;

    /**
     * Personne pour laquelle cette absence a été déposée.
     *
     * @var Personne
     */
    private $personne;

    /**
     * Type d'absence.
     *
     * @var AbsenceType
     */
    private $type;

    /**
     * Date de début.
     *
     * @var \DateTimeImmutable
     */
    private $debut;

    /**
     * Date de fin.
     *
     * @var \DateTimeImmutable
     */
    private $fin;

    /**
     * @param Personne $personne
     * @param int $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceInvalidDatesException
     * @throws AbsenceTypeInvalidException
     */
    public function __construct(Personne $personne, int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        if ($debut > $fin) {
            throw new AbsenceInvalidDatesException('Date de fin doit être après la date de début');
        }

        $this->personne = $personne;
        $this->type = new AbsenceType($type);
        $this->debut = $debut;
        $this->fin = $fin;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return AbsenceType
     */
    public function getType(): AbsenceType
    {
        return $this->type;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDebut(): \DateTimeImmutable
    {
        return $this->debut;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFin(): \DateTimeImmutable
    {
        return $this->fin;
    }
}
