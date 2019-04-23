<?php

namespace App\Domain\Exception;

/**
 * L'email été déjà enregistré.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneEmailAlreadyTakenException extends \InvalidArgumentException
{
}
