<?php

namespace App\Domain\Exception;

/**
 * Type d'absence inconnu a été choisi.
 * La liste de types d'absences valides se trouve dans @see \App\Domain\AbsenceType.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AbsenceTypeInvalidException extends \InvalidArgumentException
{
}
