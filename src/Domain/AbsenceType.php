<?php

namespace App\Domain;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceType
{
    const MALADIE = 1;
    const CONGE_PAYES = 2;

    private $type;

    /**
     * @param int $type
     */
    public function __construct(int $type)
    {
        $this->type = $type;
    }
}
