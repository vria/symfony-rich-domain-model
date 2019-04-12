<?php

namespace App\Application\DTO;

use App\Domain\Absence;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert de données pour:
 * @see \App\Application\Controller\PersonneController::deposerAbsence()
 * @see \App\Application\Service\PersonneService::deposerAbsence()
 * @see \App\Application\Form\DeposerAbsenceType
 *
 * Les champs de cet objet sont validés lors de soumission du formulaire.
 *
 * Finalement les données seront passées dans:
 * @see \App\Domain\Personne::deposerAbsence()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class ModifierAbsenceDTO
{
    /**
     * Id d'une absence.
     *
     * @var string
     */
    private $id;

    /**
     * L'email d'une personne n'est pas modifiable lors du dépôt d'absence.
     *
     * @var string
     */
    private $email;

    /**
     * @var \DateTimeImmutable
     *
     * @Assert\NotNull()
     */
    public $debut;

    /**
     * @var \DateTimeImmutable
     *
     * @Assert\NotNull()
     */
    public $fin;

    /**
     * @var int
     *
     * @Assert\NotNull()
     */
    public $type;

    /**
     * @param string $email
     * @param Absence $absence
     *
     * @return static
     */
    public static function fromAbsence(string $email, Absence $absence)
    {
        $dto = new self;

        $dto->id = $absence->getId();
        $dto->email = $email;
        $dto->debut = $absence->getDebut();
        $dto->fin = $absence->getFin();
        $dto->type = $absence->getType()->getType();

        return $dto;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
