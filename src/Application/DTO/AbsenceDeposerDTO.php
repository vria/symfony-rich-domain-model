<?php

namespace App\Application\DTO;

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
class AbsenceDeposerDTO
{
    /**
     * @var int
     *
     * @Assert\NotNull()
     */
    public $type;

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

    public function __construct()
    {
        $this->debut = \DateTimeImmutable::createFromFormat('U', time())->modify('tomorrow');
        $this->fin = \DateTimeImmutable::createFromFormat('U', time())->modify('tomorrow');
    }
}
