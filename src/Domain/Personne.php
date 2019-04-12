<?php

namespace App\Domain;

use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceInvalidDatesException;
use App\Domain\Exception\AbsenceNotFoundException;
use App\Domain\Exception\AbsenceTypeInvalidException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Repository\AbsenceRepositoryInterface;
use App\Domain\Repository\PersonneRepositoryInterface;

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
     * @var CompteurAbsence[]
     */
    private $compteursAbsence;

    /**
     * Répositoire de la personne.
     * Permet de communiquer avec la couche de persistance (bdd) afin de
     *
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @var AbsenceRepositoryInterface
     */
    private $absenceRepository;

    /**
     * @param string $email
     * @param string $nom
     * @param PersonneRepositoryInterface $personneRepository
     * @param AbsenceRepositoryInterface $absenceRepository
     */
    public function __construct(string $email, string $nom, PersonneRepositoryInterface $personneRepository, AbsenceRepositoryInterface $absenceRepository)
    {
        // Vérifier que l'email n'est pas encore enregistré.
        if ($personneRepository->emailAlreadyExist($email)) {
            throw new EmailAlreadyTakenException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;
        $this->personneRepository = $personneRepository;
        $this->absenceRepository = $absenceRepository;
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
     * @param string $email
     * @param string $nom
     */
    public function update(string $email, string $nom)
    {
        if ($email !== $this->email && $this->personneRepository->emailAlreadyExist($email)) {
            throw new EmailAlreadyTakenException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;
    }

    /**
     * Déposer une absence.
     * Il n'est pas possible de déposer une absence qui chevauche une absence déjà existante.
     *
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     * @param int $type
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceInvalidDatesException
     * @throws AbsenceTypeInvalidException
     */
    public function deposerAbsence(\DateTimeImmutable $debut, \DateTimeImmutable $fin, int $type)
    {
        if ($this->absenceRepository->absenceAlreadyExist($this, $debut, $fin)) {
            throw new AbsenceAlreadyTakenException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = new Absence($this, $type, $debut, $fin);
        $this->absenceRepository->save($absence);
    }

    /**
     * @param $id
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     * @param int $type
     *
     * @throws AbsenceAlreadyTakenException
     * @throws AbsenceInvalidDatesException
     * @throws AbsenceTypeInvalidException
     */
    public function modifierAbsence($id, \DateTimeImmutable $debut, \DateTimeImmutable $fin, int $type)
    {
        if ($this->absenceRepository->absenceAlreadyExist($this, $debut, $fin, $id)) {
            throw new AbsenceAlreadyTakenException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = $this->absenceRepository->getAbsence($this, $id);
        $absence->modify($type, $debut, $fin);
    }

    /**
     * @param Absence $absence
     */
    public function annulerAbsence(Absence $absence)
    {
        $this->absenceRepository->annuler($absence);
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
     * @return Absence
     *
     * @throws AbsenceNotFoundException
     */
    public function getAbsence($id)
    {
        return $this->absenceRepository->getAbsence($this, $id);
    }
}
