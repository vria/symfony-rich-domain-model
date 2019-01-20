<?php

namespace App\Domain;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 *
 * @internal
 */
class Absence
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Personne
     */
    private $personne;

    /**
     * @var AbsenceType
     */
    private $type;

    /**
     * @var \DateTimeImmutable
     */
    private $debut;

    /**
     * @var \DateTimeImmutable
     */
    private $fin;

    /**
     * @param Personne $personne
     * @param int $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     */
    public function __construct(Personne $personne, int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        if ($debut > $fin) {
            throw new \InvalidArgumentException('Date de début doit être avant la date de fin');
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
