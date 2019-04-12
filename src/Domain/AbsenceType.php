<?php

namespace App\Domain;

use App\Domain\Exception\AbsenceTypeInvalidException;

/**
 * Type d'absence.
 * C'est un objet-valeur qui n'a pas d'identité.
 * En outre deux objets de cette classe avec le même @see AbsenceType::$type sont égaux.
 *
 * Notez que cette classe est incorporable (embeddable) au niveau de Doctrine.
 * Cela signifie qu'il n'y a pas de table dédiée au type d'absence, en revanche
 * les champs de cette classe sont sauvegardé dans la table d'absence.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceType
{
    const MALADIE = 1;      // Maladie
    const CONGES_PAYES = 2; // Congés payés
    const TELETRAVAIL = 3;  // Télétravail

    /**
     * Type d'absence.
     *
     * @var int
     */
    private $type;

    /**
     * @param int $type
     *
     * @throws AbsenceTypeInvalidException
     */
    public function __construct(int $type)
    {
        if (!in_array($type, [self::MALADIE, self::CONGES_PAYES, self::TELETRAVAIL])) {
            throw new AbsenceTypeInvalidException("Type d'absence inconnu");
        }

        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return string
     */
    public static function getLabel($type)
    {
        switch ($type) {
            case AbsenceType::MALADIE:
                return 'Maladie';

            case AbsenceType::CONGES_PAYES:
                return 'Congé payé';

            case AbsenceType::TELETRAVAIL:
                return 'Télétravail';
        }

        throw new \InvalidArgumentException;
    }

    /**
     * La comparaison d'égalité est une méthode quasi-obligatoire dans les objets-valeurs.
     *
     * @param AbsenceType $absenceType
     *
     * @return bool
     */
    public function isEqualTo(AbsenceType $absenceType)
    {
        return $this->type === $absenceType->getType();
    }
}
