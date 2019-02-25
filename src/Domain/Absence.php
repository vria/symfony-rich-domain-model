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
class Absence extends AbsenceImmutable
{
    /**
     * @return AbsenceImmutable
     */
    public function getAbsenceImmutable()
    {
        return new AbsenceImmutable($this->personne, $this->type->getType(), $this->debut, $this->fin);
    }
}
