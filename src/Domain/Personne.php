<?php

namespace App\Domain;
use App\Domain\Exception\AbsenceAlreadyTakenException;
use App\Domain\Exception\EmailAlreadyTakenException;
use App\Domain\Repository\PersonneRepositoryInterface;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class Personne
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var Absence[]
     */
    private $absences;

    /**
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
     * @param string $email
     * @param string $nom
     *
     * @return $this
     */
    public function update(string $email, string $nom)
    {
        if ($email !== $this->email && $this->personneRepository->emailAlreadyExist($email)) {
            throw new EmailAlreadyTakenException($email.' a été déjà enregistré');
        }

        $this->email = $email;
        $this->nom = $nom;

        return $this;
    }

    /**
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     * @param int $type
     *
     * @throws AbsenceAlreadyTakenException
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
