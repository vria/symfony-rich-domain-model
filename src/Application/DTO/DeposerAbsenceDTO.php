<?php

namespace App\Application\DTO;

use App\Domain\AbsenceType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class DeposerAbsenceDTO
{
    /**
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
     */
    public function __construct(string $email)
    {
        $this->email = $email;
        $this->debut = new \DateTimeImmutable('tomorrow');
        $this->fin = new \DateTimeImmutable('tomorrow');
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
