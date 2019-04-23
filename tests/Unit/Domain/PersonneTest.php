<?php

namespace App\Tests\Unit\Domain;

use App\Domain\Absence;
use App\Domain\AbsenceType;
use App\Domain\Personne;
use App\Domain\Repository\PersonneRepositoryInterface;
use App\Domain\Service\AbsenceCompteurService;
use App\Infrastructure\Doctrine\Repository\AbsenceRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * Unit test case of @see Personne class.
 *
 * @author Vlad Riabchenko <vriabchenko@webnet.fr>
 */
class PersonneTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        // Fixer la date 25/04/2019 pour la classe Absence.
        ClockMock::register(Absence::class);
        ClockMock::withClockMock(strtotime("2019-04-25 15:00:00"));
    }

    /**
     * Le constructeur de @see Personne lève une exception si l'email est déjà pris.
     *
     * @expectedException \App\Domain\Exception\PersonneEmailAlreadyTakenException
     */
    public function testThrowsEmailAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(true);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        new Personne('', '', $personneRepository, $absenceRepository, $absenceCompteurService);
    }

    /**
     * @see Personne::getEmail() renvoie l'email initialisé dans
     * @see Personne::__construct().
     */
    public function testReturnsEmail()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('rick.sanchez@webnet.fr', '', $personneRepository, $absenceRepository, $absenceCompteurService);

        $this->assertEquals('rick.sanchez@webnet.fr', $personne->getEmail());
    }

    /**
     * @see Personne::getNom() renvoie le nom initialisé dans
     * @see Personne::__construct().
     */
    public function testReturnsNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('', 'Sanchez', $personneRepository, $absenceRepository, $absenceCompteurService);

        $this->assertEquals('Sanchez', $personne->getNom());
    }

    /**
     * @see Personne::update() modifie
     * @see Personne::$nom.
     */
    public function testUpdatesNom()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturnOnConsecutiveCalls(false, false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('', 'Rick', $personneRepository, $absenceRepository, $absenceCompteurService);
        $personne->update('Richard');

        $this->assertEquals('Richard', $personne->getNom());
    }

    /**
     * @see Personne::deposerAbsence() jette une exception si une absence existe
     * déjà pour des dates passées selon
     * @see AbsenceRepository::absenceDeposeDansPeriode().
     *
     * @expectedException \App\Domain\Exception\AbsenceAlreadyTakenException
     */
    public function testDeposerAbsenceThrowsAbsenceAlreadyTakenException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceRepository->method('absenceDeposeDansPeriode')->willReturn(true);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('', '', $personneRepository, $absenceRepository, $absenceCompteurService);
        $personne->deposerAbsence(AbsenceType::MALADIE, new \DateTimeImmutable(), new \DateTimeImmutable());
    }

    /**
     * @see Personne::deposerAbsence() et
     * @see Absence::__construct() jetent une exeption si les dates d'absence
     * sont dans le passé.
     *
     * @expectedException \App\Domain\Exception\AbsenceDatesDansLePasseException
     */
    public function testDeposerAbsenceDansLePasseThrowsAbsenceDatesInvalidesException()
    {
        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceRepository->method('absenceDeposeDansPeriode')->willReturn(false);
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('', '', $personneRepository, $absenceRepository, $absenceCompteurService);

        $dateAbsence = new \DateTimeImmutable('2019-04-24 15:00:00');
        $personne->deposerAbsence(AbsenceType::MALADIE, $dateAbsence, $dateAbsence);
    }

    /**
     * @see Personne::deposerAbsence() crée un nouvelle objet de classe
     * @see Absence.
     */
    public function testDeposerAbsenceIsAdded()
    {
        $testCase = $this;
        $debut = new \DateTimeImmutable('2019-04-27');
        $fin = new \DateTimeImmutable('2019-04-28');

        $personneRepository = $this->createMock(PersonneRepositoryInterface::class);
        $personneRepository->method('emailAlreadyExist')->willReturn(false);
        $absenceRepository = $this->createMock(AbsenceRepository::class);
        $absenceRepository->method('absenceDeposeDansPeriode')->willReturn(false);
        $absenceRepository
            ->expects($this->once())
            ->method('save')
            ->will($this->returnCallback(function($absence) use ($testCase, $debut, $fin) {
                /** @var $absence Absence */

                $testCase->assertInstanceOf(Absence::class, $absence);
                $testCase->assertInstanceOf(AbsenceType::class, $absence->getType());
                $testCase->assertEquals(AbsenceType::MALADIE, $absence->getType()->getType());
                $testCase->assertEquals($debut, $absence->getDebut());
                $testCase->assertEquals($fin, $absence->getFin());
            }));
        $absenceCompteurService = $this->createMock(AbsenceCompteurService::class);

        $personne = new Personne('', '', $personneRepository, $absenceRepository, $absenceCompteurService);
        $personne->deposerAbsence(AbsenceType::MALADIE, $debut, $fin);
    }
}
