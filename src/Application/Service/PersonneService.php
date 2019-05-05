<?php

namespace App\Application\Service;

use App\Application\DTO\AbsenceDeposerDTO;
use App\Application\DTO\AbsenceModifierDTO;
use App\Application\DTO\PersonneCreerDTO;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceDatesInvalidesException;
use App\Domain\Exception\AbsenceTypeInvalidException;
use App\Domain\Exception\PersonneEmailAlreadyTakenException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;

/**
 * Un service qui traite les actions liées aux objets @see Personne.
 *
 * La tâche principale de ce service est d'appeler les méthodes de la couche de domaine,
 * d'y passer les données de travailler avec DTOs (objets de transfert de données).
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneService
{
    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @param PersonneRepositoryInterface $personneRepository
     */
    public function __construct(PersonneRepositoryInterface $personneRepository, AbsenceRepositoryInterface $absenceRepository)
    {
        $this->personneRepository = $personneRepository;
        $this->absenceRepository = $absenceRepository;
    }

    /**
     * Ajouter une personne.
     *
     * @param PersonneCreerDTO $personneCreateDTO
     *
     * @throws PersonneEmailAlreadyTakenException
     */
    public function create(PersonneCreerDTO $personneCreateDTO)
    {
        $personne = new Personne(
            $personneCreateDTO->email,
            $personneCreateDTO->nom,
            $this->personneRepository,
            $this->absenceRepository,
            new AbsenceCompteurService($this->absenceRepository)
        );

        $this->personneRepository->save($personne);
    }

    /**
     * Déposer une absence.
     *
     * @param Personne          $personne
     * @param AbsenceDeposerDTO $deposerAbsenceDTO
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalidException
     */
    public function deposerAbsence(Personne $personne, AbsenceDeposerDTO $deposerAbsenceDTO)
    {
        $personne->deposerAbsence(
            $deposerAbsenceDTO->type,
            $deposerAbsenceDTO->debut,
            $deposerAbsenceDTO->fin
        );

        $this->personneRepository->save($personne);
    }

    /**
     * @param Personne           $personne
     * @param AbsenceModifierDTO $modifierAbsenceDTO
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalidException
     */
    public function modifierAbsence(Personne $personne, AbsenceModifierDTO $modifierAbsenceDTO)
    {
        $personne->modifierAbsence(
            $modifierAbsenceDTO->getId(),
            $modifierAbsenceDTO->debut,
            $modifierAbsenceDTO->fin,
            $modifierAbsenceDTO->type
        );
    }
}
