<?php

namespace App\Domain\DTO;

use App\Domain\AbsenceType;
use App\Domain\AbsenceCompteur;

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
     * @var integer
     */
    public $joursDisponibles;

    /**
     * @var integer
     */
    public $joursTravailles;

    /**
     * @var integer
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
