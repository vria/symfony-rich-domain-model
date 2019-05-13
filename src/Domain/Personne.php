<?php

namespace App\Domain;

use App\Domain\DTO\AbsenceDeposerDTO;
use App\Domain\DTO\AbsenceModifierDTO;
use App\Domain\DTO\CompteurInfoDTO;
use App\Domain\Exception\AbsenceDejaDeposeeException;
use App\Domain\Exception\AbsenceDatesInvalidesException;
use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;
use App\Domain\Exception\AbsenceNonTrouveeException;
use App\Domain\Exception\AbsenceTypeInvalideException;
use App\Domain\Exception\PersonneEmailDejaEnregistreException;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;

/**
 * Une personne.
 *
 * Cette classe est une *entité* et la racine d'un agrégat englobant :
 * - @see Absence
 * - @see AbsenceCompteur
 *
 * Il existe un repository pour cette classe @see PersonneRepositoryInterface.
 * Il est conseillé que seules les racines des agrégats aient le repositories
 * accessibles depuis l'exterieur de l'agrégat.
 *
 * Un nouveau objet de cette classe peut être instancié seulement dans deux cas :
 * - création d'une nouvelle personne dans @see \App\Domain\Factory\PersonneFactory::create().
 *   Dans ce cas le @see Personne::__construct() est appelé.
 * - reconstitution d'une personne de la bdd par
 *   @see \App\Infrastructure\Doctrine\Repository\PersonneRepository::get().
 *   Doctrine utilise la réflexion pour intitialiser les champs sans appeler le constructeur.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class Personne
{
    /**
     * Email.
     * C'est l'identifiant d'une personne.
     * Une fois créée la personne ne pourra pas changer son email.
     *
     * @var string
     */
    private $email;

    /**
     * Nom.
     *
     * @var string
     */
    private $nom;

    /**
     * Tableu des compteurs d'absence.
     *
     * @var AbsenceCompteur[]
     */
    private $compteursAbsence;

    /**
     * Répositoire de la personne.
     * Permet de communiquer avec la couche de persistance (bdd) afin de.
     *
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @var AbsenceCompteurService
     */
    private $absenceCompteurService;

    /**
     * @param string                      $email
     * @param string                      $nom
     * @param PersonneRepositoryInterface $personneRepository
     * @param AbsenceRepositoryInterface  $absenceRepository
     * @param AbsenceCompteurService      $absenceCompteurService
     */
    public function __construct(string $email, string $nom, PersonneRepositoryInterface $personneRepository, AbsenceRepositoryInterface $absenceRepository, AbsenceCompteurService $absenceCompteurService)
    {
        // Vérifier que l'email n'est pas encore enregistré.
        if ($personneRepository->emailAlreadyExist($email)) {
            throw new PersonneEmailDejaEnregistreException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;
        $this->personneRepository = $personneRepository;
        $this->absenceRepository = $absenceRepository;
        $this->absenceCompteurService = $absenceCompteurService;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Modifier les données d'une personne.
     *
     * @param string $nom
     */
    public function renommer(string $nom)
    {
        $this->nom = $nom;
    }

    /**
     * Déposer une absence.
     * Il n'est pas possible de déposer une absence qui chevauche une absence déjà existante.
     *
     * @param AbsenceDeposerDTO $dto
     *
     * @throws AbsenceDejaDeposeeException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalideException
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function deposerAbsence(AbsenceDeposerDTO $dto)
    {
        if ($this->absenceRepository->absenceDeposeDansPeriode($this, $dto->debut, $dto->fin)) {
            throw new AbsenceDejaDeposeeException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = new Absence($this, $dto->type, $dto->debut, $dto->fin);
        $this->absenceCompteurService->deposerAbsence($this->compteursAbsence, new AbsenceType($dto->type), $dto->debut, $dto->fin);

        $this->absenceRepository->save($absence);
        $this->personneRepository->save($this);
    }

    /**
     * @param AbsenceModifierDTO $dto
     *
     * @throws AbsenceDejaDeposeeException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalideException
     * @throws AbsenceNonTrouveeException
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function modifierAbsence(AbsenceModifierDTO $dto)
    {
        if ($this->absenceRepository->absenceDeposeDansPeriode($this, $dto->debut, $dto->fin, $dto->getId())) {
            throw new AbsenceDejaDeposeeException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = $this->absenceRepository->getAbsence($this, $dto->getId());
        $this->absenceCompteurService->modifierAbsence($this->compteursAbsence, $absence, new AbsenceType($dto->type), $dto->debut, $dto->fin);

        $absence->modifier($dto->type, $dto->debut, $dto->fin);

        $this->absenceRepository->save($absence);
        $this->personneRepository->save($this);
    }

    /**
     * @param $id
     *
     * @throws AbsenceNonTrouveeException
     */
    public function annulerAbsence($id)
    {
        $absence = $this->getAbsence($id);
        $this->absenceRepository->annuler($absence);
        $this->absenceCompteurService->annulerAbsence($this->compteursAbsence, $absence);
    }

    /**
     * @param \DateTimeImmutable $startPeriod
     * @param \DateTimeImmutable $endPeriod
     *
     * @return Absence[]
     */
    public function getAbsences(\DateTimeImmutable $startPeriod, \DateTimeImmutable $endPeriod)
    {
        return $this->absenceRepository->getAbsences($this, $startPeriod, $endPeriod);
    }

    /**
     * @param $id
     *
     * @throws AbsenceNonTrouveeException
     *
     * @return Absence
     */
    public function getAbsence($id)
    {
        return $this->absenceRepository->getAbsence($this, $id);
    }

    /**
     * @param array $compteursAbsence
     */
    public function reinitialiserComptuers(array $compteursAbsence)
    {
        $this->compteursAbsence = $compteursAbsence;
    }

    /**
     * @var \DateTimeImmutable
     *
     * Incrémenter les jours travailles dans les compteurs concernés
     */
    public function incrementerJoursTravailles(\DateTimeImmutable $date)
    {
        $this->absenceCompteurService->incrementerJoursTravailles($this->compteursAbsence, $this, $date);
    }

    /**
     * @return CompteurInfoDTO[]
     */
    public function getCompteursInfo()
    {
        return $this->absenceCompteurService->getCompteurInfoDTO($this->compteursAbsence);
    }
}
