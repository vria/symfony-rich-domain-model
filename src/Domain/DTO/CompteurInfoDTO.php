<?php

namespace App\Domain\DTO;

use App\Domain\AbsenceCompteur;
use App\Domain\AbsenceType;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class CompteurInfoDTO
{
    /**
     * @var string
     */
    public $compteur;

    /**
     * @var int
     */
    public $joursDisponibles;

    /**
     * @var int
     */
    public $joursTravailles;

    /**
     * @var int
     */
    public $periodeIncrementer;

    /**
     * @param AbsenceCompteur $compteur
     */
    public function __construct(AbsenceCompteur $compteur)
    {
        $this->compteur = AbsenceType::getLabel($compteur->getType()->getType());
        $this->joursDisponibles = $compteur->getJoursDisponibles();
        $this->joursTravailles = $compteur->getJoursTravailles();
        $this->periodeIncrementer =
            AbsenceCompteur::PERIODE_INCREMENTER_COMPTEURS[$compteur->getType()->getType()];
    }
}
