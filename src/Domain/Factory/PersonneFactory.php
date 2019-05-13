<?php

namespace App\Domain\Factory;

use App\Domain\DTO\PersonneCreerDTO;
use App\Domain\Exception\PersonneEmailDejaEnregistreException;
use App\Domain\Personne;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;

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
     * @throws PersonneEmailDejaEnregistreException
     *
     * @return Personne
     */
    public function create(PersonneCreerDTO $personneCreateDTO)
    {
        $absenceCompteurService = new AbsenceCompteurService($this->absenceRepository);

        $personne = new Personne(
            $personneCreateDTO->email,
            $personneCreateDTO->nom,
            $this->personneRepository,
            $this->absenceRepository,
            $absenceCompteurService
        );

        $compteurs = $absenceCompteurService->init($personne);
        $personne->reinitialiserComptuers($compteurs);

        return $personne;
    }
}
