<?php

namespace App\Application\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneCreateDTO
{
    /**
     * @var string
     *
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
