<?php

namespace App\Application\DTO;

use App\Domain\Absence;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert de données pour:.
 *
 * @see \App\Application\Controller\PersonneController::deposerAbsence()
 * @see \App\Application\Service\PersonneService::deposerAbsence()
 * @see \App\Application\Form\AbsenceDeposerType
 *
 * Les champs de cet objet sont validés lors de soumission du formulaire.
 *
 * Finalement les données seront passées dans:
 * @see \App\Domain\Personne::deposerAbsence()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceModifierDTO
{
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
     * Id d'une absence.
     *
     * @var string
     */
    private $id;

    /**
     * @param Absence $absence
     *
     * @return static
     */
    public static function fromAbsence(Absence $absence)
    {
        $dto = new self();

        $dto->debut = $absence->getDebut();
        $dto->fin = $absence->getFin();
        $dto->type = $absence->getType()->getType();
        $dto->id = $absence->getId();

        return $dto;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
