<?php

namespace App\Domain;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\AbsenceInvalidDatesException;
use App\Domain\Exception\AbsenceTypeInvalidException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Repository\PersonneRepositoryInterface;

/**
 * Une personne.
 *
 * Cette classe est une entité et la racine d'un agrégat englobant @see Absence.
 * Il existe un repository pour cette classe @see PersonneRepositoryInterface.
 * Il est conseillé que seules les racines des agrégats aient le repositories.
 *
 * Un nouveau objet de cette classe peut être instancié seulement dans deux cas :
 * - création d'une nouvelle personne dans @see \App\Application\Service\PersonneFactory::create().
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
     * Tableau des absences déposées.
     *
     * Notez que Doctrine sauvegarde toute nouvelle absence automatiquement
     * grâce à la persistance en cascade.
     *
     * @var Absence[]
     */
    private $absences;

    /**
     * Répositoire de la personne.
     * Permet de communiquer avec la couche de persistance (bdd) afin de
     *
     * @var PersonneRepositoryInterface
     */
    private $personneRepository;

    /**
     * @param string $email
     * @param string $nom
     * @param PersonneRepositoryInterface $personneRepository
     *
     * @throws EmailAlreadyTakenException
     */
    public function __construct(string $email, string $nom, PersonneRepositoryInterface $personneRepository)
    {
        // Vérifier que l'email n'est pas encore enregistré.
        if ($personneRepository->emailAlreadyExist($email)) {
            throw new EmailAlreadyTakenException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;
        $this->absences = [];
        $this->personneRepository = $personneRepository;
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
        if ($this->personneRepository->absenceAlreadyExist($this, $debut, $fin)) {
            throw new AbsenceAlreadyTakenException('Une absence pour ces dates a été déjà déposée');
        }

        $absence = new Absence($this, $type, $debut, $fin);
        $this->absences[] = $absence;
    }
}
