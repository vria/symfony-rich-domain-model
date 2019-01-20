<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
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
