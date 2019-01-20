<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert de données pour:
 * @see \App\Application\Controller\PersonneController::createPerson()
 * @see \App\Application\Service\PersonneFactory::create()
 * @see \App\Application\Form\PersonneCreateType
 *
 * Les champs de cet objet sont validés lors de soumission du formulaire.
 *
 * Finalement les données seront passées dans:
 * @see \App\Domain\Personne::__construct()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneCreateDTO
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
}
