<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objet de transfert de données pour:
 * @see \App\Application\Controller\PersonneController::updatePerson()
 * @see \App\Application\Service\PersonneFactory::update()
 * @see \App\Application\Form\PersonneUpdateType
 *
 * Les champs de cet objet sont validés lors de soumission du formulaire.
 *
 * Finalement les données seront passées dans:
 * @see \App\Domain\Personne::update()
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneUpdateDTO
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    public $nom;

    /**
     * @param string $email
     * @param string $nom
     */
    public function __construct(string $email, string $nom)
    {
        $this->email = $email;
        $this->nom = $nom;
    }
}
