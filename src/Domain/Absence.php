<?php

namespace App\Domain;

use App\Domain\Exception\AbsenceDatesDansLePasseException;
use App\Domain\Exception\AbsenceDatesInvalidesException;

/**
 * Une absence déposée.
 * C'est une entité qui est cachée dans un agrégat dont la racine est @see Personne.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class Absence
{
    /**
     * Identité d'une absence autogénérée.
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
     * @internal
     *   Absence peut être créée seulement via @see Personne
     *
     * @param Personne           $personne
     * @param int                $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceDatesInvalidesException
     * @throws
     */
    public function __construct(Personne $personne, int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        $this->personne = $personne;
        $this->type = new AbsenceType($type);
        $this->setDates($debut, $fin);
    }

    /**
     * @internal
     *   Absence peut être modifiée seulement via @see Personne
     *
     * @param int                $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceDatesInvalidesException
     */
    public function modify(int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        $this->type = new AbsenceType($type);
        $this->setDates($debut, $fin);
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormatted($format = 'd/m/Y')
    {
        $typeString = AbsenceType::getLabel($this->type->getType());
        $debutFormatted = $this->debut->format($format);
        $finFormatted = $this->fin->format($format);

        $datesFormatted = $debutFormatted;
        if ($debutFormatted !== $finFormatted) {
            $datesFormatted .= ' - '.$finFormatted;
        }

        return $typeString.' ('.$datesFormatted.')';
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

    /**
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceDatesInvalidesException
     */
    private function setDates(\DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        if ($debut > $fin) {
            throw new AbsenceDatesInvalidesException('Date de fin doit être après la date de début');
        }

        $today = (\DateTimeImmutable::createFromFormat('U', time()))->setTime(0, 0, 0);
        if ($debut < $today) {
            throw new AbsenceDatesDansLePasseException('Le début de l\'absence doit être dans le futur');
        }

        $this->debut = $debut;
        $this->fin = $fin;
    }
}
