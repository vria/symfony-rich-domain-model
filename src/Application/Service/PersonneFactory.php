<?php

namespace App\Application\Service;

use App\Application\DTO\DeposerAbsenceDTO;
use App\Application\DTO\PersonneCreateDTO;
use App\Application\DTO\PersonneUpdateDTO;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceInvalidDatesException;
use App\Domain\Exception\AbsenceTypeInvalidException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;

/**
 * Un service qui traite les actions liées aux objets @see Personne.
 *
 * La tâche principale de ce service est d'appeler les méthodes de la couche de domaine,
 * d'y passer les données de travailler avec DTOs (objets de transfert de données).
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneFactory
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
     * @param PersonneCreateDTO $personneCreateDTO
     *
     * @throws EmailAlreadyTakenException
     */
    public function create(PersonneCreateDTO $personneCreateDTO)
    {
        $personne = new Personne($personneCreateDTO->email, $personneCreateDTO->nom, $this->personneRepository, $this->absenceRepository);

        $this->personneRepository->save($personne);
    }

    /**
     * Modifier les données d'une personne.
     *
     * @param Personne $personne
     * @param PersonneUpdateDTO $personneUpdateDTO
     *
     * @throws EmailAlreadyTakenException
     */
    public function update(Personne $personne, PersonneUpdateDTO $personneUpdateDTO)
    {
        $personne->update($personneUpdateDTO->email, $personneUpdateDTO->nom);

        $this->personneRepository->save($personne);
    }

    /**
     * Déposer une absence.
     *
     * @param Personne $personne
     * @param DeposerAbsenceDTO $deposerAbsenceDTO
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceInvalidDatesException
     * @throws AbsenceTypeInvalidException
     */
    public function deposerAbsence(Personne $personne, DeposerAbsenceDTO $deposerAbsenceDTO)
    {
        $personne->deposerAbsence(
            $deposerAbsenceDTO->debut,
            $deposerAbsenceDTO->fin,
            $deposerAbsenceDTO->type
        );

        $this->personneRepository->save($personne);
    }
}
