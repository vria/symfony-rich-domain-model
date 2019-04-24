<?php

namespace App\Tests\Fixtures;

use App\Domain\Absence;
use App\Domain\AbsenceCompteur;
use App\Domain\AbsenceType;
use App\Domain\Personne;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class AppFixtures extends Fixture
{
    /**
     * @inheritdoc
     */
    public function load(ObjectManager $om)
    {
        $rick = $this->createPersonne('rsanchez@webnet.fr', 'Rick');
        $om->persist($rick);
        $om->persist($this->createAbsenceCompteur($rick, AbsenceType::CONGES_PAYES, 1, 9));
        $om->persist($this->createAbsenceCompteur($rick, AbsenceType::TELETRAVAIL, 0, 2));
        $om->persist($this->createAbsence($rick, AbsenceType::CONGES_PAYES, new \DateTimeImmutable('2019-04-20 00:00:00'), new \DateTimeImmutable('2019-04-24 00:00:00')));

        $morthy = $this->createPersonne('msmith@webnet.fr', 'Morthy');
        $om->persist($morthy);
        $om->persist($this->createAbsenceCompteur($morthy, AbsenceType::CONGES_PAYES, 0, 2));
        $om->persist($this->createAbsenceCompteur($morthy, AbsenceType::TELETRAVAIL, 2, 4));
        $om->persist($this->createAbsence($morthy, AbsenceType::TELETRAVAIL, new \DateTimeImmutable('2019-04-22 00:00:00'), new \DateTimeImmutable('2019-04-22 00:00:00')));

        $birdperson = $this->createPersonne('bperson@webnet.fr', 'Birdperson');
        $om->persist($birdperson);
        $om->persist($this->createAbsenceCompteur($birdperson, AbsenceType::CONGES_PAYES, 1, 5));
        $om->persist($this->createAbsenceCompteur($birdperson, AbsenceType::TELETRAVAIL, 1, 0));

        $om->flush();
    }

    /**
     * @param $email
     * @param $nom
     *
     * @return Personne
     */
    private function createPersonne($email, $nom)
    {
        $reflClass = new \ReflectionClass(Personne::class);
        $personne = $reflClass->newInstanceWithoutConstructor();

        $reflProp = $reflClass->getProperty('email');
        $reflProp->setAccessible(true);
        $reflProp->setValue($personne, $email);

        $reflProp = $reflClass->getProperty('nom');
        $reflProp->setAccessible(true);
        $reflProp->setValue($personne, $nom);

        return $personne;
    }

    /**
     * @param Personne $personne
     * @param int $type
     * @param int $joursDisponibles
     * @param int $joursTravailles
     *
     * @return AbsenceCompteur
     */
    private function createAbsenceCompteur(Personne $personne, int $type, int $joursDisponibles, int $joursTravailles)
    {
        $compteur = new AbsenceCompteur($personne, $type);

        $reflClass = new \ReflectionClass(AbsenceCompteur::class);

        $reflProp = $reflClass->getProperty('joursDisponibles');
        $reflProp->setAccessible(true);
        $reflProp->setValue($compteur, $joursDisponibles);

        $reflProp = $reflClass->getProperty('joursTravailles');
        $reflProp->setAccessible(true);
        $reflProp->setValue($compteur, $joursTravailles);

        return $compteur;
    }

    /**
     * @param Personne $personne
     * @param int $type
     * @param \DateTimeImmutable $debut
     * @param \DateTimeImmutable $fin
     *
     * @return Absence
     */
    public function createAbsence(Personne $personne, int $type, \DateTimeImmutable $debut, \DateTimeImmutable $fin)
    {
        $reflClass = new \ReflectionClass(Absence::class);
        $absence = $reflClass->newInstanceWithoutConstructor();

        $reflProp = $reflClass->getProperty('personne');
        $reflProp->setAccessible(true);
        $reflProp->setValue($absence, $personne);

        $reflProp = $reflClass->getProperty('type');
        $reflProp->setAccessible(true);
        $reflProp->setValue($absence, new AbsenceType($type));

        $reflProp = $reflClass->getProperty('debut');
        $reflProp->setAccessible(true);
        $reflProp->setValue($absence, $debut);

        $reflProp = $reflClass->getProperty('fin');
        $reflProp->setAccessible(true);
        $reflProp->setValue($absence, $fin);

        return $absence;
    }
}
