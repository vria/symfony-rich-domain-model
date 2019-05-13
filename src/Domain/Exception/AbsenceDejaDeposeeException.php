<?php

namespace App\Domain\Exception;

/**
 * Impossible de déposer une absence parce que les dates démandées sont déjà
 * prises par une autre absence.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceDejaDeposeeException extends \InvalidArgumentException
{
}
