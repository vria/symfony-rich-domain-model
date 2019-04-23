<?php

namespace App\Domain\Exception;

/**
 * Les dates de l'absence sont dans le passé.
 * Les dates de l'absnce doivent être postérieures à la date actuelle.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceDatesDansLePasseException extends \InvalidArgumentException
{
}
