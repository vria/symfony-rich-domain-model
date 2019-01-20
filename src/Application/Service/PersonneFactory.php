<?php

namespace App\Application\Service;

use App\Application\DTO\DeposerAbsenceDTO;
use App\Application\DTO\PersonneCreateDTO;
use App\Application\DTO\PersonneUpdateDTO;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneFactory
{
    /**
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @param PersonneRepositoryInterface $personneRepository
     */
    public function __construct(PersonneRepositoryInterface $personneRepository)
    {
        $this->personneRepository = $personneRepository;
    }

    /**
     * @param PersonneCreateDTO $personneCreateDTO
     *
     * @throws EmailAlreadyTakenException
     */
    public function create(PersonneCreateDTO $personneCreateDTO)
    {
        $personne = new Personne($personneCreateDTO->email, $personneCreateDTO->nom, $this->personneRepository);

        $this->personneRepository->save($personne);
    }

    /**
     * @param Personne $personne
     * @param PersonneUpdateDTO $personneUpdateDTO
     */
    public function update(Personne $personne, PersonneUpdateDTO $personneUpdateDTO)
    {
        $personne->update($personneUpdateDTO->email, $personneUpdateDTO->nom);

        $this->personneRepository->save($personne);
    }

    /**
     * @param Personne $personne
     * @param DeposerAbsenceDTO $deposerAbsenceDTO
     *
     * @throws AbsenceAlreadyTakenException
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
