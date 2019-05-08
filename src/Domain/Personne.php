<?php

namespace App\Domain;

use App\Domain\DTO\CompteurInfoDTO;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceDatesInvalidesException;
use App\Domain\Exception\AbsenceJoursDisponiblesInsuffisantsException;
use App\Domain\Exception\AbsenceNotFoundException;
use App\Domain\Exception\AbsenceTypeInvalidException;
use App\Domain\Exception\PersonneEmailAlreadyTakenException;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;

/**
 * Une personne.
 *
 * Cette classe est une *entité* et la racine d'un agrégat englobant @see Absence.
 * Il existe un repository pour cette classe @see PersonneRepositoryInterface.
 * Il est conseillé que seules les racines des agrégats aient le repositories.
 *
 * Un nouveau objet de cette classe peut être instancié seulement dans deux cas :
 * - création d'une nouvelle personne dans @see \App\Application\Service\PersonneService::create().
 *   Dans ce cas le @see Personne::__construct() est appelé.
 * - reconstitution d'une personne de la bdd par
 *
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
            throw new PersonneEmailAlreadyTakenException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;
        $this->compteursAbsence = $absenceCompteurService->init($this);
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
    public function update(string $nom)
    {
        $this->nom = $nom;
    }

    /**
     * Déposer une absence.
     * Il n'est pas possible de déposer une absence qui chevauche une absence déjà existante.
     *
     * @param int                $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalidException
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function deposerAbsence(int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        if ($this->absenceRepository->absenceDeposeDansPeriode($this, $debut, $fin)) {
            throw new AbsenceAlreadyTakenException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = new Absence($this, $type, $debut, $fin);
        $this->absenceCompteurService->deposerAbsence($this->compteursAbsence, new AbsenceType($type), $debut, $fin);

        $this->absenceRepository->save($absence);
        $this->personneRepository->save($this);
    }

    /**
     * @param $id
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     * @param int                $type
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceDatesInvalidesException
     * @throws AbsenceTypeInvalidException
     * @throws AbsenceNotFoundException
     * @throws AbsenceJoursDisponiblesInsuffisantsException
     */
    public function modifierAbsence($id, \DateTimeImmutable $debut, \DateTimeImmutable $fin, int $type)
    {
        if ($this->absenceRepository->absenceDeposeDansPeriode($this, $debut, $fin, $id)) {
            throw new AbsenceAlreadyTakenException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = $this->absenceRepository->getAbsence($this, $id);
        $this->absenceCompteurService->modifierAbsence($this->compteursAbsence, $absence, $absence->getType(), $debut, $fin);

        $absence->modify($type, $debut, $fin);

        $this->absenceRepository->save($absence);
        $this->personneRepository->save($this);
    }

    /**
     * @param $id
     *
     * @throws AbsenceNotFoundException
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
     * @throws AbsenceNotFoundException
     *
     * @return Absence
     */
    public function getAbsence($id)
    {
        return $this->absenceRepository->getAbsence($this, $id);
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
