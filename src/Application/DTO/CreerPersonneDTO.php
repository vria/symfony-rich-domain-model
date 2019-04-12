<?php

namespace App\Application\DTO;

use App\Domain\Personne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert de données pour:
 * @see \App\Application\Controller\PersonneController::createPerson()
 * @see \App\Application\Service\PersonneService::create()
 * @see \App\Application\Form\CreerPersonneType
 *
 * Les champs de cet objet sont validés lors de soumission du formulaire.
 *
 * Finalement les données seront passées dans:
 * @see \App\Domain\Personne::__construct()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class CreerPersonneDTO
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $nom;

    /**
     * @param Personne $personne
     *
     * @return static
     */
    public static function fromPerson(Personne $personne)
    {
        $dto = new static;

        $dto->email = $personne->getEmail();
        $dto->nom = $personne->getNom();

        return $dto;
    }
}
