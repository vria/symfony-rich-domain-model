<?php

namespace App\Domain\Service;

use App\Domain\Absence;
use App\Domain\AbsenceType;
use App\Domain\AbsenceCompteur;
use App\Domain\DTO\CompteurInfoDTO;
use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceCompteurService
{
    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @param AbsenceRepositoryInterface $absenceRepository
     */
    public function __construct(AbsenceRepositoryInterface $absenceRepository)
    {
        $this->absenceRepository = $absenceRepository;
    }

    /**
     * @param Personne $personne
     *
     * @return AbsenceCompteur[]
     */
    public function init(Personne $personne)
    {
        $compteurs = [];
        foreach (AbsenceCompteur::TYPES_COMPTEUR as $typeCompteur) {
            $compteurs[] = new AbsenceCompteur($personne, $typeCompteur);
        }

        return $compteurs;
    }

    /**
     * @param AbsenceCompteur[] $compteurs
     * @param Personne $personne
     * @param \DateTimeImmutable $date
     */
    public function incrementerJoursTravailles($compteurs, Personne $personne, \DateTimeImmutable $date): void
    {
        if ($this->absenceRepository->absenceDeposePourDate($personne, $date, AbsenceCompteur::TYPES_ABSENT)) {
            return;
        };

        foreach ($compteurs as $compteur) {
            if (!self::typeCompteurs($compteur->getType())) {
                return;
            }

            $compteur->incrementerJoursTravailles();
        }
    }

    /**
     * @param AbsenceCompteur[] $compteurs
     * @param AbsenceType $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function deposerAbsence($compteurs, AbsenceType $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin): void
    {
        if (!self::typeCompteurs($type)) {
            return;
        }

        foreach ($compteurs as $compteur) {
            if ($compteur->getType()->isEqualTo($type)) {
                $jours = self::calculerJoursAbsence($debut, $fin);
                $compteur->deposerAbsence($jours);
            }
        }
    }

    /**
     * @param AbsenceCompteur[] $compteurs
     * @param Absence $absence
     */
    public function annulerAbsence($compteurs, Absence $absence): void
    {
        if (!self::typeCompteurs($absence->getType())) {
            return;
        }

        foreach ($compteurs as $compteur) {
            if ($compteur->getType()->isEqualTo($absence->getType())) {
                $jours = self::calculerJoursAbsence($absence->getDebut(), $absence->getFin());
                $compteur->annulerAbsence($jours);

                break;
            }
        }
    }

    /**
     * @param AbsenceCompteur[] $compteurs
     * @param Absence $absence
     * @param AbsenceType $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     *
     * @author Vlad Riabchenko <vriabchenko@webnet.fr>
     */
    public function modifierAbsence($compteurs, Absence $absence, AbsenceType $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin): void
    {
        $this->annulerAbsence($compteurs, $absence);
        $this->deposerAbsence($compteurs, $type, $debut, $fin);
    }

    /**
     * @param AbsenceCompteur[] $compteurs
     *
     * @return CompteurInfoDTO[]
     */
    public function getCompteurInfoDTO($compteurs)
    {
        $compteursInfo = [];
        foreach ($compteurs as $compteur) {
            $compteursInfo[] = new CompteurInfoDTO($compteur);
        }

        return $compteursInfo;
    }

    /**
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @return int
     */
    private function calculerJoursAbsence(\DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        return intval($fin->diff($debut)->days) + 1;
    }

    /**
     * @param AbsenceType $type
     *
     * @return bool
     */
    private function typeCompteurs(AbsenceType $type)
    {
        return in_array($type->getType(), AbsenceCompteur::TYPES_COMPTEUR);
    }
}
