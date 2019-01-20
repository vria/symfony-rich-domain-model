<?php

namespace App\Domain\Exception;

/**
 * Les dates invalides ont été choisies (la date de début est postérieure à la date de fin).
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceInvalidDatesException extends \InvalidArgumentException
{
}
